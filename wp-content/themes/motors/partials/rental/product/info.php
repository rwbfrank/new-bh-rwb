<?php
$cart_items = stm_get_cart_items();
$order_info = 'empty';
if($cart_items['has_car']) {
    $order_info = '';
} ?>

<div class="stm_rent_order_info">
    <?php
        get_template_part('partials/rental/common/order-info', $order_info);

        if(empty($order_info) and !is_checkout()) {
            get_template_part('partials/rental/common/order-info', 'accept');
        }
    ?>
</div>