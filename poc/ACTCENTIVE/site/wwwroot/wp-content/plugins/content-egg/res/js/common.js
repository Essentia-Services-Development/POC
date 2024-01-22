jQuery(document).ready(function ($) {
    $(".content-egg-delete").on('click', function () {
        if (!confirm(contenteggL10n.are_you_shure)) {
            return false;
        }
    });

    $('#doaction, #doaction2').on('click', function () {
        var bulk_action,
                confirm_message,
                num_selected = $('.content-egg-all-tables').find('tbody').find('input:checked').length;

        if (this.id === 'doaction')
            bulk_action = 'top';
        else
            bulk_action = 'bottom';

        if ($('#bulk-action-' + bulk_action).val() === '-1')
            return false;

        if (num_selected === 0)
            return false;

        if ($('select[name=action]').val() === 'delete') {
            if (num_selected === 1)
                confirm_message = contenteggL10n.are_you_shure;
            else
                confirm_message = contenteggL10n.are_you_shure;

            if (!confirm(confirm_message)) {
                return false;
            }
        }
    });


});