<?php

namespace Keywordrush\AffiliateEgg;

defined('\ABSPATH') || exit;

/**
 * IndustrybuyingParser class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2016 keywordrush.com 
 */
class IndustrybuyingParser extends LdShopParser {

    protected $currency = 'INR';

    public function parseCatalog($max)
    {
        return array_slice($this->xpathArray(".//*[@id='AH_ProductListView']//a[@class='prFeatureName']/@href"), 0, $max);
    }

    public function parseLdJson()
    {
        if ($p = parent::parseLdJson())
            return $p;

        $lds = $this->xpathArray(".//script[@type='application/ld+json']", true);
        foreach ($lds as $ld)
        {
            $ld = TextHelper::fixHiddenCharacters($ld);
            $ld = preg_replace('/\/\*.+?\*\//', '', $ld);
            $ld = trim($ld, ";");

            //fix
            $ld = str_replace(']}},}', ']}}', $ld);

            if (!$data = json_decode($ld, true))
                continue;
            if (isset($data['mainEntity']))
                $data = $data['mainEntity'];

            if (isset($data['@graph']))
            {
                foreach ($data['@graph'] as $d)
                {
                    if (isset($d['@type']) && (in_array($d['@type'], $this->product_types) || in_array(ucfirst(strtolower($d['@type'])), $this->product_types)))
                        $data = $d;
                }
            } elseif (isset($data[0]) && isset($data[1]))
            {
                foreach ($data as $d)
                {
                    if (isset($d['@type']) && (in_array($d['@type'], $this->product_types) || in_array(ucfirst(strtolower($d['@type'])), $this->product_types)))
                        $data = $d;
                }
            } elseif (isset($data[0]))
                $data = $data[0];

            if (isset($data['@type']) && (in_array($data['@type'], $this->product_types) || in_array(ucfirst(strtolower($data['@type'])), $this->product_types)))
            {
                $this->ld_json = $data;
                return $this->ld_json;
            }
        }
        return false;
    }

    public function parseOldPrice()
    {
        return $this->xpathScalar(".//*[@id='AH_ListPrice']");
    }

    public function parseExtra()
    {
        $extra = parent::parseExtra();

        $extra['features'] = array();
        $keys = $this->xpathArray(".//*[@id='productSpecifications']//*[@class='proHeading']");
        $values = $this->xpathArray(".//*[@id='productSpecifications']//*[@class='proSpec']");
        $feature = array();
        for ($i = 0; $i < count($keys); $i++)
        {
            if (!isset($values[$i]))
                continue;
            $feature['name'] = \sanitize_text_field($keys[$i]);
            $feature['value'] = \sanitize_text_field($values[$i]);
            $extra['features'][] = $feature;
        }

        $extra['images'] = array();
        $results = $this->xpathArray(".//ul[@class='thumbsArea']//img/@data-zoom-image");
        foreach ($results as $i => $res)
        {
            if ($i == 0)
                continue;
            if ($res && !preg_match('/^https?:/', $res))
                $res = 'https:' . $res;
            $extra['images'][] = $res;
        }

        return $extra;
    }

}
