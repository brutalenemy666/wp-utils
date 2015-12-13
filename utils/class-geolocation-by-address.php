<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Crb_Geolocation_By_Address extends Crb_Abstract_Geolocation {

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
