<?php
/**
 * HTML content for the settings general tab.
 *
 * @package everest-backup
 */

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$everest_backup_settings = ! empty( $args['settings'] ) ? $args['settings'] : array();

$tags_display_type = ! empty( $everest_backup_settings['general']['tags_display_type'] ) ? $everest_backup_settings['general']['tags_display_type'] : 'included';

?>
<form method="post">

	<p class="description"><?php esc_html_e( 'General configuration for your Everest Backup plugin.', 'everest-backup' ); ?></p>

	<table class="form-table" id="general">
		<tbody>

			<?php

			/**
			 * Action hook after tbody opening tag.
			 *
			 * @since 1.1.2
			 */
			do_action( 'everest_backup_settings_general_after_tbody_open', $args );
			?>

			<tr>
				<th scope="row">
					<?php esc_html_e( 'Admin Email', 'everest-backup' ); ?>
					<?php everest_backup_tooltip( __( 'Email address that will be used by Everest Backup plugin. WordPress admin email will be used as default.', 'everest-backup' ) ); ?>
				</th>
				<td>
					<label>
						<input type="email" value="<?php echo esc_attr( everest_backup_get_admin_email() ); ?>" name="everest_backup_settings[general][admin_email]">
						<a href="<?php echo esc_url( add_query_arg( 'email-test', 'sending', network_admin_url( '/admin.php?page=everest-backup-settings' ) ) ); ?>" class="button button-primary"><?php esc_html_e( 'Send Test Email', 'everest-backup' ); ?></a>
					</label>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<?php esc_html_e( 'Tags Display Type', 'everest-backup' ); ?>
					<?php everest_backup_tooltip( __( 'Display "Included" modules or "Excluded" modules as tags in backup files listings. Ex: In history page.', 'everest-backup' ) ); ?>
				</th>
				<td>
					<label>
						<select name="everest_backup_settings[general][tags_display_type]">
							<option <?php selected( $tags_display_type, 'included' ); ?> value="included"><?php esc_html_e( 'Included', 'everest-backup' ); ?></option>
							<option <?php selected( $tags_display_type, 'excluded' ); ?> value="excluded"><?php esc_html_e( 'Excluded', 'everest-backup' ); ?></option>
						</select>
					</label>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<?php esc_html_e( 'Auto Remove', 'everest-backup' ); ?>
					<?php everest_backup_tooltip( __( 'Auto remove backup files after defined number of days, set 0 to keep all the files.', 'everest-backup' ) ); ?>
				</th>
				<td>
					<label>
						<input type="number" value="<?php echo ! empty( $everest_backup_settings['general']['auto_remove_older_than'] ) ? absint( $everest_backup_settings['general']['auto_remove_older_than'] ) : 0; ?>" min="0" name="everest_backup_settings[general][auto_remove_older_than]">
						<span><?php esc_html_e( 'days.', 'everest-backup' ); ?></span>
					</label>
				</td>
			</tr>

			<tr>
				<th scope="row">
					<?php esc_html_e( 'Exclude Files', 'everest-backup' ); ?>
					<?php everest_backup_tooltip( __( 'The file extension must be separated by single comma, without the dot as: zip, lock.', 'everest-backup' ) ); ?>
				</th>
				<td>
					<label>
						<input type="text" value="<?php echo ! empty( $everest_backup_settings['general']['exclude_files_by_extension'] ) ? esc_attr( $everest_backup_settings['general']['exclude_files_by_extension'] ) : ''; ?>" placeholder="Ex: zip, lock" name="everest_backup_settings[general][exclude_files_by_extension]">
					</label>
				</td>
			</tr>

			<?php

			/**
			 * Action hook before tbody closing tag.
			 *
			 * @since 1.1.2
			 */
			do_action( 'everest_backup_settings_general_before_tbody_close', $args );
			?>

		</tbody>
	</table>
	<?php
	everest_backup_nonce_field( EVEREST_BACKUP_SETTINGS_KEY . '_nonce' );
	submit_button( __( 'Save Settings', 'everest-backup' ) );
	?>
</form>
