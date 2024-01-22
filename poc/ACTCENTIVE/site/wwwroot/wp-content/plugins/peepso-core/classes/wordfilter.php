<?php

class PeepSoWordFilter {

    private static $_instance = NULL;

    // how to render
    const WORDFILTER_FULL = 1;
    const WORDFILTER_MIDDLE = 2;

    // shift characters to obfuscate plain keywords
    const CHARACTER_SHIFT = 5;

    public function __construct() {
        add_action('peepso_init', array(&$this, 'init'));
    }

    /**
     * Retrieve singleton class instance
     * @return Wordfilter-PeepSo instance
     */
    public static function get_instance()
    {
        if (NULL === self::$_instance) {
            self::$_instance = new self();
        }
        return (self::$_instance);
    }

    public function init()
    {

        if (!is_admin()) {
            add_action('wp_enqueue_scripts', array(&$this, 'enqueue_scripts'));
        }
    }

    /**
     * Enqueue custom scripts and styles
     *
     * @since 1.0.0
     */
    public function enqueue_scripts()
    {
        if (PeepSo::get_option('wordfilter_enable', 0)) {
            wp_enqueue_script('peepso-wordfilter', PeepSo::get_asset('js/wordfilter/bundle.min.js'),
                array('peepso'), PeepSo::PLUGIN_VERSION, TRUE);

            add_filter('peepso_data', function($data) {
                $keywords = explode( ',', PeepSo::get_option('wordfilter_keywords', '') );
                for ( $i = 0; $i < count( $keywords ); $i++ ) {
                    $keywords[ $i ] = $this->shift( $keywords[ $i ], self::CHARACTER_SHIFT );
                }

                $data['wordfilter'] = array(
                    'keywords' => $keywords,
                    'shift' => self::CHARACTER_SHIFT,
                    'mask' => PeepSo::get_option('wordfilter_character', '•'),
                    'type' => PeepSo::get_option('wordfilter_how_to_render', 1),
                    'filter_posts' => PeepSo::get_option('wordfilter_type_' . PeepSoActivityStream::CPT_POST, 1),
                    'filter_comments' => PeepSo::get_option('wordfilter_type_' . PeepSoActivityStream::CPT_COMMENT, 1),
                );

                if ( class_exists('PeepSoMessagesPlugin') ) {
                    $data['wordfilter']['filter_messages'] = PeepSo::get_option('wordfilter_type_' . PeepSoMessagesPlugin::CPT_MESSAGE, 1);
                }

                return $data;
            }, 10, 1);
        }
    }

    /**
     * Keyword characters shifter.
     *
     * @param string $keyword
     * @param int $shift
     * @return string
     */
    private function shift( $keyword = '', $offset = 0 )
    {
        $new_keyword = '';

        for ($i=0; $i < strlen($keyword); $i++) {
            $c = $keyword[$i];
            $islower = $c >= 'a' && $c <= 'z';
            $isupper = $c >= 'A' && $c <= 'Z';

            if ( $islower || $isupper  ) {
                $code = ord($c) - ($islower ? ord('a') : ord('A'));
                // shift the character code
                $code = $code + $offset;
                // normalize out-of-bound code
                $code = $code < 0 ? $code + 26 : $code % 26;
                // update character based on the new code
                $c = chr($code + ($islower ? ord('a') : ord('A')));
            }

            $new_keyword .= $c;
        }

        return $new_keyword;
    }

}