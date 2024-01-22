<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<script data-cfasync="false">

// executes this when the DOM is ready
jQuery(document).ready(function() { 

	jQuery('#submit').click(function(){
		var options = { 
			'taxonomy'       : '',
			'child_of'       : '',
			'type'       : 'compactbig',
			'limit'       : '',
			'imageheight'       : '50',
			'classcol' : 'col_wrap_fifth',
			'include' : '',
			'hide_empty' : ''					
			};
			
		var shortcode = '[wpsm_tax_archive';
		
		for( var index in options) {
			var value = jQuery('#form').find('#taxarchive-' + index).val();
			
			if ( value !== '' )
				shortcode += ' ' + index + '="' + value + '"';
			else
				shortcode += ' ' + index + '="' + options[index] + '"'; 	
		}
		if(jQuery('#taxarchive-random').is(":checked")) {
			shortcode += ' random=1';
		}

		if(jQuery('#taxarchive-hide_empty').is(":checked")) {
			shortcode += ' hide_empty=1';
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
        <label><?php esc_html_e('Taxonomy', 'rehub-theme') ;?></label>
        <input type="text" name="taxarchive-taxonomy" value="" id="taxarchive-taxonomy" /><br />
        <small>Set taxonomy. Default is Brand for woocommerce. Taxonomy for woo attribute starts from "pa_"</small>
    </p>
	<p>
        <label><?php esc_html_e('Child of', 'rehub-theme') ;?></label>
        <input type="text" name="taxarchive-child_of" value="" id="taxarchive-child_of" /><br />
        <small>Set ID of parent category if you want to show only child Items</small>        
    </p>     	
	<p>
		<label><?php esc_html_e('Type', 'rehub-theme') ;?></label>
		<select name="taxarchive-type" id="taxarchive-type" size="1">
            <option value="compactbig" selected="selected"><?php esc_html_e('Compact Blocks', 'rehub-theme') ;?></option>	
            <option value="compact"><?php esc_html_e('Compact small Blocks', 'rehub-theme') ;?></option>
            <option value="logo"><?php esc_html_e('Logo', 'rehub-theme') ;?></option>
            <option value="alpha"><?php esc_html_e('Alphabet', 'rehub-theme') ;?></option>  
            <option value="storegrid"><?php esc_html_e('Store Grid', 'rehub-theme') ;?></option> 
            <option value="woocategory"><?php esc_html_e('Woocommerce Category archive', 'rehub-theme') ;?></option>
            <option value="postcategory"><?php esc_html_e('Post category archive', 'rehub-theme') ;?></option>         
        </select>
        <small>Logo works only for Brand, Affiliate Store and woocommerce Category taxonomy. You can add logo when you edit category. If you choose Post category archive, set "category" in Taxonomy field</small>        
	</p>
	<p>
		<label><?php esc_html_e('Columns', 'rehub-theme') ;?></label>
		<select name="taxarchive-classcol" id="taxarchive-classcol" size="1">
            <option value="col_wrap_fifth" selected="selected">5</option>
            <option value="col_wrap_fourth">4</option>
            <option value="col_wrap_three">3</option>
            <option value="col_wrap_two">2</option>  
            <option value="col_wrap_six">6</option> 
            <option value="col_wrap_one">1</option>                                 
        </select>
        <small>Choose this if you want to divide all list in Compact Blocks. This parameter is not working for Logo and Alphabet Type</small>        
	</p>	
	<p>
        <label><?php esc_html_e('Limit (Number)', 'rehub-theme') ;?></label>
        <input type="text" name="taxarchive-limit" value="" id="taxarchive-limit" /><br />
    </p>		

	<p>
        <label><?php esc_html_e('Image height', 'rehub-theme') ;?></label>
        <input type="text" name="taxarchive-imageheight" value="" id="taxarchive-imageheight" /><br />
        <small>use with Logo or Alphabet type. Default is 50</small>        
    </p> 
	<p>
        <label><?php esc_html_e('Include', 'rehub-theme') ;?></label>
        <input type="text" name="taxarchive-include" value="" id="taxarchive-include" /><br />
        <small>Set Ids if you want to show only special taxonomies</small>        
    </p>         
	<p>
		<label><?php esc_html_e('Random order', 'rehub-theme') ;?></label>
        <input id="taxarchive-random" name="taxarchive-random" type="checkbox" class="checks" value="false" /> 
	</p>    
	<p>
		<label><?php esc_html_e('Hide Empty', 'rehub-theme') ;?></label>
        <input id="taxarchive-hide_empty" name="taxarchive-hide_empty" type="checkbox" class="checks" value="false" />
        <small>Will hide all categories which don't have posts</small>        
	</p> 	 
	
	 <p>
        <label>&nbsp;</label>
        <input type="button" id="submit" class="button" value="<?php esc_html_e('Insert', 'rehub-theme') ;?>" name="submit" />
    </p>

</form>