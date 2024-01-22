<?php

namespace ContentEgg\application;

defined('\ABSPATH') || exit;

use ContentEgg\application\components\ContentManager;
use ContentEgg\application\components\ModuleManager;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\admin\GeneralConfig;

/**
 * LocalRedirect class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class LocalRedirect {

    const DEFAULT_REDIRECT_PREFIX = 'go';

    private static $prefix;

    public static function initAction()
    {
        \add_action('template_redirect', array(__CLASS__, 'go'));
    }

    public static function getPrefix()
    {
        if (!self::$prefix)
        {
            $prefix = GeneralConfig::getInstance()->option('redirect_prefix');
            $prefix = TextHelper::clear($prefix);
            if (!$prefix)
                $prefix = self::DEFAULT_REDIRECT_PREFIX;
            self::$prefix = $prefix;
        }

        return self::$prefix;
    }

    public static function go()
    {
        if (\get_option('permalink_structure'))
        {
            global $wp;
            if (preg_match("/" . self::getPrefix() . "\/(.+?)$/", $wp->request, $match))
                $goce = sanitize_text_field(urldecode($match[1]));
            else
                $goce = '';
            
        } elseif (isset($_GET[self::getPrefix()]))
        {
            $goce = sanitize_text_field(wp_unslash($_GET[self::getPrefix()]));
        }
        else
            return;

        if (!$goce)
            return;

        // short url?
        $url = self::parseShortUrl($goce);

        // long url?
        if (!$url)
            $url = self::parseLongUrl($goce);

        if (!$url)
            return;

        $code = (int) \apply_filters('cegg_local_redirect_code', 301);

        \wp_redirect(wp_sanitize_redirect($url), $code); // phpcs:ignore
        exit;
    }

    public static function parseShortUrl($goce)
    {
        $segments = explode('-', $goce, 2);
        if (count($segments) != 2)
            return false;

        $post_id = (int) $segments[0];
        $unique_id = TextHelper::clearId($segments[1]);

        // post exists?
        if (!\get_post_status($post_id))
            return false;

        $module_ids = ModuleManager::getInstance()->getParserModuleIdsByTypes('ALL', true);

        foreach ($module_ids as $module_id)
        {
            $parser = ModuleManager::getInstance()->parserFactory($module_id);
            if (!$parser->config('set_local_redirect'))
                continue;

            $item = ContentManager::getProductbyUniqueId($unique_id, $module_id, $post_id);
            if ($item)
                return $item['aff_url'];
            else
                continue;
        }

        return false;
    }

    public static function parseLongUrl($goce)
    {
        $goce_parts = explode('_', $goce);
        if (count($goce_parts) == 2)
        {
            $url = $goce_parts[0];
            $code = $goce_parts[1];
        } elseif (count($goce_parts) == 3)
        {
            $url = $goce_parts[1];
            $code = $goce_parts[2];
        } else
            return false;

        if ($code != substr(md5($url), 0, 3))
            return false;
        return self::base64_url_decode($url);
    }

    public static function createRedirectUrl(array $item)
    {
        global $post;

        if (!empty($item['post_id']))
            $post_id = $item['post_id'];
        elseif ($post && $post->ID)
            $post_id = $post->ID;
        else
            $post_id = null;

        $prefix = self::getPrefix();
        if (\get_option('permalink_structure'))
            $path = urlencode($prefix) . '/';
        else
            $path = '?' . urlencode($prefix) . '=';

        // post_id = -1 for search page
        if ($post_id && $post_id > 0 && !empty($item['unique_id']))
            $path .= self::createShortRedirectPath($post_id, $item['unique_id']); // url urlencoded
        elseif (!empty($item['url']))
            $path .= self::createLongRedirectPath($item['url']); // url urlencoded
        else
            $path = '/';

        return \get_site_url(\get_current_blog_id(), $path);
    }

    private static function createShortRedirectPath($post_id, $unique_id)
    {
        $unique_id = TextHelper::clearId($unique_id);
        return urlencode($post_id . '-' . $unique_id);
    }

    private static function createLongRedirectPath($url, $title = '')
    {
        $r_url = self::base64_url_encode($url);
        $secure = substr(md5($r_url), 0, 3);
        if ($title)
        {
            $title = str_replace(' ', '-', trim($title));
            $title = preg_replace('/[^a-z0-9A-Z\-]/', '', $title);
            $title = trim($title, '-');
            $title = explode('-', $title, 4);
            $title = array_slice($title, 0, 3);
            $title = join('-', $title);
            $r_url = urlencode($title) . '_' . $r_url;
        }
        $r_url .= '_' . urlencode($secure);
        return $r_url;
    }

    /*
      public static function send404()
      {
      global $wp_query;
      $wp_query->set_404();
      \status_header(404);
      include( \get_query_template('404') );
      exit;
      }
     * 
     */

    public static function base64_url_encode($input)
    {
        return strtr(base64_encode($input), '+/=', '-~,');
    }

    public static function base64_url_decode($input)
    {
        return base64_decode(strtr($input, '-~,', '+/='));
    }

}
