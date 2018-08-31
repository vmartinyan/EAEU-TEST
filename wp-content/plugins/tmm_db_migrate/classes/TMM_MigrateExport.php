<?php

class TMM_MigrateExport extends TMM_MigrateHelper {

	public function __construct() {}

	//ajax
	public function prepare_export_data() {
		$this->create_upload_folder('', true);
		wp_die(json_encode($this->get_wp_tables()));
	}

	public function backup_data() {
		$this->create_upload_folder('tmm_backup');
		$tables = $this->get_wp_tables();

		foreach ( $tables as $table ) {
			$this->process_table($table, true);
		}

		@$this->zip_export_data(true);
	}

	//ajax
	public function process_table($table = '', $is_backup = false) {
		global $wpdb;
		$result = array();

		if (!empty($_POST['table'])) {
			$table = $_POST['table'];
		}

		if (!empty($_POST['is_backup'])) {
			$is_backup = (bool) $_POST['is_backup'];
		}

		if ($table && @strrpos($table, '_users', -6) === false && @strrpos($table, '_usermeta', -9) === false) {

			$query_res = $wpdb->get_results("SELECT * FROM " . $table, ARRAY_A);

			if (!empty($query_res)) {

				foreach ($query_res as $row) {

					if (is_array($row)) {
						foreach ($row as $field_key => $value) {
							if (is_serialized($value)) {
								$row[$field_key] = @unserialize($value);
							}
						}
					}

					/* remove transient in wp_options */
					if ($table == $wpdb->options) {
						$continue_array = array(
							'layerslider_update_info',
							'wp_icl_translators_cached'
						);
	                    $pos = strpos($row['option_name'], '_transient_');

						if (in_array($row['option_name'], $continue_array) || $pos !== false) {
							continue;
						}

					}

					$result[] = $row;
				}
			}

			$content = $this->prepare_export_content(var_export($result, true));
			$path = $this->get_upload_dir();

			if ($is_backup) {
				$path = $this->create_upload_folder('tmm_backup');
			}

			file_put_contents( $path . $table . '.dat', $content );
			file_put_contents( $path . $table . '.dsc', serialize($wpdb->get_results("DESCRIBE " . $table)) );

		}

		if (!empty($_POST['table'])) {
			wp_die(count($result));
		}
	}

	private function prepare_export_content($content) {
		$content = str_replace('\\/', '/', $content);
		$content = str_replace(home_url(), '__tmm_old_home_url__', $content);

		global $wpdb;
		$tpl_prefix = '__tmm_wpdb_prefix__';
		$content = str_replace($wpdb->prefix, $tpl_prefix, $content);
		$revert_prefix_array_repl = array(
			'wp_inactive_widgets',
			'wp_maybe_auto_update',
			'wp_version_check',
			'wp_update_plugins',
			'wp_update_themes',
			'wp_scheduled_delete',
			'wp_scheduled_auto_draft_delete',
			'wp_list_categories',
			'wp_enqueue_style',
			'wp_enqueue_script',
			'_wp_page_template',
			'dismissed_wp_pointers',
			'_wp_attached_file',
			'_wp_attachment_metadata'
		);
		$revert_prefix_array = array(
			$tpl_prefix . 'inactive_widgets',
			$tpl_prefix . 'maybe_auto_update',
			$tpl_prefix . 'version_check',
			$tpl_prefix . 'update_plugins',
			$tpl_prefix . 'update_themes',
			$tpl_prefix . 'scheduled_delete',
			$tpl_prefix . 'scheduled_auto_draft_delete',
			$tpl_prefix . 'list_categories',
			$tpl_prefix . 'enqueue_style',
			$tpl_prefix . 'enqueue_script',
			'_' . $tpl_prefix . 'page_template',
			'dismissed_' . $tpl_prefix . 'pointers',
			'_' . $tpl_prefix . 'attached_file',
			'_' . $tpl_prefix . 'attachment_metadata'
		);

		$content = str_replace($revert_prefix_array, $revert_prefix_array_repl, $content);
		return $content;
	}

	//ajax
	public function zip_export_data($is_backup = false) {
		$uploads_path = $this->get_wp_upload_dir();
		$zip_path = $this->get_upload_dir();

		if ($is_backup) {
			$zip_path = $uploads_path . 'tmm_backup/';
		}

		$tables = $this->get_wp_tables();
		$zip_filename = $zip_path . self::folder_key . '.zip';

		global $wpdb;
		
		file_put_contents($zip_path . 'wpdb.prfx', $wpdb->prefix);

		mbstring_binary_safe_encoding();
				
		require_once(ABSPATH . 'wp-admin/includes/class-pclzip.php');
		$zip = new PclZip($zip_filename);

		if (!empty($tables)) {
			foreach ($tables as $table) {
				$file = $table . '.dat';
				$zip->add($zip_path . $file, PCLZIP_OPT_REMOVE_PATH, $zip_path);
				$file = $table . '.dsc';
				$zip->add($zip_path . $file, PCLZIP_OPT_REMOVE_PATH, $zip_path);
			}
		}

		$zip->add($zip_path . 'wpdb.prfx', PCLZIP_OPT_REMOVE_PATH, $zip_path);
		@$zip->create();

		reset_mbstring_encoding();
		
		foreach ($tables as $table) {
			if(file_exists($zip_path . $table . '.dsc')){
				unlink($zip_path . $table . '.dsc');
			}
			if(file_exists($zip_path . $table . '.dat')){
				unlink($zip_path . $table . '.dat');
			}
		}
		if(file_exists($zip_path . 'wpdb.prfx')){
			unlink($zip_path . 'wpdb.prfx');
		}

		if (!$is_backup) {
			wp_die($this->get_zip_file_url());
		}

	}

}
