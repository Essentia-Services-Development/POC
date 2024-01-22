<?php defined('\ABSPATH') || exit; ?>
<div class="wrap">

    <h2>
        <?php _e('Dev tools', 'external-importer'); ?>
    </h2>

    <form action="<?php echo \add_query_arg('noheader', 'true'); ?>" id="form" method="POST">
        <input type="hidden" name="nonce" value="<?php echo $nonce; ?>"/>
        <div class="metabox-holder" id="poststuff">
            <div id="post-body">
                <div id="post-body-content">

                    <textarea name="product_urls" placeholder="Product URLs" rows="10" cols="100"></textarea>
                    <textarea name="listing_urls" placeholder="Listing URLs" rows="10" cols="100"></textarea>
                    <br />
                    <input type="submit" value="<?php _e('Go', 'external-importer'); ?>" class="button-primary" name="submit">
                </div>
            </div>
        </div>
    </form>



    <h3>Advanced parsers</h3>
    <?php $domains = \ExternalImporter\application\libs\pextractor\parser\advanced\AdvancedManager::getInstance()->getDomainList(true, true); ?>
    <?php echo 'Total: ' . count($domains); ?>
    <textarea cols="300" rows="10">
        <?php
        echo 'External Importer advanced parsers (as of ' . date('m/d/Y') . ')' . "\r\n";
        foreach ($domains as $i => $domain)
        {
            $num = $i + 1;
            echo '<tr><td';
            if ($num == 1)
                echo ' width="10"';
            echo '>' . $num . '</td><td>' . \esc_html($domain) . '</td></tr>';
        }
        ?>
    </textarea>


</div>
