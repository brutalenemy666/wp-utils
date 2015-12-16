<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class CRB_WPML {

	public static $default_language = 'en';

	public static function exists(){
		return function_exists('icl_get_languages');
	}

	public static function get_languages( $skip_missing=0 ){
		if ( !self::exists() ) {
			return;
		}

		return icl_get_languages( 'skip_missing=' . $skip_missing );
	}

	public static function get_current_language(){
		global $sitepress;

		if ( !self::exists() ) {
			return self::$default_language;
		}

		return $sitepress->get_current_language();
	}

	public static function get_active_languages(){
		$return = array();

		if ( !self::exists() ) {
			return array(self::$default_language);
		}

		global $sitepress;
		$languages = $sitepress->get_active_languages();
		foreach ($languages as $language_key => $lang_data) {
			$return[] = $language_key;
		}

		return $return;
	}

	public static function switch_language( $lang=null ) {
		if ( !self::exists() ) {
			return;
		}

		if ( !$lang ) {
			return;
		}

		global $sitepress;
		if ( !empty($sitepress) ) {
			$sitepress->switch_lang(self::$default_language);
		}
	}
}
