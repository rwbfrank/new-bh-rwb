<select name="<?php echo esc_attr($name) ?>" class="form-control">
	<?php if (!empty($options)): ?>
        <?php foreach ($options as $value => $option) : ?>
            <option
                value="<?php echo esc_attr($value) ?>" <?php selected($option['selected']) ?> <?php disabled($option['disabled']) ?>>
                <?php
                if($name == "price") {
                    if(!empty($value)) echo stm_listing_price_view($value);
                    else echo esc_html__($option['label'], 'motors');
                } else {
                    echo esc_html__($option['label'], 'motors');
                }
                ?>
            </option>
        <?php endforeach; ?>
    <?php endif; ?>
</select>