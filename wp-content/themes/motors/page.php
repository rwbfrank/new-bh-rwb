<?php if(stm_is_rental()) {
    if(is_checkout() or is_cart()) {
        get_template_part('partials/rental/reservation', 'archive');
        return false;
    }
} ?>
<?php get_header(); ?>

<?php get_template_part('partials/page_bg'); ?>
<?php get_template_part('partials/title_box'); ?>

<?php
//Get compare page
$compare_page = get_theme_mod('compare_page', 156);

if (function_exists('icl_object_id')) {
    $id = icl_object_id($compare_page, 'page', false, ICL_LANGUAGE_CODE);
    if (is_page($id)) {
        $compare_page = $id;
    }
}

if (!empty($compare_page) and get_the_id() == $compare_page): ?>
    <div class="container">
        <?php get_template_part('partials/compare'); ?>
    </div>
<?php else: ?>


    <div class="container">

        <?php if (have_posts()) :
            while (have_posts()) : the_post();
                the_content();
            endwhile;
        endif; ?>

        <?php
        wp_link_pages(array(
            'before' => '<div class="page-links"><span class="page-links-title">' . __('Pages:', 'motors') . '</span>',
            'after' => '</div>',
            'link_before' => '<span>',
            'link_after' => '</span>',
            'pagelink' => '<span class="screen-reader-text">' . __('Page', 'motors') . ' </span>%',
            'separator' => '<span class="screen-reader-text">, </span>',
        ));
        ?>

        <div class="clearfix">
            <?php
            if (comments_open() || get_comments_number()) {
                comments_template();
            }
            ?>
        </div>
    </div>
<?php endif; ?>
<?php
get_footer();