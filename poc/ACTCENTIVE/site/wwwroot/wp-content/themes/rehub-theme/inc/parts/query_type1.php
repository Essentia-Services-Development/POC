<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly 
?>
<?php global $post; ?>
<?php
$dealcat = '';
if (rehub_option('enable_brand_taxonomy') == 1) {
	$dealcats = wp_get_post_terms($post->ID, 'dealstore', array("fields" => "all"));
	if (!empty($dealcats) && !is_wp_error($dealcats)) {
		$dealcat = $dealcats[0];
	}
}
?>
<?php
$type = (isset($type)) ? $type : '';
?>
<?php if ($type == '2') : ?>
	<div class="magazinenews position-relative mb30 clearfix">
		<div class="border-grey-bottom flowhidden pb15 rh-flex-center-align">
			<div class="font250 hideonsmobile rehub-sec-color width-100 rh-num-counter rem-h1"></div>
			<div class="magazinenews-desc rh-flex-grow1">
				<h3 class="mt0 mb20 rem-h3">
					<a href="<?php the_permalink(); ?>">
						<?php $title = get_the_title();
						$title = explode(" ", $title, 3);
						if (!empty($title[2])) {
							echo '<span class="rehub-main-color">' . $title[0] . ' ' . $title[1] . '</span> ' . $title[2];
						}else if (!empty($title[1])) {
							echo '<span class="rehub-main-color">' . $title[0] . ' ' . '</span> ' . $title[1];
						}else {
							echo '' . $title[0];
						}
						?>
					</a>
				</h3>
				<div class="meta post-meta mb15">
					<?php
					$category = get_the_category($post->ID);
					if (!empty($category)) {
						$first_cat = $category[0]->term_id;
					} else {
						$first_cat = false;
					}
					meta_small(true, $first_cat, 'compactnoempty', false);
					?>
				</div>
				<?php rh_post_code_loop();?>
				<p class="mb15 greycolor hideonmobile lineheight20">
					<?php kama_excerpt('maxchar=150'); ?>
				</p>
			</div>
			<div class="magazinenews-img width-250 mobswidth-150 pl20 rh-flex-right-align">
				<figure class="height-150 img-width-auto text-center">
					<?php echo re_badge_create('ribbon'); ?>
					<a href="<?php the_permalink(); ?>">
						<?php WPSM_image_resizer::show_static_resized_image(array('thumb' => true, 'title' => get_the_title(), 'width' => 220, 'height' => 145, 'no_thumb_url' => get_template_directory_uri() . '/images/default/noimage_336_220.png')); ?>
					</a>
				</figure>
			</div>
		</div>
	</div>
<?php else : ?>
	<div class="news-community clearfix<?php echo rh_expired_or_not($post->ID, 'class'); ?>">
		<?php echo re_badge_create('ribbonleft'); ?>
		<div class="rh_grid_image_wrapper">
			<div class="newsimage rh_gr_img">
				<figure>
					<?php if (function_exists('RHF_get_wishlist')) : ?>
						<div class="favorrightside wishonimage"><?php echo RHF_get_wishlist($post->ID); ?></div>
					<?php endif; ?>
					<a href="<?php the_permalink(); ?>">
						<?php
						$showimg = new WPSM_image_resizer();
						$showimg->use_thumb = true;
						$height_figure_single = apply_filters('re_news_figure_height', 160);
						$showimg->height = $height_figure_single;
						$showimg->width = $height_figure_single;
						$showimg->crop = false;
						$showimg->title = get_the_title();
						$showimg->show_resized_image();
						?>
					</a>
				</figure>
			</div>
			<?php if (rehub_option('hotmeter_disable') != '1' && function_exists('RHgetHotLike')) : ?>
				<div class="newsdetail rh_gr_top_right mb5">
					<?php echo RHgetHotLike(get_the_ID()); ?>
				</div>
			<?php endif; ?>
			<div class="newsdetail newstitleblock rh_gr_right_sec">
				<?php echo rh_expired_or_not($post->ID, 'span'); ?><h2 class="font130 mt0 mb10 mobfont120 lineheight25"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
				<?php if (rehub_option('disable_btn_offer_loop') != '1') : ?>
					<?php rehub_generate_offerbtn('showme=price&wrapperclass=pricefont110 rehub-main-color mobpricefont90 fontbold mb5 mr10 lineheight20 floatleft'); ?>
					<?php
					$offer_price_old = get_post_meta($post->ID, 'rehub_offer_product_price_old', true);
					$offer_price_old = apply_filters('rehub_create_btn_price_old', $offer_price_old);
					if (!empty($offer_price_old)) {
						$offer_price = get_post_meta($post->ID, 'rehub_offer_product_price', true);
						$offer_price = apply_filters('rehub_create_btn_price', $offer_price);
						if (!empty($offer_price)) {
							$offer_pricesale = (float)rehub_price_clean($offer_price);
							$offer_priceold = (float)rehub_price_clean($offer_price_old);
							if ($offer_priceold != '0' && is_numeric($offer_priceold) && $offer_priceold > $offer_pricesale) {
								$off_proc = 0 - (100 - ($offer_pricesale / $offer_priceold) * 100);
								$off_proc = round($off_proc);
								echo '<span class="rh-label-string mr10 mb5 floatleft">' . $off_proc . '%</span>';
							}
						}
					}

					?>
					<span class="more-from-store-a mt0 floatleft ml0 mr10 mb5 lineheight20"><?php WPSM_Postfilters::re_show_brand_tax('list'); ?></span>
				<?php endif; ?>
				<?php $custom_notice = get_post_meta($post->ID, '_notice_custom', true); ?>
				<?php
				if ($custom_notice) {
					echo '<div class="rh_custom_notice mr10 mb5 lineheight20 floatleft fontbold font90 rehub-sec-color">' . esc_html($custom_notice) . '</div>';
				} elseif (!empty($dealcat)) {
					$dealcat_notice = get_term_meta($dealcat->term_id, 'cashback_notice', true);
					if ($dealcat_notice) {
						echo '<div class="rh_custom_notice mr10 mb5 lineheight20 floatleft fontbold font90 rehub-sec-color">' . esc_html($dealcat_notice) . '</div>';
					}
				}
				?>
				<div class="clearfix"></div>
			</div>
			<div class="newsdetail rh_gr_right_desc">
				<p class="font90 mobfont80 lineheight20 moblineheight15 mb15"><?php kama_excerpt('maxchar=160'); ?></p>
				<?php rh_post_code_loop();?>
				<?php $content = $post->post_content; ?>
				<?php if (false !== strpos($content, '[wpsm_update')) : ?>
					<?php
					$pattern = get_shortcode_regex();
					preg_match('/' . $pattern . '/s', $post->post_content, $matches);
					if (is_array($matches) && $matches[2] == 'wpsm_update') {
						$shortcode = $matches[0];
						echo do_shortcode($shortcode);
					}
					?>
				<?php else : ?>
					<?php

					$blocks = parse_blocks($content);
					$rendered = false;

					if (count($blocks) == 1 && $blocks[0]['blockName'] == null) {  // Non-Gutenberg posts
					} else {
						foreach ($blocks as $block) {

							if ($block['blockName'] == 'rehub/box' && !$rendered) {
								if (!empty($block['attrs']['takeDate'])) {
									echo render_block( $block );
								}
								$rendered = true;
							}
						}
					} ?>
				<?php endif; ?>
			</div>
			<div class="newsdetail newsbtn rh_gr_right_btn">
				<div class="rh-flex-center-align mobileblockdisplay">
					<div class="meta post-meta">
						<?php rh_post_header_meta('full', true, false, 'compactnoempty', false); ?>
					</div>
					<div class="rh-flex-right-align">
						<?php if (rehub_option('disable_btn_offer_loop') != '1') : ?>
							<?php rehub_generate_offerbtn('btn_more=yes&showme=button&wrapperclass=mobile_block_btnclock mb0'); ?>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
		<div class="newscom_head_ajax"></div>
		<div class="newscom_content_ajax"></div>
		<?php if (rehub_option('rehub_enable_expand') == 1) : ?>
			<?php wp_enqueue_script('rh-get-full-content', get_template_directory_uri() . '/js/getfullcontent.js', array('jquery', 'rehub'), '1.1', true); ?>
			<span class="showmefulln def_btn" data-postid="<?php echo (int)$post->ID; ?>" data-enabled="0"><?php esc_html_e('Expand', 'rehub-theme'); ?></span>
		<?php endif; ?>
	</div>
<?php endif; ?>