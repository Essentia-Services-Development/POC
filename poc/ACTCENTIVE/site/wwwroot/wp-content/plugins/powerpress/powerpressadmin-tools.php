<style>

</style>

<?php
// powerpressadmin-tools.php

function powerpress_admin_tools(){
    $General = get_option('powerpress_general');

    // If we have powerpress credentials, check if the account has been verified
    $credentials = get_option('powerpress_creds');
    powerpress_check_credentials($credentials);
    wp_enqueue_script('powerpress-admin', powerpress_get_root_url() . 'js/admin.js', array(), POWERPRESS_VERSION);
    ?>

    <h2 class="pp-page-header"><?php echo __('PowerPress Tools', 'powerpress'); ?></h2>
    <h3 class="pp-page-h3"><?php echo __('Useful utilities and tools.', 'powerpress'); ?></h3>

    <div class="pp-card-body">
        <!-- Update Plugins Cache -->
        <div class="pp-row pp-tools-row">
            <h3 class="pp-page-h3-bold pp-tools-item">Update Plugins Cache</h3>
            <a href="<?php echo admin_url() . wp_nonce_url("admin.php?page=powerpress/powerpressadmin_tools.php&amp;action=powerpress-clear-update_plugins", 'powerpress-clear-update_plugins'); ?>" title="Clear Plugins Cache"
               class="powerpress_save_button_other pp-tools-button">CLEAR PLUGINS CACHE</a>
        </div>
        <div class="pp-row pp-tools-row">
            <p class="pp-tools-text">The list of plugins on the plugins page will cache the plugin version numbers for up to 24 hours.
                Click the link above to clear the cache to get the latest versions of plugins listed on your <a href="<?php echo admin_url() . 'plugins.php'?>">plugins</a> page.
            </p>
        </div>

        <hr>

        <!-- Translations -->
        <div class="pp-row pp-tools-row">
            <h3 class="pp-page-h3-bold pp-tools-item">Translations</h3>
            <a href="https://blubrry.com/support/powerpress-documentation/powerpress-language/translate-powerpress/" target="_blank" title="Translate PowerPress"
               class="powerpress_save_button_other pp-tools-button">TRANSLATE POWERPRESS</a>
        </div>
        <div class="pp-row pp-tools-row">
            <p class="pp-tools-text">PowerPress translations are managed on the official
                <a href="https://translate.wordpress.org/projects/wp-plugins/powerpress/" target="_blank">WordPress translate site</a>.
            </p>
        </div>

        <hr>

        <!-- Media URL Replacement -->
        <div class="pp-row pp-tools-row">
            <h3 class="pp-page-h3-bold pp-tools-item">Media URL Replacement</h3>
            <a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_tools.php&amp;action=powerpress-find-replace"); ?>" title="Find and Replace Media"
               class="powerpress_save_button_other pp-tools-button">REPLACE MEDIA URLS</a>
        </div>
        <div class="pp-row pp-tools-row">
            <p class="pp-tools-text">Find and replace complete or partial segments of media URLs.
                Useful if you move your media to a new website or service.
            </p>
        </div>

        <hr>

        <!-- Diagnostics -->
        <div class="pp-row pp-tools-row">
            <h3 class="pp-page-h3-bold pp-tools-item">Diagnostics</h3>
            <a href="<?php echo admin_url("admin.php?page=powerpress/powerpressadmin_tools.php&amp;action=powerpress-diagnostics"); ?>" title="Diagnose Your PowerPress Installation"
               class="powerpress_save_button_other pp-tools-button">RUN DIAGNOSTICS</a>
        </div>
        <div class="pp-row pp-tools-row">
            <p class="pp-tools-text">The Diagnostics page checks to see if your server is configured to support all the available features in Blubrry PowerPress.</p>
        </div>
    </div>
<?php } ?>