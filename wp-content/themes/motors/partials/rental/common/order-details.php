<?php
$stm_rental_total_order_info = stm_rental_total_order_info();
if(!empty($stm_rental_total_order_info)): ?>
    <h4 class="rental_title"><?php esc_html_e('Summary', 'motors'); ?></h4>
    <div class="stm_rental_order_success">
        <?php foreach($stm_rental_total_order_info as $key => $stm_order_info): ?>
            <?php if(!empty($stm_order_info['content'])): ?>
                <div class="single_order_info">
                    <h4 class="title"><?php echo esc_attr($stm_order_info['title']); ?></h4>
                    <div class="content"><?php echo $stm_order_info['content']; ?></div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php get_template_part('partials/rental/common/order', 'policy'); ?>
<?php get_template_part('partials/rental/common/order', 'print'); ?>
