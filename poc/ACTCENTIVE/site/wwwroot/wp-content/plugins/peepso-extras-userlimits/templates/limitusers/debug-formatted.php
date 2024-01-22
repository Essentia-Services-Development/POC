<?php
$PeepSoProfile = PeepSoProfile::get_instance();
$PeepSoUser = $PeepSoProfile->user;
//
//echo "<pre>";
//var_dump($data);
//echo "</pre>";
?>
<div class="ps-ulimits__debug-wrapper">


    <?php

    $url = PeepSo::get_option('limitusers_roles_url', FALSE);

    if($url) { echo "<a href=\"$url\">"; }
    if (isset($data['role']) && count($data['role'])) {

        echo '<div class="ps-ulimits__debug">';
        echo '<div class="ps-ulimits__debug-title"><i class="gcis gci-user"></i>';
        echo PeepSo::get_option('limitusers_roles_message',__('Some actions are role-restricted:', 'peepsolimitusers'), TRUE);
        echo '</div>';
        echo '<div class="ps-ulimits__debug-list">';
        foreach ($data['role'] as $limit => $sections) {
            foreach ($sections as $section) {
                echo '<span class="ps-ulimits__debug-item">', $sections_icon[$section] , ' ', ucfirst($sections_descriptions[$section]), '</span>';
            }
        }
        echo '</div>';
        echo '</div>';
    }
    if($url) { echo "</a>"; }
    ?>


    <a href="<?php echo $PeepSoUser->get_profileurl();?>about">
        <?php


        if (isset($data['avatar']) && count($data['avatar'])) {
            echo '<div class="ps-ulimits__debug">';
            echo '<div class="ps-ulimits__debug-title"><i class="gcis gci-camera"></i>', __('Add an avatar to:', 'peepsolimitusers'), '</div>';
            echo '<div class="ps-ulimits__debug-list">';
            foreach ($data['avatar'] as $section) {
                echo '<span class="ps-ulimits__debug-item">',  $sections_icon[$section] , ucfirst($sections_descriptions[$section]), '</span>';
            }
            echo '</div>';
            echo '</div>';
        }

        if (isset($data['cover']) && count($data['cover'])) {
            echo '<div class="ps-ulimits__debug">';
            echo '<div class="ps-ulimits__debug-title"><i class="gcis gci-image"></i>', __('Add a profile cover to:', 'peepsolimitusers'), '</div>';
            echo '<div class="ps-ulimits__debug-list">';
            foreach ($data['cover'] as $section) {
                echo '<span class="ps-ulimits__debug-item">', $sections_icon[$section] , ucfirst($sections_descriptions[$section]), '</span>';
            }
            echo '</div>';
            echo '</div>';
        }

        if (isset($data['profile']) && count($data['profile'])) {

            ksort($data['profile']);

            echo '<div class="ps-ulimits__debug">';
            echo '<div class="ps-ulimits__debug-title"><i class="gcis gci-list-check"></i>', __('Complete your profile to at least:', 'peepsolimitusers'), '</div>';
            echo '<div class="ps-ulimits__debug-list">';
            foreach ($data['profile'] as $limit => $sections) {
                foreach ($sections as $section) {
                    echo '<span class="ps-ulimits__debug-item">',$sections_icon[$section],$limit,'% ',__('to', 'peepsolimitusers'),' ', $sections_descriptions[$section],'</span>';
                }

            }

            echo '</div>';
            echo '</div>';
        }



        ?>
    </a>
</div>
