<?php

do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p><?php printf( esc_html__( 'Hi %s,', 'woocommerce' ), esc_html( $order->get_billing_first_name() ) ); ?></p>

<p>
<?php
	printf(
		wp_kses(
			/* translators: %1$s Site title, %2$s Invitation link */
			__( 'You\'ve been invited to pay part of an order in %1$s. Click here to make your payment: %2$s', 'omsplitorderpayment' ),
            [
                'a' => [
                    'href' => []
                ]
            ]
		),
		esc_html( get_bloginfo( 'name', 'display' ) ),
		'<a href="'.$payment_link.'">' . esc_html__( 'Pay for this order', 'woocommerce' ) . '</a>'
	);

?>
</p>
<?php
do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );
if( !empty($additional_content) ){
	echo wp_kses_post(wpautop(wptexturize($additional_content)));
}
do_action( 'woocommerce_email_footer', $email );