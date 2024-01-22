<?php
function import_wp_pro()
{
    global $iwp;

    if (!is_null($iwp)) {
        return $iwp;
    }

    $iwp = new ImportWP\Pro\ImportWPPro();
    $iwp->register();
    return $iwp;
}

function iwp_pro_cli_loaded()
{
    if (!function_exists('import_wp') || version_compare(IWP_VERSION, IWP_PRO_MIN_CORE_VERSION, '<')) {
        return;
    }

    // register with wp-cli if it's running, and command hasn't already been defined elsewhere
    if (defined('WP_CLI') && WP_CLI && class_exists('ImportWP\Pro\Cli\Command')) {
        \ImportWP\Pro\Cli\Command::register();
    }
}
add_action('plugins_loaded', 'iwp_pro_cli_loaded', 20);

function iwp_pro_loaded()
{
    if (!function_exists('import_wp') || version_compare(IWP_VERSION, IWP_PRO_MIN_CORE_VERSION, '<')) {

        $message = '<strong>Import WP PRO</strong> requires Import WP v' . IWP_PRO_MIN_CORE_VERSION . ' or greater to be installed and activated, <a href="' . admin_url('/plugin-install.php?s=jc-importer&tab=search&type=term') . '">Download Import WP here</a>.';

        add_action('admin_notices', function () use ($message) {

            global $pagenow;

            if (!in_array($pagenow, ['plugins.php', 'update-core.php', 'index.php'])) {
                return;
            }

            echo '<div class="notice notice-error is-dismissible">
             <p>' . $message . '</p>
         </div>';
        });

        // display error message after plugin row
        add_action('after_plugin_row_' . plugin_basename(dirname(__FILE__) . '/importwp-pro.php'), function () use ($message) {
?>
            <tr class="iwp-updater-license-row iwp-invalid">
                <td colspan="4"><?= $message; ?></td>
            </tr>
            <style>
                .iwp-updater-license-row td {
                    -webkit-box-shadow: 0px -1px 0 rgba(255, 255, 255, 0.1),
                        inset 0 -1px 0 rgba(0, 0, 0, 0.1);
                    box-shadow: 0px -1px 0 rgba(255, 255, 255, 0.1),
                        inset 0 -1px 0 rgba(0, 0, 0, 0.1);
                    border-left: 4px solid #ffb900;
                    background-color: #fff8e5;
                }

                .iwp-updater-license-row.iwp-invalid td {
                    border-left: 4px solid #dc3232;
                    background-color: #fef1f1;
                }

                .iwp-updater-license-row.iwp-valid td {
                    border-left: 4px solid #00a0d2;
                    background-color: #f7fcfe;
                }
            </style>
<?php
        });
        return;
    }

    if (function_exists(('import_wp_pro'))) {
        import_wp_pro();
    }
}
add_action('plugins_loaded', 'iwp_pro_loaded');

// Stop ImportWP Addons loading that are active when they shouldnt be
add_action('plugins_loaded', function () {
    remove_action('plugins_loaded', 'iwp_acf_setup', 9);
}, 1);
