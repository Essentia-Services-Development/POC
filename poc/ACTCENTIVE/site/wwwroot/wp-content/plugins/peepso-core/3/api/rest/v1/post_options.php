<?php
/**
 * @title Post Options
 * @desc Retrieve available Post Options for the User
 *
 * @methods
 * @method:GET -> read() to read post Options status for Post - User pair
 *
 * @autodoc
 */

class PeepSo3_REST_V1_Endpoint_Post_Options extends PeepSo3_REST_V1_Endpoint {

    public $state;

    public function __construct() {
        parent::__construct();
    }

    private function load() {
        global $post;

        $this->state = [
            'post_id'               => $this->input->int('post_id', 0),
            'options'               => [],
        ];

        // #6188 - Backup the original post, since the $PeepSoActivity->get_post_object() function apparently
        // sets the current post that is different with the one in the global scope.
        $post_backup = $post;

        $PeepSoActivity = PeepSoActivity::get_instance();
        if($post = $PeepSoActivity->get_post_object($this->state['post_id'])) {
            $this->state['options'] = $PeepSoActivity->post_options($post);
        }

        $post = $post_backup;
    }

    public function read() {
        $this->load();
        return $this->state;
    }

    protected function can_read() {
        return TRUE;//is_user_logged_in();
    }

    protected function can_create() {
        return FALSE;
    }

    protected function can_delete() {
        return FALSE();
    }

}
