<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

/*-----------------------------------------------------------------------------------*/
# 	Compare functions
/*-----------------------------------------------------------------------------------*/
if(!is_admin()) add_action('init', 'rehub_compare_script');
function rehub_compare_script(){
	wp_enqueue_script('rehubcompare');	
	wp_enqueue_style('rhslidingpanel');
	wp_enqueue_style('rhcompare');	
	$trans_array = array( 
		'item_error_add' => esc_html__( 'Please, add items to this compare group or choose not empty group', 'rehub-theme' ), 
		'item_error_comp' => esc_html__( 'Please, add more items to compare', 'rehub-theme' ),
		'comparenonce' => wp_create_nonce('compare-nonce'),
	);
	wp_localize_script( 'rehubcompare', 'comparechart', $trans_array );
}


/*ADD COMPARE QUERY VAR*/
function add_query_vars_compareids( $vars ){
   $vars[] = "compareids";
   return $vars;
}
add_filter( 'query_vars', 'add_query_vars_compareids' );

if (!function_exists('rh_compare_charts_title_dynamic')){
	function rh_compare_charts_title_dynamic(){
		global $compareids;
		$separator = ' VS ';
		//$compareids = (get_query_var('compareids')) ? explode(',', get_query_var('compareids')) : '';
		if (!empty($compareids)){
			$countids = count($compareids);
			$title_compare = '';
			$i=0;
			foreach ($compareids as $compareid){
				$i++;
				$title_compare .= get_the_title($compareid);
				if ($i !=$countids){
					$title_compare .= $separator;
				}
			}
			return $title_compare;
		}
	}	
}

/* GET MULTICATS DATA */
if( !function_exists('rehub_get_compare_multicats') ) {
function rehub_get_compare_multicats(){
	$data = rehub_option('compare_multicats_textarea');
	if(empty($data))
		return;
	$array = array_map(
		function($string) {
			return explode(';', $string);
		},
		explode(PHP_EOL, $data)
	);
	return $array;
}
}


/*ADD PANEL TO FOOTER*/
function rehub_comparepanel_footer(){
	$compare_page = rehub_option('compare_page');
	$wraps = $tabs = $multicats_on = '';
	$multicats_array = rehub_get_compare_multicats();
	if(!empty($multicats_array)){
		$multicats_on = true;
	}
	$multipages = array();
	if($multicats_on) {
		foreach($multicats_array as $multicat) {

			$pageid = (int)$multicat[2];
			$multipages[] = $pageid;
			$compare_url = (get_post_type($pageid) =='page' ) ? esc_url(get_the_permalink($pageid)) : esc_url(get_the_permalink($compare_page));
			$tabs .= '<li class="re-compare-tab-'. $pageid .'" data-page="'. $pageid .'" data-url="'. $compare_url .'">'. $multicat[1] .' (<span>0</span>)</li>'; 
			$wraps .= '<div class="re-compare-wrap re-compare-wrap-'. $pageid .'"></div>';
		}
	}
	?>
		<div id="re-compare-bar" class="from-right rh-sslide-panel">
			<div id="re-compare-bar-wrap" class="rh-sslide-panel-wrap">
				<div id="re-compare-bar-heading" class="rh-sslide-panel-heading">
					<h5 class="rehub-main-color pt15 pb15 pr15 pl20 mt0 mb0 font120"><?php esc_html_e('Compare items', 'rehub-theme');?><i class="blackcolor closecomparepanel rh-sslide-close-btn cursorpointer floatright font130 rhi-times-circle rhicon" aria-hidden="true"></i></h5>
				</div>
				<div id="re-compare-bar-tabs" class="rh-sslide-panel-tabs abdfullwidth mt30 pb30 pt30 width-100p">
					<?php if($multicats_on) : ?>
						<ul><?php echo ''.$tabs; ?></ul>
						<div><?php echo ''.$wraps; ?></div>
					<?php else : ?>
						<ul class="rhhidden"><li class="re-compare-tab-<?php echo ''.$compare_page; ?> no-multicats" data-page="<?php echo ''.$compare_page; ?>" data-url="<?php echo esc_url(get_the_permalink($compare_page)); ?>"><?php esc_html_e('Total', 'rehub-theme');?> (<span>0</span>)</li></ul>
						<div><div class="rh-sslide-panel-inner mt10 re-compare-wrap pr20 pl20 re-compare-wrap-<?php echo ''.$compare_page; ?>"></div></div>
					<?php endif; ?>
					<span class="re-compare-destin wpsm-button rehub_main_btn" data-compareurl=""><?php esc_html_e('Compare', 'rehub-theme');?><i class="<?php if(is_rtl()):?>rhi-arrow-square-left<?php else:?>rhi-arrow-circle-right<?php endif;?> rhicon" aria-hidden="true"></i></span>
				</div>
			</div>
		</div>
		<?php if(rehub_option('compare_disable_button') != 1):?>
			<div id="re-compare-icon-fixed" class="rhhidden">
				<?php echo rh_compare_icon(array());?>
			</div>
		<?php endif;?>
		<?php if( ($compare_page && is_page($compare_page)) || (!empty($multipages) && is_page($multipages) ) ):?>
		    <div class="comp-search rhhidden">
		        <button id="btn_search_close" class="btn-search-close" aria-label="Close search form"><i class="rhicon rhi-times"></i></button>
		        <form class="comp-search-form" action="<?php echo home_url( '/' ); ?>">
		            <input class="comp-search-input" name="s" type="search" autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false">
		            <span class="comp-search-info"><?php esc_html_e('Type name of products', 'rehub-theme'); ?></span>
		        </form>
		        <div class="comp-ajax-search-wrap"></div>
		    </div>
	    <?php endif;?>		
	<?php 
}
add_action('wp_footer', 'rehub_comparepanel_footer');


/*PANEL VIEW*/
if (!function_exists('re_compare_item_in_panel')) {
	function re_compare_item_in_panel($compareid) {	
		$image_id = get_post_thumbnail_id($compareid);  
		$image_url = wp_get_attachment_image_src($image_id,'thumbnail');  
		$img = (!empty($image_url)) ? $image_url[0] : '';	
		$imgparams = array('height' => 43, 'crop' => true);			
		$nothumb = get_template_directory_uri() . '/images/default/noimage_100_70.png';
		$compare_title = get_the_title($compareid); 
		$compare_title_truncate = rehub_truncate_title(55, $compareid);		
		$out = '<div class="re-compare-item compare-item-'.$compareid.'" data-compareid="'.$compareid.'">';	
			$out .= '<i class="rhicon rhi-times-circle-solid re-compare-new-close"></i>';
			$out .= '<div class="re-compare-img">';
				$out .= '<a href="'.get_the_permalink($compareid).'">';
                    if(!empty($img)) :
                        $out .= '<img src="'.bfi_thumb( $img, $imgparams).'" alt="'.$compare_title.'" />';
                    else :   
                        $out .= '<img src="'.$nothumb.'" alt="'.$compare_title.'" />';
                    endif; 					
				$out .= '</a>';
			$out .= '</div>';
			$out .= '<div class="re-compare-title mb5">';
				$out .= '<a href="'.get_the_permalink($compareid).'">'; 
                    $out .= $compare_title_truncate; 					
				$out .= '</a>';
			$out .= '</div>';
            $the_price = get_post_meta( $compareid, '_price', true);  
            if ( '' != $the_price && function_exists('wc_price') ) {
                if(rehub_option('ce_custom_currency')){
                    $currency_code = rehub_option('ce_custom_currency');
                    $woocurrency = get_woocommerce_currency(); 
                    if($currency_code != $woocurrency && defined('\ContentEgg\PLUGIN_PATH')){
                        $currency_rate = \ContentEgg\application\helpers\CurrencyHelper::getCurrencyRate($woocurrency, $currency_code);
                        if (!$currency_rate) $currency_rate = 1;
                        $the_price = \ContentEgg\application\helpers\TemplateHelper::formatPriceCurrency($the_price*$currency_rate, $currency_code, '<span class="woocommerce-Price-currencySymbol">', '</span>');
                    }
                    else{
                        $the_price = strip_tags( wc_price( $the_price ) );
                    }                                               
                }else{
                    $the_price = strip_tags( wc_price( $the_price ) );
                }            	
                $out .= '<div class="greencolor font80">'.$the_price.'</div>';
            }                							
		$out .= '</div>';
		
		return $out;
	}
}

if( !function_exists('re_compare_panel') ) {
function re_compare_panel($echo=''){
	
	$content = $count = $post_ids_arr = $comparing = $pageids = $total_comparing_ids = array();	

	$multicats_on = '';
	$multicats_array = rehub_get_compare_multicats();
	if(!empty($multicats_array)){
		$multicats_on = true;
	}	
	$total_count = 0;
	
	#user identity
	$ip = rehub_get_ip();
	$userid = get_current_user_id();
	$userid = empty($userid) ? $ip : $userid;
	
	if($multicats_on) {
		foreach( $multicats_array as $multicat ){
			$page_id = (int)$multicat[2];
			$out = '';
			#existing posts
			$post_ids = esc_html(get_transient('re_compare_'. $page_id .'_' . $userid));
			if(empty($post_ids)) {
				continue;
			} else {
				$post_ids_arr = explode(',', $post_ids);
				$count[$page_id] = count( $post_ids_arr );
				$total_count += count( $post_ids_arr );
			}
			foreach($post_ids_arr as $compareid) {
				$out .= re_compare_item_in_panel( $compareid );
			}
			$content[$page_id] = $out;
			$comparing[$page_id] = implode(',', $post_ids_arr);
			$pageids[] = $page_id;
			$total_comparing_ids = $post_ids_arr;
		}
	} else {
		$post_ids = esc_html(get_transient('re_compare_' . $userid));
		$page_id = rehub_option('compare_page');
		$out = '';
		if(!empty($post_ids)) {
			$post_ids_arr = explode(',',$post_ids);
			$count[$page_id] = count( $post_ids_arr );
			$total_count = $count[$page_id];
		}
		foreach($post_ids_arr as $compareid) {
			$out .= re_compare_item_in_panel($compareid);
		}
		$content[$page_id] = $out;
		$comparing[$page_id] = implode(',', $post_ids_arr);
		$pageids[] = $page_id;
		$total_comparing_ids = $post_ids_arr;
	}
	$cssactive = empty($count) ? '' : 'active';
	#generate the response
	if($echo=='count'){
		return $total_count;
	}
	check_ajax_referer( 'compare-nonce', 'security' );
	$response = json_encode( array( 'content' => $content, 'cssactive' => $cssactive, 'comparing' => $comparing, 'count' => $count, 'total_count' => $total_count, 'pageids' => $pageids, 'total_comparing_ids'=> $total_comparing_ids) );
	#response output
	header( "Content-Type: application/json" );
	echo ''.$response;
	exit;
}
}
add_action('wp_ajax_re_compare_panel', 're_compare_panel');
add_action('wp_ajax_nopriv_re_compare_panel', 're_compare_panel');

/*COMPARE AJAX*/
if(!function_exists('re_add_compare')) {	
#compare toggling
function re_add_compare() {
	check_ajax_referer( 'compare-nonce', 'security' );
	$post_ids_arr = array();	
	$out = $multicats_on = '';
	$multicats_array = rehub_get_compare_multicats();
	if(!empty($multicats_array)){
		$multicats_on = true;
	}	

	$compareid = (int)$_POST['compareID'];
	$perform = sanitize_text_field($_POST['perform']);
	
	#user identity
	$ip = rehub_get_ip();
	$userid = get_current_user_id();
	$userid = empty($userid) ? $ip : $userid;
	
	if($multicats_on) {
		foreach( $multicats_array as $multicat ){
			$cat_ids = trim($multicat[0]);
			$cat_ids_arr = explode(',', $cat_ids);
			
			if( isset($multicat[3]) ) {
				$term_slug = trim($multicat[3]);
			} else {
				$term_slug = 'product_cat';
			}
			#check if post belongs to listed terms / categories
			if(isset($checkterm) && $checkterm == $term_slug){
			  //do nothing 
			}else{
			  $post_terms = wp_get_post_terms($compareid, $term_slug, array("fields" => "ids"));
			}
			$checkterm = $term_slug;
			$post_in_cat = array_intersect($post_terms, $cat_ids_arr);
			
			if(array_filter($post_in_cat)) {
				$page_id = (int)$multicat[2];
				#existing posts
				$post_ids = get_transient('re_compare_'. $page_id .'_' . $userid);
				switch($perform) {
					case 'add':
						if(empty($post_ids)) {
							$post_ids_arr[] = $compareid;
							set_transient('re_compare_'. $page_id .'_' . $userid, $compareid, 30 * DAY_IN_SECONDS);
						} else {
							$post_ids_arr = explode(',', $post_ids);
							if (($key = array_search($compareid, $post_ids_arr)) === false){
								$post_ids_arr[] = $compareid;
								$newvalue = implode(',', $post_ids_arr);
								set_transient('re_compare_'. $page_id .'_' . $userid, $newvalue, 30 * DAY_IN_SECONDS);
							}
						}
					break;
					case 'remove':
						$post_ids_arr = explode(',', $post_ids);
						if(($key = array_search($compareid, $post_ids_arr)) !== false) {
							unset($post_ids_arr[$key]);
						}
						$newvalue = implode(',', $post_ids_arr);
						if (empty($newvalue)) {
							delete_transient('re_compare_'. $page_id .'_' . $userid);
						} else {
							set_transient('re_compare_'. $page_id .'_' . $userid, $newvalue, 30 * DAY_IN_SECONDS);
						}
					break;	
				}
				#html output
				$out = re_compare_item_in_panel($compareid);
				$count = count($post_ids_arr);
				$comparing_string = implode(',', $post_ids_arr);
			}
		}
	} else {
		$post_ids = get_transient('re_compare_' . $userid);
		switch($perform) {
			case 'add':
				if(empty($post_ids)) {
					$post_ids_arr[] = $compareid;
					set_transient('re_compare_' . $userid, $compareid, 30 * DAY_IN_SECONDS);
				} else {
					$post_ids_arr = explode(',',$post_ids);
					if (($key = array_search($compareid, $post_ids_arr)) === false){
						$post_ids_arr[] = $compareid;
						$newvalue = implode(',', $post_ids_arr);
						set_transient('re_compare_' . $userid, $newvalue, 30 * DAY_IN_SECONDS);
					}
				}
			break;
			case 'remove':
				$post_ids_arr = explode(',', $post_ids);
				if(($key = array_search($compareid, $post_ids_arr)) !== false) {
					unset($post_ids_arr[$key]);
				}
				$newvalue = implode(',',$post_ids_arr);
				if (empty($newvalue)) {
					delete_transient('re_compare_' . $userid);
				}
				else {
					set_transient('re_compare_' . $userid, $newvalue, 30 * DAY_IN_SECONDS);
				}
			break;	
		}
		#html output
		$out = re_compare_item_in_panel($compareid);
		$count = count($post_ids_arr);
		$comparing_string = implode(',', $post_ids_arr);
		$page_id = rehub_option('compare_page');
	}
		
	#generate the response
	$response = json_encode( array( 'content' => $out, 'comparing' => $comparing_string, 'count' => $count, 'pageid' => $page_id ) );

	#response output
	header( "Content-Type: application/json" );
	echo ''.$response;
	exit;
}
}
add_action('wp_ajax_re_add_compare', 're_add_compare');
add_action('wp_ajax_nopriv_re_add_compare', 're_add_compare');

if (!function_exists('add_to_compare_search')){
function add_to_compare_search() {
	
    check_ajax_referer( 'compare-nonce', 'security' );
    #get search string
    if (empty($_POST['search_query']))
        return;
    
    $search_query = sanitize_text_field($_POST['search_query']);
    
    $buffer = '';
    $compare_ids_arr = array();
    
    #user identity
    $ip = rehub_get_ip();
    $userid = get_current_user_id();
    $userid = empty($userid) ? $ip : $userid;
    
    #get current comparing ids
    $compare_ids = esc_html(get_transient('re_compare_' . $userid));
    
    if( !empty( $compare_ids ) ) {
        $compare_ids_arr = explode( ',', $compare_ids );
    }
        
    #the post types for search    
    $posttype = explode(',', sanitize_text_field($_POST['posttype']));

    #build arguments fo WP_Query
    $args = array(
        's' => $search_query,
        'post_type' => $posttype,
        'posts_per_page' => 5,
        'post_status' => 'publish',
        'cache_results' => false,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'no_found_rows' => true     
    );
    
    #add terms to arguments
    $taxonomy = sanitize_text_field($_POST['taxonomy']);
    
    if (!empty($_POST['terms'])) {
        $terms = explode(',', $_POST['terms']);
        $args['tax_query'] = array(
            array(
                'taxonomy' => $taxonomy,
                'field' => 'id',
                'terms' => $terms
            )
        );
    }

    $search_query = new WP_Query($args);

    //build the results
    if (!empty($search_query->posts)) {
        foreach ($search_query->posts as $post) {

            $the_price = '';
            $post_id = $post->ID;
            
            #get product / deal price
            if($post->post_type == 'product'){
                $the_price = get_post_meta( $post_id, '_price', true);  
                if ( '' != $the_price ) {
                    if(rehub_option('ce_custom_currency')){
                        $currency_code = rehub_option('ce_custom_currency');
                        $woocurrency = get_woocommerce_currency(); 
                        if($currency_code != $woocurrency && rh_check_plugin_active('content-egg/content-egg.php')){
                            $currency_rate = \ContentEgg\application\helpers\CurrencyHelper::getCurrencyRate($woocurrency, $currency_code);
                            if (!$currency_rate) $currency_rate = 1;
                            $the_price = \ContentEgg\application\helpers\TemplateHelper::formatPriceCurrency($the_price*$currency_rate, $currency_code, '<span class="woocommerce-Price-currencySymbol">', '</span>');
                        }
                        else{
                            $the_price = strip_tags( wc_price( $the_price ) );
                        }                                               
                    }else{
                        $the_price = strip_tags( wc_price( $the_price ) );
                    }
                }                
                $terms = get_the_terms($post_id, 'product_visibility' );
                if ( ! is_wp_error($terms) && $terms ){
                    $termnames = array();
                    foreach ($terms as $term) {
                        $termnames[] = $term->name;
                    }
                    if (in_array('exclude-from-search', $termnames)){
                        continue;
                    }
                }
            }else{
                $offer_price = get_post_meta( $post_id, 'rehub_offer_product_price', true );
                if($offer_price){
                   $the_price = $offer_price; 
                }
            }
            
            if( has_post_thumbnail($post_id) ){
                $image_id = get_post_thumbnail_id($post_id);  
                $image_url = wp_get_attachment_image_src($image_id, 'minithumb');  
                $image_url = $image_url[0];
                $image_url = apply_filters('rh_thumb_url', $image_url );
            }else {
                $image_url = get_template_directory_uri() . '/images/default/noimage_100_70.png' ;
                $image_url = apply_filters('rh_no_thumb_url', $image_url, $post_id);
            } 

            $compare_active = ( in_array( $post_id, $compare_ids_arr ) ) ? ' comparing' : ' not-incompare';

            // HTML
            $buffer .= '<div class="re-search-result-div wpsm-button-new-compare addcompare-id-'. $post_id .''. $compare_active .'" data-addcompare-id="'. $post_id .'">';
            $buffer .= '<div class="re-search-result-thumb"><img src="'.$image_url.'" alt="'.get_the_title( $post_id ).'"/></div>';
            
            $buffer .= '<div class="re-search-result-info"><h3 class="re-search-result-title">'. rh_expired_or_not($post_id, "span") .''. get_the_title( $post_id ) .'</h3>';
            
            if( empty( $post->post_excerpt ) ) {
                $buffer .= '<div class="re-search-result-excerpt mb5 lineheight15">'.rehub_truncate("maxchar=150&text=$post->post_content&echo=false").'</div>';
            } else {
                $buffer .= '<div class="re-search-result-excerpt mb5 lineheight15">'.rehub_truncate("maxchar=150&text=$post->post_excerpt&echo=false").'</div>'; 
            }
            
            if( '' != $the_price ) {
                $buffer .= '<span class="re-search-result-price greencolor">'.$the_price.'</span>';               
            }else{
                $buffer .= '<span class="re-search-result-meta">'.get_the_time(get_option( 'date_format' ), $post_id).'</span>';
            }
            
            $buffer .= '</div></div>';
        }
    }

    $button = '<span class="blockstyle clearbox cursorpointer font130 font80 medium pb10 pt10 re-compare-destin rehub-main-color-bg whitecolor" data-compareurl="">'. esc_html__('Add to Comparison', 'rehub-theme') .' <i class="rhicon rhi-chevron-circle-right ml5 mr5"></i></span>';

    if (count($search_query->posts) == 0) {
        $buffer = '<div class="re-aj-search-wrap-results no-result">'. esc_html__('No results', 'rehub-theme') .'</div>';
    } else {
        $buffer = '<div class="re-aj-search-wrap-results">'. $buffer .''. $button .'</div>';
    }

    //prepare array for ajax
    $bufferArray = array(
        'compare_html' => $buffer,
    );

    //Return the String
    die(json_encode($bufferArray));
}
add_action( 'wp_ajax_nopriv_add_to_compare_search', 'add_to_compare_search' );
add_action( 'wp_ajax_add_to_compare_search', 'add_to_compare_search' );
}