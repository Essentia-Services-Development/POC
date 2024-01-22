<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<script data-cfasync="false">
// executes this when the DOM is ready
jQuery(document).ready(function() {
	// handles the click event of the submit button
	jQuery('#submit').click(function(){
		var options = { 
			'title' : '',
			'link' : '',
			'description' : '',
			'image' : '',			
		};	
		var button_contain = jQuery('#button-bg_contain');
		var shortcode = '[wpsm_cartbox';	
		for( var index in options) {
			var value = jQuery('#form').find('#button-' + index).val();
			if ( value !== '' )
				shortcode += ' ' + index + '="' + value + '"'; 	
		}
		if(button_contain.is(":checked")) {
					shortcode += ' bg_contain="1"';
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
<div>
    <p>
        <label><?php esc_html_e('Title', 'rehub-theme') ;?></label>
        <input type="text" name="button-title" value="" id="button-title" /><br />
    </p>
	<div class="clear"></div>
    <p>
        <label><?php esc_html_e('Link url', 'rehub-theme') ;?></label>
        <input type="text" name="button-link" value="" id="button-link" /><br />
        <small><?php esc_html_e('Will be used in title and image', 'rehub-theme') ;?></small>
    </p>
	<div class="clear"></div>
    <p>
        <label><?php esc_html_e('Description', 'rehub-theme') ;?></label>
        <input type="text" name="button-description" value="" id="button-description" /><br />
    </p>
    <p>	<label><?php esc_html_e('Make background contain?', 'rehub-theme') ;?></label>
        <input id="button-bg_contain" name="button-bg_contain" type="checkbox" class="checks" value="false" /></p>
    <div class="clear"></div>
	<div class="clear"></div>		
    <p>
        <label><?php esc_html_e('Image url', 'rehub-theme') ;?></label>
        <input type="text" name="button-image" value="" id="button-image" /><br />
    </p>
</div>
	 <p>
        <label>&nbsp;</label>
        <input type="button" name="submit" value="<?php esc_html_e('Insert', 'rehub-theme') ;?>" class="button" id="submit">
    </p>	
</form>