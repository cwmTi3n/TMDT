<?php
/**
 * Abstract class for handling cloud storage functionality.
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

/**
 * Abstract class for handling cloud storage functionality.
 *
 * @abstract
 * @since 1.0.0
 * @since 1.1.0 Other methods related to cloud, added to organize hooks and functionality in a proper way.
 */
class Cloud {

	/**
	 * Cloud key. Ex: google_drive.
	 *
	 * @var string
	 * @since 1.1.0
	 * @since 1.1.2 This property has become optional. See `$this->setup_cloud()` method.
	 */
	protected $cloud;

	/**
	 * Cloud folder contents transient key.
	 *
	 * @var string
	 */
	protected $transient_key;

	/**
	 * Current cloud parameters that will be merged to package locations array.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected $cloud_param = array();

	/**
	 * Settings > cloud fields names attributes prefix.
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $fields_key = 'everest_backup_settings[cloud]';

	/**
	 * Arguments for rollback.
	 *
	 * @var array
	 */
	private $rollback_args = array();

	/**
	 * Init class.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->setup_cloud();
		$this->set_settings_key();
		$this->init_view_hooks();
		$this->init_logic_hooks();
	}

	/**
	 * Setup and validate required properties and keys for current cloud.
	 *
	 * @return void
	 * @since 1.1.2
	 */
	protected function setup_cloud() {

		$cloud_param = $this->set_cloud_param();

		if ( is_array( $cloud_param ) && ( count( $cloud_param ) === 1 ) ) {
			$cloud_key = key( $cloud_param ); // Automatically extract cloud key from cloud parameters.

			if ( ! is_string( $cloud_key ) ) {
				// Cloud key needs to be string. For eg: "google_drive".
				return;
			}

			$this->cloud         = $cloud_key;
			$this->transient_key = "{$cloud_key}_folder_contents";
			$this->cloud_param   = $cloud_param;
		}

	}

	/**
	 * Set key in settings array according to the current cloud key.
	 *
	 * @return void
	 * @since 1.1.2
	 */
	protected function set_settings_key() {

		$cloud_key = $this->cloud;

		if ( ! $cloud_key ) {
			return;
		}

		$settings = everest_backup_get_settings();

		if ( isset( $settings['cloud'][ $cloud_key ] ) ) {
			return;
		}

		$settings['cloud'][ $cloud_key ] = array();

		everest_backup_update_settings( $settings );

	}

	/**
	 * Initialize hooks related views.
	 *
	 * @since 1.1.0
	 * @since 1.1.2
	 * @return void
	 */
	protected function init_view_hooks() {
		add_filter( 'everest_backup_filter_package_locations', array( $this, 'merge_package_locations' ) );
		add_action( 'everest_backup_settings_cloud_content', array( $this, 'render' ), 12, 2 );
	}

	/**
	 * Initialize hooks related to process logic.
	 *
	 * @since 1.1.0
	 * @since 1.1.2
	 * @return void
	 */
	protected function init_logic_hooks() {

		$this->reset_cache();

		add_action( 'everest_backup_after_zip_done', array( $this, 'after_zip_done' ), 12, 2 );

		add_filter( 'everest_backup_history_table_data', array( $this, 'history_table_data' ), 12, 2 );
		add_action( 'everest_backup_history_after_filters', array( $this, 'after_history_filters' ) );
		add_action( 'everest_backup_override_file_remove', array( $this, 'remove' ) );

		add_filter( 'everest_backup_filter_view_renderer_args', array( $this, 'override_view_renderer_args' ), 20, 2 );
		add_action( 'everest_backup_before_restore_init', array( $this, 'before_restore_init' ) );
		add_filter( 'everest_backup_filter_rollback_args', array( $this, 'override_rollback_args' ) );
	}

	/**
	 * Merge cloud parameters to package locations.
	 *
	 * @param array $package_locations Backup package locations.
	 * @return array Backup package locations.
	 * @since 1.0.0
	 * @since 1.1.2
	 */
	public function merge_package_locations( $package_locations ) {
		if ( ! $this->cloud_param ) {
			return $package_locations;
		}

		return array_merge( $package_locations, $this->cloud_param );
	}

	/**
	 * Set current cloud parameters that will be merged to package locations array.
	 *
	 * @abstract
	 * @return void
	 */
	protected function set_cloud_param() {
		_doing_it_wrong( __METHOD__, esc_html__( 'This method is supposed to be overridden by subclasses.', 'everest-backup' ), '' );
	}

	/**
	 * Returns fields name for the settings > cloud fields name attributes.
	 * Accepts values as:
	 * * $key = 'your_key';
	 * * $key = '[your_key][sub_key]';
	 *
	 * @param string $key Fields name attribute value.
	 * @return string Processed fields name attribute value for cloulds fields.
	 * @since 1.0.0
	 */
	protected function get_name( $key ) {
		return str_replace( array( '[[', ']]' ), array( '[', ']' ), "{$this->fields_key}[$key]" );
	}

	/**
	 * Render HTML in Settings > Cloud content.
	 *
	 * @param string $cloud_key Array key of cloud ( or package location ) paramaters passed to `everest_backup_filter_package_locations` filter hook.
	 * @param array  $settings Settings data.
	 * @abstract
	 * @return void
	 * @since 1.0.0
	 */
	public function render( $cloud_key, $settings ) { // @phpcs:ignore
		_doing_it_wrong( __METHOD__, esc_html__( 'This method is supposed to be overridden by subclasses.', 'everest-backup' ), '' );
	}

	/**
	 * Trigger zip upload to cloud. Must return boolean.
	 *
	 * @param string $zip Backup package full path.
	 * @return bool
	 * @since 1.1.1
	 * @abstract
	 */
	protected function upload( $zip ) {
		return false;
	}

	/**
	 * Method that runs after backup package is created.
	 *
	 * @param string $zip Backup package full path.
	 * @param string $migration_url Migration URL.
	 * @since 1.1.0
	 * @since 1.1.1 Use `Everest_Backup\Cloud::upload` to upload the backup file using cloud. Other log related process are handled automatically.
	 * @return void
	 */
	public function after_zip_done( $zip, $migration_url ) {

		if ( ! $zip ) {
			return;
		}

		if ( everest_backup_is_saving_to() !== $this->cloud ) {
			return;
		}

		$cloud_label = $this->cloud_param[ $this->cloud ]['label'];

		Logs::set_proc_stat(
			array(
				'status'  => 'cloud',
				/* translators: %s is the cloud label name. */
				'message' => sprintf( __( "We're uploading your site's backup to %s in the background. \nYou can now close this pop up.", 'everest-backup' ), esc_html( $cloud_label ) ),
				'data'    => array(
					'zipurl'        => everest_backup_convert_file_path_to_url( $zip ),
					'migration_url' => is_string( $migration_url ) ? $migration_url : '',
				),
			)
		);

		/* translators: %s is the cloud label name. */
		Logs::info( sprintf( __( 'Uploading zip to %s.', 'everest-backup' ), esc_html( $cloud_label ) ) );

		if ( $this->upload( $zip ) ) {
			/* translators: %s is the cloud label name. */
			Logs::info( sprintf( __( 'Zip uploaded to %s.', 'everest-backup' ), esc_html( $cloud_label ) ) );
		} else {
			/* translators: %s is the cloud label name. */
			Logs::info( sprintf( __( 'Failed to upload file to %s.', 'everest-backup' ), esc_html( $cloud_label ) ) );
		}

		$transient = new Transient( $this->transient_key );
		$transient->delete();
	}


	/**
	 * Method to override history table item data.
	 *
	 * @param array  $table_data History table item data.
	 * @param string $selected_cloud Currently selected cloud.
	 * @return array
	 * @since 1.1.0
	 * @abstract
	 */
	public function history_table_data( $table_data, $selected_cloud ) {
		return $table_data;
	}

	/**
	 * Resets transient cache.
	 *
	 * @return void
	 */
	protected function reset_cache() {

		$get = everest_backup_get_submitted_data( 'get' );

		$transient = new Transient( $this->transient_key );

		if ( ! empty( $get['action'] ) && 'reset-cache' === $get['action'] ) {
			if ( ! empty( $get['cloud'] ) && $this->cloud === $get['cloud'] ) {
				$transient->delete();
			}
		}

	}

	/**
	 * By default this method prints Cache reset button if seleted cloud is not server.
	 *
	 * @param string $cloud Selected cloud.
	 * @return void
	 * @since 1.1.0
	 */
	public function after_history_filters( $cloud ) {

		if ( ! $this->cloud ) {
			return;
		}

		if ( $this->cloud !== $cloud ) {
			return;
		}

		$cache_reset_link = add_query_arg(
			array(
				'cloud'  => $this->cloud,
				'action' => 'reset-cache',
			),
			network_admin_url( '/admin.php?page=everest-backup-history' )
		);

		$transient = new Transient( $this->transient_key );
		$timeout   = $transient->get_timeout();

		?>
		<a
			href="<?php echo esc_url( $cache_reset_link ); ?>"
			class="button"
			title="
			<?php
			/* translators: %s is human_time_diff result. */
			printf( esc_attr__( 'Cache resets in: %s', 'everest-backup' ), esc_attr( human_time_diff( $timeout ) ) );
			?>
			"
		>
			&#10227; <?php esc_html_e( 'Reset Cache Now', 'everest-backup' ); ?>
		</a>
		<?php

	}

	/**
	 * Method used for removing files from cloud when user clicks remove link or action.
	 *
	 * @param array $args Results from $_GET global variable.
	 * @return void
	 * @since 1.1.0
	 */
	public function remove( $args ) {}

	/**
	 * Method to override view render arguments.
	 *
	 * @param array  $args Arguments that will be passed to the template.
	 * @param string $template Template file name without extension.
	 * @return array
	 * @since 1.1.0
	 */
	final public function override_view_renderer_args( $args, $template ) {

		if ( 'restore' !== $template ) {
			return $args;
		}

		if (
		empty( $args['action'] ) ||
		empty( $args['cloud'] ) ||
		empty( $args['file'] )
		) {
			return $args;
		}

		if ( 'rollback' !== $args['action'] ) {
			return $args;
		}

		if ( $this->cloud !== $args['cloud'] ) {
			return $args;
		}

		$args = $this->rollback_renderer_args( $args );

		return $args;
	}

	/**
	 * Alias for Everest_Backup\Cloud::override_view_renderer_args.
	 *
	 * @param array $args Arguments that will be passed to the template.
	 * @return array
	 */
	protected function rollback_renderer_args( $args ) {
		return $args;
	}

	/**
	 * Method runs before restore/rollback starts
	 *
	 * @param array $args Ajax response.
	 * @return void
	 * @since 1.1.0
	 */
	public function before_restore_init( $args ) {}

	/**
	 * Sets arguments for rollback that will be used by `Everest_Backup\Cloud::override_rollback_args`.
	 *
	 * @param array $args Arguments.
	 * @return void
	 * @throws \Exception Throws exception if required argument is missing.
	 */
	protected function set_rollback_args( $args ) {
		$required_keys = array(
			'filename',
			'package',
		);

		if ( is_array( $required_keys ) && ! empty( $required_keys ) ) {
			foreach ( $required_keys as $required_key ) {
				if ( empty( $args[ $required_key ] ) ) {

					/* translators: %s is the name of rollback args required key. */
					throw new \Exception( sprintf( __( '%s is a required argument.', 'everest-backup' ), $required_key ), 1 );
				}
			}
		}

		$this->rollback_args = $args;
	}

	/**
	 * Method to override rollback args.
	 *
	 * @param array $args Default arguments.
	 * @return array
	 * @since 1.1.0
	 */
	public function override_rollback_args( $args ) {

		if ( $this->cloud !== $args['cloud'] ) {
			return $args;
		}

		if ( empty( $args['filename'] ) ) {
			return $args;
		}

		if ( empty( $this->rollback_args['package'] ) ) {
			return $args;
		}

		if ( empty( $this->rollback_args['filename'] ) ) {
			return $args;
		}

		$args['package']  = $this->rollback_args['package'];
		$args['filename'] = $this->rollback_args['filename'];

		return $args;
	}
}
