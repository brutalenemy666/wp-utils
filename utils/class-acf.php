<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * ACF Wrapper Functions
 * Documentation : http://www.advancedcustomfields.com/resources
 */

class Crb_ACF {

	public static function exists() {
		return class_exists('Acf');
	}

	public static function get_option( $option_name=null ) {
		if ( !self::exists() || !$option_name ) {
			return;
		}

		return get_field($option_name, 'option');
	}

	public static function update_option( $option_name, $value ) {
		if ( !self::exists() || !$option_name ) {
			return;
		}

		update_field($option_name, $value, 'option');
	}

	public static function get_post_meta( $post_id=false, $meta_key, $format_value=true ) {
		if ( !self::exists() || !$meta_key || !$post_id ) {
			return;
		}

		return get_field($meta_key, $post_id, $format_value);
	}

	public static function update_post_meta( $post_id, $meta_key, $value ) {
		if ( !self::exists() || !$meta_key || !$post_id ) {
			return;
		}

		update_field($meta_key, $value, $post_id);
	}

	public static function get_term_meta( $term_obj, $meta_key ) {
		if ( !self::exists() || !is_object($term_obj) ) {
			return;
		}

		return get_field($meta_key, $term_obj);
	}

	public static function update_term_meta( $term_obj, $meta_key, $value ) {
		if ( !self::exists() || !is_object($term_obj) ) {
			return;
		}

		update_field($meta_key, $value, $term_obj);
	}

	public static function get_user_meta( $user_id, $meta_key ) {
		if ( !self::exists() || !intval($user_id) ) {
			return;
		}

		return get_field($meta_key, 'user_' . intval($user_id));
	}

	public static function update_user_meta( $user_id, $meta_key, $value ) {
		if ( !self::exists() || !intval($user_id) ) {
			return;
		}

		update_field($meta_key, $value, 'user_' . intval($user_id));
	}

	public static function get_widget_meta( $widget_id, $meta_key ) {
		if ( !self::exists() || !$meta_key || !$widget_id ) {
			return;
		}

		return get_field($meta_key, 'widget_' . $widget_id);
	}

	# http://www.advancedcustomfields.com/resources/acf_add_options_page/
	public static function add_options_page( $params=array() ) {
		if ( !self::exists() || !function_exists('acf_add_options_page') ) {
			return;
		}

		$params = array_merge(array(
			'page_title'   => __('Options', 'crb'),
			'menu_title'   => __('Options', 'crb'),
			'menu_slug'    => 'acf-options',
			'capability'   => 'edit_posts',
			'parent_slug'  => '',
			'position'     => false,
			'icon_url'     => false,
			'redirect'     => true
		), $params);

		return acf_add_options_page($params);
	}

	public static function add_options_sub_page( $options_page, $params ) {
		if ( !self::exists() || !function_exists('acf_add_options_sub_page') ) {
			return;
		}

		$params = array_merge(array(
			'page_title' 	=> __('Sub Options Page', 'crb'),
			'menu_title' 	=> __('SubOptions', 'crb'),
			'parent_slug' 	=> $options_page['menu_slug'],
		), $params);

		acf_add_options_sub_page($params);
	}
}
