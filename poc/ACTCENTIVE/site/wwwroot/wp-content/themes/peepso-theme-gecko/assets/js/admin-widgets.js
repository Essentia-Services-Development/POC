(function($, wp) {
    // Initialize widget forms.
    function initWidgetForms() {
        // Find uninitialized widget forms.
        var $forms = $( '[data-gc="widget-form"]' );

        // Skip forms on inactive widgets.
        $forms = $forms.not(
            $( '#available-widgets, #wp_inactive_widgets' ).find( '[data-gc="widget-form"]' )
        );

        $forms.each(function() {
            var $form = $( this );
            var $select = $form.find( '[data-gc="widget-style"]' );
            var $gradients = $form.find( '[data-gc="widget-gradients"]' );
            var $colorpickers = $form.find( '[class*="my-color-picker"]' );

            // Remove `data-gc` attributes to prevent reinitialization.
            $form.add( $select ).add( $gradients ).removeAttr( 'data-gc' );

            // Toggle gradient colors box.
            $select.on( 'change.gc', function() {
                if ( 'gradient' === this.value ) {
                    $gradients.show();
                } else {
                    $gradients.hide();
                }
            } ).triggerHandler( 'change.gc' );

            // Initialize color picker.
            $colorpickers.wpColorPicker({
                change: function(e, ui) {
                    $( e.target ).val( ui.color.toString() );
                    $( e.target ).trigger('change'); // enable widget "Save" button
                }
            });
        });
    }

    // Initialize additional widget forms on page load as well as on every add and update widget.
    $(function() {
        initWidgetForms();
        $(document).on('widget-added widget-updated', initWidgetForms);
    });

})(jQuery, wp );
