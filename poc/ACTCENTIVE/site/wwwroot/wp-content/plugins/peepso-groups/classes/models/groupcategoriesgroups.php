<?php
/**
 * Class PeepSoGroupCategoriesGroups
 *
 * Manage/list all possible relationships between GroupCategory and Group
 *
 * Add 		Group 				to 		GroupCategory	(1 to 1)
 * Remove 	Group 				from	GroupCategory	(1 to 1)
 * Get 		GroupCategories 	for 	Group			(1 to many)
 * Get 		Groups				for 	GroupCategory	(1 to many)
 */
class PeepSoGroupCategoriesGroups
{
    const TABLE = 'peepso_group_categories';

    /**
     * Add a Group to one or more GroupCategory
     *
     * @param $group_id int
     * @param $cat_id array|int
     * @return bool
     */
    public static function add_group_to_categories($group_id, $cat_ids)
    {
        global $wpdb;

        // remove all categories
        self::remove_group_from_category($group_id, 0, TRUE);


        // $cat_id can be an array or an int
        if (is_int($cat_ids)) {
            $cat_ids = array($cat_ids);
        }

        // loop through categories and add
        foreach ($cat_ids as $cat_id) {
            $data = array(
                'gm_cat_id' => $cat_id,
                'gm_group_id' => $group_id
            );

            $wpdb->insert($wpdb->prefix . self::TABLE, $data);

            self::update_stats_for_category($cat_id);
        }

        return TRUE;
    }

    public static function remove_group_from_category($group_id, $cat_id, $recount = TRUE)
    {
        global $wpdb;
        $where = array(
            'gm_cat_id' => $cat_id,
            'gm_group_id' => $group_id,
        );

        // removing group from all categories
        if(0 == $cat_id) {
            unset($where['gm_cat_id']);
        }

        $wpdb->delete($wpdb->prefix . self::TABLE, $where);

        return TRUE;
    }

    public static function get_categories_id_for_group($group_id)
    {
        global $wpdb;

        $query = "SELECT `gm_cat_id` as `cat_id` FROM {$wpdb->prefix}" . self::TABLE . " WHERE `gm_group_id`=%d";
        $query = $wpdb->prepare($query, $group_id);
        $cat_ids = $wpdb->get_results($query);

        $resp = array();

        foreach ($cat_ids as $cat_id) {
            $resp[] = (int)$cat_id->cat_id;

        }
        return $resp;
    }

    public static function get_categories_for_group($group_id)
    {
        $cat_ids = self::get_categories_id_for_group($group_id);

        $resp = array();

        if (count($cat_ids)) {
            foreach ($cat_ids as $cat_id) {
                $resp[$cat_id] = new PeepSoGroupCategory($cat_id);
            }
        } else {
            $resp["-1"] = new PeepSoGroupCategory(-1);
        }

        return $resp;
    }

    // utilities - used to track group count in categories

    /**
     * Update stats of all categories for a given group
     *
     * @param $group_id
     * @return void
     */
    public static function update_stats_for_group($group_id)
    {
        $cat_ids = self::get_categories_id_for_group($group_id);

        if (count($cat_ids)) {
            foreach ($cat_ids as $cat_id) {
                self::update_stats_for_category($cat_id);
            }
        }
    }

    /**
     * Update stats of a given category
     *
     * @param $cat_id
     * @return int
     */
	public static  function update_stats_for_category($cat_id = 0) {
        global $wpdb;
        
        if ($cat_id > 0) {
            $query = "SELECT `gm_group_id` as `group_id` FROM {$wpdb->prefix}".self::TABLE." WHERE `gm_cat_id`=%d";
            $query = $wpdb->prepare($query, $cat_id);
            $group_ids= $wpdb->get_results($query);
    
            $count = count($group_ids);
    
            $PeepSoGroupCategory = new PeepSoGroupCategory($cat_id);
            $PeepSoGroupCategory->update(array('groups_count'=>$count));
        } else {
            $query = "SELECT count(*) AS `uncategorized_count` FROM {$wpdb->posts} WHERE post_type = 'peepso-group' AND ID NOT IN (SELECT gm_group_id FROM {$wpdb->prefix}" . self::TABLE . ")";
            $result = $wpdb->get_row($query);
            $count = $result->uncategorized_count;
        }

        return $count;
    }

    public static function get_group_ids_for_category($cat_id) {
	    $response = array();

	    global $wpdb;
        $query = "SELECT `gm_group_id` as `group_id` FROM {$wpdb->prefix}".self::TABLE." WHERE `gm_cat_id`=%d";
        $query = $wpdb->prepare($query, $cat_id);
        $group_ids= $wpdb->get_results($query);

        if(count($group_ids)) {
            foreach($group_ids as $group_id) {
                $response[]=$group_id->group_id;
            }
        }
        return $response;
    }
}