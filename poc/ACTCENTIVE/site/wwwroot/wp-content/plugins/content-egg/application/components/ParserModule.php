<?php

namespace ContentEgg\application\components;

defined('\ABSPATH') || exit;

use ContentEgg\application\helpers\ImageHelper;
use ContentEgg\application\helpers\TextHelper;
use ContentEgg\application\LocalRedirect;
use ContentEgg\application\admin\GeneralConfig;
use ContentEgg\application\Plugin;

/**
 * ParserModule abstract class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
abstract class ParserModule extends Module
{

    const PARSER_TYPE_CONTENT = 'CONTENT';
    const PARSER_TYPE_PRODUCT = 'PRODUCT';
    const PARSER_TYPE_COUPON = 'COUPON';
    const PARSER_TYPE_IMAGE = 'IMAGE';
    const PARSER_TYPE_VIDEO = 'VIDEO';
    const PARSER_TYPE_OTHER = 'OTHER';

    abstract public function doRequest($keyword, $query_params = array(), $is_autoupdate = false);

    abstract public function getParserType();

    public function isActive()
    {
        if ($this->is_active === null)
        {
            if ($this->getConfigInstance()->option('is_active'))
            {
                $this->is_active = true;
            } else
            {
                $this->is_active = false;
            }
        }

        return $this->is_active;
    }

    final public function isParser()
    {
        return true;
    }

    public function isUrlSearchAllowed()
    {
        return false;
    }

    public function presavePrepare($data, $post_id)
    {
        global $post;
        $data = parent::presavePrepare($data, $post_id);

        // do not save images for revisions & search results
        if (( $post && wp_is_post_revision($post_id) ) || $post_id < 0)
        {
            return $data;
        }

        $old_data = ContentManager::getData($post_id, $this->getId());

        foreach ($data as $key => $item)
        {
            // fill domain
            if (empty($item['domain']))
            {
                if (!empty($item['orig_url']))
                {
                    $url = $item['orig_url'];
                } elseif (!empty($item['img']))
                {
                    $url = $item['img'];
                } else
                {
                    $url = $item['url'];
                }

                if ($url)
                {
                    $domain = TextHelper::getHostName($url);
                    if (!in_array($domain, array('buscape.com.br', 'avlws.com')))
                    {
                        $data[$key]['domain'] = $item['domain'] = $domain;
                    }
                }
            }
            // save img
            if ($this->config('save_img') && !wp_is_post_revision($post_id))
            {
                // check old_data also. need for fix behavior with "preview changes" button and by keyword update
                if (isset($old_data[$key]) && !empty($old_data[$key]['img_file']) && file_exists(ImageHelper::getFullImgPath($old_data[$key]['img_file'])))
                {
                    // image exists
                    $item['img'] = $old_data[$key]['img'];
                    $item['img_file'] = $old_data[$key]['img_file'];
                } elseif ($item['img'] && empty($item['img_file']))
                {
                    $local_img_name = ImageHelper::saveImgLocaly($item['img'], $item['title']);
                    if ($local_img_name)
                    {
                        $uploads = \wp_upload_dir();
                        $item['img'] = $uploads['url'] . '/' . $local_img_name;
                        $item['img_file'] = ltrim(trailingslashit($uploads['subdir']), '\/') . $local_img_name;
                    }
                }
                $data[$key] = $item;
            }
        }

        return $data;
    }

    public static function getFullImgPath($img_path)
    {
        $uploads = \wp_upload_dir();

        return trailingslashit($uploads['basedir']) . $img_path;
    }

    public function defaultTemplateName()
    {
        return 'data_simple';
    }

    public function viewDataPrepare($data)
    {
        // cashback integration
        if (GeneralConfig::getInstance()->option('cashback_integration') == 'enabled' && class_exists('\CashbackTracker\application\Plugin'))
        {
            foreach ($data as $key => $d)
            {
                $data[$key]['url'] = \CashbackTracker\application\components\DeeplinkGenerator::maybeAddTracking($d['url']);
            }
        }

        // local redirect
        if ($this->config('set_local_redirect'))
        {
            foreach ($data as $key => $d)
            {
                if (isset($d['url']))
                {
                    $data[$key]['aff_url'] = $d['url'];
                } // url without redirect

                $data[$key]['url'] = LocalRedirect::createRedirectUrl($d);
            }
        }

        return $data;
    }

    public function getAccessToken($force = false)
    {
        $transient_name = Plugin::slug() . '-' . $this->getId() . '-access_token';
        $token = \get_transient($transient_name);

        if (!$token || $force)
        {
            try
            {
                list( $token, $expires_in ) = $this->requestAccessToken();
            } catch (\Exception $e)
            {
                return false;
            }
            \set_transient($transient_name, $token, (int) $expires_in);
        }

        return $token;
    }

    public function isFeedParser()
    {
        if ($this->getIdStatic() == ModuleManager::FEED_MODULES_PREFIX)
        {
            return true;
        } else
        {
            return false;
        }
    }

    public function doMultipleRequests($keyword, $query_params = array(), $is_autoupdate = false)
    {
        $groups = array();

        if (!\apply_filters('cegg_disable_group_matching', false))
        {
            $parts = explode('->', $keyword);
            if (count($parts) == 2)
            {
                $groups = explode(',', $parts[1]);
                $groups = array_map('trim', $groups);
                $groups = array_map('sanitize_text_field', $groups);

                $keyword = trim($parts[0]);
            }
        }

        if (!\apply_filters('cegg_disable_multiple_keywords', false))
        {
            $keywords = explode(',', $keyword, 10);
        } else
        {
            $keywords = array($keyword);
        }

        $keywords = array_map('trim', $keywords);

        $results = array();
        foreach ($keywords as $i => $keyword)
        {
            if ($i && $this->getId() == 'Amazon')
            {
                sleep(1);
            }

            try
            {
                $data = $this->doRequest($keyword, $query_params, $is_autoupdate);
            } catch (\Exception $e)
            {
                if (count($keywords) == 1)
                    throw new \Exception($e->getMessage(), $e->getCode());
                else
                    continue;
            }

            if (!empty($groups[$i]))
            {
                foreach ($data as $key => $d)
                {
                    $data[$key]->group = $groups[$i];
                }
            }

            $results = array_merge($results, $data);
        }

        $results = self::filterDuplicateItems($results);

        return $results;
    }

    private static function filterDuplicateItems(array $items)
    {
        $results = array();
        foreach ($items as $item)
        {
            $dup = false;
            foreach ($results as $result)
            {
                if ($item->unique_id == $result->unique_id)
                {
                    $dup = true;
                    break;
                }
            }

            if (!$dup)
            {
                $results[] = $item;
            }
        }

        return $results;
    }

}
