<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php 
global $post;
?>  
<?php
$taxonomy = rh_get_taxonomy_of_post( $post );
$categories = get_the_terms( $post->ID, $taxonomy );
$catname = '';
if( !is_wp_error($categories) && is_array($categories)){
    $category = $categories[0];
    $catname = $category->name;
}
?>
<?php
$enableimage = (isset($enableimage) && $enableimage !== 'false') ? $enableimage : '';
?>
<div class="flowhidden col_item rh-shadow3 rehub-sec-smooth rh-hover-up <?php echo ($enableimage) ? 'rehub-main-color-bg css-ani-trigger' : 'whitebg rh-main-bg-hover';?> csstransall position-relative rh-hovered-wrap">
<a class="abdfullwidth zind2" href="<?php the_permalink();?>" title="<?php the_title();?>"></a>
<div class="rh-borderinside rh-hovered-scale pointernone<?php echo ($enableimage) ? ' rh-hovered-scalebig' : '';?>"></div>
<div class="padd20 position-relative zind1">
    <div class="pt10 pr20 pl20 pb10">
        <div class="mt0 mb10 font70 colorgridtext upper-text-trans <?php echo ($enableimage) ? 'whitecolor' : 'rehub-main-color whitehovered';?> catforcgrid"><?php echo ''.$catname;?></div>
        <h3 class="mb30 mt0 font120 lineheight20 colorgridtitle <?php echo ($enableimage) ? 'whitecolor' : 'whitehovered';?>"><?php the_title();?></h3>
        <div class="mb15 font90 colorgridtext <?php echo ($enableimage) ? 'whitecolor rh_opacity_7' : 'greycolor whitehovered';?>  excerptforcgrid">                                 
            <?php kama_excerpt('maxchar=90'); ?>                       
        </div>
        <i class="rhicon <?php echo (is_rtl()) ? 'rhi-arrow-left' : 'rhi-arrow-right';?> <?php echo ($enableimage) ? 'whitecolor' : 'rehub-main-color whitehovered';?> font130 csstranstrans rh-hovered-rotate position-relative"></i>
    </div>                                     
</div>
<?php if($enableimage):?>
    <div class="abdfullwidth rh-fit-cover rh_opacity_3 rh-hovered-scalesmall csstranstrans">
        <?php echo WPSM_image_resizer::show_wp_image('large_inner'); ?>
    </div>
<?php endif;?>
</div>