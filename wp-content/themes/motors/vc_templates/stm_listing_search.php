<?php
$atts = vc_map_get_attributes($this->getShortcode(), $atts);
extract($atts);
$css_class = apply_filters(VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class($css, ' '));

if (isset($atts['items']) && strlen($atts['items']) > 0) {
    $items = vc_param_group_parse_atts($atts['items']);
    if (!is_array($items)) {
        $temp = explode(',', $atts['items']);
        $paramValues = array();
        foreach ($temp as $value) {
            $data = explode('|', $value);
            $newLine = array();
            $newLine['title'] = isset($data[0]) ? $data[0] : 0;
            $newLine['sub_title'] = isset($data[1]) ? $data[1] : '';
            if (isset($data[1]) && preg_match('/^\d{1,3}\%$/', $data[1])) {
                $colorIndex += 1;
                $newLine['title'] = (float)str_replace('%', '', $data[1]);
                $newLine['sub_title'] = isset($data[2]) ? $data[2] : '';
            }
            $paramValues[] = $newLine;
        }
        $atts['items'] = urlencode(json_encode($paramValues));
    }
}

$active_taxonomy_tab = true;
$active_taxonomy_tab_active = 'active';
$active_taxonomy_tab_content = 'in active';

if (!empty($show_all) and $show_all == 'yes') {
    $active_taxonomy_tab = false;
    $active_taxonomy_tab_active = '';
    $active_taxonomy_tab_content = '';
}

if (empty($filter_all)) {
    $active_taxonomy_tab = true;
    $active_taxonomy_tab_active = 'active';
}

/*$count_posts = wp_count_posts(stm_listings_post_type());
$all = $count_posts->publish;*/

$all = count(get_posts(array('post_type' => stm_listings_post_type(), 'post_status' => 'publish', 'posts_per_page' => -1, 'suppress_filters' => 0)));


if(empty($show_amount)) {
    $show_amount = 'no';
}

$words = array();

if (!empty($select_prefix)) {
    $words['select_prefix'] = $select_prefix;
}

if (!empty($select_affix)) {
    $words['select_affix'] = $select_affix;
}

if (!empty($number_prefix)) {
    $words['number_prefix'] = $number_prefix;
}

if (!empty($number_affix)) {
    $words['number_affix'] = $number_affix;
}
?>

    <div
        class="stm_dynamic_listing_filter filter-listing stm-vc-ajax-filter animated fadeIn <?php echo esc_attr($css_class); ?>">
        <!-- Nav tabs -->
        <ul class="stm_dynamic_listing_filter_nav clearfix heading-font" role="tablist">
            <?php if (!$active_taxonomy_tab): ?>
                <li role="presentation" class="active">
                    <a href="#stm_all_listing_tab" aria-controls="stm_all_listing_tab" role="tab" data-toggle="tab">
                        <?php echo esc_attr($show_all_label); ?>
                    </a>
                </li>
            <?php endif; ?>

            <?php if (is_array($items)):
                $i = 0;
                foreach ($items as $key => $item):
                    $i++;
                    $item_tab = str_replace(array(',', ' '), '', $item['taxonomy_tab']);
                    $data = explode('|', $item_tab);
                    if ($i > 1) {
                        $active_taxonomy_tab_active = '';
                    }
                    ?>
                    <?php if (!empty($item['taxonomy_tab']) and !empty($item['tab_title_single']) and !empty($item['filter_selected'])): ?>
                    <?php $slug = (isset($item['tab_id_single'])) ? sanitize_title($item['tab_id_single']) : sanitize_title($item['tab_title_single']); ?>

                    <li class="<?php echo esc_attr($active_taxonomy_tab_active); ?>">
                        <a href="#<?php echo esc_attr($slug); ?>" aria-controls="<?php echo esc_attr($slug); ?>"
                           role="tab" data-toggle="tab" data-value="<?php echo esc_attr($data[0]) ?>" data-slug="<?php echo esc_attr($data[1]); ?>">
                            <?php echo esc_attr($item['tab_title_single']); ?>
                        </a>
                    </li>

                <?php endif; ?>
                <?php endforeach;
                $i = 0; ?>
            <?php endif; ?>
        </ul>

        <!-- Tab panes -->
        <div class="tab-content">
            <?php if (!$active_taxonomy_tab): ?>
                <div role="tabpanel" class="tab-pane fade in active" id="stm_all_listing_tab">
                    <form action="<?php echo esc_url(stm_get_listing_archive_link()); ?>" method="GET">
                        <button type="submit" class="heading-font"><i
                                class="fa fa-search"></i> <?php echo '<span>' . $all . '</span> ' . $search_button_postfix; ?>
                        </button>
                        <div class="stm-filter-tab-selects filter stm-vc-ajax-filter">
                            <?php stm_listing_filter_get_selects($filter_all, 'stm_all_listing_tab', $words, $show_amount); ?>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <?php if (is_array($items)): ?>
                <?php foreach ($items as $key => $item): $i++;

                    if ($i > 1) {
                        $active_taxonomy_tab_content = '';
                    }

                    ?>
                    <?php if (!empty($item['taxonomy_tab']) and !empty($item['tab_title_single']) and !empty($item['filter_selected'])): ?>
                        <?php $slug = (isset($item['tab_id_single'])) ? sanitize_title($item['tab_id_single']) : sanitize_title($item['tab_title_single']); ?>
                        <div role="tabpanel" class="tab-pane fade <?php echo esc_attr($active_taxonomy_tab_content); ?>"
                             id="<?php echo esc_attr($slug); ?>">
                            <?php
                            $tax_term = explode(',', $item['taxonomy_tab']);
                            $tax_term = explode(' | ', $tax_term[0]);

                            $taxonomy_count = stm_get_custom_taxonomy_count($tax_term[0], $tax_term[1]);

                            ?>
                            <form action="<?php echo esc_url(stm_get_listing_archive_link()); ?>" method="GET">
                                <button type="submit" class="heading-font"><i
                                        class="fa fa-search"></i> <?php echo '<span>' . $taxonomy_count . '</span> ' . $search_button_postfix; ?>
                                </button>
                                <div class="stm-filter-tab-selects filter stm-vc-ajax-filter">
                                    <div class="hidden">
                                        <select name="<?php echo esc_attr($tax_term[1]); ?>">
                                            <option value="<?php echo esc_attr($tax_term[0]); ?>" selected></option>
                                        </select>
                                    </div>
                                    <?php stm_listing_filter_get_selects($item['filter_selected'], $slug, $words, $show_amount); ?>
                                </div>
                            </form>
                        </div>

                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

<?php
$bind_tax = stm_data_binding(true);
if (!empty($bind_tax)):
    ?>

	<script type="text/javascript">
        jQuery(function ($) {
            var options = <?php echo json_encode( $bind_tax ); ?>, show_amount = <?php echo json_encode( $show_amount != 'no' ) ?>;

            if (show_amount) {
                $.each(options, function (tax, data) {
                    $.each(data.options, function (val, option) {
                        option.label += ' (' + option.count + ')';
                    });
                });
            }

            $('.stm-filter-tab-selects.filter').each(function () {
                new STMCascadingSelect(this, options);
            });

            $("select[data-class='stm_select_overflowed']").on("change", function () {
                var sel = $(this);
                var selValue = sel.val();
                var selType = sel.attr("data-sel-type");
                var min = 'min_' + selType;
                var max = 'max_' + selType;
                if (selValue.includes(">")) {
                    var str = selValue.replace(">", "").trim();
                    $("input[name='" + min + "']").val(str);
                    $("input[name='" + max + "']").val("");
                } else if (selValue.includes("-")) {
                    var strSplit = selValue.split("-");
                    $("input[name='" + min + "']").val(strSplit[0]);
                    $("input[name='" + max + "']").val(strSplit[1]);
                } else {
                    var str = selValue.replace(">", "").trim();
                    $("input[name='" + min + "']").val("");
                    $("input[name='" + max + "']").val(str);
                }
            });
        });
	</script>
<?php endif; ?>