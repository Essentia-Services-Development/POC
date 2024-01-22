<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<script data-cfasync="false">
jQuery(document).ready(function() {
	// handles the click event of the submit box
	jQuery('#submit').click(function(){

				var promoBoxback = jQuery('#promoBoxback').val();
				var promoBoxborder = jQuery('#promoBoxborder');
				var promoBoxbordersize = jQuery('#promoBoxbordersize').val();
				var promoBoxbordercolor = jQuery('#promoBoxbordercolor').val();
				var promoBoxhighlightcheck = jQuery('#promoBoxhighlightcheck');
				var promoBoxhighlightcolor = jQuery('#promoBoxhighlightcolor').val();
				var promoBoxhighlightpos = jQuery('#promoBoxhighlightpos').val();
				var promoBoxbtn = jQuery('#promoBoxbtn');
				var promoBoxbtnlink = jQuery('#promoBoxbtnlink').val();
				var promoBoxbtntext = jQuery('#promoBoxbtntext').val();
				var promoBoxtitle = jQuery('#promoBoxtitle').val();
				var promoBoxcontent = jQuery('#promoBoxcontent').val();
				if( ! tinyMCE.activeEditor || tinyMCE.activeEditor.isHidden()) {
					 var contentpromobox = jQuery("textarea.wp-editor-area").selection('get');
					}
				else {
			        var contentpromobox = tinyMCE.activeEditor.selection.getContent();
			        }

				var shortcode = '[wpsm_promobox ';
                shortcode += 'background="'+promoBoxback+'" ';
				
				if(promoBoxborder.is(":checked")) {
					shortcode += 'border_size="'+promoBoxbordersize+'" ';
					shortcode += 'border_color="'+promoBoxbordercolor+'" ';
				}
				
				if(promoBoxhighlightcheck.is(":checked")) {
					shortcode += 'highligh_color="'+promoBoxhighlightcolor+'" ';
					shortcode += 'highlight_position="'+promoBoxhighlightpos+'" ';
				}				
				
				if(promoBoxbtn.is(":checked")) {
					shortcode += 'button_link="'+promoBoxbtnlink+'" ';
					shortcode += 'button_text="'+promoBoxbtntext+'" ';
				}
				
				if(promoBoxtitle !== '') {
					shortcode += 'title="'+promoBoxtitle+'" ';
				}

		        if ( promoBoxcontent !== '' )
				   shortcode += 'description="'+promoBoxcontent+'" ';
		        else if	( contentpromobox !== '' )
			       shortcode += 'description="'+contentpromobox+'" ';		
		        else	
			       shortcode += 'description="Sample content"';
				
				shortcode += ']';							

		// inserts the shortcode into the active editor
		window.send_to_editor(shortcode);
		
		
		// closes Thickbox
		tb_remove();
				
			});

		jQuery(".bordercheck").css("display","none");

	    jQuery("#promoBoxborder").click(function(){
               // If checked
		       if (jQuery("#promoBoxborder").is(":checked"))
		       {
			     //show the hidden div
			     jQuery(".bordercheck").show("slow");
		       }
		       else
		       {
			     //otherwise, hide it
			     jQuery(".bordercheck").hide("slow");
		       }
	    });
			
		jQuery(".highlightcheck").css("display","none");

	    jQuery("#promoBoxhighlightcheck").click(function(){
               // If checked
		       if (jQuery("#promoBoxhighlightcheck").is(":checked"))
		       {
			     //show the hidden div
			     jQuery(".highlightcheck").show("slow");
		       }
		       else
		       {
			     //otherwise, hide it
			     jQuery(".highlightcheck").hide("slow");
		       }
	    });
			
		jQuery(".btncheck").css("display","none");

	    jQuery("#promoBoxbtn").click(function(){
               // If checked
		       if (jQuery("#promoBoxbtn").is(":checked"))
		       {
			     //show the hidden div
			     jQuery(".btncheck").show("slow");
		       }
		       else
		       {
			     //otherwise, hide it
			     jQuery(".btncheck").hide("slow");
		       }
	    });
			
});

</script>

<form action="/" method="get" id="form" name="form" accept-charset="utf-8">
	<p>
		<label for="promoBoxback"><?php esc_html_e('Background-color :', 'rehub-theme') ;?></label>
		<select id="promoBoxback" name="promoBoxback">
			<option value="#f8f8f8"><?php esc_html_e('Grey', 'rehub-theme') ;?></option>			
			<option value="#ffffff"><?php esc_html_e('White', 'rehub-theme') ;?></option>
		</select>
	</p>
    
    <p>
		<label for="promoBoxborder"><?php esc_html_e('Show border?', 'rehub-theme') ;?></label>
		<input id="promoBoxborder" name="promoBoxborder" type="checkbox" class="checks" value="false" />
	</p>
    
    <p class="bordercheck half_left">
		<label for="promoBoxbordersize"><?php esc_html_e('Border size :', 'rehub-theme') ;?></label>
		<select id="promoBoxbordersize" name="promoBoxbordersize">
			<option value="1px">1px</option>			
			<option value="2px">2px</option>
            <option value="3px">3px</option>
            <option value="4px">4px</option>
            <option value="5px">5px</option>
		</select>
	</p>
    
    <p class="bordercheck half_left second_half">
		<label for="promoBoxbordercolor"><?php esc_html_e('Border color :', 'rehub-theme') ;?></label>
        <select id="promoBoxbordercolor" name="promoBoxbordercolor">
			<option value="#dddddd"><?php esc_html_e('grey', 'rehub-theme') ;?></option>			
			<option value="#fb7203"><?php esc_html_e('orange', 'rehub-theme') ;?></option>
            <option value="#000000"><?php esc_html_e('black', 'rehub-theme') ;?></option>
		</select>
	</p>
	<div class="clear"></div>
    
    <p>
		<label for="promoBoxhighlightcheck"><?php esc_html_e('Show highlight border?', 'rehub-theme') ;?> </label>
		<input id="promoBoxhighlightcheck" name="promoBoxhighlightcheck" type="checkbox" class="checks" value="true" />
	</p>
    
    <p class="highlightcheck half_left">
		<label for="promoBoxhighlightcolor"><?php esc_html_e('Highlight color :', 'rehub-theme') ;?></label>
        <select id="promoBoxhighlightcolor" name="promoBoxhighlightcolor">
            <option value="#fb7203"><?php esc_html_e('orange', 'rehub-theme') ;?></option>			
            <option value="#dddddd"><?php esc_html_e('grey', 'rehub-theme') ;?></option>			
            <option value="#000000"><?php esc_html_e('black', 'rehub-theme') ;?></option>
		</select>
	</p>
    
    <p class="highlightcheck half_left second_half">
		<label for="promoBoxhighlightpos"><?php esc_html_e('Highlight position :', 'rehub-theme') ;?></label>
        <select id="promoBoxhighlightpos" name="promoBoxhighlightpos">
            <option value="left"><?php esc_html_e('left', 'rehub-theme') ;?></option>			
            <option value="top"><?php esc_html_e('top', 'rehub-theme') ;?></option>			
            <option value="right"><?php esc_html_e('right', 'rehub-theme') ;?></option>
            <option value="bottom"><?php esc_html_e('bottom', 'rehub-theme') ;?></option>
		</select>
	</p>
    <div class="clear"></div>
   <p>
		<label for="promoBoxbtn"><?php esc_html_e('Show button?', 'rehub-theme') ;?></label>
		<input id="promoBoxbtn" name="promoBoxbtn" type="checkbox" class="checks" value="false" />
	</p>

    <p class="btncheck">
		<label for="promoBoxbtnlink"><?php esc_html_e('Button link :', 'rehub-theme') ;?></label>
		<input id="promoBoxbtnlink" name="promoBoxbtnlink" type="text" value="" />
	</p> 
    
    <p class="btncheck">
		<label for="promoBoxbtntext"><?php esc_html_e('Button text :', 'rehub-theme') ;?></label>
		<input id="promoBoxbtntext" name="promoBoxbtntext" type="text" value="Purchase Now" />
	</p>   

	<p>
		<label for="promoBoxtitle"><?php esc_html_e('Title of box :', 'rehub-theme') ;?></label>
		<input id="promoBoxtitle" name="promoBoxtitle" type="text" value="" />
	</p>
    
    	<p>
		<label for="promoBoxcontent"><?php esc_html_e('Box content :', 'rehub-theme') ;?></label>
		<textarea id="promoBoxcontent" name="promoBoxcontent" type="text" col="10" value=""></textarea>
	</p>
	 <p>
        <label>&nbsp;</label>
        <input type="button" id="submit" class="button" value="<?php esc_html_e('Insert', 'rehub-theme') ;?>" name="submit" />
    </p>
</form>