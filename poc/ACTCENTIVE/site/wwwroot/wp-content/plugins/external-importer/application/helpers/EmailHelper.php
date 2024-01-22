<?php

namespace ExternalImporter\application\helpers;

defined('\ABSPATH') || exit;

/**
 * EmailHelper class file
 *
 * @author keywordrush.com <support@keywordrush.com>
 * @link https://www.keywordrush.com
 * @copyright Copyright &copy; 2020 keywordrush.com
 */
class EmailHelper {

    public static function mail($to, $subject, $message, $headers = '', $attachments = array())
    {
        if (!is_array($to))
            $to = array($to);

        /*
          if (GeneralConfig::getInstance()->option('from_email'))
          \add_filter('wp_mail_from', array(__CLASS__, 'setMailFrom'));
          if (GeneralConfig::getInstance()->option('from_name'))
          \add_filter('wp_mail_from_name', array(__CLASS__, 'setMailFromName'));
         * 
         */

        foreach ($to as $email)
        {
            $res = \wp_mail($email, $subject, $message, $headers, $attachments);
        }

        /*
          \remove_filter('wp_mail_from', array(__CLASS__, 'setMailFrom'));
          \remove_filter('wp_mail_from_name', array(__CLASS__, 'setMailFromName'));
         * 
         */
        return $res;
    }

    /*
      public static function setMailFrom()
      {
      return GeneralConfig::getInstance()->option('from_email');
      }

      public static function setMailFromName()
      {
      return GeneralConfig::getInstance()->option('from_name');
      }
     * 
     */
}
