<?php
/**
 * Created by PhpStorm.
 * User: NDA
 * Date: 19.12.2017
 * Time: 14:10
 */

$logo_main = get_theme_mod('logo', get_template_directory_uri() . '/assets/images/tmp/logo.png');

$fixed_header = get_theme_mod('header_sticky', true);
if(!empty($fixed_header) and $fixed_header) {
	$fixed_header_class = 'header-listing-fixed';
} else {
	$fixed_header_class = 'header-listing-unfixed';
}

$transparent_header = get_post_meta(get_the_id(), 'transparent_header', true);

if(empty($transparent_header)) {
	$transparent_header_class = 'listing-nontransparent-header';
} else {
	$transparent_header_class = '';
}

if(empty($_COOKIE['compare_ids'])) {
	$_COOKIE['compare_ids'] = array();
}

$compare_page = get_theme_mod( 'compare_page', 156 );

$header_bg = get_theme_mod('header_listing_layout_image_bg');

?>

<div class="header-listing <?php echo esc_attr($fixed_header_class.' '.$transparent_header_class); ?>">

	<div class="listing-header-bg" <?php if(!empty($header_bg)): ?>style="background-image: url('<?php echo esc_url($header_bg); ?>')"<?php endif; ?>></div>
	<div class="container header-inner-content">
		<!--Logo-->
		<div class="listing-logo-main" style="margin-top: <?php echo get_theme_mod( 'menu_top_margin', '17' ); ?>px;">
			<?php if(empty($logo_main)): ?>
				<a class="blogname" href="<?php echo esc_url(home_url('/')); ?>" title="<?php _e('Home', 'motors'); ?>">
					<h1><?php echo esc_attr(get_bloginfo('name')) ?></h1>
				</a>
			<?php else: ?>
				<a class="bloglogo" href="<?php echo esc_url(home_url('/')); ?>">
					<img
						src="<?php echo esc_url( $logo_main ); ?>"
						style="width: <?php echo get_theme_mod( 'logo_width', '112' ); ?>px;"
						title="<?php _e('Home', 'motors'); ?>"
						alt="<?php esc_html_e('Logo', 'motors'); ?>"
					/>
				</a>
			<?php endif; ?>
		</div>

		<div class="listing-service-right clearfix">

			<div class="listing-right-actions clearfix">
				<?php if(!empty($compare_page)): ?>
					<div class="pull-right">
						<a
							class="lOffer-compare"
							href="<?php echo esc_url(get_the_permalink($compare_page)); ?>"
							title="<?php esc_html_e('Watch compared', 'motors'); ?>">
							<i class="list-icon stm-service-icon-listing-compare"></i>
							<span class="list-badge"><span class="stm-current-cars-in-compare"><?php if(!empty($_COOKIE['compare_ids']) and count($_COOKIE['compare_ids'])){ echo esc_attr(count($_COOKIE['compare_ids'])); } ?></span></span>
						</a>
					</div>
				<?php endif; ?>

				<div class="listing-menu-mobile-wrapper">
					<div class="stm-menu-trigger">
						<span></span>
						<span></span>
						<span></span>
					</div>
					<div class="stm-opened-menu-listing">
						<ul class="listing-menu-mobile heading-font visible-xs visible-sm clearfix">
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

							<?php if(!empty($compare_page) && get_theme_mod('header_compare_show', true)): ?>
								<li class="stm_compare_mobile"><a href="<?php echo esc_url(get_the_permalink($compare_page)); ?>"><?php _e('Compare', 'motors'); ?></a></li>
							<?php endif; ?>
						</ul>
						<?php //get_template_part('partials/top', 'bar'); ?>
					</div>
				</div>

			</div>

			<ul class="listing-menu clearfix" style="margin-top: <?php echo (get_theme_mod( 'menu_top_margin', '17' ) + 1); ?>px;">
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
			</ul>
		</div>
	</div>
</div>