
jQuery(document).ready(function() {
	console.log('Scalability Pro Admin JS loaded');

    jQuery(document).on('keypress', 'input.select2-search__field', function(e) {
        console.log('searching');
        const boundFunctionToUpdateList = function() {
            jQuery.ajax({
                url: ajaxurl, 
                context:jQuery(this).closest('tr'),
                type: "POST",
                data: {
                    action: 'spro_search_terms',
                    search_string: jQuery(this).val(),
                    taxonomy: jQuery(this).closest('tr').find('.attribute_name input').first().val()
                },
                success: function(response) {
                    console.log('response received');
                    console.log(response);
                    jQuery(this).find('select.attribute_values option:not(:selected)').remove();
                    
                    var newitems = jQuery(response);

                    jQuery(this).find('select.attribute_values').append(response);
                    jQuery(this).find('select.attribute_values option:selected').each(function() {
                        jQuery(this).closest('select').find('option[value="' + jQuery(this).val() + '"]:not(:selected)').remove();
                    });
                    jQuery(this).find('select.attribute_values').append(newitems.find('option'));

                    jQuery(this).find('.select2-search__field').trigger('change');
                    jQuery(this).find('select.attribute_values').trigger('change');

                }
            });
        }.bind(this);

        setTimeout(boundFunctionToUpdateList, 10);
/*
        e.stopPropagation();
        e.preventDefault();  
        e.returnValue = false;
        e.cancelBubble = true;
        return false;
        */
    }
    );

});
