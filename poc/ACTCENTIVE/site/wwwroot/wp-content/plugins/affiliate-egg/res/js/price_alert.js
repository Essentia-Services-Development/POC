jQuery(document).ready(function ()
{
    jQuery(".affegg-price-alert-wrap form").on("submit", function (event)
    {
        event.preventDefault();
        var $form = jQuery(this);
        var $wrap = $form.closest('.affegg-price-alert-wrap');
        var data = $form.serialize() + '&nonce=' + affeggPriceAlert.nonce;
        $wrap.find('.affegg-price-loading-image').show();
        $wrap.find('.affegg-price-alert-result-error').hide();
        $form.find('input, button').prop("disabled", true);
        jQuery.ajax({
            url: affeggPriceAlert.ajaxurl + '?action=affegg_start_tracking',
            type: 'post',
            dataType: 'json',
            data: data,
            success: function (result) {
                if (result.status == 'success')
                {
                    $wrap.find('.affegg-price-alert-result-succcess').show();
                    $wrap.find('.affegg-price-alert-result-succcess').html(result.message);
                    $wrap.find('.affegg-price-loading-image').hide();
                } else {
                    $form.find('input, button').prop("disabled", false);
                    $wrap.find('.affegg-price-alert-result-error').show();
                    $wrap.find('.affegg-price-alert-result-error').html(result.message);
                    $wrap.find('.affegg-price-loading-image').hide();
                }
            }
        });

    });
});
