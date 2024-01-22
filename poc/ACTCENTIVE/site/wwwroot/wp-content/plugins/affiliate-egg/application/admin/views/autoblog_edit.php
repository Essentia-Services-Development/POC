<?php defined('\ABSPATH') || exit; ?>
<div class="wrap">
    <h2>
        <?php if ($item['id']): ?>
            <?php _e('Edit autoblog', 'affegg'); ?>
        <?php else: ?>
            <?php _e('Add Autoblog', 'affegg'); ?>
        <?php endif; ?>
        <a class="add-new-h2" href="<?php echo get_admin_url(get_current_blog_id(), 'admin.php?page=affiliate-egg-autoblog'); ?>"><?php _e('Back to list', 'affegg'); ?></a>
    </h2>

    <?php if (!empty($notice)): ?>
        <div id="notice" class="error"><p><?php echo $notice ?></p></div>
    <?php endif; ?>
    <?php if (!empty($message)): ?>
        <div id="message" class="updated"><p><?php echo $message ?></p></div>
    <?php endif; ?>

    <div id="poststuff">    
        <p>

        </p>    
    </div>    

    <form id="form" method="POST">
        <input type="hidden" name="nonce" value="<?php echo $nonce; ?>"/>
        <input type="hidden" name="item[id]" value="<?php echo $item['id']; ?>"/>
        <div class="metabox-holder" id="poststuff">
            <div id="post-body">
                <div id="post-body-content">
                    <?php do_meta_boxes('person', 'normal', $item); ?>
                    <input type="submit" value="<?php _e('Save', 'affegg'); ?>" id="autoblog_submit" class="button-primary" name="submit">
                </div>
            </div>
        </div>
    </form>
</div>
