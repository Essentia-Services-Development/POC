<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<script data-cfasync="false">

// executes this when the DOM is ready
jQuery(document).ready(function() {
	// handles the click event of the submit pos
	jQuery('#submit').click(function(){

		var max = parseInt(jQuery('#form #max').val());
		var color = jQuery('#form #color').val();
		var marktype = jQuery('#form #marktype').val();
		var markcolor = jQuery('#form #markcolor').val();
		var unit = jQuery('#form #unit').val();
		var lines = jQuery('#form #lines').val();

		shortcode = '[wpsm_compare_bar';

		shortcode += ' max="'+max+'"';
		shortcode += ' color="'+color+'"';	
		shortcode += ' marktype="'+marktype+'"';	
		shortcode += ' markcolor="'+markcolor+'"';
		shortcode += ' unit="'+unit+'"';								
		
		shortcode += ' lines="';
		jQuery.each(jQuery('#lines').val().split('\n'), function(index, value) { 
		  shortcode += value +'@@';
		});
		shortcode += '"';
		
		shortcode += ']';		
		
		// inserts the shortcode into the active editor
		window.send_to_editor(shortcode);
		
		
		// closes Thickbox
		tb_remove();
	});
}); 
</script>
<form action="/" method="get" id="form" name="form" accept-charset="utf-8">
    <p>
        <label><?php esc_html_e('Maximum', 'rehub-theme') ;?></label>
        <input type="number" name="max" id="max"><br /><small><?php esc_html_e('Set value which will be equal to 100%', 'rehub-theme') ;?></small>
    </p>
    <p>
    	<label><?php esc_html_e('Color', 'rehub-theme') ;?></label>
    	<input id="color" name="color" type="text" value="" /><small><?php esc_html_e('Set default color or leave empty to leave default color as grey', 'rehub-theme') ;?></small>
    </p>
	<p>
		<label>How to choose highlighted bar</label>
		<select name="marktype" id="marktype" size="1">
            <option value="max" selected="selected"><?php esc_html_e('With maximum value', 'rehub-theme') ;?></option>  	
            <option value="min"><?php esc_html_e('With minimum value', 'rehub-theme') ;?></option>			                   
        </select>
	</p> 
    <p>
    	<label><?php esc_html_e('Highlight Color', 'rehub-theme') ;?></label>
    	<input id="markcolor" name="markcolor" type="text" value="" /><small><?php esc_html_e('Set highlighted color or leave empty to leave default color as orange', 'rehub-theme') ;?></small>
    </p>	
    <p>
    	<label><?php esc_html_e('Unit', 'rehub-theme') ;?></label>
    	<input id="unit" name="unit" type="text" value="" /><small><?php esc_html_e('Set unit, will be added to value', 'rehub-theme') ;?></small>
    </p>          
    <p>
        <label><?php esc_html_e('Set bars (Title::Value::Link', 'rehub-theme') ;?></label>
        <textarea name="lines" id="lines" rows="4"></textarea><br /><small>Set bars, each bar from new line (by Enter).<br /> Example: Samsung A5::3::http://yourlink.com. <br />Link is optional. <br />Value must be without any words, just number!!!! <br />Value must be less than maximum number</small>
    </p>   
	 <p>
        <label>&nbsp;</label>
        <input type="button" id="submit" class="button" value="<?php esc_html_e('Insert', 'rehub-theme') ;?>" name="submit" />
    </p>
</form>