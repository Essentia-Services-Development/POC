<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Select2 AJAX control
 *
 * An AJAX based control for autocomplete functionaltiy
 *
 * @since 1.0.0
 */
class Select2Ajax_Control extends \Elementor\Base_Data_Control {
    public function __construct() {
        parent::__construct();
    }
    /**
     * Get Select2 AJAX control type.
     *
     * Retrieve the control type, in this case `select2ajax`.
     *
     * @since 1.0.0
     * @access public
     *
     * @return string Control type.
     */
    public function get_type() {
        return 'select2ajax';
    }

    /**
     * Enqueue Select2 AJAX control scripts and styles.
     *
     * Used to register and enqueue custom scripts and styles used by the Select2 AJAX
     * area control.
     *
     * @since 1.0.0
     * @access public
     */
    public function enqueue() {
        $current_dir_url = get_template_directory_uri().'/rehub-elementor/controls';
        wp_enqueue_script( 'select2-ajax', $current_dir_url . '/assets/select2-ajax.js', [ 'jquery' ], '1.0.0' );
    }

    /**
     * Get Select2 AJAX control default settings.
     *
     * Retrieve the default settings of the Select2 AJAX control. Used to return
     * the default settings while initializing the Select2 AJAX control.
     *
     * @since 1.0.0
     * @access protected
     *
     * @return array Control default settings.
     */
    protected function get_default_settings() {
        return [
            'label_block'         => true,
            'rows'                => 3,
            'select2ajax_options' => [],
            'callback'            => '',
            'linked_fields'       => ''
        ];
    }

    /**
     * Render Select2 AJAX control output in the editor.
     *
     * Used to generate the control HTML in the editor using Underscore JS
     * template. The variables for the class are available using `data` JS
     * object.
     *
     * @since 1.0.0
     * @access public
     */
    public function content_template() {
        $control_uid = $this->get_control_uid();
        ?>
        <div class="elementor-control-field">
            <label for="<?php echo esc_attr($control_uid); ?>" class="elementor-control-title">{{{ data.label }}}</label>
            <div class="elementor-control-input-wrapper">
                <# var multiple = ( data.multiple ) ? 'multiple' : ''; #>
                <select id="<?php echo esc_attr($control_uid); ?>" class="elementor-select2" type="select2" {{ multiple }} data-setting="{{ data.name }}" data-test="1">
                    <# _.each( data.options, function( option_title, option_value ) {
                        var value = data.controlValue;
                        if ( typeof value == 'string' ) {
                            var selected = ( option_value === value ) ? 'selected' : '';
                        } else if ( null !== value ) {
                            var value = _.values( value );
                            var selected = ( -1 !== value.indexOf( option_value ) ) ? 'selected' : '';
                        }
                        #>
                    <option {{ selected }} value="{{ option_value }}">{{{ option_title }}}</option>
                    <# } ); #>
                </select>
            </div>
        </div>
        <# if ( data.description ) { #>
            <div class="elementor-control-field-description">{{{ data.description }}}</div>
        <# } #>
        <?php
    }

    private function get_file_url( $file = __FILE__ ) {
        $wp_content_dir = str_replace( "\\", "/", WP_CONTENT_DIR );
        $file = str_replace( "\\", "/", $file );

        return content_url( str_replace( $wp_content_dir, '', $file) );
    }
}

/**
 * Register a custom control in elementor
 */



