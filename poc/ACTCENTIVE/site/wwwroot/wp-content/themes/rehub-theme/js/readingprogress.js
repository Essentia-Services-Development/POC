jQuery(document).ready(function($) {
'use strict';	
    $('body').prepend('<progress value=\"0\" class=\"rh-progress-bar\"></progress>');
     $('body').prepend('<style>.rh-progress-bar{position: fixed;left: 0;top:0;width: 100%;height: 5px;-webkit-appearance: none;appearance: none;border: none;background-color: transparent;z-index: 100000}.rh-progress-bar{color:#43c801;}.rh-progress-bar::-webkit-progress-bar {background-color: transparent;}.rh-progress-bar::-webkit-progress-value {background-color: #43c801;}.rh-progress-bar::-moz-progress-bar {background-color: #43c801;}</style>');

    var winHeight = $(window).height(), 
        docHeight = $('.post-inner').height(),
        progressBar = $('progress'),
        max, value;

        max = docHeight - winHeight;
        progressBar.attr('max', max);
    $(window).on("scroll", function(){
        value = $(window).scrollTop();
        progressBar.attr('value', value);
    });
});
