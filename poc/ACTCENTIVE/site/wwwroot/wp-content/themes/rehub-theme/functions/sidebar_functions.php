<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php


//////////////////////////////////////////////////////////////////
// Register sidebar and footer widgets
//////////////////////////////////////////////////////////////////
if( !function_exists('rehub_register_sidebars') ) {
function rehub_register_sidebars() {

	register_sidebar(array(
		'id' => 'rhsidebar',
		'name' => esc_html__('Post Sidebar Area', 'rehub-theme'),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<div class="title">',
		'after_title' => '</div>',
	));
	register_sidebar(array(
		'id' => 'footerfirst',
		'name' => esc_html__('Footer 1', 'rehub-theme'),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<div class="title">',
		'after_title' => '</div>',
	));
	register_sidebar(array(
		'id' => 'footersecond',
		'name' => esc_html__('Footer 2', 'rehub-theme'),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<div class="title">',
		'after_title' => '</div>',
	));
	register_sidebar(array(
		'id' => 'footerthird',
		'name' => esc_html__('Footer 3', 'rehub-theme'),
		'before_widget' => '<div id="%1$s" class="widget last %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<div class="title">',
		'after_title' => '</div>',
	));
	register_sidebar(array(
		'id' => 'footercustom',
		'name' => esc_html__('Bottom Custom Footer Area', 'rehub-theme'),
		'before_widget' => '<div id="%1$s" class="footerside %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<div class="title">',
		'after_title' => '</div>',
	));	
	register_sidebar(array(
		'id' => 'rhcustomsidebar',
		'name' => esc_html__('Custom sidebar area', 'rehub-theme'),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<div class="title">',
		'after_title' => '</div>',
	));
	register_sidebar(array(
		'id' => 'rhcustomsidebarsec',
		'name' => esc_html__('Custom sidebar area 2', 'rehub-theme'),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<div class="title">',
		'after_title' => '</div>',
	));
	if(rehub_option('enable_brand_taxonomy') == 1){
		register_sidebar(array(
			'id' => 'dealstore-sidebar',
			'name' => esc_html__('Affiliate store archive sidebar', 'rehub-theme'),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<div class="title">',
			'after_title' => '</div>',
		));	
	}
	if(rehub_option('enable_blog_posttype') == 1){
		register_sidebar(array(
			'id' => 'blog-sidebar',
			'name' => esc_html__('Blog sidebar Area', 'rehub-theme'),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<div class="title">',
			'after_title' => '</div>',
		));	
	}
	if(function_exists('bp_is_active') ){
		register_sidebar(array(
			'id' => 'bprh-profile-sidebar',
			'name' => esc_html__('Buddypress Profile sidebar', 'rehub-theme'),
			'before_widget' => '<div id="%1$s" class="rh-cartbox widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<div class="widget-inner-title rehub-main-font">',
			'after_title' => '</div>',
		));	
	}	
	if(rehub_option('bp_group_widget_area') == 1 ){
		register_sidebar(array(
			'id' => 'bprh-group-sidebar',
			'name' => esc_html__('Buddypress Group sidebar', 'rehub-theme'),
			'before_widget' => '<div id="%1$s" class="rh-cartbox widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<div class="widget-inner-title rehub-main-font">',
			'after_title' => '</div>',
		));	
	}	
	if (class_exists('Woocommerce')) {
		register_sidebar(array(
			'id' => 'woostore-sidebar',
			'name' => esc_html__('Woo brand archive sidebar', 'rehub-theme'),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<div class="title">',
			'after_title' => '</div>',
		));	
		if (defined('wcv_plugin_dir') || class_exists( 'WeDevs_Dokan' )){
			register_sidebar(array(
				'id' => 'wcw-storepage-sidebar',
				'name' => esc_html__('Vendor store page sidebar', 'rehub-theme'),
				'before_widget' => '<div id="%1$s" class="rh-cartbox widget %2$s">',
				'after_widget' => '</div>',
				'before_title' => '<div class="widget-inner-title rehub-main-font">',
				'after_title' => '</div>',
			));			
		}
		register_sidebar(array(
			'id' => 'wooshopsidebar',
			'name' => esc_html__('Woocommerce shop sidebar', 'rehub-theme'),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<div class="title">',
			'after_title' => '</div>',
		));			
		register_sidebar(array(
			'id' => 'sidebarwooinner',
			'name' => esc_html__('Woocommerce product sidebar', 'rehub-theme'),
			'before_widget' => '<div id="%1$s" class="widget %2$s">',
			'after_widget' => '</div>',
			'before_title' => '<div class="title">',
			'after_title' => '</div>',
		));		
			
	}				
}
}
add_action( 'widgets_init', 'rehub_register_sidebars' );


//////////////////////////////////////////////////////////////////
// Sidebar widget functions
//////////////////////////////////////////////////////////////////

if( !function_exists('rehub_most_popular_widget_block') ) {
function rehub_most_popular_widget_block($basedby = 'hot', $number = 5) { ?>

	<?php 
	if ($basedby == 'hot') {$popular_posts = new WP_Query('showposts='.$number.'&meta_key=post_hot_count&orderby=meta_value_num&order=DESC&ignore_sticky_posts=1');}
	elseif ($basedby == 'views') {$popular_posts = new WP_Query('showposts='.$number.'&meta_key=rehub_views_mon&orderby=meta_value_num&order=DESC&ignore_sticky_posts=1');}
	else {$popular_posts = new WP_Query('showposts='.$number.'&orderby=comment_count&order=DESC&ignore_sticky_posts=1');}
	if($popular_posts->have_posts()): ?>
	
	
		<?php  $i=0; while ($popular_posts->have_posts()) : $popular_posts->the_post(); global $post; $i++; ?>
		
			<div class="clearfix flowhidden<?php if($i != $number): ?> mb15 pb15 border-grey-bottom<?php endif;?>">
	            <figure class="floatleft width-100 img-maxh-100 img-width-auto"><a href="<?php the_permalink();?>"><?php wpsm_thumb('minithumb'); ?></a></figure>
	            <div class="detail floatright width-100-calc pl15 rtlpr15">
		            <h5 class="mt0 lineheight20 fontnormal"><a href="<?php the_permalink();?>"><?php the_title();?></a></h5>
	            	<div class="post-meta">
	              		<?php $category = get_the_category($post->ID); $first_cat = $category[0]->term_id;?>
	                	<?php if ($basedby == 'views') {meta_small( false, $first_cat, false, true );} else {meta_small( false, $first_cat, true, false );}  ?>
	                </div>
	                <?php rehub_format_score('small') ?>
	            </div>
            </div>
		
		<?php endwhile; ?>
		<?php wp_reset_query(); ?>
		<?php endif;?>


<?php
}
}

if( !function_exists('rehub_latest_comment_widget_block') ) {
function rehub_latest_comment_widget_block() { ?>
<div class="last_comments_widget">

	<?php
		$args = array(
			'number'=> 5,
		);
		$comments_query = new WP_Comment_Query();
		$comments = $comments_query->query( $args );
		$i = 0;
	foreach ($comments as $comment) { 
		$i++;
	?>
		<div class="lastcomm-item font80 <?php if($i != 5): ?> mb15 pb15 border-grey-bottom<?php endif;?>">
			<div class="side-item comment">	
				<div>
					<span><strong><?php echo strip_tags($comment->comment_author); ?></strong></span> 
					<?php echo strip_tags($comment->com_excerpt); ?>...
					<span class="lastcomm-cat mt10 blockstyle fontitalic">
						<a href="<?php echo get_permalink($comment->ID); ?>#comment-<?php echo (int)$comment->comment_ID; ?>" title="<?php echo strip_tags($comment->comment_author); ?> - <?php echo strip_tags($comment->post_title); ?>"><?php echo strip_tags($comment->post_title); ?></a>
					</span>		
				</div>
			</div>
		</div>

	<?php } ?>

</div>
<?php
}
}

if( !function_exists('rehub_category_widget_block') ) {
function rehub_category_widget_block() { ?>

<div class="rh_category_tab">
	<style scoped>
        .rh_category_tab ul.cat_widget_custom {margin: 0;padding: 0;border: 0;list-style: none outside;overflow-y: auto;max-height: 166px;}
        .rh_category_tab ul.cat_widget_custom li {padding: 0 0 4px;list-style: none;font-size: 14px;line-height: 22px;}
        .rh_category_tab ul.cat_widget_custom li a, .category_tab ul.cat_widget_custom li span {padding: 1px 0;color: #111;}
        .rh_category_tab ul.cat_widget_custom li span.counts {padding: 0 2px;font-size: 80%;opacity: 0.8;}
        .rh_category_tab ul.cat_widget_custom li a:before {display: inline-block;font-size: 100%;margin-right: .618em;line-height: 1em;width: 1em;content: "\f111";color: #555;}
        .rh_category_tab ul.cat_widget_custom li a:hover:before, .rh_category_tab ul.cat_widget_custom li a.active:before {content: "\e907";color: #85c858;}
        .rh_category_tab ul.cat_widget_custom li a span.drop_list { float: none; font: 400 14px arial; color: #666; background-color: transparent; padding: 0 }
        .rh_category_tab ul.cat_widget_custom ul.children li { font-size: 12px; color: #787878; padding: 0 10px; margin-bottom: 3px;}
        .rh_category_tab ul.cat_widget_custom li ul.children li a span.drop_list { display: none; }
		.dark_sidebar .rh_category_tab ul.cat_widget_custom li a, .dark_sidebar .tabs-item .detail .post-meta a.cat{color:#fff;}
		.dark_sidebar .rh_category_tab ul.cat_widget_custom li a span.drop_list, .dark_sidebar .rh_category_tab ul.cat_widget_custom li span.counts { color: #ccc }
		.rtl .rh_category_tab ul.cat_widget_custom li a:before{margin-left: .618em;margin-right: 0;}
	</style>
	<ul class="cat_widget_custom">
	<?php 
	$codecustom = "
	   	jQuery('.cat_widget_custom .children').parent().find('a').append('<span class=\"drop_list\">&nbsp; +</span>');  
	      jQuery('.tabs-item .drop_list').click(function() {
	       jQuery(this).parent().parent().find('.children').slideToggle();
	        return false
	    }); 
	";
	wp_add_inline_script('rehub', $codecustom, array('jquery'), '1.0', true);?>
	<?php
		$variable = wp_list_categories('echo=0&show_count=1&title_li=');
		$variable = str_replace('</a> (', '</a> <span class="counts">(', $variable);
  		$variable = str_replace(')', ')</span>', $variable);
		echo ''.$variable;
	?>
	</ul>
</div>

<?php
}
}

if( !function_exists('rehub_get_social_links') ) {
function rehub_get_social_links($atts, $content = null){
	extract(shortcode_atts(array(
		'icon_size' => 'big',
	), $atts));	
	ob_start();
?>
	<div class="social_icon <?php echo esc_attr($icon_size); ?>_i">
		

		<?php if ( rehub_option('rehub_facebook') != '' ) :?>
			<a href="<?php echo rehub_option('rehub_facebook'); ?>" class="fb" rel="nofollow" target="_blank"><i class="rhicon rhi-facebook"></i></a>
		<?php endif;?>	

		<?php if ( rehub_option('rehub_twitter') != '' ) :?>
			<a href="<?php echo rehub_option('rehub_twitter'); ?>" class="tw" rel="nofollow" target="_blank"><i class="rhicon rhi-twitter"></i></a>
		<?php endif;?>

		<?php if ( rehub_option('rehub_tiktok') != '' ) :?>
			<style scope>.social_icon .ttk svg{width: 20px; height:20px; fill:#fff;transform: translateY(2px);}.social_icon.small_i .ttk svg{width: 14px;height:14px}.social_icon .ttk{background:black}</style>
			<a href="<?php echo rehub_option('rehub_tiktok'); ?>" class="ttk" rel="nofollow" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2859 3333" fill-rule="evenodd" clip-rule="evenodd"><path d="M2081 0c55 473 319 755 778 785v532c-266 26-499-61-770-225v995c0 1264-1378 1659-1932 753-356-583-138-1606 1004-1647v561c-87 14-180 36-265 65-254 86-398 247-358 531 77 544 1075 705 992-358V1h551z"/></svg></a>
		<?php endif;?>

		<?php if ( rehub_option('rehub_instagram') != '' ) :?>
			<a href="<?php echo rehub_option('rehub_instagram'); ?>" class="ins" rel="nofollow" target="_blank"><i class="rhicon rhi-instagram"></i></a>
		<?php endif;?>

		<?php if ( rehub_option('rehub_wa') != '' ) :?>
			<a href="<?php echo rehub_option('rehub_wa'); ?>" class="wa" rel="nofollow" target="_blank"><i class="rhicon rhi-whatsapp"></i></a>
		<?php endif;?>	

		<?php if ( rehub_option('rehub_youtube') != '' ) :?>
			<a href="<?php echo rehub_option('rehub_youtube'); ?>" class="yt" rel="nofollow" target="_blank"><i class="rhicon rhi-youtube"></i></a>
		<?php endif;?>

		<?php if ( rehub_option('rehub_vimeo') != '' ) :?>
			<a href="<?php echo rehub_option('rehub_vimeo'); ?>" class="vim" rel="nofollow" target="_blank"><i class="rhicon rhi-vimeo-square"></i></a>
		<?php endif;?>			
		
		<?php if ( rehub_option('rehub_pinterest') != '' ) :?>
			<a href="<?php echo rehub_option('rehub_pinterest'); ?>" class="pn" rel="nofollow" target="_blank"><i class="rhicon rhi-pinterest"></i></a>
		<?php endif;?>

		<?php if ( rehub_option('rehub_linkedin') != '' ) :?>
			<a href="<?php echo rehub_option('rehub_linkedin'); ?>" class="in" rel="nofollow" target="_blank"><i class="rhicon rhi-linkedin"></i></a>
		<?php endif;?>

		<?php if ( rehub_option('rehub_soundcloud') != '' ) :?>
			<a href="<?php echo rehub_option('rehub_soundcloud'); ?>" class="sc" rel="nofollow" target="_blank"><i class="rhicon rhi-soundcloud"></i></a>
		<?php endif;?>

		<?php if ( rehub_option('rehub_dribbble') != '' ) :?>
			<a href="<?php echo rehub_option('rehub_dribbble'); ?>" class="db" rel="nofollow" target="_blank"><i class="rhicon rhi-dribbble"></i></a>
		<?php endif;?>

		<?php if ( rehub_option('rehub_vk') != '' ) :?>
			<a href="<?php echo rehub_option('rehub_vk'); ?>" class="vk" rel="nofollow" target="_blank"><i class="rhicon rhi-vk"></i></a>
		<?php endif;?>	
		<?php if ( rehub_option('rehub_telegram') != '' ) :?>
			<a href="<?php echo rehub_option('rehub_telegram'); ?>" class="telegram" rel="nofollow" target="_blank"><i class="rhicon rhi-telegram"></i></a>
		<?php endif;?>
		<?php if ( rehub_option('discord') != '' ) :?>
			<a href="<?php echo rehub_option('discord'); ?>" class="dscord" rel="nofollow" target="_blank"><i class="rhicon rhi-discord"></i></a>
		<?php endif;?>			
		<?php if ( rehub_option('rehub_rss') != '' ) :?>
			<a href="<?php echo rehub_option('rehub_rss'); ?>" class="rss" rel="nofollow" target="_blank"><i class="rhicon rhi-rss"></i></a>
		<?php endif;?>																		
	</div>

<?php
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}
}


if( !function_exists('rehub_top_offers_widget_block_post') ) {
function rehub_top_offers_widget_block_post($tags = '', $number = '5', $order = '', $random = '', $orderby='', $notexpired = '', $comparebtn = '') { ?>

	<?php $args = array (
			'posts_per_page' => $number,
			'tag' => $tags,
			'ignore_sticky_posts' => '1',
		);
		if (!empty ($random)) {
			$args ['orderby'] = 'rand';
		}	
		if (!empty ($order)) {
			$args ['meta_key'] = $order;
			$args ['orderby'] = 'meta_value_num';
		}
		if (!empty ($orderby)) {
			$args ['order'] = $orderby;
		}
		if($notexpired){
			$args['tax_query'][] = array(
				'relation' => 'AND',
				array(
					'taxonomy' => 'offerexpiration',
					'field'    => 'name',
					'terms'    => 'yes',
					'operator' => 'NOT IN',
				)
			);
		} 		
	$offers = new WP_Query($args);
	if($offers->have_posts()): ?>
	
	<div class="rh_deal_block">
		<?php  $i =0; while ($offers->have_posts()) : $offers->the_post(); global $post; $i++; ?>	
			<?php $offer_coupon = get_post_meta( $post->ID, 'rehub_offer_product_coupon', true ); 
			$offer_coupon_mask = get_post_meta( $post->ID, 'rehub_offer_coupon_mask', true ); ?>
			<?php $offer_price_old = get_post_meta( $post->ID, 'rehub_offer_product_price_old', true );?>
			<?php $offer_price = get_post_meta( $post->ID, 'rehub_offer_product_price', true );?>
			<?php $offer_post_url = get_post_meta( $post->ID, 'rehub_offer_product_url', true );
				$offer_post_url = apply_filters('rehub_create_btn_url', $offer_post_url);
				$offer_url = apply_filters('rh_post_offer_url_filter', $offer_post_url );
				$offer_btn_text = get_post_meta( $post->ID, 'rehub_offer_btn_text', true );
			?>
			<div class="deal_block_row flowhidden clearbox<?php if($i != $number): ?> mb15 pb15 border-grey-bottom<?php endif;?>">
				<div class="deal-pic-wrapper width-80 floatleft text-center img-maxh-100">
					<a href="<?php the_permalink();?>">
						<?php wpsm_thumb('minithumb'); ?>
	            	</a>				
				</div>
	            <div class="rh-deal-details width-80-calc pl15 rtlpr15 floatright">
					<div class="rh-deal-name mb10"><h5 class="mt0 mb10 fontnormal"><a href="<?php the_permalink();?>"><?php the_title();?></a></h5></div>	            					
					<?php if(!empty($offer_coupon) && !$offer_coupon_mask) : ?>
						<div class="redemptionText flowhidden lightgreycolor font80 mb5 clearfix"><?php esc_html_e('Use Coupon Code:', 'rehub-theme');?><span class="border-grey-dashed code floatright fontbold greycolor mb10 pb5 pl5 pr5 pt5 whitebg"><?php echo esc_html($offer_coupon); ?></span></div>	
				  	<?php endif;?>
					<div class="rh-flex-columns rh-flex-nowrap">
						<div class="rh-deal-left">
							<?php if($offer_price):?>
								<div class="rh-deal-price mb10 fontbold font90">
									<span>
										<ins><?php echo esc_html($offer_price) ?></ins>
										<?php if($offer_price_old !='') :?>
											<del class="rh_opacity_3 blockstyle fontnormal blackcolor"><?php echo esc_html($offer_price_old) ; ?></del>
										<?php endif ;?>
									</span>
								</div>
							<?php endif;?>												
							<div class="rh-deal-tag greycolor font80 fontitalic">
								<?php WPSM_Postfilters::re_show_brand_tax('list'); //show brand logo?>					
							</div>
						</div>
						<div class="rh-deal-right rh-flex-right-align pl15">	
							<?php if($comparebtn):?>
							<?php else:?>
								<?php if($offer_post_url):?>
									<div class="rh-deal-btn mb10 text-right-align">
										<?php if($offer_coupon_mask && $offer_coupon):?>
								    		<div class="post_offer_anons">
								    			<?php wp_enqueue_script('zeroclipboard'); ?>
							                	<span class="coupon_btn rehub_offer_coupon mr0 re_track_btn btn_offer_block rh-deal-compact-btn padforbuttonsmall fontnormal font95 lineheight15 text-center inlinestyle masked_coupon" data-clipboard-text="<?php echo esc_html ($offer_coupon) ?>" data-codeid="<?php echo (int)$post->ID?>" data-dest="<?php echo esc_url($offer_url) ?>">
							                		<?php if($offer_btn_text !='') :?>
								            			<?php echo esc_html ($offer_btn_text) ; ?>
							                		<?php elseif(rehub_option('rehub_mask_text') !='') :?>
							                			<?php echo rehub_option('rehub_mask_text') ; ?>
							                		<?php else :?>
							                			<?php esc_html_e('Reveal coupon', 'rehub-theme') ?>
							                		<?php endif ;?>
							                	</span>
							            	</div>
										<?php else:?>
						                    <a href="<?php echo esc_url ($offer_url) ?>" class="re_track_btn rh-deal-compact-btn padforbuttonsmall fontnormal font95 lineheight15 text-center inlinestyle btn_offer_block" target="_blank" rel="nofollow">
						                        <?php if($offer_btn_text !='') :?>
						                            <?php echo esc_attr($offer_btn_text) ; ?>
						                        <?php elseif(rehub_option('rehub_btn_text') !='') :?>
						                            <?php echo rehub_option('rehub_btn_text') ; ?>
						                        <?php else :?>
						                            <?php esc_html_e('Buy this item', 'rehub-theme') ?>
						                        <?php endif ;?>
						                    </a>
										<?php endif;?>		            					
									</div>
								<?php endif;?>
						    <?php endif;?>																	
						</div>					
					</div>
	            </div>
            </div>
		
		<?php endwhile; ?>
		<?php wp_reset_query(); ?>
		<?php endif; ?>
	</div>

<?php
}
}


if( !function_exists('rehub_top_offers_widget_block_woo') ) {
function rehub_top_offers_widget_block_woo($tags = '', $number = '5', $order = '', $random = '', $orderby='', $notexpired = '', $comparebtn = '') { ?>

	<?php 
	$args = array (
			'posts_per_page' => $number,
			'ignore_sticky_posts' => '1',
			'post_type' => 'product',
		);
		if (!empty ($random)) {
			$args ['orderby'] = 'rand';
		}	
		if (!empty ($order)) {
			$args ['meta_key'] = $order;
			$args ['orderby'] = 'meta_value_num';
		}
		if (!empty ($orderby)) {
			$args ['order'] = $orderby;
		}
		if (!empty ($tags)) {          
			$tags = array_map( 'trim', explode( ",", $tags) );
	        $args['tax_query'] = array(array('taxonomy' => 'product_tag', 'terms' => $tags, 'field' => 'slug'));	
		}
		if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
			$args['tax_query'][] = array(
				'relation' => 'AND',
				array(
					'taxonomy' => 'product_visibility',
					'field'    => 'name',
					'terms'    => 'outofstock',
					'operator' => 'NOT IN',
				)
			);
		}					

	$wp_query = new WP_Query($args);
	if($wp_query->have_posts()): ?>
	
	<div class="rh_deal_block">
		<?php  $i=0; while ($wp_query->have_posts()) : $wp_query->the_post(); global $post; global $product; $i++ ?>	
			<?php $offer_coupon = get_post_meta( $post->ID, 'rehub_woo_coupon_code', true ) ?>
			<?php $offer_post_url = esc_url( $product->add_to_cart_url() );
				$offer_post_url = apply_filters('rehub_create_btn_url', $offer_post_url);
				$offer_url = apply_filters('rh_post_offer_url_filter', $offer_post_url );
				
			?>
			<div class="deal_block_row flowhidden clearbox<?php if($i != $number): ?> mb15 pb15 border-grey-bottom<?php endif;?>">
				<div class="deal-pic-wrapper width-80 floatleft text-center">
					<a href="<?php the_permalink();?>">
						<?php wpsm_thumb('minithumb'); ?>
	            	</a>				
				</div>
	            <div class="rh-deal-details width-80-calc pl15 rtlpr15 floatright">
					<div class="rh-deal-name mb10"><h5 class="mt0 mb10 fontnormal"><a href="<?php the_permalink();?>"><?php the_title();?></a></h5></div>	            					
					<div class="rh-flex-columns rh-flex-nowrap">
						<div class="rh-deal-left">
								<div class="rh-deal-price mb10 fontbold font90 lineheight15">
									<?php if ( $price_html = $product->get_price_html() ) : ?>
										<span class="price"><?php echo ''.$price_html; ?></span>
									<?php endif; ?>
								</div>												
							<div class="rh-deal-tag greycolor font80 fontitalic">
								<?php WPSM_Woohelper::re_show_brand_tax('list'); //show brand taxonomy?>					
							</div>
						</div>
						<div class="rh-deal-right rh-flex-right-align pl15">						
							<?php if(!empty($offer_coupon)) : ?>
								<div class="redemptionText flowhidden lightgreycolor mb5 clearfix">
			                        <?php wp_enqueue_script('zeroclipboard'); ?>
			                        <a class="coupon_btn rehub_offer_coupon mr0 re_track_btn btn_offer_block rh-deal-compact-btn padforbuttonsmall fontnormal font95 lineheight15 text-center inlinestyle masked_coupon" data-clipboard-text="<?php echo esc_html($offer_coupon); ?>" data-codeid="<?php echo ''.$product->get_id() ?>" data-dest="<?php echo esc_url($offer_url) ?>"><?php if(rehub_option('rehub_mask_text') !='') :?><?php echo rehub_option('rehub_mask_text') ; ?><?php else :?><?php esc_html_e('Reveal coupon', 'rehub-theme') ?><?php endif ;?>
			                        </a>							
								</div>
							<?php else :?>
								<div class="rh-deal-btn mb10 text-right-align">
									<?php if($comparebtn && (rehub_option('compare_page') || rehub_option('compare_multicats_textarea')) ):?>
                                        <?php 
                                            $cmp_btn_args = array(); 
                                            $cmp_btn_args['class']= 'rhwoosinglecompare mb15 rehub-main-smooth';
                                            $cmp_btn_args['id'] = $product->get_id();
                                            if(rehub_option('compare_woo_cats') != '') {
                                                $cmp_btn_args['woocats'] = esc_html(rehub_option('compare_woo_cats'));
                                            }
                                        ?>                                                  
                                        <?php echo wpsm_comparison_button($cmp_btn_args); ?>
									<?php else:?>

								    <?php endif;?>
					            </div>								
						  	<?php endif;?>										            								
						</div>					
					</div>
	            </div>
            </div>
		
		<?php endwhile; ?>
		<?php wp_reset_query(); ?>
		<?php endif; ?>
	</div>


<?php
}
}


?>