<?php


class PeepSoWidgetUserBar extends WP_Widget
{

    /**
     * Set up the widget name etc
     */
    public function __construct($id = null, $name = null, $args= null) {
        if(!$id)    $id     = 'PeepSoWidgetUserBar';
        if(!$name)  $name   = __('PeepSo UserBar', 'peepso-core');
        if(!$args)  $args   = array( 'description' => __('PeepSo User Bar Widget', 'peepso-core'), );

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

        $instance['user_id']                = get_current_user_id();
        $instance['user']                   = PeepSoUser::get_instance($instance['user_id']);
        $instance['content_position']       = isset($instance['content_position']) ? $instance['content_position'] : 'left';
        $instance['compact_mode']           = isset($instance['compact_mode']) ? (int) $instance['compact_mode'] : 1;
        $instance['show_avatar']            = isset($instance['show_avatar']) ? (int) $instance['show_avatar'] : 0;
        $instance['show_name']              = isset($instance['show_name']) ? (int) $instance['show_name'] : 0;
        $instance['show_notifications']     = isset($instance['show_notifications']) ? (int) $instance['show_notifications'] : 0;
        $instance['show_usermenu']          = isset($instance['show_usermenu']) ? (int) $instance['show_usermenu'] : 0;
        $instance['show_logout']            = isset($instance['show_logout']) ? (int) $instance['show_logout'] : 0;
        $instance['show_vip']               = isset($instance['show_vip']) ? (int) $instance['show_vip'] : 0;
        $instance['show_badges']            = isset($instance['show_badges']) ? (int) $instance['show_badges'] : 0;

        // Disable the widget for guests if
        if(isset($instance['guest_behavior']) && 'hide' === $instance['guest_behavior'] && !$instance['user_id'])
        {
            return FALSE;
        }


        // List of links to be displayed
        $links = apply_filters('peepso_navigation_profile', array('_user_id'=>get_current_user_id()));
        $instance['links'] = $links;


        if(!array_key_exists('template', $instance) || !strlen($instance['template']))
        {
            $instance['template'] = 'userbar.tpl';
        }

        $instance['toolbar'] = '';
        if(isset($instance['show_notifications']) && 1 === intval($instance['show_notifications'])) {
                $instance['toolbar'] = $this->toolbar();
        }


        PeepSoTemplate::exec_template( 'widgets', $instance['template'], array( 'args'=>$args, 'instance' => $instance ) );

        // Included in peepso bundle.
        wp_enqueue_script('peepso-widget-userbar', FALSE, array('peepso-bundle', 'peepso-notification'),
            PeepSo::PLUGIN_VERSION, TRUE);
    }

    // Displays the frontend navbar
    public function toolbar()
    {
        $note = PeepSoNotifications::get_instance();
        $unread_notes = $note->get_unread_count_for_user();

        $toolbar = array(
            'notifications' => array(
                'href' => PeepSo::get_page('notifications'),
                'icon' => 'gcis gci-bell',
                'class' => 'ps-notif ps-notif--general dropdown-notification ps-js-notifications',
                'title' => __('Pending Notifications', 'peepso-core'),
                'count' => $unread_notes,
                'order' => 100
            ),
        );

        $toolbar = PeepSoGeneral::get_instance()->get_navigation('notifications');

        ob_start();
        ?>

        <?php foreach ($toolbar as $item => $data) { ?>
          <div class="ps-notif <?php echo $data['class'];?>">
            <a class="ps-notif__toggle" href="<?php echo $data['href'];?>" title="<?php echo esc_attr($data['label']);?>">
              <i class="<?php echo $data['icon'];?>"></i>
              <span class="ps-notif__bubble js-counter ps-js-counter"><?php echo ($data['count'] > 0) ? $data['count'] : '';?></span>
            </a>
          </div>
        <?php } ?>

        <?php
        $html = str_replace(PHP_EOL,'',ob_get_clean());

        return $html;
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
            'integrated'   => FALSE,
            'position'  => FALSE,
            'ordering'  => FALSE,
            'hideempty' => FALSE,

        );

        ob_start();

        $settings = apply_filters('peepso_widget_form', array('html'=>'', 'that'=>$this,'instance'=>$instance));

        $guest_behavior         = !empty($instance['guest_behavior']) ? $instance['guest_behavior'] : 'hide';

        $content_position       = !empty($instance['content_position']) ? $instance['content_position'] : 'left';

        $compact_mode           = isset($instance['compact_mode']) ? $instance['compact_mode'] : 1;

        $show_notifications     = isset($instance['show_notifications']) ? $instance['show_notifications'] : 1;

        $show_usermenu          = isset($instance['show_usermenu']) ? $instance['show_usermenu'] : 1;

        $show_avatar            = isset($instance['show_avatar']) ? $instance['show_avatar'] : 1;

        $show_name              = isset($instance['show_name']) ? $instance['show_name'] : 1;

        $show_logout            = isset($instance['show_logout']) ? $instance['show_logout'] : 0;

        $show_vip               = isset($instance['show_vip']) ? $instance['show_vip'] : 0;

        $show_badges            = isset($instance['show_badges']) ? $instance['show_badges'] : 0;

        ?>
        <p>
            <label for="<?php echo $this->get_field_id('content_position'); ?>">
                <?php echo __('Content Position', 'peepso-core'); ?>
                <select class="widefat" id="<?php echo $this->get_field_id('content_position'); ?>"
                        name="<?php echo $this->get_field_name('content_position'); ?>">
                    <option value="left" <?php if('left' === $content_position) echo ' selected="selected" ';?>><?php echo __('Left', 'peepso-core'); ?></option>
                    <option value="right" <?php if('right' === $content_position) echo ' selected="selected" ';?>><?php echo __('Right', 'peepso-core'); ?></option>
                    <option value="center" <?php if('center' === $content_position) echo ' selected="selected" ';?>><?php echo __('Center', 'peepso-core'); ?></option>
                    <option value="space" <?php if('space' === $content_position) echo ' selected="selected" ';?>><?php echo __('Space Between', 'peepso-core'); ?></option>
                </select>
            </label>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('guest_behavior'); ?>">
                <?php echo __('Guest view', 'peepso-core'); ?>
                <select class="widefat" id="<?php echo $this->get_field_id('guest_behavior'); ?>"
                        name="<?php echo $this->get_field_name('guest_behavior'); ?>">
                    <option value="login"><?php echo __('Log-in link', 'peepso-core'); ?></option>
                    <option value="hide" <?php if('hide' === $guest_behavior) echo ' selected="selected" ';?>><?php echo __('Hide', 'peepso-core'); ?></option>
                </select>
            </label>
        </p>
        <b><?php echo __('Name style', 'peepso-core');?></b>
        <p>
          <label for="<?php echo $this->get_field_id('show_name'); ?>">
              <select class="widefat" id="<?php echo $this->get_field_id('show_name'); ?>"
                      name="<?php echo $this->get_field_name('show_name'); ?>">
                  <option value="0"><?php echo __('Hidden', 'peepso-core'); ?></option>
                  <option value="1" <?php if(1 === $show_name) echo ' selected="selected" ';?>><?php echo __('Short name', 'peepso-core'); ?></option>
                  <option value="2" <?php if(2 === $show_name) echo ' selected="selected" ';?>><?php echo __('Full name', 'peepso-core'); ?></option>
              </select>
          </label>
        </p>
        <b><?php echo __('Compact mode', 'peepso-core');?> <abbr title='When enabled, the Userbar is hidden under a profile icon toggle. "Disabled" will only work properly on mobile if there are no other widgets and elements (like logo) next to the widget. This setting has no effect when previewing the widget in a block editor.'><i class="gcis gci-question-circle"></i></abbr></b>
        <p>
            <label for="<?php echo $this->get_field_id('compact_mode'); ?>">
                <select class="widefat" id="<?php echo $this->get_field_id('compact_mode'); ?>"
                        name="<?php echo $this->get_field_name('compact_mode'); ?>">
                    <option value="0"><?php echo __('Disable', 'peepso-core'); ?></option>
                    <option value="1" <?php if(1 === $compact_mode) echo ' selected="selected" ';?>><?php echo __('Mobile', 'peepso-core'); ?></option>
                    <option value="2" <?php if(2 === $compact_mode) echo ' selected="selected" ';?>><?php echo __('Desktop', 'peepso-core'); ?></option>
                    <option value="3" <?php if(3 === $compact_mode) echo ' selected="selected" ';?>><?php echo __('Always', 'peepso-core'); ?></option>
                </select>
            </label>
        </p>
        <b><?php echo __('Other elements', 'peepso-core');?></b>
        <p>
            <input name="<?php echo $this->get_field_name('show_avatar'); ?>" class="ace ace-switch ace-switch-2"
                   id="<?php echo $this->get_field_id('show_avatar'); ?>" type="checkbox" value="1"
                <?php if(1 === $show_avatar) echo ' checked="" ';?>>
            <label class="lbl" for="<?php echo $this->get_field_id('show_avatar'); ?>">
                <?php echo __('Avatar', 'peepso-core'); ?>
            </label>
        </p>
        <p>
            <input name="<?php echo $this->get_field_name('show_notifications'); ?>" class="ace ace-switch ace-switch-2"
                   id="<?php echo $this->get_field_id('show_notifications'); ?>" type="checkbox" value="1"
                <?php if(1 === $show_notifications) echo ' checked="" ';?>>
            <label class="lbl" for="<?php echo $this->get_field_id('show_notifications'); ?>">
                <?php echo __('Notifications', 'peepso-core'); ?>
            </label>
        </p>
        <p>
            <input name="<?php echo $this->get_field_name('show_usermenu'); ?>" class="ace ace-switch ace-switch-2"
                   id="<?php echo $this->get_field_id('show_usermenu'); ?>" type="checkbox" value="1"
                <?php if(1 === $show_usermenu) echo ' checked="" ';?>>
            <label class="lbl" for="<?php echo $this->get_field_id('show_usermenu'); ?>">
                <?php echo __('User dropdown menu', 'peepso-core'); ?>
            </label>
        </p>
        <p>
            <input name="<?php echo $this->get_field_name('show_logout'); ?>" class="ace ace-switch ace-switch-2"
                   id="<?php echo $this->get_field_id('show_logout'); ?>" type="checkbox" value="1"
                <?php if(1 === $show_logout) echo ' checked="" ';?>>
            <label class="lbl" for="<?php echo $this->get_field_id('show_logout'); ?>">
                <?php echo __('Logout icon', 'peepso-core'); ?>
            </label>
        </p>
        <?php if (class_exists('PeepSoVIP')) {?>
        <p>
            <input name="<?php echo $this->get_field_name('show_vip'); ?>" class="ace ace-switch ace-switch-2"
                   id="<?php echo $this->get_field_id('show_vip'); ?>" type="checkbox" value="1"
                <?php if(1 === $show_vip) echo ' checked="" ';?>>
            <label class="lbl" for="<?php echo $this->get_field_id('show_vip'); ?>">
                <?php echo __('VIP icons', 'peepso-core'); ?>
            </label>
        </p>
        <?php
        }
        if (class_exists('BadgeOS_PeepSo')) {?>
        <p>
            <input name="<?php echo $this->get_field_name('show_badges'); ?>" class="ace ace-switch ace-switch-2"
                   id="<?php echo $this->get_field_id('show_badges'); ?>" type="checkbox" value="1"
                <?php if(1 === $show_badges) echo ' checked="" ';?>>
            <label class="lbl" for="<?php echo $this->get_field_id('show_badges'); ?>">
                <?php echo __('Badges', 'peepso-core'); ?>
            </label>
        </p>
        <?php
        }
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
        $instance['guest_behavior']         = isset($new_instance['guest_behavior']) ? $new_instance['guest_behavior'] : 'hide';
        $instance['content_position']       = isset($new_instance['content_position']) ? $new_instance['content_position'] : 'left';
        $instance['compact_mode']           = isset($new_instance['compact_mode']) ? (int) $new_instance['compact_mode'] : 0;
        $instance['show_avatar']            = isset($new_instance['show_avatar']) ? (int) $new_instance['show_avatar'] : 0;
        $instance['show_name']              = isset($new_instance['show_name']) ? (int) $new_instance['show_name'] : 0;
        $instance['show_notifications']     = isset($new_instance['show_notifications']) ? (int) $new_instance['show_notifications'] : 0;
        $instance['show_usermenu']          = isset($new_instance['show_usermenu']) ? (int) $new_instance['show_usermenu'] : 0;
        $instance['show_logout']            = isset($new_instance['show_logout']) ? (int) $new_instance['show_logout'] : 0;
        $instance['show_vip']               = isset($new_instance['show_vip']) ? (int) $new_instance['show_vip'] : 0;
        $instance['show_badges']            = isset($new_instance['show_badges']) ? (int) $new_instance['show_badges'] : 0;

        return $instance;
    }
}

// EOF
