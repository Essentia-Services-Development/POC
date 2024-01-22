<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<script data-cfasync="false">

// executes this when the DOM is ready
jQuery(document).ready(function() { 
	// handles the click event of the submit box
	jQuery('#submit').click(function(){
		var options = { 
			'type'       : 'info',
			'float'       : 'none',
			'textalign'       : 'left'
			};
		
					if( ! tinyMCE.activeEditor || tinyMCE.activeEditor.isHidden()) {
					 var contentbox = jQuery("textarea.wp-editor-area").selection('get');
					}
					else {
			        	var contentbox = tinyMCE.activeEditor.selection.getContent();
			        }
		var box_width = jQuery('#box-width').val();		
		var shortcode = '[wpsm_box';
		
		for( var index in options) {
			var value = jQuery('#form').find('#box-' + index).val();
			
			if ( value !== '' )
				shortcode += ' ' + index + '="' + value + '"';
			else
				shortcode += ' ' + index + '="' + options[index] + '"'; 	
		}

		if(box_width !='') {
					shortcode += ' width="' + box_width + '" ';
		}
		
		shortcode += ']<br />';
		
		var box_text = jQuery('#box-text').val();
		if ( box_text !== '' )
			shortcode += box_text;
		else if	( contentbox !== '' )
			shortcode += contentbox;		
		else	
			shortcode += 'Sample content';
						
		shortcode += '<br />[/wpsm_box]';
		
		// inserts the shortcode into the active editor
		window.send_to_editor(shortcode);
		
		
		// closes Thickbox
		tb_remove();
	});
}); 
</script>
<form action="/" method="get" id="form" name="form" accept-charset="utf-8">
	<p>
		<label><?php esc_html_e('Type', 'rehub-theme') ;?></label>
		<select name="box-type" id="box-type" size="1">
            <option value="info" selected="selected"><?php esc_html_e('Info', 'rehub-theme') ;?></option>			
            <option value="download"><?php esc_html_e('Download', 'rehub-theme') ;?></option>
            <option value="error"><?php esc_html_e('Error', 'rehub-theme') ;?></option>
            <option value="warning"><?php esc_html_e('Warning', 'rehub-theme') ;?></option>
            <option value="yellow"><?php esc_html_e('Yellow color box', 'rehub-theme') ;?></option>
            <option value="green"><?php esc_html_e('Green color box', 'rehub-theme') ;?></option>
            <option value="gray"><?php esc_html_e('Gray color box', 'rehub-theme') ;?></option>
            <option value="blue"><?php esc_html_e('Blue color box', 'rehub-theme') ;?></option>
            <option value="red"><?php esc_html_e('Red color box', 'rehub-theme') ;?></option>  
            <option value="dashed_border"><?php esc_html_e('Dashed border box', 'rehub-theme') ;?></option>
            <option value="solid_border"><?php esc_html_e('Solid border box', 'rehub-theme') ;?></option>
            <option value="transparent"><?php esc_html_e('Transparent background box', 'rehub-theme') ;?></option>                       
        </select>
	</p>
	<p>
		<label>Float</label>
		<select name="box-float" id="box-float" size="1">
            <option value="none" selected="selected"><?php esc_html_e('None', 'rehub-theme') ;?></option>  			
            <option value="left"><?php esc_html_e('Left', 'rehub-theme') ;?></option>			
            <option value="right"><?php esc_html_e('Right', 'rehub-theme') ;?></option>                    
        </select>
	</p>
	<p>
		<label>Text-align</label>
		<select name="box-textalign" id="box-textalign" size="1">
            <option value="left" selected="selected"><?php esc_html_e('Left', 'rehub-theme') ;?></option>			
            <option value="right"><?php esc_html_e('Right', 'rehub-theme') ;?></option>
            <option value="center"><?php esc_html_e('Center', 'rehub-theme') ;?></option>   
            <option value="justify"><?php esc_html_e('Justify', 'rehub-theme') ;?></option>                    
        </select>
	</p>
	<p>
        <label><?php esc_html_e('Width (with %)', 'rehub-theme') ;?></label>
        <input type="text" name="box-width" value="" id="box-width" /><br />
        <small><?php esc_html_e('Or live blank for full width', 'rehub-theme') ;?></small>
    </p>		
    <p>
        <label>Text</label>
        <textarea type="text" name="box-text" value="" id="box-text" col="10"></textarea><br />
        <small><?php esc_html_e('Leave blank if you selected text in visual editor', 'rehub-theme') ;?></small>
    </p>
	
	 <p>
        <label>&nbsp;</label>
        <input type="button" id="submit" class="button" value="<?php esc_html_e('Insert', 'rehub-theme') ;?>" name="submit" />
    </p>

</form>