<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<script data-cfasync="false">
jQuery(document).ready(function() { 
	// handles the click event of the submit box
	jQuery('#submit').click(function(){
				//var offerlinkid = jQuery('#offerlinkid').val();
				var reviewBoxtitle = jQuery('#reviewBoxtitle').val();
				var reviewBoxcontent = jQuery('#reviewBoxcontent').val();
				var reviewBoxcriterias = jQuery('#reviewBoxcriterias').val();
				var reviewBoxcons = jQuery('#reviewBoxcons').val();	
				var reviewBoxpros = jQuery('#reviewBoxpros').val();							
				var reviewBoxscore = jQuery('#reviewBoxscore').val();

				var shortcode = '[wpsm_reviewbox ';												
				if(reviewBoxtitle !== '') {
					shortcode += 'title="'+reviewBoxtitle+'" ';
				}
		        if ( reviewBoxcontent !== '' ){
				   shortcode += 'description="'+reviewBoxcontent+'" ';		
		        }
			   	if(reviewBoxcriterias !== '') {
					shortcode += 'criterias="';
					jQuery.each(reviewBoxcriterias.split('\n'), function(index, value) { 
			  			shortcode += value +';';
			  		});
			  		shortcode += '" ';
				}
			   	if(reviewBoxpros !== '') {
					shortcode += 'pros="';
					jQuery.each(reviewBoxpros.split('\n'), function(index, value) { 
			  			shortcode += value +';';
			  		});
			  		shortcode += '" ';
				}	
			   	if(reviewBoxcons !== '') {
					shortcode += 'cons="';
					jQuery.each(reviewBoxcons.split('\n'), function(index, value) { 
			  			shortcode += value +';';
			  		});
			  		shortcode += '" ';
				}														
			   	if(reviewBoxscore !== '') {
					shortcode += 'score="'+reviewBoxscore+'" ';
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
		
    <p>
		<label for="reviewBoxtitle"><?php esc_html_e('Review heading', 'rehub-theme') ;?></label>
		<input id="reviewBoxtitle" name="reviewBoxtitle" type="text" value="" />
	</p> 				
    <p>
		<label for="reviewBoxcontent"><?php esc_html_e('Review short description', 'rehub-theme') ;?></label>
		<textarea id="reviewBoxcontent" name="reviewBoxcontent" rows="6"></textarea>
	</p>
    <p>
		<label for="reviewBoxcriterias"><?php esc_html_e('Review criterias', 'rehub-theme') ;?></label>
		<textarea id="reviewBoxcriterias" name="reviewBoxcriterias" rows="6"></textarea>
		<small>Only 1-10 points are supported. Divider between criteria and score is ":", set each criteria from next line> Example:</small>
		<small>Design:8</small>	
		<small>Price:9</small>
		<small>Usability:6</small>							
	</p>
	<p>
		<label for="reviewBoxscore"><?php esc_html_e('Review manual score', 'rehub-theme') ;?></label>
		<input id="reviewBoxscore" name="reviewBoxscore" type="number" value="" step="1" min="0" max="10" />
		<small>By default, score is average between score criterias, but you can add own</small><br/>
	</p>
    <p>
		<label for="reviewBoxpros"><?php esc_html_e('Review pros', 'rehub-theme') ;?></label>
		<textarea id="reviewBoxpros" name="reviewBoxpros" rows="6"></textarea>	
		<small>Set each pros from next line</small>			
	</p>
    <p>
		<label for="reviewBoxcons"><?php esc_html_e('Review cons', 'rehub-theme') ;?></label>
		<textarea id="reviewBoxcons" name="reviewBoxcons" rows="6"></textarea>	
		<small>Set each cons from next line</small>			
	</p>			
	 <p>
        <label>&nbsp;</label>
        <input type="button" id="submit" class="button" value="<?php esc_html_e('Insert', 'rehub-theme') ;?>" name="submit" />
    </p>
</form>