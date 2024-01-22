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
			'top'      	: '20px',
			'bottom'     : '20px',
			'style'     : 'solid',
			};

		var shortcode = '[wpsm_divider';
		
		for( var index in options) {
			var value = jQuery('#form').find('#divider-' + index).val();
			
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
	<p><label><?php esc_html_e('Style', 'rehub-theme') ;?></label>
       	<select name="divider-style" id="divider-style" size="1">
			<option value="solid" selected="selected"><?php esc_html_e('solid', 'rehub-theme') ;?></option>
			<option value="dotted"><?php esc_html_e('dotted', 'rehub-theme') ;?></option>
			<option value="dashed"><?php esc_html_e('dashed', 'rehub-theme') ;?></option>
			<option value="double"><?php esc_html_e('double', 'rehub-theme') ;?></option>
			<option value="fadeout"><?php esc_html_e('fadeout', 'rehub-theme') ;?></option>
			<option value="fadein"><?php esc_html_e('fadein', 'rehub-theme') ;?></option>
			<option value="transparent"><?php esc_html_e('transparent', 'rehub-theme') ;?></option>
			<option value="clear"><?php esc_html_e('clear floats', 'rehub-theme') ;?></option>				
        </select>
    </p> 	
	<p><label><?php esc_html_e('Margin top (with px)', 'rehub-theme') ;?></label>
        <input type="text" name="divider-top" value="20px" id="divider-top" />
    </p>
	 
    <p>
        <label><?php esc_html_e('Margin bottom (with px)', 'rehub-theme') ;?></label>
        <input type="text" name="divider-bottom" value="20px" id="divider-bottom" />
    </p>    
	 <p>
        <label>&nbsp;</label>
        <input type="button" id="submit" class="button" value="<?php esc_html_e('Insert', 'rehub-theme') ;?>" name="submit" />
    </p>
</form>