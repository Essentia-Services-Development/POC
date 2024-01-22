<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php


/*-----------------------------------------------------------------------------------*/
# 	User rating function
/*-----------------------------------------------------------------------------------*/

add_action('wp_ajax_nopriv_rehub_rate_post', 'rehub_rate_post');
add_action('wp_ajax_rehub_rate_post', 'rehub_rate_post');
if( !function_exists('rehub_rate_post') ) {
function rehub_rate_post(){
	check_ajax_referer( 'ajaxed-nonce', 'security' );
	global $user_ID;

	if( ( !empty($user_ID) && rehub_option('allowtorate') == 'guests' ) ||	( empty($user_ID) && rehub_option('allowtorate') == 'users' ) ){
		return false ;
	}else{
		$count = $rating = $rate = 0;
		$postID = (isset($_REQUEST['post'])) ? $_REQUEST['post'] : '';
		$ratetype = (isset($_REQUEST['type'])) ? $_REQUEST['type'] : 'post';
		$rate = abs($_REQUEST['value']);
		if($rate > 5 ) $rate = 5;

		if( is_numeric( $postID ) && $ratetype=='post'){
			$rating = get_post_meta($postID, 'rehub_user_rate' , true);
			$count = get_post_meta($postID, 'rehub_users_num' , true);
			if( empty($count) || $count == '' ) $count = 0;

			$count++;
			$total_rate = (int)$rating + (int)$rate;
			$total = round($total_rate/$count , 2);
			if ( $user_ID ) {
				$user_rated = (array)get_the_author_meta( 'rehub_rated', $user_ID  );

				if( empty($user_rated) ){
					$user_rated[$postID] = $rate;

					update_user_meta( $user_ID, 'rehub_rated', $user_rated );
					update_post_meta( $postID, 'rehub_user_rate', $total_rate );
					update_post_meta( $postID, 'rehub_users_num', $count );
					update_post_meta( $postID, '_rh_simple_u_rate_total', $total );

					echo ''.$total;
				}
				else{
					if( !array_key_exists($postID , $user_rated) ){
						$user_rated[$postID] = $rate;
						update_user_meta( $user_ID, 'rehub_rated', $user_rated );
						update_post_meta( $postID, 'rehub_user_rate', $total_rate );
						update_post_meta( $postID, 'rehub_users_num', $count );
						update_post_meta( $postID, '_rh_simple_u_rate_total', $total );
						echo ''.$total;
					}
				}
			}else{
				$ip = rehub_get_ip();
				$ip = str_replace('.', '', $ip);
				$get_t_id = get_transient('rh_star_rate_' . $postID);
				if( empty($get_t_id) ){
					$ips = array();
					$ips[$ip] = $rate;
					set_transient('rh_star_rate_' . $postID, $ips, 30 * DAY_IN_SECONDS);
					update_post_meta( $postID, 'rehub_user_rate', $total_rate );
					update_post_meta( $postID, 'rehub_users_num', $count );
					echo ''.$total;
				}else{
					$ips = (array)$get_t_id;
					if (!array_key_exists($ip , $ips)){
						$ips[$ip] = $rate;
						set_transient('rh_star_rate_' . $postID, $ips, 30 * DAY_IN_SECONDS);
						update_post_meta( $postID, 'rehub_user_rate', $total_rate );
						update_post_meta( $postID, 'rehub_users_num', $count );	
						echo ''.$total;				
					}					
				}
			}
		}
		if( is_numeric( $postID ) && $ratetype=='tax'){
			$rating = get_term_meta( $postID, 'rehub_user_rate', true );
			$count = get_term_meta( $postID, 'rehub_users_num', true );
			if( empty($count) || $count == '' ) $count = 0;

			$count++;
			$total_rate = (int)$rating + (int)$rate;
			$total = round($total_rate/$count , 2);
			if ( $user_ID ) {
				$user_rated = (array)get_the_author_meta( 'rehub_rated', $user_ID  );

				if( empty($user_rated) ){
					$user_rated[$postID] = $rate;

					update_user_meta( $user_ID, 'rehub_rated', $user_rated );
					update_term_meta( $postID, 'rehub_user_rate', $total_rate );
					update_term_meta( $postID, 'rehub_users_num', $count );
					echo ''.$total;
				}
				else{
					if( !array_key_exists($postID , $user_rated) ){
						$user_rated[$postID] = $rate;
						update_user_meta( $user_ID, 'rehub_rated', $user_rated );
						update_term_meta( $postID, 'rehub_user_rate', $total_rate );
						update_term_meta( $postID, 'rehub_users_num', $count );
						echo ''.$total;
					}
				}
			}else{
				$ip = rehub_get_ip();
				$ip = str_replace('.', '', $ip);
				$get_t_id = get_transient('rh_star_rate_' . $postID);
				if( empty($get_t_id) ){
					$ips = array();
					$ips[$ip] = $rate;
					set_transient('rh_star_rate_' . $postID, $ips, 30 * DAY_IN_SECONDS);
					update_term_meta( $postID, 'rehub_user_rate', $total_rate );
					update_term_meta( $postID, 'rehub_users_num', $count );
					echo ''.$total;
				}else{
					$ips = (array)$get_t_id;
					if (!array_key_exists($ip , $ips)){
						$ips[$ip] = $rate;
						set_transient('rh_star_rate_' . $postID, $ips, 30 * DAY_IN_SECONDS);
						update_term_meta( $postID, 'rehub_user_rate', $total_rate );
						update_term_meta( $postID, 'rehub_users_num', $count );		
						echo ''.$total;			
					}					
				}
			}
		}
		if( is_numeric( $postID ) && $ratetype=='user'){
			$rating = get_user_meta( $postID, 'rh_total_bp_user_rating', true );
			$count = get_user_meta( $postID, 'rh_total_bp_user_rating_num', true );
			if( empty($count) || $count == '' ) $count = 0;

			$count++;
			$total_rate = (int)$rating + (int)$rate;
			$total = round($total_rate/$count , 2);
			if ( $user_ID ) {
				$user_rated = (array)get_the_author_meta( 'rehub_rated', $user_ID  );

				if( empty($user_rated) ){
					$user_rated[$postID] = $rate;

					update_user_meta( $user_ID, 'rehub_rated', $user_rated );
					update_user_meta( $postID, 'rh_total_bp_user_rating', $total_rate );
					update_user_meta( $postID, 'rh_bp_user_rating', $total );
					update_user_meta( $postID, 'rh_total_bp_user_rating_num', $count );

					echo ''.$total;
				}
				else{
					if( !array_key_exists($postID , $user_rated) ){
						$user_rated[$postID] = $rate;
						update_user_meta( $user_ID, 'rehub_rated', $user_rated );
						update_user_meta( $postID, 'rh_total_bp_user_rating', $total_rate );
						update_user_meta( $postID, 'rh_total_bp_user_rating_num', $count );
						update_user_meta( $postID, 'rh_bp_user_rating', $total );						
						echo ''.$total;
					}
				}
			}else{
				$ip = rehub_get_ip();
				$ip = str_replace('.', '', $ip);
				$get_t_id = get_transient('rh_star_rate_' . $postID);
				if( empty($get_t_id) ){
					$ips = array();
					$ips[$ip] = $rate;
					set_transient('rh_star_rate_' . $postID, $ips, 30 * DAY_IN_SECONDS);
					update_user_meta( $postID, 'rh_total_bp_user_rating', $total_rate );
					update_user_meta( $postID, 'rh_total_bp_user_rating_num', $count );
					update_user_meta( $postID, 'rh_bp_user_rating', $total );
					echo ''.$total;
				}else{
					$ips = (array)$get_t_id;
					if (!array_key_exists($ip , $ips)){
						$ips[$ip] = $rate;
						set_transient('rh_star_rate_' . $postID, $ips, 30 * DAY_IN_SECONDS);
						update_user_meta( $postID, 'rh_total_bp_user_rating', $total_rate );
						update_user_meta( $postID, 'rh_total_bp_user_rating_num', $count );
						update_user_meta( $postID, 'rh_bp_user_rating', $total );	
						echo ''.$total;					
					}					
				}
			}
		}		
	}

    wp_die();
}
}


/*-----------------------------------------------------------------------------------*/
# 	User results generating
/*-----------------------------------------------------------------------------------*/

if( !function_exists('rehub_get_user_rate') ) {
function rehub_get_user_rate($schema='admin', $type = 'post', $customid = ''){

	wp_enqueue_script( 'rh-userrating', get_template_directory_uri() . '/js/userrating.js', array( 'jquery', 'rehub' ), '1.1', true );
	if ($type == 'post') {
		global $post;
		$postid = $post->ID;
	}
	elseif($type == 'tax') {
		$postid = get_queried_object()->term_id;
	}
	elseif($type == 'user') {
		$postid = $customid;
	}	
	global $user_ID;
	$disable_rate = false ;

	if( ( !empty($user_ID) && rehub_option('allowtorate') == 'guests' ) || ( empty($user_ID) && rehub_option('allowtorate') == 'users' ) )
		$disable_rate = true ;

	if( !empty($disable_rate) ){
		$no_rate_text = esc_html__( 'No Ratings Yet!', 'rehub-theme' );
		$rate_active = false ;
	}
	else{
		$no_rate_text = esc_html__( 'Be the first one!' , 'rehub-theme' );
		$rate_active = ' user-rate-active' ;
	}

	$image_style ='stars';
	if ($type == 'post') {
		$rate = get_post_meta( $postid , 'rehub_user_rate', true );
		$count = get_post_meta( $postid , 'rehub_users_num', true );
	}
	elseif($type == 'tax') {
		$rate = get_term_meta( $postid , 'rehub_user_rate', true );
		$count = get_term_meta( $postid , 'rehub_users_num', true );
	}
	elseif($type == 'user') {
		$rate = get_user_meta( $postid , 'rh_total_bp_user_rating', true );
		$count = get_user_meta( $postid , 'rh_total_bp_user_rating_num', true );
	}	

	if( !empty($rate) && !empty($count)){
		$total = $rate/$count;
		$total_users_score = round($rate/$count,2);		
	}else{
		$total_users_score = $total = $count = 0;
	}
	$stars = '';
    for ($i = 1; $i <= 5; $i++) {
    	if ($i <= $total){
    		$active = ' active';
    	}else{
    		$active ='';
    	}
        $stars .= '<i class="starrate starrate'.$i.$active.'" data-ratecount="'.$i.'"></i>';
    }	

	if ( $user_ID ) {
		$user_rated = get_the_author_meta( 'rehub_rated' , $user_ID ) ;
		if( !empty($user_rated) && is_array($user_rated) && array_key_exists($postid , $user_rated) ){
			$user_rate = round( ($user_rated[$postid]*100)/5 , 1);
			return $output = '<div class="rh-star-ajax"><span class="title_star_ajax"><strong>'.__( "User Rating:" , "rehub-theme" ) .' </strong> <span class="userrating-score">'.$total_users_score.'</span> <small>(<span class="userrating-count">'.$count.'</span> '._n("vote", "votes", $count, "rehub-theme" ) .')</small> </span><div data-rate="'. round($total) .'" data-ratetype="'.$type.'" class="rate-post-'.$postid.' user-rate rated-done"><span class="post-norsp-rate '.$image_style.'-rate-ajax-type">'.$stars.'</span></div><div class="userrating-clear"></div></div>';
		}
	}else{
		$ip = rehub_get_ip();
		$ip = str_replace('.', '', $ip);
		$get_t_id = get_transient('rh_star_rate_' . $postid);

		if( !empty($get_t_id) ){
			if (array_key_exists($ip, $get_t_id)){			
				return $output = '<div class="rh-star-ajax"><span class="title_star_ajax"><strong>'.__( "User Rating:" , "rehub-theme" ) .' </strong> <span class="userrating-score">'.$total_users_score.'</span> <small>(<span class="userrating-count">'.$count.'</span> '._n("vote", "votes", $count, "rehub-theme" ) .')</small> </span><div data-rate="'. round($total) .'" class="rate-post-'.$postid.' user-rate rated-done"><span class="post-norsp-rate '.$image_style.'-rate-ajax-type">'.$stars.'</span></div><div class="userrating-clear"></div></div>';
			}
		}

	}
	if( $total == 0 && $count == 0)
		return $output = '<div class="rh-star-ajax"><span class="title_star_ajax"><strong>'.__( "User Rating:" , "rehub-theme" ) .' </strong> <span class="userrating-score"></span> <small>'.$no_rate_text.'</small> </span><div data-rate="'. $total .'" data-id="'.$postid.'" data-ratetype="'.$type.'" class="rate-post-'.$postid.' user-rate'.$rate_active.'"><span class="post-norsp-rate '.$image_style.'-rate-ajax-type">'.$stars.'</span></div><div class="userrating-clear"></div></div>';
	else
		return $output = '<div class="rh-star-ajax"><span class="title_star_ajax"><strong>'.__( "User Rating:" , "rehub-theme" ) .' </strong> <span class="userrating-score">'.$total_users_score.'</span> <small>(<span class="userrating-count">'.$count.'</span> '._n("vote", "votes", $count, "rehub-theme" ) .')</small> </span><div data-rate="'. $total .'" data-id="'.$postid.'" data-ratetype="'.$type.'" class="rate-post-'.$postid.' user-rate'.$rate_active.'"><span class="post-norsp-rate '.$image_style.'-rate-ajax-type">'.$stars.'</span></div><div class="userrating-clear"></div></div>';
}
}

if( !function_exists('rehub_simple_star') ) {
	function rehub_simple_star($atts, $content= null){
    	$atts = shortcode_atts(
			array(
				'schema' => 'admin',
				'type' => 'post',
				'customid' => '',
			), $atts);
    	extract($atts);
    	return '<div class="rehub_simple_star"><style scoped>.rehub_simple_star .rh-star-ajax{display:flex; align-items:center;gap: 10px;}.rehub_simple_star .title_star_ajax{order:2; margin-bottom:0 !important}.rehub_simple_star .user-rate{order:1}.rehub_simple_star .userrating-clear{display:none}</style>'.rehub_get_user_rate($schema, $type, $customid).'</div>';
	}
}

if( !function_exists('rehub_get_user_rate_criterias') ) {
function rehub_get_user_rate_criterias (){
	global $post;
	$postAverage = get_post_meta($post->ID, 'post_user_average', true);
	$userrevcount = get_post_meta($post->ID, 'post_user_raitings', true);
	if ($postAverage !='0' && $postAverage !='' ){
		$total = $postAverage*10;
		$count = $userrevcount['criteria'][0]['count'];
		return $output = '<div class="star"><span class="title_stars"><strong>'.__( "User Rating:" , "rehub-theme" ) .' </strong> <span class="userrating-score">'.$postAverage.'/10</span> <small>(<span class="userrating-count">'.$count.'</span> '._n("vote", "votes", $count, "rehub-theme" ) .')</small></span><div class="user-rate"><span class="stars-rate"><span style="width: '.$total.'%;"></span></span></div></div>';
	}
	else {
		return $output = '<div class="star criterias_star"><span class="title_stars"><strong>'.__( "User Rating:" , "rehub-theme" ) .' </strong>'.__( "No Ratings Yet!" , "rehub-theme" ) .' </span><a href="#respond" class="rehub_scroll add_user_review_link color_link">'.__("Add your review", "rehub-theme").'</a></div>';
	}
}
}


//////////////////////////////////////////////////////////////////
// User get results
//////////////////////////////////////////////////////////////////

if( !function_exists('rehub_get_user_results') ) {
function rehub_get_user_results( $size = 'small', $words = 'no' ){
	global $post ;
	$rate = get_post_meta( $post->ID , 'rehub_user_rate', true );
	$count = get_post_meta( $post->ID , 'rehub_users_num', true );
	$postAverage = get_post_meta($post->ID, 'post_user_average', true);

	if ((rehub_option('type_user_review') == 'full_review') && ($postAverage !='0' && $postAverage !='' )){
		$total = $postAverage*10;
		?>
		<?php if ($words == 'yes') :?><strong><?php esc_html_e('User rating', 'rehub-theme'); ?>: </strong><?php endif ;?><div class="star-<?php echo ''.$size ?>"><span class="stars-rate"><span style="width: <?php echo ''.$total ?>%;"></span></span></div>
		<?php
	}
	elseif( rehub_option('type_user_review') == 'simple' && !empty($rate) && !empty($count)){
		$total = (($rate/$count)/5)*100;
		?>
		<?php if ($words == 'yes') :?><strong><?php esc_html_e('User rating', 'rehub-theme'); ?>: </strong><?php endif ;?><div class="star-<?php echo ''.$size ?>"><span class="stars-rate"><span style="width: <?php echo ''.$total ?>%;"></span></span></div>
		<?php
	}
	else{}
}
}

if( !function_exists('rehub_get_review_data') ) {
function rehub_get_review_data(){
	global $post;
	
	if(is_null($post)) return;
	
	$review_post_raw = get_post_meta( $post->ID, 'review_post', true );
	
	if(empty($review_post_raw[0])) return;
	
	return $review_post_raw[0];
}
}

if( !function_exists('rehub_get_overall_score') ) {
function rehub_get_overall_score($criterias='', $manual=''){

	global $post;
	if(!empty($criterias)){
		$thecriteria = $criterias;
	}
	else{
		$thecriteria = get_post_meta((int)$post->ID, '_review_post_criteria', true);
		if(empty($thecriteria)){
			$review_post = rehub_get_review_data();
			if(!empty($review_post['review_post_criteria'])){
				$thecriteria = $review_post['review_post_criteria'];
			}
		}
	}
	if(!empty($manual)){
		$manual_score = $manual;
	}
	else{
		$manual_score = get_post_meta((int)$post->ID, '_review_post_score_manual', true);
		if(empty($manual_score)){
			if(!isset($review_post)){
				$review_post = rehub_get_review_data();
			}
			if(!empty($review_post['review_post_score_manual'])){
				$manual_score = $review_post['review_post_score_manual'];
			}
		}
	}
	$score = 0; $total_counter = 0;

	if (!empty($thecriteria))  {
	    foreach ($thecriteria as $criteria) {
	    	$score += (float)$criteria['review_post_score']; $total_counter ++;
	    }
	}
    if (!empty($manual_score))  {
    	$total_score = $manual_score;
    	return $total_score;
    }
    else {
		if( !empty( $score ) && !empty( $total_counter ) ) $total_score =  $score / $total_counter ;
		if( empty($total_score) ) $total_score = 0;
		$total_score = round($total_score,1);
		if (rehub_option('type_user_review') == 'full_review' && rehub_option('type_total_score') == 'average') {
			$userAverage = get_post_meta((int)$post->ID, 'post_user_average', true);
			if ($userAverage !='0' && $userAverage !='' ) {
				$total_score = ((float)$total_score + (float)$userAverage) / 2;
				$total_score = round($total_score,1);
			}
		}
		if (rehub_option('type_user_review') == 'full_review' && rehub_option('type_total_score') == 'user') {
			$total_score = 0;
			$userAverage = get_post_meta((int)$post->ID, 'post_user_average', true);
			if ($userAverage !='0' && $userAverage !='' ) {
				$total_score = $userAverage;
				$total_score = round($total_score,1);
			}
		}		
		elseif (rehub_option('type_user_review') == 'simple' && rehub_option('type_total_score') == 'average') {
			$rate = get_post_meta((int)$post->ID, 'rehub_user_rate', true );
			$count = get_post_meta((int)$post->ID, 'rehub_users_num', true );
			if( !empty($rate) && !empty($count)){
				$userAverage = (($rate/$count)/5)*10;
				$total_score = ((float)$total_score + (float)$userAverage) / 2;
				$total_score = round($total_score,1);
			}
		}	
		elseif (rehub_option('type_user_review') == 'simple' && rehub_option('type_total_score') == 'user') {
			$rate = get_post_meta((int)$post->ID, 'rehub_user_rate', true );
			$count = get_post_meta((int)$post->ID, 'rehub_users_num', true );
			if( !empty($rate) && !empty($count)){
				$userAverage = (($rate/$count)/5)*10;
				$total_score = $userAverage;
				$total_score = round($total_score,1);
			}
		}			
		return $total_score;
	}
}
}

if( !function_exists('rehub_get_overall_score_editor') ) {
function rehub_get_overall_score_editor($criterias='', $manual=''){	
	global $post;
	if(!empty($criterias)){
		$thecriteria = $criterias;
	}
	else{
		$thecriteria = get_post_meta((int)$post->ID, '_review_post_criteria', true);
		if(empty($thecriteria)){
			$review_post = rehub_get_review_data();
			if(!empty($review_post['review_post_criteria'])){
				$thecriteria = $review_post['review_post_criteria'];
			}
		}
	}
	if(!empty($manual)){
		$manual_score = $manual;
	}
	else{
		$manual_score = get_post_meta((int)$post->ID, '_review_post_score_manual', true);
		if(empty($manual_score)){
			if(!isset($review_post)){
				$review_post = rehub_get_review_data();
			}
			if(!empty($review_post['review_post_score_manual'])){
				$manual_score = $review_post['review_post_score_manual'];
			}
		}
	}
	$score = 0; $total_counter = 0;

	if (!empty($manual_score))  {
    	$total_score = $manual_score;
    	return $total_score;
    }

    foreach ($thecriteria as $criteria) {

    	$score += $criteria['review_post_score']; $total_counter ++;
    }
		if( !empty( $score ) && !empty( $total_counter ) ) $total_score =  $score / $total_counter ;
		if( empty($total_score) ) $total_score = 0;
		$total_score = round($total_score,1);
		return $total_score;
}
}


/*-----------------------------------------------------------------------------------*/
# 	Review box generating
/*-----------------------------------------------------------------------------------*/

if( !function_exists('rehub_get_review') ) {
function rehub_get_review(){
	wp_enqueue_style('rhuserreviews');
	$review_post = rehub_get_review_data();
	global $post;
	$postid = $post->ID;
    ?>
    <?php $overal_score = rehub_get_overall_score(); $postAverage = get_post_meta($postid, 'post_user_average', true); ?>
	<?php if ($overal_score !='0') :?>
		<?php 
			$reviewheading = get_post_meta((int)$post->ID, '_review_heading', true);		
			if(!$reviewheading){
				$reviewheading = $review_post['review_post_heading'];	
			}
			$reviewsummary = get_post_meta((int)$post->ID, '_review_post_summary_text', true);		
			if(!$reviewsummary){
				$reviewsummary = $review_post['review_post_summary_text'];	
			}
		?>
		<div class="rate_bar_wrap<?php if ((rehub_option('type_user_review') == 'full_review') && ($postAverage !='0' && $postAverage !='' )) {echo ' two_rev';} ?><?php if (rehub_option('color_type_review') == 'multicolor') {echo ' colored_rate_bar';} ?>">
			<div class="review-top">								
				<div class="overall-score">
					<span class="overall r_score_<?php echo round($overal_score); ?>"><?php echo round($overal_score, 1) ?></span>
					<span class="overall-text"><?php esc_html_e('Total Score', 'rehub-theme'); ?></span>
					<?php if (rehub_option('type_schema_review') == 'user' && rehub_option('type_user_review') == 'full_review' && get_post_meta($postid, 'post_user_raitings', true) !='') :?>						
					<div class="overall-user-votes"><span><?php $user_rates = get_post_meta($postid, 'post_user_raitings', true); echo ''.$user_rates['criteria'][0]['count'] ;?></span> <?php esc_html_e('reviews', 'rehub-theme'); ?></div>
					<?php endif;?>				
				</div>				
				<div class="review-text">
					<span class="review-header"><?php echo ''.$reviewheading; ?></span>
					<p>
						<?php echo wp_kses_post($reviewsummary); ?>
					</p>
				</div>
			</div>

			<?php 
			$thecriteria = get_post_meta($postid, '_review_post_criteria', true);
			if(empty($thecriteria)){
				$thecriteria = $review_post['review_post_criteria']; 
			}
			$firstcriteria = $thecriteria[0]['review_post_name']; 
			?>

			<?php if ((rehub_option('type_user_review') == 'full_review') && ($postAverage !='0' && $postAverage !='' )) :?>
				<div class="rate_bar_wrap_two_reviews flowhidden">
					<?php if($firstcriteria) : ?>
					<div class="wpsm-one-half floatleft review-criteria">
						<div class="l_criteria"><span class="score_val r_score_<?php echo round(rehub_get_overall_score_editor()); ?>"><?php echo round(rehub_get_overall_score_editor(), 1); ?></span><span class="score_tit"><?php esc_html_e('Expert Score', 'rehub-theme'); ?></span></div>
						<div class="r_criteria">
							<?php foreach ($thecriteria as $criteria) { ?>
							<?php $perc_criteria = $criteria['review_post_score']*10; ?>
							<div class="rate-bar clearfix" data-percent="<?php echo ''.$perc_criteria; ?>%">
								<div class="rate-bar-title"><span><?php echo ''.$criteria['review_post_name']; ?></span></div>
								<div class="rate-bar-bar r_score_<?php echo round($criteria['review_post_score']); ?>"></div>
								<div class="rate-bar-percent"><?php echo round($criteria['review_post_score'], 1) ?></div>
							</div>
							<?php } ?>
						</div>
					</div>
					<?php endif; ?>
					<?php 
						$user_rates = get_post_meta($postid, 'post_user_raitings', true); 
						$usercriterias = $user_rates['criteria'];  
					?>
					<div class="review-criteria wpsm-one-half wpsm-last-column floatright user-review-criteria">
						<div class="l_criteria"><span class="score_val r_score_<?php echo round($postAverage); ?>"><?php echo round($postAverage, 1) ?></span><span class="score_tit"><?php esc_html_e('User\'s score', 'rehub-theme'); ?></span></div>
						<div class="r_criteria">
							<?php foreach ($usercriterias as $usercriteria) { ?>
							<?php $perc_criteria = $usercriteria['average']*10; ?>
							<div class="rate-bar user-rate-bar clearfix" data-percent="<?php echo ''.$perc_criteria; ?>%">
								<div class="rate-bar-title"><span><?php echo ''.$usercriteria['name']; ?></span></div>
								<div class="rate-bar-bar r_score_<?php echo round($usercriteria['average']); ?>"></div>
								<div class="rate-bar-percent"><?php echo round($usercriteria['average'], 1) ?></div>
							</div>
							<?php } ?>
						</div>
					</div>
				</div>
			<?php else :?>

				<?php if($firstcriteria) : ?>
					<div class="review-criteria">
						<?php foreach ($thecriteria as $criteria) { ?>
							<?php $perc_criteria = $criteria['review_post_score']*10; ?>
							<div class="rate-bar clearfix" data-percent="<?php echo ''.$perc_criteria; ?>%">
								<div class="rate-bar-title"><span><?php echo ''.$criteria['review_post_name']; ?></span></div>
								<div class="rate-bar-bar r_score_<?php echo round($criteria['review_post_score']); ?>"></div>
								<div class="rate-bar-percent"><?php echo ''.$criteria['review_post_score']; ?></div>
							</div>
						<?php } ?>
					</div>
				<?php endif; ?>
			<?php endif ;?>

			<?php 	
				$prosvalues = get_post_meta($post->ID, '_review_post_pros_text', true);
				if(empty($prosvalues)){
					$prosvalues = (!empty($review_post['review_post_pros_text'])) ? $review_post['review_post_pros_text'] : '';
				}
				$consvalues = get_post_meta($post->ID, '_review_post_cons_text', true);
				if(empty($consvalues)){
					$consvalues = (!empty($review_post['review_post_cons_text'])) ? $review_post['review_post_cons_text'] : '';
				}
			?> 
			<?php $pros_cons_wrap = (!empty($prosvalues) || !empty($consvalues) ) ? ' class="pros_cons_values_in_rev flowhidden"' : ' class="flowhidden"'?>
			<!-- PROS CONS BLOCK-->
			<div<?php echo esc_attr($pros_cons_wrap);?>>
			<?php if(!empty($prosvalues)):?>
			<div <?php if(!empty($prosvalues) && !empty($consvalues)):?>class="wpsm-one-half wpsm-column-first"<?php endif;?>>
				<div class="wpsm_pros padd20">
					<div class="title_pros"><?php esc_html_e('PROS', 'rehub-theme');?></div>
					<ul>		
						<?php $prosvalues = explode(PHP_EOL, $prosvalues);?>
						<?php foreach ($prosvalues as $prosvalue) {
							echo '<li>'.$prosvalue.'</li>';
						}?>
					</ul>
				</div>
			</div>
			<?php endif;?>
		
			<?php if(!empty($consvalues)):?>
			<div class="wpsm-one-half wpsm-column-last">
				<div class="wpsm_cons padd20">
					<div class="title_cons"><?php esc_html_e('CONS', 'rehub-theme');?></div>
					<ul>
						<?php $consvalues = explode(PHP_EOL, $consvalues);?>
						<?php foreach ($consvalues as $consvalue) {
							echo '<li>'.$consvalue.'</li>';
						}?>
					</ul>
				</div>
			</div>
			<?php endif;?>
			</div>	
			<!-- PROS CONS BLOCK END-->	

			<?php if (rehub_option('type_user_review') == 'simple') :?>
				<?php if ($overal_score !='0') :?>
					<div class="rating_bar flowhidden mt15"><?php echo rehub_get_user_rate() ; ?></div>
				<?php else :?>
					<div class="rating_bar no_rev flowhidden mt15"><?php echo rehub_get_user_rate() ; ?></div>
				<?php endif; ?>
			<?php elseif (rehub_option('type_user_review') == 'full_review' && comments_open()) :?>
				<a href="#respond" class="rehub_scroll add_user_review_link"><?php esc_html_e("Add your review", "rehub-theme"); ?></a> <?php $comments_count = wp_count_comments($postid); if ($comments_count->total_comments !='') :?><span class="add_user_review_link"> &nbsp;|&nbsp; </span><a href="#comments" class="rehub_scroll add_user_review_link"><?php esc_html_e("Read reviews and comments", "rehub-theme"); ?></a><?php endif;?>
			<?php endif; ?>

		</div>
	<?php endif ;?>

<?php

}
}

//COMMENT SORT FUNCTIONS
add_action('wp_ajax_nopriv_show_tab', 'show_tab_ajax');
add_action('wp_ajax_show_tab', 'show_tab_ajax');
function show_tab_ajax() {
  	if (!isset($_POST['rating_tabs_id']) || !wp_verify_nonce($_POST['rating_tabs_id'], 'rating_tabs_nonce'))
    wp_die(sha1(microtime())); // return some random trash :)

  	if (!isset($_POST['post_id']) || !isset($_POST['tab_number']))
    	wp_die(sha1(microtime())); // return some random trash :)

  	$post_id = (int)$_POST['post_id'];
  	$tab_number = (int)$_POST['tab_number'];
  	$posttype = (isset($_POST['posttype']) && $_POST['posttype'] == 'product') ? 'product' : 'post';
  	if (empty($post_id) || empty($tab_number) || $post_id<1 || $tab_number<1 || $tab_number>4)
    	wp_die(sha1(microtime())); // return some random trash :)

  	$comments_count = wp_count_comments($post_id);
  	if (empty($comments_count->approved))
    	wp_die(esc_html__('No comments on this post', 'rehub-theme'));
  	unset($comments_count);

	$comments_v = get_comments(array(
		'post_id' => $post_id,
		'status'  => 'approve',
		'orderby' => 'comment_date',
		'order'   => 'DESC',
	));

  	foreach($comments_v as $key=>$comment) {
    	$meta = get_comment_meta($comment->comment_ID);
    	$comment->user_average = isset($meta['user_average'][0]) ? $meta['user_average'][0] : 0;
    	if(isset($meta['user_average'][0])){
    		$comment->user_average = $meta['user_average'][0];
    	}
    	elseif(isset($meta['rating'][0])){
    		$comment->user_average = $meta['rating'][0];
    	}else{
    		$comment->user_average = 0;
    	} 
    	$comment->recomm_plus  = isset($meta['recomm_plus'][0]) ? $meta['recomm_plus'][0] : 0;
    	//$comment->recomm_minus = isset($meta['recomm_minus'][0]) ? $meta['recomm_minus'][0] : 0;
    	$comments_and_meta_v[$key] = $comment;
  	}
  	unset($comments_v);

  	switch ($tab_number) {
    	case 1 : $sorted_comments_v = show_tab_get_newest($comments_and_meta_v); break;
    	case 2 : $sorted_comments_v = show_tab_get_most_helpful($comments_and_meta_v); break;
    	case 3 : $sorted_comments_v = show_tab_get_highest_rating($comments_and_meta_v); break;
    	case 4 : $sorted_comments_v = show_tab_get_lowest_rating($comments_and_meta_v); break;
    default: die(sha1(microtime())); // not needed, but...
  	}
  	unset($comments_and_meta_v);

  	show_tab_print_comments($sorted_comments_v, $posttype);
  	exit;
}
// ----------------------------------------------
function show_tab_get_newest($comments_v) {
  	return $comments_v; // it already sorted as we need
}
// ----------------------------------------------
function show_tab_get_most_helpful_sort ($a, $b) {
    if ($a->recomm_plus > $b->recomm_plus)
      	return -1;
    elseif ($a->recomm_plus < $b->recomm_plus)
      	return 1;
    elseif ($a->comment_ID > $b->comment_ID)
      	return -1;
    else
      	return 1;
}
function show_tab_get_most_helpful($comments_v) {
  	$comments_v = show_tab_delete_unlikes_comments($comments_v);
  	usort($comments_v, 'show_tab_get_most_helpful_sort');
  	return $comments_v;
}
// ----------------------------------------------
function show_tab_get_highest_rating_sort ($a, $b) {
    if ($a->user_average > $b->user_average)
      	return -1;
    elseif ($a->user_average < $b->user_average)
      	return 1;
    elseif ($a->comment_ID > $b->comment_ID)
      	return -1;
    else
      return 1;
}
function show_tab_get_highest_rating($comments_v) {
  	$comments_v = show_tab_delete_unrated_comments($comments_v);
  	usort($comments_v, 'show_tab_get_highest_rating_sort');
  	return $comments_v;
}
// ----------------------------------------------

function show_tab_get_lowest_rating_sort ($a, $b) {
   if ($a->user_average > $b->user_average)
      	return 1;
    elseif ($a->user_average < $b->user_average)
      	return -1;
    elseif ($a->comment_ID > $b->comment_ID)
      	return 1;
    else
      	return -1;
}
function show_tab_get_lowest_rating($comments_v) {
  	$comments_v = show_tab_delete_unrated_comments($comments_v);
  	usort($comments_v, 'show_tab_get_lowest_rating_sort');
  	return $comments_v;
}
// ----------------------------------------------
function show_tab_delete_unrated_comments($comments_v) {
  	$result_v = array();
  	foreach($comments_v as $comment) {
    if (empty($comment->user_average)) continue;
    	$result_v[] = $comment;
  	}
  	return $result_v;
}
// ----------------------------------------------
function show_tab_delete_unlikes_comments($comments_v) {
  	$result_v = array();
  	foreach($comments_v as $comment) {
    	if (empty($comment->recomm_plus)) continue;
    	$result_v[] = $comment;
  	}
  	return $result_v;
}
// ----------------------------------------------
function show_tab_print_comments($sorted_comments_v, $posttype) {
	$callback = ($posttype == 'product') ? 'woocommerce_comments' : 'rehub_framework_comments';
  	wp_list_comments(array(
    	'avatar_size'   => 50,
    	'max_depth'     => 4,
    	'style'         => 'ul',
    	'reverse_top_level' => 0,
    	'callback'      => $callback,
    	'echo'          => 'true'
  	), $sorted_comments_v);
}

//////////////////////////////////////////////////////////////////
// Helpful or NOT
//////////////////////////////////////////////////////////////////

add_action( 'wp_ajax_nopriv_commentplus', 'commentplus_re' );
add_action( 'wp_ajax_commentplus', 'commentplus_re' );
function commentplus_re() {
	$nonce = sanitize_text_field($_POST['cplusnonce']);
    if ( ! wp_verify_nonce( $nonce, 'commre-nonce' ) )
        die ( 'Nope!' );
	
	if ( isset( $_POST['comm_help'] ) ) {	
		$post_id = intval($_POST['post_id']); // post id
		$comm_plus = get_comment_meta( $post_id, "recomm_plus", true ); // get helpful comment count
		$comm_minus = get_comment_meta( $post_id, "recomm_minus", true ); // get unhelpful comment count				
		if ( is_user_logged_in() ) { // user is logged in
			global $current_user;
			$user_id = $current_user->ID; // current user
			$meta_POSTS = get_user_meta( $user_id, "_comm_help_posts" ); // post ids from user meta
			$meta_USERS = get_comment_meta( $post_id, "_user_comm_help" ); // user ids from post meta
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

			if ($_POST['comm_help'] =='plus') {				
				if ( !AlreadyCommentplus( $post_id ) ) {
					update_comment_meta( $post_id, "recomm_plus", ++$comm_plus ); // +1 count to helpful
					update_comment_meta( $post_id, "_user_comm_help", $liked_USERS ); // Add user ID to post meta
					update_user_meta( $user_id, "_comm_help_posts", $liked_POSTS ); // Add post ID to user meta
					update_user_meta( $user_id, "_comm_help_count", $user_likes ); // +1 count user meta					
				} 		
			}
			if ($_POST['comm_help'] =='minus') {
				if ( !AlreadyCommentplus( $post_id ) ) {
					update_comment_meta( $post_id, "recomm_minus", ++$comm_minus ); // +1 count to unhelpful
					update_comment_meta( $post_id, "_user_comm_help", $liked_USERS ); // Add user ID to post meta
					update_user_meta( $user_id, "_comm_help_posts", $liked_POSTS ); // Add post ID to user meta
					update_user_meta( $user_id, "_comm_help_count", $user_likes ); // +1 count user meta					
				} 									
			}			
			
		} else { // user is not logged in (anonymous)
			$ip = rehub_get_ip(); // user IP address
			$meta_IPS = get_comment_meta( $post_id, "_user_IP_comm_help" ); // stored IP addresses
			$liked_IPS = ""; // set up array variable			
			if ( count( $meta_IPS ) != 0 ) { // meta exists, set up values
				$liked_IPS = $meta_IPS[0];
			}	
			if ( !is_array( $liked_IPS ) ) // make array just in case
				$liked_IPS = array();				
			if ( !in_array( $ip, $liked_IPS ) ) // if IP not in array
				$liked_IPS['ip-'.$ip] = $ip; // add IP to array	

			if ($_POST['comm_help'] =='plus') {				
				if ( !AlreadyCommentplus( $post_id ) ) {
					update_comment_meta( $post_id, "recomm_plus", ++$comm_plus ); // +1 count post meta
					update_comment_meta( $post_id, "_user_IP_comm_help", $liked_IPS ); // Add user IP to post meta					
				} 		
			}
			if ($_POST['comm_help'] =='minus') {
				if ( !AlreadyCommentplus( $post_id ) ) {
					update_comment_meta( $post_id, "recomm_minus", ++$comm_minus ); // +1 count to unhelpful
					update_comment_meta( $post_id, "_user_IP_comm_help", $liked_IPS ); // Add user IP to post meta					
				} 										
			}
		}
	}
	exit;
}

function AlreadyCommentplus( $post_id ) { // test if user liked before
	
	if ( is_user_logged_in() ) { // user is logged in
		global $current_user;
		$user_id = $current_user->ID; // current user
		$meta_USERS = get_comment_meta( $post_id, "_user_comm_help" ); // user ids from post meta
		$liked_USERS = ""; // set up array variable		
		if ( is_array($meta_USERS) && count( $meta_USERS ) != 0 ) { // meta exists, set up values
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
		$meta_IPS = get_comment_meta($post_id, "_user_IP_comm_help"); // get previously voted IP address
		$ip = rehub_get_ip(); // Retrieve current user IP
		$liked_IPS = ""; // set up array variable
		if ( count( $meta_IPS ) != 0 ) { // meta exists, set up values
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

function getCommentLike_re( $comment_text  ) {
	wp_enqueue_script('rhcommentvote');
	$post_id = get_comment_ID();	
	$comm_plus = get_comment_meta( $post_id, "recomm_plus", true ); // get helpful comment count
	$comm_minus = get_comment_meta( $post_id, "recomm_minus", true ); // get unhelpful comment count	
	if ( ( !$comm_plus ) || ( $comm_plus && $comm_plus == "0" ) ) { // no votes, set up empty variable
		$comm_plus_count = '0';
	} elseif ( $comm_plus && $comm_plus != "0" ) { // there are votes!
		$comm_plus_count = esc_attr( $comm_plus );
	}
	if ( ( !$comm_minus ) || ( $comm_minus && $comm_minus == "0" ) ) { // no votes, set up empty variable
		$comm_minus_count = '0';
	} elseif ( $comm_minus && $comm_minus != "0" ) { // there are votes!
		$comm_minus_count = esc_attr( $comm_minus );
	}	
	$already = (AlreadyCommentplus( $post_id )) ? ' alreadycomment' : '';
	$output = '<div class="user-review-vote lineheight20 pt10 mt10 border-top flowhidden" id="commhelp'.$post_id.'">';
	$output .= '<span class="font80 lineheight15 floatleft mr10 rtlml10 cursorpointer padforbuttonsmall upper-text-trans csstransall us-rev-vote-up'.$already.'" data-post_id="'.$post_id.'" data-informer="'.$comm_plus_count.'"><i class="floatleft mr5 rhi-thumbs-up rhicon rtlml5"></i> <span class="comm_help_title floatleft mr5 rtlml5">'.__('Helpful', 'rehub-theme').'</span>(<span class="help_up_count" id="commhelpplus'.$post_id.'">'.$comm_plus_count.'</span>)</span>'; 
	$output .= '<span class="font80 lineheight15 floatleft mr10 rtlml10 cursorpointer padforbuttonsmall upper-text-trans csstransall us-rev-vote-down'.$already.'" data-post_id="'.$post_id.'" data-informer="'.$comm_minus_count.'"><i class="rhicon rhi-thumbs-down floatleft mr5 rtlml5"></i> <span class="comm_help_title floatleft mr5 rtlml5">'.__('Unhelpful', 'rehub-theme').'</span>(<span class="help_up_count" id="commhelpminus'.$post_id.'">'.$comm_minus_count.'</span>)</span>'; 
	$output .= '<span class="already_commhelp clearbox mt5 font70 rhhidden">'.__('You have already voted this', 'rehub-theme').'</span></div>';

		return $comment_text.$output;
}