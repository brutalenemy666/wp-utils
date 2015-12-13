<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Crb_User {

	protected $user = null;
	protected $meta_prefix = '';

	protected $new_userdata = array(
		'user_id'   => 0,
		'userdata'  => array(
			// 'ID' => $current_user_id
		),
		'metadata'  => array(
			// meta_key => meta_value
		)
	);

	public static function get_instance( $user=null ) {
		return new self($user);
	}

	protected function __construct( $user=null ) {
		// $this->meta_prefix = '_crb_';

		$this->_load_user($user);
	}

	public function __isset( $key ) {
		return (bool) $this->$key;
	}

	public function __get( $key ) {
		$function_name = '_get_meta';
		$user_fields = array(
			'user_login',
			'user_email',
			'user_level',
			'user_firstname',
			'user_lastname',
			'display_name',
			'ID',
		);

		if ( in_array($key, $user_fields) ) {
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

	protected function _load_user( $user ) {
		if ( !is_user_logged_in() && !$user ) {
			return;
		}

		if ( is_object($user) ) {
			$this->user = $user;
		} else if ( is_integer($user) ) {
			$this->user = get_user_by('id', $user);
		} else if ( is_string($user) && is_email($user) ) {
			$this->user = get_user_by('email', $user);
		} else if ( is_string($user) ) {
			$this->user = get_user_by('login', $user);
		} else {
			$this->user = wp_get_current_user();
		}

		return $this;
	}

	protected function _set_meta( $key, $value ) {
		update_user_meta($this->user->ID, $this->meta_prefix . $key, $value);
	}

	protected function _delete_meta( $key ) {
		delete_user_meta($this->user->ID, $key);
	}

	protected function _get_meta( $key ) {
		$value = get_user_meta($this->user->ID, $this->meta_prefix . $key, true);

		return $value;
	}

	/* ==========================================================================
		# Public Functions
	========================================================================== */

	public function can( $capability ) {
		if ( !$this->user ) {
			return;
		}

		return call_user_func_array('user_can', array(
			$this->user,
			$capability
		));
	}

	public function get_role() {
		if ( !$this->user ) {
			return;
		}

		$user_roles = $this->user->roles;
		$user_role = array_shift($user_roles);

		return $user_role;
	}

	public function get_id() {
		return $this->user->ID;
	}

	/* ==========================================================================
		# Login
	========================================================================== */

	// WIP

	/* ==========================================================================
		# Reset Password
	========================================================================== */

	// WIP

	/* ==========================================================================
		# User Save/Update
	========================================================================== */

	// WIP

}
