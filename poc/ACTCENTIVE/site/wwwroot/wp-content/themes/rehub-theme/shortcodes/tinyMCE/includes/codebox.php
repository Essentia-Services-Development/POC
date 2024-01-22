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
			'style'     : '1'
			};
		var codebox_text = jQuery('#codebox-text').val();
		if( ! tinyMCE.activeEditor || tinyMCE.activeEditor.isHidden()) {
		 var selection_codebox = jQuery("textarea.wp-editor-area").selection('get');
		}
		else {
        	var selection_codebox = tinyMCE.activeEditor.selection.getContent();  
        }

		var shortcode = '[wpsm_codebox';
		
		for( var index in options) {
			var value = jQuery('#form').find('#codebox-' + index).val();
			
			if ( value !== '' )
				shortcode += ' ' + index + '="' + value + '"';
			else
				shortcode += ' ' + index + '="' + options[index] + '"'; 	
		}
		shortcode += ']';

		if ( codebox_text !== '' )
			shortcode += codebox_text;
		else if	( selection_codebox !== '' )
			shortcode += selection_codebox;
		else 
			shortcode += 'Sample Text';

		shortcode += '[/wpsm_codebox]';
		
        window.send_to_editor(shortcode);
		
		// closes Thickbox
		tb_remove();
	});
}); 
</script>
<form action="/" method="get" id="form" name="form" accept-charset="utf-8">
	
	<p><label><?php esc_html_e('Style', 'rehub-theme') ;?></label>
       	<select name="codebox-style" id="codebox-style" size="1">
			<option value="1" selected="selected"><?php esc_html_e('Simple', 'rehub-theme') ;?></option>
			<option value="2"><?php esc_html_e('With left blue line', 'rehub-theme') ;?></option>
        </select>
    </p>  
    <p>
        <label><?php esc_html_e('Text', 'rehub-theme') ;?></label>
        <textarea type="text" name="codebox-text" value="" id="codebox-text" col="10"></textarea><br />
        <small><?php esc_html_e('Leave blank if you selected text in visual editor', 'rehub-theme') ;?></small>
    </p>    
	 <p>
        <label>&nbsp;</label>
        <input type="button" id="submit" class="button" value="<?php esc_html_e('Insert', 'rehub-theme') ;?>" name="submit" />
    </p>
</form>