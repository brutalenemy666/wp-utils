<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Crb_Helpers {

	/**
	 * @param  string $post_name
	 * @param  string $defaults
	 * @return a value if available from a POST request, otherwise returns a default value
	 */
	public static function read_post( $post_name, $default = '' ) {
		if ( !isset($_POST[$post_name]) || !$_POST[$post_name] ) {
			return $default;
		}

		return $_POST[$post_name];
	}

	public static function read_post_escaped( $post_name, $default = '' ) {
		$post_value = self::read_post($post_name, $default);

		return esc_sql($post_value);
	}

	/**
	 * @param  string $post_name
	 * @param  string $defaults
	 * @return a value if available from a GET request, otherwise returns a default value
	 */
	public static function read_get( $get_name, $default = '' ) {
		if ( !isset($_GET[$get_name]) || !$_GET[$get_name] ) {
			return $default;
		}

		return $_GET[$get_name];
	}

	public static function read_get_escaped( $post_name, $default = '' ) {
		$get_value = self::read_get($post_name, $default);

		return esc_sql($get_value);
	}

	/**
	 * Return true if the page has been requested via XMLHttpRequest
	 */
	public static function is_ajax() {
		return strtolower(self::read_server_var('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest';
	}

	/**
	 * Return true is this is a post request
	 */
	public static function is_post() {
		return self::read_server_var('REQUEST_METHOD') === "POST";
	}

	/**
	 * escapes the html
	 */
	public static function esc_html( $html ) {
		return htmlspecialchars($html);
	}

	/**
	 * Read the old Input
	 */
	public static function old_input( $field ) {
		echo esc_html(self::read_post($field));
	}

	public static function checked_attribute( $checked, $current = '', $echo = false ) {
		return checked($checked, $current, $echo);
	}

	public static function selected_attribute( $val ) {
		return selected($selected, $current, $echo);
	}

	public static function read_server_var( $var_name ) {
		if ( empty($_SERVER[$var_name]) ) {
			return;
		}

		return $_SERVER[$var_name];
	}

	/**
	 * Recursive function for retrieving a value by a given variable route in a specific array or object
	 * Return false if the variable isn't available
	 *
	 * Examples:
	 * $variable_route=ID, $arr_obj=new WP_POST, return => $arr_obj->ID
	 * $variable_route=post|post_name, $arr_obj=(object), return => $arr_obj->post->post_name
	 *
	 * @param  string $variable_route
	 * @param  mixed $arr_obj
	 * @return a value specified by a variable route from a given array or object
	 */
	public static function get_var( $variable_route, $arr_obj, $default_value = '' ) {
		if ( !$arr_obj ) {
			return $default_value;
		}

		$route_parts = explode('|', $variable_route);
		$var_name = trim($route_parts[0]);

		$result = '';

		// try to convert json/serialized strings
		$arr_obj = self::unpack_variable($arr_obj);

		// get the value
		if ( is_object($arr_obj) && !empty($arr_obj->$var_name) ) {
			$result = $arr_obj->$var_name;
		} else if ( is_array($arr_obj) && !empty($arr_obj[$var_name]) ) {
			$result = $arr_obj[$var_name];
		} else {
			return $default_value;
		}

		if ( count($route_parts) > 1 ) {
			unset($route_parts[0]);
			return self::get_var(implode('|', $route_parts), $result, $default_value);
		} else {
			return $result;
		}
	}

	public static function unpack_variable( $variable ) {
		if ( is_string($variable) && is_serialized_string($variable) ) {
			$variable = unserialize($variable);
		} else if ( is_string($variable) && self::is_json($variable) ) {
			$variable = json_decode($variable);
		}

		return $variable;
	}

	/**
	 * @param  array $errors
	 * @param  string $name
	 * @return specific error message specified by an error name from a given errors array
	 */
	public static function maybe_display_error( $errors = array(), $name = '' ) {
		$errors = (array) $errors;

		if ( empty($errors[$name]) ) {
			return;
		}

		$msg = '';
		foreach ((array) $errors[$name] as $err_text) {
			$msg .= '<span>' . $err_text . '</span>';
		}

		echo '<span class="error">' . $msg . '</span>';
	}

	/**
	 * Truncates a string to a certain word count.
	 * @param  string  $input Text to be shortalized. Any HTML will be stripped.
	 * @param  integer $words_limit number of words to return
	 * @param  string $end the suffix of the shortalized text
	 * @return string
	 */
	public static function shortalize( $input, $words_limit = 15, $end = '...' ) {
		return wp_trim_words($input, $words_limit, $end);
	}

	/**
	 * Checks if json
	 * @param  mixed  $string
	 * @return boolean         [description]
	 */
	public static function is_json( $string ) {
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}

	public static function comes_from_same_url() {
		$request_uri = self::read_server_var('REQUEST_URI');
		$referer = self::read_server_var('HTTP_REFERER');
		$origin = self::read_server_var('HTTP_ORIGIN');

		return rtrim($request_uri, '/') === rtrim(str_replace($origin, '', $referer), '/');
	}

	/**
	 * Returns the top most parent post
	 */
	public static function get_top_parent( $post_id = 0 ) {
		$ancestors = get_post_ancestors($post_id);
		$parents = array_reverse($ancestors);

		if ( empty($parents) ) {
			return get_post($post_id);
		}

		$top_parent = get_post($parents[0]);

		return $top_parent;
	}

	/**
	 * Returns the url of the current page
	 */
	public static function get_current_url() {
		$page_url = 'http';

		if ( !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on" ) {
			$page_url .= "s";
		}

		$page_url .= "://";
		if ( $_SERVER["SERVER_PORT"] != "80" ) {
			$page_url .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
		} else {
			$page_url .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
		}

		return $page_url;
	}

	/**
	 * Returns the top most parent post
	 */
	public static function get_top_parent( $post_id = 0 ) {
		$parents = self::get_parents($post_id);

		if ( empty($parents) ) {
			return get_post($post_id);
		}

		$top_parent = get_post($parents[0]);

		return $top_parent;
	}

	/**
	 * Returns an array with post parents
	 */
	public function get_parents( $post_id = 0 ) {
		$ancestors = get_post_ancestors($post_id);
		$parents = array_reverse($ancestors);

		return $parents;
	}

	/**
	 * Returns the url of the current page
	 */
	public static function get_current_url() {
		$page_url = 'http';

		if ( !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on" ) {
			$page_url .= "s";
		}

		$page_url .= "://";
		if ( $_SERVER["SERVER_PORT"] != "80" ) {
			$page_url .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
		} else {
			$page_url .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
		}

		return $page_url;
	}
}
