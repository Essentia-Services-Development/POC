<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<script data-cfasync="false">

// executes this when the DOM is ready
jQuery(document).ready(function() { 
	// handles the click event of the submit button
	jQuery('#submit').click(function(){

		var options = { 
			'btn_text' : '',
			'btn_url' : '',
			'btn_price' : '',
			'meta_btn_url' : '',
			'meta_btn_price' : '',			
		};	
		var timer = jQuery('#timer');

		var shortcode = '[rehub_affbtn';	
		for( var index in options) {
			var value = jQuery('#form').find('#button-' + index).val();
			if ( value !== '' )
				shortcode += ' ' + index + '="' + value + '"'; 	
		}
		if(timer.is(":checked")) {
			shortcode += ' timer="1"';
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
<div class="affbtn">
    <p>
        <label><?php esc_html_e('Text on button', 'rehub-theme') ;?></label>
        <input type="text" name="button-btn_text" value="" id="button-btn_text" /><br />
        <small><?php esc_html_e('Or live blank for default', 'rehub-theme') ;?></small>
    </p>
	<div class="clear"></div>
    <p>
        <label><?php esc_html_e('Button url', 'rehub-theme') ;?></label>
        <input type="text" name="button-btn_url" value="" id="button-btn_url" /><br />
    </p>
	<div class="clear"></div>
    <p>
        <label><?php esc_html_e('Or set name of meta field where you store url', 'rehub-theme') ;?></label>
        <input type="text" name="button-meta_btn_url" value="" id="button-meta_btn_url" /><br />
        <small><?php esc_html_e('In this case, leave blank previous field', 'rehub-theme') ;?></small>
    </p>
	<div class="clear"></div>		
    <p>
        <label><?php esc_html_e('Price on Button', 'rehub-theme') ;?></label>
        <input type="text" name="button-btn_price" value="" id="button-btn_price" /><br />
    </p>
	<div class="clear"></div>
    <p>
        <label><?php esc_html_e('Or set name of meta field where you store price', 'rehub-theme') ;?></label>
        <input type="text" name="button-meta_btn_price" value="" id="button-meta_btn_price" /><br />
        <small><?php esc_html_e('In this case, leave blank previous field', 'rehub-theme') ;?></small>
    </p>
	<div class="clear"></div>		

	<p>
		<label><?php esc_html_e('Enable timer?', 'rehub-theme') ;?></label>
        <input id="timer" name="timer" type="checkbox" class="checks" value="false" />
        <small><?php esc_html_e('You can leave all fields blank and enable this field if you want to show button from Main offer of post and timer based on expiration date', 'rehub-theme') ;?></small>        
	</p>
	<div class="clear"></div>
</div>
	 <p>
        <label>&nbsp;</label>
        <input type="button" name="submit" value="<?php esc_html_e('Insert', 'rehub-theme') ;?>" class="button" id="submit">
    </p>	
</form>