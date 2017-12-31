<?php

// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'Direct script access denied.' );
}

$query = new WP_Query(array(
	'post_type' 	=> 'listings',
	'post_status' 	=> 'publish',
	'posts_per_page'=> -1,
	'meta_query' 	=> array(
		array(
			'key' 		=> 'stm_lat_car_admin',
			'compare' 	=> 'NOT EXISTS'
		),
	),
));

$theme = stm_get_theme_info();
$theme_name = $theme['name'];
$listings_location_empty_count = $query->post_count;
wp_reset_query();
?>
	<div class="wrap about-wrap stm-admin-wrap  stm-admin-support-screen">
		<?php stm_get_admin_tabs('patching'); ?>
		<?php if($listings_location_empty_count > 0): ?>
		<div class="stm-admin-important-notice stm-admin-important-patch-notice">

		<p class="about-description">
			<div>
				<p><?php esc_html_e( 'Listings location needs to be updated. Please click the button below and wait.', 'motors' ); ?></p>
			</div>
			<form id="stm_start_location_patch">
				<input type="hidden" id="stm_location_patch_offset" name="offset" value="0">
				<input type="hidden" name="action" value="stm_admin_patch_location">
				<button type="submit" class="button button-large button-primary stm-admin-button">
					<?php esc_html_e('Patch', 'motors'); ?>
				</button>
			</form>
		</p>
	</div>
	<div class="stm-patch-price-stat">
		<?php
			if($listings_location_empty_count > 0): ?>
				<h4><?php esc_html_e('Patch in progress, dont reload the page.' , 'motors') ?></h4>
				<span class="offset">0</span>/<span class="total"><?php echo intval($listings_location_empty_count); ?></span>
				<div class="error_mess" style="color: #ff0000;"></div>
			<?php
		endif; ?>
	</div>
<?php else: ?>
	<p class="about-description">
	<h4><?php esc_html_e('Your location are up-to-date.', 'motors') ?></h4>
	</p>
<?php endif; ?>
</div>


<!--Patching price code-->
<script type="text/javascript">
    jQuery(document).ready(function() {
        var $ = jQuery;
        $('#stm_start_location_patch').on('submit', function(e) {
            e.preventDefault();
            var offset = $(this).find('#stm_location_patch_offset').val();

            $('.stm-admin-important-patch-notice').slideUp();
            $('.stm-patch-price-stat').slideDown();

            stmLocationPatchingAjax(offset);
        });

        function stmLocationPatchingAjax(offset) {
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                data: {
                    'offset' : offset,
                    'action' : 'stm_admin_patch_location'
                },
                method: 'POST',
                dataType: 'json',
                context: this,
                success: function(data){
                    console.log(data);
                    if(typeof data.error_message !== 'undefined' && data.error_message != '') {
						$('.error_mess').text(data.error_message + ' Please try to run the patch after several hours!' );
					} else if(data.offset != 'none') {
                        $('.stm-patch-price-stat .offset').text(data.offset);
                        stmLocationPatchingAjax(data.offset);
                    } else {
                        window.location.reload();
                    }
                }
            });
        }
    });
</script>