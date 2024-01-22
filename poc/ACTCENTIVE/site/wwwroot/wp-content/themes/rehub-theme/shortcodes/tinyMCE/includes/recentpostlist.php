<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>

<script data-cfasync="false">

// executes this when the DOM is ready
jQuery(document).ready(function() { 
	// handles the click event of the submit button
	jQuery('#submit').click(function(){
		// defines the options and their default values
		// again, this is not the most elegant way to do this
		// but well, this gets the job done nonetheless
		var options = { 
			'cat'      	: '',
			'show'     : '3',
			};
		var shortcode = '[wpsm_recent_posts_list';
		
		for( var index in options) {
			var value = jQuery('#form').find('#recentpostlist-' + index).val();
			
			if ( value !== '' )
				shortcode += ' ' + index + '="' + value + '"';
			else
				shortcode += ' ' + index + '="' + options[index] + '"'; 	
		}
		shortcode += ']';
		
        window.send_to_editor(shortcode);
		
		// closes Thickbox
		tb_remove();
	});
}); 
</script>
<form action="/" method="get" id="form" name="form" accept-charset="utf-8">
	<p><label><?php esc_html_e('Cat ID', 'rehub-theme') ;?></label>
        <input type="text" name="recentpostlist-cat" value="" id="recentpostlist-cat" />
    </p>
	
	<p><label><?php esc_html_e('Number of posts to show', 'rehub-theme') ;?></label>
        <input type="text" name="recentpostlist-show" value="" id="recentpostlist-show" />
    </p>
	 <p>
        <label>&nbsp;</label>
        <input type="button" id="submit" class="button" value="<?php esc_html_e('Insert', 'rehub-theme') ;?>" name="submit" />
    </p>
</form>