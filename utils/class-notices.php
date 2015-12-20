<?php
/*
 * https://codex.wordpress.org/Plugin_API/Action_Reference/admin_notices
 *
 * Crb_Notice::add_notice( 'notice message', 'notice type: updated || error || notice-warning' );
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Crb_Notice {

	protected $notice_text = '';
	protected $notice_type = 'updated'; // updated || error || notice-warning

	private function __construct() {

	}

	public static function add_notice($notice_text='', $type='updated') {
		$notice = new self();
		$notice->notice_text = $notice_text;
		$notice->notice_type = $type;
		$notice->_add_notice();
	}

	protected function _add_notice() {
		$this->check_notice_type();

		if ( $this->notice_text ) {
			add_action('admin_notices', array($this, 'print_notice'));
		}
	}

	protected function check_notice_type() {
		if ( !in_array($this->notice_type, array('updated', 'error', 'notice-warning')) ) {
			$this->notice_type = 'updated';
		}

		return $this;
	}

	public function print_notice() {
		$class = $this->notice_type;
		if ( $class==='notice-warning' ) {
			$class .= ' notice';
		}

		echo '<div class="' . esc_attr($class) . '"><p>' . $this->notice_text . '</p></div>';
	}
}
