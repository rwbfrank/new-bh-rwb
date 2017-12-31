<?php
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );

wp_reset_query();
$cars = new WP_Query(_stm_listings_build_query_args(array(
    'nopaging' => true,
    'posts_per_page' => -1
)));

$mapLocationCar = array();
$carsData = array();
$markers = array();
$carsInfo = array();

foreach(stm_get_map_listings() as $k => $val) {
    if(isset($val["use_on_map_page"]) && $val["use_on_map_page"] == 1) {
        $carsInfo[count($carsInfo)] = array('key' => $val["slug"], "icon" => $val["font"]);
    }
}

$i = 0;
foreach($cars->get_posts() as $k => $val) {

    if(get_post_meta($val->ID, "stm_lat_car_admin") != null && $val->post_status == "publish" || $val->post_status == "private") {

        $carMeta = get_post_meta($val->ID, "");
        $img = "<img src='" . get_template_directory_uri() . "/assets/images/plchldr255.png" . "'/>";
        if(has_post_thumbnail($val->ID)) {
            $img = get_the_post_thumbnail($val->ID, 'full');
        }

        $price = (isset($carMeta["price"])) ? stm_listing_price_view($carMeta["price"][0]) : 0 . stm_get_price_currency();
        if(isset($carMeta["sale_price"]) && $carMeta["sale_price"][0] != null) $price = stm_listing_price_view($carMeta["sale_price"][0]);

        $car_price_form = get_post_meta($val->ID, 'car_price_form', true);
        $car_price_form_label = get_post_meta($val->ID, 'car_price_form_label', true);
        if(!empty($car_price_form) and $car_price_form == 'on') $price = $car_price_form_label;

        $carsData[$i]["id"]             = $val->ID;
        $carsData[$i]["link"]           = get_the_permalink($val->ID);
        $carsData[$i]["title"]          = $val->post_title;
        $carsData[$i]["image"]          = $img;
        $carsData[$i]["price"]          = $price;
        $carsData[$i]["year"]           = (isset($carMeta["ca-year"])) ? $carMeta["ca-year"][0] : "";
        $carsData[$i]["condition"]      = (isset($carMeta["condition"])) ? mb_strtoupper(str_replace("-cars", "", $carMeta["condition"][0])) : "";
        $carsData[$i]["mileage"]        = (isset($carsInfo[0]) && isset($carMeta[$carsInfo[0]["key"]])) ? $carMeta[$carsInfo[0]["key"]][0] : "";
        $carsData[$i]["engine"]         = (isset($carsInfo[1]) && isset($carMeta[$carsInfo[1]["key"]])) ? $carMeta[$carsInfo[1]["key"]][0] : "";
        $carsData[$i]["transmission"]   = (isset($carsInfo[2]) && isset($carMeta[$carsInfo[2]["key"]])) ? $carMeta[$carsInfo[2]["key"]][0] : "";
        $carsData[$i]["mileage_font"]        = (isset($carsInfo[0]) && isset($carsInfo[0]["icon"])) ? $carsInfo[0]["icon"] : "";
        $carsData[$i]["engine_font"]         = (isset($carsInfo[1]) && isset($carsInfo[1]["icon"])) ? $carsInfo[1]["icon"] : "";
        $carsData[$i]["transmission_font"]   = (isset($carsInfo[2]) && isset($carsInfo[2]["icon"])) ? $carsInfo[2]["icon"] : "";

        $markers[$i]["lat"] = (double) $carMeta["stm_lat_car_admin"][0];
        $markers[$i]["lng"] = (double) $carMeta["stm_lng_car_admin"][0];

        $mapLocationCar[(string) $markers[$i]["lat"]][] = $i;
        $i++;
    }
}

wp_reset_query();

$id = rand();

if ( empty( $lat ) ) {
    $lat = 36.169941;
}
if ( empty( $lng ) ) {
    $lng = - 115.139830;
}

$map_style = array();
$map_style['width'] = ' width: 100vw;';
$map_style['height'] = ' height: 100%;';
$disable_mouse_whell = 'true';

$pin_url = get_template_directory_uri() . '/assets/images/classified_inventory_pin.png';
$cluster_url_path = get_template_directory_uri() . '/assets/images/';

if (!empty($image)) {
    $image = explode(',',$image);
    if(!empty($image[0])) {
        $image = $image[0];
        $image = wp_get_attachment_image_src($image, 'full');
        $pin_url = $image[0];
    }
}

$filter = stm_listings_filter();
?>

<div class="stm-inventory-map-wrap">
    <div<?php echo( ( $map_style ) ? ' style="' . esc_attr( implode( ' ', $map_style ) ) . ' margin: 0 auto; "' : '' ); ?> id="stm_map-<?php echo esc_attr( $id ); ?>" class="stm_gmap"></div>
    <div class="stm-inventory-map-filter-arrow-wrap">
        <div class="stm-filter-arrow stm-map-filter-open"></div>
        <div class="stm-inventory-map-filter-wrap">
            <div class="stm-filter-scrollbar">
                <form action="<?php echo stm_listings_current_url() ?>" method="get" data-trigger="filter-map">
                    <div class="filter filter-sidebar ajax-filter">

                        <?php do_action( 'stm_listings_filter_before' ); ?>

                        <div class="sidebar-entry-header">
                            <i class="stm-icon-car_search"></i>
                            <span class="h4"><?php _e( 'Search Options', 'motors' ); ?></span>
                        </div>

                        <div class="row row-pad-top-24">

                            <?php foreach ( $filter['filters'] as $attribute => $config ):

                                if($attribute == 'price') {
                                    continue;
                                }
                                if ( ! empty( $config['slider'] ) && $config['slider'] ):
                                    stm_listings_load_template( 'filter/types/slider', array(
                                        'taxonomy' => $config,
                                        'options'  => $filter['options'][ $attribute ]
                                    ) );
                                else: ?>
                                    <?php if(isset($filter['options'][ $attribute ])) : ?>
                                        <div class="col-md-12 col-sm-6 stm-filter_<?php echo esc_attr( $attribute ) ?>">
                                            <div class="form-group">
                                                <?php stm_listings_load_template('filter/types/select', array(
                                                    'options' => $filter['options'][$attribute],
                                                    'name' => $attribute
                                                ));
                                                ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>

                            <?php stm_listings_load_template('filter/types/location'); ?>

                            <?php
                            stm_listings_load_template( 'filter/types/features', array(
                                'taxonomy' => 'stm_additional_features',
                            ) );
                            ?>

                        </div>

                        <!--View type-->
                        <input type="hidden" id="stm_view_type" name="view_type"
                               value="<?php echo esc_attr( stm_listings_input( 'view_type' ) ); ?>"/>
                        <!--Filter links-->
                        <input type="hidden" id="stm-filter-links-input" name="stm_filter_link" value=""/>
                        <!--Popular-->
                        <input type="hidden" name="popular" value="<?php echo esc_attr( stm_listings_input( 'popular' ) ); ?>"/>

                        <input type="hidden" name="s" value="<?php echo esc_attr( stm_listings_input( 's' ) ); ?>"/>
                        <input type="hidden" name="sort_order" value="<?php echo esc_attr( stm_listings_input( 'sort_order' ) ); ?>"/>

                        <div class="sidebar-action-units">
                            <input id="stm-classic-filter-submit" class="hidden" type="submit"
                                   value="<?php _e( 'Show cars', 'motors' ); ?>"/>

                            <a href="<?php echo esc_url( get_permalink() ); ?>"
                               class="button"><span><?php _e( 'Reset all', 'motors' ); ?></span></a>
                        </div>

                        <?php do_action( 'stm_listings_filter_after' ); ?>
                    </div>

                    <!--Classified price-->
                    <?php
                    if(!empty($filter['options']) and !empty($filter['options']['price'])) {
                        stm_listings_load_template( 'filter/types/price', array(
                            'taxonomy' => 'price',
                            'options'  => $filter['options']['price']
                        ) );
                    }
                    ?>

                    <?php stm_listings_load_template('filter/types/checkboxes', array('filter' => $filter)); ?>

                </form>
            </div>
            <div class="stm-inventory-map-btn">
                <div class="stm-inventory-map-cars-count" data-sprint="<?php echo esc_html__("%s matches", "motors")?>"><?php echo sprintf(esc_html__("%s matches", "motors"), count($carsData));?></div>
                <input class="button" type="submit" value="<?php echo esc_html__("Apply", "motors");?>" />
            </div>
        </div>
    </div>
</div>


    <script type="text/javascript">
        jQuery("body").addClass("stm-inventory-map-body");
        jQuery(document).ready(function ($) {

            var mapHeight = ((parseInt($(window).height()) - parseInt($("#top-bar").height())) - parseInt($("#header").height())) - parseInt($("#footer").height());

            if(mapHeight > 440) {
                $(".stm-inventory-map-wrap").height(mapHeight);
                $(".stm-filter-scrollbar").height(mapHeight - $(".stm-inventory-map-btn").outerHeight() + 2);
            }

            $(".stm-filter-scrollbar").mCustomScrollbar({
                theme:"dark"
            });

            if(stm_check_mobile()) {
                $(".stm-filter-scrollbar").mCustomScrollbar('destroy');
            }

            google.maps.event.addDomListener(window, 'load', init);

            var center, map;
            var markers = [];
            var markerCluster;

            function init() {

                var locations = <?php echo json_encode($markers); ?>;
                var carData = <?php echo json_encode($carsData); ?>;
                var mapLocationCar = <?php echo json_encode($mapLocationCar); ?>

                if(locations.length > 0) center = new google.maps.LatLng(locations[0]["lat"],locations[0]["lng"]);
                else center = new google.maps.LatLng(<?php echo esc_js( $lat ); ?>, <?php echo esc_js( $lng ); ?>);
                var mapOptions = {
                    zoom: 3,
                    center: center,
                    scrollwheel: <?php echo esc_js( $disable_mouse_whell ); ?>,
                    mapTypeId: 'roadmap',
                    minZoom: 2,
                    maxZoom: 20,
                };

                var mapElement = document.getElementById('stm_map-<?php echo esc_js( $id ); ?>');
                map = new google.maps.Map(mapElement, mapOptions);

                for (var i = 0; i < locations.length; i++) {
                    var latLng = new google.maps.LatLng(locations[i]["lat"],locations[i]["lng"]);
                    var marker = new google.maps.Marker({
                        position: latLng,
                        icon: '<?php echo esc_url($pin_url); ?>',
                        map: map
                    });
                    var infowindow = new google.maps.InfoWindow({ });
                    markers.push(marker);
                    google.maps.event.addListener(marker, 'mouseover', (function(marker, i) {
                        return function() {
                            var groupClass = (mapLocationCar[locations[i]["lat"]].length <= 3) ? "stm_if_group_" + mapLocationCar[locations[i]["lat"]].length + " stm_if_group_no_scroll" : "stm_if_group_scroll"

							<?php if(wp_is_mobile()) :?>
							groupClass += " stm_is_mobile"
							<?php endif; ?>
                            var infoWindowHtml = '<div class="stm_map_info_window_group_wrap ' + groupClass +'"><div class="stm_if_scroll">';

                            if(mapLocationCar[locations[i]["lat"]].length == 1) {
                                infoWindowHtml += '<a class="stm_iw_link" href="' + carData[i]["link"] + '"> <div class="stm_map_info_window_wrap">' +
                                    '<div class="stm_iw_condition">' + carData[i]["condition"] + ' ' + carData[i]["year"] + '</div>' +
                                    '<div class="stm_iw_title">' + carData[i]["title"] + '</div>' +
                                    '<div class="stm_iw_car_data_wrap">' +
                                    '<div class="stm_iw_img_wrap">' +
                                    carData[i]["image"] +
                                    '</div>' +
                                    '<div class="stm_iw_car_info">' +
                                    '<span class="stm_iw_car_opt stm_car_mlg"><i class="' + carData[i]["mileage_font"] + '"></i>' + carData[i]["mileage"] + '</span>' +
                                    '<span class="stm_iw_car_opt stm_car_engn"><i class="' + carData[i]["engine_font"] + '"></i>' + carData[i]["engine"] + '</span>' +
                                    '<span class="stm_iw_car_opt stm_car_trnsmsn"><i class="' + carData[i]["transmission_font"] + '"></i>' + carData[i]["transmission"] + '</span>' +
                                    '</div>' +
                                    '</div>' +
                                    '<div class="stm_iw_car_price"><span class="stm_iw_price_trap"></span>' + carData[i]["price"] + '</div>' +
                                    '</div></a>';
                            } else {

                                //var groupClass = (mapLocationCar[locations[i]["lat"]].length <= 3) ? "stm_if_group_" + mapLocationCar[locations[i]["lat"]].length + " stm_if_group_no_scroll" : "stm_if_group_scroll"

                                //var infoWindowHtml = '<div class="stm_map_info_window_group_wrap ' + groupClass +'">';

                                for(var w=0;w<mapLocationCar[locations[i]["lat"]].length;w++) {
                                    var carPos = mapLocationCar[locations[i]["lat"]][w];
                                    infoWindowHtml += '<a class="stm_iw_link" href="' + carData[carPos]["link"] + '"> <div class="stm_map_info_window_wrap">' +
                                        '<div class="stm_iw_condition">' + carData[carPos]["condition"] + ' ' + carData[carPos]["year"] + '</div>' +
                                        '<div class="stm_iw_title">' + carData[carPos]["title"] + '</div>' +
                                        '<div class="stm_iw_car_data_wrap">' +
                                        '<div class="stm_iw_img_wrap">' +
                                        carData[carPos]["image"] +
                                        '</div>' +
                                        '<div class="stm_iw_car_info">' +
                                        '<span class="stm_iw_car_opt stm_car_mlg"><i class="' + carData[carPos]["mileage_font"] + '"></i>' + carData[carPos]["mileage"] + '</span>' +
                                        '<span class="stm_iw_car_opt stm_car_engn"><i class="' + carData[carPos]["engine_font"] + '"></i>' + carData[carPos]["engine"] + '</span>' +
                                        '<span class="stm_iw_car_opt stm_car_trnsmsn"><i class="' + carData[carPos]["transmission_font"] + '"></i>' + carData[carPos]["transmission"] + '</span>' +
                                        '</div>' +
                                        '</div>' +
                                        '<div class="stm_iw_car_price"><span class="stm_iw_price_trap"></span>' + carData[carPos]["price"] + '</div>' +
                                        '</div></a>';
								}
                            }
                            infoWindowHtml += '</div></div>';
                            infowindow.setContent(infoWindowHtml);

                            infowindow.open(map, marker);

                            $(".stm_if_group_scroll .stm_if_scroll").mCustomScrollbar({
                                theme:"dark"
                            });

                            if(stm_check_mobile()) {
                                $(".stm_if_group_scroll .stm_if_scroll").mCustomScrollbar('destroy');
                            }
                        }

                    })(marker, i));
                }

                markerCluster = new MarkerClusterer(map, markers, {maxZoom: 9, averageCenter: true, styles: [{url: '<?php echo $cluster_url_path; ?>1.png', textColor: 'white', height: 60, width: 60, textSize: 20}]});

                google.maps.event.addListener(map, 'click', function() {
                    if (infowindow) {
                        infowindow.close();
                    }
                });
            }

            $(window).resize(function(){
                if(typeof map != 'undefined' && typeof center != 'undefined') {
                    setTimeout(function () {
                        map.setCenter(center);
                    }, 1000);
                }
            });

            $('#ca_location_listing_filter').on('keydown', function() {
                $("form[data-trigger=filter-map]").submit(function (e) { e.preventDefault(); });
                buildUrl();
            });

            $(".stm-inventory-map-btn input[type='submit']").on("click", function () {
                $(".stm_gmap").addClass("stm-loading");

                $("form[data-trigger=filter-map]").submit(function (e) { e.preventDefault(); });
                var data = [];

                $.each($("form[data-trigger=filter-map]").serializeArray(), function (i, field) {
                    if (field.value != '') {
                        data.push(field.name + '=' + field.value)
                    }
                });

                for (var i = 0; i < markers.length; i++ ) {
                    console.log("remove");
                    markers[i].setMap(null);
                    markerCluster.removeMarkers(markers);
                }

                markers.length = 0;

                $.ajax({
                    url: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
                    type: "GET",
                    data: 'action=stm_ajax_get_cars_for_inventory_map&' + data.join('&'),
                    dataType: "json",
                    success: function (msg) {
                        $(".stm_gmap").removeClass("stm-loading");
                        locations = msg['markers'];
                        carData = msg['carsData'];
                        mapLocationCar = msg['mapLocationCar'];

                        var strForReplace = $(".stm-inventory-map-cars-count").attr("data-sprint");
                        $(".stm-inventory-map-cars-count").text(strForReplace.replace("%s", carData.length));

                        for (var i = 0; i < locations.length; i++) {
                            var latLng = new google.maps.LatLng(locations[i]["lat"],locations[i]["lng"]);

                            if(i == 0) {
                                center = new google.maps.LatLng(locations[i]["lat"],locations[i]["lng"]);
                                map.setCenter(center);
                            }

                            var marker = new google.maps.Marker({
                                position: latLng,
                                icon: '<?php echo esc_url($pin_url); ?>',
                                map: map
                            });
                            var infowindow = new google.maps.InfoWindow({ });
                            markers.push(marker);
                            google.maps.event.addListener(marker, 'mouseover', (function(marker, i) {
                                return function() {
                                    if(mapLocationCar[locations[i]["lat"]].length == 1) {
                                        infowindow.setContent('<a class="stm_iw_link" href="' + carData[i]["link"] + '"> <div class="stm_map_info_window_wrap">' +
                                            '<div class="stm_iw_condition">' + carData[i]["condition"] + ' ' + carData[i]["year"] + '</div>' +
                                            '<div class="stm_iw_title">' + carData[i]["title"] + '</div>' +
                                            '<div class="stm_iw_car_data_wrap">' +
                                            '<div class="stm_iw_img_wrap">' +
                                            carData[i]["image"] +
                                            '</div>' +
                                            '<div class="stm_iw_car_info">' +
                                            '<span class="stm_iw_car_opt stm_car_mlg"><i class="' + carData[i]["mileage_font"] + '"></i>' + carData[i]["mileage"] + '</span>' +
                                            '<span class="stm_iw_car_opt stm_car_engn"><i class="' + carData[i]["engine_font"] + '"></i>' + carData[i]["engine"] + '</span>' +
                                            '<span class="stm_iw_car_opt stm_car_trnsmsn"><i class="' + carData[i]["transmission_font"] + '"></i>' + carData[i]["transmission"] + '</span>' +
                                            '</div>' +
                                            '</div>' +
                                            '<div class="stm_iw_car_price"><span class="stm_iw_price_trap"></span>' + carData[i]["price"] + '</div>' +
                                            '</div></a>');
                                    } else {

                                        var groupClass = (mapLocationCar[locations[i]["lat"]].length <= 3) ? "stm_if_group_" + mapLocationCar[locations[i]["lat"]].length + " stm_if_group_no_scroll" : "stm_if_group_scroll"

                                        var infoWindowHtml = '<div class="stm_map_info_window_group_wrap ' + groupClass +'">';

                                        for(var w=0;w<mapLocationCar[locations[i]["lat"]].length;w++) {
                                            var carPos = mapLocationCar[locations[i]["lat"]][w];
                                            infoWindowHtml += '<a class="stm_iw_link" href="' + carData[carPos]["link"] + '"> <div class="stm_map_info_window_wrap">' +
                                                '<div class="stm_iw_condition">' + carData[carPos]["condition"] + ' ' + carData[carPos]["year"] + '</div>' +
                                                '<div class="stm_iw_title">' + carData[carPos]["title"] + '</div>' +
                                                '<div class="stm_iw_car_data_wrap">' +
                                                '<div class="stm_iw_img_wrap">' +
                                                carData[carPos]["image"] +
                                                '</div>' +
                                                '<div class="stm_iw_car_info">' +
                                                '<span class="stm_iw_car_opt stm_car_mlg"><i class="' + carData[carPos]["mileage_font"] + '"></i>' + carData[carPos]["mileage"] + '</span>' +
                                                '<span class="stm_iw_car_opt stm_car_engn"><i class="' + carData[carPos]["engine_font"] + '"></i>' + carData[carPos]["engine"] + '</span>' +
                                                '<span class="stm_iw_car_opt stm_car_trnsmsn"><i class="' + carData[carPos]["transmission_font"] + '"></i>' + carData[carPos]["transmission"] + '</span>' +
                                                '</div>' +
                                                '</div>' +
                                                '<div class="stm_iw_car_price"><span class="stm_iw_price_trap"></span>' + carData[carPos]["price"] + '</div>' +
                                                '</div></a>';
                                        }

                                        infoWindowHtml += '</div>';

                                        infowindow.setContent(infoWindowHtml);
                                    }
                                    infowindow.open(map, marker);
                                }

                            })(marker, i));
                        }
                        markerCluster = new MarkerClusterer(map, markers, {maxZoom: 9, averageCenter: true, styles: [{url: '<?php echo $cluster_url_path; ?>1.png', textColor: 'white', height: 60, width: 60, textSize: 20}]});

                        google.maps.event.addListener(map, 'click', function() {
                            if (infowindow) {
                                infowindow.close();
                            }
                        });
                    }
                });
            });

            $(".stm-filter-arrow").on("click", function () {
                setTimeout(function () {
                    google.maps.event.trigger(map, "resize");
                }, 400);
                if($(this).hasClass("stm-map-filter-open")) {
                    $(this).removeClass("stm-map-filter-open").addClass("stm-map-filter-close");
                } else {
                    $(this).removeClass("stm-map-filter-close").addClass("stm-map-filter-open");
                }
            });
        });


        function buildUrl() {
            var data = [],
                url = $("form[data-trigger=filter-map]").attr('action'),
                sign = url.indexOf('?') < 0 ? '?' : '&';

            $.each($("form[data-trigger=filter-map]").serializeArray(), function (i, field) {
                if (field.value != '') {
                    data.push(field.name + '=' + field.value)
                }
            });

            url = url + sign + data.join('&');
            window.history.pushState('', '', decodeURI(url));
        }
    </script>
<?php
function invMapScript() {
?>
   <script type="text/javascript">
       jQuery(document).ready(function ($) {
           $("form[data-trigger=filter-map]").submit(function (e) { e.preventDefault(); });
           $(document).on('slidestop', '.stm-filter-listing-directory-price .stm-price-range', function (event, ui) {
               console.log(1);
               $("form[data-trigger=filter-map]").submit(function (e) { e.preventDefault(); });
               buildUrl();
           });

           $(document).on('click', '.stm-ajax-checkbox-button .button, .stm-ajax-checkbox-instant .stm-option-label input, .stm-ajax-checkbox-button .stm-option-label input', function (e) {
               console.log(2);
               $("form[data-trigger=filter-map]").submit(function (e) { e.preventDefault(); });
               buildUrl();
           });

           $(document).on('change', '.ajax-filter select, .stm-sort-by-options select, .stm-slider-filter-type-unit', function (event) {
               console.log(3);
               $("form[data-trigger=filter-map]").submit(function (e) { e.preventDefault(); });
               buildUrl();
           });

           $(document).on('slidestop', '.ajax-filter .stm-filter-type-slider', function (event, ui) {
               console.log(4);
               $("form[data-trigger=filter-map]").submit(function (e) { e.preventDefault(); });
               buildUrl();
           });
       });
   </script>
<?php
}
add_action('wp_footer', 'invMapScript');
?>