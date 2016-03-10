<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

# ------------------------------------------------------------
	# Yes / No array options
# ------------------------------------------------------------

function crb_yes_no() {
	return array(
		'Yes' => __('Yes', 'crb'),
		'No' => __('No', 'crb')
	);
}

function crb_no_yes() {
	return array_reverse(crb_yes_no());
}
