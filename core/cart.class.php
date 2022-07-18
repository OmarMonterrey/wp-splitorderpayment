<?php
    namespace OMSplitOrderPayment;
    class Cart{
        protected static $slug = 'split-payment-product';
        public static function init(){ return new static; }
        function __construct(){
            add_filter('woocommerce_cart_item_price', [$this, 'cart_item_price'], 10, 3);
            add_filter('woocommerce_cart_item_name', [$this, 'cart_item_name'], 10, 3);
            add_action('woocommerce_before_calculate_totals', [$this, 'before_calculate_totals'], 10, 1);
            add_action('woocommerce_checkout_create_order_line_item', [$this, 'create_order_line_item'], 10, 4);
            add_action('woocommerce_payment_complete', [$this, 'payment_complete'], 10, 1);
            add_action('woocommerce_order_status_completed', [$this, 'payment_complete'], 10, 1);
            add_action('woocommerce_order_status_changed', [$this, 'status_changed'], 10, 1);

            add_filter( 'woocommerce_available_payment_gateways', [$this, 'available_gateways'] );
        }
        public static function get_product_id(){
            $exists = get_page_by_path( static::$slug, \OBJECT, 'product');
            if( $exists ) return $exists->ID;
            $ID = wp_insert_post([
                'post_status' => 'private',
                'post_title' => __('Split Payment Product', 'omsplitorderpayment'),
                'post_content' => __('Split Payment Product', 'omsplitorderpayment'),
                'post_name' => static::$slug,
                'post_author' => 1,
                'post_type' => 'product'
            ]);
            wp_set_object_terms( $ID, 'simple', 'product_type' );
            update_post_meta( $ID, '_stock_status', 'instock');
            update_post_meta( $ID, 'total_sales', 0 );
            update_post_meta( $ID, '_downloadable', 'no' );
            update_post_meta( $ID, '_virtual', 'yes' );
            update_post_meta( $ID, '_regular_price', 0 );
            update_post_meta( $ID, '_sku', 'splitpaymentproduct' );
            update_post_meta( $ID, '_price', 0 );
            update_post_meta( $ID, '_manage_stock', 'no' );
            update_post_meta( $ID, '_stock', '' );
            return $ID;
        }
        public static function add($args){
            // Don't use extract for security
            $order = $args['order'];
            $amount = $args['amount'];
            $payment_list = $args['payment_list'];
            $payment_data = $args['payment_data'];
            $payment_key = $args['payment_key'];
            $gateway = $args['gateway'];

            $payment_done = 0;
            foreach( $payment_list as $single_payment )
                $payment_done += $single_payment->amount;
            $payment_remaining = $order->get_total() - $payment_done;

            if( $gateway->get_option('payment-type') == 0 ){
                $pending_invites = 0;
                foreach( $payment_list as $single_payment )
                    if( $single_payment->amount == 0 ) $pending_invites++;
                $equal_part = (!$pending_invites) ? 0 : $payment_remaining / $pending_invites;
                if( $amount != $equal_part ){
                    throw new \Exception( sprintf(__('Amount should be equal to %s', 'omsplitorderpayment'), wc_price($equal_part)), 1);
                }
            }


            if( $amount > $payment_remaining )
                throw new \Exception( sprintf(__('Amount should be less than %s', 'omsplitorderpayment'), wc_price($payment_remaining)), 1);
            if( $amount < 0.01 )
                throw new \Exception( sprintf(__('Amount should be more than %s', 'omsplitorderpayment'), wc_price(0.01)), 1);
            $product_id = static::get_product_id();
            $cart = \WC()->cart;
            $cart->empty_cart();
            $cart->add_to_cart( $product_id, 1, 0, 0, [
                'omsplitorderpayment_name' => sprintf(__('Split payment for Order #%s'), $order->get_id()),
                'omsplitorderpayment_price' => $amount,
                'omsplitorderpayment_order_id' => $order->get_id(),
                'omsplitorderpayment_payment_key' => $payment_key,
            ] );
            return true; 
        }
        function cart_item_name( $current, $item, $key ){
            if( isset($item['omsplitorderpayment_name']) )
                return wp_kses_post( $item['omsplitorderpayment_name'] );
            return $current;
        }
        function cart_item_price( $current, $item, $key ){
            if( isset($item['omsplitorderpayment_price']) )
                return wc_price( $item['omsplitorderpayment_price'] );
            return $current;
        }
        function before_calculate_totals( $cart ){
            if( is_admin() && !defined('DOING_AJAX') ) return;
            if( did_action( 'woocommerce_before_calculate_totals' ) >= 2 ) return;
            foreach( $cart->get_cart() as $item ){
                if( !isset($item['omsplitorderpayment_price']) ) continue;
                $item['data']->set_price( $item['omsplitorderpayment_price'] );
            }
        }
        function create_order_line_item($item, $key, $values, $order){
            if( empty($values['omsplitorderpayment_payment_key']) ) return;
            $item->add_meta_data('_omsplitorderpayment_payment_key', $values['omsplitorderpayment_payment_key']);
            $item->add_meta_data('_omsplitorderpayment_order_id', $values['omsplitorderpayment_order_id']);
        }
        function payment_complete( $order_id ){
            $processed = get_post_meta( $order_id, '_om_split_payment_processed', true ) ?: false;
            if( $processed ) return;
            update_post_meta( $order_id, '_om_split_payment_processed', true );
            $order = new \WC_Order( $order_id );
            if( !$order ) return;
            $items = $order->get_items();
            foreach($items as $item){
                $meta_data = $item->get_meta_data();
                $parent_id = false;
                $payment_key = false;
                foreach( $meta_data as $meta ){
                    $data = $meta->get_data();
                    if( $data['key'] == '_omsplitorderpayment_order_id' )
                        $parent_id = $data['value'];
                    else if( $data['key'] == '_omsplitorderpayment_payment_key' )
                        $payment_key = $data['value'];
                }
                if( !$parent_id ) continue;
                $payment_list = get_post_meta( $parent_id, '_has_om_split_payment', true ) ?: [];
                if( !isset($payment_list[ $payment_key ]) ) continue;
                $payment_list[ $payment_key ]->amount += floatval( $item->get_total() );
                update_post_meta($parent_id, '_has_om_split_payment', $payment_list);
                $this->check_paid( $parent_id );
            }
        }
        function status_changed($order_id){
            $processed = get_post_meta( $order_id, '_om_split_payment_parent_checked', true ) ?: false;
            if( $processed ) return;
            update_post_meta( $order_id, '_om_split_payment_parent_checked', true );
            $order = new \WC_Order( $order_id );
            if( !$order ) return;
            $items = $order->get_items();
            foreach($items as $item){
                $meta_data = $item->get_meta_data();
                foreach( $meta_data as $meta ){
                    $data = $meta->get_data();
                    if( $data['key'] == '_omsplitorderpayment_order_id' ){
                        update_post_meta($order_id, '_om_split_payment_parent', $data['value']);
                        break 2;
                    }
                }
            }

        }
        function check_paid($order_id){
            $order = new \WC_Order( $order_id );
            if( $order->is_paid() ) return;
            $payment_list = get_post_meta($order_id, '_has_om_split_payment', true) ?: [];
            $paid = 0;
            foreach( $payment_list as $single_payment )
                $paid += $single_payment->amount;
            if( $paid < $order->get_total() ) return; # Not paid
            $order->set_date_paid(gmdate('Y-m-d H:i:s'));
            $order->set_status('completed');
            $order->save();
        }
        function available_gateways($gateways){
            if( is_admin() ) return;
            if( !isset($gateways['om_split_payment']) ) return $gateways;
            $cart = \WC()->cart;
            $content = $cart->get_cart();
            foreach( $content as $item ){
                if( !empty($item['omsplitorderpayment_order_id']) ){
                    unset($gateways['om_split_payment']);
                    break;
                }
            }
            return $gateways;
        }
    }