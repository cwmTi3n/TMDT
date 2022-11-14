<?php
/**
 * Class for handling database import.
 *
 * @package everest-backup
 */

namespace Everest_Backup\Modules;

use Everest_Backup\Database;
use Everest_Backup\Logs;

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for handling database import.
 *
 * @since 1.0.0
 * @since 1.0.6 - Rewrite database class for import.
 */
class Import_Database extends Database {

	/**
	 * List of prefixed tables during export.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected $exported_tables;

	/**
	 * Find and replace value from the sql string.
	 * Array key as the string to find and array value as string to replace with.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected $find_replace;

	/**
	 * Init class.
	 *
	 * @param string $filename Path to the sql dump file.
	 * @param array  $exported_tables List of prefixed tables during export.
	 * @param array  $find_replace Find and replace value from the sql string.
	 *                             Array key as the string to find and array value as string to replace with.
	 * @since 1.0.0
	 */
	public function __construct( $filename, $exported_tables, $find_replace = array() ) {

		global $wpdb;

		$this->filename        = $filename;
		$this->exported_tables = $exported_tables;
		$this->find_replace    = $find_replace;

		$old_db_prefix = get_file_data( $filename, array( '-- Prefix' ) );

		$this->old_db_prefix = ! empty( $old_db_prefix[0] ) ? $old_db_prefix[0] : '';
		$this->new_db_prefix = $wpdb->prefix;

		parent::__construct( $wpdb );
	}

	/**
	 * Import database.
	 *
	 * @return bool $success Returns true on success and false on query error.
	 * @since 1.0.0
	 */
	public function import() {

		$imported = false;

		$filename = $this->filename;

		$handle = fopen( $filename, 'r' ); // @phpcs:ignore

		$queries = array();

		if ( $handle ) {

			$count        = 1;
			$total_tables = is_array( $this->exported_tables ) ? count( $this->exported_tables ) : 0;

			while ( ! feof( $handle ) ) {
				$sql_line = fgets( $handle );

				if ( ! is_string( $sql_line ) ) {
					continue;
				}

				$sql_line = trim( $sql_line );

				if ( ! $this->is_valid( $sql_line ) ) {
					continue;
				}

				/**
				 * Run queries other than INSERT.
				 */
				if ( false === strpos( $sql_line, 'INSERT INTO `' ) ) {

					if ( ! $this->is_query_end( $sql_line ) ) {
						$queries[] = $sql_line;
						continue;
					} else {
						$queries[] = $sql_line;
					}

					$query = implode( '', $queries );
					$query = $this->replace_table_options( $query );
					$query = $this->replace_table_constraints( $query );
					$query = $this->rename_table_names( $query, $this->exported_tables );

					$imported = $this->query( $query );

					if ( false !== strpos( $query, 'DROP TABLE IF EXISTS `' ) ) {

						$progress = ( ( $count / $total_tables ) * 100 );

						Logs::set_proc_stat(
							array(
								'status'   => 'in-process',
								'progress' => 30,
								/* translators: %d is database restore progress percent. */
								'message'  => sprintf( __( 'Restoring database ( %d%% )', 'everest-backup' ), $progress ),
							),
							0
						);

						$count++;

					}

					$queries = array();
					$query   = '';
				} else {

					$tmp = $this->rename_table_names( $sql_line, $this->exported_tables );
					$tmp = $this->replace_users_login_related_metas( $tmp );

					$imported = $this->query( $this->fix_str_length( strtr( $tmp, $this->find_replace ) ) );

					$queries = array();
					$tmp     = '';
					$query   = '';
				}

				$sql_line = '';

			}

			fclose( $handle ); // @phpcs:ignore
		}

		return $imported;

	}

}
