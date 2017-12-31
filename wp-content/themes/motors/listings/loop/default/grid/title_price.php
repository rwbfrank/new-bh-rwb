<div class="car-meta-top heading-font clearfix">
	<?php if(empty($car_price_form_label)): ?>
		<?php if(!empty($price) and !empty($sale_price) and $price != $sale_price):?>
			<div class="price discounted-price">
				<div class="regular-price"><?php echo esc_attr(stm_listing_price_view($price)); ?></div>
				<div class="sale-price"><?php echo esc_attr(stm_listing_price_view($sale_price)); ?></div>
			</div>
		<?php elseif(!empty($price)): ?>
			<div class="price">
				<div class="normal-price"><?php echo esc_attr(stm_listing_price_view($price)); ?></div>
			</div>
		<?php endif; ?>
	<?php else: ?>
		<div class="price">
			<div class="normal-price"><?php echo esc_attr($car_price_form_label); ?></div>
		</div>
	<?php endif; ?>
    <div class="car-title">
        <?php echo esc_attr(trim(preg_replace( '/\s+/', ' ', substr(stm_generate_title_from_slugs(get_the_id()), 0, 35) ))); ?>
        <?php if(strlen(stm_generate_title_from_slugs(get_the_id())) > 35){
            echo esc_attr('...');
        } ?>
    </div>
</div>