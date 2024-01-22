<?php defined('\ABSPATH') || exit; ?>
<?php if (\ContentEgg\application\Plugin::isFree() || \ContentEgg\application\Plugin::isInactiveEnvato()): ?>
    <div class="cegg-maincol">
    <?php endif; ?>
    <div class="wrap">
        <h2>
            <?php if ($item['id']): ?>
                <?php esc_html_e('Edit autoblogging', 'content-egg'); ?>
            <?php else: ?>
                <?php esc_html_e('Add autoblogging', 'content-egg'); ?>
                <?php if ($batch): ?>
                    - <?php esc_html_e('bulk adding', 'content-egg'); ?>
                <?php endif; ?>
            <?php endif; ?>
            <?php if (!$batch && !$item['id']): ?>
                <a class="add-new-h2 button-primary" href="<?php echo esc_url_raw(\get_admin_url(\get_current_blog_id(), 'admin.php?page=content-egg-autoblog-edit--batch')); ?>"><?php esc_html_e('Bulk adding', 'content-egg'); ?></a>
            <?php endif; ?>
            <a class="add-new-h2" href="<?php echo esc_url_raw(\get_admin_url(\get_current_blog_id(), 'admin.php?page=content-egg-autoblog')); ?>"><?php esc_html_e('Back to list', 'content-egg'); ?></a>
        </h2>

        <?php if (!empty($notice)): ?>
            <div id="notice" class="error"><p><?php echo esc_html($notice) ?></p></div>
        <?php endif; ?>
        <?php if (!empty($message)): ?>
            <div id="message" class="updated"><p><?php echo esc_html($message) ?></p></div>
        <?php endif; ?>

        <div id="poststuff">    
            <p>
            </p>    
        </div>    
        <form action="<?php echo esc_url_raw(add_query_arg('noheader', 'true')); ?>" id="form" method="POST"<?php if ($batch) echo ' enctype="multipart/form-data" accept-charset="utf-8"'; ?>>
            <input type="hidden" name="nonce" value="<?php echo \esc_attr($nonce); ?>"/>
            <input type="hidden" name="item[id]" value="<?php echo \esc_attr($item['id']); ?>"/>
            <div class="metabox-holder" id="poststuff">
                <div id="post-body">
                    <div id="post-body-content">
                        <?php $item['batch'] = $batch; ?>
                        <?php do_meta_boxes('autoblog_create', 'normal', $item); ?>
                        <input type="submit" value="<?php esc_html_e('Save', 'content-egg'); ?>" id="autoblog_submit" class="button-primary" name="submit">

                        &nbsp;&nbsp;&nbsp;<?php if ($batch): ?><em><?php esc_html_e('Don\'t close page until process finishes. Be patient, can have some time.', 'content-egg'); ?></em><?php endif; ?>

                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        jQuery(document).ready(function () {
            jQuery("#form").submit(function () {
                jQuery("#autoblog_submit").attr("disabled", true);
                return true;
            });
        });
    </script>        

    <?php if (\ContentEgg\application\Plugin::isFree() || \ContentEgg\application\Plugin::isInactiveEnvato()): ?>
    </div>    
    <?php include('_promo_box.php'); ?>
<?php endif; ?>  