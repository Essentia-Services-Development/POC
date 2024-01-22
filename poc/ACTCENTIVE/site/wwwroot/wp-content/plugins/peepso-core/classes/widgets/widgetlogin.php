<?php


class PeepSoWidgetLogin extends WP_Widget
{

    /**
     * Set up the widget name etc
     */
    public function __construct($id = null, $name = null, $args= null) {
        if(!$id)    $id     = 'PeepSoWidgetLogin';
        if(!$name)  $name   = __('PeepSo Login', 'peepso-core');
        if(!$args)  $args   = array( 'description' => __('PeepSo Login Widget', 'peepso-core'), );

        parent::__construct(
            $id, // Base ID
            $name, // Name
            $args // Args
        );
    }

    /**
     * Outputs the content of the widget
     *
     * @param array $args
     * @param array $instance
     */
    public function widget( $args, $instance ) {

        $instance['user_id']        = get_current_user_id();
        $instance['user']           = PeepSoUser::get_instance($instance['user_id']);
        $instance['view_option']    = isset($instance['view_option']) ? $instance['view_option'] : 'vertical';

        // Disable the widget for users
        if($instance['user_id'] > 0)
        {
            return FALSE;
        }

        if(!array_key_exists('template', $instance) || !strlen($instance['template']))
        {
            $instance['template'] = 'login.tpl';
        }

        PeepSoTemplate::exec_template( 'widgets', $instance['template'], array( 'args'=>$args, 'instance' => $instance ) );

        // Included in peepso bundle.
        wp_enqueue_script('peepso-widget-login', FALSE, array('peepso-bundle', 'peepso-notification'),
            PeepSo::PLUGIN_VERSION, TRUE);
        if (!is_user_logged_in() && PeepSo::get_option('recaptcha_login_enable', 0)) {
            wp_enqueue_script('peepso-recaptcha');
        }
    }


    /**
     * Outputs the admin options form
     *
     * @param array $instance The widget options
     */
    public function form( $instance ) {

        $instance['fields'] = array(
            // general
            'section_general' => FALSE,
            'limit'     => FALSE,
            'title'     => TRUE,

            // peepso
            'integrated'   => FALSE,
            'position'  => FALSE,
            'ordering'  => FALSE,
            'hideempty' => FALSE,

        );

        ob_start();

        $settings = apply_filters('peepso_widget_form', array('html'=>'', 'that'=>$this,'instance'=>$instance));
        // options
        $view_option = !empty($instance['view_option']) ? $instance['view_option'] : 'vertical';

        ?>
        <p>
            <label for="<?php echo $this->get_field_id('view_option'); ?>">
                <?php echo __('View option', 'peepso-core'); ?>
                <select class="widefat" id="<?php echo $this->get_field_id('view_option'); ?>"
                        name="<?php echo $this->get_field_name('view_option'); ?>">
                    <option value="vertical"><?php echo __('Vertical', 'peepso-core'); ?></option>
                    <option value="horizontal" <?php if('horizontal' === $view_option) echo ' selected="selected" ';?>><?php echo __('Horizontal', 'peepso-core'); ?></option>
                </select>

            </label>
        </p>
        <?php
        $settings['html']  .= ob_get_clean();

        echo $settings['html'];
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     * @param array $new_instance Values just sent to be saved.
     * @param array $old_instance Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title']       = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['view_option'] = isset($new_instance['view_option']) ? $new_instance['view_option'] : 'vertical';

        return $instance;
    }
}

// EOF
