<?php
$logo_main = get_theme_mod('logo', get_template_directory_uri() . '/assets/images/tmp/logo-boats.png');
$compare_page = get_theme_mod( 'compare_page', 156 );
$shopping_cart_boats = get_theme_mod('shopping_cart_boats', true);
//Get archive shop page id
if( function_exists('WC')) {
    $woocommerce_shop_page_id = wc_get_cart_url();
}
?>

<div class="stm-boats-mobile-header">
	<?php if(empty($logo_main)): ?>
		<a class="blogname" href="<?php echo esc_url(home_url('/')); ?>" title="<?php _e('Home', 'motors'); ?>">
			<h1><?php echo esc_attr(get_bloginfo('name')) ?></h1>
		</a>
	<?php else: ?>
		<a class="bloglogo" href="<?php echo esc_url(home_url('/')); ?>">
			<img
				src="<?php echo esc_url( $logo_main ); ?>"
				style="width: <?php echo get_theme_mod( 'logo_width', '160' ); ?>px;"
				title="<?php _e('Home', 'motors'); ?>"
				alt="<?php esc_html_e('Logo', 'motors'); ?>"
				/>
		</a>
	<?php endif; ?>

	<div class="stm-menu-boats-trigger">
		<span></span>
		<span></span>
		<span></span>
	</div>
</div>

<div class="stm-boats-mobile-menu">
	<div class="inner">
		<div class="inner-content">
			<ul class="listing-menu heading-font clearfix">
				<?php
				wp_nav_menu( array(
						'menu'              => 'primary',
						'theme_location'    => 'primary',
						'depth'             => 3,
						'container'         => false,
						'menu_class'        => 'service-header-menu clearfix',
						'items_wrap'        => '%3$s',
						'fallback_cb' => false
					)
				);
                ?>
                <?php if($shopping_cart_boats && !empty($woocommerce_shop_page_id)): ?>
                    <li class="menu-item menu-item-type-post_type menu-item-object-page">
                        <?php $items = WC()->cart->cart_contents_count; ?>
                        <!--Shop archive-->
                        <a class="help-bar-shop" href="<?php echo esc_url($woocommerce_shop_page_id); ?>" title="<?php esc_html_e('Watch shop items', 'motors'); ?>" >
                            <span><?php esc_html_e('Cart', 'motors'); ?></span>
                            <?php if($items > 0): ?><span class="list-badge"><span class="stm-current-items-in-cart"><?php echo esc_attr($items); ?></span></span><?php endif; ?>
                        </a>
                    </li>
                <?php endif; ?>

                <?php if(!empty($compare_page)): ?>
                    <li class="menu-item menu-item-type-post_type menu-item-object-page">
                        <a class="lOffer-compare" href="<?php echo esc_url(get_the_permalink($compare_page)); ?>" title="<?php esc_html_e('Watch compared', 'motors'); ?>">
                            <span><?php esc_html_e('Compare', 'motors'); ?></span>
                            <?php if(!empty($_COOKIE['compare_ids']) and count($_COOKIE['compare_ids'])): ?><span class="list-badge"><span class="stm-current-cars-in-compare"><?php echo esc_attr(count($_COOKIE['compare_ids']));?></span></span><?php endif; ?>
                        </a>
                    </li>
                <?php endif; ?>
			</ul>
			<?php get_template_part('partials/top-bar-boats', 'mobile'); ?>
		</div>
	</div>
</div>