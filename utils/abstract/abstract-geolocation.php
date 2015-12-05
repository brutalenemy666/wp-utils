<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Crb_Geolocation {

	protected $ip;
	protected $address;
	protected static $cache_time = 259200; // a three days in seconds

	/** @var array API endpoints for looking up user IP address */
	protected $ip_lookup_apis = array(
		'icanhazip'         => 'http://ipv4.icanhazip.com',
		'ipify'             => 'http://api.ipify.org/',
		'ipecho'            => 'http://ipecho.net/plain',
		'ident'             => 'http://v4.ident.me',
		'whatismyipaddress' => 'http://bot.whatismyipaddress.com',
		'ip.appspot'        => 'http://ip.appspot.com'
	);

	protected function __construct() { }

	protected abstract function geolocate();

	public static function get($ip_or_address=null) {
		$ip = '';
		$address = '';

		if (!$ip_or_address) {
			$locator_class = 'Crb_Geolocation_By_IP';
			$ip = self::get_ip_address();
		} else if (self::is_ip($ip_or_address) || self::is_ipv4($ip_or_address)) {
			$locator_class = 'Crb_Geolocation_By_IP';
			$ip = $ip_or_address;
		} else {
			$address = $ip_or_address;
			$locator_class = 'Crb_Geolocation_By_Address';
		}

		$locator = new $locator_class($ip_or_address);
		$locator->ip = $ip;
		$locator->address = $address;
		return $locator->geolocate();
	}

	public static function get_cached($ip_or_address=null, $reset_cache=false) {
		$transient_name = 'crb_geo_' . md5($ip_or_address);
		$geolocation = get_transient($transient_name);

		if (false===$geolocation || $reset_cache===true) {
			$geolocation = self::get($ip_or_address);

			set_transient($transient_name, $geolocation, self::$cache_time);
		}

		return $geolocation;
	}

	public static function get_ip_address() {
		$client  = $_SERVER['HTTP_CLIENT_IP'];
		$forward = $_SERVER['HTTP_X_FORWARDED_FOR'];
		$remote  = $_SERVER['REMOTE_ADDR'];

		if(filter_var($client, FILTER_VALIDATE_IP)) {
			$ip = $client;
		} elseif(filter_var($forward, FILTER_VALIDATE_IP)) {
			$ip = $forward;
		} else {
			$ip = $remote;
		}

		return $ip;
	}

	public static function is_ip($str) {
		$ret = filter_var($str, FILTER_VALIDATE_IP);

		return $ret;
	}

	public static function is_ipv4($str) {
		$ret = filter_var($str, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);

		return $ret;
	}
}
