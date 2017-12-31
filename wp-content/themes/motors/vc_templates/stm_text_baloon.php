<?php
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $css, ' ' ) ); ?>

<?php if(!empty($content)): ?>
	<div class="stm_text_baloon">
		<div class="inner">
			<?php echo wpb_js_remove_wpautop($content); ?>
		</div>
		<i class="stm-rental-baloon_tail"></i>
	</div>
<?php endif; ?>