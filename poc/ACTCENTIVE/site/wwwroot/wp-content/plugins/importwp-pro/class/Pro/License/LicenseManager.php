<?php

namespace ImportWP\Pro\License;

use ImportWP\Common\Properties\Properties;

class LicenseManager
{
    /**
     * @var Properties $properties
     */
    public $properties;

    public function __construct($properties)
    {
        $this->properties = $properties;

        add_action('after_plugin_row_' . $this->properties->plugin_pro_basename, [$this, 'render_license_form']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('wp_ajax_iwp_validate_license', [$this, 'ajax_validate_license']);
    }

    public function enqueue_scripts($hook)
    {
        wp_register_script('iwp-updater',  plugin_dir_url($this->properties->plugin_pro_file_path) . 'updater.js', ['jquery']);

        wp_localize_script('iwp-updater', 'iwp', [
            'nonce' => wp_create_nonce('iwp_validate_license'),
            'ajax_url' => admin_url('admin-ajax.php')
        ]);

        if ($hook == 'plugins.php') {
            wp_enqueue_style('iwp-updater');
            wp_enqueue_script('iwp-updater');
        }
    }

    public function render_license_form()
    {
        $is_valid = $this->is_valid_license(get_site_transient('iwp_api_token'));
?>
        <tr class="iwp-updater-license-row <?php echo sanitize_html_class($is_valid ? 'iwp-valid' : 'iwp-invalid'); ?>">
            <td colspan="5">
                <?php if ($is_valid) : ?>
                    <span class="iwp-updater-msg">Your license is active for ImportWP Pro</span>
                    <span class="iwp-invalid-hide">
                        &nbsp;-&nbsp;
                        <button type="button" class="iwp-updater-button iwp-updater-button--deactivate">Deactivate license</button>
                        <span class="spinner" style="float: none; vertical-align: top;"></span>
                    </span>
                <?php else : ?>
                    <span class="iwp-valid-hide">
                        <label for="">License key: <input class="iwp-license-input" type="text" placeholder="Enter license key" value="<?php echo get_site_option('iwp_access_token'); ?>" /></label>
                        <button type="button" class="iwp-updater-button iwp-updater-button--activate">Activate</button>
                        <span class="spinner" style="float: none; vertical-align: top;"></span>
                    </span>
                    <span class="iwp-updater-msg">Enter your license key to enable updates, you can <a href="https://importwp.com/register-license" target="_blank">obtain your license code here</a>.</span>
                <?php endif; ?>
            </td>
        </tr>
        <style type="text/css">
            .iwp-updater-license-row {}

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
                background-color: #fef7f1;
            }

            .iwp-updater-license-row.iwp-valid td {
                border-left: 4px solid #00a0d2;
                background-color: #f7fcfe;
            }

            .iwp-updater-button {
                min-height: 30px;
            }

            .iwp-updater-button--deactivate {
                min-height: unset;
                padding: 0;
                border: none;
                background: none;
                cursor: pointer;
            }

            .iwp-updater-button--deactivate:hover {
                color: #dc3232;
            }

            .iwp-updater-license-row.iwp-valid .iwp-valid-hide {
                display: none;
            }

            .iwp-updater-license-row.iwp-invalid .iwp-invalid-hide {
                display: none;
            }
        </style>
<?php
    }

    public function ajax_validate_license()
    {

        if (!wp_verify_nonce($_POST['nonce'], 'iwp_validate_license')) {
            echo json_encode([
                'status' => 'E',
                'data' => 'Invalid Nonce'
            ]);
            exit;
        }

        $result = ['license' => 'invalid', 'msg' => 'Invalid license key, please try again.'];

        $license = sanitize_text_field($_POST['license']);
        if ($this->is_valid_license($license, true)) {
            $result = ['license' => 'valid', 'msg' => 'License key activated.'];
        }

        update_site_option('iwp_api_token', $license);
        delete_site_option('_site_transient_update_plugins');

        echo json_encode([
            'status' => 'S',
            'data' => $result
        ]);
        exit;
    }

    public function is_valid_license($license, $skip_transient = false)
    {

        if ($skip_transient === true || false === ($status = get_transient('iwp_license_status'))) {
            $request_uri = sprintf('https://www.importwp.com/api/v1/index.php?access_token=%s&action=status&plugin=importwp-pro', $license);
            $response = json_decode(wp_remote_retrieve_body(wp_remote_get($request_uri)), true);
            if (is_array($response) && isset($response['zipball_url'])) {
                $status = 'yes';
            } else {
                $status = 'no';
            }

            // Logger::write(__METHOD__ . " -url=" . $request_uri . ' -status=' . $status . " -response=" . print_r($response, true));

            set_transient('iwp_license_status', $status);
        }

        return $status == 'yes';
    }
}
