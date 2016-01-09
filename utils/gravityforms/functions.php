<?php

/**
 * Disable the confirmation anchor
 * http://www.gravityhelp.com/documentation/page/Gform_confirmation_anchor
 */
add_filter('gform_confirmation_anchor', '__return_false');

/**
 * Display an "Add Form" button above rich text fields on all custom field containers
 * http://www.gravityhelp.com/documentation/page/Gform_display_add_form_button
 */
add_filter('gform_display_add_form_button', '__return_true');

/**
 * Get rid of the main error styles, however, keep the rest
 * replaces gform_wrapper to crb_gform_wrapper
 */
add_filter('gform_get_form_filter', 'crb_gform_replace_wrapper_class', 10, 2);
function crb_gform_replace_wrapper_class($form_string, $form) {
	return preg_replace(
		'~(class=["\'][^"\']*)gform_wrapper([^"\']*["\'])~i',
		'$1crb_gform_wrapper$2',
		$form_string
	);
}

/**
 * Get rid of the fields error styles, however, keep the regular field styles
 */
add_filter('gform_field_css_class', 'crb_gforms_change_field_error_class', 10, 3);
function crb_gforms_change_field_error_class($classes, $field, $form) {
	$classes = str_replace('gfield_error', 'crb_gfield_error', $classes);
	return $classes;
}

/**
 * Add a field type class to each field
 */
add_filter('gform_field_css_class', 'crb_gforms_add_field_type_class', 10, 3);
function crb_gforms_add_field_type_class($classes, $field, $form) {
	$classes = ' gfield-' . $field['type'] . ' gfield-' . $field['size'];
	return $classes;
}

/**
 * Get all available gravity forms
 */
function crb_get_forms() {
	$forms = array();

	if ( !class_exists('RGFormsModel') ) {
		return;
	}

	$available_forms = RGFormsModel::get_forms(null, 'title');
	foreach($available_forms as $form) {
		$forms[$form->id] = $form->title;
	}

	return $forms;
}
