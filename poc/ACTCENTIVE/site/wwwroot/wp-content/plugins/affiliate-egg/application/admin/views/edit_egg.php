<?php defined('\ABSPATH') || exit; ?>
<div id="affegg_waiting" style="display:none; text-align: center;"> 
    <h2><?php _e('Working... Please wait...', 'affegg'); ?></h2> 
    <p>
        <img src="<?php echo Keywordrush\AffiliateEgg\PLUGIN_RES ?>/img/admin/egg_waiting.gif" />
    </p>
</div>
<script type="text/javascript">
    var $j = jQuery.noConflict();
    $j(document).ready(function () {
        $j('#egg_submit').click(function () {
            $j.blockUI({message: $j('#affegg_waiting')});
            test();
        });
    });
</script>    
<div class="wrap">
    <h2>
        <?php if ($item['id']): ?>
            <?php _e('Edit storefront', 'affegg'); ?>
        <?php else: ?>
            <?php _e('Add storefront', 'affegg'); ?>
        <?php endif; ?>
        <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=affiliate-egg'); ?>"><?php _e('Back to list of storefronts', 'affegg'); ?></a>
    </h2>

    <?php if (!empty($notice)): ?>
        <div id="notice" class="error"><p><?php echo $notice ?></p></div>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
        <div id="message" class="updated"><p><?php echo $message ?></p></div>
    <?php endif; ?>

    <div id="poststuff">    
        <p>
            <?php _e('To create storefront, just add url of pages with products, each link from new line', 'affegg'); ?>
            <?php _e('You can also add url of catalog page on shop (it can be also page with search results, archive, etc) to bulk add list of products.', 'affegg'); ?>
            <?php _e('For this, use special symbols, for example:', 'affegg'); ?>
            <br><em>[catalog limit=10]http://supershop.com/super-catalog</em><br>
            <?php _e('Parameter <em>limit=10</em> shows how many products we need to take from catalog.', 'affegg'); ?>
        </p>    
    </div>    

    <form id="form" method="POST">
        <input type="hidden" name="nonce" value="<?php echo $nonce; ?>"/>
        <input type="hidden" name="item[id]" value="<?php echo $item['id']; ?>"/>
        <div class="metabox-holder" id="poststuff">
            <div id="post-body">
                <div id="post-body-content">
                    <?php do_meta_boxes('person', 'normal', $item); ?>
                    <input type="submit" value="<?php _e('Save', 'affegg'); ?>" id="egg_submit" class="button-primary" name="submit" onClick="popup('popUpDiv');">
                </div>
            </div>
        </div>
    </form>
</div>
