<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Crb_User {

	protected $user = null;
	protected $meta_prefix = '';

	public $new_userdata = array(
		'user_id'   => 0,
		'userdata'  => array(
			// 'ID' => $current_user_id
		),
		'metadata'  => array(
			// meta_key => meta_value
		)
	);

	protected $user_fields = array(
		'ID',

		'user_nicename',
		'display_name',

		'user_login',
		'user_email',
		'user_level',
		'user_url',
		'user_status',
		'user_registered',
		'user_activation_key',
	);

	public static function get_instance( $user=null ) {
		return new self($user);
	}

	protected function __construct( $user ) {
		// $this->meta_prefix = '_crb_';

		$this->_load_user($user);
	}

	public function __isset( $key ) {
		return (bool) $this->$key;
	}

	public function __get( $key ) {
		$function_name = '_get_meta';

		if ( in_array($key, $this->user_fields) ) {
			return $this->user->$key;
		} else if ( method_exists($this, $key) ) {
			// make the function accessible only if the method is public
			$reflection = new ReflectionMethod($this, $key);
			if ( $reflection->isPublic() ) {
				return $this->$key();
			}
		}

		return $this->$function_name($key);
	}

	protected function _load_user( $user=null ) {
		if ( is_user_logged_in() && !$user ) {
			$this->user = wp_get_current_user();
		} else if ( $user instanceof WP_User ) {
			$this->user = $user;
		} else if ( is_integer($user) ) {
			$this->user = get_user_by('id', $user);
		} else if ( is_string($user) && is_email($user) ) {
			$this->user = get_user_by('email', $user);
		} else if ( is_string($user) ) {
			$this->user = get_user_by('login', $user);
		}

		if ( !$this->user ) {
			$message = __('User not found.', 'crb');
			throw new Exception($message);
		}

		return $this;
	}

	/* ==========================================================================
		# Public Functions
	========================================================================== */

	public function refresh_userdata() {
		$this->_load_user( $this->get_id() );
	}

	public function get_id() {
		return (int) $this->user->ID;
	}

	public function set_meta( $key, $value ) {
		update_user_meta($this->get_id(), $this->meta_prefix . $key, $value);
	}

	public function delete_meta( $key ) {
		delete_user_meta($this->get_id(), $key);
	}

	public function get_meta( $key ) {
		return get_user_meta($this->get_id(), $this->meta_prefix . $key, true);
	}

	public function can( $capability ) {
		return call_user_func_array('user_can', array($this->user, $capability));
	}

	public function get_role() {
		$user_roles = $this->user->roles;
		$user_role = array_shift($user_roles);

		return $user_role;
	}

}
