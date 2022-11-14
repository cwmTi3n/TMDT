<?php
/**
 * Template part for process lock information for other users.
 *
 * @package everest-backup
 */

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $args['type'] ) ) {
	return;
}

$everest_backup_class = ! empty( $args['class'] ) ? $args['class'] : '';

?>
<style>
	.notice.ebwp-center.ebwp-proc-lock-wrapper {
		background: #ffffff;
		display: flex;
		align-items: center;
		border: 1px solid #a2badd;
		border-left: 4px solid #0b5cd1;
		gap: 20px;
		padding: 1px 12px
	}

	.notice.ebwp-center.ebwp-proc-lock-wrapper img {
		width: 80px;
	}

	.notice.ebwp-center.ebwp-proc-lock-wrapper h1,
	.notice.ebwp-center.ebwp-proc-lock-wrapper p {
		padding: 0;
		margin: 0;
	}

	.ebwp-proc-info {
		padding: 10px;
	}

	.ebwp-proc-stale {
		background-image: linear-gradient(90deg, #ebe5e5, transparent);
		padding: 10px 10px 10px 15px;
		margin: 10px 0 3px;
	}

	.ebwp-proc-stale h2 {
		padding: 0;
		margin: 0 0 5px;
	}

	.ebwp-proc-stale .button {
		margin: 15px 0 5px;
	}
</style>

<div class="<?php echo esc_attr( $everest_backup_class ); ?> ebwp-center ebwp-proc-lock-wrapper">

	<img class="logo-icon" src="<?php echo esc_url( EVEREST_BACKUP_URL . 'assets/images/ebwp-loading.gif' ); ?>">

	<div class="ebwp-proc-info">
		<h1><?php echo esc_html( wptexturize( __( "Everest Backup's process is running", 'everest-backup' ) ) ); ?></h1>

		<p>
			<?php
			$everest_backup_userdata      = get_userdata( $args['uid'] );
			$everest_backup_process_types = everest_backup_get_process_types();

			if ( ! empty( $everest_backup_userdata->user_nicename ) ) {
				printf(
					/* translators: %1$s is user nicename, %2$s is process type, %3$s is time started and %4$s is time elapsed. */
					esc_html__( 'The user %1$s is performing %2$s since %3$s [ Elapsed: %4$s ]', 'everest-backup' ),
					'<strong>"' . esc_html( ucwords( $everest_backup_userdata->user_nicename ) ) . '"</strong>',
					'<strong>' . esc_html( $everest_backup_process_types[ $args['type'] ] ) . '</strong>',
					'<strong>' . esc_html( wp_date( 'h:i:s A', $args['time'] ) ) . '</strong>',
					'<strong>' . esc_html( human_time_diff( $args['time'] ) ) . '</strong>'
				);
			} else {
				printf(
					/* translators: %1$s is Everest Backup plugin name, %2$s is process type, %3$s is time started and %4$s is time elapsed. */
					esc_html__( '%1$s is performing %2$s since %3$s [ Elapsed: %4$s ]', 'everest-backup' ),
					'<strong>"Everest Backup"</strong>',
					'<strong>' . esc_html( $everest_backup_process_types[ $args['type'] ] ) . '</strong>',
					'<strong>' . esc_html( wp_date( 'h:i:s A', $args['time'] ) ) . '</strong>',
					'<strong>' . esc_html( human_time_diff( $args['time'] ) ) . '</strong>'
				);
			}
			?>
		</p>

		<?php

		if ( ! empty( $args['is_stale'] ) ) {
			$force_abort_url = add_query_arg(
				array(
					'page'        => 'everest-backup-export',
					'force-abort' => true,
					'uid'         => get_current_user_id(),
				),
				network_admin_url( '/admin.php' )
			);

			?>

			<div class="ebwp-proc-stale">
				<h2><?php esc_html_e( '*** Attention Required ***', 'everest-backup' ); ?></h2>
				<p><?php esc_html_e( 'It seems like this process has been running for too long. If you think Everest Backup is showing a false message, then you can "Force Abort" this process and clear this notice.', 'everest-backup' ); ?></p>

				<a href="<?php echo esc_url( wp_nonce_url( $force_abort_url ) ); ?>" onclick="onEBWPAbortBtnClick(this)" class="button button-hero button-danger force-delete"><?php esc_html_e( 'Force Abort', 'everest-backup' ); ?></a>
			</div>
			<?php
		}
		?>

	</div>
</div>
