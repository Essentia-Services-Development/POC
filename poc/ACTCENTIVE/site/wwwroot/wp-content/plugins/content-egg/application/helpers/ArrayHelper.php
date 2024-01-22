<?php

namespace ContentEgg\application\helpers;

defined('\ABSPATH') || exit;

/**
 * ArrayHelper class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2015 keywordrush.com
 *
 */
class ArrayHelper
{

	/**
	 * @link: http://php.net/manual/ru/function.array-diff-assoc.php#111675
	 */
	public static function array_diff_assoc_recursive($array1, $array2)
	{
		$difference = array();
		foreach ($array1 as $key => $value)
		{
			if (is_array($value))
			{
				if (!isset($array2[$key]) || !is_array($array2[$key]))
				{
					$difference[$key] = $value;
				}
				else
				{
					$new_diff = array_diff_assoc_recursive($value, $array2[$key]);
					if (!empty($new_diff))
					{
						$difference[$key] = $new_diff;
					}
				}
			}
			else if (!array_key_exists($key, $array2) || $array2[$key] !== $value)
			{
				$difference[$key] = $value;
			}
		}

		return $difference;
	}

	/**
	 *  Full depth recursive conversion to array
	 *
	 * @param type $object
	 *
	 * @return array
	 */
	public static function object2Array($object)
	{
		return json_decode(json_encode($object), true);
	}

	public static function array2Object(array $a)
	{
		return json_decode(json_encode($a), false);
	}

	public static function asortStable(array $array, $order1 = SORT_ASC, $order2 = SORT_ASC)
	{
		if (!$array)
		{
			return $array;
		}

		foreach ($array as $key => $value)
		{
			$keys[] = $key;
			$data[] = $value;
		}
		array_multisort($data, $order1, $keys, $order2, $array);

		return $array;
	}

	/**
	 * Hightest value of an associative array
	 */
	public static function getMaxKeyAssoc($array, $key_name, $zero_ignore = false)
	{
		$first     = reset($array);
		$max_key   = key($array);
		$max_value = $first[$key_name];
		foreach ($array as $k => $v)
		{
			if ($zero_ignore && !(float) $v[$key_name])
			{
				continue;
			}
			if ((float) $v[$key_name] > (float) $max_value)
			{
				$max_value = $v[$key_name];
				$max_key   = $k;
			}
		}

		return $max_key;
	}

	public static function getMinKeyAssoc($array, $key_name, $zero_ignore = false)
	{
		$first     = reset($array);
		$min_key   = key($array);
		$min_value = $first[$key_name];
		foreach ($array as $k => $v)
		{
			if ($zero_ignore && !(float) $v[$key_name])
			{
				continue;
			}
			if ((float) $v[$key_name] < (float) $min_value)
			{
				$min_value = $v[$key_name];
				$min_key   = $k;
			}
		}

		return $min_key;
	}
}
