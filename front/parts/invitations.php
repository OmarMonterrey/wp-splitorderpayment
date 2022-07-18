<h3><?=__('Invitations', 'omsplitorderpayment')?></h3>
<table class="woocommerce-table">
    <thead>
        <tr>
            <th><?=__('Payer Name', 'omsplitorderpayment')?></th>
            <th><?=__('Payer Email', 'omsplitorderpayment')?></th>
        </tr>
    </thead>
    <tbody>
        <?php
            $count = 0;
            foreach( $payment_list as $payment_key => $single_payment ){
                if( $single_payment->amount != 0 ) continue;
                $count ++;
                echo "<tr>";
                    echo "<td>{$single_payment->name}</td>";
                    echo "<td>";
                        echo $single_payment->email;
                        include(__DIR__.'/cancel_invitation.php');
                    echo "</td>";
                echo "</tr>";
            }
        ?>
        <?php if( !$count ): ?>
            <tr>
                <td colspan="2"><?=__('This order has no invitations.', 'omsplitorderpayment')?></td>
            </tr>
        <?php endif; ?>
    </tbody>

</table>
<h3><?=__('Send Payment Invitations', 'omsplitorderpayment')?></h3>
<form method="post" id="omsplitorderpayment-invite">
    <input type="hidden" name="order" value="<?=$order->get_id()?>">
    <div id="invite-list">
        <div class="single-invite">
            <div class="input-field">
                <label for="email_1"><?=__('Invite Email', 'omsplitorderpayment')?></label>
                <input type="email" name="email[]" id="email_1" required/>
            </div>
            <div class="input-field">
                <label for="name_1"><?=__('Invite Name', 'omsplitorderpayment')?> (<?=__('Optional', 'omsplitorderpayment')?>)</label>
                <input type="text" name="name[]" id="name_1" />
            </div>
        </div>
    </div>
    <button type="button" id="add-invite" class="button"><?=__('Add invitation', 'omsplitorderpayment')?></button>
    <button type="submit" id="send-invite" class="button alt"><?=__('Send invitations', 'omsplitorderpayment')?></button>
</form>