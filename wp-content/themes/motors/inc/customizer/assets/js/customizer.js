jQuery(document).ready(function ($) {
    "use strict";

    $(".stm-icons-wrapper label").on("click", function () {
        $(this).closest("ul").find("li.active").removeClass("active");
        $(this).closest("li").addClass("active");
    });

    $(".stm-color-selector").wpColorPicker({
        change: _.throttle(function () {
            $(this).trigger('change');
        })
    });

    $(".stm-multiple-checkbox-wrapper input[type='checkbox']").on("change", function () {

        var checkbox_values = jQuery(this).parents(".customize-control").find("input[type='checkbox']:checked").map(function () {
            return this.value;
        }).get().join(",");

        $(this).parents(".stm-multiple-checkbox-wrapper").find("input[type='hidden']").val(checkbox_values).trigger("change");
    });

    $(".stm-socials-wrapper input[type='text']").on("change, keyup", function () {

        var data = $(this).closest("form").serialize();

        $(this).parents('.stm-socials-wrapper').find('input[type="hidden"]').val(data).trigger('change');
    });

    $("#customize-control-price_currency input[type='text']").on("change, keyup", function () {
        if(getCookie('stm_current_currency').split('-')[0].trim() == $(this).val().trim()) document.cookie = 'stm_current_currency=' + $(this).val() +"-1; expires=" + 7 +"; path=/; ";
    });

    $("#stm-customize-control-currency_list input.stm-text-repeater-symbol").on("change, keyup", function () {
        if(getCookie('stm_current_currency').split('-')[0].trim() == $(this).val().trim()) document.cookie = 'stm_current_currency=' + encodeURI($(this).val()) + "-" + $(this).parent().parent().find("input.stm-text-repeater-to").val() +"; expires=" + 7 +"; path=/; ";
    });

    $("#stm-customize-control-currency_list input.stm-text-repeater-to").on("change, keyup", function () {
        var curr = $(this).parent().parent().find("input.stm-text-repeater-symbol").val();
        if(getCookie('stm_current_currency').split('-')[0].trim() == curr.trim()) document.cookie = 'stm_current_currency=' + curr + "-" + $(this).val() +"; expires=" + 7 +"; path=/; ";
    });

    var bg_image = $("#customize-control-bg_image input");
    var site_layout_checked = $("#customize-control-site_boxed input:checked");

    var colorCustom = $('#site_style').val();
    if(colorCustom=='site_style_custom') {
        $('#customize-control-site_style_secondary_color_listing,#customize-control-site_style_base_color_listing,#customize-control-site_style_base_color,#customize-control-site_style_secondary_color')
            .addClass('active');
    }

    $('#site_style').on('change', function(){
       if($(this).val()=='site_style_custom') {
           $('#customize-control-site_style_secondary_color_listing,#customize-control-site_style_base_color_listing,#customize-control-site_style_base_color,#customize-control-site_style_secondary_color')
               .addClass('active');
       } else {
           $('#customize-control-site_style_secondary_color_listing,#customize-control-site_style_base_color_listing,#customize-control-site_style_base_color,#customize-control-site_style_secondary_color')
               .removeClass('active');
       }
    });

    wp.customize('site_boxed', function (value) {
        value.bind(function (to) {
            if (to) {
                $("#customize-control-bg_image").show();
                $("#customize-control-custom_bg_image").show();
            } else {
                $("#customize-control-bg_image").hide();
                $("#customize-control-custom_bg_image").hide();
            }
        });
    });

    if (site_layout_checked.val()) {
        $("#customize-control-bg_image").show();
        $("#customize-control-custom_bg_image").show();
    } else {
        $("#customize-control-bg_image").hide();
        $("#customize-control-custom_bg_image").hide();
    }

    bg_image.on('change', function () {
        $(".theme_bg li.active").removeClass('active');
        $(this).closest('li').addClass('active');
    });

    $("#customize-control-bg_image input[name='bg_image']:checked").closest('li').addClass('active');


    /*Text Repeater block*/
    textRepeaterListenerInit();
    textRepeaterRemoveListener();

    $(".stm-text-repeater-btn").on("click", function () {
        var randInt = Math.floor((Math.random() * 10000) + 1);
        var dynamicClass = "stm-tr-" + randInt;
        $(".stm-diff-currency-list li:last-child").clone().appendTo(".stm-diff-currency-list");
        $(".stm-diff-currency-list li:last-child").find("input[type='text']").val('');
        $(".stm-diff-currency-list li:last-child").find("span").attr("data-position", randInt);
        $(".stm-diff-currency-list li:last-child").removeClass().addClass(dynamicClass);
        textRepeaterListenerInit();
        textRepeaterRemoveListener();
    });

    function getCookie(name) {
        var matches = document.cookie.match(new RegExp(
            "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
        ));
        return matches ? decodeURIComponent(matches[1]) : undefined;
    }

    function textRepeaterListenerInit() {
        $(".stm-diff-currency-list").find("input[type='text']").keyup(function () {
           textRepeaterReTake();
        });
    }

    function textRepeaterRemoveListener() {
        $(".stm-text-repeater-remove").on("click", function () {
            var classLi = ".stm-tr-" + $(this).attr("data-position");

            $(".stm-diff-currency-list").find(classLi).remove();

            textRepeaterReTake();
        });
    }

    function textRepeaterReTake() {
        var curr =  $(".stm-text-repeater-currency").map(function () {
            if(this.value.length > 0) return this.value;
        }).get().join(",");

        var symbol =  $(".stm-text-repeater-symbol").map(function () {
            if(this.value.length > 0) return this.value;
        }).get().join(",");

        var to =  $(".stm-text-repeater-to").map(function () {
            if(this.value.length > 0) return this.value;
        }).get().join(",");

        $(".stm-text-repeater-wrapper").find("input[type='hidden']").val(JSON.stringify({ currency: curr, symbol: symbol, to: to })).trigger("change");
    }
    /*Text Repeater Block*/

    function changeHeaderEl() {
        var curVal = $('#motorcycle_header_layout').val();

        if(curVal == 'car_dealer') {
            changing_elements.show();
        } else {
            changing_elements.hide();
        }
    }

    /*HIDE SETTINGS CUSTOMIZER*/
    var changing_elements = $('#customize-control-header_main_phone_label,' +
        '#customize-control-header_secondary_phone_label_1,' +
        '#customize-control-header_secondary_phone_1,' +
        '#customize-control-header_secondary_phone_label_2,' +
        '#customize-control-header_secondary_phone_2,' +
        '#customize-control-header_address,' +
        '#customize-control-header_layout_break_1,' +
        '#customize-control-header_style,' +
        '#customize-control-header_break_1,' +
        '#customize-control-header_compare_show,' +
        '#customize-control-header_cart_show,' +
        '#customize-control-header_layout_break_2');

    if($('#motorcycle_header_layout').length) {
        changeHeaderEl();

        $('#motorcycle_header_layout').on('change', function(){
            changeHeaderEl();
        });
    }
});