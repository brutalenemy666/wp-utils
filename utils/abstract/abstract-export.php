<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class Crb_Export {

	protected $headings = array(
		// label 1,
		// label 2
	);
	protected $values = array(
		// row 1 : array( value 1, value 2 ),
		// row 2 : array( value 1, value 2 )
	);

	protected $file;

	protected $filename;

	public function __construct($headings, $values) {
		$this->headings = $headings;
		$this->values = $values;

		$this->process_file();
	}

	protected abstract function process_file();

	public abstract function output();

	public function set_filename( $fielname = '' ) {
		$this->filename = $fielname;
	}

	public function get_filename() {
		$filename = $this->filename;
		if ( !$filename ) {
			$filename = sprintf(__('Export_%s', 'crb'), date('Y-m-d'));
		}

		return $filename;
	}
}
