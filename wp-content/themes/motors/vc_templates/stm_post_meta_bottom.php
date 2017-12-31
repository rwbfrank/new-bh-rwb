<?php
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $css, ' ' ) );
$css_share_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $css_share, ' ' ) );
?>

<div class="blog-meta-bottom <?php echo esc_attr($css_class); ?>">
	<div class="clearfix">
		<div class="left">
			<!--Categories-->
			<?php $cats = get_the_category( get_the_id() ); //print_r($cats); ?>
			<?php if ( ! empty( $cats ) ): ?>
				<div class="post-cat">
					<span class="h6"><?php esc_html_e( 'Category:', 'motors' ); ?></span>
					<?php foreach ( $cats as $cat ): ?>
						<span class="post-category">
							<a href="<?php echo esc_url( get_category_link( $cat->term_id ) ); ?>"><span><?php echo $cat->name; ?></span></a><span class="divider">,</span>
						</span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>

			<!--Tags-->
			<?php if( $tags = wp_get_post_tags( get_the_ID() ) ){ ?>
				<div class="post-tags">
					<span class="h6"><?php esc_html_e( 'Tags:', 'motors' ); ?></span>
					<span class="post-tag">
						<?php echo get_the_tag_list('', ', ', ''); ?>
					</span>
				</div>
			<?php } ?>
		</div>

		<div class="right">
			<div class="stm-shareble<?php echo esc_attr($css_share_class); ?>">
				<a
					href="#"
					class="car-action-unit stm-share"
					title="<?php esc_html_e('Share this', 'motors'); ?>"
					download>
					<i class="stm-icon-share"></i>
					<?php esc_html_e('Share this', 'motors'); ?>
				</a>
				<?php if(function_exists( 'ADDTOANY_SHARE_SAVE_KIT' ) && ! get_post_meta( get_the_ID(), 'sharing_disabled', true )): ?>
					<div class="stm-a2a-popup">
						<?php echo do_shortcode('[addtoany url="'.get_the_permalink(get_the_ID()).'" title="'.get_the_title(get_the_ID()).'"]'); ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>