<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php global $post;?>
<?php $secondtype = (isset($secondtype)) ? $secondtype : '';?>
<?php $thirdtype = (isset($thirdtype)) ? $thirdtype : '';?>
<?php $colfclass = (!$thirdtype || $thirdtype=='no') ? 'wpsm-one-half' : 'wpsm-two-fifth';?>
<?php $colsecclass = (!$thirdtype || $thirdtype=='no') ? 'wpsm-one-half' : 'news_sec_col';?>

    <?php if($i == 1):?>
        <div class="news_first_col mobileblockdisplay mr0 ml0 mb30 <?php echo ''.$colfclass;?>">
            <?php $small = false;?>
            <?php include(rh_locate_template('inc/parts/newsblock.php')); ?>
        </div>
    <?php endif;?>
    <?php if($i == $secstart):?>
        <div class="mr0 ml0 mb30 mobileblockdisplay <?php echo ''.$colsecclass;?>">
    <?php endif;?>
        <?php if($i >= $secstart && $i <= $secend):?>
            <?php if($secondtype == '1'):?>
                <?php $image = true;?>
                <?php include(rh_locate_template('inc/parts/simplepostlist.php')); ?>
            <?php elseif($secondtype == '2'):?>
                <?php $image = false;?>
                <?php include(rh_locate_template('inc/parts/simplepostlist.php')); ?>
            <?php elseif($secondtype == '3'):?>
                <?php $small = true;?>
                <?php include(rh_locate_template('inc/parts/newsblock.php')); ?>
            <?php else:?> 
                <?php $image = true;?>   
                <?php include(rh_locate_template('inc/parts/simplepostlist.php')); ?>
            <?php endif;?>
        <?php endif;?>
    <?php if($i == $secend):?>
        </div>
    <?php endif;?>
    <?php if($thirdtype && $thirdtype!='no'):?>
        <?php if($i == $thirdstart):?>
            <div class="news_third_col mobileblockdisplay mr0 ml0 <?php echo ''.$colsecclass;?>">
        <?php endif;?>
            <?php if($i >= $thirdstart && $i <= $thirdend):?>
                <?php if($thirdtype == '1'):?>
                    <?php $image = true;?>
                    <?php include(rh_locate_template('inc/parts/simplepostlist.php')); ?>
                <?php elseif($thirdtype == '2'):?>
                    <?php $image = false;?>
                    <?php include(rh_locate_template('inc/parts/simplepostlist.php')); ?>
                <?php elseif($thirdtype == '3'):?>
                    <?php $small = true;?>
                    <?php include(rh_locate_template('inc/parts/newsblock.php')); ?>
                <?php else:?> 
                    <?php $image = true;?>   
                    <?php include(rh_locate_template('inc/parts/simplepostlist.php')); ?>
                <?php endif;?>
            <?php endif;?>
        <?php if($i == $thirdend):?>
            </div>
        <?php endif;?>
    <?php endif;?>