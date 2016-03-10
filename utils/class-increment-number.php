<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for generating an auto increment number
 *
 * 00000001
 * 00000002
 * 00000032
 * 00002591
 * ...
 *
 * $key = 'product_number';
 * $number = new Crb_Increment_Number( $key );
 * $number_one = $number->get_next();
 * $number_two = $number->get_next();
 * ...
 */
class Crb_Increment_Number {

	public $option_name = '';
	public $length;

	public function __construct( $key, $length = 9 ) {
		$this->option_name = 'crb_increment_' . esc_attr($key);
		$this->length = max(1, intval($length));
	}

	public function format_number( $number ) {
		return str_pad(intval($number), $this->length, 0, STR_PAD_LEFT);
	}

	public function get_previous( $format = true ) {
		// get previous value
		$number = (int) get_option( $this->option_name );

		if ( $format===true ) {
			return $this->format_number($number);
		} else {
			return $number;
		}
	}

	public function get_next( $format = true ) {
		$previous_number = $this->get_previous(false);
		$next_number = $previous_number + 1;

		// update value
		update_option($this->option_name, $next_number);

		if ( $format === true ) {
			return $this->format_number($next_number);
		} else {
			return $next;
		}
	}
}
