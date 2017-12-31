<div class="image">

	<!--Video-->
	<?php stm_listings_load_template('loop/list/video'); ?>

	<a href="<?php the_permalink() ?>" class="rmv_txt_drctn">
		<div class="image-inner">

			<!--Badge-->
			<?php stm_listings_load_template('loop/list/badge'); ?>

			<?php if(has_post_thumbnail()):
				the_post_thumbnail('medium', array('class' => 'img-responsive'));

			else:
			?>
				<img
					src="<?php echo esc_url(get_stylesheet_directory_uri().'/assets/images/plchldr350.png'); ?>"
					class="img-responsive"
					alt="<?php esc_html_e('Placeholder', 'motors'); ?>"
				/>
			<?php endif; ?>
		</div>
	</a>
</div>