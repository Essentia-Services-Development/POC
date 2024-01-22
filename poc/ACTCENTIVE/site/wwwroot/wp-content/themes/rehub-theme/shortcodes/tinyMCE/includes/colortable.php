<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<script data-cfasync="false">

// executes this when the DOM is ready
jQuery(document).ready(function() {
	// handles the click event of the submit button
	jQuery('#submit').click(function(){
		// defines the options and their default values
		// again, this is not the most elegant way to do this
		// but well, this gets the job done nonetheless
				var colortable = jQuery('#colortable').val();
                var contenttable = jQuery('#TableContent').val();
				var Columnposition = jQuery('#Columnposition');
					if( ! tinyMCE.activeEditor || tinyMCE.activeEditor.isHidden()) {
					 var tablecontent = jQuery("textarea.wp-editor-area").selection('get');
					}
					else {
			        	var tablecontent = tinyMCE.activeEditor.selection.getContent();
			        }				
				
				var shortcode = '[wpsm_colortable ';
				
				if(colortable) {
					shortcode += 'color="'+colortable+'"]';
				}
				
				if ( contenttable !== '' )
					shortcode += contenttable;
				else if	( tablecontent !== '' )
					shortcode += tablecontent;
				else 
					shortcode += '<table><tr><th>Sample heading</th><th>Sample heading</th></tr><tr><td>Sample text</td><td>Sample text</td></tr><tr><td>Sample text</td><td>Sample text</td></tr></table>';

				shortcode += '[/wpsm_colortable]';
		
        window.send_to_editor(shortcode);
		
		// closes Thickbox
		tb_remove();
	});
}); 
</script>
<form action="/" method="get" id="form" name="form" accept-charset="utf-8">	
	<p>
		<label for="colortable"><?php esc_html_e('Color of heading table :', 'rehub-theme') ;?></label>
		<select id="colortable" name="colortable">
			<option value="main-color"><?php esc_html_e('Main theme color', 'rehub-theme') ;?></option>
			<option value="sec-color"><?php esc_html_e('Secondary theme color', 'rehub-theme') ;?></option>			
			<option value="grey"><?php esc_html_e('grey', 'rehub-theme') ;?></option>
			<option value="black"><?php esc_html_e('black', 'rehub-theme') ;?></option>
            <option value="yellow"><?php esc_html_e('yellow', 'rehub-theme') ;?></option>
			<option value="blue"><?php esc_html_e('blue', 'rehub-theme') ;?></option>
			<option value="red"><?php esc_html_e('red', 'rehub-theme') ;?></option>
			<option value="green"><?php esc_html_e('green', 'rehub-theme') ;?></option>
            <option value="orange"><?php esc_html_e('orange', 'rehub-theme') ;?></option>
            <option value="purple"><?php esc_html_e('purple', 'rehub-theme') ;?></option>
		</select>
	</p>
    <p>
		<label for="TableContent"><?php esc_html_e('Content :', 'rehub-theme') ;?></label>
		<textarea id="TableContent" name="TableContent" col="20"></textarea><br />
		<small><?php esc_html_e('Leave blank if you selected text in visual editor', 'rehub-theme') ;?></small>
	</p>
	 <p>
        <label>&nbsp;</label>
        <input type="button" id="submit" class="button" value="<?php esc_html_e('Insert', 'rehub-theme') ;?>" name="submit" />
    </p>
</form>