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
			'title'      	: 'Sample title',
			'style'     : '1',
			};
		var titlebox_text = jQuery('#titlebox-text').val();
		if( ! tinyMCE.activeEditor || tinyMCE.activeEditor.isHidden()) {
		 var selection_titlebox = jQuery("textarea.wp-editor-area").selection('get');
		}
		else {
        	var selection_titlebox = tinyMCE.activeEditor.selection.getContent();  
        }

		var shortcode = '[wpsm_titlebox';
		
		for( var index in options) {
			var value = jQuery('#form').find('#titlebox-' + index).val();
			
			if ( value !== '' )
				shortcode += ' ' + index + '="' + value + '"';
			else
				shortcode += ' ' + index + '="' + options[index] + '"'; 	
		}
		shortcode += ']<br />';

		if ( titlebox_text !== '' )
			shortcode += titlebox_text;
		else if	( selection_titlebox !== '' )
			shortcode += selection_titlebox;
		else 
			shortcode += 'Sample Text';

		shortcode += '<br />[/wpsm_titlebox]';
		
        window.send_to_editor(shortcode);
		
		// closes Thickbox
		tb_remove();
	});
}); 
</script>
<form action="/" method="get" id="form" name="form" accept-charset="utf-8">
	<p><label><?php esc_html_e('Title', 'rehub-theme') ;?></label>
        <input type="text" name="titlebox-title" value="" id="titlebox-title" />
    </p>
	
	<p><label><?php esc_html_e('Style', 'rehub-theme') ;?></label>
       	<select name="titlebox-style" id="titlebox-style" size="1">
			<option value="1" selected="selected"><?php esc_html_e('Grey', 'rehub-theme') ;?></option>
			<option value="main"><?php esc_html_e('Main Theme Color', 'rehub-theme') ;?></option>
			<option value="secondary"><?php esc_html_e('Secondary Theme Color', 'rehub-theme') ;?></option>
        </select>
    </p>  
    <p>
        <label><?php esc_html_e('Text', 'rehub-theme') ;?></label>
        <textarea type="text" name="titlebox-text" value="" id="titlebox-text" col="10"></textarea><br />
        <small><?php esc_html_e('Leave blank if you selected text in visual editor', 'rehub-theme') ;?></small>
    </p>    
	 <p>
        <label>&nbsp;</label>
        <input type="button" id="submit" class="button" value="<?php esc_html_e('Insert', 'rehub-theme') ;?>" name="submit" />
    </p>
</form>