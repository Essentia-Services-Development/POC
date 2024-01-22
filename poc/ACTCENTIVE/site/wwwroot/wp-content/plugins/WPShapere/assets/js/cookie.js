jQuery(document).ready(function($) {
    "use strict";
    $('#wps-pro-show-15').on('click', function() { alert('15days');
        $.cookie('wps-pro-show-15' , 'Fifteen Days' , {expires:15});
    });

    $('#wps-pro-show-30').on('click', function() {
        $.cookie('wps-pro-show-30' , 'Thirty Days' , {expires:30});
    });

    $('#wps-pro-show-60').on('click', function() {
        $.cookie('wps-pro-show-60' , 'Sixty Days' , {expires:60});
    });
});
