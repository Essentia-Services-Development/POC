<?php


class PeepSoWidgetFriends extends WP_Widget
{

    /**
     * Set up the widget name etc
     *
     * Last modified: July 29 2015
     * Last reviewed: July 29 2015
     * Review status: OK
     */
    public function __construct( $id = NULL, $name = NULL, $args= NULL ) {

        $id     = ( NULL !== $id )  ? $id   : 'PeepSoWidgetFriends';
        $name   = ( NULL !== $name )? $name : __('PeepSo Friends', 'friendso');
        $args   = ( NULL !== $args )? $args : array('description' => __('PeepSo Friends Widget', 'friendso'),);

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
     * Last modified: July 29 2015
     * Last reviewed: July 29 2015
     * Review status: @TODO
     */
    public function widget( $args, $instance ) {

        $view_id = NULL;
        $instance['hideempty']   = isset($instance['hideempty']) ? (int) $instance['hideempty'] : 0;

        if (isset($instance['is_profile_widget'])) {
            // Use currently viewed profile
            $view_id = PeepSoProfileShortcode::get_instance()->get_view_user_id();

            // Override the HTML wrappers
            $args = apply_filters('peepso_widget_args_internal', $args);
        }

        // Additional shared adjustments
        $instance = apply_filters('peepso_widget_instance', $instance);

        if (!$view_id) {
            $view_id = get_current_user_id();
        }

        $instance['template'] = 'friends.tpl';

        if(!array_key_exists('limit', $instance)) {
            $instance['limit'] = 12;
        }

        if (!array_key_exists('user_id', $instance)) {
            $instance['user_id'] = $view_id;
        }

        if (!array_key_exists('search_args', $instance)) {
            $instance['search_args'] = array(
                'number' => $instance['limit'],
            );
        }

        // @TODO guest should be able to see the friends of another person if privacy allows it
        if (!$instance['user_id']) {
            return false;
        }

        if (!array_key_exists('list', $instance) || isset($_GET['legacy-widget-preview'])) {
            $friendsModel = PeepSoFriendsModel::get_instance();

            $mayfly_list_friends = 'peepso_cache_widget_friendslist_'. $instance['user_id'];
            $mayfly_save_date = 'peepso_cache_widget_friendslist_savedate_'. $instance['user_id'];

            // check cache
            $list_friends = PeepSo3_Mayfly::get($mayfly_list_friends);
            $save_date = PeepSo3_Mayfly::get($mayfly_save_date);

            // load if no cache, load and cache
            // #5557 override cache when previewing in WP 5.8 block editor
            if(false === $list_friends || (date('Ymd') != $save_date)  || isset($_GET['legacy-widget-preview'])) {
                $list_friends = $friendsModel->get_friends($instance['user_id'], $instance['search_args']);
                if(!isset($_GET['legacy-widget-preview'])) {
                    PeepSo3_Mayfly::set($mayfly_list_friends, serialize($list_friends), 3 * HOUR_IN_SECONDS);
                    PeepSo3_Mayfly::set($mayfly_save_date, date('Ymd'), 3 * HOUR_IN_SECONDS);
                }
            } else {
                $list_friends = unserialize($list_friends);
                $list_friends = (array) $list_friends;
            }

            $instance['list'] = $list_friends;

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
			$instance['title'] = __('My Friends', 'friendso');
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