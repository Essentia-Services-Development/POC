<?php defined('\ABSPATH') || exit; ?>
<div class="wrap">
    <h2>Affiliate Egg <?php _e('License', 'affegg'); ?></h2>

    <?php \settings_errors(); ?>
    
    <form action="options.php" method="POST">
        <?php settings_fields($page_slug); ?>
        <table class="form-table">
            <?php \do_settings_fields($page_slug, 'default'); ?>
        </table>
        <?php \submit_button(__('Activate license', 'affegg')); ?>
    </form>

    <?php if (Keywordrush\AffiliateEgg\AffiliateEgg::isActivated()): ?>
        <h2><?php _e('Deactivate license', 'affegg'); ?></h2>
        <?php _e('You can transfer your license to another domain.', 'affegg'); ?>
        <?php _e('After deactivating license, you must deactivate and delete Affiliate Egg from current domain.', 'affegg'); ?>
        <br>
        <br>
        <form action="<?php echo \get_admin_url(\get_current_blog_id(), 'admin.php?page=affiliate-egg-lic'); ?>" method="POST">
            <input type="hidden" name="cmd" id="cmd" value="lic_reset"  />            
            <input type="hidden" name="nonce_reset" value="<?php echo \wp_create_nonce('license_reset_ae'); ?>"/>
            <input type="submit" name="submit2" id="submit2" class="button submitdelete deletion" value="<?php _e('Deactivate license', 'affegg'); ?>"  />
        </form>
    <?php endif; ?>       
</div>