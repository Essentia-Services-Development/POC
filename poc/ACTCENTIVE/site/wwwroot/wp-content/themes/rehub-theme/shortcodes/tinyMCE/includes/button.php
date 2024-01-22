<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<script data-cfasync="false">

// executes this when the DOM is ready
jQuery(document).ready(function() {
	// handles the click event of the submit button
	jQuery('#submit').click(function(){

		var options = { 
			'color'      : 'orange',
			'size'       : 'small',
			'link'     : '#',
			'icon'     : '',
			'class'	: '',
			};
		var button_text = jQuery('#button-text').val();	
		var button_class = jQuery('#button-class').val();
		var button_rel = jQuery('#button-rel');
		var button_target = jQuery('#button-target');
		var border_radius = jQuery('#button-border_radius').val();
		if( ! tinyMCE.activeEditor || tinyMCE.activeEditor.isHidden()) {
		 var contenthighlight = jQuery("textarea.wp-editor-area").selection('get');
		}
		else {
        	var contenthighlight = tinyMCE.activeEditor.selection.getContent();	
        }

		var shortcode = '[wpsm_button';
		
		for( var index in options) {
			var value = jQuery('#form').find('#button-' + index).val();
			
			if ( value !== '' )
				shortcode += ' ' + index + '="' + value + '"';
			else
				shortcode += ' ' + index + '="' + options[index] + '"'; 	
		}
		if(border_radius !='') {
					shortcode += ' border_radius="' + border_radius + '"';
		}
		if(button_target.is(":checked")) {
					shortcode += ' target="_blank"';
		}
		if(button_rel.is(":checked")) {
					shortcode += ' rel="nofollow sponsored"';
		}
		
		shortcode += ']';
		
		
		if ( button_text !== '' )
			shortcode += button_text;
		else if	( contenthighlight !== '' )
			shortcode += contenthighlight;
		else 
			shortcode += 'Button';

		shortcode += '[/wpsm_button]';
        
		
		// inserts the shortcode into the active editor
		window.send_to_editor(shortcode);
		
		
		// closes Thickbox
		tb_remove();
	});

}); 
</script>
<form action="/" method="get" id="form" name="form" accept-charset="utf-8">
<div class="tabss">
	<p>
		<label>Color</label>
		<select name="button-color" id="button-color" size="1">
			<option value="main"><?php esc_html_e('Main Theme Color', 'rehub-theme') ;?></option>
			<option value="secondary"><?php esc_html_e('Secondary Theme Color', 'rehub-theme') ;?></option>
			<option value="btncolor" selected="selected"><?php esc_html_e('Main Color of Buttons', 'rehub-theme') ;?></option>
			<option value="red"><?php esc_html_e('Red', 'rehub-theme') ;?></option>
			<option value="green"><?php esc_html_e('Green', 'rehub-theme') ;?></option>
			<option value="black"><?php esc_html_e('Black', 'rehub-theme') ;?></option>
			<option value="gold"><?php esc_html_e('Gold', 'rehub-theme') ;?></option>
			<option value="white"><?php esc_html_e('White', 'rehub-theme') ;?></option>
        </select>
        <small><?php esc_html_e('You can set theme colors in Theme option - appearance or via Customizer', 'rehub-theme') ;?></small>
	</p>
    <p>
		<label>Button Size :</label>
		<select id="button-size" name="button-size">
			<option value="small"><?php esc_html_e('Small', 'rehub-theme') ;?></option>
			<option value="medium"><?php esc_html_e('Medium', 'rehub-theme') ;?></option>
			<option value="big"><?php esc_html_e('Big', 'rehub-theme') ;?></option>
		</select>
	</p>
	<p>
		<label><?php esc_html_e('Button Link :', 'rehub-theme') ;?></label>
		<input id="button-link" name="button-link" type="text" value="http://" />
	</p>
	<p  class="half_left">
		<label><?php esc_html_e('Border radius (with px) :', 'rehub-theme') ;?></label>
		<input id="button-border_radius" name="button-border_radius" type="text" value="" />
	</p>
	<p class="half_left second_half">
		<label>Icon</label>
        <select name="button-icon" id="button-icon" size="1">
            <option value="none" selected="selected"><?php esc_html_e('None', 'rehub-theme') ;?></option>
            <option value="download"><?php esc_html_e('Download', 'rehub-theme') ;?></option>
            <option value="check-circle"><?php esc_html_e('Tick', 'rehub-theme') ;?></option>
            <option value="save"><?php esc_html_e('Save', 'rehub-theme') ;?></option>
            <option value="heart"><?php esc_html_e('Heart', 'rehub-theme') ;?></option>
            <option value="star"><?php esc_html_e('Star', 'rehub-theme') ;?></option>
            <option value="map-marker"><?php esc_html_e('Map marker', 'rehub-theme') ;?></option>
            <option value="thumbs-up"><?php esc_html_e('Thumbs Up', 'rehub-theme') ;?></option>
            <option value="thumbs-down"><?php esc_html_e('Thumbs Down', 'rehub-theme') ;?></option>
            <option value="phone"><?php esc_html_e('Phone', 'rehub-theme') ;?></option>
            <option value="link"><?php esc_html_e('Link', 'rehub-theme') ;?></option>
            <option value="paperclip"><?php esc_html_e('Paper clip', 'rehub-theme') ;?></option>
            <option value="key"><?php esc_html_e('Key', 'rehub-theme') ;?></option>
        </select>
	</p>
	<div class="clear"></div>
	<p class="half_left">
		<label><?php esc_html_e('Open in a new window :', 'rehub-theme') ;?></label>
        <input id="button-target" name="button-target" type="checkbox" class="checks" value="false" />
	</p>
	<p class="half_left second_half">
		<label><?php esc_html_e('Add rel=nofollow :', 'rehub-theme') ;?></label>
        <input id="button-rel" name="button-rel" type="checkbox" class="checks" value="false" />
	</p>
	<div class="clear"></div>
	<p>
		<label><?php esc_html_e('Additional class :', 'rehub-theme') ;?></label>
		<input id="button-class" name="button-class" type="text" value="" />
	</p>	
    <p>
        <label>Text</label>
        <input type="text" name="button-text" value="" id="button-text" /><br />
        <small><?php esc_html_e('Or live blank if you selected a text in visual editor', 'rehub-theme') ;?></small>
    </p>
</div>
	 <p>
        <label>&nbsp;</label>
        <input type="button" name="submit" value="<?php esc_html_e('Insert', 'rehub-theme') ;?>" class="button" id="submit">
    </p>	
</form>