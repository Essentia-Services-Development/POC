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
			'num'      	: '1',
			'style'     : '1',
			};
		var numbox_text = jQuery('#numbox-text').val();
		if( ! tinyMCE.activeEditor || tinyMCE.activeEditor.isHidden()) {
		 var selection_numbox = jQuery("textarea.wp-editor-area").selection('get');
		}
		else {
        	var selection_numbox = tinyMCE.activeEditor.selection.getContent();  
        }

		var shortcode = '[wpsm_numbox';
		
		for( var index in options) {
			var value = jQuery('#form').find('#numbox-' + index).val();
			
			if ( value !== '' )
				shortcode += ' ' + index + '="' + value + '"';
			else
				shortcode += ' ' + index + '="' + options[index] + '"'; 	
		}
		shortcode += ']';

		if ( numbox_text !== '' )
			shortcode += numbox_text;
		else if	( selection_numbox !== '' )
			shortcode += selection_numbox;
		else 
			shortcode += 'Sample Text';

		shortcode += '[/wpsm_numbox]';
		
        window.send_to_editor(shortcode);
		
		// closes Thickbox
		tb_remove();
	});
}); 
</script>
<form action="/" method="get" id="form" name="form" accept-charset="utf-8">
	<p><label><?php esc_html_e('Number', 'rehub-theme') ;?></label>
        <input type="text" name="numbox-num" value="1" id="numbox-num" />
    </p>
	
	<p><label><?php esc_html_e('Style of number', 'rehub-theme') ;?></label>
       	<select name="numbox-style" id="numbox-style" size="1">
			<option value="1" selected="selected"><?php esc_html_e('Grey', 'rehub-theme') ;?></option>
			<option value="2"><?php esc_html_e('Black', 'rehub-theme') ;?></option>
			<option value="3"><?php esc_html_e('Orange', 'rehub-theme') ;?></option>
			<option value="4"><?php esc_html_e('Blue', 'rehub-theme') ;?></option>
        </select>
    </p>  
    <p>
        <label><?php esc_html_e('Text', 'rehub-theme') ;?></label>
        <textarea type="text" name="numbox-text" value="" id="numbox-text" col="10"></textarea><br />
        <small><?php esc_html_e('Leave blank if you selected text in visual editor', 'rehub-theme') ;?></small>
    </p>    
	 <p>
        <label>&nbsp;</label>
        <input type="button" id="submit" class="button" value="<?php esc_html_e('Insert', 'rehub-theme') ;?>" name="submit" />
    </p>
</form>