<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<script data-cfasync="false">
jQuery(document).ready(function() { 
	// handles the click event of the submit box
	jQuery('#submit').click(function(){
				//var offerlinkid = jQuery('#offerlinkid').val();
				var offerid = jQuery('#offerid').val();
				var offertype = jQuery('#offertype').val();

				if(offertype == 'ceoffer'){
					var shortcode = '[wpsm_bigoffer post_id="'+offerid+'" offset="" limit=""] ';
				}	

				else if(offertype == 'cemerchant'){
					var shortcode = '[content-egg-block template=custom/all_merchant_widget_group post_id="'+offerid+'"] ';
				}

				else if(offertype == 'cewidget'){
					var shortcode = '[content-egg-block template=custom/all_logolist_widget post_id="'+offerid+'"] ';
				}	

				else if(offertype == 'cegrid'){
					var shortcode = '[content-egg-block template=custom/all_offers_grid post_id="'+offerid+'"] ';
				}

				else if(offertype == 'celist'){
					var shortcode = '[content-egg-block template=custom/all_offers_list post_id="'+offerid+'"] ';
				}				

				else if(offertype == 'celistlogo'){
					var shortcode = '[content-egg-block template=custom/all_offers_logo post_id="'+offerid+'"] ';
				}

				else if(offertype == 'celistdef'){
					var shortcode = '[content-egg-block template=offers_list post_id="'+offerid+'"] ';
				}				

				else if(offertype == 'celistdeflogo'){
					var shortcode = '[content-egg-block template=offers_logo post_id="'+offerid+'"] ';
				}				

				else if(offertype == 'cestat'){
					var shortcode = '[content-egg-block template=price_statistics post_id="'+offerid+'"] ';
				}	

				else if(offertype == 'cehistory'){
					var shortcode = '[content-egg-block template=custom/all_pricehistory_full post_id="'+offerid+'"] ';
				}	

				else if(offertype == 'cealert'){
					var shortcode = '[content-egg-block template=custom/all_pricealert_full post_id="'+offerid+'"] ';
				}																																

		// inserts the shortcode into the active editor
		window.send_to_editor(shortcode);
		
		
		// closes Thickbox
		tb_remove();
				
			});
			
});

</script>

<form action="/" method="get" id="form" name="form" accept-charset="utf-8">
	
	<p>
	<label for="offerid"><?php esc_html_e('Type post name', 'rehub-theme') ;?></label>
	<input id="offerid" name="offerid" type="text" value="" />
	<small>You can choose several for reviews list</small>
	</p>
    
	<p>
		<label><?php esc_html_e('Type', 'rehub-theme') ;?></label>
		<select name="offertype" id="offertype" size="1">
            <option value="ceoffer" selected="selected"><?php esc_html_e('Post box with comparison widget', 'rehub-theme') ;?></option>
            <option value="cemerchant"><?php esc_html_e('Content Egg merchants widget', 'rehub-theme') ;?></option>
            <option value="cewidget"><?php esc_html_e('Content Egg price widget', 'rehub-theme') ;?></option>
            <option value="cegrid"><?php esc_html_e('Content Egg grid', 'rehub-theme') ;?></option>
            <option value="celist"><?php esc_html_e('Content Egg list with offer images', 'rehub-theme') ;?></option>
            <option value="celistlogo"><?php esc_html_e('Content Egg list with logo images', 'rehub-theme') ;?></option>
            <option value="celistdef"><?php esc_html_e('Content Egg list with offer images (default style)', 'rehub-theme') ;?></option>
            <option value="celistdeflogo"><?php esc_html_e('Content Egg list with logo images (default style)', 'rehub-theme') ;?></option>
            <option value="cestat"><?php esc_html_e('Content Egg Price statistic', 'rehub-theme') ;?></option>
            <option value="cehistory"><?php esc_html_e('Content Egg price history', 'rehub-theme') ;?></option>
            <option value="cealert"><?php esc_html_e('Content Egg price alert', 'rehub-theme') ;?></option>                                                            
        </select>
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
	jQuery("#offerid").tokenInput("$path_script", { 
		minChars: 3,
		preventDuplicates: true,
		theme: "rehub",
		tokenLimit: 1,
		onSend: function(params) {
			params.data.posttype = 'any';
			params.data.postnum = 5;
		}
	});
});
</script>
END;
?>