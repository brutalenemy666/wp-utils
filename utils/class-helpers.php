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
	 * Returns the page template name by given page ID
	 */
	public static function page_template( $page_id ) {
		$page_template_name = array_search(
			get_post_meta( $page_id, '_wp_page_template', true ),
			get_page_templates()
		);

		if ( $page_template_name === false ) {
			$page_template_name = 'Default';
		}

		return $page_template_name;
	}

	/**
	 * Get a page by its template, and optionally by additional criteria
	 */
	public static function get_page_by_template($template, $additional_meta = array()) {

		// the query for the page template
		$meta_query = array(
			array(
				'key' => '_wp_page_template',
				'value' => $template,
			)
		);

		// if there is an additional criteria, merge with the above meta query
		if ($additional_meta) {
			$meta_query = array_merge($meta_query, $additional_meta);
			$meta_query['relation'] = 'AND';
		}

		// perform the query
		$pages = get_posts(array(
			'post_type' => 'page',
			'posts_per_page' => 1,
			'orderby' => 'ID',
			'order' => 'ASC',
			'meta_query' => $meta_query,
			'fields' => 'ids'
		));

		// get the first page only
		if ($pages && !empty($pages[0])) {
			return get_post($pages[0]);
		}

		return false;
	}

	/**
	 * Get post_id by given post_name ( and post_type )
	 *
	 * @param  string  $post_name
	 * @param  string  $post_type optional
	 * @return integer $post_id
	 */
	public static function get_post_id_by_name( $post_name, $post_type = false ) {
		global $wpdb;

		if ( $post_name ) {
			$query = "SELECT DISTINCT ID FROM $wpdb->posts WHERE post_type = %s AND post_name = %s";
			$query = $wpdb->prepare($query, $post_type, $post_name);
		} else {
			$query = "SELECT DISTINCT ID FROM $wpdb->posts WHERE post_name = %s";
			$query = $wpdb->prepare($query, $post_name);
		}

		return $wpdb->get_var($query);
	}

	/**
	 * Remove an object filter.
	 *
	 * @param  string $tag                Hook name.
	 * @param  string $class              Class name. Use 'Closure' for anonymous functions.
	 * @param  string|void $method        Method name. Leave empty for anonymous functions.
	 * @param  string|int|void $priority  Priority
	 * @return void
	 */
	public static function remove_object_filter( $tag, $class = 'Closure', $method = NULL, $priority = NULL ) {
		$filters = $GLOBALS['wp_filter'][ $tag ];

		if ( empty( $filters ) ) {
			return;
		}

		foreach ( $filters as $p => $filter ) {
			if ( ! is_null($priority) && ( (int) $priority !== (int) $p ) ) {
				continue;
			}

			$remove = FALSE;

			foreach ( $filter as $identifier => $function ) {
				$function = $function['function'];

				if ( is_array( $function )
					&& (
						is_a( $function[0], $class )
						|| ( is_array( $function ) && $function[0] === $class )
					)
				) {
					$remove = ( $method && ( $method === $function[1] ) );
				} else if ( $function instanceof Closure && $class === 'Closure' ) {
					$remove = TRUE;
				}

				if ( $remove ) {
					unset( $GLOBALS['wp_filter'][$tag][$p][$identifier] );
				}
			}
		}
	}
}
