<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


/**
 * Get filter configuration
 *
 * @param array $args
 *
 * @return array
 */
function stm_listings_attributes($args = array())
{
    $args = wp_parse_args($args, array(
        'where' => array(),
        'key_by' => ''
    ));

    $result = array();
    $data = array_filter((array)get_option('stm_vehicle_listing_options'));

    foreach ($data as $key => $_data) {
        $passed = true;
        foreach ($args['where'] as $_field => $_val) {
            if (array_key_exists($_field, $_data) && $_data[$_field] != $_val) {
                $passed = false;
                break;
            }
        }

        if ($passed) {
            if ($args['key_by']) {
                $result[$_data[$args['key_by']]] = $_data;
            } else {
                $result[] = $_data;
            }
        }
    }

    return apply_filters('stm_listings_attributes', $result, $args);
}

/**
 * Get single attribute configuration by taxonomy slug
 *
 * @param $taxonomy
 *
 * @return array|mixed
 */
function stm_listings_attribute($taxonomy)
{
    $attributes = stm_listings_attributes(array('key_by' => 'slug'));
    if (array_key_exists($taxonomy, $attributes)) {
        return $attributes[$taxonomy];
    }

    return array();
}

/**
 * Get all terms grouped by taxonomy for the filter
 *
 * @return array
 */
function stm_listings_filter_terms()
{
    static $terms;

    if (isset($terms)) {
        return $terms;
    }

    $filters = stm_listings_attributes(array('where' => array('use_on_car_filter' => true), 'key_by' => 'slug'));

    $numeric = array_keys(stm_listings_attributes(array(
        'where' => array(
            'use_on_car_filter' => true,
            'numeric' => true
        ),
        'key_by' => 'slug'
    )));
    $_terms = get_terms(array(
        'taxonomy' => $numeric,
        'hide_empty' => false,
        'update_term_meta_cache' => false,
    ));

    $taxes = array_diff(array_keys($filters), $numeric);
    $taxes = apply_filters('stm_listings_filter_taxonomies', $taxes);

    $_terms = array_merge($_terms, get_terms(array(
        'taxonomy' => $taxes,
        'hide_empty' => false,
        'update_term_meta_cache' => false,
    )));

    $terms = array();

    foreach ($taxes as $tax) {
        $terms[$tax] = array();
    }

    foreach ($_terms as $_term) {
        $terms[$_term->taxonomy][$_term->slug] = $_term;
    }

    $terms = apply_filters('stm_listings_filter_terms', $terms);

    return $terms;
}

/**
 * Drop-down options grouped by attribute for the filter
 *
 * @return array
 */
function stm_listings_filter_options()
{
    static $options;

    if (isset($options)) {
        return $options;
    }

    $filters = stm_listings_attributes(array('where' => array('use_on_car_filter' => true), 'key_by' => 'slug'));
    $terms = stm_listings_filter_terms();
    $options = array();

    foreach ($terms as $tax => $_terms) {
        $_filter = isset($filters[$tax]) ? $filters[$tax] : array();
        $options[$tax] = _stm_listings_filter_attribute_options($tax, $_terms);

        if (empty($_filter['numeric'])) {
            $_remaining = stm_listings_options_remaining($terms[$tax], stm_listings_query());

			foreach ($_terms as $_term) {
				if (isset($_remaining[$_term->term_taxonomy_id])) {
					$options[$tax][$_term->slug]['count'] = (int) $_remaining[$_term->term_taxonomy_id];
				}
				else {
					$options[$tax][$_term->slug]['count'] = 0;
					$options[$tax][$_term->slug]['disabled'] = true;
				}
			}
        }
    }

    $options = apply_filters('stm_listings_filter_options', $options);

    return $options;
}

/**
 * Get list of attribute options filtered by query
 *
 * @param array $terms
 * @param WP_Query $from
 *
 * @return array
 */
function stm_listings_options_remaining($terms, $from = null)
{
    /** @var WP_Query $from */
    $from = is_null($from) ? $GLOBALS['wp_query'] : $from;

    if (empty($terms) || (!count($from->get('meta_query', array())) && !count($from->get('tax_query')))) {
        return array();
    }

    global $wpdb;
    $meta_query = new WP_Meta_Query($from->get('meta_query', array()));
    $tax_query = new WP_Tax_Query($from->get('tax_query', array()));
    $meta_query_sql = $meta_query->get_sql('post', $wpdb->posts, 'ID');
    $tax_query_sql = $tax_query->get_sql($wpdb->posts, 'ID');
    $term_ids = wp_list_pluck($terms, 'term_taxonomy_id');
    $post_type = $from->get('post_type');

    // Generate query
    $query = array();
    $query['select'] = "SELECT term_taxonomy.term_taxonomy_id, COUNT( {$wpdb->posts}.ID ) as count";
    $query['from'] = "FROM {$wpdb->posts}";
    $query['join'] = "INNER JOIN {$wpdb->term_relationships} AS term_relationships ON {$wpdb->posts}.ID = term_relationships.object_id";
    $query['join'] .= "\nINNER JOIN {$wpdb->term_taxonomy} AS term_taxonomy USING( term_taxonomy_id )";
    //$query['join'] .= "\nINNER JOIN {$wpdb->terms} AS terms USING( term_id )";
    $query['join'] .= "\n" . $tax_query_sql['join'] . $meta_query_sql['join'];
    $query['where'] = "WHERE {$wpdb->posts}.post_type IN ( '{$post_type}' ) AND {$wpdb->posts}.post_status = 'publish' ";
    $query['where'] .= "\n" . $tax_query_sql['where'] . $meta_query_sql['where'];
    $query['where'] .= "\nAND term_taxonomy.term_taxonomy_id IN (" . implode(',', array_map('absint', $term_ids)) . ")";
    $query['group_by'] = "GROUP BY term_taxonomy.term_taxonomy_id";

    $query = apply_filters('stm_listings_options_remaining_query', $query);
    $query = join("\n", $query);

    $results = $wpdb->get_results($query);
    $results = wp_list_pluck($results, 'count', 'term_taxonomy_id');
    return $results;

//    $terms = wp_list_pluck($terms, 'slug', 'term_taxonomy_id');
//    $remaining = array_intersect_key($terms, $results);
//    $remaining = array_flip($remaining);
//
//    return $remaining;
}

/**
 * Filter configuration array
 *
 * @return array
 */
function stm_listings_filter()
{
    $query = stm_listings_query();
    $total = $query->found_posts;
    $filters = stm_listings_attributes(array('where' => array('use_on_car_filter' => true), 'key_by' => 'slug'));
    $options = stm_listings_filter_options();
    $terms = stm_listings_filter_terms();
    $url = stm_get_listing_archive_link( array_diff_key( $_GET, array_flip( array( 'ajax_action', 'fragments' ) ) ) );

    return apply_filters( 'stm_listings_filter', compact( 'options', 'filters', 'total', 'url' ), $terms );
}

/**
 * Retrieve input data from $_POST, $_GET by path
 *
 * @param $path
 * @param $default
 *
 * @return mixed
 */
function stm_listings_input($path, $default = null)
{

    if (trim($path, '.') == '') {
        return $default;
    }

    foreach (array($_POST, $_GET) as $source) {
        $value = $source;
        foreach (explode('.', $path) as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                $value = null;
                break;
            }

            $value = &$value[$key];
        }

        if (!is_null($value)) {
            return $value;
        }
    }

    return $default;
}

/**
 * Current URL with native WP query string parameters ()
 *
 * @return string
 */
function stm_listings_current_url()
{
    global $wp, $wp_rewrite;

    $url = preg_replace("/\/page\/\d+/", '', $wp->request);
    $url = home_url($url . '/');
    if (!$wp_rewrite->permalink_structure) {
        parse_str($wp->query_string, $query_string);

        $leave = array('post_type', 'pagename', 'page_id', 'p');
        $query_string = array_intersect_key($query_string, array_flip($leave));

        $url = trim(add_query_arg($query_string, $url), '&');
        $url = str_replace('&&', '&', $url);
    }

    return apply_filters( 'stm_listings_current_url', $url );
}

function _stm_listings_filter_attribute_options($taxonomy, $_terms)
{

    $attribute = stm_listings_attribute($taxonomy);
    $attribute = wp_parse_args($attribute, array(
        'slug' => $taxonomy,
        'single_name' => '',
        'numeric' => false,
        'slider' => false,
    ));

    $options = array();

    if (!$attribute['numeric']) {


        $options[''] = array(
            'label' => apply_filters('stm_listings_default_tax_name', $attribute['single_name']),
            'selected' => stm_listings_input($attribute['slug']) == null,
            'disabled' => false,
        );

        foreach ($_terms as $_term) {
            $options[$_term->slug] = array(
                'label' => $_term->name,
                'selected' => stm_listings_input($attribute['slug']) == $_term->slug,
                'disabled' => false,
                'count' => $_term->count,
            );
        }
    } else {
        $numbers = array();
        foreach ($_terms as $_term) {
            $numbers[intval($_term->slug)] = $_term->name;
        }
        ksort($numbers);

        if (!empty($attribute['slider'])) {
            foreach ($numbers as $_number => $_label) {
                $options[$_number] = array(
                    'label' => $_label,
                    'selected' => stm_listings_input($attribute['slug']) == $_label,
                    'disabled' => false,
                );
            }
        } else {

            $options[''] = array(
                'label' => sprintf(__('Max %s', 'stm_vehicles_listing'), $attribute['single_name']),
                'selected' => stm_listings_input($attribute['slug']) == null,
                'disabled' => false,
            );

            $_prev = null;
            $_affix = empty($attribute['affix']) ? '' : __($attribute['affix'], 'stm_vehicles_listing');

            foreach ($numbers as $_number => $_label) {

                if ($_prev === null) {
                    $_value = '<' . $_number;
                    $_label = '< ' . $_label . ' ' . $_affix;
                } else {
                    $_value = $_prev . '-' . $_number;
                    $_label = $_prev . '-' . $_label . ' ' . $_affix;
                }

                $options[$_value] = array(
                    'label' => $_label,
                    'selected' => stm_listings_input($attribute['slug']) == $_value,
                    'disabled' => false,
                );

                $_prev = $_number;
            }

            if ($_prev) {
                $_value = '>' . $_prev;
                $options[$_value] = array(
                    'label' => '>' . $_prev . ' ' . $_affix,
                    'selected' => stm_listings_input($attribute['slug']) == $_value,
                    'disabled' => false,
                );
            }
        }
    }

    return $options;
}

if (!function_exists('stm_listings_user_defined_filter_page')) {
    function stm_listings_user_defined_filter_page()
    {
        return apply_filters('stm_listings_inventory_page_id', get_theme_mod('listing_archive', false));
    }
}

function stm_listings_paged_var()
{
    global $wp;

    $paged = null;

    if (isset($wp->query_vars['paged'])) {
        $paged = $wp->query_vars['paged'];
    } elseif (isset($_GET['paged'])) {
        $paged = sanitize_text_field($_GET['paged']);
    }

    return $paged;
}

/**
 * Listings post type identifier
 *
 * @return string
 */
if (!function_exists('stm_listings_post_type')) {
    function stm_listings_post_type()
    {
        return apply_filters('stm_listings_post_type', 'listings');
    }
}

add_action('init', 'stm_listings_init', 1);

function stm_listings_init()
{

    $options = get_option('stm_post_types_options');

    $stm_vehicle_options = wp_parse_args($options, array(
        'listings' => array(
            'title' => __('Listings', 'stm_vehicles_listing'),
            'plural_title' => __('Listings', 'stm_vehicles_listing'),
            'rewrite' => 'listings'
        ),
    ));

    register_post_type(stm_listings_post_type(), array(
        'labels' => array(
            'name' => $stm_vehicle_options['listings']['plural_title'],
            'singular_name' => $stm_vehicle_options['listings']['title'],
            'add_new' => __('Add New', 'stm_vehicles_listing'),
            'add_new_item' => __('Add New Item', 'stm_vehicles_listing'),
            'edit_item' => __('Edit Item', 'stm_vehicles_listing'),
            'new_item' => __('New Item', 'stm_vehicles_listing'),
            'all_items' => __('All Items', 'stm_vehicles_listing'),
            'view_item' => __('View Item', 'stm_vehicles_listing'),
            'search_items' => __('Search Items', 'stm_vehicles_listing'),
            'not_found' => __('No items found', 'stm_vehicles_listing'),
            'not_found_in_trash' => __('No items found in Trash', 'stm_vehicles_listing'),
            'parent_item_colon' => '',
            'menu_name' => __($stm_vehicle_options['listings']['plural_title'], 'stm_vehicles_listing'),
        ),
        'menu_icon' => 'dashicons-location-alt',
        'show_in_nav_menus' => true,
        'supports' => array('title', 'editor', 'thumbnail', 'comments', 'excerpt', 'author'),
        'rewrite' => array('slug' => $stm_vehicle_options['listings']['rewrite']),
        'has_archive' => true,
        'public' => true,
        'publicly_queryable' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'query_var' => true,
        'hierarchical' => false,
        'menu_position' => null,
    ));

}

add_filter('get_pagenum_link', 'stm_listings_get_pagenum_link');

function stm_listings_get_pagenum_link($link)
{
    return remove_query_arg('ajax_action', $link);
}

/*Functions*/
function stm_check_motors()
{
    return apply_filters('stm_listing_is_motors_theme', false);
}

require_once 'templates.php';
require_once 'enqueue.php';
require_once 'vehicle_functions.php';

add_action('init', 'stm_listings_include_customizer');

function stm_listings_include_customizer()
{
    if (!stm_check_motors()) {
        require_once 'customizer/customizer.class.php';
    }
}

function stm_listings_search_inventory()
{
    return apply_filters('stm_listings_default_search_inventory', false);
}