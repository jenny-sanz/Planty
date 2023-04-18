<?php

class WPR_WooCommerce_Config {

	public function __construct() {
		add_action('wp_ajax_wpr_addons_add_cart_single_product', [$this, 'add_cart_single_product_ajax']);
		add_action('wp_ajax_nopriv_wpr_addons_add_cart_single_product', [$this, 'add_cart_single_product_ajax']);
		
		if ( 'on' == get_option('wpr_enable_woo_flexslider_navigation', 'on') ) {
			add_filter('woocommerce_single_product_carousel_options', [$this, 'wpr_update_woo_flexslider_options']);
		}

		if ( 'on' !== get_option('wpr_enable_product_image_zoom', 'on') ) {
			add_filter( 'woocommerce_single_product_zoom_enabled', '__return_false' );
		}
		
		add_action( 'wp',[$this, 'wpr_remove_wc_lightbox'], 99 ); //: TODO condition
		// add_filter( 'body_class', [$this, 'wpr_remove_elementor_lightbox'] ); //: TODO condition

		// Change number of products that are displayed per page (shop page)
		add_filter( 'loop_shop_per_page', [$this, 'shop_products_per_page'], 20 );

		// Rewrite WC Default Templates
		add_filter( 'wc_get_template', [ $this, 'rewrite_default_wc_templates' ], 10, 3 );

		add_filter( 'woocommerce_add_to_cart_fragments', [$this, 'wc_refresh_mini_cart_count']);
	}

	function wpr_remove_elementor_lightbox( $classes ) {
	
		$classes[] = 'wpr-no-lightbox';
	
		// Return classes
		return $classes;
	}

	function wpr_remove_wc_lightbox() {	 	 
	   remove_theme_support( 'wc-product-gallery-lightbox' );	 	 
	}

	function wc_refresh_mini_cart_count($fragments) {
		ob_start();
		$items_count = WC()->cart->get_cart_contents_count();
		?>
		<span class="wpr-mini-cart-icon-count <?php echo $items_count ? '' : 'wpr-mini-cart-icon-count-hidden'; ?>"><?php echo $items_count ? $items_count : '0'; ?></span>
		<?php
		$fragments['.wpr-mini-cart-icon-count'] = ob_get_clean();

		ob_start();
		$sub_total = WC()->cart->get_cart_subtotal();
		?>
				<span class="wpr-mini-cart-btn-price">
					<?php
							echo $sub_total; 
					?>
				</span>
		<?php
		$fragments['.wpr-mini-cart-btn-price'] = ob_get_clean();

		return $fragments;
	}

	public function add_cart_single_product_ajax() {
		add_action( 'wp_loaded', [ 'WC_Form_Handler', 'add_to_cart_action' ], 20 );
	
		if ( is_callable( [ 'WC_AJAX', 'get_refreshed_fragments' ] ) ) {
			WC_AJAX::get_refreshed_fragments();
		}
	
		die();
	}
	
	public function wpr_update_woo_flexslider_options( $options ) {
		$options['directionNav'] = true;
		return $options;
	}
	
	public function shop_products_per_page( $cols ) {
	  return get_option('wpr_woo_shop_ppp', 9);
	}

	public function rewrite_default_wc_templates( $located, $template_name ) {
		// Cart template
		if ( $template_name === 'cart/cart.php' ) {
			$located = WPR_ADDONS_PATH .'includes/woocommerce/templates/cart/cart.php';
		}

		// Mini-cart template
		if ( $template_name === 'cart/mini-cart.php' ) {
			$located = WPR_ADDONS_PATH .'includes/woocommerce/templates/cart/mini-cart.php';
		}

		if ( $template_name === 'notices/success.php' ) {
			$located = WPR_ADDONS_PATH .'includes/woocommerce/templates/notices/success.php';
		}

		if ( $template_name === 'notices/error.php' ) {
			$located = WPR_ADDONS_PATH .'includes/woocommerce/templates/notices/error.php';
		}
		
		if ( $template_name === 'notices/notice.php' ) {
			$located = WPR_ADDONS_PATH .'includes/woocommerce/templates/notices/notice.php';
		}

		// if ( $template_name === 'cart/cart-empty.php' ) {

		// }

		// if ( $template_name === 'checkout/form-checkout.php' ) {

		// }

		return $located;
	}

}

new WPR_WooCommerce_Config();