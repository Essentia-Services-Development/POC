<?php
/**
 * Display single product reviews (comments)
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product-reviews.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     4.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
global $product;

$review_count 		= $product->get_review_count();
$avg_rate_score 	= number_format( $product->get_average_rating(), 1 );
$rate_counts 		= WPSM_Woohelper::get_ratings_counts( $product );

if ( ! comments_open() || !function_exists('wc_review_ratings_enabled')) {
	return;
}

?>
<?php wp_enqueue_style('rhwoocomments');?>
<div id="reviews" class="woocommerce-Reviews">
	<h2 class="rh-heading-icon woocommerce-Reviews-title mb15 fontnormal font120"><?php
		$count = $product->get_review_count();
		if ($count && wc_review_ratings_enabled())
			printf( _n( '%s review for %s%s%s', '%s reviews for %s%s%s', $count, 'rehub-theme' ), $count, '<span class="rh-woo-section-sub">', get_the_title(), '</span>' );
		else
			esc_html_e( 'User Reviews', 'rehub-theme' );
	?>
	</h2>
	<div class="mb20 rh-line"></div>	
	<div class="mobileblockdisplay rh-flex-center-align">
		<div class="woo-rev-part pr30 pl30 text-center">
			<div class="woo-avg-rating">
				<span class="orangecolor font200 fontbold"><?php echo ''.$avg_rate_score;?></span> <span class="greycolor font90"><?php esc_html_e('out of', 'rehub-theme');?> 5</span>
				<div class="clearfix"></div>
				<?php 	
					if ( 0 < $avg_rate_score ) {
						echo '<div class="rh_woo_star rh_woo_star_big" title="'.sprintf( esc_html__( 'Rated %s out of', 'rehub-theme' ), (float)$avg_rate_score ).' 5">';
						echo wc_get_star_rating_html( $avg_rate_score, $review_count);
						echo '</div>';
					} 			
				?>
			</div>				
		</div>
		<div class="woo-rev-part pl20 pr20 rh-line-left rh-line-right rh-flex-grow1">
			<div class="woo-rating-bars">
				<?php for( $rating = 5; $rating > 0; $rating-- ) : ?>
				<div class="rating-bar">
					<div class="star-rating-wrap">
						<div class="rh_woo_star" title="<?php printf( esc_html__( 'Rated %s out of 5', 'rehub-theme' ), $rating ); ?>">
							<?php for ($i = 1; $i <= 5; $i++){
						    	if ($i <= $rating){
						    		$active = ' active';
						    	}else{
						    		$active ='';
						    	}
						        echo '<span class="rhwoostar rhwoostar'.$i.$active.'">&#9733;</span>';
								}
							?>
						</div>	

					</div>
					<?php 
						$rating_percentage = 0;
						if ( isset( $rate_counts[$rating] ) && $review_count !=0 ) {
							$rating_percentage = (round( $rate_counts[$rating] / $review_count, 2 ) * 100 );
						}
					?>
					<div class="rating-percentage-bar-wrap">
						<div class="rating-percentage-bar">
							<span style="width:<?php echo esc_attr( $rating_percentage ); ?>%" class="rating-percentage"></span>
						</div>
					</div>
					<?php if ( isset( $rate_counts[$rating] ) ) : ?>
					<div class="rating-count"><?php echo esc_html( $rate_counts[$rating] ); ?></div>
					<?php else : ?>
					<div class="rating-count zero">0</div>
					<?php endif; ?>
				</div>
				<?php endfor; ?>
			</div>		
		</div>
		<div class="woo-rev-part pl30 ml10 pr30 pt25 pb25 mobilecenterdisplay">
			<span class="wpsm-button medium rehub_main_btn rehub-main-smooth rehub_scroll" data-scrollto="#woo_comm_form"><?php esc_html_e('Write a review', 'rehub-theme');?></span>
		</div>
	</div>
	<div class="mb20 mt20 rh-line"></div>

	<div id="comments">
		<?php if ( have_comments() ) : ?>
			<?php wp_enqueue_script('rhcommentsort');?>
	        <div id="rehub-comments-tabs" class="rh_grey_tabs_span mb20 font90 lineheight20" data-postid = "<?php echo get_the_ID();?>">
	            <span data-tabID="1" data-posttype="product" class="active"><?php esc_html_e('Show all', 'rehub-theme'); ?></span>
	            <span data-tabID="2" data-posttype="product"><?php esc_html_e('Most Helpful', 'rehub-theme'); ?></span>
	            <span data-posttype="product" data-tabID="3"><?php esc_html_e('Highest Rating', 'rehub-theme'); ?></span>
	            <span data-posttype="product" data-tabID="4"><?php esc_html_e('Lowest Rating', 'rehub-theme'); ?></span>
	        </div>
	        <div id="tab-1">			
				<ol class="commentlist">
					<?php wp_list_comments( apply_filters( 'woocommerce_product_review_list_args', array( 'callback' => 'woocommerce_comments' ) ) ); ?>
				</ol>
				<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
					echo '<nav class="woocommerce-pagination">';
					paginate_comments_links( apply_filters( 'woocommerce_comment_pagination_args', array(
						'prev_text' => '&larr;',
						'next_text' => '&rarr;',
						'type'      => 'list',
					) ) );
					echo '</nav>';
				endif; ?>				
			</div>
			<ol id="loadcomment-list" class="commentlist"></ol>

		<?php else : ?>

			<p class="woocommerce-noreviews"><?php esc_html_e( 'There are no reviews yet.', 'rehub-theme' ); ?></p>

		<?php endif; ?>
	</div>

	<div id="woo_comm_form">

		<?php if ( get_option( 'woocommerce_review_rating_verification_required' ) === 'no' || wc_customer_bought_product( '', get_current_user_id(), $product->get_id() ) ) : ?>

			<div id="review_form_wrapper">
				<div id="review_form">
					<?php
						$commenter = wp_get_current_commenter();

						$comment_form = array(
							'title_reply'          => have_comments() ? esc_html__( 'Add a review', 'rehub-theme' ) : sprintf( esc_html__( 'Be the first to review &ldquo;%s&rdquo;', 'rehub-theme' ), get_the_title() ),
							'title_reply_to'       => esc_html__( 'Leave a Reply', 'rehub-theme' ),
							'comment_notes_after'  => '',
							'fields'               => array(
								'author' => '<p class="comment-form-author">' . '<label for="author">' . esc_html__( 'Name', 'rehub-theme' ) . ' <span class="required">*</span></label> ' .
								            '<input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30" required /></p>',
								'email'  => '<p class="comment-form-email"><label for="email">' . esc_html__( 'Email', 'rehub-theme' ) . ' <span class="required">*</span></label> ' .
								            '<input id="email" name="email" type="email" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30" required /></p>',
							),
							'label_submit'  => esc_html__( 'Submit', 'rehub-theme' ),
							'logged_in_as'  => '',
							'comment_field' => ''
						);

						if ( $account_page_url = wc_get_page_permalink( 'myaccount' ) ) {
							$comment_form['must_log_in'] = '<p class="must-log-in">' .  sprintf( esc_html__( 'You must be %s%s%s logged in %s to post a review.', 'rehub-theme' ), '<a href="', esc_url( $account_page_url ), '">', '</a>'  ) . '</p>';
						}

						if (wc_review_ratings_enabled()) {
							$usercomment = '';
							if(is_user_logged_in()){
								$currentuser = get_current_user_id();
								$usercomment = get_comments(array('user_id' => $currentuser, 'post_id' => $product->get_id()));								
							}
							else{
								$commentemail = (!empty($commenter['comment_author_email'])) ? $commenter['comment_author_email'] : '';
								if($commentemail){
									$usercomment = get_comments(array('author_email' => $commentemail, 'post_id' => $product->get_id()));				
								}								
							}
							if(empty($usercomment)){
								$comment_form['comment_field'] = '<p class="comment-form-rating"><label for="rating">' . esc_html__( 'Your Rating', 'rehub-theme' ) .'</label><select name="rating" id="rating" required>
									<option value="">' . esc_html__( 'Rate&hellip;', 'rehub-theme' ) . '</option>
									<option value="5">' . esc_html__( 'Perfect', 'rehub-theme' ) . '</option>
									<option value="4">' . esc_html__( 'Good', 'rehub-theme' ) . '</option>
									<option value="3">' . esc_html__( 'Average', 'rehub-theme' ) . '</option>
									<option value="2">' . esc_html__( 'Not that bad', 'rehub-theme' ) . '</option>
									<option value="1">' . esc_html__( 'Very Poor', 'rehub-theme' ) . '</option>
								</select></p>';								
							}

						}

						$comment_form['comment_field'] .= '<p class="comment-form-comment"><label for="comment">' . esc_html__( 'Your Review', 'rehub-theme' ) . ' <span class="required">*</span></label><textarea id="comment" name="comment" cols="45" rows="8" required></textarea></p>';

						comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $comment_form ) );
					?>
				</div>
			</div>
		<?php else : ?>
			<p class="woocommerce-verification-required"><?php esc_html_e( 'Only logged in customers who have purchased this product may leave a review.', 'rehub-theme' ); ?></p>
		<?php endif; ?>			

	</div>



	<div class="clear"></div>
</div>
