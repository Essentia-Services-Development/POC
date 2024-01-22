<?php

class PeepSoWidgetGroup extends WP_Widget {

    public function __construct( $id = NULL, $name = NULL, $args= NULL ) {

        $id     = ( NULL !== $id )  ? $id   : 'PeepSoGroupsWidgetGroup';
        $name   = ( NULL !== $name )? $name : __('PeepSo Groups: about the group', 'groupso');
        $args   = ( NULL !== $args )? $args : array('description' => __('PeepSo Groups: about the group', 'groupso'),);

        parent::__construct(
            $id,
            $name,
            $args
        );
    }

    public function widget( $args, $instance ) {

        $view_id = NULL;

        // Additional shared adjustments
        $instance = apply_filters('peepso_widget_instance', $instance);

        $instance['template'] = 'group-about.tpl';

        if (!array_key_exists('group_id', $instance)) {
            $instance['group_id'] = 124;
        }

        $instance['group_id'] = intval($instance['group_id']);

        if (!$instance['group_id']) {
            return false;
        }

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

            // peepso
            'integrated'   => FALSE,
            'position'  => FALSE,
            'hideempty' => FALSE,
        );

        if (!isset($instance['title'])) {
            $instance['title'] = sprintf(__('About %s', 'groupso'),'[GROUPNAME]');
        }

        $this->instance = $instance;

        $settings =  apply_filters('peepso_widget_form', array('html'=>'', 'that'=>$this,'instance'=>$instance));
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
        $instance['hideempty']   = isset($new_instance['hideempty']) ? (int) $new_instance['hideempty'] : 0;

        return $instance;
    }
}