<?php
if(!function_exists('stm_input')) {
	function stm_input($args = array())
	{

		$defaults = array(
			'type' => 'text',
			'name' => '',
			'id' => '',
			'placeholder' => '',
			'data' => '',
			'value' => '',
			'class' => '',
			'link' => '',
			'options' => array()
		);

		$input = wp_parse_args($args, $defaults);

		$input_props = array();
		$input_props['id'] = ($input['id'] != '') ? 'id="' . $input['id'] . '"' : '';
		$input_props['name'] = ($input['name'] != '') ? 'name="' . $input['name'] . '"' : '';
		$input_props['placeholder'] = ($input['placeholder'] != '') ? 'placeholder="' . esc_attr($input['placeholder']) . '"' : '';
		$input_props['class'] = ($input['class'] != '') ? 'class="' . $input['class'] . '"' : '';
		$input_props['disabled'] = isset($input['disabled']) ? 'disabled="disabled"' : '';
		$input_props['data'] = $input['link'];
		$input_props['min'] = (isset($input['min'])) ? 'min="' . $input['min'] . '"' : '';
		$input_props['max'] = (isset($input['max'])) ? 'max="' . $input['max'] . '"' : '';

		switch ($input['type']) {

			case 'checkbox' : ?>
				<input
					type="checkbox" <?php echo implode(' ', $input_props); ?> <?php checked($input['value'], true); ?>/>
				<?php if (isset($input['label'])) { ?>
					<label for="<?php echo esc_attr($input['id']); ?>"><?php echo esc_html($input['label']); ?></label>
				<?php } ?>
				<?php break;

			case 'socials' : ?>
				<?php
				$values = array();
				if (!empty($input['value'])) {
					parse_str($input['value'], $values);
				}
				?>
				<form>
					<ul>
						<?php foreach ($input['options'] as $value => $label) { ?>
							<?php
							$input_value = '';
							if (!empty($values[$value])) {
								$input_value = $values[$value];
							}
							?>
							<li>
								<label><?php echo esc_html($label); ?></label>
								<input type="text" value="<?php echo $input_value; ?>"
									   name="<?php echo esc_attr($value); ?>"/>
							</li>

						<?php } ?>
					</ul>
				</form>
				<input type="hidden" <?php echo $input_props['data']; ?> value=""/>

				<?php break;

			case 'multiple-checkbox' : ?>
				<?php $multi_values = !is_array($input['value']) ? explode(',', $input['value']) : $input['value']; ?>
				<ul>
					<?php foreach ($input['options'] as $value => $label) { ?>

						<li>
							<label>
								<input type="checkbox"
									   value="<?php echo esc_attr($value); ?>" <?php checked(in_array($value, $multi_values)); ?> />
								<?php echo esc_html($label); ?>
							</label>
						</li>

					<?php } ?>
				</ul>

				<input type="hidden" <?php echo $input_props['data']; ?>
					   value="<?php echo esc_attr(implode(',', $multi_values)); ?>"/>

				<?php break;


            case 'text-repeater' : ?>
				<ul class="stm-diff-currency-list">
                    <?php
                    $currStr = ""; $symbolStr = ""; $toStr = "";
                    if(isset($input['options']['currency']) && count($input['options']['currency']) > 0 && $input['options']['currency'][0] != "") :
                        for($q=0;$q<count($input['options']['currency']);$q++) :
                        $currStr = ($q == 0) ? $currStr . $input['options']['currency'][$q] : $currStr . "," . $input['options']['currency'][$q];
                        $symbolStr = ($q == 0) ? $symbolStr . $input['options']['symbol'][$q] : $symbolStr . "," . $input['options']['symbol'][$q];
                        $toStr = ($q == 0) ? $toStr . $input['options']['to'][$q] : $toStr . "," . $input['options']['to'][$q];
                            if($input['options']['currency'][$q] != "") :
                    ?>

                            <li class="stm-tr-<?php echo $q; ?>">
                                <label>
                                    <div><?php echo esc_html__('Currency', 'motors'); ?></div>
                                    <input class="stm-text-repeater-currency" type="text" value="<?php echo esc_attr($input['options']['currency'][$q]); ?>" />
                                </label>
                                <label>
                                    <div><?php echo esc_html__('Symbol', 'motors'); ?></div>
                                    <input class="stm-text-repeater-symbol" type="text" value="<?php echo esc_attr($input['options']['symbol'][$q]); ?>" />
                                </label>
                                <label>
                                    <div><?php echo esc_html__('Conver. Rate', 'motors'); ?></div>
                                    <input class="stm-text-repeater-to" type="text" value="<?php echo esc_attr($input['options']['to'][$q]); ?>" />
                                </label>
                                <span class="stm-text-repeater-remove" data-position="<?php echo $q; ?>">
                                    <i class="fa fa-times" aria-hidden="true"></i>
                                </span>
                            </li>

					<?php
                            endif;
					    endfor;
					else : ?>
                        <li>
                            <label>
                                <?php echo esc_html__('Currency', 'motors'); ?>
                                <input class="stm-text-repeater-currency" type="text" />
                            </label>
                            <label>
                                <?php echo esc_html__('Symbol', 'motors'); ?>
                                <input class="stm-text-repeater-symbol" type="text" />
                            </label>
                            <label>
                                <?php echo esc_html__('Conver. Rate', 'motors'); ?>
                                <input class="stm-text-repeater-to" type="text" />
                            </label>
                        </li>
                    <?php endif; ?>
				</ul>
                   <input type="hidden" <?php echo $input_props['data']; ?> />
				<?php break;

			case 'select' : ?>
				<select size="1" <?php echo implode(' ', $input_props); ?>>
					<?php foreach ($input['options'] as $value => $label) { ?>
						<option
							value="<?php echo esc_attr($value); ?>" <?php selected($input['value'], $value, true); ?>>
							<?php echo esc_html($label); ?>
						</option>
					<?php } ?>
				</select>
				<?php break;

			case 'textarea' : ?>
				<textarea <?php echo implode(' ', $input_props); ?> <?php if (isset($input['rows'])) {
					echo 'rows="', esc_attr($input['rows']), '"';
				} ?>><?php echo esc_textarea($input['value']); ?></textarea>
				<?php break;

			case 'number' : ?>
				<input type="number" <?php echo implode(' ', $input_props); ?>
					   value="<?php echo esc_attr($input['value']); ?>"/>
				<?php break;

			case 'text' : ?>
				<input type="text" <?php echo implode(' ', $input_props); ?>
					   value="<?php echo esc_attr($input['value']); ?>"/>
				<?php break;

			default : ?>
				<input type="hidden" <?php echo implode(' ', $input_props); ?>
					   value="<?php echo esc_attr($input['value']); ?>"/>
			<?php }

	}
}