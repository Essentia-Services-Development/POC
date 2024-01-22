<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<script data-cfasync="false">

// executes this when the DOM is ready
jQuery(document).ready(function() {
	// handles the click event of the submit button
	jQuery('#submit').click(function(){
		// defines the options and their default values
		// again, this is not the most elegant way to do this
		// but well, this gets the job done nonetheless
		var options = { 
			'author'      	: '',
			'float'     : 'left',
			'width'     : '34%',
			};
		var quote_text = jQuery('#quote-text').val();
		if( ! tinyMCE.activeEditor || tinyMCE.activeEditor.isHidden()) {
		 var selection_quote = jQuery("textarea.wp-editor-area").selection('get');
		}
		else {
        	var selection_quote = tinyMCE.activeEditor.selection.getContent();  
        }

		var shortcode = '[wpsm_quote';
		
		for( var index in options) {
			var value = jQuery('#form').find('#quote-' + index).val();
			
			if ( value !== '' )
				shortcode += ' ' + index + '="' + value + '"';
			else
				shortcode += ' ' + index + '="' + options[index] + '"'; 	
		}
		shortcode += ']';

		if ( quote_text !== '' )
			shortcode += quote_text;
		else if	( selection_quote !== '' )
			shortcode += selection_quote;
		else 
			shortcode += 'Sample Text';

		shortcode += '[/wpsm_quote]';
		
        window.send_to_editor(shortcode);
		
		// closes Thickbox
		tb_remove();
	});
}); 
</script>
<form action="/" method="get" id="form" name="form" accept-charset="utf-8">
	<p><label><?php esc_html_e('Author', 'rehub-theme') ;?></label>
        <input type="text" name="quote-author" value="" id="quote-author" /><br />
        <small>Or live blank if you don't want to show author</small>
    </p>
	
	<p><label><?php esc_html_e('Float', 'rehub-theme') ;?></label>
       	<select name="quote-float" id="quote-float" size="1">
			<option value="left" selected="selected"><?php esc_html_e('left', 'rehub-theme') ;?></option>
			<option value="right"><?php esc_html_e('right', 'rehub-theme') ;?></option>
			<option value="none"><?php esc_html_e('none', 'rehub-theme') ;?></option>
        </select>
    </p>  
    <p><label><?php esc_html_e('Width (with %)', 'rehub-theme') ;?></label>
        <input type="text" name="quote-width" value="" id="quote-width" />
    </p> 
    <p>
        <label><?php esc_html_e('Text', 'rehub-theme') ;?></label>
        <input type="text" name="quote-text" value="" id="quote-text" /><br />
        <small><?php esc_html_e('Leave blank if you selected text in visual editor', 'rehub-theme') ;?></small>
    </p>    
	 <p>
        <label>&nbsp;</label>
        <input type="button" id="submit" class="button" value="<?php esc_html_e('Insert', 'rehub-theme') ;?>" name="submit" />
    </p>
</form>