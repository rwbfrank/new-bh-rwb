
	<div class="row">
		<div class="col-md-9 col-sm-12 col-xs-12">
			<div class="stm-single-car-content">
				<h1 class="title h2"><?php the_title(); ?></h1>

				<!--Actions-->
				<?php get_template_part('partials/single-car/car', 'actions'); ?>

				<!--Gallery-->
				<?php get_template_part('partials/single-car/car', 'gallery'); ?>

				<!--Car Gurus if is style BANNER-->
				<?php if(strpos(get_theme_mod("carguru_style", "STYLE1"), "BANNER") !== false) get_template_part('partials/single-car/car', 'gurus'); ?>
				<?php the_content(); ?>
			</div>
		</div>

		<div class="col-md-3 col-sm-12 col-xs-12">
			<div class="stm-single-car-side">

				<!--Prices-->
				<?php get_template_part('partials/single-car/car', 'price'); ?>

				<!--Data-->
				<?php get_template_part('partials/single-car/car', 'data'); ?>

				<!--MPG-->
				<?php get_template_part('partials/single-car/car', 'mpg'); ?>

				<!--Calculator-->
				<?php get_template_part('partials/single-car/car', 'calculator'); ?>

			</div>
		</div>
	</div>