<?php
    namespace OMSplitOrderPayment;
    class Admin{
        public static function init(){ new static; }
        function __construct(){
            add_filter('parse_query', [$this, 'hide_child_orders']);
            add_action('woocommerce_admin_order_data_after_billing_address', [$this, 'show_childrens']);
        }
        public function hide_child_orders( $query ){
            global $pagenow;
            if(!is_admin() || !$pagenow == 'edit.php' || !isset($_GET['post_type'])  || $_GET['post_type'] != 'shop_order') return;
            $meta_query = is_array( $query->get('meta_query') ) ? $query->get('meta_query') : [];
            $meta_query[] = [
                'key' => '_om_split_payment_parent',
                'compare' => 'NOT EXISTS'
            ];
            $query->set('meta_query', $meta_query);
        }
        public function show_childrens($order){
            $parent_id = get_post_meta($order->get_id(), '_om_split_payment_parent', true);
            if( $parent_id ){
                    printf("<h3>%s</h3>", esc_html__('Parent for this Order', 'omsplitorderpayment'));
                    printf('<a href="%s">Order #%s</a>',
                        esc_attr(get_edit_post_link($parent_id)),
                        esc_html__($parent_id)
                    );
                return;
            }
            $Q = new \WP_Query([
                'post_type' => 'shop_order',
                'post_status' => 'any',
                'meta_query' => [
                    [
                        'key' => '_om_split_payment_parent',
                        'value' => $order->get_id(),
                        'compare' => '='
                    ]
                ],
                'fields' => 'ids'
            ]);
            if( count($Q->posts) ){
                printf('<h3 style="margin-top:1em;">%s</h3>', esc_html__('Split Payments for this Order', 'omsplitorderpayment'));
                foreach( $Q->posts as $ID ){
                    printf('<a href="%s">Order #%s</a>',
                        esc_attr(get_edit_post_link($ID)),
                        esc_html__($ID)
                    );
                }
            }
        }
    }