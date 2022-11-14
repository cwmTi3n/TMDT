<?php
/**
 * The Public side of the plugin
 *
 * @link       https://www.addonify.com
 * @since      1.0.0
 *
 * @package    Addonify_Compare_Products
 * @subpackage Addonify_Compare_Products/admin
 */

/**
 * The Public side of the plugin
 *
 * Defines the plugin name, version, and other required variables.
 *
 * @package    Addonify_Compare_Products
 * @subpackage Addonify_Compare_Products/admin
 * @author     Addodnify <info@addonify.com>
 */
class Addonify_Compare_Products_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * The compare cookie data.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array    $compare_cookie_items    The compare cookie data.
	 */
	private $compare_cookie_items;

	/**
	 * Number of items in compare cookie.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $compare_cookie_items_count    Number of items in compare cookie.
	 */
	private $compare_cookie_items_count;


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string $plugin_name The name of the plugin.
	 * @param    string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		$this->compare_cookie_items = $this->get_compare_cookie_items();

		$this->compare_cookie_items_count = $this->get_compare_cookie_items_count();
	}

	/**
	 * Initialize public hooks.
	 * 
	 * @since 1.0.0
	 */
	public function public_init() {

		if ( 
			! class_exists( 'WooCommerce' ) ||
			(int) addonify_compare_products_get_option( 'enable_product_comparison' ) != 1
		) {
			return;
		}

		// Initialize the compare cookie.
		add_action( 'init', array( $this, 'init_callback' ) );

		// Register scripts and styles for the frontend.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// Add the compare button to the product catalog.
		switch ( addonify_compare_products_get_option( 'compare_products_btn_position' ) ) {
			case 'before_add_to_cart' :
				add_action( 'woocommerce_after_shop_loop_item', array( $this, 'render_compare_button' ), 5 );
				break;
			default :
				add_action( 'woocommerce_after_shop_loop_item', array( $this, 'render_compare_button' ), 15 );
		}

		// Add custom markup into footer to display comparison modal.
		add_action( 'wp_footer', array( $this, 'add_markup_into_footer_callback' ) );

		// Ajax callback handler to add product into the comapre list.
		add_action( 'wp_ajax_addonify_compare_products_add_product', array( $this, 'add_product_into_compare_cookie' ) );
		add_action( 'wp_ajax_nopriv_addonify_compare_products_add_product', array( $this, 'add_product_into_compare_cookie' ) );

		// Ajax callback handler to remove product from the comapre list.
		add_action( 'wp_ajax_addonify_compare_products_remove_product', array( $this, 'remove_product_from_compare_cookie' ) );
		add_action( 'wp_ajax_nopriv_addonify_compare_products_remove_product', array( $this, 'remove_product_from_compare_cookie' ) );

		// Ajax callback handler to search products.
		add_action( 'wp_ajax_addonify_compare_products_search_products', array( $this, 'ajax_products_search_callback' ) );
		add_action( 'wp_ajax_nopriv_addonify_compare_products_search_products', array( $this, 'ajax_products_search_callback' ) );

		// Ajax callback handler to render comparison table in the compare modal.
		add_action( 'wp_ajax_addonify_compare_products_compare_content', array( $this, 'render_comparison_content' ) );
		add_action( 'wp_ajax_nopriv_addonify_compare_products_compare_content', array( $this, 'render_comparison_content' ) );

		// Register shortocode to display comparison table in the comparison page.
		add_shortcode( 'addonify_compare_products', array( $this, 'render_comparison_content' ) );
	}


	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		wp_enqueue_style( 'perfect-scrollbar', plugin_dir_url( __FILE__ ) . 'assets/build/css/conditional/perfect-scrollbar.css', array(), $this->version );

		if ( is_rtl() ) {
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/build/css/public-rtl.css', array(), $this->version );
		} else {
			wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/build/css/public.css', array(), $this->version );
		}

		if ( (int) addonify_compare_products_get_option( 'load_styles_from_plugin' ) === 1 ) {

			$inline_css = $this->dynamic_css();

			$custom_css = addonify_compare_products_get_option( 'custom_css' );

			if ( $custom_css ) {
				$inline_css .= $custom_css;
			}
			
			$inline_css = $this->minify_css( $inline_css );

			wp_add_inline_style( $this->plugin_name, $inline_css );
		}
	}


	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( 'perfect-scrollbar', plugin_dir_url( __FILE__ ) . 'assets/build/js/conditional/perfect-scrollbar.min.js', null, $this->version, true );

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'assets/build/js/public.min.js', array( 'jquery' ), $this->version, true );

		$localize_args = array(
			'ajaxURL' => admin_url( 'admin-ajax.php' ),
			'compareItemsCount' => $this->compare_cookie_items_count,
			'nonce' => wp_create_nonce( $this->plugin_name ),
			'actionSearchProducts' => 'addonify_compare_products_search_products',
			'actionRemoveProduct' => 'addonify_compare_products_remove_product',
			'actionAddProduct' => 'addonify_compare_products_add_product',			
			'actionGetCompareContent' => 'addonify_compare_products_compare_content',
		);

		// localize script.
		wp_localize_script(
			$this->plugin_name,
			'addonifyCompareProductsJSObject',
			$localize_args
		);

	}



	/**
	 * Set cookie if cookie is not set.
	 *
	 * @since    1.0.0
	 */
	public function init_callback() {

		if ( ! array_key_exists( $this->plugin_name, $_COOKIE ) ) {

			$this->set_compare_cookie();
		} 
	}


	/**
	 * Checks if compare cookie is set and get the compare cookie items.
	 * 
	 * @since 1.0.0
	 * @return array
	 */
	public function get_compare_cookie_items() {

		return ( array_key_exists( $this->plugin_name, $_COOKIE ) ) ? json_decode( stripslashes( $_COOKIE[$this->plugin_name] ), 1 ) : array();
	}


	/**
	 * Checks if compare cookie is array and number of items in the compare cookie.
	 * 
	 * @since 1.0.0
	 * @return int
	 */
	public function get_compare_cookie_items_count() {

		return is_array( $this->compare_cookie_items ) ? count( $this->compare_cookie_items ) : 0;
	}


	/**
	 * Set the compare cookie with products.
	 * 
	 * @since 1.0.0
	 * @return boolean true if cookie is set, false otherwise.
	 */
	private function set_compare_cookie( $product_ids = array() ) {

		// Set browser cookie if there are products in the compare list.
		$cookies_lifetime = (int) addonify_compare_products_get_option( 'compare_products_cookie_expires' ) * DAY_IN_SECONDS;

		if ( 
			is_array( $product_ids ) &&
			count( $product_ids ) > 0 
		) {
			return setcookie( $this->plugin_name, json_encode( $product_ids ), time() + $cookies_lifetime, COOKIEPATH, COOKIE_DOMAIN );
		} else {

			return setcookie( $this->plugin_name, '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN );
		}
	}



	/**
	 * Ajax call handler to add product into the compare cookie.
	 * 
	 * @since 1.0.0
	 * @return array $response_data
	 */
	public function add_product_into_compare_cookie() {

		$response_data = array(
			'success' => false,
			'message' => '',
		);

		if ( 
			! array_key_exists( 'nonce', $_POST ) ||
			! isset( $_POST['nonce'] ) ||
			! wp_verify_nonce( $_POST['nonce'], $this->plugin_name )
		) {
			$response_data['message'] = __( 'Invalid security token.', 'addonify-compare-products' );	
			wp_send_json( $response_data );
		} 

		$product_id = (int) $_POST['product_id'];

		$product = wc_get_product( $product_id );

		if ( ! $product ) {
			$response_data['message'] = __( 'Invalid product ID.', 'addonify-compare-products' );
			wp_send_json( $response_data );  
		}

		if ( in_array( $product_id, $this->compare_cookie_items ) ) {
			$response_data['message'] = __( 'Product ID is already in compare list.'. 'addonify-compare-products' );
			wp_send_json( $response_data );
		}

		$this->compare_cookie_items[] = $product_id;

		if ( ! $this->set_compare_cookie( $this->compare_cookie_items ) ) {
			$response_data['message'] = __( 'Product could not be added into the compare list.', 'addonify-compare-products' );
			wp_send_json( $response_data );
		}

		$response_data['success'] = true;

		$response_data['product_image'] = $this->get_docker_product_image( $product );

		$response_data['items_count'] = $this->get_compare_cookie_items_count();

		$response_data['message'] = __( 'Product added into the compare list.', 'addonify-compare-products' );

		wp_send_json( $response_data );
	}


	/**
	 * Ajax call handler to remove product from the compare cookie.
	 * 
	 * @since 1.0.0
	 * @return array $response_data
	 */
	public function remove_product_from_compare_cookie() {

		$response_data = array(
			'success' => false,
			'message' => '',
		);

		if ( 
			! array_key_exists( 'nonce', $_POST ) ||
			! isset( $_POST['nonce'] ) ||
			! wp_verify_nonce( $_POST['nonce'], $this->plugin_name )
		) {
			$response_data['message'] = __( 'Invalid security token.', 'addonify-compare-products' );	
			wp_send_json( $response_data );
		} 

		$product_id = (int) $_POST['product_id'];

		$product = wc_get_product( $product_id );

		if ( ! $product ) {
			$response_data['message'] = __( 'Invalid product ID.', 'addonify-compare-products' );
			wp_send_json( $response_data );  
		}

		if ( ! in_array( $product_id, $this->compare_cookie_items ) ) {
			$response_data['message'] = __( 'Product ID is not in the compare list.'. 'addonify-compare-products' );
			wp_send_json( $response_data );
		}

		$product_id_index = array_search( $product_id, $this->compare_cookie_items );

		unset( $this->compare_cookie_items[$product_id_index] );

		$this->compare_cookie_items = array_values( $this->compare_cookie_items );

		if ( ! $this->set_compare_cookie( $this->compare_cookie_items ) ) {
			$response_data['message'] = __( 'Product could not be removed from the compare list.', 'addonify-compare-products' );
			wp_send_json( $response_data );
		}

		$response_data['success'] = true;

		$response_data['items_count'] = $this->get_compare_cookie_items_count();

		$response_data['message'] = __( 'Product removed from the compare list.', 'addonify-compare-products' );

		wp_send_json( $response_data );
	}

	
	/**
	 * Return product's image when product is added into the compare cookie.
	 * 
	 * @since 1.0.0
	 * @return string HTML markup for product image.
	 */
	public function get_docker_product_image( $product ) {
		
		return '<div class="addonify-compare-dock-components" data-product_id="' . esc_attr( $product->get_id() ) . '"><div class="sortable addonify-compare-dock-thumbnail" data-product_id="' . esc_attr( $product->get_id() ) . '"><span class="addonify-compare-dock-remove-item-btn addonify-compare-docker-remove-button" data-product_id="' . esc_attr( $product->get_id() ) . '"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"></path></svg></span>' . wp_kses_post( $product->get_image() ) . '</div></div>';
	}


	/**
	 * Ajax call handler to search products.
	 *
	 * @since    1.0.0
	 */
	public function ajax_products_search_callback() {

		// only ajax request is allowed.
		if ( ! wp_doing_ajax() ) {
			wp_die( 'Invalid Request' );
		}

		$query = isset( $_POST['query'] ) ? sanitize_text_field( wp_unslash( $_POST['query'] ) ) : '';
		$nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';

		// search query is required.
		if ( empty( $query ) ) {
			wp_die( 'search query is required !' );
		}

		// verify nonce.
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, $this->plugin_name ) ) {

			wp_die( 'nonce validation fail !' );
		}

		// skip products that are already in cookies.
		$wp_query = new WP_Query(
			array(
				's' => $query,
				'post__not_in' => $this->get_compare_cookie_items(),
				'post_type' => 'product',
			)
		);

		do_action( 'addonify_compare_products/search_result', array( 
			'wp_query' => $wp_query,
			'query' => $query
		 ) );

		wp_die();
	}


	/**
	 * Generating "Compare" button
	 *
	 * @since    1.0.0
	 */
	public function render_compare_button() {

		do_action( 'addonify_compare_products/compare_button' );
	}



	/**
	 * Generate required markups and print it in footer of the website
	 *
	 * @since    1.0.0
	 */
	public function add_markup_into_footer_callback() {

		// do not show following template if it is a shortcode display page.
		if ( get_the_ID() === (int) addonify_compare_products_get_option( 'compare_page' ) ) {
			return;
		}

		do_action( 'addonify_compare_products/docker_modal' );

		do_action( 'addonify_compare_products/search_modal' );

		do_action( 'addonify_compare_products/comparison_modal' );
	}



	/**
	 * Generate contents for compare and print it
	 * Can be used in ajax requests or in shortcodes
	 *
	 * @since    1.0.0
	 */
	public function render_comparison_content() {

		if ( wp_doing_ajax() ) {

			do_action( 'addonify_compare_products/comparison_content' );
			wp_die();
		} else {

			ob_start();
			do_action( 'addonify_compare_products/comparison_content' );
			return ob_get_clean();
		}
	}


	/**
	 * Print dynamic CSS generated from settings page.
	 */
	public function dynamic_css() {

		$css_values = array(
			'--adfy_compare_products_button_color' => addonify_compare_products_get_option( 'compare_btn_text_color' ),
			'--adfy_compare_products_button_color_hover' => addonify_compare_products_get_option( 'compare_btn_text_color_hover' ),
			'--adfy_compare_products_button_bg_color' => addonify_compare_products_get_option( 'compare_btn_bck_color' ),
			'--adfy_compare_products_button_bg_color_hover' => addonify_compare_products_get_option( 'compare_btn_bck_color_hover' ),
			'--adfy_compare_products_dock_bg_color' => addonify_compare_products_get_option( 'floating_bar_bck_color' ),
			'--adfy_compare_products_dock_text_color' => addonify_compare_products_get_option( 'floating_bar_text_color' ),
			'--adfy_compare_products_dock_add_button_color' => addonify_compare_products_get_option( 'floating_bar_add_button_text_color' ),
			'--adfy_compare_products_dock_add_button_color_hover' => addonify_compare_products_get_option( 'floating_bar_add_button_text_color_hover' ),
			'--adfy_compare_products_dock_add_button_bg_color' => addonify_compare_products_get_option( 'floating_bar_add_button_bck_color' ),
			'--adfy_compare_products_dock_add_button_bg_color_hover' => addonify_compare_products_get_option( 'floating_bar_add_button_bck_color_hover' ),
			'--adfy_compare_products_dock_compare_button_color' => addonify_compare_products_get_option( 'floating_bar_compare_button_text_color' ),
			'--adfy_compare_products_dock_compare_button_color_hover' => addonify_compare_products_get_option( 'floating_bar_compare_button_text_color_hover' ),
			'--adfy_compare_products_dock_compare_button_bg_color' => addonify_compare_products_get_option( 'floating_bar_compare_button_bck_color' ),
			'--adfy_compare_products_dock_compare_button_bg_color_hover' => addonify_compare_products_get_option( 'floating_bar_compare_button_bck_color_hover' ),
			'--adfy_compare_products_search_modal_overlay_bg_color' => addonify_compare_products_get_option( 'search_modal_overlay_bck_color' ),
			'--adfy_compare_products_search_modal_bg_color' => addonify_compare_products_get_option( 'search_modal_bck_color' ),
			'--adfy_compare_products_search_modal_add_button_color' => addonify_compare_products_get_option( 'search_modal_add_btn_text_color' ),
			'--adfy_compare_products_search_modal_add_button_color_hover' => addonify_compare_products_get_option( 'search_modal_add_btn_text_color_hover' ),
			'--adfy_compare_products_search_modal_add_button_bg_color' => addonify_compare_products_get_option( 'search_modal_add_btn_bck_color' ),
			'--adfy_compare_products_search_modal_add_button_bg_color_hover' => addonify_compare_products_get_option( 'search_modal_add_btn_bck_color_hover' ),
			'--adfy_compare_products_search_modal_close_button_color' => addonify_compare_products_get_option( 'search_modal_close_btn_text_color' ),
			'--adfy_compare_products_search_modal_close_button_color_hover' => addonify_compare_products_get_option( 'search_modal_close_btn_text_color_hover' ),
			'--adfy_compare_products_search_modal_close_button_border_color' => addonify_compare_products_get_option( 'search_modal_close_btn_border_color' ),
			'--adfy_compare_products_search_modal_close_button_border_color_hover' => addonify_compare_products_get_option( 'search_modal_close_btn_border_color_hover' ),
			'--adfy_compare_products_table_title_color' => addonify_compare_products_get_option( 'table_title_color' ),
			'--adfy_compare_products_table_title_color_hover' => addonify_compare_products_get_option( 'table_title_color_hover' ),
		);

		$css = ':root {';

		foreach ( $css_values as $key => $value ) {

			if ( $value ) {
				$css .= $key . ': ' . $value . ';';
			}
		}

		$css .= '}';

		return $css;
	}


	/**
	 * Minify the dynamic css.
	 * 
	 * @param string $css css to minify.
	 * @return string minified css.
	 */
	public function minify_css( $css ) {

		$css = preg_replace( '/\s+/', ' ', $css );
		$css = preg_replace( '/\/\*[^\!](.*?)\*\//', '', $css );
		$css = preg_replace( '/(,|:|;|\{|}) /', '$1', $css );
		$css = preg_replace( '/ (,|;|\{|})/', '$1', $css );
		$css = preg_replace( '/(:| )0\.([0-9]+)(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}.${2}${3}', $css );
		$css = preg_replace( '/(:| )(\.?)0(%|em|ex|px|in|cm|mm|pt|pc)/i', '${1}0', $css );

		return trim( $css );
	}


	/**
	 * Return product attributes array
	 *
	 * Used for future purpose.
	 * 
	 * @since    1.0.0
	 * @param    string $product Woocommerce product object.
	 */
	private function get_product_attributes( $product ) {

		$attributes = $product->get_attributes();

		$display_result = array();

		foreach ( $attributes as $attribute ) {

			$name = $attribute->get_name();
			if ( $attribute->is_taxonomy() ) {

				$terms = wp_get_post_terms( $product->get_id(), $name, 'all' );
				$cwtax = $terms[0]->taxonomy;
				$cw_object_taxonomy = get_taxonomy( $cwtax );

				if ( isset( $cw_object_taxonomy->labels->singular_name ) ) {
					$tax_label = $cw_object_taxonomy->labels->singular_name;
				} elseif ( isset( $cw_object_taxonomy->label ) ) {
					$tax_label = $cw_object_taxonomy->label;
					if ( 0 === strpos( $tax_label, 'Product ' ) ) {
						$tax_label = substr( $tax_label, 8 );
					}
				}

				$tax_terms = array();
				foreach ( $terms as $term ) {
					$single_term = esc_html( $term->name );
					array_push( $tax_terms, $single_term );
				}

				$display_result[ $tax_label ] = implode( ', ', $tax_terms );

			} else {
				$display_result[ $name ] = esc_html( implode( ', ', $attribute->get_options() ) );
			}
		}

		return ! empty( $display_result ) ? $display_result : array();
	}


	/**
	 * Register shortcode to use in comparison page
	 *
	 * @since    1.0.0
	 */
	public function register_shortcode() {

		
	}
}
