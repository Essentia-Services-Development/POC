/**
 * JavaScript code for the "Affegg" button in the QuickTags editor toolbar
 *
 */

/* global affegg_editor_button, QTags, tb_show */

jQuery(document).ready(function ($) {

    'use strict';

    window.affegg_open_shortcode_thickbox = function () {
        var width = $(window).width(),
                W = (720 < width) ? 720 : width,
                H = $(window).height();
        if ($('#wpadminbar').length) {
            H -= parseInt(jQuery('#wpadminbar').css('height'), 10);
        }

        tb_show(affegg_editor_button.thickbox_title, affegg_editor_button.thickbox_url + '&TB_iframe=true&height=' + (H - 85) + '&width=' + (W - 80), false);
    };

    // only do this if QuickTags is available
    if ('undefined' === typeof (QTags)) {
        return;
    }

    /**
     * Register a button for the Quicktags (aka HTML editor) toolbar
     */
    QTags.addButton(
            'affegg_quicktags_button', // ID
            affegg_editor_button.caption, // button caption
            window.affegg_open_shortcode_thickbox, // click callback
            false, // unused
            false, // access key
            affegg_editor_button.title, // button title
            115											// button position (here: between code and more)
            );

});
