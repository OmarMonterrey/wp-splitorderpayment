<?php
    namespace OMSplitOrderPayment;
    $core = glob( __DIR__.'/core/*.class.php' );
    foreach( $core as $singleFile ){
        $file = basename($singleFile, ".class.php");
        $className = "OMSplitOrderPayment\\" . ucfirst( $file );
        if( !class_exists($className) )
            require( $singleFile );
    }

    class INIT{
        function __construct(){
            add_action('plugins_loaded', function(){
                if( !class_exists("WC_Payment_Gateway") ) return;
                include_once( __DIR__.'/core/method.wc.php' );
            });
            add_filter( 'woocommerce_payment_gateways', function($gateways){
                $gateways[] = "WC_Gateway_Split_Payment";
                return $gateways;
            } );

            add_filter( 'woocommerce_email_classes', function($classes){
                include_once( __DIR__.'/core/email.wc.php' );
                if( class_exists("WC_Split_Payment_Invitation") )
                    $classes['WC_Split_Payment_Invitation'] = new \WC_Split_Payment_Invitation();
                return $classes;
            });
            Publicpage::init();
            Ajax::init();
            Cart::init();
            Admin::init();

            
            add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
            
            //** PRHU Order Sync Integration */
            add_filter('osync_prhu_order_should', [$this, 'osync_prhu_order_should'], 10, 2);
        }

        function enqueue_scripts(){
            $base_url = plugin_dir_url( realpath(__FILE__) );
            wp_enqueue_style('splitpayment', $base_url . 'assets/css/front.css', [], OM_SPLIT_ORDER_PAYMENT_VERSION);
            wp_enqueue_script('splitpayment', $base_url . 'assets/js/front.js', [], OM_SPLIT_ORDER_PAYMENT_VERSION);
            wp_localize_script('splitpayment', 'omsplitorderpayment', [
                'email_label' => __('Invite Email', 'omsplitorderpayment'),
                'name_label' => __('Invite Name', 'omsplitorderpayment'). ' (' . __('Optional', 'omsplitorderpayment') . ')',
                'ajax_url' => admin_url('admin-ajax.php'),
                'ajax_action_invite' => Ajax::prefix('invite'),
                'ajax_action_pay' => Ajax::prefix('pay'),
                'ajax_nonce' => Ajax::nonce()
            ], );
        }
        function osync_prhu_order_should($should, $order_id){
            $parent_id = get_post_meta($order_id, '_om_split_payment_parent', true);
            if( !empty($parent_id) ) return false;
            return $should;
        }
    }

    new INIT();
