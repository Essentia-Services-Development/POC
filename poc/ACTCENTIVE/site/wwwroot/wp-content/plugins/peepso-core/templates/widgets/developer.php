<?php

    $plugins_dir 	= dirname(dirname(dirname(dirname(__FILE__))));

$plugins = scandir($plugins_dir);
$git = array();
    $git_desc = array();
foreach($plugins as $plugin_dir) {

        $plugin_desc = $plugin_dir;
    $plugin_dir = $plugins_dir.'/'.$plugin_dir;
    if(!is_dir($plugin_dir) || in_array($plugin_dir, array('.','..'))) continue;

    $git_dir = $plugin_dir.'/'.'.git';

    if(file_exists($git_dir)) {

        $stringfromfile = file($git_dir.'/HEAD', FILE_USE_INCLUDE_PATH);

        $gs=explode('/', $stringfromfile[0]);
        $gs = end($gs);

        @$git[$gs]++;
            @$git_desc[$gs].="$plugin_desc\n";

        }

        arsort($git);
    }

    // themes
    $themes_dir = dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/themes';
    $themes = scandir($themes_dir);
    $git_p = array();
    $git_p_desc = array();
    foreach($themes as $theme_dir) {

        $theme_desc = $theme_dir;
        $theme_dir = $themes_dir.'/'.$theme_dir;
        if(!is_dir($theme_dir) || in_array($theme_dir, array('.','..'))) continue;

        $git_dir = $theme_dir.'/'.'.git';

        if(file_exists($git_dir)) {

            $stringfromfile = file($git_dir.'/HEAD', FILE_USE_INCLUDE_PATH);

            $gs=explode('/', $stringfromfile[0]);
            $gs = end($gs);

            @$git_p[$gs]++;
            @$git_p_desc[$gs].="$theme_desc\n";

    }

    arsort($git);
}
?>

<div style="border-radius:10px;padding:10px;font-size:12px;line-height:14px;margin-top:-15px;margin-bottom:15px;opacity:0.5;border:solid 1px #aaaaaa;">
    <center>
        <strong>Plugins .git</strong><br/>
        <?php foreach($git as $v=>$c) { echo "$v <abbr title='{$git_desc[$v]}'><small style='font-size:0.8em;opacity:0.5'>($c)</small></abbr><br/>"; } ?>

        <br/>

        <strong>Themes .git</strong><br/>
        <?php foreach($git_p as $v=>$c) { echo "$v <abbr title='{$git_p_desc[$v]}'><small style='font-size:0.8em;opacity:0.5'>($c)</small></abbr><br/>"; } ?>

        <br/>
        <strong>Environment</strong><br/>
        WP: <?php global $wp_version; echo $wp_version;?><br/>
        PHP: <?php echo PHP_VERSION;?><br/>
    </center>
</div>