<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<script data-cfasync="false">
jQuery(document).ready(function() { 
	// handles the click event of the submit box
	jQuery('#submit').click(function(){
				//var offerlinkid = jQuery('#offerlinkid').val();
				var offerBoxprice = jQuery('#offerBoxprice').val();
				var offerBoxcoupon = jQuery('#offerBoxcoupon').val();
				var offerBoxcoupondate = jQuery('#offerBoxcoupondate').val();
				var offerBoxpriceold = jQuery('#offerBoxpriceold').val();
				var offerBoxbtnlink = jQuery('#offerBoxbtnlink').val();
				var offerBoxbtntext = jQuery('#offerBoxbtntext').val();
				var offerBoxtitle = jQuery('#offerBoxtitle').val();
				var offerBoximg = jQuery('#offerBoximg').val();
				var offerBoximglogo = jQuery('#offerBoximglogo').val();
				var offerBoxcontent = jQuery('#offerBoxcontent').val();
				var offerBoxcouponmask = jQuery('#offerBoxcouponmask');
				if( ! tinyMCE.activeEditor || tinyMCE.activeEditor.isHidden()) {
					 var contentofferbox = jQuery("textarea.wp-editor-area").selection('get');
					}
				else {
			        var contentofferbox = tinyMCE.activeEditor.selection.getContent();
			        }

				var shortcode = '[wpsm_offerbox ';
				if(offerBoxbtnlink !== '') {
					shortcode += 'button_link="'+offerBoxbtnlink+'" ';
				}
				if(offerBoxbtntext !== '') {
					shortcode += 'button_text="'+offerBoxbtntext+'" ';
				}													
				if(offerBoxprice !== '') {
					shortcode += 'price="'+offerBoxprice+'" ';
				}
				if(offerBoxpriceold !== '') {
					shortcode += 'price_old="'+offerBoxpriceold+'" ';
				}
				if(offerBoxcoupon !== '') {
					shortcode += 'offer_coupon="'+offerBoxcoupon+'" ';
				}	
				if(offerBoxcoupondate !== '') {
					shortcode += 'offer_coupon_date="'+offerBoxcoupondate+'" ';
				}												
				if(offerBoxtitle !== '') {
					shortcode += 'title="'+offerBoxtitle+'" ';
				}
		        if ( offerBoxcontent !== '' )
				   shortcode += 'description="'+offerBoxcontent+'" ';
		        else if	( contentofferbox !== '' )
			       shortcode += 'description="'+contentofferbox+'" ';		

			   	if(offerBoximg !== '') {
					shortcode += 'thumb="'+offerBoximg+'" ';
				}
			   	if(offerBoximglogo !== '') {
					shortcode += 'logo_thumb="'+offerBoximglogo+'" ';
				}	
				if(offerBoxcouponmask.is(":checked")) {
					shortcode += ' offer_coupon_mask="1"';
				}							
				
				shortcode += ']';							

		// inserts the shortcode into the active editor
		window.send_to_editor(shortcode);
		
		
		// closes Thickbox
		tb_remove();
				
			});
			
});

</script>

<form action="/" method="get" id="form" name="form" accept-charset="utf-8">
	
    <p class="half_left">
		<label for="offerBoxprice"><?php esc_html_e('Offer sale price', 'rehub-theme') ;?></label>
		<input id="offerBoxprice" name="offerBoxprice" type="text" value="" />
	</p>
    <p class="half_left second_half">
		<label for="offerBoxpriceold"><?php esc_html_e('Offer old price', 'rehub-theme') ;?></label>
		<input id="offerBoxpriceold" name="offerBoxpriceold" type="text" value="" />
	</p>	
	<div class="clear"></div>
    <p>
		<label for="offerBoxbtnlink"><?php esc_html_e('Button link :', 'rehub-theme') ;?></label>
		<input id="offerBoxbtnlink" name="offerBoxbtnlink" type="text" value="" />
	</p> 
    
    <p>
		<label for="offerBoxbtntext"><?php esc_html_e('Button text :', 'rehub-theme') ;?></label>
		<input id="offerBoxbtntext" name="offerBoxbtntext" type="text" value="" />
	</p>   

	<p>
		<label for="offerBoxtitle"><?php esc_html_e('Title of offer :', 'rehub-theme') ;?></label>
		<input id="offerBoxtitle" name="offerBoxtitle" type="text" value="" />
	</p>
    
    <p>
		<label for="offerBoxcontent"><?php esc_html_e('OfferBox description:', 'rehub-theme') ;?></label>
		<textarea id="offerBoxcontent" name="offerBoxcontent" type="text" col="10" value=""></textarea>
	</p>
	<p>
		<label for="offerBoximg"><?php esc_html_e('Offer thumbnail url', 'rehub-theme') ;?></label>
		<input id="offerBoximg" name="offerBoximg" type="text" value="" />
		<small><?php esc_html_e('You can leave this field blank', 'rehub-theme') ;?></small>
	</p>
	<p>
		<label for="offerBoximglogo"><?php esc_html_e('Brand logo url', 'rehub-theme') ;?></label>
		<input id="offerBoximglogo" name="offerBoximglogo" type="text" value="" />
		<small><?php esc_html_e('You can leave this field blank', 'rehub-theme') ;?></small>
	</p>
    <p>
		<label for="offerBoxcoupon"><?php esc_html_e('Set coupon code', 'rehub-theme') ;?></label>
		<input id="offerBoxcoupon" name="offerBoxcoupon" type="text" value="" />
	</p>
    <p>
		<label for="offerBoxcoupondate"><?php esc_html_e('Coupon End Date', 'rehub-theme') ;?></label>
		<input id="offerBoxcoupondate" name="offerBoxcoupondate" type="text" value="" />
		<small><?php esc_html_e('Format date-month-year. Example, 20-12-2015', 'rehub-theme') ;?></small>
	</p>
	<p>
		<label><?php esc_html_e('Mask coupon code?', 'rehub-theme') ;?></label>
		<input id="offerBoxcouponmask" name="offerBoxcouponmask" type="checkbox" class="checks" value="false" />
	</p>				

	 <p>
        <label>&nbsp;</label>
        <input type="button" id="submit" class="button" value="<?php esc_html_e('Insert', 'rehub-theme') ;?>" name="submit" />
    </p>
</form>