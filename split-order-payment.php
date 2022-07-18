<?php
    /**
     * 
     * Plugin Name: Split Order's Payment
     * Version: 0.0.1
     * Description: Allows your customers to split their payment between many of them.
     * Author: Omar Monterrey
     * Author URI: https://omarmonterrey.com/
     * Plugin URI: https://omarmonterrey.com/
     * 
     * Text Domain: omsplitorderpayment
     * Domain Path: /languages
    **/
    define('OM_SPLIT_ORDER_PAYMENT_VERSION', '0.0.1');
    add_action('init', function(){
        load_plugin_textdomain( 'omsplitorderpayment', false,  dirname( plugin_basename( __FILE__ ) ).'/languages');
    });
    include_once(__DIR__.'/init.php');