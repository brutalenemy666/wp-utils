<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Crb_Woo_EmailTemplates {

	protected $order_id;
	protected $order;
	protected $emails;

	public function __construct($order_id=0) {
		$this->order_id = $order_id;
		$this->order = new WC_Order($this->order_id);

		$wc_emails = new WC_Emails();
		$this->emails = $wc_emails->get_emails();
	}

	protected function _get_mail_content( $template='' ) {
		if ( !$template ) {
			return;
		}

		$order = $this->order;
		$new_email = $this->emails[$template];
		$new_email->trigger($this->order_id);

		return $new_email->get_content();
	}

	// ------

	public function processing_order() {
		return $this->_get_mail_content('WC_Email_Customer_Processing_Order');
	}

	public function completed_order() {
		return $this->_get_mail_content('WC_Email_Customer_Completed_Order');
	}

	public function refunded_order() {
		return $this->_get_mail_content('WC_Email_Customer_Refunded_Order');
	}


	public function reset_password() {
		return $this->_get_mail_content('WC_Email_Customer_Reset_Password');
	}

	public function new_account() {
		return $this->_get_mail_content('WC_Email_Customer_New_Account');
	}


	public function invoice() {
		return $this->_get_mail_content('WC_Email_Customer_Invoice');
	}

	public function note() {
		return $this->_get_mail_content('WC_Email_Customer_Note');
	}


	public function new_order() {
		return $this->_get_mail_content('WC_Email_New_Order');
	}

	public function cancelled_order() {
		return $this->_get_mail_content('WC_Email_Cancelled_Order');
	}
}

// Preview the email messages for a specific order
add_action('init', function() {
	$allowed_templates = array(
		'processing_order',
		'completed_order',
		'refunded_order',
		'reset_password',
		'new_account',
		'invoice',
		'note',
		'new_order',
		'cancelled_order'
	);
	$order_id = !empty($_GET['crb_order_id']) ? intval($_GET['crb_order_id']) : 0;
	$security_key = !empty($_GET['crb_security_key']) && $_GET['crb_security_key']==='h7VCY9CvMjWr';
	$template_name = !empty($_GET['crb_mail']) ? trim($_GET['crb_mail']) : 'new_order';
	if ( !$security_key || !$order_id || !in_array($template_name, $allowed_templates) ) {
		return;
	}

	$mail_templates = new Crb_Woo_EmailTemplates($order_id);
	echo $mail_templates->$template_name();
	exit;
});
