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

	public static function get_endpoint( $key ) {
		return get_option('woocommerce_' . $key . '_endpoint');
	}

	// Get WooCommerce Page ID

	public static function get_shop_page() {
		return get_option( 'woocommerce_shop_page_id' );
	}

	public static function get_myaccount_page() {
		return get_option( 'woocommerce_myaccount_page_id' );
	}

	public static function get_terms_page() {
		return get_option( 'woocommerce_terms_page_id' );
	}

	public static function get_cart_page() {
		return get_option( 'woocommerce_cart_page_id' );
	}

	public static function get_checkout_page() {
		return get_option( 'woocommerce_checkout_page_id' );
	}

	// Get WooCommerce Page URLs

	public static function get_shop_url() {
		return get_permalink( self::get_shop_page() );
	}

	public static function get_myaccount_url( $endpoint = '' ) {
		$url = get_permalink( self::get_myaccount_page() );

		$_endpoint = '';
		if ( $endpoint ) {
			$_endpoint = self::get_endpoint( $endpoint );
		}

		if ( $_endpoint ) {
			$url .= $_endpoint . '/';
		}

		return $url;
	}

	public static function get_edit_address_url() {
		return self::get_myaccount_url('myaccount_edit_address');
	}

	public static function get_edit_account_url() {
		return self::get_myaccount_url('myaccount_edit_account');
	}


	public static function get_add_payment_method_url() {
		return self::get_myaccount_url('myaccount_add_payment_method');
	}

	public static function get_view_orders_url() {
		return self::get_myaccount_url('myaccount_view_order');
	}


	public static function get_lost_password_url() {
		return self::get_myaccount_url('myaccount_lost_password');
	}

	public static function get_logout_url() {
		return self::get_myaccount_url('logout');
	}


	public static function get_checkout_pay_url() {
		return self::get_myaccount_url('checkout_pay');
	}

	public static function get_checkout_order_received_url() {
		return self::get_myaccount_url('checkout_order_received');
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

	public static function is_view_order_page() {
		return self::exists() && is_view_order_page();
	}

	public static function is_edit_account_page() {
		return self::exists() && is_edit_account_page();
	}

	public static function is_cart() {
		return self::exists() && is_cart();
	}

	public static function is_product_taxonomy() {
		return self::exists() && is_product_taxonomy();
	}

	public static function is_product_category( $term = '' ) {
		return self::exists() && is_product_category( $term );
	}

	public static function is_product_tag( $term = '' ) {
		return self::exists() && is_product_category( $term );
	}

	public static function is_order_received_page() {
		return self::exists() && is_order_received_page();
	}

	public static function is_checkout_pay_page() {
		return self::exists() && is_checkout_pay_page();
	}

	public static function is_wc_endpoint_url( $endpoint = false ) {
		return self::exists() && is_wc_endpoint_url( $endpoint );
	}

	public static function is_add_payment_method_page() {
		return self::exists() && is_add_payment_method_page();
	}

	public static function is_lost_password_page() {
		return self::exists() && is_lost_password_page();
	}

	public static function is_ajax() {
		return self::exists() && is_ajax();
	}

	public static function is_store_notice_showing() {
		return self::exists() && is_store_notice_showing();
	}

	public static function is_filtered() {
		return self::exists() && is_filtered();
	}
}
