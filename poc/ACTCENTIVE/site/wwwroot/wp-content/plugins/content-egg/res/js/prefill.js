var cegg_p_stop = 0;
var cegg_start_prefill_begin = 0;
var cegg_xxx;
var cegg_in_action = false;
var cegg_post_ids = [];
var cegg_post_ids_total = 0;

jQuery(document).ready(function ($) {

    updateProgress(0);
    getPostIds();

    jQuery('#start_prefill_begin').on('click', function () {
        if (cegg_in_action) {
            return false;
        }

        getPostIds();
        if (cegg_post_ids.length == 0)
            return false;

        cegg_p_stop = 0;
        cegg_start_prefill_begin = 1;
        jQuery('#start_prefill').prop('disabled', true);
        jQuery('#start_prefill_begin').prop('disabled', true);
        jQuery('#stop_prefill').prop('disabled', false);
        updateProgress(0);
        jQuery("#filled").text(0);
        jQuery("#not_filled").text(jQuery("#total").text());
        jQuery("#logs").css("display", 'none');
        jQuery('#logs').html('');
        jQuery("#logs").css("height", '200px');
        if (jQuery("#logs").is(":hidden"))
            jQuery("#logs").slideDown("slow");

        prefill();
    });

    jQuery('#start_prefill').on('click', function () {
        if (cegg_in_action) {
            return false;
        }
        getPostIds();
        if (cegg_post_ids.length == 0)
            return false;

        cegg_p_stop = 0;
        jQuery('#start_prefill').prop('disabled', true);
        jQuery('#start_prefill_begin').prop('disabled', true);
        jQuery('#stop_prefill').prop('disabled', false);
        //jQuery("#logs").css("display", 'none');
        //jQuery('#logs').html('');
        //jQuery("#logs").css("height", '200px');
        if (jQuery("#logs").is(":hidden"))
            jQuery("#logs").slideDown("slow");

        prefill();
    });

    jQuery('#stop_prefill').on('click', function () {
        cegg_p_stop = 1;
        cegg_xxx.abort();
        jQuery('#start_prefill').prop('disabled', false);
        jQuery('#start_prefill_begin').prop('disabled', false);
        jQuery('#stop_prefill').prop('disabled', true);
        jQuery('#ajaxBusy').hide();

    });

    jQuery(function () {
        jQuery('#keyword_source').on('change', function () {
            if (jQuery('#keyword_source').val() == '_custom_field') {
                jQuery('#custom_field').show();
            } else {
                jQuery('#custom_field').hide();
            }
        });
    });

});

function getPostIds()
{
    var post_type = jQuery("#post_type").val();
    var post_status = jQuery("#post_status").val();

    cegg_post_ids = [];
    var j = 0;
    for (var i = 0; i < content_egg_prefill.posts.length; i++) {

        if (jQuery.inArray(content_egg_prefill.posts[i].post_type, post_type) == -1)
            continue;
        if (jQuery.inArray(content_egg_prefill.posts[i].post_status, post_status) == -1)
            continue;

        cegg_post_ids[j] = content_egg_prefill.posts[i].id;
        j++;
    }
    cegg_post_ids_total = cegg_post_ids.length;
    jQuery('#post_ids_total').text(cegg_post_ids_total);
}

function prefill() {

    jQuery('#ajaxBusy').show();

    var post_id = cegg_post_ids.shift();

    if (cegg_post_ids.length == 0)
        cegg_p_stop = 1;

    var prefill_url = ajaxurl + '?action=content-egg-prefill';

    var module_id = jQuery("#module_id").val();
    var keyword_source = jQuery("#keyword_source").val();
    var keyword_count = jQuery("#keyword_count").val();
    var minus_words = jQuery("#minus_words").val();
    var autoupdate = jQuery("#autoupdate").is(':checked');
    var custom_field = jQuery("#custom_field").val();
    // var custom_field_names = jQuery('input[name="custom_field_names[]"]').val();    
    //var custom_field_values = jQuery('input[name="custom_field_values[]"]').serialize();   

    var custom_field_names = jQuery("input[name='custom_field_names[]']")
            .map(function () {
                return jQuery(this).val();
            }).get();
    var custom_field_values = jQuery("input[name='custom_field_values[]']")
            .map(function () {
                return jQuery(this).val();
            }).get();


    var data = {
        'post_id': post_id,
        'module_id': module_id,
        'keyword_source': keyword_source,
        'keyword_count': keyword_count,
        'autoupdate': autoupdate,
        'minus_words': minus_words,
        'custom_field': custom_field,
        'custom_field_names': custom_field_names,
        'custom_field_values': custom_field_values,
        'nonce': content_egg_prefill.nonce
    };

    cegg_xxx = jQuery.ajax({
        url: prefill_url,
        dataType: 'json',
        //dataType: (jQuery.browser.msie) ? "text" : "xml",
        cache: false,
        type: 'POST',
        timeout: 600000, //10min
        data: data,
    });

    var progress = (cegg_post_ids_total - cegg_post_ids.length) * 100 / cegg_post_ids_total;

    cegg_xxx.done(function (data, textStatus) {
        jQuery('#logs').prepend(data['log'] + '<br />');
        //updateProgress(parseInt(data['progress']));
        jQuery("#total").text(data['total']);
        jQuery("#filled").text(data['filled']);
        jQuery("#not_filled").text(data['total'] - data['filled']);
        cmd = data['cmd'];
        cegg_start_prefill_begin = 0;
        updateProgress(progress);

        //stop prefill
        if (cegg_p_stop == 1 || cmd == 'stop') {
            if (cegg_p_stop)
                jQuery('#start_prefill').prop('disabled', false);
            else
                jQuery('#start_prefill').prop('disabled', true);

            jQuery('#start_prefill_begin').prop('disabled', false);
            jQuery('#stop_prefill').prop('disabled', true);
            jQuery('#ajaxWaiting').hide();
            jQuery('#ajaxBusy').hide();

            return false;
        } else {
            //recursion
            jQuery('#ajaxWaiting').show();
            var pause = jQuery('#delay').val();
            setTimeout('prefill()', pause);
        }
    });

    cegg_xxx.fail(function (jqXHR, textStatus, errorThrown) {
        jQuery('#logs').prepend('<span class="label label-important">Error: ' + errorThrown + '</span><br>');
        updateProgress(progress);

        //stop prefill
        if (cegg_p_stop == 1) {
            jQuery('#start_prefill').prop('disabled', false);
            jQuery('#start_prefill_begin').prop('disabled', false);
            jQuery('#stop_prefill').prop('disabled', true);
            jQuery('#ajaxWaiting').hide();
            jQuery('#ajaxBusy').hide();

            return false;
        } else {
            //recursion
            jQuery('#ajaxWaiting').show();
            //var sleep = jQuery("#sleep").text();
            var sleep = 300;
            setTimeout('prefill()', sleep);
        }
    });


}

function updateProgress(percentage) {
    if (isNaN(percentage))
        return;
    if (percentage > 100)
        percentage = 100;

    jQuery("#progressbar").progressbar({
        value: percentage
    });
}