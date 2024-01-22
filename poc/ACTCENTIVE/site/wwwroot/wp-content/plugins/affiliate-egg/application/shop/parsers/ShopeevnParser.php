<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * ShopeevnParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2022 keywordrush.com
 */
class ShopeevnParser extends ShopParser
{

    protected $charset = 'utf-8';
    protected $currency = 'VND';
    private $_product;
    protected $canonical_domain = 'https://shopee.vn';
    protected $user_agent = array('ia_archiver');

    public function parseCatalog($max)
    {
        $url = $this->canonical_domain . '/api/v4/search/search_items?by=relevancy&limit=50&newest=0&order=desc&page_type=search&version=2';

        $keyword = TextHelper::getQueryVar('keyword', $this->getUrl());

        if (preg_match('/-cat\.([\d\.]+)/', $this->getUrl(), $matches))
        {
            $parts = explode('.', $matches[1]);
            $category_id = $parts[count($parts) - 1];
        }
        elseif (preg_match('/-col\.(\d+)/', $this->getUrl(), $matches))
        {
            $category_id = $matches[1];
            $url = \add_query_arg('page_type', 'collection', $url);
        }
        else
            $category_id = '';

        if (!$keyword && !$category_id)
            return array();

        if ($keyword)
            $url = \add_query_arg('keyword', $keyword, $url);

        if ($category_id)
            $url = \add_query_arg('match_id', $category_id, $url);


        if ($page = TextHelper::getQueryVar('page', $this->getUrl()))
        {
            if ($page > 1)
                $url = \add_query_arg('newest', $page * 50, $url);
        }

        $headers = array(
            'User-Agent' => reset($this->user_agent),
            'af-ac-enc-dat' => 'AAcyLjUuMC0yAAABhTlvB6gAAAmDAdAAAAAAAAAAAuvlR3weVVU60ykHUkkzSmQs+0sol/82EyfDx/bVRcPaaRvYm9kzPhhnhoTdQF3h1sDYorbbZ1AfnJvm60oc1lC4fzjhutEQ8+9c/VRAmAsifdsEcIKm7fG4G9WtpqgV5HCZSY3ZR+rNccwANqoPl3SRNg1L8Zcr5LKUhm2Ka5rFj918XR3OjSwege29malw3mJwfJdV7YDDDo3MVbSCSityRRQCQgFqSO+Kjkx02Jlm8cFNXnXTm/A4C0emoxQGadM3SxX2qfsTgstKn+hXHmAjXNSURq23yVrkWJRafidLXFJOr/KnbyEBMzqKdbtkwPVUJbPwxLeAPgu/RxmxYNhgd2lHVzc2nwHNyxFE+b9lmAUtSTTVjSlvb1bJBdyhKrnDU3GmiEUewQVOQ8CuUhUOflsFVGJl5oskJUlTRJ8bl1EvJYaHdkfU8vnmbfZWN+gk2oBY+H96VtOXGp9xuJblhnDhRq23yVrkWJRafidLXFJOr8+otmXNbRljPrcNVcxPYpMjrK+5sj5xLZxcyoRO1lJ6STe/CqG4C0fFkFklipN5Ro69ij343tqq7j2VHs0r32dfTvn0vrcjuTApd6gT2wG8C6VdTK36/rhI4NRmrxr6+Q==',
            'Cookie' => 'SPC_T_IV=VzdadnRuWklwSmRZbnhETQ==; SPC_F=x0yNC1qN1gSo3cFVi0mFftk2n7ohUj02; REC_T_ID=ed6dd60a-6f24-11ed-821f-1409dccf1e18; SPC_R_T_ID=cXYwIgDHKxUlnzrjpOl8jqPKmNoY3YlbJZ+ON49fr9ovLWED6HHgL9uZLYVgJclOg0DfIdA8lMEj2yqn0plR0Z4cHMX5L2SiR2FeATs/qzTu2SBQpUVpr7mgSG5BoZ0kgyU9VMIlpqorDdVdA/VIeAdBnt1BeaUAci99lNTswok=; SPC_R_T_IV=VzdadnRuWklwSmRZbnhETQ==; SPC_T_ID=cXYwIgDHKxUlnzrjpOl8jqPKmNoY3YlbJZ+ON49fr9ovLWED6HHgL9uZLYVgJclOg0DfIdA8lMEj2yqn0plR0Z4cHMX5L2SiR2FeATs/qzTu2SBQpUVpr7mgSG5BoZ0kgyU9VMIlpqorDdVdA/VIeAdBnt1BeaU…dVw%3D%3D%7CnSzcKf8h2sy3v3b2%7C06%7C3; ds=7a859dcba0867d5bd7e94ec7d040a9fe; AMP_TOKEN=%24NOT_FOUND; _gid=GA1.2.1925197872.1671705781; _hjSessionUser_868286=eyJpZCI6ImRlYjk3MTAzLTU0ZDctNWU1Mi1iMWNjLWNhMGVjYTIyMWI5MiIsImNyZWF0ZWQiOjE2NzE3MDU3ODA3MjQsImV4aXN0aW5nIjpmYWxzZX0=; _hjFirstSeen=1; _hjIncludedInSessionSample=0; _hjSession_868286=eyJpZCI6IjlkMDQ3OGI4LTQ5ZGItNDhlNC05ODBiLWVkY2I3ZDg4NzI5MCIsImNyZWF0ZWQiOjE2NzE3MDU3ODA3MjcsImluU2FtcGxlIjpmYWxzZX0=; _hjAbsoluteSessionInProgress=1; _dc_gtm_UA-61914164-6=1',
            'sz-token' => '9vNMYZuejkEYw0v8dSz5rw==|zMpGSyr7LAoNWl4X1dJU0JcYOu6pQm5B0yExOowNiNC8SaKfJYEGyA0mvQeWl0PKhfTx6RlQkGom1Dx9sbNUoJJncXduCbAtAA==|bPVWCe9zukbM+jzo|06|3',
            'X-API-SOURCE' => 'pc',
            'X-CSRFToken' => 'e7trPIyHHFeQVkhpp1eEtSJtxOoP4RLT',
            'X-Requested-With' => 'XMLHttpRequest',
            'X-Shopee-Language' => 'en',
        );
        $result = $this->getRemoteJson($url, $headers);
        if (!$result || !isset($result['items']))
            return false;
        $urls = array();

        foreach ($result['items'] as $item)
        {
            $urls[] = $this->canonical_domain . '/' . str_replace(' ', '-', html_entity_decode($item['item_basic']['name'])) . '-i.' . $item['shopid'] . '.' . $item['itemid'];
        }

        return $urls;
    }

    public function parseTitle()
    {
        if (!$this->_getProduct())
            return;

        if (!$this->_product)
            return;

        if (isset($this->_product['name']))
            return $this->_product['name'];
    }

    public function _getProduct()
    {
        $this->_product = array();

        $item_id = 0;
        $shop_id = 0;

        if (preg_match('~\-i\.(\d+\.\d+)~', $this->getUrl(), $matches))
        {
            $ids = $matches[1];
            $ids = explode('.', $ids);
            $item_id = $ids[1];
            $shop_id = $ids[0];
        }

        if (preg_match('~\/product\/(\d+)\/(\d+)~', $this->getUrl(), $matches))
        {
            $item_id = $matches[2];
            $shop_id = $matches[1];
        }

        if (!$item_id || !$shop_id)
            return;

        $headers = array(
            'User-Agent' => reset($this->user_agent),
            'af-ac-enc-dat' => 'AAcyLjUuMC0yAAABhTlvB6gAAAmDAdAAAAAAAAAAAuvlR3weVVU60ykHUkkzSmQs+0sol/82EyfDx/bVRcPaaRvYm9kzPhhnhoTdQF3h1sDYorbbZ1AfnJvm60oc1lC4fzjhutEQ8+9c/VRAmAsifdsEcIKm7fG4G9WtpqgV5HCZSY3ZR+rNccwANqoPl3SRNg1L8Zcr5LKUhm2Ka5rFj918XR3OjSwege29malw3mJwfJdV7YDDDo3MVbSCSityRRQCQgFqSO+Kjkx02Jlm8cFNXnXTm/A4C0emoxQGadM3SxX2qfsTgstKn+hXHmAjXNSURq23yVrkWJRafidLXFJOr/KnbyEBMzqKdbtkwPVUJbPwxLeAPgu/RxmxYNhgd2lHVzc2nwHNyxFE+b9lmAUtSTTVjSlvb1bJBdyhKrnDU3GmiEUewQVOQ8CuUhUOflsFVGJl5oskJUlTRJ8bl1EvJYaHdkfU8vnmbfZWN+gk2oBY+H96VtOXGp9xuJblhnDhRq23yVrkWJRafidLXFJOr8+otmXNbRljPrcNVcxPYpMjrK+5sj5xLZxcyoRO1lJ6STe/CqG4C0fFkFklipN5Ro69ij343tqq7j2VHs0r32dfTvn0vrcjuTApd6gT2wG8C6VdTK36/rhI4NRmrxr6+Q==',
            'Cookie' => 'SPC_T_IV=VzdadnRuWklwSmRZbnhETQ==; SPC_F=x0yNC1qN1gSo3cFVi0mFftk2n7ohUj02; REC_T_ID=ed6dd60a-6f24-11ed-821f-1409dccf1e18; SPC_R_T_ID=cXYwIgDHKxUlnzrjpOl8jqPKmNoY3YlbJZ+ON49fr9ovLWED6HHgL9uZLYVgJclOg0DfIdA8lMEj2yqn0plR0Z4cHMX5L2SiR2FeATs/qzTu2SBQpUVpr7mgSG5BoZ0kgyU9VMIlpqorDdVdA/VIeAdBnt1BeaUAci99lNTswok=; SPC_R_T_IV=VzdadnRuWklwSmRZbnhETQ==; SPC_T_ID=cXYwIgDHKxUlnzrjpOl8jqPKmNoY3YlbJZ+ON49fr9ovLWED6HHgL9uZLYVgJclOg0DfIdA8lMEj2yqn0plR0Z4cHMX5L2SiR2FeATs/qzTu2SBQpUVpr7mgSG5BoZ0kgyU9VMIlpqorDdVdA/VIeAdBnt1BeaU…dVw%3D%3D%7CnSzcKf8h2sy3v3b2%7C06%7C3; ds=7a859dcba0867d5bd7e94ec7d040a9fe; AMP_TOKEN=%24NOT_FOUND; _gid=GA1.2.1925197872.1671705781; _hjSessionUser_868286=eyJpZCI6ImRlYjk3MTAzLTU0ZDctNWU1Mi1iMWNjLWNhMGVjYTIyMWI5MiIsImNyZWF0ZWQiOjE2NzE3MDU3ODA3MjQsImV4aXN0aW5nIjpmYWxzZX0=; _hjFirstSeen=1; _hjIncludedInSessionSample=0; _hjSession_868286=eyJpZCI6IjlkMDQ3OGI4LTQ5ZGItNDhlNC05ODBiLWVkY2I3ZDg4NzI5MCIsImNyZWF0ZWQiOjE2NzE3MDU3ODA3MjcsImluU2FtcGxlIjpmYWxzZX0=; _hjAbsoluteSessionInProgress=1; _dc_gtm_UA-61914164-6=1',
            'sz-token' => '9vNMYZuejkEYw0v8dSz5rw==|zMpGSyr7LAoNWl4X1dJU0JcYOu6pQm5B0yExOowNiNC8SaKfJYEGyA0mvQeWl0PKhfTx6RlQkGom1Dx9sbNUoJJncXduCbAtAA==|bPVWCe9zukbM+jzo|06|3',
            'X-API-SOURCE' => 'pc',
            'X-CSRFToken' => 'e7trPIyHHFeQVkhpp1eEtSJtxOoP4RLT',
            'X-Requested-With' => 'XMLHttpRequest',
            'X-Shopee-Language' => 'en',
        );
        $result = $this->getRemoteJson($this->canonical_domain . '/api/v4/item/get?itemid=' . urlencode($item_id) . '&shopid=' . urlencode($shop_id), $headers);

        if (!$result || !isset($result['data']))
            return false;

        $this->_product = $result['data'];
        return $this->_product;
    }

    public function parseDescription()
    {
        if (isset($this->_product['description']))
            return $this->_product['description'];
    }

    public function parsePrice()
    {
        if (isset($this->_product['price']))
            return $this->_product['price'] / 100000;
        if (isset($this->_product['price_min']))
            return $this->_product['price_min'] / 100000;
    }

    public function parseOldPrice()
    {
        if (isset($this->_product['price_before_discount']))
            return $this->_product['price_before_discount'] / 100000;
        if (isset($this->_product['price_min_before_discount']))
            return $this->_product['price_min_before_discount'] / 100000;
    }

    public function parseManufacturer()
    {
        if (isset($this->_product['brand']))
            return $this->_product['brand'];
    }

    public function parseImg()
    {
        if (isset($this->_product['image']))
            return str_replace('https://', 'https://cf.', $this->canonical_domain) . '/file/' . $this->_product['image'];
    }

    public function parseExtra()
    {
        $extra = array();
        if (isset($this->_product['rating_star']))
            $extra['rating'] = TextHelper::ratingPrepare($this->_product['rating_star']);
        return $extra;
    }

    public function isInStock()
    {
        if (isset($this->_product['status']) && !$this->_product['status'])
            return false;

        if (isset($this->_product['stock']) && !$this->_product['stock'])
            return false;

        return true;
    }
}
