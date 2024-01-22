<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * LManager class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2021 keywordrush.com
 */
class LManager {

    const CACHE_TTL = 86400;

    private $data = null;
    private static $instance = null;

    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new self;

        return self::$instance;
    }

    public function adminInit()
    {
        \add_action('admin_notices', array($this, 'displayNotice'));
        $this->hideNotice();
    }

    public function getData($force = false)
    {
        if (!LicConfig::getInstance()->option('license_key'))
            return array();

        if (!$force && $this->data !== null)
            return $this->data;

        $this->data = $this->getCache();
        if ($this->data === false || $force)
        {
            $data = $this->remoteRetrieve();
            if (!$data || !is_array($data))
                $data = array();

            $this->data = $data;
            $this->saveCache($this->data);
        }

        return $this->data;
    }

    public function remoteRetrieve()
    {
        if (!$response = Plugin::apiRequest(array('method' => 'POST', 'timeout' => 10, 'body' => $this->getRequestArray('license'))))
            return false;

        if (!$result = json_decode(\wp_remote_retrieve_body($response), true))
            return false;

        return $result;
    }

    public function saveCache($data)
    {
        \set_transient(Plugin::getShortSlug() . '_' . 'ldata', $data, self::CACHE_TTL);
    }

    public function getCache()
    {
        return \get_transient(Plugin::getShortSlug() . '_' . 'ldata');
    }

    public function deleteCache()
    {
        \delete_transient(Plugin::getShortSlug() . '_' . 'ldata');
    }

    private function getRequestArray($cmd)
    {
        return array('cmd' => $cmd, 'd' => parse_url(\site_url(), PHP_URL_HOST), 'p' => Plugin::product_id, 'v' => Plugin::version(), 'key' => LicConfig::getInstance()->option('license_key'));
    }

    public function isConfigPage()
    {
        if ($GLOBALS['pagenow'] == 'admin.php' && isset($_GET['page']) && $_GET['page'] == 'affiliate-egg-lic')
            return true;
        else
            return false;
    }

    public function displayNotice()
    {
        if (LManager::isNulled() && time() > 1633093261)
        {
            $notice_date = \get_option(Plugin::slug . '_nulled_notice_date', 0);
            if ($notice_date && time() > $notice_date + 86400 * 3)
            {
                LManager::deactivateLic();
                return;
            }

            $this->displayNulledNotice();
            return;
        }


        if (!$data = LManager::getInstance()->getData())
            return;

        if ($data['activated_on'] && $data['activated_on'] != preg_replace('/^www\./', '', strtolower(parse_url(\site_url(), PHP_URL_HOST))))
        {
            $this->displayLicenseMismatchNotice();
            return;
        }

        if (time() >= $data['expiry_date'])
        {
            $this->displayExpiredNotice($data);
            return;
        }

        $days_left = floor(($data['expiry_date'] - time()) / 3600 / 24);
        if ($days_left >= 0 && $days_left <= 21)
        {
            $this->displayExpiresSoonNotice($data);
            return;
        }

        if ($this->isConfigPage())
        {
            $this->displayActiveNotice($data);
            return;
        }
    }

    public function displayActiveNotice(array $data)
    {
        $this->addInlineCss();
        $purchase_uri = '/product/purchase/1015';
        $days_left = floor(($data['expiry_date'] - time()) / 3600 / 24);

        echo '<div class="notice notice-success affegg-notice"><p>';
        echo sprintf(__('License status: <span class="affegg-label affegg-label-%s">%s</span>.', 'affegg'), strtolower($data['status']), strtoupper($data['status']));
        if ($data['status'] == 'active')
            echo ' ' . __('You are receiving automatic updates.', 'affegg');
        echo '<br />' . sprintf(__('Expires at %s (%d days left).', 'affegg'), gmdate('F d, Y H:i', $data['expiry_date']) . ' GMT', $days_left);
        echo '</p>';
        echo '<p>';
        $this->displayCheckAgainButton();

        echo ' ' . sprintf('<a class="button-primary" target="_blank" href="%s">%s</a>', Plugin::website . '/login?return=' . urlencode($purchase_uri), "&#10003; " . __('Extend now', 'affegg'));
        if ((int) $data['extend_discount'])
            echo ' <small>' . sprintf(__('with a %d%% discount', 'affegg'), $data['extend_discount']) . '</small>';

        echo '</p></div>';
    }

    public function displayExpiresSoonNotice(array $data)
    {
        if (\get_transient('affegg_hide_notice_lic_expires_soon') && !$this->isConfigPage())
            return;

        $this->addInlineCss();
        $purchase_uri = '/product/purchase/1015';
        $days_left = floor(($data['expiry_date'] - time()) / 3600 / 24);
        echo '<div class="notice notice-warning affegg-notice">';
        echo '<p>';
        if (!$this->isConfigPage())
        {
            $hide_notice_uri = \add_query_arg(array('affegg_hide_notice' => 'lic_expires_soon', '_affegg_notice_nonce' => \wp_create_nonce('hide_notice')), $_SERVER['REQUEST_URI']);
            echo '<a href="' . $hide_notice_uri . '" class="affegg-notice-close notice-dismiss">' . __('Dismiss', 'affegg') . '</a>';
        }
        echo '<strong>' . __('License expires soon', 'affegg') . '</strong><br />';
        echo sprintf(__('Your %s license expires at %s (%d days left).', 'affegg'), Plugin::getName(), gmdate('F d, Y H:i', $data['expiry_date']) . ' GMT', $days_left);
        echo ' ' . __('You will not receive automatic updates, bug fixes, and technical support.', 'affegg');
        echo '</p>';
        echo '<p>';
        $this->displayCheckAgainButton();
        echo ' ' . sprintf('<a class="button-primary" target="_blank" href="%s">%s</a>', Plugin::website . '/login?return=' . urlencode($purchase_uri), "&#10003; " . __('Extend now', 'affegg'));
        if ((int) $data['extend_discount'])
            echo ' <span class="affegg-label affegg-label-success">' . sprintf(__('with a %d%% discount', 'affegg'), $data['extend_discount']) . '</span>';
        echo '</p>';
        echo '</div>';
    }

    public function displayExpiredNotice(array $data)
    {
        if (\get_transient('affegg_hide_notice_lic_expired') && !$this->isConfigPage())
            return;

        $this->addInlineCss();
        $purchase_uri = '/product/purchase/1015';
        echo '<div class="notice notice-error affegg-notice">';
        echo '<p>';

        if (!$this->isConfigPage())
        {
            $hide_notice_uri = \add_query_arg(array('affegg_hide_notice' => 'lic_expired', '_affegg_notice_nonce' => \wp_create_nonce('hide_notice')), $_SERVER['REQUEST_URI']);
            echo '<a href="' . $hide_notice_uri . '" class="affegg-notice-close notice-dismiss">' . __('Dismiss', 'affegg') . '</a>';
        }

        echo '<strong>' . __('License expired', 'affegg') . '</strong><br />';
        echo sprintf(__('Your %s license expired on %s.', 'affegg'), Plugin::getName(), gmdate('F d, Y H:i', $data['expiry_date']) . ' GMT');
        echo ' ' . __('You are not receiving automatic updates, bug fixes, and technical support.', 'affegg');
        echo '</p>';
        echo '<p>';
        $this->displayCheckAgainButton();
        echo ' ' . sprintf('<a class="button-primary" target="_blank" href="%s">%s</a>', Plugin::website . '/login?return=' . urlencode($purchase_uri), "&#10003; " . __('Renew now', 'affegg'));
        echo '</p></div>';
    }

    public function displayLicenseMismatchNotice()
    {
        $this->addInlineCss();
        echo '<div class="notice notice-error affegg-notice"><p>';
        echo '<strong>' . __('License mismatch', 'affegg') . '</strong><br />';
        echo sprintf(__("Your %s license doesn't match your current domain.", 'affegg'), Plugin::getName());
        echo ' ' . sprintf(__('If you wish to continue using the plugin then you must <a target="_blank" href="%s">revoke</a> the license and then <a href="%s">reactivate</a> it again or <a target="_blank" href="%s">buy a new license</a>.', 'affegg'), Plugin::panelUri, \get_admin_url(\get_current_blog_id(), 'admin.php?page=affiliate-egg-lic'), 'https://www.keywordrush.com/affiliateegg/pricing');
        echo '</p></div>';
    }

    public function displayCheckAgainButton()
    {
        echo '<form style="display: inline;" action=" ' . \get_admin_url(\get_current_blog_id(), 'admin.php?page=affiliate-egg-lic') . '" method="POST">';
        echo '<input type="hidden" name="affegg_cmd" id="affegg_cmd" value="refresh" />';
        echo '<input type="hidden" name="nonce_refresh" value="' . \wp_create_nonce('license_refresh') . '"/>';
        echo '<input type="submit" name="submit3" id="submit3" class="button" value="&#8635; ' . __('Check again', 'affegg') . '" />';
        echo '</form>';
    }

    public function hideNotice()
    {
        if (!isset($_GET['affegg_hide_notice']))
            return;

        if (!isset($_GET['_affegg_notice_nonce']) || !\wp_verify_nonce($_GET['_affegg_notice_nonce'], 'hide_notice'))
            return;

        $notice = $_GET['affegg_hide_notice'];

        if (!in_array($notice, array('lic_expires_soon', 'lic_expired')))
            return;

        if ($notice == 'lic_expires_soon')
            $expiration = 7 * 24 * 3600;
        elseif ($notice == 'lic_expired')
            $expiration = 90 * 24 * 3600;
        else
            $expiration = 0;

        \set_transient('affegg_hide_notice_' . $notice, true, $expiration);

        \wp_redirect(\remove_query_arg(array('affegg_hide_notice', '_affegg_notice_nonce'), \wp_unslash($_SERVER['REQUEST_URI'])));
        exit;
    }

    public function addInlineCss()
    {
        echo '<style>.affegg-notice a.affegg-notice-close {position:static;float:right;top:0;right0;padding:0;margin-top:-20px;line-height:1.23076923;text-decoration:none;}.affegg-notice a.affegg-notice-close::before{position: relative;top: 18px;left: -20px;}.affegg-notice img {float:left;width:40px;padding-right:12px;}.affegg-label {display: inline;padding: .3em .6em .3em;line-height: 1;color: #fff;text-align: center;vertical-align: baseline;border-radius: .25em;font-size: 85%;}.affegg-label-success, .affegg-label-active {background-color: #00ba37;} .affegg-label-error, .affegg-label-inactive {background-color: #d63638;}</style>';
    }

    public function displayNulledNotice()
    {
        $activation_date = \get_option(Plugin::slug . '_first_activation_date', false);
        if ($activation_date && $activation_date < time() + 86400 * 3)
            return;

        $notice_date = \get_option(Plugin::slug . '_nulled_notice_date');
        if (!$notice_date)
        {
            $notice_date = time();
            \update_option(Plugin::slug . '_nulled_notice_date', $notice_date);
        }
        $valid_date = $notice_date + 86400 * 2;

        $this->addInlineCss();
        echo '<div class="notice notice-error affegg-notice" style="padding: 10px;">';
        echo '<img src=" ' . \Keywordrush\AffiliateEgg\PLUGIN_RES . '/img/logo.svg' . '" width="40" />';
        echo '<strong>Cracked Version: The Real Danger!</strong><br />';
        echo sprintf('<p>You are using a cracked version of %s plugin. This is an illegal and dangerous copy of the plugin.', Plugin::getName());
        echo '<br/>Cracked plugins often have backdoors and other malware injected into code that is used to get full third-party access to your site, distribute SEO spam, viruses and redirect site visitors. Your site will be probably blacklisted by Google.</p>';
        echo '<p>Please note: You can purchase Affiliate Egg Pro only on our <a target="_blank" href="https://www.keywordrush.com/?utm_source=affegg&utm_medium=referral&utm_campaign=legal">official site</a>. If you purchased your pirated copy on any other site, we recommend requesting a refund and reinstalling the plugin (your existing settings and plugin data are safe!).</p>';
        echo '<p>The official version includes <u>direct support, automatic updates</u> and a guarantee of proper work.</p>';

        if ($valid_date > time())
        {
            echo sprintf('Use code <b>LEGAL25</b> for a 25%% discount (valid until %s).', TemplateHelper::dateFormatFromGmt($valid_date, false));
            echo '<br><br><a target="_blank" class="button button-primary button-large" href="https://www.keywordrush.com/affiliateegg/pricing?ref=LEGAL25&utm_source=affegg&utm_medium=referral&utm_campaign=legal">Apply Coupon</a>';
        } else
            echo '<a target="_blank" class="button button-primary button-large" href="https://www.keywordrush.com/affiliateegg/pricing?utm_source=affegg&utm_medium=referral&utm_campaign=legal">Buy Now</a>';

        echo '</div>';
    }

    public static function isNulled()
    {
        $l = LicConfig::getInstance()->option('license_key');

        if (!$l && Plugin::isEnvato())
            return false;

        if (!LManager::isValidLicFormat($l))
            return true;

        if (in_array(md5($l), LManager::getNulledLics()))
            return true;

        return false;
    }

    public static function isValidLicFormat($value)
    {
        if (preg_match('/[^0-9a-zA-Z_~\-]/', $value))
            return false;
        if (strlen($value) != 32 && strlen($value) != 36)
            return false;
        return true;
    }

    public static function getNulledLics()
    {
        return array(
            '782827cbd9dab148548f41184850a17d', 'bc57aa5923d803c465184623de14114e', '1342e3cd2142a46010550cb8d1c07a4a', 'dd025e37d236de2346a09a51331c1232', '280f18cd8dbadd98f39f271b70d37df2', 'c52fadb5a51c73aeb510932d75a6ebc4', 'a5c2305222b9bea26a5611101c93db86', '6bf28f33081e6eb9ef83de7d374c3280', 'bb2abb70723a23442624cfb7263f0418', 'fded028cb4a7f4e5300f4b909fb9ff22', 'b8799671e1b70a8c82ade63187789a54', 'f875624c61165879063a51e9239f112b', 'd92d03899e674d265c54221de87f47ab', '4a21fce420d531857b5c07368bf07072', 'a282d805e6e7b5f8f96ddcec966809fc', 'f3f8db4cb0837916c1c3ee961950bfdb', '0b62675cbf52fa58cf304aae7c64704d', 'f6d39075bfceb776ebb6bf79c4b46114', '14b4065233f9bd21d4cf5d1daa6390ff', '4d89b9ea00db777228ac587149b7b403', '401e8ae5470d90581ff58ccc1d46c35d', '60a9115ad107ba9778e761ecc57a2521', 'b26170918a5c497de65e1f9f947ad05d', '86d55dbe32b9bced65b25e6d3c3746e4', '357c950561096089e2275dab50483d61', 'e95cab97ed66287bffec97513943c453', '560e12b4143100d5324cf0ca1fd73c80', '18d18341724e616e6038b30cc1afb103', 'dbabdfb522f2cf1e229ecd268cebf935', '9973938d5a865f7ddaf3f5fd13f61036', '2fc7e4480a15ed988c18b6051a34c5b4', 'ab7a7f8d13a683cfad86cbfe3d1bdc12', '6ce822b6177738de4992989da6b8e4fa', '4530cd97c2f0e44a4fa57bee3f41b1db', '38c6cbd28bf165070d070980dd1fb595', '559fb575d4fa65482e76245ccfe39ca9', '216a567115e7c454377262b0d1978a28', '3050138fffd03b322fee0c6ceb9dc204', 'dd469b3e16bd8f151751d61445438ecb', '50896af36231cad47384235bd074a24f', '5905b2c532c061e1d47e7eb12634d0d7', '6d05d7139c2f3d3871c9b0cdf3982603', '98ad392d8839aa551478330ce4639003', '675bf6ae2966e89be8302f29a5d1c2e1', '1f92b6f95d6c0e0c7605dbf023fd838b', 'b9e23eb31cb8b140d6cb88a82a67674a', 'cdeea93bbf1e3d8423fedf2974798e41', '6fbdcfc6d3ab9bd5985274b3d386615e', 'abf6cb41afd9116a062dcadec2b4bb09', 'cd9e459ea708a948d5c2f5a6ca8838cf', '5f588b581e5f9429f041b009c6bf3a50', '6e6dbff2ae19344cca4f39aa68bf9085', '7ed72229b2d0cd6a96a29b2fabaafd1d', 'dc8fe1d6497ebd23f5975d8d2a1c5e81', 'eb974e8d25c7d8e63d36d0610d759a29', 'b7209797b1a25e5dc8357202f0c4e957', 'a9a6dae49f04b063438574f9272a4560', '2f983f471cc14bb29cb384b01bff9398', '36dcee17c23dce6f6975f0710192acfe', 'ca7c9cbad07b024f0433426f9a84f29f', '4c267c041197c92e39917b47cfa99ae6', '00a119c9b3e16b35153f677035c2a08f', 'dc0bf117f2d2ca6e883b69bb7527964f', '981540125d6dbc442da361de72eb70fc', '2241ace14c5fec7dceb041ea3c9a540d', '492474da5c68f1e50ea61921c97b12d3', 'cee5602e7430d1d90e210be9dabaa6df', '09cf4df9279976e8d872a55fb0534c6c', '477f79182b686d15531f9e5ff85d2ca8', 'c455891a37fb8d2d499a64ddcbb50fef', '19b18b505d21b47da99f0a777f5d8f81', 'f0c9e830f0b49aa55026fb5d5a26046c', '99ebca64333a57ffdc528829b163e3b5', '732dd0b39b6483319f092301fc9a160b', '5d485509cc03b35b2522d37f1b7c66f6', '36eededa354c2986586e6ea28cdbc866', 'b473c2a1fb60142527780b829ba0ba76', 'ee21f612172ea8022ce0e01d9c22c779', 'ba6b005bef79e7b95f3e08181e2501ce', '1a80cbf2859e019559365d6be478794e', '2a39ffe9cd6004ccb943e90f4a59f66c', '1130d4c1fcca8b55fc9adab0a907a058', '530c1825212f7e7f8f8552987d2e40dc', '41fc00071902db7a19d3bf03d44341e7', '36ef21be023337478eb58310824397d0', '296833ad0648c97931a805490c044b12', 'ec282bdfba1641d5bb3e4df2f55a2fb4', 'dd39906086048521afca6e2367a988e4', '9db20881666a1b54eac6412dcacc7109', '0205fdd3ac57e10c8505cdefa83be333', '8e2ae942cc25d2d27a32c9ef17dcfff1', 'e9dc621decb9d1f171abdfa5b3bb3bf1', '20e96ab48df4222cc3237b96cedc816c', '36bcdb7f251684a6a8448ec5b718eb7e', '7525bfb57a695adb85dd56e0e49393f3', 'c027e96dc12536569be4f11aefb16bb2', '593545b717345d61df3cd2667a731d1f', '280abd4bffbc18d03e0d8ab3214c3162', '9c6c98bf7c76d1168922e67ebc4df059', 'd31a770a67aff6be5b4679cc9f8e09b3', '63ee89bd518501b66097688a476f3e4b', '3982d043b8a1696a2fe914f9775bc788', 'eece2f3bdbbfdaa6df5d42e9116cfe8f', '54651715b14416c28cdd6352232a24fe', 'bee54612879a23e7dea34b4b60f6d312', '4bc17bed5d5512411581a8d6a373ec43', '30cddc736e7c8e7a4f338fb73f04366f', '025f8065bc3a359a6b103b9f90cb5666', 'ed06b064849e861eb3380f12c0136273', 'a06b3890ddfc3f3faae15da1b2b3b614', '48860cbb4e510ef0358f19304c54d881', '6b8701b0f8ed19ee96d54728ea85c272', '770c2830dc9e057b1c97febac6256c3e', 'b289ef1665bd5ab5811a8f8d583dfa98', 'ad8f6c5337c70afb96479682271452e8', '379c06e979da810d03e2e25be4ca6634', '1e0a43725379325ce601a55ce82d103e', 'b4353b4b3d13206c0a2e884bd0728f2f', 'e74bf2cde0104a108a4e41c9d518789f', '5a9355643f606c1180def0813b321dce', 'd1d0d881ecd189422cecd45bc362ccc2', '4712ac02dddba46e4b9a1d6eb66ae607', '0b2190a723362140d92251e02cbd4bec', 'b9906d282d8378ddeb0a654c2d4d970d', '23dd5e6dd6d6f05bf31fbad5f30e3f96', 'f2bef54bf8e246dc5d291a53ff123b75', 'ee098dd1a21b44445bf1119bd1d8c03b', '33bbc11f6ac9a866f836c1c8f96d360d', 'd68205f1aa61f2739d023ab3a75d92b1', '9f056fe2c264c8f0422a15567f427382', '6a0e5f04fcdb3b32b8e634d87f35421e', '5373f906297bd2ddd133fce8cbaad001', 'c66a898cacc6367b3661f01d6d161b31', '6bef0f0509ee79814fe97721c8c7a25d', '12e4c23edf79cc6dc96336646e8ea56a', '990044ce86ea14f0b8b8b380605c6e12', 'bac6fa8846a73ba00dcd1b863dd0b90a', 'aea51bb57851a0feb14f60db136e3ce4', '8e8f166e189e34b2f13d4e231e79431e', '2f53976b7a4a5e7268236a12bb636c8c', 'df7acac887419b8db4131e37908f2b26', '78d167d9bbd33709ff0a6d908c50ca8a', 'abf338e526251db6f76392b44c48b86c', '2ccffae25b74950a13540ea699b3d576', '3686ec6a66635ab42187c96b82dc9bd5', '60a458f51e9a08bbf26d8686f18d2525', 'a7725646dffe1333c8ccf5c365c1cbd4', '96b797d1f6655662c7bedcafe2ddc067', '74f7ad3ab536baea28476b3aebbd2543', 'e5753f9219977b38ab53670150da1761', '360a4b3fc519e3a00cf40189dd57446b', 'af0a04e13dfcc28c19e00dc36c071da3', '21cf9e87cfbb9eb5c9defab52f8a8b3c', 'f263a6b157b739f6f09360aff3b84430', 'e09ffb9ae909c418d2cf3ca1e9fa5429', '854277ac94772d5b72928bfdf67ca2a1', '88b37b1b10deae53e80c240ced7db6b7', '5c82786a1f6836c7e664ba8bf5fd1956', 'a7208c404b28ba7422b3330fd1aa6fe9', '3fa0de116e68f7f39df1af6ce98ee992', 'd72f61d8cecc903c21fb72e2e8ba1f0d', '9b650cf225e36317be1d9013af26a9da', 'f1c5fc1a3d88cbcdf6969089e116a036', 'c14b7494d02c559a58b79ce769b69b3a', '8515842f5ae08cb6c67e33d1f6e836ef', 'a8f9c1ba8b195a0538bd70d3308e4107', '94ca33c37f77c8dd37f07a4e29cef697', '15f72ed03221006fda1fbf112981be03', 'cc42781bace364f75d226ad93f5ca21c', '9cb21d64cdf3e058ddbd36fb8a47ce70', 'd06154cf886aad8a3a9ff455708edb37', '34efb52c14365b7c5b8d949648038c3e', 'a51cc9d0c45fe7f4a22ab2ce031e854d', 'da9a0bbff6fd454dc89c10c6559ec0a4', 'c67a504ca0956d4252e37f99b5fe1679', 'a4aefea75579e932fa4d1e7dd63ef36a', 'd8d77b70d9f158f1d13bbba7a6f14ed9', '02339673fdefcb42eb6ecaa7ab0a1627', 'd8b4d3e2933abf25a6f6f49811459fd0', 'fcf70cfe3cda0628735482dfefd68675', 'f6dfe15226af620a6b8e267f7529a96a', '3cf26115631e7b914b135dfc83e0fee7', '2e32c7597c2241c44a41aac18fc3255e', '2754ab8050927244dc1860392d1c60d9', 'd22f8e87719847065ea1e51292302f28', '8bba9fd24dce073c45ae125459bc0e1d', 'fa549e08cc7a2637def7e4028f93ee31', '2e326498c24b8b3498eb0cce49919316', 'd97199e56b47e119a5e4adf1ed659be6', '0af06f2257fad1d2543ff7e41fc4f0ea', '5de5631ebe9edbb2fb4e3bc5c9d98b07', '381a9d58dd1c0501238402bf0185a17a', '5d4bf8a4bfc8cc58e89486136a2817cc', '2be0759e48fa188ec4b92a122fe902a8', '9785c723f67fb875b2c78676991fb627', '0851b3ca9fff7fd114ac1c7a55082d10', 'b2413480b73b4df26846ff5bf1a06921', '4886b77df2c54d33b45bcf1e919b2c84', '15b1104e706129580e4bdbe796502b77', '8f4617ef0eb4b8959b3be49805218c5e', '1b1a73e41deca236f5b89db68d4c960c', '0709f89db955610e1dc772e1fc4c16ce', '8c843a67c9795b6c7f64207fe669ff6c', '0e83356409cedc44f40a86252955582a', '8069059b97224f4109f72b0e161d9545', '3b5d75688342a5008893a27ed9278945', '5f02d33151b3af389d9936f3e414e9f3', '70a2b469903dec06525f3d26d897fb40', '6ce23fc6794aa40d8f6e96b0d6d4b80b', '6dbe7af851d3a50aafcc3ed1b8f123bf', 'ca3d3974e81cd81e9c2f2136b0da50d6', 'c098a9954983fabda58801b0b6c8f787', '3f8b5b64edd6eedb968eb776337a35eb', '84d813e8d7726239ffe5a19da7e49aa4', '154de7e9b54d9c27f212ed69abd05fd7', 'f1cab0a3fbe94fdce1cec5f5a48e2f68', '3d193f1bd1b8cd3453ac11da7fe412e1', '427954306adbc7aa6fb1a4188d75679d', '935c6b67a69c1b988bfb1e73f3ddc7bf', 'bd369b6687a60889c6d7155a6bb04020', '17553a17924fe9743cd0622f4e1b6df3', '52be558b25611da48ff7feb13bcef263', 'c0fd43f7b7e60f2c160c280310d54116', '65ede94f3537ac08effed442f9667b7a', '9ff9eba04febf154374798e67d086c14', '0a62352c2613da82305efb4cc78decb1', '1e016f841fe8c4848f833f6f0850cff9', '84dce634829e8dd79eb203977dd5a2f8', 'df40440b0b1d8fe5f49cba3dc543f2a4', '849d5310f031c08afefa1ddc25681d0f', '9ff64461a7a798c36b2358831e2c7cc4', '5eca9bd3eb07c006cd43ae48dfde7fd3', '7751be51d8932f4da27b5bd8cc620165', '2d35e30587002c3b6ac7fb8109d769f7', 'a39bfc38b32ae9e969e50bf2b2bd45b1', '2a2d33d7d1e45cedc30f9b6264f5021e', 'bf80b62f9a843fb4d9255535a428e6ce', '62caaa881ec26a8c3fb0c6e75022045a', '36a31db6bda7d2aeac517f2e943add8d', 'a60db1dae2b9f9e2c581d13b04f03259', '981b072528fda08104a916a96d16bf82', 'f045b4bbf20c99c15eca4823a70be4bd', '529ffcac479f37cba4ed9da530e240b2', 'c69ec2d2f7cba15c810f130c3224bc2a', 'ea95322b3432c2d884cb787ee9257807', '27d86cd5e61a2eb6a15f4fe9866985b2', '799e580c784e8ba243d4e3bfa25eb9b1', '55222e3b9827b0c8135132da97a887d6', '5b2c85fe424d87c48a0517237575e76d', 'aea42a0a8a870e7f1a035d67c62419c1', '041111d7d810fde28b878b21a5889d78', '720c316fd2b7f5aed7f9b10cf12e9810', '0d82c8380cc14a728808a1094accfc61', 'b4458c1afe34fbdac1b72c51bfe220c6', 'ba3f8edd3cd514c9f39b92d0d09bdcc8', 'b06d4f76e55955e691702dae05b2a7b4', '2dac6d4a8c9d576701660e8dd45eb764', '8fcef9fe9edf15355d2f596ba7379998', 'd3b81e8eb408a58b9036be62035e059e', '2e4d90f0ba6fda26e4ba8e0f1cef3e71', '9ad1007f84afaf97e10fd9ffb6a9565d', 'ee7872ee1a94438466d2aae183d37262', '320f3811351b717271a7a5722ba6aaaa', 'ca0800626a8331cf74a48255eb960a2e', '93d787a64dab5c299c9685e7e4f40a60', '88710142b9f61a052c0aa74bd5611409', 'a1060861f43c285b93789bfa40743748', '2166e4938975ba9b30ae0bd90a536fa2', '40ecdc075a7ffaa8514d236964462924', '8a807881705c223bf576ed01d2e067ff', '468320301b64c7669e4657cf6353f7bc', '300d6cb41e748a9c927a6822f2c979e0', 'b0dafccca4919b60f2aef18235b05f44', '948c273327e2d4d33dc5d1e422e32560', '028736d55d58d71dc5f2bf45eec959c1', '515ca3fc2ec6e99694579eb8eaffa6a0', 'c02c515af73cfbaed7ec1ee44a2d9df9', '3cbb6fd74d432d941271f2f37937c09b',
        );
    }

    public static function deactivateLic()
    {
        \update_option(Plugin::slug . '_nulled_key', LicConfig::getInstance()->option('license_key'));
        \update_option(Plugin::slug . '_nulled_deactiv_date', time());
        \delete_option(LicConfig::getInstance()->option_name());
    }

}
