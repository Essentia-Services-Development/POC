<?php
defined('\ABSPATH') || exit;
/*
  Name: Compare (deprecated)
 */

__('Compare', 'content-egg-tpl');

use ContentEgg\application\helpers\TemplateHelper;
?>

<?php
\wp_enqueue_style('egg-bootstrap');
\wp_enqueue_style('egg-products');
?>

<?php
$barcodes = array(
    'ISBN' => 'ISBN',
    'EAN' => 'EAN',
    'MPN' => 'MPN',
    'SKU' => 'SKU',
    'UPC' => 'UPC',
    'Model' => 'Model',
    'PartNumber' => 'Part Number',
);
?>

<div class="egg-container egg-compare">
    <?php if ($title): ?>
        <h3><?php echo esc_html($title); ?></h3>
    <?php endif; ?>

    <?php $length = 2; ?>
    <?php for ($offset = 0; $offset < count($items); $offset += $length): ?>

        <?php $current_items = array_slice($items, $offset, $length); ?>
        <?php $first = reset($current_items); ?>
        <div class="row">
            <div class="col-xs-12 col-md-2 text-info">
                <?php esc_html_e('Compare', 'content-egg-tpl'); ?>
            </div>
            <?php foreach ($current_items as $item): ?>
                <div class="col-xs-6 col-md-5">
                    <?php if ($item['img']): ?>
                        <?php
                        $img = $item['img'];
                        if (strstr($item['img'], 'images-amazon.com'))
                        {
                            $img = str_replace('.jpg', '._AA300_.jpg', $img);
                        }
                        ?>
                        <a<?php TemplateHelper::printRel(); ?> target="_blank" href="<?php echo esc_url_raw($item['url']); ?>">
                            <img class="img-responsive" src="<?php echo esc_attr($img) ?>"
                                 alt="<?php echo esc_attr($item['title']); ?>"/>
                        </a>
                        <br>
                    <?php endif; ?>
                    <h3><?php echo esc_html(TemplateHelper::truncate($item['title'], 120)); ?></h3>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-2 text-info">
                <?php esc_html_e('Price', 'content-egg-tpl'); ?>
            </div>
            <?php foreach ($current_items as $item): ?>
                <div class="col-xs-6 col-md-5 text-center ">
                    <?php if ($item['price']): ?>
                        <span class="cegg-price">
                            <?php echo wp_kses(TemplateHelper::formatPriceCurrency($item['price'], $item['currencyCode'], '<small>', '</small>'), array('small')); ?>
                        </span>
                        <?php if ($item['priceOld']): ?>
                            <br><s class="text-muted"><?php echo esc_html(TemplateHelper::formatPriceCurrency($item['priceOld'], $item['currencyCode'])); ?></s>
                        <?php endif; ?>
                    <?php elseif ($item['extra']['toLowToDisplay']): ?>
                        <span class="text-muted"><?php esc_html_e('Too low to display', 'content-egg-tpl'); ?></span>
                    <?php endif; ?>
                    <?php if ((bool) $item['extra']['IsEligibleForSuperSaverShipping']): ?>
                        <p class="text-muted"><small><?php esc_html_e('Free shipping', 'content-egg-tpl'); ?></small></p>
                    <?php endif; ?>

                    <span class="text-muted">
                        <?php if (!empty($item['extra']['totalNew'])): ?>
                            <?php echo esc_html($item['extra']['totalNew']); ?>
                            <?php esc_html_e('new', 'content-egg-tpl'); ?>
                            <?php if ($item['extra']['lowestNewPrice']): ?>
                                <?php esc_html_e('from', 'content-egg-tpl'); ?><?php echo esc_html(TemplateHelper::formatPriceCurrency($item['extra']['lowestNewPrice'], $item['currencyCode'])); ?>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if (!empty($item['extra']['totalUsed'])): ?>
                            <br><?php echo esc_html($item['extra']['totalUsed']); ?>
                            <?php esc_html_e('used', 'content-egg-tpl'); ?><?php esc_html_e('from', 'content-egg-tpl'); ?>
                            <?php echo esc_html(TemplateHelper::formatPriceCurrency($item['extra']['lowestUsedPrice'], $item['currencyCode'])); ?>
                        <?php endif; ?>
                    </span>
                    <span class="text-muted">
                        <br/><?php echo esc_html(sprintf(TemplateHelper::__('as of %s'), TemplateHelper::getLastUpdateFormatted('Amazon', $post_id))); ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-2 text-info">
                <?php esc_html_e('Shop Now', 'content-egg-tpl'); ?>
            </div>
            <?php foreach ($current_items as $item): ?>
                <div class="col-xs-6 col-md-5 text-center">
                    <a<?php TemplateHelper::printRel(); ?> target="_blank" href="<?php echo esc_url_raw($item['url']); ?>"
                                                           class="btn btn-danger"><?php TemplateHelper::buyNowBtnText(true, $item, $btn_text); ?></a>
                    <div class="title-case text-muted"><?php TemplateHelper::getMerhantName($item, true); ?></div>

                </div>
            <?php endforeach; ?>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-2 text-info">
                <?php esc_html_e('Features', 'content-egg-tpl'); ?>
            </div>
            <?php foreach ($current_items as $item): ?>
                <div class="col-xs-6 col-md-5">
                    <?php if (!empty($item['extra']['itemAttributes']['Feature'])): ?>
                        <ul>
                            <?php foreach ($item['extra']['itemAttributes']['Feature'] as $k => $feature): ?>
                                <li><?php echo esc_html(TemplateHelper::truncate(__($feature, 'content-egg-tpl'), 100)); ?></li>
                                <?php
                                if ($k >= 3)
                                {
                                    break;
                                }
                                ?>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <?php
        $lines = array();
        $i = 0;
        foreach ($current_items as $item)
        {
            foreach ($item['extra']['itemAttributes'] as $attribute => $value)
            {
                if (!is_string($value) && !is_integer($value) || !$value || array_key_exists($attribute, $barcodes))
                {
                    continue;
                }
                if (!isset($lines[$attribute]))
                {
                    $lines[$attribute] = array();
                }
                $lines[$attribute][$i] = $value;
            }
            $i++;
        }
        ?>
        <?php foreach ($lines as $attribute => $line): ?>
            <div class="row">
                <div class="col-xs-12 col-md-2 text-info">
                    <?php esc_html_e(TemplateHelper::splitAttributeName($attribute), 'content-egg-tpl'); ?>
                </div>
                <?php for ($i = 0; $i < count($current_items); $i++): ?>
                    <div class="col-xs-6 col-md-5">
                        <?php if (isset($line[$i])): ?>
                            <?php echo esc_html($line[$i]); ?>
                        <?php endif; ?>
                    </div>
                <?php endfor; ?>
            </div>
        <?php endforeach; ?>

        <?php if (!empty($first['extra']['customerReviews'])): ?>
            <div class="row">
                <div class="col-xs-12 col-md-2 text-info">
                    <?php esc_html_e('User Reviews', 'content-egg-tpl'); ?>
                </div>
                <?php foreach ($current_items as $item): ?>
                    <div class="col-xs-6 col-md-5 products">
                        <?php if (!empty($item['extra']['customerReviews']['reviews'])): ?>
                            <?php foreach ($item['extra']['customerReviews']['reviews'] as $review): ?>
                                <div>
                                    <em><?php echo esc_html($review['Summary']); ?>,
                                        <small><?php echo esc_html(TemplateHelper::formatDate($review['Date'])); ?></small></em>

                                </div>
                                <p><?php echo esc_html($review['Content']); ?></p>
                            <?php endforeach; ?>
                        <?php elseif ($item['extra']['customerReviews']['HasReviews'] == 'true'): ?>
                            <iframe src='<?php echo esc_url_raw($item['extra']['customerReviews']['IFrameURL']); ?>' width='100%'
                                    height='500'></iframe>
                                <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($first['extra']['editorialReviews'])): ?>
            <div class="row">
                <div class="col-xs-12 col-md-2 text-info">
                    <?php esc_html_e('Expert Reviews', 'content-egg-tpl'); ?>
                </div>
                <?php foreach ($current_items as $item): ?>
                    <div class="col-xs-6 col-md-5 products">
                        <?php if ($item['extra']['editorialReviews']): ?>
                            <?php $review = $item['extra']['editorialReviews'][0]; ?>
                            <p><?php echo esc_html($review['Content']); ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>


        <div class="row">
            <div class="col-xs-12 col-md-2 text-info">
                <?php esc_html_e('Shop Now', 'content-egg-tpl'); ?>
            </div>
            <?php foreach ($current_items as $item): ?>
                <div class="col-xs-6 col-md-5 text-center">
                    <a<?php TemplateHelper::printRel(); ?> target="_blank" href="<?php echo esc_url_raw($item['url']); ?>"
                                                           class="btn btn-danger"><?php TemplateHelper::buyNowBtnText(true, $item); ?></a>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="row">
            <div class="col-xs-12 col-md-2 text-info">
                <?php esc_html_e('Images', 'content-egg-tpl'); ?>
            </div>
            <?php foreach ($current_items as $item): ?>
                <div class="col-xs-6 col-md-5">
                    <?php if (!empty($item['extra']['imageSet'][1])): ?>
                        <?php $img = str_replace('.jpg', '._AA300_.jpg', $item['extra']['imageSet'][1]['LargeImage']); ?>
                        <img class="img-responsive" src="<?php echo esc_attr($img) ?>"
                             alt="<?php echo esc_attr($item['title']); ?>"/>
                         <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endfor; ?>
</div>