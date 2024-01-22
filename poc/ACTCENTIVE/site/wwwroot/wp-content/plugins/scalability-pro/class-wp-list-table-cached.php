<?php
define('SPRO_ROW_CACHE_KEY', 'sprc6');

 
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
if ( ! class_exists( 'WP_Posts_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-posts-list-table.php' );
}


class WP_List_Table_Cached extends WP_Posts_List_Table {

    // override the single_row function to make it cached in the object cache
    public $iscachable;

    function __construct($parentObj) {
		global $avail_post_stati, $wp_query, $per_page, $mode;
        $objValues = get_object_vars($parentObj); // return array of object values
        foreach($objValues AS $key=>$value)
        {
             $this->$key = $value;
             error_log($key . " -> " . print_r($value, true));
        }
        $this->iscachable = true;

		if ( $this->hierarchical_display ) {
			$total_items = $wp_query->post_count;
		} elseif ( $wp_query->found_posts || $this->get_pagenum() === 1 ) {
			$total_items = $wp_query->found_posts;
		} else {
			$post_counts = (array) wp_count_posts( $post_type, 'readable' );

			if ( isset( $_REQUEST['post_status'] ) && in_array( $_REQUEST['post_status'] , $avail_post_stati ) ) {
				$total_items = $post_counts[ $_REQUEST['post_status'] ];
			} elseif ( isset( $_REQUEST['show_sticky'] ) && $_REQUEST['show_sticky'] ) {
				$total_items = $this->sticky_posts_count;
			} elseif ( isset( $_GET['author'] ) && $_GET['author'] == get_current_user_id() ) {
				$total_items = $this->user_posts_count;
			} else {
				$total_items = array_sum( $post_counts );

				// Subtract post types that are not included in the admin all list.
				foreach ( get_post_stati( array( 'show_in_admin_all_list' => false ) ) as $state ) {
					$total_items -= $post_counts[ $state ];
				}
			}
		}

		if ( ! empty( $_REQUEST['mode'] ) ) {
			$mode = $_REQUEST['mode'] === 'excerpt' ? 'excerpt' : 'list';
			set_user_setting( 'posts_list_mode', $mode );
		} else {
			$mode = get_user_setting( 'posts_list_mode', 'list' );
		}

		$this->is_trash = isset( $_REQUEST['post_status'] ) && $_REQUEST['post_status'] === 'trash';

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page' => $per_page
        ) );
 
    }

    public function single_row( $post, $level = 0 ) {
        $cachedrow = sppro_get_cache($post->ID, get_current_user_id(), $level, 'wpadmin');
        if (empty($cachedrow)) {
            ob_start();

            $global_post = get_post();
        
            $post                = get_post( $post );
            $this->current_level = $level;

            $GLOBALS['post'] = $post;
            setup_postdata( $post );

            $classes = 'iedit author-' . ( get_current_user_id() == $post->post_author ? 'self' : 'other' );

            $lock_holder = wp_check_post_lock( $post->ID );
            if ( $lock_holder ) {
                $classes .= ' wp-locked';
            }

            if ( $post->post_parent ) {
                $count    = count( get_post_ancestors( $post->ID ) );
                $classes .= ' level-' . $count;
            } else {
                $classes .= ' level-0';
            }
            ?>
            <tr id="post-<?php echo $post->ID; ?>" class="<?php echo implode( ' ', get_post_class( $classes, $post->ID ) ); ?>">

            <?php
            $this->single_row_columns( $post );
            $GLOBALS['post'] = $global_post;
            $cachedrow = ob_get_clean();
            echo '</tr>';
            sppro_set_cache($post->ID, get_current_user_id(), $level, 'wpadmin', $cachedrow);
        }    
        echo $cachedrow;
    }
}

//add_filter('edit_shop_order_per_page', 'changeListTableToCached');
//add_filter('edit_product_per_page', 'changeListTableToCached');

function changeListTableToCached($perpage) {
    global $wp_list_table;
    global $avail_post_stati, $wp_query, $per_page, $mode;

//    echo "<pre>" . print_r($wp_query, true) . "</pre>";


    if (!property_exists($wp_list_table, 'iscachable') && $wp_query->found_posts > 5) {
        $cached_wp_list_table = new WP_List_Table_Cached($wp_list_table);
        $wp_list_table = $cached_wp_list_table;
    }
    return $perpage;
}

//add_action('post_updated', 'spro_clearadminrowcache', 10, 3);
function spro_clearadminrowcache($postid, $post_after, $post_before) {
    sppro_delete_cache($postid);
}

/*
        $pagination = array('total_items' => $parentObj->get_pagination_arg('total_items'), 'per_page' => $parentObj->get_pagination_arg('per_page'));

        error_log(print_r($pagination, true));
        $this->set_pagination_args($pagination);

*/