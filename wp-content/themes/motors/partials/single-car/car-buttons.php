<?php
	$show_trade_in = get_theme_mod('show_trade_in', false);
	$show_offer_price = get_theme_mod('show_offer_price', false);

	if($show_offer_price or $show_trade_in): ?>


	<div class="stm-car_dealer-buttons heading-font">

		<?php if($show_trade_in): ?>
			<a href="#trade-in" data-toggle="modal" data-target="#trade-in">
				<?php esc_html_e('Trade in form', 'motors'); ?>
				<i class="stm-moto-icon-trade"></i>
			</a>
		<?php endif; ?>

		<?php if($show_offer_price): ?>
			<a href="#trade-offer" data-toggle="modal" data-target="#trade-offer">
				<?php esc_html_e('Make an offer price', 'motors'); ?>
				<i class="stm-moto-icon-cash"></i>
			</a>
		<?php endif; ?>

	</div>

<?php endif; ?>