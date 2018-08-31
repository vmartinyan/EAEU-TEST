<?php

class TMM_MigrateImport extends TMM_MigrateHelper {

	private $saved_options = array();
	private $demo_user_id = 0;

	public function __construct() {}
	
	/* handle objects before push content to eval function */
	private static function set_state($array) {
		$obj = new stdClass();
		foreach($array as $k => $v){
			$obj->$k = $v;
		}
		return serialize($obj);
	}

	/* ajax */
	public function import_content() {
		$result = array(
			'tables_count' => 0,
			'attachments' => array(),
		);

		/* backup database tables */
		if ( (bool) $_POST['backup'] ) {
			$export = new TMM_MigrateExport();
			$export->backup_data();
		}

		/* save general options */
		$this->saved_options = array(
			'blogname' => get_option('blogname'),
			'blogdescription' => get_option('blogdescription'),
			'admin_email' => get_option('admin_email'),
			'auth_key' => get_option('auth_key'),
			'auth_salt' => get_option('auth_salt'),
			'ftp_credentials' => get_option('ftp_credentials'),
			'db_version' => get_option('db_version'),
			'initial_db_version' => get_option('initial_db_version'),
		);

		/* create demo user */
		$demo_user_id = username_exists('demo');
		if ($demo_user_id) {
			$this->demo_user_id = $demo_user_id;
		} else {
			$this->demo_user_id = wp_create_user('demo', 'demo', 'demouser@webtemplatemasters.com');
			wp_update_user( array( 'ID' => $this->demo_user_id, 'display_name' => 'Demo' ) );
			wp_update_user( array( 'ID' => $this->demo_user_id, 'user_url' => get_user_option('user_url', 1) ) );
			update_user_meta($this->demo_user_id, 'description', get_user_meta(1, 'description', 1));
			update_user_meta($this->demo_user_id, 'twitter', get_user_meta(1, 'twitter', 1));
			update_user_meta($this->demo_user_id, 'facebook', get_user_meta(1, 'facebook', 1));
			update_user_meta($this->demo_user_id, 'pinteres', get_user_meta(1, 'pinteres', 1));
			update_user_meta($this->demo_user_id, 'rss', get_user_meta(1, 'rss', 1));
			update_user_meta($this->demo_user_id, 'gplus', get_user_meta(1, 'gplus', 1));
		}

		/* upload archive with demo data */
		$db_upload_dir = $this->create_upload_folder();

		$demofile_name = TMM_MIGRATE_PATH . 'demo_data/' . self::folder_key . '.zip';

		if (file_exists($demofile_name)) {
			copy( $demofile_name, $db_upload_dir . self::folder_key . '.zip' );
		}

		/* extract and install demo content */
		$this->extract_zip();
		chdir($db_upload_dir);
		$dat_files = glob("*.dat");

		if(is_array($dat_files)){
			$result['tables_count'] = count($dat_files);

			foreach ($dat_files as $filename) {
				$table = basename($filename, '.dat');

				try {
					if (@strrpos($table, '_users', -6) === false && @strrpos($table, '_usermeta', -9) === false) {
						$this->process_table($table);
					}
					if(file_exists($db_upload_dir . $table . '.dsc')){
						unlink($db_upload_dir . $table . '.dsc');
					}
					if(file_exists($db_upload_dir . $table . '.dat')){
						unlink($db_upload_dir . $table . '.dat');
					}
				} catch (Exception $e) {}
			}
			if(file_exists($db_upload_dir . 'wpdb.prfx')){
				unlink($db_upload_dir . 'wpdb.prfx');
			}
		}

		/* upload attachments */
		if ( (bool) $_POST['upload_attachments'] ) {

			if (TMM_MIGRATE_UPLOAD_ATTACHMENTS_PACK) {
				/* copy attachments folders with files to uploads folder */
				$this->upload_attachments_pack();
			} else {
				/* upload each attachment by ajax */
				$uploads_url = wp_upload_dir();
				$uploads_url = $uploads_url['baseurl'] . '/';
				$attachments = get_posts( array('post_type' => 'attachment', 'post_mime_type' => 'image, video, audio', 'numberposts' => -1) );

				foreach ( $attachments as $attachment ) {

					$attached_file = get_post_meta($attachment->ID, '_wp_attached_file', 1);
					if ($attached_file && $uploads_url . $attached_file !== $attachment->guid) {
						$attached_file = $uploads_url . $attached_file;
						$result['attachments'][] = str_replace( $uploads_url, '', $attached_file );
					}

					$result['attachments'][] = str_replace( $uploads_url, '', $attachment->guid );
				}
			}

		}

		$this->delete_dir($db_upload_dir);
		wp_die( json_encode($result) );
	}

	public function process_table($table) {
		global $wpdb;
		$db_upload_dir = $this->get_upload_dir();
		$table_dsc = unserialize(file_get_contents($db_upload_dir . $table . '.dsc'));
		$old_wpdb_prefix = file_get_contents($db_upload_dir . 'wpdb.prfx');
		$new_table_name = preg_replace('[^' . $old_wpdb_prefix . ']', $wpdb->prefix, $table);

		$wpdb->query('DROP TABLE IF EXISTS `' . $new_table_name . '`;');

		$table_sql = "CREATE TABLE `" . $new_table_name . "` (";
		if (!empty($table_dsc)) {
			$PRIMARY_KEY = "";
			$UNIQUE_KEY = "";
			$KEY = array();
			foreach ($table_dsc as $col) {
				$table_sql.="`" . $col->Field . "` " . $col->Type;

				if ($col->Null == 'NO') {
					$table_sql.=" NOT NULL";
				}

				if (!empty($col->Default)) {
					$table_sql.=" DEFAULT '" . $col->Default . "'";
				}

				if ($col->Extra == 'auto_increment') {
					$table_sql.=" AUTO_INCREMENT";
				}

				if ($col->Key == 'PRI') {
					$set_pk = true;
					if (($col->Field == 'term_taxonomy_id' OR $col->Field == 'object_id') AND $new_table_name == ($wpdb->prefix . 'term_relationships')) {
						//prevent little bug in db
						$set_pk = false;
					}

					if ($set_pk) {
						$PRIMARY_KEY = $col->Field;
					}
				}

				if ($col->Key == 'UNI') {
					$UNIQUE_KEY = $col->Field;
				}

				if ($col->Key == 'MUL') {
					$KEY[] = $col->Field;
				}

				$table_sql.=',';
			}

			if (!empty($PRIMARY_KEY)) {
				$table_sql.="PRIMARY KEY (`" . $PRIMARY_KEY . "`),";
			}

			if (!empty($UNIQUE_KEY)) {
				if ($table == $old_wpdb_prefix . 'term_taxonomy') {
					$table_sql.="`term_id_taxonomy` (`term_id`,`taxonomy`)";
				} else {
					$table_sql.="UNIQUE KEY `" . $UNIQUE_KEY . "` (`$UNIQUE_KEY`),";
				}
			}

			if (!empty($KEY)) {
				foreach ($KEY as $k) {
					$table_sql.="KEY `" . $k . "` (`" . $k . "`),";
				}
			}
		}
		$table_sql.=");";
		$table_sql = str_replace(",);", ");", $table_sql);
		$wpdb->query($table_sql);

		/* data inserting */
		$content = str_replace('__tmm_old_home_url__', home_url(), file_get_contents($db_upload_dir . $table . '.dat'));
		$content = str_replace('__tmm_wpdb_prefix__', $wpdb->prefix, $content);
		$content = str_replace('stdClass::__set_state', 'self::set_state', $content);

		eval('$table_data=' . $content . ';');
		
		if (!empty($table_data) AND is_array($table_data)) {
			foreach ($table_data as $row) {

				if (!empty($row)) {
					$data = array();

					if (isset($row['option_name']) && isset($this->saved_options[ $row['option_name'] ])) {
						$row['option_value'] = $this->saved_options[ $row['option_name'] ];
					}

					if (isset($row['post_author'])) {
						$row['post_author'] = $this->demo_user_id;
					}

					foreach ($row as $key => $value) {

						if (is_array($value) OR is_object($value)) {
							$data[$key] = serialize($value);
						} else {
							$data[$key] = $value;
						}

					}
				}
				$wpdb->insert($new_table_name, $data);
			}
		}
	}

	public function upload_attachments_pack() {
		$is_filesystem = WP_Filesystem();

		if ($is_filesystem) {
			$upload_dir = $this->get_wp_upload_dir();
			$is_content_copied = copy_dir( TMM_MIGRATE_PATH . 'demo_data/uploads/', $upload_dir, array('.DS_Store', 'thumbs.db'));

			if ($is_content_copied) {
				return 'Files imported!';
			}

		}

		return 'Files uploading error!';
	}

	public function upload_attachment( $attachment ) {
		if (!empty($_POST['attachment'])) {
			$attachment = $_POST['attachment'];
		}

		if (TMM_MIGRATE_UPLOAD_ATTACHMENT_BY_HTTP) {
			/* upload file by HTTP request */
			$msg = $this->upload_attachment_handler($attachment);
		} else {
			/* copy file by WP_Filesystem */
			$msg = $this->copy_attachment_handler($attachment);
		}

		if (!empty($_POST['attachment'])) {
			echo ($msg);exit();
		} else {
			return $msg;
		}

	}

	/**
	 * Copy file to uploads folder.
	 * @param $path
	 *
	 * @return string
	 */
	protected function copy_attachment_handler( $path ) {
		$file_name = basename( $path );
		$upload_dir = $this->get_wp_upload_dir();
		$is_filesystem = WP_Filesystem();

		if ( file_exists( $upload_dir. $path ) ) {
			return 'File already exists - ' . $file_name;
		}

		if ($is_filesystem) {
			global $wp_filesystem;
			$dirs = explode('/', str_replace($file_name, '', $path) );

			$wp_filesystem->mkdir( $upload_dir . $dirs[0] );
			$wp_filesystem->mkdir( $upload_dir . $dirs[0] . '/' . $dirs[1] );

			$is_content_copied = $wp_filesystem->copy( TMM_MIGRATE_PATH . 'demo_data/uploads/' . $path, $upload_dir . $path);

			if ($is_content_copied) {
				return 'File imported: ' . $file_name;
			}

		}

		return 'File uploading error - ' . $file_name;
	}

	/**
	 * Upload file by HTTP request. This function not uses, use copy_attachment_handler instead.
	 * @param $url
	 *
	 * @return string
	 */
	protected function upload_attachment_handler( $url ) {
		$file_name = basename( $url );
		$post_date_format = substr($url, 0, 7);
		$upload_dir = $this->get_wp_upload_dir();

		if ( file_exists( $upload_dir. $url ) ) {
			return 'File already exists - ' . $file_name;
		}

		/* create placeholder file in the upload dir */
		$upload = wp_upload_bits( $file_name, 0, '', $post_date_format );

		if ( $upload['error'] ) {
			return $upload['error'] . ' - ' . $file_name;
		}

		if ( !wp_check_filetype( $upload['file'] ) ) {
			return 'Failure: invalid file type - ' . $file_name;
		}

		/* fetch the remote url and write it to the placeholder file */
		$url = TMM_MIGRATE_URL . '/demo_data/uploads/' . $url;
		$headers = $this->tmm_get_http( $url, $upload['file'] );

		if ( !$headers || $headers['response'] != '200' ) {
			@unlink( $upload['file'] );
			return 'Failure: remote server did not respond - ' . $file_name;
		}

		$filesize = @filesize( $upload['file'] );

		if ( !$filesize || (isset($headers['content-length']) && $filesize != $headers['content-length']) ) {
			@unlink( $upload['file'] );
			return 'Failure: incorrect file size - ' . $file_name;
		}

		return 'File imported: ' . $file_name;
	}

	/**
	 * Perform a HTTP HEAD or GET request. (wp_get_http)
	 *
	 * If $file_path is a writable filename, this will do a GET request and write
	 * the file to that path.
	 *
	 * @since 2.5.0
	 *
	 * @param string      $url       URL to fetch.
	 * @param string|bool $file_path Optional. File path to write request to. Default false.
	 * @param int         $red       Optional. The number of Redirects followed, Upon 5 being hit,
	 *                               returns false. Default 1.
	 * @return bool|string False on failure and string of headers if HEAD request.
	 */
	public function tmm_get_http( $url, $file_path = false, $red = 1 ) {
		@set_time_limit( 180 );//increased time_limit

		if ( $red > 5 )
			return false;

		$options = array();
		$options['redirection'] = 5;
		$options['timeout'] = 180;//increased timeout

		if ( false == $file_path )
			$options['method'] = 'HEAD';
		else
			$options['method'] = 'GET';

		$response = wp_safe_remote_request( $url, $options );

		if ( is_wp_error( $response ) )
			return false;

		$headers = wp_remote_retrieve_headers( $response );
		$headers['response'] = wp_remote_retrieve_response_code( $response );

		// WP_HTTP no longer follows redirects for HEAD requests.
		if ( 'HEAD' == $options['method'] && in_array($headers['response'], array(301, 302)) && isset( $headers['location'] ) ) {
			return wp_get_http( $headers['location'], $file_path, ++$red );
		}

		if ( false == $file_path )
			return $headers;

		// GET request - write it to the supplied filename
		$out_fp = fopen($file_path, 'w');
		if ( !$out_fp )
			return $headers;

		fwrite( $out_fp,  wp_remote_retrieve_body( $response ) );
		fclose($out_fp);
		clearstatcache();

		return $headers;
	}

}
