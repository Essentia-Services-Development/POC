<?php

namespace ContentEgg\application\components;

defined('\ABSPATH') || exit;

/**
 * ExtraData class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 */
class ExtraData
{

    public $date;
    public $author;
    public $source;
    public $domain;
    public $pricePerUnitDisplay;

    public static function fillAttributes($object_or_array, array $data)
    {
        foreach ($data as $key => $d)
        {
            if (is_object($object_or_array) && property_exists($object_or_array, $key))
            {
                if (is_array($d) && !is_array($object_or_array->$key))
                {
                    continue;
                } //?
                $object_or_array->$key = $d;
            } elseif (is_array($object_or_array))
            {
                $object_or_array[$key] = $d;
            }
        }

        return $object_or_array;
    }

}
