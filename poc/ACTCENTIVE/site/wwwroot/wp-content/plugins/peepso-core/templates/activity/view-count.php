<?php

$decimals = apply_filters('peepso_filter_short_view_count_decimals',1);
$threshold = apply_filters('peepso_filter_short_view_count_threshold',1000);


$general = PeepSo3_Utilities_String::shorten_big_number($general,$decimals,$threshold);
$unique = PeepSo3_Utilities_String::shorten_big_number($unique,$decimals,$threshold);

// General count only
if(1 == $mode) {
    echo $general;
}

// Unique count only
if(2 == $mode) {
    echo $unique;
}

// Unique + General
if(3 == $mode) {
    echo $unique . ' (' . $general . ')';
}