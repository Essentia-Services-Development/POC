<?php

/**
 * Class PeepSoGroups
 *
 * This class is used for getting data sets containing multiple groups, based on various conditions/filters
 *
 */

class PeepSoGroups
{
	public $user_id;
    public $search;
    public $search_mode;
	public $category;
	public $where_clauses;

	public function get_groups($offset, $limit, $order_by, $order, $search, $user_id = 0, $category = 0, $search_mode = 'exact')
	{
		global $wpdb;

		$this->user_id = $user_id;
        $this->search = $search;
        $this->search_mode = $search_mode;
		$this->category = $category;

		$args = array(
			'orderby' => $order_by,
			'order' => $order,
			'posts_per_page' => $limit,
			'offset' => $offset,
			'ignore_sticky_posts' => true
		);

		add_filter('posts_clauses_request', array(&$this, 'filter_post_clauses'), 10, 2);

        #6666 GeoMyWp hooks
        $args = apply_filters( 'peepso_filter_groups_query_args', $args );

		$post_query = new WP_Query($args);

		remove_filter('posts_clauses_request', array(&$this, 'filter_post_clauses'));

		$groups = array();

		while ($post_query->have_posts()) {
			$post_query->the_post();
			$post = $post_query->post;
			
			if($post->post_type != PeepSoGroup::POST_TYPE) { 
				new PeepSoError('[GROUPS] PeepSoGroups::get_groups() encountered a wrong post_type');
				continue; 
			}
			
			$groups[] = new PeepSoGroup($post->ID);
		}

		wp_reset_postdata();

		return $groups;
	}

	public function filter_post_clauses($clauses, $query)
	{
		global $wpdb;

		$tbl_ps_members = $wpdb->prefix.PeepSoGroupUsers::TABLE;
		$tbl_wp_posts = $wpdb->posts;
		$tbl_ps_categories = $wpdb->prefix.PeepSoGroupCategoriesGroups::TABLE;

		// Add the default groupby clause anyway, to prevent duplicate records retrieved, one instance of this behavior is showing comments with the friends add-on enabled
		$clauses['groupby'] = "`$tbl_wp_posts`.`ID`";

		$clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} gp ON {$wpdb->posts}.ID = gp.post_id AND gp.meta_key = 'peepso_group_privacy' ";

		// If looking at a specific user's listing we need only groups he is a member of
		if ($this->user_id !== 0) {
			$clauses['join'] .= "  JOIN `$tbl_ps_members` as `gm` ON " .
			" `$tbl_wp_posts`.`ID` = `gm`.`gm_group_id` " .
			" AND `gm`.`gm_user_status` LIKE 'member%' " .
			" AND  `gm`.`gm_user_id` = '{$this->user_id}' ";
		}

		// if looking at specific category
		$clauses['join'] .= " LEFT JOIN `$tbl_ps_categories` as `gc` ON " .
			" `$tbl_wp_posts`.`ID` = `gc`.`gm_group_id` ";
		
		// Set post_type
		$where = " AND `$tbl_wp_posts`.`post_type`='".PeepSoGroup::POST_TYPE."'";

		// Search
		if(!empty($this->search)) {
		    $this->search = trim($this->search);
		    $this->search = str_replace('%', '\%', $this->search);
		    if($this->search_mode == 'any') {

		        // multi-word search
                if(strstr($this->search, ' ')) {
                    $search = explode(' ', $this->search);
                } else {
                    $search = [$this->search];
                }

                $where_words = '';
                foreach($search as $s) {
                    $s="%$s%";
                    $where_words .= "OR( `$tbl_wp_posts`.`post_title` LIKE '$s' OR `$tbl_wp_posts`.`post_content` LIKE '$s') ";
                }

                $where .= " AND ( " . trim($where_words,'OR') . " ) ";
            } else {
		        // exact phrase search
                $s = "%{$this->search}%";
                $where .= " AND ( `$tbl_wp_posts`.`post_title` LIKE '$s' OR `$tbl_wp_posts`.`post_content` LIKE '$s') ";
            }
		}
		
		// Handle unpublished groups
		if(!PeepSo::is_admin()) {
			$uid = get_current_user_id();
			if ($uid == 0) {
				$where .= 	" AND (`$tbl_wp_posts`.`post_status` = 'publish' AND gp.meta_value != '".PeepSoGroupPrivacy::PRIVACY_SECRET."')";
			} else {
				$where .= 	" AND (`$tbl_wp_posts`.`post_status` = 'publish' AND (gp.meta_value != '".PeepSoGroupPrivacy::PRIVACY_SECRET."' OR (gp.meta_value = '".PeepSoGroupPrivacy::PRIVACY_SECRET."' AND EXISTS (SELECT `gm_id` FROM `$tbl_ps_members` WHERE `$tbl_ps_members`.`gm_user_id`=$uid AND `$tbl_ps_members`.`gm_group_id`=`$tbl_wp_posts`.`ID` AND `$tbl_ps_members`.`gm_user_status` IN ('member', 'pending_user', 'member_moderator', 'member_owner','member_manager')))))";
			}
		} else {
			$where .= 	" AND `$tbl_wp_posts`.`post_status` = 'publish'";
		}

		// handle filter group by category
		// display uncategories
		if($this->category == -1) {
			$where .= " AND `gc`.`gm_group_id` IS NULL ";
		} elseif($this->category > 0) {
			$where .= " AND `gc`.`gm_cat_id` = " . $this->category . " ";
		}

		if ($query->query['orderby'] == 'meta_members_count') {
			$clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} group_count ON {$wpdb->posts}.ID = group_count.post_id AND group_count.meta_key = 'peepso_group_members_count' ";

			$clauses['orderby'] = ' CAST(group_count.meta_value AS UNSIGNED) ' . $query->query['order'];
		}

		// Override the where clause completely
		$clauses['where'] = $where . $this->where_clauses;

		return $clauses;
	}

	public static function admin_count_groups($search='', $show='all')
	{
		global $wpdb;

		$where = " WHERE `{$wpdb->posts}`.`post_type`='peepso-group' ";

		if(!empty($search)) {
			$where .= " AND `{$wpdb->posts}`.`post_title` LIKE '%" . $search . "%'";
		}

		if($show !== 'all') {
			$where .= " AND `{$wpdb->posts}`.`post_status` = '" . $show . "'";
		}

		$rowcount = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} " . $where);

    	return $rowcount;
	}

	public static function admin_get_groups($offset = 0, $limit = 10, $orderby = 'post_title', $sort = 'desc', $search='', $show='all')
	{
		global $wpdb;

		$orderby = ($orderby === NULL) ? 'post_title' : $orderby;
		$sort = ($sort === NULL) ? 'desc' : $sort;

		$clauses=array('join'=>'', 'where'=> []);

		$clauses['join'] .=
			" LEFT JOIN `{$wpdb->posts}` ON `gm_group_id`=`{$wpdb->posts}`.`ID` ";

		$clauses['where'][] = "`gm_user_status` LIKE 'member%'";

		#$clauses['where'][]="(`{$wpdb->posts}`.`post_status`='publish' OR `{$wpdb->posts}`.`post_status`='pending')";

		$clauses['where'][] = "`{$wpdb->posts}`.`post_type`='peepso-group'";

		if(!empty($search)) {
			$clauses['where'][] = "`{$wpdb->posts}`.`post_title` LIKE '%" . $search . "%'";
		}

		if($show !== 'all') {
			$clauses['where'][] = "`{$wpdb->posts}`.`post_status` = '" . $show . "'";
		}

		$sql = "SELECT DISTINCT `a`.`gm_group_id`, (SELECT COUNT(*) FROM `{$wpdb->prefix}" . PeepSoGroupUsers::TABLE . "` WHERE  gm_group_id = a.gm_group_id) as members_count FROM `{$wpdb->prefix}" . PeepSoGroupUsers::TABLE . "` a";

		$sql .= $clauses['join'];

		$sql .= ' WHERE ' . implode(' AND ', $clauses['where']);

		$sql .= " ORDER BY `{$orderby}` {$sort}";

		if ($limit) {
			$sql .= " LIMIT {$offset}, {$limit}";
		}

		$group_ids = $wpdb->get_results($sql, ARRAY_A);

		$groups = array();
		if(count($group_ids)) {
			foreach ($group_ids as $group_id) {
				$groups[] = new PeepSoGroup($group_id['gm_group_id']);
			}
		}

		return $groups;
	}
}

// EOF