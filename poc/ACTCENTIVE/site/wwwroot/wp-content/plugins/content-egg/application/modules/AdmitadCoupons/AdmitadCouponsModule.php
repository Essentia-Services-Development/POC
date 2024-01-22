<?php

namespace ContentEgg\application\modules\AdmitadCoupons;

defined('\ABSPATH') || exit;

use ContentEgg\application\components\AffiliateParserModule;
use ContentEgg\application\libs\admitad\AdmitadCoupons;
use ContentEgg\application\components\ContentCoupon;
use ContentEgg\application\admin\PluginAdmin;

/**
 * AdmitadCouponsModule class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com
 */
class AdmitadCouponsModule extends AffiliateParserModule
{

    private $api_client = null;

    public function info()
    {
        return array(
            'name' => 'Admitad Coupons',
            'description' => __('Adds coupons from <a href="http://www.keywordrush.com/go/admitad">Admitad</a>.', 'content-egg') . ' ' . __('You must get approve for each program separately.', 'content-egg'),
        );
    }

    public function defaultTemplateName()
    {
        return 'coupons';
    }

    public function getParserType()
    {
        return self::PARSER_TYPE_COUPON;
    }

    public function isFree()
    {
        return false;
    }

    public function doRequest($keyword, $query_params = array(), $is_autoupdate = false)
    {
        $results = $this->getApiClient()->search($keyword, $this->config('url'));
        if (!is_array($results) || !isset($results['coupons']['coupon']))
        {
            return array();
        }

        if (!isset($results['coupons']['coupon'][0]) && isset($results['coupons']['coupon']['name']))
        {
            $results['coupons']['coupon'] = array($results['coupons']['coupon']);
        }

        if ($is_autoupdate)
        {
            $limit = $this->config('entries_per_page_update');
        } else
        {
            $limit = $this->config('entries_per_page');
        }
        $coupons = array_slice($results['coupons']['coupon'], 0, $limit);

        return $this->prepareResults($coupons);
    }

    private function prepareResults($results)
    {
        $data = array();
        foreach ($results as $key => $r)
        {
            $content = new ContentCoupon;
            $content->unique_id = $r['@attributes']['id'];
            $content->title = $r['name'];
            $content->url = $r['gotolink'];
            $content->img = $r['logo'];
            if (isset($r['description']) && !strstr($r['description'], 'Акция распространяется на определенную группу товаров'))
            {
                $content->description = $r['description'];
            }

            $content->startDate = strtotime($r['date_start']);
            $content->endDate = strtotime($r['date_end']);
            if ($r['promocode'] != 'Не нужен')
            {
                $content->code = $r['promocode'];
            }

            $content->extra = new ExtraDataAdmitadCoupons;
            ExtraDataAdmitadCoupons::fillAttributes($content->extra, $r);

            $data[] = $content;
        }

        return $data;
    }

    public function viewDataPrepare($data)
    {
        foreach ($data as $key => $d)
        {
            if ($d['domain'] == 'admitad.com')
            {
                $data[$key]['domain'] = '';
            }
        }

        return parent::viewDataPrepare($data);
    }

    private function getApiClient()
    {
        if ($this->api_client === null)
        {
            $this->api_client = new AdmitadCoupons();
        }

        return $this->api_client;
    }

    public function renderResults()
    {
        PluginAdmin::render('_metabox_results', array('module_id' => $this->getId()));
    }

    public function renderSearchResults()
    {
        PluginAdmin::render('_metabox_search_results', array('module_id' => $this->getId()));
    }

}
