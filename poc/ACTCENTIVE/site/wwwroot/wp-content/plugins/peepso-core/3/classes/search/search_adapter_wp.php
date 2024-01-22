<?php

if(!class_exists('PeepSo3_Search_Adapter')) {
    require_once(dirname(__FILE__) . '/search_adapter.php');
    //new PeepSoError('Autoload issue: PeepSo3_Search_Adapter not found ' . __FILE__);
}

class PeepSo3_Search_Adapter_WP extends PeepSo3_Search_Adapter {

    public $post_type = '';
    public $results = [];

    public function __construct($post_type, $section_title, $section_url = NULL)
    {
        $this->post_type = $post_type;

        $this->title = $section_title;
        $this->url = $section_url;

        if(NULL == $this->url) {
            $this->url = '/?s=';
        }

        $this->section ='wp_'.$this->post_type;

        parent::__construct();
    }

    public function results() {

        $args=[
            's' => $this->query,
            'post_type' => $this->post_type,
            'posts_per_page' => $this->config['items_per_section'],
            'orderby' => 'date',
            'order' => 'desc',
        ];

        $the_query = new WP_Query($args);

        if ($the_query->have_posts()) {

            while ($the_query->have_posts()) {
                $the_query->the_post();

                $this->results[] = $this->map_item([
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'text' => get_the_excerpt(),
                    'image' => get_the_post_thumbnail_url(),
                    'url' => get_permalink(),
                ]);
            }
        }

        wp_reset_postdata();

        return $this->results;
    }

}