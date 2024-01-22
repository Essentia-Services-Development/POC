<?php

class PeepSoEmbed
{
    public $url;
    public $data;
    public $_error;
    public $_oembed;
    public $_oembed_discovery;

    public function __construct($url)
    {
        $this->url = $url;
        $this->_data = array();
        $this->_error = FALSE;
        $this->_oembed = defined('PEEPSO_ENABLE_OEMBED') ? PEEPSO_ENABLE_OEMBED : TRUE;
        $this->_oembed_discovery = defined('PEEPSO_ENABLE_OEMBED_DISCOVERY') ? PEEPSO_ENABLE_OEMBED_DISCOVERY : TRUE;
    }

    public function fetch()
    {
        if (strpos($this->url, PeepSo::get_page('profile')) !== FALSE) {
            $url = $this->url;
            add_filter('embed_html', function($output, $post, $width, $height) use ($url) {
                $output = preg_replace('/<a(.*)href="([^"]*)"(.*)>/','<a$1href="' . $url . '"$3>', $output);
                $output = preg_replace('/<iframe(.*)src="([^"]*)"(.*)>/','<iframe$1src="' . $url . '/embed"$3>', $output);
                return $output;
            }, 99, 4);

            add_filter('oembed_request_post_id', function($post_id, $url) {
                if (strpos($url, PeepSo::get_page('profile')) !== FALSE) {
                    $page = get_page_by_path(PeepSo::get_option('page_profile'));
                    if (is_object($page)) {
                        $post_id = $page->ID;
                    }
                }
                return $post_id;
            }, 10, 2);
        }

        $this->_oembed = apply_filters('peepso_enable_oembed', $this->_oembed, $this->url);
        $this->_oembed_discovery = apply_filters('peepso_enable_oembed_discovery', $this->_oembed_discovery, $this->url);

        // This method might end up firing several calls
        // The timeout is multiplied by the amount of attempts
        // The time taken to fire everything might cause perceived very slow loads
        $timeout = 2;
        $redirection = 2;
        $oembed_html = '';

        new PeepSoError("\n\n\n\n* * * * * * * * * * * * * * NEW PEEPSOEMBED() * * * * * * * * * * * * * *\n\n" . $this->url."\n" . date("Y-m-d H:i:s"));

        new PeepSoError("\n* * * * * * * * * * * * * * EMBED CONFIGURATION * * * * * * * * * * * * *\n");
        new PeepSoError( "Load previews \t\t".PeepSo::get_option('allow_embed'));
        new PeepSoError( "Small thumbs \t\t".PeepSo::get_option('small_url_preview_thumbnail'));
        new PeepSoError( "Allow non-SSL \t\t".PeepSo::get_option('allow_non_ssl_embed',''));
        new PeepSoError( "Prefer images \t\t".PeepSo::get_option('prefer_img_embeds',''));
        new PeepSoError( "Fallback img \t\t".PeepSo::get_option('guess_img_embeds',''));
        new PeepSoError( "Refresh embeds \t\t".PeepSo::get_option('refresh_embeds',''));

        new PeepSoError("\n* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *\n");

        $args = array('redirection' => $redirection, 'timeout' => $timeout, 'limit_response_size' => 512000,'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/116.0.0.0 Safari/537.36');

        if ($this->_oembed) {

            new PeepSoError("\nAttempt WP oEmbed: ");

            $oembed = _wp_oembed_get_object($args);

            // Try to find oEmbed endpoint from registered providers first
            // to prevent HTTP request from "WP_oEmbed::discover" function call.
            $html = $oembed->get_html( $this->url, array_merge(array('discover' => FALSE),$args) );
            if ($html) {
                new PeepSoError("  OK");
                $this->_data['html'] = $this->_filter_html($html);
                return TRUE;
            } else {
                new PeepSoError("  Fail");
            }
        } else {
            new PeepSoError("\nSkip WP oEmbed");
        }

        new PeepSoError("\nVerify content type: ");

        // Check content type before attempt to discover oEmbed endpoint from URL content.
        $request = wp_safe_remote_head($this->url, $args);
        $content_type = wp_remote_retrieve_header($request, 'content-type');
        $content_type = preg_replace('#^([^/]+/[^;]+).*$#i', '$1', $content_type);
        $is_text = preg_match('#^(text/|application/(javascript|x-javascript|json))#i', $content_type);
        $is_html = $is_text && preg_match('#^text/html#i', $content_type);

        // Do not fetch non-HTML content.
        if (!$is_html) {
            new PeepSoError("  Fail: content is not valid  HTML - DDOS protection?");
            // Set thumbnail based on content type.
            if (preg_match('#^(image|audio|video)/#i', $content_type, $matches)) {
                $thumbnail = array('type' => $matches[1], 'value' => $this->url);
            } else {
                $thumbnail = $this->_content_type_to_thumbnail($content_type);
            }

            $this->_data['url'] = $this->url;
            $this->_data['site_name'] = $this->_url_to_sitename($this->url);
            $this->_data['title'] = $this->_url_to_title($this->url);
            $this->_data['description'] = '';
            $this->_data['mime_type'] = $content_type;
            $this->_data['thumbnail'] = $thumbnail;
            return TRUE;
        } else {
            new PeepSoError("  OK");
        }

        // Discover oEmbed endpoint from URL content.
        if ($this->_oembed && $this->_oembed_discovery) {

            new PeepSoError("\nAttempt oEmbed discovery:");

            $html = $oembed->get_html( $this->url, array_merge(array('discover' => TRUE), $args) );

            if ($html) {
                new PeepSoError("  OK: oEmbed has HTML");
                if(!stristr($html, '<img') && PeepSo::get_option_new('prefer_img_embeds')) {
                    // admin prefers embeds with img, try falling back
                    new PeepSoError('  oEmbed has no image, and the image preference is enabled');
                    $oembed_html = $html; // use this if PeepSoEmbed fails
                } else {
                    new PeepSoError("  OK: using oEmbed");
                    $this->_data['html'] = $this->_filter_html($html);
                    return TRUE;
                }
            } else {
                new PeepSoError("  Fail");
            }
        } else {
            new PeepSoError("\nSkip oEmbed discovery");
        }

        // Do manual parsing if failed to get the oEmbed code.

        new PeepSoError("\nFallback 1: common browser headers");

        $request = wp_safe_remote_get($this->url, array_merge($args, ['timeout' => min(30, $timeout * 5)]));
        $html = wp_remote_retrieve_body($request);


        // If failed or can't find og:image, try spoofing Facebook user agent
        if(!$html || !strpos($html, 'og:image')) {

            new PeepSoError("  Fail");
            new PeepSoError("\nFallback 2: Facebook headers");

            $args_fb = array('timeout' => $timeout, 'limit_response_size' => 512000, 'user-agent' => 'facebookexternalhit/1.1');
            $request = wp_safe_remote_get($this->url, $args_fb);
            $html = wp_remote_retrieve_body($request);
        } else {
            // new PeepSoError("  OK");
        }

        if(!$html) {
            new PeepSoError("  Fail");
            new PeepSoError("\nFallbacks failed, using oEmbed anyway");
            // PeepSoEmbed failed, use whatever oembed gave us
            $html = $oembed_html;
        }else {
            new PeepSoError("  OK");
        }

        if ($html) {
            new PeepSoError("\nEmbed result\n  OK");

            $data = $this->_parse_html($html);

            // infer image from a <img> tag if you have to
            if(!$data['thumbnail'] && PeepSo::get_option_new('guess_img_embeds')) {
                new PeepSoError("\nAttempt to infer an image, as embed has no img tags");

                $dom = new DOMDocument;
                @$dom->loadHTML($html);
                $images = $dom->getElementsByTagName('img');

                if (count($images)) {

                    new PeepSoError("  ".count($images) . ' image tag(s) found');

                    $potential_thumbs = array();

                    foreach ($images as $image) {
                        $src = $image->getAttribute('src');
                        new PeepSoError("\n".$src);

                        $search = 'https';

                        if(PeepSo::get_option('allow_non_ssl_embed')) {
                            $search = 'http';
                        }

                        if($search == strtolower(substr($src,0,strlen($search)))) {
                            $potential_thumbs[]=$src;
                            new PeepSoError("  ".$search . ' found, potential thumb');
                            break;
                        } else {
                            new PeepSoError("  ".$search . ' not found, skipping this image');
                        }
                    }

                    new PeepSoError("\n".count($potential_thumbs) . " potential thumb(s)");
                    if(count($potential_thumbs)) {
                        $key = isset($_GET['thumb_override']) ? $_GET['thumb_override'] : 0;
                        $key = isset($potential_thumbs[$key]) ? $key : 0;

                        $data['thumbnail'] = $potential_thumbs[$key];

                        new PeepSoError("\nUsing thumb $key: {$data['thumbnail']}");

                        $this->_data['potential_thumbnails'] = count($potential_thumbs);
                    }


                } else {
                    new PeepSoError("  No img tags found");
                }
            }

            // Set generic thumbnail icon if no thumbnail image is found.
            if ($data['thumbnail']) {
                $thumbnail = array('type' => 'image', 'value' => $data['thumbnail']);
            } else {
                $thumbnail = $this->_content_type_to_thumbnail($content_type);
            }

            $this->_data['url'] = $this->url;
            $this->_data['site_name'] = $this->_url_to_sitename($this->url);
            $this->_data['title'] = $data['title'];
            $this->_data['description'] = $data['description'];
            $this->_data['mime_type'] = $content_type;
            $this->_data['thumbnail'] = $thumbnail;
            return TRUE;
        }

        new PeepSoError('Embed result negative');

        return FALSE;
    }

    public function get_data() {
        return $this->_data;
    }

    public function get_error()
    {
        return $this->_error;
    }

    private function _url_to_sitename($url)
    {
        if (preg_match('#^https?://(([^/]+)/)#i', $url, $matches)) {
            return $matches[2];
        }
        return '';
    }

    private function _url_to_title($url)
    {
        if (preg_match('#^https?://(([^/]+)/)*([^/\?\#]+)#i', $url, $matches)) {
            return $matches[3];
        }
        return '';
    }

    private function _content_type_to_thumbnail($content_type)
    {
        return array(
            'type' => 'image',
            'value' => PeepSo::get_asset('images/embeds/no_preview_available.png'),
        );

        $thumbnail = '';

        switch ($content_type) {
            // TODO: Return more thumbnail type based on content type.
            case 'application/pdf':
            case 'application/vnd.ms-excel':
            case 'application/x-sql':
            case 'application/zip':

            default:
                $thumbnail = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M4.01 2L4 22h16V8l-6-6H4.01zM13 9V3.5L18.5 9H13z"/></svg>';
                break;
        }

        return array(
            'type' => 'svg',
            'value' => $thumbnail
        );
    }

    private function _parse_html($html = '')
    {
        $data = array(
            'title' => '',
            'description' => '',
            'thumbnail' => ''
        );

        // Get title information.
        if (preg_match_all('#<meta[^>]+property=([\'"])og:title\1[^>]*>#is', $html, $matches)) {
            if (preg_match('# content=([\'"])(.*?)\1#is', array_pop($matches[0]), $matches)) {
                $data['title'] = $matches[2];
            }
        } else if (preg_match_all('#<title[^>]*>(.*?)</title>#is', $html, $matches)) {
            $data['title'] = array_pop($matches[1]);
        }

        // Get description information.
        if (preg_match_all('#<meta[^>]+property=([\'"])og:description\1[^>]*>#is', $html, $matches)) {
            if (preg_match('# content=([\'"])(.*?)\1#is', array_pop($matches[0]), $matches)) {
                $data['description'] = $matches[2];
            }
        } else if (preg_match_all('#<meta[^>]+name=([\'"])description\1[^>]*>#is', $html, $matches)) {
            if (preg_match('# content=([\'"])(.*?)\1#is', array_pop($matches[0]), $matches)) {
                $data['description'] = $matches[2];
            }
        }

        // Get thumbnail information.
        if (preg_match_all('#<meta[^>]+property=([\'"])og:image\1[^>]*>#is', $html, $matches)) {
            if (preg_match('# content=([\'"])(.*?)\1#is', array_pop($matches[0]), $matches)) {
                $data['thumbnail'] = $matches[2];
            }
        }

        return $data;
    }

    private function _filter_html($html)
    {
        // Alter Facebook embed code.
        if (preg_match('# class=([\'"])fb-(post|video)\1#is', $html)) {
            $html = preg_replace('#<div[^>]+id=([\'"])fb-root\1[^<]+</div>#is', '', $html);
            $html = preg_replace('#<script[^<]+</script>#is', '', $html);
            // Remove video width setting.
            $html = preg_replace('# data-width=([\'"])\d+%?\1#is', '', $html);
            // Add PeepSo Facebook embed handler.
            $html = $html . '<script>try{peepso.util.fbParseXFBML()}catch(e){}</script>';

        }

        // Alter iframe width to match container width.
        $html = preg_replace(
            '#(<iframe[^>]*) width=([\'"])(\d+%?)\2([^>]*>)#is',
            '$1 width=${2}100%$2 data-original-width=$2$3$2$4',
            $html
        );

        return $html;
    }

    /**
     * Setup PeepSo embed hooks.
     *
     * @static
     */
    public static function init()
    {
        add_filter('peepso_data', array('PeepSoEmbed', 'filter_data'));
        add_filter('peepso_embed_content', array('PeepSoEmbed', 'filter_embed_content'), 10, 3);
        add_action('wp_ajax_peepso_embed_content', array('PeepSoEmbed', 'ajax_embed_content'));
    }

    /**
     * Attach embed config to peepsodata variable.
     *
     * @static
     * @param array $data
     * @return array
     */
    public static function filter_data($data)
    {
        $data['embed'] = array(
            'enable' => PeepSo::get_option('allow_embed', 1),
            'enable_non_ssl' => PeepSo::get_option('allow_non_ssl_embed', 0),
        );
        return $data;
    }

    /**
     * Filter to fetch embed content of a URL.
     *
     * @static
     * @param array $data
     * @param string $url
     * @param boolean $refresh
     * @return array
     */
    public static function filter_embed_content($data, $url, $refresh = FALSE)
    {
        if(PeepSo::is_dev_mode('embeds')) {
            $refresh = true;
        }

        $embed_cache_key = 'peepso_embed_content_' . md5($url);

        // Get embed content from cache if possible.
        if (!$refresh) {
            $embed_content = PeepSo3_Mayfly::get($embed_cache_key);
            if ($embed_content) {
                $data['data'] = $embed_content;
                return $data;
            }
        }

        $is_peepso = FALSE;

        if( PeepSo::is_dev_mode('iframe_embeds') ) {
            $peepso_url = add_query_arg(['peepso_embed' => 1], $url);
            $resp = wp_remote_get($peepso_url, array('timeout' => 10, 'sslverify' => FALSE));
            new PeepSoError('Detecting PeepSo Single Activity');
            // In some cases sslverify is needed
            if (is_wp_error($resp)) {
                $resp = wp_remote_get($peepso_url, array('timeout' => 10, 'sslverify' => TRUE));
            }

            if (is_wp_error($resp)) {

            } else {
                $html = $resp['body'];

                if (strstr($html, '<!--PEEPSO_IS_POST-->')) {
                    $is_peepso = TRUE;
                    new PeepSoError('Confirmed PeepSo Single Activity');
                } else {
                    new PeepSoError('Not a PeepSo Single Activity, falling back');
                }
            }
        }

        if($is_peepso) {
            // @todo request the target site to provide an embeddable HTML based on privacy etc
            $data['data']['url'] = $url;
            $data['data']['site_name'] = 'SITENAME';
            $data['data']['title'] = 'TITLE';
            $data['data']['description'] = 'DESC';
            $data['data']['mime_type'] = 'MIME';
            $data['data']['thumbnail'] = 'THUMB';
            $data['data']['html']="<iframe width=100% src=$peepso_url></iframe>";
        } else {
            $embed = new PeepSoEmbed($url);
            if ($embed->fetch()) {
                $embed_content = $embed->get_data();
                $data['data'] = $embed_content;
                PeepSo3_Mayfly::set($embed_cache_key, $embed_content, HOUR_IN_SECONDS);
            } else {
                $data['error'] = $embed->get_error();
                PeepSo3_Mayfly::del($embed_cache_key);
            }
        }

        return $data;
    }

    /**
     * Ajax endpoint to fetch embed content of a URL.
     *
     * @static
     */
    public static function ajax_embed_content()
    {
        $result = array();
        $url = (string) $_POST['url'];
        $refresh = (int) $_POST['refresh'] ? TRUE : FALSE;
        $data = apply_filters('peepso_embed_content', array(), trim($url), $refresh);

        if ( isset($data['error']) ) {
            $result['error'] = $data['error'];
        } else {
            $html = PeepSoTemplate::exec_template('activity', 'content-embed', $data['data'], TRUE);
            $result['success'] = TRUE;
            $result['data'] = array('html' => $html);
        }

        echo json_encode($result);
        die();
    }
}

// EOF
