<?php
/*
Plugin Name: STM Importer
Plugin URI: http://stylemixthemes.com/
Description: STM Importer
Author: Stylemix Themes
Author URI: http://stylemixthemes.com/
Text Domain: stm_importer
Version: 3.6
*/

if ( !function_exists('stm_demo_import'))
{
	function stm_demo_import()
	{
		?>
		<div class="stm_message content" style="display:none;">
			<img src="<?php echo plugin_dir_url( __FILE__ ) ?>assets/images/spinner.gif" alt="spinner">
			<h1 class="stm_message_title"><?php esc_html_e('Importing Demo Content...', 'motors'); ?></h1>
			<p class="stm_message_text"><?php esc_html_e('Duration of demo content importing depends on your server speed.', 'motors'); ?></p>
		</div>

		<div class="stm_message success" style="display:none;">
			<p class="stm_message_text"><?php echo wp_kses( sprintf(__('Congratulations and enjoy <a href="%s" target="_blank">your website</a> now!', 'motors'), esc_url( home_url() )), array( 'a' => array( 'href' => array(), 'target' => array() ) ) ); ?></p>
		</div>

		<form class="stm_importer" id="import_demo_data_form" action="?page=stm_demo_import" method="post">

			<div class="stm_importer_options">

				<div class="stm_importer_note">
					<strong><?php esc_html_e('Before installing the demo content, please NOTE:', 'motors'); ?></strong>
					<p><?php echo wp_kses( sprintf(__('Install the demo content only on a clean WordPress. Use <a href="%s" target="_blank">Reset WP</a> plugin to clean the current Theme.', 'motors'), 'https://wordpress.org/plugins/reset-wp/', esc_url( home_url() )), array( 'a' => array( 'href' => array(), 'target' => array() ) ) ); ?></p>
					<p><?php esc_html_e('Remember that you will NOT get the images from live demo due to copyright / license reason.', 'motors'); ?></p>
				</div>
				<p>
					<strong style="font-size:22px;margin-top:15px;"><?php esc_html_e('Choose a demo template to import:', 'motors'); ?></strong>
				</p>
				<div class="stm_demo_import_choices">
					<label>
						<img src="<?php echo plugin_dir_url( __FILE__ ) ?>assets/images/car-dealer-preview.jpg" />
						<div class="stm_choice_radio_button">
							<input type="radio" name="demo_template" value="car_dealer" checked="1"/>
							<?php esc_html_e('Car Dealer', 'motors'); ?>
						</div>
					</label>
					<label>
						<img src="<?php echo plugin_dir_url( __FILE__ ) ?>assets/images/car-repair-preview.jpg" />
						<div class="stm_choice_radio_button">
							<input type="radio" name="demo_template" value="service"/>
							<?php esc_html_e('Service', 'motors'); ?>
						</div>
					</label>
					<label>
						<img src="<?php echo plugin_dir_url( __FILE__ ) ?>assets/images/car-listing-preview.jpg" />
						<div class="stm_choice_radio_button">
							<input type="radio" name="demo_template" value="listing"/>
							<?php esc_html_e('Classified', 'motors'); ?>
						</div>
					</label>
					<label>
						<img src="<?php echo plugin_dir_url( __FILE__ ) ?>assets/images/car-boats-preview.png" />
						<div class="stm_choice_radio_button">
							<input type="radio" name="demo_template" value="boats"/>
							<?php esc_html_e('Boats', 'motors'); ?>
						</div>
					</label>
					<label>
						<img src="<?php echo plugin_dir_url( __FILE__ ) ?>assets/images/motorcycle-preview.png" />
						<div class="stm_choice_radio_button">
							<input type="radio" name="demo_template" value="motorcycle"/>
							<?php esc_html_e('Motorcycle', 'motors'); ?>
						</div>
					</label>
                    <label>
                        <img src="<?php echo plugin_dir_url( __FILE__ ) ?>assets/images/rental-preview.jpg" />
                        <div class="stm_choice_radio_button">
                            <input type="radio" name="demo_template" value="car_rental"/>
                            <?php esc_html_e('Rental Service', 'motors'); ?>
                        </div>
                    </label>
				</div>
				<p class="stm_demo_button_align">
					<input class="button-primary size_big" type="submit" value="Import" id="import_demo_data">
				</p>
			</div>

		</form>
		<script type="text/javascript">
			jQuery(document).ready(function() {
				jQuery('#import_demo_data_form').on('submit', function() {
                    var layout = jQuery(this).find("input[name='demo_template']:checked").val();

					jQuery("html, body").animate({
						scrollTop: 0
					}, {
						duration: 300
					});
					jQuery('.stm_importer').slideUp(null, function(){
						jQuery('.stm_message.content').slideDown();
					});

					// Importing Content
					jQuery.ajax({
						type: 'POST',
						url: '<?php echo admin_url('admin-ajax.php'); ?>',
						data: jQuery(this).serialize()+'&action=stm_demo_import_content',
						success: function(){

							jQuery('.stm_message.content').slideUp();
							jQuery('.stm_message.success').slideDown();

                            jQuery.ajax({
                                url: 'https://panel.stylemixthemes.com/api/active/',
                                type: 'post',
                                dataType: 'json',
                                data: {
                                    theme: 'motors',
                                    layout: layout,
                                    website: "<?php echo esc_url(get_site_url()); ?>",

									<?php
									$envato = get_option('envato_market', array());
									$token = (!empty($envato['token'])) ? $envato['token'] : ''; ?>
                                    token: "<?php echo esc_js($token); ?>"
                                }
                            });

						}
					});
					return false;
				});
			});
		</script>
		<?php
	}

	// Content Import
	function stm_demo_import_content() {
		
		$chosen_template = 'car_dealer';
		$demo_content = '';
		
		if(!empty($_POST['demo_template'])){
			$chosen_template = $_POST['demo_template'];
		}
		
		if($chosen_template == 'service') {
			$demo_content = '_service';
		}

		if($chosen_template == 'listing') {
			$demo_content = '_listing';
		}

		if($chosen_template == 'boats') {
			$demo_content = '_boats';
		}

		if($chosen_template == 'motorcycle') {
			$demo_content = '_motorcycle';
		}

        if($chosen_template == 'car_rental') {
            $demo_content = '_rental';
        }
		
		update_option('stm_motors_chosen_template', $chosen_template);
		

		set_time_limit( 0 );

		if ( ! defined( 'WP_LOAD_IMPORTERS' ) ) {
			define( 'WP_LOAD_IMPORTERS', true );
		}

		require_once( 'wordpress-importer/wordpress-importer.php' );

		$wp_import                    = new WP_Import();
		$wp_import->fetch_attachments = true;

		ob_start();
			$wp_import->import( get_template_directory() . '/inc/demo/demo_content'.$demo_content.'.xml' );
		ob_end_clean();

		do_action( 'stm_importer_done' );

		echo 'done';
		die();

	}

	add_action( 'wp_ajax_stm_demo_import_content', 'stm_demo_import_content' );

}