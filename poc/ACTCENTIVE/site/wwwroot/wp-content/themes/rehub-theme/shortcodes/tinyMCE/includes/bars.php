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
			'title'      	: '',
			'percentage'     : '90',
			'color'      	: '#fb7203'
			};

		var shortcode = '[wpsm_bar';
		
		for( var index in options) {
			var value = jQuery('#form').find('#bar-' + index).val();
			
			if ( value !== '' )
				shortcode += ' ' + index + '="' + value + '"';
			else
				shortcode += ' ' + index + '="' + options[index] + '"'; 	
		}
		shortcode += ']';
		
        window.send_to_editor(shortcode);
		
		// closes Thickbox
		tb_remove();
	});
}); 
</script>
<form action="/" method="get" id="form" name="form" accept-charset="utf-8">
	<p><label><?php esc_html_e('Title', 'rehub-theme') ;?></label>
        <input type="text" name="bar-title" value="wordpress" id="bar-title" />
    </p>
	
	<p><label><?php esc_html_e('Style', 'rehub-theme') ;?></label>
       	<select name="bar-color" id="bar-color" size="1">
			<option value="#fb7203" selected="selected"><?php esc_html_e('orange', 'rehub-theme') ;?></option>
			<option value="#00aae9"><?php esc_html_e('blue', 'rehub-theme') ;?></option>
			<option value="#222222"><?php esc_html_e('black', 'rehub-theme') ;?></option>
			<option value="#dd0007"><?php esc_html_e('red', 'rehub-theme') ;?></option>
            <option value="#77bb0f"><?php esc_html_e('green', 'rehub-theme') ;?></option>			
        </select>
    </p>  
    <p>
        <label><?php esc_html_e('Percentage', 'rehub-theme') ;?></label>
        <input type="text" name="bar-percentage" value="" id="bar-percentage" /><br />
        <small><?php esc_html_e('type without %', 'rehub-theme') ;?></small>
    </p>    
	 <p>
        <label>&nbsp;</label>
        <input type="button" id="submit" class="button" value="<?php esc_html_e('Insert', 'rehub-theme') ;?>" name="submit" />
    </p>
</form>