<?php

class PeepSoGroupFollower {

    public $user_id;
    public $group_id;

    public $user		 = NULL;
    public $group		 = NULL;

    public $is_follower  = 0;

    public $follow       = 1;
    public $notify       = 1;
    public $email       = 1;

    private $_table;

    public function __construct($group_id, $user_id = NULL, $group_instance = NULL)
    {
        global $wpdb;
        $this->_table = $wpdb->prefix.PeepSoGroupFollowers::TABLE;

        // default to logged in user
        if( NULL === $user_id ) {
            $user_id = get_current_user_id();
        }

        $this->group_id = intval($group_id);
        $this->user_id  = intval($user_id);

        if( NULL !== $group_instance) {
            $this->group = $group_instance;
        }

        if( $this->group_id > 0) {
            $this->_init();
        }
    }

    /**
     * Set class flags based on the database values
     */
    public function _init($create = FALSE)
    {
        // Reset all flags
        $this->is_follower  = 0;
        $this->follow       = 0;
        $this->notify		= 0;
        $this->email        = 0;

        // Calculate flags based on the database state
        global $wpdb;

        $query = "SELECT * FROM $this->_table WHERE `gf_group_id`=%d AND `gf_user_id`=%d LIMIT 1";
        $query = $wpdb->prepare($query, array($this->group_id, $this->user_id));

        $follower = $wpdb->get_row($query);

        if (NULL !== $follower) {

            $this->is_follower = 1;

            $this->follow       = $follower->gf_follow;
            $this->notify		= $follower->gf_notify;
            $this->email        = $follower->gf_email;

            $this->save();

        } else {
            $PeepSoGroupUser = new PeepSoGroupUser($this->group_id, $this->user_id);
            if($PeepSoGroupUser->is_member) {

                $notify = PeepSo::get_option('groups_notify_default', 1);
                $email = PeepSo::get_option('groups_notify_email_default', 1);

                $query = "INSERT INTO $this->_table (`gf_group_id`, `gf_user_id`,`gf_notify`,`gf_email`) VALUES (%d, %d, %d, %d)";
                $wpdb->query($wpdb->prepare($query, $this->group_id, $this->user_id, $notify, $email));

                $this->is_follower  = 1;
                $this->follow       = 1;
                $this->notify		= $notify;
                $this->email        = $email;
            }
        }
    }


    /**
     * Get a property or use a getter
     * @param $prop
     * @return mixed
     */
    public function get($prop)
    {
        if(property_exists($this, $prop)) {
            return $this->$prop;
        }

        $method = "get_$prop";
        if(method_exists($this, $method)) {
            return $this->$method();
        }

        trigger_error("Unknown property/method $prop/$method");
    }



    public function get_follower_actions() {

        if(!$this->is_follower) {
            return NULL;
        }

        $actions = array();

        $icon_off = 'ps-icon-check-empty';
        $icon_on = 'ps-icon-check';


        // Following
        $follow_set = 1;
        $follow_icon = $icon_off;

        // Notifications
        $notify_set = 1;
        $notify_icon = $icon_off;


        // Emails
        $email_set = 1;
        $email_icon = $icon_off;


        // Build label and set flags for AJAX calls

        if($this->follow) {
            $follow_icon = $icon_on;
            $follow_set = 0;
        }

        if($this->notify) {
            $notify_icon = $icon_on;
            $notify_set = 0;
        }

        if($this->email) {
            $email_icon = $icon_on;
            $email_set = 0;
        }


        $style= "opacity:50%";

        $aria_label = [];

        $icon_follow_on = '<i class="gcis gci-square-check"></i>';
        $icon_follow_off = '<i class="gcir gci-square-check" style="'.$style.'"></i>';
        $icon_follow_off = '<i class="gcir gci-square-check" style="'.$style.'"></i>';

        $icon_notif_on = '<i class="gcis gci-bell"></i>';
        $icon_notif_off = '<i class="gcir gci-bell" style="'.$style.'"></i>';
        $icon_notif_off = '<i class="gcir gci-bell-slash" style="'.$style.'"></i>';

        $icon_email_on = '<i class="gcis gci-envelope"></i>';
        $icon_email_off = '<i class="gcir gci-envelope" style="'.$style.'"></i>';

        $aria_label = $label = [];

        if($this->follow) {
            $label[] = $icon_follow_on;
            $aria_label[] = __('Following', 'groupso');
        } else {
            $label[]=$icon_follow_off;
            $aria_label[] = __('Not following', 'groupso');
        }

        if($this->notify || $this->email) {
            if($this->email) {
                $label[]=$icon_email_on;
            } else {
                $label[]=$icon_notif_on;
            }

            $aria_label[] = __('Receiving notifications', 'groupso');

        } else {
            $label[]=$icon_notif_off;
            $aria_label[] = __('Not receiving notifications', 'groupso');
        }


        $label = implode(' ', $label);
        $aria_label = implode(' & ', $aria_label);

        $child_actions = array(
            
            0 => array(
                'action'=> 'set',
                'label' => __('Follow', 'groupso'),
                'icon'  => $follow_icon,
                'args'  => array('prop'=>'follow','value'=>$follow_set),
                'desc'  => __('Show posts from this group in "my following" stream', 'groupso'),
            ),
            1 => array(
                'action'=> 'set',
                'label' => __('Be notified', 'groupso'),
                'icon'  => $notify_icon,
                'args'  => array('prop'=>'notify','value'=>$notify_set),
                'desc'  => __('Be notified about new posts in this group', 'groupso'),
            ),
            2 => array(
                'action'=> 'set',
                'label' => __('Receive emails', 'groupso'),
                'icon'  => $email_icon,
                'args'  => array('prop'=>'email','value'=>$email_set),
                'desc'  => __('Receive emails about new posts in this group', 'groupso'),
            ),
        );


        $actions[] = array(
            'action' 		=> $child_actions,
            'label'			=> $label,
            'aria-label'    => $aria_label,
        );

        return $actions;
    }


    public function set($prop, $value) {
        if(!$this->is_follower || !property_exists($this, $prop)) {
            return NULL;
        }

        $this->$prop = $value;

        return $this->save();
    }

    public function save() {
        if(!$this->is_follower) {
            return NULL;
        }

        global $wpdb;
        return $wpdb->update($this->_table, array( 'gf_follow'=>$this->follow, 'gf_notify'=>$this->notify,'gf_email'=>$this->email), array('gf_group_id'=>$this->group_id, 'gf_user_id'=>$this->user_id) );
    }

    public function delete() {
        global $wpdb;
        return $wpdb->query($wpdb->prepare("DELETE FROM $this->_table WHERE `gf_group_id`=%d AND `gf_user_id`=%d", $this->group_id, $this->user_id));
    }

}
