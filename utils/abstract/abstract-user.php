<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Crb_Abstract_User {

	protected $user = null;
	protected $meta_prefix = '';

	protected $use_acf_meta_functions = false;

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

	public static function get_instance( $user = null ) {
		$child_class = get_called_class();

		return new $child_class($user);
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

	protected function _load_user( $user = null ) {
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

	public function _set_meta( $key, $value ) {
		if ( $this->use_acf_meta_functions && function_exists('update_field') ) {
			update_field( $this->meta_prefix . $key, $value, 'user_' . $this->get_id() );
		} else {
			update_user_meta( $this->get_id(), $this->meta_prefix . $key, $value );
		}
	}

	public function _delete_meta( $key ) {
		if ( $this->use_acf_meta_functions && function_exists('get_field') ) {
			delete_user_meta( $this->get_id(), '_' . $this->meta_prefix . $key );
		}

		delete_user_meta( $this->get_id(), $this->meta_prefix . $key );
	}

	public function _get_meta( $key ) {
		if ( $this->use_acf_meta_functions && function_exists('get_field') ) {
			$meta_value = get_field( $this->meta_prefix . $key, 'user_' . $this->get_id() );
		} else {
			$meta_value = get_user_meta( $this->get_id(), $this->meta_prefix . $key, true );
		}

		return $meta_value;
	}

	public function _refresh_userdata() {
		$this->_load_user( $this->get_id() );
	}

	/* ==========================================================================
		# Public Functions
	========================================================================== */

	public function get_id() {
		return (int) $this->user->ID;
	}

	public function can( $capability ) {
		return call_user_func_array('user_can', array($this->user, $capability));
	}

	public function get_role() {
		$user_roles = $this->user->roles;
		$user_role = array_shift($user_roles);

		return $user_role;
	}

	public function set_password( $password = '' ) {
		if ( !$password ) {
			$password = wp_generate_password();
		}

		wp_set_password($password, $user->get_id());

		return $password;
	}

	public function check_password( $password = '' ) {
		return wp_check_password($password, $this->user->data->user_pass, $user->get_id());
	}

	public function process_login( $password = false, $redirect_url = '' ) {
		if ( $password && !$this->check_password($password) ) {
			$message = __('Invalid user password.', 'crb');
			throw new Exception($message);
		}

		wp_set_auth_cookie($user->get_id(), true);

		if ( !$redirect_url ) {
			return;
		}

		wp_redirect($redirect_url);
		exit;
	}

	/* ==========================================================================
		# User Save/Update
	========================================================================== */

	protected function _save_to_db() {
		$this->_before_save();
		$this->_insert_as_wp_user();
		$this->_update_metas();
		$this->_after_save();

		// reset $new_userdata
		$this->new_userdata = array(
			'user_id'   => 0,
			'userdata'  => array(),
			'metadata'  => array()
		);

		return $this;
	}

	protected function _insert_as_wp_user() {
		$userdata = $this->new_userdata['userdata'];
		$user_id = !empty($userdata['ID']) ? intval($userdata['ID']) : false;

		if ( $user_id ) {
			$function_name = 'wp_update_user';
		} else if ( $this->post ) {
			$function_name = 'wp_insert_user';
		}

		$this->new_userdata['user_id'] = $function_name($userdata, true);

		if ( is_wp_error($this->new_userdata['user_id']) ) {
			$error = $this->new_userdata['user_id'];
			throw new Exception($error->get_error_message());
		} else {
			$user_id = $this->new_userdata['user_id'];
			$this->_load_user($user_id);
		}

		return $this;
	}

	protected function _update_metas() {
		$user_id = $this->get_id();

		$metadata = $this->new_userdata['metadata'];
		if ( empty($metadata) ) {
			return;
		}

		foreach ($metadata as $meta_key => $meta_value) {
			$this->_set_meta( $meta_key, $meta_value );
		}

		return $this;
	}

	protected function _before_save() {
		// do something to the userdata before save
		// might be handy in the child class
	}

	protected function _after_save() {
		// do something when post saving is successful
		// might be handy in the child class
	}
}
