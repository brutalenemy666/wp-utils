<?php
/*
 * Source : https://philipnewcomer.net/2014/06/filter-output-wordpress-widget/
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'dynamic_sidebar_params', 'crb_filter_dynamic_sidebar_params' );
function crb_filter_dynamic_sidebar_params( $sidebar_params ) {
	if ( is_admin() ) {
		return $sidebar_params;
	}

	global $wp_registered_widgets;
	$widget_id = $sidebar_params[0]['widget_id'];

	$wp_registered_widgets[ $widget_id ]['original_callback'] = $wp_registered_widgets[ $widget_id ]['callback'];
	$wp_registered_widgets[ $widget_id ]['callback'] = 'crb_custom_widget_callback_function';

	return $sidebar_params;
}

function crb_custom_widget_callback_function() {
	global $wp_registered_widgets;
	$original_callback_params = func_get_args();
	$widget_id = $original_callback_params[0]['widget_id'];

	$original_callback = $wp_registered_widgets[ $widget_id ]['original_callback'];
	$wp_registered_widgets[ $widget_id ]['callback'] = $original_callback;

	$widget_id_base = $wp_registered_widgets[ $widget_id ]['callback'][0]->id_base;

	if ( is_callable( $original_callback ) ) {

		ob_start();
			call_user_func_array($original_callback, $original_callback_params);
		$widget_output = ob_get_clean();

		// allow the user to hook and cange the widget output
		echo apply_filters( 'crb_widget_output', $widget_output, $widget_id_base, $widget_id );
	}
}
