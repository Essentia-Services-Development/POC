<?php
defined('\ABSPATH') || exit;

use ContentEgg\application\helpers\TemplateHelper;
?>

<?php
if ($disable_features)
{
    return;
}

?>

<?php if (!empty($item['extra']['specificationList'])): ?>

    <h4 class="cegg-no-top-margin"><?php TemplateHelper::esc_html_e('Specifications'); ?></h4>
    <table class='table table-condensed cegg-features-table'>
        <tbody>

            <?php foreach ($item['extra']['specificationList'] as $specificationList): ?>
                <?php
                if (!empty($specificationList['key']))
                {
                    echo '<tr><td colspan="2"><b>' . esc_html($specificationList['key']) . '</b></td></tr>';
                }
                ?>
                <?php foreach ($specificationList['values'] as $feature): ?>
                    <tr>
                        <td class='text-muted'><?php echo esc_html($feature['key']) ?></td>
                        <td><?php echo esc_html(join('; ', $feature['value'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php elseif (!empty($item['extra']['itemAttributes']['Feature'])): ?>
    <div class="cegg-features-box">
        <h4 class="cegg-no-top-margin"><?php TemplateHelper::esc_html_e('Features'); ?></h4>
        <ul class="cegg-feature-list">
            <?php foreach ($item['extra']['itemAttributes']['Feature'] as $k => $feature): ?>
                <li><?php echo esc_html($feature); ?></li>
                <?php
                if ($k >= 4)
                {
                    break;
                }
                ?>
            <?php endforeach; ?>
        </ul>
    </div>

<?php elseif (!empty($item['features'])): ?>
    <h4 class="cegg-no-top-margin"><?php TemplateHelper::esc_html_e('Features'); ?></h4>
    <table class='table table-condensed cegg-features-table'>
        <tbody>
            <?php foreach ($item['features'] as $feature): ?>
                <tr>
                    <td class='text-muted'><?php echo esc_html(__($feature['name'], 'content-egg-tpl')) ?></td>
                    <td><?php echo esc_html($feature['value']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php elseif (!empty($item['extra']['param'])): ?>

    <h4 class="cegg-no-top-margin"><?php TemplateHelper::esc_html_e('Features'); ?></h4>
    <table class='table table-condensed cegg-features-table'>
        <tbody>
            <?php foreach ($item['extra']['param'] as $fname => $fvalue): ?>
                <tr>
                    <td class='text-muted'><?php echo esc_html($fname) ?></td>
                    <td><?php echo esc_html($fvalue); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php elseif (!empty($item['extra']['features'])): ?>

    <h4 class="cegg-no-top-margin"><?php TemplateHelper::esc_html_e('Features'); ?></h4>
    <table class='table table-condensed cegg-features-table'>
        <tbody>
            <?php foreach ($item['extra']['features'] as $feature): ?>
                <tr>
                    <td class='text-muted'><?php echo esc_html(__($feature['name'], 'content-egg-tpl')) ?></td>
                    <td><?php echo esc_html($feature['value']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php elseif (!empty($item['extra']['properties'])): ?>

    <h4 class="cegg-no-top-margin"><?php TemplateHelper::esc_html_e('Features'); ?></h4>
    <table class='table table-condensed cegg-features-table'>
        <tbody>
            <?php foreach ($item['extra']['properties'] as $property): ?>
                <tr>
                    <td class='text-muted'><?php echo esc_html(__($feature['name'], 'content-egg-tpl')) ?></td>
                    <td><?php echo esc_html($property['value']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<?php elseif (!empty($item['extra']['keySpecs'])): ?>
    <div class="cegg-features-box">
        <h4 class="cegg-no-top-margin"><?php TemplateHelper::esc_html_e('Features'); ?></h4>
        <ul class="cegg-feature-list">
            <?php foreach ($item['extra']['keySpecs'] as $feature): ?>
                <li><?php echo esc_html($feature); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php elseif (!empty($item['extra']['Detail'])): ?>

    <h4 class="cegg-no-top-margin"><?php TemplateHelper::esc_html_e('Features'); ?></h4>
    <table class='table table-condensed cegg-features-table'>
        <tbody>
            <?php foreach ($item['extra']['Detail'] as $name => $value): ?>
                <tr>
                    <td class='text-muted'><?php echo esc_html($name) ?></td>
                    <td><?php echo esc_html($value); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

<?php endif; ?>
