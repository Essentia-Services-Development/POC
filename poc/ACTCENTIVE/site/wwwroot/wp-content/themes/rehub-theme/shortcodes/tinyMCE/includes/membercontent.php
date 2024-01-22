<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<script data-cfasync="false">

// executes this when the DOM is ready
jQuery(document).ready(function() { 
	// handles the click event of the submit button
	jQuery('#submit').click(function(){
		// defines the options and their default values
		// again, this is not the most elegant way to do this
		// but well, this gets the job done nonetheless
		var member_text = jQuery('#member-text').val();
		var guest_text = jQuery('#member-guest').val();
		if( ! tinyMCE.activeEditor || tinyMCE.activeEditor.isHidden()) {
		 var selection_member = jQuery("textarea.wp-editor-area").selection('get');
		}
		else {
        	var selection_member = tinyMCE.activeEditor.selection.getContent();  
        }

		var shortcode = '[wpsm_member ';

		if(guest_text !=='') {
					shortcode += 'guest_text="'+guest_text+'"';
		}
        shortcode += ']';

		if ( member_text !== '' )
			shortcode += member_text;
		else if	( selection_member !== '' )
			shortcode += selection_member;
		else 
			shortcode += 'Sample Text';

		shortcode += '[/wpsm_member]';
		
        window.send_to_editor(shortcode);
		
		// closes Thickbox
		tb_remove();
	});
}); 
</script>
<form action="/" method="get" id="form" name="form" accept-charset="utf-8">
	<p>
        <label><?php esc_html_e('Text for guests', 'rehub-theme') ;?></label>
        <input type="text" name="member-guest" value="" id="member-guest" /><br />
        <small><?php esc_html_e('Or live blank for default text', 'rehub-theme') ;?></small>
    </p>
    <p>
        <label><?php esc_html_e('Text for members', 'rehub-theme') ;?></label>
        <textarea type="text" name="member-text" value="" id="member-text" rows="8"></textarea><br />
        <small><?php esc_html_e('Leave blank if you selected text in visual editor', 'rehub-theme') ;?></small>
    </p>     
	 <p>
        <label>&nbsp;</label>
        <input type="button" id="submit" class="button" value="<?php esc_html_e('Insert', 'rehub-theme') ;?>" name="submit" />
    </p>
</form>