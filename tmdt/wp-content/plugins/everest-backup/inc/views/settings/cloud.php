<?php
/**
 * HTML content for the settings cloud tab.
 *
 * @package everest-backup
 */

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$everest_backup_settings          = ! empty( $args['settings'] ) ? $args['settings'] : array();
$everest_backup_package_locations = ! empty( $args['package_locations'] ) ? $args['package_locations'] : array();

?>
<form method="post">

	<?php if ( isset( $everest_backup_package_locations['server'] ) && count( $everest_backup_package_locations ) === 1 ) { ?>
		<a href="<?php echo esc_url( network_admin_url( '/admin.php?page=everest-backup-addons&cat=Cloud' ) ); ?>"><?php esc_html_e( 'Install our addons to store your backup files on the cloud.', 'everest-backup' ); ?></a>
	<?php } else { ?>
		<p class="description"><?php esc_html_e( 'Configuration for your cloud storage.', 'everest-backup' ); ?></p>
	<?php } ?>

	<table class="form-table" id="cloud">
		<tbody>
			<?php
			if ( is_array( $everest_backup_package_locations ) && ! empty( $everest_backup_package_locations ) ) {
				foreach ( $everest_backup_package_locations as $everest_backup_package_location_key => $everest_backup_package_location ) {
					echo wp_kses_post( "<!-- Start [Key:{$everest_backup_package_location_key}]: {$everest_backup_package_location['label']} -->" );

					do_action(
						'everest_backup_settings_cloud_content',
						$everest_backup_package_location_key,
						$everest_backup_settings
					);

					echo wp_kses_post( "<!-- End [Key:{$everest_backup_package_location_key}]: {$everest_backup_package_location['label']} -->" );
				}
			}
			?>
		</tbody>
	</table>

	<?php
	everest_backup_nonce_field( EVEREST_BACKUP_SETTINGS_KEY . '_nonce' );
	submit_button( __( 'Save Settings', 'everest-backup' ) );
	?>
</form>
