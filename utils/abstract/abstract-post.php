<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Crb_Abstract_Post {

	// post type name to validate
	protected $pt_to_validate = null; // post | page, etc.

	public $post = null;

	protected $meta_prefix = '';

	protected $new_postdata = array(
		'post_id'    => 0,
		'postdata'   => array(
			// 'post_type' => 'post'
		),
		'metadata'   => array(
			// meta_key => meta_value
		),
		'taxonomies' => array(
			// taxonomy_name => terms
		)
	);

	protected $author = null;

	public static function get_instance( $post=null ) {
		$child_class = get_called_class();

		return new $child_class($post);
	}

	protected function __construct( $post ) {
		// $this->meta_prefix = '_crb_';

		if ( is_null($this->pt_to_validate) ) {
			$message = __('"post type name to validate : $pt_to_validate" protected variable is required, but not specified.', 'crb');
			throw new Exception($message);
		}

		if ( is_numeric($post) || intval($post) ) {
			$post_id = absint($post);
			$this->post = get_post($post_id);
		} elseif ( $post instanceof WC_Post ) {
			$this->post = $post->post;
		} elseif ( isset($post->ID) ) {
			$this->post = $post;
		}

		if ( $post!==null && !$this->post ) {
			$message = __('Post not found.', 'crb');
			throw new Exception($message);
		} else if ( $this->post ) {
			$this->post_type = $this->post->post_type;
		}

		$this->_post_type_validation();
	}

	public function __isset( $key ) {
		return (bool) $this->$key;
	}

	public function __get( $key ) {
		$function_name = '_get_meta';

		if ( preg_match('~^author_~', $key) ) {
			$function_name = '_get_author_info';
		} else if ( method_exists($this, $key) ) {
			// make the function accessible only if the method is public
			$reflection = new ReflectionMethod($this, $key);
			if ( $reflection->isPublic() ) {
				return $this->$key();
			}
		}

		return $this->$function_name($key);
	}

	protected function _set_meta( $key, $value ) {
		update_post_meta($this->get_id(), $this->meta_prefix . $key, $value);
	}

	protected function _delete_meta( $key ) {
		delete_post_meta($this->get_id(), $key);
	}

	protected function _get_meta( $key ) {
		return get_post_meta($this->get_id(), $this->meta_prefix . $key, true);
	}

	protected function _get_author_info( $key ) {
		if ( !$this->post ) {
			return;
		}

		if ( !$this->author ) {
			$this->author = get_userdata($this->post->post_author);
		}

		if ( $key==='data' ) {
			return $this->author;
		}

		$author_key = preg_replace('~^author_~', '', $key);

		return $this->author->$author_key;
	}

	/**
	 * Validates if the current user has permissions to edit/create new entries
	 */
	protected function _current_user_can_save_to_db( $action_name ) {
		if ( !is_user_logged_in() ) {
			return $this;
		}

		// validation goes here
		// throw an error on failure

		return $this;
	}

	/**
	 * Validates if the post_type matches the Class post_type
	 */
	protected function _post_type_validation( $post_type=null ) {
		if ( !$post_type ) {
			$post_type = $this->get_post_type();
		}

		if ($post_type===$this->pt_to_validate) {
			return;
		}

		$message = __('Cheating, uh?', 'crb');
		throw new Exception($message);
	}

	/* ==========================================================================
		# Public Functions
	========================================================================== */

	public function get_id() {
		return (int) $this->post->ID;
	}

	public function get_permalink( $leavename=false ) {
		return get_permalink($this->get_id(), $leavename);
	}

	public function get_title() {
		return $this->post->post_title;
	}

	public function get_post_type() {
		return $this->post->post_type;
	}

	public function get_content( $wpauto=false ) {
		$content = $this->post->post_content;
		if ( $wpauto ) {
			$content = wpautop(do_shortcode($content));
		}

		return $content;
	}

	public function get_extended() {
		return get_extended($this->post->post_content);
	}

	public function get_thumbnail( $size='thumbnail', $html=false ) {
		if ( !has_post_thumbnail($this->get_id()) ) {
			return;
		}

		$thumbnail_id = get_post_thumbnail_id($this->get_id());

		if ( $html ) {
			$return = wp_get_attachment_image($thumbnail_id, $size);
		} else {
			$attachment = wp_get_attachment_image_src($thumbnail_id, $size);
			$return = $attachment[0];
		}

		return $return;
	}

	public function get_children( $post_type='', $post_status='publish' ) {
		global $wpdb;

		if ( !empty($post_type) ) {
			$query = "SELECT ID FROM {$wpdb->posts} WHERE post_type = '%s' AND post_parent = %d AND post_status = %s";
			$query = $wpdb->prepare($query, $post_type, $this->get_id(), $post_status);
		} else {
			$query = "SELECT ID FROM {$wpdb->posts} WHERE post_parent = %d AND post_status = %s";
			$query = $wpdb->prepare($query, $this->get_id(), $post_status);
		}

		return $wpdb->get_results($select);
	}

	/* ==========================================================================
		# Post Save/Update
	========================================================================== */

	protected function _save_to_db() {
		$this->_insert_as_wp_post();
		$this->_update_metas();
		$this->_update_taxonomies();
		$this->_after_save();

		// reset $new_postdata
		$this->new_postdata = array(
			'post_id'    => 0,
			'postdata'   => array(),
			'metadata'   => array(),
			'taxonomies' => array()
		);

		return $this;
	}

	protected function _insert_as_wp_post() {
		$postdata = $this->new_postdata['postdata'];

		$function_name = 'wp_insert_post';
		$postdata_post_id = empty($postdata['ID']) ? intval($postdata['ID']) : false;
		if ( $postdata_post_id ) {
			$function_name = 'wp_update_post';
			$this->_post_type_validation( get_post_type($postdata_post_id) );
		} else if ( $this->post ) {
			$function_name = 'wp_update_post';
			$this->_post_type_validation();
		}

		// check for irregularities
		$this->_current_user_can_save_to_db($function_name);

		$this->new_postdata['post_id'] = $function_name($postdata, true);

		if ( is_wp_error($this->new_postdata['post_id']) ) {
			$error = $this->new_postdata['post_id'];
			throw new Exception($error->get_error_message());
		} else {
			$post_id = $this->new_postdata['post_id'];
			$this->post = get_post($post_id);
		}

		return $this;
	}

	protected function _update_metas() {
		$post_id = $this->get_id();

		$metadata = $this->new_postdata['metadata'];
		if ( empty($metadata) ) {
			return;
		}

		foreach ($metadata as $meta_key => $meta_value) {
			update_post_meta($post_id, $this->meta_prefix . $meta_key, $meta_value);
		}

		return $this;
	}

	protected function _update_taxonomies() {
		$post_id = $this->get_id();

		$taxonomies = $this->new_postdata['taxonomies'];
		if ( empty($taxonomies) ) {
			return;
		}

		foreach ($taxonomies as $taxonomy_name => $taxonomy_terms) {
			if ( empty($taxonomy_terms) ) {
				continue;
			}

			$terms = wp_set_object_terms($post_id, $taxonomy_terms, $taxonomy_name);

			if ( is_wp_error($terms) ) {
				$error = $terms;
				throw new Exception($error->get_error_message());
			}
		}

		return $this;
	}

	protected function _after_save() {
		// do something when post saving is successful
		// might be handy in the child class
	}
}
