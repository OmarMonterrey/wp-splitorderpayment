<h3><?php esc_html_e('Invitations', 'omsplitorderpayment'); ?></h3>
<table class="woocommerce-table">
    <thead>
        <tr>
            <th><?php esc_html_e('Payer Name', 'omsplitorderpayment'); ?></th>
            <th><?php esc_html_e('Payer Email', 'omsplitorderpayment'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
            $count = 0;
            foreach( $payment_list as $payment_key => $single_payment ){
                if( $single_payment->amount != 0 ) continue;
                $count ++;
                echo "<tr>";
                    printf("<td>%s</td>", esc_html( $single_payment->name ));
                    echo "<td>";
                        echo esc_html( $single_payment->email );
                        include(__DIR__.'/cancel_invitation.php');
                    echo "</td>";
                echo "</tr>";
            }
        ?>
        <?php if( !$count ): ?>
            <tr>
                <td colspan="2"><?php esc_html_e('This order has no invitations.', 'omsplitorderpayment'); ?></td>
            </tr>
        <?php endif; ?>
    </tbody>

</table>
<h3><?php esc_html_e('Send Payment Invitations', 'omsplitorderpayment'); ?></h3>
<form method="post" id="omsplitorderpayment-invite">
    <input type="hidden" name="order" value="<?php esc_html_e($order->get_id()); ?>">
    <div id="invite-list">
        <div class="single-invite">
            <div class="input-field">
                <label for="email_1"><?php esc_html_e('Invite Email', 'omsplitorderpayment'); ?></label>
                <input type="email" name="email[]" id="email_1" required/>
            </div>
            <div class="input-field">
                <label for="name_1"><?php esc_html_e('Invite Name', 'omsplitorderpayment'); ?> (<?php esc_html_e('Optional', 'omsplitorderpayment'); ?>)</label>
                <input type="text" name="name[]" id="name_1" />
            </div>
        </div>
    </div>
    <button type="button" id="add-invite" class="button"><?php esc_html_e('Add invitation', 'omsplitorderpayment'); ?></button>
    <button type="submit" id="send-invite" class="button alt"><?php esc_html_e('Send invitations', 'omsplitorderpayment'); ?></button>
</form>