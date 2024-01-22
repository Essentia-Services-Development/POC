<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<script data-cfasync="false">

// executes this when the DOM is ready
jQuery(document).ready(function() { 
	// handles the click event of the submit button
	jQuery('#submit').click(function(){
		// defines the options and their default values
		// again, this is not the most elegant way to do this
		// but well, this gets the job done nonetheless
        var ColumnType = jQuery('#ColumnType').val();
				var Columnposition = jQuery('#Columnposition');
					if( ! tinyMCE.activeEditor || tinyMCE.activeEditor.isHidden()) {
					 var contentcolumn = jQuery("textarea.wp-editor-area").selection('get');
					}
					else {
			        	var contentcolumn = tinyMCE.activeEditor.selection.getContent();  
			        }				
				var ColumnContent = jQuery('#ColumnContent').val();
				var shortcode = '[wpsm_column ';
		
				if(ColumnType) {
					shortcode += 'size="'+ColumnType+'"';
				}
				if(Columnposition.is(":checked")) {
					shortcode += ' position="last"';
				}

				shortcode += ']<br />'
                
                if ( ColumnContent !== '' )
					shortcode += ColumnContent;
				else if	( contentcolumn !== '' )
					shortcode += contentcolumn;
				else 
					shortcode += 'Sample Text';



				shortcode += '<br />[/wpsm_column]'
		
        window.send_to_editor(shortcode);
		
		// closes Thickbox
		tb_remove();
	});
}); 
</script>
<form action="/" method="get" id="form" name="form" accept-charset="utf-8">
	
	<p>
		<label for="ColumnType"><?php esc_html_e('Type of column :', 'rehub-theme') ;?></label>
		<select id="ColumnType" name="ColumnType">
			<option value="one-half"><?php esc_html_e('One half', 'rehub-theme') ;?></option>
            <option value="one-third"><?php esc_html_e('One third', 'rehub-theme') ;?></option>
            <option value="two-third"><?php esc_html_e('Two third', 'rehub-theme') ;?></option>
            <option value="one-fourth"><?php esc_html_e('One fourth', 'rehub-theme') ;?></option>
            <option value="three-fourth"><?php esc_html_e('Three fourth', 'rehub-theme') ;?></option>
            <option value="one-fifth"><?php esc_html_e('One fifth', 'rehub-theme') ;?></option>
            <option value="two-fifth"><?php esc_html_e('Two fifth', 'rehub-theme') ;?></option>
            <option value="three-fifth"><?php esc_html_e('Three fifth', 'rehub-theme') ;?></option>
            <option value="four-fifth"><?php esc_html_e('Four fifth', 'rehub-theme') ;?></option>
            <option value="one-sixth"><?php esc_html_e('One sixth', 'rehub-theme') ;?></option>
            <option value="five-sixth"><?php esc_html_e('Five sixth', 'rehub-theme') ;?></option>
		</select>
	</p>

	<p>
		<label for="Columnposition"><?php esc_html_e('Check if column is last:', 'rehub-theme') ;?> </label>
		<input id="Columnposition" name="Columnposition" type="checkbox" class="checks" value="false" />
	</p>
	<p>
		<label for="ColumnContent"><?php esc_html_e('Content :', 'rehub-theme') ;?> </label>
		<textarea id="ColumnContent" name="ColumnContent" col="20"></textarea><br />
		<small><?php esc_html_e('Leave blank if you selected text in visual editor', 'rehub-theme') ;?></small>
	</p>
	 <p>
        <label>&nbsp;</label>
        <input type="button" id="submit" class="button" value="<?php esc_html_e('Insert', 'rehub-theme') ;?>" name="submit" />
    </p>
</form>