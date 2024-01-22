jQuery(document).ready(function ()
{
    jQuery("i.cegg-disclaimer").click(function () {
        var $title = jQuery(this).find(".cegg-disclaimer-title");
        if (!$title.length) {
            jQuery(this).append('<span class="cegg-disclaimer-title">' + jQuery(this).attr("title") + '</span>');
        } else {
            $title.remove();
        }
    });

});
