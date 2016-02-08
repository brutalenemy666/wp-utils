<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * If you use this class, then you should install a plugin to purge the outdated cache such as https://wordpress.org/plugins/delete-expired-transients/
 */

abstract class Crb_Geolocation {

	protected $ip;
	protected $address;
	protected static $cache_time = 2 * 24 * 60; // a two days in seconds

	protected function __construct() { }

	protected abstract function _geolocate();

	public static function get( $ip_or_address=null ) {
		$ip = '';
		$address = '';
		$ip_or_address = trim($ip_or_address);

		if ( !$ip_or_address ) {
			$locator_class = 'Crb_Geolocation_By_IP';
			$ip = self::get_ip_address();
		} else if ( self::is_ip($ip_or_address) || self::is_ipv4($ip_or_address) ) {
			$locator_class = 'Crb_Geolocation_By_IP';
			$ip = $ip_or_address;
		} else {
			$address = $ip_or_address;
			$locator_class = 'Crb_Geolocation_By_Address';
		}

		$locator = new $locator_class($ip_or_address);
		$locator->ip = $ip;
		$locator->address = $address;
		return $locator->_geolocate();
	}

	public static function get_cached( $ip_or_address=null, $reset_cache=false ) {
		$ip_or_address = trim($ip_or_address);
		$transient_name = 'crb_geo_' . md5($ip_or_address);
		$geolocation = get_transient($transient_name);

		if ( $geolocation===false || $reset_cache===true ) {
			$geolocation = self::get($ip_or_address);

			set_transient($transient_name, $geolocation, self::$cache_time);
		}

		return $geolocation;
	}

	public static function get_ip_address() {
		$client  = $_SERVER['HTTP_CLIENT_IP'];
		$forward = $_SERVER['HTTP_X_FORWARDED_FOR'];
		$remote  = $_SERVER['REMOTE_ADDR'];

		if ( filter_var($client, FILTER_VALIDATE_IP) ) {
			$ip = $client;
		} elseif( filter_var($forward, FILTER_VALIDATE_IP) ) {
			$ip = $forward;
		} else {
			$ip = $remote;
		}

		return $ip;
	}

	public static function is_ip( $str ) {
		$return = filter_var($str, FILTER_VALIDATE_IP);

		return $return;
	}

	public static function is_ipv4( $str ) {
		$return = filter_var($str, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);

		return $return;
	}
}

class Crb_Geolocation_By_Address extends Crb_Geolocation {

	protected $geocode_apis = 'http://maps.googleapis.com/maps/api/geocode/json?address=%s&sensor=false';

	protected function _geolocate() {
		$webservice_url = sprintf($this->geocode_apis, urlencode($this->address));

		$result = wp_remote_get($webservice_url);
		if( is_wp_error($result) ) {
			$error = $result;
			throw new Exception($error->get_error_message());
		}

		$geocode = json_decode($result['body']);

		if ( !empty($geocode->results) ) {
			$lat = $geocode->results[0]->geometry->location->lat;
			$lng = $geocode->results[0]->geometry->location->lng;
		} else {
			$lat = 0;
			$lng = 0;
		}

		$address = array(
			'lat'     => $lat,
			'lng'     => $lng,
			'ip'      => false,
			'address' => $this->address
		);

		return $address;
	}
}

class Crb_Geolocation_By_IP extends Crb_Geolocation {

	/** @var array API endpoints for geolocating an IP address */
	protected $geoip_apis = array(
		'telize'           => 'http://www.telize.com/geoip/%s',
		'geoip-api.meteor' => 'http://geoip-api.meteor.com/lookup/%s',
	);

	protected function _geolocate() {
		$lat = 0;
		$lng = 0;

		foreach ($this->geoip_apis as $service_name => $service_url) {
			$webservice_url = sprintf($service_url, urlencode($this->ip));

			$response = wp_remote_get($webservice_url, array(
				'timeout' => 2
			));

			if ( is_wp_error($response) ) {
				$error = $response;
				// throw new Exception($error->get_error_message());
				continue;
			} else if ( !$response['body'] ) {
				continue;
			}

			$geocode = json_decode($response['body']);

			switch ($service_name) {
				case 'telize':
					$lat = $geocode->latitude;
					$lng = $geocode->longitude;
					break;
				case 'geoip-api.meteor':
					$lat = $geocode->ll[0];
					$lng = $geocode->ll[1];
					break;
				default:
					break;
			}

			if ( $lat!==0 || $lng!==0 ) {
				break;
			}
		}

		$address = array(
			'lat'     => $lat,
			'lng'     => $lng,
			'ip'      => $this->ip,
			'address' => false
		);

		return $address;
	}
}
