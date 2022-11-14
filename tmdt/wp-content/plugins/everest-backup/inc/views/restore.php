<?php
/**
 * Template file for the import/restore page..
 *
 * @package everest-backup
 */

use Everest_Backup\Modules\Restore_Tab;

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

everest_backup_render_view(
	'template-parts/modal',
	array(
		'on_success'     => array(
			'title'   => everest_backup_doing_rollback() ? __( 'Rollback Completed', 'everest-backup' ) : __( 'Restoration Completed', 'everest-backup' ),
			'content' => function () {
				?>
				<p><?php esc_html_e( 'Please do not forget to save the permalink from sites settings.', 'everest-backup' ); ?></p>
				<a href="<?php echo esc_url( network_admin_url() ); ?>" class="button button-primary"><?php esc_html_e( 'Go To Dashboard', 'everest-backup' ); ?></a>
				<?php
			},
		),
		'on_error'       => array(
			'title'   => everest_backup_doing_rollback() ? __( 'Rollback Aborted', 'everest-backup' ) : __( 'Restoration Aborted', 'everest-backup' ),
			'content' => function () {
				?>
				<a href="<?php echo esc_url( network_admin_url() ); ?>" class="button button-primary"><?php esc_html_e( 'Go To Dashboard', 'everest-backup' ); ?></a>
				<?php
			},
		),
		'on_process_msg' => function () use ( $args ) {
			?>
			<div id="import-on-process">

				<div id="process-info">
					<strong>
						<p class="process-message">
							<?php
							if ( everest_backup_doing_rollback() ) {
								$cloud = ! empty( $args['cloud'] ) ? $args['cloud'] : 'server';
								if ( 'server' !== $cloud ) {
									$package_locations = everest_backup_package_locations();

									/* translators: %s is cloud storage label. Ex: Google Drive */
									printf( esc_html__( 'Downloading file from %s', 'everest-backup' ), esc_html( $package_locations[ $cloud ]['label'] ) );
								} else {
									esc_html_e( 'Initializing rollback...', 'everest-backup' );
								}
							} else {
								esc_html_e( 'Uploading package...', 'everest-backup' );
							}
							?>
						</p>
					</strong>

					<div class="progress progress-striped active">
						<div role="progressbar" style="width: 0%;" class="progress-bar progress-bar-success text-left">
							<span></span>
						</div>
					</div>
				</div>

				<strong><?php esc_html_e( 'Restoration/Rollback is in progress. Please do not close this window or tab.', 'everest-backup' ); ?></strong>

				<div class="after-file-uploaded hidden">
					<button id="cancel" class="button button-danger button-hero">
						<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 15 15">
							<path
							id="Icon_metro-cross"
							data-name="Icon metro-cross"
							d="M17.434,13.979h0l-4.55-4.551,4.55-4.551h0a.47.47,0,0,0,0-.663l-2.15-2.15a.47.47,0,0,0-.663,0h0L10.071,6.616,5.52,2.065h0a.47.47,0,0,0-.663,0l-2.15,2.15a.47.47,0,0,0,0,.663h0L7.258,9.428,2.708,13.979h0a.47.47,0,0,0,0,.663l2.15,2.15a.47.47,0,0,0,.663,0h0l4.551-4.551,4.551,4.551h0a.47.47,0,0,0,.663,0l2.15-2.15a.47.47,0,0,0,0-.663Z"
							transform="translate(-2.571 -1.928)"
							fill="#D14E39"/>
						</svg>
						<span><?php esc_html_e( 'Cancel' ); ?></span>
					</button>
					<button id="restore" class="button button-success button-hero">
						<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 -5 40 35">
							<path id="Icon_metro-checkmark" 
							data-name="Icon metro-checkmark" 
							d="M35.477,5.784,17.2,24.065,8.664,15.534,2.571,21.628,17.2,36.253,41.571,11.878Z" 
							transform="translate(-2.571 -5.784)"
							fill="#5bb914"></path>
						</svg>
						<span><?php esc_html_e( 'Restore' ); ?></span>
					</button>
				</div>

			</div>
			<?php
		},
	)
);

$everest_backup_restore_tab = new Restore_Tab();

?>
<div class="wrap">
	<hr class="wp-header-end">

	<?php
	everest_backup_render_view( 'template-parts/header' );
	?>
	<main class="everest-backup-wrapper">

		<div id="everest-backup-container">

			<?php everest_backup_render_view( 'template-parts/message-box' ); ?>

			<div id="restore-wrapper">

				<?php
				if ( empty( $args['proc_lock'] ) ) {

					if ( ! everest_backup_doing_rollback() ) {
						$everest_backup_restore_tab->display();
					} else {

						$everest_backup_max_upload_size = everest_backup_max_upload_size();

						?>

						<div class="rollback-container card">
							<p class="notice notice-info hidden"><strong><?php esc_html_e( 'Please wait while we are rolling back your website to the previous version.', 'everest-backup' ); ?></strong></p>
							<span class="spinner"></span>

							<h2><?php esc_html_e( 'Package Information', 'everest-backup' ); ?></h2>

							<?php if ( ! empty( $args['filename'] ) ) { ?>
								<ul>
									<li><?php printf( '<strong>%1$s :</strong> %2$s', esc_html__( 'Filename', 'everest-backup' ), esc_html( $args['filename'] ) ); ?></li>
									<li><?php printf( '<strong>%1$s :</strong> %2$s', esc_html__( 'Created On', 'everest-backup' ), esc_html( wp_date( 'h:i:s A [F j, Y]', $args['time'] ) ) ); ?></li>
									<li><?php printf( '<strong>%1$s :</strong> %2$s', esc_html__( 'Size', 'everest-backup' ), esc_html( everest_backup_format_size( $args['size'] ) ) ); ?></li>
								</ul>

								<?php
								if ( $everest_backup_max_upload_size && ( $args['size'] >= $everest_backup_max_upload_size ) ) {
									?>
									<p class="notice notice-error"><?php printf( '<strong>%1$s :</strong> %2$s', esc_html__( 'Maximum Upload Size', 'everest-backup' ), esc_html( everest_backup_format_size( $everest_backup_max_upload_size ) ) ); ?></p>
									<p class="notice notice-error"><strong><?php esc_html_e( 'Rollback denied because package size is larger than allowed maximum upload size.', 'everest-backup' ); ?></strong> <a href="<?php echo esc_url( network_admin_url( 'admin.php?page=everest-backup-addons&cat=Upload+Limit' ) ); ?>"><?php esc_html_e( 'View Available Addons', 'everest-backup' ); ?></a></p>
									<?php
								} else {
									if ( empty( $args['rollback'] ) ) {
										?>
										<div class="confirmation-wrapper">
											<p class="notice notice-warning"><strong><?php esc_html_e( 'Are you sure? It cannot be undone after rollback is started.', 'everest-backup' ); ?></strong></p>
											<form method="post" id="rollback-form">
												<a href="<?php echo esc_url( network_admin_url( '/admin.php?page=everest-backup-import' ) ); ?>" class="button-primary"><?php esc_html_e( 'Cancel', 'everest-backup' ); ?></a>
												<input type="hidden" name="file" value="<?php echo esc_attr( $args['file'] ); ?>">
												<input type="hidden" name="filename" value="<?php echo esc_attr( $args['filename'] ); ?>">
												<input type="hidden" name="cloud" value="<?php echo esc_attr( $args['cloud'] ); ?>">
												<input type="hidden" name="page" value="<?php echo esc_attr( $args['page'] ); ?>">
												<button class="button-secondary" id="btn-rollback" type="submit"><?php esc_html_e( 'Rollback', 'everest-backup' ); ?></button>
											</form>
										</div>
										<?php
									}
								}
								?>

							<?php } else { ?>
								<p class="notice notice-error"><strong><?php esc_html_e( 'Oops! The selected package is either broken or does not exist.', 'everest-backup' ); ?></strong></p>
								<a href="<?php echo esc_url( network_admin_url( '/admin.php?page=everest-backup-history' ) ); ?>"><span>&larr;</span> <?php esc_html_e( 'Go to history', 'everest-backup' ); ?></a>
							<?php } ?>
						</div>

						<?php
					}
				} else {
					everest_backup_render_view( 'template-parts/proc-lock-info', $args['proc_lock'] );
				}
				?>

			</div>
		</div>

		<?php
		everest_backup_render_view(
			'template-parts/sidebar',
			array(
				'current_tab' => $everest_backup_restore_tab->get_current(),
			)
		);
		?>
	</main>

</div>
<?php
