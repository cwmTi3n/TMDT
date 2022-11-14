<?php
/**
 * Template file for displaying the list of previous backups.
 *
 * @package everest-backup
 */

/**
 * Exit if accessed directly.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! is_array( $args ) ) {
	return;
}

$everest_backup_history_table_obj = ! empty( $args['history_table_obj'] ) ? $args['history_table_obj'] : false;

if ( ! is_object( $everest_backup_history_table_obj ) ) {
	return;
}

$everest_backup_history_table_obj->prepare_items();

?>
<div class="wrap">

	<hr class="wp-header-end">

	<?php
		everest_backup_render_view( 'template-parts/header' );
	?>
	<main class="everest-backup-wrapper">
		<form id="everest-backup-container" method="get">
			<input type="hidden" name="page" value="<?php echo esc_attr( $args['page'] ); ?>">
			<?php $everest_backup_history_table_obj->display(); ?>
		</form>

		<?php everest_backup_render_view( 'template-parts/sidebar' ); ?>

	</main>
</div>
