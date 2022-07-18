<h3><?=__('Payments Made', 'omsplitorderpayment')?></h3>
<table class="woocommerce-table">
    <thead>
        <tr>
            <th><?=__('Payer Email', 'omsplitorderpayment')?></th>
            <th><?=__('Payment Amount', 'omsplitorderpayment')?></th>
        </tr>
    </thead>
    <tbody>
        <?php
            $count = 0;
            foreach( $payment_list as $single_payment ){
                if( $single_payment->amount == 0 ) continue;
                $count ++;
                echo "<tr>";
                    echo "<td>{$single_payment->email}</td>";
                    echo "<td>".wc_price($single_payment->amount)."</td>";
                echo "</tr>";
            }
        ?>
        <?php if( !$count ): ?>
            <tr>
                <td colspan="2"><?=__('This order has no payments made.', 'omsplitorderpayment')?></td>
            </tr>
        <?php endif; ?>
    </tbody>
    <?php if( !empty($show_footer) ): ?>
        <tfoot>
            <tr>
                <th scope="row"><?=__('Order Total', 'omsplitorderpayment')?></th>
                <th><?=wc_price($order->get_total())?></th>
            </tr>
            <tr>
                <th scope="row"><?=__('Payment Made', 'omsplitorderpayment')?></th>
                <th><?=wc_price($payment_done)?></th>
            </tr>
            <tr>
                <th scope="row"><?=__('Payment Remaining', 'omsplitorderpayment')?></th>
                <th><?=wc_price($payment_remaining)?></th>
            </tr>
        </tfoot>
    <?php endif; ?>
</table>