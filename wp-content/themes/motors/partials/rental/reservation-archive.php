<?php get_header(); ?>
<?php
$shop_sidebar_id = get_theme_mod('shop_sidebar', 768);
$shop_sidebar_position = get_theme_mod('shop_sidebar_position', 'left');


if (!empty($shop_sidebar_id)) {
    $shop_sidebar = get_post($shop_sidebar_id);
}

$stm_sidebar_layout_mode = stm_sidebar_layout_mode($shop_sidebar_position, $shop_sidebar_id);
?>

<?php get_template_part('partials/title_box'); ?>
<?php get_template_part('partials/page_bg'); ?>

<?php get_template_part('partials/rental/wizard'); ?>
<?php get_template_part('partials/rental/wizard', 'bg'); ?>

    <div class="stm-reservation-archive">
        <div class="container">
            <div class="row">

                <?php echo $stm_sidebar_layout_mode['content_before'];

                if(is_shop()) {
                    get_template_part('partials/rental/main-shop/archive', 'content');
                } elseif(is_product() or is_cart()) {
                    get_template_part('partials/rental/product/content');
                } elseif(is_checkout()) {
                    get_template_part('partials/rental/checkout/checkout');
                } else {
                    if(have_posts()) {
                        woocommerce_content();
                    }
                }

                echo $stm_sidebar_layout_mode['content_after']; ?>

                <?php echo $stm_sidebar_layout_mode['sidebar_before']; ?>
                <div class="stm-shop-sidebar-area">
                    <?php
                    if (!empty($shop_sidebar_id)) {
                        echo apply_filters('the_content', $shop_sidebar->post_content);
                    }
                    ?>
                </div>
                <?php echo $stm_sidebar_layout_mode['sidebar_after']; ?>

            </div> <!--row-->
        </div> <!--container-->
    </div>


<?php get_footer(); ?>