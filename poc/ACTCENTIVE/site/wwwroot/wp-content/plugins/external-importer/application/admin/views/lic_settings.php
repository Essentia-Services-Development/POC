<?php defined('\ABSPATH') || exit; ?>
<div class="wrap">
    <h2><?php echo sprintf(__('%s license', 'external-importer'), \ExternalImporter\application\Plugin::getName()); ?></h2>
    
    <?php \settings_errors(); ?>
    
    <form action="options.php" method="POST">
        <?php \settings_fields($page_slug); ?>
        <table class="form-table">
            <?php \do_settings_fields($page_slug, 'default'); ?>
        </table>
        <?php \submit_button(__('Activate license', 'external-importer')); ?>
    </form>

    <?php if (\ExternalImporter\application\Plugin::isActivated()): ?>
        <h2><?php _e('Deactivate license', 'external-importer'); ?></h2>
        <?php _e('You can transfer your license to a new domain.', 'external-importer'); ?>
        <?php _e('After deactivating license, you must deactivate and delete External Importer from your current domain.', 'external-importer'); ?>
        <br>
        <br>
        <form action="<?php echo \get_admin_url(\get_current_blog_id(), 'admin.php?page=external-importer-lic'); ?>" method="POST">
            <input type="hidden" name="cmd" id="cmd" value="lic_reset"  />            
            <input type="hidden" name="nonce_reset" value="<?php echo \wp_create_nonce('license_reset'); ?>"/>
            <input type="submit" name="submit2" id="submit2" class="button submitdelete deletion" value="<?php _e('Deactivate license', 'external-importer'); ?>"  />            
        </form>
    <?php endif; ?>         
</div>