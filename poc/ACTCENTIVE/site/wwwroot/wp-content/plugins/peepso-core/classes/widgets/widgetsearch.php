<?php

class PeepSoWidgetSearch extends WP_Widget
{

    /**
     * Set up the widget name etc
     */
    public function __construct($id = null, $name = null, $args= null) {
        if(!$id)    $id     = 'PeepSoWidgetSearch';
        if(!$name)  $name   = __('PeepSo Search', 'peepso-core');
        if(!$args)  $args   = array( 'description' => __('PeepSo Search Widget', 'peepso-core') . ' (Early Access)', );

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

        if(!array_key_exists('template', $instance) || !strlen($instance['template']))
        {
            $instance['template'] = 'search.tpl';
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
            'section_general' => FALSE,
            'limit'     => FALSE,
            'title'     => TRUE,

            // peepso
            'integrated'   => FALSE,
            'position'  => FALSE,
            'ordering'  => FALSE,
            'hideempty' => FALSE,
        );

        if (!isset($instance['title'])) {
            $instance['title'] = __('Search', 'peepso-core');
        }

        ob_start();
        $settings = apply_filters('peepso_widget_form', array('html'=>'', 'that'=>$this,'instance'=>$instance));
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

        return $instance;
    }
}

// EOF
