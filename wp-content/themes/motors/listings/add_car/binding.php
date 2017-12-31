<script type="text/javascript">
    jQuery(function ($) {
        var options = <?php echo json_encode( stm_data_binding(true) ); ?>;

        $.each(options, function (slug, config) {
            config.selector = '[name="stm_f_s[' + slug.replace('-',  '_pre_') + ']"]';
        });

        $('.stm_add_car_form .stm_add_car_form_1').each(function () {
            new STMCascadingSelect(this, options);
        });
    });
</script>