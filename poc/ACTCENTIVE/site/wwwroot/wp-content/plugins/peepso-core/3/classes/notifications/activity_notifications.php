<?php

class PeepSo3_Activity_Notifications
{
    private static $instance;
    private $table;
    private $post_id;
    private $user_id;
    private $follow;
    private $is_exists;


    public function __construct($post_id, $user_id)
    {
        global $wpdb;
        $this->table = $wpdb->prefix.'peepso_activity_followers';

        $this->user_id = (int) $user_id;
        $this->post_id = (int) $post_id;

        $current = $this->get_follow();
        $this->follow  = $current ? $current->follow : 0;
        $this->is_exists = $current ? TRUE: FALSE;
    }

    public function set($follow)
    {
        global $wpdb;

        $this->follow = intval((bool) $follow);

        // initiates default sql query
        $sql = "INSERT INTO {$this->table} (`post_id`,`user_id`, `follow`) VALUES ({$this->post_id}, {$this->user_id}, {$this->follow})";
        
        $current = $this->get_follow();
        if ($current) {
            $sql = "UPDATE {$this->table} SET `follow`={$this->follow} WHERE `user_id`={$this->user_id} AND `post_id`={$this->post_id}";
        }
        
        $wpdb->query($sql);
    }

    public function is_following()
    {
        return $this->follow;
    }

    public function is_exists()
    {
        return $this->is_exists;
    }

    private function get_follow()
    {
        global $wpdb;

        $sql = "SELECT * FROM {$this->table} WHERE post_id = %d AND user_id = %d";
        $ret = $wpdb->get_row($wpdb->prepare($sql, $this->post_id, $this->user_id));

        return $ret;
    }

    /**
     * Returns followers.
     * @return array
     */
    public function get_followers()
    {
        global $wpdb;

        $sql = "SELECT user_id FROM {$this->table} WHERE post_id = %d AND follow = 1";
        $query = $wpdb->prepare($sql, $this->post_id);

        $result = $wpdb->get_results($query, ARRAY_A);

        $followers = array();
        if ($wpdb->num_rows > 0) {
            foreach ($result as $follower) {
                $followers[] = $follower['user_id'];
            }
        }

        return $followers;
    }
}
