<?php
if(PeepSo::get_option_new('fix_redirect_canonical')) {
    add_filter('redirect_canonical', function ($one, $two) {
        if (urldecode($one) == urldecode($two)) {
            return FALSE;
        }
    }, 9999, 2);
}