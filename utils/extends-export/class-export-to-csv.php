<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class Crb_Export_To_CSV extends Crb_Abstract_Export {

	protected $separator = ';';

	// build CSV file content
	protected function process_file() {
		ob_start();

			// create file
			$file = fopen('php://output', 'w') or show_error(__("Can't open php://output", 'crb'));

			// add headings
			if ( $this->headings && !fputcsv($file, $this->headings, $this->separator) ) {
				_e("Can't write line - 0", 'crb');
		    }

		    foreach ($this->values as $row_index => $values) {
			    if ( !fputcsv($file, $values, $this->separator) ) {
					echo sprintf(__('Can\'t write line %d', 'crb'), $row_index);
			    }
		    }

		    fclose($file) or show_error(__("Can't close php://output", 'crb'));

		$this->file = ob_get_clean();
	}

	// print CSV file content
	public function output() {
		$csv_file_name = $this->get_filename();

		header("Content-type: application/csv"); # Declare the file type
		header("Content-Transfer-Encoding: binary");
		header("Content-Disposition: attachment; filename=" . $csv_file_name); # Export the generated CSV File
		header("Pragma: no-cache");
		header("Expires: 0");

		echo $this->file;
		exit;
	}
}

# ------------------------------------------------------------
	# Example: Export Userdata to CSV
# ------------------------------------------------------------
$headings = array(
	__('First Name', 'crb'),
	__('Last Name', 'crb'),
	__('Email', 'crb'),
);
$userdata = array(
	array('John', 'Doe', 'john_doe@example.com'),
	array('Linda', 'Doe', 'linda_doe@example.com'),
	array('Jim', 'Doe', 'jim_doe@example.com')
);

$export = new Crb_Export_To_CSV($headings, $userdata);
$export->set_filename('Users_Export_List-' . date('d-m-Y') . '.csv');
$export->output();
