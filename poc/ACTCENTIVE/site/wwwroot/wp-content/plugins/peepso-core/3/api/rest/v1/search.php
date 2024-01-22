<?php

class PeepSo3_REST_V1_Endpoint_Search extends PeepSo3_REST_V1_Endpoint {

    protected $query;
    protected $section = NULL;
    private $user_id = 0;

    protected $results = array();

    public function __construct() {

        parent::__construct();

        $this->query = stripslashes_deep($this->input->value('query', '', FALSE)); // SQL Safe
        $this->section = stripslashes_deep($this->input->value('section', NULL, FALSE)); // SQL Safe
        $this->user_id = get_current_user_id();

        $PeepSo3_Input = new PeepSo3_Input();

        $this->results['meta'] = array(
            'query' => $this->query,
            'section' => $this->section,
            'user_id' => $this->user_id,
            'timestamp' => date('Y-m-d H:i:s'),
//            'config' => [
//                'items_per_section' => $PeepSo3_Input->int('items_per_section', PeepSo::get_option_new('peepso_search_limit_items_per_section')),
//                'max_length_title' => $PeepSo3_Input->int('items_per_section', PeepSo::get_option_new('peepso_search_limit_length_title')),
//                'max_length_text' => $PeepSo3_Input->int('items_per_section', PeepSo::get_option_new('peepso_search_limit_length_text')),
//            ],
        );
    }

    public function read($data) {
        remove_all_actions('peepso_action_before_posts_per_page');
        $this->results = apply_filters('peepso_search_results', $this->results);
        (new PeepSo3_Search_Analytics())->store($this->query,'search');
        return $this->results;
    }

    protected function can_read() {
        return TRUE;(bool) get_current_user_id();
    }

    protected function can_create() {
        return FALSE;
    }

    protected function can_delete() {
        return FALSE;
    }

}