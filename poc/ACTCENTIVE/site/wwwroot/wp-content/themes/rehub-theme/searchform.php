<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php $search_text = (rehub_option("rehub_search_text")) ? rehub_option("rehub_search_text") : esc_html__("Search", "rehub-theme"); ?>
<?php $posttypes = rehub_option("rehub_search_ptypes");?>
<?php $defaulttype = ( class_exists( 'Woocommerce' )) ? 'product' : 'post';?>
<?php $posttype = (!empty($posttypes) && is_array($posttypes)) ? implode(',', $posttypes) : $defaulttype; ?>
<form  role="search" method="get" class="search-form" action="<?php echo home_url( '/' ); ?>">
  	<input type="text" name="s" placeholder="<?php echo esc_attr($search_text) ;?>" <?php if (rehub_option('rehub_ajax_search') == '1') {echo 'class="re-ajax-search" autocomplete="off"';} ?> data-posttype="<?php echo ''.$posttype;?>">
  	<?php if(rehub_option('rehub_search_ptypes') !='') {echo '<input type="hidden" name="post_type" value="'.$posttype.'" />';}?>
  	<button type="submit" class="btnsearch hideonmobile" aria-label="<?php echo esc_attr($search_text) ;?>"><i class="rhicon rhi-search"></i></button>
</form>
<?php if (rehub_option('rehub_ajax_search') == '1') {echo '<div class="re-aj-search-wrap rhscrollthin"></div>';} ?>