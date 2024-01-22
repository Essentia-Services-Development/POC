<?php
/**
 * @title Saved Posts
 * @desc Create / modify / retrieve Saved status for  Post - User paiid
 *
 * @methods
 * @method:GET -> read() to read Saved status for Post - User pad
 * @method:POST -> edit() to set users RSVP status [no,yes,maybe]
 * @method:DELETE
 *
 * @autodoc
 */

class PeepSo3_REST_V1_Endpoint_Post_Save extends PeepSo3_REST_V1_Endpoint {

    private $post_id;
    private $user_id;

    private $table;

    public function __construct() {

        parent::__construct();

        $this->post_id = $this->input->int('post_id', 0);
        $this->user_id = get_current_user_id();

        $this->table = $this->wpdb->prefix.'peepso_saved_posts';

        $this->state = array(
            'saved'     => NULL,
            'id'        => NULL,
            'user_id'   => $this->user_id,
            'post_id'   => $this->post_id,
        );
    }

    public function read($data) {
        return $this->state();
    }

    public function create($data) {
        $this->wpdb->insert($this->table, array('user_id' => $this->user_id, 'post_id' => $this->post_id));
        $this->state['id'] = $this->wpdb->insert_id;

        if (PeepSo::get_option_new('post_auto_follow_save')) {
            $PeepSoActivity = PeepSoActivity::get_instance();
            $post = $PeepSoActivity->get_activity_post($this->post_id);
            $act_notif = new PeepSo3_Activity_Notifications($post->ID, $this->user_id);
            $act_notif->set(1);
        }

        return $this->state();
    }

    public function delete($data) {

        $id = $data['id'];

        $sql = "SELECT `post_id` FROM {$this->table} WHERE `id` = '$id' LIMIT 1";
        $this->state['post_id'] = $this->wpdb->get_var($sql);

        $this->wpdb->delete($this->table, array('id' => $id));
        $this->state['id'] = NULL;

        return $this->state();
    }

    private function state() {

        if(!$this->state['id']) {
            $sql = "SELECT `id` FROM {$this->table} WHERE `user_id` = '{$this->user_id}' AND `post_id` = '{$this->post_id}' LIMIT 1";

            $this->state['id'] = $this->wpdb->get_var($sql);
        }

        $this->state['saved'] = (is_null($this->state['id'])) ? FALSE : TRUE;

        return $this->state;
    }

    protected function can_read() {
        return is_user_logged_in();
    }

    protected function can_create() {
        return is_user_logged_in();
    }

    protected function can_delete() {
        return is_user_logged_in();
    }

}