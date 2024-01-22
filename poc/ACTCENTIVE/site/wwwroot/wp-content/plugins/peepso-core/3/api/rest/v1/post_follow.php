<?php
/**
 * @title Post Follow
 * @desc Subscribe / Unsubscribe for Post Notification  
 *
 * @methods
 * @method:GET -> read() to check if user already follows the post
 * @method:POST -> edit() to create/change the preference
 *
 * @autodoc
 */


class PeepSo3_REST_V1_Endpoint_Post_Follow extends PeepSo3_REST_V1_Endpoint {
    private $post_id;
    private $user_id;

    private $table;
    private $notif;

    public function __construct() {

        parent::__construct();

        $this->post_id = $this->input->int('post_id', 0);
        $this->user_id = get_current_user_id();

        $this->table = $this->wpdb->prefix  .'peepso_activity_followers';
        $this->notif = new PeepSo3_Activity_Notifications($this->post_id, $this->user_id);

        $this->state = array(
            'follow'    => NULL,
            'user_id'   => $this->user_id,
            'post_id'   => $this->post_id,
        );
    }

    public function read() {
        return $this->state();
    }

    public function edit($data) {
        $follow = $data['follow'];
        $this->notif->set($follow);

        return $this->state();
    }

    private function state() {
        $this->state['follow'] = intval((bool) $this->notif->is_following());
        return $this->state;
    }

    protected function can_read() {
        return is_user_logged_in();
    }

    protected function can_edit() {
        return is_user_logged_in();
    }

    protected function can_delete() {
        return is_user_logged_in();
    }
}