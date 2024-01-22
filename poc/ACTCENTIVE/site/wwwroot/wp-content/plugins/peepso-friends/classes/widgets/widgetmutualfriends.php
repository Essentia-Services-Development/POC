<?php


class PeepSoWidgetMutualfriends extends WP_Widget
{

    /**
     * Set up the widget name etc
     *
     * Last modified: April 11 2016
     */
    public function __construct( $id = NULL, $name = NULL, $args= NULL ) {

        $id     = ( NULL !== $id )  ? $id   : 'PeepSoWidgetMutualfriends';
        $name   = ( NULL !== $name )? $name : __('PeepSo Mutual Friends', 'friendso');
        $args   = ( NULL !== $args )? $args : array('description' => __('PeepSo Mutual Friends Widget', 'friendso'),);

        parent::__construct(
           $id,
           $name,
           $args
        );
    }

    /**
     * Outputs the content of the widget
     *
     * @param array $args
     * @param array $instance
     * @return void
     *
     * Last modified: April 11 2016
     */
    public function widget( $args, $instance ) {

        // Use currently viewed profile
        $view_id = PeepSoProfileShortcode::get_instance()->get_view_user_id();

        /**
         * Quit if:
         * current user is a guest
         * the profile viewed is my own
         * we are not looking at a profile at all
         */
        if(!get_current_user_id() || !$view_id || $view_id == get_current_user_id()) {
            return FALSE;
        }

        // Override the HTML wrappers
        if (isset($instance['is_profile_widget'])) {
            $args = apply_filters('peepso_widget_args_internal', $args);
        }

        // Additional shared adjustments
        $instance = apply_filters('peepso_widget_instance', $instance);

        $instance['template'] = 'mutual-friends.tpl';

        if(!array_key_exists('limit', $instance)) {
            $instance['limit'] = 12;
        }

        if (!array_key_exists('user_id', $instance)) {
            $instance['user_id'] = $view_id;
        }

        if (!array_key_exists('hideempty', $instance)) {
            $instance['hideempty'] = 0;
        }
        
        if (!array_key_exists('search_args', $instance)) {
            $instance['search_args'] = array(
                'offset' => 0,
                'number' => $instance['limit'],
            );
        }

        if (!array_key_exists('list', $instance)) {
            $friendsModel = PeepSoFriendsModel::get_instance();
            $instance['list'] = $friendsModel->get_mutual_friends($instance['user_id'], get_current_user_id(), $instance['search_args']);
        }

        if(0==count($instance['list']) && true == $instance['hideempty']) {
            return FALSE;
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
            'limit'     => TRUE,
            'title'     => TRUE,

            // peepso
            'integrated'   => TRUE,
            'position'  => TRUE,
            'hideempty' => TRUE,
        );
		
		if (!isset($instance['title'])) {
			$instance['title'] = __('Mutual Friends', 'friendso');
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
        $instance['limit']       = isset($new_instance['limit']) ? (int) $new_instance['limit'] : 12;

        $instance['hideempty']   = isset($new_instance['hideempty']) ? (int) $new_instance['hideempty'] : 0;
        $instance['position']    = isset($new_instance['position']) ? strip_tags($new_instance['position']) : 0;

        return $instance;
    }
}

// EOF