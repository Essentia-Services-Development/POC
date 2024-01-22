<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php $singleadscode = rehub_option('rehub_single_code');?>

<?php if($singleadscode) : ?>
	<?php $disableads = get_post_meta($post->ID, 'show_banner_ads', true);?>
	<?php if(!$disableads):?>
		<div class="single_custom_bottom mt10 mb10 margincenter text-center clearbox">
			<?php echo do_shortcode ($singleadscode); ?>
		</div>
		<div class="clearfix"></div>
	<?php endif;?>
<?php endif; ?>

<?php if(rehub_option('rehub_disable_share') =='1')  : ?>
<?php else :?>
    <?php include(rh_locate_template('inc/parts/post_share.php')); ?>  
<?php endif; ?>

<?php if(rehub_option('rehub_disable_prev') =='1')  : ?>
<?php else :?>
    <?php include(rh_locate_template('inc/parts/prevnext.php')); ?>                    
<?php endif; ?>                 

<?php if(rehub_option('rehub_disable_tags') =='1')  : ?>
<?php else :?>
	<div class="tags mb25">
		<?php global $post;?>
		<?php if($post->post_type == 'blog'):?>
			<p><?php echo get_the_term_list( $post->ID, 'blog_tag', '<span class="tags-title-post">'.__('Tags: ', 'rehub-theme').'</span>','','');?></p>
		<?php else:?>
	        <p><?php the_tags('<span class="tags-title-post">'.__('Tags: ', 'rehub-theme').'</span>',""); ?></p>
	    <?php endif; ?>
	</div>
<?php endif; ?>

<?php if(rehub_option('rehub_disable_author') =='1')  : ?>
<?php else :?>
    <?php rh_author_detail_box();?>
<?php endif; ?>               

<?php if(rehub_option('rehub_disable_relative') =='1')  : ?>
<?php else :?>
    <?php include(rh_locate_template('inc/parts/related_posts.php')); ?>
<?php endif; ?>  