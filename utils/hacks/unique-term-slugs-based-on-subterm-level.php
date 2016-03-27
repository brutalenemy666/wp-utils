<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Usage:
 *
 * Allows using duplicating term slugs across same taxonomy, however, restricted to be unique on a parential level
 *
 * Crb_Unique_Subterm_Slugs::add_taxonomy( $taxonomy_name );
 * Example: Crb_Unique_Subterm_Slugs::add_taxonomy('category');
 */

class Crb_Unique_Subterm_Slugs {

	private static $initialized = false;

	private static $taxonomies = array();

	protected $_args = null;
	protected $_term_id = null;
	protected $_taxonomy = null;
	protected $_parsed_args = null;
	protected $_parent_term_id = null;

	private function __construct() {
		add_filter( 'wp_update_term_parent', array($this, 'wp_update_term_parent'), 15, 5 );
	}

	public static function add_taxonomy( $taxonomy_name = '' ) {
		if ( !in_array($taxonomy_name, self::$taxonomies) ) {
			self::$taxonomies[] = $taxonomy_name;
		}

		if ( !self::$initialized ) {
			self::$initialized = true;
			new self();
		}
	}

	public function wp_update_term_parent( $parent_term_id, $term_id, $taxonomy, $parsed_args, $args ) {
		if ( !in_array($taxonomy, self::$taxonomies) ) {
			return $parent_term_id;
		}

		$cloned_instance = clone $this;

		$cloned_instance->_args = $args;
		$cloned_instance->_term_id = $term_id;
		$cloned_instance->_taxonomy = $taxonomy;
		$cloned_instance->_parsed_args = $parsed_args;
		$cloned_instance->_parent_term_id = $parent_term_id;

		add_filter( 'get_term', array($cloned_instance, 'get_term'), 15, 2 );

		return $parent_term_id;
	}

	public function get_term( $_term, $taxonomy ) {
		remove_filter( 'get_term', array($this, 'get_term'), 15 );

		return $this->_get_term( $_term );
	}

	protected function _get_term( $_term ) {
		if ( !$_term ) {
			return false;
		}

		if ( $_term->parent === $this->_parent_term_id ) {
			return $_term;
		}

		global $wpdb;
		$query = "SELECT t.*, tt.*
				FROM $wpdb->terms AS t
				INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id
				WHERE t.slug = %s
				AND tt.taxonomy = %s
				AND tt.parent = %d
				LIMIT 1";
		$query = $wpdb->prepare( $query, $this->_args['slug'], $this->_taxonomy, $this->_parent_term_id );
		$terms = $wpdb->get_results( $query );

		if ( !$terms ) {
			$query = "SELECT t.*, tt.*
					FROM $wpdb->terms AS t
					INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id
					WHERE tt.term_id = %d
					AND tt.taxonomy = %s
					LIMIT 1";
			$query = $wpdb->prepare( $query, $this->_term_id, $this->_taxonomy );
			$terms = $wpdb->get_results( $query );
		}

		$term = $terms[0];
		$term_obj = new WP_Term( $term );
		$term_obj->filter( $term_obj->filter );

		return $term_obj;
	}
}
