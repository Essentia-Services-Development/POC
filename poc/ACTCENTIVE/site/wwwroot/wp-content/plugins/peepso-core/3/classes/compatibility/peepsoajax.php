<?php

class PeepSo3_Helper_PeepSoAJAX_Online
{
    private static $instance;

    public static $message = 'PeepSo encountered an issue with PeepSoAJAX URLs. This will interfere with the community front-end and licensing/updates.';
    public static $message_installer = 'Sorry, the operation cannot be completed due to server error. Please <a href="https://www.PeepSo.com" target="_blank">contact PeepSo Support</a>.';
    public static $description = '
If you are using an <b>NGINX server</b>, please <a href="https://www.google.com/search?q=nginx+and+wordpress" target="_blank">make sure your rewrites are configured properly</a>.

<br/><br/>

This message might be a <b>false positive</b> if you are using <b>htpasswd, guest redirect or other security solutions</b> - if this is the case and your community works fine, <a id="peepsoajax_dismiss_warning" href="HIDEURL"><b>dismiss this message</b></a> for a month.

<span class="peepsoajax_debug">

<br/><br/>

Please do not hesitate to <a href="https://www.PeepSo.com/contact" target="_blank">contact the PeepSo support</a>, we are happy to help!<br/><br/>

<a href="RESETURL">Click here</a> to test this problem again.

<br/><br/>

DEBUG

</span>
';

    public static function get_instance() {
        if(!is_admin()) { return; }
        return isset(self::$instance) ? self::$instance : self::$instance = new self;
    }

    public static function get_message($context = '') {

        if( strlen($context) && property_exists('PeepSo3_Helper_PeepSoAJAX_Online', 'message_'.$context) ) {
            $context = 'message_'.$context;
            return self::$$context;
        }

        return self::$message;
    }

    public static function get_description() {
        ob_start();
        $debug= [
            'response_code' => print_r( PeepSo3_Mayfly::get('peepsoajax_response_code'),TRUE ),
            'response_json' => print_r( PeepSo3_Mayfly_Int::get('peepsoajax_response_is_json'), TRUE ),
            'response_size' => print_r( strlen(PeepSo3_Mayfly_Int::get('peepsoajax_response_body')), TRUE),
        ];
        ?>
        <b>Debug information:</b><br/>
        <code><?php echo json_encode($debug);?></code>
        <?php
        $debug = ob_get_clean();

        return str_replace(['RESETURL','HIDEURL','DEBUG'], [add_query_arg('check_peepsoajax',1),add_query_arg('dismiss_peepsoajax',1), $debug], self::$description);
    }

    private function __construct()
    {
        // If user clicked "check again" or we are trying to reset everything
        if(isset($_GET['check_peepsoajax']) && 1==$_GET['check_peepsoajax']) {
            PeepSo3_Mayfly_Int::del_like('%peepsoajax%');
            $result = NULL;
        }

        // If user already dismissed
        if(PeepSo3_Helper_PeepSoAJAX_Online::maybe_dismissed()) { return; }

        // Check cached result
        // 0 = not broken, 1 = broken, NULL = not tested
        $result = PeepSo3_Mayfly_Int::get('peepsoajax_is_broken');

        if(NULL === $result) {
            // Assume it's fine
            $result = 0;

            // Test PeepSoAJAX
            $url = trim(home_url(),'/').'/peepsoajax/adminAddons.check_license';

            // Uncomment to force an issue
            // $url = trim(home_url(),'/').'/breakpeepsoajax/adminAddons.check_license';

            $response = wp_remote_get($url,array( 'sslverify' => false, 'timeout' => 30 ) );
            $response_code = wp_remote_retrieve_response_code( $response );
            $response_body = wp_remote_retrieve_body($response);

//          echo $url;
//          var_dump($response);
//          var_dump($response_code);
//          var_dump(strlen($response_body));

            // We expect valid JSON
            json_decode($response_body);
            if(json_last_error() === JSON_ERROR_NONE) {
                $response_is_json = 1;
            } else {
                $response_is_json = 0;
                $result = 1; // peepsoajax_is_broken
            }

            PeepSo3_Mayfly_Int::set('peepsoajax_is_broken', $result, 3600);
            PeepSo3_Mayfly::set('peepsoajax_response_code', $response_code, 3600);
            PeepSo3_Mayfly_Int::set('peepsoajax_response_is_json', $response_is_json, 3600);
            PeepSo3_Mayfly::set('peepsoajax_response_body', $response_body, 3600);
        }

        if( is_numeric($result) && 0 == $result) {
            // All is OK, no action
        }

        if( is_numeric($result) && 1 == $result && (!isset($_GET['page']) || 'peepso-installer'!=$_GET['page']) ) {
            add_action('admin_notices', function() {
                echo "<div class=\"error peepso error-peepsoajax\">";
                echo "<span style=\"font-weight:bold;font-size:14px;\">".self::get_message()."</span>";
                echo "<br/><br/>";
                echo self::get_description();
                echo "<br/><br/></div>";
            });
        }
    }

    public static function maybe_dismissed() {
        // If user dismissed the message
        $mayfly = 'peepsoajax_dismiss_user_'.get_current_user_id();

        if(isset($_GET['dismiss_peepsoajax']) && 1==$_GET['dismiss_peepsoajax']) {
            PeepSo3_Mayfly_Int::set($mayfly,1,30*24*3600);
        }

        if(PeepSo3_Mayfly_Int::get($mayfly)) {
            return TRUE;
        }

        return FALSE;
    }
}

add_action('init', function() {
    PeepSo3_Helper_PeepSoAJAX_Online::get_instance();
});