<?php
//$car_guru_code = get_theme_mod('carguru', '');
$vin = get_post_meta(get_the_ID(), 'vin_number', true);
$price = get_post_meta(get_the_ID(), 'price', true);
$sale_price = get_post_meta(get_the_ID(), 'sale_price', true);

$guruStyle = get_theme_mod("carguru_style", "STYLE1");
$guruRating = get_theme_mod("carguru_min_rating", "GREAT_PRICE");
$guruHeight = get_theme_mod("carguru_default_height", "42");

if (!empty($sale_price)) {
    $price = $sale_price;
}

if (!empty($guruStyle) and !empty($vin) and !empty($price)): ?>

    <script>

        var CarGurus = window.CarGurus || {}; window.CarGurus = CarGurus;
        CarGurus.DealRatingBadge = window.CarGurus.DealRatingBadge || {};
        CarGurus.DealRatingBadge.options = {
            "style": "<?php echo $guruStyle; ?>",
            "minRating": "<?php echo $guruRating; ?>",
            <?php if(strpos($guruStyle, "STYLE") !== false) : ?>"defaultHeight": "<?php echo $guruHeight; ?>"<?php endif; ?>
        };

        (function() {
            var script = document.createElement('script');
            script.src = "https://static.cargurus.com/js/api/en_US/1.0/dealratingbadge.js";
            script.async = true;
            var entry = document.getElementsByTagName('script')[0];
            entry.parentNode.insertBefore(script, entry);
        })();
	</script>

    <div class="stm_cargurus_wrapper <?php echo (strpos($guruStyle, "STYLE") !== false) ? "cg_style" : "cg_banner"; ?>">
        <span data-cg-vin="<?php echo esc_attr($vin); ?>" data-cg-price="<?php echo intval($price); ?>"></span>
    </div>

<?php endif; ?>