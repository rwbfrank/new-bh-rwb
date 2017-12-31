<?php
/*
Plugin Name: JSON Content Importer
Plugin URI: https://json-content-importer.com/
Description: Plugin to import, cache and display a JSON-Feed. Display is done with wordpress-shortcode.
Version: 1.2.18
Author: Bernhard Kux
Author URI: https://json-content-importer.com/
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html
*/

/* block direct requests */
if ( !function_exists( 'add_action' ) ) {
	echo 'Hello, this is a plugin: You must not call me directly.';
	exit;
}
defined('ABSPATH') OR exit;

if(!class_exists('JsonContentImporter')){
 require_once plugin_dir_path( __FILE__ ) . '/class-json-content-importer.php';
}

require_once plugin_dir_path( __FILE__ ) . '/options.php';
$JsonContentImporter = new JsonContentImporter();

/* extension hook BEGIN */
do_action('json_content_importer_extension');
/* extension hook END */

?>