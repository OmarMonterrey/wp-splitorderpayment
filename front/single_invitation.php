<h3 class="woocommerce-order-details__title"><?php esc_html_e( 'Order details', 'woocommerce' ); ?></h3>
<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">
    <thead>
        <tr>
            <th class="woocommerce-table__product-name product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
            <th class="woocommerce-table__product-table product-total"><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
            $order_items = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) );
            $show_purchase_note    = $order->has_status( apply_filters( 'woocommerce_purchase_note_order_statuses', array( 'completed', 'processing' ) ) );
            foreach ( $order_items as $item_id => $item ) {
                $product = $item->get_product();
                wc_get_template(
                    'order/order-details-item.php',[
                        'order' => $order,
                        'item_id' => $item_id,
                        'item' => $item,
                        'show_purchase_note' => $show_purchase_note,
                        'purchase_note' => $product ? $product->get_purchase_note() : '',
                        'product' => $product,
                    ]);
            }
            do_action( 'woocommerce_order_details_after_order_table_items', $order );
        ?>
    </tbody>
    <tfoot>
        <?php foreach ( $order->get_order_item_totals() as $key => $total ): ?>
            <?php if( $key == 'payment_method' ) continue; ?>
            <tr>
                <th scope="row"><?php echo esc_html( $total['label'] ); ?></th>
                <th><?=wp_kses_post( $total['value'] );?></th>
            </tr>
        <?php endforeach; ?>
        <?php if ( $order->get_customer_note() ) : ?>
            <tr>
                <th><?php esc_html_e( 'Note:', 'woocommerce' ); ?></th>
                <th><?=wp_kses_post( nl2br( wptexturize( $order->get_customer_note() ) ) );?></th>
            </tr>
        <?php endif; ?>
        <tr>
            <th scope="row"><?=__('Payment Made', 'omsplitorderpayment')?></th>
            <th><?=wc_price($payment_done)?></th>
        </tr>
        <tr>
            <th scope="row"><?=__('Payment Remaining', 'omsplitorderpayment')?></th>
            <th><?=wc_price($payment_remaining)?></th>
        </tr>
    </tfoot>
</table>
<?php
    if( $order->is_paid() ){
        echo '<div class="omsplitorderpayment-invitation-notice">'.__('This order is marked as paid.', 'omsplitorderpayment').'</div>';
    } else {
        if(
            !empty($payment_data->amount) # Invitation paid
                &&
            ( $gateway->get_option('allow-multiple-payments') != 'yes' || $gateway->get_option('payment-type') == 0 )
            /** Multiple payments not allowed OR payment type is equal parts */
        ){
            echo '<div class="omsplitorderpayment-invitation-notice">'.__('This invitation is marked as paid.', 'omsplitorderpayment').'</div>';
        } else {
            include_once(__DIR__.'/parts/pay.php');
        }
    }

    if( $gateway->get_option('show-payment-list') == 'yes' ){
        $show_footer = false;
        include_once(__DIR__.'/parts/made.php');
    }
?>
