<?php
    namespace OMSplitOrderPayment;
    class Ajax{
        protected static $ajax_prefix = 'omsplitorderpayment_';
        protected static $nonce_key = 'omsplitorderpayment_ajax';
        public static function init(){ new static; }
        function __construct(){
            add_action('wp_ajax_' . static::prefix('invite'), [$this, 'invite']);
            add_action('wp_ajax_' . static::prefix('pay'), [$this, 'pay']);
        }
        public static function prefix( $name ){
            return static::$ajax_prefix . $name;
        }
        public static function nonce(){
            return wp_create_nonce( static::$nonce_key );
        }
        public function invite(){
            $response = [];
            try{
                if( !wp_verify_nonce($_POST['nonce'], static::$nonce_key) )
                    throw new \Exception(__('Invalid or expired request, please try again.', 'omsplitorderpayment'), 1);
                $order_id = sanitize_text_field( $_POST['order'] );
                $order_id = intval( $order_id );
                $order = new \WC_Order( $order_id );
                if( !$order || $order->get_user_id() != get_current_user_id() )
                    throw new \Exception(__('Invalid order.', 'omsplitorderpayment'), 1);

                $payment_list = get_post_meta( $order->get_id(), '_has_om_split_payment', true ) ?: [];
                $invited = [];
                foreach( $payment_list as $single ){
                    $invited[] = $single->email;
                }

                $emails = false;
                if( !empty($_POST['email']) && is_array($_POST['email']) ){
                    array_map('sanitize_text_field', $_POST['email']);
                }
                foreach($emails as $key => $email){
                    $email = filter_var(trim($email), FILTER_VALIDATE_EMAIL);
                    if( !$email )
                        throw new \Exception( sprintf(esc_html__('%s is not a valid email.' ,'omsplitorderpayment'), $email) , 1);
                    if( in_array($email, $invited) )
                        throw new \Exception( sprintf(esc_html__('%s is already invited.' ,'omsplitorderpayment'), $email) , 1);
                    $payment_key = md5( $email );
                    $payment_list[ $payment_key ] = (object) [
                        'email' => $email,
                        'name' => sanitize_text_field( $_POST['name'][$key] ),
                        'amount' => 0,
                        'mail_sent' => false
                    ];
                }
                update_post_meta($order->get_id(), '_has_om_split_payment', $payment_list);
                $mailer = \WC()->mailer();
                if( !empty($mailer) && !empty($mailer->emails) && !empty($mailer->emails['WC_Split_Payment_Invitation']))
                    $sent = $mailer->emails['WC_Split_Payment_Invitation']->trigger( $order->get_id() );
                    

                $response = ['success' => true];


            } catch( \Throwable $e ){
               $response = ['error' => $e->getMessage()]; 
            }
            echo json_encode($response);
            wp_die();
        }
        public function pay(){
            $response = [];
            try{
                if( !wp_verify_nonce($_POST['nonce'], static::$nonce_key) )
                    throw new \Exception(__('Invalid or expired request, please try again.', 'omsplitorderpayment'), 1);

                $order_id = intval( sanitize_text_field($_POST['order']) );
                $amount = floatval( sanitize_text_field($_POST['amount']) );
                $order = new \WC_Order( $order_id );
                if( !$order )
                    throw new \Exception(__('Invalid order.', 'omsplitorderpayment'), 1);
                if( $order->is_paid() )
                    throw new \Exception(__('This order is marked as paid.', 'omsplitorderpayment'), 1);

                $payment_list = get_post_meta( $order->get_id(), '_has_om_split_payment', true ) ?: [];
                $payment_key = sanitize_text_field($_POST['payment_key']);
                if( !isset($payment_list[$payment_key])  )
                    throw new \Exception(__('Invalid invitation.', 'omsplitorderpayment'), 1);

                $gateway = false;
                $gateways = \WC()->payment_gateways();
                foreach( $gateways->payment_gateways as $single ){
                    if( $single->id == 'om_split_payment' ){
                        $gateway = $single;
                        break;
                    }
                }
                if( !$gateway )
                    throw new Exception(__('Payment method not instantiated.', 'omsplitorderpayment'), 1);

                $payment_data = $payment_list[$payment_key];
                if(
                    !empty($payment_data->amount) # Invitation paid
                        &&
                    ( $gateway->get_option('allow-multiple-payments') != 'yes' || $gateway->get_option('payment-type') == 0 )
                    /** Multiple payments not allowed OR payment type is equal parts */
                )
                    throw new Exception(__('This invitation is marked as paid.', 'omsplitorderpayment'), 1);

                Cart::add( compact("order", "amount", "payment_list", "payment_data", "payment_key", "gateway") );
    
                $response = ['goto' => wc_get_checkout_url()];


            } catch( \Throwable $e ){
               $response = ['error' => $e->getMessage()]; 
            }
            echo json_encode($response);
            wp_die();
        }
    }