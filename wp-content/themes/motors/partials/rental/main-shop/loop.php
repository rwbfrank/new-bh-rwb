<?php
    $col_1 = '';
    $col_2 = 'col-md-12 col-sm-12';
    if(has_post_thumbnail()) {
        $col_1 = 'col-md-4 col-sm-4 first';
        $col_2 = 'col-md-8 col-sm-8 second';
    }

    $id = get_the_ID();
    $s_title = get_post_meta($id, 'cars_info', true);
    $car_info = stm_get_car_rent_info($id);
    $excerpt = get_the_excerpt($id);

    $current_car = stm_get_cart_items();

    $current_car_id = 0;

    if(!empty($current_car['car_class'])) {
        if(!empty($current_car['car_class']['id'])) {
            $current_car_id = $current_car['car_class']['id'];
        }
    }

    $current_car = '';
    if($id == $current_car_id) {
        $current_car = 'current_car';
    }

    $dates = (isset($_COOKIE['stm_pickup_date_' . get_current_blog_id()])) ? checkOrderAvailable($id, $_COOKIE['stm_pickup_date_' . get_current_blog_id()], $_COOKIE['stm_return_date_' . get_current_blog_id()]) : array();
    $disableCar = (count($dates) > 0) ? 'stm-disable-car' : '';
?>

<div class="stm_single_class_car <?php echo esc_attr($current_car); ?> <?php echo esc_attr($disableCar)?>" id="product-<?php echo $id; ?>">
    <div class="row">
        <div class="<?php echo sanitize_text_field($col_1) ?>">
            <?php if(has_post_thumbnail()): ?>
                <div class="image">
                    <?php the_post_thumbnail('stm-img-350-181', array('class' => 'img-responsive')); ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="<?php echo sanitize_text_field($col_2) ?>">
            <div class="row">

                <div class="col-md-6 col-sm-6">
                    <div class="top">
                        <div class="heading-font">
                            <h3><?php the_title(); ?></h3>
                            <?php if(!empty($s_title)): ?>
                                <div class="s_title"><?php echo sanitize_text_field($s_title); ?></div>
                            <?php endif; ?>
                            <?php if(!empty($car_info)): ?>
                                <div class="infos">
                                    <?php foreach($car_info as $slug => $info):
                                        $name = $info['value'];
                                        if($info['numeric']) {
                                            $name = $info['value'] . ' ' . esc_html__($info['name'], 'motors');
                                        }
                                        $font = $info['font'];
                                        ?>
                                        <div class="single_info stm_single_info_font_<?php echo esc_attr($font) ?>">
                                            <i class="<?php echo esc_attr($font); ?>"></i>
                                            <span><?php echo sanitize_text_field($name); ?></span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if(!empty($excerpt)): ?>
                            <div class="stm-more">
                                <a href="#">
                                    <span><?php esc_html_e('More information', 'motors'); ?></span>
                                    <i class="fa fa-angle-down"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-md-6 col-sm-6">
                    <?php get_template_part('partials/rental/main-shop/price'); ?>
                </div>
                <?php if(!empty($excerpt)): ?>
                    <div class="col-md-12 col-sm-12">
                        <div class="more">
                            <div class="lists-inline">
                                <?php echo apply_filters('the_content', $excerpt); ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
	<?php if(!empty($disableCar)): ?>
		<div class="stm-enable-car-date">
			<?php
				$formatedDates = array();
				foreach ($dates as $val){
					$formatedDates[] = get_formated_date($val, 'd M');
				}
			?>
			<h3><?php echo esc_html__('This Class is already booked in: ', 'motors') . "<span class='yellow'>" . implode('<span>,</span> ', $formatedDates);?></span>.</h3>
		</div>
	<?php endif; ?>
</div>