<?php
if( !defined('ABSPATH') ) exit;

if( !class_exists('WC_Email') ) return;
class WC_Split_Payment_Invitation extends WC_Email {
	function __construct() {
		$this->id          = 'wc_split_payment_invitation';
		$this->title       = __( 'Split Payment Invitation', 'omsplitorderpayment' );
		$this->description = __( 'An email sent to customers when invited to split payment.', 'omsplitorderpayment' );
		$this->customer_email = true;
		$this->heading     = __( 'Split Payment Invitation', 'omsplitorderpayment' );
		$this->subject     = sprintf( _x( '[%s] Split Payment Invitation', 'default email subject split payments invitations', 'omsplitorderpayment' ), '{blogname}' );
		$this->template_html  = 'split-payment-invitation.php';
		$this->template_plain = 'split-payment-invitation.plain.php';
		$this->template_base  = realpath(__DIR__.'/../email/') . '/';
		$this->payment_key = '';
        add_action( 'woocommerce_split_order_payment_notification', [$this, 'trigger'] );
		parent::__construct();
	}
    public function get_content_html() {
		return wc_get_template_html( $this->template_html, array(
			'order'         => $this->object,
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => false,
			'plain_text'    => false,
			'email'			=> $this,
			'payment_link' => \OMSplitOrderPayment\Publicpage::getLink( $this->object->get_id(), $this->payment_key )
		), '', $this->template_base );
	}
	public function get_content_plain() {
		return wc_get_template_html( $this->template_plain, array(
			'order'         => $this->object,
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => false,
			'plain_text'    => true,
			'email'			=> $this,
			'payment_link' => \OMSplitOrderPayment\Publicpage::getLink( $this->object->get_id(), $this->payment_key )
		), '', $this->template_base );
	}
    function trigger( $order_id ) {
		$this->object = wc_get_order( $order_id );
		if ( version_compare( '3.0.0', WC()->version, '>' ) ) {
			$order_email = $this->object->billing_email;
		} else {
			$order_email = $this->object->get_billing_email();
		}
		if ( ! $this->is_enabled() ) {
			return;
		}
        $payment_list = get_post_meta( $order_id, '_has_om_split_payment', true ) ?: [];
		foreach($payment_list as $key => $single_payment){
			if( !empty($single_payment->mail_sent) ) continue;

			$this->payment_key = $key;
			$this->send( $single_payment->email, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
			$payment_list[$key]->mail_sent = true;
		}
		update_post_meta( $order_id, '_has_om_split_payment', $payment_list );
	}
}