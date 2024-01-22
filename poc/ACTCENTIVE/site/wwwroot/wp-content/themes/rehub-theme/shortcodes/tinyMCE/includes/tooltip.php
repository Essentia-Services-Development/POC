<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<script data-cfasync="false">

// executes this when the DOM is ready
jQuery(document).ready(function() {
	// handles the click event of the submit button
	jQuery('#submit').click(function(){
		// defines the options and their default values
		// again, this is not the most elegant way to do this
		// but well, this gets the job done nonetheless
		var Text = jQuery('#Text').val();
		var gravity = jQuery('#Gravities').val();
		var Content = jQuery('#Content').val();
		if( ! tinyMCE.activeEditor || tinyMCE.activeEditor.isHidden()) {
		 var contenttooltip = jQuery("textarea.wp-editor-area").selection('get');
		}
		else {
        	var contenttooltip = tinyMCE.activeEditor.selection.getContent(); 
        }

		var shortcode = '[wpsm_tooltip ';
				

				if(Text) {
					shortcode += 'text="'+Text+'"';
				}


				if(gravity) {
					shortcode += ' gravity="'+gravity+'"';
				}
                shortcode += ']'
                if ( Content !== '' )
					shortcode += Content;
				else if	( contenttooltip !== '' )
					shortcode += contenttooltip;
				else 
					shortcode += 'Sample Text';


				shortcode += '[/wpsm_tooltip]';
		
        window.send_to_editor(shortcode);
		
		// closes Thickbox
		tb_remove();
	});
}); 
</script>
<form action="/" method="get" id="form" name="form" accept-charset="utf-8">
	
	<p>
		<label for="Text"><?php esc_html_e('Content of tooltip', 'rehub-theme') ;?></label>
		<input id="Text" name="Text" type="Text" value="Sample title" />
	</p>
	<p>
		<label for="Gravities"><?php esc_html_e('Gravities :', 'rehub-theme') ;?></label>
		<select id="Gravities" name="Gravities">
			<option value="nw"><?php esc_html_e('Northwest', 'rehub-theme') ;?></option>
			<option value="n"><?php esc_html_e('North', 'rehub-theme') ;?></option>
			<option value="ne"><?php esc_html_e('Northeast', 'rehub-theme') ;?></option>
			<option value="w"><?php esc_html_e('West', 'rehub-theme') ;?></option>
			<option value="e"><?php esc_html_e('East', 'rehub-theme') ;?></option>
			<option value="sw" selected="selected"><?php esc_html_e('Southwest', 'rehub-theme') ;?></option>
			<option value="s"><?php esc_html_e('South', 'rehub-theme') ;?></option>
			<option value="se"><?php esc_html_e('Southeast', 'rehub-theme') ;?></option>
		</select>
	</p>
	<p>
		<label for="Content"><?php esc_html_e('Content :', 'rehub-theme') ;?> </label>
		<textarea id="Content" name="Content" col="10"></textarea><br />
		<small><?php esc_html_e('Leave blank if you selected text in visual editor', 'rehub-theme') ;?></small>
	</p>
	 <p>
        <label>&nbsp;</label>
        <input type="button" id="submit" class="button" value="<?php esc_html_e('Insert', 'rehub-theme') ;?>" name="submit" />
    </p>
</form>