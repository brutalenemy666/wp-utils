<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Crb_Post {

	public $post_id = 0;

	public $post = null;

	public $post_type = null;

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

	abstract protected function post_type_validation();
	/**
	 * Example:
	 * protected function post_type_validation() {
	 * 		if ($this->post->post_type!=='post-type-name') {
	 * 			$message = __('Cheating, uh?', 'crb');
	 * 			throw new Exception($message);
	 * 		}
	 * }
	 */

	protected function __construct($post=null) {
		// $this->meta_prefix = '_crb_';

		if (is_numeric($post) || intval($post)) {
			$this->post_id = absint($post);
			$this->post = get_post($this->post_id);
		} elseif ($post instanceof WC_Post) {
			$this->post_id = absint($post->id);
			$this->post = $post->post;
		} elseif (isset($post->ID)) {
			$this->post_id = absint($post->ID);
			$this->post = $post;
		}

		if ($post!==null && !$this->post) {
			$message = __('Post not found.', 'crb');
			throw new Exception($message);
		} else if ( $this->post ) {
			$this->post_type = $this->post->post_type;
		}

		$this->post_type_validation();
	}

	public static function get_instance($post) {
		$child_class = get_called_class();

		return new $child_class($post);
	}

	public function __isset($key) {
		return (bool) $this->$key;
	}

	public function __get($key) {
		$function_name = '_get_meta';

		if (preg_match('~^author_~', $key)) {
			$function_name = '_get_author_info';
		} else if (method_exists($this, $key)) {
			// make the function accessible only if the method is public
			$reflection = new ReflectionMethod($this, $key);
			if ($reflection->isPublic()) {
				return $this->$key();
			}
		}

		return $this->$function_name($key);
	}

	protected function _set_meta($key, $value) {
		update_post_meta($this->post_id, $this->meta_prefix . $key, $value);
	}

	protected function _get_meta($key) {
		$value = get_post_meta($this->post_id, $this->meta_prefix . $key, true);

		return $value;
	}

	protected function _get_author_info($key) {
		if (!$this->post) {
			return;
		}

		if (!$this->author) {
			$this->author = get_userdata($this->post->post_author);
		}

		if ($key==='data') {
			return $this->author;
		}

		$author_key = preg_replace('~^author_~', '', $key);

		return $this->author->$author_key;
	}

	/**
	 * Validates if the current user has permissions to edit/create new entries
	 */
	protected function current_user_can_save_to_db($action_name) {
		if ( !is_user_logged_in() ) {
			return $this;
		}

		// validation goes here

		return $this;
	}

	/* ==========================================================================
		# Public Functions
	========================================================================== */

	public function get_id() {
		return (int) $this->post->ID;
	}

	public function get_permalink($leavename=false) {
		return get_permalink($this->post_id, $leavename);
	}

	public function get_title() {
		return $this->post->post_title;
	}

	public function get_content($wpauto=false) {
		$content = $this->post->post_content;
		if ($wpauto) {
			$content = wpautop(do_shortcode($content));
		}

		return $content;
	}

	public function get_extended() {
		return get_extended($this->post->post_content);
	}

	public function get_thumbnail($size='thumbnail', $html=false) {
		if (!has_post_thumbnail($this->post->ID)) {
			return;
		}

		$thumbnail_id = get_post_thumbnail_id($this->post->ID);

		if ( $html ) {
			$return = wp_get_attachment_image($thumbnail_id, $size);
		} else {
			$attachment = wp_get_attachment_image_src($thumbnail_id, $size);
			$return = $attachment[0];
		}

		return $return;
	}

	public function get_children($post_type='') {
		$key = 'children';
		$post_type = esc_sql($post_type);

		if ( !empty($post_type) ) {
			$key .= '_' . $post_type;
		}

		if ( !empty($this->$key) ) {
			return $this->$key;
		}

		global $wpdb;

		if ( !empty($post_type) ) {
			$select = "SELECT ID FROM {$wpdb->posts} WHERE post_type = '{$post_type}' AND post_parent = {$this->post->ID} AND post_status = 'publish'";
		} else {
			$select = "SELECT ID FROM {$wpdb->posts} WHERE post_parent = {$this->post->ID} AND post_status = 'publish'";
		}

		$this->$key = $wpdb->get_results($select);

		return $this->$key;
	}

	/* ==========================================================================
		# Post Save/Update
	========================================================================== */

	protected function save_to_db() {
		$this->insert_as_wp_post();
		$this->update_metas();
		$this->update_taxonomies();

		return $this->new_postdata['post_id'];
	}

	protected function insert_as_wp_post() {
		$postdata = $this->new_postdata['postdata'];

		$function_name = 'wp_insert_post';
		if (
			!empty($postdata['ID']) ||
			($this->post && intval($postdata['ID'])===intval($this->post->ID))
		) {
			$function_name = 'wp_update_post';
		}

		// check for irregularities
		$this->current_user_can_save_to_db($function_name);

		$this->new_postdata['post_id'] = $function_name($postdata, true);

		if (is_wp_error($this->new_postdata['post_id'])) {
			$error = $this->new_postdata['post_id'];
			throw new Exception($error->get_error_message());
		} else {
			$this->post_id = $this->new_postdata['post_id'];
			$this->post = get_post($this->post_id);
			$this->post_type = $this->post->post_type;
		}
	}

	protected function update_metas() {
		$post_id = $this->new_postdata['post_id'];

		$metadata = $this->new_postdata['metadata'];
		if (empty($metadata)) {
			return;
		}

		foreach ($metadata as $meta_key => $meta_value) {
			update_post_meta($post_id, $this->meta_prefix . $meta_key, $meta_value);
		}
	}

	protected function update_taxonomies() {
		$post_id = $this->new_postdata['post_id'];

		$taxonomies = $this->new_postdata['taxonomies'];
		if (empty($taxonomies)) {
			return;
		}

		foreach ($taxonomies as $taxonomy_name => $taxonomy_terms) {
			if (empty($taxonomy_terms)) {
				continue;
			}

			$terms = wp_set_object_terms(
				$post_id,
				$taxonomy_terms,
				$taxonomy_name
			);

			if (is_wp_error($terms)) {
				$error = $terms;
				throw new Exception($error->get_error_message());
			}
		}
	}
}
