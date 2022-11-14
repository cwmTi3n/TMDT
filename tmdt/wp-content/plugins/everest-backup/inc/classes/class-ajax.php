<?php
/**
 * Handles ajax requests.
 *
 * @package everest-backup
 */

namespace Everest_Backup;

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Everest_Backup\Modules\Cloner;
use Everest_Backup\Modules\Restore_Config;
use Everest_Backup\Modules\Restore_Content;
use Everest_Backup\Modules\Restore_Database;
use Everest_Backup\Modules\Restore_Multisite;
use Everest_Backup\Modules\Restore_Plugins;
use Everest_Backup\Modules\Restore_Themes;
use Everest_Backup\Modules\Restore_Uploads;
use Everest_Backup\Modules\Restore_Users;

/**
 * Handles ajax requests.
 *
 * @since 1.0.0
 */
class Ajax {


	/**
	 * Init ajax.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function __construct() {

		add_action( 'wp_ajax_everest_backup_addon', array( $this, 'install_addon' ) );

		add_action( 'wp_ajax_' . EVEREST_BACKUP_EXPORT_ACTION, array( $this, 'export_files' ) );
		add_action( 'wp_ajax_' . EVEREST_BACKUP_UPLOAD_PACKAGE_ACTION, array( $this, 'upload_package' ) );
		add_action( 'wp_ajax_' . EVEREST_BACKUP_REMOVE_UPLOADED_PACKAGE_ACTION, array( $this, 'remove_uploaded_package' ) );
		add_action( 'wp_ajax_' . EVEREST_BACKUP_IMPORT_ACTION, array( $this, 'import_files' ) );

		add_action( 'everest_backup_before_restore_init', array( $this, 'clone_init' ) );

		add_action( 'wp_ajax_nopriv_everest_process_status', array( $this, 'process_status' ) );
		add_action( 'wp_ajax_everest_process_status', array( $this, 'process_status' ) );

		add_action( 'wp_ajax_nopriv_everest_backup_process_status_unlink', array( $this, 'process_status_unlink' ) );
		add_action( 'wp_ajax_everest_backup_process_status_unlink', array( $this, 'process_status_unlink' ) );
	}

	/**
	 * Send process status.
	 *
	 * @return void
	 */
	public function process_status() {
		wp_send_json( Logs::get_proc_stat() );
	}

	/**
	 * Unlink process status file after process complete.
	 *
	 * @return void
	 */
	public function process_status_unlink() {
		if ( file_exists( EVEREST_BACKUP_PROC_STAT_PATH ) ) {
			unlink( EVEREST_BACKUP_PROC_STAT_PATH );
		}
		die;
	}

	/**
	 * Set/Get temp session data.
	 *
	 * If you want to clear its data then call this method as: `$this->temp();`.
	 *
	 * @param array|object $data Data to save temporarily.
	 * @param bool         $get If passed true, then it will return the data otherwise it will save it.
	 * @return mixed
	 */
	protected function temp( $data = array(), $get = false ) {
		if ( ! session_id() ) {
			session_start();
		}

		$ebwp_ajax_temp = isset( $_SESSION['ebwp_ajax_temp'] ) ? everest_backup_sanitize_array( $_SESSION['ebwp_ajax_temp'] ) : null;

		if ( $get ) {
			return $ebwp_ajax_temp;
		}

		if ( is_array( $ebwp_ajax_temp ) ) {
			$data = array_merge( $ebwp_ajax_temp, $data );
		}

		$_SESSION['ebwp_ajax_temp'] = $data;

		if ( ! $data ) {
			if ( isset( $_SESSION['ebwp_ajax_temp'] ) ) {
				unset( $_SESSION['ebwp_ajax_temp'] );
			}

			return;
		}

	}

	/**
	 * Install and activate free addon from the addon page.
	 *
	 * @return void
	 */
	public function install_addon() {

		$plugins_dir = WP_PLUGIN_DIR;
		$response    = everest_backup_get_ajax_response( 'everest_backup_addon' );

		wp_cache_flush();

		$addon_category = ! empty( $response['addon_category'] ) ? $response['addon_category'] : '';
		$addon_slug     = ! empty( $response['addon_slug'] ) ? $response['addon_slug'] : '';

		$addon_info = everest_backup_addon_info( $addon_category, $addon_slug );

		$package = $addon_info['package'];

		$plugin_folder = $plugins_dir . DIRECTORY_SEPARATOR . $addon_slug;
		$plugin_zip    = $plugin_folder . '.zip';
		$plugin        = $addon_slug . '/' . $addon_slug . '.php';

		$data = wp_remote_get(
			$package,
			array(
				'sslverify' => false,
			)
		);

		$content = wp_remote_retrieve_body( $data );

		if ( ! $content ) {
			wp_send_json_error();
		}

		if ( file_exists( $plugin_zip ) ) {
			unlink( $plugin_zip );
		}

		Filesystem::init()->writefile( $plugin_zip, $content );

		if ( ! file_exists( $plugin_zip ) ) {
			wp_send_json_error();
		}

		if ( is_dir( $plugin_folder ) ) {
			/**
			 * Plugin directory already exists, then delete the existing plugin directory first.
			 */
			Filesystem::init()->delete( $plugin_folder, true );
		}

		unzip_file( $plugin_zip, $plugins_dir );

		wp_cache_flush();
		everest_backup_activate_ebwp_addon( $plugin );

		unlink( $plugin_zip );

		wp_send_json_success();
	}




	/**
	 * ====================================
	 *
	 * Backup related methods.
	 *
	 * ====================================
	 */


	/**
	 * Initialize our backup related work.
	 *
	 * @since 1.0.0
	 */
	public function export_files() {
		everest_backup_compress_init();
	}




	/**
	 * ====================================
	 *
	 * Restore/Rollback/Clone related methods.
	 *
	 * ====================================
	 */




	/**
	 * Init cloning process.
	 *
	 * @param array $response Ajax response.
	 * @return void
	 */
	public function clone_init( $response ) {

		if ( ! everest_backup_doing_clone() ) {
			return;
		}

		if ( empty( $response['download_url'] ) ) {
			$message = __( 'Clone failed because package download url is missing.', 'everest-backup' );
			Logs::error( $message );
			everest_backup_send_error( $message );
		}

		Logs::info( __( 'Downloading the file from the host site.', 'everest-backup' ) );

		$everest_backup_cloner = new Cloner();
		$file                  = $everest_backup_cloner->handle_package_clone( $response );

		if ( ! $file ) {
			$message = __( 'Failed to download the file from the host site.', 'everest-backup' );
			Logs::error( $message );
			everest_backup_send_error( $message );
		}

		Logs::info( __( 'File downloaded successfully.', 'everest-backup' ) );

	}


	/**
	 * Check phpversion during restore.
	 *
	 * @return void
	 */
	public function restore_check_phpversion() {

		$current_php_version = PHP_VERSION;

		wp_cache_flush();

		everest_backup_setup_environment();

		$temp = $this->temp( null, true );

		$response = array(
			'status'      => 'in-process',
			'next_action' => is_multisite() ? 'everest_backup_restore_multisite' : 'everest_backup_restore_database',
			'message'     => is_multisite() ? __( 'Configuring multisite restore...', 'everest-backup' ) : __( 'Importing database...', 'everest-backup' ),
		);

		/**
		 * \Everest_Backup\Extract class object.
		 *
		 * @var \Everest_Backup\Extract
		 */
		$extract = $temp['extract'];

		$config_data     = $extract->get_temp_data( 'config_data' );
		$zip_php_version = ! empty( $config_data['PHP']['Version'] ) ? $config_data['PHP']['Version'] : '';

		$is_comparable = ( ( $current_php_version !== $zip_php_version ) && ( version_compare( $current_php_version, $zip_php_version, 'gt' ) ) );

		$is_minor_update = $zip_php_version && $is_comparable ? everest_backup_version_compare( $current_php_version, $zip_php_version, 'gt', true ) : true;

		if ( ! $is_minor_update ) {
			$response['status'] = 'on-hold';
			/* translators: Here, %1$s is package php version and %2$ is current site php version. */
			$response['message']     = sprintf( __( "Attention Needed!!! \r\n\r\nYou are trying to restore the package from PHP Version %1\$s to PHP Version %2\$s. An incompatible plugin or theme might cause the issue. \r\n\r\nDo you want to continue?", 'everest-backup' ), $zip_php_version, PHP_VERSION );
			$response['decline_msg'] = __( 'Process aborted. Cleaning extracted files.', 'everest-backup' );
		}

		wp_send_json( $response );

	}


	/**
	 * Pre restore method, works for uploading package.
	 *
	 * @return void
	 */
	public function upload_package() {

		if ( ! current_user_can( 'upload_files' ) ) {
			$message = __( 'Current user does not have permission to upload files.', 'everest-backup' );
			Logs::error( $message );
			everest_backup_send_error( $message );
		}

		wp_cache_flush();

		everest_backup_setup_environment();

		everest_backup_get_ajax_response( EVEREST_BACKUP_UPLOAD_PACKAGE_ACTION );

		$package = new File_Uploader(
			array(
				'form'      => 'file',
				'urlholder' => 'ebwp_package',
			)
		);

		wp_send_json( $package );

	}

	/**
	 * Delete uploaded package.
	 *
	 * @return void
	 */
	public function remove_uploaded_package() {

		if ( ! current_user_can( 'upload_files' ) ) {
			$message = __( 'Current user does not have permission to upload files.', 'everest-backup' );
			Logs::error( $message );
			everest_backup_send_error( $message );
		}

		wp_cache_flush();

		everest_backup_setup_environment();

		$response = everest_backup_get_ajax_response( EVEREST_BACKUP_REMOVE_UPLOADED_PACKAGE_ACTION );

		$package = new File_Uploader( $response );

		$package->cleanup();

		wp_send_json( $package );

	}

	/**
	 * Initialize import.
	 *
	 * @return void
	 */
	public function import_files() {

		if ( ! everest_backup_doing_clone() ) {
			if ( everest_backup_doing_rollback() ) {
				Logs::init( 'rollback' );
			} else {
				Logs::init( 'restore' );
			}
		} else {
			Logs::init( 'clone' );
		}

		if ( ! current_user_can( 'upload_files' ) ) {
			$message = __( 'Current user does not have permission to upload files.', 'everest-backup' );
			Logs::error( $message );
			everest_backup_send_error( $message );
		}

		wp_cache_flush();

		everest_backup_setup_environment();

		$response = everest_backup_get_ajax_response( EVEREST_BACKUP_IMPORT_ACTION );

		$timer_start = time();

		/**
		 * Action just before restore starts.
		 * Useful for the cloud modules for downloading files and set process status.
		 *
		 * @param array $response Ajax response.
		 *
		 * @since 1.0.7
		 */
		do_action( 'everest_backup_before_restore_init', $response );

		/* translators: %s is the restore start time. */
		Logs::info( sprintf( __( 'Restore started at: %s', 'everest-backup' ), wp_date( 'h:i:s A', $timer_start ) ) );

		Logs::set_proc_stat(
			array(
				'status'   => 'in-process',
				'progress' => 5,
				'message'  => __( 'Extracting package', 'everest-backup' ),
			)
		);

		$extract = new Extract( $response ); // @phpcs:ignore

		Restore_Config::init( $extract );
		Restore_Multisite::init( $extract );
		Restore_Database::init( $extract );
		Restore_Users::init( $extract );
		Restore_Uploads::init( $extract );
		Restore_Themes::init( $extract );
		Restore_Plugins::init( $extract );
		Restore_Content::init( $extract );

		Logs::set_proc_stat(
			array(
				'status'   => 'in-process',
				'progress' => 92,
				'message'  => __( 'Cleaning remaining extracted files', 'everest-backup' ),
			)
		);

		$extract->clean_storage_dir();

		/* translators: %s is the restore completed time. */
		Logs::info( sprintf( __( 'Restore completed at: %s', 'everest-backup' ), wp_date( 'h:i:s A' ) ) );

		/* translators: %s is the total restore time. */
		Logs::info( sprintf( __( 'Total time: %s', 'everest-backup' ), human_time_diff( $timer_start ) ) );

		Logs::done( __( 'Restore completed.', 'everest-backup' ) );

		do_action( 'everest_backup_after_restore_done', $response );

		everest_backup_send_success();
	}
}

new Ajax();
