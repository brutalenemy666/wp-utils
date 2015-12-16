<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Crb_Woo {

	public static function exists() {
		return class_exists('WooCommerce');
	}

	public static function is_woocommerce() {
		return self::exists() && is_woocommerce();
	}

	// Get WooCommerce Page ID

	public static function get_shop_page() {
		return get_option( 'woocommerce_shop_page_id' );
	}

	public static function get_cart_page() {
		return get_option( 'woocommerce_cart_page_id' );
	}

	public static function get_checkout_page() {
		return get_option( 'woocommerce_checkout_page_id' );
	}

	public static function get_pay_page() {
		return get_option( 'woocommerce_pay_page_id' );
	}

	public static function get_thanks_page() {
		return get_option( 'woocommerce_thanks_page_id' );
	}

	public static function get_myaccount_page() {
		return get_option( 'woocommerce_myaccount_page_id' );
	}

	public static function get_edit_address_page() {
		return get_option( 'woocommerce_edit_address_page_id' );
	}

	public static function get_view_order_page() {
		return get_option( 'woocommerce_view_order_page_id' );
	}

	public static function get_terms_page() {
		return get_option( 'woocommerce_terms_page_id' );
	}

	// check for a cirtain WooCommerce page

	public static function is_product() {
		return self::exists() && is_product();
	}

	public static function is_shop() {
		return self::exists() && is_shop();
	}

	public static function is_checkout() {
		return self::exists() && is_checkout();
	}

	public static function is_account_page() {
		return self::exists() && is_account_page();
	}

	public static function is_cart() {
		return self::exists() && is_cart();
	}

	public static function is_product_category() {
		return self::exists() && is_product_category();
	}

	public static function is_product_tag() {
		return self::exists() && is_product_category();
	}

	public static function is_order_received_page() {
		return self::exists() && is_order_received_page();
	}
}
