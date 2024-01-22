<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

//////////////////////////////////////////////////////////////////////////////
// Hot meter and thumbs function. It works like + and - and have overall score
//////////////////////////////////////////////////////////////////////////////

$rehub_max_temp = (rehub_option('hot_max')) ? rehub_option('hot_max') : 10;
$rehub_min_temp = (rehub_option('hot_min')) ? rehub_option('hot_min') : -10;
define('REHUB_MAX_TEMP', $rehub_max_temp); //define maximum temperature meter
define('REHUB_MIN_TEMP', $rehub_min_temp); //define minimum temperature meter under minus

add_action( 'wp_ajax_nopriv_hot-count', 'hot_count' );
add_action( 'wp_ajax_hot-count', 'hot_count' );

if (!function_exists('hot_count')){
function hot_count() {
    $nonce = sanitize_text_field($_POST['hotnonce']);
    if ( ! wp_verify_nonce( $nonce, 'hotnonce' ) )
        die ( 'Nope!' );
    
    if ( isset( $_POST['hot_count'] ) ) {   
        $post_id = intval($_POST['post_id']); // post id
        $posthot = get_post($post_id);
        $postauthor = $posthot->post_author; 
        $post_hot_count = get_post_meta( $post_id, "post_hot_count", true ); // post like count  
        $overall_post_likes = get_user_meta( $postauthor, "overall_post_likes", true ); // get overall post likes of user   
        if ( is_user_logged_in() ) { // user is logged in
            global $current_user;
            $user_id = $current_user->ID; // current user
            $meta_POSTS = get_user_meta( $user_id, "_liked_posts" ); // post ids from user meta
            $meta_USERS = get_post_meta( $post_id, "_user_liked" ); // user ids from post meta
            $liked_POSTS = ""; // setup array variable
            $liked_USERS = ""; // setup array variable          
            if ( count( $meta_POSTS ) != 0 ) { // meta exists, set up values
                $liked_POSTS = $meta_POSTS[0];
            }           
            if ( !is_array( $liked_POSTS ) ) // make array just in case
                $liked_POSTS = array();             
            if ( count( $meta_USERS ) != 0 ) { // meta exists, set up values
                $liked_USERS = $meta_USERS[0];
            }       
            if ( !is_array( $liked_USERS ) ) // make array just in case
                $liked_USERS = array();             
            $liked_POSTS['post-'.$post_id] = $post_id; // Add post id to user meta array
            $liked_USERS['user-'.$user_id] = $user_id; // add user id to post meta array
            $user_likes = count( $liked_POSTS ); // count user likes

            if ($_POST['hot_count'] =='hot') {              
                if ( !AlreadyHot( $post_id ) ) {
                    update_post_meta( $post_id, "post_hot_count", ++$post_hot_count ); // +1 count post meta
                    update_post_meta( $post_id, "_user_liked", $liked_USERS ); // Add user ID to post meta
                    update_user_meta( $user_id, "_liked_posts", $liked_POSTS ); // Add post ID to user meta
                    update_user_meta( $user_id, "_user_like_count", $user_likes ); // +1 count user meta    
                    update_user_meta( $postauthor, "overall_post_likes", ++$overall_post_likes ); // +1 count to post author overall likes             
                } 
                else {
                   // update_post_meta( $post_id, "post_hot_count", $post_hot_count+2 );
                }       
            }
            if ($_POST['hot_count'] =='cold') {
                if ( !AlreadyHot( $post_id ) ) {
                    update_post_meta( $post_id, "post_hot_count", --$post_hot_count ); // -1 count post meta
                    update_post_meta( $post_id, "_user_liked", $liked_USERS ); // Add user ID to post meta
                    update_user_meta( $user_id, "_liked_posts", $liked_POSTS ); // Add post ID to user meta
                    update_user_meta( $user_id, "_user_like_count", $user_likes ); // -1 count user meta   
                    update_user_meta( $postauthor, "overall_post_likes", --$overall_post_likes ); // -1 count to post author overall likes                 
                } 
                else {
                    if(!empty($_POST['heart'])){
                        update_post_meta( $post_id, "post_hot_count", $post_hot_count-1 );
                        update_user_meta( $postauthor, "overall_post_likes", --$overall_post_likes );
                        update_user_meta( $user_id, "_user_like_count", $user_likes-1 );
                        
                        $userkeyip = 'user-'.$user_id;
                        unset($liked_USERS[$userkeyip]);
                        update_post_meta( $post_id, "_user_liked", $liked_USERS );

                        $postkeyip = 'post-'.$post_id;
                        unset($liked_POSTS[$postkeyip]);
                        update_user_meta( $user_id, "_liked_posts", $liked_POSTS );                        
                    }
                }                                       
            }           
            
        } else { // user is not logged in (anonymous)
            $ip = rehub_get_ip(); // user IP address
            $postidarray = array();
            $guest_likes_transient = get_transient('re_guest_likes_' . $ip);
            $meta_IPS = get_post_meta( $post_id, "_user_IP" ); // stored IP addresses
            $liked_IPS = ""; // set up array variable           
            if ( count( $meta_IPS ) != 0 ) { // meta exists, set up values
                $liked_IPS = $meta_IPS[0];
            }   
            if ( !is_array( $liked_IPS ) ) // make array just in case
                $liked_IPS = array();               
            if ( !in_array( $ip, $liked_IPS ) ) // if IP not in array
                $liked_IPS['ip-'.$ip] = $ip; // add IP to array 

            if ($_POST['hot_count'] =='hot') {              
                if ( !AlreadyHot( $post_id ) ) {
                    update_post_meta( $post_id, "post_hot_count", ++$post_hot_count ); // +1 count post meta
                    update_post_meta( $post_id, "_user_IP", $liked_IPS ); // Add user IP to post meta  
                    update_user_meta( $postauthor, "overall_post_likes", ++$overall_post_likes ); // +1 count to post author overall likes   
                    if(empty($guest_likes_transient)) {
                        $postidarray[] = $post_id;
                        set_transient('re_guest_likes_' . $ip, $postidarray, 30 * DAY_IN_SECONDS);
                    } else {
                        if(is_array($guest_likes_transient)){
                            $guest_likes_transient[] = $post_id;
                            set_transient('re_guest_likes_' . $ip, $guest_likes_transient, 30 * DAY_IN_SECONDS);
                        }                   
                    }                                  
                } 
                else {
                    //update_post_meta( $post_id, "post_hot_count", $post_hot_count+2 );
                }       
            }
            if ($_POST['hot_count'] =='cold') {
                if ( !AlreadyHot( $post_id ) ) {
                    update_post_meta( $post_id, "post_hot_count", --$post_hot_count ); // -1 count post meta
                    update_post_meta( $post_id, "_user_IP", $liked_IPS ); // Add user IP to post meta   
                    update_user_meta( $postauthor, "overall_post_likes", --$overall_post_likes ); // -1 count to post author overall likes                    
                } 
                else {
                    if(!empty($_POST['heart'])){
                        update_post_meta( $post_id, "post_hot_count", $post_hot_count-1 );
                        update_user_meta( $postauthor, "overall_post_likes", --$overall_post_likes );   

                        $keyip = 'ip-'.$ip;
                        unset($meta_IPS[$keyip]);
                        update_post_meta( $post_id, "_user_IP", $meta_IPS );
                        $keydelete = array_search($post_id, $guest_likes_transient);
                        unset($guest_likes_transient[$keydelete]);
                        set_transient('re_guest_likes_' . $ip, $guest_likes_transient, 30 * DAY_IN_SECONDS);

                    }
                }                                       
            }
        }
        do_action('rh_overall_post_likes_add');
    }
    exit;
}
}

if (!function_exists('AlreadyHot')){
function AlreadyHot( $post_id ) { // test if user liked before
    
    if ( is_user_logged_in() ) { // user is logged in
        global $current_user;
        $user_id = $current_user->ID; // current user
        $meta_USERS = get_post_meta( $post_id, "_user_liked" ); // user ids from post meta
        $liked_USERS = ""; // set up array variable     
        if ( is_numeric($meta_USERS) && count( $meta_USERS ) != 0 ) { // meta exists, set up values
            $liked_USERS = $meta_USERS[0];
        }       
        if( !is_array( $liked_USERS ) ) // make array just in case
            $liked_USERS = array();         
        if ( in_array( $user_id, $liked_USERS ) ) { // True if User ID in array
            return true;
        }
        return false;       
    } 
    else { // user is anonymous, use IP address for voting  
        $meta_IPS = get_post_meta($post_id, "_user_IP"); // get previously voted IP address
        $ip = rehub_get_ip(); // Retrieve current user IP
        $liked_IPS = ""; // set up array variable
        if ( is_array($meta_IPS) && count( $meta_IPS ) != 0 ) { // meta exists, set up values
            $liked_IPS = $meta_IPS[0];
        }
        if ( !is_array( $liked_IPS ) ) // make array just in case
            $liked_IPS = array();
        if ( in_array( $ip, $liked_IPS ) ) { // True is IP in array
            return true;
        }
        return false;
    }   
}
}

add_action( 'wp_ajax_nopriv_rhwishlist', 'rh_wishlist_fn' );
add_action( 'wp_ajax_rhwishlist', 'rh_wishlist_fn' );

if (!function_exists('rh_wishlist_fn')){
function rh_wishlist_fn() {
    $nonce = sanitize_text_field($_POST['wishnonce']);
    if ( ! wp_verify_nonce( $nonce, 'wishnonce' ) )
        die ( 'Nope!' );
    
    if ( isset( $_POST['wish_count'] ) ) {   
        $post_id = intval($_POST['post_id']); // post id
        $posthot = get_post($post_id);
        $postauthor = $posthot->post_author; 
        $post_wish_count = get_post_meta( $post_id, "post_wish_count", true ); // post like count  
        $overall_post_wishes = get_user_meta( $postauthor, "overall_post_wishes", true ); // get overall post likes of user   
        if ( is_user_logged_in() ) { // user is logged in
            global $current_user;
            $user_id = $current_user->ID; // current user
            $meta_POSTS = get_user_meta( $user_id, "_wished_posts" ); // post ids from user meta
            $meta_USERS = get_post_meta( $post_id, "_user_wished" ); // user ids from post meta
            $liked_POSTS = ""; // setup array variable
            $liked_USERS = ""; // setup array variable          
            if ( count( $meta_POSTS ) != 0 ) { // meta exists, set up values
                $liked_POSTS = $meta_POSTS[0];
            }           
            if ( !is_array( $liked_POSTS ) ) // make array just in case
                $liked_POSTS = array();             
            if ( count( $meta_USERS ) != 0 ) { // meta exists, set up values
                $liked_USERS = $meta_USERS[0];
            }       
            if ( !is_array( $liked_USERS ) ) // make array just in case
                $liked_USERS = array();             
            $liked_POSTS['post-'.$post_id] = $post_id; // Add post id to user meta array
            $liked_USERS['user-'.$user_id] = $user_id; // add user id to post meta array
            $user_likes = count( $liked_POSTS ); // count user likes

            if ($_POST['wish_count'] =='add') {              
                if ( !AlreadyWish( $post_id ) ) {
                    update_post_meta( $post_id, "post_wish_count", ++$post_wish_count ); // +1 count post meta
                    update_post_meta( $post_id, "_user_wished", $liked_USERS ); // Add user ID to post meta
                    update_user_meta( $user_id, "_wished_posts", $liked_POSTS ); // Add post ID to user meta
                    update_user_meta( $user_id, "_user_wish_count", $user_likes ); // +1 count user meta    
                    update_user_meta( $postauthor, "overall_post_wishes", ++$overall_post_wishes ); // +1 count to post author overall likes             
                } 
                else {
                   // update_post_meta( $post_id, "post_wish_count", $post_wish_count+2 );
                }       
            }
            if ($_POST['wish_count'] =='remove') {
                update_post_meta( $post_id, "post_wish_count", $post_wish_count-1 );
                update_user_meta( $postauthor, "overall_post_wishes", --$overall_post_wishes );
                update_user_meta( $user_id, "_user_wish_count", $user_likes-1 );
                
                $userkeyip = 'user-'.$user_id;
                unset($liked_USERS[$userkeyip]);
                update_post_meta( $post_id, "_user_wished", $liked_USERS );

                $postkeyip = 'post-'.$post_id;
                unset($liked_POSTS[$postkeyip]);
                update_user_meta( $user_id, "_wished_posts", $liked_POSTS );                                           
            }           
            
        } else { // user is not logged in (anonymous)
            $ip = rehub_get_ip(); // user IP address
            $postidarray = array();
            $guest_wishes_transients = get_transient('re_guest_wishes_' . $ip);
            $meta_IPS = get_post_meta( $post_id, "_userwish_IP" ); // stored IP addresses
            $liked_IPS = ""; // set up array variable           
            if ( count( $meta_IPS ) != 0 ) { // meta exists, set up values
                $liked_IPS = $meta_IPS[0];
            }   
            if ( !is_array( $liked_IPS ) ) // make array just in case
                $liked_IPS = array();               
            if ( !in_array( $ip, $liked_IPS ) ) // if IP not in array
                $liked_IPS['ip-'.$ip] = $ip; // add IP to array 

            if ($_POST['wish_count'] =='add') {              
                if ( !AlreadyWish( $post_id ) ) {
                    update_post_meta( $post_id, "post_wish_count", ++$post_wish_count ); // +1 count post meta
                    update_post_meta( $post_id, "_userwish_IP", $liked_IPS ); // Add user IP to post meta  
                    update_user_meta( $postauthor, "overall_post_wishes", ++$overall_post_wishes ); // +1 count to post author overall likes   
                    if(empty($guest_wishes_transients)) {
                        $postidarray[] = $post_id;
                        set_transient('re_guest_wishes_' . $ip, $postidarray, 30 * DAY_IN_SECONDS);
                    } else {
                        if(is_array($guest_wishes_transients)){
                            $guest_wishes_transients[] = $post_id;
                            set_transient('re_guest_wishes_' . $ip, $guest_wishes_transients, 30 * DAY_IN_SECONDS);
                        }                   
                    }                                  
                } 
                else {
                    //update_post_meta( $post_id, "post_wish_count", $post_wish_count+2 );
                }       
            }
            if ($_POST['wish_count'] =='remove') {
                update_post_meta( $post_id, "post_wish_count", $post_wish_count-1 );
                update_user_meta( $postauthor, "overall_post_wishes", --$overall_post_wishes );   

                $keyip = 'ip-'.$ip;
                unset($meta_IPS[$keyip]);
                update_post_meta( $post_id, "_userwish_IP", $meta_IPS );
                $keydelete = array_search($post_id, $guest_wishes_transients);
                unset($guest_wishes_transients[$keydelete]);
                set_transient('re_guest_wishes_' . $ip, $guest_wishes_transients, 30 * DAY_IN_SECONDS);
                                      
            }
        }
        do_action('rh_overall_post_wishes_add');
    }
    exit;
}
}

if (!function_exists('AlreadyWish')){
function AlreadyWish( $post_id ) { // test if user liked before
    
    if ( is_user_logged_in() ) { // user is logged in
        global $current_user;
        $user_id = $current_user->ID; // current user
        $meta_USERS = get_post_meta( $post_id, "_user_wished" ); // user ids from post meta
        $liked_USERS = ""; // set up array variable     
        if ( !empty($meta_USERS) && count( $meta_USERS ) != 0 ) { // meta exists, set up values
            $liked_USERS = $meta_USERS[0];
        }       
        if( !is_array( $liked_USERS ) ) // make array just in case
            $liked_USERS = array();         
        if ( in_array( $user_id, $liked_USERS ) ) { // True if User ID in array
            return true;
        }
        return false;       
    } 
    else { // user is anonymous, use IP address for voting  
        $meta_IPS = get_post_meta($post_id, "_userwish_IP"); // get previously voted IP address
        $ip = rehub_get_ip(); // Retrieve current user IP
        $liked_IPS = ""; // set up array variable
        if ( is_array($meta_IPS) && count( $meta_IPS ) != 0 ) { // meta exists, set up values
            $liked_IPS = $meta_IPS[0];
        }
        if ( !is_array( $liked_IPS ) ) // make array just in case
            $liked_IPS = array();
        if ( in_array( $ip, $liked_IPS ) ) { // True is IP in array
            return true;
        }
        return false;
    }   
}
}

if (!function_exists('getHotLike')){
function getHotLike( $post_id ) {
    if (rehub_option('exclude_hotmeter') =='1') {
        return false;
    }
    wp_enqueue_script('rhhotcount');   
    $max_temp= REHUB_MAX_TEMP; //max temperature
    $min_temp= REHUB_MIN_TEMP; //min temperature    
    $like_count = get_post_meta( $post_id, "post_hot_count", true ); // get post likes
    if ( ( !$like_count ) || ( $like_count && $like_count == "0" ) ) { // no votes, set up empty variable
        $temp = '0';
    } elseif ( $like_count && $like_count != "0" ) { // there are votes!
        $temp = esc_attr( $like_count );
    }
    if ($temp >= $max_temp){
        $icontemp = '<i class="rhicon rhi-fire"></i> ';
    }
    elseif ($temp <= $min_temp) {
        $icontemp = '<i class="rhicon rhi-snowflake"></i> ';
    }
    else {
        $icontemp = '';
    }
    $onlyuser_class = rehub_option('thumb_only_users');
    $loginurl = '';
    if($onlyuser_class == 1){
        if (is_user_logged_in()){
            $onlyuser_class = '';
        }
        else{
            if(rehub_option('custom_login_url')){
                $urllogin = rehub_option('custom_login_url');
                $loginurl = ' data-type="url" data-customurl="'.esc_url($urllogin).'"';
            }else{
                $loginurl = '';
            }
            $onlyuser_class = ' act-rehub-login-popup restrict_for_guests';
        }
    }else{
        $onlyuser_class = '';
    }    
    $output = '<div class="hotmeter_wrap flexbasisclear"><div class="hotmeter"><span class="table_cell_hot first_cell"><span id="temperatur'.$post_id.'" class="temperatur';
    if ($temp < 0) :
        $output .= ' cold_temp';
    endif;
    $output .= '">'.$icontemp.$temp.'<span class="gradus_icon"></span></span></span> ';
    $output .= '<span class="table_cell_hot cell_minus_hot">';
    if ( AlreadyHot( $post_id ) ) { // already liked, set up unlike addon
        $output .= '<button class="hotcountbtn hotminus alreadyhot" alt="'.__('Vote down', 'rehub-theme').'" title="'.__('Vote down', 'rehub-theme').'" data-post_id="'.$post_id.'" data-informer="'.$temp.'"></button>';
    } else { // normal like button
        $output .= '<button class="hotcountbtn hotminus'.$onlyuser_class.'"'.$loginurl.' alt="'.__('Vote down', 'rehub-theme').'" title="'.__('Vote down', 'rehub-theme').'" data-post_id="'.$post_id.'" data-informer="'.$temp.'"></button>';
    }
    $output .= '</span><span class="table_cell_hot cell_plus_hot">';
    if ( AlreadyHot( $post_id ) ) { // already liked, set up unlike addon
        $output .= '<button class="hotcountbtn hotplus alreadyhot" alt="'.__('Vote up', 'rehub-theme').'" title="'.__('Vote up', 'rehub-theme').'" data-post_id="'.$post_id.'" data-informer="'.$temp.'"></button>';
    } else { // normal like button
        $output .= '<button class="hotcountbtn hotplus'.$onlyuser_class.'"'.$loginurl.' alt="'.__('Vote up', 'rehub-theme').'" title="'.__('Vote up', 'rehub-theme').'" data-post_id="'.$post_id.'" data-informer="'.$temp.'"></button>';
    }
    $output .= '</span>';
    $output .= '<span id="textinfo'.$post_id.'" class="textinfo table_cell_hot"></span>';

    $output .= '<div class="table_cell_hot fullwidth_cell">';
    if ($temp >= $max_temp) :
        $temp = $max_temp;
    elseif ($temp <= $min_temp) :
        $temp = $min_temp;
    endif;
    $output .= '<div id="fonscale'.$post_id.'" class="fonscale">';      
    $output .= '<div id="scaleperc'.$post_id.'" class="scaleperc';
    if ($temp < 0) :
        $output .= ' cold_bar';
    endif;
    $output .= '" style="width:';
    if ($temp >= 0) :
        $output .= ''.($temp / $max_temp * 100).'%">';
    else:
        $output .= ''.($temp / $min_temp * 100).'%">';
    endif;
    $output .= '</div></div></div></div></div>';    

    return $output;
}
}

if (!function_exists('getHotIconfire')){
function getHotIconfire( $post_id, $wish = false ) {
    $max_temp= REHUB_MAX_TEMP; //max temperature  
    if ($wish == true){
        if(rehub_option('wishlist_disable')){
            return;
        }         
        $like_count = get_post_meta( $post_id, "post_wish_count", true );
    }else{
        if(rehub_option('hotmeter_disable')){
            return;
        }        
        $like_count = get_post_meta( $post_id, "post_hot_count", true );
    }
    if (!empty($like_count) && $like_count > $max_temp){
        return '<i class="rhicon rhi-fire"></i> ';
    }    
}
}

if (!function_exists('getHotIconclass')){
function getHotIconclass( $post_id, $wish = false ) {
    $max_temp= REHUB_MAX_TEMP; //max temperature  
    if ($wish == true){
        if(rehub_option('wishlist_disable')){
            return;
        }        
        $like_count = get_post_meta( $post_id, "post_wish_count", true );
    }else{
        if(rehub_option('hotmeter_disable')){
            return;
        }
        $like_count = get_post_meta( $post_id, "post_hot_count", true );
    }
    if (!empty($like_count) && $like_count > $max_temp){
        return 'hoticonfireclass';
    }    
}
}

if (!function_exists('getHotLikeTitle')){
function getHotLikeTitle( $post_id, $wish = false ) {

    if ($wish == true){
        if(rehub_option('wishlist_disable')){
            return;
        } 
        $like_count = get_post_meta( $post_id, "post_wish_count", true );
    }else{
        if(rehub_option('hotmeter_disable')){
            return;
        }
        $like_count = get_post_meta( $post_id, "post_hot_count", true );
    }
    if ( ( !$like_count ) || ( $like_count && $like_count == "0" ) ) { // no votes, set up empty variable
        $temp = '0';
    } elseif ( $like_count && $like_count != "0" ) { // there are votes!
        $temp = esc_attr( $like_count );
    }
    $output = '<span id="temperatur'.$post_id.'" class="temperatur';
    if ($temp < 0) :
        $output .= ' cold_temp';
    endif;
    $output .= '">'.$temp.'<span class="gradus_icon"></span></span> ';
    return $output;
}
}

if (!function_exists('getHotThumb')){
function getHotThumb( $post_id, $comment_meta = false, $deal_score = false, $onlyone = false, $wishlistadd = '',$wishlistadded = '', $wishlistremoved = '' ) {  
    wp_enqueue_script('rhhotcount');
    if($onlyone == true){
        return RH_get_wishlist($post_id, $wishlistadd = '',$wishlistadded = '', $wishlistremoved = '');
    }  
    $like_count = get_post_meta( $post_id, "post_hot_count", true ); // get post likes
    if ( ( !$like_count ) || ( $like_count && $like_count == "0" ) ) { // no votes, set up empty variable
        $temp = '0';
    } elseif ( $like_count && $like_count != "0" ) { // there are votes!
        $temp = esc_attr( $like_count );
    }
    $deal_score_wrap = ($deal_score == true) ? ' dealScoreWrap': '';
    $onlyonewrap = ($onlyone == true) ? ' heart_thumb_wrap text-center' : '';
    $onlyheartclass = ($onlyone == true) ? ' heartplus' : '';
    $alreadyclass = ( AlreadyHot( $post_id ) ) ? ' alreadyhot' : '';
    $output = '<div class="post_thumbs_wrap'.$deal_score_wrap.$onlyonewrap.'">';
    if ($comment_meta == true){
        $output .='<span class="post_thumbs_comm"><span>'.get_comments_number().'</span></span>';
    }
    if ($deal_score == true) {
        $output .= '<div class="dealScore">';
        $output .='<span class="label">'.__('Deal Score', 'rehub-theme').'</span>';
        $output .='<span id="thumbscount'.$post_id.'" class="thumbscount';
        if ($temp < 0) :
            $output .= ' cold_temp';
        endif;          
        $output .= '">';
        if ($temp > 0) {
            $output .= '+';
        }        
        $output .= $temp.'</span></div>';        
    }   
    $onlyuser_class = rehub_option('thumb_only_users');
    $loginurl = '';
    if($onlyuser_class == 1){
        if (is_user_logged_in()){
            $onlyuser_class = '';
        }
        else{
            if(rehub_option('custom_login_url')){
                $urllogin = rehub_option('custom_login_url');
                $loginurl = ' data-type="url" data-customurl="'.esc_url($urllogin).'"';
            }            
            $onlyuser_class = ' act-rehub-login-popup restrict_for_guests';
        }
    }else{
        $onlyuser_class = '';
    } 
    $outputtext = $wishlistpage = $wishlisted = '';
    if (rehub_option('wishlistpage') !=''){
        $wishlistpage = esc_url(get_the_permalink((int)rehub_option('wishlistpage')));
        $wishlisted = ' wishlisted';
    }
    if ($wishlistadd) {
        $outputtext .= '<span class="ml5 rtlmr5 wishaddwrap" id="wishadd'.$post_id.'">'; 
        $outputtext .= $wishlistadd.'</span>';        
    }  
    if ($wishlistadded) {
        $outputtext .= '<span class="ml5 rtlmr5 wishaddedwrap" id="wishadded'.$post_id.'">'; 
        $outputtext .= $wishlistadded.'</span>';        
    } 
    if ($wishlistremoved) {
        $outputtext .= '<span class="ml5 rtlmr5 wishremovedwrap" id="wishremoved'.$post_id.'">'; 
        $outputtext .= $wishlistremoved.'</span> ';        
    }     
    $output .= '<span class="table_cell_thumbs">';
        if ($onlyone == false) {
            if ( AlreadyHot( $post_id ) ) { // already liked, set up unlike addon
                $output .= '<span class="thumbminus hotcountbtn alreadyhot" title="'.__('Already voted', 'rehub-theme').'" data-post_id="'.$post_id.'" data-informer="'.$temp.'">'.$outputtext.'</span>';
            } else { 
                $output .= '<span class="hotcountbtn thumbminus'.$onlyuser_class.'"'.$loginurl.' title="'.__('Vote down', 'rehub-theme').'" data-post_id="'.$post_id.'" data-informer="'.$temp.'">'.$outputtext.'</span>';
            }
        }    
        if ( AlreadyHot( $post_id ) ) { // already liked, set up unlike addon
            $output .= '<span class="hotcountbtn thumbplus alreadyhot'.$onlyheartclass.$wishlisted.'" data-post_id="'.$post_id.'" title="'.__('Already voted', 'rehub-theme').'" data-informer="'.$temp.'" data-wishlink="'.$wishlistpage.'">'.$outputtext.'</span>';
        } else {
            $output .= '<span class="hotcountbtn thumbplus'.$onlyheartclass.$onlyuser_class.'"'.$loginurl.' data-post_id="'.$post_id.'" title="'.__('Vote up', 'rehub-theme').'" data-informer="'.$temp.'">'.$outputtext.'</span>';
        }   
    $output .= '</span>';
    if ($deal_score == false) {
        $output .= '<span id="thumbscount'.$post_id.'" class="thumbscount';
        if ($temp < 0) :
            $output .= ' cold_temp';
        endif;  
        $output .= '">'.$temp.'</span> ';        
    }        
    $output .= '</div>';    

    return $output;
}
}

if (!function_exists('RH_get_wishlist')){
function RH_get_wishlist( $post_id, $wishlistadd = '',$wishlistadded = '', $wishlistremoved = '' ) { 
    if(rehub_option('wishlist_disable') == 1){return;}   
    wp_enqueue_script('rhwishcount');
    $like_count = get_post_meta( $post_id, "post_wish_count", true ); // get post likes
    if ( ( !$like_count ) || ( $like_count && $like_count == "0" ) ) { // no votes, set up empty variable
        $temp = '0';
    } elseif ( $like_count && $like_count != "0" ) { // there are votes!
        $temp = esc_attr( $like_count );
    }
    $alreadyclass = ( AlreadyWish( $post_id ) ) ? ' alreadyhot' : '';
    $output = '<div class="heart_thumb_wrap text-center">';
    $onlyuser_class = rehub_option('wish_only_users');
    $loginurl = '';
    if($onlyuser_class == 1){
        if (is_user_logged_in()){
            $onlyuser_class = '';
        }
        else{
            if(rehub_option('custom_login_url')){
                $urllogin = rehub_option('custom_login_url');
                $loginurl = ' data-type="url" data-customurl="'.esc_url($urllogin).'"';
            }            
            $onlyuser_class = ' act-rehub-login-popup restrict_for_guests';
        }
    }else{
        $onlyuser_class = '';
    } 
    $outputtext = $wishlistpage = $wishlisted = '';
    if (rehub_option('wishlistpage') !=''){
        $wishlistpage = esc_url(get_the_permalink((int)rehub_option('wishlistpage')));
        $wishlisted = ' wishlisted';
    }
    if ($wishlistadd) {
        $outputtext .= '<span class="ml5 rtlmr5 wishaddwrap" id="wishadd'.$post_id.'">'; 
        $outputtext .= $wishlistadd.'</span>';        
    }  
    if ($wishlistadded) {
        $outputtext .= '<span class="ml5 rtlmr5 wishaddedwrap" id="wishadded'.$post_id.'">'; 
        $outputtext .= $wishlistadded.'</span>';        
    } 
    if ($wishlistremoved) {
        $outputtext .= '<span class="ml5 rtlmr5 wishremovedwrap" id="wishremoved'.$post_id.'">'; 
        $outputtext .= $wishlistremoved.'</span> ';        
    }     
    $output .= '<span class="flowhidden cell_wishlist">';   
        if ( AlreadyWish( $post_id ) ) { // already liked, set up unlike addon
            $output .= '<span class="alreadywish heartplus'.$wishlisted.'" data-post_id="'.$post_id.'" data-informer="'.$temp.'" data-wishlink="'.$wishlistpage.'">'.$outputtext.'</span>';
        } else {
            $output .= '<span class="heartplus'.$onlyuser_class.'"'.$loginurl.' data-post_id="'.$post_id.'" data-informer="'.$temp.'">'.$outputtext.'</span>';
        }   
    $output .= '</span>';
    $output .= '<span data-wishcount="'.$temp.'" id="wishcount'.$post_id.'" class="thumbscount'; 
    $output .= '">'.$temp.'</span> ';                
    $output .= '</div>';    

    return $output;
}
}

if(!function_exists('rh_hot_shortcode')){
    function rh_hot_shortcode($atts , $content = null ){
        $atts = shortcode_atts(
            array(
                'post_id' => '',
                'comment_meta' => false,
                'deal_score' => false,
                'onlyone' => false,
                'wishlistadd' => '',
                'wishlistadded' => '',
                'wishlistremoved' => '',
                'as_btn'=> '',
                'hotline' => ''                           
            ),
            $atts,
            'getHotThumb'
        );
        extract($atts);
        if(empty($post_id)){
            global $post;
            $post_id = $post->ID;
        } 
        $out = '';
        if($as_btn){
            $out .= '<span class="woo-button-actions-area">';
        }
        if($hotline){
            $out .= getHotLike( (int)$post_id); 
        }else{
            $out .= getHotThumb( (int)$post_id, $comment_meta, $deal_score, $onlyone, $wishlistadd,$wishlistadded, $wishlistremoved); 
        }
        if($as_btn){
            $out .= '</span>';
        }  
        return $out;           
    }
}

if(!function_exists('rh_wishlist_shortcode')){
    function rh_wishlist_shortcode($atts , $content = null ){
        $atts = shortcode_atts(
            array(
                'post_id' => '',
                'wishlistadd' => '',
                'wishlistadded' => '',
                'wishlistremoved' => '',
                'as_btn'=> ''                            
            ),
            $atts,
            'RH_wishlist'
        );
        extract($atts);
        if(empty($post_id)){
            global $post;
            $post_id = $post->ID;
        } 
        $out = '';
        if($as_btn){
            $out .= '<span class="woo-button-actions-area">';
        }
        $out .= RH_get_wishlist( (int)$post_id, $wishlistadd,$wishlistadded, $wishlistremoved); 
        if($as_btn){
            $out .= '</span>';
        }  
        return $out;           
    }
}

if (!function_exists('RhGetUserFavorites')){
function RhGetUserFavorites($atts, $content = null ) {
    extract( shortcode_atts( array(
        'icon' => 'no',
      ),
      $atts ) ); 
    ob_start();   
    $wishlistids = $likedposts = '';
    if(!empty($_GET['wishlistids'])){
        $wishlistids = explode(',', esc_html($_GET['wishlistids']));
    }
    else{
        if ( is_user_logged_in() ) { // user is logged in
            global $current_user;
            $user_id = $current_user->ID; // current user
            $likedposts = get_user_meta( $user_id, "_wished_posts", true);
        }
        else{
            $ip = rehub_get_ip(); // user IP address
            $likedposts = get_transient('re_guest_wishes_' . $ip);
        } 
        $wishlistids = $likedposts;      
    }
    
    if (!empty($wishlistids)){

        $wishlistids = array_reverse($wishlistids);
        if($icon == 'yes'){
            echo '<span class="rhicon rhi-heart position-relative">';
                echo '<span class="rh-icon-notice rehub-main-color-bg">'.count($wishlistids).'</span>';
            echo '</span>';
        }else{
            foreach ($wishlistids as $wishlistid) {
                if ( 'publish' != get_post_status($wishlistid)) {
                    if(!empty($user_id)){
                        $postkeyip = 'post-'.$wishlistid;
                        unset($likedposts[$postkeyip]);
                        update_user_meta( $user_id, "_wished_posts", $likedposts );
                    }else{
                        $keydelete = array_search($wishlistid, $likedposts);
                        unset($likedposts[$keydelete]);
                        set_transient('re_guest_wishes_' . $ip, $likedposts, 30 * DAY_IN_SECONDS);                        
                    }
                }
            }            
            $args = array( 
                'post_status' => 'publish', 
                'ignore_sticky_posts' => 1, 
                'orderby' => 'post__in',
                'post__in' => $wishlistids,
                'posts_per_page'=> -1,
                'post_type' => 'any'
            );
            $wp_query = new WP_Query($args);  
            if ($wp_query->have_posts()){
                echo '<div class="tabledisplay re-favorites-posts mb20">';
                echo '<style scoped>.re-favorites-posts .heartplus.alreadywish:before, .re-favorites-posts .heart_thumb_wrap:hover .heartplus.alreadywish:before{content: "\f00d";}
                    .re-favorites-posts{border-collapse: collapse;}
                    .re-favorites-posts .yith_woo_chart{display: none;}
                    .re-favorites-posts .price-woo-compare-chart{font-size: 15px; margin: 0}
                    .re-favorites-posts .priced_block .btn_offer_block, .re-favorites-posts .priced_block .rh_price_wrapper, .re-favorites-posts .rh_button_wrapper{display: block; float: none;margin-bottom: 10px;}
                    .re-favorites-posts img{ max-width: 50px;}
                    @media only screen and (max-width: 567px) {
                    .re-favorites-posts img{ max-width: 200px;}
                    .re-favorites-posts .rowdisplay{border: 1px solid #ddd; margin: 0 0 15px 0; display: block;}
                    .re-favorites-posts .celldisplay{display: block; border: none; text-align: center; width: 100% !important; padding: 5px 15px !important}
                    }
                </style>';
                while ($wp_query->have_posts()) : $wp_query->the_post();
                global $post;
                $posttype = $post->post_type;
                   ?>
                    <div class="rowdisplay <?php echo ''.$posttype;?>">
                    <div class="celldisplay pt10 pr15 pb10 pl15 border-grey text-center" style="width: 30px">
                        <?php echo RH_get_wishlist($post->ID);?>
                    </div>
                    <div class="celldisplay pt10 pr15 pb10 pl15 border-grey text-center" style="width: 100px">
                        <a href="<?php echo get_the_permalink($post->ID);?>" target="_blank" class="font90">
                        <?php         
                            $image_id = get_post_thumbnail_id($post->ID);  
                            $image_url = wp_get_attachment_image_src($image_id,'full');
                            if(!empty($image_url)){
                                $aff_thumb = $image_url[0];
                            }else{$aff_thumb = '';}
                        ?>                    
                        <?php WPSM_image_resizer::show_static_resized_image(array('src'=> $aff_thumb, 'height'=> 100));?>
                        </a>
                    </div>
                    <div class="celldisplay pt10 pr15 pb10 pl15 border-grey">
                        <a href="<?php echo get_the_permalink($post->ID);?>" target="_blank">
                            <?php echo get_the_title($post->ID);?>
                        </a>
                        <?php if($posttype == 'product'):?>
                            <?php global $product;?>
                            <?php if ( ! $product->is_in_stock() ):?>
                                <div class="stock mt5 out-of-stock mb5"><?php esc_html_e('Out of Stock', 'rehub-theme');?></div>
                            <?php endif;?>
                        <?php endif;?>
                        <div class="mt5 wishcomparebtn">
                        <?php if((rehub_option('compare_page') || rehub_option('compare_multicats_textarea')) && $posttype == 'product') :?>           
                            <?php 
                                $cmp_btn_args = array(); 
                                $cmp_btn_args['class']= 'rhwoosinglecompare';
                                if(rehub_option('compare_woo_cats') != '') {
                                    $cmp_btn_args['woocats'] = esc_html(rehub_option('compare_woo_cats'));
                                }
                            ?>                                                  
                            <?php echo wpsm_comparison_button($cmp_btn_args); ?> 
                        <?php endif;?> 
                        </div>
                    </div>
                    <div class="celldisplay pt10 pr15 pb10 pl15 border-grey text-center" style="width: 200px">
                        <?php if ($posttype == 'product'):?>
                            <?php include(rh_locate_template('inc/top/woobtn.php')); ?>
                        <?php else:?>
                            <?php rehub_create_btn('yes') ;?> 
                        <?php endif;?>
                    </div>                
                    </div> 
                   <?php 
                endwhile;
                echo '</div>';                       
            } 
            wp_reset_query(); 
            if (function_exists('rehub_social_share')){
                echo '<div class="post_share">'.rehub_social_share('row', '', '', '', implode(',', $wishlistids)).'</div>';
            }
        }          
    }else{
        esc_html_e('There is nothing in your wishlist', 'rehub-theme');
    }
    $output = ob_get_contents();
    ob_end_clean();
    return $output;        
}
}

//////USER LIKES - USE IT FOR WC VENDORS//////
add_action( 'wp_ajax_nopriv_rh-user-favor-shop', 'rh_user_favorite_shop');
add_action( 'wp_ajax_rh-user-favor-shop', 'rh_user_favorite_shop' );
if (!function_exists('rh_user_favorite_shop')){
function rh_user_favorite_shop() {
    $nonce = sanitize_text_field($_POST['favornonce']);
    if ( ! wp_verify_nonce( $nonce, 'favornonce' ) )
        die ( 'Nope!' );
    
    if ( isset( $_POST['rh_user_favorite_shop'] ) ) {
    
        $user_id = intval($_POST['user_id']); // post id
        $rh_user_favorite_shop_count = get_user_meta( $user_id, "_rh_user_favorite_shop_count", true ); // post like count      
        if ( is_user_logged_in() ) { // user is logged in
            global $current_user;
            $currentuserID = $current_user->ID; // current user
            $ilike_users = get_user_meta( $currentuserID, "_i_liked_users" ); // user ids which were liked by user
            $who_like_me = get_user_meta( $user_id, "_users_who_like_me" ); // user ids which make like for user
            $ilike_USERS = ""; // setup array variable
            $users_like_ME = ""; // setup array variable
            
            if ( count( $ilike_users ) != 0 ) { // meta exists, set up values
                $ilike_USERS = $ilike_users[0];
            }
            
            if ( !is_array( $ilike_USERS ) ) // make array just in case
                $ilike_USERS = array();
                
            if ( count( $who_like_me ) != 0 ) { // meta exists, set up values
                $users_like_ME = $who_like_me[0];
            }       

            if ( !is_array( $users_like_ME ) ) // make array just in case
                $users_like_ME = array();
                
            $ilike_USERS['user-'.$user_id] = $user_id; // Add user id to user meta array of your likes
            $users_like_ME['user-'.$currentuserID] = $currentuserID; // add user id to user meta array of who liked user
    
            if ( !AlreadylikedShop($user_id) ) { // like the post
                update_user_meta( $user_id, "_users_who_like_me", $users_like_ME ); // Add current user ID array who likes
                update_user_meta( $user_id, "_rh_user_favorite_shop_count", ++$rh_user_favorite_shop_count ); // +1 count of likes
                update_user_meta( $currentuserID, "_i_liked_users", $ilike_USERS ); // Add user ID to user meta
                echo ''.$rh_user_favorite_shop_count; // update count on front end
                
            } else { // unlike the post
                $pid_key = array_search( $user_id, $ilike_USERS ); // find the key
                $uid_key = array_search( $currentuserID, $users_like_ME ); // find the key
                unset( $ilike_USERS[$pid_key] ); // remove from array
                unset( $users_like_ME[$uid_key] ); // remove from array
                update_user_meta( $user_id, "_users_who_like_me", $users_like_ME ); // Add current user ID array who likes
                update_user_meta( $user_id, "_rh_user_favorite_shop_count", --$rh_user_favorite_shop_count ); // -1 count of likes
                update_user_meta( $currentuserID, "_i_liked_users", $ilike_USERS ); // Add post ID to user meta
                echo "already".$rh_user_favorite_shop_count; // update count on front end
                
            }           
        } 
    }   
    exit;
}
}

if (!function_exists('AlreadylikedShop')){
function AlreadylikedShop( $user_id ) { // test if user liked before
    
    if ( is_user_logged_in() ) { // user is logged in
        global $current_user;
        $currentuserID = $current_user->ID; // current user
        $who_like_me = get_user_meta($currentuserID, "_i_liked_users" ); // user ids from post meta
        $users_like_ME = ""; // set up array variable
        
        if ( count( $who_like_me ) != 0 ) { // meta exists, set up values
            $users_like_ME = $who_like_me[0];
        }
        
        if( !is_array( $users_like_ME ) ) // make array just in case
            $users_like_ME = array();
            
        if ( in_array( $user_id, $users_like_ME ) ) { // True if User ID in array
            return true;
        }
        return false;   
    }    
}
}

if (!function_exists('getShopLikeButton')){
function getShopLikeButton( $user_id ) {
    wp_enqueue_script('rhshoplike', get_template_directory_uri() . '/js/shoplike.js', array('jquery', 'rehub'), '1.0', true);
    $wooscriptvars = array( 
        'favornonce' => wp_create_nonce('favornonce'),  
    );
    wp_localize_script( 'rhshoplike', 'wooscriptvars', $wooscriptvars );
    $like_count = get_user_meta( $user_id, "_rh_user_favorite_shop_count", true ); // get post likes
    if ( ( !$like_count ) || ( $like_count && $like_count == "0" ) ) { // no votes, set up empty variable
        $likes = '0';
    } elseif ( $like_count && $like_count != "0" ) { // there are votes!
        $likes = esc_attr( $like_count );
    }
    $alreadyclass = ( AlreadylikedShop( $user_id ) ) ? ' alreadyinfavor' : '';
    $output = '<span class="cursorpointer rh-user-favor-shop'.$alreadyclass.'"  data-user_id="'.$user_id.'">';
    if ( AlreadylikedShop( $user_id ) ) { // already liked, set up unlike addon
        $output .= '<span class="favorshop_like"><i class="rhicon rhi-heart-solid"></i></span>';
        $output .= ' <span class="count">'.$likes.'</span></span>';
    } else { // normal like button
        $output .= '<span class="favorshop_like"><i class="rhicon rhi-heart"></i></span>';
        $output .= ' <span class="count">'.$likes.'</span></span>';
    }
    if (rehub_option('exclude_rh_user_favorite_shop') !='1') {
        return $output;
    }
    else {
        return false;
    }
}
}

if (!function_exists('RhGetUserFavoriteShops')){
function RhGetUserFavoriteShops() {
    if ( is_user_logged_in() ) { // user is logged in
        global $current_user;
        $user_id = $current_user->ID; // current user
        $likedposts = get_user_meta( $user_id, "_i_liked_users", true);
    }
    if (!empty($likedposts)){
        $likedposts = implode(',', $likedposts);
        return do_shortcode('[wpsm_vendorlist per_page=50 user_id='.$likedposts.']');       
    }
    else{
        return esc_html__('You don\'t have any favorite store.', 'rehub-theme');
    }
}
}