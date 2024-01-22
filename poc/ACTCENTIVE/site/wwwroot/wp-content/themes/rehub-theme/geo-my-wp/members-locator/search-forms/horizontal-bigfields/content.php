<?php 
/**
 * Members Locator "horizontal-bigfield" search form template file. 
 * 
 * The information on this file will be displayed as the search forms.
 * 
 * The function pass 1 args for you to use:
 * $gmw  - the form being used ( array )
 * 
 * You could but It is not recomemnded to edit this file directly as your changes will be overridden on the next update of the plugin.
 * Instead you can copy-paste this template ( the "horizontal-bigfield" folder contains this file and the "css" folder ) 
 * into the theme's or child theme's folder of your site and apply your changes from there. 
 * 
 * The template folder will need to be placed under:
 * your-theme's-or-child-theme's-folder/geo-my-wp/members-locator/search-forms/
 * 
 * Once the template folder is in the theme's folder you will be able to choose it when editing the Members locator form.
 * It will show in the "Search results" dropdown menu as "Custom: horizontal-bigfield".
 */
?>
<?php do_action( 'gmw_before_search_form_template', $gmw ); ?>

<div class="gmw-form-wrapper <?php echo esc_attr( $gmw['prefix'] ); ?> gmw-fl-form-wrapper gmw-fl-hor-bigfield-custom-form-wrapper text-center">
	
	<?php do_action( 'gmw_before_search_form', $gmw ); ?>
	
	<form class="standard-form gmw-form" name="gmw_form" action="<?php echo esc_attr( $gmw_form->get_results_page() ); ?>" method="get" data-id="<?php echo absint( $gmw['ID'] ); ?>" data-prefix="<?php echo esc_attr( $gmw['prefix'] ); ?>">
			
		<?php do_action( 'gmw_search_form_start', $gmw ); ?>
		
		<?php do_action( 'gmw_search_form_before_address', $gmw ); ?>
	
		<?php gmw_search_form_xprofile_fields( $gmw ); ?>
		<?php gmw_search_form_address_field( $gmw ); ?>		
		<?php gmw_search_form_locator_button( $gmw ); ?>
				
		<?php do_action( 'gmw_search_form_before_xprofile', $gmw ); ?>
		
		<div class="xfield-trigger-wrapper">
			<div class="xfield-trigger" onclick="jQuery(this).closest('form').find('.gmw-search-form-xprofile-fields').slideToggle();">
				<?php echo (!empty($gmw['labels']['search_form']['show_options'])) ? $gmw['labels']['search_form']['show_options'] : esc_html__('Show options', 'rehub-theme'); ?>
			</div>
		</div>
				            		
		<?php do_action( 'gmw_search_form_before_distance', $gmw ); ?>
		
		<!--distance values -->
		<?php gmw_search_form_radius( $gmw ); ?>
		
		<!--distance units-->
		<?php gmw_search_form_units( $gmw ); ?>	
		
		<?php gmw_search_form_submit_button( $gmw ); ?>
		
		<?php do_action( 'gmw_search_form_end', $gmw ); ?>
		
	</form>
	
	<?php do_action( 'gmw_after_search_form', $gmw ); ?>
	
</div><!--form wrapper -->	

<?php do_action( 'gmw_after_search_form_template', $gmw ); ?>