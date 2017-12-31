<?php
/**
 * Created by PhpStorm.
 * User: Пользователь
 * Date: 01.06.2017
 * Time: 9:56
 */
if ( ! class_exists( 'STM_Customizer_Text_Repeater_Control' ) ) {

    class STM_Customizer_Text_Repeater_Control extends WP_Customize_Control {

        public $type = 'stm-text-repeater';

        public function render_content() {

            $input_args = array(
                'type'    => 'text-repeater',
                'label'   => $this->label,
                'name'    => '',
                'description' => $this->description,
                'id'      => $this->id,
                'value'   => $this->value(),
                'link'    => $this->get_link(),
                'options' => $this->choices
            );

            ?>

            <div id="stm-customize-control-<?php echo esc_attr( $this->id ); ?>" class="stm-customize-control stm-customize-control-<?php echo esc_attr( str_replace( 'stm-', '', $this->type ) ); ?>">

				<span class="customize-control-title">
					<div><?php echo esc_html( $this->label ); ?></div>
				</span>

                <div class="stm-form-item">
                    <div class="stm-text-repeater-wrapper stm-form-items">
                        <?php stm_input( $input_args ); ?>
                    </div>
                </div>

                <?php if ( '' != $this->description ) : ?>
                    <div class="description customize-control-description">
                        <?php echo esc_html( $this->description ); ?>
                    </div>
                <?php endif; ?>
                <div class="stm-text-repeater-btn-wrap">
                    <button type="button" class="button stm-text-repeater-btn"><?php echo esc_html__("Add Currency", 'motors'); ?></button>
                </div>

            </div>
            <?php
        }
    }
}