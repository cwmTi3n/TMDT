<?php
/**
 * Handle admin menu for this plugin.
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
 * Handle admin menu for this plugin.
 *
 * @since 1.0.0
 */
class Admin_Menu {

	/**
	 * Create and register admin menus.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public static function init() {
		$hook = is_multisite() ? 'network_admin_menu' : 'admin_menu';
		add_action( $hook, array( __CLASS__, 'register' ) );
		add_action( 'admin_bar_menu', array( __CLASS__, 'admin_bar_item' ), 100 );
	}

	/**
	 * Register admin menu and sub menus.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	public static function register() {
		self::register_menus();
		self::register_submenus();
	}

	/**
	 * Add Everest Backup related menu items to admin bar for user ease.
	 *
	 * @param \WP_Admin_Bar $admin_bar WP_Admin_Bar class object.
	 * @return void
	 */
	public static function admin_bar_item( \WP_Admin_Bar $admin_bar ) {

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( is_multisite() && ! is_network_admin() ) {
			return;
		}

		$admin_menus = self::get_menus();

		if ( is_array( $admin_menus ) && ! empty( $admin_menus ) ) {
			foreach ( $admin_menus as $slug => $admin_menu ) {
				$main_menu_slug = "everest-backup-{$slug}";

				$admin_bar->add_menu(
					array(
						'id'     => $main_menu_slug,
						'parent' => null,
						'group'  => null,
						'title'  => ! empty( $admin_menu['menu_title'] ) ? $admin_menu['menu_title'] : '',
						'href'   => network_admin_url( "admin.php?page={$main_menu_slug}" ),
					)
				);

			}
		}

		$submenus = self::get_submenus();

		if ( is_array( $submenus ) && ! empty( $submenus ) ) {
			foreach ( $submenus as $slug => $submenu ) {
				$menu_slug = "everest-backup-{$slug}";

				$admin_bar->add_menu(
					array(
						'id'     => 'everest-backup-export' === $menu_slug ? "$menu_slug-2" : $menu_slug,
						'parent' => ! empty( $submenu['parent_slug'] ) ? $submenu['parent_slug'] : 'everest-backup-export',
						'group'  => null,
						'title'  => ! empty( $submenu['menu_title'] ) ? $submenu['menu_title'] : '',
						'href'   => network_admin_url( "admin.php?page={$menu_slug}" ),
					)
				);

			}
		}

	}

	/**
	 * Register admin menus.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	protected static function register_menus() {
		$admin_menus = self::get_menus();

		if ( is_array( $admin_menus ) && ! empty( $admin_menus ) ) {
			foreach ( $admin_menus as $slug => $admin_menu ) {
				$menu_slug = "everest-backup-{$slug}";

				add_menu_page(
					! empty( $admin_menu['page_title'] ) ? $admin_menu['page_title'] : '',
					! empty( $admin_menu['menu_title'] ) ? $admin_menu['menu_title'] : '',
					! empty( $admin_menu['capability'] ) ? $admin_menu['capability'] : '',
					$menu_slug,
					! empty( $admin_menu['function'] ) ? $admin_menu['function'] : "Everest_Backup\Template_Functions\\${slug}_page_template_cb",
					! empty( $admin_menu['icon_url'] ) ? $admin_menu['icon_url'] : '',
					! empty( $admin_menu['position'] ) ? $admin_menu['position'] : null
				);

			}
		}
	}

	/**
	 * Register submenus.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	protected static function register_submenus() {
		$submenus = self::get_submenus();

		if ( is_array( $submenus ) && ! empty( $submenus ) ) {
			foreach ( $submenus as $slug => $submenu ) {
				$menu_slug = "everest-backup-{$slug}";

				add_submenu_page(
					! empty( $submenu['parent_slug'] ) ? $submenu['parent_slug'] : 'everest-backup-export',
					! empty( $submenu['page_title'] ) ? $submenu['page_title'] : '',
					! empty( $submenu['menu_title'] ) ? $submenu['menu_title'] : '',
					! empty( $submenu['capability'] ) ? $submenu['capability'] : '',
					$menu_slug,
					! empty( $submenu['function'] ) ? $submenu['function'] : "Everest_Backup\Template_Functions\\${slug}_page_template_cb",
					! empty( $submenu['position'] ) ? $submenu['position'] : null
				);

			}
		}
	}

	/**
	 * Return an array of menus arguments.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	protected static function get_menus() {
		$menus = array(
			'export' => array(
				'page_title' => __( 'Backup', 'everest-backup' ),
				'menu_title' => __( 'Everest Backup', 'everest-backup' ),
				'capability' => 'manage_options',
				'function'   => '',
				'icon_url'   => EVEREST_BACKUP_URL . 'assets/images/icon.png',
				'position'   => null,
			),
		);

		return apply_filters( 'everest_backup_filter_admin_menus', $menus );
	}

	/**
	 * Returns an array of submenus arguments.
	 *
	 * @return array
	 * @since 1.0.0
	 */
	protected static function get_submenus() {
		$submenus = array(
			'export'          => array(
				'parent_slug' => '',
				'page_title'  => __( 'Backup', 'everest-backup' ),
				'menu_title'  => __( 'Backup', 'everest-backup' ),
				'capability'  => 'manage_options',
				'function'    => '',
				'position'    => null,
			),
			'import'          => array(
				'parent_slug' => '',
				'page_title'  => __( 'Restore', 'everest-backup' ),
				'menu_title'  => __( 'Restore', 'everest-backup' ),
				'capability'  => 'manage_options',
				'function'    => '',
				'position'    => null,
			),
			'migration_clone' => array(
				'parent_slug' => '',
				'page_title'  => __( 'Migration / Clone', 'everest-backup' ),
				'menu_title'  => __( 'Migration / Clone', 'everest-backup' ),
				'capability'  => 'manage_options',
				'function'    => '',
				'position'    => null,
			),
			'history'         => array(
				'parent_slug' => '',
				'page_title'  => __( 'Backup History', 'everest-backup' ),
				'menu_title'  => __( 'History', 'everest-backup' ),
				'capability'  => 'manage_options',
				'function'    => '',
				'position'    => null,
			),
			'logs'            => array(
				'parent_slug' => '',
				'page_title'  => __( 'Logs', 'everest-backup' ),
				'menu_title'  => __( 'Logs', 'everest-backup' ),
				'capability'  => 'manage_options',
				'function'    => '',
				'position'    => null,
			),
			'settings'        => array(
				'parent_slug' => '',
				'page_title'  => __( 'Settings', 'everest-backup' ),
				'menu_title'  => __( 'Settings', 'everest-backup' ),
				'capability'  => 'manage_options',
				'function'    => '',
				'position'    => null,
			),
			'addons'        => array(
				'parent_slug' => '',
				'page_title'  => __( 'Addons', 'everest-backup' ),
				'menu_title'  => __( 'Addons', 'everest-backup' ),
				'capability'  => 'manage_options',
				'function'    => '',
				'position'    => null,
			),
		);

		return apply_filters( 'everest_backup_filter_admin_submenus', $submenus );
	}

}

Admin_Menu::init();
