<?php
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $css, ' ' ) );

if(!empty($link)) {
    $link = vc_build_link($link);
}
?>

<div class="stm-call-to-action heading-font <?php echo esc_attr($css_class); ?>" style="background-color:<?php echo esc_attr($call_to_action_color); ?>">
	<div class="clearfix">
		<div class="call-to-action-content pull-left">
			<?php if(!empty($call_to_action_label)): ?>
				<div class="content">
					<?php if(!empty($call_to_action_icon)): ?>
						<i class="<?php echo esc_attr($call_to_action_icon); ?>"></i>
					<?php endif; ?>
					<?php if(!empty($call_to_action_label_2)): ?>
						<span><?php echo esc_attr($call_to_action_label_2); ?></span>
					<?php endif; ?>
					<?php echo esc_attr($call_to_action_label); ?>
				</div>
			<?php endif; ?>
		</div>
		<div class="call-to-action-right">

			<?php if(stm_is_rental() and !empty($link['url']) and !empty($link['title'])): ?>
				<a class="button stm-button stm-button-rental"
				   href="<?php echo esc_url($link['url']) ?>"
				   title="<?php echo esc_attr($link['title']); ?>"
					<?php if(!empty($link['target'])): ?>
						target="_blank"
					<?php endif; ?>>

					<?php if(!empty($cta_icon)): ?>
						<i class="<?php echo esc_attr($cta_icon); ?>"></i>
					<?php endif; ?>

					<?php echo esc_attr($link['title']); ?>
				</a>
			<?php endif; ?>

			<div class="call-to-action-meta">
				<?php if(!empty($call_to_action_label_right)): ?>
					<div class="content">
						<?php if(!empty($call_to_action_icon_right)): ?>
							<i class="<?php echo esc_attr($call_to_action_icon_right); ?>"></i>
						<?php endif; ?>
						<?php echo esc_attr($call_to_action_label_right); ?>
					</div>
				<?php endif; ?>

			</div>
		</div>

	</div>
</div>