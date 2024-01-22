<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
	<?php if(rehub_option('rehub_ads_infooter') != '') : ?><div class="rh-container mediad_footer mt20 mb20"><div class="clearfix"></div><div class="mediad megatop_mediad floatnone text-center flowhidden"><?php echo do_shortcode(rehub_option('rehub_ads_infooter')); ?></div><div class="clearfix"></div></div><?php endif; ?>
	<?php if ( is_active_sidebar( 'footercustom' ) ) : ?>
		<div id="footercustomarea">	
			<?php dynamic_sidebar( 'footercustom' ); ?>
		</div>
	<?php endif; ?>	
	<?php $footer_template = rehub_option('footer_template');?>	
	 <?php if ( (! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'footer' )) && !is_numeric($footer_template) ) :?>
		<?php 

			$footer_style = (rehub_option('footer_style') == '1') ? ' white_style' : ' dark_style';
			$footer_bottom = (rehub_option('footer_style_bottom') == '1') ? 'white_style' : 'dark_style';    
		?>
		<div class="footer-bottom<?php echo ''.$footer_style;?>">
			<?php if($footer_style==' dark_style'):?>
				<?php echo rh_generate_incss('footerdark');?>
			<?php else:?>
				<?php echo rh_generate_incss('footerwhite');?>
			<?php endif;?>
			<div class="rh-container clearfix">
				<?php if(rehub_option('rehub_footer_widgets')) : ?>
					<div class="rh-flex-eq-height col_wrap_three mb0">
						<div class="footer_widget mobileblockdisplay pt25 col_item mb0">
							<?php if ( is_active_sidebar( 'footerfirst' ) ) : ?>
								<?php dynamic_sidebar( 'footerfirst' ); ?>
							<?php else : ?>
								<p><?php esc_html_e('No widgets added. You can disable footer widget area in theme options - footer options', 'rehub-theme'); ?></p>
							<?php endif; ?> 
						</div>
						<div class="footer_widget mobileblockdisplay disablemobilepadding pt25 col_item mb0">
							<?php if ( is_active_sidebar( 'footersecond' ) ) : ?>
								<?php dynamic_sidebar( 'footersecond' ); ?>
							<?php endif; ?> 
						</div>
						<div class="footer_widget mobileblockdisplay pt25 col_item last mb0">
							<?php if ( is_active_sidebar( 'footerthird' ) ) : ?>
								<?php dynamic_sidebar( 'footerthird' ); ?>
							<?php endif; ?> 
						</div>
					</div>
				<?php endif; ?>					
			</div>	
		</div>
		<?php if(rehub_option('rehub_footer_text')) : ?>
		<footer id='theme_footer' class="pt20 pb20 <?php echo ''.$footer_bottom;?>">
			<?php if($footer_bottom=='dark_style'):?>
				<?php echo rh_generate_incss('footerbottomdark');?>
			<?php else:?>
				<?php echo rh_generate_incss('footerbottomwhite');?>
			<?php endif;?>
			<div class="rh-container clearfix">
				<div class="footer_most_bottom mobilecenterdisplay mobilepadding">
					<div class="f_text font80">
						<span class="f_text_span"><?php echo do_shortcode(rehub_option('rehub_footer_text')); ?></span>
						<?php if(rehub_option('rehub_footer_logo')) : ?><div class="floatright ml15 mr15 mobilecenterdisplay disablefloatmobile"><img src="<?php echo esc_url(rehub_option('rehub_footer_logo')); ?>" alt="<?php bloginfo( 'name' ); ?>" /></div><?php endif; ?>	
					</div>		
				</div>
			</div>
		</footer>
		<?php endif; ?>
	<?php endif; ?>
	<?php if(is_numeric($footer_template)):?>
		<div class="footer_clean_style post clearfix mb0">                      
			<?php echo rh_wp_reusable_render(array('id' => $footer_template));?>                  
		</div>
	<?php endif;?>
	<!-- FOOTER -->
</div><!-- Outer End -->
<span class="rehub_scroll" id="topcontrol" data-scrollto="#top_ankor"><i class="rhicon rhi-chevron-up"></i></span>
<?php wp_footer(); ?>
</body>
</html>