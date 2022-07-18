<?php
    class WC_Gateway_Split_Payment extends WC_Payment_Gateway{
        function __construct(){
            $this->id = "om_split_payment";
            $this->icon = "";
            $this->has_fields = true;
            $this->method_title = __('Split Payment', 'omsplitorderpayment');
            $this->method_description = __('Allow customers to split their payment between many of them.', 'omsplitorderpayment');
            $this->description = __('You will be able to invite payers once your order is placed.', 'omsplitorderpayment');

            $this->init_form_fields();
            $this->init_settings();

            $this->title = $this->get_option( 'title' );

            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options'] );
            add_action( 'woocommerce_order_details_before_order_table', [$this, 'show_payment_box'] );
            
            
        }
        function init_form_fields(){
            $this->form_fields = [
                'enabled' => [
                    'title' => __( 'Enable/Disable', 'omsplitorderpayment' ),
                    'type' => 'checkbox',
                    'label' => __( 'Allow Split Payment', 'omsplitorderpayment' ),
                    'default' => 'yes'
                ],
                'allow-multiple-payments' => [
                    'title' => __( 'Allow Multiple Payments', 'omsplitorderpayment' ),
                    'type' => 'checkbox',
                    'label' => __( 'Allow invites to pay multiple times with a single invitation, (only works for custom amounts payments)', 'omsplitorderpayment' ),
                    'default' => 'yes',
                    'description' => __('When unmarked, only one payment will be allowed per invite.', 'omsplitorderpayment'),
                    'desc_tip' => true
                ],
                'show-payment-list' => [
                    'title' => __( 'Payment List Privacy', 'omsplitorderpayment' ),
                    'type' => 'checkbox',
                    'label' => __( 'Show payment list to all invited customers', 'omsplitorderpayment' ),
                    'default' => 'yes',
                    'description' => __('When unmarked, only the customer that places the order will see the payment list.', 'omsplitorderpayment'),
                    'desc_tip' => true
                ],
                'title' => [
                    'title' => __( 'Title', 'omsplitorderpayment' ),
                    'type' => 'text',
                    'description' => __( 'This controls the name the users see for this payment method.', 'omsplitorderpayment' ),
                    'default' => __( 'Split Payment', 'omsplitorderpayment' ),
                    'desc_tip'      => true,
                ],
                'payment-type' => [
                    'title' => __( 'Payment Type', 'omsplitorderpayment' ),
                    'type' => 'select',
                    'default' => 0,
                    'options' => [
                        0 => 'Equal Parts',
                        1 => 'Allow Custom Amount'
                    ]
                ],
            ];
        }
        function process_payment( $order_id ){
            global $woocommerce;
            if( !is_user_logged_in() ){
                wc_add_notice( __('You need to be logged in to be able to split payments.', 'omsplitorderpayment' ), 'error');
                return;
            }
            $order = new WC_Order($order_id);
            $order->update_status('on-hold', __('Waiting split payment', 'omsplitorderpayment'));
            update_post_meta($order_id, '_is_om_split_payment', 1);
            $U = wp_get_current_user();
            $email = get_user_meta($U->ID, 'billing_email', true) ?: $U->user_email;
            $name = get_user_meta($U->ID, 'billing_first_name', true) ?: $U->user_nicename;
            $payment_key = md5( $email );
            update_post_meta($order_id, '_has_om_split_payment', [
                $payment_key => (object) [
                    'email' => $email,
                    'name' => $name,
                    'amount' => 0,
                    'mail_sent' => false
                ]
            ]);
            $mailer = \WC()->mailer();
            if( !empty($mailer) && !empty($mailer->emails) && !empty($mailer->emails['WC_Split_Payment_Invitation']))
                    $mailer->emails['WC_Split_Payment_Invitation']->trigger( $order_id );

            $woocommerce->cart->empty_cart();

            return [
                'result' => 'success',
                'redirect' => $this->get_return_url( $order )
            ];
        }
        function show_payment_box($order){
            if( is_scalar($order) )
                $order = new WC_Order($order);
            $is_split_payment = get_post_meta( $order->get_id(), '_is_om_split_payment', true ) == 1;
            if( !$is_split_payment ) return;


            $payment_list = get_post_meta( $order->get_id(), '_has_om_split_payment', true ) ?: [];
            $payment_done = 0;
            foreach( $payment_list as $single_payment )
                $payment_done += $single_payment->amount;
            if( isset($_POST['_cancel_invitation']) ){
                $invitation = $payment_list[ $_POST['_cancel_invitation'] ];
                if( !empty($invitation) && $invitation->amount == 0 ){
                    unset( $payment_list[ $_POST['_cancel_invitation'] ] );
                    update_post_meta( $order->get_id(), '_has_om_split_payment', $payment_list );
                }
            }
            $payment_remaining = $order->get_total() - $payment_done;

            include_once(__DIR__.'/../front/box.php');
        }

    }