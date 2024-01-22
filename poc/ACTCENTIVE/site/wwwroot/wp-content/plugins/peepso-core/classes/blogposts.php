<?php

    class PeepSoBlogPosts {
        private static $instance;

        public static function get_instance()
        {
            if (self::$instance === NULL) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function __construct()
        {
            if(PeepSo::get_option('blogposts_activity_enable', 0)) {

                /** INIT */
                add_action('peepso_init', function() {
                    PeepSoTemplate::add_template_directory(plugin_dir_path(__FILE__));
                });

                /** ADMIN HOOKS */

                // Store the BlogPosts meta in the companion poost
                add_action('save_post',  function($post_id) {

                    $cats = get_the_category($post_id);

                    // #4424 exclude selected categories
                    if(!PeepSoBlogPosts::enabled_for_post_categories($post_id)) {
                        // force unpublish if activity already created and category integration is disabled
                        $act_id = get_post_meta($post_id, PeepSo::BLOGPOSTS_SHORTCODE, TRUE);
                        if(strlen($act_id) && is_numeric($act_id) && 0 < $act_id) {
                            wp_update_post(array(
                                'ID' => $act_id,
                                'post_status' => 'pending'
                            ));
                        }

                        return;
                    }

                    // #5983 remove activity stream when set to draft / pending review
                    $post_status = get_post_status($post_id);
                    if ($post_status === 'draft' || $post_status === 'pending' || $post_status === 'future' ) {
                        $act_id = get_post_meta($post_id, PeepSo::BLOGPOSTS_SHORTCODE, TRUE);
                        if(strlen($act_id) && is_numeric($act_id) && 0 < $act_id) {
                            wp_update_post(array(
                                'ID' => $act_id,
                                'post_status' => 'pending'
                            ));
                        }
                    }

                    // Handle the excerpt
                    if (array_key_exists('peepso_excerpt', $_POST)) {

                        update_post_meta(
                            $post_id,
                            'peepso_excerpt',
                            $_POST['peepso_excerpt']
                        );
                    }

                    // Handle the hashtags
                    $hashtags = '';
                    $import_tags = FALSE;
                    $import_cats = FALSE;

                    if(PeepSo::get_option('hashtags_enable', 1)) {

                        if (array_key_exists('peepso_hashtags', $_POST)) {
                            $hashtags = $this->hashtags_meta_cleanup($_POST['peepso_hashtags']);
                        }


                        // Use WP tags
                        if(array_key_exists('peepso_hashtags_use_wp_tags', $_POST)) {
                            update_post_meta($post_id, 'peepso_hashtags_use_wp_tags', 1);
                            $import_tags = TRUE;
                        } else {
                            delete_post_meta($post_id, 'peepso_hashtags_use_wp_tags');
                        }

                        // Use WP cats
                        if(array_key_exists('peepso_hashtags_use_wp_cats', $_POST)) {
                            update_post_meta($post_id, 'peepso_hashtags_use_wp_cats', 1);
                            $import_cats = TRUE;
                        } else {
                            delete_post_meta($post_id, 'peepso_hashtags_use_wp_cats');
                        }

                        update_post_meta(
                            $post_id,
                            'peepso_hashtags',
                            $hashtags
                        );

                    }

                    // Update the companion post
                    if($companion_id = get_post_meta($post_id,PeepSo::BLOGPOSTS_SHORTCODE, TRUE)) {

                        $excerpt = get_post_meta($post_id, 'peepso_excerpt', TRUE);
                        $hashtags = get_post_meta($post_id, 'peepso_hashtags', TRUE);

                        if($import_tags) {
                            // Import WP tags
                            $tags = get_the_tags($post_id);
                            if($tags && count($tags)) {
                                foreach($tags as $tag) {
                                    // new PeepSoError(print_r($tag, TRUE));

                                    // WP tags might contain spaces, so we handle that.
                                    // Any other special characters are the responsibility of the admin
                                    // To use more than [a-z0-0], enable non-alpha hashtags in PeepSo
                                    $peepso_tag = str_replace(' ','-', $tag->name);
                                    $hashtags .= ' #'.$peepso_tag;
                                }
                            }
                        }

                        if($import_cats) {
                            // Import WP tags
                            new PeepSoError(print_r($cats, TRUE));


                            if(count($cats)) {
                                foreach($cats as $cat) {
                                    // WP categories might contain spaces, so we handle that.
                                    // Any other special characters are the responsibility of the admin
                                    // To use more than [a-z0-0], enable non-alpha hashtags in PeepSo
                                    $peepso_tag = str_replace(' ','-', $cat->name);
                                    $hashtags .= ' #'.$peepso_tag;
                                }
                            }
                        }

                        $companion = get_post($companion_id);

                        if($companion instanceof WP_Post && $companion->post_content = json_decode($companion->post_content)) {

                            $companion->post_content->excerpt = $excerpt;

                            if (PeepSo::get_option('hashtags_enable', 1)) {

                                if (PeepSo::get_option('blogposts_hashtags_sort', 1) && strstr($hashtags, ' ')) {

                                    $hashtags = explode(' ', $hashtags);

                                    natcasesort($hashtags);

                                    $hashtags = implode(' ', $hashtags);
                                }

                                $hashtags = " " . $hashtags . " ";
                                //$hashtags = strtolower($hashtags);
                                $companion->post_content->hashtags = $hashtags;
                            }

                            $companion->post_content = json_encode($companion->post_content, JSON_UNESCAPED_UNICODE);
                            add_filter( 'wp_insert_post_data', function($new, $old) {
                                $new['post_modified'] = $old['post_date'];
                                $new['post_modified_gmt'] = $old['post_date_gmt'];
                                return $new;
                            }, 10, 2 );
                
                            wp_update_post($companion);

                            remove_all_filters('wp_insert_post_data');

                        } else {
                            new PeepSoError("Blogposts: missing or malformed companion [$companion_id] for post [$post_id]");
                        }

                        // Make sure hashtag maintenance script catches the companion post
                        delete_post_meta($companion_id, PeepSo::HASHTAGS_POST_META);
                        PeepSo::get_instance()->hashtags_build_hashtags(PeepSo::get_option('hashtags_post_count_batch_size', 5));
                        PeepSo::get_instance()->hashtags_build_posts(PeepSo::get_option('hashtags_post_count_batch_size', 5));
                    }

                },9999,1);

                //  Meta box
                add_action( 'add_meta_boxes', function(){
                    add_meta_box(
                        'peepso_meta_box',
                        __('PeepSo Blog Posts  Integration', 'peepso-core'),
                        array(&$this,'blogposts_meta_box'),
                        'post',
                        'side',
                        'default'
                    );
                });

                // Category settings UI
                add_action ( 'category_edit_form_fields', function($term) {
                    ?>
                    <tr class="form-field">
                        <th scope="row" valign="top"><label for="cat_Image_url"><?php echo __('PeepSo Blog Posts integration', 'peepso-core'); ?></label></th>
                        <td>
                            <select name="peepso_blogposts" id="peepso_blogposts" style="width:60%;">
                                <option value="1"><?php echo __('Enabled','peepso-core');?></option>
                                <option value="0" <?php if(!PeepSoBlogPosts::enabled_for_category($term->term_id)) { echo "selected"; }?> ><?php echo __('Disabled','peepso-core');?></option>
                            </select>

                            <br />
                            <span class="description"><?php echo __('Use with caution! Disabling PeepSo BlogPosts integration for a category will prevent new stream posts from being created. Old posts (if any) will remain, but comment integration will be disabled both for new and old posts in this category.','peepso-core'); ?></span>
                        </td>
                    </tr>
                    <?php
                });

                // Category settings save
                add_action ( 'edited_category', function( $term_id ) {
                    if ( isset( $_POST['peepso_blogposts'] ) ) {
                        //save the option array
                        update_option( "peepso_blogposts_category_$term_id", $_POST['peepso_blogposts'] );
                    }
                });



                /** FRONTEND HOOKS - COMPANION POST */

                // Attach excerpt to the PeepSo companion post
                add_filter('peepso_activity_content', function($content, $post=NULL){

                    $original_content = strip_tags(get_post_field('post_content', $post, 'raw'));

                    if(!stristr($original_content, PeepSo::BLOGPOSTS_SHORTCODE)) {
                        return $content;
                    }

                    if($target_post = json_decode($original_content)) {
                        $excerpt = '';

                        if(isset($target_post->excerpt)) {
                            $excerpt = $target_post->excerpt;
                        }

                        if (strlen($excerpt)) {
                            if (PeepSo::get_option('md_post', 0)) {
                                $excerpt = PeepSo::do_parsedown($excerpt) . '<br>';
                            } else {
                                $excerpt = '<p>' . $excerpt . '</p>';
                            }
                        }

                        $content = $excerpt . $content;
                    }

                    return $content;

                },9999,2);

                // Attach hashtags to the PeepSo companion post (meta area)
                add_filter('peepso_post_extras', function($extras) {

                    if('below_author' != PeepSo::get_option('blogposts_hashtags_peepso_post_location','below_author')) {
                        return $extras;
                    }

                    global $post;
                    $hashtags = $this->get_hashtags_from_post_json($post->ID);

                    if(!strlen($hashtags)) {
                        return $extras;
                    }

                    $extras['hashtags'] = PeepSoTemplate::exec_template('blogposts','extras-hashtags', array('hashtags' => $hashtags), TRUE);

                    return $extras;
                },9999,1);

                // Attach hashtags to PeepSo companion post (above / below embed)
                add_filter('peepso_activity_content', function($content, $post = NULL) {

                    $location = PeepSo::get_option('blogposts_hashtags_peepso_post_location','below_author');

                    if('below_author' == $location) {
                        return $content;
                    }

                    $hashtags  = $this->get_hashtags_from_post_json($post->ID);

                    if(!strlen($hashtags)) {
                        return $content;
                    }

                    $hashtags = PeepSoTemplate::exec_template('blogposts','wp-post-hashtags', array('hashtags' => $hashtags), TRUE);


                    if($location == 'above_embed') {
                        return $hashtags . $content;
                    }

                    if($location == 'below_embed') {
                        return $content .  $hashtags;
                    }

                },10,2);

                /** FRONTEND HOOKS - WP POST */

                // Attach hashtags to the WP post
                add_filter('the_content', function($content) {

                    if(! in_the_loop()  )   { return $content; }
                    if(! is_singular()  )   { return $content; }
                    if(! is_single()    )   { return $content; }
                    if(! is_main_query())   { return $content; }
                    if(  is_embed()     )   { return $content; }

                    global $post;
                    if($post->post_type != 'post') { return $content; }

                    $location = PeepSo::get_option('blogposts_hashtags_wp_post_location');
                    if(!$location) {
                        return $content;
                    }

                    $hashtags  = $this->get_hashtags_from_post_json(get_post_meta($post->ID, PeepSo::BLOGPOSTS_SHORTCODE, TRUE));

                    if(!strlen($hashtags)) {
                        return $content;
                    }

                    $hashtags = PeepSoTemplate::exec_template('blogposts','wp-post-hashtags', array('hashtags' => $hashtags,'location'=>$location), TRUE);

                    if(stristr($location,'above_post')) {
                        $content = $hashtags . $content;
                    }

                    if(stristr($location,'below_post')) {
                        $content = $content .  $hashtags;
                    }

                    return $content;
                },-2);

            }
        }

        /** ADMIN HOOKS */

        // Print meta box in the Post Edit screen
        public function blogposts_meta_box() {
            global $post;
            ?>
            <strong><?php echo sprintf(__('%s excerpt','peepso-core'),'PeepSo');?></strong>

            <p style="font-size:12px;color: #666666;">
                <?php  echo __('Displays above the blog post embed on PeepSo stream.','peepso-core'); ?>
                <textarea placeholder="<?php echo __('Supports MarkDown formatting. HTML will be ignored.','peepso-core');?>" style="width:100%;min-height:200px;" name="peepso_excerpt"><?php echo get_post_meta($post->ID, 'peepso_excerpt', TRUE);?></textarea>
            </p>

            <?php if(PeepSo::get_option('hashtags_enable', 1)) { ?>
                <hr>

                <strong><?php echo sprintf(__('%s hashtags', 'peepso-core'), 'PeepSo'); ?></strong>
                <p style="font-size:12px;color: #666666;">

                    <?php echo __('List your hashtags separated by spaces, using the # character for each hashtag.', 'peepso-core'); ?>

                    <input
                            type="text"
                            placeholder="#hashtagOne #hashtagTwo"
                            style="width:100%;"
                            name="peepso_hashtags"
                            value="<?php echo $this->hashtags_meta_cleanup(get_post_meta($post->ID, 'peepso_hashtags', TRUE)); ?>"
                    />
                </p>
                <hr>
                <strong><?php echo __('WordPress meta as PeepSo hashtags', 'peepso-core'); ?></strong>

                <p style="font-size:12px;color: #666666;">
                    Attach the WordPress tags and/or categories as PeepSo hashtags in the front-end.
                </p>

                <?php
                $checked = 0;
                $screen = get_current_screen();
                if('add' == $screen->action) {
                    $checked = (int) PeepSo::get_option('blogposts_hashtags_always_use_wp_tags');
                } elseif(strlen($meta = get_post_meta($post->ID, 'peepso_hashtags_use_wp_tags', TRUE))) {
                    $checked = $meta;
                }
                ?>

                <input type="checkbox"
                       value="1" <?php echo $checked ? "checked" : ""; ?>
                       name="peepso_hashtags_use_wp_tags" id="peepso_hashtags_use_wp_tags"/>
                <label for="peepso_hashtags_use_wp_tags">
                    <?php echo __('Use WordPress tags', 'peepso-core'); ?>
                </label>

                <br/>

                <?php
                $checked = 0;
                $screen = get_current_screen();
                if('add' == $screen->action) {
                    $checked = (int) PeepSo::get_option('blogposts_hashtags_always_use_wp_cats');
                } elseif(strlen($meta = get_post_meta($post->ID, 'peepso_hashtags_use_wp_cats', TRUE))) {
                    $checked = $meta;
                }
                ?>

                <input type="checkbox"
                       value="1" <?php echo $checked ? "checked" : ""; ?>
                       name="peepso_hashtags_use_wp_cats" id="peepso_hashtags_use_wp_cats"/>
                <label for="peepso_hashtags_use_wp_cats">
                    <?php echo __('Use WordPress categories', 'peepso-core'); ?>
                </label>


                <?php
            }
        }

        /** UTILITIES */

        // Retrieves list of hashtags stored in the JSON object of PeepSo BlogPosts companion post
        public function get_hashtags_from_post_json($post_id) {
            $post = get_post($post_id);

            if(!$post instanceof WP_Post) {
                return '';
            }

            $content = $post->post_content;

            if(!stristr($content, PeepSo::BLOGPOSTS_SHORTCODE)) {
                return '';
            }

            if($target_post = json_decode($content)) {
                if (isset($target_post->hashtags) && strlen($target_post->hashtags)) {
                    return trim($target_post->hashtags);
                }
            }
            return '';
        }

        // Cleanup the hashtag list
        // Unused for now
        public function hashtags_meta_cleanup($hashtags) {
            //$hashtags = str_replace(array('.',','), ' ', $hashtags);
            //$hashtags = str_replace(' ', ' ', $hashtags);
            return $hashtags;
        }

        // Check if integration is enabled for the post, based on its categories
        public static function enabled_for_post_categories($post_id) {
            $cats = get_the_category($post_id);
            
            if(count($cats)) {
                foreach($cats as $cat) {
                    if(!PeepSoBlogPosts::enabled_for_category($cat->term_id))
                    {
                        return FALSE;
                    }
                }
            }

            return TRUE;
        }
        // Checks if integration is enabled for given category ID
        public static function enabled_for_category($id) {
            return get_option( "peepso_blogposts_category_$id", 1);
        }
    }