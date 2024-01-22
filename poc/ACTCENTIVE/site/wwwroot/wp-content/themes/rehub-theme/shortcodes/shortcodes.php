<?php

require_once ( get_template_directory() . '/shortcodes/tinyMCE/tinyMCE.php'); 

//////////////////////////////////////////////////////////////////
// Buttons
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_shortcode_button') ) {
function wpsm_shortcode_button( $atts, $content = null ) {
        $atts = shortcode_atts(
			array(
				'color' => 'btncolor',
				'size' => 'medium',
				'icon' => '',
				'link' => '',				
				'target' => '',
				'border_radius' => '',
				'class' => '',
				'rel' => '',
				'author_id'=> ''
			), $atts);
    $icon_show = (!empty($atts['icon'])) ? '<i class="rhicon rhi-'.$atts['icon'].'"></i>' : ''; 
    $class_show = (!empty($atts['class'])) ? ' '.$atts['class'].'' : '';
    $link = (!empty($atts['link'])) ? $atts['link'] : '';    
    $border_show = (!empty($atts['border_radius'])) ? ' style="border-radius:'.$atts['border_radius'].'"' : '';
    if($atts['color'] == 'main'){
    	$themeclass = ' rehub-main-color-bg rehub-main-color-border';
    }
    elseif($atts['color'] == 'secondary'){
    	$themeclass = ' rehub-sec-color-bg rehub-sec-color-border';
    }  
    elseif($atts['color'] == 'btncolor'){
    	$themeclass = ' rehub_btn_color';
    }      
    else{
    	$themeclass = '';
    } 
    if($link && $link == 'buddypress' && class_exists( 'BuddyPress' )){
    	if ( bp_is_active( 'messages' )){
			if(empty($atts['author_id'])){
				global $post;
				$author_id=$post->post_author;
			}else{
				$author_id=(int)$atts['author_id'];
			}
    		$link = (is_user_logged_in()) ? wp_nonce_url( bp_loggedin_user_domain() . bp_get_messages_slug() . '/compose/?r=' . bp_core_get_username($author_id) .'&ref='. urlencode(get_permalink())) : '#';
			$class_show = (!is_user_logged_in() && rehub_option('userlogin_enable') == '1') ? $class_show.' act-rehub-login-popup' : $class_show;    		
    	}
    }
	$out = '<a href="'.esc_url($link).'"';
    if ($atts['target'] !='') :
    	$out .=' target="'.$atts['target'].'"';
    endif;
    if ($atts['rel'] !='') :
    	$out .=' rel="'.$atts['rel'].'"';
    endif;    
    $out .=''.$border_show.' class="wpsm-button '.$atts['color'].' '.$atts['size'].''.$class_show.$themeclass.'">'.$icon_show.'' .do_shortcode($content). '</a>';
    return $out;
}
}

//////////////////////////////////////////////////////////////////
// Column
//////////////////////////////////////////////////////////////////

if( !function_exists('wpsm_column_shortcode') ) {
	function wpsm_column_shortcode( $atts, $content = null ){
		extract( shortcode_atts( array(
		'size' => 'one-half',
		'position' =>'first'
		), $atts ) );
		$out = '';
		// Remove all instances of "<p>&nbsp;</p><br>" to avoid extra lines.
		$content = do_shortcode($content);
		$content = preg_replace( '%<p>&nbsp;\s*</p>%', '', $content ); 
		$Old     = array( '<br />', '<br>' );
		$New     = array( '','' );
		$content = str_replace( $Old, $New, $content );	
		$prefix = '</p>';
		if (substr($content, 0, strlen($prefix)) == $prefix) {
			$content = substr($content, strlen($prefix));
		} 
		$content = str_replace( '<p>&nbsp;</p>', '', $content );		  	  
		$out .= '<div class="wpsm-' . $size . ' wpsm-column-'.$position.'">' . $content . '</div>';
		if($position == 'last') {
			$out .= '<div class="clearfix"></div>';
		}
		return $out;	  
	}
}


//////////////////////////////////////////////////////////////////
// Highlight
//////////////////////////////////////////////////////////////////

if ( !function_exists( 'wpsm_highlight_shortcode' ) ) {
	function wpsm_highlight_shortcode( $atts, $content = null ) {
		extract( shortcode_atts( array(
			'color' => 'yellow',
		  ),
		  $atts ) );
		  return '<span class="wpsm-highlight wpsm-highlight-'. $color .'">' . do_shortcode( $content ) . '</span>';
	
	}
}

//////////////////////////////////////////////////////////////////
// Color table
//////////////////////////////////////////////////////////////////
if ( !function_exists( 'wpsm_colortable_shortcode' ) ) {
	function wpsm_colortable_shortcode( $atts, $content = null ) {
		extract( shortcode_atts( array(
			'color' => 'black',
		  	),
		  	$atts ) );
		  	// Remove all instances of "<p>&nbsp;</p><br>" to avoid extra lines.
		  	$content = do_shortcode($content);
		  	$content = preg_replace( '%<p>&nbsp;\s*</p>%', '', $content ); 
			$content = preg_replace('/^(?:<br\s*\/?>\s*)+/', '', $content);	  
			if($color == 'orange'){
				$colorstyle = '<style scoped>body .wpsm-table.wpsm-table-orange table tr th { background: none repeat scroll 0 0 #fb7203; }</style>';
			}
			else if($color == 'blue'){
				$colorstyle = '<style scoped>body .wpsm-table.wpsm-table-blue table tr th { background: none repeat scroll 0 0 #00AAE9; }</style>';
			}
			else if($color == 'yellow'){
				$colorstyle = '<style scoped>body .wpsm-table.wpsm-table-yellow table tr th { background: none repeat scroll 0 0 #FFDD00; color: #222222; }</style>';
			}
			else if($color == 'red'){
				$colorstyle = '<style scoped>body .wpsm-table.wpsm-table-red table tr th { background: none repeat scroll 0 0 #DD0007; }</style>';
			}
			else if($color == 'green'){
				$colorstyle = '<style scoped>body .wpsm-table.wpsm-table-green table tr th { background: none repeat scroll 0 0 #77bb0f; }</style>';
			}
			else if($color == 'purple'){
				$colorstyle = '<style scoped>body .wpsm-table.wpsm-table-purple table tr th { background: none repeat scroll 0 0 #662D91; }</style>';
			}else{
				$colorstyle = '';
			}
		  	return '<div class="wpsm-table wpsm-table-'. $color .'">' . $content . '</div>';
	
	}
}

//////////////////////////////////////////////////////////////////
// Quote
//////////////////////////////////////////////////////////////////	
if(!function_exists('wpsm_quote_shortcode')) {
	function wpsm_quote_shortcode($atts, $content) {   
		$out = '';
		$out .= '<blockquote class="wpsm-quote';
		if(!empty($atts['float']) && $atts['float']):
	      $out .= ' align'.$atts['float'].'';
	    endif;  
		$out .= '"';
		if(!empty($atts['width']) && $atts['width']):
	      $out .= 'style="width:'.$atts['width'].'"';
	    endif;
		$out .= '><p>'.$content.'</p>';
		if(!empty($atts['author']) && $atts['author']):
	      $out .= '<cite>'.$atts['author'].'</cite>';
	    endif;
		$out .='</blockquote>';
		return $out;
	} 
	// add the shortcode to system
}

//////////////////////////////////////////////////////////////////
// Dropcap
//////////////////////////////////////////////////////////////////	
if(!function_exists('wpsm_dropcap_shortcode')) {
function wpsm_dropcap_shortcode( $atts, $content = null ) { 
    return '<span class="wpsm_dropcap">'.$content.'</span>';  
}   
}	

//////////////////////////////////////////////////////////////////
// Video
//////////////////////////////////////////////////////////////////
if(!function_exists('wpsm_shortcode_AddVideo')) {
function wpsm_shortcode_AddVideo( $atts, $content = null ) {
	extract(shortcode_atts(array(
		'schema' => '',
		'width' => '',
		'height' => '',
		'title' => '',
		'description' => '',
	), $atts));	
    if ($schema =='yes') {
		$width  = ($width)  ? $width  :'703' ;
		$height = ($height) ? $height : '395';
    }
    else {
 		$width  = ($width)  ? $width  :'765' ;
		$height = ($height) ? $height : '430';   	
    }
	$title = ($title) ? $title : get_the_title();
	$description = ($description) ? $description : get_the_title();
	global $post;

		if ($schema =='yes') {
			$out = '<div class="media_video clearfix text-center" itemscope itemtype="http://schema.org/VideoObject"><meta content="'.$title.'" itemprop="name"><meta itemprop="uploadDate" content="'.$post->post_date.'" /><meta itemprop="thumbnailURL" content="'.parse_video_url($content, "hqthumb").'"><meta itemprop="embedUrl" content="'.parse_video_url($content, "embedurl").'" /><div class="border-lightgrey clearfix inner padd20 pb0 rh-shadow3"><div class="video-container">'.parse_video_url($content, "embed", "$width", "$height").'</div><h4>'.$title.'</h4><p itemprop="description">'.$description.'</p></div></div>';
		}
		else {	
		$out ='<div class="video-container">'.parse_video_url($content, "embed", "$width", "$height").'</div>';
		}
		
    return $out;
}
}

//////////////////////////////////////////////////////////////////
// Lightbox
//////////////////////////////////////////////////////////////////
if(!function_exists('wpsm_shortcode_lightbox')) {
function wpsm_shortcode_lightbox( $atts, $content = null ) {
    wp_enqueue_script('modulobox');wp_enqueue_style('modulobox');
	extract(shortcode_atts(array(
		'full' => '',
		'title' => '',
	), $atts));
	if(!isset($title)) {
		$title = '';
	}
	$out = '<span class="modulo-lightbox"><a href="'.$full.'" data-title="'.$title.'">' .do_shortcode($content). '</a></span>';
    return $out;
}
}



//////////////////////////////////////////////////////////////////
// Boxes
//////////////////////////////////////////////////////////////////
if(!function_exists('wpsm_shortcode_box')) {
function wpsm_shortcode_box( $atts, $content = null ) {
        $atts = shortcode_atts(
			array(
				'type' => 'info',
				'float' => 'none',
				'textalign' => 'left',
				'width' => 'auto',
			), $atts);
	// Remove all instances of "<p>&nbsp;</p><br>" to avoid extra lines.
	$content = do_shortcode($content);
	$content = preg_replace( '%<p>&nbsp;\s*</p>%', '', $content ); 
	$content = preg_replace('/^(?:<br\s*\/?>\s*)+/', '', $content);
	$out = '<div class="mb30 wpsm_box '.$atts['type'].'_type '.$atts['float'].'float_box" style="text-align:'.$atts['textalign'].'; width:'.$atts['width'].'"><i></i><div>
			' .$content. '
			</div></div>';
    return $out;
}
}


//////////////////////////////////////////////////////////////////
// Promoboxes
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_promobox_shortcode') ) {
function wpsm_promobox_shortcode( $atts, $content = null ) {
	
	extract(shortcode_atts(array(
			'background' => '#f8f8f8',
			'border_size' => '',
			'border_color' => '',
			'highligh_color' => '',
			'highlight_position' => '',
			'title' => '',
			'description' => '',
			'text_color' => '',
			'button_link' => '',
			'button_text' => '',
		), $atts));	
	wp_enqueue_style('rhpromobox');
	$out = '<div class="wpsm_promobox" style="background-color:'.$background.' !important;';
	if($border_size && $border_color):
		$out .= ' border-width:'.$border_size.';border-color:'.$border_color.'!important; border-style:solid;';
	endif;
	if($text_color):
		$out .= ' color:'.$text_color.';';
	endif;
	if($highligh_color && $highlight_position):
		$out .= ' border-'.$highlight_position.'-width:3px !important;border-'.$highlight_position.'-color:'.$highligh_color.'!important;border-'.$highlight_position.'-style:solid';
	endif;
	$out .= '">';
	if($button_link && $button_text):
		$out .= '<a href="'.$button_link.'" class="wpsm-button rehub_main_btn" target="_blank" rel="nofollow"><span>'.$button_text.'</span></a>';
	endif;
	if($title):
		$out .= '<div class="title_promobox">'.$atts['title'].'</div>';
	endif;
	if($description):
		$out.= '<p>'.$description.'</p>';
	endif;
	$out .= '</div>';
    return $out;
}
}

//////////////////////////////////////////////////////////////////
// Number box
//////////////////////////////////////////////////////////////////

if(!function_exists('wpsm_numbox_shortcode')) {
		function wpsm_numbox_shortcode($atts, $content) {  
			extract(shortcode_atts( array('num' => '1', 'style' => '1'), $atts));
			wp_enqueue_style('rhnumbox');
			$content = do_shortcode($content);
			$content = preg_replace( '%<p>&nbsp;\s*</p>%', '', $content ); 
			$Old     = array( '<br />', '<br>' );
			$New     = array( '','' );
			$content = str_replace( $Old, $New, $content );
			$styledot = ($style=='5' || $style=='6') ? '.' : '';			
			// return output
		    return "<div class=\"wpsm-numbox wpsm-style$style\"><span class=\"num\">" . $num . $styledot ."</span>" . $content . "</div>";  
		} 
		// add the shortcode to system
}

//////////////////////////////////////////////////////////////////
// Numbered heading
//////////////////////////////////////////////////////////////////

if(!function_exists('wpsm_numhead_shortcode')) {
		function wpsm_numhead_shortcode($atts, $content) {  
			// get the optional style value
			extract(shortcode_atts( array('num' => '1', 'style' => '1', 'heading' => '2'), $atts));
			wp_enqueue_style('rhnumbox');
			$content = do_shortcode($content);
			$content = preg_replace( '%<p>&nbsp;\s*</p>%', '', $content ); 
			$Old     = array( '<br />', '<br>' );
			$New     = array( '','' );
			$content = str_replace( $Old, $New, $content );			
			// return output
		    return "<div class=\"wpsm-numhead wpsm-style$style\"><span>" . $num . "</span><h$heading>" . $content . "</h$heading></div>";  
		} 
		// add the shortcode to system
}

//////////////////////////////////////////////////////////////////
// Titled box
//////////////////////////////////////////////////////////////////

if(!function_exists('wpsm_titlebox_shortcode')) {
		function wpsm_titlebox_shortcode($atts, $content) {   
			// get the optional style value
			extract(shortcode_atts( array('title' => 'Sample title', 'style' => '1', 'align'=>''), $atts));
			$content = do_shortcode($content);
			$content = preg_replace( '%<p>&nbsp;\s*</p>%', '', $content ); 
			$content = preg_replace('/^(?:<br\s*\/?>\s*)+/', '', $content);
		    if($style == 'main'){
		    	$themeclass = ' rehub-main-color-border';
		    	$colorclass = 'rehub-main-color';
		    }
		    elseif($style == 'secondary'){
		    	$themeclass = ' rehub-sec-color-border';
		    	$colorclass = 'rehub-sec-color';
		    }       
		    else{
		    	$themeclass = $colorclass = '';
		    } 	
			$alignclass = (!empty($align)) ? ' align'.esc_attr($align).' ' : '';
		    wp_enqueue_style('rhtitlebox', get_template_directory_uri() . '/css/shortcodes/titlebox.css');					
			// return the url
		    return '<div class="'.$alignclass.'wpsm-titlebox clearbox wpsm_style_' . $style .$themeclass. '"><strong class="'.$colorclass.'">' . $title . '</strong><div>'.$content.'</div></div>';  
		} 
		// add the shortcode to system
}

//////////////////////////////////////////////////////////////////
// Code box
//////////////////////////////////////////////////////////////////

if(!function_exists('wpsm_code_shortcode')) {
		function wpsm_code_shortcode($atts, $content) {   
			// get the optional style value
			extract(shortcode_atts( array('style' => '1'), $atts));
			// Remove all instances of "<p>&nbsp;</p><br>" to avoid extra lines.
			$content = do_shortcode($content);
			$content = preg_replace( '%<p>&nbsp;\s*</p>%', '', $content ); 
			$Old     = array( '<br />', '<br>' );
			$New     = array( '','' );
			$content = str_replace( $Old, $New, $content );			
			// return the element
		    return '<pre class="wpsm-code wpsm_code_' . $style . '"><code>'. trim($content) .'</code></pre>'; 
			 
		} 
		// add the shortcode to system
}

//////////////////////////////////////////////////////////////////
// Accordition
//////////////////////////////////////////////////////////////////

// Main
if( !function_exists('wpsm_accordion_main_shortcode') ) {
	function wpsm_accordion_main_shortcode( $atts, $content = null  ) {		

		extract( shortcode_atts( array(
		  'disableschema' => ''
		), $atts ) );
        
		// Remove all instances of "<p>&nbsp;</p><br>" to avoid extra lines.
		$content = do_shortcode($content);
		$content = preg_replace( '%<p>&nbsp;\s*</p>%', '', $content ); 
		$content = preg_replace('/^(?:<br\s*\/?>\s*)+/', '', $content);
		
		wp_enqueue_script('rhaccordion');
		wp_enqueue_style('rhaccordion');
		$schema = ($disableschema) ? '' : ' itemscope="" itemtype="https://schema.org/FAQPage"';
		// Display the accordion	
		return '<div class="wpsm-accordion mb30" data-accordion="yes"'.$schema.'>' .$content . '</div>';
	}
}

// Section
if( !function_exists('wpsm_accordion_section_shortcode') ) {
	function wpsm_accordion_section_shortcode( $atts, $content = null  ) {
		extract( shortcode_atts( array(
		  'title' => 'Title',
		  'disableschema' => ''
		), $atts ) );

		$schema = ($disableschema) ? '' : ' itemscope="" itemprop="mainEntity" itemtype="https://schema.org/Question"';
		  
	   return '<div class="wpsm-accordion-item close"'.$schema.'><h3 class="wpsm-accordion-trigger" itemprop="name">'. $title .'</h3><div class="accordion-content"  itemscope="" itemprop="acceptedAnswer" itemtype="https://schema.org/Answer"><div itemprop="text">' . do_shortcode($content) . '</div></div></div>';
	}
}

//////////////////////////////////////////////////////////////////
// Toggle
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_toggle_shortcode') ) {
	function wpsm_toggle_shortcode( $atts, $content = null ) {
		extract( shortcode_atts( array( 'title' => 'Toggle Title', 'class' => ''), $atts ) );

		// Remove all instances of "<p>&nbsp;</p><br>" to avoid extra lines.
		$content = do_shortcode($content);
        $content = preg_replace( '%<p>&nbsp;\s*</p>%', '', $content ); 
		$content = preg_replace('/^(?:<br\s*\/?>\s*)+/', '', $content);
		
		// Display the Toggle

		$opens = '';
		if ( $class == 'active' ) {  
			$opens = ' open';
		} else {
			$opens = ' close';
		}
		wp_enqueue_script('rhaccordion');
		wp_enqueue_style('rhaccordion');
		return '<div class="wpsm-accordion mb30" data-accordion="no"><div class="wpsm-accordion-item'.$opens.'"><h3 class="wpsm-accordion-trigger">'. $title .'</h3><div class="accordion-content">' . $content . '</div></div></div>';
	}
}

//////////////////////////////////////////////////////////////////
// Testimonial
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_testimonial_shortcode') ) { 
	function wpsm_testimonial_shortcode( $atts, $content = null  ) {
		extract( shortcode_atts( array(
			'by' => '',
			'image' => '',
		  ), $atts ) );
		// Remove all instances of "<p>&nbsp;</p><br>" to avoid extra lines.
		wp_enqueue_style('rhtestimonial');
		$content = do_shortcode($content);
        $content = preg_replace( '%<p>&nbsp;\s*</p>%', '', $content ); 
		$content = preg_replace('/^(?:<br\s*\/?>\s*)+/', '', $content);
				  
		$out = '';
		$out .= '<div class="wpsm-testimonial"><div class="wpsm-testimonial-content">';
		$out .= $content;
		$out .= '</div><div class="wpsm-testimonial-author">';
		if ($image && is_numeric($image)) {
			$image_url = wp_get_attachment_image_src($image, 'full');
			$image = $image_url[0];
		}		
		if (isset($image) && !empty($image)) {
			$out .= '<img src="'. $image .'" alt="'. $by .'" class="author_image">';
		}
		$out .= $by .'</div></div>';	
		return $out;
	}
}


//////////////////////////////////////////////////////////////////
// Slider
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_shortcode_quick_slider') ) {
	function wpsm_shortcode_quick_slider($atts, $content = null) {
		extract(shortcode_atts(array(
				"ids" => '',
		), $atts));
		wp_enqueue_script('flexslider');wp_enqueue_script('flexinit');wp_enqueue_style('flexslider');
		return wpsm_get_post_slide($ids);
	}
}

//////////////////////////////////////////////////////////////////
// Post image attachment slider
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_post_slide') ) {
function wpsm_post_slide( $atts, $content = null ) {
		wp_enqueue_script('flexslider');
	return wpsm_get_post_slide();
}
function wpsm_get_post_slide($ids='') {
		$out = '';
		if (!empty($ids)) {
			$attachments = array_map( 'trim', explode( ",", $ids ) );
		}
		else {
			$attachments = get_posts( array(
            	'post_type' => 'attachment',
				'post_mime_type' => 'image',
            	'posts_per_page' => -1,
            	'post_parent' => get_the_ID(),
            	'exclude'     => get_post_thumbnail_id()
        	));
		}

        if ( $attachments ) {

            $out = '<div class="flexslider post_slider media_slider blog_slider loading"><ul class="slides">';
            foreach ( $attachments as $attachment ) {
            	if (!empty($ids)) {
            		$thumbimg = wp_get_attachment_image($attachment, 'full', false);
            	}
            	else {
            		$thumbimg = wp_get_attachment_image($attachment->ID, 'full', false);
            	}                      
                $out .= '<li>' . $thumbimg . '</li>';
            }
            $out .='</ul></div>';
            
        }
        return $out;
    }
}


//////////////////////////////////////////////////////////////////
// Map
//////////////////////////////////////////////////////////////////
if (! function_exists( 'wpsm_shortcode_googlemaps' ) ) :
 	function wpsm_shortcode_googlemaps($atts, $content = null) { 
	  	extract(shortcode_atts(array(
	    "title" => '',
	    "location" => '',
	    "height" => '300px',
	    "zoom" => 10,
	    "align" => '',
	    "lat" => '',
	    "lng" => '',
	    "key" => ''
	  ), $atts));
  
		// load scripts
		$fullkey = empty($key) ? 'sensor=false' : 'key='. $key;
		wp_enqueue_script('wpsm_googlemap');
		wp_enqueue_script('wpsm_googlemap_api', 'https://maps.googleapis.com/maps/api/js?'. $fullkey, array( 'jquery' ), '', true);
		$output = '';
  
	  	if ($location){
	   		$output .= '<div id="map_canvas_'.mt_rand().'" class="wpsm_googlemap position-relative wpsm_gmap_loc" style="height:'.$height.';width:100%">';
	    	$output .= (!empty($title)) ? '<input class="title" type="hidden" value="'.$title.'" />' : '';
	    	$output .= '<input class="location" type="hidden" value="'.$location.'" />';
	    	$output .= '<input class="zoom" type="hidden" value="'.$zoom.'" />';
	    	$output .= '<div class="map_canvas width-100p"></div>';
	   		$output .= '</div>';   
	  	}  
  		elseif ($lat && $lng){
   			$output .= '<div id="map_canvas_'.mt_rand().'" class="wpsm_googlemap wpsm_gmap_pos" style="height:'.$height.';width:100%">';
    		//$output .= (!empty($title)) ? '<input class="title" type="hidden" value="'.$title.'" />' : '';
    		$output .= '<input class="lat" type="hidden" value="'.$lat.'" />';
    		$output .= '<input class="lng" type="hidden" value="'.$lng.'" />';    
    		$output .= '<input class="zoom" type="hidden" value="'.$zoom.'" />';
    		$output .= '<div class="map_canvas width-100p"></div>';
   			$output .= '</div>';   
  		}
  	return $output;   
	}
endif;


//////////////////////////////////////////////////////////////////
// Dividers
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_divider_shortcode') ) {
	function wpsm_divider_shortcode( $atts ) {
		extract( shortcode_atts( array(
			'style' => 'solid',
			'top' => '20px',
			'bottom' => '20px',
		  ),
		  $atts ) );
		$style_attr = '';
		wp_enqueue_style('rhdividers');
		if ( $top && $bottom ) {  
			$style_attr = 'style="margin-top: '. $top .';margin-bottom: '. $bottom .';"';
		} elseif( $bottom ) {
			$style_attr = 'style="margin-bottom: '. $bottom .';"';
		} elseif ( $top ) {
			$style_attr = 'style="margin-top: '. $top .';"';
		} else {
			$style_attr = NULL;
		}
	 return '<hr class="wpsm-divider '. $style .'_divider" '.$style_attr.' />';
	}
}


//////////////////////////////////////////////////////////////////
// Price Table shortcode
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_price_shortcode') ) {
	function wpsm_price_shortcode( $atts, $content = null  ) {
	  	// Remove all instances of "<p>&nbsp;</p><br>" to avoid extra lines.
	  	$content = do_shortcode($content);
	  	$content = preg_replace( '%<p>&nbsp;\s*</p>%', '', $content ); 
	  	$Old     = array( '<br />', '<br>' );
	  	$New     = array( '','' );
	  	$content = str_replace( $Old, $New, $content );
	  	wp_enqueue_style('rhpricetable');
	   	return '<ul class="wpsm-price mt20 mb20 clearfix">' . $content . '</ul><br class="clear" />';
	}
}
/* Column of price*/
if( !function_exists('wpsm_price_column_shortcode') ) {
	function wpsm_price_column_shortcode( $atts, $content = null  ) {
		extract( shortcode_atts( array(
			'size' => '3',
			'featured' => '',
			'name' => 'Sample Name',
			'price' => '',
			'per' => '',
			'button_url' => '',
			'button_text' => 'Buy Now',
			'button_color' => 'orange',
		), $atts ) );
		
	  // Remove all instances of "<p>&nbsp;</p><br>" to avoid extra lines.
	  $content = do_shortcode($content);
	  $content = preg_replace( '%<p>&nbsp;\s*</p>%', '', $content ); 
	  $Old     = array( '<br />', '<br>' );
	  $New     = array( '','' );
	  $content = str_replace( $Old, $New, $content );		
		
		if($size == '2') $column_size = 'one-half';
		if($size == '3') $column_size = 'one-third';
		if($size =='4') $column_size = 'one-fourth';
		if($size =='5') $column_size = 'one-fifth';
	
		if($featured =='yes') $featured_price = 'wpsm-featured-price';
		else $featured_price = NULL;
			
		//fetch content  
		$out_price ='';
		$out_price .= '<li class="wpsm-price-column wpsm-'. $column_size .' '. $featured .' '. $featured_price .'">';
		$out_price .= '<div class="wpsm-price-header"><h4>'. $name. '</h4></div>';
		$out_price .= '<div class="wpsm-price-content"><div class="wpsm-price-cell"><span class="wpsm-price-value">'. $price .'</span>';
		if (!empty($per)) :
			$out_price .= ' /'.$per.'';
		endif;
		$out_price .='</div>';
		$out_price .= $content;
		if ($button_url){
			$out_price .= '<div class="wpsm-price-button"><a href="'. $button_url .'" class="wpsm-button '. $button_color .'"><span class="wpsm-button-inner">'. $button_text .'</span></a></div>';
		}
		$out_price .= '</div></li>';
		  
	   return $out_price;
	}
}

//////////////////////////////////////////////////////////////////
// tab shortcode
//////////////////////////////////////////////////////////////////

if (!function_exists('wpsm_tabgroup_shortcode')) {
	function wpsm_tabgroup_shortcode( $atts, $content = null ) {
		
		$defaults = array();
		extract( shortcode_atts( $defaults, $atts ) );
		preg_match_all( '/tab title="([^\"]+)"/i', $content, $matches, PREG_OFFSET_CAPTURE );
		$tab_titles = array();

		wp_enqueue_script('rhtabs');
		
		// Remove all instances of "<p>&nbsp;</p><br>" to avoid extra lines.
		$content = do_shortcode($content);
        $content = preg_replace( '%<p>&nbsp;\s*</p>%', '', $content ); 
		$Old     = array( '<br />', '<br>' );
		$New     = array( '','' );
		$content = str_replace( $Old, $New, $content );
		
		if( isset($matches[1]) ){ $tab_titles = $matches[1]; }
		$output = '';
		if( count($tab_titles) ){
		    $output .= '<div id="wpsm-tab-'. rand(1, 100) .'" class="wpsm-tabs mb25 tabs">';
		    $output .= rh_generate_incss('tabs');
			$output .= '<ul class="tabs-menu rh-tab-shortcode smart-scroll-mobile">';
			foreach( $tab_titles as $index=>$tab){
				$output .= '<li><span class="cursorpointer">' . $tab[0] . '</span></li>';
			}
		    $output .= '</ul>';
		    $output .= $content;
		    $output .= '</div>';
		} else {
			$output .= $content;
		}
		return $output;
	}
}
if (!function_exists('wpsm_tab_shortcode')) {
	function wpsm_tab_shortcode( $atts, $content = null ) {
		$defaults = array( 'title' => 'Tab' );
		extract( shortcode_atts( $defaults, $atts ) );
		
		return '<div class="tab-content tabs-item rhhidden">'. do_shortcode( $content ) .'</div>';
	}
}


//////////////////////////////////////////////////////////////////
// Get feeds
//////////////////////////////////////////////////////////////////

if( !function_exists('wpsm_shortcode_feeds') ) {
function wpsm_shortcode_feeds( $atts, $content = null ) {
	extract(shortcode_atts(array(
		'number' => '5',
		'url' => '',
	), $atts));
	$number  = ($number)  ? $number  : '5' ;
	return wpsm_get_feeds( $url , $number );
}
}

function wpsm_get_feeds( $feed , $number ){
	include_once(ABSPATH . WPINC . '/feed.php');

	$rss = @fetch_feed( $feed );
	if (!is_wp_error( $rss ) ){
		$maxitems = $rss->get_item_quantity($number); 
		$rss_items = $rss->get_items(0, $maxitems); 
	}
	if ($maxitems == 0) {
		$out = "<ul><li>No items</li></ul>";
	}else{
		$out = "<ul>";
		
		foreach ( $rss_items as $item ) : 
			$out .= '<li><a href="'. esc_url( $item->get_permalink() ) .'" title="Posted '.$item->get_date("j F Y | g:i a").'">'. esc_html( $item->get_title() ) .'</a></li>';
		endforeach;
		$out .='</ul>';
	}
	
	return $out;
}

//////////////////////////////////////////////////////////////////
// Percent bars
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_bar_shortcode') ) {
	function wpsm_bar_shortcode( $atts  ) {		
		extract( shortcode_atts( array(
			'title' => '',
			'percentage' => '100%',
			'color' => '#6adcfa',
		), $atts ) );		

		$output = '<div class="wpsm-bar wpsm-clearfix" data-percent="'. $percentage .'%">';
			if ( $title !== '' ) $output .= '<div class="wpsm-bar-title" style="background: '. $color .';"><span>'. $title .'</span></div>';
			$output .= '<div class="wpsm-bar-bar" style="background: '. $color .';"></div>';
			$output .= '<div class="wpsm-bar-percent">'.$percentage.' %</div>';
		$output .= '</div>';
		
		return $output;
	}
}

//////////////////////////////////////////////////////////////////
// List
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_list_shortcode') ) {
function wpsm_list_shortcode( $atts, $content = null ) {

		extract( shortcode_atts( array(
			'type' => 'arrow',
			'hover' => '',
			'gap' => '',
			'darklink' => ''
		), $atts ) ); 
		wp_enqueue_style('rhprettylist');
		$content = do_shortcode($content);
        $content = preg_replace( '%<p>&nbsp;\s*</p>%', '', $content ); 
		$Old     = array( '<br />', '<br>' );
		$New     = array( '','' );
		$content = str_replace( $Old, $New, $content );
		$gapclass = ($gap == 'small') ? ' small_gap_list' : '';	
		$hoverclass = ($hover) ? ' wpsm_pretty_hover' : '';	
		$darklinkclass = ($darklink) ? ' darklink' : '';
    return '<div class="wpsm_'.$type.'list wpsm_pretty_list'.$gapclass.$hoverclass.$darklinkclass.'">'.$content.'</div>';  
}  
}

//////////////////////////////////////////////////////////////////
// Pros
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_pros_shortcode') ) {
function wpsm_pros_shortcode( $atts, $content = null ) {
	extract(shortcode_atts(array(
		'title' => 'Positives',
	), $atts));
	// Remove all instances of "<p>&nbsp;</p><br>" to avoid extra lines.
	$content = do_shortcode($content);
    $content = preg_replace( '%<p>&nbsp;\s*</p>%', '', $content ); 
	$Old     = array( '<br />', '<br>' );
	$New     = array( '','' );
	$content = str_replace( $Old, $New, $content );		 	
    return '<div class="wpsm_pros"><div class="title_pros">'.$title.'</div>'.$content.'</div>';  
}  
}

//////////////////////////////////////////////////////////////////
// Cons
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_cons_shortcode') ) {
function wpsm_cons_shortcode( $atts, $content = null ) {
	extract(shortcode_atts(array(
		'title' => 'Negatives',
	), $atts));	
    return '<div class="wpsm_cons"><div class="title_cons">'.$title.'</div>'.$content.'</div>';  
}  
}

//////////////////////////////////////////////////////////////////
// Tooltip
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_shortcode_tooltip') ) {
function wpsm_shortcode_tooltip( $atts, $content = null ) {
	wp_enqueue_script('tipsy');

	extract(shortcode_atts(array(
		'text'=> '',
	), $atts));
	$content_true = do_shortcode($content);
	if( empty($content_true) ) return;
	$out = '';
	wp_enqueue_style('rhtipsy');
	$out .= '<span class="wpsm-tooltip wpsm-tooltip-sw" original-title="'.$content_true.'">'.$text.'</span>';
   return $out;
}
}


//////////////////////////////////////////////////////////////////
// Member block
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_member_shortcode') ) {
function wpsm_member_shortcode( $atts, $content = null ) {
	extract(shortcode_atts(array(
		'guest_text' => ' This content visible only for members. You can login <a href="/wp-login.php" class="act-rehub-login-popup">here</a>.',
	), $atts));
	// Remove all instances of "<p>&nbsp;</p><br>" to avoid extra lines.
	$content = do_shortcode($content);
	$content = preg_replace( '%<p>&nbsp;\s*</p>%', '', $content ); 
	$content = preg_replace('/^(?:<br\s*\/?>\s*)+/', '', $content);
	$css = '
		.wpsm-members { background: none repeat scroll 0 0 #FAFAFA; border: 1px solid #ddd; color: #444; margin: 25px 0 18px 0; padding: 17px 15px 10px 15px; position: relative; }
		.wpsm-members > strong:first-child { font-size: 12px; padding: 0 10px; width: auto !important; color: #FFFFFF; height: 20px; left:10px; line-height: 21px; position: absolute; text-align: center; top: -10px; width: 20px; }';
	wp_register_style( 'wpsm_member_shortcode', false );
	wp_enqueue_style( 'wpsm_member_shortcode' );
	wp_add_inline_style( 'wpsm_member_shortcode', $css); 
	if (is_user_logged_in() && !is_null( $content ) && !is_feed()) {
		return '<div class="wpsm-members"><strong>'.__("Members only", "rehub-theme").'</strong>' . $content . '</div>';
	}
	else { 
		return '<div class="wpsm-members not-logined"><strong>'.esc_html__("Members only", "rehub-theme").'</strong> '.$guest_text.'</div>';	
		 }

	}	
}

//////////////////////////////////////////////////////////////////
// Member content
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_shortcode_is_logged_in') ) {
function wpsm_shortcode_is_logged_in( $atts, $content = null ) {
	//@extract($atts);
	// Remove all instances of "<p>&nbsp;</p><br>" to avoid extra lines.
	$content = do_shortcode($content);
	$content = preg_replace( '%<p>&nbsp;\s*</p>%', '', $content ); 
	$content = preg_replace('/^(?:<br\s*\/?>\s*)+/', '', $content);
	if (is_user_logged_in() && !is_null( $content ) && !is_feed()) {
		return $content;
	}
	else { 
		return;	
	}

}	
}

//////////////////////////////////////////////////////////////////
// Guest content
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_shortcode_is_guest') ) {
function wpsm_shortcode_is_guest( $atts, $content = null ) {
	//@extract($atts);
	// Remove all instances of "<p>&nbsp;</p><br>" to avoid extra lines.
	$content = do_shortcode($content);
	$content = preg_replace( '%<p>&nbsp;\s*</p>%', '', $content ); 
	$content = preg_replace('/^(?:<br\s*\/?>\s*)+/', '', $content);
	if (!is_user_logged_in() && !is_null( $content ) && !is_feed()) {
		return $content;
	}
	else { 
		return;	
	}

}	
}

//////////////////////////////////////////////////////////////////
// Vendor content
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_shortcode_is_vendor') ) {
function wpsm_shortcode_is_vendor( $atts, $content = null ) {
	//@extract($atts);
	// Remove all instances of "<p>&nbsp;</p><br>" to avoid extra lines.
	$content = do_shortcode($content);
	$content = preg_replace( '%<p>&nbsp;\s*</p>%', '', $content ); 
	$content = preg_replace('/^(?:<br\s*\/?>\s*)+/', '', $content);
	$user = wp_get_current_user();
	$rolesarray = array('vendor', 'seller', 'dc_vendor', 'wcfm_vendor');
	foreach ($rolesarray as $role) {
		if ( in_array( $role, (array) $user->roles )) {
			return $content;
		}
	}
	return;


}	
}

//////////////////////////////////////////////////////////////////
// Vendor content
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_shortcode_is_pending_vendor') ) {
function wpsm_shortcode_is_pending_vendor( $atts, $content = null ) {
	//@extract($atts);
	// Remove all instances of "<p>&nbsp;</p><br>" to avoid extra lines.
	$content = do_shortcode($content);
	$content = preg_replace( '%<p>&nbsp;\s*</p>%', '', $content ); 
	$content = preg_replace('/^(?:<br\s*\/?>\s*)+/', '', $content);
	$user = wp_get_current_user();
	$rolesarray = array('pending_vendor', 'dc_pending_vendor');
	foreach ($rolesarray as $role) {
		if ( in_array( $role, (array) $user->roles )) {
			return $content;
		}
	}
	return;

}	
}

//////////////////////////////////////////////////////////////////
// Vendor content
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_shortcode_not_vendor_logged') ) {
function wpsm_shortcode_not_vendor_logged( $atts, $content = null ) {
	//@extract($atts);
	// Remove all instances of "<p>&nbsp;</p><br>" to avoid extra lines.
	$content = do_shortcode($content);
	$content = preg_replace( '%<p>&nbsp;\s*</p>%', '', $content ); 
	$content = preg_replace('/^(?:<br\s*\/?>\s*)+/', '', $content);
	$user = wp_get_current_user();
	if ( is_user_logged_in() && !in_array( 'vendor', (array) $user->roles ) && !in_array( 'wcfm_vendor', (array) $user->roles )  && !in_array( 'seller', (array) $user->roles ) && !in_array( 'dc_vendor', (array) $user->roles )) {
		return $content;
	}		
	else { 
		return;	
	}

}	
}

//////////////////////////////////////////////////////////////////
// Vendor content
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_shortcode_customer_user') ) {
function wpsm_shortcode_customer_user( $atts, $content = null ) {
	//@extract($atts);
	// Remove all instances of "<p>&nbsp;</p><br>" to avoid extra lines.
	$user = wp_get_current_user();
	if ( is_user_logged_in() && !in_array( 'customer', (array) $user->roles )  && !is_null( $content ) && !is_feed()) {
	$content = do_shortcode($content);
	$content = preg_replace( '%<p>&nbsp;\s*</p>%', '', $content ); 
	$content = preg_replace('/^(?:<br\s*\/?>\s*)+/', '', $content);
	$user = wp_get_current_user();		
		return $content;
	}		
	else { 
		return;	
	}

}	
}


//////////////////////////////////////////////////////////////////
// Gallery carousel
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_gallery_carousel') ) {
function wpsm_gallery_carousel( $atts, $content = null ) {
	wp_enqueue_style('rhcarousel');wp_enqueue_script('owlcarousel'); wp_enqueue_script('owlinit');
	extract(shortcode_atts(array(
		'ids' => '',
		'prettyphoto' => '',
		'title'=> '',
	), $atts));
    $pretty_id = rand(5, 150) ;
    $everul =''; 
	$gals = explode(',', $ids);
	$title = (!empty($title)) ? '<h3>'.$title.'</h3>' : '';
	$everul .='<div class="modulo-lightbox media_owl_carousel carousel-style-2 pretty_photo_'.$pretty_id.' clearfix">'.$title.'<div class="re_carousel" data-showrow="4" data-auto="">';
	foreach ($gals as $gal){
		$urlgal =  wp_get_attachment_url( $gal);
		$params = array( 'width' => 200, 'crop' => false  );
		$everul .='<div class="photo-item"><a data-rel="pretty_photo_'.$pretty_id.'" href="'.$urlgal.'" data-thumb="'.$urlgal.'" data-title="'.esc_attr(get_post_field( "post_excerpt", $gal)).'"><img src="'.bfi_thumb($urlgal, $params).'" alt="image" /></a></div>';
	}
	$everul .='</div></div>';
    if ($prettyphoto){
    	wp_enqueue_script('modulobox');wp_enqueue_style('modulobox');	
    } 			
	 return $everul;
}
}

//////////////////////////////////////////////////////////////////
// Woo Box
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_woobox_shortcode') ) {
function wpsm_woobox_shortcode( $atts, $content = null ) {
	
	extract(shortcode_atts(array(
			'id' => '',
			'wooid'=> '',
			'title_tag' => 'h3'
		), $atts));
		
	if(!empty($id)):
		ob_start(); 
		rehub_get_woo_offer(esc_attr($id), $title_tag);
		$output = ob_get_contents();
		ob_end_clean();
		return $output;	
	endif;	

}
}

//////////////////////////////////////////////////////////////////
// Woo Compare box
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_woocompare_shortcode') ) {
function wpsm_woocompare_shortcode( $atts, $content = null ) {
	
	extract(shortcode_atts(array(
			'ids' => '',
			'notitle' => '',
			'field' => '',
			'logo' => 'vendor',
			'compact' => '',
			'titlefield' => ''
		), $atts));
		
	if($ids || $field):
		if($ids){
			$ids = array_map( 'trim', explode( ",", $ids ) );
			$args = array(
		        'post__in' => $ids,
		        'numberposts' => '-1',
		        'orderby' => 'meta_value_num', 
		        'post_type' => 'product',  
		        'meta_key' => '_price', 
		        'order' => 'ASC'        
		    );
		}elseif ($field){
			$field = esc_html($field);
			$valuekey = get_post_meta(get_the_ID(), $field, true);
			if(empty($valuekey)){return;}
			$args = array(
				'post_type' => 'product',
		        'numberposts' => '-1',
		        'orderby' => 'meta_value_num',   
		        'meta_key' => '_price', 
		        'order' => 'ASC',
				'meta_query' => array(
					array(
						'key'     => $field,
						'value'   => $valuekey,
						'compare' => '=',
					),
				),		                
		    );			
		}
		if ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) ) {
			$args['tax_query'][] = array(
				'relation' => 'AND',
				array(
					'taxonomy' => 'product_visibility',
					'field'    => 'name',
					'terms'    => 'outofstock',
					'operator' => 'NOT IN',
				)
			);
		}
		ob_start(); 
		?>

			<?php $currentid = get_the_ID(); $wp_query = new WP_Query( $args ); if ( $wp_query->have_posts() ) : ?> 
			<?php if ($compact):?>
				<div class="clearfix"></div>
				<div class="ce_common_simple_list">
					<?php while ( $wp_query->have_posts() ) : $wp_query->the_post(); global $product;?>			
					<?php $this_id = $product->get_id();?>	
			        <div class="flowhidden pb10 pt15 border-grey-bottom">               
			            <div class="floatleft mobileblockdisplay mb15 offer_thumb">   
			                	<?php 
			                		$term_ids =  wp_get_post_terms($this_id, 'store', array("fields" => "ids")); 
			                		$brand_url = '';
						        	if (!empty($term_ids) && ! is_wp_error($term_ids)) {
						        		$term_id = $term_ids[0];
						        		$brand_url = get_term_meta( $term_id, 'brandimage', true );
						        	}
						        ?>
			                	<?php if ($brand_url) :?>
			                		<?php WPSM_Woohelper::re_show_brand_tax('logo', '30'); //show brand logo?>
			                	<?php else:?>  
									<?php $vendor_id = get_the_author_meta( 'ID' );?>
									<?php if (defined('wcv_plugin_dir')):?>
										<a href="<?php echo WCV_Vendors::get_vendor_shop_page( $vendor_id );?>">
											<img src="<?php echo rh_show_vendor_avatar($vendor_id, 50, 50, false);?>" class="vendor_store_image_single" width="50" height="50" />
										</a>
									<?php elseif ( class_exists( 'WeDevs_Dokan' ) ):?>
										<a href="<?php echo dokan_get_store_url( $vendor_id );?>">
											<img src="<?php echo rh_show_vendor_avatar($vendor_id, 50, 50, false);?>" class="vendor_store_image_single" width="50" height="50" />
										</a>
									<?php elseif (defined('WCFMmp_TOKEN')): ?>
										<a href="<?php echo wcfmmp_get_store_url( $vendor_id ); ?>">
											<img src="<?php echo rh_show_vendor_avatar($vendor_id, 50, 50, false);?>" class="vendor_store_image_single" width="50" height="50" />
										</a>										
									<?php else:?>
										<?php 
				                        $showimg = new WPSM_image_resizer();
				                        $showimg->use_thumb = true;
				                        $showimg->no_thumb = rehub_woocommerce_placeholder_img_src('');
				                        $showimg->height = 30;
				                        $showimg->crop = false;           
				                        $showimg->show_resized_image();                                    
				                    	?>										
									<?php endif;?>
								<?php endif;?>                                                                                
			            </div>
			            <div class="floatright buttons_col pl20 rtlpr20 wpsm-one-half-mobile wpsm-column-last">
		                    <div class="priced_block mt0 mb0 clearfix floatright">
			                    <?php if ( $product->add_to_cart_url() !='') : ?>
			                        <?php  echo apply_filters( 'woocommerce_loop_add_to_cart_link',
			                            sprintf( '<a href="%s" data-product_id="%s" data-product_sku="%s" class="re_track_btn mb5 woo_loop_btn btn_offer_block %s %s product_type_%s"%s%s>%s</a>',
			                            esc_url( $product->add_to_cart_url() ),
			                            esc_attr( $this_id ),
			                            esc_attr( $product->get_sku() ),
			                            $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
			                            $product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : '',
			                            esc_attr( $product->get_type() ),
			                            $product->get_type() =='external' ? ' target="_blank"' : '',
			                            $product->get_type() =='external' ? ' rel="nofollow"' : '',
			                            esc_html( $product->add_to_cart_text() )
			                            ),
			                        $product );?>
			                    <?php endif; ?>
		                    </div>
		                    <?php if($this_id != $currentid):?>
								<a href="<?php the_permalink();?>" class="font80 lineheight15 clearbox floatright"><?php esc_html_e('Details', 'rehub-theme');?></a>
							<?php endif; ?>	                                 
			            </div>                                  
			            <div class="floatright text-right-align disablemobilealign wpsm-one-half-mobile">
			            	<span class="font120 rehub-btn-font fontbold wooprice_count"><?php echo ''.$product->get_price_html(); ?>
			            	</span>                      
			            </div>                                            
			        </div>
			        <?php endwhile; ?>					
				</div>
				<div class="clearfix"></div>
			<?php else:?>
				<div class="rh_listoffers rh_listoffers_price_col vendor-list-container">			
					<?php while ( $wp_query->have_posts() ) : $wp_query->the_post();  global $product;  ?>
						<?php $this_id = $product->get_id();?>
						<?php $woolink = get_post_permalink($this_id) ;?>  
			            <div class="rh_listofferitem rh_list_mbl_im_left border-grey-bottom"> 
			            	<div class="rh-flex-center-align rh-flex-justify-center pt15 pb15 mobileblockdisplay">
				                <div class="rh_listcolumn rh_listcolumn_image text-center">
				                	<?php if ($logo == 'brand') :?>
				                		<?php WPSM_Woohelper::re_show_brand_tax('logo', '80'); //show brand logo?>
				                	<?php elseif ($logo == 'product') :?>
					                    <?php 
					                        $showimg = new WPSM_image_resizer();
					                        $showimg->use_thumb = true;
					                        $showimg->no_thumb = rehub_woocommerce_placeholder_img_src('');
					                        $showimg->height = 80;
					                        $showimg->crop = false;           
					                        $showimg->show_resized_image();                                    
					                    ?>			
				                	<?php else:?>  
										<?php $vendor_id = get_the_author_meta( 'ID' );?>
										<?php if (defined('wcv_plugin_dir')):?>
											<a href="<?php echo WCV_Vendors::get_vendor_shop_page( $vendor_id );?>">
												<img src="<?php echo rh_show_vendor_avatar($vendor_id, 90, 90);?>" class="vendor_store_image_single" width="90" height="90" />
											</a>
										<?php elseif ( class_exists( 'WeDevs_Dokan' ) ):?>
											<a href="<?php echo dokan_get_store_url( $vendor_id );?>">
												<img src="<?php echo rh_show_vendor_avatar($vendor_id, 90, 90);?>" class="vendor_store_image_single" width="90" height="90" />
											</a>
										<?php elseif (defined('WCFMmp_TOKEN')): ?>
											<a href="<?php echo wcfmmp_get_store_url( $vendor_id );?>">
												<img src="<?php echo rh_show_vendor_avatar($vendor_id, 90, 90);?>" class="vendor_store_image_single" width="90" height="90" />
											</a>										
										<?php endif;?>
									<?php endif;?>
				                </div>
				                <div class="rh_listcolumn rh-flex-grow1 rh_listcolumn_text">
				                	<?php if (!$notitle):?>
				                        <a href="<?php echo esc_url($woolink) ?>" class="blackcolor rehub-main-font blockstyle mb10 lineheight15 font90">
				                            <?php the_title(); ?>
				                        </a>
			                    	<?php endif;?>
			                    	<?php if($titlefield):?>
			                    		<div class="blackcolor rehub-main-font blockstyle mb10 font90 lineheight15"><?php echo wpsm_get_custom_value(array('field'=>$titlefield));?></div>
			                    	<?php endif;?>
			                    	<?php if ($notitle):?><div class="only-vendor-title"><?php endif;?>
			                        	<?php do_action( 'rehub_vendor_show_action' ); ?> 
			                        <?php if ($notitle):?></div><?php endif;?>
				                </div>                    
				                <div class="rh_listcolumn rh_listcolumn_price text-center"> 
				                	<?php echo '<span class="price_count fontbold rehub-main-color rehub-btn-font">'.$product->get_price_html().'</span>';?>    
				                </div>
				                <div class="text-right-align rh_listcolumn_btn pr15 rtlpl15">
				                    <div class="priced_block mb0 clearfix">
					                    <?php if ( $product->is_in_stock() &&  $product->add_to_cart_url() !='') : ?>
					                        <?php  echo apply_filters( 'woocommerce_loop_add_to_cart_link',
					                            sprintf( '<a href="%s" data-product_id="%s" data-product_sku="%s" class="re_track_btn mb5 woo_loop_btn btn_offer_block %s %s product_type_%s"%s%s>%s</a>',
					                            esc_url( $product->add_to_cart_url() ),
					                            esc_attr( $this_id ),
					                            esc_attr( $product->get_sku() ),
					                            $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
					                            $product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : '',
					                            esc_attr( $product->get_type() ),
					                            $product->get_type() =='external' ? ' target="_blank"' : '',
					                            $product->get_type() =='external' ? ' rel="nofollow"' : '',
					                            esc_html( $product->add_to_cart_text() )
					                            ),
					                        $product );?>
					                    <?php endif; ?>
				                    </div>
				                    <?php if($this_id != $currentid):?>
										<a href="<?php the_permalink();?>" class="font80 details-link-list"><?php esc_html_e('Details', 'rehub-theme');?></a>	
									<?php endif; ?>	                    
				                </div>
			                </div>                                                                         
			            </div>               					

					<?php endwhile; ?>    
				</div>
			<?php endif;?>
			<?php endif; wp_reset_query(); ?> 

		<?php

		$output = ob_get_contents();
		ob_end_clean();
		return $output;	
	endif;	

}
}

//////////////////////////////////////////////////////////////////
// POPUP BUTTON
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_button_popup_funtion') ) {
function wpsm_button_popup_funtion( $atts, $content = null ) {
    extract(shortcode_atts(array(
		'color' => 'btncolor',
		'size' => 'medium',
		'icon' => 'none',
		'btn_text' => 'Show me popup',
		'max_width' => '500',
		'enable_icon' => '',
		'class' => ''    
    ), $atts));	
    $rand = rand(1, 100) ;
    $iconshow = ($enable_icon !='') ? '<span class="'.$icon.' mr5 rtlml5"></span>' : '';
    $width = ($max_width !='') ? ' style="width:'.$max_width.'px"' : '';
    $class_show = (!empty($class)) ? ' '.$class.'' : '';
    if($color == 'main'){
    	$themeclass = ' rehub-main-color-bg rehub-main-color-border';
    }
    elseif($color == 'secondary'){
    	$themeclass = ' rehub-sec-color-bg rehub-sec-color-border';
    }  
    elseif($color == 'btncolor'){
    	$themeclass = ' rehub_btn_color';
    }      
    else{
    	$themeclass = '';
    }    
	$out = '<div class="csspopup" id="custom_popup_rh_'.$rand.'"><div class="csspopupinner"'.$width.'><span class="cpopupclose cursorpointer lightgreybg rh-close-btn rh-flex-center-align rh-flex-justify-center rh-shadow5 roundborder">×</span>'.do_shortcode($content).'</div></div>';
	$out .= '<span data-popup="custom_popup_rh_'.$rand.'" class="wpsm-button csspopuptrigger wpsm-flat-btn '.$color.' '.$size.$class_show.$themeclass.'"><span class="wpsm-button-inner">'.$iconshow.$btn_text.'</span></span>';
    return $out;
}
}

//////////////////////////////////////////////////////////////////
// Countdown
//////////////////////////////////////////////////////////////////
if (! function_exists( 'wpsm_countdown' ) ) :
	function wpsm_countdown($atts, $content = null) {	
		extract(shortcode_atts(array(
				"year" => '',
				"month" => '',
				"day" => '',
				"hour" => '23',
				"minute" => '59',
		), $atts));
		
		// load scripts
		$rand_id = rand(1, 100);
		ob_start(); 		
		?>
		
		<div id="countdown_dashboard<?php echo ''.$rand_id;?>" class="countdown_dashboard" data-day="<?php echo ''.$day;?>" data-month="<?php echo ''.$month;?>" data-year="<?php echo ''.$year;?>" data-hour="<?php echo ''.$hour;?>" data-min="<?php echo ''.$minute;?>"> 			  
			<div class="dash days_dash"> <span class="dash_title">days</span>
				<div class="digit">0</div>
				<div class="digit">0</div>
			</div>
			<div class="dash hours_dash"> <span class="dash_title">hours</span>
				<div class="digit">0</div>
				<div class="digit">0</div>
			</div>
			<div class="dash minutes_dash"> <span class="dash_title">minutes</span>
				<div class="digit">0</div>
				<div class="digit">0</div>
			</div>
			<div class="dash seconds_dash"> <span class="dash_title">seconds</span>
				<div class="digit">0</div>
				<div class="digit">0</div>
			</div>
		</div>
		<!-- Countdown dashboard end -->
		<div class="clearfix"></div>		

		<?php		
		$output = ob_get_contents();
		ob_end_clean();
		return $output;	
	   
	}
endif;


//////////////////////////////////////////////////////////////////
// TITLE
//////////////////////////////////////////////////////////////////
if( !function_exists('rehub_title_function') ) {
function rehub_title_function( $atts, $content = null ) {  
    extract(shortcode_atts(array(
		'link' => '',				   
    ), $atts));
    $out = '';
    if(!empty($link)) :
	    $link_source = ($link =='affiliate') ? rehub_create_affiliate_link() : get_the_permalink() ;
		$link_target = ($link =='affiliate') ? ' target="_blank" rel="nofollow"' : '' ;
		$out .='<a href="'.$link_source.'"'.$link_target.'>';
	endif;
	$out .= get_the_title();
    if(!empty($link)) :
		$out .='</a>';
	endif;	
    return $out;
}
}

//////////////////////////////////////////////////////////////////
// AFF BUTTON
//////////////////////////////////////////////////////////////////
if( !function_exists('rehub_affbtn_function') ) {
function rehub_affbtn_function( $atts, $content = null ) { 
    extract(shortcode_atts(array(
		'btn_text' => '',
		'btn_url' => '',
		'btn_price' => '',
		'meta_btn_url' => '',
		'meta_btn_price' => '',
		'timer' => '',			   
    ), $atts));
    if ($meta_btn_url || $btn_url) :
	    $button_url = (!empty($meta_btn_url)) ? get_post_meta( get_the_ID(), esc_html($meta_btn_url), true ) : $btn_url;
		if (empty ($button_url)) {$button_url = get_the_permalink();}
		$button_price = (!empty($meta_btn_price)) ? get_post_meta( get_the_ID(), esc_html($meta_btn_price), true ) : $btn_price;    
		$out = 	'<div class="priced_block clearfix">';
		if (!empty($button_price)) :
			$out .= '<span class="rh_price_wrapper"><span class="price_count">'.esc_html($button_price).'</span></span>'; 
		endif;
		$out .='<div><a href="'.esc_url($button_url).'" class="re_track_btn btn_offer_block" target="_blank" rel="nofollow">';
		if (!empty($btn_text)) :         
			$out .= $btn_text;
		elseif (rehub_option('rehub_btn_text') !='') :
			$out .= rehub_option("rehub_btn_text");
		else :
			$out .= esc_html__("Buy this item", "rehub-theme");	
		endif;
		$out .='</a></div></div>';                	
	else :	
    	ob_start();
    	rehub_create_btn('', '', '', $timer); 
		$out = ob_get_contents();
		ob_end_clean();
	endif;	
	return $out;

}
}

//////////////////////////////////////////////////////////////////
// EXCERPT
//////////////////////////////////////////////////////////////////
if( !function_exists('rehub_exerpt_function') ) {
function rehub_exerpt_function( $atts, $content = null ) { 
    extract(shortcode_atts(array(
		'length' => '120',
		'reviewtext' => '',
		'reviewheading'=> '',
		'reviewpros'=>'',
		'reviewcons'=>'',
		'reviewcriterias' => '',
    ), $atts));
    global $post;
    $out = '';
    if ($reviewtext =='1'){
		$reviewtext = get_post_meta((int)$post->ID, '_review_post_summary_text', true);		
    	if(!$reviewtext){
			$review_post = rehub_get_review_data();
			$reviewtext = (!empty($review_post['review_post_summary_text'])) ? $review_post['review_post_summary_text'] : '';	
		}
		if($length){
			$out .= kama_excerpt('maxchar='.$length.'&text='.$reviewtext.'&echo=false');
		}else{
			$out .= $reviewtext;
		}
    }
    elseif ($reviewheading =='1') {
		$reviewtext = get_post_meta((int)$post->ID, '_review_heading', true);		
    	if(!$reviewtext){
			$review_post = rehub_get_review_data();
			$reviewtext = (!empty($review_post['review_post_heading'])) ? $review_post['review_post_heading'] : '';	
		}
		$out .= $reviewtext;   	
    }
    elseif($reviewcriterias =='editor') {
    	$firstcriteria = '';
		$thecriteria = get_post_meta((int)$post->ID, '_review_post_criteria', true);
		if(empty($thecriteria)){
			$review_post = rehub_get_review_data();
			$thecriteria = (!empty($review_post['review_post_criteria'])) ? $review_post['review_post_criteria'] : '';
		}		
		if(!empty($thecriteria[0]['review_post_name'])){
			$firstcriteria = $thecriteria[0]['review_post_name'];
		}		 
		if($firstcriteria){
			$out .= '<div class="cmp_crt_block"><div class="rate_bar_wrap pt0 pr0 pl0 pb0">';
				$out .= '<div class="review-criteria">';
					foreach ($thecriteria as $criteria){
						$perc_criteria = $criteria['review_post_score']*10;
						$out .= '<div class="flowhidden font90 lineheight15 position-relative pr15 text-left-align pb5 rtltext-right-align"><div class="floatleft mr10">'.$criteria["review_post_name"].'</div><div class="abdposright fontbold">'.$criteria["review_post_score"].'</div></div>';
						$out .= '<div class="rate-bar clearfix mb10" data-percent="'.$perc_criteria.'%">';
						$out .= '<div class="rate-bar-bar r_score_'.round($criteria["review_post_score"]).'" style="width:'.$perc_criteria.'%"></div>';
						$out .= '</div>';
					}
				$out .= '</div>';
			$out .= '</div></div>';  
		}
	}  
	elseif ($reviewpros){
		$prosvalues = get_post_meta($post->ID, '_review_post_pros_text', true);
		if(empty($prosvalues)){
			$review_post = rehub_get_review_data();
			$prosvalues = (!empty($review_post['review_post_pros_text'])) ? $review_post['review_post_pros_text'] : '';
		}
		if(!empty($prosvalues))	{
	    	$prosvalues = explode(PHP_EOL, $prosvalues);	    	
		    $out .= '<div class="wpsm_pros"><ul>';
		    foreach ($prosvalues as $prosvalue) {
		    	$out .= '<li>'.$prosvalue.'</li>';
		    }
		    $out .= '</ul></div>';	
		}
	}
	elseif ($reviewcons =='1'){
		$consvalues = get_post_meta($post->ID, '_review_post_cons_text', true);
		if(empty($consvalues)){
			$review_post = rehub_get_review_data();
			$consvalues = (!empty($review_post['review_post_cons_text'])) ? $review_post['review_post_cons_text'] : '';
		}		
		if(!empty($consvalues))	{		
		    $consvalues = explode(PHP_EOL, $consvalues);	    
		    $out .= '<div class="wpsm_cons"><ul>';
		    foreach ($consvalues as $consvalue) {
		    	$out .= '<li>'.$consvalue.'</li>';
		    }
		    $out .= '</ul></div>';
		}
	}		         
    else{
		$out .= kama_excerpt('maxchar='.$length.'&echo=false');
    }
	return $out; 
}
}

//////////////////////////////////////////////////////////////////
// Review and ads shortcode and functions
//////////////////////////////////////////////////////////////////

if( !function_exists('rehub_shortcode_review') ) {
function rehub_shortcode_review( $atts, $content = null ) {	
	ob_start();
	rehub_get_review();
	$output = ob_get_contents();
	ob_end_clean();
	return $output; 
}
}


if( !function_exists('rehub_shortcode_quick_offer') ) {
function rehub_shortcode_quick_offer( $atts, $content = null ) {
        $atts = shortcode_atts(
			array(
				'id' => '',
			), $atts);	
		ob_start(); 
		rehub_quick_offer($atts['id']);
		$output = ob_get_contents();
		ob_end_clean();
		return $output; 
}
}

if(!function_exists('wpsm_shortcode_boxad')) {
function wpsm_shortcode_boxad( $atts, $content = null ) {
        $atts = shortcode_atts(
			array(
				'float' => 'none',
			), $atts);

	$out = '<div class="wpsm_boxad mediad align'.$atts['float'].'">
			' .rehub_option("rehub_shortcode_ads"). '
			</div>';
    return $out;
}
}

if(!function_exists('wpsm_shortcode_boxad2')) {
function wpsm_shortcode_boxad2( $atts, $content = null ) {
        $atts = shortcode_atts(
			array(
				'float' => 'none',
			), $atts);

	$out = '<div class="wpsm_boxad mediad align'.$atts['float'].'">
			' .rehub_option("rehub_shortcode_ads_2"). '
			</div>';
    return $out;
}
}

//////////////////////////////////////////////////////////////////
// Specification for meta filter plugin
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_specification_shortcode') ) {
function wpsm_specification_shortcode($atts, $content = null ) {
extract(shortcode_atts(array(
	'id' => '',
	'title' => '',
	'product_id' => '',
), $atts));
if(class_exists('Woocommerce')){
	if(!$product_id){
		global $post;
		$product_id = $post->ID;
	}
	$the_product = wc_get_product( $product_id );
	if(!empty($the_product)){
		ob_start();
		echo '<div class="woocommerce">';
		wc_display_product_attributes( $the_product );
		echo '</div>';
		$output = ob_get_contents();
		ob_end_clean();
		return $output;		
	}
}elseif(class_exists('MetaDataFilter')){
	global $post;
	if(!isset($atts['id']) || $atts['id'] =='') {
		$id = get_the_ID();
	}
	$title_label = (!empty($atts['title'])) ? $atts['title'] : esc_html__('Specification', 'rehub-theme');

	ob_start();
	echo '<div class="rehub_specification"><div class="title_specification">'.$title_label.'</div>';
	MetaDataFilterPage::draw_single_page_items($id, false);
	echo '</div>';
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}

}
}

//////////////////////////////////////////////////////////////////
// Top rating shortcode
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_toprating_shortcode') ) {
function wpsm_toprating_shortcode( $atts, $content = null ) {
	
	extract(shortcode_atts(array(
			'id' => '',
			'postid' => '',
			'full_width' => '0',
			'module_desc' => '',
			'module_desc_fields' => '',
			'title_tag' => 'h3'
		), $atts));
	$module_pagination = '';	
	if(isset($atts['id']) || isset($atts['postid'])):

		if(!empty($atts['id'])){
			$toppost = get_post($atts['id']);
			$module_cats = get_post_meta( $toppost->ID, 'top_review_cat', true ); 
	    	$module_tag = get_post_meta( $toppost->ID, 'top_review_tag', true ); 
	    	$module_fetch = get_post_meta( $toppost->ID, 'top_review_fetch', true ); 
	    	$module_ids = get_post_meta( $toppost->ID, 'manual_ids', true ); 
	    	$order_choose = get_post_meta( $toppost->ID, 'top_review_choose', true ); 
	    	$module_desc = get_post_meta( $toppost->ID, 'top_review_desc', true );
	    	$module_desc_fields = get_post_meta( $toppost->ID, 'top_review_custom_fields', true );
	    	$rating_circle = get_post_meta( $toppost->ID, 'top_review_circle', true );
	    	$module_pagination = get_post_meta( $toppost->ID, 'top_review_pagination', true );
	    	$module_field_sorting = get_post_meta( $toppost->ID, 'top_review_field_sort', true );
	    	$module_order = get_post_meta( $toppost->ID, 'top_review_order', true );   
	    	$module_custom_post = get_post_meta( $toppost->ID, 'top_review_custompost', true );
		    $catalog_tax = get_post_meta( $toppost->ID, 'catalog_tax', true );
		    $catalog_tax_slug = get_post_meta( $toppost->ID, 'catalog_tax_slug', true );
		    $catalog_tax_sec = get_post_meta( $toppost->ID, 'catalog_tax_sec', true );
		    $catalog_tax_slug_sec = get_post_meta( $toppost->ID, 'catalog_tax_slug_sec', true );
	    	if ($module_fetch ==''){$module_fetch = '10';}; 
	    	if ($module_desc ==''){$module_desc = 'post';};
	    	if ($rating_circle ==''){$rating_circle = '1';};
		}
		elseif(!empty($atts['postid'])){
			$module_cats = $module_tag = ''; 
	    	$module_fetch = 1; 
	    	$module_ids = explode(',', $atts['postid']); 
	    	$order_choose = 'manual_choose'; 
	    	$rating_circle = 1;
	    	$module_field_sorting = '';
	    	$module_order = '';    				
		}
		ob_start(); 

    	?>
            <div class="clearfix"></div>
            <?php  if ( get_query_var('paged') ) { $paged = get_query_var('paged'); } else if ( get_query_var('page') ) {$paged = get_query_var('page'); } else {$paged = 1; }  ?>
            <?php if ($order_choose == 'cat_choose') :?>
                <?php $args = array( 
                    'cat' => $module_cats, 
                    'tag' => $module_tag, 
                    'posts_per_page' => $module_fetch, 
                    'paged' => $paged, 
                    'post_status' => 'publish', 
                    'ignore_sticky_posts' => 1, 
                    'meta_key' => 'rehub_review_overall_score', 
                    'orderby' => 'meta_value_num',
                );
                ?> 
                <?php if(!empty ($module_field_sorting)) {$args['meta_key'] = $module_field_sorting;} ?>
                <?php if($module_order =='asc') {$args['order'] = 'ASC';} ?>	                
        	<?php elseif ($order_choose == 'manual_choose' && $module_ids !='') :?>
                <?php $args = array( 
                    'post_status' => 'publish', 
                    'ignore_sticky_posts' => 1,
                    'posts_per_page'=> -1, 
                    'orderby' => 'post__in',
                    'post_type' => 'any',
                    'post__in' => $module_ids
                );
                ?>
            <?php elseif ($order_choose == 'custom_post') :?>
                <?php $args = array(  
                    'posts_per_page' => $module_fetch, 
                    'paged' => $paged, 
                    'post_status' => 'publish', 
                    'ignore_sticky_posts' => 1,
                    'post_type' => $module_custom_post, 
                );
                ?> 
                <?php if (!empty ($catalog_tax_slug) && !empty ($catalog_tax)) : ?>
                    <?php $args['tax_query'] = array (
                        array(
                            'taxonomy' => $catalog_tax,
                            'field'    => 'slug',
                            'terms'    => $catalog_tax_slug,
                        ),
                    );?>
                <?php endif ?>
                <?php if (!empty ($catalog_tax_slug_sec) && !empty ($catalog_tax_sec)) : ?>
                    <?php 
                        $args['tax_query']['relation'] = 'AND';
                        $args['tax_query'][] = 
                        array(
                            'taxonomy' => $catalog_tax_sec,
                            'field'    => 'slug',
                            'terms'    => $catalog_tax_slug_sec,
                        );
                    ;?>
                <?php endif ?>                    
                <?php if(!empty ($module_field_sorting)) {$args['meta_key'] = $module_field_sorting; $args['orderby'] = 'meta_value_num';} ?>
                <?php if($module_order =='asc') {$args['order'] = 'ASC';} ?>                
        	<?php else :?>
                <?php $args = array( 
                    'posts_per_page' => $module_fetch, 
                    'paged' => $paged, 
                    'post_status' => 'publish', 
                    'ignore_sticky_posts' => 1, 
                    'meta_key' => 'rehub_review_overall_score', 
                    'orderby' => 'meta_value_num',
                );
                ?>
                <?php if(!empty ($module_field_sorting)) {$args['meta_key'] = $module_field_sorting;} ?>
                <?php if($module_order =='asc') {$args['order'] = 'ASC';} ?>	                             		
        	<?php endif ;?>	

	        <?php 
			    $args = apply_filters('rh_module_args_query', $args);
			    $wp_query = new WP_Query($args);
			    do_action('rh_after_module_args_query', $wp_query);
	        ?>
            <?php $i=0; if ($wp_query->have_posts()) :?>
            <div class="rh_list_builder rh-shadow4 disablemobileshadow mb30">
            <?php while ($wp_query->have_posts()) : $wp_query->the_post(); global $post; $i ++?>
				<?php if(get_post_type($post->ID) == 'product'):?>
					<?php $isproduct = true;global $product;?>
				<?php else:?>
					<?php $isproduct = false;?>
				<?php endif;?>
            	<?php $disclaimer = get_post_meta($post->ID, 'rehub_offer_disclaimer', true);?>     
                <div class="top_table_list_item border-lightgrey whitebg"> 
                <div class="rh-flex-eq-height mobileblockdisplay">                   
		            <div class="listbuild_image border-right listitem_column text-center rh-flex-center-align position-relative pt15 pb15 pr20 pl20">
	            		<div class="colored_rate_bar abdposright mt15 mr15">
				        	<?php $reviewscore = wpsm_reviewbox(array('compact'=>'mediumcircle', 'id'=> $post->ID));?>
				        	<?php echo ''.$reviewscore;?>
				        </div>
		                <figure class="position-relative margincenter width-150">
		                    <a class="img-centered-flex rh-flex-center-align rh-flex-justify-center" href="<?php the_permalink();?>">
		                    <?php 
		                    $showimg = new WPSM_image_resizer();
		                    $showimg->use_thumb = true; 
		                    $showimg->no_thumb = get_template_directory_uri() . '/images/default/noimage_200_140.png' ;
		                    $showimg->height = '126';  
		                    $showimg->width = '200'; 
		                    $showimg->lazy = false;                  
		                    ?>        
		                    <?php $showimg->show_resized_image(); ?> 
		                    </a> 
		                </figure>                              
		            </div>                            
	                <div class="rh-flex-grow1 border-right listitem_title listitem_column pt15 pb15 pr20 pl20">
	                    <<?php echo ''.$title_tag?> class="font120 mb10 mt0 list_heading fontbold">
	                    	<?php 
	                    		$offer_title  = get_post_meta( $post->ID, 'rehub_offer_name', true );
                				if ( empty( $offer_title ) ) {
									$offer_title = get_the_title( $post->ID );
								}
	                    	?>
	                    	<a href="<?php the_permalink();?>">
	                    		<?php echo ''.$offer_title;?>
	                    	</a>
	                    	<span class="blockstyle"><?php echo re_badge_create('labelsmall'); ?></span></<?php echo ''.$title_tag?>>
	                    <div class="lineheight20">
							<?php if ($full_width == 1):?>
								<?php kama_excerpt('maxchar=250'); ?>                        			
							<?php else:?>
								<?php kama_excerpt('maxchar=120'); ?> 
							<?php endif;?>
	                    </div>
						<?php if($isproduct):?>
							<div class="woo-button-actions-area tabletblockdisplay pt5 border-top mt15">
								<?php $wishlistadd = esc_html__('Add to wishlist', 'rehub-theme');?>
								<?php $wishlistadded = esc_html__('Added to wishlist', 'rehub-theme');?>
								<?php $wishlistremoved = esc_html__('Removed from wishlist', 'rehub-theme');?>
								<?php echo RH_get_wishlist($post->ID, $wishlistadd, $wishlistadded, $wishlistremoved);?>
								<?php if(rehub_option('compare_page') || rehub_option('compare_multicats_textarea')) :?>           
									<?php 
										$cmp_btn_args = array(); 
										$cmp_btn_args['class']= 'rhwoosinglecompare';
										if(rehub_option('compare_woo_cats') != '') {
											$cmp_btn_args['woocats'] = esc_html(rehub_option('compare_woo_cats'));
										}
									?>                                                  
									<?php echo wpsm_comparison_button($cmp_btn_args); ?> 
								<?php endif;?>                      
							</div> 
						<?php endif;?>
	                </div>
	                <div class="listbuild_btn listitem_column text-center rh-flex-center-align pt15 pb15 pr20 pl20 rh-flex-justify-center">
	                	<div class="width-100p">
		            	<?php if($isproduct):?>
							<?php if ($product->get_price() !='') : ?>
								<?php echo '<span class="font110 fontbold mb10 lineheight20 rehub-main-color blockstyle text-center"><span class="price">'.$product->get_price_html().'</span></span>';?>
							<?php endif ;?>
							<?php if ( $product->add_to_cart_url() !='') : ?>
								<div class="priced_block clearbox">
			                    <?php  echo apply_filters( 'woocommerce_loop_add_to_cart_link',
			                        sprintf( '<a href="%s" data-product_id="%s" data-product_sku="%s" class="re_track_btn woo_loop_btn btn_offer_block %s %s product_type_%s"%s %s>%s</a>',
			                        esc_url( $product->add_to_cart_url() ),
			                        esc_attr( $product->get_id() ),
			                        esc_attr( $product->get_sku() ),
			                        $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
			                        $product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : '',
			                        esc_attr( $product->get_type() ),
			                        $product->get_type() =='external' ? ' target="_blank"' : '',
			                        $product->get_type() =='external' ? ' rel="nofollow"' : '',
			                        esc_html( $product->add_to_cart_text() )
			                        ),
			                    $product );?> 
			                	</div>
			                <?php endif; ?> 	            		
		            	<?php else:?>	
		            		<?php rehub_generate_offerbtn('wrapperclass=block_btnblock mobile_block_btnclock mb5');?>
		            	<?php endif;?>  
		            	<?php if(!$isproduct):?>	
							<a href="<?php the_permalink();?>" class="read_full font85"><?php if(rehub_option('rehub_review_text') !='') :?><?php echo rehub_option('rehub_review_text') ; ?><?php else :?><?php esc_html_e('Read full review', 'rehub-theme'); ?><?php endif ;?></a>
		            	<?php endif;?>                
	                	</div>
	                </div>
	            </div>
                </div>
				<?php if($disclaimer):?>
				    <div class="rev_disclaimer lightbluebg font70 lineheight15 pt10 pb10 pl15 pr15 flowhidden"><?php echo wp_kses($disclaimer, 'post');?></div>
				<?php endif;?> 
            <?php endwhile; ?>
            </div>
            <?php if ($module_pagination =='1') :?><div class="pagination"><?php rehub_pagination();?></div><?php endif ;?>            
            <?php wp_reset_query(); ?>
            <?php else: ?><?php esc_html_e('No posts for this criteria.', 'rehub-theme'); ?>
            <?php endif; ?>

    	<?php 
		$output = ob_get_contents();
		ob_end_clean();
		return $output;   
	endif;	

}
}

//////////////////////////////////////////////////////////////////
// Top table shortcode
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_toptable_shortcode') ) {
function wpsm_toptable_shortcode( $atts, $content = null ) {
	
	extract(shortcode_atts(array(
			'id' => '',
			'full_width' => '0',
			'post_ids' => ''
		), $atts));
		
	if(isset($atts['id']) && $atts['id']):

		$toppost = get_post($atts['id']);
		$module_cats = get_post_meta( $toppost->ID, 'top_review_cat', true );
		$disable_filters = get_post_meta( $toppost->ID, 'top_review_filter_disable', true ); 
    	$module_tag = get_post_meta( $toppost->ID, 'top_review_tag', true ); 
    	$module_fetch = intval(get_post_meta( $toppost->ID, 'top_review_fetch', true ));  
    	$module_ids = get_post_meta( $toppost->ID, 'manual_ids', true ); 
    	$order_choose = get_post_meta( $toppost->ID, 'top_review_choose', true ); 
	    $module_custom_post = get_post_meta( $toppost->ID, 'top_review_custompost', true );
	    $catalog_tax = get_post_meta( $toppost->ID, 'catalog_tax', true );
	    $catalog_tax_slug = get_post_meta( $toppost->ID, 'catalog_tax_slug', true ); 
    	$catalog_tax_sec = get_post_meta( $toppost->ID, 'catalog_tax_sec', true );
    	$catalog_tax_slug_sec = get_post_meta( $toppost->ID, 'catalog_tax_slug_sec', true );  
    	$image_width = get_post_meta( $toppost->ID, 'image_width', true );    
    	$image_height = get_post_meta( $toppost->ID, 'image_height', true ); 
    	$disable_crop = get_post_meta( $toppost->ID, 'disable_crop', true ); 	       	
    	$module_field_sorting = get_post_meta( $toppost->ID, 'top_review_field_sort', true );
    	$module_order = get_post_meta( $toppost->ID, 'top_review_order', true );
	    $first_column_enable = get_post_meta( $toppost->ID, 'first_column_enable', true );
	    $first_column_rank = get_post_meta( $toppost->ID, 'first_column_rank', true ); 
	    $last_column_enable = get_post_meta( $toppost->ID, 'last_column_enable', true );
	    $first_column_name = (get_post_meta( $toppost->ID, 'first_column_name', true ) !='') ? esc_html(get_post_meta( $toppost->ID, 'first_column_name', true )) : esc_html__('Product', 'rehub-theme') ;
	    $last_column_name = (get_post_meta( $toppost->ID, 'last_column_name', true ) !='') ? esc_html(get_post_meta( $toppost->ID, 'last_column_name', true )) : '' ;
	    $affiliate_link = get_post_meta( $toppost->ID, 'first_column_link', true );
	    $rows = get_post_meta( $toppost->ID, 'columncontents', true ); //Get the rows     	    	
    	if ($module_fetch ==''){$module_fetch = '10';}; 
		
		ob_start(); 
    	?>
        <div class="clearfix"></div>
        <?php 
            if ( get_query_var('paged') ) { 
                $paged = get_query_var('paged'); 
            } 
            else if ( get_query_var('page') ) {
                $paged = get_query_var('page'); 
            } 
            else {
                $paged = 1; 
            }        
        ?> 
        <?php if($post_ids) :?>

        	<?php $module_ids = array_map( 'trim', explode( ",", $post_ids ) );?>
        	<?php $order_choose = 'manual_choose';?>

        <?php endif;?>
               
        <?php if ($order_choose == 'cat_choose') :?>
            <?php $args = array( 
                'cat' => $module_cats, 
                'tag' => $module_tag, 
                'posts_per_page' => $module_fetch, 
                'paged' => $paged,  
                'post_status' => 'publish', 
                'ignore_sticky_posts' => 1, 
            );
            ?> 
            <?php if(!empty ($module_field_sorting)) {$args['meta_key'] = $module_field_sorting; $args['orderby'] = 'meta_value_num';} ?>
            <?php if($module_order =='asc') {$args['order'] = 'ASC';} ?>	                
    	<?php elseif ($order_choose == 'manual_choose' && !empty($module_ids)) :?>
            <?php $args = array( 
                'post_status' => 'publish', 
                'ignore_sticky_posts' => 1,
                'posts_per_page'=> -1, 
                'orderby' => 'post__in',
                'post__in' => $module_ids,
                'post_type' => 'any'

            );
            ?>
	    <?php elseif ($order_choose == 'custom_post') :?>
	        <?php $args = array(  
	            'posts_per_page' => $module_fetch,  
	            'post_status' => 'publish', 
	            'ignore_sticky_posts' => 1,
	            'paged' => $paged, 
	            'post_type' => $module_custom_post, 
	        );
	        ?> 
	        <?php if (!empty ($catalog_tax_slug) && !empty ($catalog_tax)) : ?>
	            <?php $args['tax_query'] = array (
	                array(
	                    'taxonomy' => $catalog_tax,
	                    'field'    => 'slug',
	                    'terms'    => $catalog_tax_slug,
	                ),
	            );?>
	        <?php endif ?>
            <?php if (!empty ($catalog_tax_slug_sec) && !empty ($catalog_tax_sec)) : ?>
                <?php 
                    $args['tax_query']['relation'] = 'AND';
                    $args['tax_query'][] = 
                    array(
                        'taxonomy' => $catalog_tax_sec,
                        'field'    => 'slug',
                        'terms'    => $catalog_tax_slug_sec,
                    );
                ;?>
            <?php endif ?> 	         
            <?php if(!empty ($module_field_sorting)) {$args['meta_key'] = $module_field_sorting; $args['orderby'] = 'meta_value_num';} ?>
            <?php if($module_order =='asc') {$args['order'] = 'ASC';} ?>	                    
    	<?php else :?>
            <?php $args = array( 
                'posts_per_page' => $module_fetch, 
                'paged' => $paged,
                'post_status' => 'publish', 
                'ignore_sticky_posts' => 1, 
            );
            ?>
            <?php if(!empty ($module_field_sorting)) {$args['meta_key'] = $module_field_sorting; $args['orderby'] = 'meta_value_num';} ?>
            <?php if($module_order =='asc') {$args['order'] = 'ASC';} ?>	                             		
    	<?php endif ;?>	

        <?php 
		    $args = apply_filters('rh_module_args_query', $args);
		    $wp_query = new WP_Query($args);
		    do_action('rh_after_module_args_query', $wp_query);
        ?>
        <?php $i=0; if ($wp_query->have_posts()) :?>
        <?php wp_enqueue_script('tablesorter'); wp_enqueue_style('tabletoggle'); ?>
        <?php $sortable_col = ($disable_filters !=1) ? ' data-tablesaw-sortable-col' : '';?>
        <?php $sortable_switch = ($disable_filters !=1) ? ' data-tablesaw-sortable-switch' : '';?>
        <div class="rh-top-table">
            <?php if ($image_width || $image_height):?>
                <style scoped>.rh-top-table .top_rating_item figure > a img{max-height: <?php echo (int)$image_height;?>px; max-width: <?php echo (int)$image_width;?>px;}.rh-top-table .top_rating_item figure > a, .rh-top-table .top_rating_item figure{height: auto;width: auto; border:none;}</style>
            <?php endif;?>        
	        <table data-tablesaw-sortable<?php echo ''.$sortable_switch; ?> class="tablesaw top_table_block<?php if ($full_width =='1') : ?> full_width_rating<?php else :?> with_sidebar_rating<?php endif;?> tablesorter" cellspacing="0">
	            <thead> 
	            <tr class="top_rating_heading">
	                <?php if ($first_column_enable):?><th class="product_col_name" data-tablesaw-priority="persist"><?php echo ''.$first_column_name; ?></th><?php endif;?>
	                <?php if (!empty ($rows)) {
	                    $nameid=0;                       
	                    foreach ($rows as $row) {                       
	                    $col_name = $row['column_name'];
	                    echo '<th class="col_name"'.$sortable_col.' data-tablesaw-priority="1">'.esc_html($col_name).'</th>';
	                    $nameid++;
	                    } 
	                }
	                ?>
	                <?php if ($last_column_enable):?><th class="buttons_col_name" <?php echo ''.$sortable_col; ?> data-tablesaw-priority="1"><?php echo ''.$last_column_name; ?></th><?php endif;?>                      
	            </tr>
	            </thead>
	            <tbody>
	        <?php while ($wp_query->have_posts()) : $wp_query->the_post(); $i ++?>     
	            <tr class="top_rating_item" id='rank_<?php echo (int)$i?>'>
	                <?php if ($first_column_enable):?>
	                    <td class="product_image_col"><?php echo re_badge_create('tablelabel'); ?>
	                        <figure>
	                            <?php if (!is_paged() && $first_column_rank) :?><span class="rank_count"><?php if (($i) == '1') :?><i class="rhicon rhi-trophy-alt"></i><?php else:?><?php echo (int)$i?><?php endif ?></span><?php endif ?>                        
	                            <?php $link_on_thumb = ($affiliate_link =='1') ? rehub_create_affiliate_link() : get_the_permalink(); ?>
	                            <?php $link_on_thumb_target = ($affiliate_link =='1') ? ' class="btn_offer_block" target="_blank" rel="nofollow"' : '' ; ?>
	                            <a href="<?php echo esc_url($link_on_thumb);?>" <?php echo ''.$link_on_thumb_target;?>>
	                                <?php 
		                                $showimg = new WPSM_image_resizer();
		                                $showimg->use_thumb = true;
		                                if(!$image_height) $image_height = 120;
		                                $showimg->height =  $image_height;
		                                if($image_width) {
		                                    $showimg->width =  $image_width;
		                                }
		                                if($disable_crop) {
		                                    $showimg->crop = false;
		                                }else{
		                                    $showimg->crop = true;
		                                }
		                                $showimg->show_resized_image();                                    
	                                ?>  
	                            </a>
	                        </figure>
	                    </td>
	                <?php endif;?>
	                <?php 
	                $pbid=0; 
	                if (!empty ($rows)) {
	                                          
	                    foreach ($rows as $row) {
	                    $centered = (!empty($row['column_center'])) ? ' centered_content' : '' ;
	                    echo '<td class="column_'.$pbid.' column_content'.$centered.'">';
	                    echo do_shortcode(wp_kses_post($row['column_html']));                       
	                    $element = $row['column_type'];
	                        if ($element == 'meta_value') {
	                            include(rh_locate_template('inc/top/metacolumn.php'));
	                        } else if ($element == 'review_function') {
	                            include(rh_locate_template('inc/top/reviewcolumn.php'));
	                        } else if ($element == 'taxonomy_value') {
	                            include(rh_locate_template('inc/top/taxonomyrow.php'));                            
	                        } else if ($element == 'user_review_function') {
	                            include(rh_locate_template('inc/top/userreviewcolumn.php')); 
	                        } else if ($element == 'static_user_review_function') {
	                            include(rh_locate_template('inc/top/staticuserreviewcolumn.php'));
	                        } else if ($element == 'woo_review') {
	                            include(rh_locate_template('inc/top/wooreviewrow.php'));
	                        } else if ($element == 'woo_btn') {
	                            include(rh_locate_template('inc/top/woobtn.php')); 
	                        } else if ($element == 'woo_vendor') {
	                            include(rh_locate_template('inc/top/woovendor.php')); 
	                        } else if ($element == 'woo_attribute') {
	                            include(rh_locate_template('inc/top/wooattribute.php'));                             
	                        } else {
	                            
	                        };
	                    echo '</td>';
	                    $pbid++;
	                    } 
	                }
	                ?>
	                <?php if ($last_column_enable):?>
	                    <td class="buttons_col">
	                        <?php if ('product' == get_post_type(get_the_ID())):?>
	                            <?php include(rh_locate_template('inc/top/woobtn.php'));?>
	                        <?php else:?>
	                    	   <?php rehub_generate_offerbtn('wrapperclass=block_btnblock mobile_block_btnclock mb5');?>
	                        <?php endif ;?>                                
	                    </td>
	                <?php endif ;?>
	            </tr>
	        <?php endwhile; ?>
		        </tbody>
		    </table>
		</div>
        <?php else: ?><?php esc_html_e('No posts for this criteria.', 'rehub-theme'); ?>
        <?php endif; ?>
        <?php wp_reset_query(); ?>

    	<?php 
		$output = ob_get_contents();
		ob_end_clean();
		return $output;   
	endif;	

}
}

//////////////////////////////////////////////////////////////////
// Top charts shortcode
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_topcharts_shortcode') ) {
function wpsm_topcharts_shortcode( $atts, $content = null ) {
	
	extract(shortcode_atts(array(
			'id' => '',
			'postids'=> '',
			'topcontent'=> ''
		), $atts));
		
	if(isset($atts['id']) && $atts['id']):
		$topchart = get_post($atts['id']);
	    $type_chart = get_post_meta( $topchart->ID, 'top_chart_type', true );
	    $ids_chart = get_post_meta( $topchart->ID, 'top_chart_ids', true );
	    if($ids_chart) {$module_ids = explode(',', $ids_chart);}
	    $module_width = get_post_meta( $topchart->ID, 'top_chart_width', true );     
	    $rows = get_post_meta( $topchart->ID, 'columncontents', true ); //Get the rows 
	    if($postids){
	    	$compareids = explode(',', $postids);
	    }else if(get_query_var('compareids')){
	    	$compareids = explode(',', get_query_var('compareids'));    
	    }else{
	    	$compareids = '';
	    }    		
		ob_start(); 
    	?>
        <?php if ($compareids !='') :?>
            <?php $args = array( 
                'post_status' => 'publish', 
                'ignore_sticky_posts' => 1, 
                'orderby' => 'post__in',
                'post__in' => $compareids,
                'posts_per_page'=> -1,

            );
            ?>
    	<?php elseif (!empty($module_ids)) :?>
            <?php $args = array( 
                'post_status' => 'publish', 
                'ignore_sticky_posts' => 1, 
                'orderby' => 'post__in',
                'post__in' => $module_ids,
                'posts_per_page'=> -1,

            );
            ?>
    	<?php else :?>
            <?php $args = array( 
                'posts_per_page' => 5,  
                'post_status' => 'publish', 
                'ignore_sticky_posts' => 1, 
            );
            ?>                                		
    	<?php endif ;?>
        <?php if (post_type_exists( $type_chart )) {$args['post_type'] = $type_chart;} ?>	

        <?php 
	    $args = apply_filters('rh_module_args_query', $args);
	    $wp_query = new WP_Query($args);
	    do_action('rh_after_module_args_query', $wp_query);   
        $i=0; if ($wp_query->have_posts()) :?>
        <?php wp_enqueue_script('carouFredSel'); wp_enqueue_script('touchswipe'); wp_enqueue_script('rehubtablechart'); wp_enqueue_style('rhchartbuilder'); ?>                                       
        <div class="top_chart table_view_charts loading">
            <div class="top_chart_controls">
                <a href="/" class="controls prev"></a>
                <div class="top_chart_pagination"></div>
                <a href="/" class="controls next"></a>
            </div>
            <div class="top_chart_first">
                <ul>
                    <?php if (!empty ($rows)) {
                        $nameid=0;                       
                        foreach ($rows as $row) {   
                        $element_type = $row['column_type']; 
                        $first_col_value = '<div';  
                        if (isset ($row['sticky_header']) && $row['sticky_header'] == 1) {$first_col_value .= ' class="sticky-cell"';} 
                        $first_col_value .= '>'.do_shortcode($row["column_name"]).'';
                        if (isset ($row['enable_diff']) && $row['enable_diff'] == 1) {$first_col_value .= '<br /><label class="diff-label"><input class="re-compare-show-diff" name="re-compare-show-diff" type="checkbox" />'.__('Show only differences', 'rehub-theme').'</label>';}                                                              
                        $first_col_value .= '</div>';                
                        echo '<li class="row_chart_'.$nameid.' '.$element_type.'_row_chart">'.$first_col_value.'</li>';
                        $nameid++;
                        } 
                    }
                    ?>
                </ul>
            </div>
        	<div class="top_chart_wrap"><div class="top_chart_carousel">
		        <?php while ($wp_query->have_posts()) : $wp_query->the_post(); $i ++?>     
		            <div class="<?php echo re_badge_create('class'); ?> top_rating_item top_chart_item compare-item-<?php echo get_the_ID();?>" id='rank_<?php echo (int)$i?>' data-compareid="<?php echo get_the_ID();?>">
		                <ul>
		                <?php 
		                $pbid=0;
		                if (!empty ($rows)) {
		                                           
		                    foreach ($rows as $row) {                                                     
		                    $element = $row['column_type'];
		                        echo '<li class="row_chart_'.$pbid.' '.$element.'_row_chart">';
		                        if ($element == 'meta_value') {                                
		                            include(rh_locate_template('inc/top/metarow.php'));
		                        } else if ($element == 'image') {
		                            include(rh_locate_template('inc/top/imagerow.php'));
                                } else if ($element == 'imagefull') {
                                        include(rh_locate_template('inc/top/imagefullrow.php'));
		                        } else if ($element == 'title') {
		                            include(rh_locate_template('inc/top/titlerow.php'));   
		                        } else if ($element == 'taxonomy_value') {
		                            include(rh_locate_template('inc/top/taxonomyrow.php'));     
		                        } else if ($element == 'affiliate_btn') {
		                            include(rh_locate_template('inc/top/btnrow.php'));
		                        } else if ($element == 'review_link') {
		                            include(rh_locate_template('inc/top/reviewlinkrow.php'));
                                } else if ($element == 'review_criterias') {
                                    include(rh_locate_template('inc/top/reviewcriterias.php'));
		                        } else if ($element == 'review_function') {
		                            include(rh_locate_template('inc/top/reviewrow.php'));          
		                        } else if ($element == 'user_review_function') {
		                            include(rh_locate_template('inc/top/userreviewcolumn.php'));
                                } else if ($element == 'static_user_review_function') {
                                    include(rh_locate_template('inc/top/staticuserreviewcolumn.php'));
                                } else if ($element == 'woo_review') {
                                    include(rh_locate_template('inc/top/wooreviewrow.php'));
                                } else if ($element == 'woo_btn') {
                                    include(rh_locate_template('inc/top/woobtn.php')); 
                                } else if ($element == 'woo_vendor') {
                                    include(rh_locate_template('inc/top/woovendor.php')); 
                                } else if ($element == 'excerpt') {
                                    include(rh_locate_template('inc/top/excerpt.php')); 
                                } else if ($element == 'woo_attribute') {
                                    include(rh_locate_template('inc/top/wooattribute.php'));                
                                } else if ($element == 'shortcode') {
                                    $shortcodevalue = (isset($row['shortcode_value'])) ? $row['shortcode_value'] : '';
                                    echo do_shortcode(wp_kses_post($shortcodevalue));                                     
		                        } else {   
		                        };
		                        echo '</li>';
		                    $pbid++;
		                    } 
		                }
		                ?>
		            </ul>
		            </div>
		        <?php endwhile; ?>
        	</div></div>
        	<span class="top_chart_row_found" data-rowcount="<?php echo (int)$pbid;?>"></span>
        </div>
        <?php else: ?><?php esc_html_e('No posts for this criteria.', 'rehub-theme'); ?>
        <?php endif; ?>
        <?php wp_reset_query(); ?>

    	<?php 
		$output = ob_get_contents();
		ob_end_clean();
		return $output;   
	endif;	

}
}


//////////////////////////////////////////////////////////////////
// Woo charts shortcode
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_woocharts_shortcode') ) {
function wpsm_woocharts_shortcode( $atts, $content = null ) {
	
	extract(shortcode_atts(array(
			'ids' => '',
			'searchbtn' => 'yes',
            'posttype' => 'product', // comma separated post types
            'taxonomy' => 'product_cat',
            'terms' => '', // comma separated term slugs
            'disable' => '',
			'topcontent'=> '',
			'contentlabel'=> 'Additionally'		
		), $atts));
	ob_start();
	$compareids = array();
	if($ids):
		$searchbtn = false;
		$compareids = explode(',', $ids);
	else :
		$compareids = (get_query_var('compareids')) ? explode(',', get_query_var('compareids')) : '';
	endif;

	#user identity
	$ip = rehub_get_ip();
	$userid = get_current_user_id();
	$userid = empty($userid) ? $ip : $userid;
	$post_ids = get_transient('re_compare_' . $userid);
	if(empty($post_ids) && !empty($compareids)){
		$newvalue = implode(',', $compareids);
		set_transient('re_compare_' . $userid, $newvalue, 30 * DAY_IN_SECONDS);
	}

	if($searchbtn){
		wp_enqueue_style('rhcomparesearch');wp_enqueue_style('rhajaxsearch');
		echo '<div class="search-wrap"><button id="btn_search" class="btn-comp-search def_btn font90 pb10 pl15 pr15 pt10 cursorpointer"><i class="rhicon rhi-search"></i> '.esc_html__('Add more items', 'rehub-theme').'</button><input type="hidden" id="compare_search_data" data-posttype="'. esc_attr($posttype) .'" data-terms="'. (int)$terms .'" data-taxonomy="'. esc_attr($taxonomy) .'"></div>';
	}
	if($disable){
		$disable = wp_parse_slug_list($disable);
		if(is_array($disable)){
			$addstyles = '';
			wp_register_style( 'rhheader-inline-style', false );
			wp_enqueue_style( 'rhheader-inline-style' );
			
			foreach( $disable as $item){
				if($item == 'description'){
					$addstyles .= '.row_chart_2{display: none}';
				}
				if($item == 'overview'){
					$addstyles .= '.row_chart_1{display: none}';
				}
				if($item == 'brand'){
					$addstyles .= '.row_chart_5{display: none}';
				}
				if($item == 'stock'){
					$addstyles .= '.row_chart_7{display: none}';
				}
				if($item == 'userrate'){
					$addstyles .= '.row_chart_3{display: none}';
				}
				if($item == 'review'){
					$addstyles .= '.row_chart_6{display: none}';
				}
			}
			wp_add_inline_style('rhheader-inline-style', $addstyles);
		}
	}
	if(!empty($compareids)):
		if(count($compareids) > 1){
			
			$comparedarray = get_transient( 'rh_latest_compared_ids' );
			if(empty($comparedarray)){
				$comparedarray = array();
			}
			$saveids = array_slice($compareids, 0, 2);
			$saveids = implode(',', $saveids);
			if (!in_array($saveids, $comparedarray)) {
				array_unshift($comparedarray , $saveids);
			}
			$comparesave = array_slice($comparedarray, 0, 8);
			set_transient( 'rh_latest_compared_ids', $comparesave, DAY_IN_SECONDS * 31 );
		}

		 
		?>		
	        <?php $args = array( 
	            'post_status' => 'publish', 
	            'ignore_sticky_posts' => 1, 
	            'orderby' => 'post__in',
	            'post__in' => $compareids,
	            'posts_per_page'=> -1,
	            'post_type'=> 'product'

	        );
	        ?>	

	        <?php $common_attributes = $attributes_group = array(); $common_criterias = false; ?>
	        <?php $common = new WP_Query($args); if ($common->have_posts()) : ?>
	        <?php while ($common->have_posts()) : $common->the_post(); global $product; global $post; ?>
	        	<?php 
	        		$attributes_group = (function_exists('rh_get_attributes_group')) ? rh_get_attributes_group( $product ) : '';
					if(is_array($attributes_group)){
						$countgroup = count($attributes_group);
					}else{
						$countgroup = 0;
					}
	        	?>

				<?php if($countgroup > 1): ?>
					<?php foreach( $attributes_group as $group_key => $attribute_group ): ?>
						<?php 
						if(!is_array($attribute_group['attributes'])) continue; 
						ksort($attribute_group['attributes']); 
						$common_attributes[$group_key]['name'] = $attribute_group['name']; 
						$attributes = $attribute_group['attributes']; 
						foreach ($attributes as $key => $attribute) {
							$key = $attribute['name'];
							if(!empty($common_attributes[$group_key]['attributes']) && array_key_exists($key, $common_attributes[$group_key]['attributes'])){
								continue;
							}
							$common_attributes[$group_key]['attributes'][$key] = $attribute;
						}
						?>
					<?php endforeach; ?>
				<?php else: ?>
					<?php $attributes = $product->get_attributes();?>
					<?php 
					foreach ($attributes as $key => $attribute) {
						if($attribute['is_visible'] == 1){
							$key = $attribute['name'];
							if(!empty($common_attributes) && array_key_exists($key, $common_attributes)){
								continue;
							}
							$common_attributes[$key] = $attribute;
						}
					}
					?>
				<?php endif; ?>
	        	<?php 
	        		$thecriteria = get_post_meta((int)$post->ID, '_review_post_criteria', true);
	        		if($thecriteria) {$common_criterias = true;}
	        	?>

	        <?php endwhile; endif; wp_reset_query(); ?>

	    	<?php $wp_query = new WP_Query($args); $ci=0; if ($wp_query->have_posts()) : ?>

	    	<?php wp_enqueue_script('carouFredSel'); wp_enqueue_script('touchswipe'); wp_enqueue_script('rehubtablechart');wp_enqueue_style('rhchartbuilder'); ?>
		    <div class="top_chart table_view_charts loading">		        	
		    	<div class="chart_helper floatleft mr10 ml10 rhhidden"><i class="rhicon rhi-arrows-h font150"></i></div>
		        <div class="top_chart_controls">
		            <a href="/" class="controls prev"></a>
		            <div class="top_chart_pagination"></div>
		            <a href="/" class="controls next"></a>
		        </div>
                <div class="top_chart_first">
                    <ul>
                        <li class="row_chart_0 image_row_chart">
                            <div class="sticky-cell"><br /><label class="diff-label"><input class="re-compare-show-diff" name="re-compare-show-diff" type="checkbox" /><?php esc_html_e('Show only differences', 'rehub-theme');?></label></div>
                        </li>
                        <li class="row_chart_1 heading_row_chart">
                            <?php esc_html_e('Overview', 'rehub-theme');?>
                        </li>                        
                        <li class="row_chart_2 meta_value_row_chart">
                            <?php esc_html_e('Description', 'rehub-theme');?>
                        </li>                          
                        <li class="row_chart_5 meta_value_row_chart">
                            <?php esc_html_e('Brand/Store', 'rehub-theme');?>
                        </li>                                                 
                        <li class="row_chart_7 meta_value_row_chart">
                            <?php esc_html_e('Availability', 'rehub-theme');?>
                        </li> 
                        <li class="row_chart_3 meta_value_row_chart">
                            <?php esc_html_e('User Rating', 'rehub-theme');?>
                        </li>                         
                        <?php if ($common_criterias):?>
                        <li class="row_chart_criterias row_chart_6">
                            <?php esc_html_e('Review', 'rehub-theme');?>
                        </li> 
                        <?php endif;?>                          
						<?php if(!empty($common_attributes)): ?>
							<?php if($countgroup > 1): ?>
		                        <?php $i = 7; foreach($common_attributes as $common_attribute):?>
		                            <?php $i++; ?>
									<li class="row_chart_<?php echo (int)$i;?> heading_row_chart sub_heading_row_chart"><?php echo esc_attr($common_attribute['name']); ?></li> 
									<?php foreach($common_attribute['attributes'] as $attribute_name => $attribute_value): ?>
										<?php $i++; ?>
										<li class="row_chart_<?php echo (int)$i;?> meta_value_row_chart"><?php echo wc_attribute_label( $attribute_name ); ?></li>
									<?php endforeach;?>
		                        <?php endforeach;?>
							<?php else: ?>
								<li class="row_chart_8 heading_row_chart"><?php esc_html_e('Specification', 'rehub-theme');?></li>
		                        <?php $i = 8; foreach($common_attributes as $attribute_value):?>
		                            <?php $i++;?>
		                            <li class="row_chart_<?php echo (int)$i;?> meta_value_row_chart"><?php echo wc_attribute_label( $attribute_value['name'] ); ?></li>
		                        <?php endforeach;?>
							<?php endif;?>
						<?php else:?>
							<?php $i = 7;?>
						<?php endif;?>
						<?php if ($content && !$topcontent):?>
							<?php $i++;?>
							<li class="row_chart_<?php echo (int)$i;?> shortcode_row_chart">
                            <?php echo esc_attr($contentlabel);?>
                        	</li> 
						<?php endif;?>
                    </ul>
                </div>
		    	<div class="top_chart_wrap woocommerce"><div class="top_chart_carousel">
			        <?php while ($wp_query->have_posts()) : $wp_query->the_post(); global $product, $post; $ci ++?>
			            <div class="top_rating_item top_chart_item compare-item-<?php echo (int)$post->ID;?>" id='rank_<?php echo (int)$ci?>' data-compareid="<?php echo (int)$post->ID;?>">
			                <ul>
                                <li class="row_chart_0 image_row_chart">
                                    <div class="product_image_col sticky-cell">                                  
                                        <i class="rhicon rhi-times-circle-solid re-compare-close-in-chart"></i>
                                        <figure>
								            <?php if ( $product->is_featured() ) : ?>
								                    <?php echo apply_filters( 'woocommerce_featured_flash', '<span class="onfeatured">' . esc_html__( 'Featured!', 'rehub-theme' ) . '</span>', $post, $product ); ?>
								            <?php endif; ?>        
								            <?php if ( $product->is_on_sale()) : ?>
								                <?php 
								                $percentage=0;
								                $featured = ($product->is_featured()) ? ' onsalefeatured' : '';
								                if ($product->get_regular_price() && is_numeric($product->get_regular_price()) && $product->get_regular_price() !=0) {
								                    $percentage = round( ( ( $product->get_regular_price() - $product->get_price() ) / $product->get_regular_price() ) * 100 );
								                }
								                if ($percentage && $percentage>0 && !$product->is_type( 'variable' )) {
								                    $sales_html = apply_filters( 'woocommerce_sale_flash', '<span class="onsale'.$featured.'"><span>- ' . $percentage . '%</span></span>', $post, $product );
								                }
								                else{
								                    $sales_html = apply_filters( 'woocommerce_sale_flash', '<span class="onsale'.$featured.'">' . esc_html__( 'Sale!', 'rehub-theme' ) . '</span>', $post, $product );  
								                }                 
								                ?>
								                <?php echo ''.$sales_html; ?>
								            <?php endif; ?>                                        
                                            <a href="<?php the_permalink();?>">
                								<?php WPSM_image_resizer::show_static_resized_image(array('lazy'=> false, 'thumb'=> true, 'crop'=> false, 'height'=> 150, 'no_thumb_url' => rehub_woocommerce_placeholder_img_src('')));?>
                                            </a>
                                        </figure>
                                        <h2>
                                            <a href="<?php the_permalink();?>">
                                                <?php echo the_title();?>                     
                                            </a>
                                        </h2>
									    <div class="rev-in-compare-flip">
									        <?php $rating_score_clean = '';?> 
									        <?php $rating_score_clean = get_post_meta($post->ID, 'rehub_review_overall_score', true); ?>            

									        <?php if ($rating_score_clean):?>
									            <div class="radial-progress" data-rating="<?php echo ''.$rating_score_clean?>">
									                <div class="circle">
									                    <div class="mask full">
									                        <div class="fill"></div>
									                    </div>
									                    <div class="mask half">
									                        <div class="fill"></div>
									                        <div class="fill fix"></div>
									                    </div>
									                    
									                </div>
									                <div class="inset">
									                    <div class="percentage"><?php echo ''.$rating_score_clean?></div>
									                </div>
									            </div>                                                            
									        <?php endif;?>                                                        
									    </div>                                         
                                        <div class="price-in-compare-flip mt20">
                                         
                                            <?php if ($product->get_price() !='') : ?>
                                                <span class="price-woo-compare-chart rehub-btn-font rehub-main-color mb15 fontbold"><?php echo ''.$product->get_price_html(); ?></span>
                                                <div class="mb10"></div>
                                            <?php endif;?>
										    <?php $syncitem = $ceofferurl = ''; $countoffers = 0;?>
										    <?php if (defined('\ContentEgg\PLUGIN_PATH')):?>
										        <?php $itemsync = \ContentEgg\application\WooIntegrator::getSyncItem($post->ID);?>
										        <?php if(!empty($itemsync)):?>
										            <?php                            
										                $syncitem = $itemsync;                            
										            ?>
										            <?php $countoffers = rh_ce_found_total_offers($post->ID);?>
										            <?php if($countoffers == 1 && !empty($itemsync['url'])) $ceofferurl = apply_filters('rh_post_offer_url_filter', esc_url( $itemsync['url'] ));?>
										        <?php endif;?>
										    <?php endif;?>
										    <?php if (rehub_option('woo_btn_disable') != '1'):?>   
									            <?php if($countoffers > 1):?>
									                <a href="<?php echo get_post_permalink($post->ID);?>" data-product_id="<?php echo esc_attr( $product->get_id() );?>" data-product_sku="<?php echo esc_attr( $product->get_sku() );?>" class="re_track_btn btn_offer_block btn-woo-compare-chart woo_loop_btn">
									                    <?php if(rehub_option('rehub_btn_text_aff_links') !='') :?>
									                        <?php echo rehub_option('rehub_btn_text_aff_links') ; ?>
									                    <?php else :?>
									                        <?php esc_html_e('Choose offer', 'rehub-theme') ?>
									                    <?php endif ;?>
									                </a>                                         
								                <?php elseif ( $product->add_to_cart_url() !='') : ?>
								                    <?php  echo apply_filters( 'woocommerce_loop_add_to_cart_link',
								                        sprintf( '<a href="%s" data-product_id="%s" data-product_sku="%s" class="re_track_btn btn_offer_block btn-woo-compare-chart woo_loop_btn %s %s product_type_%s"%s%s>%s</a>',
								                        $ceofferurl ? $ceofferurl : esc_url( $product->add_to_cart_url() ),
								                        esc_attr( $product->get_id() ),
								                        esc_attr( $product->get_sku() ),
								                        $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
								                        $product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : '',
								                        esc_attr( $product->get_type() ),
								                        $product->get_type() =='external' ? ' target="_blank"' : '',
								                        $product->get_type() =='external' ? ' rel="nofollow"' : '',
								                        esc_html( $product->add_to_cart_text() )
								                        ),
								                    $product );?>
								                <?php endif; ?>
							                <?php endif; ?>
										    <div class="yith_woo_chart"> 
										        <?php $wishlistadd = esc_html__('Add to wishlist', 'rehub-theme');?>
										        <?php $wishlistadded = esc_html__('Added to wishlist', 'rehub-theme');?>
										        <?php $wishlistremoved = esc_html__('Removed from wishlist', 'rehub-theme');?>
                                            	<?php echo RH_get_wishlist($post->ID, $wishlistadd, $wishlistadded, $wishlistremoved);?> 
											</div>                      
                                        </div>                                              
                                    </div>
                                </li> 
                                <li class="row_chart_1 heading_row_chart">
                                </li>                               
                                <li class="row_chart_2 meta_value_row_chart">
                                	<?php the_excerpt(); ?>
                                </li>                                  
                                <li class="row_chart_5 meta_value_row_chart">
                                	<?php WPSM_Woohelper::re_show_brand_tax(); //show brand taxonomy?>
                                </li>                                                                
                                <li class="row_chart_7 meta_value_row_chart">
                                	<?php if ( $product->is_in_stock() ):?>
										<span class="greencolor"><?php esc_html_e( 'In stock', 'rehub-theme' ) ;?></span>
									<?php else :?>
										<span class="redcolor"><?php esc_html_e( 'Out of stock', 'rehub-theme' ) ;?></span>
									<?php endif;?>
                                </li>
                                <li class="row_chart_3 meta_value_row_chart">
                                    <?php if ( get_option( 'woocommerce_enable_review_rating' ) === 'yes'):?>
                                    	<?php $avg_rate_score 	= number_format( $product->get_average_rating(), 1 ) * 20 ;?>
                                    	<?php if ($avg_rate_score):?>
	                                    	<div class="rev-in-woocompare">
	                                    		<div class="star-big"><span class="stars-rate"><span style="width: <?php echo ''.$avg_rate_score;?>%;"></span></span></div>
	                                    	</div>
                                    	<?php else:?>
                                    		-
                                    	<?php endif;?>
                                    <?php else:?>
                                    		-
                                    <?php endif;?>
                                </li>                                
		                        <?php if ($common_criterias):?>
		                        <li class="row_chart_6 row_chart_criterias">
		                            <?php echo rehub_exerpt_function(array('reviewcriterias'=> 'editor'));?>
		                        </li> 
		                        <?php endif;?>                                
	                            <?php if(!empty($common_attributes)): ?> 
									<?php $attrnames = array(); ?>
									<?php if($countgroup > 1): ?>
										<?php $i = 7; foreach($common_attributes as $attr_group): ?>
											<?php $i++;?>
											<li class="row_chart_<?php echo (int)$i; ?> heading_row_chart sub_heading_row_chart"></li>
											<?php $currentattr =  $attr_group['attributes']; ?>
											<?php foreach($currentattr as $attribute):?>
												<?php $i++;?>
												<li class="row_chart_<?php echo (int)$i; ?> meta_value_row_chart">
													<?php 
													if($attribute['is_visible'] != 1) continue;
												//	if(!in_array()) continue;
													if ($attribute['is_taxonomy']) {
														$values = wc_get_product_terms( $product->get_id(), $attribute['name'], array( 'fields' => 'names' ) );
														if(!empty($values)){
															echo apply_filters('woocommerce_attribute', wpautop(wptexturize(implode(', ', $values))), $attribute, $values );	
														}
													} else {
														if($product->get_attribute($attribute['name'])){
															echo wc_implode_text_attributes($attribute->get_options());
														}
													}
													?>
												</li>
											<?php endforeach;?>
										<?php endforeach;?>
									<?php else: ?>
										<?php $i = 8;?>
										<li class="row_chart_<?php echo (int)$i; ?> heading_row_chart"></li>
										<?php $currentattr =  $product->get_attributes(); ?>
										<?php foreach ($currentattr as $key => $attr) {
											if($attr['is_visible'] == 1){
												$key = $attr['name'];
												$attrnames[$key] = $attr;
											}
										}
										?>	                                                                                        
										<?php foreach($common_attributes as $attkey => $attribute):?>
											<?php $i++;?>
											<li class="row_chart_<?php echo (int)$i;?> meta_value_row_chart">
												<?php 
													$currentname = $attribute['name'];
													if(array_key_exists($currentname, $attrnames)){
														if ( $attribute['is_taxonomy'] ) {
															$values = wc_get_product_terms( $product->get_id(), $currentname, array( 'fields' => 'names' ) );
															if(!empty($values)){
																echo apply_filters( 'woocommerce_attribute', wpautop( wptexturize( implode( ', ', $values ) ) ), $attribute, $values );	
															}
														} else {
															$curtextattr = $attrnames[$currentname];
															echo wc_implode_text_attributes( $curtextattr->get_options() );
														}
													}
												?>
											</li>
										<?php endforeach;?>
									<?php endif;?>	
				                <?php else:?>
									<?php $i = 7;?>
								<?php endif;?>
								<?php if ($content && !$topcontent):?>
									<?php $i++;?>
									<li class="row_chart_<?php echo (int)$i;?> shortcode_row_chart">
                            			<?php echo do_shortcode(wp_kses_post($content));?>
                        			</li> 
									
								<?php endif;?>                                                              
			            </ul>
			            </div>
			        <?php endwhile; ?>
		    	</div></div>
		    	<span class="top_chart_row_found" data-rowcount="<?php echo (int)$i + 1;?>"></span>
		    </div>
		    <?php else: ?><?php esc_html_e('No posts for this criteria.', 'rehub-theme'); ?>
		    <?php endif; ?>
		    <?php wp_reset_query(); ?>

		<?php   
	else:
		echo '<div class="mb30 clearfix"></div>';
		echo esc_html__('No products for comparison', 'rehub-theme');
		echo '<div class="mb30 clearfix"></div>';
	endif;

	$output = ob_get_contents();
	ob_end_clean();
	return $output; 		

}
}


//////////////////////////////////////////////////////////////////
// Categorizator
//////////////////////////////////////////////////////////////////
add_action( 'wp_ajax_multi_cat', 'ajax_action_multi_cat' );
add_action( 'wp_ajax_nopriv_multi_cat', 'ajax_action_multi_cat' );
if( !function_exists('ajax_action_multi_cat') ) {
function ajax_action_multi_cat() {
	$nonce = sanitize_text_field($_POST['nonce']);
    if ( ! wp_verify_nonce( $nonce, 'ajaxed-nonce' ) )
        die ( 'Nope!' );   

		$page = intval($_POST['page']);
		$paged = ($page) ? $page : 1;
		ob_start();
		$query_args = array(
			'paged' => $paged,
			'post_type' => 'post',
			'posts_per_page' => 5,
			'tax_query' => array(
				array(
					'taxonomy' => sanitize_text_field( $_POST['tax'] ),
					'field' => 'id',
					'terms' => sanitize_text_field( $_POST['term'] )
				)
			),
		);
		$query = new WP_Query($query_args);
		$response = '';
		if ( $query->have_posts() ) {
			while ($query->have_posts() ) {
				$query->the_post();
				ob_start();
				get_template_part( 'content', 'multi_category' );
				$response .= ob_get_clean();
			}
			wp_reset_postdata();
		} else {
			$response = 'fail';
		}

		echo ''.$response ;
		exit;
}
}

if( !function_exists('wpsm_categorizator_shortcode') ) {
function wpsm_categorizator_shortcode( $atts, $content = null ) {
	
	extract(shortcode_atts(array(
			'tax' => 'category',
			'exclude' => '',
			'include' => '',
			'col' => '3',
			'sorting_meta' => '',
			'order' => 'DESC'
		), $atts));
        
    $args = array(
    	'taxonomy'=> $tax,
        'orderby' => 'name',
		'exclude' => explode(',', $exclude),
		'include' => explode(',', $include),
    );
    $terms = get_terms($args );
    wp_enqueue_style('rhcategorizator');
    wp_enqueue_script( 'rhcategorizator', get_template_directory_uri() . '/js/categorizator.js', array( 'jquery' ), '1.1', true );
	ob_start(); 
    ?>

    <?php
        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
            if ($col == '4') {
            	echo '<div class="col_wrap_fourth">';
            }
            elseif ($col == '2') {
            	echo '<div class="col_wrap_two">';
            }  
            elseif ($col == '1') {
            	echo '<div class="alignleft multicatleft">';
            }                       
            else {echo '<div class="col_wrap_three">'; }
            $i = 1;
            foreach ($terms as $term) {
                $query_args = array(
                    'post_type' => 'post',
                    'posts_per_page' => 5,
                    'tax_query' => array(
                        array(
                            'taxonomy' => $term->taxonomy,
                            'field' => 'id',
                            'terms' => $term->term_id
                        )
                    ),
                    'order' => $order,
                );

                if($sorting_meta){
                	$query_args['orderby'] = 'meta_value_num';
            		$query_args['meta_key'] = $sorting_meta;
                }

                $query = new WP_Query($query_args);

                if ( $query->have_posts() ) :
                    ?>

                    <div id="directory-<?php echo (int)$term->term_id; ?>" class="multi_cat col_item"
                         data-tax="<?php echo ''.$term->taxonomy; ?>"
                         data-term="<?php echo (int)$term->term_id; ?>">
                        <div class="multi_cat_header">
							<div class="multi_cat_lable">
								<?php echo ''.$term->name; ?>
							</div>
                        </div>
                        <div class="multi_cat_wrap eq_height_post">

                            <?php while ($query->have_posts() ) :
                                $query->the_post();
                                get_template_part( 'content', 'multi_category' );
                            endwhile; wp_reset_postdata(); ?>

                        </div>
                        <div class="cat-pagination multi_cat_header clearfix">

                            <?php for ($j = 1, $max_count = $query->max_num_pages; $j<= $max_count;  $j++) : ?>
                                <?php $active = ($j ===1) ? 'active' : '' ;?>
                                <a class="styled <?php echo ''.$active; ?>" data-paginated="<?php echo (int)$j; ?>"><?php echo (int)$j;?></a>
                            <?php endfor; ?>

                        </div>
                    </div>

                    <?php $i++;
                    
                endif;
            }
            echo '</div>';
        }   
    ?>

	<?php 
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}
}

//////////////////////////////////////////////////////////////////
// Cartbox
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_cartbox_shortcode') ) {
function wpsm_cartbox_shortcode( $atts, $content = null ) {

	extract(shortcode_atts(array(
			'title' => '',
			'link' => '',
			'description' => '',
			'image' => '',
			'revert_image' =>'',
			'revert_title' =>'',
			'design' => '1',
			'linktitle' => '',
			'bg_cover' => '1'
		), $atts));

	if (is_numeric($image)) {$image = wp_get_attachment_url( $image);}
	$output = '';

	if($bg_cover) {
		$covercss = 'abdfullwidth imageasbg rh-flex-center-align rh-flex-justify-center flowhidden rh-flex-align-stretch rh-fit-cover';
	}else{
		$covercss = 'rh-flex-center-align rh-flex-justify-center flowhidden img-width-auto';
	}

	if(is_array($link)){
		$target = ($link['is_external'] == true) ? '_blank' : '_self';
		$nofollow = ($link['nofollow'] == true) ? 'nofollow' : '';
		$url = ($link['url']) ? $link['url'] : '#';
		$urlres = array( 'url' => $url, 'title' => $linktitle, 'target' => $target, 'rel' => $nofollow );
	}else{
		$url_pairs = explode( '|', $link );
		if ( !empty( $url_pairs[0] ) && is_array($url_pairs)) {
		    $urlres = array( 'url' => '', 'title' => '', 'target' => '', 'rel' => '' );
		    if( preg_match( '/url:/', $url_pairs[0] ) == false ){
		        $url_pairs[0] = 'url:'. $url_pairs[0];
		    }   
		    foreach ( $url_pairs as $pair ) {
		        $param = preg_split( '/\:/', $pair, 2 ); //CHANGED//
		        if ( ! empty( $param[0] ) && isset( $param[1] ) ) {
		            $urlres[ $param[0] ] = rawurldecode( $param[1] );
		        }
		    }
		}
		else{
		    $urlres = array( 'url' => $link, 'title' => '', 'target' => '_self', 'rel' => '' );
		}
	}

    if ($design == '2'){
    	$output .= '<div class="rh-cartbox catboxmodule">';
    		$output .= '<div class="rh-flex-center-align">';
    			$output .= '<div class="rh-cbox-left floatleft mr20">';
    				$output .= '<div class="lineheight20 rehub-main-font mb10">'.esc_html($title).'</div>';
					$output .= '<div class="lineheight15 font80 mb10">'.esc_html($description).'</div>';
					if(!empty($urlres['url']) && !empty($urlres['title'])){
						$output .= '<div class="lineheight15 font85 fontbold"><a target="'.esc_attr($urlres['target']).'" rel="'.esc_attr($urlres['rel']).'" href="'.esc_url($urlres['url']).'">'.esc_html($urlres['title']).'</a></div>';						
					}
    			$output .= '</div>';
    			$output .= '<div class="rh-cbox-right rh-flex-right-align text-center width-80 height-80">';
    				if($image){
						$cardimg = new WPSM_image_resizer();
		                $cardimg->width = '100';
		                $cardimg->src = $image;
		                $thumbnail_url = $cardimg->get_resized_url();
						$output .= '<a target="'.esc_attr($urlres['target']).'" rel="'.esc_attr($urlres['rel']).'" href="'.esc_url($urlres['url']).'"><img src="'. $thumbnail_url .'" alt="'. esc_html($title) .'" /></a>';		                    					
    				}
    			$output .= '</div>';
    		$output .= '</div>';
    	$output .= '</div>';
    } else{
    	wp_enqueue_style('rhbanner');
		$imagehtml = '';
		if($image){
			$imagehtml = '<img class="lazyload" data-src="'.$image.'" width=300 height=300 alt="'.esc_html($title).'" src="'.get_template_directory_uri() . '/images/default/blank.gif" />';
		}
		$output .= '<div class="categoriesbox blackcolor rh-hovered-wrap full_cover_link flowhidden margincenter mb15 rh-cartbox rh-heading-hover-color rh-hover-up rh-shadow4">';
		if ($link) : 
			$output .= '<a target="'.esc_attr($urlres['target']).'" rel="'.esc_attr($urlres['rel']).'" href="'.esc_url($urlres['url']).'" class="position-relative">';
		endif;
		if ($revert_image) :
			if ($image) :
				$output .= '<div class="categoriesbox-bg csstranstranslong rh-hovered-scalesmall">';	
					$output .= '<div class="'.$covercss.'">'.$imagehtml.'</div>';
				$output .= '</div>';	
			endif;		
		endif;
		$output .='<div class="categoriesbox-content pb15 pt25 pr15 pl15 text-center">';
		if ($description && $revert_title) :
			$output .= '<p class="mb10">'.$description.'</p>';		
		endif;		
		if ($title) :
			$titleclass = $revert_title ? "mb0" : "mb10";
			$output .= '<h3 class="'.$titleclass.' position-relative pt0 pb0 pl0 pr0">';
				$output .= $title;	
			$output .= '</h3>';		
		endif;
		if ($description && !$revert_title) :
			$output .= '<p class="mb0">'.$description.'</p>';		
		endif;	
		$output .= '</div>';
		if ($revert_image =='' || $revert_image =='0') :
			if ($image) :
				$output .= '<div class="categoriesbox-bg csstranstranslong rh-hovered-scalesmall">';	
					$output .= '<div class="'.$covercss.'">'.$imagehtml.'</div>';
				$output .= '</div>';	
			endif;
		endif;
		if ($link) : 
			$output .= '</a>';
		endif;
		$output .= '</div>';
	}

	return $output;
}
}

//////////////////////////////////////////////////////////////////
// Score box
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_scorebox_shortcode') ) {
function wpsm_scorebox_shortcode( $atts, $content = null ) {

	extract(shortcode_atts(array(
			'criterias' => 'editor',
			'simplestar' => '',
			'offerbtn' => 'yes',
			'id' => '',
			'title'=> '',
			'proscons' => '',
			'prostitle' => 'PROS:',
			'constitle' => 'CONS:',
			'ce_enable'=> '',
			'image' => '',
		), $atts));

	ob_start(); 
    ?>

	<?php if(isset($atts['id']) && $atts['id']) :?>		
		<?php $revid = $atts['id'];?>
	<?php else :?>   
		<?php if (!is_singular() || is_front_page()) {return; } ?>
    	<?php $revid = get_the_ID();?>
    <?php endif ;?>  
	<?php if(isset($atts['title']) && $atts['title']) :?>		
		<?php $title = $atts['title'];?>
	<?php else :?>   
    	<?php $title = esc_html__('Average Score', 'rehub-theme');?>
    <?php endif ;?>

    <?php $args = array('no_found_rows' => 1,'p' => $revid, 'post_type' => 'any'); $query = new WP_Query($args);?>
    <?php if ($query->have_posts()) : ?>
    <?php while ($query->have_posts()) : $query->the_post(); global $post; ?>
    	<div class="wpsm_score_box whitebg wpsm_score_box blackcolor rh-shadow3 border-lightgrey"> 
    		<style scoped>
    			.wpsm_score_box .rate_bar_wrap{ background-color: transparent; padding: 0; border: none; box-shadow: none; margin: 0}
				.wpsm_inside_scorebox .rate_bar_wrap .review-criteria{ border: none}
				.wpsm_score_box .rate-bar, .wpsm_score_box .rate-bar-bar, .cmp_crt_block .rate-bar, .cmp_crt_block .rate-bar-bar{ height: 9px}
				.woocommerce .wpsm_score_box .quantity input.qty{float: none; margin: 0 auto; border:none;display: block;}
				.wpsm_score_box .user-rate{ float: none;}
			</style>   
    		<?php if($post->post_type == 'product'):?>
				<?php $score = get_post_meta((int)$revid, 'rehub_review_overall_score', true);?>
		    	<?php if($score) :?>	    	
		    		<div class="font120 lightgreybg lineheight25 pb15 pl20 pr20 pt15 wpsm_score_title">
		    			<span class="overall-text"><?php echo esc_attr($title); ?></span>
		    			<span class="floatright font140 fontbold overall-score"><?php echo round($score, 1) ?></span>
		    		</div>
		    		<div class="wpsm_inside_scorebox padd20">
		    			<?php if ($simplestar == 'yes') :?>
		    				<div class="rating_bar flowhidden mt15"><?php echo rehub_get_user_rate() ; ?></div>
		    			<?php endif ;?>
		    			<?php 
		    				$thecriteria = get_post_meta((int)$revid, '_review_post_criteria', true);
							$firstcriteria = $thecriteria[0]['review_post_name']; 
						?>
			    		<?php if($firstcriteria) : ?>
			    		<div class="rate_bar_wrap">
							<div class="review-criteria mt0 pt25">
								<?php foreach ($thecriteria as $criteria) { ?>
									<?php $perc_criteria = $criteria['review_post_score']*10; ?>
									<div class="rate-bar clearfix" data-percent="<?php echo ''.$perc_criteria; ?>%">
										<div class="rate-bar-title"><span><?php echo ''.$criteria['review_post_name']; ?></span></div>
										<div class="rate-bar-bar r_score_<?php echo round($criteria['review_post_score']); ?>"></div>
										<div class="rate-bar-percent"><?php echo ''.$criteria['review_post_score']; ?></div>
									</div>
								<?php } ?>
							</div>
						</div>
						<?php endif; ?>		
						<?php if($proscons):?>
							<?php 	
								$prosvalues = get_post_meta((int)$revid, '_review_post_pros_text', true);
								$consvalues = get_post_meta((int)$revid, '_review_post_cons_text', true);
							?> 
							<!-- PROS CONS BLOCK-->
							<div class="prosconswidget">
							<?php if(!empty($prosvalues)):?>
								<div class="wpsm_pros mb30 mt10">
									<div class="title_pros"><?php echo esc_attr($prostitle);?></div>
									<ul>		
										<?php $prosvalues = explode(PHP_EOL, $prosvalues);?>
										<?php foreach ($prosvalues as $prosvalue) {
											if(!$prosvalue) continue;
											echo '<li>'.$prosvalue.'</li>';
										}?>
									</ul>
								</div>
							<?php endif;?>	
							<?php if(!empty($consvalues)):?>
								<div class="wpsm_cons">
									<div class="title_cons"><?php echo esc_attr($constitle);?></div>
									<ul>
										<?php $consvalues = explode(PHP_EOL, $consvalues);?>
										<?php foreach ($consvalues as $consvalue) {
											if(!$consvalue) continue;
											echo '<li>'.$consvalue.'</li>';
										}?>
									</ul>
								</div>
							<?php endif;?>
							</div>	
							<!-- PROS CONS BLOCK END-->
						<?php endif;?>		    		    		
		    		</div>
		    	<?php endif;?>
	    		<?php if ($offerbtn=="yes") :?>
	    			<div class="btn_score_btm mt15 padd20 border-top priced_block woo-button-area">
	    				<?php do_action('rhwoo_template_single_add_to_cart');?>
	    			</div>
	    		<?php endif ;?>			    		    	

    		<?php else:?>
		    	<?php $overal_score = rehub_get_overall_score(); 
		    	if($overal_score !='0') :?>	    	
		    		<div class="font120 lightgreybg lineheight25 pb15 pl20 pr20 pt15 wpsm_score_title">
		    			<span class="overall-text"><?php echo esc_attr($title); ?></span>
		    			<span class="floatright font140 fontbold overall-score"><?php echo round($overal_score, 1) ?></span>
		    		</div>
		    	<?php endif;?>
		    	<?php if($image) :?>
            		<?php wpsm_thumb('mediumgrid'); ?>
		    	<?php endif;?>
		    	<?php  if($overal_score !='0') :?>	
		    		<div class="wpsm_inside_scorebox padd20">
		    			<?php if ($simplestar == 'yes') :?><div class="rating_bar flowhidden mt15"><?php echo rehub_get_user_rate() ; ?></div><?php endif ;?>
			    		<?php if ($criterias == 'editor' || $criterias == 'both') :?>
			    			<?php 
								$thecriteria = get_post_meta((int)$revid, '_review_post_criteria', true);
								if(empty($thecriteria)){
									$review_post = rehub_get_review_data();
									$thecriteria = $review_post['review_post_criteria'];
								}
								$firstcriteria = $thecriteria[0]['review_post_name'];
							?>
				    		<?php if($firstcriteria) : ?>
				    		<div class="rate_bar_wrap">
								<div class="review-criteria mt0 pt25">
									<?php foreach ($thecriteria as $criteria) { ?>
										<?php $perc_criteria = $criteria['review_post_score']*10; ?>
										<div class="rate-bar clearfix" data-percent="<?php echo ''.$perc_criteria; ?>%">
											<div class="rate-bar-title"><span><?php echo ''.$criteria['review_post_name']; ?></span></div>
											<div class="rate-bar-bar r_score_<?php echo round($criteria['review_post_score']); ?>"></div>
											<div class="rate-bar-percent"><?php echo ''.$criteria['review_post_score']; ?></div>
										</div>
									<?php } ?>
								</div>
							</div>
							<?php endif; ?>
			    		<?php endif ;?>	
			    		<?php if ($criterias == 'user' || $criterias == 'both') :?>
			    			<?php $postAverage = get_post_meta($revid, 'post_user_average', true); ?>
				    		<?php if($postAverage !='0' && $postAverage !='') : ?>
							<div class="rate_bar_wrap">	
								<?php $user_rates = get_post_meta($revid, 'post_user_raitings', true); $usercriterias = $user_rates['criteria'];  ?>
								<div class="review-criteria mt0 pt25 user-review-criteria">
									<div class="r_criteria">
										<?php foreach ($usercriterias as $usercriteria) { ?>
										<?php $perc_criteria = $usercriteria['average']*10; ?>
										<div class="rate-bar user-rate-bar clearfix" data-percent="<?php echo ''.$perc_criteria; ?>%">
											<div class="rate-bar-title"><span><?php echo ''.$usercriteria['name']; ?></span></div>
											<div class="rate-bar-bar r_score_<?php echo round($usercriteria['average']); ?>"></div>
											<div class="rate-bar-percent"><?php echo ''.$usercriteria['average']; ?></div>
										</div>
										<?php } ?>
									</div>
								</div>
							</div>
							<?php endif; ?>
			    		<?php endif ;?>	
						<?php if($proscons):?>
							<?php 	
								$prosvalues = get_post_meta($revid, '_review_post_pros_text', true);
								if(empty($prosvalues)){
									$review_post = rehub_get_review_data();
									$prosvalues = $review_post['review_post_pros_text'];
								}	
								$consvalues = get_post_meta($revid, '_review_post_cons_text', true);
								if(empty($consvalues)){
									$review_post = rehub_get_review_data();
									$consvalues = $review_post['review_post_cons_text'];
								}
							?> 
							<!-- PROS CONS BLOCK-->
							<div class="prosconswidget">
							<?php if(!empty($prosvalues)):?>
								<div class="wpsm_pros mb30 mt10">
									<div class="title_pros"><?php echo esc_attr($prostitle);?></div>
									<ul>		
										<?php $prosvalues = explode(PHP_EOL, $prosvalues);?>
										<?php foreach ($prosvalues as $prosvalue) {
											echo '<li>'.$prosvalue.'</li>';
										}?>
									</ul>
								</div>
							<?php endif;?>	
							<?php if(!empty($consvalues)):?>
								<div class="wpsm_cons">
									<div class="title_cons"><?php echo esc_attr($constitle);?></div>
									<ul>
										<?php $consvalues = explode(PHP_EOL, $consvalues);?>
										<?php foreach ($consvalues as $consvalue) {
											echo '<li>'.$consvalue.'</li>';
										}?>
									</ul>
								</div>
							<?php endif;?>
							</div>	
							<!-- PROS CONS BLOCK END-->
						<?php endif;?>		    		    		
		    		</div>
		    	<?php endif;?>	    	
	    		<?php if ($offerbtn=="yes") :?>
	    			<div class="btn_score_btm mt15 padd20 border-top">
	    				<?php rehub_generate_offerbtn('wrapperclass=block_btnblock mobile_block_btnclock mb5 text-center');?>
	    			</div>
	    		<?php endif ;?>
    		<?php endif ;?>		    
    		<?php if ($ce_enable && defined('\ContentEgg\PLUGIN_PATH')) :?>

    			<div class="wpsm_inside_scorebox_ce">
	                <?php
	                    $cegg_field_array = rehub_option('save_meta_for_ce');
	                    $cegg_fields = array();
	                    if (!empty($cegg_field_array) && is_array($cegg_field_array)) {
	                        foreach ($cegg_field_array as $cegg_field) {
	        					if ($cegg_field == 'none' || $cegg_field == ''){ continue;}	                        	
                                $cegg_field_value = \ContentEgg\application\components\ContentManager::getViewData($cegg_field, $post->ID);
	                            if (!empty ($cegg_field_value) && is_array($cegg_field_value)) {
	                                $cegg_fields[$cegg_field]= $cegg_field_value;
	                            }       
	                        }		                        
	                        if (!empty($cegg_fields) && is_array($cegg_fields)) {
								$all_items = array(); 
							    foreach ($cegg_fields as $module_id => $items) {
							        foreach ($items as $item_ar) {
							            $item_ar['module_id'] = $module_id;
							            $all_items[] = $item_ar;

							        }       
							    }		                        	
	                        	?>
				    			<div class="btn_score_btm rh_deal_block mt15 padd20 border-top">		                        	
		                        	<?php foreach ($all_items as $key => $item) :?>
		                        		<?php                             
		                        			$currency_code = (!empty($item['currencyCode'])) ? $item['currencyCode'] : '';                                
	                        				$offer_price = (!empty($item['price'])) ? \ContentEgg\application\helpers\TemplateHelper::formatPriceCurrency($item['price'], $currency_code) : '';
	                        				$offer_price_old = (!empty($item['priceOld'])) ? \ContentEgg\application\helpers\TemplateHelper::formatPriceCurrency($item['priceOld'], $currency_code) : '';    
	                        				$offer_title = (!empty($item['title'])) ? $item['title'] : '';
	                        				$offer_post_url = (!empty($item['url'])) ? $item['url'] : '';
	                        				$offer_url = apply_filters('rh_post_offer_url_filter', $offer_post_url );
	                        			?>
								        <?php if (!empty($item['domain'])):?>
								            <?php $domain = $item['domain'];?>
								        <?php elseif (!empty($item['extra']['domain'])):?>
								            <?php $domain = $item['extra']['domain'];?>
								        <?php else:?>
								            <?php $domain = '';?>        
								        <?php endif;?>  	                            			
	                        			<?php $merchant = (!empty($item['merchant'])) ? $item['merchant'] : ''; ?>
	                        			<?php $logo = \ContentEgg\application\helpers\TemplateHelper::getMerhantLogoUrl($item, true);?>
										<div class="deal_block_row flowhidden clearbox mb15 pb15 border-grey-bottom">									
											<div class="rh-flex-columns">
												<div class="rh-deal-left">
													<div class="rh-deal-name mb10">
														<h5 class="font95 mt0 mb10 fontnormal"><a href="<?php echo esc_url($offer_url); ?>" class="blackcolor"><?php echo esc_attr($offer_title);?></a></h5>
													</div>
									                <?php if ($logo):?>
									                	<div class="rh-deal-brandlogo mb10">
									                        <?php if($logo) :?>
	            												<?php WPSM_image_resizer::show_static_resized_image(array('lazy'=> false, 'src'=> $logo, 'crop'=> false, 'width'=> 70, 'height'=> 70));?>
									                        <?php endif ;?>	            											
	        											</div>
	        										<?php elseif ($merchant):?>
	        											<div class="rh-deal-tag aff_tag">
	        												<span><?php echo esc_attr($merchant);?></span>
	        											</div>
									                <?php endif;?>
												</div>
												<div class="rh-deal-right rh-flex-right-align pl15">
													<?php if(!empty($offer_price)) : ?>
							                            <div class="rh-deal-price mb10 fontbold font90">
							                                <ins><?php echo ''.$offer_price ?></ins>
							                                <?php if(!empty($offer_price_old)) : ?>
								                                <del class="rh_opacity_3 blockstyle fontnormal blackcolor">
								                                    <?php echo ''.$offer_price_old ?>
								                                </del>
							                                <?php endif ;?>                                
							                            </div>
							                        <?php endif ;?>
													<div class="rh-deal-btn mb10 text-right-align">
										                <a href="<?php echo esc_url($offer_url) ?>" class="re_track_btn rh-deal-compact-btn padforbuttonsmall fontnormal font95 lineheight15 text-center inlinestyle btn_offer_block" target="_blank" rel="nofollow">
										                    <?php if(rehub_option('rehub_btn_text') !='') :?>
										                        <?php echo rehub_option('rehub_btn_text') ; ?>
										                    <?php else :?>
										                        <?php esc_html_e('Buy Now', 'rehub-theme') ?>
										                    <?php endif ;?>
										                </a>	            					
													</div>						
												</div>					
											</div>
										</div>                             			
		                        	<?php endforeach;?>
		                        </div>
	                        	<?php
	                        }
	                    }
	                ?>	    		
				</div>

			<?php endif ;?>		    
	    </div>
    <?php endwhile; endif; wp_reset_postdata(); ?>

    <?php 
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}
}

//////////////////////////////////////////////////////////////////
// Reveal shortcode
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_reveal_shortcode') ) {
function wpsm_reveal_shortcode( $atts, $content = null ) {
extract(shortcode_atts(array(
		'textcode' => '',
		'btntext' => '',
		'url' => '',
	), $atts));
wp_enqueue_script('affegg_coupons');
wp_enqueue_script('zeroclipboard');

$output = '<div class="priced_block"><div class="post_offer_anons"><div class="coupon_btn re_track_btn btn_offer_block rehub_offer_coupon masked_coupon" data-clipboard-text="'.rawurlencode(esc_html($textcode)).'" data-codetext="'.rawurlencode(esc_html($textcode)).'" data-dest="'.esc_url($url).'">';
if($btntext !='') :
	$output .=esc_html($btntext);
else :
	$output .= esc_html__('Reveal', 'rehub-theme');
endif;
	$output .='<i class="rhicon rhi-external-link-square"></i></div></div></div>';
return $output;
}
} 


//////////////////////////////////////////////////////////////////
// User login/register link with popup
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_user_modal_shortcode') ) {
function wpsm_user_modal_shortcode( $atts, $content = null ) {
extract(shortcode_atts(array(
	'wrap' => 'span',
	'as_btn' => '',
	'class' => '',
	'loginurl' => '',
	'icon' => '',		
), $atts));
wp_enqueue_script('rehubuserlogin');
$as_button = (!empty($as_btn)) ? ' wpsm-button white medium ' : '';
$icon_class = (!empty($icon)) ? $icon : 'rhicon rhi-sign-in';
$class_show = (!empty($class)) ? ' '.$class.'' : '';
$output='';
if (is_user_logged_in()) {
	global $current_user;
	$notification_bp_item = '';
	$user_id  = get_current_user_id();
	$current_user = wp_get_current_user();
	$profile_url  = rehub_option('userlogin_profile_page');
	$submit_url = rehub_option('userlogin_submit_page');
	$edit_url = rehub_option('userlogin_edit_page');
	$submit_url_label = rehub_option('userlogin_submit_page_label');
	$edit_url_label = rehub_option('userlogin_edit_page_label');	
	if(function_exists('mycred_render_shortcode_my_balance')){
	    if(rehub_option('rh_mycred_custom_points')){
	        $custompoint = rehub_option('rh_mycred_custom_points');
	        $mycredpoint = mycred_render_shortcode_my_balance(array('type'=>$custompoint, 'user_id'=>$user_id, 'wrapper'=>'', 'balance_el' => '') );
	        $mycredlabel = mycred_get_point_type_name($custompoint, false);
	    }
	    else{
	    	if(!rehub_option('cashback_points')){
	        	$mycredpoint = mycred_render_shortcode_my_balance(array('user_id'=>$user_id, 'wrapper'=>'', 'balance_el' => '') );
	        	$mycredlabel = mycred_get_point_type_name('', false); 
	        }          
	    }
	} 
	$notice_number = $unread_notice = $unread_message = $unread_enquiry = $notice_bp_number = 0;
	$notification_bp_item = '';  
	if ( function_exists('bp_notifications_get_notifications_for_user')) {
		$notifications = bp_notifications_get_notifications_for_user($user_id, 'object');		
		if (!empty($notifications)){
			$notice_bp_number = count($notifications);
			foreach ((array)$notifications as $notification) {
				$notice_number ++;
				$notification_bp_item .= '<li id="bp-profile-menu-note-'.$notification->id.'" class="bp-profile-menu-item menu-item bppmi_'.$notice_number.' bp-profile-menu-'.$notification->component_action.'"><a href="'.$notification->href.'">'.$notification->content.'</a></li>';
			}			
		}
	}

	if( defined( 'WCFMmp_TOKEN') && wcfm_is_vendor($user_id) ){
		global $WCFM;
		if( apply_filters( 'wcfm_is_pref_direct_message', true ) && apply_filters( 'wcfm_is_allow_notifications', true ) && apply_filters( 'wcfm_is_allow_sc_notifications', true ) ) {
			$unread_message = $WCFM->wcfm_notification->wcfm_direct_message_count( 'message' );
		}
		if(apply_filters( 'wcfm_is_pref_enquiry', true ) && apply_filters( 'wcfm_is_allow_enquiry', true ) && apply_filters( 'wcfm_is_allow_sc_enquiry_notifications', true ) ) { 
			$unread_enquiry = $WCFM->wcfm_notification->wcfm_direct_message_count( 'enquiry' );
		}
		if(apply_filters( 'wcfm_is_pref_notice', true ) && apply_filters( 'wcfm_is_allow_notice', true ) && apply_filters( 'wcfm_is_allow_sc_notice_notifications', true ) ) {
			$unread_notice = $WCFM->wcfm_notification->wcfm_direct_message_count( 'notice' );
		}
		
		$notice_bp_number = $notice_bp_number + $unread_notice + $unread_message + $unread_enquiry;

		if($unread_message > 0){
			$notification_bp_item .= '<li id="bp-profile-menu-note-wcfm-message" class="bp-profile-menu-item menu-item"><a href="'.get_wcfm_messages_url().'">'.$unread_message.' '.__("unread messages", "rehub-theme").'</a></li>';
		}
		if($unread_enquiry > 0){
			$notification_bp_item .= '<li id="bp-profile-menu-note-wcfm-enquiry" class="bp-profile-menu-item menu-item"><a href="'.get_wcfm_enquiry_url().'">'.$unread_enquiry.' '.__("unread inquiries", "rehub-theme").'</a></li>';
		}
		if($unread_notice > 0){
			$notification_bp_item .= '<li id="bp-profile-menu-note-wcfm-enquiry" class="bp-profile-menu-item menu-item"><a href="'.get_wcfm_notices_url().'">'.$unread_notice.' '.__("unread notices", "rehub-theme").'</a></li>';
		}				

	}	

	$output .= '<div class="position-relative user-dropdown-intop'.$class_show.'">';
	if (!empty($notice_bp_number)){
		$output .='<span class="rh_bp_notice_profile rehub-main-color-bg">'.$notice_bp_number.'</span>';
	}
    $output .= '<span class="user-ava-intop">'.get_avatar( $user_id, 28 ).'</span>';
    $output .= '<ul class="user-dropdown-intop-menu">';
        $output .= '<li class="user-name-and-badges-intop"><span class="user-image-in-name">'.get_avatar( $user_id, 35 ).'</span>';
        $output .=$current_user->display_name;
        if(function_exists('bp_get_member_type')){
			$membertype = bp_get_member_type($user_id);
			$membertype_object = bp_get_member_type_object($membertype);
			$membertype_label = (!empty($membertype_object) && is_object($membertype_object)) ? $membertype_object->labels['singular_name'] : ''; 
			if(!empty($membertype_label)){
        		$output .='<br /><span class="rh_user_s2_label redcolor font70">'.$membertype_label.'</span>';
			}      	
        }        
        if (!empty($mycredpoint)){
        	$output .='<br />'.$mycredlabel.': '.$mycredpoint.'';
        }
        $cashpoint = 0;
        if(rehub_option('cashback_points') && function_exists('mycred_render_shortcode_my_balance')){
        	$cashpoint = rehub_option('cashback_points');
        	$mycashpoint = mycred_render_shortcode_my_balance(array('type'=>$cashpoint, 'user_id'=>$user_id, 'wrapper'=>'', 'balance_el' => '') );
			$mycashlabel = mycred_get_point_type_name($cashpoint, false);
			if (!empty($mycashpoint)){
				$output .='<br />'.$mycashlabel.': '.$mycashpoint.'';
			}
        }
        $output .= '</li>';
        if (function_exists('bp_core_get_user_domain')) :
			$output .= '<li class="bp-profile-edit-menu-item menu-item"><a href="'.bp_core_get_user_domain( $user_id ).'"><i class="rhicon rhi-cogs"></i></i><span>'. esc_html__("Edit Profile", "rehub-theme") .'</span></a></li>';        	
        endif;
        if ($submit_url) :
        	if(is_numeric($submit_url)){
        		$submit_url = get_the_permalink($submit_url);
        	}
        	if(!$submit_url_label){
        		$submit_url_label = esc_html__("Submit a Post", "rehub-theme");
        	}
        	$output .= '<li class="user-addsome-link-intop menu-item"><a href="'. esc_url($submit_url) .'"><i class="rhicon rhi-cloud-upload"></i><span>'. $submit_url_label .'</span></a></li>';
        endif; 
        if ($edit_url) :
        	if(is_numeric($edit_url)){
        		$edit_url = get_the_permalink($edit_url);
        	}   
        	if(!$edit_url_label){
        		$edit_url_label = esc_html__("Edit My Posts", "rehub-theme");
        	}        	     	
        	$output .= '<li class="user-editposts-link-intop menu-item"><a href="'. esc_url($edit_url) .'"><i class="rhicon rhi-edit"></i><span>'. $edit_url_label .'</span></a></li>';
        endif;
        $ifvendor = false;  
        if (defined('wcv_plugin_dir')) :
		    if (class_exists('WCV_Vendors') && class_exists('WCVendors_Pro') && WCV_Vendors::is_vendor($user_id) ) {
		    		$dashboard_page_ids = (array) get_option( 'wcvendors_dashboard_page_id' );
		    		if(!empty($dashboard_page_ids)){
						$dashboard_page_id  = reset( $dashboard_page_ids );
		        		$redirect_to = get_permalink($dashboard_page_id);
		    		}
		    }
		    elseif (class_exists('WCV_Vendors') && WCV_Vendors::is_vendor($user_id) ) {
		    	$redirect_to = get_permalink(get_option('wcvendors_vendor_dashboard_page_id'));
		    }
        	if (!empty($redirect_to)){
	        	$output .= '<li class="user-editshop-link-intop menu-item"><a href="'. esc_url($redirect_to) .'"><i class="rhicon rhi-shopping-bagfeather" aria-hidden="true"></i><span>'. esc_html__("Manage Your Shop", "rehub-theme") .'</span></a></li>';
	        	$ifvendor = true;        	
        	}
        endif; 
		if( defined( 'WCFMmp_TOKEN' ) && wcfm_is_vendor( $user_id ) ):
			$output .= '<li class="user-editshop-link-intop menu-item"><a href="'. esc_url(get_wcfm_url()) .'"><i class="rhicon rhi-shopping-bagfeather" aria-hidden="true"></i><span>'. esc_html__("Manage Your Shop", "rehub-theme") .'</span></a></li>'; 
			$ifvendor = true;
		endif;        
        if( function_exists('dokan_is_user_seller')) :
        	$is_vendor = dokan_is_user_seller( $user_id );
        	if($is_vendor) :
	        	$output .= '<li class="user-editshop-link-intop menu-item"><a href="'. dokan_get_navigation_url() .'"><i class="rhicon rhi-shopping-bagfeather" aria-hidden="true"></i><span>'. esc_html__("Manage Your Shop", "rehub-theme") .'</span></a></li>'; 
	        	$ifvendor = true;
	        endif; 
        endif; 
		if( function_exists('get_mvx_vendor')) :
        	$is_vendor = is_user_mvx_vendor( $user_id );        
        	if($is_vendor) :
				$dashlink = mvx_vendor_dashboard_page_id();        		
        		if ($dashlink > 0):
	        		$output .= '<li class="user-editshop-link-intop menu-item"><a href="'. get_permalink($dashlink) .'"><i class="rhicon rhi-shopping-bagfeather" aria-hidden="true"></i><span>'. esc_html__("Manage Your Shop", "rehub-theme") .'</span></a></li>'; 
	        	endif; 
	        	$ifvendor = true;        		
    		endif;
        endif; 
		if(function_exists('tutor_utils')){
			$tutordash = tutor_utils()->tutor_dashboard_url();
        	if(!empty($tutordash)){
	        	$output .= '<li class="user-tutor-dashlink menu-item"><a href="'. esc_url($tutordash) .'"><i class="rhicon rhi-edit-regular" aria-hidden="true"></i><span>'. esc_html__("Dashboard", "rehub-theme") .'</span></a></li>';
        	}			
		}
        if(class_exists('Woocommerce') && $ifvendor ==false && rehub_option('disable_woo_scripts') != 1){
        	$myaccpageid = get_option('woocommerce_myaccount_page_id');
        	if(!empty($myaccpageid)){
	        	$output .= '<li class="user-editorders-link-intop menu-item"><a href="'. get_permalink($myaccpageid) .'"><i class="rhicon rhi-shopping-bagfeather" aria-hidden="true"></i><span>'. esc_html__("Manage Your Orders", "rehub-theme") .'</span></a></li>';
        	}         	
        }                                 
        if(has_nav_menu('user_logged_in_menu')):
        	$output .= wp_nav_menu( array( 'theme_location' => 'user_logged_in_menu','menu_class' => '','container' => false,'depth' => 1,'items_wrap'=> '%3$s', 'echo' => false ) );
        endif;
        $output .=$notification_bp_item;
        $output .= '<li class="user-logout-link-intop menu-item"><a href="'. wp_logout_url( home_url()) .'"><i class="rhicon rhi-lock-alt"></i><span>'. esc_html__("Log out", "rehub-theme") .'</span></a></li>';
$output .= '</ul></div>';
} else {
	if(get_option('users_can_register')) :
		if (empty ($loginurl)):
			if ($wrap =='a'):
				$output .= '<a class="act-rehub-login-popup'.$as_button.$class_show.'" data-type="login"  aria-label="Login" href="#"><i class="'.$icon_class.'"></i><span>'.__("Login / Register", "rehub-theme").'</span></a>';
			else:
				$output .= '<span class="act-rehub-login-popup'.$as_button.$class_show.'" data-type="login"><i class="'.$icon_class.'"></i><span>'.__("Login / Register", "rehub-theme").'</span></span>';
			endif;
		else:
			if ($wrap =='a'):
				$output .= '<a class="act-rehub-login-popup'.$as_button.$class_show.'" data-type="url"  aria-label="Login" data-customurl="'.esc_url($loginurl).'"><i class="'.$icon_class.'"></i><span>'.__("Login / Register", "rehub-theme").'</span></a>';
			else:
				$output .= '<span class="act-rehub-login-popup'.$as_button.$class_show.'" data-type="url" data-customurl="'.esc_url($loginurl).'"><i class="'.$icon_class.'"></i><span>'.__("Login / Register", "rehub-theme").'</span></span>';
			endif;			
		endif;
	else:
		$output .= '<a class="act-rehub-login-popup'.$as_button.$class_show.'" data-type="restrict" href="#"><i class="'.$icon_class.'"></i><span>'.__("Login / Register is disabled", "rehub-theme").'</span></a>';
	endif;	
	
}

return $output;

}
}

//////////////////////////////////////////////////////////////////
// Search form
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_searchform_shortcode') ) {
function wpsm_searchform_shortcode( $atts, $content = null ) {
extract(shortcode_atts(array(
	'class' => '',		
), $atts));

return '<div class="'.$class.'">'.get_search_form(false).'</div>';

}
}

//////////////////////////////////////////////////////////////////
// Link hide
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_hidelink_shortcode') ) {
function wpsm_hidelink_shortcode( $atts, $content = null ) {

	extract(shortcode_atts(array(
			'text' => 'Click here',
			'link' => '',
	), $atts));

	$output = '<span class="ext-source" data-dest="'.$link.'">'.$text.'</span>';
	return $output;
}
}


//////////////////////////////////////////////////////////////////
// Compare Buttons
//////////////////////////////////////////////////////////////////

if( !function_exists('wpsm_comparison_button') ) {
function wpsm_comparison_button( $atts, $content = null ) {
        $atts = shortcode_atts(
			array(
				'color' => 'white',
				'size' => 'small',
				'cats' => '',
				'class' => '',
				'id' => '',
				'woocats' => '',
				'label' => esc_html__("Add to compare", "rehub-theme"),
			), $atts);
	$postid = (!empty($atts['id'])) ? $atts['id'] : get_the_ID();
	$multicats_on = $multicats_array = '';	 
	if(function_exists('rehub_get_compare_multicats')){
		$multicats_array = rehub_get_compare_multicats();
	}
	if(!empty($multicats_array)){
		$multicats_on = true;
	}
	$singlecat_on = rehub_option('compare_page');
	if($multicats_on == '' && $singlecat_on == '') return;	
	if (isset ($atts['cats']) && !empty($atts['cats'])) : //Check if button is not in category
		$cats_array = explode (',', $atts['cats']);
		if (!in_category ($cats_array, $postid)) return;
	endif;
	if (isset ($atts['woocats']) && !empty($atts['woocats'])) : //Check if button is not in woocategory
		$cats_array = explode (',', $atts['woocats']);
		if (!has_term($cats_array, 'product_cat', $postid)) return;
	endif;	     
    $class_show = (!empty($atts['class'])) ? ' '.$atts['class'].'' : '';
	$ip = rehub_get_ip();
	$userid = get_current_user_id();
	$userid = empty($userid) ? $ip : $userid;

	$post_ids_arr = array();
	
	if($multicats_on) {
		foreach( $multicats_array as $multicat ){
			$page_id = (int)$multicat[2];
			$post_ids_arr[] = get_transient('re_compare_'. $page_id .'_' . $userid);
		}
		$post_ids = implode(',', $post_ids_arr);
	} else {
		$post_ids = get_transient('re_compare_' . $userid);
	}
	
	if(!empty($post_ids)) {
		$post_ids_arr = explode(',', $post_ids);
	}

	$compare_active = ( in_array( $postid, $post_ids_arr ) ) ? ' comparing' : ' not-incompare';
	
	$out = '<span';   
    $out .=' class="wpsm-button wpsm-button-new-compare addcompare-id-'.$postid.' '.$atts['color'].' '.$atts['size'].''.$compare_active.$class_show.'" data-addcompare-id="'.$postid.'"><i class="rhicon re-icon-compare"></i><span class="comparelabel">'.esc_attr($atts['label']).'</span></span>';
    return $out;
}
}


//////////////////////////////////////////////////////////////////
// Get custom value shortcode
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_get_custom_value') ) {
function wpsm_get_custom_value($atts, $content = null){
	extract(shortcode_atts(array(
	    'post_id' => NULL,
	    'field' => NULL,
	    'subfield' => NULL,
	    'subsubfield' => NULL,
	    'attrfield' => '',
	    'type' => 'custom',
	    'show_empty' => '',
	    'label' => '',
	    'posttext' => '',
	    'icon' => '',
	    'list' => '',
	    'labelclass' => '',
	    'labelblock' => '',
	    'posttextclass' => '',
	    'showtoggle' => '',
	    'symbollimit' => '',
		'spanvalue'=> '1',
		'imageMapper' => '',

	), $atts));
  	if(!$field && !$attrfield) return;
	$field = trim($field);  
	$attrfield = trim($attrfield);	
  	$result = $out = '';
    $field = esc_attr($field);
    $attrfield = esc_attr($attrfield);
    global $post;
    $post_id = (NULL === $post_id && is_object($post)) ? $post->ID : (int)$post_id;
    if ($type=='custom'){
    	$result = get_post_meta($post_id, $field, true);
    }else if(($type=='attribute' || $type=='local') && function_exists('wc_get_product')){
		if($post_id){
			$post_id = trim($post_id);
			$post_id = (int)$post_id;
			$product = wc_get_product( $post_id );
			if(!$product) return;
		}else{
			global $product;
			if ( ! is_object( $product)) $product = wc_get_product( get_the_ID() );
			if(!$product) return;
		}
        if($attrfield) $field = $attrfield;
        if(!empty($product)){
	        $woo_attr = $product->get_attribute(esc_html($field));
	        if(!is_wp_error($woo_attr)){
	            $result = $woo_attr;
	        }
        }    	
    }
    else if($type=='checkattribute' && function_exists('wc_get_product')){
		if($post_id){
			$post_id = trim($post_id);
			$post_id = (int)$post_id;
			$product = wc_get_product( $post_id );
			if(!$product) return;
		}else{
			global $product;
			if ( ! is_object( $product)) $product = wc_get_product( get_the_ID() );
			if(!$product) return;
		}
        if($attrfield) $field = $attrfield;
        if(!empty($product)){
	        $woo_attr = $product->get_attribute(esc_html($field));
	        if(!is_wp_error($woo_attr)){
	            $result = $woo_attr;
	        }
        } 
    	if (!empty($result)){
			$content = do_shortcode($content);
			$content = preg_replace( '%<p>&nbsp;\s*</p>%', '', $content ); 
			$content = preg_replace('/^(?:<br\s*\/?>\s*)+/', '', $content);
			return $content;
    	} 
    	return false;
    }
	else if($type=='swatch' && function_exists('wc_get_product')){
		if($post_id){
			$post_id = trim($post_id);
			$post_id = (int)$post_id;
			$product = wc_get_product( $post_id );
			if(!$product) return;
		}else{
			global $product;
			if(!$product) return;
		}
        if($attrfield) $field = $attrfield;
        $taxonomy = esc_html($field);
		$attribute_id = wc_attribute_taxonomy_id_by_name( $taxonomy );
		if($attribute_id){
			$attribute = wc_get_attribute( $attribute_id );        
	        if(!empty($attribute)){
	        	$swatch_type = $attribute->type;
	        	if($swatch_type == 'select'){
			        $woo_attr = $product->get_attribute(esc_html($field));
			        if(!is_wp_error($woo_attr)){
			            $result = $woo_attr;
			        }
	        	}else{
	        		if ( false === strpos( $taxonomy, 'pa_' ) ) {
	        			$taxonomy = 'pa_'.$taxonomy;
	        		}
	        		$terms = wc_get_product_terms( $product->get_id(), $taxonomy, array( 'fields' => 'all' ) );
	        		$result .= '<span class="rh_swatch_getter">';
	        		foreach ( $terms as $term ) {
	        			$term_swatch = get_term_meta( $term->term_id, "rh_swatch_{$swatch_type}", true );
	        			if($term_swatch){
							switch( $swatch_type ) {
								case 'color':
									$style = 'background-color:'. $term_swatch .';';
									break;
								case 'image':
									$style = 'background-image:url('. esc_url( wp_get_attachment_thumb_url( $term_swatch ) ) .');';
									break;
								default:
								   $style = '';
							}
							$attributelabel = 'text' == $swatch_type ? $term_swatch : '';	        				
							$result .= '<span class="rh-var-label label-non-selectable '.$swatch_type.'-label-rh" style="'. $style .'">'. $attributelabel .'</span>';	        				
	        				
	        			}
	        		}
	        		$result .= '</span>';
	        	}
	        }			
		}
    }    
	else if($type=='vendor'){
		$vendor_id = get_query_var( 'author' );
		if(!empty($vendor_id)){
			$result = get_user_meta($vendor_id, $field, true);		
		}	
    }  
	else if($type=='taxonomy'){
		$terms = get_the_terms($post_id, esc_html($field));
        if ($terms && ! is_wp_error($terms)){
            $term_slugs_arr = array();
            foreach ($terms as $term) {
                $term_slugs_arr[] = ''.$term->name.'';
            }
            $terms_slug_str = join(", ", $term_slugs_arr);
            $result = $terms_slug_str;
        }
    }
	else if($type=='taxonomylink'){
    	$term_list = get_the_term_list($post_id, esc_html($field), '', ', ', '' );
        if(!is_wp_error($term_list)){
            $result = $term_list;
        }
    }
	else if($type=='author'){
		$author_id=$post->post_author;
		if(!empty($author_id)){
			$result = get_user_meta($author_id, $field, true);
		}	
    }   
	else if($type=='date'){
		if($field == 'year'){
			return date_i18n("Y");
		}else if($field == 'month'){
			return date_i18n("F");
		}	
    }     
	else if($type=='attributelink'){
		if($attrfield) $field = $attrfield;
		if(function_exists('wc_get_product_terms')) {
	        $attribute_values = wc_get_product_terms( $post->ID, $field, array( 'fields' => 'all' ) );
	        $values = array();
	        foreach ( $attribute_values as $attribute_value ) {
	            $value_name = esc_html( $attribute_value->name );
	            $values[] = '<a href="' . esc_url( get_term_link( $attribute_value->term_id, $field ) ) . '" rel="tag">' . $value_name . '</a>';
	        }
	        $result = implode (',', $values); 
        }  	
    }
    else if($type=='checkmeta'){
    	$result = get_post_meta($post_id, $field, true);
    	if (!empty($result)){
			$content = do_shortcode($content);
			$content = preg_replace( '%<p>&nbsp;\s*</p>%', '', $content ); 
			$content = preg_replace('/^(?:<br\s*\/?>\s*)+/', '', $content);
			return $content;
    	} 
    	return false;
    }
    else{
    	$result = get_post_meta($post_id, $field, true);
    }
    if(isset($subfield) && isset($subsubfield) && is_array($result)){
    	$result = $result[$subfield][$subsubfield];
    }
    else if(isset($subfield) && is_array($result)){
    	$result = $result[$subfield];
    }
    if($symbollimit){
    	$result = kama_excerpt('maxchar='.(int)$symbollimit.'&text='.$result.'&echo=false');
    }
	if($labelblock){
		$labelclass = $labelclass.' blockstyle';
	}else{
		$labelclass = $labelclass.' mr5 rtlml5';
	}    
    if($result && !is_array($result)){
    	if($label && !$labelblock) {$out .='<div class="rh-flex-center-align">';}
	    if($list){
	    	$out .= '<li class="ml15 list-type-disc mb0 lineheight15">';
	    }     	
    	if ($icon){
    		$out .= '<i class="meta_icon_label '.esc_attr($icon).'"></i> ';
    	}     	
    	if ($label){
    		$out .= '<span class="meta_v_label '.esc_attr($labelclass).'">'.esc_attr($label).'</span> ';
    	}  
    	if($showtoggle){
    		$out .= '<i class="rhicon rhi-check-circle-solid greencolor"></i>';
    	}else{
			if($spanvalue){
				$out .= '<span class="meta_v_value">';
			}
			$key = '';
			if(!empty($imageMapper)){
				$key = array_search($result, $imageMapper);
				if($key){
					$out .= wp_get_attachment_image( (int)$key, 'full');
				}
			}
			if(!$key){
				$out .= $result;
			}
			if($spanvalue){
				$out .='</span>';
			}

    	}
    	
    	if ($posttext){
    		$out .= '<span class="meta_v_posttext '.esc_attr($posttextclass).'">'.esc_attr($posttext).'</span> ';
    	}
	    if($list){
	    	$out .= '</li>';
	    } 
	    if($label && !$labelblock) {$out .='</div>';}   	    	
    } 
    else{
    	if($show_empty){
		    if($list){
		    	$out .= '<li class="ml15 list-type-disc mb0 lineheight15">';
		    }    		
	    	if ($icon){
	    		$out .= '<i class="meta_icon_label '.esc_attr($icon).'"></i> ';
	    	}     		
	    	if ($label){
	    		$out .= '<span class="meta_v_label '.esc_attr($labelclass).'">'.esc_attr($label).'</span> ';
	    	}
	    	if($showtoggle){
	    		$out .= '<i class="rhicon rhi-times redcolor"></i>';
	    	}else{
	    		$out .= '-';
	    	}	    	
		    if($list){
		    	$out .= '</li>';
		    }	    	   		
    	}
    }
    if($list){
    	$out .= '</ul>';
    }      
    return $out; 

}
}

//////////////////////////////////////////////////////////////////
// Taxonomy Catalog Shortcode
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_tax_archive_shortcode') ) {
function wpsm_tax_archive_shortcode( $atts, $content = null ) {
	// Attributes
	extract( shortcode_atts(
		array(
			'type' => 'compactbig',
			'taxonomy' => 'store',
			'show_images' => 1,
			'limit' =>'',
			'random' => '',
			'imageheight' => 50,
			'classcol' => 'col_wrap_fifth',
			'classitem' => '',
			'child_of' => '',
			'rows' => 1,
			'include' => '',
			'excludeToggle' => '',
			'anchor_before' => '',
			'anchor_after' => '',
			'wrapclass' => 'no_padding_wrap',
			'hide_empty' => true,
			'showcount' => '',
			'leftimage' => '',
			'originalimg' => ''
		), $atts, 'wpsm_tax_archive' )
	);

	$thumbnail_url = '';

	if($random){
		$number = '';
	}else{
		$number = $limit;
	}

	if(false !== strpos( $taxonomy, ',' )){
		$taxonomy = array_map( 'trim', explode( ",", $taxonomy));
	}

	$args = array( 'hide_empty' => $hide_empty, 'orderby'=>'name', 'order' => 'ASC', 'taxonomy'=> $taxonomy, 'number'=> $number, 'child_of' => $child_of);

	if($include){
		$args['include'] = array_map( 'trim', explode( ",", $include ) );
		$args['orderby'] = 'include';
	}

	if(!is_array($taxonomy) && $taxonomy == 'product_cat' && $type !='alpha'){
		$args['orderby'] = 'menu_order';
	}

	if($excludeToggle && $include){
		unset($args['include']);
		$args['exclude'] = array_map( 'trim', explode( ",", $include ) );
	}
	 
	$terms = get_terms($args );

	if(is_wp_error($terms)) return;

	if($random){
		shuffle($terms);
		if ($limit){
			$terms = array_slice($terms, 0, $limit);
		}
	}

	$letter_keyed_terms = array();

	$term_letter_links = '';
	$term_titles = '';

	if($type == 'alpha') {
		if( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach( $terms as $term ) {
				$first_letter = mb_substr( $term->name, 0, 1, 'UTF-8' );
				
				if( is_numeric( $first_letter ) ) {
					$first_letter = '0-9';
				} else {
					$first_letter = mb_strtoupper( $first_letter, 'UTF-8' );
				}
				
				if ( !array_key_exists( $first_letter, $letter_keyed_terms ) ) {
					$letter_keyed_terms[ $first_letter ] = array();
				}
				
				$letter_keyed_terms[ $first_letter ][] = $term;
			}
			
			foreach( $letter_keyed_terms as $letter => $terms ) {

				$term_letter_links .= '<li><a href="#'.rh_convert_cyr_symbols($letter).'" class="font120 blackcolor rehub_scroll">'.$letter.'</a></li>';

				$term_titles .= '<div class="single-letter mt20 mb20 pb10 border-grey-bottom"><a href="#" name="'.rh_convert_cyr_symbols($letter).'"></a><div class="letter_tag fontbold font120 lineheight20">'.$letter.'<div class="return_to_letters cursorpointer floatright font80"><span class="rehub_scroll rehub-main-color-bg" data-scrollto="#top_ankor"><i class="rhicon rhi-angle-up"></i></span></div></div></div>';
				$term_titles .= '<div class="tax-wrap flowhidden rh-flex-eq-height">';
										
				foreach( $terms as $term ) {

					$thumbnail = $thumbnail_url = '';
					
					if ( $taxonomy == 'product_tag' && $show_images == 1 ) {
						  	$term_tag_array = get_option( 'taxonomy_term_'. $term->term_id ); 
						  	if (!empty ($term_tag_array['brand_image'])) {
							  	$showbrandimg = new WPSM_image_resizer();
				                $showbrandimg->height = $imageheight;
				                $showbrandimg->src = $term_tag_array['brand_image'];
				                $thumbnail_url = $showbrandimg->get_resized_url();					  		
						  	}					  
						if ( $thumbnail_url ) {
							$thumbnail = '<img src="'. $thumbnail_url .'" alt="'. $term->name .'" />';
						}
					}
					elseif ( $taxonomy == 'store' && $show_images == 1 ) {
							$brandimage = get_term_meta( $term->term_id, 'brandimage', true ); 
						  	if (!empty ($brandimage)) {
							  	$showbrandimg = new WPSM_image_resizer();
				                $showbrandimg->height = $imageheight;
				                $showbrandimg->src = $brandimage;
				                $thumbnail_url = $showbrandimg->get_resized_url();					  		
						  	}					  
						if ( $thumbnail_url ) {
							$thumbnail = '<img src="'. $thumbnail_url .'" alt="'. $term->name .'" />';
						}
					}
					elseif ( $taxonomy == 'dealstore' && $show_images == 1 ) {
							$brandimage = get_term_meta( $term->term_id, 'brandimage', true ); 
						  	if (!empty ($brandimage)) {
							  	$showbrandimg = new WPSM_image_resizer();
				                $showbrandimg->height = $imageheight;
				                $showbrandimg->src = $brandimage;
				                $thumbnail_url = $showbrandimg->get_resized_url();					  		
						  	}					  
						if ( $thumbnail_url ) {
							$thumbnail = '<img src="'. $thumbnail_url .'" alt="'. $term->name .'" />';
						}
					}	

					elseif ( $taxonomy == 'product_cat' && $show_images == 1 ) {
							$brandimageid = get_term_meta( $term->term_id, 'thumbnail_id', true ); 
						  	if ($brandimageid) {
						  		$brandimage = wp_get_attachment_url( $brandimageid );
						  		if ( $brandimage ) {
								  	$showbrandimg = new WPSM_image_resizer();
					                $showbrandimg->height = $imageheight;
					                $showbrandimg->src = $brandimage;

					                $thumbnail_url = $showbrandimg->get_resized_url();	
						  		}				  		
						  	}					  
						if ( $thumbnail_url ) {
							$thumbnail = '<img src="'. $thumbnail_url .'" alt="'. $term->name .'" />';
						}
					}									
					
					$term_titles .= '<a class="single-letter-link rh-flex-column rh-flex-center-align rh-flex-justify-center pt10 pb10 pl10 pr10 floatleft mt10 mr10 text-center" id="taxonomy-'. $term->term_id .'"  href="' . esc_url( get_term_link( $term ) ) . '" title="' . esc_attr( sprintf( esc_html__( 'View all post filed under %s', 'rehub-theme' ), $term->name ) ) . '">' . $thumbnail . '<h5 class="mt5 mb0 font80 fontnormal">'. $term->name . '</h5></a>';
				}
				
				$term_titles .= '</div>';		
			}
		}
		
		return	'<div class="alphabet-filter">
					<style scoped>
						.alphabet-filter .list-inline{margin:0;list-style:none}
						.alphabet-filter .list-inline>li{display:inline-block;padding-right:5px;padding-left:5px;margin:0}
						.alphabet-filter .list-inline>li:first-child{margin-left: 0;padding-left:0}
						.alphabet-filter .return_to_letters span{color:#fff;width: 18px;height: 18px;display: inline-block;text-align: center;line-height: 18px;}
						.alphabet-filter a.single-letter-link{text-decoration: none !important; border: 1px solid #E9E9E9; box-shadow: 0 1px 2px rgba(0,0,0,0.05);width: 102.5px }
						.alphabet-filter a.single-letter-link img{ max-width: 100%;}
						.alphabet-filter a.single-letter-link:hover, .alphabet-filter a.compact-tax-link:hover{ box-shadow: none; border: 1px solid #333}
						.alphabet-filter a.compact-tax-link{display: inline-block;padding: 5px 12px;text-decoration: none !important; border: 1px solid #E9E9E9; box-shadow: 0 1px 2px rgba(0,0,0,0.05);}
						.alphabet-filter a.logo-tax-link{display: table-cell; vertical-align:middle; text-align:center; padding: 5px 12px;text-decoration: none !important; border: 1px solid #E9E9E9; box-shadow: 0 1px 2px rgba(0,0,0,0.05); height: 55px}
						.alphabet-filter a.logo-tax-link img{max-width: 100px; max-height: 55px}
						@media only screen and (max-width: 479px) {
							.alphabet-filter a.single-letter-link{width:30%;}
						}
					</style>
					<div class="head-wrapper mb15 pt10 pb10 pl15 pr15 lightgreybg clearfix">
						<ul class="list-inline">
							'. $term_letter_links .'
						</ul>
					</div>
					<div class="body-wrapper clearfix">
							'. $term_titles .'
					</div>
				</div>';		
	}
	elseif ($type == 'compact') {
		if( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach( $terms as $term ) {
				$term_titles .= '<a class="'.$classitem.' compact-tax-link tax-item text-center floatleft mt10 mr10" href="' . esc_url( get_term_link( $term ) ) . '" title="' . esc_attr( sprintf( esc_html__( 'View all post filed under %s', 'rehub-theme' ), $term->name ) ) . '"><h5 class="mt0 mb0 font80 lineheight15">'.esc_html($anchor_before).$term->name.esc_html($anchor_after).'</h5></a>';
			}
			return '<div class="alphabet-filter">'.$term_titles.'</div>';	
		}
	}
	elseif ($type == 'inlinelinks') {
		if( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach( $terms as $term ) {
				$term_titles .= '<span class="inlinestyle position-relative pt10 pb10 pl15 pr15 '.$classitem.'"><a class="" href="' . esc_url( get_term_link( $term ) ) . '" title="' . esc_attr( sprintf( esc_html__( 'View all post filed under %s', 'rehub-theme' ), $term->name ) ) . '"><span class="blockstyle">'.esc_html($anchor_before).$term->name.esc_html($anchor_after).'</span><span class="blockstyle rh_opacity_7 font80">'.$term->count.__(' Products', 'rehub-theme').'</span></a></span>';
			}
			return '<div class="flowhidden">'.$term_titles.'</div>';	
		}
	}	
	elseif ($type == 'compactbig') {
		if( ! empty( $terms ) && ! is_wp_error( $terms ) ) {

			foreach( $terms as $term ) {
				$term_titles .= '<a class="'.$classitem.' col_item mb10 text-center rh-main-bg-hover blackcolor rh-cartbox big-tax-link" href="' . esc_url( get_term_link( $term ) ) . '" title="' . esc_attr( sprintf( esc_html__( 'View all post filed under %s', 'rehub-theme' ), $term->name ) ) . '"><div class="rehub-main-font">'.esc_html($anchor_before).$term->name.esc_html($anchor_after).'</div></a>';
			}
			return '<div class="'.$classcol.' rh-flex-eq-height">'.$term_titles.'</div>';	
		}
	}	
	elseif ($type == 'logo') {
		if( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach( $terms as $term ) {
				$thumbnail = $thumbnail_url = '';
				
				if ( $taxonomy == 'product_tag' && $show_images == 1 ) {
					  	$term_tag_array = get_option( 'taxonomy_term_'. $term->term_id ); 
					  	if (!empty ($term_tag_array['brand_image'])) {
						  	$showbrandimg = new WPSM_image_resizer();
			                $showbrandimg->height = $imageheight;
			                $showbrandimg->src = $term_tag_array['brand_image'];
			                $thumbnail_url = $showbrandimg->get_resized_url();					  		
					  	}					  
					if ( $thumbnail_url ) {
						$thumbnail = '<img src="'. $thumbnail_url .'" alt="'. $term->name .'" />';
					}
				}
				elseif ( $taxonomy == 'store' && $show_images == 1 ) {
						$brandimage = get_term_meta( $term->term_id, 'brandimage', true ); 
					  	if (!empty ($brandimage)) {
						  	$showbrandimg = new WPSM_image_resizer();
			                $showbrandimg->height = $imageheight;
			                $showbrandimg->src = $brandimage;
			                $thumbnail_url = $showbrandimg->get_resized_url();					  		
					  	}					  
					if ( $thumbnail_url ) {
						$thumbnail = '<img src="'. $thumbnail_url .'" alt="'. $term->name .'" />';
					}
				}
				elseif ( $taxonomy == 'product_cat' && $show_images == 1 ) {
						$brandimageid = get_term_meta( $term->term_id, 'thumbnail_id', true ); 
					  	if ($brandimageid) {
					  		$brandimage = wp_get_attachment_url( $brandimageid );
					  		if ( $brandimage ) {
							  	$showbrandimg = new WPSM_image_resizer();
				                $showbrandimg->height = $imageheight;
				                $showbrandimg->src = $brandimage;
				                $thumbnail_url = $showbrandimg->get_resized_url();	
					  		}				  		
					  	}					  
					if ( $thumbnail_url ) {
						$thumbnail = '<img src="'. $thumbnail_url .'" alt="'. $term->name .'" />';
					}
				}				
				elseif ( $taxonomy == 'dealstore' && $show_images == 1 ) {
						$brandimage = get_term_meta( $term->term_id, 'brandimage', true ); 
					  	if (!empty ($brandimage)) {
						  	$showbrandimg = new WPSM_image_resizer();
			                $showbrandimg->height = $imageheight;
			                $showbrandimg->src = $brandimage;
			                $thumbnail_url = $showbrandimg->get_resized_url();					  		
					  	}					  
					if ( $thumbnail_url ) {
						$thumbnail = '<img src="'. $thumbnail_url .'" alt="'. $term->name .'" />';
					}
				}
				if ($thumbnail){
					$term_titles .= '<a class="'.$classitem.' col_item mb10 two_column_mobile rh-flex-center-align rh-flex-justify-center text-center rh-cartbox pt10 pb10 pl10 pr10 logo-tax-link" href="' . esc_url( get_term_link( $term ) ) . '" title="' . esc_attr( sprintf( esc_html__( 'View all post filed under %s', 'rehub-theme' ), $term->name ) ) . '">'. $thumbnail . '</a>';					
				}
			}
			return '<div class="'.$classcol.' rh-flex-eq-height">'.$term_titles.'</div>';	
		}
	}
	elseif ($type == 'woocategory' || $type == 'postcategory') {
		if($type == 'woocategory'){
			$taxonomy = 'product_cat';
		}
		if( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach( $terms as $term ) {
				$thumbnail_url = $term_childes = $right_style = '';
				if( $term->parent == '0' ){
					$term_titles .= '<div class="rh-heading-hover-color col_item rh-cartbox woocatbox pt15 pb15 pr15 pl15 rh-hov-bor-line product-category rh-flex-center-align '. $classitem .'">';
					if ($show_images == 1){
						$brandimageid = get_term_meta( $term->term_id, 'thumbnail_id', true ); 
						if($brandimageid){
							$brandimage = wp_get_attachment_url($brandimageid);
						}
						else{
							$brandimage = get_term_meta( $term->term_id, 'brandimage', true );
						}
						if($brandimage){
							if ($originalimg){
								$thumbnail_url = $brandimage;
							}else{
								$showbrandimg = new WPSM_image_resizer();
								$showbrandimg->height = $imageheight;
								$showbrandimg->src = $brandimage;
								$thumbnail_url = $showbrandimg->get_resized_url();
							}
						}
						$imagewidth = $imageheight + 10;
						$right_style = 'style="width:calc(100% - '. $imagewidth .'px);"';
					}
					//fetch subcategories
					$child_ids = get_term_children($term->term_id, $taxonomy);
					$subcat_total = count($child_ids);
					$ellipsis = ($subcat_total > 10) ? '&hellip;' : '';
					$child_ids = array_slice($child_ids, 0, 10);
					$subcat_sliced = count($child_ids);
					for($i = 0; $i < $subcat_sliced; ++$i) {
						$coma = ($i == ($subcat_sliced-1)) ? '' : ',';
						$child_term = get_term_by( 'id', $child_ids[$i], $taxonomy );
						$term_childes .= '<a href="'. esc_url(get_term_link($child_term, $taxonomy)) .'">'. esc_html($child_term->name) .'</a>'. $coma .' ';
					}
					if (rehub_option('enable_lazy_images') == '1'){
						$imgcl = 'class="lazyload" data-src="'.esc_url($thumbnail_url).'" src="'.get_template_directory_uri() . '/images/default/blank.gif"';
					}
					else{
						$imgcl = 'src="'. esc_url($thumbnail_url) .'"';
					}
					if($thumbnail_url && $leftimage){
						$term_titles .= '<a href="'. esc_url(get_term_link($term)) .'" title="'. esc_attr(sprintf( esc_html__('View all post filed under %s', 'rehub-theme' ), $term->name)) .'" class="mr15"><img src="'. esc_url($thumbnail_url) .'" alt="'. esc_attr($term->name) .'"';
						if(!$originalimg){
							$term_titles .= ' width="'. $imageheight .'"  height="'. $imageheight .'"';
						}
						$term_titles .= '/></a>';
					}					
					$term_titles .= '<div '. $right_style .'><h5 class="mb10 font110 mt0"><a class="" href="'. esc_url(get_term_link($term)) .'" title="'. esc_attr(sprintf( esc_html__('View all post filed under %s', 'rehub-theme' ), $term->name)) .'">'. esc_html($term->name) . '</a></h5>';
					if($showcount){
						$term_titles .= '<div class="greycolor mb10 font90">'.$term->count.' '._n('item', 'items', $term->count, 'rehub-theme').'</div>';
					}					
					$term_titles .= '<div class="subcategortes font70 lineheight15 blackcolor wordbreak">'. $term_childes .''. $ellipsis .'</div>'; 
					$term_titles .= '</div>';					
					if($thumbnail_url && !$leftimage){
						$term_titles .= '<a href="'. esc_url(get_term_link($term)) .'" title="'. esc_attr(sprintf( esc_html__('View all post filed under %s', 'rehub-theme' ), $term->name)) .'" class="rh-flex-right-align"><img '.$imgcl.' alt="'. esc_attr($term->name) .'"';
						if(!$originalimg){
							$term_titles .= ' width="'. $imageheight .'"  height="'. $imageheight .'"';
						}
						$term_titles .= '/></a>';
					}				
					$term_titles .= '</div>';
				}
			}
			return '<div class="'. $wrapclass .'"><div class="'. $classcol .' rh-flex-eq-height mb20">'. $term_titles .'</div></div>';
		}
	}	
	elseif ($type == 'storegrid') {
		if($classcol == 'col_wrap_tenth'){
			$columns = 10;
			$classitem .= ' rh-flex-center-align rh-flex-justify-center';
		}else{
			$columns = 5;
			$term_titles .= "<style scope>.rh-hover-tax-title{height:".$imageheight."px;}.rh-cash-tax img{max-height:".$imageheight."px;}.rh-hover-tax .rh-hover-tax-inner{position: absolute;top: 0;left: 0;height: 100%;width: 100%;transition: all 0.2s ease-in-out;opacity: 0;line-height: 1.8em;white-space: normal;}.rh-hover-tax:hover .rh-hover-tax-inner{opacity: 1;}</style>";
		}
		if(!empty($terms) && !is_wp_error($terms)) {
			$terms = array_slice($terms, 0, ($columns*$rows));
			foreach( $terms as $term ) {
				$thumbnail = $thumbnail_url = '';
				$term_titles .= '<div class="'. $classitem .' col_item two_column_mobile text-center rh-cartbox mb10 pt10 pb10 pl10 pr10 rh-hover-tax rh-cash-tax">';
				if( $show_images == 1 ) {
						if ($taxonomy == 'product_cat'){
							$brandimageid = get_term_meta( $term->term_id, 'thumbnail_id', true ); 
							$brandimage = ($brandimageid) ? wp_get_attachment_url($brandimageid) : '';
						}else{
							$brandimage = get_term_meta( $term->term_id, 'brandimage', true );
						} 
						if($originalimg){
							$thumbnail_url = $brandimage;
						}else{
						  	if (!empty($brandimage)) {
							  	$showbrandimg = new WPSM_image_resizer();
				                $showbrandimg->height = $imageheight;
				                $showbrandimg->src = $brandimage;
				                $thumbnail_url = $showbrandimg->get_resized_url();					  		
						  	}
						}
					if (rehub_option('enable_lazy_images') == '1'){
						$imgcl = 'class="lazyload" data-src="'.esc_url($thumbnail_url).'" src="'.get_template_directory_uri() . '/images/default/blank.gif"';
					}
					else{
						$imgcl = 'src="'. esc_url($thumbnail_url) .'"';
					}										  
					if ($thumbnail_url) {
						$thumbnail = '<img '.$imgcl.' alt="'. esc_attr($term->name) .'" />';
					}
				}
				if($thumbnail){
					$term_titles .= $thumbnail;					
				}else{
					$term_titles .= '<div class="rh-hover-tax-title rehub-main-font font120 rh-flex-center-align rh-flex-justify-center"><div>'.esc_html($anchor_before).$term->name.esc_html($anchor_after).'</div></div>';
				}
				$get_cashback_notice = get_term_meta($term->term_id, 'cashback_notice', true); 
				$cashback_notice = ($get_cashback_notice) ? $get_cashback_notice : ''; 
				if($columns == 5 && $cashback_notice){
					$term_titles .= '<div class="rehub-main-color lineheight20 pt10 mt10 border-top font90">'. esc_attr($cashback_notice) .'</div>';
				}
				$term_titles .= '<a class="rh-hover-tax-inner rh-flex-center-align rh-flex-justify-center rh-main-bg-hover whitecolor font80" href="'. esc_url(get_term_link($term)) .'" title="'. esc_attr(sprintf(__('View all post filed under %s', 'rehub-theme'), $term->name)) .'"><div><div class="rh-hover-tax-head font120 fontbold">'. esc_html($term->name) .'</div><div class="rh-hover-tax-offer fontbold">'. sprintf( esc_html__( '%d Offers', 'rehub-theme' ), $term->count ) .'</div><div class="rh-hover-tax-cashback">'. esc_attr($cashback_notice) .'</div></div></a>';
				$term_titles .= '</div>';
			}
			return '<div class="'. $classcol .' rh-flex-eq-height">'. $term_titles .'</div>';	
		}
	}		
}
}



//////////////////////////////////////////////////////////////////
// USER REVIEWS BASED ON FULL REVIEWS
//////////////////////////////////////////////////////////////////
if( !function_exists('re_user_rating_shortcode') ) {
function re_user_rating_shortcode( $atts, $content = null ) {
    $atts = shortcode_atts(
	array(
		'size' => 'big',
	), $atts);

    $postAverage = get_post_meta(get_the_ID(), 'post_user_average', true);
    if(!empty($postAverage)){
    	$starscore = $postAverage*10 ;
    	$output = '<div class="star-'.$atts['size'].'"><span class="stars-rate"><span style="width: '.$starscore.'%;"></span></span></div>';
    	return $output;
    }
}
}

//////////////////////////////////////////////////////////////////
// UPDATE BLOCK
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_update_shortcode') ) {
function wpsm_update_shortcode( $atts, $content = null ) {
    $atts = shortcode_atts(
	array(
		'date' => '',
		'label' => '',
	), $atts);
	$date = (!empty($atts['date'])) ? ' - '.$atts['date'].'' : '';
	$label = (!empty($atts['label'])) ? $atts['label'] : esc_html__('Update', 'rehub-theme');
	$content = do_shortcode($content);
	$content = preg_replace( '%<p>&nbsp;\s*</p>%', '', $content ); 
	$content = preg_replace('/^(?:<br\s*\/?>\s*)+/', '', $content);
	$output = '<div class="wpsm_update mobfont90"><span class="label-info">'.$label.$date.'</span>'.$content.'</div>';
	return $output;
}
}


//////////////////////////////////////////////////////////////////
// SPECIFICATION BUILDER
//////////////////////////////////////////////////////////////////
if ( !function_exists( 'wpsm_spec_builders_shortcode' ) ) {
	function wpsm_spec_builders_shortcode( $atts, $content = null ) {
		
		extract(shortcode_atts( array(
				'id' => '',
				'postid' => '',
			), $atts));
			
		if( !empty($id) ) :

			$rows = get_post_meta( $id, '_wpsm_spec_line', true );
			if(empty($rows)) return;

			ob_start(); 
			?>

                <?php 
                	$postID = (!empty($postid)) ? $postid : get_the_ID();
                    $pbid=0;                       
                    foreach ($rows as $row) {
                    echo '<div class="wpsm_spec_row_'.$id.'_'.$pbid.'">';                       
                    $element = $row['column_type'];
                        if ($element == 'heading_line') {
                            include(rh_locate_template('inc/specification/heading_line.php'));
                        } else if ($element == 'meta_line') {
                            include(rh_locate_template('inc/specification/meta_line.php'));                          
                        } else if ($element == 'divider_line') {
                            include(rh_locate_template('inc/specification/divider_line.php'));                            
                        } else if ($element == 'tax_line') {
                            include(rh_locate_template('inc/specification/tax_line.php'));                            
                        } else if ($element == 'shortcode_line') {
                            include(rh_locate_template('inc/specification/shortcode_line.php')); 
                        } else if ($element == 'photo_line') {
                            include(rh_locate_template('inc/specification/photo_line.php'));
                        } else if ($element == 'video_line') {
                            include(rh_locate_template('inc/specification/video_line.php'));
                        } else if ($element == 'mdtf_line') {
                            include(rh_locate_template('inc/specification/mdtf_line.php'));   
                        } else if ($element == 'proscons_line') {
                            include(rh_locate_template('inc/specification/proscons_line.php'));  
                        } else if ($element == 'map_line') {
                            include(rh_locate_template('inc/specification/map_line.php'));
                        } else {
                            
                        };
                    echo '</div>';
                    $pbid++;
                    } 
                ?>

			<?php 
			$output = ob_get_contents();
			ob_end_clean();
			return $output;   
		endif;	

	}
}

//////////////////////////////////////////////////////////////////
// Category box
//////////////////////////////////////////////////////////////////
if ( !function_exists('wpsm_catbox_shortcode') ) {
function wpsm_catbox_shortcode( $atts, $content = null ) {

	extract( shortcode_atts( array(
			'category' => '', // one ID
			'title' => '', // if empty - original title
			'disablelink' => '', // 1 or 0
			'disablechild' => '', // 1 or 0
			'image' => '', // URL or post_id in media library
			'size_img' => '', // % or px ('width' or 'width height')
			'taxslug' => '',
			'tax_name' => ''
		), $atts ) );

	if ( empty( $category ) )
		return;

	if(is_numeric( $category )){
		$term = get_term( (int) $category );
	}elseif($taxslug){
		$term = get_term_by( 'slug', $category, $taxslug );
	}elseif($tax_name){
		$term = get_term_by( 'slug', $category, $tax_name );
	}	
	
 	if ( is_wp_error( $term ) ) {
		$error_string = $term->get_error_message();
		return '<div id="message" class="error"><p><b>Error</b>: Category ID '. $category .' - '. $error_string .'</p></div>';
 	}

	if ( is_numeric( $image ) ) {
		$image = wp_get_attachment_url( $image );
	}
	
	$bg_size = ( $size_img ) ? ' height:'. $size_img .'' : '';
	$termchildren = get_terms( array(
		'taxonomy' => $term->taxonomy,
		'orderby' => 'name',
		'hide_empty' => true,
		'child_of' => $term->term_id
	) );
	$count = $term->count;
	foreach ($termchildren as $tax_term_child) {
        $count +=$tax_term_child->count;
    }		
    wp_enqueue_style('rhbanner');
	// HTML output
	$output = '<div class="rh-cartbox rh-hovered-wrap categoriesbox catbox pt10 pb10 pr10 pl10 mb20">';
		
		if ( $image ){
			$imagehtml = '<img class="lazyload" data-src="'.$image.'" width=300 height=300 alt="'.esc_html($title).'" src="'.get_template_directory_uri() . '/images/default/blank.gif" />';
			$title = ( $title && $title !='' ) ? $title : $term->name;
			$output .= '<div class="rh-transition-box position-relative flowhidden full_cover_link">';					
				if ( !$disablelink && is_numeric( $term->term_id )) {
					$output .= '<a href="'. get_term_link( $term->term_id ) .'">';
				}
				
				if ( !$disablelink ) {
					$output .= '</a>';
				}
				$output .= '<div class="categoriesbox-bg csstranstranslong rh-hovered-scalesmall" style="'. $bg_size .'"><div class="abdfullwidth imageasbg rh-flex-center-align rh-flex-justify-center flowhidden rh-flex-align-stretch rh-fit-cover">'.$imagehtml.'</div></div>';	
				$output .= '<h3 class="blackcolor font110 lineheight20 mb0 ml0 mr0 mt0 pb20 pl5 pr5 pt20 text-center upper-text-trans width-100p zind2">'. $title .'<mark class="blockstyle catcount darkbg font80 height-22 roundborder vertmiddle whitecolor width-22">'.$count.'</mark></h3>';					
			$output .= '</div>';
		}

		if(!$disablechild){
			$output .='<div class="catbox-content mt15 lineheight20 r_offer_details">';
				
				if ( is_wp_error( $termchildren ) ) {
					$error_string = $termchildren->get_error_message();
					return '<div id="message" class="error"><p><b>Error</b>: Category ID '. $category .' - '. $error_string .'</p></div>';
				}

				
				$term_count = count( $termchildren ); 
				if($term_count > 0) {
					$output .= '<ul class="catbox-child-list mt0 mb0 mr0 ml0 pt0 pb0 pl0 pr0 flowhidden">';
					$i = 0;
					foreach ( $termchildren as $termchild ) {

						if ( $i == 3 )
							$output .= '<div class="open_dls_onclk">';
						$output .= '<li class="font80 fontitalic mb5 ml0 mr0 mt0 pb0 pl0 pr0 pt0"><a href="'. get_term_link( (int) $termchild->term_id ) .'" class="greycolor">'. $termchild->name .'</a> ('. (int) $termchild->count .')</li>';
						
						if ( $i == $term_count )
							$output .= '</div>';
						$i++;
					}
					$output .= '</ul>';
				}

				
				if ( $term_count > 3 )
					$output .= '<span class="r_show_hide rehub-sec-color mt5 inlinestyle font90">'.__('See all', 'rehub-theme').'</span>';

				$output .= '</div>';
				
		}	

	$output .= '</div>';

	return $output;
}
}

if (!function_exists('rh_wcv_vendorslist_flat')) {
function rh_wcv_vendorslist_flat( $atts ) {

		$html = ''; 
		
	  	extract( shortcode_atts( array(
	  			'orderby' => 'registered',
	  			'order'	=> 'ASC',
				'per_page' => '12',
				'show_products' => 'yes',
				'search_form' => 0,
				'user_id' => '' 
			), $atts ) );

	  	$paged = ( get_query_var('paged') ) ? get_query_var('paged') : 1;   
	  	$offset = ( $paged - 1 ) * $per_page;
		
		// Search query fom the form
		$search_sellers = isset($_GET['search_sellers']) ? esc_attr($_GET['search_sellers']) : '';
		
		// Sort filter and data from the form change parametres of the WP user query
		$alphabet = $mostpopular = $mostposts = $mostresent = '';
		$selected = ' selected="selected"';
		if (defined('wcv_plugin_dir')){
			$role = 'vendor';
			$meta_key = 'pv_shop_name';
		}
		elseif ( class_exists( 'WeDevs_Dokan' ) ){
			$role = 'seller';
			$meta_key = 'dokan_enable_selling';			
		}
		elseif( defined('WCFMmp_TOKEN') ){
			$role = 'wcfm_vendor';
			$meta_key = 'store_name';
		}			
		
		if( isset($_GET['orderby_sellers']) ) {
			$orderby_sellers = $_GET['orderby_sellers'];
			switch ($orderby_sellers) {
				case 'alphabet':
					$orderby = 'display_name';
					$order = 'ASC';
					$alphabet = $selected;
					break;
				case 'mostpopular':
					$orderby = 'meta_value';
					$order = 'DESC';
					$meta_key = '_rh_user_favorite_shop_count';
					$mostpopular = $selected;
					break;
				case 'mostposts': // omitted
					$mostposts = $selected;
					break;
				default;
					$mostresent = $selected;
			}
		} else {
			$mostresent = $selected;
		}

	  	// Hook into the user query to modify the query to return users that have at least one product 
	  	if ($show_products == 'yes') add_action( 'pre_user_query', 'rh_vendors_with_products' );

	  	// Get all vendors 
	  	$vendor_total_args = array ( 
	  		'role' 			=> $role, 
			'meta_key' 	=> $meta_key, 
			'meta_value'   	=> '',
			'meta_compare'	=> '>',
			'orderby' 		=> $orderby,
  			'order'			=> $order,
	  	);

	  	if ($show_products == 'yes') $vendor_total_args['query_id'] = 'vendors_with_products'; 

	  	$vendor_query = New WP_User_Query( $vendor_total_args ); 
	  	$all_vendors =$vendor_query->get_results(); 

	  	// Get the paged vendors 
	  	$vendor_paged_args = array ( 
	  		'role' 			=> $role, 
			'meta_key' 	=> $meta_key, 
			'meta_value'   	=> '',
			'meta_compare'	=> '>',
			'search'		=> $search_sellers,
			'orderby' 		=> $orderby,
  			'order'			=> $order,
	  		'offset' 		=> $offset, 
	  		'number' 		=> $per_page, 
	  	);

	  	if ($show_products == 'yes' ) $vendor_paged_args['query_id'] = 'vendors_with_products'; 

	  	if ($user_id){
	  		$user_ids = array_map( 'trim', explode( ",", $user_id ) );
		  	$vendor_paged_args = array ( 
		  		'role' 			=> $role, 
				'meta_key' 	=> $meta_key, 
				'meta_value'   	=> '',
				'meta_compare'	=> '>',
				'include' 		=> $user_ids,
		  	);	  		
	  	}	  	

	  	$vendor_paged_query = New WP_User_Query( $vendor_paged_args ); 
	  	$paged_vendors = $vendor_paged_query->get_results(); 

	  	// Pagination calcs 
		$total_vendors = count( $all_vendors );  
		$total_vendors_paged = count($paged_vendors);  
		$total_pages = ceil( $total_vendors / $per_page );
	    
	   	ob_start();
		
		if($search_form ==1){
		$html .='
		<div class="tabledisplay mb20">
			<form id="search-sellers" role="search" method="get" class="celldisplay search-form floatleft mb10">
				<input type="text" name="search_sellers" placeholder="'. esc_html__('Search sellers', 'rehub-theme') .'" value="">
				<button type="submit" alt="'. esc_html__('Search', 'rehub-theme') .'" value="'. esc_html__('Search', 'rehub-theme') .'" class="btnsearch"><i class="rhicon rhi-search"></i></button>
			</form>
			<form id="filter-sellers" method="get" class="celldisplay floatright mb10 ml10">
				<label>'. esc_html__('Sort by:', 'rehub-theme') .'</label>
				<select name="orderby_sellers" class="orderby">
					<option value="alphabet"'. $alphabet .'>'. esc_html__('Alphabetical', 'rehub-theme') .'</option>
					<option value="mostpopular"'. $mostpopular .'>'. esc_html__('Most popular', 'rehub-theme') .'</option>
					<option value="mostresent"'. $mostresent .'>'. esc_html__('Most recent', 'rehub-theme') .'</option>
				</select>
			</form>
			<script>jQuery( function( $ ) {
				$( "#filter-sellers" ).on( "change", "select.orderby", function() {
					$( this ).closest( "form" ).submit();
				});
			});
			</script>
		</div>';
		}

	    // Loop through all vendors and output a simple link to their vendor pages
	    foreach ($paged_vendors as $vendor) {
			if (defined('wcv_plugin_dir')){
				$shop_link = WCV_Vendors::get_vendor_shop_page($vendor->ID);
	    		$shop_name = $vendor->pv_shop_name;
			}
			elseif ( class_exists( 'WeDevs_Dokan' ) ){
	    	    $shop_link = dokan_get_store_url($vendor->ID);		
            	$store_info = dokan_get_store_info( $vendor->ID );
            	$shop_name = isset( $store_info['store_name'] ) ? esc_html( $store_info['store_name'] ) : esc_html__( 'Noname Shop', 'rehub-theme' );	    				
			}
			elseif ( defined( 'WCFMmp_TOKEN' ) ){
				$shop_link = wcfmmp_get_store_url( $vendor->ID );
				$shop_name = get_user_meta( $vendor->ID, 'store_name', true );
			}							    	
	    	$vendor_id= $vendor->ID;
	    	include(rh_locate_template('inc/wcvendor/vendorlist.php'));

	    } // End foreach 
	   	
	   	$html .= '<div class="rh_vendors_listflat">' . ob_get_clean() . '</div>';

	    if ( $total_vendors > $total_vendors_paged ) {  
			$html .= '<nav class="woocommerce-pagination">';  
			  $current_page = max( 1, get_query_var('paged') );  
			  $html .= paginate_links( 	array(  
			        'base' => get_pagenum_link() . '%_%',
			        'format' => 'page/%#%/',  
			        'current' => $current_page,  
			        'total' => $total_pages,  
			        'prev_next' => false,  
			        'type' => 'list',  
			    ));  
			$html .= '</nav>'; 
		}

	    return $html; 
	}
}

if (!function_exists('rh_vendors_with_products')) {
function rh_vendors_with_products( $query ) {
	global $wpdb; 
    if ( isset( $query->query_vars['query_id'] ) && 'vendors_with_products' == $query->query_vars['query_id'] ) {  
        $query->query_from = $query->query_from . ' LEFT OUTER JOIN (
                SELECT post_author, COUNT(*) as post_count
                FROM '.$wpdb->prefix.'posts
                WHERE post_type = "product" AND (post_status = "publish" OR post_status = "private")
                GROUP BY post_author
            ) p ON ('.$wpdb->prefix.'users.ID = p.post_author)';
        $query->query_where = $query->query_where . ' AND post_count  > 0 ' ;  
    } 
}
}

//GMW SHORTCODE MAP
function rh_add_map_gmw($atts=array(), $content = null ) {
	extract( shortcode_atts( array(
			'user_id' => '', // one ID
		), $atts ) );	
	if ( function_exists('gmw_member_location_form')) {
		$user_id = (!empty($user_id)) ? $user_id : get_current_user_id();
		if (is_user_logged_in()){
			ob_start(); 
			gmw_member_location_form(array('member_id'=>$user_id, 'exclude_fields_groups'=>'coordinates,address','exclude_fields'=>'address,message'));
			$output = ob_get_contents();
			ob_end_clean();
			return $output; 
		}else{
			ob_start(); 
			_e('Please, login to set location', 'rehub-theme');
			$output = ob_get_contents();
			ob_end_clean();
			return $output;		
		}		
	}
}

function rh_compare_icon($atts, $content = null ) {
	if (rehub_option('compare_page') || rehub_option('compare_multicats_textarea')) {	
		$output = '<span class="re-compare-icon-toggle position-relative">';
			$output .= '<i class="rhicon rhi-shuffle"></i>';
			$totalcompared = re_compare_panel('count');
			if ($totalcompared == '') {$totalcompared = 0;}
			$output .= '<span class="re-compare-notice rehub-main-color-bg">'.$totalcompared.'</span>';		
		$output .= '</span>';
		return $output;
	}
}

//VC SHORTCODES
include ( get_template_directory() . '/shortcodes/module_shortcodes.php'); 

if( !function_exists('wpsm_get_bigoffer') ) {
function wpsm_get_bigoffer($atts){
	extract(shortcode_atts(array(
		'title' => NULL,
        'post_id' => NULL,
        'offset' => NULL,
        'limit' => NULL,
        'notitle' => ''
    ), $atts));

	if(!$post_id){
		global $post;
		$post_id = $post->ID;
	}

	if($post_id && is_numeric($post_id)){
		if(!defined('\ContentEgg\PLUGIN_PATH')){
			return 'Content Egg is not installed on your site';
		}
		$title = (!empty($title)) ? $title : get_the_title($post_id);
		ob_start();
		?>
        <div class="border-lightgrey clearbox flowhidden mb25 rh-shadow1 rh-tabletext-block whitebg width-100p">
            <?php if(!$notitle):?>
            	<div class="rh-tabletext-block-heading fontbold border-grey-bottom"><h4><a href="<?php echo get_the_permalink($post_id) ?>"><?php echo esc_attr($title); ?></a></h4> </div>
            <?php endif;?>		
	        <div class="rh-tabletext-block-wrapper padd20 pb0 flowhidden"> 
	            <div class="featured_compare_left wpsm-one-half">
	                <figure class="img-maxh-350 img-width-auto">                                                                    
	                    <a href="<?php echo get_the_permalink($post_id) ?>">
	                        <?php           
                    			$image_id = get_post_thumbnail_id($post_id);  
                    			$image_url = wp_get_attachment_image_src($image_id,'full');
                    			$image_url = $image_url[0]; 
                			?> 
	                        <?php WPSM_image_resizer::show_static_resized_image(array('lazy'=> true, 'src'=> $image_url, 'crop'=> false, 'height'=> 350, 'width'=> 350));?>
	                    </a>
	                </figure>                             
	            </div>
	            <div class="single_compare_right wpsm-one-half">	
	            	<?php $overall_review  = get_post_meta($post_id, 'rehub_review_overall_score', true);?>

                    <?php if($overall_review):?>
                    	<?php $overall_review_100 = $overall_review * 10;?>                  	
                    	<?php 
                    	if($overall_review<=2){
                    		$color = "#940000";
                    	}    
                    	elseif($overall_review<=4){
                    		$color = "#cc0000";
                    	}   
                    	elseif($overall_review<=6){
                    		$color = "#9c0";
                    	}  
                    	elseif ($overall_review <=8){
                    		$color = "#ffac00";
                    	}                    	                  	                  	                 	
                    	elseif ($overall_review <=10) {
                    		$color = "#ffac00";
                    	}
                    	?>                    	                   	
                        <div class="bigoffer-overall-score mb20 fontbold font120">
                        	<div class="text-overal-score mb10 flowhidden">
                            <span class="overall floatleft"><?php echo ''.$overall_review;?>/10 </span>
                            <span class="floatright font70 fontnormal text-read-review"><a href="<?php echo get_the_permalink($post_id) ?>"><?php esc_html_e('Read review', 'rehub-theme');?></a></span>
                            </div>
                            <?php 
                            	echo '<div class="wpsm-bar minibar wpsm-clearfix" data-percent="'. $overall_review_100 .'%">';
								echo'<div class="wpsm-bar-bar" style="background: '. $color .';"></div>';
								echo '</div>';
							?>
                        </div>                         
                    <?php endif;?>
                    <?php 
                    $attsce = array();
                    $attsce['template']= 'custom/all_merchant_widget';
                    $attsce['post_id'] = $post_id;
                    $attsce['offset'] = $offset;
                    $attsce['limit'] = $limit;
                    echo \ContentEgg\application\BlockShortcode::getInstance()->viewData($attsce);
                    ?>
	            </div> 
			</div>
		</div>

		<?php
		$output = ob_get_contents();
		ob_end_clean();
		return $output; 

	}

}
}


if( !function_exists('wpsm_get_add_deal_popup') ){
	function wpsm_get_add_deal_popup($atts, $content = NULL){
		extract(shortcode_atts(array(
	        'postid' => NULL,
	        'role' => 'contributor',
	        'membertype' => '',
			'currency' => '',
			'nothumb' => '',
			'rolename' => '',
			'label' => esc_html__('Add your deal', 'rehub-theme'),
			'editlabel' => esc_html__('Edit your deal', 'rehub-theme'),
	    ), $atts));

		if(!defined('\ContentEgg\PLUGIN_PATH')){
			return;
		}

		wp_enqueue_script( 'rh-ce-front', get_template_directory_uri() . '/js/cefrontsubmit.js', array( 'jquery', 'rehub' ), '1.1', true );

	   	global $post;
	   	$post_id = (NULL === $postid) ? $post->ID : $postid;	

		if($post_id && is_numeric($post_id)){
			ob_start();
			$rand = mt_rand();
			?>
			<?php if (is_user_logged_in()): 
				$current_user = wp_get_current_user();
			?>
				<?php 
				$cur_offers = get_post_meta( $post_id, '_cegg_data_Offer', true );
				$offer_key = 'OfferID_'. $current_user->ID;
				?>
				<?php if ( !empty($cur_offers[$offer_key]) ): ?>
					<?php $user_offer = $cur_offers[$offer_key]; ?>
					<a class="padforbuttonsmall fontnormal font95 lineheight15 text-center inlinestyle btn_offer_block csspopuptrigger rh-deal-compact-btn act-rehub-addoffer-popup act-rehub-login-popup" data-popup="addfrontdeal_<?php echo ''.$rand;?>"><?php echo esc_attr($editlabel) ?></a>
				<?php else: ?>
					<?php $user_offer = array(); ?>
					<a class="padforbuttonsmall fontnormal font95 lineheight15 text-center inlinestyle btn_offer_block csspopuptrigger rh-deal-compact-btn act-rehub-addoffer-popup act-rehub-login-popup" data-popup="addfrontdeal_<?php echo ''.$rand;?>"><?php echo esc_attr($label) ?></a>
				<?php endif; ?>
			
				<div class="csspopup" id="addfrontdeal_<?php echo ''.$rand;?>">
					<div class="csspopupinner addfrontdeal-popup">
						<span class="cpopupclose cursorpointer lightgreybg rh-close-btn rh-flex-center-align rh-flex-justify-center rh-shadow5 roundborder">×</span> 
						<?php 
							$show = false;
							if($membertype && $role){
								if(function_exists('wcfm_get_membership')){
									$checkrole = wcfm_get_membership();
									if($checkrole == $role){
										$show = true;
									}
								}
							}elseif($role){
								if(in_array( $role, (array) $current_user->roles )){
									$show = true;
								}
							}else{
								$show = true;
							}
						?>
						<?php if ( $show):?>
							<div class="rehub-offer-popup">
								<div class="re_title_inmodal"><?php if( empty( $user_offer ) ): esc_html_e('Add an Offer', 'rehub-theme'); else: esc_html_e('Edit the Offer', 'rehub-theme'); endif; ?></div>
								<form id="rehub_add_offer_form_modal" action="<?php echo home_url( '/' ); ?>" method="post">
									<?php do_action('wpsm_deal_popup_fields_before', $user_offer); ?>
									<div class="re-form-group mb20">
										<label for="ce_title"><?php esc_html_e('Name of product', 'rehub-theme') ?><span>*</span></label>
										<input class="re-form-input required" name="ce_title" id="ce_title" type="text" value="<?php echo isset( $user_offer['title'] ) ? $user_offer['title'] : ''; ?>" required />
									</div>
									<div class="re-form-group mb20">
										<label for="ce_orig_url"><?php esc_html_e('Offer url', 'rehub-theme') ?><span>*</span></label>
										<input class="re-form-input required" name="ce_orig_url" id="ce_orig_url" type="url" value="<?php echo isset( $user_offer['orig_url'] ) ? $user_offer['orig_url'] : ''; ?>" required />
									</div>
									<?php if(!$nothumb):?>
									<div class="re-form-group mb20">
										<label for="ce_img"><?php esc_html_e('Thumbnail url', 'rehub-theme') ?><span>*</span></label>
										<input class="re-form-input required" name="ce_img" id="ce_img" type="url" value="<?php echo isset( $user_offer['img'] ) ? $user_offer['img'] : ''; ?>" />
									</div>
									<?php endif;?>
									<div class="re-form-group mb20">
										<label for="ce_price"><?php esc_html_e('Offer sale price (example, 9999.99)', 'rehub-theme') ?><span>*</span></label>
										<input class="re-form-input required" name="ce_price" id="ce_price" type="number" step="0.01" value="<?php echo isset( $user_offer['price'] ) ? $user_offer['price'] : ''; ?>" />
									</div>
									<?php if(empty($currency)): ?>
									<div class="re-form-group mb20" style="width:15%">
									     <select class="form-control" name="ce_currency">
											<option value=""><?php esc_html_e('Currency...', 'rehub-theme') ?></option>
	                                        <?php foreach (\ContentEgg\application\helpers\CurrencyHelper::getCurrenciesList() as $ce_currency): ?>
												<?php $current_currency = isset( $user_offer['currencyCode'] ) ? $user_offer['currencyCode'] : ''; ?>
	                                            <option value="<?php echo esc_attr($ce_currency); ?>" <?php selected($current_currency, $ce_currency); ?>><?php echo esc_html($ce_currency); ?></option>
	                                        <?php endforeach; ?>
	                                    </select>        
									</div>
									<?php else: ?>
										<input type="hidden" name="ce_currency" value="<?php echo esc_attr($currency); ?>" />
									<?php endif; ?>
									<div class="re-form-group mb20">
										<label for="ce_description"><?php esc_html_e('Short description', 'rehub-theme') ?><span></span></label>
										<input class="re-form-input" name="ce_description" id="ce_description" type="text" value="<?php echo isset( $user_offer['description'] ) ? $user_offer['description'] : ''; ?>" />
									</div>
									<?php do_action('wpsm_deal_popup_fields_after', $user_offer); ?>
									<div class="re-form-group mb20">
										<input type="hidden" name="action" value="rehub_ce_user_offer" />
										<input type="hidden" name="from_user" value="<?php echo (int)$current_user->ID; ?>" />
										<input type="hidden" name="post_id" value="<?php echo (int)$post_id; ?>" />
										<?php wp_nonce_field( 'rehub_ce_user_offer', 'offer_nonce' ); ?>
										<button class="wpsm-button rehub_main_btn" type="submit" name="send"><?php esc_html_e('Send', 'rehub-theme'); ?></button>
									</div>
								</form>
								<div class="rehub-errors"></div>
							</div>
							<?php if( empty( $user_offer ) ): ?>		
								<div class="rehub-offer-popup-ok font110 rhhidden"><div class="re_title_inmodal"><?php esc_html_e('Send Offer', 'rehub-theme'); ?></div><?php printf( esc_html__('Thank you, %s! Your offer has been sent', 'rehub-theme'), $current_user->display_name ); ?></div>
							<?php else: ?>
								<div class="rehub-offer-popup-ok font110 rhhidden"><div class="re_title_inmodal"><?php esc_html_e('Updated Offer', 'rehub-theme'); ?></div><?php printf( esc_html__('Thank you, %s! Your offer has been updated', 'rehub-theme'), $current_user->display_name ); ?></div>						
							<?php endif; ?>
						<?php else:?>
							<?php $content = do_shortcode($content);?>
							<?php if($content):?>
								<?php echo ''.$content;?>
							<?php else:?>
								<?php if(!$rolename) $rolename = $role;?>
								<?php echo sprintf( esc_html__( 'Only users with role %s%s%s are allowed to post deals', 'rehub-theme' ), '<span class="greencolor">', esc_attr($rolename), '</span>');?>
							<?php endif;?>
						<?php endif;?>
					</div>				
				</div>
			<?php endif;?>
			
			<?php
			$output = ob_get_contents();
			ob_end_clean();
			return $output; 
		}
	}
}


if( !function_exists('rh_get_post_thumbnails') ) {
function rh_get_post_thumbnails($atts, $content = NULL){
	extract(shortcode_atts(array(
        'postid' => NULL,
        'video' => '',
        'height' => '100',
        'columns' => 5,
        'class' => '',
        'justify' => '',
        'disableimages'=> '',
        'galleryids' => ''
    ), $atts));	
	global $post;
   	$post_id = (NULL === $postid) ? $post->ID : $postid;
   	if($galleryids){
   		$post_image_gallery = $galleryids;
   	}else{
    	$post_image_gallery = get_post_meta( $post_id, 'rh_post_image_gallery', true );   		
   	}
   	if($post->post_type == 'product'){
   		$post_image_videos = get_post_meta( $post_id, 'rh_product_video', true );
   	}else{
   		$post_image_videos = get_post_meta( $post_id, 'rh_post_image_videos', true );
   	}
    
    $countimages = '';
    $columnclass = ($columns==5) ? ' five-thumbnails' : '';
    $justifyclass = ($justify) ? 'modulo-lightbox justified-gallery rh-tilled-gallery ' : 'modulo-lightbox rh-flex-eq-height compare-full-thumbnails mt15 ';
	ob_start();
	?>    
    <?php if(!empty($post_image_gallery) || (!empty($post_image_videos) && $video == 1) ) :?>
    	<?php $random_key = rand(0, 50);?>
        <?php $post_image_gallery = explode(',', $post_image_gallery);?>
        <?php if($post_image_videos):?>
        	<?php $post_image_videos = array_map('trim', explode(PHP_EOL, $post_image_videos));?>
        <?php endif;?> 
        <div class="<?php echo ''.$justifyclass.$class.$columnclass;?> mb20" data-galleryid="rhgal_<?php echo ''.$random_key;?>">
            <?php foreach($post_image_gallery as $key=>$image_gallery):?>
            	<?php if($image_gallery && $disableimages !=1):?>
	                <a href="<?php echo wp_get_attachment_url($image_gallery);?>" target="_blank" class="mb10" data-thumb="<?php echo wp_get_attachment_url($image_gallery);?>" data-rel="rehub_postthumb_gallery_<?php echo ''.$random_key;?>" data-title="<?php echo esc_attr(get_post_field( 'post_excerpt', $image_gallery));?>">
	                    <?php WPSM_image_resizer::show_static_resized_image(array('lazy'=>true, 'src'=> wp_get_attachment_url($image_gallery), 'crop'=> false, 'height'=> $height, 'title' => esc_attr(get_post_meta( $image_gallery, '_wp_attachment_image_alt', true))));?>                                                     
	                    </a> 
                <?php endif;?>                              
            <?php endforeach;?>  
            <?php if($video == 1 && !empty($post_image_videos)):?> 
	            <?php foreach($post_image_videos as $key=>$video):?>
	            	<?php $video = trim($video);?>
	                <a href="<?php echo esc_url($video);?>" data-rel="rehub_postthumb_gallery_<?php echo ''.$random_key;?>" target="_blank" class="mb10 rh_videothumb_link" data-poster="<?php echo parse_video_url(esc_url($video), 'maxthumb'); ?>" data-thumb="<?php echo parse_video_url(esc_url($video), 'hqthumb'); ?>">
						<img src="<?php echo parse_video_url(esc_url($video), 'hqthumb'); ?>" height="<?php echo ''.$height;?>" alt="image" />
	                </a>                               
	            <?php endforeach;?> 
            <?php endif;?>                       
        </div>
        <?php  wp_enqueue_script('modulobox'); wp_enqueue_style('modulobox');?>
        <?php if($justify):?>
        	<?php wp_enqueue_script('justifygallery');wp_enqueue_style('justify');?>        	
        <?php endif;?>
        
    <?php endif;?>   
	<?php
	$output = ob_get_contents();
	ob_end_clean();
	return $output; 
}
}

if( !function_exists('rh_get_post_videos') ) {
function rh_get_post_videos($atts, $content = NULL){
	extract(shortcode_atts(array(
        'postid' => NULL,
        'class' => '',
        'height' => '',
    ), $atts));	
	global $post;
   	$post_id = (NULL === $postid) ? $post->ID : $postid;

    $post_image_videos = get_post_meta( $post_id, 'rh_post_image_videos', true );
	ob_start();
	?>    
    <?php if(!empty($post_image_videos) ) :?>
    	<?php $random_key = rand(0, 50);?>
		<?php $post_image_videos = array_map('trim', explode(PHP_EOL, $post_image_videos));?> 
        <div class="<?php echo esc_attr($class);?> modulo-lightbox rh_post_videos mt15 mb20" data-galleryid="rhvid_<?php echo ''.$random_key;?>">   
            <?php foreach($post_image_videos as $key=>$video):?>
                <a href="<?php echo esc_url($video);?>" data-rel="rehub_postvid_gallery_<?php echo ''.$random_key;?>" target="_blank" class="mb10 inlinestyle rh_videothumb_link" data-poster="<?php echo parse_video_url(esc_url($video), 'maxthumb'); ?>" data-thumb="<?php echo parse_video_url(esc_url($video), 'hqthumb'); ?>"> 
					<img data-src="<?php echo parse_video_url(esc_url($video), 'maxthumb'); ?>" src="<?php echo get_template_directory_uri() . '/images/default/noimage_450_350.png';?>" alt="image" class="lazyload" />
                </a>                               
            <?php endforeach;?>                       
        </div>
        <?php  wp_enqueue_script('modulobox'); wp_enqueue_style('modulobox');?>
    <?php endif;?>   
	<?php
	$output = ob_get_contents();
	ob_end_clean();
	return $output; 
}
}

if( !function_exists('rh_get_profile_data') ) {
function rh_get_profile_data($atts, $content = NULL){
	extract(shortcode_atts(array(
        'name' => '',
        'type' => 'text',
        'userid' =>'',
        'usermeta' => '',
        'pointmeta' => ''
    ), $atts));	


	if($userid == 'author'){
		global $post;
		$userid=$post->post_author; 
	}
	elseif($userid == 'current'){
		$userid = get_current_user_id();
	}
	elseif($userid == 'bpuser' && function_exists('bp_displayed_user_id')){
		$userid = bp_displayed_user_id();
	}	
	if(!$userid) return;    
    if($usermeta){
    	return esc_html(get_user_meta($userid, $usermeta, true));
    }
    if($pointmeta){
		if(function_exists('mycred_render_shortcode_my_balance')){
			$custompoint = ($pointmeta == 'default') ? '' : $pointmeta; 
			$mycredpoint = mycred_render_shortcode_my_balance(array('type'=>$custompoint, 'user_id'=>$userid, 'wrapper'=>'', 'balance_el' => '', 'title'=> '') );
			return $mycredpoint;
		}
    }    
	if(!$name || !bp_is_active( 'xprofile' )) return;
	if(bp_get_profile_field_data('field='.$name.'&user_id='.$userid.'')){
		$data = bp_get_profile_field_data('field='.$name.'&user_id='.$userid.'');
		if($type == 'text'){
			$data = esc_html($data);
		}
		elseif ($type=='link'){
			$data = esc_url($data);
		}
		elseif ($type=='raw'){
			$data = rehub_kses($data);
		}		
		return $data;
	}

}
}


if( !function_exists('rh_is_bpmember_type') ) {
function rh_is_bpmember_type($atts, $content = NULL){
	extract(shortcode_atts(array(
        'type' => '',
        'bp_user' => '',
    ), $atts));	

	if(!$type || !function_exists('bp_get_member_type')) return;
	if($bp_user){
		$userid = bp_displayed_user_id();
		if(!$userid) return;
	}
	else{
		$userid = get_current_user_id();
		if(!$userid) return;
	}
	$usertype = bp_get_member_type($userid);
	if(($usertype == $type) && !is_null( $content )){		
		$content = do_shortcode($content);
		$content = preg_replace( '%<p>&nbsp;\s*</p>%', '', $content ); 
		$content = preg_replace('/^(?:<br\s*\/?>\s*)+/', '', $content);
		return $content;
	}
	else{
		return false;
	}
}
}

if( !function_exists('rh_bpmember_type') ) {
function rh_bpmember_type($atts, $content = NULL){
	extract(shortcode_atts(array(
        'bp_user' => '',
    ), $atts));	

	if(!function_exists('bp_get_member_type')) return;
	if($bp_user){
		$userid = bp_displayed_user_id();
		if(!$userid) return;
	}
	else{
		$userid = get_current_user_id();
		if(!$userid) return;
	}
	$usertype = bp_get_member_type($userid);
	$membertype_object = bp_get_member_type_object($usertype);
	$membertype_label = (!empty($membertype_object) && is_object($membertype_object)) ? $membertype_object->labels['singular_name'] : '';	
	return $membertype_label;

}
}

if( !function_exists('rh_is_bpmember_role') ) {
function rh_is_bpmember_role($atts, $content = NULL){
	extract(shortcode_atts(array(
        'role' => '',
        'bp_user' => '',        
    ), $atts));	
	if(!$role) return;
	if($bp_user){
		if(!function_exists('bp_displayed_user_id')) return;
		$userid = bp_displayed_user_id();
		if(!$userid) return;
	}
	else{
		$userid = get_current_user_id();
		if(!$userid) return;
	}    
	$user = get_userdata($userid);

	if (!empty($user)){
		$rolesarray = array_map( 'trim', explode( ",", $role));
		foreach ($rolesarray as $rolecheck) {
			if ( in_array( $rolecheck, (array) $user->roles )) {
				$content = do_shortcode($content);
				$content = preg_replace( '%<p>&nbsp;\s*</p>%', '', $content ); 
				$content = preg_replace('/^(?:<br\s*\/?>\s*)+/', '', $content);
				return $content;
			}
		}		
	}
	else{
		return false;
	}
}
}


//////////////////////////////////////////////////////////////////
// RH WCFM ROLE
//////////////////////////////////////////////////////////////////
if( !function_exists('rh_is_wcfm_role') ) {
function rh_is_wcfm_role($atts, $content = NULL){
	extract(shortcode_atts(array(
        'role' => '',       
    ), $atts));	
	if(!$role || !function_exists('wcfm_get_membership')) return false;
	$checkrole = wcfm_get_membership();
	if($checkrole == $role){
		$content = do_shortcode($content);
		$content = preg_replace( '%<p>&nbsp;\s*</p>%', '', $content ); 
		$content = preg_replace('/^(?:<br\s*\/?>\s*)+/', '', $content);
		return $content;		
	}else{
		return false;
	}
}
}

//////////////////////////////////////////////////////////////////
// RH WCFM ROLE
//////////////////////////////////////////////////////////////////
if( !function_exists('rh_is_wcfm_role') ) {
function rh_is_not_wcfm_role($atts, $content = NULL){
	extract(shortcode_atts(array(
        'role' => '',       
    ), $atts));	
	if(!$role || !function_exists('wcfm_get_membership')) return false;
	$checkrole = wcfm_get_membership();
	if($checkrole != $role){
		$content = do_shortcode($content);
		$content = preg_replace( '%<p>&nbsp;\s*</p>%', '', $content ); 
		$content = preg_replace('/^(?:<br\s*\/?>\s*)+/', '', $content);
		return $content;		
	}else{
		return false;
	}
}
}

if( !function_exists('rh_is_bpmember_profile') ) {
function rh_is_bpmember_profile($atts, $content = NULL){	
	if(!function_exists('bp_is_my_profile')) return;
	if (bp_is_my_profile()){		
		$content = do_shortcode($content);
		return $content;	
	}
	else{
		return false;
	}
}
}


if( !function_exists('rh_get_group_admins') ) {
function rh_get_group_admins($atts, $content = NULL){
	extract(shortcode_atts(array(
        'text' => '',
    ), $atts));	
    if(!bp_is_group_single()) return;
	global $groups_template;
	$output = '';
	if ( empty( $group ) ) {
		$group =& $groups_template->group;
	}
	$txt = ($text) ? $text : esc_html__('Write message', 'rehub-theme') ;
	if ( ! empty( $group->admins ) ) { 
		$output .= '<ul class="buddypress widget">';
			foreach( (array) $group->admins as $admin ) {
				$output .= '<li class="vcard mb15">';
					$output .= '<div class="item-avatar"><a href="'.bp_core_get_user_domain( $admin->user_id, $admin->user_nicename, $admin->user_login ).'">'.bp_core_fetch_avatar( array( 'item_id' => $admin->user_id, 'email' => $admin->user_email, 'alt' => sprintf( esc_html__( 'Profile picture of %s', 'rehub-theme' ), bp_core_get_user_displayname( $admin->user_id ) ) ) ).'</a></div>';
					$link = (is_user_logged_in()) ? wp_nonce_url( bp_loggedin_user_domain() . bp_get_messages_slug() . '/compose/?r=' . bp_core_get_username( $admin->user_id) .'&ref='. urlencode(get_permalink())) : '#';
					$class = (!is_user_logged_in() && rehub_option('userlogin_enable') == '1') ? ' act-rehub-login-popup' : '';
					$output .='<div class="item"><div class="item-title-bpadmin"><a href="'.bp_core_get_user_domain( $admin->user_id, $admin->user_nicename, $admin->user_login ).'">'.$admin->user_nicename.'</a></div><a href="'.$link.'" class="vendor_store_owner_contactlink'.$class.'"><i class="rhicon rhi-envelope" aria-hidden="true"></i> <span>'. $txt .'</span></a></div>';					
				$output .= '</li>';
			} 
		$output .= '</ul>';
	}
	return $output; 
}
}


//////////////////////////////////////////////////////////////////
// AMP Button to mobile version
//////////////////////////////////////////////////////////////////
if( !function_exists('rh_get_permalink') ) {
function rh_get_permalink( $atts, $content = null ) {
    return get_the_permalink();
}
}

//////////////////////////////////////////////////////////////////
// SEARCH CE BIG
//////////////////////////////////////////////////////////////////
if( !function_exists('rh_ce_search_form') ) {
function rh_ce_search_form( $atts=array(), $content = null ) {
	$build_args =shortcode_atts(array(
		'placeholder' => esc_html__('Search Products...', 'rehub-theme'),
		'label' => esc_html__('Search', 'rehub-theme'),		
	), $atts, 'rh_ce_search_form'); 
	extract( $build_args ); 
	ob_start(); 
	?>
	<style scope> .custom_search_box{padding: 20px 0; }.custom_search_box form{ position: relative; display: block; width: 100%;}.custom_search_box input[type="text"] {transition: all 0.5s ease-out; background: #f6f6f6;border: 3px solid #ececec;height: 50px;width: 100%;padding:0 55px 0 40px;outline: none;  }@media(min-width: 1224px){.custom_search_box input[type="text"]{font-size: 115%}.custom_search_box.flat_style_form input[type="text"]{font-size: 105%}}.custom_search_box i.inside-search{ position: absolute; top: 50%; left: 16px; margin-top: -8px}.custom_search_box.flat_style_form i{display: none;}.custom_search_box button[type="submit"] { padding: 0 13px; position: absolute; height: calc(100% - 6px); right: 3px; top:3px;  color: #fff !important; font-size: 130% !important; margin: 0; border-radius: 0; box-shadow: none !important;}.custom_search_box input[type="text"]:focus, .custom_search_box input[type="text"]:hover{border-color: #666; background-color: #fff}.custom_search_box.flat_style_form input[type="text"] {border-width: 1px;height: 52px;padding:0 130px 0 20px; }.custom_search_box.flat_style_form button[type="submit"] { padding: 0 35px; height: 100%; right: 0; top:0; font-size: 100% !important;}.cssProgress{opacity: 0; visibility: hidden; transform: translate3d(0, 25px, 0);transition: all .4s ease-out;}.cssProgress.active{opacity: 1; visibility:visible ;-webkit-transform: translate3d(0, 0, 0);transform: translate3d(0, 0, 0);}.progress2{position: relative;overflow: hidden;width: 100%;    background-color: #EEE;box-shadow: inset 0px 1px 3px rgba(0, 0, 0, 0.2);}.progress2 .cssProgress-bar {height: 14px;}.cssProgress .cssProgress-active {-webkit-animation: cssProgressActive 2s linear infinite;-ms-animation: cssProgressActive 2s linear infinite;animation: cssProgressActive 2s linear infinite;}.cssProgress .cssProgress-stripes, .cssProgress .cssProgress-active, .cssProgress .cssProgress-active-right {background-image: -webkit-linear-gradient(135deg, rgba(255, 255, 255, 0.125) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.125) 50%, rgba(255, 255, 255, 0.125) 75%, transparent 75%, transparent);background-image: linear-gradient(-45deg, rgba(255, 255, 255, 0.125) 25%, transparent 25%, transparent 50%, rgba(255, 255, 255, 0.125) 50%, rgba(255, 255, 255, 0.125) 75%, transparent 75%, transparent);background-size: 35px 35px;}.cssProgress .cssProgress-success {background-color: #41bc03 !important;}.cssProgress .cssProgress-bar {display: block;float: left;width: 0%;box-shadow: inset 0px -1px 2px rgba(0, 0, 0, 0.1);}@-webkit-keyframes cssProgressActive {0% {background-position: 0 0;}100% {background-position: 35px 35px;}}@-ms-keyframes cssProgressActive {0% {background-position: 0 0;}100% {background-position: 35px 35px;}}@keyframes cssProgressActive {0% {background-position: 0 0;}100% {background-position: 35px 35px;}}@-webkit-keyframes cssProgressActiveRight {0% {background-position: 0 0;}100% {background-position: -35px -35px;}}@-ms-keyframes cssProgressActiveRight {0% {background-position: 0 0;}100% {background-position: -35px -35px;}}@keyframes cssProgressActiveRight {0% {background-position: 0 0;}100% {background-position: -35px -35px;}}</style>
	<?php wp_enqueue_script( 'rh-ce-search-form', get_template_directory_uri() . '/js/cefrontsearch.js', array( 'jquery' ), 1.0, true );?>	
	<div class="progress-animate-onclick width-100p position-relative custom_search_box flat_style_form">
		<div class="cssProgress mb10">
          <div class="progress2">
            <div class="cssProgress-bar cssProgress-success cssProgress-active" style="width: 20%; transition: none;">
            </div>
          </div>
	    </div>
		<form  role="search" method="get" id="searchform" action="<?php echo \ContentEgg\application\ProductSearchWidget::getSearchFormUri(); ?>">
			<?php if (!get_option('permalink_structure')): ?> 
			 	<input name="pagename" type="hidden" value="product-search" />
			<?php endif; ?>			
		  	<input type="text" name="s" placeholder="<?php echo esc_attr($placeholder)?>">
		  	<button type="submit" class="wpsm-button rehub_main_btn trigger-progress-bar"><?php echo esc_attr($label)?></button>
		</form>
	</div>

	<?php
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
}
}

//////////////////////////////////////////////////////////////////
// Is Post type shortcode
//////////////////////////////////////////////////////////////////
if( !function_exists('rh_is_singular') ) {
function rh_is_singular( $atts, $content = null ) {
	extract(shortcode_atts(array(
        'type' => '',
        'id' => '',
    ), $atts, 'rh_is_singular'));	
	// Remove all instances of "<p>&nbsp;</p><br>" to avoid extra lines.
	$content = do_shortcode($content);
	$content = preg_replace( '%<p>&nbsp;\s*</p>%', '', $content ); 
	$content = preg_replace('/^(?:<br\s*\/?>\s*)+/', '', $content);
	if($id){
		if(is_single($id) || is_page($id) ){
			return $content;
		}
		else { 
			return;	
		}
	}elseif($type){
		if ( is_singular($type)) {
			return $content;
		}
		else { 
			return;	
		}
	}
	else { 
		return;	
	}
}	
}

//////////////////////////////////////////////////////////////////
// Is Woo category
//////////////////////////////////////////////////////////////////


if( !function_exists('rh_is_category') ) {
function rh_is_category( $atts, $content = null ) {
	extract(shortcode_atts(array(
        'ids' => '',
        'tax' => 'product_cat',
    ), $atts, 'rh_is_category'));	
    $postid = get_the_ID();
    $post_terms = wp_get_post_terms($postid, $tax, array("fields" => "ids"));
	$ids = array_map( 'trim', explode( ",", $ids ) );
	$post_in_cat = array_intersect($post_terms, $ids);
	if(array_filter($post_in_cat)) {
		// Remove all instances of "<p>&nbsp;</p><br>" to avoid extra lines.
		$content = do_shortcode($content);
		$content = preg_replace( '%<p>&nbsp;\s*</p>%', '', $content ); 
		$content = preg_replace('/^(?:<br\s*\/?>\s*)+/', '', $content);	
		return $content;
	}		
	else { 
		return;	
	}
}	
}


if( !function_exists( 'rh_mailchimp_shortcode' ) ) {
	function rh_mailchimp_shortcode( $atts ) {
		$atts = shortcode_atts(
			array(
				'action' => '',
				'title' => '',
				'placeholder' => 'email address',
				'inputname' => '',
				'button' => 'Subscribe',
				'subtitle' => '',
				'class' => '',
				'flat' => '',
				'provider'=> 'mailchimp'
			),
			$atts,
			'rh_mailchimp'
		);
		wp_enqueue_style('rhmailchimp');
		if($atts['provider'] == 'mailchimp'){
			if ( $atts['action'] == '' OR $atts['inputname'] == ''  ) {
				$output = '';
			} else {
				$flat = ($atts['flat'] == 1) ? ' rehub_chimp_flat' : ' rehub_chimp rehub-sec-smooth';
				$title = ($atts['title'] != '') ? '<h3 class="chimp_title">'.$atts['title'].'</h3>' : '';
				$subtitle = ($atts['subtitle'] != '') ? '<p class="chimp_subtitle">'.$atts['subtitle'].'</p>' : '';
				$output = '
				<div class="centered_form '.$atts['class'].$flat.'">
				'.$title.'
				<!-- Begin MailChimp Signup Form -->
				<div id="mc_embed_signup">
				<form action="'. esc_url($atts['action']) .'" method="post" id="mc-embedded-subscribe-form" name="mc-embedded-subscribe-form" class="validate" target="_blank" novalidate>
					<div id="mc_embed_signup_scroll">
					<input type="email" value="" name="EMAIL" class="email" id="mce-EMAIL" placeholder="'. $atts['placeholder'] .'" required>
					<div style="position: absolute; left: -5000px;" aria-hidden="true"><input type="text" name="'. esc_html($atts['inputname']) .'" tabindex="-1" value=""></div>
					<div class="clear"><input type="submit" value="'. $atts['button'] .'" name="subscribe" id="mc-embedded-subscribe" class="button"></div>
					</div>
				</form>
				</div>
				<!--End mc_embed_signup-->
				'.$subtitle.'
				</div>';		
			}
		}elseif ($atts['provider'] == 'followit'){
			$flat = ($atts['flat'] == 1) ? ' rehub_chimp_flat' : ' rehub_chimp rehub-sec-smooth';
			$title = ($atts['title'] != '') ? '<h3 class="chimp_title">'.$atts['title'].'</h3>' : '';
			$subtitle = ($atts['subtitle'] != '') ? '<p class="chimp_subtitle">'.$atts['subtitle'].'</p>' : '';
			$output = '
			<div class="centered_form '.$atts['class'].$flat.'">
			'.$title.'
			<!-- Begin Followit Signup Form -->
			<div id="mc_embed_signup">
			<form action="https://api.follow.it/subscribe?pub=1qapKlLKD5cJnUqWJiIBqgp41t9tiERU" method="post" target="_blank">
				<input type="email" value="" name="email" class="email" id="mce-EMAIL" placeholder="'. $atts['placeholder'] .'" required>
				<div class="clear"><input type="submit" value="'. $atts['button'] .'" name="subscribe" id="mc-embedded-subscribe" class="button"></div>
			</form>
			</div>
			<!--End signup-->
			'.$subtitle.'
			</div>';			
		}

		return $output;
	}
}

//////////////////////////////////////////////////////////////////
// Review box
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_reviewbox') ) {
function wpsm_reviewbox( $atts, $content = null ) {
        $atts = shortcode_atts(
			array(
				'title' => '',
				'description' => '',
				'criterias' => '',
				'score' => '',
				'pros' => '',
				'prostitle' => esc_html__('PROS', 'rehub-theme'),				
				'cons' => '',
				'constitle' => esc_html__('CONS', 'rehub-theme'),
				'id' => '',
				'compact' =>'',
				'scrollid' => '',
				'woobox' => '',
				'regular' => '',
				'additional_class' => '',															
			), $atts, 'wpsm_reviewbox');
        extract($atts);

    $out = '';

    if(empty($id)){
    	global $post;
    	$id = $post->ID;
    }

    if($compact){
    	$score = get_post_meta((int)$id, 'rehub_review_overall_score', true);
    	if($score){
    		if($compact == 'circle'){
			    $out = '<div class="review_visible_circle review_big_circle"><div class="top-rating-item-circle-view">
			    <div class="radial-progress" data-rating="'.$score.'">
			        <div class="circle">
			            <div class="mask full">
			                <div class="fill"></div>
			            </div>
			            <div class="mask half">
			                <div class="fill"></div>
			                <div class="fill fix"></div>
			            </div>
			            
			        </div>
			        <div class="inset">
			            <div class="percentage">'.round($score, 1).'</div>
			        </div>
			    </div>
			    </div></div>';
    		}
    		elseif($compact == 'circleaverage'){
			    $out = '<div class="review_visible_circle"><div class="top-rating-item-circle-view">
			    <div class="radial-progress" data-rating="'.$score.'">
			        <div class="circle">
			            <div class="mask full">
			                <div class="fill"></div>
			            </div>
			            <div class="mask half">
			                <div class="fill"></div>
			                <div class="fill fix"></div>
			            </div>
			            
			        </div>
			        <div class="inset">
			            <div class="percentage">'.$score.'</div>
			        </div>
			    </div>
			    </div></div>';
    		}
    		elseif($compact == 'text'){
			    $out = '<span class="lineheight20 rh-flex-center-align rehub-main-font"><span class="score_text_r font110 mr5 rtlml5"><span class="fontbold">'.$score.'</span>/10</span>
			    		<span class="greycolor font70">('.__("Expert Score", "rehub-theme").')</span>
			    		</span>';
    		} 
     		elseif($compact == 'textbigcenter'){
			    $out = '<div class="rehub-main-font"><div class="score_text_r font200 mr5 rtlml5"><span class="fontbold">'.$score.'</span>/10</div>
			    		<div class="greycolor font70">('.__("Expert Score", "rehub-theme").')</div>
			    		</div>';
    		} 
    		elseif($compact == 'square'){
			    $out = '<div class="review-top">
                    <div class="overall-score">
                        <span class="overall">'.$score.'</span>
                        <span class="overall-text">'.__("Expert Score", "rehub-theme").'</span>
                    </div> 
                </div> ';
    		} 
    		elseif($compact == 'smallsquare'){
			    $out = '<div class="review-small-square mb10 fontbold text-center whitecolor mr10 floatleft rtlml10 r_score_'. round($score).'">
                    <div class="overall-score">
                        <span class="overall">'.round($score, 1).'</span><span class="font70 fontnormal">/10</span>
                    </div> 
                </div> ';
    		} 
    		elseif($compact == 'smallcircle'){
			    $out = '<div class="fontbold font90 text-center width-22 height-22 roundborder50p whitecolor r_score_'. round($score).'">
                    <div class="overall-score">
                        <span class="overall">'.round($score).'</span></span>
                    </div> 
                </div> ';
    		} 
    		elseif($compact == 'mediumcircle'){
			    $out = '<div class="review-small-circle fontbold font90 text-center whitecolor r_score_'. round($score).'">
                    <div class="overall-score">
                        <span class="overall">'.round($score, 1).'</span></span>
                    </div> 
                </div> ';
    		}    		   		   		
    		elseif($compact == 'squarecolor'){
			    $out = '<div class="review-top">
                    <div class="overall-score colored_rate_bar">
                        <span class="overall r_score_'. round($score).'">'.$score.'</span>
                        <span class="overall-text">'.__("Expert Score", "rehub-theme").'</span>
                    </div> 
                </div> ';
    		}     		  		
    		else{
		    	$title = (!empty($title)) ? $title : esc_html__('Expert Score', 'rehub-theme');
		    	$description = (!empty($description)) ? $description : esc_html__('Read review', 'rehub-theme');

		    	$link = get_the_permalink((int)$id);
		    	$scrollid = (!empty($scrollid)) ? ' class="rehub_scroll" data-scrollto="#'.$scrollid.'"' : ' target="_blank"';
		    	$out = '<div class="mb15 compact-reviewbox whitebg colored_rate_bar rh-flex-center-align border-lightgrey">';
		    		$out .= '<div class="score-compact r_score_'. round($score).'">'.$score.'</div>';
		    		$out .= '<div class="rev-comp-text lineheight20 ml15 mr15"><span class="rev-comp-title fontbold font115 blockstyle upper-text-trans">'.$title.'</span>';
		    		$out .= '<span class="rev-comp-link font90"><a href="'.$link.'"'.$scrollid.'>'.$description.'</a></span>';    		
		    		$out .= '</div>';
		    	$out .= '</div>';     			
    		}   		
    	}
    }
    elseif($woobox){
    	$out = '';
		$total_score = get_post_meta((int)$id, 'rehub_review_overall_score', true);
		if(!$total_score) return;
		$thecriteria = get_post_meta((int)$id, '_review_post_criteria', true);
		$pros = get_post_meta((int)$id, '_review_post_pros_text', true);
		$cons = get_post_meta((int)$id, '_review_post_cons_text', true);
		if(empty($thecriteria[0]['review_post_name']) && empty($pros)) return;

		$image_url = get_post_meta((int)$id, '_woo_review_image_bg', true);
		if(!$image_url){
	        $image_id = get_post_thumbnail_id($id);  
	        $image_url = wp_get_attachment_image_src($image_id,'full');
	        $image_url = $image_url[0];
		} 
        $rand_id = mt_rand();

        $cols = (empty($pros) && empty($cons)) ? 'rate_col_2' : 'rate_col_3';	

		$out = '<div class="rate_wide_block mobileblockdisplay rh-flex-center-align '.$cols.'" id="rate_wide_block_'.$rand_id.'">';
			$out .='<style scoped>#rate_wide_block_'.$rand_id.'{background-image: url('.esc_url($image_url).');     background-size: cover;}</style>';
			$out .= '<style scoped>
				.rate_wide_block{color: #fff; font-size: 14px; position: relative;}
				.rate_wide_block .rh-post-layout-image-mask{background: rgba(0,0,0,0.7);}
				.rate_wide_block .rh_col{z-index: 2; position: relative;}
				.rate_col_2 .rh_col{width: calc(100% - 160px); padding: 20px}
				.rate_col_3 .rh_col{width: calc((100% - 160px)/2); padding: 20px}
				.rate_wide_block .rh_col.rhscorewrap{width: 160px; margin: 0 auto; padding: 20px 10px 20px 40px}
				.rate_wide_block .wpsm-bar, .rate_wide_block .wpsm-bar-title span, .rate_wide_block .wpsm-bar-bar{height: 23px; line-height: 23px; }
				.rate_wide_block .wpsm-bar{background: rgba(221, 221, 221, 0.16);}
				@media (max-width: 767px) {
				.rate_wide_block .rh_col{width: 100% !important; margin: 0 0 15px 0 !important; padding: 20px !important}
				}
			</style>
			';
			$out .= '<div class="rh_col rhscorewrap">';
			    $out .= '<div class="review_visible_circle review_big_circle"><div class="top-rating-item-circle-view">
			    <div class="radial-progress" data-rating="'.$total_score.'">
			        <div class="circle">
			            <div class="mask full">
			                <div class="fill"></div>
			            </div>
			            <div class="mask half">
			                <div class="fill"></div>
			                <div class="fill fix"></div>
			            </div>
			            
			        </div>
			        <div class="inset">
			            <div class="percentage">'.$total_score.'</div>
			        </div>
			    </div>
			    </div></div>';				
			$out .='</div>';

			if (!empty($thecriteria[0]['review_post_name']))  {
				$out .= '<div class="rh_col wooratebarwrap rate-line mt10 position-relative">';
				    foreach ($thecriteria as $criteria) {
				    	if(!empty($criteria)){
					    	$criteriascore = $criteria['review_post_score'];
					    	$criterianame = $criteria['review_post_name'];
					    	$perc_criteria = $criteriascore*10;


							$color = '#e43917';
							$out .= '<div class="mb5">'. $criterianame .'</div>';
							$out .= '<div class="wpsm-bar wpsm-clearfix" data-percent="'. $perc_criteria .'%">';
								$out .= '<div class="wpsm-bar-title" style="background: '. $color .';"><span>'.$criteriascore.'</span></div>';
								$out .= '<div class="wpsm-bar-bar" style="background: '. $color .';"></div>';
							$out .= '</div>';							
						}
				    }	
				$out .='</div>';	
			}			

			if(!empty($pros) || !empty($cons) ) {
				$out .= '<div class="rh_col prosconswrap">';
					if(!empty($pros)):
						$out .='<div class="prosblock lineheight15"><div class="title_pros mb15 font110 fontbold upper-text-trans">'.__('+ Positives:', 'rehub-theme').'</div><ul>';		
						$prosvalues = explode(PHP_EOL, $pros);
						foreach ($prosvalues as $prosvalue) {
							if(!empty($prosvalue)){
								$out .='<li>'.esc_html($prosvalue).'</li>';						
							}
						}
						$out .='</ul></div>';
					endif;
					if(!empty($cons) && !empty($thecriteria[0]['review_post_name'])):
						$out .='<div class="consblock lineheight15"><div class="title_cons mb15 font110 fontbold upper-text-trans">'.__('- Negatives:', 'rehub-theme').'</div><ul class="mb0">';		
						$consvalues = explode(PHP_EOL, $cons);
						foreach ($consvalues as $consvalue) {
							if(!empty($consvalue)){
								$out .='<li>'.esc_html($consvalue).'</li>';						
							}
						}
						$out .='</ul></div>';
					endif;					
				$out .='</div>';				
			}

			if(empty($thecriteria[0]['review_post_name']) && !empty($cons) ) {
				$out .= '<div class="rh_col conswrap">';
					$out .='<div class="consblock lineheight15"><div class="title_cons mb15 upper-text-trans">'.__('- Negatives:', 'rehub-theme').'</div><ul>';		
					$consvalues = explode(PHP_EOL, $cons);
					foreach ($consvalues as $consvalue) {
						if(!empty($consvalue)){
							$out .='<li>'.esc_html($consvalue).'</li>';						
						}
					}
					$out .='</ul></div>';					
				$out .='</div>';				
			}

		$out .='<span class="rh-post-layout-image-mask"></span></div>';		  					
    }
	elseif($regular){
		
    	$out = $headinghtml = '';
		$total_score = get_post_meta((int)$id, 'rehub_review_overall_score', true);
		if(!$total_score) return;
		$thecriteria = get_post_meta((int)$id, '_review_post_criteria', true);
		$pros = get_post_meta((int)$id, '_review_post_pros_text', true);
		$cons = get_post_meta((int)$id, '_review_post_cons_text', true);
		if(empty($thecriteria[0]['review_post_name']) && empty($pros)) return;
		$description = get_post_meta((int)$id, '_review_post_summary_text', true);
		$heading = get_post_meta((int)$id, '_review_heading', true);
		if(!$description){
			$description = get_the_excerpt((int)$id);
		}
		if($heading) {
			$headinghtml = '<div class="rehub-main-font font150 fontbold mb15">'.esc_html($heading).'</div>';
		}
		$image_url = get_post_meta((int)$id, '_woo_review_image_bg', true);
	

		$out = '<div class="rate_bar_wrap"><div class="review-top">';
			$out .= '<div class="overall-score"><span class="overall r_score_'.round($total_score).'">'.$total_score.'</span><span class="overall-text">'.__('Expert Score', 'rehub-theme').'</span></div>';
			$out .='<div class="review-text"><div>';
			if($image_url){
				$img = new WPSM_image_resizer();
		        $img->width = '200';
		        $img->src = $image_url;
		        $thumbnail_url = $img->get_resized_url();				
				$out .=	'<img src="'. $thumbnail_url .'" alt="'. get_the_title() .'" class="alignright hideonmobile" /> ';
			}

			$out .= $headinghtml.do_shortcode($description).'</div></div></div><div class="rh-line mb10 mt10"></div>';	
			if (!empty($thecriteria[0]['review_post_name']))  {
				$out .='<div class="pt30 mt10">';
				    foreach ($thecriteria as $criteria) {
				    	if(!empty($criteria)){
					    	$criteriascore = (float)$criteria['review_post_score'];
					    	$criterianame = $criteria['review_post_name'];
					    	$perc_criteria = $criteriascore*10;
					    	$out .='<div class="rate-bar clearfix" data-percent="'.$perc_criteria.'%">
								<div class="rate-bar-title"><span>'.esc_html($criterianame).'</span></div>
								<div class="rate-bar-bar r_score_'.round($criteriascore).'"></div>
								<div class="rate-bar-percent">'.esc_html($criteriascore).'</div>
							</div>';
						}
				    }	
				$out .='</div>';	
			}
			$pros_cons_wrap = (!empty($pros) || !empty($cons) ) ? ' class="mt20 flowhidden"' : '';
			$out .='<div'.$pros_cons_wrap.'>';
				if(!empty($pros)):
					$out .='<div';
					if(!empty($pros) && !empty($cons)):
						$out .=' class="wpsm-one-half wpsm-column-first"';
					endif;
					$out .='>';
					$out .='<div class="wpsm_pros"><div class="title_pros">'.$prostitle.'</div><ul>';		
					$prosvalues = explode(PHP_EOL, $pros);
					foreach ($prosvalues as $prosvalue) {
						if(!empty($prosvalue)){
							$out .='<li>'.$prosvalue.'</li>';						
						}
					}
					$out .='</ul></div></div>';
				endif;
				if(!empty($cons)):
					$out .='<div';
					$out .=' class="wpsm-one-half wpsm-column-last"';
					$out .='>';
					$out .='<div class="wpsm_cons"><div class="title_cons">'.$constitle.'</div><ul>';
					$consvalues = explode(PHP_EOL, $cons);
					foreach ($consvalues as $consvalue) {
						if(!empty($consvalue)){
							$out .='<li>'.$consvalue.'</li>';
						}
					}
					$out .='</ul></div></div>';
				endif;			
			$out .='</div>';
		$out .='</div>';
	}    
	else{
		$postcriteria = get_post_meta($id, '_review_post_criteria', true);
		$postAverage = get_post_meta($id, 'post_user_average', true);

		if(!empty($postcriteria) && rehub_option('type_user_review') == 'full_review' && rehub_option('type_total_score') == 'average' && $postAverage && $postAverage !='0'){
			ob_start();
			rehub_get_review();
			return '<div class="'. $additional_class .'">'.ob_get_clean().'</div>';
		}
		$style_classes = 'rate_bar_wrap';

		if ( ! empty( $additional_class ) ) {
			$style_classes .= ' ' . $additional_class;
		}
	    $scoretotal = 0; $total_counter = 0; $total_score = 0;
		$out = '<div class="'. $style_classes .'"><div class="review-top"><div class="overall-score">';
			if (!empty($criterias))  {
				$thecriteria = explode(';', $criterias);
			    foreach ($thecriteria as $criteria) {
			    	if(!empty($criteria)){
			    		$criteriaflat = explode(':', $criteria);
			    		$scoretotal += $criteriaflat[1]; $total_counter ++;
			    	}
			    }
			    if( !empty( $scoretotal ) && !empty( $total_counter ) ) $total_score =  $scoretotal / $total_counter ;
			    $total_score = round($total_score,1);
			}
		    if (!empty($score))  {
		    	$total_score = $score;
		    }	
			if($total_score){
				$out .= '<span class="overall r_score_'.round($total_score).'">'.$total_score.'</span><span class="overall-text">'.__('Expert Score', 'rehub-theme').'</span></div>';
			}	    
			$out .='<div class="review-text"><span class="review-header">'.esc_html($title).'</span><p>'.wp_kses_post($description).'</p></div></div>';
			if (!empty($criterias))  {
				$out .='<div class="review-criteria">';
				    foreach ($thecriteria as $criteria) {
				    	if(!empty($criteria)){
					    	$criteriaflat = explode(':', $criteria);
					    	$perc_criteria = $criteriaflat[1]*10;
					    	$out .='<div class="rate-bar clearfix" data-percent="'.$perc_criteria.'%">
								<div class="rate-bar-title"><span>'.$criteriaflat[0].'</span></div>
								<div class="rate-bar-bar r_score_'.round($criteriaflat[1]).'"></div>
								<div class="rate-bar-percent">'.$criteriaflat[1].'</div>
							</div>';
						}
				    }	
				$out .='</div>';	
			}
			elseif (!empty($thecriteria))  {
				$out .='<div class="pt30 mt10">';
				    foreach ($thecriteria as $criteria) {
				    	if(!empty($criteria)){
					    	$criteriascore = $criteria['review_post_score'];
					    	$criterianame = $criteria['review_post_name'];
					    	$perc_criteria = $criteriascore*10;
					    	$out .='<div class="rate-bar clearfix" data-percent="'.$perc_criteria.'%">
								<div class="rate-bar-title"><span>'.esc_html($criterianame).'</span></div>
								<div class="rate-bar-bar r_score_'.round($criteriascore).'"></div>
								<div class="rate-bar-percent">'.esc_html($criteriascore).'</div>
							</div>';
						}
				    }	
				$out .='</div>';	
			}			
			$pros_cons_wrap = (!empty($pros) || !empty($cons) ) ? ' class="mt20 flowhidden"' : '';
			$out .='<div'.$pros_cons_wrap.'>';
				if(!empty($pros)):
					$out .='<div';
					if(!empty($pros) && !empty($cons)):
						$out .=' class="wpsm-one-half wpsm-column-first"';
					endif;
					$out .='>';
					$out .='<div class="wpsm_pros"><div class="title_pros">'.esc_html($prostitle).'</div><ul>';		
					$prosvalues = explode(';', $pros);
					foreach ($prosvalues as $prosvalue) {
						if(!empty($prosvalue)){
							$out .='<li>'.esc_html($prosvalue).'</li>';						
						}
					}
					$out .='</ul></div></div>';
				endif;
				if(!empty($cons)):
					$out .='<div';
					$out .=' class="wpsm-one-half wpsm-column-last"';
					$out .='>';
					$out .='<div class="wpsm_cons"><div class="title_cons">'.esc_html($constitle).'</div><ul>';
					$consvalues = explode(';', $cons);
					foreach ($consvalues as $consvalue) {
						if(!empty($consvalue)){
							$out .='<li>'.esc_html($consvalue).'</li>';
						}
					}
					$out .='</ul></div></div>';
				endif;			
			$out .='</div>';	

		$out .='</div>';		
	}
    return $out;
}
}

//////////////////////////////////////////////////////////////////
// LATEST COMMENTS WITH REVIEW
//////////////////////////////////////////////////////////////////
if( !function_exists('rh_latest_comments') ) {
	function rh_latest_comments( $atts=array(), $content = null ) {
		$build_args =shortcode_atts(array(
			'number' => 5,
			'user_id' => '',
			'ids' => '',
			'postids' => '',
			'post_type' => 'post',
			'only_review' => '',
			'best' => '',
			'img_height' => 50,
			'img_width' => 50,
			'offset' => ''
		), $atts, 'rh_latest_comments'); 
		extract( $build_args ); 
		ob_start(); 
		?>
		
		<?php
		wp_enqueue_style('rhcomments');
		wp_enqueue_style('rhuserreviews');
		$args = array(
			'number'=> $number,
			'post_type' => $post_type,
		);
		$meta_key = 'user_average';
		if ( $post_type == 'product' && class_exists('Woocommerce') ) {
			$meta_key = 'rating';
		}
		if( $only_review ) {
			$args['meta_key'] = $meta_key;
			if($best){
				$args['orderby'] = 'meta_value_num';
				if($best == 'helpful'){
					$args['meta_key'] = 'recomm_plus';
				}else if($best == 'reverse'){
					$args['order'] = 'ASC';
				}
			}
		}
		if( $user_id ) {
			$args['user_id'] = $user_id;
		}
		if( $ids ) {
			$idsArr = explode( ',', $ids );
			$args['comment__in'] = $idsArr;
		}
		if( $postids ) {
			if($postids == 'current'){
				$postid = get_the_ID();
				$postidsArr = array($postid);
			}else{
				$postidsArr = explode( ',', $postids );				
			}

			$args['post__in'] = $postidsArr;
		}		
		if( rehub_option('color_type_review') == 'simple' ) {
			$color_type = ' simple_color';
		} else {
			$color_type = ' multi_color';
		}
		if($offset){
			$args['offset'] = $offset;
		}
		$args['status'] = 'approve';

		$comments_query = new WP_Comment_Query();
		$comments = $comments_query->query( $args );
		?>
		<ol class="rh_reviewlist commentlist">
		<?php 
		if ( $comments ) : foreach ( $comments as $comment ) :
			$author_id = $comment->user_id;
			$comment_ID = $comment->comment_ID;
			$comment_post_ID = $comment->comment_post_ID;
			$userCriteria = get_comment_meta( $comment_ID, 'user_criteria', true );
			$userAverage = get_comment_meta( $comment_ID, 'user_average', true );
			$pros_review = get_comment_meta( $comment_ID, 'pros_review', true );
			$cons_review = get_comment_meta( $comment_ID, 'cons_review', true );
			$offer_price_old = get_post_meta( $comment_post_ID, 'rehub_offer_product_price_old', true );
			$offer_price = get_post_meta( $comment_post_ID, 'rehub_offer_product_price', true );
			$offer_thumb = get_post_meta( $comment_post_ID, 'rehub_offer_product_thumb', true );
			$offer_url = get_post_meta( $comment_post_ID, 'rehub_offer_product_url', true );
			$post_url = get_permalink( $comment_post_ID );
			
			if ( $post_type == 'product' && class_exists('Woocommerce') ) {
				$_product = wc_get_product( $comment_post_ID );
				$product_price = $_product->get_price_html();
				$userAverage = get_comment_meta( $comment_ID, 'rating', true );
				$pros_review = get_comment_meta( $comment->comment_ID, 'pos_comment', true );
				$cons_review = get_comment_meta( $comment->comment_ID, 'neg_comment', true );
			}
			$text = $textsec = '';
		?>
		<li class="mb15 ml0 commid-<?php echo (int)$comment_ID; ?>">
			<div class="commbox">
				<div class="commheader clearfix padd20 pb10 border-grey-bottom">
					<?php if($postids == 'current'):?>		
						<div class="comment-author vcard clearfix">                   
							<?php echo get_avatar($comment,50); ?>
							<div class="comm_meta_wrap">
								<span class="fn"><?php echo get_comment_author( $comment_ID); ?></span>	
								<?php 
								if( isset( $userAverage ) && $userAverage != '' ) {
									$userAverages = ($post_type == 'product') ? ($userAverage * 20) : ($userAverage * 10); 
									$userstartitle = ($post_type == 'product') ? $userAverage : ($userAverage / 2);
									echo '<div class="user_reviews_view_score mt10 mb0"><div class="userstar-rating" title="'. esc_html__('Rated', 'rehub-theme') .' '. $userstartitle .' '. esc_html__('out of', 'rehub-theme') .' 5"><span style="width:'. $userAverages .'%"><strong class="rating">'. $userstartitle .'</strong></span></div></div>';
								}
								?>			
								<span class="time"><a href="#comment-<?php echo ''.$comment_ID ?>"><?php printf( esc_html__( 'Reviewed on %s %s %s', 'rehub-theme' ), '<span class="date greycolor">', get_comment_date( get_option( 'date_format' ), $comment_ID ), '</span>' ); ?></a></span>
			                </div>				
						</div>
					<?php else:?>	
						<figure style="width:<?php echo (int)$img_width; ?>px" class="floatleft <?php echo (is_rtl()) ? 'ml20' : 'mr20';?>">
							<a href="<?php echo ''.$post_url; ?>">
								<?php if ( empty( $offer_thumb ) ) :?>
									<?php echo get_the_post_thumbnail( $comment_post_ID, array($img_width, $img_height) ); ?>
								<?php else :?>
									<?php WPSM_image_resizer::show_static_resized_image(array('lazy'=> true, 'src'=> $offer_thumb, 'crop'=> true, 'height'=> $img_height, 'width'=> $img_width));?>
								<?php endif ;?>
							</a>
						</figure>
						<?php $img_width_2 = $img_width + 20;?>
						<div class="commwrap floatleft" style="width:calc(100% - <?php echo (int)$img_width_2; ?>px)">
							<h4 class="mt0 mb10"><a href="<?php echo ''.$post_url; ?>"><?php echo esc_html( get_the_title( $comment_post_ID ) ); ?></a>
								<?php if(!empty($product_price)):?>
									- <span class="fontnormal rehub-main-color product_price_in_comm"><?php echo ''.$product_price;?></span>
								<?php elseif($offer_price):?>
									<span class="fontnormal"> - <span class="product_price_in_comm rehub-main-color"><?php echo ''.$offer_price;?></span>
									<?php if($offer_price_old):?> 
										<del class="product_price_in_comm lightgreycolor font80"><?php echo ''.$offer_price_old;?></del>
									<?php endif;?>
									</span>
								<?php endif;?>
							</h4>			
							<span class="commmeta font80">
							<?php 
								if( isset( $userAverage ) && $userAverage != '' ) {
									$userAverages = ($post_type == 'product') ? ($userAverage * 20) : ($userAverage * 10); 
									$userstartitle = ($post_type == 'product') ? $userAverage : ($userAverage / 2);
									echo '<div class="user_reviews_view_score mb0"><div class="userstar-rating" title="'. esc_html__('Rated', 'rehub-theme') .' '. $userstartitle .' '. esc_html__('out of', 'rehub-theme') .' 5"><span style="width:'. $userAverages .'%"><strong class="rating">'. $userstartitle .'</strong></span></div></div>';
								}
								printf( esc_html__( 'Reviewed on %s %s %s by', 'rehub-theme' ), '<span class="date greycolor">', get_comment_date( get_option( 'date_format' ), $comment_ID ), '</span>' );
								echo ' <a href="'. get_comment_link( $comment_ID ) .'" class="author-'. $author_id .'">' . get_comment_author( $comment_ID ) .'</a>'; 
							?>
							</span>
						</div>	
					<?php endif;?>			
				</div>
				<div class="commcontent padd20">
				<?php 
		
					if( is_array( $userCriteria ) && !empty( $userCriteria ) ) {
						$text ='<div class="user_reviews_view_box mt20 mobileblockdisplay">';
						for( $i = 0; $i < count($userCriteria); $i++ ) {
							$value_criteria = $userCriteria[$i]['value'] * 10;		
							$text .= '<div class="user_reviews_view_criteria_line lineheight15 mb10 flowhidden"><span class="user_reviews_view_criteria_name floatleft">'. $userCriteria[$i]['name'] .'</span><div class="userstar-rating"><span style="width:'. $value_criteria .'%"><strong class="rating">'. $value_criteria .'</strong></span></div></div>';
						}
						$text .= '</div>';
						
						$textsec .= '<div class="flowhidden">';
						if( isset($pros_review) && $pros_review != '' ) {
							$pros_reviews = explode(PHP_EOL, $pros_review);
							$proscomment = '';
							foreach ($pros_reviews as $pros) {
								$proscomment .='<span class="pros_comment_item">'. $pros .'</span>';
							}
							$textsec .= '<div class="wpsm-one-half wpsm-column-first user_reviews_view_pros"><span class="user_reviews_view_pc_title mb5">'.__('+ PROS:', 'rehub-theme').' </span><span> '. wp_kses_post($proscomment) .'</span></div>';
						}
					
						if( isset($cons_review) && $cons_review != '' ) {
							$cons_reviews = explode(PHP_EOL, $cons_review);
							$conscomment = '';
							foreach ($cons_reviews as $cons) {
								$conscomment .='<span class="cons_comment_item">'. $cons .'</span>';
							}		
							$textsec .= '<div class="wpsm-one-half wpsm-column-last user_reviews_view_cons"><span class="user_reviews_view_pc_title mb5">'.__('- CONS:', 'rehub-theme').'</span><span> '. wp_kses_post($conscomment) .'</span></div>';
						}
						$textsec .= '</div>';
						
						
						echo '<div class="font90 user_reviews_view'. $color_type .'">';
						comment_text($comment_ID);
						echo ''.$text;
						echo '<div class="user_reviews_view_proscons mt20 mobileblockdisplay">';
							echo ''.$textsec;
						echo '</div></div>';
					}else if($post_type == 'product'){
						echo '<div class="font90 user_reviews_view'. $color_type .'">';
						comment_text($comment_ID);
						echo rehub_wc_comment_neg_get($comment);
						echo '</div>';
					}
					 else {
						echo '<div class="font90 user_reviews_view'. $color_type .'">';
						comment_text($comment_ID);
						echo '</div>';
					}
				?>
				</div>
			</div>
		</li>
		<?php endforeach; endif; ?>
		</ol>
		<?php
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
}

// WPSM Banner
if( !function_exists('wpsm_banner_shortcode') ) {
function wpsm_banner_shortcode( $atts, $content = null ) {
	$atts = shortcode_atts(
		array(
			'title' => 'Title',
			'subtitle' => 'Subtitle',
			'image_id' => '',
			'enable_icon' => '',
			'icon' => 'rhicon rhi-gift',
			'color' => '',
			'colortext' => '#111',			
			'padding' => 40,
			'height' => '',
			'align' => '',
			'overlay' => '',
			'url' => '',
			'firstsize' => '',
			'secondsize' => '',
			'vertical' => 'middle',
			'bg' => '#cecece',
			'image_url' => '',
			'targetself' => '',
			'btn' => '',
			'btn_label' => 'Buy this',

		),
		$atts,
		'wpsm_hover_banner'
	);
	extract( $atts );

	wp_enqueue_style('rhbanner');

	$imagehtml = '';
	if ($image_id) {
		$image_url = wp_get_attachment_image_src($image_id, 'full');
		$image_url = $image_url[0];
	}
	if($image_url){
		$imagehtml = '<img class="lazyload" data-src="'.$image_url.'" width=300 height=300 alt="'.esc_html($title).'" src="'.get_template_directory_uri() . '/images/default/blank.gif" />';
	}
	//$b_style = empty($image_url) ? '' : 'background-image:url('.$image_url.');';
	$h_style = empty($height) ? '' : 'height:'.$height.'px';
	$c_pad = 'padding: '.$padding.'px';
	$b_pad = (int)$padding / 2 .'px';
	$main_color = rehub_option('rehub_custom_color');
	$color = empty($color) ? $main_color : $color;
	$target = empty($targetself) ? 'target="_blank"' : 'target="_self"';

	$rand_id = mt_rand().time();
	
	$icon = $enable_icon ? '<i class="'. $icon .'" aria-hidden="true"></i> ' : '';
	
	if($align == 'right'){
		$text_align = ' text-right-align';
	}else if($align == 'center'){
		$text_align = ' text-center';
	}else{
		$text_align = '';
	}
	
	if($overlay == 1){
		$overlay_class = ' wpsm-banner-overlay';
		$mask_div = '<div class="wpsm-banner-mask"></div>';
	}else{
		$overlay_class = '';
		$mask_div = '';
	}
	$colortext = empty($colortext) ? '' : '#wpsm_banner_'.$rand_id.' h4, #wpsm_banner_'.$rand_id.' h6{color:'.$colortext.'}';
	$firstsize = empty($firstsize) ? '' : '#wpsm_banner_'.$rand_id.' h4{font-size:'.$firstsize.'}';	
	$secondsize = empty($secondsize) ? '' : '#wpsm_banner_'.$rand_id.' h6{font-size:'.$secondsize.'}';
	$vertical = ($vertical =='middle') ? '' : '#wpsm_banner_'.$rand_id.' .celldisplay{vertical-align:'.$vertical.'}';
	$output = '';
	$output .= '<div id="wpsm_banner_'.$rand_id.'" class="wpsm-banner-wrapper rh-hovered-wrap full_cover_link position-relative flowhidden'.$overlay_class.'">';
	$output .= '<style scope>#wpsm_banner_'.$rand_id.' .wpsm-banner-image{background-color:'.$bg.';'.$h_style.'}#wpsm_banner_'.$rand_id.' .wpsm-banner-text i{color:'.$color.'}#wpsm_banner_'.$rand_id.' .wpsm-banner-text:before, #wpsm_banner_'.$rand_id.' .wpsm-banner-text:after{border-color:'.$color.';top:'.$b_pad.';right:'.$b_pad.';bottom:'.$b_pad.';left:'.$b_pad.';opacity: 0.9;}#wpsm_banner_'.$rand_id.' .celldisplay{'.$c_pad.'}'.$colortext.$firstsize.$secondsize.$vertical.'</style>';	
		if (!empty($url)) { $output .= '<a href="'.$url.'" '.$target.' title="'.$title.'" class="position-relative">'; }
			$output .= '<div class="wpsm-banner-image categoriesbox-bg csstranstranslong rh-hovered-scalebig">'.$mask_div.'<div class="abdfullwidth imageasbg rh-flex-center-align rh-flex-justify-center flowhidden rh-flex-align-stretch rh-fit-cover">'.$imagehtml.'</div></div>';

			$output .= '<div class="wpsm-banner-text"><div class="tabledisplay">';
				$output .= '<div class="celldisplay'. $text_align .'">';
					$output .= sprintf( '%s<h4>%s</h4><h6>%s</h6>', $icon, $title, $subtitle );
					if($btn) {
						$output .= '<span class="wpsm-button medium wpsm-nobrd">'.esc_attr($btn_label).'</span>';
					}
				$output .='</div>';
			$output .= '</div></div>';
		if (!empty($url)) { $output .= '</a>'; }
	$output .= '</div>';
	
	return $output;
}
}


//////////////////////////////////////////////////////////////////
// Itinerary shortcode
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_itinerary_shortcode') ) {
	function wpsm_itinerary_shortcode( $atts, $content = null  ) {	
		wp_enqueue_style('rhitinerary');
		$content = do_shortcode($content);
        $content = preg_replace( '%<p>&nbsp;\s*</p>%', '', $content ); 
		$content = preg_replace('/^(?:<br\s*\/?>\s*)+/', '', $content);
		return '<div class="wpsm-itinerary">'. $content .'</div>';
	}
}
if( !function_exists('wpsm_itinerary_item_shortcode') ) {
	function wpsm_itinerary_item_shortcode( $atts , $content = null ) {
		$atts = shortcode_atts(
			array(
				'icon' => '',
				'color' => ''
			),
			$atts,
			'wpsm_itinerary_item'
		);
		extract($atts);
		$color = empty($color) ? '#409cd1' : $color;
		$icon = empty($icon) ? 'rhi-circle-solid' : $icon;
		$content = do_shortcode($content);
		$prefix = '</p>';
		if (substr($content, 0, strlen($prefix)) == $prefix) {
    		$content = substr($content, strlen($prefix));
		} 
		$content = str_replace( '<p>&nbsp;</p>', '', $content );
		$output = '<div class="wpsm-itinerary-item">';
			$output .= '<div class="wpsm-itinerary-icon"><span style="background-color:'. $color .'"><i class="rhicon '. $icon .'" aria-hidden="true"></i></span></div>';
			$output .= '<div class="wpsm-itinerary-content">'. $content .'</div>';
		$output .= '</div>';
		return $output;
	}
}

//////////////////////////////////////////////////////////////////
// Versus shortcode
//////////////////////////////////////////////////////////////////

if( !function_exists('wpsm_versus_shortcode') ) {
	function wpsm_versus_shortcode( $atts , $content = null ) {
		$atts = shortcode_atts(
			array(
				'heading' => '',
				'subheading' => '',
				'type' => 'two',
				'bg' => '',
				'color' => '',
				'firstcolumntype' => '',
				'secondcolumntype' => '',
				'thirdcolumntype' => '',		
				'firstcolumngrey' => '',
				'secondcolumngrey' => '',
				'thirdcolumngrey' => '',
				'firstcolumncont' => '',
				'secondcolumncont' => '',
				'thirdcolumncont' => '',
				'firstcolumnimg' => '',
				'secondcolumnimg' => '',
				'thirdcolumnimg' => '',								
			),
			$atts,
			'wpsm_versus'
		);
		extract($atts);
		wp_enqueue_style('rhversus');
		$fclass = $sclass = $tclass = array();
		$fclass[] = 'vs-1-col';
		$sclass[] = 'vs-2-col';
		$tclass[] = 'vs-3-col';
		$rand_id = mt_rand().'vers';
		$output = '<div class="wpsm-versus-item" id="wpsm-vs-'.$rand_id .'">';

			if($bg || $color){
				$colorstyle = empty($color) ? '' : '#wpsm-vs-'.$rand_id.', #wpsm-vs-'.$rand_id.' .vs-conttext{color:'.$color.'}';
				$bgstyle = empty($bg) ? '' : '#wpsm-vs-'.$rand_id.'{background-color:'.$bg.'; margin-bottom:6px}';				
				$output .= '<style scope>'.$colorstyle.$bgstyle.'</style>';	
			}

			$output .= '<div class="title-versus rehub-main-font"><span class="vs-heading">'.$heading.'</span><span class="vs-subheading">'.$subheading.'</span></div>';
			$output .= '<div class="wpsm-versus-cont">';

				if($firstcolumntype == 'tick'){
					$fclass[] = 'vs-tick';
				}
				elseif($firstcolumntype == 'times'){
					$fclass[] = 'vs-times';
				}	
				elseif($firstcolumntype == 'image'){
					$fclass[] = 'vs-img-col';
				}					
				else{
					$fclass[] = 'vs-conttext';						
				}				
				if($firstcolumngrey){
					$fclass[] = 'vs-greyscale';
				}						
				$output .= '<div class="'.implode(' ', $fclass).'">';
					if($firstcolumntype == 'tick'){
						$output .= '<i class="rhicon rhi-check-circle-solid" aria-hidden="true"></i>';
					}
					elseif($firstcolumntype == 'times'){
						$output .= '<i class="rhicon rhi-times" aria-hidden="true"></i>';
					}		
					elseif($firstcolumntype == 'image'){
						$image_url = wp_get_attachment_image_url($firstcolumnimg, 'smallgrid');						
						$output .=  '<img src="'.$image_url.'" class="vs-image" />';
					}	
					else{
						$output .=  do_shortcode($firstcolumncont);
					}																	
				$output .= '</div>';
				$output .= '<div class="vs-circle-col"><div class="vs-circle">VS</div></div>';

				if($secondcolumntype == 'tick'){
					$sclass[] = 'vs-tick';
				}
				elseif($secondcolumntype == 'times'){
					$sclass[] = 'vs-times';
				}	
				elseif($secondcolumntype == 'image'){
					$sclass[] = 'vs-img-col';
				}					
				else{
					$sclass[] = 'vs-conttext';						
				}				
				if($secondcolumngrey){
					$sclass[] = 'vs-greyscale';
				}						
				$output .= '<div class="'.implode(' ', $sclass).'">';
					if($secondcolumntype == 'tick'){
						$output .= '<i class="rhicon rhi-check-circle-solid" aria-hidden="true"></i>';
					}
					elseif($secondcolumntype == 'times'){
						$output .= '<i class="rhicon rhi-times" aria-hidden="true"></i>';
					}	
					elseif($secondcolumntype == 'image'){
						$image_url = wp_get_attachment_image_url($secondcolumnimg, 'smallgrid');					
						$output .=  '<img src="'.$image_url.'" class="vs-image" />';
					}
					else{
						$output .=  do_shortcode($secondcolumncont);
					}																		
				$output .= '</div>';	

				if($type=='three'){
					$output .= '<div class="vs-circle-col"><div class="vs-circle">VS</div></div>';
					if($thirdcolumntype == 'tick'){
						$tclass[] = 'vs-tick';
					}
					elseif($thirdcolumntype == 'times'){
						$tclass[] = 'vs-times';
					}
					elseif($thirdcolumntype == 'image'){
						$tclass[] = 'vs-img-col';
					}					
					else{
						$tclass[] = 'vs-conttext';						
					}	
					if($thirdcolumngrey){
						$tclass[] = 'vs-greyscale';
					}						
					$output .= '<div class="'.implode(' ', $tclass).'">';
						if($thirdcolumntype == 'tick'){
							$output .= '<i class="rhicon rhi-check-circle-solid" aria-hidden="true"></i>';
						}
						elseif($thirdcolumntype == 'times'){
							$output .= '<i class="rhicon rhi-times" aria-hidden="true"></i>';
						}		
						elseif($thirdcolumntype == 'image'){
							$image_url = wp_get_attachment_image_url($thirdcolumnimg, 'smallgrid');					
							$output .=  '<img src="'.$image_url.'" class="vs-image" />';
						}
						else{
							$output .=  do_shortcode($thirdcolumncont);
						}																			
					$output .= '</div>';					
				}


			$output .= '</div>';
		$output .= '</div>';
		return $output;
	}
}

//////////////////////////////////////////////////////////////////
// Compare Bar
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_compare_bar_shortcode') ) {
	function wpsm_compare_bar_shortcode( $atts  ) {		
		extract( shortcode_atts( array(
			'max' => '',
			'lines' => '',
			'color' => '',
			'unit' => '',
			'marktype' => 'max',
			'markcolor' => ''
		), $atts ) );	

		$output = '';
		if (empty($lines) || empty($max)) return;

		$lines =  explode('@@', $lines);

		$bar_array = array();
		$value_array = array();

		foreach ($lines as $key => $bars) {
			if(empty($bars)) continue;
			$bars = explode('::', $bars);
			if(empty($bars[1]) || empty($bars[0])) continue;
			$bar_array[$key]['title'] = esc_html($bars[0]);
			$bar_array[$key]['value'] = $value_array[] = (int)$bars[1];
			$bar_array[$key]['link'] = (!empty($bars[2])) ? esc_url($bars[2]) : '';
			$perc_value = (int)$bars[1] / $max * 100;
			if($perc_value >100) $perc_value = 100;
			$bar_array[$key]['percentage'] = $perc_value;
		}	

		if($marktype == 'min'){
			$minvalue = min($value_array);
			$bestkey = array_search($minvalue, $value_array);
		}else{
			$maxvalue = max($value_array);
			$bestkey = array_search($maxvalue, $value_array);			
		}

		$output .= '<div class="wpsm-bar-compare mb25">';

		$output .= rh_generate_incss('barcompare');
		
		foreach ($bar_array as $index => $barline) {
			
			if($index == $bestkey){
				if($markcolor) {
					$bg = $markcolor;
				}
				else{
					$bg = '#f07a00';
				}
			}
			elseif(!empty($color)){
				$bg = $color;
			}
			else{
				$bg='';
			}
			$percentage = $barline['percentage'];
			$title = $barline['title'];
			$value = $barline['value'];
			$link = (!empty($barline['link'])) ? '<a href="'.$barline["link"].'">' : '';
			$linkclose = (!empty($link)) ? ' <i class="rhicon rhi-external-link" aria-hidden="true"></i></a>' : '';

			$stylebg = ($bg) ? ' style="background: '. $bg .'"' : '';
			$output .= '<div class="wpsm-bar wpsm-clearfix wpsm-bar-compare" data-percent="'. $percentage .'%">';
				$output .= '<div class="wpsm-bar-title"><span>'.$link. $title .$linkclose.'</span></div>';
				$output .= '<div class="wpsm-bar-bar"'.$stylebg.'></div>';
				$output .= '<div class="wpsm-bar-percent">'.$value.$unit.'</div>';
			$output .= '</div>';			

		}

		$output .= '</div>';
		
		return $output;
	}
}