<?php
/**
 * WooCommerce - Fixing tax calculation on Cart Page when Tax City is set.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'woocommerce_customer_taxable_address', 'crb_woocommerce_customer_taxable_address' );
function crb_woocommerce_customer_taxable_address( $address_args=array() ) {
	$country_code = $address_args[0];
	$state_code = $address_args[1];
	$zip_code = $address_args[2];
	$city_name = $address_args[3];

	// if the zip code is available then skip the city name validation.
	// the city name will be pulled using the zip code
	if ( !$zip_code ) {
		return $address_args;
	}

	global $wpdb;
	$query = "SELECT `TAX_ONE`.`location_code` AS 'city_name'
			FROM `{$wpdb->prefix}woocommerce_tax_rate_locations` AS `TAX_ONE`
			INNER JOIN `{$wpdb->prefix}woocommerce_tax_rate_locations` AS `TAX_TWO` ON `TAX_TWO`.`tax_rate_id` = `TAX_ONE`.`tax_rate_id`
			WHERE `TAX_TWO`.`location_code` = %s
			AND `TAX_ONE`.`location_code` != %s";
	$query = $wpdb->prepare($query, $zip_code, $zip_code);

	$result = $wpdb->get_row( $query );
	if ( !$result ) {
		return $address_args;
	}

	// preventing the right city name when:
	// 1. the customer misspell the city name
	// 2. the city name is missing but required for tax calculation, however, the zip code is available
	// basically, replacing the city name with the city name relation to that zip code
	$city_name = $result->city_name;

	// set new city name
	$address_args[3] = $city_name;

	return $address_args;
}
