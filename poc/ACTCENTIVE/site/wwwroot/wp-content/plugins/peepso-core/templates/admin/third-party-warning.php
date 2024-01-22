<?php
// if class has not been provided, it's a regular WP thickbox installer
if(!$url_class) {
    $target = '';
    $url_class = 'thickbox open-plugin-details-modal';
    $url = admin_url('plugin-install.php?tab=plugin-information&plugin='.$url.'&TB_iframe=true&width=772&height=850');
}  else {
    $target = 'target="_blank"';
}
?>

<div class="error peepso">
    <?php
    $install_link_1 = sprintf('<a href="%s" class="%s" %s><b>%s</b></a>', $url, $url_class, $target, $name);
    $install_link_2 = sprintf('<a href="%s" class="%s" %s  style="text-decoration:none !important;">', $url, $url_class, $target);
    $install_link_3 = sprintf('<a href="%s" style="text-decoration:none !important;">', admin_url('plugins.php?s='.urlencode($name)));
    echo sprintf( __( '<b>PeepSo</b> requires %s (%s or newer) to be %sinstalled%s and %sactivated%s to run <b>PeepSo %s</b>', 'peepso-core'), $install_link_1, $ver_min, $install_link_2,'</a>',$install_link_3,'</a>',$peepso_name );?>
</div>

<?php if(strlen($extra)) {?>
    <div class="error peepso">
        <?php echo $extra; ?>
    </div>
<?php } ?>
