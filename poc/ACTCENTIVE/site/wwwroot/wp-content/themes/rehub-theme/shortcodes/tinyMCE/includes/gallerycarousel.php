<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<script data-cfasync="false">

// executes this when the DOM is ready
jQuery(document).ready(function() {
	// handles the click event of the submit button
	jQuery('#submit').click(function(){
        var idsvalue = jQuery('#minigallery-ids').val();
        var titlevalue = jQuery('#minigallery-title').val();
        var prettyvalue = jQuery('#minigallery-pretty');

		var shortcode = '[wpsm_minigallery';

			shortcode += ' ids="' + idsvalue + '"';

        if ( titlevalue !== '' ) {
			shortcode += ' title="' + titlevalue + '"';
        }        
        
        if(prettyvalue.is(":checked")) {
                    shortcode += ' prettyphoto="true"';
        }
		shortcode += ']';

		
		// inserts the shortcode into the active editor
		window.send_to_editor(shortcode);
		
		
		// closes Thickbox
		tinyMCEPopup.close();
	});
}); 
</script>
<form action="/" method="get" id="form" name="form" accept-charset="utf-8">
    <p><label><?php esc_html_e('Title', 'rehub-theme') ;?></label>
        <input type="text" name="minigallery-title" value="" id="minigallery-title" />
    </p> 
    <p><label><?php esc_html_e('Images ids', 'rehub-theme') ;?></label>
        <input type="text" name="minigallery-ids" value="" id="minigallery-ids" /><br />
        <small><?php esc_html_e('Insert ids of images with commas.', 'rehub-theme') ;?>. <a href="http://rehub.wpsoul.com/documentation/docs.html#51" target="_blank">Tip - How to get images ids very fast</a></small>
    </p> 
    <p>
        <label><?php esc_html_e('Enable prettyphoto?', 'rehub-theme') ;?></label>
        <input id="minigallery-pretty" name="minigallery-pretty" type="checkbox" class="checks" value="false" />
    </p>          
	 <p>
        <label>&nbsp;</label>
        <input type="button" id="submit" class="button" value="<?php esc_html_e('Insert', 'rehub-theme') ;?>" name="submit" />
    </p>
</form>