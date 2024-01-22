<?php

if(!class_exists('PeepSo3_Search_Adapter')) {
    require_once(dirname(__FILE__) . '/search_adapter.php');
    //new PeepSoError('Autoload issue: PeepSo3_Search_Adapter not found ' . __FILE__);
}

class PeepSo3_Search_Adapter_Hashtags extends PeepSo3_Search_Adapter {

    private $results;
    public $ids;

    public function __construct()
    {
        $this->section = 'hashtags';
        $this->title = __('Hashtags', 'peepso-core');
        $this->url = PeepSo::get_page('activity').'?filter=';

        $this->results=[];
        $this->ids=[];

        parent::__construct();
    }

    public function results() {

        // @TODO Hashtags model?

        // Full phrase EXACT
        $this->add_results(TRUE, TRUE);


        // Full phrase LIKE
        $this->add_results(FALSE, TRUE);

        // Word-by word
        if(strstr($this->query,' ')) {

            $searches = explode(' ', $this->query);

            // EXACT
            foreach($searches as $search) {
                $this->add_results(TRUE, FALSE, $search);
            }

            // LIKE
            foreach($searches as $search) {
                $this->add_results(FALSE, FALSE, $search);
            }
        }


        return $this->results;
    }

    private function add_results($exact, $full_phrase, $search = NULL) {

        $accuracy = 1;
        if($exact) { $accuracy += 2;}
        if($full_phrase) { $accuracy += 3;}
        if($full_phrase && $exact) { $accuracy += 4;}


        if(count($this->results) >= $this->config['items_per_section']) {
            return;
        }

        global $wpdb;

        if(!$search) {
            $search = $this->query;
        }

        $search = str_replace(' ' ,'-', $search);

        if($exact) {
            $query = "SELECT * FROM {$wpdb->prefix}peepso_hashtags WHERE ht_name='$search'";
        } else {
            $query = "SELECT * FROM {$wpdb->prefix}peepso_hashtags WHERE ht_name LIKE '%$search%'";
        }

        if(count($this->ids)) {
            $query .= " AND ht_id NOT IN (".implode(',',$this->ids).")";
        }


        $hashtags = $wpdb->get_results($query);

        if ( count($hashtags) ) {
            foreach($hashtags as $hashtag) {


                if(count($this->results) >= $this->config['items_per_section']) {
                    return;
                }

                $this->ids[]=$hashtag->ht_id;

                $this->results[] = $this->map_item(
                    [
                        'id' => $hashtag->ht_id,
                        'title' => '#'.$hashtag->ht_name,
                        'meta' => [
                            [
                                'icon' => 'gcis gci-edit',
                                'title' => sprintf( _n('%d post','%d posts', $hashtag->ht_count,'peepso-core'), $hashtag->ht_count),
                            ]
                        ],
                        'extras' => [
                            'accuracy'=> $accuracy,
                        ],
                    ]
                );
            }
        }

    }

    public function map_item($item)
    {
        $item = parent::map_item($item);
        //unset($item['text']);
        unset($item['image']);

        return $item;
    }

}

new PeepSo3_Search_Adapter_Hashtags();