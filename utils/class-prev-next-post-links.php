<?php
/**
 * Helps you to change the Prev/next post links query.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Crb_PrevNext_Post_links {

	public $wpdb;

	private function __construct() {
		global $wpdb;
		$this->wpdb = $wpdb;
	}

	public static function add_filters() {
		$links = new self();

		add_filter( 'get_next_post_where', array($links, 'next_post_where') );
		add_filter( 'get_previous_post_where', array($links, 'prev_post_where') );

		add_filter( 'get_next_post_sort', array($links, 'next_post_sort') );
		add_filter( 'get_previous_post_sort', array($links, 'prev_post_sort') );

		add_filter( 'get_next_post_join', array($links, 'next_post_join') );
		add_filter( 'get_previous_post_join', array($links, 'prev_post_join') );
	}

	public static function remove_filters() {
		$links = new self();

		remove_filter( 'get_next_post_where', array($links, 'next_post_where') );
		remove_filter( 'get_previous_post_where', array($links, 'prev_post_where') );

		remove_filter( 'get_next_post_sort', array($links, 'next_post_sort') );
		remove_filter( 'get_previous_post_sort', array($links, 'prev_post_sort') );

		remove_filter( 'get_next_post_join', array($links, 'next_post_join') );
		remove_filter( 'get_previous_post_join', array($links, 'prev_post_join') );
	}

	/**
	 * Prev Links
	 */

	public function prev_post_sort( $sort_sql ) {

		return $sort_sql;
	}

	public function prev_post_where( $where_sql ) {

		return $where_sql;
	}

	public function prev_post_join( $join_sql ) {

		return $join_sql;
	}

	/**
	 * Next Links
	 */

	public function next_post_sort( $sort_sql ) {

		return $sort_sql;
	}

	public function next_post_where( $where_sql ) {

		return $where_sql;
	}

	public function next_post_join( $join_sql ) {

		return $join_sql;
	}

}

/**
 * Usage:
 */

Crb_PrevNext_Post_links::add_filters();
	next_post_link('%link', __('Previous', 'crb'));

 	previous_post_link('%link', __('Next', 'crb'));
Crb_PrevNext_Post_links::remove_filters();
