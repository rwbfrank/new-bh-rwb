<?php
$enable_features_search = get_theme_mod('enable_features_search', true);
if($enable_features_search):
    if(!empty($taxonomy)):
        $features = get_terms('stm_additional_features', array(
            'hide_empty' => true
        ));

        $selected = [];
        if(!empty($_GET['stm_features'])) {
            $selected = $_GET['stm_features'];
        }

        if(!empty($features) and !is_wp_error($features)): ?>
            <div class="col-md-12">
                <div class="stm-multiple-select">
                    <h5><?php esc_html_e('Additional features', 'motors'); ?></h5>
                    <select multiple="multiple" name="stm_features[]">
                        <?php foreach($features as $feature): ?>
                            <option value="<?php echo esc_attr($feature->slug) ?>"
                                <?php echo (in_array($feature->slug, $selected)) ? 'selected' : ''; ?>>
                                <?php echo sanitize_text_field($feature->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>