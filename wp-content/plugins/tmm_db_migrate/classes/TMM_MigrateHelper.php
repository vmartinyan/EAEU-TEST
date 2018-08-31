<?php

class TMM_MigrateHelper {

	const folder_key = 'tmm_db_migrate';

	const DIRSEP = '/';

	public function get_upload_dir() {
		$wp_upload_dir =  $this->get_wp_upload_dir();
		return $wp_upload_dir . self::folder_key . self::DIRSEP;
	}
	
	public function get_wp_upload_dir() {
		$path = wp_upload_dir();
		$basedir = str_replace('\\', self::DIRSEP, $path['basedir']);
		return $basedir . self::DIRSEP;
	}
	
	/* theme options check TODO: check this function usage */
	public static function is_zip_file_exists() {
		$path = wp_upload_dir();
		$basedir = str_replace('\\', self::DIRSEP, $path['basedir']);
		$path = $basedir . self::DIRSEP . self::folder_key . self::DIRSEP . self::folder_key . '.zip';
		return file_exists($path);
	}

	protected function get_zip_file_url() {
		$path = wp_upload_dir();
		$baseurl = str_replace('\\', self::DIRSEP, $path['baseurl']);
		return $baseurl . self::DIRSEP . self::folder_key . self::DIRSEP . self::folder_key . '.zip';
	}

	protected function get_wp_tables() {
		global $wpdb;
		$tmp_tables = $wpdb->get_results('SHOW TABLES', ARRAY_N);
		$tables = array();
		if (!empty($tmp_tables) AND is_array($tmp_tables)) {
			foreach ($tmp_tables as $t) {
				$tables[] = $t[0];
			}
		}

		return $tables;
	}
	
	protected function create_upload_folder($folder = '', $clean = false) {
		$path = $this->get_wp_upload_dir();

		if (!file_exists($path)) {
			mkdir($path, 0775);
		}

		if (!$folder) {
			$folder = self::folder_key;
		}

		$path = $path . $folder . self::DIRSEP;

		if (!file_exists($path)) {
			mkdir($path, 0775);
		} else if ($clean) {
			$this->delete_dir($path); //remove previous results
			mkdir($path, 0775);
		}

		return $path;
	}
	
	protected function extract_zip($file_name = '') {
		if (!$file_name) {
			$file_name = $this->get_upload_dir() . self::folder_key . '.zip';
		}

		if(class_exists('ZipArchive')){
			$zip = new ZipArchive();
			if ($zip->open($file_name) === TRUE) {
				$zip->extractTo($this->get_upload_dir());
				$zip->close();
				$zipfile = true;
			} else {
				$zipfile = false;
			}
		}else{
			WP_Filesystem();
			$zipfile = unzip_file($file_name, $this->get_upload_dir());
		}
		return $zipfile;
	}

	protected function delete_dir($path) {
		try {
			if (is_dir($path)) {
				$it = new RecursiveDirectoryIterator($path);
				$files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
				foreach ($files as $file) {
					if ($file->isDir()) {
						@rmdir($file->getRealPath());
					} else {
						try {
							@unlink($file->getRealPath());
						} catch (Exception $e) {
							echo $e->getCode();
						}
					}
				}
				try {
					@rmdir($path);
				} catch (Exception $e) {
					echo $e->getCode();
				}
			}
		} catch (Exception $e) {
			echo $e->getCode();
		}
	}

}