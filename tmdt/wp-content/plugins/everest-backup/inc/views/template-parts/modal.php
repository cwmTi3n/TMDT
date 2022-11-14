<?php
/**
 * Progress modal html.
 *
 * @package everest-backup
 */

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div id="everest-backup-modal-wrapper">
	<div class="modal" id="modal">

		<?php
		if ( ! empty( $args['is_dismissible'] ) ) {
			?>
			<div id="btn-modal-dismiss-wrapper">
				<button type="button" class="notice-dismiss" id="btn-modal-dismiss" onclick="document.body.classList.remove('ebwp-is-active');"></button>
			</div>
			<?php
		}
		?>

		<div class="loader-wrapper">
			<div class="modal-header">
				<div class="loader-box">
					<img src="<?php echo esc_url( EVEREST_BACKUP_URL . 'assets/images/ebwp-loading.gif' ); ?>">
				</div>
			</div>

			<?php if ( ! empty( $args['on_process_msg'] ) ) { ?>
				<div class="modal-body">
					<?php
					if ( is_callable( $args['on_process_msg'] ) ) {
						call_user_func( $args['on_process_msg'] );
					} elseif ( is_string( $args['on_process_msg'] ) ) {
						echo wp_kses_post( $args['on_process_msg'] );
					}
					?>
				</div>
				<style>
					#process-info .progress-bar {
						transition:all 200ms linear 0s;
					}
				</style>
			<?php } ?>
		</div>

		<div class="after-process-complete hidden">
			<div class="after-process-success hidden">
				<?php
				if ( ! empty( $args['on_success'] ) ) {
					if ( ! empty( $args['on_success']['title'] ) ) {
						?>
							<div class="modal-header">
								<h2 class="title on-process-success">
									<span class="dashicons dashicons-yes-alt"></span> <?php echo wp_kses_post( $args['on_success']['title'] ); ?>
								</h2>
							</div>
							<?php
					}

					if ( ! empty( $args['on_success']['content'] ) ) {
						?>
							<div class="modal-body">
							<?php
							if ( is_callable( $args['on_success']['content'] ) ) {
								call_user_func( $args['on_success']['content'] );
							} elseif ( is_string( $args['on_success']['content'] ) ) {
								echo wp_kses_post( $args['on_success']['content'] );
							}
							?>
							</div>
							<?php
					}
				}
				?>
			</div>

			<div class="after-process-error hidden">
				<?php
				if ( ! empty( $args['on_error'] ) ) {
					if ( ! empty( $args['on_error']['title'] ) ) {
						?>
							<div class="modal-header">
								<h2 class="title on-process-error">
									<span class="dashicons dashicons-dismiss"></span> <?php echo wp_kses_post( $args['on_error']['title'] ); ?>
								</h2>
							</div>
							<?php
					}

					if ( ! empty( $args['on_error']['content'] ) ) {
						?>
							<div class="modal-body">
							<?php
							if ( is_callable( $args['on_error']['content'] ) ) {
								call_user_func( $args['on_error']['content'] );
							} elseif ( is_string( $args['on_error']['content'] ) ) {
								echo wp_kses_post( $args['on_error']['content'] );
							}
							?>
							</div>
							<?php
					}
				}
				?>
			</div>

		</div>

	</div>
	<div id="overlay"></div>
</div>
