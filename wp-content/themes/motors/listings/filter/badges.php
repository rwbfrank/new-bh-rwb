<?php
$filter_badges = stm_get_filter_badges();
if (!empty($filter_badges)): ?>
    <div class="stm-filter-chosen-units">
        <ul class="stm-filter-chosen-units-list">
            <?php foreach ($filter_badges as $badge => $badge_info) : ?>
                <li>
                    <span><?php esc_html_e($badge_info['name'], 'motors'); ?>: </span>
                    <?php esc_html_e( str_replace('\\', '', $badge_info['value'] ), 'motors'); ?>
                    <i data-url="<?php echo esc_url( $badge_info['url'] ); ?>"
                       data-type="<?php echo $badge_info['type']; ?>"
                       data-slug="<?php echo $badge_info['slug']; ?>"
                       class="fa fa-close stm-clear-listing-one-unit stm-clear-listing-one-unit-classic"></i>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>