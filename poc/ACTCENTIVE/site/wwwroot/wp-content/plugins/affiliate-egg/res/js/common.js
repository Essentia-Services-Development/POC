jQuery(document).ready(function ($) {
    $(".affegg-delete").on('click', function () {
        if (!confirm(affeggL10n.are_you_shure)) {
            return false;
        }
    });

    $('#doaction, #doaction2').on('click', function () {
        var bulk_action,
                confirm_message,
                num_selected = $('.affegg-all-tables').find('tbody').find('input:checked').length;

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
                confirm_message = affeggL10n.are_you_shure;
            else
                confirm_message = affeggL10n.are_you_shure;


            if (!confirm(confirm_message)) {
                return false;
            }
        }
    });

    $('.affegg-all-tables').on('click', '.shortcode a', function ( /* event */ ) {
        prompt(affeggL10n.use_shortcode, $(this).attr('title'));
        return false;
    });


});