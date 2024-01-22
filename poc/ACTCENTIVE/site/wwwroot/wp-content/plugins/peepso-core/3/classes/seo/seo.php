<?php
if(PeepSo::is_dev_mode('seo')) {

    /**
     * Class PeepSo3_Sitemap_Provider
     */
    class PeepSo3_Sitemap_Provider extends WP_Sitemaps_Provider {


        private $limit = 10; // @TODO CONFIGURABLE

        public function __construct() {
            $this->name        = 'communityposts';
            $this->object_type = 'communityposts';

            if(isset($_GET['peepso_sitemap_debug'])) {
                $page_num = isset($_GET['page_num']) ? $_GET['page_num'] : 1;

                $pages = $this->get_max_num_pages();

                echo "Pages: ";

                if($pages>0) {
                    for($i=1;$i<=$pages;$i++) {
                        echo " &nbsp; <a href=/?debug_sitemap&page_num=$i>$i</a> ";
                    }
                } else {
                    echo "0";
                }
                echo "<br/><br/>";

                echo "Page $page_num:<br/><br/>";

                $list = $this->get_url_list($page_num);
                if(count($list)) {
                    foreach($list as $url) {
                        echo "<a href={$url['loc']} target=_blank>{$url['loc']}</a><br/><br/>";
                    }
                } else {
                    echo "0 results";
                }



                die();
            }
        }

        private function sql($page_num) {

            $sql_order_limit = '';
            $sql_select = " p.post_title ";

            // counting only
            if(-1 == $page_num) {
                $sql_select = " count(p.ID) as count_posts ";
            } else {
                $offset = ($page_num-1) * $this->limit;

                $sql_order_limit = " ORDER BY p.ID DESC limit $offset,{$this->limit} ";
            }

            global $wpdb;

            $module_ids = [
                1, // core
                4, // photos
                5, // media
                30, // polls
            ];

            $join = '';
            $clauses = '';
            // handle groups
            if (class_exists('PeepSoGroupsPlugin')) {
                if (PeepSo::get_option('groups_allow_guest_access_to_groups_listing')) {
                    array_push($module_ids, 8); // groups

                    $join .= "LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'peepso_group_id' " .  
                        "LEFT JOIN {$wpdb->postmeta} pm2 on pm.meta_value = pm2.post_id AND pm2.meta_key = 'peepso_group_privacy'";
                    $clauses .= "AND (pm.meta_value is null OR (pm.meta_value IS NOT NULL AND pm2.meta_value = 0)) ";
                }
            }

            $module_ids = implode(',', $module_ids);
            $sql = "SELECT $sql_select
                FROM {$wpdb->posts} p 
                LEFT JOIN {$wpdb->prefix}peepso_activities a 
                    ON p.ID=a.`act_external_id` 
                $join
                WHERE p.post_type='peepso-post' AND p.post_status='publish' 
                    AND (a.`act_module_id` IN ($module_ids) AND a.`act_access`='".PeepSo::ACCESS_PUBLIC."') 
                $clauses
                $sql_order_limit ";

            return $wpdb->get_results($sql);
        }

        public function get_url_list( $page_num, $object_subtype = '' ) {

            $url_list = [];
            $url_base = PeepSo::get_page('activity_status_seo');

            $posts = $this->sql($page_num);

            foreach($posts as $post)  {
                $url_list[] = ['loc' => $url_base.$post->post_title];
            }

            return $url_list;
        }

        public function get_max_num_pages( $object_subtype = '' ) {
            $posts = $this->sql(-1);
            return ceil($posts[0]->count_posts/$this->limit);
        }
    }

    /**
     * Class PeepSo3_SEO
     */
    class PeepSo3_SEO {
        private static $instance;

        public static function get_instance() {
            return isset(self::$instance) ? self::$instance : self::$instance = new self;
        }

        private function __construct()
        {
            // Fix the canonical tag
            $this->replace_canonical_tag_wp();

            // Register XML Sitemap Provider
            add_filter(
                'init',
                function() {

                    if(!apply_filters('wp_sitemaps_enabled', TRUE)) {
                        return;
                    }

                    if(PeepSo::get_option('site_activity_hide_stream_from_guest', 0)) {
                        return;
                    }

                    $provider = new PeepSo3_Sitemap_Provider();
                    wp_register_sitemap_provider( 'communityposts', $provider );
                }
            );
        }

        // Replace WP canonical with our own
        private function replace_canonical_tag_wp() {
            add_action('wp', function() {
                global $post;
                if($post instanceof WP_Post && stristr($post->post_content, '[peepso_')) {
                    remove_action('wp_head', 'rel_canonical');
                    add_action('wp_head', function() {
                        echo "<!-- PeepSo canonical override -->\n<link rel='canonical' href='{$this->real_url()}' />\n";
                    });
                }
            });
        }

        // Return full current URL, including query args
        private  function real_url() {
            global $wp;
            return add_query_arg( $_GET, home_url( trim($wp->request,'/').'/' ) );
        }
    }


    PeepSo3_SEO::get_instance();

}