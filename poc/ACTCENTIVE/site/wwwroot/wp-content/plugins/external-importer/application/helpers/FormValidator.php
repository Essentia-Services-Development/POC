<?php

namespace ExternalImporter\application\helpers;

defined('\ABSPATH') || exit;
/**
 * FormValidator class file
 * 
 * Modified version of CodeIgniter CI_Form_validation
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link http://www.keywordrush.com/
 * @copyright Copyright &copy; 2014 keywordrush.com
 * 
 * 
 */

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP 5.2.4 or newer
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the Open Software License version 3.0
 *
 * This source file is subject to the Open Software License (OSL 3.0) that is
 * bundled with this package in the files license.txt / license.rst.  It is
 * also available through the world wide web at this URL:
 * http://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to obtain it
 * through the world wide web, please send an email to
 * licensing@ellislab.com so we can send you a copy immediately.
 *
 * @package		CodeIgniter
 * @author		EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2013, EllisLab, Inc. (http://ellislab.com/)
 * @license		http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @link		http://codeigniter.com
 * @since		Version 1.0
 * @filesource
 */
class FormValidator {

    /**
     * Required
     *
     * @param	string
     * @return	bool
     */
    public static function required($str)
    {
        return is_array($str) ? (bool) count($str) : (trim($str) !== '');
    }

    // --------------------------------------------------------------------

    /**
     * Performs a Regular Expression match test.
     *
     * @param	string
     * @param	string	regex
     * @return	bool
     */
    public static function regex_match($str, $regex)
    {
        return (bool) preg_match($regex, $str);
    }

    // --------------------------------------------------------------------

    /**
     * Match one field to another
     *
     * @param	string	$str	string to compare against
     * @param	string	$field
     * @return	bool
     */
    public static function matches($str, $field)
    {
        return isset($this->_field_data[$field], $this->_field_data[$field]['postdata']) ? ($str === $this->_field_data[$field]['postdata']) : FALSE;
    }

    // --------------------------------------------------------------------

    /**
     * Differs from another field
     *
     * @param	string
     * @param	string	field
     * @return	bool
     */
    public static function differs($str, $field)
    {
        return !(isset($this->_field_data[$field]) && $this->_field_data[$field]['postdata'] === $str);
    }

    // --------------------------------------------------------------------

    /**
     * Minimum Length
     *
     * @param	string
     * @param	string
     * @return	bool
     */
    public static function min_length($str, $val)
    {
        if (!is_numeric($val))
        {
            return FALSE;
        } else
        {
            $val = (int) $val;
        }

        return (MB_ENABLED === TRUE) ? ($val <= mb_strlen($str)) : ($val <= strlen($str));
    }

    // --------------------------------------------------------------------

    /**
     * Max Length
     *
     * @param	string
     * @param	string
     * @return	bool
     */
    public static function max_length($str, $val)
    {
        if (!is_numeric($val))
        {
            return FALSE;
        } else
        {
            $val = (int) $val;
        }

        return (MB_ENABLED === TRUE) ? ($val >= mb_strlen($str)) : ($val >= strlen($str));
    }

    // --------------------------------------------------------------------

    /**
     * Exact Length
     *
     * @param	string
     * @param	string
     * @return	bool
     */
    public static function exact_length($str, $val)
    {
        if (!is_numeric($val))
        {
            return FALSE;
        } else
        {
            $val = (int) $val;
        }

        return (MB_ENABLED === TRUE) ? (mb_strlen($str) === $val) : (strlen($str) === $val);
    }

    // --------------------------------------------------------------------

    /**
     * Valid URL
     *
     * @param	string	$str
     * @return	bool
     */
    public static function valid_url($str)
    {
        if (empty($str))
            return FALSE;

        $pattern = '/^(http|https):\/\/(([A-Z0-9][A-Z0-9_-]*)(\.[A-Z0-9][A-Z0-9_-]*)+)/i';

        if (is_string($str) && strlen($str) < 2000)
        {
            if (preg_match($pattern, $str))
                return true;
        }
        return false;

        /*
          if (empty($str))
          {
          return FALSE;
          } elseif (preg_match('/^(?:([^:]*)\:)?\/\/(.+)$/', $str, $matches))
          {
          if (empty($matches[2]))
          {
          return FALSE;
          } elseif (!in_array($matches[1], array('http', 'https'), TRUE))
          {
          return FALSE;
          }

          $str = $matches[2];
          }

          $str = 'http://' . $str;

          // There's a bug affecting PHP 5.2.13, 5.3.2 that considers the
          // underscore to be a valid hostname character instead of a dash.
          // Reference: https://bugs.php.net/bug.php?id=51192
          if (version_compare(PHP_VERSION, '5.2.13', '==') === 0 OR version_compare(PHP_VERSION, '5.3.2', '==') === 0)
          {
          sscanf($str, 'http://%[^/]', $host);
          $str = substr_replace($str, strtr($host, array('_' => '-', '-' => '_')), 7, strlen($host));
          }

          return (filter_var($str, FILTER_VALIDATE_URL) !== FALSE);
         * 
         */
    }

    // --------------------------------------------------------------------

    /**
     * Valid Email
     *
     * @param	string
     * @return	bool
     */
    public static function valid_email($str)
    {
        return (bool) filter_var($str, FILTER_VALIDATE_EMAIL);
    }

    // --------------------------------------------------------------------

    /**
     * Valid Emails
     *
     * @param	string
     * @return	bool
     */
    public static function valid_emails($str)
    {
        if (strpos($str, ',') === FALSE)
        {
            return $this->valid_email(trim($str));
        }

        foreach (explode(',', $str) as $email)
        {
            if (trim($email) !== '' && $this->valid_email(trim($email)) === FALSE)
            {
                return FALSE;
            }
        }

        return TRUE;
    }

    // --------------------------------------------------------------------

    /**
     * Validate IP Address
     *
     * @param	string
     * @param	string	'ipv4' or 'ipv6' to validate a specific IP format
     * @return	bool
     */
    public static function valid_ip($ip, $which = '')
    {
        return $this->CI->input->valid_ip($ip, $which);
    }

    // --------------------------------------------------------------------

    /**
     * Alpha
     *
     * @param	string
     * @return	bool
     */
    public static function alpha($str)
    {
        return ctype_alpha($str);
    }

    // --------------------------------------------------------------------

    /**
     * Alpha-numeric
     *
     * @param	string
     * @return	bool
     */
    public static function alpha_numeric($str)
    {
        return ctype_alnum((string) $str);
    }

    // --------------------------------------------------------------------

    /**
     * Alpha-numeric w/ spaces
     *
     * @param	string
     * @return	bool
     */
    public static function alpha_numeric_spaces($str)
    {
        return (bool) preg_match('/^[A-Z0-9 ]+$/i', $str);
    }

    // --------------------------------------------------------------------

    /**
     * Alpha-numeric with underscores and dashes
     *
     * @param	string
     * @return	bool
     */
    public static function alpha_dash($str)
    {
        return (bool) preg_match('/^[a-z0-9_-]+$/i', $str);
    }

    // --------------------------------------------------------------------

    /**
     * Numeric
     *
     * @param	string
     * @return	bool
     */
    public static function numeric($str)
    {
        return (bool) preg_match('/^[\-+]?[0-9]*\.?[0-9]+$/', $str);
    }

    // --------------------------------------------------------------------

    /**
     * Integer
     *
     * @param	string
     * @return	bool
     */
    public static function integer($str)
    {
        return (bool) preg_match('/^[\-+]?[0-9]+$/', $str);
    }

    // --------------------------------------------------------------------

    /**
     * Decimal number
     *
     * @param	string
     * @return	bool
     */
    public static function decimal($str)
    {
        return (bool) preg_match('/^[\-+]?[0-9]+\.[0-9]+$/', $str);
    }

    // --------------------------------------------------------------------

    /**
     * Greater than
     *
     * @param	string
     * @param	int
     * @return	bool
     */
    public static function greater_than($str, $min)
    {
        return is_numeric($str) ? ($str > $min) : FALSE;
    }

    // --------------------------------------------------------------------

    /**
     * Equal to or Greater than
     *
     * @param	string
     * @param	int
     * @return	bool
     */
    public static function greater_than_equal_to($str, $min)
    {
        return is_numeric($str) ? ($str >= $min) : FALSE;
    }

    // --------------------------------------------------------------------

    /**
     * Less than
     *
     * @param	string
     * @param	int
     * @return	bool
     */
    public static function less_than($str, $max)
    {
        return is_numeric($str) ? ($str < $max) : FALSE;
    }

    // --------------------------------------------------------------------

    /**
     * Equal to or Less than
     *
     * @param	string
     * @param	int
     * @return	bool
     */
    public static function less_than_equal_to($str, $max)
    {
        return is_numeric($str) ? ($str <= $max) : FALSE;
    }

    // --------------------------------------------------------------------

    /**
     * Is a Natural number  (0,1,2,3, etc.)
     *
     * @param	string
     * @return	bool
     */
    public static function is_natural($str)
    {
        return ctype_digit((string) $str);
    }

    // --------------------------------------------------------------------

    /**
     * Is a Natural number, but not a zero  (1,2,3, etc.)
     *
     * @param	string
     * @return	bool
     */
    public static function is_natural_no_zero($str)
    {
        return ($str != 0 && ctype_digit((string) $str));
    }

    // --------------------------------------------------------------------

    /**
     * Valid Base64
     *
     * Tests a string for characters outside of the Base64 alphabet
     * as defined by RFC 2045 http://www.faqs.org/rfcs/rfc2045
     *
     * @param	string
     * @return	bool
     */
    public static function valid_base64($str)
    {
        return (base64_encode(base64_decode($str)) === $str);
    }

    // --------------------------------------------------------------------

    /**
     * Prep data for form
     *
     * This function allows HTML to be safely shown in a form.
     * Special characters are converted.
     *
     * @param	string
     * @return	string
     */
    public static function prep_for_form($data = '')
    {
        if ($this->_safe_form_data === FALSE OR empty($data))
        {
            return $data;
        }

        if (is_array($data))
        {
            foreach ($data as $key => $val)
            {
                $data[$key] = $this->prep_for_form($val);
            }

            return $data;
        }

        return str_replace(array("'", '"', '<', '>'), array('&#39;', '&quot;', '&lt;', '&gt;'), stripslashes($data));
    }

    // --------------------------------------------------------------------

    /**
     * Prep URL
     *
     * @param	string
     * @return	string
     */
    public static function prep_url($str = '')
    {
        if ($str === 'http://' OR $str === '')
        {
            return '';
        }

        if (strpos($str, 'http://') !== 0 && strpos($str, 'https://') !== 0)
        {
            return 'http://' . $str;
        }

        return $str;
    }

    // --------------------------------------------------------------------

    /**
     * Strip Image Tags
     *
     * @param	string
     * @return	string
     */
    public static function strip_image_tags($str)
    {
        return $this->CI->security->strip_image_tags($str);
    }

    // --------------------------------------------------------------------

    /**
     * XSS Clean
     *
     * @param	string
     * @return	string
     */
    public static function xss_clean($str)
    {
        return $this->CI->security->xss_clean($str);
    }

    // --------------------------------------------------------------------

    /**
     * Convert PHP tags to entities
     *
     * @param	string
     * @return	string
     */
    public static function encode_php_tags($str)
    {
        return str_replace(array('<?', '?>'), array('&lt;?', '?&gt;'), $str);
    }

}

if (!function_exists('affegg_intval_bool'))
{

    function affegg_intval_bool($str)
    {
        return intval((bool) $str);
    }

}