<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<script data-cfasync="false">

// executes this when the DOM is ready
jQuery(document).ready(function() {
	// handles the click event of the submit list
	jQuery('#submit').click(function(){
		var idsvalue = jQuery('#slider-ids').val();
		var shortcode = '[wpsm_quick_slider';

		if ( idsvalue !== '' ) {
			shortcode += ' ids="' + idsvalue + '"';
        }
		
		shortcode += ']';
		
		// inserts the shortcode into the active editor
		window.send_to_editor(shortcode);
		
		
		// closes Thickbox
		tb_remove();
	});
}); 
</script>
<form action="/" method="get" id="form" name="form" accept-charset="utf-8">
    <p><label><?php esc_html_e('Images ids', 'rehub-theme') ;?></label>
        <input type="text" name="slider-ids" value="" id="slider-ids" /><br />
        <small><?php esc_html_e('Insert ids of images with commas.', 'rehub-theme') ;?>. <a href="http://rehub.wpsoul.com/documentation/docs.html#51" target="_blank">Tip - How to get images ids very fast</a></small>
    </p>      
	 <p>
        <label>&nbsp;</label>
        <input type="button" id="submit" class="button" value="<?php esc_html_e('Insert', 'rehub-theme') ;?>" name="submit" />
    </p>
</form>