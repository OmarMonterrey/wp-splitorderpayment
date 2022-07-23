<h3><?php esc_html_e('Payments Made', 'omsplitorderpayment'); ?></h3>
<table class="woocommerce-table">
    <thead>
        <tr>
            <th><?php esc_html_e('Payer Email', 'omsplitorderpayment'); ?></th>
            <th><?php esc_html_e('Payment Amount', 'omsplitorderpayment'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
            $count = 0;
            foreach( $payment_list as $single_payment ){
                if( $single_payment->amount == 0 ) continue;
                $count ++;
                echo "<tr>";
                    printf("<td>%s</td>", esc_html( $single_payment->email ));
                    printf("<td>%s</td>", esc_html( wc_price( $single_payment->amount ) ));
                echo "</tr>";
            }
        ?>
        <?php if( !$count ): ?>
            <tr>
                <td colspan="2"><?php esc_html_e('This order has no payments made.', 'omsplitorderpayment'); ?></td>
            </tr>
        <?php endif; ?>
    </tbody>
    <?php if( !empty($show_footer) ): ?>
        <tfoot>
            <tr>
                <th scope="row"><?php esc_html_e('Order Total', 'omsplitorderpayment'); ?></th>
                <th><?php esc_html_e( wc_price($order->get_total()) ); ?></th>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Payment Made', 'omsplitorderpayment'); ?></th>
                <th><?php esc_html_e( wc_price($payment_done) ); ?></th>
            </tr>
            <tr>
                <th scope="row"><?php esc_html_e('Payment Remaining', 'omsplitorderpayment'); ?></th>
                <th><?php esc_html_e( wc_price($payment_remaining) ); ?></th>
            </tr>
        </tfoot>
    <?php endif; ?>
</table>