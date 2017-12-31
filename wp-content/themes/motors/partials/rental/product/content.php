<div id="rent_notice">
    <?php wc_print_notices(); ?>
</div>
<div class="row stm_rental_archive_top">
    <div class="col-md-7 col-sm-7">
        <h2 class="title">
            <?php esc_html_e('Vehicle Add-ons', 'motors'); ?>
        </h2>
    </div>
    <div class="col-md-5 col-sm-5">
        <?php get_template_part('partials/rental/common/coupon', 'form'); ?>
    </div>
</div>
<div class="row">
    <div class="col-md-7">
        <?php get_template_part('partials/rental/product/options', 'archive'); ?>
    </div>
    <div class="col-md-5">
        <?php get_template_part('partials/rental/product/info'); ?>
    </div>
</div>

<?php
    $remove_params = array('add-to-cart', 'product_id', 'variation_id', 'quantity', 'remove-from-cart');
?>
<script type="text/javascript">
    jQuery(document).ready(function(){
        window.history.pushState('', '', '<?php echo esc_url(remove_query_arg($remove_params)) ?>');
    });
</script>