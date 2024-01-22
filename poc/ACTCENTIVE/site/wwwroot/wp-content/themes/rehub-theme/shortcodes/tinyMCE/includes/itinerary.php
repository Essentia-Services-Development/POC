<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<script data-cfasync="false">
jQuery(document).ready(function() {
	jQuery('#submit').click(function(){
		var shortcode = '[wpsm_itinerary]';
		jQuery("input[id^=itinerary-num]").each(function(index) {
			var itinerary_content = jQuery('textarea.itinerary-content:eq('+index+')').val();
			var itinerary_icon = jQuery('input.itinerary-icon:eq('+index+')').val().replace(/\s/g, '');
			var itinerary_color = jQuery('input.itinerary-color:eq('+index+')').val().replace(/\s/g, '');
			shortcode +='<br />[wpsm_itinerary_item icon="'+itinerary_icon+'" color="'+itinerary_color+'"]<br />'+itinerary_content+'<br />[/wpsm_itinerary_item]<br />';
		});
       	shortcode += '[/wpsm_itinerary]';
		window.send_to_editor(shortcode);
		tb_remove();
	});
	jQuery("#add-tab").click(function() {
		jQuery('.shortcode_loop').append('<p><label><?php esc_html_e('Icon', 'rehub-theme'); ?></label><input type="text" name="itinerary-icon" value="" class="itinerary-icon" /></p><p><label><?php esc_html_e('Color', 'rehub-theme'); ?></label><input type="text" name="itinerary-color" value="" class="itinerary-color" /></p><p><label><?php esc_html_e('Content', 'rehub-theme'); ?></label><textarea type="text" name="itinerary-content" value="" class="itinerary-content" col="10"></textarea></p><p style="display:none"><input type="hidden" name="itinerary-num[]" value="" id="itinerary-num[]" /></p>');
	});
}); 
</script>
<form action="/" method="get" id="form" name="form" accept-charset="utf-8">
	<div class="shortcode_loop">
		<p><label><?php esc_html_e('Icon', 'rehub-theme'); ?></label><input type="text" name="itinerary-icon" value="" class="itinerary-icon" /><br /><small><?php printf( '%s %s <a href="http://wpsoul.net/icons/" target="_blank">%s</a>', esc_html__('Set icon class', 'rehub-theme'), esc_html__('Or leave blank.', 'rehub-theme'), esc_html__('More detail...', 'rehub-theme') ); ?></small></p>
		<p><label><?php esc_html_e('Color', 'rehub-theme'); ?></label><input type="text" name="itinerary-color" value="" class="itinerary-color" /><br /><small><?php printf( '%s %s <a href="//www.w3schools.com/colors/colors_picker.asp" target="_blank">%s</a>', esc_html__('Set HEX color like #409cd1', 'rehub-theme'), esc_html__('Or leave blank.', 'rehub-theme'), esc_html__('More detail...', 'rehub-theme') ); ?></small></p>
		<p><label><?php esc_html_e('Content', 'rehub-theme'); ?></label><textarea type="text" name="itinerary-content" value="" class="itinerary-content" col="10"></textarea></p>
		<p style="display:none"><input type="hidden" name="itinerary-num[]" value="" id="itinerary-num[]" /></p>
	</div>
	<p><strong><a style="cursor: pointer;" id="add-tab">+ <?php esc_html_e('Add Item', 'rehub-theme') ;?></a></strong></p>
	<p><label>&nbsp;</label><input type="button" name="submit" value="<?php esc_html_e('Insert', 'rehub-theme') ;?>" class="button" id="submit"></p>	
</form>