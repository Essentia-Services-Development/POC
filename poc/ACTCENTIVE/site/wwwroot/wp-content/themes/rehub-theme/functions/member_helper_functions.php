<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

/**
 * Display Users Badges
 * Will echo all badge images a given user has earned.
 * @since 1.5
 * @version 1.2
 */
if ( ! function_exists( 'rh_mycred_display_users_badges' ) ) :
    function rh_mycred_display_users_badges( $user_id = NULL ) {

        if ( $user_id === NULL || $user_id == 0 ) return;
        if (!function_exists('mycred_get_users_badges')) return;
        $users_badges = mycred_get_users_badges( $user_id );
        if ( ! empty( $users_badges ) ) {
        do_action( 'mycred_before_users_badges', $user_id, $users_badges );
        echo '<div class="rh_mycred-users-badges">';
            foreach ( $users_badges as $badge_id => $level ) {
                $badge = mycred_get_badge( $badge_id, $level );
                if ( $badge === false ) continue;
                if ( $badge->level_image !== false ) {
                    echo apply_filters( 'mycred_the_badge', $badge->level_image, $badge_id, $badge, $user_id );
                }
            }
        echo '</div>';
        do_action( 'mycred_after_users_badges', $user_id, $users_badges );
        }   
    }
endif;

if(!function_exists('rh_author_detail_box')){
    function rh_author_detail_box ($postid=''){
        ?>
        <?php 
            if(!$postid){
                $author_ID = get_the_author_meta('ID');                
            }else{               
                $author_ID = get_post_field( 'post_author', $postid );
            }
            $name = get_the_author_meta( 'display_name', $author_ID );
            if(function_exists('mycred_get_users_rank')){
                if(rehub_option('rh_mycred_custom_points')){
                    $custompoint = rehub_option('rh_mycred_custom_points');
                    $mycredrank = mycred_get_users_rank($author_ID, $custompoint );
                }
                else{
                    $mycredrank = mycred_get_users_rank($author_ID);        
                }
            }
            if(function_exists('mycred_display_users_total_balance') && function_exists('mycred_render_shortcode_my_balance')){
                if(rehub_option('rh_mycred_custom_points')){
                    $custompoint = rehub_option('rh_mycred_custom_points');
                    $mycredpoint = mycred_render_shortcode_my_balance(array('type'=>$custompoint, 'user_id'=>$author_ID, 'wrapper'=>'', 'balance_el' => '') );
                }
                else{
                    $mycredpoint = mycred_render_shortcode_my_balance(array('user_id'=>$author_ID, 'wrapper'=>'', 'balance_el' => '') );           
                }
            }           
        ?>
            <div class="author_detail_box clearfix"><?php echo get_avatar( $author_ID, '69', '', $name ); ?>
                <style scoped>
                    .author_detail_box { background-color: #fff; border: 1px solid #ededed; padding: 20px 0px; margin: 5px auto 40px auto; position: relative; min-height: 90px;width: 100%}
                    .author_detail_box a{text-decoration: none;}
                    .archive .author_detail_box { margin: 0 0 10px 0 }
                    .author_detail_box .avatar {width: 71px; position: absolute; left: 20px; top: 15px; }
                    .author_detail_box > div { width: 100%;    padding: 0 20px 0 110px }
                    .author_detail_box div .social_icon { border-bottom: 1px solid #eee; padding-bottom: 15px; margin-bottom: 15px; }
                    .author_detail_box div h4 {margin: 0 0 8px 0;}
                    .author_detail_box div p { font-size: 14px;line-height: 16px; color: #111111; margin: 0 0 10px 0 }
                    .author_detail_box .rh_mycred-users-badges{display: inline-block; margin-right: 5px }
                    .rtl .author_detail_box .avatar { left: inherit; right: 20px }
                    .rtl .author_detail_box > div { padding: 0 110px 0 20px }
                </style>
                <div class="clearfix">
                    <?php if ( function_exists('bp_core_get_user_domain') ) : ?>
                        <a href="<?php echo bp_core_get_user_domain( $author_ID ); ?>" class="see_full_profile_btn floatright mr10 mb10 ml10"><?php esc_html_e( 'Show full profile', 'rehub-theme' ); ?></a>
                    <?php endif; ?>                
                    <strong class="mb10">
                        <a href="<?php echo get_author_posts_url( $author_ID );?>"><?php echo esc_attr($name);?></a>
                        <?php   
                            if (function_exists('bp_get_member_type')){     
                                $membertype = bp_get_member_type($author_ID);
                                $membertype_object = bp_get_member_type_object($membertype);
                                $membertype_label = (!empty($membertype_object) && is_object($membertype_object)) ? $membertype_object->labels['singular_name'] : '';
                                if($membertype_label){
                                    echo '<span class="rh-user-rank-mc rh-user-rank-'.$membertype.'">'.$membertype_label.'</span>';
                                }
                            }
                        ?>                        
                    </strong class="mb10">
                    <div class="social_icon small_i">
                        <div class="comm_meta_cred">
                            <?php if ( function_exists( 'mycred_get_users_badges' ) && $author_ID !=0 ) : ?>
                                <?php rh_mycred_display_users_badges( $author_ID ) ?>
                            <?php endif; ?>
                            <?php if (!empty($mycredpoint)) :?><i class="rhicon rhi-star-empty"></i> <?php echo ''.$mycredpoint; ?><?php endif;?>
                        </div>                     
                        <?php if(get_the_author_meta('user_url')) : ?><a href="<?php the_author_meta('user_url'); ?>" class="author-social hm" rel="nofollow"><i class="rhicon rhi-home"></i></a><?php endif; ?>
                        <?php if(get_the_author_meta('facebook')) : ?><a href="<?php the_author_meta('facebook'); ?>" class="author-social fb" rel="nofollow"><i class="rhicon rhi-facebook"></i></a><?php endif; ?>
                        <?php if(get_the_author_meta('twitter')) : ?><a href="<?php the_author_meta('twitter'); ?>" class="author-social tw" rel="nofollow"><i class="rhicon rhi-twitter"></i></a><?php endif; ?>
                        <?php if(get_the_author_meta('linkedin')) : ?><a href="<?php the_author_meta('linkedin'); ?>" class="author-social in" rel="nofollow"><i class="rhicon rhi-linkedin"></i></a><?php endif; ?>
                        <?php if(get_the_author_meta('google')) : ?><a href="<?php the_author_meta('google'); ?>?rel=author" class="author-social gp" rel="nofollow"><i class="rhicon rhi-google-plus"></i></a><?php endif; ?>
                        <?php if(get_the_author_meta('tumblr')) : ?><a href="<?php the_author_meta('tumblr'); ?>" class="author-social tm" rel="nofollow"><i class="rhicon rhi-tumblr"></i></a><?php endif; ?>
                        <?php if(get_the_author_meta('instagram')) : ?><a href="<?php the_author_meta('instagram'); ?>" class="author-social ins" rel="nofollow"><i class="rhicon rhi-instagram"></i></a><?php endif; ?>
                        <?php if(get_the_author_meta('vkontakte')) : ?><a href="<?php the_author_meta('vkontakte'); ?>" class="author-social vk" rel="nofollow"><i class="rhicon rhi-vk"></i></a><?php endif; ?>
                        <?php if(get_the_author_meta('youtube')) : ?><a href="<?php the_author_meta('youtube'); ?>" class="author-social yt" rel="nofollow"><i class="rhicon rhi-youtube"></i></a><?php endif; ?>
                     </div>
                    <?php if (get_the_author_meta('description', $author_ID) !='') :?><p><?php the_author_meta('description', $author_ID); ?></p><?php endif;?>
                    <p>
                </div>
            </div>
        <?php
    }
}

/* Redirect from author profile to BP */
if(rehub_option('bp_redirect') =='1'){
    add_action( 'template_redirect', 'rh_redirect_author_archive_to_profile' ); 
}
function rh_redirect_author_archive_to_profile() {
  if(is_author()){
    $user_id = get_query_var( 'author' );
    if (function_exists('bp_core_get_user_domain')) {
        wp_redirect( bp_core_get_user_domain( $user_id ) );       
    }
  }
}

/* Add user sub id to link */
if (rehub_option('enable_user_sub_id')){
    if (is_user_logged_in() || rehub_option('sub_id_show') == 'author' || rehub_option('sub_id_show') == 'authorid') {
        add_filter('rehub_create_btn_url', 'rehub_add_subid_tracker');
        add_filter('rh_post_offer_url_filter', 'rehub_add_subid_cash');
        function rehub_add_subid_cash($offer_post_url){
            if(rehub_option('sub_id_show') == 'id'){
                $showuser = 'id';
            }elseif(rehub_option('sub_id_show') == 'author'){
                $showuser = 'author';
            }elseif(rehub_option('sub_id_show') == 'authorid'){
                $showuser = 'authorid';
            }elseif(rehub_option('sub_id_show') == 'name'){
                $showuser = 'name';
            }else{
                return $offer_post_url;
            }
            if($showuser == 'name'){
                $current_user = wp_get_current_user();
                $userlogin = trim($current_user->user_login);                
            }elseif($showuser == 'id'){
                $userlogin = get_current_user_id();               
            }elseif($showuser == 'authorid'){
                global $post;
                $userlogin=$post->post_author;            
            }
            elseif($showuser == 'author'){
                global $post;
                $author_id=$post->post_author;
                $user_info = get_userdata($author_id); 
                $userlogin = $user_info->user_login;            
            }           

            $subidpart = rehub_option('custom_sub_id') ? rehub_option('custom_sub_id') : 'subid=';
            $parsed_query = parse_url( $offer_post_url, PHP_URL_QUERY );
            $subidarray = array_map('trim', explode(PHP_EOL, $subidpart));
            $shop = parse_url($offer_post_url, PHP_URL_HOST);
            $shop = preg_replace('/^www\./', '', $shop);            

            foreach ($subidarray as $subidpart) {
                $subid = array_map('trim', explode('@', $subidpart));
                if (isset($subid[1])){
                    if($shop == $subid[0]){
                        if($subid[1] == 'exclude'){return $offer_post_url;}
                        if(!empty($parsed_query)){
                            $offer_post_url = $offer_post_url.'&'.$subid[1].$userlogin;
                        }else{
                            $offer_post_url = $offer_post_url.'?'.$subid[1].$userlogin;
                        }
                        return $offer_post_url;
                    }else{
                        continue;
                    }
                }
                       
                if(!empty($parsed_query)){
                    $offer_post_url = $offer_post_url.'&'.$subidpart.$userlogin;
                }else{
                    $offer_post_url = $offer_post_url.'?'.$subidpart.$userlogin;
                }
                return $offer_post_url;                                        
            }
        }
        function rehub_add_subid_tracker($offer_post_url){
            if(class_exists('\CashbackTracker\application\Plugin')){
                $domain = \CashbackTracker\application\helpers\TextHelper::getHostName($offer_post_url);
                if($domain) {
                    $advertiser = \CashbackTracker\application\components\AdvertiserManager::getInstance()->findAdvertiserByDomain($domain);
                    if($advertiser){
                        $offer_post_url = \CashbackTracker\application\components\DeeplinkGenerator::generateTrackingLink($advertiser['module_id'], $advertiser['id'], $offer_post_url);
                    }
                }
                $offer_post_url = \CashbackTracker\application\components\DeeplinkGenerator::maybeAddTracking($offer_post_url);
            } 
            return $offer_post_url;         
        }
    }   
}


if ( function_exists( 'mycred' ) ) {
    /**
     * Register Hook
     */
    add_filter( 'mycred_setup_hooks', 'mycred_register_overall_post_likes_hook', 120 );
    function mycred_register_overall_post_likes_hook( $installed ) {
        $installed['overallpostlikes'] = array(
            'title' => esc_html__( 'Hot Meter & Thumbs Likes', 'rehub-theme' ),
            'description' => esc_html__( 'Awards %_plural% to Author for post likes via the Hot Meter.', 'rehub-theme' ),
            'callback' => array( 'myCRED_Hook_Overall_Post_Likes' )
        );
        $installed['overallpostwishes'] = array(
            'title' => esc_html__( 'Wishlist', 'rehub-theme' ),
            'description' => esc_html__( 'Awards %_plural% to Author for adding his post to wishlist', 'rehub-theme' ),
            'callback' => array( 'myCRED_Hook_Overall_Post_Wishes' )
        );        
        return $installed;
    }

    add_filter( 'mycred_all_references', 'add_overall_post_likes_references' );
    function add_overall_post_likes_references( $references ) {      
        $references['ref_overall_post_likes'] = esc_html__( 'Hot Meter & Thumbs Likes', 'rehub-theme' );
        $references['ref_overall_post_wishes'] = esc_html__( 'Wishlist', 'rehub-theme' );        
        return $references;
    }

    /**
     * Overall Post Likes Hook
     */
    add_action( 'mycred_load_hooks', 'mycred_load_overall_post_likes_hook', 120 );
    function mycred_load_overall_post_likes_hook() {
        // If the hook has been replaced or if plugin is not installed, exit now
        if ( class_exists( 'myCRED_Hook_Overall_Post_Likes' ) || class_exists( 'myCRED_Hook_Overall_Post_Wishes' )) return;
        class myCRED_Hook_Overall_Post_Likes extends myCRED_Hook {
            /**
             * Construct
             */
            function __construct( $hook_prefs, $type = 'mycred_default' ) {
                parent::__construct( array(
                    'id' => 'overallpostlikes',
                    'defaults' => array(
                        'added' => array(
                            'creds' => '1',
                            'log'   => '%plural% for added a post like',
                            'limit' => '0/x'
                        ),
                        'removed' => array(
                            'creds' => '-1',
                            'log'   => '%plural% deduction for removed a post like'
                        ),
                    )
                ), $hook_prefs, $type );
            }

            /**
             * Run
             */
            public function run() {               
                //add_action( 'rh_overall_post_likes_add', array( $this, 'add_post_likes' ) );
                // add_action( 'rh_overall_post_likes_remove', array( $this, 'remove_post_likes' ) );
                add_action( 'rh_overall_post_likes_add', array( $this, 'get_post_likes_ajax' ) );
            }

            /**
             * Get Ajax Data
             */
            public function get_post_likes_ajax() {
                $post_id = intval( $_POST['post_id'] );            
                if ( $post_id && $_POST['hot_count'] == 'hot' )
                    $this->add_post_likes( $post_id );
                if ( $post_id && $_POST['hot_count'] == 'cold' )
                    $this->remove_post_likes( $post_id );
            }
            
            /**
             * Added Like
             */
            public function add_post_likes( $post_id ) {
                $post = get_post( $post_id );
                $user_id = get_current_user_id();
                if ( $user_id != $post->post_author ) {
                    // Award post author for being added like to his post
                    if ( $this->prefs['added']['creds'] != 0 && ! $this->core->exclude_user( $post->post_author ) ) {
                        // Limit
                        if ( ! $this->over_hook_limit( 'added', 'ref_overall_post_likes', $post->post_author ) ) {
                            // Execute
                            $this->core->add_creds(
                                'ref_overall_post_likes',
                                $post->post_author,
                                $this->prefs['added']['creds'],
                                $this->prefs['added']['log'],
                                $post_id,
                                array( 'ref_type' => 'post', 'by' => $user_id ),
                                $this->mycred_type
                            );
                        }
                    }
                }
            }

            /**
             * Removed Like
             */
            public function remove_post_likes( $post_id ) {
                $post = get_post( $post_id );
                $user_id = get_current_user_id();
                if ( $user_id != $post->post_author ) {
                    if ( $this->prefs['removed']['creds'] != 0 && ! $this->core->exclude_user( $post->post_author ) ) {
                        $this->core->add_creds(
                            'ref_overall_post_likes',
                            $post->post_author,
                            $this->prefs['removed']['creds'],
                            $this->prefs['removed']['log'],
                            $post_id,
                            array( 'ref_type' => 'post', 'by' => $user_id ),
                            $this->mycred_type
                        );
                    }
                }
            }

            /**
             * Preferences for Post Likes
             */
            public function preferences() {
            $prefs = $this->prefs;
            ?>
                <label class="subheader" for="<?php echo ''.$this->field_id( array( 'added' => 'creds' ) ); ?>"><?php esc_html_e( 'Author Content is liked', 'rehub-theme' ); ?></label>
                <ol>
                    <li>
                        <div class="h2"><input type="text" name="<?php echo ''.$this->field_name( array( 'added' => 'creds' ) ); ?>" id="<?php echo ''.$this->field_id( array( 'added' => 'creds' ) ); ?>" value="<?php echo ''.$this->core->number( $prefs['added']['creds'] ); ?>" size="8" /></div>
                    </li>
                    <li>
                        <label for="<?php echo ''.$this->field_id( array( 'added' => 'limit' ) ); ?>"><?php esc_html_e( 'Limit', 'rehub-theme' ); ?></label>
                        <?php echo ''.$this->hook_limit_setting( $this->field_name( array( 'added' => 'limit' ) ), $this->field_id( array( 'added' => 'limit' ) ), $prefs['added']['limit'] ); ?>
                    </li>
                </ol>
                <label class="subheader" for="<?php echo ''.$this->field_id( array( 'added' => 'log' ) ); ?>"><?php esc_html_e( 'Log Template', 'rehub-theme' ); ?></label>
                <ol>
                    <li>
                        <div class="h2"><input type="text" name="<?php echo ''.$this->field_name( array( 'added' => 'log' ) ); ?>" id="<?php echo ''.$this->field_id( array( 'added' => 'log' ) ); ?>" value="<?php echo esc_attr( $prefs['added']['log'] ); ?>" class="long" /></div>
                        <span class="description"><?php echo ''.$this->available_template_tags( array( 'general', 'post' ) ); ?></span>
                    </li>
                </ol>

                <label class="subheader" for="<?php echo ''.$this->field_id( array( 'removed' => 'creds' ) ); ?>"><?php esc_html_e( 'Author Content is disliked', 'rehub-theme' ); ?></label>
                <ol>
                    <li>
                        <div class="h2"><input type="text" name="<?php echo ''.$this->field_name( array( 'removed' => 'creds' ) ); ?>" id="<?php echo ''.$this->field_id( array( 'removed' => 'creds' ) ); ?>" value="<?php echo ''.$this->core->number( $prefs['removed']['creds'] ); ?>" size="8" /></div>
                    </li>
                </ol>
                <label class="subheader" for="<?php echo ''.$this->field_id( array( 'removed' => 'log' ) ); ?>"><?php esc_html_e( 'Log Template', 'rehub-theme' ); ?></label>
                <ol>
                    <li>
                        <div class="h2"><input type="text" name="<?php echo ''.$this->field_name( array( 'removed' => 'log' ) ); ?>" id="<?php echo ''.$this->field_id( array( 'removed' => 'log' ) ); ?>" value="<?php echo esc_attr( $prefs['removed']['log'] ); ?>" class="long" /></div>
                        <span class="description"><?php echo ''.$this->available_template_tags( array( 'general', 'post' ) ); ?></span>
                    </li>
                </ol>
            <?php
            }
            
            /**
             * Sanitise Preferences
             */
            function sanitise_preferences( $data ) {

                if ( isset( $data['added']['limit'] ) && isset( $data['added']['limit_by'] ) ) {
                    $limit = sanitize_text_field( $data['added']['limit'] );
                    if ( $limit == '' ) $limit = 0;
                    $data['added']['limit'] = $limit . '/' . $data['added']['limit_by'];
                    unset( $data['added']['limit_by'] );
                }

                return $data;
            }
        }
        class myCRED_Hook_Overall_Post_Wishes extends myCRED_Hook {
            /**
             * Construct
             */
            function __construct( $hook_prefs, $type = 'mycred_default' ) {
                parent::__construct( array(
                    'id' => 'overallpostwishes',
                    'defaults' => array(
                        'added' => array(
                            'creds' => '1',
                            'log'   => '%plural% for added to wishlist',
                            'limit' => '0/x'
                        ),
                        'removed' => array(
                            'creds' => '-1',
                            'log'   => '%plural% deduction for removed from wishlist'
                        ),
                    )
                ), $hook_prefs, $type );
            }

            /**
             * Run
             */
            public function run() {               
                add_action( 'rh_overall_post_wishes_add', array( $this, 'get_post_wishes_ajax' ) );
            }

            /**
             * Get Ajax Data
             */
            public function get_post_wishes_ajax() {
                $post_id = intval( $_POST['post_id'] );            
                if ( $post_id && $_POST['wish_count'] == 'add' )
                    $this->add_post_wishes( $post_id );
                if ( $post_id && $_POST['wish_count'] == 'remove' )
                    $this->remove_post_wishes( $post_id );
            }
            
            /**
             * Added Like
             */
            public function add_post_wishes( $post_id ) {
                $post = get_post( $post_id );
                $user_id = get_current_user_id();
                if ( $user_id != $post->post_author ) {
                    // Award post author for being added like to his post
                    if ( $this->prefs['added']['creds'] != 0 && ! $this->core->exclude_user( $post->post_author ) ) {
                        // Limit
                        if ( ! $this->over_hook_limit( 'added', 'ref_overall_post_wishes', $post->post_author ) ) {
                            // Execute
                            $this->core->add_creds(
                                'ref_overall_post_wishes',
                                $post->post_author,
                                $this->prefs['added']['creds'],
                                $this->prefs['added']['log'],
                                $post_id,
                                array( 'ref_type' => 'post', 'by' => $user_id ),
                                $this->mycred_type
                            );
                        }
                    }
                }
            }

            /**
             * Removed Like
             */
            public function remove_post_wishes( $post_id ) {
                $post = get_post( $post_id );
                $user_id = get_current_user_id();
                if ( $user_id != $post->post_author ) {
                    if ( $this->prefs['removed']['creds'] != 0 && ! $this->core->exclude_user( $post->post_author ) ) {
                        $this->core->add_creds(
                            'ref_overall_post_wishes',
                            $post->post_author,
                            $this->prefs['removed']['creds'],
                            $this->prefs['removed']['log'],
                            $post_id,
                            array( 'ref_type' => 'post', 'by' => $user_id ),
                            $this->mycred_type
                        );
                    }
                }
            }

            /**
             * Preferences for Post Wishes
             */
            public function preferences() {
            $prefs = $this->prefs;
            ?>
                <label class="subheader" for="<?php echo ''.$this->field_id( array( 'added' => 'creds' ) ); ?>"><?php esc_html_e( 'Author Content is added to wishlist', 'rehub-theme' ); ?></label>
                <ol>
                    <li>
                        <div class="h2"><input type="text" name="<?php echo ''.$this->field_name( array( 'added' => 'creds' ) ); ?>" id="<?php echo ''.$this->field_id( array( 'added' => 'creds' ) ); ?>" value="<?php echo ''.$this->core->number( $prefs['added']['creds'] ); ?>" size="8" /></div>
                    </li>
                    <li>
                        <label for="<?php echo ''.$this->field_id( array( 'added' => 'limit' ) ); ?>"><?php esc_html_e( 'Limit', 'rehub-theme' ); ?></label>
                        <?php echo ''.$this->hook_limit_setting( $this->field_name( array( 'added' => 'limit' ) ), $this->field_id( array( 'added' => 'limit' ) ), $prefs['added']['limit'] ); ?>
                    </li>
                </ol>
                <label class="subheader" for="<?php echo ''.$this->field_id( array( 'added' => 'log' ) ); ?>"><?php esc_html_e( 'Log Template', 'rehub-theme' ); ?></label>
                <ol>
                    <li>
                        <div class="h2"><input type="text" name="<?php echo ''.$this->field_name( array( 'added' => 'log' ) ); ?>" id="<?php echo ''.$this->field_id( array( 'added' => 'log' ) ); ?>" value="<?php echo esc_attr( $prefs['added']['log'] ); ?>" class="long" /></div>
                        <span class="description"><?php echo ''.$this->available_template_tags( array( 'general', 'post' ) ); ?></span>
                    </li>
                </ol>

                <label class="subheader" for="<?php echo ''.$this->field_id( array( 'removed' => 'creds' ) ); ?>"><?php esc_html_e( 'Author Content is disliked', 'rehub-theme' ); ?></label>
                <ol>
                    <li>
                        <div class="h2"><input type="text" name="<?php echo ''.$this->field_name( array( 'removed' => 'creds' ) ); ?>" id="<?php echo ''.$this->field_id( array( 'removed' => 'creds' ) ); ?>" value="<?php echo ''.$this->core->number( $prefs['removed']['creds'] ); ?>" size="8" /></div>
                    </li>
                </ol>
                <label class="subheader" for="<?php echo ''.$this->field_id( array( 'removed' => 'log' ) ); ?>"><?php esc_html_e( 'Log Template', 'rehub-theme' ); ?></label>
                <ol>
                    <li>
                        <div class="h2"><input type="text" name="<?php echo ''.$this->field_name( array( 'removed' => 'log' ) ); ?>" id="<?php echo ''.$this->field_id( array( 'removed' => 'log' ) ); ?>" value="<?php echo esc_attr( $prefs['removed']['log'] ); ?>" class="long" /></div>
                        <span class="description"><?php echo ''.$this->available_template_tags( array( 'general', 'post' ) ); ?></span>
                    </li>
                </ol>
            <?php
            }
            
            /**
             * Sanitise Preferences
             */
            function sanitise_preferences( $data ) {

                if ( isset( $data['added']['limit'] ) && isset( $data['added']['limit_by'] ) ) {
                    $limit = sanitize_text_field( $data['added']['limit'] );
                    if ( $limit == '' ) $limit = 0;
                    $data['added']['limit'] = $limit . '/' . $data['added']['limit_by'];
                    unset( $data['added']['limit_by'] );
                }

                return $data;
            }
        }        
    }
}

//FUNCTIONS FOR GEO LOCATOR. USE IT WITH GEO MY WP PLUGIN

// Pop-up info window on the map
if (!function_exists('rh_gmw_vendor_in_popup')){
    function rh_gmw_vendor_in_popup ($output, $location, $args, $gmw){
        $userid = $location->ID;
        if (defined('wcv_plugin_dir')) {
            $avatar = WCV_Vendors::is_vendor($userid) ? '<img src='.rh_show_vendor_avatar($userid, 120, 120).' />' : bp_core_fetch_avatar ( array( 'item_id' => $userid, 'type' => 'full' ) );
            $link = WCV_Vendors::is_vendor($userid) ? WCV_Vendors::get_vendor_shop_page($userid) : bp_core_get_user_domain($userid);
            $name = WCV_Vendors::is_vendor($userid) ? WCV_Vendors::get_vendor_sold_by( $userid ) : $location->display_name;
        }
        elseif ( class_exists( 'WeDevs_Dokan' ) ){
            $is_vendor = dokan_is_user_seller( $userid);
            $avatar = $is_vendor ? '<img src='.rh_show_vendor_avatar($userid, 120, 120).' />' : bp_core_fetch_avatar ( array( 'item_id' => $userid, 'type' => 'full' ) );
            $link = $is_vendor ? dokan_get_store_url($userid) : bp_core_get_user_domain($userid);
            $name = $is_vendor ? get_user_meta( $userid, 'dokan_store_name', true ) : $location->display_name;
        }
        elseif (defined('WCFMmp_TOKEN')) {
            $avatar = wcfm_is_vendor($userid) ? '<img src='.rh_show_vendor_avatar($userid, 120, 120).' />' : bp_core_fetch_avatar ( array( 'item_id' => $userid, 'type' => 'full' ) );
            $link = wcfm_is_vendor($userid) ? wcfmmp_get_store_url($userid) : bp_core_get_user_domain($userid);
            $name = wcfm_is_vendor($userid) ? get_user_meta($userid, 'store_name', true) : $location->display_name;
        }        
        else {
            $avatar = bp_core_fetch_avatar(array('item_id' => $userid, 'type' => 'full'));
            $link = bp_core_get_user_domain($userid);
            $name = $location->display_name;
        }

        $args_fields = (!empty($args["address_fields"])) ? $args["address_fields"] : '';
     
        $output                  = array();
        $output['wrap']         = '<div class="gmw-fl-infow-window-wrapper wppl-fl-info-window">';
        $output['image']         = '<div class="thumb wppl-info-window-thumb">'.$avatar.'</div>';
        $output['content_start'] = '<div class="content wppl-info-window-info"><table>';
        $output['title']          = '<tr><td><span class="wppl-info-window-permalink"><a href="'.esc_url($link).'">'.esc_attr($name).'</a></span></td></tr>';
        if($args_fields){
            $output['address_fields']       = '<tr><td><span class="address gmw-icon-location">'.gmw_get_location_address( $location, $args["address_fields"], $gmw ).'</span></td></tr>';
        }
        
        if ( !empty($args['distance'] ) && isset( $location->distance ) ) {
            $output['distance'] = '<tr><td><span class="distance">'. esc_attr( $location->distance ) . ' ' .$location->units.'</td></tr>';
        }
         
        $output['content_end']  = '</table></div>';
        $output['/wrap']          = '</div>';
        return $output;
    }
}
add_filter( 'gmw_fl_info_window_content', 'rh_gmw_vendor_in_popup', 10, 4);

// Replace the default location pin for vendor / client location - ext. Members (Friends) Locator
if (!function_exists('rh_gmw_vendor_mapin')){
    function rh_gmw_vendor_mapin ($map_icon, $location){
        $is_vendor = '';
        $user_id = $location->ID;
        $map_icon = get_template_directory_uri() . '/images/default/mapuserpin.png'; 
        $map_vendor_icon = get_template_directory_uri() . '/images/default/mapvendorpin.png';  
        if(defined('wcv_plugin_dir')) {
            $is_vendor = WCV_Vendors::is_vendor($user_id);
        }elseif(defined('WCFMmp_TOKEN')) {
            $is_vendor = wcfm_is_vendor($user_id);            
        }elseif(class_exists('WeDevs_Dokan')){
            $is_vendor = dokan_is_user_seller($user_id);
        }
        if($is_vendor)
            return $map_vendor_icon;
        return $map_icon;
    }
}
add_filter( 'gmw_fl_map_icon', 'rh_gmw_vendor_mapin', 10, 2);


// Replace the default location pin for group location - ext. Groups Locator
if (!function_exists('rh_gmwgl_vendor_mapin')){
    function rh_gmwgl_vendor_mapin ($member, $gmw_form){
        return get_template_directory_uri() . '/images/default/mappostpin.png';               
    }
}
add_filter( 'gmw_gl_map_icon', 'rh_gmwgl_vendor_mapin', 10, 2);


// GMW Function - Update post, product location base on user location
function rh_gmw_friends_pass_map_data( $post_id, $post ) {
    if( !function_exists('gmw_get_user_location') || !function_exists('gmw_update_location_data') )
        return;
    $user_id = $post->post_author;
    $gmw_member_info = gmw_get_user_location( $user_id ); 
    $gmw_member_info = ( array ) $gmw_member_info;
    if( empty($gmw_member_info) )
        return;
    $gmw_member_info['object_type'] = 'post';
    $gmw_member_info['object_id'] = ( int ) $post_id;
    unset( $gmw_member_info['ID'], $gmw_member_info['created'], $gmw_member_info['updated'] );
    unset( $gmw_member_info['lat'], $gmw_member_info['lng'] );
    gmw_update_location_data( $gmw_member_info );
}
if(rehub_option('post_sync_with_user_location') == 1){
    add_action( 'publish_post', 'rh_gmw_friends_pass_map_data', 10, 2 );
    add_action( 'publish_product', 'rh_gmw_friends_pass_map_data', 10, 2 );    
}

if( !function_exists( 'bd_gmw_before_members_query' ) ){
    function bd_gmw_before_members_query( $query_args, $obj ){
        
        $templates = array( 'custom_vendor-users', 'custom_vendor-users-3-col', 'custom_vendor-users-with-last-products' );
        $curr_template = $obj['search_results']['results_template'];
    
        if( !in_array( $curr_template, $templates ) )
            return $query_args;
        
        $include_vendor = array();
        if( class_exists( 'WeDevs_Dokan' ) ) {
            $user_role = 'seller';
        }
        elseif(defined( 'wcv_plugin_dir' )) {
            $user_role = 'vendor';
        }
        elseif(defined( 'WCFMmp_TOKEN' )) {
            $user_role = 'wcfm_vendor';
        }
        else{
            $user_role = '';
        }

        if( !$user_role )
            return $query_args;
        
        $vendors = get_users( array( 'role' => $user_role ) );
        
        if( !empty( $vendors ) ){
            foreach( $vendors as $vendor ){
                $include_vendor[] = $vendor->ID;
            }

            $query_args['include'] = $include_vendor;
        }

        return $query_args;
    }
}
add_filter( 'gmw_fl_search_query_args', 'bd_gmw_before_members_query', 10, 2 );
/* END GEO MY WP PLUGIN HOOKS*/

//Automatically assign vendor role to new roles of user
if (rehub_option('rh_sync_role') != ''){
    $data = rehub_option('rh_sync_role');
    $data = explode(':', $data);
    if(!empty($data[0]) && !empty($data[1]) && !empty($data[2])){
        add_action( 'set_user_role', 'assign_to_rhcustom_role', 30, 3 );
        function assign_to_rhcustom_role( $user_id, $new_role, $old_roles ) {
            $data = rehub_option('rh_sync_role');
            $data = explode(':', $data);            
            $wp_user_object = new WP_User($user_id);
            $vendor_role   = $data[0];
            $roles_remove = array_map('trim', explode(",", $data[1]));          
            $roles_add = array_map('trim', explode(",", $data[2]));
            if ( in_array($new_role, $roles_remove) ) {
                $wp_user_object->remove_role( $vendor_role ); 
            }
            elseif ( in_array($new_role, $roles_add) ) {
                $wp_user_object->add_role( $vendor_role ); 
            }
            else {
                return;
            }
        }       
    }
}

if (rehub_option('rh_award_role_mycred') != ''){
    add_filter( 'mycred_add_finished', 'rh_award_new_role_mycred', 99, 3 );
    function rh_award_new_role_mycred( $reply, $request, $mycred ) {
        // Make sure that if any other filter has declined this we also decline
        if ( $reply === false ) return $reply;

        // Exclude admins
        if ( user_can( $request['user_id'], 'manage_options' ) ) return $reply;

        extract( $request );

        $rolechangedarray = rehub_option('rh_award_role_mycred');

        $rolechangedarray = explode(PHP_EOL, $rolechangedarray);
        $thresholds = array();

        foreach ($rolechangedarray as $key => $value) {
            $values = explode(':', $value);
            if (empty($values[0]) || empty($values[1])) return;
            $roleforchange = trim($values[0]);
            $numberforchange = trim($values[1]);            
            $thresholds[$roleforchange] = (int)$numberforchange;
        }

        // Get users current balance
        $current_balance = $mycred->get_users_balance( $user_id, $type );
        $current_balance = (int)$current_balance + (int)$amount;

        // Check if the users current balance awards a new role
        $new_role = false;
        foreach ( $thresholds as $role => $min ) {
            if ( $current_balance >= $min )
                $new_role = $role;
        }

        // Change users role if we have one
        if ( $new_role !== false ){
            if(rehub_option('rh_award_type_mycred') ==1 && function_exists('bp_get_member_type')){
                $roles = bp_get_member_type($user_id, false);
                if(!empty($roles) && is_array($roles)){
                    if (!in_array( $new_role, (array) $roles)){
                        bp_set_member_type( $user_id, $new_role );
                    }                     
                }else{
                    bp_set_member_type( $user_id, $new_role );
                } 
            }else{
                $wp_user_object = new WP_User($user_id);
                if(empty($wp_user_object)) return;
                if (!in_array( $new_role, (array) $wp_user_object->roles )){
                    $wp_user_object->add_role($new_role);
                }                
            }
        }
        return $reply;
    }
}

if (!function_exists('rh_bp_show_vendor_in_loop')){
    function rh_bp_show_vendor_in_loop ($vendor_id){
        $out = '';
        if (defined('wcv_plugin_dir')){
            if(WCV_Vendors::is_vendor($vendor_id)){
                $out .='<div class="store_member_in_m_loop"><span class="store_member_in_m_loop_l">'.__('Owner of shop:', 'rehub-theme').'</span> ';
                $out .='<a href="'.WCV_Vendors::get_vendor_shop_page( $vendor_id).'" class="store_member_in_m_loop_a">'.get_user_meta( $vendor_id, 'pv_shop_name', true ).'</a>';
                $out .='</div>';                
            }
        }
        elseif (defined('WCFMmp_TOKEN')){
            if(wcfm_is_vendor($vendor_id)){
                $out .='<div class="store_member_in_m_loop"><span class="store_member_in_m_loop_l">'.__('Owner of shop:', 'rehub-theme').'</span> ';
                $out .='<a href="'.wcfmmp_get_store_url( $vendor_id).'" class="store_member_in_m_loop_a">'.get_user_meta( $vendor_id, 'store_name', true ).'</a>';
                $out .='</div>';                
            }
        }        
        elseif ( class_exists( 'WeDevs_Dokan' ) ){
            $sold_by = dokan_is_user_seller( $vendor_id );
            if ($sold_by){
                $store_info = dokan_get_store_info( $vendor_id );
                $out .='<div class="store_member_in_m_loop"><span class="store_member_in_m_loop_l">'.__('Owner of shop:', 'rehub-theme').'</span> ';
                $out .='<a href="'.dokan_get_store_url( $vendor_id ).'" class="store_member_in_m_loop_a">'.esc_html( $store_info['store_name'] ).'</a>';
                $out .='</div>';                
            }
        }       
        return $out;
    }
}

//Tutor LMS
if(function_exists('tutor_utils')){
	add_action('tutor_course/single/before/content', 'rh_add_post_class_tutor_before');
	add_action('tutor_course/single/after/content', 'rh_add_post_class_tutor_after');
	add_action('tutor_course/single/excerpt/before', 'rh_add_post_class_tutor_before');
	add_action('tutor_course/single/excerpt/after', 'rh_add_post_class_tutor_after');
	function rh_add_post_class_tutor_before(){
		echo '<div class="post">';
	}
	function rh_add_post_class_tutor_after(){
		echo '</div>';
	}
	add_filter('tutor_dashboard/instructor_nav_items', 'rh_tutor_links_dashboard');
	function rh_tutor_links_dashboard($links){
		$fclink = rehub_option('userlogin_submit_page');
		$fclabel = rehub_option('userlogin_submit_page_label');
		if($fclink && $fclabel){
			$links['rhf_add_link'] = array('title' => esc_attr($fclabel), 'auth_cap' => tutor()->instructor_role, 'url' => esc_url($fclink));
		}
		$fslink = rehub_option('userlogin_edit_page');
		$fslabel = rehub_option('userlogin_edit_page_label');
		if($fslink && $fslabel){
			$links['rhs_add_link'] = array('title' => esc_attr($fslabel), 'auth_cap' => tutor()->instructor_role, 'url' => esc_url($fslink));
		}
		return $links;
	}
}