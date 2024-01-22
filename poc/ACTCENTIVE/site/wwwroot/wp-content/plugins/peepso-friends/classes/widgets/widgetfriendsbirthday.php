<?php


class PeepSoWidgetFriendsbirthday extends WP_Widget
{

    /**
     * Set up the widget name etc
     *
     */
    public function __construct( $id = NULL, $name = NULL, $args= NULL ) {

        $id     = ( NULL !== $id )  ? $id   : 'PeepSoWidgetFriendsBirthday';
        $name   = ( NULL !== $name )? $name : __('PeepSo Friends Birthday', 'friendso');
        $args   = ( NULL !== $args )? $args : array('description' => __('PeepSo Friends Birthday Widget', 'friendso'),);

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
     */
    public function widget( $args, $instance ) {
        $view_id                                = get_current_user_id();
        $instance['show_upcoming_birthday']     = isset($instance['show_upcoming_birthday']) ? (int) $instance['show_upcoming_birthday'] : 0;
        $instance['how_many_days_ahead']        = isset($instance['how_many_days_ahead']) ? (int) $instance['how_many_days_ahead'] : 0;
        $instance['hideempty']                  = isset($instance['hideempty']) ? (int) $instance['hideempty'] : 0;

        if(isset($instance['is_profile_widget']))
        {
            // Use currently viewed profile
            $view_id = PeepSoProfileShortcode::get_instance()->get_view_user_id();

            // Override the HTML wrappers
            $args = apply_filters('peepso_widget_args_internal', $args);
        }

        // Additional shared adjustments
        $instance = apply_filters('peepso_widget_instance', $instance);

        if (!$view_id) {
            $view_id = isset($instance['user_id'])?$instance['user_id']:'';
        }

        // check if user_id is current user logged in
        if($view_id !== get_current_user_id()) {
            return false;
        }

        $instance['template'] = 'friendsbirthday.tpl';

        if(!array_key_exists('limit', $instance)) {
            $instance['limit'] = 12;
        }

        if (!array_key_exists('user_id', $instance)) {
            $instance['user_id'] = $view_id;
        }

        if (!array_key_exists('search_args', $instance)) {
            $instance['search_args'] = array(
                'number' => $instance['limit'],
                'days_ahead' => $instance['how_many_days_ahead'],
            );
        }

        $friendsBirthdayModel = PeepSoFriendsBirthdayModel::get_instance();
        $instance['list']['upcoming_birthday'] = array();

        if(1 === $instance['show_upcoming_birthday']) {

            $mayfly_upcoming_birthday = 'peepso_cache_widget_friendsupcomingbirthday_'. $instance['user_id'];
            $mayfly_save_date = 'peepso_cache_widget_friendsupcomingbirthday_savedate_'. $instance['user_id'];

            // check cache
            $list_upcoming_birthday = PeepSo3_Mayfly::get($mayfly_upcoming_birthday);
            $save_date = PeepSo3_Mayfly::get($mayfly_save_date);

            // load if no cache, load and cache
            if(false === $list_upcoming_birthday || (date('Ymd') != $save_date) || isset($_GET['legacy-widget-preview'])) {
                $list_upcoming_birthday = $friendsBirthdayModel->get_upcoming_birthday($instance['user_id'], $instance['search_args']);
                if(!isset($_GET['legacy-widget-preview'])) {
                    PeepSo3_Mayfly::set($mayfly_upcoming_birthday, serialize($list_upcoming_birthday), 3 * HOUR_IN_SECONDS);
                    PeepSo3_Mayfly::set($mayfly_save_date, date('Ymd'), 3 * HOUR_IN_SECONDS);
                }
            } else {
                $list_upcoming_birthday = unserialize($list_upcoming_birthday);
                $list_upcoming_birthday = (array) $list_upcoming_birthday;
            }

            $instance['list']['upcoming_birthday'] = $list_upcoming_birthday;
        }

        $mayfly_birthday = 'peepso_cache_widget_friendsbirthday_'. $instance['user_id'];
        $mayfly_save_date_birthday = 'peepso_cache_widget_friendsbirthday_savedate_'. $instance['user_id'];

        // check cache
        $list_birthday = PeepSo3_Mayfly::get($mayfly_birthday);
        $save_date_birthday = PeepSo3_Mayfly::get($mayfly_save_date_birthday);
        if(false === $list_birthday || (date('Ymd') != $save_date_birthday) || @isset($_GET['legacy-widget-preview'])) {
            $list_birthday = $friendsBirthdayModel->get_today_birthday($instance['user_id'], $instance['search_args']);
            if(@isset($_GET['legacy-widget-preview'])) {
                PeepSo3_Mayfly::set($mayfly_birthday, serialize($list_birthday), 3 * HOUR_IN_SECONDS);
                PeepSo3_Mayfly::set($mayfly_save_date_birthday, date('Ymd'), 3 * HOUR_IN_SECONDS);
            }
        } else {
            $list_birthday = unserialize($list_birthday);
            $list_birthday = (array) $list_birthday;
        }

        $instance['list']['today_birthday'] = $list_birthday;

        if(0==count($instance['list']['upcoming_birthday']) && 0==count($instance['list']['today_birthday']) && true == $instance['hideempty']) {
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
            'section_general' => FALSE,
            'limit'     => FALSE,
            'title'     => FALSE,

            // peepso
            'integrated'   => TRUE,
            'position'  => TRUE,
            'hideempty' => FALSE,
        );

		if (!isset($instance['title'])) {
			$instance['title'] = __('Upcoming Friends Birthdays', 'friendso');
		}

        $this->instance = $instance;

        ob_start();

        $title = !empty($instance['title']) ? $instance['title'] : '';
        $hideempty = isset($instance['hideempty']) ? $instance['hideempty'] : 1;
        $show_upcoming_birthday = !empty($instance['show_upcoming_birthday']) ? $instance['show_upcoming_birthday'] : '';
        $how_many_days_ahead = !empty($instance['how_many_days_ahead']) ? $instance['how_many_days_ahead'] : '';
        $limit = !empty($instance['limit']) ? $instance['limit'] : '';
        ?>
        <h3><?php echo __('General Settings', 'peepso-core');?></h3>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">
                <?php echo __('Title', 'peepso-core'); ?>
                <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                        name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title?>">
            </label>
        </p>
        <p>
            <input name="<?php echo $this->get_field_name('hideempty'); ?>" class="ace ace-switch ace-switch-2"
                    id="<?php echo $this->get_field_id('hideempty'); ?>" type="checkbox" value="1"
                    <?php if(1 === $hideempty) echo ' checked="" ';?>>
            <label class="lbl" for="<?php echo $this->get_field_id('hideempty'); ?>">
                <?php echo __('Hide if empty', 'peepso-core'); ?>
            </label>
        </p>
        <h3><?php echo __('Upcoming Birthdays', 'friendso');?></h3>
        <p>
            <input name="<?php echo $this->get_field_name('show_upcoming_birthday'); ?>" class="ace ace-switch ace-switch-2"
                    id="<?php echo $this->get_field_id('show_upcoming_birthday'); ?>" type="checkbox" value="1"
                    <?php if(1 === $show_upcoming_birthday) echo ' checked="" ';?>>
            <label class="lbl" for="<?php echo $this->get_field_id('show_upcoming_birthday'); ?>">
                <?php echo __('Show upcoming birthdays', 'friendso'); ?>
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('how_many_days_ahead'); ?>">
                <?php echo __('How many days ahead?', 'friendso'); ?>
                <select class="widefat" id="<?php echo $this->get_field_id('how_many_days_ahead'); ?>"
                        name="<?php echo $this->get_field_name('how_many_days_ahead'); ?>">
                    <?php
                    for($i=1;$i<=30;$i++){
                    ?>
                    <option value="<?php echo $i?>"<?php if($i === $how_many_days_ahead) echo ' selected="selected" ';?>><?php echo $i?></option>
                    <?php
                    }
                    ?>
                </select>
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('limit'); ?>">
                <?php echo __('Max number of upcoming birthdays to show:', 'friendso'); ?>
                <select class="widefat" id="<?php echo $this->get_field_id('limit'); ?>"
                        name="<?php echo $this->get_field_name('limit'); ?>">
                    <?php
                    for($i=1;$i<=10;$i++){
                    ?>
                    <option value="<?php echo $i?>"<?php if($i === $limit) echo ' selected="selected" ';?>><?php echo $i?></option>
                    <?php
                    }
                    ?>
                </select>
            </label>
        </p>
        <?php
        $settings = ob_get_clean();

        $settings =  apply_filters('peepso_widget_form', array('html'=> $settings, 'that'=>$this,'instance'=>$instance));

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

        // additional fields
        $instance['show_upcoming_birthday']     = isset($new_instance['show_upcoming_birthday']) ? (int) $new_instance['show_upcoming_birthday'] : 0;
        $instance['how_many_days_ahead']        = isset($new_instance['how_many_days_ahead']) ? (int) $new_instance['how_many_days_ahead'] : 0;

        $instance['hideempty']   = isset($new_instance['hideempty']) ? (int) $new_instance['hideempty'] : 0;
        $instance['position']    = isset($new_instance['position']) ? strip_tags($new_instance['position']) : 0;

        return $instance;
    }
}

// EOF