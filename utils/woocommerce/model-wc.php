<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// add_filter('woocommerce_show_page_title', '__return_false');

/**
 * Content Wrappers
 *
 * @see woocommerce_output_content_wrapper()
 * @see woocommerce_output_content_wrapper_end()
 */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
add_action( 'woocommerce_before_main_content', array('Crb_Woo_Wrappers', 'woocommerce_before_main_content'), 10 );
add_action( 'woocommerce_after_main_content', array('Crb_Woo_Wrappers', 'woocommerce_after_main_content'), 10 );

/**
 * Single Summary Content Wrappers
 *
 * @see woocommerce_before_single_product_summary()
 * @see woocommerce_after_single_product_summary()
 */
add_action( 'woocommerce_before_single_product_summary', array('Crb_Woo_Wrappers', 'woocommerce_before_single_product_summary'), 20 );
add_action( 'woocommerce_after_single_product_summary', array('Crb_Woo_Wrappers', 'woocommerce_after_single_product_summary'), 20 );


/**
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * WooCommerce Filters and Actions
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 */

class Crb_Woo_Wrappers {

	/* ==========================================================================
		# Main Wrappers
	========================================================================== */

	public static function woocommerce_before_main_content() {
		// ...
	}

	public static function woocommerce_after_main_content() {
		// ...
	}

	/* ==========================================================================
		# Single Summary Wrappers
	========================================================================== */

	public static function woocommerce_before_single_product_summary() {
		// ...
	}

	public static function woocommerce_after_single_product_summary() {
		// ...
	}

}

class Crb_Woo_Functions {

}

/**
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Rewrite WooCommerce functions
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 */

// functions...
