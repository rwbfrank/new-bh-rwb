<?php
$args = array(
    'post_type' => 'product',
    'post_status' => 'publish',
    'posts_per_page' => '15',
    'tax_query' => array(
        array(
            'taxonomy' => 'product_type',
            'field'    => 'slug',
            'terms'    => 'car_option',
        )
    )
);

$p = new WP_Query($args);
if($p->have_posts()):
    while($p->have_posts()):
        $p->the_post(); ?>
        <div class="stm_rental_options_archive">
            <?php get_template_part('partials/rental/product/option'); ?>
        </div>

    <?php endwhile; ?>
    <script type="text/javascript">
        jQuery(document).ready(function(){
            var $ = jQuery;
            $('.stm-manage-stock-yes a').on('click', function(e){
                e.preventDefault();
                e.stopPropagation();
                var stmHref = $(this).attr('href');
                var quantityValue = $(this).closest('.meta').find('.qty').val();
                var quantity = '&quantity=' + quantityValue;
                stmHref += quantity;
                window.location.href = stmHref;
            });
        })
    </script>
<?php endif;
wp_reset_postdata();