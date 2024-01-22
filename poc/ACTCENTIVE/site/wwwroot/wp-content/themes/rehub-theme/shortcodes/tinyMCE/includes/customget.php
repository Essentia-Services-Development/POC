<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<script data-cfasync="false">

// executes this when the DOM is ready
jQuery(document).ready(function() { 

	jQuery('#submit').click(function(){
		var options = { 
			'post_id'       : '',
			'field'       : '',
			'type'       : 'custom',
			'icon'       : '',
			'label'       : '',
			'posttext'       : '',						
			};
			
		var shortcode = '[wpsm_custom_meta';
		
		for( var index in options) {
			var value = jQuery('#form').find('#customget-' + index).val();
			
			if ( value !== '' )
				shortcode += ' ' + index + '="' + value + '"';
			else
				shortcode += ' ' + index + '="' + options[index] + '"'; 	
		}
		if(jQuery('#customget-showempty').is(":checked")) {
			shortcode += ' showempty=1';
		}
		
		shortcode += ']<br />';
		
		
		// inserts the shortcode into the active editor
		window.send_to_editor(shortcode);
		
		
		// closes Thickbox
		tb_remove();
	});
}); 
</script>
<form action="/" method="get" id="form" name="form" accept-charset="utf-8">
	<p>
        <label><?php esc_html_e('Post ID', 'rehub-theme') ;?></label>
        <input type="text" name="customget-post_id" value="" id="customget-post_id" /><br />
        <small><?php esc_html_e('Leave Blank to get value from current post', 'rehub-theme') ;?></small>
    </p>	
	<p>
		<label><?php esc_html_e('Type', 'rehub-theme') ;?></label>
		<select name="customget-type" id="customget-type" size="1">
            <option value="custom" selected="selected"><?php esc_html_e('Custom field', 'rehub-theme') ;?></option>	
            <option value="attribute"><?php esc_html_e('Woocommerce Attribute', 'rehub-theme') ;?></option>
            <option value="attributelink"><?php esc_html_e('Woocommerce Attribute with Link', 'rehub-theme') ;?></option>
        </select>
	</p>
	<p>
        <label><?php esc_html_e('Field Key', 'rehub-theme') ;?></label>
        <input type="text" name="customget-field" value="" id="customget-field" /><br />
        <small>Required. Set custom field key or attribute slug which you want to get. Woocommerce attribute can start from pa_. For example, if you have "mobilesize" attribute key, place here "pa_mobilesize"</small>
    </p>
	<p>
        <label><?php esc_html_e('Icon', 'rehub-theme') ;?></label>
        <input type="text" name="customget-icon" value="" id="customget-icon" /><br />
        <small>Classes for icons from FontAwesome site. Example: "rhicon rhi-gift". If you want to add more margin from right side of icon, add also class "mr5". All classes must be added with space between classes</small>
    </p>    	
	<p>
        <label><?php esc_html_e('Label', 'rehub-theme') ;?></label>
        <input type="text" name="customget-label" value="" id="customget-label" /><br />
    </p>
	<p>
        <label><?php esc_html_e('Text after value', 'rehub-theme') ;?></label>
        <input type="text" name="customget-posttext" value="" id="customget-posttext" /><br />
    </p>   
	<p>
		<label><?php esc_html_e('Show Empty', 'rehub-theme') ;?></label>
        <input id="customget-showempty" name="customget-showempty" type="checkbox" class="checks" value="false" />
        <small>Show value as "-" if value is empty</small>        
	</p>     
	
	 <p>
        <label>&nbsp;</label>
        <input type="button" id="submit" class="button" value="<?php esc_html_e('Insert', 'rehub-theme') ;?>" name="submit" />
    </p>

</form>