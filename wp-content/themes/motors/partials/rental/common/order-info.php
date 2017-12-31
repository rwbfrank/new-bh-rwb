<?php
    $cart_items = stm_get_cart_items();
    $car_rent = $cart_items['car_class'];
    $id = $car_rent['id'];
?>


    <div class="title">
        <h4><?php echo sanitize_text_field($car_rent['name']); ?></h4>
        <div class="subtitle heading-font"><?php echo sanitize_text_field($car_rent['subname']); ?></div>
    </div>
    <?php if(has_post_thumbnail($id)):
        $image = wp_get_attachment_image_src(get_post_thumbnail_id($id), 'stm-img-350-181');
        if(!empty($image[0])): ?>
            <div class="image">
                <img src="<?php echo esc_url($image[0]); ?>" />
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!--Car rent-->
    <div class="stm_rent_table">
        <div class="heading heading-font"><h4><?php esc_html_e('Rate', 'motors'); ?></h4></div>
        <table>
            <thead class="heading-font">
                <tr>
                    <td><?php esc_html_e('QTY', 'motors'); ?></td>
                    <td><?php esc_html_e('Rate', 'motors'); ?></td>
                    <td><?php esc_html_e('Subtotal', 'motors'); ?></td>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="3" class="divider"></td>
                </tr>
                <tr>
                    <td><?php echo sprintf(esc_html__('%s Days', 'motors'), $car_rent['days']); ?></td>
                    <td><?php echo wc_price($car_rent['price']); ?></td>
                    <td><?php echo wc_price($car_rent['total']); ?></td>
                </tr>
                <tr>
                    <td colspan="3" class="divider"></td>
                </tr>
            </tbody>
            <tfoot class="heading-font">
                <tr>
                    <td colspan="2"><?php esc_html_e('Rental Charges Rate', 'motors'); ?></td>
                    <td><?php echo wc_price($car_rent['total']); ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!--Add-ons-->
    <?php if(!empty($cart_items['options'])): ?>
        <div class="stm_rent_table">
            <div class="heading heading-font"><h4><?php esc_html_e('Add-ons', 'motors'); ?></h4></div>
            <table>
                <thead class="heading-font">
                    <tr>
                        <td><?php esc_html_e('QTY', 'motors'); ?></td>
                        <td><?php esc_html_e('Rate', 'motors'); ?></td>
                        <td><?php esc_html_e('Subtotal', 'motors'); ?></td>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td colspan="3" class="divider"></td>
                    </tr>
                    <?php foreach($cart_items['options'] as $car_option): ?>
                        <tr>
                            <td><?php echo sprintf(esc_html__('%s x %1s %s %s day(s)', 'motors'), ($car_option['quantity']/$car_rent['days']), $car_option['name'], "for", $car_rent['days']); ?></td>
                            <td><?php echo wc_price($car_option['price']); ?></td>
                            <td><?php echo wc_price($car_option['total']); ?></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="divider"></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot class="heading-font">
                    <tr>
                        <td colspan="2"><?php esc_html_e('Add-ons Charges Rate', 'motors'); ?></td>
                        <td><?php echo wc_price($cart_items['option_total']); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    <?php endif; ?>

    <?php get_template_part('partials/rental/common/tax'); ?>

    <?php get_template_part('partials/rental/common/coupon'); ?>

    <div class="stm-rent-total heading-font">
        <table>
            <tr>
                <td><?php esc_html_e('Estimated total', 'motors'); ?></td>
                <td><?php echo $cart_items['total']; ?></td>
            </tr>
        </table>
    </div>