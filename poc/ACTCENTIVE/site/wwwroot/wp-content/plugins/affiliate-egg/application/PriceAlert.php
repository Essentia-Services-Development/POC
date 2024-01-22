<?php

namespace Keywordrush\AffiliateEgg;
defined('\ABSPATH') || exit;

/**
 * PriceAlert class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class PriceAlert {

    private static $instance = null;
    private $tickbox_message;
    private $tickbox_subject;

    public static function getInstance()
    {
        if (self::$instance == null)
            self::$instance = new self;

        return self::$instance;
    }

    private function __construct()
    {
        
    }

    public function init()
    {
        if (!self::isPriceAlertAllowed())
            return;
        if (\is_admin())
        {
            // anonymous visitors
            \add_action('wp_ajax_nopriv_affegg_start_tracking', array($this, 'ajaxTrackProduct'));
            // logged in users
            \add_action('wp_ajax_affegg_start_tracking', array($this, 'ajaxTrackProduct'));
        }
        \add_action('init', array($this, 'registerJs'));
        \add_action('template_redirect', array($this, 'subscriptionManager'));
    }

    public function registerJs()
    {
        \wp_enqueue_script('affegg-price-alert', PLUGIN_RES . '/js/price_alert.js', array('jquery'), AffiliateEgg::version());
        \wp_localize_script('affegg-price-alert', 'affeggPriceAlert', array(
            'ajaxurl' => \admin_url('admin-ajax.php'),
            'nonce' => \wp_create_nonce('affegg-price-alert')
        ));
    }

    public function ajaxTrackProduct()
    {
        if (!isset($_POST['nonce']) || !\wp_verify_nonce($_POST['nonce'], 'affegg-price-alert'))
            die('Invalid nonce');

        $product_id = (int) TextHelper::clear(InputHelper::post('product_id', null));
        $price = (float) TextHelper::parsePriceAmount(InputHelper::post('price', null));
        $post_id = (int) InputHelper::post('post_id', null);
        $email = strtolower(TextHelper::clearId(InputHelper::post('email', null)));
        if (!$product_id || !$post_id)
            die('Invalid params');

        if (!$current = PriceHistoryModel::model()->getLastPrices($product_id, $limit = 1))
            die('Product not found.');
        $current = $current[0];

        // find product        
        if (!$product = ProductModel::model()->findByPk($product_id))
            die('Product not found.');

        // post exists?
        if (!\get_post_status($post_id))
            die('Post not found.');

        if (!$price || !$email)
            $this->jsonError(__('All fields are required.', 'affegg-tpl'));

        if (!\is_email($email))
            $this->jsonError(__('Your email address is invalid.', 'affegg-tpl'));

        if ($price >= $current['price'])
            $this->jsonError(__('The price has already been reached.', 'affegg-tpl'));

        // dublicate?
        $where = array(
            'product_id = %d AND email = %s AND status != %d',
            array($product_id, $email, PriceAlertModel::STATUS_DELETED)
        );
        if (PriceAlertModel::model()->find(array('where' => $where)))
            $this->jsonError(__('You already tracking this product.', 'affegg-tpl'));

        // Prepare product
        $product = Shortcode::prepareItem($product);

        $alert = array(
            'product_id' => $product_id,
            'post_id' => $post_id,
            'email' => $email,
            'price' => $price,
            'start_price' => $current['price'],
            'status' => PriceAlertModel::STATUS_INACTIVE,
            'activkey' => TextHelper::randomPassword(16),
        );

        // save
        if (PriceAlertModel::model()->save($alert))
        {
            // email
            $this->sendActivationEmail($email, $product, $alert);
            $this->jsonResult(__('We are now tracking this product for you. Please verify your email address to be notified of price drops.', 'affegg-tpl'), 'success');
        } else
            $this->jsonError(__('Internal Error. Please notify the administrator.', 'affegg-tpl'));
        exit;
    }

    private function sendActivationEmail($email, $product, $alert)
    {
        $subject = sprintf(__('Welcome to %s', 'affegg-tpl'), \esc_html(\get_bloginfo('name')));
        $product_title = \esc_html(TextHelper::truncate($product['title']));

        $uri = \add_query_arg(array(
            'affeggaction' => 'validate',
            'email' => urlencode($email),
            'key' => urlencode($alert['activkey']),
                ), \get_permalink($alert['post_id']));
        $uri .= '#' . urlencode($alert['product_id']);

        $body = '<p>' . __('Hello,', 'affegg-tpl') . '<br></p>';
        $body .= '<p>' . sprintf(__('You have successfully set a price drop alert for %s.', 'affegg-tpl'), $product_title) . '<p>';
        $body .= '<p>' . __('We will not send you any price alerts until you verified your email address.', 'affegg-tpl');
        $body .= ' ' . sprintf(__('Please open this link to validate your email address:<br> <a href="%s">%s</a>', 'affegg-tpl'), \esc_url($uri), \esc_url($uri)) . '</p>';
        $body .= $this->getEmailSignature();

        self::mail($email, $subject, $body);
    }

    private function getEmailSignature()
    {
        return sprintf(__("<br><pre class=\"moz-signature\" cols=\"72\">--\r\nThank You,\r\n Team %s</pre>", 'affegg-tpl'), \get_bloginfo('name'));
    }

    private function jsonResult($message, $status = 'success')
    {
        header("Content-Type: application/json");
        echo json_encode(array(
            'status' => $status,
            'message' => $message
        ));
        exit;
    }

    private function jsonError($message)
    {
        $this->jsonResult($message, 'error');
        exit;
    }

    public function subscriptionManager()
    {
        if (!$action = InputHelper::get('affeggaction', null))
            return;

        switch ($action)
        {
            case 'validate':
                $this->actionValidateEmail();
                return;
            case 'unsubscribe':
                $this->actionUnsubscribeAll();
                return;
            default:
                return;
        }
    }

    private function actionValidateEmail()
    {
        $email = strtolower(TextHelper::clearId(InputHelper::get('email', null)));
        $key = TextHelper::clear(InputHelper::get('key', null));

        $where = array(
            'email = %s AND activkey = %s AND status = %d',
            array($email, $key, PriceAlertModel::STATUS_INACTIVE)
        );
        $alert = PriceAlertModel::model()->find(array('where' => $where));
        if (!$alert)
            return;
        $alert['status'] = PriceAlertModel::STATUS_ACTIVE;
        // save
        PriceAlertModel::model()->save($alert);
        // tickbox
        $this->openTickbox(__('Your email has been verified. We will let you know by email when the Price Drops.', 'affegg-tpl'), __('Success!', 'affegg-tpl'));
    }

    private function actionUnsubscribeAll()
    {
        $email = strtolower(TextHelper::clearId(InputHelper::get('email', null)));
        $key = TextHelper::clear(InputHelper::get('key', null));

        $where = array(
            'email = %s AND activkey = %s',
            array($email, $key)
        );
        $alert = PriceAlertModel::model()->find(array('where' => $where));
        if (!$alert)
            return;

        PriceAlertModel::model()->unsubscribeAll($alert['email']);
        $this->openTickbox(__('You are now unsubscribed from our Price Alerts via email.', 'affegg-tpl'), __('Unsubscribed!', 'affegg-tpl'));
    }

    public function openTickbox($message, $subject = "")
    {
        $this->tickbox_message = strip_tags($message);
        $this->tickbox_subject = strip_tags($subject);
        \add_thickbox();
        \add_action('wp_footer', array($this, 'tickboxInlineScript'));
    }

    public function tickboxInlineScript()
    {
        echo '<script>
            jQuery(window).load(function()
            {
                jQuery("body").append("<div id=\"affegg-price-alert-tickbox\"><p>' . \esc_js($this->tickbox_message) . '<div style=\"text-align:center; padding-top: 30px;padding-right: 20px;\"><input value=\"' . esc_js(__('  Ok  ', 'affegg-tpl')) . '\" type=\"button\" onclick=\"javascript:tb_remove()\"></div></p></div>");
                tb_show("' . esc_js($this->tickbox_subject) . '", "#TB_inline?height=200&amp;width=400&amp;inlineId=affegg-price-alert-tickbox", false);
            });</script>';
    }

    public static function mail($to, $subject, $message, $headers = '', $attachments = array())
    {
        \add_filter('wp_mail_content_type', array(__CLASS__, 'setMailContentType'));
        \wp_mail($to, $subject, $message, $headers, $attachments);
        \remove_filter('wp_mail_content_type', 'setMailContentType');
    }

    public static function setMailContentType()
    {
        return 'text/html';
    }

    public function sendAlert(array $item)
    {
        if (empty($item['id']) || empty($item['price']))
            return false;

        // Price drops?
        $previous_price = PriceHistoryModel::model()->getPreviousPriceValue($item['id']);
        if (!$previous_price || (float) $previous_price <= (float) $item['price']) //!!!!!
            return false;

        // Subscribers exist?
        $params = array(
            'where' => array('product_id=%d AND status=%d AND price >= %f', array($item['id'], PriceAlertModel::STATUS_ACTIVE, $item['price'])),
        );
        $subscribers = PriceAlertModel::model()->findAll($params);
        if (!$subscribers)
            return false;

        $this->sendAlertEmails($subscribers, $item);

        // clean up & optimize
        if (rand(1, 15) == 15)
        {
            PriceAlertModel::model()->cleanOld(PriceAlertModel::CLEAN_DELETED_DAYS);
        }
    }

    private function sendAlertEmails($alerts, $product)
    {
        $product = Shortcode::prepareItem($product);

        foreach ($alerts as $alert)
        {
            $post_id = $alert['post_id'];

            $product_title = \esc_html(TextHelper::truncate($product['title']));
            $subject = sprintf(__('Price alert: "%s"', 'affegg-tpl'), $product_title);
            $post_url = \get_permalink($post_id) . '#' . urlencode($product['id']);

            $unsubscribe_url = \add_query_arg(array(
                'affeggaction' => 'unsubscribe',
                'email' => urlencode($alert['email']),
                'key' => urlencode($alert['activkey']),
                    ), \get_site_url());

            $desired_price = TemplateHelper::formatPriceCurrency($alert['price'], $product['currency_code']);
            $current_price = TemplateHelper::formatPriceCurrency($product['price_raw'], $product['currency_code']);
            $start_price = TemplateHelper::formatPriceCurrency($alert['start_price'], $product['currency_code']);
            $saved_amount = round($alert['start_price'] - $product['price_raw'], 2);
            $saved_amount = TemplateHelper::formatPriceCurrency($saved_amount, $product['currency_code']);
            $saved_percentage = round(100 - (100 * $product['price_raw']) / $alert['start_price'], 2);

            $body = '<p>' . __('Good news!', 'affegg-tpl') . '<br></p>';
            $body .= '<p>' . __('The price target you set for the item has been reached.', 'affegg-tpl');
            $body .= '<p>' . sprintf(__('<a href="%s">Save %s (%s%%) on %s</a>', 'affegg-tpl'), $post_url, $saved_amount, $saved_percentage, $product_title);
            $body .= '<ul>';
            $body .= '<li>' . sprintf(__('Desired Price: %s', 'affegg-tpl'), $desired_price) . '</li>';
            $body .= '<li>' . sprintf(__('Current Price: <strong>%s</strong>', 'affegg-tpl'), $current_price)
                    . ' (' . __('as of', 'affegg-tpl') . ' ' . TemplateHelper::getLastUpdateFormatted($product['id'], true) . ')</li>';
            $body .= '<li>' . sprintf(__('Price dropped from %s to %s', 'affegg-tpl'), $start_price, $current_price) . '</li>';
            $body .= '</ul><br>';
            $body .= sprintf(__('<a href="%s">More info...</a>', 'affegg-tpl'), $post_url);
            $body .= '</p><br>';

            $body .= '<p>' . sprintf(__('This present alert has now expired. You may <a href="%s">create a new alert</a> for this item.', 'affegg-tpl'), $post_url);
            $body .= '<br>' . sprintf(__('If you don\'t want to receive any price alerts from us in the future, <a href="%s">please click here</a>.', 'affegg-tpl'), $unsubscribe_url) . '</p>';
            $body .= $this->getEmailSignature();

            // send alert email
            self::mail($alert['email'], $subject, $body);

            // delete alert
            $alert['status'] = PriceAlertModel::STATUS_DELETED;
            $alert['complet_date'] = \current_time('mysql');
            PriceAlertModel::model()->save($alert);
        }
    }

    public static function isPriceAlertAllowed($product_id = null)
    {
        if (!GeneralConfig::getInstance()->option('price_history_days'))
            return false;

        if (!GeneralConfig::getInstance()->option('price_alert_enabled'))
            return false;
        if ($product_id)
        {
            if (!PriceHistoryModel::model()->getLastPrices($product_id, 1))
                return false;
        }
        return true;
    }

}
