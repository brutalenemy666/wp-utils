<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Crb_Shortcodes {

	/**
	 * Init shortcodes
	 */
	public static function init() {
		$shortcode_functions = new Crb_Shortcode_Functions();

		// Define shortcodes
		$shortcodes = array(
			'hello-world' => new Crb_Shortcode_HelloWorld(),
			'indexed-text' => new Crb_Shortcode_IndexedText()
		);

		// add_shortcode( 'shortcode-tag', array(__CLASS__, 'output') );
		foreach ( $shortcodes as $shortcode => $shortcode_obj ) {
			add_shortcode(
				apply_filters("crb_{$shortcode}_shortcode_tag", $shortcode),
				array(
					apply_filters("crb_{$shortcode}_shortcode_obj", $shortcode_obj),
					'output'
				)
			);
		}
	}
}

class Crb_Shortcode_HelloWorld extends Crb_Shortcodes {
	public function output() {
		return 'Hello World!';
	}
}

class Crb_Shortcode_IndexedText extends Crb_Shortcodes {

	protected $index = 0;

	protected function increment_index() {
		$this->index++;
		return $this;
	}

	protected function get_next_index() {
		$this->increment_index();
		return $this->index;
	}

	public function output( $atts ) {
		$atts = shortcode_atts( array(
			'text' => 'Sample line of text :)'
		), $atts );

		$index = $this->get_next_index();

		return "#{$index}: {$atts['text']}";
	}
}

Crb_Shortcodes::init();
