<?php
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );
$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $css, ' ' ) );

$testimonials_carousel = 'testimonials_carousel_'.rand(0,99999);

if(empty($slides_per_row)) {
	$slides_per_row = 1;
}

$not_rental = stm_is_rental();

?>

<div class="testimonials-carousel-wrapper">
	<div class="testimonials-carousel <?php echo esc_attr($testimonials_carousel.$css_class); ?>">
		<?php echo wpb_js_remove_wpautop($content); ?>
	</div>
</div>

<script type="text/javascript">
	(function($) {
		"use strict";

		var owlRtl = false;
		if( $('body').hasClass('rtl') ) {
			owlRtl = true;
		}

		var owl = $('.<?php echo esc_js($testimonials_carousel); ?>');

		var loopOwl = (owl.find(".testimonial-unit").length > 1) ? true : false;
		
		$(document).ready(function () {
			owl.owlCarousel({
				rtl: owlRtl,
				items: <?php echo esc_js($slides_per_row); ?>,
                responsive: {
				    0: {
				        items: 1
                    },
                    769: {
                        items: <?php echo esc_js($slides_per_row); ?>
                    }
                },
				smartSpeed: 800,
				dots: <?php echo (!stm_is_rental()) ? 'false' : 'true'; ?>,
				nav: <?php echo (stm_is_rental()) ? 'false' : 'true'; ?>,
				autoplay: false,
				loop: loopOwl,
				navText: '',
				responsiveRefreshRate: 1000
			});
		});
	})(jQuery);
</script>