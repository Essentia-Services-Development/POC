<?php

if(!class_exists('PeepSo3_Search_Index')) {

    class PeepSo3_Search_Index {
        const TABLE = 'peepso_search_index';
        private $db_version = 10;
        private $table;

        public function __construct() {
            global $wpdb;
            $this->table = $wpdb->prefix . self::TABLE;

            $sql = "CREATE TABLE {$this->table} (
					id BIGINT(20) NOT NULL AUTO_INCREMENT,
					post_id BIGINT(20) NOT NULL,
					post_content TEXT,
					comment_content TEXT,
					last_index datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				    PRIMARY KEY (id),
				    UNIQUE KEY (post_id)
				    
				  )";

            PeepSo3_DBDelta::_(self::TABLE, $this->db_version, $sql);
        }

        public function store($post_id) {
            /*

            Build and store post_content
                If it's a simple post, just use the text
                If it's a bunch of photos, use post text + captions of every photo

            Build and store comment_content
                Get all comments and comment replies
                Including if they are comments and comment replies to multiple photos
                Concatenate it all together

            In both cases (post and photo) use both the raw content and the human readable preview
            Replace all linebreaks with spaces
            Separate chunks with two linebreaks

            DELETE old entry from the table and INSERT a new one


             */
        }
    }
}