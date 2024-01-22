/* global tinymce , affegg_editor_button
 */

(function () {

    'use strict';

    // only do this if TinyMCE is available
    if ('undefined' === typeof (tinymce)) {
        return;
    }

    /**
     * Register a button for the TinyMCE (aka Visual Editor) toolbar
     *
     * @since 1.0.0
     */

    tinymce.create('tinymce.plugins.AffeggPlugin', {
        init: function (ed, url) {
            ed.addCommand('Affegg_insert_table', window.affegg_open_shortcode_thickbox);

            ed.addButton('affegg_insert_table', {
                title: affegg_editor_button.title,
                cmd: 'Affegg_insert_table',
            });
        }

    });
    tinymce.PluginManager.add('affegg_tinymce', tinymce.plugins.AffeggPlugin);

})();
