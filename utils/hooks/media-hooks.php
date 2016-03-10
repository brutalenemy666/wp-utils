<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

# ------------------------------------------------------------
	# Add additional mime types / files support
# ------------------------------------------------------------

add_filter('upload_mimes', 'crb_upload_mimes');
function crb_upload_mimes( $existing_mimes=array() ) {

	// allow uploading vCard files
	$existing_mimes['vcf'] = 'text/x-vcard';

	return $existing_mimes;
}

add_filter( 'post_mime_types', 'crb_modify_post_mime_types' );
function crb_modify_post_mime_types( $post_mime_types ) {

	// filter by vCard
	$post_mime_types['text/x-vcard'] = array( __( 'vCards' ), __( 'Manage vCards' ), _n_noop( 'vCard <span class="count">(%s)</span>', 'vCards <span class="count">(%s)</span>' ) );

	return $post_mime_types;
}
