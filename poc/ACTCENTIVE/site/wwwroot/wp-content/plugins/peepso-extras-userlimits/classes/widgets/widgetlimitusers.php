<?php


class PeepSoWidgetLimitUsers extends WP_Widget
{
    public function __construct( $id = NULL, $name = NULL, $args= NULL ) {

        $id     = ( NULL !== $id )  ? $id   : 'PeepSoWidgetLimitUsers';
        $name   = ( NULL !== $name )? $name : __('PeepSo User Limits', 'peepsolimitusers');
        $args   = ( NULL !== $args )? $args : array('description' => __('PeepSo User Limits Widget', 'peepsolimitusers'),);

        parent::__construct(
            $id,
            $name,
            $args
        );
    }

    public function widget( $args, $instance ) {
        $PeepSoLimitUsers = PeepSoLimitUsers::get_instance();
        if(!is_array($PeepSoLimitUsers->debug) || !count($PeepSoLimitUsers->debug)) { return NULL; }

        $instance['user_id']    = get_current_user_id();
        $instance['user']       = PeepSoUser::get_instance($instance['user_id']);

        $user  = $instance['user'];

        if($instance['user_id'] > 0 && $instance['user_id'] == get_current_user_id()) {
            $user->profile_fields->load_fields();
            $stats = $user->profile_fields->profile_fields_stats;
            $instance['stats'] = $stats;
            
            ob_start();
            PeepSoLimitUsers::get_instance()->debug_formatted();
            $debug = ob_get_clean();

            if(strlen($debug) == 0 && strlen($instance['message']) == 0 && true == $instance['hideempty']) {
                return FALSE;
            }
        }

        $instance['template'] = 'limitusers.tpl';
        PeepSoTemplate::exec_template( 'widgets', $instance['template'], array( 'args'=>$args, 'instance' => $instance ) );
    }

    /**
     * Outputs the admin options form
     *
     * @param array $instance The widget options
     */
    public function form( $instance ) {

        $instance['fields'] = array(
            // general
            'limit'     => FALSE,
            'title'     => TRUE,
            'hideempty' => TRUE,
        );

        if (!isset($instance['title'])) {
            $instance['title'] = '';
        }

        if (!isset($instance['message'])) {
            $instance['message'] = __('Your actions in the Community are limited.','peepsolimitusers');
        }

        $this->instance = $instance;

        add_filter('peepso_widget_form', array(&$this, 'filter_admin_form'));

        $settings = apply_filters('peepso_widget_form', array('html'=>'', 'that'=>$this,'instance'=>$instance));

        remove_filter('peepso_widget_form', array(&$this, 'filter_admin_form'));
        echo $settings['html'];
    }

    public function filter_admin_form($arr) {
        ob_start();
        $message = !empty($arr['instance']['message']) ? $arr['instance']['message'] : '';
        ?>
        <p>
            <label for="<?php echo $arr['that']->get_field_id('message'); ?>"><?php echo __('Message:'); ?></label>
            <input class="widefat" id="<?php echo $arr['that']->get_field_id('message'); ?>"
                   name="<?php echo $arr['that']->get_field_name('message'); ?>" type="text" value="<?php echo esc_attr($message); ?>">
        </p>
        <?php
        $arr['html'] .= ob_get_clean();

        return $arr;
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
        $instance['message']     = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['message'] ) : '';
        $instance['limit']       = isset($new_instance['limit']) ? (int) $new_instance['limit'] : 12;
        $instance['hideempty']   = isset($new_instance['hideempty']) ? (int) $new_instance['hideempty'] : 0;
        return $instance;
    }
}

// EOF