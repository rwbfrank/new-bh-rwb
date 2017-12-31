<?php

/* 100% match ms */
defined('ABSPATH') or die("you do not have access to this page!");
class rsssl_scan {
    private static $_this;
    //private $mixed_content_detected       = FALSE;
    private $nr_requests_in_one_run       = 5;
    private $nr_files_in_one_run          = 200;
    private $file_array                   = array();
    public $files_with_blocked_resources = array();
    public $posts_with_external_resources= array();
    public $widgets_with_external_resources = array();
    public $posts_with_blocked_resources = array();
    public $widgets_with_blocked_resources = array();
    private $external_resources           = array();
    public $blocked_resources            = array();
    public $source_of_resource           = array();//match filename to url
    private $webpages                     = array();
    private $css_js_files                 = array();
    public $css_js_with_mixed_content    = array();
    public $traced_urls                  = array();
    private $tables_with_blocked_resources= array();
    private $files_with_css_js            = array();
    private $files_with_external_css_js   = array();
    private $external_css_js_with_mixed_content=array();
    private $queue                        = 0;
    private $scan_completed_no_errors     = "NEVER";
    private $last_scan_time;
    //private $timeout                      = 5;
    //private $rsssl_plugin_domain          = "https://www.really-simple-ssl.com";
    private $error_number=0;
    private $safe_domains= array(
        "http://",
        "http://gmpg.org/xfn/11",
        "http://player.vimeo.com/video/",
        "http://www.youtube.com/embed/",
        "http://platform.twitter.com/widgets.js"
    );
    public $ignored_urls;


    function __construct() {
        if ( isset( self::$_this ) )
            wp_die( sprintf( __( '%s is a singleton class and you cannot create a second instance.','really-simple-ssl-pro' ), get_class( $this ) ) );

        self::$_this = $this;

        add_action("plugins_loaded", array($this, "process_scan_submit"), 100);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('maybe_show_expiration_alert', array($this, 'maybe_show_expiration_alert'));

        add_action('admin_init', array($this, 'clear_transient'), 10);
        //add_action('admin_init', array($this, 'load_results'), 20);
        add_filter('rsssl_tabs', array($this,'add_scan_tab'),10,3 );
        add_action('show_tab_scan', array($this, 'add_scan_page'));
        add_action('rsssl_configuration_page', array($this, 'configuration_page_scan'),20);

        add_action("rsssl_scan_modals", array($this, "fix_post_modal"));
        add_action("rsssl_scan_modals", array($this, "ignore_url_modal"));
        add_action("rsssl_scan_modals", array($this, "fix_file_modal"));
        add_action("rsssl_scan_modals", array($this, "fix_cssjs_modal"));
        add_action("rsssl_scan_modals", array($this, "roll_back_modal"));
        add_action("rsssl_scan_modals", array($this, "editor_modal"));

        add_action( 'wp_ajax_scan', array($this,'scan_callback'));
    }

    static function this() {
        return self::$_this;
    }



    /*
     * The transient is cleared when requested, and expiration time has passed.
     * For backward compatibility, we move some data to the new options.
     *
     * */


    public function clear_transient(){

        if (get_option('rlrsssl_scan')) {
            $options = get_option('rlrsssl_scan');

            if (isset($options['last_scan_time'])) update_option('rsssl_last_scan_time', $options['last_scan_time']);
            if (isset($options['scan_completed_no_errors'])) update_option('rsssl_scan_completed_no_errors', $options['scan_completed_no_errors']);
            delete_option('rlrsssl_scan');
        }

        //clear by retrieving the data
        $options = get_transient('rlrsssl_scan');

    }

    public function maybe_show_expiration_alert(){
        $completed_scan_before = ($this->get_last_scan_time() != __("Never", "really-simple-ssl-pro"));
        if (!get_transient('rlrsssl_scan') && $completed_scan_before) {
            ?>
            <div class="alert alert-warning alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <?php _e('You have scanned your site before, but the scan results are cleared from the cache. Run a new scan to see the results.','really-simple-ssl-pro');?>
            </div>
            <?php
        }
    }

    public function process_scan_submit(){
        if (!class_exists('rsssl_admin')) return;
        if (!current_user_can('manage_options')) return;

        //$bypass_nonce = (defined("rsssl_bypass_nonce") && rsssl_bypass_nonce) ? true : false;
        //if (!$bypass_nonce && !( isset( $_POST['rsssl_nonce'] ) && wp_verify_nonce( $_POST['rsssl_nonce'], 'rsssl_nonce' ))) return;
        if (isset($_POST['rsssl_no_scan']) ) {

            if (isset($_POST['rsssl_show_ignore_urls']) ) {
                update_option("rsssl_show_ignore_urls", 1);
            } else {
                update_option("rsssl_show_ignore_urls", 0);
            }

        } elseif (isset($_POST['rsssl_do_scan']) || isset($_POST['rsssl_do_scan_home']) ) {

            add_action('admin_print_footer_scripts', array($this,'insert_scan'));
            if (isset($_POST['rsssl_disable_bruteforce_dbsearch'])) {
                update_option("rsssl_disable_bruteforce_dbsearch", 1);
            } else {
                update_option("rsssl_disable_bruteforce_dbsearch", 0);
            }

            if (isset($_POST['rsssl_show_ignore_urls'])) {
                update_option("rsssl_show_ignore_urls", 1);
            } else {
                update_option("rsssl_show_ignore_urls", 0);
            }

            update_option("rsssl_scan_progress", 1);

            if(isset($_POST['rsssl_do_scan'])) {
                update_option("rsssl_scan_type", "all");
            } else {
                update_option("rsssl_scan_type", "home");
            }

        }
    }

    public function url_is_safe($url) {
        if (in_array($url, $this->ignored_urls)){
            return true;
        }

        return false;

    }


    public function insert_scan() {
        if (!current_user_can('manage_options')) return;

        $ajax_nonce = wp_create_nonce( "rsssl_nonce" );
        ?>
        <script type='text/javascript'>
            jQuery(document).ready(function($) {
                rsssl_do_ajax_scan();
                function rsssl_do_ajax_scan(progress, output) {
                    progress = typeof progress !== 'undefined' ? progress : 5;
                    output = typeof output !== 'undefined' ? output : '<?php echo esc_html(__("Generating list of website pages","really-simple-ssl-pro"))?>';
                    $('#rsssl-scan-list').replaceWith(
                        '<div id="rsssl-scan-list"><div class="rsssl  progress">'+
                        '<div class="rsssl bar progress-bar  progress-bar-striped active" role="progressbar" aria-valuenow="'+progress+'" aria-valuemin="0" aria-valuemax="100" style="width: '+progress+'%">'+
                        '</div>'+
                        '</div><div>'+output+'</div></div>');
                    var data = {
                        'action': 'scan',
                        'rsssl_nonce': '<?php echo $ajax_nonce; ?>',
                    };
                    $.post(ajaxurl, data, function(response) {
                        var obj;
                        if (!response) {
                            output = "Scan not completed, please try again";
                        } else {
                            obj = jQuery.parseJSON( response );
                            if (obj['progress']=='finished') {
                                $('#rsssl-scan-list').replaceWith(obj['output']);
                            } else {
                                rsssl_do_ajax_scan(obj['progress'], obj['output']);
                            }
                        }
                    });
                }
            });
        </script>

        <?php
    }

    private function get_last_scan_time(){
        if ($this->last_scan_time == __("Never", "really-simple-ssl-pro")) return $this->last_scan_time;
        if (!empty($this->last_scan_time) ) {
            //$date = date(DateTime::RFC850);
            return
                date(get_option('date_format'), intval($this->last_scan_time )) .
                "&nbsp;" .
                __("at", "really-simple-ssl-pro") .
                "&nbsp;" .
                date("H:i", $this->last_scan_time );
        }
        return false;
    }

    public function add_scan_page(){
        ?>
        <div id="rsssl">
            <?php do_action('maybe_show_expiration_alert')?>

            <form id="rsssl_scan_form" action="" method="POST">
                <h1><?php _e("Mixed content scan", "really-simple-ssl-pro");?></h1>
                <ul class="rsssl-tips">
                    <li><?php _e("For best results, deactivate caching and/or security plugins.", "really-simple-ssl-pro");?></li>
                    <!-- <li><?php _e("If your scan freezes, try disabling curl or brute force database search.", "really-simple-ssl-pro");?></li> -->
                </ul>
                <!-- <input type="checkbox" name="rsssl_disable_bruteforce_dbsearch" id="rsssl_disable_bruteforce_dbsearch"  <?php echo (get_option("rsssl_disable_bruteforce_dbsearch")==1) ? 'checked="checked"' : "";?>>
     <?php _e("Disable brute force database search.", "really-simple-ssl-pro");?>
     <input type="checkbox" name="rsssl_disable_curl" id="rsssl_disable_curl"  <?php echo (get_option("rsssl_disable_curl")==1) ? 'checked="checked"' : "";?>>
     <?php _e("Disable curl.", "really-simple-ssl-pro");?><br> -->
                <?php wp_nonce_field( 'rsssl_nonce', 'rsssl_nonce' );?>


                <div class="rsssl-scan-options">
                    <div class="rsssl-buttons-scan">
                        <div class="rsssl-btn-group-scan" role="group" aria-label="...">
                            <?php
                            //check if the per page plugin is used.
                            if (class_exists('REALLY_SIMPLE_SSL_PP')) {?>
                                <button type="submit" class="btn btn-primary"  id="rsssl_do_scan" name="rsssl_do_scan"> <?php _e("SCAN", "really-simple-ssl-pro");?></button>
                            <?php } else { ?>
                                <button type="submit" class="btn btn-primary"  id="rsssl_do_scan_home" name="rsssl_do_scan_home"><?php _e("QUICK SCAN", "really-simple-ssl-pro");?></button>
                                <button type="submit" class="btn btn-success" id="rsssl_do_scan" name="rsssl_do_scan"><?php _e("FULL SCAN", "really-simple-ssl-pro");?></button>
                            <?php } ?>
                            <button class="btn btn-warning" id="rsssl-more-options-btn"><span class="glyphicon glyphicon-cog"></span></button>
                        </div>
                    </div></div>
                <div class="rsssl-scan-more-options" id="rsssl-more-options-container">
                    <h3> Advanced Settings </h3>

                    <table class="form-table">
                        <tr>
                            <td scope="row">Disable brute force database search</td>
                            <td>
              <span class="rsssl-tooltip-right tooltip-right" data-rsssl-tooltip="If your scan freezes, you can disable this option to exclude datables tables from scans.">
              <input type="checkbox" name="rsssl_disable_bruteforce_dbsearch" id="rsssl_disable_bruteforce_dbsearch"  <?php echo (get_option("rsssl_disable_bruteforce_dbsearch")==1) ? 'checked="checked"' : "";?>>
              <span class="dashicons dashicons-editor-help"></span>
                            </td>
                        </tr>
                        <tr>
                            <td scope="row">Show ignored URL's</td>
                            <td>
              <span class="rsssl-tooltip-right tooltip-right" data-rsssl-tooltip="This option shows ignored URLs.">

              <input type="checkbox" name="rsssl_show_ignore_urls" id="rsssl_show_ignore_urls"  <?php echo (get_option("rsssl_show_ignore_urls")==1) ? 'checked="checked"' : "";?>>
              <span class="dashicons dashicons-editor-help"></span>
                            </td>
                        </tr>
                    </table>
                </div>

            </form>
            <div id="rsssl-scan-list">
                <?php
                $this->load_results();
                echo __("Last scan: ","really_simple_ssl").$this->get_last_scan_time()."<br><br>";
                echo $this->generate_output("html");
                ?>
            </div>
            <br>
            <?php do_action("rsssl_pro_rollback_button");?>
            <?php do_action("rsssl_scan_modals");?>
        </div><!-- end rsssl wrapper -->
        <?php
    }



    public function load_translation() {
        $success = load_plugin_textdomain('really-simple-ssl-pro', FALSE, dirname(plugin_basename(__FILE__)).'/languages/');
    }

    public function scan_callback() {

        $output="";
        $step = 100/11;

        $iteration = get_option("rsssl_scan_progress",1);
        error_log("iteratie ".$iteration);
        $in_queue = FALSE;
        $mixed_content_detected = false;
        global $wpdb;

        $this->load_translation();

        check_ajax_referer( 'rsssl_nonce', 'rsssl_nonce' );

        if ($iteration==1) {
            $this->load_results(TRUE); //true to reset all values
            error_log("generating web page list");

            //get all pages of this website
            $this->webpages = $this->get_webpage_list();
            $output = array(
                'progress' => $iteration*$step,
                'output'=> __("Searching for js and css files and external resources in website","really-simple-ssl-pro"),
            );
            $this->save_results();
        }

        if ($iteration==2) {
            error_log("searching js css files and external resources");
            $this->load_results();
            //find all css and js files
            $this->parse_for_css_js_and_external_files($this->webpages);

            $progress = $this->calculate_queue_progress($this->webpages, $this->queue, $step, $iteration);
            $current_queue = ($this->queue==0) ? count($this->webpages) : $this->queue;
            $output = array(
                'progress' => $progress,
                'output'=> __("Searching for js and css files and links to external resources in website, ".$current_queue." of ".count($this->webpages),"really-simple-ssl-pro"),
            );
            $in_queue = $this->still_in_queue($this->webpages);
            $this->save_results();
        }

        if ($iteration==3) {
            error_log("searching mixed content in js and css files");
            $this->load_results();

            //parse these files for http links

            $this->css_js_with_mixed_content = $this->parse_for_http($this->css_js_files, $this->css_js_with_mixed_content);

            $progress = $this->calculate_queue_progress($this->css_js_files, $this->queue, $step, $iteration);
            $current_queue = ($this->queue==0) ? count($this->css_js_files) : $this->queue;
            $output = array(
                'progress' => $progress,
                'output' => __("Searching for mixed content in css and js files, ".$current_queue." of ".(count($this->css_js_files)+2),"really-simple-ssl-pro"),
            );
            $in_queue = $this->still_in_queue($this->css_js_files);
            $this->save_results();
        }

        if ($iteration==4) {
            error_log("generating file list");
            $this->load_results();
            $this->get_file_array();
            $output = array(
                'progress' => $iteration*$step,
                'output' => __("Generating file list","really-simple-ssl-pro"),
            );

            $this->save_results();
        }

        if ($iteration==5) {
            error_log("checking which posts contain external resources");
            $this->load_results();
            $this->search_posts_for_external_urls();
            //Also search for widgets with external urls
            $this->search_widgets_for_external_urls();

            $this->save_results();
            $output = array(
                'progress' => $iteration * $step,
                'output' => __("Checking which posts contain external resources","really-simple-ssl-pro"),
            );
        }

        if ($iteration==6) {
            error_log("checking if external resources can load over ssl");
            $this->load_results();

            //check which of these files cannot load over ssl
            $this->find_blocked_resources($this->external_resources);

            $progress = $this->calculate_queue_progress($this->external_resources, $this->queue, $step, $iteration);
            $in_queue = $this->still_in_queue($this->external_resources);
            $current_queue = ($this->queue==0) ? count($this->external_resources) : $this->queue;
            $this->save_results();
            $output = array(
                'progress' => $progress,
                'output' => __("Checking which resources can't load over ssl, ".$current_queue." of ".count($this->external_resources),"really-simple-ssl-pro"),
            );
        }

        if ($iteration==7) {
            error_log("checking if external js or css files contain http links");
            $this->load_results();

            $external_css_js_files = $this->get_external_css_js_files();
            //check which of these files contain http links.
            $this->external_css_js_with_mixed_content = $this->parse_external_files_for_http($external_css_js_files, $this->external_css_js_with_mixed_content);

            $progress = $this->calculate_queue_progress($external_css_js_files, $this->queue, $step, $iteration);
            $in_queue = $this->still_in_queue($external_css_js_files);
            $current_queue = ($this->queue==0) ? count($external_css_js_files) : $this->queue;
            $this->save_results();
            $output = array(
                'progress' => $progress,
                'output' => __("Checking if external js or css files contain http links, ".$current_queue." of ".count($external_css_js_files),"really-simple-ssl-pro"),
            );
        }

        if ($iteration==8) {
            error_log("Looking up blocked resources in files");
            $this->load_results();

            //search in php files and db for references to ext res.
            $this->search_files_for_urls();

            $progress = $this->calculate_queue_progress($this->file_array, $this->queue, $step, $iteration);
            $in_queue = $this->still_in_queue($this->file_array);
            $current_queue = ($this->queue==0) ? count($this->file_array) : $this->queue;
            $this->save_results();
            $output = array(
                'progress' => $progress,
                'output' => __("Looking up blocked resources in files, ".$current_queue." of ".count($this->file_array),"really-simple-ssl-pro"),
            );
        }

        if ($iteration==9) {
            error_log("Looking up blocked resources in posts");
            $this->load_results();
            $this->find_posts_with_blocked_urls();

            $this->save_results();
            $output = array(
                'progress' => $step*$iteration,
                'output' => __("Looking up blocked resources in posts","really-simple-ssl-pro"),
            );
        }

        if ($iteration==10) {
            error_log("looking up widgets with blocked resources");
            $this->load_results();
            $this->find_widgets_with_blocked_urls();
            $this->save_results();
            $output = array(
                'progress' => $step*$iteration,
                'output' => __("Looking up blocked resources in widgets","really-simple-ssl-pro"),
            );
        }

        if ($iteration==11) {
            $this->load_results();
            //look up any stray urls we didn't locate yet, by a brute force search in the db.
            $not_accounted_for = array_diff($this->blocked_resources, $this->traced_urls);

            $tables_with_blocked_resources = array();
            if (get_option("rsssl_disable_bruteforce_dbsearch")!=1) {
                foreach($not_accounted_for as $url ){
                    error_log("executing search all db. ");
                    $this->tables_with_blocked_resources = $this->searchAllDB($url);
                }
            }
            //echos the output
            $output = $this->generate_output();

        }

        error_log("que output ".$in_queue);
        if (!$in_queue) $iteration++;
        update_option("rsssl_scan_progress", $iteration);


        $obj = new stdClass();
        $obj = $output;
        echo json_encode($obj);
        wp_die(); // this is required to terminate immediately and return a proper response
    }

    /*
      Lookup all posts that have a blocked external url
    */

    private function find_posts_with_blocked_urls() {
        global $wpdb;
        $posts_array = array();

        $blocked_urls = $this->blocked_resources;

        $posts_with_external_resources = $this->posts_with_external_resources;

        foreach ($posts_with_external_resources as $post_id=>$urls) {

            //check if one of the found urls is in the blocked resources array.
            foreach($urls as $url) {
                if ($this->in_array_r($url, $blocked_urls)) {
                    //add post to post list with blocked resources
                    if (!in_array($post_id, $posts_array)) $posts_array[] = $post_id;
                    $this->traced_urls[] = $url;
                }
            }
        }
        $this->posts_with_blocked_resources = $posts_array;
    }


    private function get_widget_data($title){

        //Get the widget type, before the -
        $type =  substr($title, 0, strpos($title, '-'));
        //Get the widget id, after type -
        $id = substr($title, strpos($title, '-')+1);
        //Get the widget options, save to array to retrieve the HTML
        $widget_array = get_option("widget_".$type);
        if (!isset($widget_array[$id]["content"])) return false;

        $widget_html = $widget_array[$id]["content"];
        $widget_title= $widget_array[$id]["title"];


        return array("type" => $type, "id" => $id, "html"=>$widget_html, "title"=>$widget_title);
    }

    private function get_widget_area($widget_title){
        $widget_areas = wp_get_sidebars_widgets();

        foreach($widget_areas as $widget_area_name => $widgets) {
            $found=false;
            foreach($widgets as $widget_title) {
                $found = true;
            }
            if ($found) return $widget_area_name;

        }

        return false;
    }

    /**
     *   Get the friendly title for a widget area
     *   @param string widget index
     *   @return string
     */

    private function get_widget_title($area){

        global $wp_registered_sidebars, $wp_registered_widgets;

        if (isset($wp_registered_sidebars[$area])) {
            $title = $wp_registered_sidebars[$area]["name"];
        }

        if (isset($wp_registered_widgets[$area])) {
            $title = $wp_registered_widgets[$area]["name"];
        }

        return $title;
    }

    /**
     *
     * Search for widgets in external URLs
     *
     *
     *
     **/

    private function search_widgets_for_external_urls() {
        global $wpdb;
        $query = "";

        //$external_resources = $this->external_resources;
        $patterns = $this->external_domain_patterns();
        $widgets_array = array();


        $widget_areas = wp_get_sidebars_widgets();
        foreach($widget_areas as $widgets) {
            foreach($widgets as $widget_title) {
                $widget_area = $this->get_widget_area($widget_title);
                $widget_data = $this->get_widget_data($widget_title);
                if (!$widget_data) continue;

                $type = $widget_data["type"];
                $id = $widget_data["id"];
                $html = $widget_data["html"];

                // $widgets = unserialize($result->option_value);
                // error_log(print_r($widgets,true));

                foreach($patterns as $pattern) {
                    if (preg_match_all($pattern, $html, $matches, PREG_PATTERN_ORDER)) {
                        foreach($matches[1] as $key=>$match) {
                            //list to show all posts with external urls
                            $url = $matches[1][$key].$matches[2][$key];

                            if (!isset($widgets_array[$widget_title]) || (isset($widgets_array[$widget_title]) && !in_array($url, $widgets_array[$widget_title])))
                                $widgets_array[$widget_title][] = $matches[1][$key].$matches[2][$key];
                        }
                    }
                }
            }

        }
        $this->widgets_with_external_resources = $widgets_array;
    }

    /**
     *      Search for
     *
     *      @param void
     *      @return
     *
     */

    private function find_widgets_with_blocked_urls() {
        global $wpdb;

        $blocked_urls = $this->blocked_resources;
        $widgets_with_blocked_resources = array();
        $widgets_with_external_resources = $this->widgets_with_external_resources;

        foreach ($widgets_with_external_resources as $widget_name => $urls) {

            //check if one of the found urls is in the blocked resources array.
            foreach($urls as $url) {
                if ($this->in_array_r($url, $blocked_urls)) {
                    //add post to post list with blocked resources
                    if (!in_array($widget_name, $widgets_with_blocked_resources)) $widgets_with_blocked_resources[] = $widget_name;
                    $this->traced_urls[] = $url;
                }
            }
        }
        $this->widgets_with_blocked_resources = $widgets_with_blocked_resources;
    }


    /*
          Scan all posts for external urls.
    */

    private function search_posts_for_external_urls() {
        global $wpdb;
        $query = "";

        $external_resources = $this->external_resources;
        $patterns = $this->external_domain_patterns();
        $posts_array = array();
        //look only in posts of used post types.
        $args = array(
            'public'   => true,
        );
        $post_types = get_post_types( $args);
        $post_types_query = array();
        foreach ( $post_types  as $post_type ) {
            $post_types_query[] = " post_type = '".$post_type."'";
        }

        $posttypes_query = implode(" OR ", $post_types_query);

        $query = "select ID, post_content, guid from $wpdb->posts  where post_status='publish' and (".$posttypes_query.") order by post_modified DESC limit 5000";

        $results = $wpdb->get_results($query);
        foreach ($results as $result) {
            $str = $result->post_content;
            foreach ($patterns as $pattern){
                if (preg_match_all($pattern, $str, $matches, PREG_PATTERN_ORDER)) {
                    foreach($matches[1] as $key=>$match) {
                        //list to show all posts with external urls
                        $url = $matches[1][$key].$matches[2][$key];
                        //check if already in array
                        if (!isset($posts_array[$result->ID]) || (isset($posts_array[$result->ID]) && !in_array($url, $posts_array[$result->ID])))
                            $posts_array[$result->ID][] = $url;

                        //list to check all external urls.
                        if (!in_array($url, $external_resources))
                            $external_resources[] = $url;

                        //list to track those resource back to where they came from.
                        $this->source_of_resource[$url] = $result->ID;
                    }
                }
            }
        }

        $this->posts_with_external_resources = $posts_array;
        $this->external_resources = $external_resources;
    }


    /*
      check each item in the array to see if it can load over https, it no, adds it to the output array.

    */

    private function find_blocked_resources($external_resources){

        $blocked_urls = $this->blocked_resources;

        $start = $this->queue;
        $count=0;
        for ($i = $start; $i < count($external_resources); ++$i) {
            $this->queue = $i+1;

            //sometimes indexes are removed as doubles, skip to next.
            if (!isset($external_resources[$i])) continue;
            $count++;
            $url = $external_resources[$i];
            $ssl_url = str_replace("http://", "https://", $url);

            if ($url!="http://" && !in_array($url, $blocked_urls)) {
                $html = $this->get_contents($ssl_url);
                //if the mixed content fixer is active, the url might be https.
                if($this->error_number!=0) $blocked_urls[] = str_replace("https://", "http://", $url);
            }
            if ($count>$this->nr_requests_in_one_run) break;
        }
        $this->blocked_resources = $blocked_urls;
    }

    /*

        Links in these files can contain http links, if these domains can be loaded over https.
        Generates a list of files with urls that could not be loaded over https.

    */

    private function search_files_for_urls() {
        $file_array = $this->file_array;

        $start = $this->queue;
        $count=0;

        for ($i = $start; $i < count($file_array); ++$i) {
            $this->queue = $i+1;

            //sometimes indexes are removed as doubles, skip to next.
            if (!isset($file_array[$i])) continue;
            $count++;

            $file = $file_array[$i];
            if(file_exists($file) ) {
                $html = file_get_contents($file);
                //search the files where blocked resources are used.
                foreach($this->blocked_resources as $url) {
                    if (strpos($html, $url)!==FALSE) {
                        if (!isset($this->files_with_blocked_resources[$file]) || ( isset($this->files_with_blocked_resources[$file]) && !in_array($url, $this->files_with_blocked_resources[$file])))
                            $this->files_with_blocked_resources[$file][] = $url;
                        //by adding this one to a tracing list, we keep track of the urls that are accounted for.
                        if (!in_array($url, $this->traced_urls))
                            $this->traced_urls[] = $url;
                    }
                }

                //search the files where external css or js is used.
                foreach($this->external_css_js_with_mixed_content as $url => $value) {
                    if (strpos($html, $url)!==FALSE) {
                        if (!isset($this->files_with_external_css_js[$file]) || (isset($this->files_with_external_css_js[$file]) && !in_array($url, $this->files_with_external_css_js[$file])))
                            $this->files_with_external_css_js[$file][] = $url;
                        //by adding this one to a tracing list, we keep track of the urls that are accounted for.
                        if (!in_array($url, $this->traced_urls))
                            $this->traced_urls[] = $url;
                    }
                }
            }
            if ($count>$this->nr_files_in_one_run) break;
        }

    }

    /*
      get list of webpages on this site, only on per posttype, as we only need to check each template
    */

    private function get_webpage_list() {
        $scan_type = get_option("rsssl_scan_type");
        $url_list=array();

        //check if the per page plugin is used.
        if (class_exists('REALLY_SIMPLE_SSL_PP')) {

            $pages = RSSSL()->really_simple_ssl->ssl_pages;
            if (!empty($pages)) {
                foreach($pages as $page_id) {
                    $url_list[] = get_permalink($page_id);
                }
            }
        } else {
            //we're on the default ssl plugin.

            $url_list[] = home_url();
            if ($scan_type != "home") {

                $menus = get_nav_menu_locations();
                foreach ($menus as $location => $menu_id ) {
                    $menu_items = wp_get_nav_menu_items($menu_id);

                    foreach ( (array) $menu_items as $key => $menu_item ) {
                        //only insert url if on the same domain as homeurl
                        if (isset($menu_item->url) && strpos($menu_item->url, home_url())!==false) {
                            $url_list[] = $menu_item->url;
                        }
                    }
                }

                //also add an url from each post type that is used in this website.
                $args = array(
                    'public'   => true,
                );

                $post_types = get_post_types( $args);
                $post_types_query = array();
                foreach ( $post_types  as $post_type ) {
                    $post_types_query[] = " post_type = '".$post_type."'";
                }

                $sql = implode(" OR ", $post_types_query);
                global $wpdb;
                if ($scan_type == "partial") {
                    $sql = "SELECT ID FROM $wpdb->posts where post_status='publish' and (".$sql.") group by post_type";
                } else {
                    $sql = "SELECT ID FROM $wpdb->posts where post_status='publish' and (".$sql.")";
                }

                $results = $wpdb->get_results($sql);

                foreach($results as $result) {
                    if (!in_array(get_permalink($result->ID), $url_list))
                        $url_list[] = get_permalink($result->ID);
                }
            }
        }

        return $url_list;
    }


    /*
      Create an array of all files we have to check in the plugins and theme directory.
    */


    private function get_file_array(){
        $childtheme_dir = get_stylesheet_directory();
        $parenttheme_dir = get_template_directory();

        $plugin_dir = dirname(dirname( __FILE__ ));

        $file_array = $this->get_filelist_from_dir($childtheme_dir);
        //if parentthemedir and childtheme dir are different, check those as well
        if (strcasecmp($childtheme_dir, $parenttheme_dir)==0) {
            $file_array = array_merge($file_array, $this->get_filelist_from_dir($parenttheme_dir));
        }

        $file_array = array_merge($file_array, $this->get_filelist_from_dir($plugin_dir));
        $this->file_array =  array_unique($file_array);
    }

    public function uploads_dirname(){
        // defaults to uploads.
        $upload_dir_name = "uploads";
        if ( defined( 'UPLOADS' ) ) {
            $upload_dir_name = str_replace( trailingslashit( WP_CONTENT_DIR ), '', untrailingslashit( UPLOADS ) );
        }
        return $upload_dir_name;
    }

    public function get_path_to($directory, $file) {
        if ($directory!="plugins" && $directory!=$this->uploads_dirname() && $directory!="themes")
            return $file;

        //find position within wp-content
        $needle = "wp-content/".$directory."/";

        $pos = strpos($file,$needle);
        if ($pos!==false)
            $file = substr($file, $pos+strlen($needle));

        return "wp-content/".$directory."/".$file;
    }

    /**
     *  Get a list of files from a directory, with the extensions as passed.
     *   @param array() $extensions list of extensions to search for.
     *   @param string $path: path to directory to search in.
     */

    private function get_filelist_from_dir($path) {
        $filelist = array();
        $extensions = array("php");
        if ($handle = opendir($path)) {
            while (false !== ($file = readdir($handle)))
            {
                if ($file != "." && $file != "..")
                {
                    $file   = $path.'/'.$file;
                    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));

                    //we also exclude backup files generated by really simple ssl, rsssl-bkp-
                    if(is_file($file) && in_array($ext, $extensions) && (strpos($file, "rsssl-bkp-")===FALSE)){
                        $filelist[] = $file;
                    } elseif (is_dir($file)) {
                        if (strpos($file, "really-simple-ssl") ===FALSE) {
                            error_log(print_r($file, true));
                            $filelist = array_merge($filelist, $this->get_filelist_from_dir($file, $extensions));
                        }
                    }
                }
            }
            closedir($handle);
        }

        return $filelist;
    }
    /*
      These files are loaded in the website, but cannot be dynamically changed, so have to contain only https links.

      Input: array of js and css files, by url
      Output: array of js and css files, that contain http references.

    */

    private function parse_for_http($urls, $files_with_http) {
        $url_pattern = '([\w.,@?^=%&:\/~+#-]*[\w@?^=%&\/~+#-]?)';
        $patterns = array(
            '/url\([\'"]?\K(http:\/\/)'.$url_pattern.'/i',
            '/<link [^>].*?href=[\'"]\K(http:\/\/)'.$url_pattern.'/i',
            '/<meta property="og:image" .*?content=[\'"]\K(http:\/\/)'.$url_pattern.'/i',
            '/<(?:img|iframe)[^>].*?src=[\'"]\K(http:\/\/)'.$url_pattern.'/i',
            '/<script [^>]*?src=[\'"]\K(http:\/\/)'.$url_pattern.'/i',
        );

        //search for occurence of links without https
        $start = $this->queue;
        $count=0;
        for ($i = $start; $i <= count($urls)+1; ++$i) {
            $count++;
            $this->queue = $i+1;

            if (!isset($urls[$i])) continue;
            $url = $urls[$i];
            $file = $this->convert_url_to_path($url);

            if (!file_exists($file)) {error_log($file." does not exist");continue;}
            $str = file_get_contents($file);
            foreach ($patterns as $pattern){
                if (preg_match_all($pattern, $str, $matches, PREG_PATTERN_ORDER)) {
                    $this->traced_urls[] = $url;
                    foreach($matches[1] as $key=>$match) {
                        $file_with_http = $matches[1][$key].$matches[2][$key];
                        if (!$this->url_is_safe($file_with_http) && !isset($files_with_http[$url]) || (isset($files_with_http[$url]) && !in_array($file_with_http, $files_with_http[$url])))
                            $files_with_http[$url][] = $file_with_http;
                    }
                }
            }
            if ($count>$this->nr_requests_in_one_run) break;
        }

        return $files_with_http;
    }

    private function get_external_css_js_files(){
        //get not blocked urls
        $not_blocked_urls = array_diff($this->external_resources, $this->blocked_resources);
        $result_arr = array();
        foreach ($not_blocked_urls as $url) {
            if ( ((strpos($url, ".js")!==false) || (strpos($url, ".css")!==false)) && !in_array($url, $result_arr) ) {

                $result_arr[] = $url;
            }
        }
        return $result_arr;
    }


    /*

      These files are loaded in the website, but cannot be dynamically changed, so have to contain only https links.

      Input: array of js and css files, by url
      Output: array of js and css files, that contain http references.

    */

    private function parse_external_files_for_http($urls, $files_with_http) {

        $url_pattern = '([\w.,@?^=%&:\/~+#-]*[\w@?^=%&\/~+#-]?)';
        $patterns = array(
            '/url\([\'"]?\K(http:\/\/)'.$url_pattern.'/i',
            '/<link [^>].*?href=[\'"]\K(http:\/\/)'.$url_pattern.'/i',
            '/<meta property="og:image" .*?content=[\'"]\K(http:\/\/)'.$url_pattern.'/i',
            '/<(?:img|iframe)[^>].*?src=[\'"]\K(http:\/\/)'.$url_pattern.'/i',
            '/<script [^>]*?src=[\'"]\K(http:\/\/)'.$url_pattern.'/i',
        );

        $start = $this->queue;
        $count=0;
        for ($i = $start; $i < count($urls); ++$i) {
            $this->queue = $i+1;
            if (!isset($urls[$i])) continue;
            $count++;
            $url = $urls[$i];
            $str = $this->get_contents($url);
            if($this->error_number!=0) {error_log("file could not be loaded ".$url ); continue;}
            foreach ($patterns as $pattern){
                if (preg_match_all($pattern, $str, $matches, PREG_PATTERN_ORDER)) {
                    $this->traced_urls[] = $url;
                    foreach($matches[1] as $key=>$match) {
                        $file_with_http = $matches[1][$key].$matches[2][$key];
                        if (!$this->url_is_safe($file_with_http) )
                            $files_with_http[$url][] = $file_with_http;
                    }
                }
            }

            if ($count>$this->nr_requests_in_one_run) break;
        }

        return $files_with_http;

    }


    /*
      convert any url to the corresponding absolute path.
    */

    private function convert_url_to_path($url){
        //$url can start with http, https, or //
        //home_url can start with http, or https
        if (strpos($url, "//")===0) $url = "http:".$url;
        $url = str_replace("https://", "http://", $url);

        $wp_root_dir = $this->get_ABSPATH();
        $wp_root_url = str_replace("https://", "http://", home_url());
        return str_replace($wp_root_url, $wp_root_dir, $url);
    }

    private function parse_for_css_js_and_external_files($webpages){
        $css_js_files = $this->css_js_files;
        $external_resources = $this->external_resources;
        $css_js_patterns = array(
            "/(http:\/\/|https:\/\/|\/\/)([\w.,@?^=%&:\/~+#-]*[\w@?^=%&\/~+#-]?\.js)(?:\?.*[\'|\"])/", //js url pattern. after .js can be a string if it starts with ?
            "/(http:\/\/|https:\/\/|\/\/)([\w.,@?^=%&:\/~+#-]*[\w@?^=%&\/~+#-]?\.css)(?:\?.*[\'|\"])/", //css url pattern  after .css can be a string if it starts with ?
        );
        $start = $this->queue;
        $count=0;
        $nr_of_pages = count($webpages);
        for ($i = $start; $i < $nr_of_pages; ++$i) {
            $this->queue = $i+1;

            //sometimes indexes are removed as doubles, skip to next.
            if (!isset($webpages[$i])) continue;
            $count++;
            $url = $webpages[$i];

            $local_only = true;
            $html = $this->get_contents($url, $local_only);

            //first, look up css and js files.
            foreach ($css_js_patterns as $pattern){
                if (preg_match_all($pattern, $html, $matches, PREG_PATTERN_ORDER)) {
                    foreach($matches[1] as $key=>$match) {
                        $css_js_file = $matches[1][$key].$matches[2][$key];
                        if (!$this->url_is_safe($css_js_file) && !in_array($css_js_file, $css_js_files)) {
                            $css_js_files[] = $css_js_file;
                            $this->source_of_resource[$css_js_file] = $url;
                        }
                    }
                }
            }

            //now, look up external resources.
            foreach ($this->external_domain_patterns() as $pattern){
                if (preg_match_all($pattern, $html, $matches, PREG_PATTERN_ORDER)) {
                    foreach($matches[1] as $key=>$match) {
                        $external_resource = $matches[1][$key].$matches[2][$key];
                        if (!$this->url_is_safe($external_resource) && !in_array($external_resource, $external_resources) ) { //&& !$this->url_is_safe($matches[1][$key].$matches[2][$key])
                            $external_resources[] = $external_resource;
                            $this->source_of_resource[$external_resource] = $url;
                        }
                    }
                }
            }

            if ($count>$this->nr_requests_in_one_run) break;
        }

        //put all css and js files on external urls in separate array
        foreach($css_js_files as $key=>$file) {

            $home_url = str_replace(array("https://", "http://"),"", home_url());
            if (strpos($file, $home_url)===false) {
                unset($css_js_files[$key]);
                if (!in_array($file, $external_resources))
                    $external_resources[] = $file;
            }
        }

        $this->external_resources = $external_resources;
        $this->css_js_files = $css_js_files;
    }

    /**
     * Returns a succes, error or warning image for the settings page
     *
     * @since  2.0
     *
     * @access public
     *
     * @param string $type the type of image
     *
     * @return html string
     */

    public function img($type) {
        if ($type=='success') {
            return "<img class='rsssl-icons' src='".rsssl_pro_url."img/check-icon.png' alt='success'>";
        } elseif ($type=="error") {
            return "<img class='rsssl-icons' src='".rsssl_pro_url."img/cross-icon.png' alt='error'>";
        } else {
            return "<img class='rsssl-icons' src='".rsssl_pro_url."img/warning-icon.png' alt='warning'>";
        }
    }

    /**
     * Returns a succes, error or warning image for the settings page
     *
     * @since  2.0
     *
     * @access public
     *
     * @param string $type the type of image
     *
     * @return html string
     */

    public function img_path($type) {
        if ($type=='success') {
            return rsssl_pro_url."img/check-icon.png";
        } elseif ($type=="error") {
            return rsssl_pro_url."img/cross-icon.png";
        } else {
            return rsssl_pro_url."img/warning-icon.png";
        }
    }

    /*
          deprecated
    */

    public function plugin_url(){
        $plugin_url = trailingslashit(plugin_dir_url( __FILE__ ));
        if (strpos(str_replace("http://","https://",$plugin_url), str_replace("http://","https://",home_url()))===FALSE) {
            //make sure we do not have a slash at the start
            $plugin_url = ltrim($plugin_url,"/");
            $plugin_url = trailingslashit(home_url()).$plugin_url;
        }
        return $plugin_url;
    }

    /**
     * Get the absolute path the the www directory of this site, where .htaccess lives.
     *
     * @since  1.0
     *
     * @access public
     *
     */

    public function get_ABSPATH(){
        $path = ABSPATH;
        if($this->is_subdirectory_install()){
            $siteUrl = site_url();
            $homeUrl = home_url();
            $diff = str_replace($homeUrl, "", $siteUrl);
            $diff = trim($diff,"/");
            $pos = strrpos($path, $diff);
            if($pos !== false){
                $path = substr_replace($path, "", $pos, strlen($diff));
                $path = trim($path,"/");
                $path = "/".$path."/";
            }
        }
        return $path;
    }

    /**
     * Find if this wordpress installation is installed in a subdirectory
     *
     * @since  2.0
     *
     * @access protected
     *
     */

    protected function is_subdirectory_install(){
        if(strlen(site_url()) > strlen(home_url())){
            return true;
        }
        return false;
    }

    /*
        return a pattern with wich all references to external domains can be found.
    */

    private function external_domain_patterns(){
        $url_pattern = '([\w.,@?^=%&:\/~+#-]*[\w@?^=%&\/~+#-]?)(?:[\'|\"])';
        $patterns = array();

        $domain = preg_quote(str_replace(array("http://","https://"),"", home_url()), "/");

        $patterns = array_merge($patterns, array(
            '/url\([\'"]?\K(http:\/\/|https:\/\/)(?!('.$domain.'))'.$url_pattern.'/i',
            '/<link[^>].*?href=[\'"]\K(http:\/\/|https:\/\/)(?!'.$domain.')'.$url_pattern.'/i',
            '/<meta property="og:image" .*?content=[\'"]\K(http:\/\/|https:\/\/)(?!'.$domain.')'.$url_pattern.'/i',
            '/<(?:img|iframe)[^>].*?src=[\'"]\K(http:\/\/|https:\/\/)(?!'.$domain.')'.$url_pattern.'/i',
            '/<script[^>]*?src=[\'"]\K(http:\/\/|https:\/\/)(?!'.$domain.')'.$url_pattern.'/i',
            '/<form[^>]*?action=[\'"]\K(http:\/\/|https:\/\/)(?!'.$domain.')'.$url_pattern.'/i',
        ));

        return $patterns;
    }

    /**
     *
     *      Generate the result output for the scan
     *
     */

    private function generate_output($format="json") {

        $list_html = "";
        $has_result = false;
        $mixed_content_detected = FALSE;
        $container = file_get_contents(rsssl_pro_path."templates/result-container.php");
        $item = file_get_contents(rsssl_pro_path."templates/result-item.php");

        /*
        *       Blocked urls
        *       check if we have urls that can't load over https .
        */

        $body = "";

        $not_traceable_urls_found = false;
        foreach ($this->blocked_resources as $url) {
            if ($this->in_array_r($url, $this->files_with_blocked_resources) ||
                $this->in_array_r($url, $this->posts_with_blocked_resources) ||
                $this->in_array_r($url, $this->tables_with_blocked_resources) ) continue;
            $not_traceable_urls_found = true;
        }

        if (  (count($this->files_with_blocked_resources)>0) ||
            (count($this->posts_with_blocked_resources)>0) ||
            (count($this->css_js_with_mixed_content)>0) ||
            (count($this->tables_with_blocked_resources)>0) ||
            $not_traceable_urls_found ||
            (count($this->external_css_js_with_mixed_content)>0)
        ) $mixed_content_detected = TRUE;

        $title = __("Blocked url's ", "really-simple-ssl-pro");
        $has_result = false;

        foreach ($this->files_with_blocked_resources as $file => $urls) {

            $results = "";
            $file_type= "";
            $edit_link = "";

            if (strpos($file, "themes")!==false) {
                $item_title = __("In Theme file", "really-simple-ssl-pro");
                $file_type = "themes";
            } elseif (strpos($file, "plugins")!==false) {
                $item_title = __("In Plugin file", "really-simple-ssl-pro");
                $file_type = "plugins";
            } elseif (strpos($file, "uploads")!==false) {
                $item_title = __("File in uploads directory, possibly generated by plugin or theme", "really-simple-ssl-pro");
                $file_type = $this->uploads_dirname();
            } else {
                $item_title = __("File", "really-simple-ssl-pro");
                $file_type = "na";
            }

            $edit_link = $this->get_edit_link($file);
            $nice_file_path = $this->get_path_to($file_type, $file);
            $description_file = __("Found in file:","really-simple-ssl-pro");
            $description_blocked_url = __("Url cannot load over https","really-simple-ssl-pro");
            $help_file = "https://www.really-simple-ssl.com/knowledge-base/fix-blocked-resources-domains-without-ssl-certificate";

            foreach($urls as $blocked_url) {
                if (!get_option("rsssl_show_ignore_urls") && in_array($blocked_url, $this->ignored_urls)) continue;
                $has_result = true;

                $results .= str_replace(
                    array(  "[ITEM_TITLE]",
                        "[BLOCKED_URL]",
                        "[FILE]",
                        "[PATH]",
                        "[DESCRIPTION_BLOCKED_URL]",
                        "[DESCRIPTION_FILE]",
                        "[EDIT_LINK]",
                        "[HELP_FILE]" ,
                        "rsssl_deletefile",
                        "[DATA_TARGET]"
                    ),
                    array(  $item_title,
                        $blocked_url,
                        $nice_file_path,
                        $file,
                        $description_blocked_url . "<br>",
                        $description_file,
                        $edit_link,
                        $help_file ,
                        'hidden' ,
                        '#fix-file-modal'),
                    $item
                );
            }
        }

        if ($has_result) {
            $container_icon = $this->img_path("error");
        } else {
            $container_icon = $this->img_path("success");
            $results = "<b>".__('No references to domains without ssl certificate found.','really-simple-ssl-pro')."</b>";
        }
        $list_html .= str_replace(array("[RESULTS]", "[TITLE]", "[BODY]", "[ERROR_IMG]"), array($results, $title, $body, $container_icon), $container);

        /**
         *       CSS and JS with mixed content
         *       List CSS and JS files that contain http links
         *
         */

        $title = __('CSS and JS files with mixed content','really-simple-ssl-pro');
        $file_type = "";
        $file = "";
        $results = "";
        $has_result = false;

        foreach ($this->css_js_with_mixed_content as $file => $mixed_resources) {
            if (strpos($file, "themes")!==false) {
                $item_title = __("Theme file", "really-simple-ssl-pro");
                $file_type = "themes";
            } elseif (strpos($file, "plugins")!==false) {
                $item_title = __("Plugin file", "really-simple-ssl-pro");
                $file_type = "plugins";
            } elseif (strpos($file, "uploads")!==false) {
                $item_title = __("Uploads file, possibly generated by plugin or theme", "really-simple-ssl-pro");
                $file_type = $this->uploads_dirname();
            } elseif (strpos($file, "cache")!==false) {
                $item_title = __("Cached file, deactivate cache to see the actual source", "really-simple-ssl-pro");
                $file_type = $this->uploads_dirname();
            } else {
                $item_title = __("File", "really-simple-ssl-pro");
                $file_type = "na";
            }

            $nice_file_path = $this->get_path_to($file_type, $file);
            $edit_link = $this->get_edit_link($file);
            $description_file = __("Found in file:", "really-simple-ssl-pro");
            $description_blocked_url = __("Reference to file with http://","really-simple-ssl-pro")."<br>";
            $help_link = "https://really-simple-ssl.com/knowledge-base/fix-css-and-js-files-with-mixed-content/";


            foreach($mixed_resources as $src) {
                if (!get_option("rsssl_show_ignore_urls") && in_array($src, $this->ignored_urls)) continue;

                $has_result = true;
                //make distinction between $src on own domain and $src on remote domain
                //remote domain resources need to be downloaded.
                //compare non www url.
                $home_url_no_www = str_replace("https://", "http://", str_replace("://www.", "://", home_url()) );
                $src_no_www = str_replace("://www.", "://", $src);
                if (strpos($src_no_www,  $home_url_no_www)===FALSE) {
                    $modal = "#fix-file-modal";
                } else  {
                    $modal = "#fix-cssjs-modal";
                }

                $results .= str_replace(
                    array("[ITEM_TITLE]",
                        "[BLOCKED_URL]",
                        "[FILE]",
                        "[PATH]",
                        "[DESCRIPTION_BLOCKED_URL]",
                        "[DESCRIPTION_FILE]",
                        "[EDIT_LINK]",
                        "[HELP_LINK]",
                        "rsssl_deletefile",
                        "[DATA_TARGET]"
                    ),
                    array($item_title,
                        $src,
                        $nice_file_path,
                        $file,
                        $description_blocked_url,
                        $description_file,
                        $edit_link,
                        $help_link,
                        'hidden',
                        $modal),
                    $item
                );
            }
        }

        if ($has_result) {
            $container_icon = $this->img_path("error");
        } else {
            $container_icon = $this->img_path("success");
            $results = "<b>".__('No references to domains without ssl certificate found.','really-simple-ssl-pro')."</b>";
        }

        $list_html .= str_replace(array("[RESULTS]", "[TITLE]", "[BODY]", "[ERROR_IMG]"), array($results, $title, $body, $container_icon), $container);

        /**
         *       CSS and JS from other domains with mixed content
         *       List CSS and JS files on other domains that contain http links
         *
         */

        $src="";
        $file="";
        $title = __('CSS and JS files from other domains with mixed content','really-simple-ssl-pro');
        $description_file = __("Found in file:", "really-simple-ssl-pro");
        $help_link = "https://really-simple-ssl.com/knowledge-base/fix-css-js-files-mixed-content-domains/";
        $item_title ="";
        $results = "";
        $has_result = false;

        foreach ($this->external_css_js_with_mixed_content as $url => $mixed_resources) {
            if (!get_option("rsssl_show_ignore_urls") && in_array($url, $this->ignored_urls)) continue;

            $description_blocked_url = __("http link","really-simple-ssl-pro");
            foreach($this->files_with_external_css_js as $file=>$url_array) {
                foreach($url_array as $lookup_url) {
                    $has_result = true;
                    $str = __("File containing this url: ","really-simple-ssl-pro").$url;
                    $src="";
                    foreach($mixed_resources as $http_src){
                        $src = $src.$http_src."<br>";
                    }
                    if ($lookup_url==$url) $results .= str_replace(
                        array("[ITEM_TITLE]",
                            "[BLOCKED_URL]",
                            "[FILE]",
                            "[DESCRIPTION_BLOCKED_URL]",
                            "[DESCRIPTION_FILE]",
                            "rsssl_edit",
                            "rsssl_fix",
                            "rsssl_deletefile",
                            "[HELP_LINK]"
                        ),
                        array($item_title,
                            $src,
                            $url."<br><b>" . $description_file . "</b>".$file,
                            $description_blocked_url . "<br>", __("Remote file: " , "really-simple-ssl-pro"),
                            'hidden',
                            'hidden',
                            'hidden',
                            $help_link),
                        $item
                    );
                }
            }
        }
        if ($has_result) {
            $container_icon = $this->img_path("error");
        } else {
            //nothing found
            $container_icon = $this->img_path("success");
            $results = "<b>" . __('No CSS and JS files on other domains with mixed content.','really-simple-ssl-pro') . "</b>";
        }

        $list_html .= str_replace(array( "[RESULTS]", "[TITLE]", "[BODY]", "[ERROR_IMG]"), array($results, $title, $body, $container_icon), $container);

        /**
         *       Posts with blocked resources
         *       List posts with images or resources that could not load over https://
         *
         */

        $description_blocked_url = __("Url cannot load over https","really-simple-ssl-pro");
        $description_file= __("","really-simple-ssl-pro");
        $title = __('Posts with blocked resources','really-simple-ssl-pro');

        $path ="";
        $help_link = "https://www.really-simple-ssl.com/fix-posts-with-blocked-resources-domains-without-ssl-certificate";
        $results = "";
        $has_result = false;

        foreach ($this->posts_with_blocked_resources as $post_id) {

            $blocked_urls = $this->posts_with_external_resources[$post_id];

            foreach($blocked_urls as $url){
                //check if it's ignored
                if (!get_option("rsssl_show_ignore_urls") && in_array($url, $this->ignored_urls)) continue;
                if (!in_array( $url, $this->blocked_resources)) continue;

                $has_result = true;
                $edit_link = get_admin_url(null, 'post.php?post='.$post_id.'&action=edit');
                $post_title = get_the_title($post_id);
                $results .= str_replace(
                    array("[ITEM_TITLE]",
                        "[BLOCKED_URL]",
                        "[FILE]",
                        "[PATH]",
                        "[POST_ID]",
                        "[DESCRIPTION_BLOCKED_URL]",
                        "[DESCRIPTION_FILE]",
                        'data-url="[EDIT_LINK]" href="#" data-toggle="modal" data-target="#editor-modal"',
                        "[DATA_TARGET]"
                    ),
                    array("",
                        $url,
                        "",
                        "",
                        $post_id,
                        $description_blocked_url . "<br>",
                        "In post: " . $post_title,
                        ' href="'.$edit_link.'" ',
                        '#fix-post-modal'),
                    $item
                );

            }
        }

        if ($has_result) {
            $container_icon = $this->img_path("error");
        } else {
            $container_icon = $this->img_path("success");
            $results = "<b>".__('No posts found that contain references to domains without an SSL certificate.','really-simple-ssl-pro')."</b>";
        }

        $list_html .= str_replace(array("[RESULTS]", "[TITLE]", "[BODY]", "[ERROR_IMG]", "[HELP_LINK]"), array( $results, $title, $body, $container_icon, $help_link), $container);


        /**
         *       Widgets with blocked resources
         *       List widgets with images or resources that could not load over https://
         *
         */

        $description_blocked_url = __("File cannot load over https","really-simple-ssl-pro");
        $description_file= __("","really-simple-ssl-pro");
        $title = __('Widgets with blocked resources','really-simple-ssl-pro');

        $path = "";
        $help_link = "https://really-simple-ssl.com/knowledge-base/locating-mixed-content-in-widgets/";
        $edit_link = get_admin_url(null, '/widgets.php');
        $results = "";
        $has_result = false;

        foreach ($this->widgets_with_blocked_resources as $widget_name) {

            $blocked_urls = $this->widgets_with_external_resources[$widget_name];

            foreach($blocked_urls as $url){
                //check if it's ignored
                if (!get_option("rsssl_show_ignore_urls") && in_array($url, $this->ignored_urls)) continue;
                if (!in_array( $url, $this->blocked_resources)) continue;

                $has_result = true;
                $widget_data = $this->get_widget_data($widget_name);
                $widget_area = $this->get_widget_area($widget_name);
                $widget_title = $this->get_widget_title($widget_area);
                $results .= str_replace(
                    array("[ITEM_TITLE]",
                        "[BLOCKED_URL]",
                        "[FILE]",
                        "[PATH]",
                        "[POST_ID]",
                        'data-url="[EDIT_LINK]" href="#" data-toggle="modal" data-target="#editor-modal"',
                        "[DESCRIPTION_BLOCKED_URL]",
                        "[DESCRIPTION_FILE]",
                        "rsssl_edit",
                        "rsssl_fix",
                        "#editor-modal"
                    ),
                    array("",
                        $url,
                        "",
                        "",
                        'hidden',
                        'href="'.$edit_link.'"',
                        $description_blocked_url . "<br>",
                        "In widget area:</b> " . $widget_title." <b> in widget:</b> ".$widget_data["title"],
                        'href="'.$edit_link.'"',
                        'hidden',
                        "#widget-modal"),
                    $item
                );
            }
        }


        if ($has_result) {
            $container_icon = $this->img_path("error");
        } else {
            $container_icon = $this->img_path("success");
            $results = "<b>".__('No posts found that contain references to domains without an SSL certificate.','really-simple-ssl-pro')."</b>";
        }

        $list_html .= str_replace(array("[RESULTS]", "[TITLE]", "[BODY]", "[ERROR_IMG]", "[HELP_LINK]"), array( $results, $title, $body, $container_icon, $help_link), $container);


        /**
         *       Tables with blocked resources
         *       List tables with images or resources that could not load over https://
         *
         */

        $description_file = __("Found in:", "really-simple-ssl-pro");
        $description_blocked_url = __("File:","really-simple-ssl-pro");
        $help_link = "https://really-simple-ssl.com/knowledge-base/fix-blocked-resources-not-found-file-post/";
        $title = __("Database tables with blocked resources", "really-simple-ssl-pro");

        $item_title ="";
        $results = "";
        $edit_link = "";
        $nice_file_path = $this->get_path_to($file_type, $file);
        $has_result = false;

        foreach ($this->tables_with_blocked_resources as $url => $table) {

            if (!get_option("rsssl_show_ignore_urls") && in_array($url, $this->ignored_urls)) continue;
            if (!isset($this->source_of_resource[$url])) continue;

            $has_result = true;

            $results .= str_replace(
                array("[ITEM_TITLE]",
                    "[BLOCKED_URL]",
                    "[FILE]",
                    "[PATH]",
                    "[DESCRIPTION_BLOCKED_URL]",
                    "[DESCRIPTION_FILE]",
                    "rsssl_fix",
                    "rsssl_deletefile",
                    "rsssl_edit"
                ),
                array("",
                    $url,
                    $table, //tables, so not file here.
                    $nice_file_path,
                    $description_blocked_url. "<br>",
                    "Location:",
                    'hidden',
                    'hidden',
                    'hidden'),
                $item
            );

        }
        if ($has_result) {
            $container_icon = $this->img_path("error");
        } else {
            $container_icon = $this->img_path("success");
            $results = "<b>".__('No references to blocked resources found within the database.','really-simple-ssl-pro')."</b>";
        }

        $list_html .= str_replace(array("[RESULTS]", "[TITLE]", "[BODY]", "[ERROR_IMG]", "[HELP_LINK]"), array( $results, $title, $body, $container_icon, $help_link), $container);

        /**
         *       Not traceable urls
         *       List resources that could not be traced
         *
         */

        $results = "";


        if ($not_traceable_urls_found) {

            foreach ($this->blocked_resources as $url) {
                if (!get_option("rsssl_show_ignore_urls") && in_array($url, $this->ignored_urls)) continue;
                //check if it was found in posts, files or DB
                if ($this->in_array_r($url, $this->files_with_blocked_resources) ||
                    $this->in_array_r($url, $this->posts_with_blocked_resources) ||
                    $this->in_array_r($url, $this->tables_with_blocked_resources) ) continue;
                $description_file = __('Used on page:', 'really-simple-ssl-pro');
                if (isset($this->source_of_resource[$url])) {
                    if (is_numeric($this->source_of_resource[$url])) {
                        $results .= str_replace(
                            array("[ITEM_TITLE]",
                                "[BLOCKED_URL]",
                                "[FILE]",
                                "[PATH]",
                                "[DESCRIPTION_BLOCKED_URL]",
                                "[DESCRIPTION_FILE]",
                                "rsssl_fix",
                                "rsssl_deletefile",
                                "rsssl_edit"
                            ),
                            array("",
                                $url,
                                __("Unknown", "really-simple-ssl-pro"), //not found, so no source
                                __("Unknown", "really-simple-ssl-pro"),
                                $description_blocked_url,
                                "Location:",
                                'hidden',
                                'hidden',
                                'hidden'),
                            $item
                        );

                        //Only show this container when there are results. If there are no results the container stays hidden.

                        $help_link = "https://really-simple-ssl.com/knowledge-base/fix-blocked-resources-not-traceable/";
                        $container_icon = $this->img_path("error");
                        $title = __("The scan was not able to determine where this url is located. Please contact support.", "really-simple-ssl-pro");
                        $list_html .= str_replace(array("[RESULTS]", "[TITLE]", "[BODY]", "[ERROR_IMG]", "[HELP_LINK]"), array( $results, $title, $body, $container_icon, $help_link), $container);
                    }
                }
            }
        }


        //$html = '<div id="rsssl">' . $html . '</div>';

        if ($format=="json") {
            $this->last_scan_time = time();
            if (!$mixed_content_detected) {
                $this->scan_completed_no_errors = "COMPLETED";
            } else {
                $this->scan_completed_no_errors = "ERRORS";
            }
        }

        $this->save_results();

        if ($format=="html") {
            return $list_html;
        } else {
            return array(
                'progress' => "finished",
                'output'=> $list_html
            );
        }
    }

    /**
     * @param bool $reset
     */
    public function load_results($reset = false){

        // $arr = unserialize($widgets);
        // error_log(print_r($arr, true));

        $this->scan_completed_no_errors       = get_option('rsssl_scan_completed_no_errors', 'NEVER');
        $this->last_scan_time                 = get_option('rsssl_last_scan_time', __("Never", "really-simple-ssl-pro"));
        $options = get_transient('rlrsssl_scan');
        if (isset($options)) {
            //$this->scan_completed_no_errors       = isset($options['scan_completed_no_errors']) ? $options['scan_completed_no_errors'] : "NEVER";
            //$this->last_scan_time                 = isset($options['last_scan_time']) ? $options['last_scan_time'] : __("Never", "really-simple-ssl-pro");

            if (!$reset) {
                $this->css_js_files                 = isset($options['css_js_files']) ? $options['css_js_files'] : array();
                $this->queue                        = isset($options['queue']) ? $options['queue'] : array();
                $this->css_js_with_mixed_content    = isset($options['css_js_with_mixed_content']) ? $options['css_js_with_mixed_content'] : array();
                $this->webpages                     = isset($options['webpages']) ? $options['webpages'] : array();
                $this->external_resources           = isset($options['external_resources']) ? $options['external_resources'] : array();
                $this->file_array                   = isset($options['file_array']) ? $options['file_array'] : array();
                $this->files_with_blocked_resources = isset($options['files_with_blocked_resources']) ? $options['files_with_blocked_resources'] : array();
                $this->posts_with_blocked_resources = isset($options['posts_with_blocked_resources']) ? $options['posts_with_blocked_resources'] : array();
                $this->blocked_resources            = isset($options['blocked_resources']) ? $options['blocked_resources'] : array();
                $this->traced_urls                  = isset($options['traced_urls']) ? $options['traced_urls'] : array();
                $this->source_of_resource           = isset($options['source_of_resource']) ? $options['source_of_resource'] : array();
                $this->tables_with_blocked_resources= isset($options['tables_with_blocked_resources']) ? $options['tables_with_blocked_resources'] : array();
                $this->external_css_js_with_mixed_content= isset($options['external_css_js_with_mixed_content']) ? $options['external_css_js_with_mixed_content'] : array();
                $this->files_with_css_js            = isset($options['files_with_css_js']) ? $options['files_with_css_js'] : array();
                $this->files_with_external_css_js   = isset($options['files_with_external_css_js']) ? $options['files_with_external_css_js'] : array();
                $this->posts_with_external_resources= isset($options['posts_with_external_resources']) ? $options['posts_with_external_resources'] : array();
                $this->widgets_with_external_resources = isset($options['widgets_with_external_resources']) ? $options['widgets_with_external_resources'] :array();
                $this->widgets_with_blocked_resources = isset($options ['widgets_with_blocked_resources']) ? $options['widgets_with_blocked_resources'] : array();

            }
            $this->ignored_urls                 = isset($options['ignored_urls']) ? $options['ignored_urls'] : array();
            $this->ignored_urls = array_merge($this->safe_domains, $this->ignored_urls);

        }


    }

    public function save_results(){

        //do not save when we're not scanning
        if (isset($_POST['rsssl_no_scan']) ) return;
        $this->ignored_urls = array_diff($this->ignored_urls, $this->safe_domains);
        $options = array(
            'css_js_files'                => $this->css_js_files,
            'queue'                       => $this->queue,
            'css_js_with_mixed_content'   => $this->css_js_with_mixed_content,
            'webpages'                    => $this->webpages,
            'external_resources'          => $this->external_resources,
            'blocked_resources'           => $this->blocked_resources,
            'file_array'                  => $this->file_array,
            'files_with_blocked_resources'=> $this->files_with_blocked_resources,
            'posts_with_blocked_resources'=> $this->posts_with_blocked_resources,
            'traced_urls'                 => $this->traced_urls,
            'source_of_resource'          => $this->source_of_resource,
            'scan_completed_no_errors'    => $this->scan_completed_no_errors,
            'tables_with_blocked_resources'=> $this->tables_with_blocked_resources,
            //'last_scan_time'              => $this->last_scan_time,
            'external_css_js_with_mixed_content'=> $this->external_css_js_with_mixed_content,
            'files_with_css_js'           => $this->files_with_css_js,
            'files_with_external_css_js'   => $this->files_with_external_css_js,
            'posts_with_external_resources'=> $this->posts_with_external_resources,
            'ignored_urls'                 =>  $this->ignored_urls,
            'widgets_with_external_resources' => $this->widgets_with_external_resources,
            'widgets_with_blocked_resources' => $this->widgets_with_blocked_resources,
        );


        update_option('rsssl_scan_completed_no_errors', $this->scan_completed_no_errors);
        update_option('rsssl_last_scan_time', $this->last_scan_time);
        set_transient('rlrsssl_scan', $options, WEEK_IN_SECONDS);
    }






    /**
     * Add some css for the settings page
     *
     * @since  1.0
     *
     * @access public
     *
     */

    public function enqueue_assets($hook){
        $options = get_option('rlrsssl_options');
        if (isset($options)) $plugin_version = isset($options['plugin_db_version']) ? $options['plugin_db_version'] : "1.0";
        if ($plugin_version>"2.3.3") {
            global $rsssl_admin_page;
            if( $hook != $rsssl_admin_page )
                return;
        }


        wp_register_style( 'rsssl-bootstrap', rsssl_pro_url . 'bootstrap/css/bootstrap.min.css',"", rsssl_pro_version);
        wp_enqueue_style( 'rsssl-bootstrap');
        wp_enqueue_script('rsssl-bootstrap', rsssl_pro_url . 'bootstrap/js/bootstrap.min.js', array('jquery'), rsssl_pro_version, false);
        wp_enqueue_script('rsssl-main', rsssl_pro_url . 'js/rsssl.js', array('jquery'), rsssl_pro_version, false);
        wp_localize_script('rsssl-main','rsssl_ajax', array(
            'ajaxurl'=> admin_url( 'admin-ajax.php' ),
        ));
        wp_register_style( 'rsssl-main', rsssl_pro_url . 'css/main.css',"", rsssl_pro_version);
        wp_enqueue_style( 'rsssl-main');
    }

    private function calculate_queue_progress($array, $queue, $total, $iteration ) {
        $nr_of_pages_left = count($array) - $queue;
        $array_count = (count($array)==0) ? 1 : count($array);
        $part_left = $nr_of_pages_left/$array_count;
        return  $total * $iteration - $part_left * $total;
    }

    private function searchAllDB($url){
        global $wpdb;

        $output = array();
        $sql = "show tables";
        $tables = $wpdb->get_results($sql);
        $count=0;
        foreach($tables as $table){
            $fields = array();
            $table = current((array)$table);
            //if ($table=="$wpdb->options") continue;
            $count++;

            $query = "show columns from ".$table;
            $cols = $wpdb->get_results($query);
            foreach($cols as $col) {
                if (!is_array($col) && !empty($col) && substr($col->Field,0,2)!="t_") $fields[]= $col->Field." LIKE ('%".$url."%') OR ".$col->Field." LIKE ('%".str_replace("/", "\/", $url)."%')";
            }
            $search_sql = implode(" OR ", $fields);
            $results = $wpdb->get_results("select * from ".$table." where ".$search_sql);
            if (!empty($results)) $output[$url] = $table;
        }
        return $output;
    }

    private function still_in_queue($array){
        $in_queue = true;
        //if array is empty, or the queue is same as array minus one, we are not in queue anymore
        if ($this->queue>=count($array) || count($array)==0) {
            $in_queue = false;
            $this->queue=0;
        }
        return $in_queue;
    }

    private function get_pluginname_by_table($table){
        global $wpdb;

        $table = str_replace($wpdb->prefix, "", $table);
        $plugin_names = array(
            'layerslider' => 'LayerSlider WP',
            'revslider' => 'Slider Revolution',
            'posts' => 'Default wp posts table',
            'postmeta' => 'wp postmeta table',
            'wf'  => 'Wordfence',
            'woocommerce' => 'Woocommerce',
            'itsec' => 'iThemes Security',
            'duplicator' => 'Duplicator',
            'wpgmza' => 'WP Google Maps',
            'iwp'=>'Infinite WP',
            'ngg' => 'Next Generation',
            'gallery' => 'Photo Gallery',
            'redirection' => 'Redirection',
        );

        for($i=0;$i<strlen($table); $i++){
            $tablename = ($i==0) ? $table : substr($table, 0,-$i);
            if (isset($plugin_names[$tablename])) return $plugin_names[$tablename];
        }
        return __("Plugin not found","really-simple-ssl-pro");
    }

    public function scan_completed_no_errors(){

        $this->scan_completed_no_errors = get_option('rsssl_scan_completed_no_errors', 'NEVER');

        return $this->scan_completed_no_errors;
    }



    public function get_edit_link($file){
        if (defined('DISALLOW_FILE_EDIT') && DISALLOW_FILE_EDIT) {
            return 'FILE_EDIT_BLOCKED';
        }
        $edit_link = false;
        if (stristr($file, "themes")) {
            $themes = wp_get_themes();
            foreach($themes as $theme){
                $template = "/".$theme->template."/";
                if (stristr($file, $template)) {
                    $filename = substr($file, strrpos($file, $template)+strlen($template));
                    $filename = trim($filename, "/");
                    $edit_link = "theme-editor.php?file=".$filename."&theme=".$theme->template;
                    break;
                }
            }
        }

        if (stristr($file, "plugins")) {
            $plugins = get_plugins();
            foreach($plugins as $plugin_dir=>$plugin){
                $plugin_folder = "/".dirname($plugin_dir)."/";
                if (stristr($file, $plugin_folder)) {
                    $filename = substr($file, strrpos($file, $plugin_folder));
                    $filename = trim($filename, "/");
                    $edit_link = "plugin-editor.php?file=".$filename."&plugin=".$plugin_dir;
                    break;
                }
            }
        }

        return  $edit_link;

    }


    /**
     *
     *     Add pro information to the configuration tab
     *
     */


    public function configuration_page_scan(){
        ?>
        <table>
            <tr>
                <td><?php echo is_ssl() ? $this->img("success") : $this->img("error");?> </td>
                <td>
                    <?php
                    if (is_ssl()) {
                        _e("The native Wordpress function is_ssl() returned true","really-simple-ssl-pro");
                    } else {
                        _e("The native Wordpress function is_ssl() returned false","really-simple-ssl-pro");
                    }
                    ?>
                </td></tr>
            <?php
            if (!RSSSL()->really_simple_ssl->site_has_ssl) {
                if ($this->scan_completed_no_errors()=="COMPLETED") {
                    echo "<tr><td>".$this->img("success")."</td><td>".__("Great! Your scan last completed without errors.","really-simple-ssl-pro")."</td></tr>";
                } elseif ($this->scan_completed_no_errors=="ERRORS") {
                    echo "<tr><td>".$this->img("warning")."</td><td>".__("The last scan was completed with errors. Only migrate if you are sure the found errors are not a problem for your site.","really-simple-ssl-pro")."</td></tr>";
                } else {
                    echo "<tr><td>".$this->img("warning")."</td><td>".__("You haven't scanned the site yet, you should scan your site to check for possible issues before migrating to ssl.","really-simple-ssl-pro")."</td></tr>";
                }
            } else {
                if ($this->scan_completed_no_errors()=="COMPLETED") {
                    echo "<tr><td>".$this->img("success")."</td><td>".__("Great! Your scan last completed without errors.","really-simple-ssl-pro")."</td></tr>";
                } elseif ($this->scan_completed_no_errors=="ERRORS") {
                    echo "<tr><td>".$this->img("warning")."</td><td>".__("The last scan was completed with errors. Are you sure these issues don't impact your site?.","really-simple-ssl-pro")."</td></tr>";
                } else {
                    echo "<tr><td>".$this->img("warning")."</td><td>".__("You haven't scanned the site yet, you should scan your site to check for possible issues.","really-simple-ssl-pro")."</td></tr>";
                }
            } ?>
        </table>
        <?php
    }

    public function add_scan_tab($tabs){
        $tabs['scan'] = __("Scan for issues","really-simple-ssl-pro");
        return $tabs;
    }


    /*
        recursive arraysearch function, that searches for both key and value.
    */


    private function in_array_r($needle, $haystack) {
        foreach ($haystack as $key=>$value) {
            if (($key == $needle) || ($value == $needle)  || (is_array($value) && $this->in_array_r($needle, $value))) {
                return true;
            }
        }
        return false;
    }

    /**
     * Handles any errors as the result of trying to open a https page when there may be no ssl.
     *
     * @since  2.0
     *
     * @access public
     *
     */

    private function custom_error_handling($errno, $errstr, $errfile, $errline, array $errcontext) {
        $this->error_number = $errno;
    }

    /*
        retrieves the content of an url
        If a redirection is in place, the new url serves as input for this function
        max 5 iterations

        set local only to true, if no external urls should be followed.

    */


    public function get_contents($url, $local_only = false) {
        //if url is protocol independent, (//) get contents might not work.
        if (strpos($url, "//")===0) $url = "https:".$url;
        $response = wp_remote_get( $url );
        $filecontents = "";

        if( is_array($response) ) {
            $status = wp_remote_retrieve_response_code( $response );
            $filecontents = wp_remote_retrieve_body($response);
        }

        if(is_wp_error( $response )) {
            $this->site_has_ssl = FALSE;
            $this->error_number = "404";
            error_log($response->get_error_message());

        } else {
            $this->error_number =0;
        }

        return $filecontents;
    }

    public function fix_post_modal(){
        ?>
        <div class="modal fade" id="fix-post-modal" tabindex="-1" role="dialog" aria-labelledby="fix-post-modal">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel"><?php _e("Import and insert file","really-simple-ssl-pro");?></h4>
                    </div>
                    <div class="modal-body">
                        <b><?php _e("Copyright warning!","really-simple-ssl-pro");?></b><br>

                        <?php _e("Downloading files from other websites can cause serious copyright issues! It is always illegal to use images, files, or any copyright protected material on your own site without the consent of the copyrightholder. Please ask the copyrightholder for permission. Use this function at your own risk.","really-simple-ssl-pro");?>
                        <br><br>
                        <?php _e("This downloads the file from the domain without SSL, inserts it into WP media, and changes the URL to the new URL.","really-simple-ssl-pro");?> </div>

                    <div class="modal-footer">
                        <button type="button" class="button button-default" data-dismiss="modal">Close</button>
                        <button type="button" data-id=0 data-path=0 data-url=0 data-token="<?php echo wp_create_nonce('rsssl_fix_post');?>" class="button button-primary" id="start-fix-post"><?php _e("I have read the warning, continue", "really-simple-ssl-pro")?></button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function ignore_url_modal(){
        ?>
        <div class="modal fade" id="ignore-url-modal" tabindex="-1" role="dialog" aria-labelledby="ignore-url-modal">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel"><?php _e("Add to ignore list","really-simple-ssl-pro");?></h4>
                    </div>
                    <div class="modal-body">
                        <?php _e("By adding this file to the ignore list it will not show up in future scan results.","really-simple-ssl-pro");?>
                        <?php _e("If you want to view ignored urls, you can do so in the advanced settings of the scan. ","really-simple-ssl-pro");?>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="button button-default" data-dismiss="modal">Close</button>
                        <button type="button" data-id=0 data-path=0 data-url=0 data-token="<?php echo wp_create_nonce('rsssl_ignore_url');?>" class="button button-primary" id="start-ignore-url"><?php _e("Ignore", "really-simple-ssl-pro")?></button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }


    public function fix_file_modal(){
        ?>
        <div class="modal fade" id="fix-file-modal" tabindex="-1" role="dialog" aria-labelledby="fix-file-modal">
            <div class="rsssl modal-dialog" role="document">
                <div class="rsssl modal-content">
                    <div class="rsssl modal-header">
                        <button type="button" class="rsssl close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="rsssl modal-title" id="myModalLabel"><?php _e("Import and insert file","really-simple-ssl-pro");?></h4>
                    </div>
                    <div class="rsssl modal-body">
                        <b><?php _e("Copyright warning!","really-simple-ssl-pro");?></b><br>
                        <?php _e("Downloading files from other websites can cause serious copyright issues! It is always illegal to use images, files, or any copyright protected material on your own site without the consent of the copyrightholder. Please ask the copyrightholder for permission. Use this function at your own risk.","really-simple-ssl-pro");?>
                        <br><br>
                        <?php _e("This function downloads the file from the domain without SSL, inserts it into WP media, and changes the URL to the new URL.","really-simple-ssl-pro");?>
                        <br><br><b><?php _e("Always backup first!","really-simple-ssl-pro");?></b><br>
                        <?php _e("Be very carefull with this function! Please backup your site before proceeding. This function will also create a backup of each changed file, name rsssl-bkp-filename. You can use the 'roll back files' function to restore the original files.","really-simple-ssl-pro");?>
                    </div>

                    <div class="rsssl modal-footer">
                        <button type="button" class="rsssl button button-default" data-dismiss="modal">Close</button>
                        <button type="button" data-id=0 data-path=0 data-url=0 data-token="<?php echo wp_create_nonce('rsssl_fix_post');?>" class="rsssl button button-primary" id="start-fix-file"><?php _e("I have read the warnings, continue", "really-simple-ssl-pro")?></button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function fix_cssjs_modal(){
        ?>
        <div class="rsssl modal fade" id="fix-cssjs-modal" tabindex="-1" role="dialog" aria-labelledby="fix-cssjs-modal">
            <div class="rsssl modal-dialog" role="document">
                <div class="rsssl modal-content">
                    <div class="rsssl modal-header">
                        <button type="button" class="rsssl close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="rsssl modal-title" id="myModalLabel"><?php _e("Fix http in CSS and JS files","really-simple-ssl-pro");?></h4>
                    </div>
                    <div class="rsssl modal-body">
                        <b><?php _e("Always backup first!", "really-simple-ssl-pro")?></b><br><br>
                        <?php _e("This function will change the urls to the protocol independent // instead of http://","really-simple-ssl-pro");?>
                        <br><bR>
                        <?php _e("If these files are generated by a theme or plugin, it is best to change the settings in that plugin instead. Otherwise your changes maybe overwritten by the plugin.","really-simple-ssl-pro");?>
                    </div>

                    <div class="rsssl modal-footer">
                        <button type="button" class="rsssl button button-default" data-dismiss="modal">Close</button>
                        <button type="button" data-id=0 data-path=0 data-url=0 data-token="<?php echo wp_create_nonce('rsssl_fix_post');?>" class="rsssl button button-primary" id="start-fix-cssjs"><?php _e("Fix urls", "really-simple-ssl-pro")?></button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function roll_back_modal(){
        ?>
        <div class="rsssl modal fade" id="roll-back-modal" tabindex="-1" role="dialog" aria-labelledby="roll-back-modal">
            <div class="rsssl modal-dialog" role="document">
                <div class="rsssl modal-content">
                    <div class="rsssl modal-header">
                        <button type="button" class="rsssl close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="rsssl modal-title" id="myModalLabel"><?php _e("Roll back changes made to your files","really-simple-ssl-pro");?></h4>
                    </div>
                    <div class="rsssl modal-body">
                        <?php _e("This will put the files back that were changed by the fix option in Really Simple SSL pro.","really-simple-ssl-pro");?>
                        <br><br>
                        <?php _e("Please note that any changes you have made since to your current files, will be lost. ","really-simple-ssl-pro");?>
                    </div>

                    <div class="rsssl modal-footer">
                        <button type="button" class="rsssl button button-default" data-dismiss="modal">Close</button>
                        <button type="button" data-id=0 data-path=0 data-url=0 data-token="<?php echo wp_create_nonce('rsssl_fix_post');?>" class="rsssl button button-primary" id="start-roll-back"><?php _e("Restore files","really-simple-ssl-pro")?></button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    public function editor_modal(){
        ?>
        <div class="rsssl modal fade" id="editor-modal" tabindex="-1" role="dialog" aria-labelledby="editor-modal">
            <div class="rsssl modal-dialog" role="document">
                <div class="rsssl modal-content">
                    <div class="rsssl modal-header">
                        <button type="button" class="rsssl close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="rsssl modal-title" id="myModalLabel"><?php _e("Edit","really-simple-ssl-pro");?></h4>
                    </div>
                    <div class="rsssl modal-body">
                        <div id="edit-files">
                            <b><?php _e("Always backup first!", "really-simple-ssl-pro")?></b><br><br>
                            <?php _e("Editing files can break your site if you do not do it right!","really-simple-ssl-pro");?>
                        </div>
                        <div id="edit-files-blocked">
                            <b><?php _e("File editing blocked in WordPress!", "really-simple-ssl-pro")?></b><br><br>
                            <?php _e("File editing is blocked in WordPress. To edit these files, please use your FTP client.","really-simple-ssl-pro");?>
                        </div>
                    </div>

                    <div class="rsssl modal-footer">
                        <button type="button" class="rsssl button button-default" data-dismiss="modal">Close</button>
                        <button type="button" class="rsssl button button-primary" id="open-editor"><?php _e("Go to editor","really-simple-ssl-pro")?></button>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }






}//class closure
