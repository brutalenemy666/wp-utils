<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Excerpt ending
 */
add_filter('excerpt_more', 'crb_excerpt_more');
function crb_excerpt_more() {
	return '...';
}

/**
 * Excerpt length
 */
add_filter('excerpt_length', 'crb_excerpt_length', 999);
function crb_excerpt_length() {
	return 55;
}

/**
 * Removes empty paragraphes from content when using shortcodes
 */
add_filter('the_content', 'crb_shortcode_empty_paragraph_fix');
function crb_shortcode_empty_paragraph_fix($content) {
	$array = array(
		'<p>['    => '[',
		']</p>'   => ']',
		']<br />' => ']',
		']<br>'   => ']',
	);

	$content = strtr($content, $array);

	return $content;
}

/**
 * add "popup-open" class to all anchored images
 */
add_filter( 'the_content', 'crb_add_popup_class_to_content_images', 90 );
function crb_add_popup_class_to_content_images( $content ) {
	$content = preg_replace( '~(<a)([^>]*>\s*<img[^>]+)(alignnone|alignleft|aligncenter|alignright)([^>]+>\s*</a>)~i', '$1 class="popup-open" $2 $3 $4', $content );

	return $content;
}

/**
 * unwrap the content images
 */
add_filter( 'the_content', 'crb_unwrap_content_images', 99 );
function crb_unwrap_content_images( $content ) {
	// IMAGE PARAGRAPH FIX
	// removed the empty paragraphs around the images if there is only an image inside
	$content = preg_replace( '~<p[^>]*>\s*(<img[^>]+>)\s*</p>~i', '$1', $content );
	$content = preg_replace( '~<p[^>]*>\s*(<a[^>]*>\s*<img[^>]+>\s*</a>)\s*</p>~i', '$1', $content );

	return $content;
}

/**
 * change the gallery cropping size (150 x 150 by default)
 * requires wpthumb
 */
add_filter( 'the_content', 'crb_modify_gallery_shortcote_markup', 999 );
function crb_modify_gallery_shortcote_markup( $content ) {
	// fix WP gallery image sizes
	$pattern = "/<dt\s*class='gallery-icon[^']*'\s*>\s*<a[^>]*href='([^']*)'>\s*(<img[^>]+>)\s*<\/a>\s*<\/dt>/";
	preg_match_all($pattern, $content, $gallery_items);
	foreach ($gallery_items[0] as $index => $gallery_item_html) {
		$full_image_url = $gallery_items[1][$index];
		$image_html = $gallery_items[2][$index];

		# http://andrew.hedges.name/experiments/aspect_ratio/
		# required size : 165 x 114, cropped size : 500 x 345 pixels
		# aspect ratio 55 : 38

		$new_image_html = '<img class="attachment-thumbnail" src="' . wpthumb( $full_image_url, 500, 345 ) . '" alt="" />';

		$new_gallery_item_html = str_replace( $image_html , $new_image_html, $gallery_item_html);

		# update new markup
		$content = str_replace($gallery_item_html, $new_gallery_item_html, $content);
	}

	return $content;
}
