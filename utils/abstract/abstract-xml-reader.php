<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class Crb_XML_Reader {

	protected $dom;
	protected $file;
	protected $xml;

	public function __construct( $file ) {
		$this->file = $file;
		$this->_load_file();
		$this->_fetch_data();
	}

	/*
	protected function _get_xml_data() {
		$entries = array();
		foreach ($this->xml as $xml_entry) {
			$entries[] = array(
				// parsed data from $xml_entry
			);
		}
		return $entries;
	}
	 */
	abstract protected function _get_xml_data();

	public static function read( $file ) {
		if ( !file_exists($file) ) {
			throw new Exception('XML fIle does not exists.');
		}

		$called_class = get_called_class();

		$parser = new $called_class($file);
		return $parser->_get_xml_data();
	}

	protected function _load_file() {
		$this->dom = new DOMDocument;
		$old_value = null;

		if ( function_exists('libxml_disable_entity_loader') ) {
			$old_value = libxml_disable_entity_loader(true);
		}

		$content = file_get_contents($this->file);

		$success = $this->dom->loadXML($content);
		if ( !is_null($old_value) ) {
			libxml_disable_entity_loader($old_value);
		}

		if ( !$success || isset($this->dom->doctype) ) {
			$errors = print_r(libxml_get_errors(), true);
			throw new Exception($errors);
		}

		return $this;
	}

	protected function _fetch_data() {
		$this->xml = simplexml_import_dom($this->dom);

		if ( !$this->xml ) {
			$errors = print_r(libxml_get_errors(), true);
			throw new Exception($errors);
		}

		return $this;
	}
}
