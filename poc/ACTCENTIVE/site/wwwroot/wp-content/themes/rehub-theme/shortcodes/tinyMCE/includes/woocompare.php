<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<script data-cfasync="false">
// executes this when the DOM is ready
jQuery(document).ready(function() {
	// handles the click event of the submit button
	jQuery('#submit').click(function(){
        var idvalue = jQuery('#woocompare-ids').val();       
        var notitle = jQuery('#notitle');
        var logo = jQuery('#logo').val();
		var shortcode = '[wpsm_woocompare';

		if (idvalue !='') {
			shortcode += ' ids="' + idvalue + '"';
		}
		if(notitle.is(":checked")) {
			shortcode += ' notitle="1"';
		}

		shortcode += ' logo="' + logo + '"';				

		shortcode += ']';

		// inserts the shortcode into the active editor
		window.send_to_editor(shortcode);		
		
		// closes Thickbox
		tinyMCEPopup.close();
	});
}); 
</script>
<form action="/" method="get" id="form" name="form" accept-charset="utf-8">
    <p><label><?php esc_html_e('Add products', 'rehub-theme') ;?></label>
        <input type="text" name="woocompare-ids" value="" id="woocompare-ids" /><br />
		<small></small>
    </p> 
	<p><label><?php esc_html_e('Logo', 'rehub-theme') ;?></label>
       	<select name="logo" id="logo" size="1">
			<option value="vendor" selected="selected"><?php esc_html_e('Vendor', 'rehub-theme') ;?></option>
			<option value="product"><?php esc_html_e('product', 'rehub-theme') ;?></option>
			<option value="brand"><?php esc_html_e('brand', 'rehub-theme') ;?></option>			
        </select>
    </p>     
	<p>
		<label><?php esc_html_e('Disable title of products?', 'rehub-theme') ;?></label>

        <input id="notitle" name="notitle" type="checkbox" class="checks" value="false" />
	</p>        
	<p>
        <label>&nbsp;</label>
        <input type="button" id="submit" class="button" value="<?php esc_html_e('Insert', 'rehub-theme') ;?>" name="submit" />
    </p>
</form>
<?php
$path_script = get_template_directory_uri() . '/jsonids/json-ids.php';
print <<<END
<script data-cfasync="false">
jQuery(document).ready(function () {
	jQuery("#woocompare-ids").tokenInput("$path_script", { 
		minChars: 3,
		preventDuplicates: true,
		theme: "rehub",
		onSend: function(params) {
			params.data.posttype = 'product';
			params.data.postnum = 6;
		}
	});
});
</script>
END;
?>