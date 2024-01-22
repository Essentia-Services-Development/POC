<?php
/*
 * Name: Top listing
 * Modules:
 * Module Types: PRODUCT
 * 
 */

use ContentEgg\application\helpers\TemplateHelper;

$all_items = TemplateHelper::mergeAll($data, $order);
$ratings = TemplateHelper::generateStaticRatings(count($all_items));
?>



<div class="egg-container cegg-top-listing">
    <?php if ($title): ?>
        <h3><?php echo \esc_html($title); ?></h3>
    <?php endif; ?>

    <div class="egg-listcontainer">

        <?php foreach ($all_items as $i => $item): ?>    

            <div class="row-products row">
                <div class="col-md-2 col-sm-2 col-xs-3 cegg-image-cell">

                    <div class="cegg-position-container2">
                        <span class="cegg-position-text2"><?php echo (int) $i + 1; ?></span>
                    </div>

                    <?php if ($item['img']): ?>
                        <a<?php TemplateHelper::printRel(); ?> target="_blank" href="<?php echo esc_url_raw($item['url']); ?>">
                            <?php TemplateHelper::displayImage($item, 130, 100); ?>
                        </a>
                    <?php endif; ?>
                </div>
                <div class="col-md-6 col-sm-6 col-xs-6 cegg-desc-cell">

                    <?php if (strstr($item['description'], 'class="label')): ?>
                        <?php echo wp_kses_post($item['description']); ?>
                    <?php else: ?>
                        <?php if ($i == 0 && TemplateHelper::getChance($i)): ?>
                            <span class="label label-success">&check; <?php esc_html_e('Best choice', 'content-egg-tpl'); ?></span>
                        <?php elseif ($i == 1 && TemplateHelper::getChance($i)): ?>
                            <span class="label label-success"><?php esc_html_e('Recommended', 'content-egg-tpl'); ?></span>
                        <?php elseif ($i == 2 && TemplateHelper::getChance($i)): ?>
                            <span class="label label-success"><?php esc_html_e('High quality', 'content-egg-tpl'); ?></span>
                        <?php endif; ?>
                    <?php endif; ?>

                    <div class="cegg-no-top-margin cegg-list-logo-title">
                        <a<?php TemplateHelper::printRel(); ?> target="_blank" href="<?php echo esc_url_raw($item['url']); ?>"><?php echo \esc_html(TemplateHelper::truncate($item['title'], 100)); ?></a>
                    </div>
                    <div class="text-center cegg-mt10 visible-xs">
                        <a<?php TemplateHelper::printRel(); ?> target="_blank" href="<?php echo esc_url_raw($item['url']); ?>" class="btn btn-danger btn-block"><span><?php TemplateHelper::buyNowBtnText(true, $item, $btn_text); ?></span></a> 
                        <?php if ($merchant = TemplateHelper::getMerhantName($item)): ?>
                            <small class="text-muted title-case"><?php echo \esc_html($merchant); ?></small>
                        <?php endif; ?>
                    </div>  
                </div>

                <div class="col-md-2 col-sm-2 col-xs-3">   
                    <?php TemplateHelper::printProgressRing($ratings[$i]); ?>

                </div>                

                <div class="col-md-2 col-sm-2 col-xs-12 cegg-btn-cell hidden-xs">   
                    <div class="cegg-btn-row">
                        <a<?php TemplateHelper::printRel(); ?> target="_blank" href="<?php echo esc_url_raw($item['url']); ?>" class="btn btn-danger btn-block"><span><?php TemplateHelper::buyNowBtnText(true, $item, $btn_text); ?></span></a> 
                    </div>  
                    <?php if ($merchant = TemplateHelper::getMerhantName($item)): ?>
                        <div class="text-center">
                            <small class="text-muted title-case"><?php echo \esc_html($merchant); ?></small>
                        </div>
                    <?php endif; ?>

                </div>
            </div>



        <?php endforeach; ?>

    </div>
</div>


