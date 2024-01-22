<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php
/**
 * Plugin Name: News Widget
 */

add_action( 'widgets_init', 'rehub_tabs_load_widget' );

function rehub_tabs_load_widget() {
	register_widget( 'rehub_tabs_widget' );
}

class rehub_tabs_widget extends WP_Widget {

    function __construct() {
		$widget_ops = array( 'classname' => 'tabs mb25', 'description' => esc_html__('A widget that displays 2 tabs (popular, categories, tags, latest comments). Use only in sidebar! ', 'rehub-framework') );
		$control_ops = array( 'width' => 250, 'height' => 350, 'id_base' => 'rehub_latest_tabs_widget' );
        parent::__construct('rehub_latest_tabs_widget', esc_html__('ReHub: Tabs', 'rehub-framework'), $widget_ops, $control_ops  );
    }

	/**
	 * How to display the widget on the screen.
	 */
	function widget( $args, $instance ) {
		extract( $args );

		/* Our variables from the widget settings. */
		$tabs1 = $instance['tabs1'];
		$tabs2 = $instance['tabs2'];
		if( function_exists('icl_t') )  $titlefirst = icl_t( 'Widget title' , 'widget_title_'.$this->id , $instance['titlefirst'] ); else $titlefirst = $instance['titlefirst'] ;
		if( function_exists('icl_t') )  $titlesecond = icl_t( 'Widget title second' , 'widget_title_second'.$this->id , $instance['titlesecond'] ); else $titlesecond = $instance['titlesecond'] ;
		if( !empty($instance['dark']) ) $color = 'dark';
		else $color = '';
		if( empty($instance['basedby']) ) {$basedby = 'comments';}
		else {$basedby = $instance['basedby'];}
		if( empty($instance['basedbysec']) ) {$basedbysec = 'views';}
		else {$basedbysec = $instance['basedbysec'];}		
		
		/* Before widget (defined by themes). */
		echo ''.$before_widget;
		wp_enqueue_script('rhtabs');

		?>
		<?php echo rh_generate_incss('tabs');?>
		<ul class="clearfix tabs-menu">
            <li>
				<?php echo esc_attr($titlefirst) ;?>
            </li>
            <li>
				<?php echo esc_attr($titlesecond) ;?>	
            </li>
       </ul>
    <div class="color_sidebar padd20<?php if ($color == 'dark') :?> dark_sidebar darkbg whitecolor whitecolorinner<?php else:?> border-lightgrey<?php endif ;?>">
    	<style scoped>
			.widget.tabs > ul { border-bottom: 1px solid #000000; }
			.widget.tabs > ul > li { float: left; margin: 0 2% 0 0; padding: 10px 0px; width: 49%; display: block;}
			.widget.tabs > ul > li:last-child { margin-right: 0px; float: right; }
		</style>
		<?php if ($color == 'dark') :?>
			<style scoped>
				/* style for darksidebar */
				.dark_sidebar .tabs-item > div { border-color: #515151;}
				.dark_sidebar .tabs-item .detail .post-meta a.cat{color:#fff;}
				.dark_sidebar .lastcomm-item { border-bottom: 1px solid #515151; color: #fff }
				.dark_sidebar .tabs-item .detail .post-meta a.comm_meta { color: #ccc !important; }
			</style>
		<?php endif ;?>
       <div class="tabs-item clearfix">
   			<?php if ($tabs1 == 'popular') :?>
            	<?php rehub_most_popular_widget_block($basedby);?>
            <?php elseif ($tabs1 == 'comments'):?>
            	<?php rehub_latest_comment_widget_block();?>
            <?php elseif ($tabs1 == 'category'):?>	
            	<?php rehub_category_widget_block();?>
            <?php else : ?>            
            	<div class="tagcloud"><?php wp_tag_cloud(); ?></div> 	            
            <?php endif ;?>	      	
       	</div>
       <div class="tabs-item rhhidden">
          	<?php if ($tabs2 == 'popular') :?>
            	<?php rehub_most_popular_widget_block($basedbysec);?>
            <?php elseif ($tabs2 == 'comments'):?>
            	<?php rehub_latest_comment_widget_block();?>
            <?php elseif ($tabs2 == 'category'):?>	
            	<?php rehub_category_widget_block();?>
            <?php else : ?>            
            	<div class="tagcloud"><?php wp_tag_cloud(); ?></div>	            
            <?php endif ;?>	    	
       	</div>
   </div>
			
		<?php
	
		/* After widget (defined by themes). */
		echo ''.$after_widget;
	}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['tabs1'] = $new_instance['tabs1'];
		$instance['tabs2'] = $new_instance['tabs2'];
		$instance['basedby'] = $new_instance['basedby'];
		$instance['basedbysec'] = $new_instance['basedbysec'];
		$instance['dark'] = (!empty($new_instance['dark'])) ? strip_tags( $new_instance['dark'] ) : '';
		$instance['titlefirst'] = strip_tags($new_instance['titlefirst']);
		$instance['titlesecond'] = strip_tags($new_instance['titlesecond']);

		if (function_exists('icl_register_string')) {
			icl_register_string( 'Widget title' , 'widget_title_'.$this->id, $new_instance['titlefirst'] );
			icl_register_string( 'Widget title second' , 'widget_title_second'.$this->id, $new_instance['titlesecond'] );
		}		

		return $instance;
	}


	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'tabs1' => 'popular', 'tabs2' => 'comments', 'basedby' => 'comments', 'basedbysec' => 'views', 'titlefirst' => esc_html__('Popular', 'rehub-framework'),  'titlesecond' => esc_html__('Comments', 'rehub-framework'), 'dark' =>'');
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>


		<script type="text/javascript">
			jQuery(document).ready(function($) {
				var tabsrehfirst = $('#<?php echo ''.$this->get_field_id("tabs1"); ?>');
				var tabsrehsec = $('#<?php echo ''.$this->get_field_id("tabs2"); ?>');
			    if( tabsrehfirst.val()==="popular"){
			    	tabsrehfirst.parent().parent().find(".tabbaserehubfirst, .tabsortrehubfirst").show()
			    }
			    else{
			    	tabsrehfirst.parent().parent().find(".tabbaserehubfirst, .tabsortrehubfirst").hide()
			    }
			    if( tabsrehsec.val()==="popular"){
			    	tabsrehsec.parent().parent().find(".tabbaserehubsecond, .tabsortrehubsec").show()
			    }
			    else{
			    	tabsrehsec.parent().parent().find(".tabbaserehubsecond, .tabsortrehubsec").hide()
			    }			    				
				tabsrehfirst.on('change',function(){
				    if( $(this).val()==="popular"){
				    $(this).parent().parent().find(".tabbaserehubfirst, .tabsortrehubfirst").show()
				    }
				    else{
				    $(this).parent().parent().find(".tabbaserehubfirst, .tabsortrehubfirst").hide()
				    }
				});
				$('#<?php echo ''.$this->get_field_id("tabs2"); ?>').on('change',function(){
				    if( $(this).val()==="popular"){
				    $(this).parent().parent().find(".tabbaserehubsecond, .tabsortrehubsec").show()
				    }
				    else{
				    $(this).parent().parent().find(".tabbaserehubsecond, .tabsortrehubsec").hide()
				    }
				});				
			});
		</script>		
		<div>
		<p><em style="color:red;"><?php esc_html_e('Use this widget only in sidebar area!', 'rehub-framework');?></em></p>
				
		<p>
		<label for="<?php echo ''.$this->get_field_id('tabs1'); ?>"><?php esc_html_e('Content for 1 tab', 'rehub-framework');?></label> 
		<select id="<?php echo ''.$this->get_field_id('tabs1'); ?>" name="<?php echo ''.$this->get_field_name('tabs1'); ?>" style="width:100%;">
			<option value='popular' <?php if ( 'popular' == $instance['tabs1'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('Popular posts', 'rehub-framework');?></option>
			<option value='comments' <?php if ( 'comments' == $instance['tabs1'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('Latest comments', 'rehub-framework');?></option>
			<option value='category' <?php if ( 'category' == $instance['tabs1'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('Category list', 'rehub-framework');?></option>
			<option value='tags' <?php if ( 'tags' == $instance['tabs1'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('Tags cloud', 'rehub-framework');?></option>
		</select>
		</p>

		<p>
		<label for="<?php echo ''.$this->get_field_id('tabs2'); ?>"><?php esc_html_e('Content for 2 tab', 'rehub-framework');?></label> 
		<select id="<?php echo ''.$this->get_field_id('tabs2'); ?>" name="<?php echo ''.$this->get_field_name('tabs2'); ?>" style="width:100%;">
			<option value='popular' <?php if ( 'popular' == $instance['tabs2'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('Popular posts', 'rehub-framework');?></option>
			<option value='comments' <?php if ( 'comments' == $instance['tabs2'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('Latest comments', 'rehub-framework');?></option>
			<option value='category' <?php if ( 'category' == $instance['tabs2'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('Category list', 'rehub-framework');?></option>
			<option value='tags' <?php if ( 'tags' == $instance['tabs2'] ) : echo 'selected="selected"'; endif; ?>><?php esc_html_e('Tags cloud', 'rehub-framework');?></option>
		</select>
		</p>
		<p>
			<label for="<?php echo ''.$this->get_field_id( 'titlefirst' ); ?>"><?php esc_html_e('Enter title for first tab:', 'rehub-framework'); ?></label>
			<input  type="text" class="widefat" id="<?php echo ''.$this->get_field_id( 'titlefirst' ); ?>" name="<?php echo ''.$this->get_field_name( 'titlefirst' ); ?>" value="<?php echo ''.$instance['titlefirst']; ?>"  />
			<span><em><?php esc_html_e('Note, maximum 15 symbols!', 'rehub-framework');?></em></span>
		</p>
		<p>
			<label for="<?php echo ''.$this->get_field_id( 'titlesecond' ); ?>"><?php esc_html_e('Enter title for second tab', 'rehub-framework'); ?></label>
			<input  type="text" class="widefat" id="<?php echo ''.$this->get_field_id( 'titlesecond' ); ?>" name="<?php echo ''.$this->get_field_name( 'titlesecond' ); ?>" value="<?php echo ''.$instance['titlesecond']; ?>"  />
		</p>				

		<p class="tabbaserehubfirst">
		<label for="<?php echo ''.$this->get_field_id('basedby'); ?>"><?php _e('Popular posts for tab 1 based on:', 'rehub_framework');?></label> 
		<select id="<?php echo ''.$this->get_field_id('basedby'); ?>" name="<?php echo ''.$this->get_field_name('basedby'); ?>" style="width:100%;">
			<option value='hot' <?php if ( 'hot' == $instance['basedby'] ) : echo 'selected="selected"'; endif; ?>><?php _e('Post hot count', 'rehub_framework');?></option>
			<option value='comments' <?php if ( 'comments' == $instance['basedby'] ) : echo 'selected="selected"'; endif; ?>><?php _e('Comments', 'rehub_framework');?></option>
			<option value='views' <?php if ( 'views' == $instance['basedby'] ) : echo 'selected="selected"'; endif; ?>><?php _e('Post views', 'rehub_framework');?></option>
		</select>
		<span><em><?php _e('Note, post views may not work if you use cache plugins!', 'rehub_framework');?></em></span>		
		</p>				
		<p class="tabbaserehubsecond">
		<label for="<?php echo ''.$this->get_field_id('basedbysec'); ?>"><?php _e('Popular posts for tab 2 based on:', 'rehub_framework');?></label> 
		<select id="<?php echo ''.$this->get_field_id('basedbysec'); ?>" name="<?php echo ''.$this->get_field_name('basedbysec'); ?>" style="width:100%;">
			<option value='hot' <?php if ( 'hot' == $instance['basedbysec'] ) : echo 'selected="selected"'; endif; ?>><?php _e('Post hot count', 'rehub_framework');?></option>			
			<option value='comments' <?php if ( 'comments' == $instance['basedbysec'] ) : echo 'selected="selected"'; endif; ?>><?php _e('Comments', 'rehub_framework');?></option>
			<option value='views' <?php if ( 'views' == $instance['basedbysec'] ) : echo 'selected="selected"'; endif; ?>><?php _e('Post views', 'rehub_framework');?></option>
		</select>
		<span><em><?php _e('Note, post views may not work if you use cache plugins!', 'rehub_framework');?></em></span>		
		</p>						
		<p>
			<label for="<?php echo ''.$this->get_field_id( 'dark' ); ?>"><?php esc_html_e('Dark Skin ?', 'rehub-framework'); ?></label>
			<input id="<?php echo ''.$this->get_field_id( 'dark' ); ?>" name="<?php echo ''.$this->get_field_name( 'dark' ); ?>" value="true" <?php if( $instance['dark'] ) echo 'checked="checked"'; ?> type="checkbox" />
		</p>		
		</div>


	<?php
	}


}

?>