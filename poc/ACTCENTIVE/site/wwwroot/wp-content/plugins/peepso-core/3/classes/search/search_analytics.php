<?php

class PeepSo3_Search_Analytics {
    const TABLE = 'peepso_search_ranking';
    const AUX_TABLE = 'peepso_search_ranking_totals';
    private $db_version = 2;
    private $table;
    private $aux_table;

    public function __construct() {

        @include_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        if(!function_exists('dbDelta')) {
            new PeepSoError("dbDelta() not found");
            return;
        }

        // Run dbDelta() once in a while no matter what
        $override = TRUE;(rand(1,100) == 1) ? TRUE : FALSE;

        global $wpdb;
        $this->table = $wpdb->prefix . self::TABLE;
        $this->aux_table = $wpdb->prefix . self::AUX_TABLE;

        $version = PeepSo::PLUGIN_VERSION.PeepSo::PLUGIN_RELEASE.'-'.$this->db_version;
        $charset_collate = $wpdb->get_charset_collate();

        // DB table: peepso_search_ranking
        if(get_option(self::TABLE) != $version || $override) {

            $sql = "CREATE TABLE {$this->table} (
					id BIGINT(20) NOT NULL AUTO_INCREMENT,
					user_id BIGINT(20) NOT NULL,
					search VARCHAR(256),	
					class VARCHAR(64),
					date datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
				    PRIMARY KEY (id)
				  ) ENGINE=InnoDB $charset_collate;";

            dbDelta($sql);

            $sql = "CREATE TABLE {$this->aux_table} (
					id BIGINT(20) NOT NULL AUTO_INCREMENT,
					search VARCHAR(256),	
					class VARCHAR(64),
					count BIGINT(20) UNSIGNED DEFAULT '0',
					PRIMARY KEY (id)
				  ) ENGINE=InnoDB $charset_collate;";

            dbDelta($sql);

            update_option(self::TABLE, $version);
        }
    }

    public function store($search, $class) {
        $user_id = (int) get_current_user_id();
        global $wpdb;

        if(!empty($search)) {
            $wpdb->insert($this->table, ['user_id' => $user_id, 'search' => $search, 'class'=>$class]);
            $this->update_total($search, $class);
        }
    }

    private function update_total($search, $class) {
        global $wpdb;

        // Insert the search-class pair if it's missing
        $sql = "SELECT id FROM {$this->aux_table} WHERE `search`='$search' AND `class`='$class'";
        $res = $wpdb->get_results($sql, ARRAY_A);

        if(!is_array($res) || !count($res)) {
            $sql = "INSERT IGNORE INTO {$this->aux_table} SET `search`='$search', `class`='$class'";
            $wpdb->query($sql);
        }

        // Update total
        $total = 0;
        $sql = "SELECT count(id) as total FROM {$this->table} WHERE search='$search' AND class='$class'";
        $res = $wpdb->get_results($sql, ARRAY_A);

        if(is_array($res) && count($res)) {
            $total = max(0, intval($res[0]['total']));
        }

        $sql = "UPDATE {$this->aux_table} SET `count`='$total' WHERE `search`='$search' AND `class`='$class'";
        $wpdb->query($sql);
    }

}