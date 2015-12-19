<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Crb_Enqueue_Scripts {

	protected $prefix;

	private function __construct() {
		$this->prefix = 'crb_';
		$this->path_to_js = '/';
		$this->path_to_css = '/';

		add_action('wp_enqueue_scripts', array($this, 'enqueue_front_end_script'));
		add_action('admin_enqueue_scripts', array($this, 'enqueue_back_end_script'));
	}

	public static function init() {
		return new self();
	}

	/**
	 * Enqueue front end script
	 */
	public function enqueue_front_end_script() {
		// js
		wp_enqueue_script($this->prefix . 'functions', $this->path_to_js . '/frontend-functions.js', array(
			'jquery'
		));

		// css
		wp_enqueue_style($this->prefix . 'styles', $this->path_to_css . '/frontend-style.css');

		// js vars
		$this->localize_script();
	}

	/**
	 * Enqueue administration script
	 */
	public function enqueue_back_end_script() {
		// js
		wp_enqueue_script($this->prefix . 'functions', $this->path_to_js . '/backend-functions.js', array(
			'jquery'
		));

		// css
		wp_enqueue_style($this->prefix . 'styles', $this->path_to_css . '/backend-style.css');

		// js vars
		$this->localize_script();
	}

	/**
	 * Localize JS Variables
	 */
	public function localize_script() {
		// administration js variable
		$js_variables_array = array(
			'prefix' => $this->prefix,
			'ajaxurl' => admin_url('admin-ajax.php')
		);
		wp_localize_script('jquery', 'localized_js', $js_variables_array);
	}

}

Crb_Enqueue_Scripts::init();
