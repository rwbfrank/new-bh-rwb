<div class="row">

    <?php $filter = stm_listings_filter(); ?>

    <?php
    $sidebar_pos = stm_get_sidebar_position();
    $sidebar_id = get_theme_mod('listing_sidebar', 'primary_sidebar');
    if( !empty($sidebar_id) ) {
        $blog_sidebar = get_post( $sidebar_id );
    }

    if($sidebar_id == 'no_sidebar') {
        $sidebar_id = false;
    }
    ?>

    <div class="col-md-3 col-sm-12 classic-filter-row sidebar-sm-mg-bt <?php echo $sidebar_pos['sidebar'] ?>">
        <?php stm_listings_load_template('motorcycles/filter/sidebar', array('filter' => $filter)); ?>
        <!--Sidebar-->
        <div class="stm-inventory-sidebar">
            <?php
            if($sidebar_id == 'primary_sidebar') {
                get_sidebar();
            }else if(!empty($sidebar_id)) {
                echo apply_filters( 'the_content' , $blog_sidebar->post_content);
            ?>
                <style type="text/css">
                    <?php echo get_post_meta( $sidebar_id, '_wpb_shortcodes_custom_css', true ); ?>
                </style>
            <?php }
            ?>
        </div>
    </div>

    <div class="col-md-9 col-sm-12 <?php echo $sidebar_pos['content'] ?>">
        <div class="stm-ajax-row">
            <?php stm_listings_load_template('motorcycles/filter/actions', array('filter' => $filter)); ?>
            <div id="listings-result">
                <?php stm_listings_load_results(); ?>
            </div>
        </div>
    </div> <!--col-md-9-->

</div>

<?php wp_reset_postdata(); ?>