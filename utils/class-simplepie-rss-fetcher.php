<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/*
 * Sources
 *
 * https://core.trac.wordpress.org/browser/tags/4.0/src/wp-includes/class-simplepie.php#L0
 * http://simplepie.org/wiki/
 * http://code.tutsplus.com/articles/extending-simplepie-to-parse-unique-rss-feeds--net-3202
 * http://www.sitepoint.com/consuming-feeds-with-simplepie/
 *
 * @usage Crb_RSS_Parser::get_feed( 'http://wordpress-site.com' )
 */

class Crb_RSS_Parser {

	protected $feed_url = null;

	protected $feed;

	protected $beautyfied_feed = array();

	protected $cache_duration = 3600; # seconds

	protected $cache_directory;

	private function __construct( $url ) {
		if ( !$url ) {
			return;
		}

		if ( !strstr($url, '/feed/') ) {
			$url .= 'feed/';
		}

		$this->feed_url = $url;

		$this->set_cache_directory();

		$this->fetch_rss();
	}

	public static function get_feed( $url=null ) {
		try {
			$parser = new self( $url );
			return $parser->_get_feed();
		} catch (Exception $e) {
			throw new Exception( $e->getMessage() . ' ' . $e->getTraceAsString() );
		}
	}

	protected function set_cache_directory() {
		$upload_dir = wp_upload_dir();
		$this->cache_directory = $upload_dir['basedir'] . DIRECTORY_SEPARATOR . 'Crb_RSS_Parser';

		if ( !file_exists( $this->cache_directory ) ) {
			mkdir( $this->cache_directory , 0777, true );
		}

		return $this;
	}

	protected function fetch_rss() {
		include_once(ABSPATH . WPINC . '/class-simplepie.php');

		$feed = new SimplePie();
		$feed->set_feed_url( $this->feed_url );
		$feed->enable_cache(true);
		$feed->set_cache_location( $this->cache_directory );
		$feed->set_cache_duration( $this->cache_duration );
		$feed->init();
		$this->feed = $feed;

		return $this;
	}

	protected function _get_feed() {
		if ( !$this->feed ) {
			return;
		}

		foreach ($this->feed->get_items() as $item) {
			$this->_beautify_item( $item );
		}

		return $this->beautyfied_feed;
	}

	# http://simplepie.org/api/class-SimplePie_Item.html
	protected function _beautify_item( $feed_item ) {

		$author = $feed_item->get_author();

		// the following structure may vary depending of the RSS
		$this->beautyfied_feed[] = array(
			'title' => $feed_item->get_title(),
			'permalink' => $feed_item->get_permalink(),
			'date' => $feed_item->get_date(),
			'thumbnail' => $this->_get_item_thumbnails( $feed_item ),

			'author' => $author->get_name(),
			'author_link' => $author->get_link(),
			'author_email' => $author->get_email(),

			'description' => $feed_item->get_description(),
			'content' => $feed_item->get_content(),
		);

		return $this;
	}

	protected function _get_item_thumbnails( $item ) {
		$item_feed = $item->get_feed();
		if ( $thumbnail = $item_feed->get_image_url() ) {
			$images[] = $thumbnail;
		} else {
			preg_match_all('~src="([^"]*)"~i', $item->get_description(), $all_images);

			$images = array_map( function( $item ) {
				return preg_replace('~-\d+[xX]\d+(\.\w+)$~i', '$1', $item); # get full image url, not cropped one
			}, $all_images[1] );
		}

		return $images;
	}
}
