<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Crb_Func_Wrappers {

	public static $option_prefix = 'crb_';

	/**
	 * get_option
	 * @param string $option  Name of option to retrieve. Expected to not be SQL-escaped.
	 * @param mixed  $default Optional. Default value to return if the option does not exist.
	 * @return mixed Value set for the option.
	 */
	public static function get_option($option, $default=false) {
		$option = self::$option_prefix . $option;
		return get_option($option, $default);
	}

	/**
	 * update_option
	 * @param string      $option   Option name. Expected to not be SQL-escaped.
	 * @param mixed       $value    Option value. Must be serializable if non-scalar. Expected to not be SQL-escaped.
	 * @param string|bool $autoload Optional. Whether to load the option when WordPress starts up.
	 * @return bool False if value was not updated and true if value was updated.
	 */
	public static function update_option($option, $value='', $autoload=null) {
		$option = self::$option_prefix . $option;
		return update_option($option, $value, $autoload);
	}

	/**
	 * delete_option
	 * @param string $option Name of option to remove. Expected to not be SQL-escaped.
	 * @return bool True, if option is successfully deleted. False on failure.
	 */
	public static function delete_option($option) {
		$option = self::$option_prefix . $option;
		delete_option( $option );
	}

}
