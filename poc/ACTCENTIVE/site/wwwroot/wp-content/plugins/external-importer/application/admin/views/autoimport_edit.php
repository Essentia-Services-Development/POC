<?php defined('\ABSPATH') || exit; ?>
<div class="wrap">
    <h2>
        <?php if ($item['id']): ?>
            <?php _e('Edit auto import', 'external-importer'); ?>
        <?php else: ?>
            <?php _e('Create auto import', 'external-importer'); ?>
        <?php endif; ?>
        <a class="add-new-h2" href="<?php echo \get_admin_url(\get_current_blog_id(), 'admin.php?page=external-importer-autoimport'); ?>"><?php _e('Back to list', 'external-importer'); ?></a>
    </h2>

    <?php if (!empty($notice)): ?>
        <div id="notice" class="error"><p><?php echo $notice ?></p></div>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
        <div id="message" class="updated"><p><?php echo $message ?></p></div>
    <?php endif; ?>

    <form action="<?php echo \add_query_arg('noheader', 'true'); ?>" id="form" method="POST">
        <input type="hidden" name="nonce" value="<?php echo $nonce; ?>"/>
        <input type="hidden" name="item[id]" value="<?php echo $item['id']; ?>"/>
        <div class="metabox-holder" id="poststuff">
            <div id="post-body">
                <div id="post-body-content">
                    <?php \do_meta_boxes('autoimport_create', 'normal', $item); ?>
                    <input type="submit" value="<?php _e('Save', 'external-importer'); ?>" id="autoimport_submit" class="button-primary" name="submit">
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    jQuery(document).ready(function () {
        jQuery("#form").submit(function () {
            jQuery("#autoimport_submit").attr("disabled", true);
            return true;
        });
    });
</script>        

