<h2><?=$this->get_option('title')?></h2>
<?php
    if($order->get_user_id() == get_current_user_id()) {
        include_once( __DIR__.'/parts/invitations.php' );
    }
    $show_footer = true;
    include_once(__DIR__.'/parts/made.php');
?>