<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Crb_Fragments {

	protected $slug;

	protected $fragment_type;

	private function __construct($slug, $fragment_type) {
		$this->slug = $slug;
		$this->fragment_type = $fragment_type;

		$this->_set_path();
	}

	protected function _set_path() {
		$template_path = "{$this->slug}.php";
		$this->template_path = $this->_get_base_path() . $template_path;

		return $this;
	}

	protected function _get_path() {
		return $this->template_path;
	}

	protected function _get_base_path() {
		$dsep = DIRECTORY_SEPARATOR;

		// might be plugin or theme dir path
		$sdir = stylesheet_directory_uri();

		switch ( $this->fragment_type ) {
			case 'admin': // in plugin usage
				$destination_directory = $sdir . $dsep . 'includes' . $dsep . 'admin' . $dsep . 'views' . $dsep;
				break;
			case 'list': // in plugin usage
				$destination_directory = $sdir . $dsep . 'includes' . $dsep . 'lists' . $dsep;
				break;
			case 'shortcode': // for a shortcode content
				$destination_directory = $sdir . $dsep . 'shortcode' . $dsep;
				break;
			case 'template':
				$destination_directory = $sdir . $dsep . 'templates' . $dsep;
				break;
			case 'fragment':
			default:
				$destination_directory = $sdir . $dsep . 'fragments' . $dsep;
				break;
		}

		return $destination_directory;
	}

	public static function load($slug, $fragment_type=null) {
		$fragments = new self($slug, $fragment_type);

		return include($fragments->_get_path());
	}

	public static function get_path($slug, $fragment_type=null) {
		$fragments = new self($slug, $fragment_type);

		return $fragments->_get_path();
	}

	public static function get_base_path($slug, $fragment_type=null) {
		$fragments = new self($slug, $fragment_type);

		return $fragments->_get_base_path();
	}
}
