<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<script data-cfasync="false">

// executes this when the DOM is ready
jQuery(document).ready(function() {
	// handles the click event of the submit button
	jQuery('#submit').click(function(){
			
		var shortcode = '';
		
		var new_price = jQuery('#price-new').val();
		var content = jQuery('#price-content').val();
		
		if( new_price == 'yes' ){
			shortcode += '[wpsm_price_table]<br />';
		}
		
		shortcode += '[wpsm_price_column';
		
		var options = { 
			'size'        : '1/3',
			'featured'    : 'no',
			'name'        : 'Sample Name',
			'price'       : '$99.95',
			'per'         : 'month',
			'button_color'  : 'orange',
			'button_url'  : '#',
			'button_text' : 'Sign Up'
			};
		
		for( var index in options) {
			var value = jQuery('#form').find('#price-' + index).val();
			
			if ( value !== '' )
				shortcode += ' ' + index + '="' + value + '"';
			else
				shortcode += ' ' + index + '="' + options[index] + '"'; 	
		}
		
		shortcode += ']<ul>';
		
		if ( jQuery('#price-content').val() !== '' ){
			jQuery.each(jQuery('#price-content').val().split('\n'), function(index, value) { 
			  shortcode += '<li>' + value +'</li>';
			});
		}else{
			shortcode += '<li>Sample Item #1</li><li>Sample Item #2</li><li>Sample Item #3</li>';
		}
		
		shortcode += '</ul>[/wpsm_price_column]';
		
		if( new_price == 'yes' ){
			shortcode += '<br />[/wpsm_price_table]';
		}
		
        window.send_to_editor(shortcode);
		
		
		// closes Thickbox
		tb_remove();
	});
}); 
</script>
<form action="/" method="get" id="form" name="form" accept-charset="utf-8">
	<p>
		<label><?php esc_html_e('Create new section', 'rehub-theme') ;?></label>
		<select name="price-new" id="price-new" size="1">
            <option value="no" selected="selected"><?php esc_html_e('No', 'rehub-theme') ;?></option>
            <option value="yes"><?php esc_html_e('Yes', 'rehub-theme') ;?></option>
        </select>
	</p>
	<p><label><?php esc_html_e('Column size', 'rehub-theme') ;?></label>
        <select name="price-size" id="price-size" size="1">
            <option value="3" selected="selected">1/3</option>
            <option value="4">1/4</option>
			<option value="5">1/5</option>
			<option value="2">1/2</option>
        </select></small>
    </p>
	
	<p>
		<label><?php esc_html_e('Featured', 'rehub-theme') ;?></label>
		<select name="price-featured" id="price-featured" size="1">
            <option value="no" selected="selected"><?php esc_html_e('No', 'rehub-theme') ;?></option>
            <option value="yes"><?php esc_html_e('Yes', 'rehub-theme') ;?></option>
        </select>
	</p>
	
	<p><label><?php esc_html_e('Name', 'rehub-theme') ;?></label>
        <input type="text" name="price-name" value="" id="price-name" />
    </p>
	
	<p><label><?php esc_html_e('Price', 'rehub-theme') ;?></label>
        <input type="text" name="price-price" value="" id="price-price" style="width:100px" />
		 / <input type="text" name="price-per" value="" id="price-per" style="width:100px" />
		<br />
		<small><?php esc_html_e('Example: $99.95 / month', 'rehub-theme') ;?></small>
    </p>
    	<p>
		<label><?php esc_html_e('Color', 'rehub-theme') ;?></label>
		<select name="price-button_color" id="price-button_color" size="1">
			<option value="red"><?php esc_html_e('Red', 'rehub-theme') ;?></option>
			<option value="orange" selected="selected"><?php esc_html_e('Orange', 'rehub-theme') ;?></option>
			<option value="blue"><?php esc_html_e('Blue', 'rehub-theme') ;?></option>
			<option value="green"><?php esc_html_e('Green', 'rehub-theme') ;?></option>
			<option value="black"><?php esc_html_e('Black', 'rehub-theme') ;?></option>
			<option value="rosy"><?php esc_html_e('Rosy', 'rehub-theme') ;?></option>
			<option value="brown"><?php esc_html_e('Brown', 'rehub-theme') ;?></option>
			<option value="pink"><?php esc_html_e('Pink', 'rehub-theme') ;?></option>
			<option value="purple"><?php esc_html_e('Purple', 'rehub-theme') ;?></option>
			<option value="gold"><?php esc_html_e('Gold', 'rehub-theme') ;?></option>
			<option value="teal"><?php esc_html_e('Teal', 'rehub-theme') ;?></option>
        </select>
	</p>
	
	<p><label><?php esc_html_e('Button URL', 'rehub-theme') ;?></label>
        <input type="text" name="price-button_url" value="" id="price-button_url" />
    </p>
	<p><label><?php esc_html_e('Button text', 'rehub-theme') ;?></label>
        <input type="text" name="price-button_text" value="" id="price-button_text" />
    </p>
	
	<p><label><?php esc_html_e('List of items', 'rehub-theme') ;?></label>
        <textarea name="price-content" id="price-content" rows="6" /></textarea><br /><small><?php esc_html_e('Separated by a new-line (press Enter)', 'rehub-theme') ;?></small>
    </p>
	 <p>
        <label>&nbsp;</label>
        <input type="button" id="submit" class="button" value="<?php esc_html_e('Insert', 'rehub-theme') ;?>" name="submit" />
    </p>

</form>