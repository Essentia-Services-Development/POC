<?php

/****** ENABLED & VALIDATION ******/

$PeepSoInput = new PeepSoInput();
$url = $PeepSoInput->value('url', '', FALSE); // SQL Safe

$preview_mode = FALSE;
// Check if it is currently in Elementor preview mode.
if ( class_exists( '\Elementor\Plugin' ) ) {
    if ( \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
        $preview_mode = TRUE;
    }
}

if (!$preview_mode) {

    $redirect = FALSE;

    // if the feature is disabled
    if(!$redirect && 0==PeepSo::get_option('external_link_warning',0)) {
        $redirect = TRUE;
    }

    // if the URL passed is invalid
    if(!$redirect && !filter_var($url, FILTER_VALIDATE_URL)) {
        $redirect = TRUE;
    }

    // @TODO validate nonce?

    if($redirect) {
        PeepSo::redirect(PeepSo::get_page('activity'));
    }
}




/***** WHITELIST ******/

// if the URL is whitelisted
$parse = parse_url($url);
$host=trim(str_replace('www.','',strtolower($parse['host'])),' /');

$whitelist = explode("\n", PeepSo::get_option('external_link_whitelist', ''));

if(!is_array($whitelist)) {
    $whitelist = array();
}

// whitelist self
$parse = parse_url(get_site_url());
$self_host=str_replace('www.','',strtolower($parse['host']));
$whitelist[]=$self_host;

$allowed = array();
foreach($whitelist as $whitelist_item) {
    $whitelist_item = trim($whitelist_item);
    #$whitelist_item = trim($whitelist_item,'/');

    if(strlen($whitelist_item)) {
        $allowed[]=$whitelist_item;
    }
}

if(in_array($host, $allowed)) {
    if (!$preview_mode) {
        PeepSo::redirect($url);
    }
}

/****** BACK LINK ***/
$back = "javascript:window.history.back();";
$back_label = __('No, take me back', 'peepso-core');

$link_target = (int) PeepSo::get_option('site_activity_open_links_in_new_tab', 1);
if (2 === $link_target && 0 === strpos($url, site_url())) {
    $link_target = 0;
}

if ($link_target) {
    $back = "javascript:window.close();";
    $back_label = __('No, close this tab', 'peepso-core');
}
/****** RENDER ******/

// https://stackoverflow.com/questions/1734250/what-is-the-equivalent-of-javascripts-encodeuricomponent-in-php
function encodeURIComponent($str) {
    $revert = array('%21'=>'!', '%2A'=>'*', '%27'=>"'", '%28'=>'(', '%29'=>')');
    return strtr(rawurlencode($str), $revert);
}

// Encode URL params.
$url_encoded = $url;
$url_parsed = parse_url($url_encoded);
if ( isset($url_parsed['query']) && $url_parsed['query'] ) {
    $query = explode('&', $url_parsed['query']);
    foreach ($query as $key => $value) {
        if (preg_match('/^([^=]+)=([^=]+)$/', $value, $matches)) {
            $query[$key] = $matches[1] . '=' . encodeURIComponent($matches[2]);
        }
    }
    $query = implode('&', $query);
    $url_encoded = str_replace($url_parsed['query'], $query, $url_encoded);
}

//$url_link = "<a href=\"$url_encoded\">$url</a>";

?>

<div class="ps-redirect__box">
    <div class="ps-redirect__box-body">
        <p><?php echo sprintf(__('The link you just clicked redirects to: <span class="ps-redirect__link">%s</span>', 'peepso-core'), $url_encoded); ?></p>
        <hr>
        <p><?php echo __('Do you want to continue?', 'peepso-core'); ?></p>
    </div>
    <div class="ps-redirect__box-actions">
        <a role="button" class="ps-btn" href="<?php echo $back;?>"><?php echo $back_label; ?></a>
        <a role="button" class="ps-btn ps-btn-primary" href="<?php echo $url_encoded; ?>"
           data-no-hijack="1"><?php echo __('Yes, take me there', 'peepso-core'); ?></a>
    </div>
</div>
