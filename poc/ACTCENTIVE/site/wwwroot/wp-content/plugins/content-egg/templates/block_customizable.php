<?php

/*
 * Name: Customizable (use with "show" parameter)
 * Modules:
 * Module Types: PRODUCT
 * 
 */

__('Customizable (use with "show" parameter)', 'content-egg-tpl');

use ContentEgg\application\helpers\TemplateHelper;
?>

<?php foreach ($data as $module_id => $items): ?>
    <?php foreach ($items as $item): ?>
        <?php

        switch ($params['show'])
        {
            case 'title':
                echo \esc_html($item['title']);
                break;
            case 'img':
                echo '<img src="' . \esc_attr($item['img']) . '" alt="' . \esc_attr($item['title']) . '" />';
                break;
            case 'price':
                if ($item['price'])
                    echo esc_html(TemplateHelper::formatPriceCurrency($item['price'], $item['currencyCode']));
                break;
            case 'priceold':
                echo esc_html(TemplateHelper::formatPriceCurrency($item['priceOld'], $item['currencyCode']));
                break;
            case 'currencycode':
                echo \esc_html($item['currencyCode']);
                break;
            case 'button':
                echo '<span class="egg-container"><a';
                TemplateHelper::printRel();
                echo ' target="_blank" href="' . esc_url_raw($item['url']) . '" class="btn btn-danger">';
                TemplateHelper::buyNowBtnText(true, $item);
                echo '</a></span>';
                break;
            case 'stock_status':
                echo esc_html(TemplateHelper::getStockStatusStr($item));
                break;
            case 'description':
                echo wp_kses_post($item['description']);
                break;
            case 'url':
                echo esc_url_raw($item['url']);
                break;
            default:
                break;
        }
        ?>

    <?php endforeach; ?>  
<?php endforeach; ?>  

