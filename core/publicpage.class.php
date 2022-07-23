<?php
    namespace OMSplitOrderPayment;
    class Publicpage{
        protected static $slug = 'split-payment-invitation';
        protected static $shortcode = 'omsplitorderpayment_invitation';
        public static function init(){ new static; }
        function __construct(){
            add_action('init', [$this, 'create_page']);
            add_shortcode( static::$shortcode, [$this, 'show_page'] );
        }
        public function create_page(){
            $exists = get_page_by_path( static::$slug );
            if( $exists ) return $exists;
            $ID = wp_insert_post([
                'post_status' => 'publish',
                'post_content' => '[' . static::$shortcode . ']',
                'post_title' => __('Split Payment Invitation', 'omsplitorderpayment'),
                'post_name' => static::$slug,
                'comment_status' => 'closed',
                'post_author' => 1,
                'post_type' => 'page'
            ]);
            return get_post( $ID );
        }
        public static function getLink( $order_id, $payment_key ){
            return get_home_url(null, "/" . static::$slug . "/?order_id={$order_id}&payment={$payment_key}");
        }
        public function show_page(){
            $order_id = sanitize_text_field($_GET['order_id']);
            $order_id = intval( $order_id );
            $order = new \WC_Order( $order_id );
            if( !$order )
                return $this->show_error( __('Invalid order.', 'omsplitorderpayment') );

            $payment_list = get_post_meta( $order->get_id(), '_has_om_split_payment', true ) ?: [];
            $payment_key = sanitize_text_field($_GET['payment']);
            if( !isset($payment_list[ $payment_key ]) )
                return $this->show_error( __('Invalid invitation.', 'omsplitorderpayment') );
            
            
            $payment_done = 0;
            foreach( $payment_list as $single_payment )
                $payment_done += $single_payment->amount;
            $payment_remaining = $order->get_total() - $payment_done;
   
            $gateway = false;
            $gateways = \WC()->payment_gateways();
            foreach( $gateways->payment_gateways as $single ){
                if( $single->id == 'om_split_payment' ){
                    $gateway = $single;
                    break;
                }
            }
            if( !$gateway )
                return $this->show_error( __('Payment method not instantiated.', 'omsplitorderpayment') );

            $payment_data = $payment_list[ $payment_key ];

            ob_start();
            include_once( __DIR__.'/../front/single_invitation.php' );
            $content = ob_get_clean();
            ob_end_flush();
            return $content;
        }
        protected function show_error($str){
            return '<div id="omsplitorderpayment-invitation"><div class="omsplitorderpayment-error">'.$str.'</div></div>';
        }
    }