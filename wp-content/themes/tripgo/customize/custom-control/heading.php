<?php if (!defined( 'ABSPATH' )) { exit; }
if ( ! class_exists( 'WP_Customize_Control' ) ) return NULL;
/**
 * Class OTF_Customize_Control_Heading
 */
class Tripgo_Customize_Control_Heading extends WP_Customize_Control {

    public $type = 'heading';

    /**
     * @return  void
     */
    public function render_content() {
        if ( ! empty( $this->label ) ) :
            ?>
            <h4 style="text-transform: uppercase; background: #343434; padding: 10px; color: #fff; margin-left: -13px;margin-right: -13px;"><?php echo esc_html( $this->label ); ?></h4>
            <?php
        endif;

        if ( ! empty( $this->description ) ) :
            ?>
            <span class="description customize-control-description"><?php echo '' . $this->description ; ?></span>
        <?php endif;
    }
}
