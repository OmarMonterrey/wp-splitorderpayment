<?php
    $input_properties = (object) [
        'value' => '',
        'min' => '0.01',
        'max' => $payment_remaining,
        'readonly' => false
    ];
    if( $gateway->get_option('payment-type') == 0 ){
        $pending_invites = 0;
        foreach( $payment_list as $single_payment )
            if( $single_payment->amount == 0 ) $pending_invites++;
        $input_properties->value = (!$pending_invites) ? 0 : $payment_remaining / $pending_invites;
        $input_properties->readonly = true;
    }

?>
<h3><?php esc_html_e('Make Payment', 'omsplitorderpayment'); ?></h3>
<?php
    if( $gateway->get_option('payment-type') == 1 && $gateway->get_option('allow-multiple-payments') != 'yes' )
        printf('<p class="multiplepayments-notice">%s</p>',
            esc_html__('You will be able to make only one payment, make sure the amount is correct.', 'omsplitorderpayment')
        );
?>
<form method="post" id="omsplitorderpayment-pay">
    <input type="hidden" name="order" value="<?php echo esc_attr($order->get_id()); ?>">
    <input type="hidden" name="payment_key" value="<?php echo esc_attr($payment_key); ?>">
    <div class="input-field">
        <label for="amount"><?php esc_html_e('Amount', 'omsplitorderpayment'); ?></label>
        <input
            name="amount"
            type="number"
            value="<?php echo esc_attr($input_properties->value); ?>"
            <?php if($input_properties->readonly){echo 'readonly';} ?>
            min="<?php echo esc_attr($input_properties->min); ?>"
            max="<?php echo esc_attr($input_properties->max); ?>"
            required
            step="0.01"
        />
    </div>
    <div class="input-field">
        <button class="button alt"><?php esc_html_e('Make Payment', 'omsplitorderpayment'); ?></button>
    </div>
</form>