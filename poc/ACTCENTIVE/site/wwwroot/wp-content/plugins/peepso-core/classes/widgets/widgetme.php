<?php


class PeepSoWidgetMe extends WP_Widget
{

    /**
     * Set up the widget name etc
     */
    public function __construct($id = null, $name = null, $args= null) {
        if(!$id)    $id     = 'PeepSoWidgetMe';
        if(!$name)  $name   = __('PeepSo Profile', 'peepso-core');
        if(!$args)  $args   = array( 'description' => __('PeepSo Profile Widget', 'peepso-core'), );

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
        $instance['show_in_profile']        = isset($instance['show_in_profile']) ? $instance['show_in_profile'] : 3;
        $instance['show_community_links']   = isset($instance['show_community_links']) ? (int) $instance['show_community_links'] : 0;
        $instance['show_cover']             = isset($instance['show_cover']) ? (int) $instance['show_cover'] : 0;

        // Disable the widget for guests if
        if(isset($instance['guest_behavior']) && 'hide' === $instance['guest_behavior'] && !$instance['user_id'])
        {
            return FALSE;
        }

        // Hide from profile page?
        global $post;
        if ($post instanceof  WP_Post) {
            $profile_page = $post->post_type == 'page' && stristr($post->post_content,'[peepso_profile');

            // https://gitlab.com/PeepSo/PeepSo/-/issues/4753
            if(!$profile_page) {
                global $wp_query;

                if($wp_query instanceof WP_Query && isset($wp_query->post) && $wp_query->post instanceof WP_Post && stristr($wp_query->post->post_content,'[peepso_profile')) {
                    $profile_page = TRUE;
                }
            }
            if (!$profile_page && $post->post_type === 'peepso-post') {
                $url = PeepSoUrlSegments::get_instance();
                if ($url->_shortcode === 'peepso_profile') {
                    $profile_page = true;
                }
            }

            // 3 = always show
            if($profile_page && $instance['show_in_profile'] < 3) {

                // 0 = always hide
                if (0 == $instance['show_in_profile']) {
                    return FALSE;
                }

                $PeepSoProfile = PeepSoProfileShortcode::get_instance();
                $view_id = $PeepSoProfile->get_view_user_id();

                // 1 = show on "mine" and hide on "theirs"
                if (1 == $instance['show_in_profile'] && $view_id != $instance['user_id']) {
                    return FALSE;
                }

                // 2 = hide on "mine" and show on "theirs"
                if (2 == $instance['show_in_profile'] && $view_id == $instance['user_id']) {
                    return FALSE;
                }
            }
        }

        // List of links to be displayed
        $links = apply_filters('peepso_navigation_profile', array('_user_id'=>get_current_user_id()));

        $community_links = apply_filters('peepso_navigation', array());
        unset($community_links['profile']);

        $instance['links'] = $links;
        $instance['community_links'] = $community_links;


        if(!array_key_exists('template', $instance) || !strlen($instance['template']))
        {
            $instance['template'] = 'me.tpl';
        }

        $instance['toolbar'] = '';
        if(isset($instance['show_notifications']) && 1 === intval($instance['show_notifications'])) {
                $instance['toolbar'] = $this->toolbar();
        }


        PeepSoTemplate::exec_template( 'widgets', $instance['template'], array( 'args'=>$args, 'instance' => $instance ) );

        // Included in peepso bundle.
        wp_enqueue_script('peepso-widget-me', FALSE, array('peepso-bundle', 'peepso-notification'),
            PeepSo::PLUGIN_VERSION, TRUE);
        if (!is_user_logged_in() && PeepSo::get_option('recaptcha_login_enable', 0)) {
            wp_enqueue_script('peepso-recaptcha');
        }
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
                'class' => 'ps-notif--general dropdown-notification ps-js-notifications',
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
            'title'     => TRUE,

            // peepso
            'integrated'   => FALSE,
            'position'  => FALSE,
            'ordering'  => FALSE,
            'hideempty' => FALSE,

        );

        ob_start();

        $settings =  apply_filters('peepso_widget_form', array('html'=>'', 'that'=>$this,'instance'=>$instance));

        $guest_behavior         = !empty($instance['guest_behavior']) ? $instance['guest_behavior'] : 'login';
        $show_notifications     = isset($instance['show_notifications']) ? $instance['show_notifications'] : 1;
        $show_community_links   = isset($instance['show_community_links']) ? $instance['show_community_links'] : 0;
        $show_cover             = isset($instance['show_cover']) ? $instance['show_cover'] : 0;
        $show_in_profile     = isset($instance['show_in_profile']) ? $instance['show_in_profile'] : 3;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('guest_behavior'); ?>">
                <?php echo __('Guest view', 'peepso-core'); ?>
                <select class="widefat" id="<?php echo $this->get_field_id('guest_behavior'); ?>"
                        name="<?php echo $this->get_field_name('guest_behavior'); ?>">
                    <option value="login"><?php echo __('Log-in form', 'peepso-core'); ?></option>
                    <option value="hide" <?php if('hide' === $guest_behavior) echo ' selected="selected" ';?>><?php echo __('Hide', 'peepso-core'); ?></option>
                </select>

            </label>
        </p>
        <p>
            <input name="<?php echo $this->get_field_name('show_notifications'); ?>" class="ace ace-switch ace-switch-2"
                   id="<?php echo $this->get_field_id('show_notifications'); ?>" type="checkbox" value="1"
                <?php if(1 === $show_notifications) echo ' checked="" ';?>>
            <label class="lbl" for="<?php echo $this->get_field_id('show_notifications'); ?>">
                <?php echo __('Show notifications', 'peepso-core'); ?>
            </label>
        </p>
        <p>
            <input name="<?php echo $this->get_field_name('show_community_links'); ?>" class="ace ace-switch ace-switch-2"
                   id="<?php echo $this->get_field_id('show_community_links'); ?>" type="checkbox" value="1"
                <?php if(1 === $show_community_links) echo ' checked="" ';?>>
            <label class="lbl" for="<?php echo $this->get_field_id('show_community_links'); ?>">
                <?php echo __('Show community links', 'peepso-core'); ?>
            </label>
        </p>

        <p>
            <input name="<?php echo $this->get_field_name('show_cover'); ?>" class="ace ace-switch ace-switch-2"
                   id="<?php echo $this->get_field_id('show_cover'); ?>" type="checkbox" value="1"
                <?php if(1 === $show_cover) echo ' checked="" ';?>>
            <label class="lbl" for="<?php echo $this->get_field_id('show_cover'); ?>">
                <?php echo __('Show cover', 'peepso-core'); ?>
            </label>
        </p>

        <p>
            <label class="lbl" for="<?php echo $this->get_field_id('show_in_profile'); ?>">
                <?php echo __('Show on the Profile page', 'peepso-core'); ?>:
            </label>
            <select name="<?php echo $this->get_field_name('show_in_profile'); ?>" class="ace ace-switch ace-switch-2"
                   id="<?php echo $this->get_field_id('show_in_profile'); ?>" type="checkbox" value="1"
                >
                <option value="0"><?php echo __('Never', 'peepso-core');?></option>
                <option value="1" <?php if(1 === $show_in_profile) echo ' selected="selected" ';?>><?php echo __('When on my profile', 'peepso-core');?></option>
                <option value="2" <?php if(2 === $show_in_profile) echo ' selected="selected" ';?>><?php echo __('When not on my profile', 'peepso-core');?></option>
                <option value="3" <?php if(3 === $show_in_profile) echo ' selected="selected" ';?>><?php echo __('Always', 'peepso-core');?></option>
            </select>

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
        $instance['guest_behavior']         = isset($new_instance['guest_behavior']) ? $new_instance['guest_behavior'] : 'login';
        $instance['show_notifications']     = isset($new_instance['show_notifications']) ? (int) $new_instance['show_notifications'] : 0;
        $instance['show_community_links']   = isset($new_instance['show_community_links']) ? (int) $new_instance['show_community_links'] : 0;
        $instance['show_cover']             = isset($new_instance['show_cover']) ? (int) $new_instance['show_cover'] : 0;
        $instance['title']                  = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['show_in_profile']        = isset($new_instance['show_in_profile']) ? (int) $new_instance['show_in_profile'] : 3;

        return $instance;
    }
}

// EOF
