`<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', 'crb_register_post_type' );
function crb_register_post_type() {
	$labels = array(
		'name'                => __( 'Plural Name', 'crb' ),
		'singular_name'       => __( 'Singular Name', 'crb' ),
		'add_new'             => _x( 'Add New Singular Name', 'crb', 'crb' ),
		'add_new_item'        => __( 'Add New Singular Name', 'crb' ),
		'edit_item'           => __( 'Edit Singular Name', 'crb' ),
		'new_item'            => __( 'New Singular Name', 'crb' ),
		'view_item'           => __( 'View Singular Name', 'crb' ),
		'search_items'        => __( 'Search Plural Name', 'crb' ),
		'not_found'           => __( 'No Plural Name found', 'crb' ),
		'not_found_in_trash'  => __( 'No Plural Name found in Trash', 'crb' ),
		'parent_item_colon'   => __( 'Parent Singular Name:', 'crb' ),
		'menu_name'           => __( 'Plural Name', 'crb' ),
	);

	$args = array(
		'labels'              => $labels,
		'hierarchical'        => false,
		'description'         => 'description',
		'taxonomies'          => array(),
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_admin_bar'   => true,
		'menu_position'       => null,
		'menu_icon'           => 'dashicons-clipboard',
		'show_in_nav_menus'   => true,
		'publicly_queryable'  => true,
		'exclude_from_search' => false,
		'has_archive'         => true,
		'query_var'           => true,
		'can_export'          => true,
		'rewrite'             => true,
		'capability_type'     => 'post',
		'supports'            => array('title', 'editor')
	);

	register_post_type( 'post_type_name', $args );
}

add_filter('post_type_link', 'crb_post_type_link', 10, 3);
function crb_post_type_link($permalink, $post, $leavename) {
	if ( !gettype($post)==='post' ) {
		return $permalink;
	}

	switch ($post->post_type) {
		case 'post_type_name':
			$post_name = $post->post_name;
			if ( is_admin() ) {
				$post_name = '%postname%';
			}

			$permalink = get_home_url() . '/' . $post_name . '/';
			break;
	}

	return $permalink;
}

add_action('pre_get_posts', 'crb_pre_get_posts');
function crb_pre_get_posts($query) {
	global $wpdb;

	if( !$query->is_main_query() ) {
		return;
	}

	$post_name = $query->get('name');
	if ( !$post_name ) {
		return;
	}

	// check if there is a Singular Name entry with the same post name
	$mysql_query = "SELECT * FROM `$wpdb->posts` WHERE `post_name` = '%s' AND `post_type` = 'post_type_name' LIMIT 1"
	$result = $wpdb->get_row($wpdb->prepare($mysql_query, $post_name));

	if ( !$result ) {
		foreach (array('urlencode', 'urldecode') as $func_name) {
			$tmp_post_name = $func_name($post_name);

			// check if there is a Singular Name entry with the same post name
			$result = $wpdb->get_row($wpdb->prepare($mysql_query, $tmp_post_name));
			if ( $result ) {
				break;
			}
		}
	}

	if ( !$result ) {
		return;
	}

	// if such post exists then overwrite the main query
	$query->set('post_type_name', $result->post_name);
	$query->set('post_type', $result->post_type);
	$query->is_single = true;
	$query->is_page = false;
}

// ensure we have unique post names across pages, posts and blog posts
add_filter('wp_unique_post_slug', 'crb_wp_unique_post_slug', 10, 6);
function crb_wp_unique_post_slug($slug, $post_ID, $post_status, $post_type, $post_parent, $original_slug) {
	global $wpdb;

	$check_sql = "SELECT post_name FROM $wpdb->posts 
		WHERE post_type IN ('post_type_name', 'page') 
		AND post_name = %s 
		AND post_parent = %d
		AND ID != %d 
		LIMIT 1";
	$post_name_check = $wpdb->get_var( $wpdb->prepare( $check_sql, $slug, $post_parent, $post_ID ) );

	if ( !$post_name_check ) {
		return $slug;
	}

	$suffix = 2;
	do {
		$alt_post_name = _truncate_post_slug( $slug, 200 - ( strlen( $suffix ) + 1 ) ) . "-" . $suffix;
		$post_name_check = $wpdb->get_var( $wpdb->prepare( $check_sql, $alt_post_name, $post_parent, $post_ID ) );
		$suffix++;
	} while ( $post_name_check );
	$slug = $alt_post_name;

	return $slug;
}