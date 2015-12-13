<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Crb_Geolocation_By_IP extends Crb_Abstract_Geolocation {

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
