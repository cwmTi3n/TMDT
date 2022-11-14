<?php
/**
 * Class to manage cron hook actions.
 *
 * @package everest-backup
 */

namespace Everest_Backup\Modules;

use Everest_Backup\Backup_Directory;
use Everest_Backup\Logs;
use Everest_Backup\Temp_Directory;

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class to manage cron hook actions.
 *
 * @since 1.0.0
 */
class Cron_Actions {

	/**
	 * Init class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		add_action( 'wp_scheduled_delete', array( $this, 'cron_delete_files' ) ); // Triggers once daily.
		$this->init_schedule_backup();
	}

	/**
	 * Handle backup files deletion related actions.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function cron_delete_files() {
		Temp_Directory::init()->clean_temp_dir();
		$this->delete_misc_files();
		$this->auto_remove();
	}

	/**
	 * Delete non backup directory related files.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	protected function delete_misc_files() {

		/**
		 * All misc files older than 1 day.
		 */
		$files = Backup_Directory::init()->get_misc_files( 1 );

		if ( is_array( $files ) && ! empty( $files ) ) {
			foreach ( $files as $file ) {

				if ( ! is_file( $file ) ) {
					continue;
				}

				unlink( $file );
			}
		}
	}

	/**
	 * Auto remove archive files from the server.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	protected function auto_remove() {
		$general = everest_backup_get_settings( 'general' );

		$auto_remove = ! empty( $general['auto_remove_older_than'] ) && $general['auto_remove_older_than'] > 0 ? absint( $general['auto_remove_older_than'] ) : 0;

		if ( ! $auto_remove ) {
			return;
		}

		$backups = Backup_Directory::init()->get_backups_older_than( $auto_remove );

		if ( is_array( $backups ) && ! empty( $backups ) ) {
			foreach ( $backups as $backup ) {
				if ( empty( $backup['path'] ) ) {
					continue;
				}

				if ( ! is_file( $backup['path'] ) ) {
					continue;
				}

				unlink( $backup['path'] );
			}
		}

	}

	/**
	 * Init schedule backup cron.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	protected function init_schedule_backup() {
		$schedule_backup = everest_backup_get_settings( 'schedule_backup' );

		if ( empty( $schedule_backup['enable'] ) ) {
			return;
		}

		if ( empty( $schedule_backup['cron_cycle'] ) ) {
			return;
		}

		$cron_cycle = $schedule_backup['cron_cycle'];

		$hook = "{$cron_cycle}_hook";

		add_action( $hook, array( $this, 'schedule_backup' ) );
		add_filter( 'everest_backup_filter_backup_modules_params', array( $this, 'filter_backup_modules_params' ) );
	}

	/**
	 * Do schedule backup.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public function schedule_backup() {

		if ( wp_doing_ajax() ) {
			return;
		}

		Logs::init( 'schedule_backup' );

		$cron_cycles = everest_backup_cron_cycles();

		$settings = everest_backup_get_settings();

		$cron_cycle_key = $settings['schedule_backup']['cron_cycle'];

		$cron_cycle = ! empty( $cron_cycles[ $cron_cycle_key ]['display'] ) ? $cron_cycles[ $cron_cycle_key ]['display'] : '';

		/* translators: Here, %s is the schedule type or cron cycle. */
		Logs::info( sprintf( __( 'Schedule type: %s', 'everest-backup' ), $cron_cycle ) );

		do_action( 'wp_ajax_' . EVEREST_BACKUP_EXPORT_ACTION ); // @phpcs:ignore

	}

	/**
	 * Filter the parameters to manipulate backup modules.
	 *
	 * @param array $params Parameters to manipulate backup modules.
	 * @return array
	 * @since 1.0.0
	 */
	public function filter_backup_modules_params( $params ) {

		if ( wp_doing_ajax() ) {
			return $params;
		}

		$new_params = array();

		$backup_excludes = array_keys( everest_backup_get_backup_excludes() );
		$schedule_backup = everest_backup_get_settings( 'schedule_backup' );

		if ( is_array( $backup_excludes ) && ! empty( $backup_excludes ) ) {
			foreach ( $backup_excludes as $backup_exclude ) {
				if ( ! empty( $schedule_backup[ $backup_exclude ] ) ) {
					$new_params[ $backup_exclude ] = 1;
				}
			}
		}

		$new_params['delete_from_server'] = isset( $schedule_backup['delete_from_server'] ) && $schedule_backup['delete_from_server'];
		$new_params['custom_name_tag']    = isset( $schedule_backup['custom_name_tag'] ) ? $schedule_backup['custom_name_tag'] : '';
		$new_params['save_to']            = isset( $schedule_backup['save_to'] ) && $schedule_backup['save_to'] ? $schedule_backup['save_to'] : 'server';

		return $new_params;
	}
}

new Cron_Actions();
