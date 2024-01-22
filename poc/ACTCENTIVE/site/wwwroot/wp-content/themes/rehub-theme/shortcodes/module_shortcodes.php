<?php

//////////////////////////////////////////////////////////////////
// Function for extract args from VC filter
//////////////////////////////////////////////////////////////////

if( !class_exists('WPSM_Postfilters') ) {
class WPSM_Postfilters{
	public $filter_args = array(
		'data_source'=>'cat',
		'cat'=>'',
		'cat_name'=>'',
		'tag'=>'',
		'cat_exclude'=>'',
		'tag_exclude'=>'',
		'ids'=>'',
		'orderby'=>'',
		'order '=> 'DESC',
		'meta_key'=>'',
		'show'=>12,
		'offset'=>'',
		'show_date' => '',		
		'post_type'=>'',
		'tax_name'=>'',
		'tax_slug'=>'',
		'tax_slug_exclude'=>'',
		'post_formats'=>'',
		'badge_label '=>'1',
		'enable_pagination'=>'',
		'price_range' => '',		
		'show_coupons_only'=>'',
		'user_id' => '',
		'searchtitle' => '',
	);
	function __construct( $filter_args = array() ){
		$this->set_opt( $filter_args );
		return $this;
	}
	function set_opt( $filter_args = array() ){
		$this->filter_args = (object) array_merge( $this->filter_args, (array) $filter_args );
	}	
	public function extract_filters(){

		$filter_args = & $this->filter_args;

	    if ($filter_args->data_source == 'ids' && $filter_args->ids !='') {
	    	$ids = array_map( 'trim', explode( ",", $filter_args->ids ) );
	        $args = array(
	            'post__in' => $ids,
	            'numberposts' => '-1',
	            'orderby' => 'post__in', 
	            'ignore_sticky_posts' => 1,
	            'post_type'=> 'any'            
	        );
	    }
	    elseif ($filter_args->data_source == 'badge') {
	        $args = array(
	            'post_type' => 'any',
	            'posts_per_page'   => (int)$filter_args->show, 
	            'order' => $filter_args->order,                  
	        );
	        if ($filter_args->offset != '') {$args['offset'] = (int)$filter_args->offset;}
	        if (($filter_args->orderby == 'meta_value' || $filter_args->orderby == 'meta_value_num') && $filter_args->meta_key !='') {$args['meta_key'] = $filter_args->meta_key;}
	        $args['meta_query'] = array(
	    		array(
					'key'     => 'is_editor_choice',
					'value'   => $filter_args->badge_label,
					'compare' => '=',        			
	    		)
	        );
	        if ($filter_args->post_formats != 'all' && $filter_args->post_formats != '') {
	        	$args['meta_query'][] = array(
						'key'     => 'rehub_framework_post_type',
						'value'   => $filter_args->post_formats,
						'compare' => '=',         		
	        		);
	        }
	    }      
	    elseif ($filter_args->data_source == 'cpt') {
	        $args = array(
	            'post_type' => $filter_args->post_type,
	            'posts_per_page'   => (int)$filter_args->show, 
	            'order' => $filter_args->order,                  
	        );
	        if ($filter_args->offset != '') {$args['offset'] = (int)$filter_args->offset;}
	        if ($filter_args->post_formats != 'all' && $filter_args->post_formats != '') {$args['meta_key'] = 'rehub_framework_post_type'; $args['meta_value'] = $filter_args->post_formats;} 
			if($filter_args->post_type == 'product') {
				if ($filter_args->cat !='') {
					$cat = array_map( 'trim', explode( ",", $filter_args->cat ) );
					$args['tax_query'][] = array(
						'relation' => 'AND',
						array(
							'taxonomy' => 'product_cat',
							'field'    => 'term_id',
							'terms'    => $cat,
						)
					);
				}
				if ($filter_args->cat_exclude !='') {
					$cat_exclude = array_map( 'trim', explode( ",", $filter_args->cat_exclude ) );
					$args['tax_query'][] = array(
						'relation' => 'AND',
						array(
							'taxonomy' => 'product_cat',
							'field'    => 'term_id',
							'terms'    => $cat_exclude,
							'operator' => 'NOT IN'
						)
					);
				}
				if ($filter_args->tag !='') {
					$tag = array_map( 'trim', explode( ",", $filter_args->tag ) );
					$args['tax_query'][] = array(
						'relation' => 'AND',
						array(
							'taxonomy' => 'product_tag',
							'field'    => 'term_id',
							'terms'    => $tag,
						)
					);
				}         
				if ($filter_args->tag_exclude !='') {
					$tag_exclude = array_map( 'trim', explode( ",", $filter_args->tag_exclude ) );
					$args['tax_query'][] = array(
						'relation' => 'AND',
						array(
							'taxonomy' => 'product_tag',
							'field'    => 'term_id',
							'terms'    => $tag_exclude,
							'operator' => 'NOT IN'
						)
					);
				}         
				if (isset($filter_args->type) && $filter_args->type !='') {
	
					if($filter_args->type =='featured') {
						$args['tax_query'][] = array(
							'relation' => 'AND',
							array(
								'taxonomy' => 'product_visibility',
								'field'    => 'name',
								'terms'    => 'featured',
								'operator' => 'IN',
							)
						);
					}
					elseif($filter_args->type =='sale') {
						$product_ids_on_sale = wc_get_product_ids_on_sale();
						$args['post__in'] = array_merge( array( 0 ), $product_ids_on_sale );
						$args['no_found_rows'] = 1;
					}
					elseif($filter_args->type =='recentviews') {
						$viewed_products = ! empty( $_COOKIE['woocommerce_recently_viewed'] ) ? (array) explode( '|', $_COOKIE['woocommerce_recently_viewed'] ) : array();
						$viewed_products = array_reverse( array_filter( array_map( 'absint', $viewed_products ) ) );
						$args['post__in'] = $viewed_products;
						$args['no_found_rows'] = 1;
					}
					elseif($filter_args->type =='saled') {
						$args['meta_query'][] = array(
							'key'     => 'total_sales',
							'value'   => '0',
							'compare' => '!=',
						);
					 }
				}
			}       
	    } 
	    elseif ($filter_args->data_source == 'auto') {
	        $args = array(
	            'posts_per_page'   => (int)$filter_args->show, 
	            'order' => $filter_args->order,                  
	        );	
	        if($filter_args->enable_pagination == ''){
	        	$filter_args->enable_pagination = '1';
	        }    	
			if(is_category()){
				$args['post_type'] = 'post';
				$catid = get_query_var( 'cat' );
				$args['cat'] = $catid;
			}elseif (is_tag()) {
				$args['post_type'] = 'post';
				$tagid = get_queried_object_id();
	            $args['tax_query'] = array (
	                array(
	                    'taxonomy' => 'post_tag',
	                    'field'    => 'id',
	                    'terms'    => array($tagid),
	                )
	            );				
			} 
			elseif (is_tax('blog_category')) {
				$args['post_type'] = 'blog';
				$tagid = get_queried_object_id();
	            $args['tax_query'] = array (
	                array(
	                    'taxonomy' => 'blog_category',
	                    'field'    => 'id',
	                    'terms'    => array($tagid),
	                )
	            );				
			}
			elseif (is_tax('blog_tag')) {
				$args['post_type'] = 'blog';
				$tagid = get_queried_object_id();
	            $args['tax_query'] = array (
	                array(
	                    'taxonomy' => 'blog_tag',
	                    'field'    => 'id',
	                    'terms'    => array($tagid),
	                )
	            );				
			}
			elseif (is_tax('dealstore')) {
				$args['post_type'] = 'post';
				$tagid = get_queried_object_id();
	            $args['tax_query'] = array (
	                array(
	                    'taxonomy' => 'dealstore',
	                    'field'    => 'id',
	                    'terms'    => array($tagid),
	                )
	            );				
			}
			elseif (is_tax('store')) {
				$args['post_type'] = 'product';
				$tagid = get_queried_object_id();
	            $args['tax_query'] = array (
	                array(
	                    'taxonomy' => 'store',
	                    'field'    => 'id',
	                    'terms'    => array($tagid),
	                )
	            );				
			}
			elseif (is_tax('product_cat')) {
				$args['post_type'] = 'product';
				$tagid = get_queried_object_id();
	            $args['tax_query'] = array (
	                array(
	                    'taxonomy' => 'product_cat',
	                    'field'    => 'id',
	                    'terms'    => array($tagid),
	                )
	            );				
			}
			elseif (is_tax('product_tag')) {
				$args['post_type'] = 'product';
				$tagid = get_queried_object_id();
	            $args['tax_query'] = array (
	                array(
	                    'taxonomy' => 'product_tag',
	                    'field'    => 'id',
	                    'terms'    => array($tagid),
	                )
	            );				
			}										
			elseif (is_search()) {
				$args['post_type'] = 'any';
				$searchid = get_search_query();
	            $args['s'] = esc_attr($searchid);				
			}
			elseif (is_author()) {
				$args['post_type'] = array('post', 'blog', 'product');
				$curauth = ( get_query_var( 'author_name' ) ) ? get_user_by( 'slug', get_query_var( 'author_name' ) ) : get_userdata( get_query_var( 'author' ) );
				$author_ID = $curauth->ID;
	            $args['author'] = (int)$author_ID;				
			}
			if (class_exists('WooCommerce')){
				if(is_shop() || is_product_taxonomy()) {
					$args['post_type'] = 'product';
					if ( isset( $_GET['rating_filter'] ) && $args['post_type'] == 'product' ) {
						$visibility_terms = array();
						$rating_filter = array_filter( array_map( 'absint', explode( ',', wp_unslash( $_GET['rating_filter'] ) ) ) );
						$product_visibility_terms = wc_get_product_visibility_term_ids();
						foreach( $rating_filter as $rating ) {
							$visibility_terms[] = $product_visibility_terms['rated-'. $rating];
						}
						$args['tax_query'][] = array(
							'relation' => 'AND',
							array(
								'taxonomy' => 'product_visibility',
								'field' => 'term_taxonomy_id',
								'terms' => $visibility_terms,
							)
						);
					}
					$_chosen_attributes = WC_Query::get_layered_nav_chosen_attributes();
					if(!empty($_chosen_attributes)){
						foreach($_chosen_attributes as $_chosen_attribute_tax => $_chosen_attribute ){
							$filter_name = 'filter_' . wc_attribute_taxonomy_slug( $_chosen_attribute_tax );
							if( isset($_GET[$filter_name]) && $args['post_type'] == 'product' ){
								$args['tax_query'][] = array(
									'taxonomy' => $_chosen_attribute_tax,
									'field' => 'slug',
									'terms' => $_chosen_attribute['terms']
								);
							}
						}
					}
					$args['tax_query'][] = array(
						'relation' => 'AND',
						array(
							'taxonomy' => 'product_visibility',
							'field'    => 'name',
							'terms'    => 'exclude-from-catalog',
							'operator' => 'NOT IN',
						)
					);					
				}
			}																	     
	    } 	 
	    else {
	        $args = array(
	            'post_type' => 'post',
	            'posts_per_page'   => (int)$filter_args->show, 
	            'order' => $filter_args->order,                  
	        );	        
	        if ($filter_args->offset != '') {$args['offset'] = (int)$filter_args->offset;}
	        if ($filter_args->cat !='') {$args['cat'] = $filter_args->cat;}
	        if ($filter_args->cat_name !='') {$args['category_name'] = $filter_args->cat_name;}
	        if ($filter_args->tag !='') {$args['tag__in'] = array_map( 'trim', explode(",", $filter_args->tag ));}
	        if ($filter_args->cat_exclude !='') {$args['category__not_in'] = array_map( 'trim', explode(",", $filter_args->cat_exclude ));}
	        if ($filter_args->tag_exclude !='') {$args['tag__not_in'] = explode(',', $filter_args->tag_exclude);}
	        if ($filter_args->post_formats != 'all' && $filter_args->post_formats != '') {$args['meta_key'] = 'rehub_framework_post_type'; $args['meta_value'] = $filter_args->post_formats;}
	    }    
        if (!empty ($filter_args->searchtitle) ) {
            $args['s'] = urlencode($filter_args->searchtitle);
        	if($filter_args->searchtitle == 'CURRENTPAGE'){
        		$currenttitle = get_the_title();
        		$args['s'] = urlencode($currenttitle);
        	}            
        }
        if (!empty ($filter_args->tax_name) && !empty ($filter_args->tax_slug)) {
        	$tax_slugs = array_map( 'trim', explode( ",", $filter_args->tax_slug ) );
			$args['tax_query'][] = array(
				'relation' => 'AND',
				array(
					'taxonomy' => $filter_args->tax_name,
					'field'    => 'slug',
					'terms'    => $tax_slugs,
				)
			);
        }
        if (!empty ($filter_args->tax_name) && !empty ($filter_args->tax_slug_exclude)) {
        	$tax_slugs_exclude = array_map( 'trim', explode( ",", $filter_args->tax_slug_exclude) );
			$args['tax_query'][] = array(
				'relation' => 'AND',
				array(
					'taxonomy' => $filter_args->tax_name,
					'field'    => 'slug',
                    'terms'    => $tax_slugs_exclude,
                    'operator' => 'NOT IN',
				)
			);
        }	    
	    if (!empty($filter_args->user_id)) {  
	        if(is_numeric($filter_args->user_id)) {
		        $args['author'] = $filter_args->user_id;	        	
	        }  
	    }
	    if (($filter_args->orderby == 'meta_value' || $filter_args->orderby == 'meta_value_num') && $filter_args->meta_key !='') {
	    	$args['meta_key'] = $filter_args->meta_key;
	    }
	    if ($filter_args->orderby != ''){
	    	$args['orderby'] = $filter_args->orderby;
			if($filter_args->orderby == 'random'){
				$args['orderby'] = 'rand';
            }
	    }	
	    if ($filter_args->orderby == 'view' || $filter_args->orderby == 'thumb' || $filter_args->orderby == 'discount' || $filter_args->orderby == 'price'){
	    	$args['orderby'] = 'meta_value_num';
	    }
	    if ($filter_args->orderby == 'expirationdate'){
	    	$args['orderby'] = 'meta_value';
	    	$args['meta_type'] = 'DATE';
	    	$args['meta_key'] = 'rehub_offer_coupon_date';
	    }	    	    
	    if ($filter_args->orderby == 'view'){
	    	$args['meta_key'] = 'rehub_views';
	    }
	    if ($filter_args->orderby == 'thumb'){
	    	$args['meta_key'] = 'post_hot_count';
	    }
	    if ($filter_args->orderby == 'wish'){
	    	$args['meta_key'] = 'post_wish_count';
	    }	    
	    if ($filter_args->orderby == 'discount'){
	    	$args['meta_key'] = '_rehub_offer_discount';
	    }
	    if ($filter_args->orderby == 'price'){
	    	if ($filter_args->post_type == 'product' || $args['post_type'] == 'product') {
	    		$args['meta_key'] = '_price';
	    	}else{
	    		$args['meta_key'] = 'rehub_main_product_price';	    		
	    	}

	    }
	    if ($filter_args->orderby == 'hot'){
            $rehub_max_temp = (rehub_option('hot_max')) ? rehub_option('hot_max') : 50;
            $args['meta_query'] = array (
                array (
                    'key'     => 'post_hot_count',
                    'value'   => $rehub_max_temp,
                    'type'    => 'numeric',
                    'compare' => '>=',
                    )
                );
            $args['orderby'] = 'date';
        } 

        if ($filter_args->orderby == 'date' && $filter_args->data_source == 'ids'){
			$args['orderby'] = 'post__in';
        }	    

	    if ($filter_args->show_coupons_only == '1') { 
	    	$args['meta_query']['relation'] = 'AND';    
	        $args['meta_query'][] = array(
	            'key'     => 'rehub_offer_product_price_old',
	            'value' => '',
	            'compare' => '!=',
	        );
			$args['tax_query'][] = array(
				'relation' => 'AND',
				array(
					'taxonomy' => 'offerexpiration',
					'field'    => 'name',
					'terms'    => 'yes',
					'operator' => 'NOT IN',
				)
			);		        
	    }	     
	    if ($filter_args->show_coupons_only == '2') { 
	    	$args['meta_query']['relation'] = 'AND';    
	        $args['meta_query'][] = array(
	            'key'     => 'rehub_offer_product_coupon',
	            'value' => '',
	            'compare' => '!=',
	        );
			$args['tax_query'][] = array(
				'relation' => 'AND',
				array(
					'taxonomy' => 'offerexpiration',
					'field'    => 'name',
					'terms'    => 'yes',
					'operator' => 'NOT IN',
				)
			);	        
	    } 
	    if ($filter_args->show_coupons_only == '3') {     
			$args['tax_query'][] = array(
				'relation' => 'AND',
				array(
					'taxonomy' => 'offerexpiration',
					'field'    => 'name',
					'terms'    => 'yes',
					'operator' => 'NOT IN',
				)
			);
	    } 	
	    if ($filter_args->show_coupons_only == '6') {     
		    $args['meta_query'][] = array(
		    	array(
		       		'key' => 'rehub_review_overall_score',
		       		'compare' => 'EXISTS',
		    	),	    	
			);
	    } 	    
	    if ($filter_args->show_coupons_only == '4') {     
			$args['tax_query'][] = array(
				'relation' => 'AND',
				array(
					'taxonomy' => 'offerexpiration',
					'field'    => 'name',
					'terms'    => 'yes',
					'operator' => 'IN',
				)
			);
	    } 
	    if ($filter_args->show_coupons_only == '5') { 
	    	$args['meta_query']['relation'] = 'AND';    
	        $args['meta_query'][] = array(
	            'key'     => 'rehub_offer_product_coupon',
	            'compare' => 'NOT EXISTS',
	        );
			$args['tax_query'][] = array(
				'relation' => 'AND',
				array(
					'taxonomy' => 'offerexpiration',
					'field'    => 'name',
					'terms'    => 'yes',
					'operator' => 'NOT IN',
				)
			);	        
	    }	

	    if ($filter_args->price_range !='') {
	    	if (!empty($args['meta_query'])){
	    		$args['meta_query']['relation'] = 'AND';
	    	}    
	    	$price_range_array = array_map( 'trim', explode( "-", $filter_args->price_range ) );
	    	if ($filter_args->post_type == 'product' || $args['post_type'] == 'product') {
	    		$key = '_price';
	    	}
	    	else{
	    		$key = 'rehub_main_product_price';
	    	}

	        $args['meta_query'][] = array(
	            'key'     => $key,
	            'value'   => $price_range_array,
	            'type'    => 'numeric',
	            'compare' => 'BETWEEN',
	        );		        
	    }	

	    if (isset($_GET['min_price']) && isset($_GET['max_price'])) {
	    	if (!empty($args['meta_query'])){
	    		$args['meta_query']['relation'] = 'AND';
	    	} 
	    	$price_range_array = array(floatval($_GET['min_price']), floatval($_GET['max_price']));   
	    	if ($filter_args->post_type == 'product' || $args['post_type'] == 'product') {
	    		$key = '_price';
	    	}
	    	else{
	    		$key = 'rehub_main_product_price';
	    	}

	        $args['meta_query'][] = array(
	            'key'     => $key,
	            'value'   => $price_range_array,
	            'type'    => 'numeric',
	            'compare' => 'BETWEEN',
	        );		        
	    }	            

	    if ($filter_args->show_date == 'day') {     
	        $args['date_query'][] = array(
				'after'  => '1 day ago',
	        );
	    }
	    if ($filter_args->show_date == 'week') {    
	        $args['date_query'][] = array(
				'after'  => '7 days ago',
	        );
	    }	
	    if ($filter_args->show_date == 'month') {     
	        $args['date_query'][] = array(
				'after'  => '1 month ago',
	        );
	    }	
	    if ($filter_args->show_date == 'year') {     
	        $args['date_query'][] = array(
				'after'  => '1 year ago',
	        );
	    }	            

		if ( get_query_var('paged') ) { $paged = get_query_var('paged'); } else if ( get_query_var('page') ) {$paged = get_query_var('page'); } else {$paged = 1; }	    
		if ($filter_args->enable_pagination != '' && $filter_args->enable_pagination != '0') {
			$args['paged'] = $paged;
		}
		else {
			$args['no_found_rows'] = 1;
		}
		if(!is_archive()){
			$args['ignore_sticky_posts'] = 1;
		}

		return $args;		
	}

	public static function re_show_brand_tax($type='list'){  
		$term_brand_image = $brand_link = $brand_url = $brandtermname = '';
        if ($type == 'list'){
	    	$term_list = get_the_term_list( get_the_ID(), 'dealstore', '<span class="store_post_meta_item">', ', ', '</span>' );
	    	if(!is_wp_error($term_list)){
	    		echo '<span class="tag_post_store_meta">'.$term_list.'</span>';
	    	}	        	
        }  
        if ($type=='logo'){
	        $brand_url = get_post_meta( get_the_ID(), 'rehub_offer_logo_url', true );
	        if (!empty ($brand_url)) {
	            $term_brand_image = esc_url($brand_url);
	        }  
	        else {
	        	$term_ids =  wp_get_post_terms(get_the_ID(), 'dealstore', array("fields" => "ids")); 
	        	if (!empty($term_ids) && ! is_wp_error($term_ids)) {
	        		$term_id = $term_ids[0];
	        		$brand_url = get_term_meta( $term_id, 'brandimage', true );
	        		$brand_link = get_term_link( $term_id );
	        		$brandterm = get_term( $term_id);
	        		$brandtermname = $brandterm->name;
	        	}
		        if ($brand_url) {
		            $term_brand_image = esc_url($brand_url);
		        }  
	        } 
	        if ($brand_link){echo '<a href="' . esc_url( $brand_link ) . '">';}
	        if ($term_brand_image) : 
		        WPSM_image_resizer::show_static_resized_image(array('lazy'=> true, 'src'=> $term_brand_image, 'crop'=> false, 'height'=> 80, 'title'=> $brandtermname));
			endif;
			if ($brand_link){echo '</a>';}
        }                
	}		

}
}

//////////////////////////////////////////////////////////////////
// Rehub Woo helper class
//////////////////////////////////////////////////////////////////
if( !class_exists('WPSM_Woohelper') ) {
class WPSM_Woohelper{
	public $filter_args = array(
		'data_source' => 'cat',
		'cat' => '',
		'tag' => '',
		'ids' => '',	
		'orderby' => '',
		'order' => 'DESC',
		'meta_key'=>'',	
		'show' => '',
		'offset' => '',
		'show_date' => '',			
		'show_coupons_only' => '',
		'user_id' => '',	
		'type' => '',	
		'tax_name'=>'',
		'tax_slug'=>'',	
		'tax_slug_exclude'=>'',	
		'enable_pagination' => '',	
		'price_range' => '',			
	);
	function __construct( $filter_args = array() ){
		$this->set_opt( $filter_args );
		return $this;
	}
	function set_opt( $filter_args = array() ){
		$this->filter_args = (object) array_merge( $this->filter_args, (array) $filter_args );
	}	

	public function extract_filters(){
		$filter_args = & $this->filter_args;
	    if ($filter_args->data_source == 'ids' && $filter_args->ids !='') {
	        $ids = array_map( 'trim', explode( ",", $filter_args->ids ) );
	        $args = array(
	            'post__in' => $ids,
	            'orderby' => 'post__in', 
	            'post_type' => 'product', 
	            'posts_per_page'   => $filter_args->show,          
	        );
	    }
		elseif ($filter_args->data_source == 'auto') {	
	        $args = array(
	            'post_type' => 'product',
	            'posts_per_page'   => $filter_args->show, 
	            'orderby' => $filter_args->orderby,
	            'order' => $filter_args->order,                  
	        );
			if($filter_args->enable_pagination == ''){
				$filter_args->enable_pagination = '1';
			} 
			if (is_tax('product_cat')) {
				$tagid = get_queried_object_id();
				$args['tax_query'] = array (
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'id',
						'terms'    => array($tagid),
					)
				);				
			}		           	
			elseif (is_tax('store')) {
				$tagid = get_queried_object_id();
				$args['tax_query'] = array (
					array(
						'taxonomy' => 'store',
						'field'    => 'id',
						'terms'    => array($tagid),
					)
				);				
			}
			elseif (is_tax('product_tag')) {
				$tagid = get_queried_object_id();
				$args['tax_query'] = array (
					array(
						'taxonomy' => 'product_tag',
						'field'    => 'id',
						'terms'    => array($tagid),
					)
				);				
			}										
			elseif (is_search()) {
				$searchid = get_search_query();
				$args['s'] = esc_attr($searchid);				
			}
			elseif (is_author()) {
				$curauth = ( get_query_var( 'author_name' ) ) ? get_user_by( 'slug', get_query_var( 'author_name' ) ) : get_userdata( get_query_var( 'author' ) );
				$author_ID = $curauth->ID;
				$args['author'] = (int)$author_ID;				
			}
			if(is_shop() || is_product_taxonomy()) {
				if ( isset( $_GET['rating_filter'] ) ) {
					$visibility_terms = array();
					$rating_filter = array_filter( array_map( 'absint', explode( ',', wp_unslash( $_GET['rating_filter'] ) ) ) );
					$product_visibility_terms = wc_get_product_visibility_term_ids();
					foreach( $rating_filter as $rating ) {
						$visibility_terms[] = $product_visibility_terms['rated-'. $rating];
					}
					$args['tax_query'][] = array(
						'relation' => 'AND',
						array(
							'taxonomy' => 'product_visibility',
							'field' => 'term_taxonomy_id',
							'terms' => $visibility_terms,
						)
					);
				}
				$_chosen_attributes = WC_Query::get_layered_nav_chosen_attributes();
				if(!empty($_chosen_attributes)){
					foreach($_chosen_attributes as $_chosen_attribute_tax => $_chosen_attribute ){
						$filter_name = 'filter_' . wc_attribute_taxonomy_slug( $_chosen_attribute_tax );
						if( isset($_GET[$filter_name]) ){
							$args['tax_query'][] = array(
								'taxonomy' => $_chosen_attribute_tax,
								'field' => 'slug',
								'terms' => $_chosen_attribute['terms']
							);
						}
					}
				}
			}																		     
		} 
	    else {
	        $args = array(
	            'post_type' => 'product',
	            'posts_per_page'   => $filter_args->show, 
	            'orderby' => $filter_args->orderby,
	            'order' => $filter_args->order,                  
	        );
	        if ($filter_args->cat !='') {
				if(!is_array($filter_args->cat)){
					$cat = array_map( 'trim', explode( ",", $filter_args->cat ) );
				}else{
					$cat = $filter_args->cat;
				}

				$args['tax_query'][] = array(
					'relation' => 'AND',
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'term_id',
						'terms'    => $cat,
					)
				);
	        }
	        if ($filter_args->tag !='') {
				if(!is_array($filter_args->tag)){
					$tag = array_map( 'trim', explode( ",", $filter_args->tag ) );
				}else{
					$tag = $filter_args->tag;
				}
				$args['tax_query'][] = array(
					'relation' => 'AND',
					array(
						'taxonomy' => 'product_tag',
						'field'    => 'term_id',
						'terms'    => $tag,
					)
				);
	        }         
	        if ($filter_args->type !='') {

	            if($filter_args->type =='featured') {
					$args['tax_query'][] = array(
						'relation' => 'AND',
						array(
							'taxonomy' => 'product_visibility',
							'field'    => 'name',
							'terms'    => 'featured',
							'operator' => 'IN',
						)
					);
	            }
	            elseif($filter_args->type =='sale') {
	                $product_ids_on_sale = wc_get_product_ids_on_sale();
	                $args['post__in'] = array_merge( array( 0 ), $product_ids_on_sale );
	                $args['no_found_rows'] = 1;
	            }
	            elseif($filter_args->type =='recentviews') {
					$viewed_products = ! empty( $_COOKIE['woocommerce_recently_viewed'] ) ? (array) explode( '|', $_COOKIE['woocommerce_recently_viewed'] ) : array();
					$viewed_products = array_reverse( array_filter( array_map( 'absint', $viewed_products ) ) );
	                $args['post__in'] = $viewed_products;
	                $args['no_found_rows'] = 1;
	            }
	            elseif($filter_args->type =='saled') {
					$args['meta_query'][] = array(
						'key'     => 'total_sales',
						'value'   => '0',
						'compare' => '!=',
					);
				 }
	        }	        
	        if (!empty ($filter_args->tax_name) && !empty ($filter_args->tax_slug)) {
	            $args['tax_query'][] = array (
	            	'relation' => 'AND',
	                array(
	                    'taxonomy' => $filter_args->tax_name,
	                    'field'    => 'slug',
	                    'terms'    => array($filter_args->tax_slug),
	                )
	            );
	        }		        
	        if (!empty ($filter_args->tax_name) && !empty ($filter_args->tax_slug_exclude)) {
	            $args['tax_query'][] = array (
	            	'relation' => 'AND',
	                array(
	                    'taxonomy' => $filter_args->tax_name,
	                    'field'    => 'slug',
	                    'terms'    => array($filter_args->tax_slug_exclude),
	                    'operator' => 'NOT IN',
	                ),
	            );
	        } 	        	   
	        if ($filter_args->offset != '') {$args['offset'] = (int)$filter_args->offset;}   	        
	    }
		if($filter_args->orderby == 'random'){
			$args['orderby'] = 'rand';
		}
		if (($filter_args->orderby == 'meta_value' || $filter_args->orderby == 'meta_value_num') && $filter_args->meta_key !='') {$args['meta_key'] = $filter_args->meta_key;}
		if ($filter_args->orderby == 'price'){
			$args['meta_key'] = '_price';
			$args['orderby'] = 'meta_value_num';
		}  
		if ($filter_args->orderby == 'sales'){
			$args['meta_key'] = 'total_sales';
			$args['orderby'] = 'meta_value_num';
		}

	    if ($filter_args->show_coupons_only == '1') { 
	    	$args['meta_query']['relation'] = 'AND';    
	        $args['meta_query'][] = array(
	            'key'     => '_sale_price',
	            'value' => '',
	            'compare' => '!=',
	        );
	        $args['meta_query'][] = array(
	            'key'     => 're_post_expired',
	            'value'   => '1',
	            'compare' => '!=',
	        );		        
	    }	    	  
	    if ($filter_args->show_coupons_only == '4') {     
	    	$args['meta_query']['relation'] = 'AND';    
	        $args['meta_query'][] = array(
	            'key'     => 'rehub_woo_coupon_code',
	            'value' => '',
	            'compare' => '!=',
	        );
	        $args['meta_query'][] = array(
	            'key'     => 're_post_expired',
	            'value'   => '1',
	            'compare' => '!=',
	        );	        
	    } 	      
	    if ($filter_args->show_coupons_only == '2') {     
		    $args['meta_query'][] = array(
		    	array(
		       		'key' => 're_post_expired',
		       		'value' => '1',
		       		'compare' => '!=',
		    	),	    	
			);
	    } 	
	    if ($filter_args->show_coupons_only == '3') {     
	        $args['meta_query'][] = array(
	            'key'     => 're_post_expired',
	            'value'   => '1',
	            'compare' => '=',
	        );
	    }	
	    if ($filter_args->show_coupons_only == '5') { 
	    	$args['meta_query']['relation'] = 'AND';    
	        $args['meta_query'][] = array(
	            'key'     => 'rehub_woo_coupon_code',
	            'compare' => 'NOT EXISTS',
	        );
	        $args['meta_query'][] = array(
	            'key'     => 're_post_expired',
	            'value'   => '1',
	            'compare' => '!=',
	        );		        
	    }	    
	    if ($filter_args->show_date == 'day') {     
	        $args['date_query'][] = array(
				'after'  => '1 day ago',
	        );
	    }
	    if ($filter_args->show_date == 'week') {    
	        $args['date_query'][] = array(
				'after'  => '7 days ago',
	        );
	    }	
	    if ($filter_args->show_date == 'month') {     
	        $args['date_query'][] = array(
				'after'  => '1 month ago',
	        );
	    }	
	    if ($filter_args->show_date == 'year') {     
	        $args['date_query'][] = array(
				'after'  => '1 year ago',
	        );
	    }	
	    if (!empty($filter_args->user_id)) {  
	        if(is_numeric($filter_args->user_id)) {
		        $args['author'] = $filter_args->user_id;	        	
	        }  
	    }	 
	    if ($filter_args->price_range !='') {
	    	if (!empty($args['meta_query'])){
	    		$args['meta_query']['relation'] = 'AND';
	    	}    
	    	$price_range_array = array_map( 'trim', explode( "-", $filter_args->price_range ) );
	        $args['meta_query'][] = array(
	            'key'     => '_price',
	            'value'   => $price_range_array,
	            'type'    => 'numeric',
	            'compare' => 'BETWEEN',
	        );		        
	    }
	    if (isset($_GET['min_price']) && isset($_GET['max_price'])) {
	    	if (!empty($args['meta_query'])){
	    		$args['meta_query']['relation'] = 'AND';
	    	} 
	    	$price_range_array = array(floatval($_GET['min_price']), floatval($_GET['max_price']));   
	    	$key = '_price';

	        $args['meta_query'][] = array(
	            'key'     => $key,
	            'value'   => $price_range_array,
	            'type'    => 'numeric',
	            'compare' => 'BETWEEN',
	        );		        
	    }	    
		$args['tax_query'][] = array(
			'relation' => 'AND',
			array(
				'taxonomy' => 'product_visibility',
				'field'    => 'name',
				'terms'    => 'exclude-from-catalog',
				'operator' => 'NOT IN',
			)
		);	

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

		    	    	           

		if ( get_query_var('paged') ) { $paged = get_query_var('paged'); } else if ( get_query_var('page') ) {$paged = get_query_var('page'); } else {$paged = 1; }	    
		if ($filter_args->enable_pagination != '' && $filter_args->enable_pagination != '0') {
			$args['paged'] = $paged;
		}
		else {
			$args['no_found_rows'] = 1;
		}
		//$args['ignore_sticky_posts'] = 1;		
		
		return $args;		
	}

	public static function re_show_brand_tax($type='list', $height = '30'){   
        if ($type == 'list'){
	    	$term_list = get_the_term_list( get_the_ID(), 'store', '<span class="tag_woo_meta_item">', ', ', '</span>' );
	        if(!empty($term_list) && !is_wp_error($term_list)){
	        	echo '<span class="tag_woo_meta">'.$term_list.'</span>';
	        }	        	
        }  
        if ($type=='logo' || $type=='logoname' || $type=='logonamelink' || $type=='logonamenolink' || $type=='name' ){
        	$term_ids =  wp_get_post_terms(get_the_ID(), 'store', array("fields" => "ids")); 
        	if (!empty($term_ids) && ! is_wp_error($term_ids)) {
        		$term_id = $term_ids[0];
        		$brand_url = get_term_meta( $term_id, 'brandimage', true );
        		$brand_link = get_term_link( $term_id );
        	}
	        if (!empty ($brand_url)) {
	            $term_brand_image = esc_url($brand_url);
	        }   
	        if (!empty($brand_link) && ($type=='name' || $type=='logoname' || !empty($term_brand_image) || $type=='logonamelink')){echo '<a href="' . esc_url( $brand_link ) . '">';}
	        if ($type=='name' || $type=='logonamelink' || $type=='logonamenolink'){echo '<div class="rh-flex-center-align aff_tag">';}	        
	        if (!empty($term_brand_image) && $type !='name') :
		        $showbrandimg = new WPSM_image_resizer();
		        $showbrandimg->height = $height;
		        $showbrandimg->src = $term_brand_image;
		        $showbrandimg->show_resized_image();   
		    elseif(!empty($term_id) && $type=='logoname'):
		    	$tagobj = get_term_by('id', $term_id, 'store');
		    	$tagname = $tagobj->name;
		    	echo '<span class="brandname blockstyle rh-cartbox ml15">'.$tagname.'</span>';                                
			endif;
			if (!empty($term_id) && ($type=='name' || $type=='logonamelink' || $type=='logonamenolink')){
				$tagobj = get_term_by('id', $term_id, 'store');
		    	$tagname = $tagobj->name;
		    	echo '<span class="brandname ml5 mr5">'.$tagname.'</span>';
			}
			if ($type=='name' || $type=='logonamelink' || $type=='logonamenolink'){echo '</div>';}
			if (!empty($brand_link) && ($type=='name' || $type=='logoname' || !empty($term_brand_image) || $type=='logonamelink' )){echo '</a>';}
        }                
	}	

	public static function get_ratings_counts( $product ) {
		global $wpdb;
		
		$counts     = array();
		$raw_counts = $wpdb->get_results( $wpdb->prepare("
                SELECT meta_value, COUNT( * ) as meta_value_count FROM $wpdb->commentmeta
                LEFT JOIN $wpdb->comments ON $wpdb->commentmeta.comment_id = $wpdb->comments.comment_ID
                WHERE meta_key = 'rating'
                AND comment_post_ID = %d
                AND comment_approved = '1'
                AND meta_value > 0
                GROUP BY meta_value
            ", $product->get_id() ) );
		
		foreach ( $raw_counts as $count ) {
			$counts[ $count->meta_value ] = $count->meta_value_count;
		}
        
        return $counts;
	}	

}
}

function rehub_custom_taxonomy_dropdown( $taxdrop, $limit = '40', $class = '', $taxdroplabel = '', $containerid ='', $taxdropids = '' ) {
    $args = array(
        'taxonomy'=> $taxdrop,
        'number' => $limit,
        'hide_empty' => true,
        'parent'        => 0,
    );
    if($taxdropids){
        $taxdropids = array_map( 'trim', explode(",", $taxdropids ));
        $args['include'] = $taxdropids;
        $args['parent'] = '';
        $args['orderby'] = 'include';
    }
    $terms = get_terms($args );
    $class = ( $class ) ? $class : 're_tax_dropdown';
    $output = '';
    if ( $terms && !is_wp_error($terms) ) {
        $output .= '<ul class="'.$class.'">';
        if (empty($taxdroplabel)){$taxdroplabel = esc_html__('Choose category', 'rehub-theme');}
        $output .= '<li class="label"><span class="rh_tax_placeholder">'.$taxdroplabel.'</span><span class="rh_choosed_tax"></span></li>';
        $output .= '<li class="rh_drop_item"><span data-sorttype="" class="re_filtersort_btn" data-containerid="'.$containerid.'">'.esc_html__('All categories', 'rehub-theme').'</span></li>';
        foreach ( $terms as $term ) {
            $term_link = get_term_link( $term );
            if ( is_wp_error( $term_link ) ) {
                continue;
            }    
            if(!empty($containerid)){
                $sort_array=array();
                $sort_array['filtertype'] = 'tax';
                $sort_array['filtertaxkey'] = $taxdrop;
                $sort_array['filtertaxtermslug'] = $term->slug;
                $json_filteritem = json_encode($sort_array);
                $output .='<li class="rh_drop_item"><span data-sorttype=\''.$json_filteritem.'\' class="re_filtersort_btn" data-containerid="'.$containerid.'">';
                    $output .= $term->name;
                $output .= '</span></li>';
            }    
            else{
                $output .= '<li class="rh_drop_item"><span><a href="' . esc_url( $term_link ) . '">' . $term->name . '</a></span></li>';                
            }            
        }
        $output .= '</ul>';
    }
    return $output;
}

//////////////////////////////////////////////////////////////////
// RFILTER PANEL RENDER
//////////////////////////////////////////////////////////////////
if( !function_exists('rehub_vc_filterpanel_render') ) {
function rehub_vc_filterpanel_render( $filterpanel='', $containerid='', $taxdrop='', $taxdroplabel='', $taxdropids = '', $filterheading='' ) {
	if(!$filterpanel){
		return;
	}
	$filterpanel = (array) json_decode( urldecode( $filterpanel ), true );
	$output = '';
	if (!empty($filterpanel[0])){
		wp_enqueue_style('rhfilterpanel'); wp_enqueue_script('rhfilterpanel');wp_enqueue_script('rhajaxpagination');
		$tax_enabled_div = (!empty($taxdrop)) ? ' tax_enabled_drop' : '';
		$heading_enabled_div = (!empty($filterheading)) ? ' heading_enabled' : '';
		$output .= '<div class="rh-flex-center-align tabletblockdisplay re_filter_panel'.$tax_enabled_div.$heading_enabled_div.'">';
			if($filterheading){
				$output .= '<div class="rh-border-line below-border disablemobilemargin re_filter_heading fontbold font130 pt15 pb15 mr15 rtlml15 position-relative lineheight20">'.wp_kses_post($filterheading).'</div>';
			}
			$output .= '<ul class="re_filter_ul">';
			foreach ( $filterpanel as $k => $v ) {
				$output .= '<li class="inlinestyle">';
					$label = '';
					if (!empty($v['filtertitle'])) {
						$label = $v['filtertitle'];
						unset ($v['filtertitle']);		
					}
					$json_filteritem = json_encode($v);	
					$class = ($k==0) ? ' class="active re_filtersort_btn resort_'.$k.'"' : ' class="re_filtersort_btn resort_'.$k.'"';								
					$output .= '<span data-sorttype=\''.$json_filteritem.'\''.$class.' data-containerid="'.$containerid.'">';
						$output .= $label;						
					$output .= '</span>';					
				$output .= '</li>';				
			}
			$output .= '</ul>';

			if($taxdrop){
				$output .= '<div class="rh-flex-right-align">';
				$output .= rehub_custom_taxonomy_dropdown($taxdrop, '40', 're_tax_dropdown', $taxdroplabel, $containerid,$taxdropids);
				$output .='</div>';
			}

		$output .= '</div>';
	}
	echo ''.$output;

}
}

//////////////////////////////////////////////////////////////////
// WOOCOMMERCE FEATURED AREA
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_woofeatured_function') ) {
function wpsm_woofeatured_function( $atts, $content = null ) {
$build_args =shortcode_atts(array(
	'data_source' => 'cat',
	'cat' => '',
	'tag' => '',
	'ids' => '',	
	'orderby' => '',
	'order' => 'DESC',	
	'meta_key'=>'',
	'show' => 5,	
	'offset' => '',
	'show_date' => '',	
	'show_coupons_only' => '',	
	'user_id' => '',	
	'type' => '',	
	'tax_name'=>'',
	'tax_slug'=>'',	
	'tax_slug_exclude'=>'',	
	'enable_pagination' => '', //end woo filters
	'feat_type'=>'2',
	'dis_excerpt' =>'',
	'bottom_style' =>'',
	'custom_height'=>'',
	'price_range' => '',
), $atts, 'wpsm_woofeatured'); 
extract($build_args); 
$rand_id = 'woo_feat'.mt_rand();            
ob_start(); 
?>
<?php if( !is_paged()) : ?>
<?php if ($feat_type=='1') {wp_enqueue_script('flexslider');wp_enqueue_script('flexinit');wp_enqueue_style('flexslider');} ;?>
<?php if(!$show) $build_args['show'] = 5;
	$argsfilter = new WPSM_Woohelper($build_args);
	$args = $argsfilter->extract_filters();
	
	$products = new WP_Query($args);
?>
<div class="wpsm_featured_wrap flowhidden mb35 wpsm_featured_<?php echo esc_attr($feat_type)?>" id="<?php echo ''.$rand_id;?>">
<?php if($feat_type =='1') : //First type - featured full width slider?>
	<?php if($custom_height) :?>
    	<style scoped>
    		@media (min-width: 768px){
    			#<?php echo ''.$rand_id;?> .main_slider.full_width_slider.flexslider .slides .slide{height: <?php echo (int)$custom_height;?>px; line-height: <?php echo (int)$custom_height;?>px;} 
    			#<?php echo ''.$rand_id;?> .main_slider.full_width_slider.flexslider{height:<?php echo (int)$custom_height;?>px}
    		}        		
    	</style>
	<?php endif ;?>
	<div class="flexslider main_slider loading full_width_slider<?php if ($bottom_style =='1') :?> bottom_style_slider<?php endif ?>">
		<i class="rhicon rhi-spinner fa-pulse"></i>
		<ul class="slides">	
		<?php if($products->have_posts()): while($products->have_posts()): $products->the_post(); global $post; global $product; ?>
			<?php 
		  		$image_id = get_post_thumbnail_id(get_the_ID());  
		  		$image_url = wp_get_attachment_image_src($image_id,'full');
				$image_url = $image_url[0];
				if (function_exists('_nelioefi_url')){
					$image_nelio_url = get_post_meta( $post->ID, _nelioefi_url(), true );
					if (!empty($image_nelio_url)){
						$image_url = esc_url($image_nelio_url);
					}			
				}			
			?>	
			<li class="slide" style="background-image: url('<?php echo esc_url($image_url) ;?>');"> 
				<span class="pattern"></span>
				<a href="<?php the_permalink();?>" class="feat_overlay_link"></a>
		  		<div class="flex-overlay">
		    		<h2><a href="<?php the_permalink();?>"><?php the_title();?></a></h2>	    		
		    		<?php if ($dis_excerpt !='1' && $bottom_style !='1') :?><div class="hero-description"><p><?php kama_excerpt('maxchar=150'); ?></p></div><?php endif ;?>
		    		<?php if(rehub_option('disable_btn_offer_loop')!='1')  : ?>
		    		<div class="priced_block clearfix">
		    			<span class="rh_price_wrapper"> <span class="blacklabelprice"><?php wc_get_template( 'loop/price.php' ); ?> </span>
			            <?php if ( $product->add_to_cart_url() !='') : ?>			            	
			                <?php  echo apply_filters( 'woocommerce_loop_add_to_cart_link',
			                    sprintf( '<a href="%s" rel="nofollow" data-product_id="%s" data-product_sku="%s" class="re_track_btn woo_loop_btn btn_offer_block %s %s product_type_%s"%s>%s</a>',
			                    esc_url( $product->add_to_cart_url() ),
			                    esc_attr( $product->get_id() ),
			                    esc_attr( $product->get_sku() ),
			                    $product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
			                    $product->supports( 'ajax_add_to_cart' ) ? 'ajax_add_to_cart' : '',
			                    esc_attr( $product->get_type() ),
			                    $product->get_type() =='external' ? ' target="_blank"' : '',
			                    esc_html( $product->add_to_cart_text() )
			                    ),
			            $product );?> 
		    			<?php endif; ?>	
		    		</div>
		    		<?php endif;?>            		
		    	</div>
			</li>
		<?php endwhile; endif; ?>
		<?php  wp_reset_query(); ?>
		</ul>
	</div>
<?php elseif($feat_type =='2') : //Second type - featured grid ?>
	<?php echo rh_generate_incss('featgrid'); ?>
	<div class="featured_grid">	
		<?php $col_number = 0; if($products->have_posts()): while($products->have_posts()): $products->the_post(); global $post; global $product; $col_number ++; ?>
			<?php if ($col_number == 2) {echo '<div class=" smart-scroll-mobile one-col-mob scroll-on-mob-nomargin disabletabletspadding col-feat-50 rh-flex-columns rh-flex-space-between pl10">';}?>
			<?php 
		  		$image_id = get_post_thumbnail_id($post->ID);  
		  		if ($col_number == 1) {
		  			$image_url = wp_get_attachment_image_src($image_id,'large');
		  		}
		  		else {
		  			$image_url = wp_get_attachment_image_src($image_id,'mediumgrid');
		  		}	
				$image_url = (!empty($image_url[0])) ? $image_url[0] : get_template_directory_uri() . '/images/default/noimage_800_520.png';			
			?>
			<div class="col-feat-grid col_item rh-hovered-wrap flowhidden item-<?php echo ''.$col_number;?>">
				<style scoped>
					#<?php echo ''.$rand_id;?> .item-<?php echo ''.$col_number;?>{
						background-image: url('<?php echo esc_url($image_url) ;?>');
					}
				</style>
				<a href="<?php the_permalink();?>" class="feat_overlay_link"></a> 
		  		<div class="feat-grid-overlay text_in_thumb pt0 pr20 pb10 pl20 csstransall">	  		
		    		<h2><a href="<?php the_permalink();?>"><?php the_title();?></a></h2>
		    		<div class="blacklabelprice"><?php wc_get_template( 'loop/price.php' ); ?> </div>		    		
		    		<?php if ($col_number == 1) :?>
		    		<div class="post-meta">
                		<?php if(rehub_option('exclude_date_meta') != 1):?>
                			<span class="date_ago"><i class="rhicon rhi-clock"></i> <?php printf( esc_html__( '%s ago', 'rehub-theme' ), human_time_diff( get_the_time( 'U' ), current_time( 'timestamp' ) ) ); ?></span>
                		<?php endif;?>
                		<?php if(rehub_option('exclude_comments_meta') != 1):?>		    		
		    			<span class="comm_count_meta"><?php comments_popup_link( esc_html__('no comments','rehub-theme'), esc_html__('1 comment','rehub-theme'), esc_html__('% comments','rehub-theme'), 'comm_meta', ''); ?></span> 
		    			<?php endif;?>               
		    		</div>
		    		<?php endif;?>	            		
		    	</div> 
			</div>
		<?php endwhile; echo '</div>'; endif; ?>
		<?php  wp_reset_query(); ?>
	</div>
<?php endif;?>
</div>
<?php endif;?>


<?php 
$output = ob_get_contents();
ob_end_clean();
return $output;
}
}

//////////////////////////////////////////////////////////////////
// Woo GRID
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_woogrid_shortcode') ) {
function wpsm_woogrid_shortcode( $atts, $content = null ) {
$module_name = 'wpsm_woogrid';
$build_args = shortcode_atts(array(
	'data_source' => 'cat',
	'cat' => '',
	'tag' => '',
	'ids' => '',	
	'orderby' => '',
	'order' => 'DESC',	
	'meta_key'=>'',
	'show' => 12,	
	'offset' => '',
	'show_date' => '',	
	'show_coupons_only' => '',	
	'user_id' => '',	
	'type' => '',	
	'tax_name'=>'',
	'tax_slug'=>'',	
	'tax_slug_exclude'=>'',	
	'enable_pagination' => '', //end woo filters
	'custom_col' => '',
	'custom_img_width'=>'',
	'custom_img_height'	=>'',	
	'columns' => '4_col',
	'woolinktype' => 'product',	
	'filterpanel' => '',
	'filterheading' => '',
	'taxdrop' => '',
	'taxdroplabel' => '',
	'taxdropids' => '',
	'disable_thumbs'=>'',
	'price_range' => '',
	'gridtype' => '',
	'soldout' => '',
	'attrelpanel' => '',	
	'smartscrolllist' => '',
	'iscart'=>''
), $atts, $module_name);
extract($build_args);

if ($columns == '3_col'){
    $col_wrap = ' col_wrap_three';
}
elseif ($columns == '4_col'){
    $col_wrap = ' col_wrap_fourth';
}  
elseif ($columns == '5_col'){
    $col_wrap = ' col_wrap_fifth';
} 
elseif ($columns == '6_col'){
    $col_wrap = ' col_wrap_six';
}             
if ($enable_pagination =='2'){
	$infinitescrollwrap = ' re_aj_pag_auto_wrap';
}     
elseif ($enable_pagination =='3') {
	$infinitescrollwrap = ' re_aj_pag_clk_wrap';
} 
else {
	$infinitescrollwrap = '';
}  
$containerid = 'rh_woogrid_' . mt_rand(); 
$ajaxoffset = (int)$show + (int)$offset;  
$additional_vars = array();
$additional_vars['columns'] = $columns;
$additional_vars['woolinktype'] = $woolinktype; 
$additional_vars['disable_thumbs'] = $disable_thumbs;
$additional_vars['gridtype'] = $gridtype;
$additional_vars['soldout'] = $soldout;
$additional_vars['attrelpanel'] = $attrelpanel;
if($custom_col){
$additional_vars['custom_col'] = $custom_col;
$additional_vars['custom_img_width'] = $custom_img_width;
$additional_vars['custom_img_height'] = $custom_img_height;
}
ob_start(); 
?>
	<?php		 
		$argsfilter = new WPSM_Woohelper($build_args);
		$args = $argsfilter->extract_filters();
		global $post; global $woocommerce; global $wp_query; $temp = $wp_query; 
	?>
	<?php 
    $args = apply_filters('rh_module_args_query', $args);
    $wp_query = new WP_Query($args);
    do_action('rh_after_module_args_query', $wp_query);

	$i=1; if ( $wp_query->have_posts() ) : ?>
		<?php 
			if(!empty($args['paged'])){unset($args['paged']);}
			$jsonargs = json_encode($args);
			$json_innerargs = json_encode($additional_vars);
		?> 
		<div class="woocommerce">
			<?php rehub_vc_filterpanel_render($filterpanel, $containerid, $taxdrop, $taxdroplabel, $taxdropids, $filterheading);?>
			<?php 
				if($gridtype == 'compact'){
					$gridtypeclass = ' eq_grid pt5';
				}
				elseif($gridtype == 'review'){
					$gridtypeclass = ' woogridrev';
				}
				elseif($gridtype == 'digital'){
					$gridtypeclass = ' woogridrev woogriddigi';
				}
				elseif($gridtype == 'dealwhite'){
					$gridtypeclass = ' woodealgrid';
				}
				elseif($gridtype == 'dealdark'){
					$gridtypeclass = ' woodealgriddark';
				}
				elseif($gridtype == 'image'){
					$gridtypeclass = ' woogridimage';
				}	
				elseif($gridtype == 'gridmart'){
					$gridtypeclass = ' grid_mart';
				}		
				else{
					$gridtypeclass = ' grid_woo';
				}
			?> 		
			<?php 
				if($gridtype == 'compact'){
					echo rh_generate_incss('offergrid');
					$gridtypetemplate = 'woogridcompact';
				}
				elseif($gridtype == 'review'){
					$gridtypetemplate = 'woogridrev';
				}
				elseif($gridtype == 'gridmart'){
					echo rh_generate_incss('gridmart');
					$gridtypetemplate = 'woogridmart';
				}
				elseif($gridtype == 'dealwhite'){
					$gridtypetemplate = 'woodealgrid';
				}
				elseif($gridtype == 'dealdark'){
					$gridtypetemplate = 'woodealgriddark';
				}
				elseif($gridtype == 'digital'){
					$gridtypetemplate = 'woogriddigi';
				}
				elseif($gridtype == 'image'){
					$gridtypetemplate = 'woogridimage';
				}			
				else{
					$gridtypetemplate = 'woogridpart';
				}
			?>  
			<?php if($smartscrolllist):?><div class="smart-scroll-desktop"><?php endif;?>
			<div class="rh-flex-eq-height products <?php echo ''.$infinitescrollwrap; echo ''.$col_wrap.$gridtypeclass; ?>" data-filterargs='<?php echo ''.$jsonargs.'';?>' data-template="<?php echo ''.$gridtypetemplate;?>" id="<?php echo esc_attr($containerid);?>" data-innerargs='<?php echo ''.$json_innerargs.'';?>'>                   
			
				<?php while ( $wp_query->have_posts() ) : $wp_query->the_post();  ?>
					<?php if($gridtype == 'compact'):?>
				  		<?php include(rh_locate_template('inc/parts/woogridcompact.php')); ?>	
					<?php elseif($gridtype == 'review'):?>
				  		<?php include(rh_locate_template('inc/parts/woogridrev.php')); ?>
					<?php elseif($gridtype == 'digital'):?>
				  		<?php include(rh_locate_template('inc/parts/woogriddigi.php')); ?>
					<?php elseif($gridtype == 'dealwhite'):?>
				  		<?php include(rh_locate_template('inc/parts/woodealgrid.php')); ?>
					<?php elseif($gridtype == 'dealdark'):?>
				  		<?php include(rh_locate_template('inc/parts/woodealgriddark.php')); ?>
					<?php elseif($gridtype == 'image'):?>
				  		<?php include(rh_locate_template('inc/parts/woogridimage.php')); ?>		
					<?php elseif($gridtype == 'gridmart'):?>
				  		<?php include(rh_locate_template('inc/parts/woogridmart.php')); ?>		
					<?php else:?>
				  		<?php include(rh_locate_template('inc/parts/woogridpart.php')); ?>					
					<?php endif;?>  
				<?php $i++; endwhile; ?>

				<?php if ($enable_pagination == '1') :?>
				    <div class="pagination"><?php rehub_pagination();?></div>
				<?php elseif ($enable_pagination == '2' || $enable_pagination == '3' ) :?>
					<?php wp_enqueue_script('rhajaxpagination');?> 
				    <div class="re_ajax_pagination"><span data-offset="<?php echo esc_attr($ajaxoffset);?>" data-containerid="<?php echo esc_attr($containerid);?>" class="re_ajax_pagination_btn def_btn"><?php esc_html_e('Show next', 'rehub-theme') ?></span></div>      
				<?php endif;?>
			</div> 
			<?php if($smartscrolllist):?></div><?php endif;?>
		</div>
	<?php endif; 
		$wp_query = $temp; 
		wp_reset_query();
	?>   
	<div class="clearfix"></div>

<?php 
$output = ob_get_contents();
ob_end_clean();
return $output;
}
} 

//////////////////////////////////////////////////////////////////
// Woo COLUMNS
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_woocolumns_shortcode') ) {
function wpsm_woocolumns_shortcode( $atts, $content = null ) {
$build_args = shortcode_atts(array(
	'data_source' => 'cat',
	'cat' => '',
	'tag' => '',
	'ids' => '',	
	'orderby' => '',
	'order' => 'DESC',
	'meta_key'=>'',		
	'show' => 12,	
	'offset' => '',	
	'show_date' => '',
	'show_coupons_only' => '',
	'user_id' => '',		
	'type' => '',
	'tax_name'=>'',
	'tax_slug'=>'',	
	'tax_slug_exclude'=>'',		
	'enable_pagination' => '', //end woo filters
	'columns' => '4_col',
	'woolinktype' => 'product',	
	'filterpanel' => '',
	'filterheading' => '',
	'taxdrop' => '',
	'taxdroplabel' => '',
	'taxdropids' => '',
	'custom_col' => '',
	'custom_img_width'=>'',
	'custom_img_height'	=>'',	
	'price_range' => '',
	'attrelpanel' => '',
	'smartscrolllist' => ''			
), $atts, 'wpsm_woocolumns');
extract($build_args);             
if ($enable_pagination =='2'){
	$infinitescrollwrap = ' re_aj_pag_auto_wrap';
}     
elseif ($enable_pagination =='3') {
	$infinitescrollwrap = ' re_aj_pag_clk_wrap';
} 
else {
	$infinitescrollwrap = '';
}  
if ($columns == '3_col'){
    $col_wrap = ' col_wrap_three';
}
elseif ($columns == '4_col'){
    $col_wrap = ' col_wrap_fourth';
}  
elseif ($columns == '5_col'){
    $col_wrap = ' col_wrap_fifth';
} 
elseif ($columns == '6_col'){
    $col_wrap = ' col_wrap_six';
} 
$containerid = 'rh_woocolumn_' . mt_rand(); 
$ajaxoffset = (int)$show + (int)$offset;    
$additional_vars = array();
$additional_vars['columns'] = $columns;
$additional_vars['woolinktype'] = $woolinktype;
$additional_vars['attrelpanel'] = $attrelpanel;
if($custom_col){
$additional_vars['custom_col'] = $custom_col;
$additional_vars['custom_img_width'] = $custom_img_width;
$additional_vars['custom_img_height'] = $custom_img_height;
}
ob_start(); 
?>

<?php		 
	$argsfilter = new WPSM_Woohelper($build_args);
	$args = $argsfilter->extract_filters();
	global $post; global $woocommerce; global $wp_query; $temp = $wp_query;
?>
<?php 

    $args = apply_filters('rh_module_args_query', $args);
    $wp_query = new WP_Query($args);
    do_action('rh_after_module_args_query', $wp_query);

	$i=1; if ( $wp_query->have_posts() ) : ?> 
	<?php 
		if(!empty($args['paged'])){unset($args['paged']);}
		$jsonargs = json_encode($args);
		$json_innerargs = json_encode($additional_vars);
	?> 
	<div class="woocommerce">
		<?php rehub_vc_filterpanel_render($filterpanel, $containerid, $taxdrop, $taxdroplabel, $taxdropids, $filterheading);?>
		<?php if($smartscrolllist):?><div class="smart-scroll-desktop"><?php endif;?>
		<div class="column_woo products <?php echo ''.$infinitescrollwrap; echo ''.$col_wrap;?>" data-filterargs='<?php echo ''.$jsonargs.'';?>' data-template="woocolumnpart" data-innerargs='<?php echo ''.$json_innerargs.'';?>' id="<?php echo esc_attr($containerid);?>">                     
			<?php while ( $wp_query->have_posts() ) : $wp_query->the_post();  ?>
			   <?php include(rh_locate_template('inc/parts/woocolumnpart.php')); ?>  
			<?php $i++; endwhile; ?>
			<?php if ($enable_pagination == '1') :?>
			    <div class="pagination"><?php rehub_pagination();?></div>
			<?php elseif ($enable_pagination == '2' || $enable_pagination == '3' ) :?>
				<?php wp_enqueue_script('rhajaxpagination');?> 
			    <div class="re_ajax_pagination"><span data-offset="<?php echo esc_attr($ajaxoffset);?>" data-containerid="<?php echo esc_attr($containerid);?>" class="re_ajax_pagination_btn def_btn"><?php esc_html_e('Show next', 'rehub-theme') ?></span></div>      
			<?php endif;?>	
		</div>
		<?php if($smartscrolllist):?></div><?php endif;?>
	</div>

<?php endif; $wp_query = $temp; wp_reset_query(); ?>   
<div class="clearfix"></div>

<?php 
$output = ob_get_contents();
ob_end_clean();
return $output;
}
}

//////////////////////////////////////////////////////////////////
// Woo List
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_woolist_shortcode') ) {
function wpsm_woolist_shortcode( $atts, $content = null ) {
	
$build_args = shortcode_atts(array(
	'data_source' => 'cat',
	'cat' => '',
	'tag' => '',
	'ids' => '',	
	'orderby' => '',
	'order' => 'DESC',	
	'meta_key'=>'',
	'show' => 12,
	'offset' => '',	
	'show_date' => '',		
	'show_coupons_only' => '',	
	'user_id' => '',
	'type' => '',	
	'tax_name'=>'',
	'tax_slug'=>'',	
	'tax_slug_exclude'=>'',	
	'enable_pagination' => '', //end woo filters
	'filterpanel' => '',
	'filterheading' => '',
	'taxdrop' => '',
	'taxdroplabel' => '',
	'taxdropids' => '',
	'price_range' => '',
	'attrelpanel' => '',
), $atts, 'wpsm_woolist');
extract($build_args);
if ($enable_pagination =='2'){
	$infinitescrollwrap = ' re_aj_pag_auto_wrap';
}     
elseif ($enable_pagination =='3') {
	$infinitescrollwrap = ' re_aj_pag_clk_wrap';
} 
else {
	$infinitescrollwrap = '';
}  
$containerid = 'rh_woolist_' . mt_rand(); 
$ajaxoffset = (int)$show + (int)$offset; 
$additional_vars = array();
$additional_vars['attrelpanel'] = $attrelpanel;
ob_start(); 
?>

<?php rehub_vc_filterpanel_render($filterpanel, $containerid, $taxdrop, $taxdroplabel, $taxdropids, $filterheading);?>
<?php		 
	$argsfilter = new WPSM_Woohelper($build_args);
	$args = $argsfilter->extract_filters();
	global $post; global $woocommerce; $backup=$post; $result_min = array(); //add array of prices
?>
<?php     

	$args = apply_filters('rh_module_args_query', $args);
    $wp_query = new WP_Query($args);
    do_action('rh_after_module_args_query', $wp_query); 

	$i=1; if ( $wp_query->have_posts() ) : ?> 
	<?php 
		if(!empty($args['paged'])){unset($args['paged']);}
		$jsonargs = json_encode($args);
		$json_innerargs = json_encode($additional_vars);
	?> 
	<div class="woo_offer_list <?php echo ''.$infinitescrollwrap; ?>" data-innerargs='<?php echo ''.$json_innerargs.'';?>' data-filterargs='<?php echo ''.$jsonargs.'';?>' data-template="woolistpart" id="<?php echo esc_attr($containerid);?>">	                    
		<a name="woo-link-list"></a>		
		<?php while ( $wp_query->have_posts() ) : $wp_query->the_post();  global $product;  ?>
			<?php include(rh_locate_template('inc/parts/woolistpart.php')); ?>
            <?php
                $price_clean = $product->get_price();
                $result_min[] = $price_clean;
            ?>			
		<?php $i++; endwhile; ?>
		
		<?php if ($enable_pagination == '1') :?>
		    <div class="pagination"><?php rehub_pagination();?></div>
		<?php elseif ($enable_pagination == '2' || $enable_pagination == '3' ) :?>
			<?php wp_enqueue_script('rhajaxpagination');?> 
		    <div class="re_ajax_pagination"><span data-offset="<?php echo esc_attr($ajaxoffset);?>" data-containerid="<?php echo esc_attr($containerid);?>" class="re_ajax_pagination_btn def_btn"><?php esc_html_e('Show next', 'rehub-theme') ?></span></div>      
		<?php endif;?>	

	</div>
<?php endif; $post=$backup; wp_reset_query(); ?> 


<?php
if (!empty($result_min)) {
	$min_woo_price_old = get_post_meta( get_the_ID(), 'rehub_min_woo_price', true );
	$min_woo_price = min($result_min); 
	if ( $min_woo_price !='' && $min_woo_price_old !='' && $min_woo_price != $min_woo_price_old ){
		update_post_meta(get_the_ID(), 'rehub_min_woo_price', $min_woo_price);
		update_post_meta(get_the_ID(), 'rehub_main_product_price', $min_woo_price); 
	}
	elseif($min_woo_price !='' && $min_woo_price_old =='') {
		update_post_meta(get_the_ID(), 'rehub_min_woo_price', $min_woo_price); 
		update_post_meta(get_the_ID(), 'rehub_main_product_price', $min_woo_price);
	}					 
}
?>	

<?php
$output = ob_get_contents();
ob_end_clean();
return $output;		

}
}

//////////////////////////////////////////////////////////////////
// Woo Rows
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_woorows_shortcode') ) {
function wpsm_woorows_shortcode( $atts, $content = null ) {
	
$build_args = shortcode_atts(array(
	'data_source' => 'cat',
	'cat' => '',
	'tag' => '',
	'ids' => '',	
	'orderby' => '',
	'order' => 'DESC',	
	'meta_key'=>'',
	'show' => 12,
	'offset' => '',	
	'show_date' => '',		
	'show_coupons_only' => '',
	'user_id' => '',	
	'type' => '',	
	'tax_name'=>'',
	'tax_slug'=>'',	
	'tax_slug_exclude'=>'',	
	'price_range' => '',	
	'enable_pagination' => '', //end woo filters
	'designtype' => '',	
	'filterpanel' => '',
	'filterheading' => '',
	'taxdrop' => '',
	'taxdroplabel' => '',
	'taxdropids' => '',	
	'price_range' => '',
	'attrelpanel' => '',	
), $atts, 'wpsm_woorows');
extract($build_args);
if ($enable_pagination =='2'){
	$infinitescrollwrap = ' re_aj_pag_auto_wrap';
}     
elseif ($enable_pagination =='3') {
	$infinitescrollwrap = ' re_aj_pag_clk_wrap';
} 
else {
	$infinitescrollwrap = '';
}  
$containerid = 'rh_woorows_' . mt_rand(); 
$ajaxoffset = (int)$show + (int)$offset; 
$additional_vars = array();
$additional_vars['attrelpanel'] = $attrelpanel;
ob_start(); 
?>

<?php		 
	$argsfilter = new WPSM_Woohelper($build_args);
	$args = $argsfilter->extract_filters();
	global $post; global $woocommerce; $backup=$post;
?>
<?php 

    $args = apply_filters('rh_module_args_query', $args);
    $wp_query = new WP_Query($args);
    do_action('rh_after_module_args_query', $wp_query);

	$i=1; if ( $wp_query->have_posts() ) : ?> 
	<?php 
		if(!empty($args['paged'])){unset($args['paged']);}
		$jsonargs = json_encode($args);
		$json_innerargs = json_encode($additional_vars);
	?> 
	<div class="woocommerce">
	<?php rehub_vc_filterpanel_render($filterpanel, $containerid, $taxdrop, $taxdroplabel, $taxdropids, $filterheading);?>
	<div class="list_woo products <?php echo ''.$infinitescrollwrap; ?>" data-filterargs='<?php echo ''.$jsonargs.'';?>' data-template="woolistmain" data-innerargs='<?php echo ''.$json_innerargs.'';?>' id="<?php echo esc_attr($containerid);?>">	                    
		
		<?php while ( $wp_query->have_posts() ) : $wp_query->the_post();  global $product;  ?>
			<?php if($designtype == 'compact'):?>
				<?php include(rh_locate_template('inc/parts/woolistcompact.php')); ?>
			<?php else:?>
				<?php include(rh_locate_template('inc/parts/woolistmain.php')); ?>
			<?php endif;?>
		<?php $i++; endwhile; ?>
		
		<?php if ($enable_pagination == '1') :?>
		    <div class="pagination"><?php rehub_pagination();?></div>
		<?php elseif ($enable_pagination == '2' || $enable_pagination == '3' ) :?>
			<?php wp_enqueue_script('rhajaxpagination');?> 
		    <div class="re_ajax_pagination"><span data-offset="<?php echo esc_attr($ajaxoffset);?>" data-containerid="<?php echo esc_attr($containerid);?>" class="re_ajax_pagination_btn def_btn"><?php esc_html_e('Show next', 'rehub-theme') ?></span></div>      
		<?php endif;?>	
	</div>
	</div>
<?php endif; $post=$backup; wp_reset_query(); ?> 

<?php
$output = ob_get_contents();
ob_end_clean();
return $output;		

}
}

//////////////////////////////////////////////////////////////////
// COMPACT DEAL GRID
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_compactgrid_loop_shortcode') ) {
function wpsm_compactgrid_loop_shortcode( $atts, $content = null ) {

$build_args = shortcode_atts(array(
	'data_source' => 'cat', //Filters start
	'cat' => '',
	'cat_name' => '',
	'tag' => '',
	'cat_exclude' => '',
	'tag_exclude' => '',	
	'ids' => '',	
	'orderby' => '',
	'order' => 'DESC',	
	'meta_key' => '',
	'show' => 12,
	'offset' => '',
	'show_date' => '',	
	'post_type' => '',
	'tax_name' => '',
	'tax_slug' => '',
	'tax_slug_exclude' => '',
	'post_formats' => '',
	'badge_label'=> '1',	
	'enable_pagination' => '',	
	'price_range' => '',
	'user_id' => '',
	'show_coupons_only' =>'', //Filters end
	'columns' => '4_col',
	'aff_link' => '',
	'disable_btn'=>'',
	'disable_act'=>'',	
	'price_meta'=> '1',
	'filterpanel' => '',
	'filterheading' => '',
	'taxdrop' => '',
	'taxdroplabel' => '',
	'taxdropids' => '',	
	'gridtype' => '',
	'smartscrolllist'=> ''
), $atts, 'compactgrid_loop_mod');

extract($build_args);

if ($columns == '4_col'){
    $col_wrap = 'col_wrap_fourth';
}  
elseif ($columns == '5_col'){
    $col_wrap = 'col_wrap_fifth';
} 
elseif ($columns == '6_col'){
    $col_wrap = 'col_wrap_six';
} 
else {
   $col_wrap = 'col_wrap_three'; 
}             
if ($enable_pagination =='2'){
	$infinitescrollwrap = ' re_aj_pag_auto_wrap';
}     
elseif ($enable_pagination =='3') {
	$infinitescrollwrap = ' re_aj_pag_clk_wrap';
} 
else {
	$infinitescrollwrap = '';
}  
$containerid = 'rh_dealgrid_' . mt_rand();    
$ajaxoffset = (int)$show + (int)$offset;   
$additional_vars = array();
$additional_vars['columns'] = $columns;
$additional_vars['aff_link'] = $aff_link;
$additional_vars['disable_btn'] = $disable_btn;
$additional_vars['disable_act'] = $disable_act;
$additional_vars['price_meta'] = $price_meta;
$additional_vars['gridtype'] = $gridtype;
ob_start(); 
?>
<?php rehub_vc_filterpanel_render($filterpanel, $containerid, $taxdrop, $taxdroplabel, $taxdropids, $filterheading);?>
<?php
	global $wp_query; 
	$argsfilter = new WPSM_Postfilters($build_args);
	$args = $argsfilter->extract_filters();

    $args = apply_filters('rh_module_args_query', $args);
    $wp_query = new WP_Query($args);
    do_action('rh_after_module_args_query', $wp_query);

?>
<?php if ( $wp_query->have_posts() ) : ?>
	<?php 
		if(!empty($args['paged'])){unset($args['paged']);}
		$jsonargs = json_encode($args);
		$json_innerargs = json_encode($additional_vars);
	?> 	
	<?php echo rh_generate_incss('offergrid');?>
	<?php if($smartscrolllist):?><div class="smart-scroll-desktop"><?php endif;?>
		<div class="eq_grid pt5 rh-flex-eq-height <?php echo esc_attr($col_wrap); echo esc_attr($infinitescrollwrap);?>" data-filterargs='<?php echo ''.$jsonargs.'';?>' data-template="compact_grid" id="<?php echo esc_attr($containerid);?>" data-innerargs='<?php echo ''.$json_innerargs.'';?>'>
			<?php while ( $wp_query->have_posts() ) : $wp_query->the_post();  ?>
				<?php include(rh_locate_template('inc/parts/compact_grid.php')); ?>
			<?php endwhile; ?>

			<?php if ($enable_pagination == '1') :?>
				<?php wp_enqueue_script('rhajaxpagination');?>
				<div class="pagination"><?php rehub_pagination();?></div>
			<?php elseif ($enable_pagination == '2' || $enable_pagination == '3' ) :?>
				<?php wp_enqueue_script('rhajaxpagination');?> 
				<div class="re_ajax_pagination"><span data-offset="<?php echo esc_attr($ajaxoffset);?>" data-containerid="<?php echo esc_attr($containerid);?>" class="re_ajax_pagination_btn def_btn"><?php esc_html_e('Show next', 'rehub-theme') ?></span></div>      
			<?php endif ;?>
		</div>
	<?php if($smartscrolllist):?></div><?php endif;?>
	
	<div class="clearfix"></div>
<?php endif; wp_reset_query(); ?>


<?php 
$output = ob_get_contents();
ob_end_clean();
return $output;
}
}

//////////////////////////////////////////////////////////////////
// COLUMN GRID
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_columngrid_loop_shortcode') ) {
function wpsm_columngrid_loop_shortcode( $atts, $content = null ) {
$build_args = shortcode_atts(array(
	'data_source' => 'cat', //Filters start
	'cat' => '',
	'cat_name' => '',	
	'tag' => '',
	'cat_exclude' => '',
	'tag_exclude' => '',	
	'ids' => '',	
	'orderby' => '',
	'order' => 'DESC',	
	'meta_key' => '',
	'show' => 12,
	'offset' => '',
	'show_date' => '',	
	'post_type' => '',
	'tax_name' => '',
	'tax_slug' => '',
	'tax_slug_exclude' => '',
	'post_formats' => '',
	'badge_label'=> '1',
	'enable_pagination' => '',
	'price_range' => '',
	'user_id' => '',		
	'show_coupons_only' =>'', //Filters end
	'columns' => '4_col',
	'exerpt_count' => '',
	'enable_btn'=> '',
	'disable_meta' => '',
	'disable_price' => '',
	'image_padding'=>'',
	'aff_link' => '',	
	'filterpanel' => '',
	'filterheading' => '',
	'taxdrop' => '',
	'taxdroplabel' => '',
	'taxdropids' => '',	
	'disablecard' => '',
), $atts, 'columngrid_loop'); 
extract($build_args);            
if ($enable_pagination =='2'){
	$infinitescrollwrap = ' re_aj_pag_auto_wrap';
}     
elseif ($enable_pagination =='3') {
	$infinitescrollwrap = ' re_aj_pag_clk_wrap';
} 
else {
	$infinitescrollwrap = '';
}  
$containerid = 'rh_clmgrid_' . mt_rand();    
$ajaxoffset = (int)$show + (int)$offset;   
$additional_vars = array();
$additional_vars['columns'] = $columns;
$additional_vars['aff_link'] = $aff_link;
$additional_vars['exerpt_count'] = $exerpt_count;
$additional_vars['disable_meta'] = $disable_meta;
$additional_vars['enable_btn'] = $enable_btn;
$additional_vars['disable_price'] = $disable_price;
$additional_vars['image_padding'] = $image_padding;
$additional_vars['disablecard'] = $disablecard;
ob_start(); 
?>
<?php rehub_vc_filterpanel_render($filterpanel, $containerid, $taxdrop, $taxdroplabel, $taxdropids, $filterheading);?>
<?php if ($columns =='2_col') : ?>
	<?php $col_number_class= ' col_wrap_two'; ?>
<?php elseif ($columns =='3_col') : ?>
	<?php $col_number_class= ' col_wrap_three'; ?>
<?php elseif ($columns =='4_col') : ?>
	<?php $col_number_class= ' col_wrap_fourth'; ?>
<?php elseif ($columns =='5_col') : ?>
	<?php $col_number_class= ' col_wrap_fifth'; ?>
<?php elseif ($columns =='6_col') : ?>
	<?php $col_number_class= ' col_wrap_six'; ?>	
<?php endif ;?>
<?php
	global $wp_query; 
	$argsfilter = new WPSM_Postfilters($build_args);
	$args = $argsfilter->extract_filters();

    $args = apply_filters('rh_module_args_query', $args);
    $wp_query = new WP_Query($args);
    do_action('rh_after_module_args_query', $wp_query);

?>

<?php $i=1; if ( $wp_query->have_posts() ) : ?>
	<?php 
		if(!empty($args['paged'])){unset($args['paged']);}
		$jsonargs = json_encode($args);
		$json_innerargs = json_encode($additional_vars);
	?>   
	<div class="columned_grid_module rh-flex-eq-height <?php echo ''.$infinitescrollwrap.$col_number_class;?>" data-filterargs='<?php echo ''.$jsonargs.'';?>' data-template="column_grid" id="<?php echo esc_attr($containerid);?>" data-innerargs='<?php echo ''.$json_innerargs.'';?>'>                    
		
		<?php while ( $wp_query->have_posts() ) : $wp_query->the_post();?>
			<?php include(rh_locate_template('inc/parts/column_grid.php')); ?>
		<?php $i++; endwhile; ?>

		<?php if ($enable_pagination == '1') :?>
		    <div class="pagination"><?php rehub_pagination();?></div>
		<?php elseif ($enable_pagination == '2' || $enable_pagination == '3' ) :?>
			<?php wp_enqueue_script('rhajaxpagination');?> 
		    <div class="re_ajax_pagination"><span data-offset="<?php echo esc_attr($ajaxoffset);?>" data-containerid="<?php echo esc_attr($containerid);?>" class="re_ajax_pagination_btn def_btn"><?php esc_html_e('Show next', 'rehub-theme') ?></span></div>      
		<?php endif ;?>

	</div>
	<div class="clearfix"></div>
<?php endif; wp_reset_query(); ?>

<?php 
$output = ob_get_contents();
ob_end_clean();
return $output;
}
}

//////////////////////////////////////////////////////////////////
// COLOR GRID
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_colorgrid_shortcode') ) {
function wpsm_colorgrid_shortcode( $atts, $content = null ) {
$build_args = shortcode_atts(array(
	'data_source' => 'cat', //Filters start
	'cat' => '',
	'cat_name' => '',	
	'tag' => '',
	'cat_exclude' => '',
	'tag_exclude' => '',	
	'ids' => '',	
	'orderby' => '',
	'order' => 'DESC',	
	'meta_key' => '',
	'user_id' => '',
	'show' => 12,
	'offset' => '',
	'show_date' => '',	
	'post_type' => '',
	'tax_name' => '',
	'tax_slug' => '',
	'tax_slug_exclude' => '',
	'post_formats' => '',
	'badge_label'=> '1',
	'enable_pagination' => '',
	'price_range' => '',		
	'show_coupons_only' =>'', //Filters end
	'columns' => '4_col',
	'filterpanel' => '',
	'filterheading' => '',
	'taxdrop' => '',
	'taxdroplabel' => '',
	'taxdropids' => '',	
	'enableimage'=>'',
	'mobilescroll'=> '',
	'mobilescrollwidth' => 280
), $atts, 'wpsm_colorgrid'); 
extract($build_args);            
if ($enable_pagination =='2'){
	$infinitescrollwrap = ' re_aj_pag_auto_wrap';
}     
elseif ($enable_pagination =='3') {
	$infinitescrollwrap = ' re_aj_pag_clk_wrap';
} 
else {
	$infinitescrollwrap = '';
}  
$containerid = 'rh_colorgrid_' . mt_rand();    
$ajaxoffset = (int)$show + (int)$offset;   
$additional_vars = array();
$additional_vars['columns'] = $columns;
$additional_vars['enableimage'] = $enableimage;
ob_start(); 
?>
<?php rehub_vc_filterpanel_render($filterpanel, $containerid, $taxdrop, $taxdroplabel, $taxdropids, $filterheading);?>
<?php if ($columns =='2_col') : ?>
	<?php $col_number_class= ' col_wrap_two'; ?>
<?php elseif ($columns =='3_col') : ?>
	<?php $col_number_class= ' col_wrap_three'; ?>
<?php elseif ($columns =='4_col') : ?>
	<?php $col_number_class= ' col_wrap_fourth'; ?>
<?php elseif ($columns =='5_col') : ?>
	<?php $col_number_class= ' col_wrap_fifth'; ?>
<?php elseif ($columns =='6_col') : ?>
	<?php $col_number_class= ' col_wrap_six'; ?>	
<?php endif ;?>
<?php
	global $wp_query; 
	$argsfilter = new WPSM_Postfilters($build_args);
	$args = $argsfilter->extract_filters();

    $args = apply_filters('rh_module_args_query', $args);
    $wp_query = new WP_Query($args);
    do_action('rh_after_module_args_query', $wp_query);

?>

<?php $i=1; if ( $wp_query->have_posts() ) : ?>
	<?php 
		if(!empty($args['paged'])){unset($args['paged']);}
		$jsonargs = json_encode($args);
		$json_innerargs = json_encode($additional_vars);
	?>   
	<?php if($mobilescroll):?><div class="smart-scroll-mobile"><?php endif;?>
	<div class="coloredgrid pt5 rh-flex-eq-height <?php echo ''.$infinitescrollwrap.$col_number_class;?>" data-filterargs='<?php echo ''.$jsonargs.'';?>' data-template="color_grid" id="<?php echo esc_attr($containerid);?>" data-innerargs='<?php echo ''.$json_innerargs.'';?>'>                    
		
		<?php while ( $wp_query->have_posts() ) : $wp_query->the_post();?>
			<?php include(rh_locate_template('inc/parts/color_grid.php')); ?>
		<?php $i++; endwhile; ?>

		<?php if ($enable_pagination == '1') :?>
		    <div class="pagination"><?php rehub_pagination();?></div>
		<?php elseif ($enable_pagination == '2' || $enable_pagination == '3' ) :?>
			<?php wp_enqueue_script('rhajaxpagination');?> 
		    <div class="re_ajax_pagination"><span data-offset="<?php echo esc_attr($ajaxoffset);?>" data-containerid="<?php echo esc_attr($containerid);?>" class="re_ajax_pagination_btn def_btn"><?php esc_html_e('Show next', 'rehub-theme') ?></span></div>      
		<?php endif ;?>

	</div>
	<div class="clearfix"></div>
	<?php if($mobilescroll):?></div><?php endif;?>
<?php endif; wp_reset_query(); ?>

<?php 
$output = ob_get_contents();
ob_end_clean();
return $output;
}
}

//////////////////////////////////////////////////////////////////
// LIST LOOP OF POSTS
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_small_thumb_loop_shortcode') ) {
function wpsm_small_thumb_loop_shortcode( $atts, $content = null ) {
$build_args = shortcode_atts(array(
	'data_source' => 'cat', //Filters start
	'cat' => '',
	'cat_name' => '',
	'tag' => '',
	'cat_exclude' => '',
	'tag_exclude' => '',	
	'ids' => '',	
	'orderby' => '',
	'order' => 'DESC',	
	'meta_key' => '',
	'show' => 10,
	'offset' => '',
	'show_date' => '',	
	'post_type' => '',
	'tax_name' => '',
	'tax_slug' => '',
	'user_id' => '',
	'tax_slug_exclude' => '',
	'post_formats' => '',
	'badge_label'=> '1',	
	'enable_pagination' => '',
	'price_range' => '',		
	'show_coupons_only' =>'', //Filters end
	'type' => '1',
	'filterpanel' => '',
	'filterheading' => '',
	'taxdrop' => '',
	'taxdroplabel' => '',
	'taxdropids' => '',	
), $atts, 'small_thumb_loop');   
extract($build_args);   
if ($enable_pagination =='2'){
	$infinitescrollwrap = ' re_aj_pag_auto_wrap';
}     
elseif ($enable_pagination =='3') {
	$infinitescrollwrap = ' re_aj_pag_clk_wrap';
} 
else {
	$infinitescrollwrap = '';
}   
$containerid = 'rh_filterid_' . mt_rand(); 
$ajaxoffset = (int)$show + (int)$offset;
$additional_vars = array();
$additional_vars['type'] = $type;
ob_start(); 
?>
<?php rehub_vc_filterpanel_render($filterpanel, $containerid, $taxdrop, $taxdroplabel, $taxdropids, $filterheading);?>
<?php
	global $wp_query; 
	$argsfilter = new WPSM_Postfilters($build_args);
	$args = $argsfilter->extract_filters();

    $args = apply_filters('rh_module_args_query', $args);
    $wp_query = new WP_Query($args);
    do_action('rh_after_module_args_query', $wp_query);

?>
<?php if ( $wp_query->have_posts() ) : ?>
	<?php 
		if(!empty($args['paged'])){unset($args['paged']);}
		$jsonargs = json_encode($args);
		$json_innerargs = json_encode($additional_vars);
	?>
	<div class="<?php echo ''.$infinitescrollwrap; ?>" data-filterargs='<?php echo ''.$jsonargs.'';?>' data-template="query_type1" id="<?php echo esc_attr($containerid);?>" data-innerargs='<?php echo ''.$json_innerargs.'';?>'>

		<?php while ( $wp_query->have_posts() ) : $wp_query->the_post();  ?>	
			<?php include(rh_locate_template('inc/parts/query_type1.php')); ?>
		<?php endwhile; ?>

		<?php if ($enable_pagination == '1') :?>
			<div class="clearfix"></div>
		    <div class="pagination"><?php rehub_pagination();?></div>
		<?php elseif ($enable_pagination == '2' || $enable_pagination == '3' ) :?>
			<?php wp_enqueue_script('rhajaxpagination');?> 
		    <div class="re_ajax_pagination"><span data-offset="<?php echo esc_attr($ajaxoffset);?>" data-containerid="<?php echo esc_attr($containerid);?>" class="re_ajax_pagination_btn def_btn"><?php esc_html_e('Show next', 'rehub-theme') ?></span></div>      
		<?php endif ;?>

	</div>
	<div class="clearfix"></div>
<?php endif; wp_reset_query(); ?>

<?php 
$output = ob_get_contents();
ob_end_clean();
return $output;
}
}

//////////////////////////////////////////////////////////////////
// LIST OFFERS LOOP OF POSTS
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_offer_list_loop_shortcode') ) {
function wpsm_offer_list_loop_shortcode( $atts, $content = null ) {
$build_args = shortcode_atts(array(
	'data_source' => 'cat', //Filters start
	'cat' => '',
	'cat_name' => '',
	'tag' => '',
	'cat_exclude' => '',
	'tag_exclude' => '',	
	'ids' => '',	
	'orderby' => '',
	'order' => 'DESC',	
	'meta_key' => '',
	'show' => 10,
	'user_id' => '',
	'offset' => '',
	'show_date' => '',	
	'post_type' => '',
	'tax_name' => '',
	'tax_slug' => '',
	'tax_slug_exclude' => '',
	'post_formats' => '',
	'badge_label'=> '1',	
	'enable_pagination' => '',
	'price_range' => '',		
	'show_coupons_only' =>'', //Filters end
	'filterpanel' => '',
	'filterheading' => '',
	'taxdrop' => '',
	'taxdroplabel' => '',
	'taxdropids' => '',
	'aff_link' => '',	
), $atts, 'small_thumb_loop');   
extract($build_args);   
if ($enable_pagination =='2'){
	$infinitescrollwrap = ' re_aj_pag_auto_wrap';
}     
elseif ($enable_pagination =='3') {
	$infinitescrollwrap = ' re_aj_pag_clk_wrap';
} 
else {
	$infinitescrollwrap = '';
}   
$containerid = 'rh_filterid_' . mt_rand(); 
$ajaxoffset = (int)$show + (int)$offset;
$additional_vars = array();
$additional_vars['aff_link'] = $aff_link; 
ob_start(); 
?>
<?php rehub_vc_filterpanel_render($filterpanel, $containerid, $taxdrop, $taxdroplabel, $taxdropids, $filterheading);?>
<?php
	global $wp_query; 
	$argsfilter = new WPSM_Postfilters($build_args);
	$args = $argsfilter->extract_filters();

    $args = apply_filters('rh_module_args_query', $args);
    $wp_query = new WP_Query($args);
    do_action('rh_after_module_args_query', $wp_query);

?>
<?php if ( $wp_query->have_posts() ) : ?>
	<?php 
		if(!empty($args['paged'])){unset($args['paged']);}
		$jsonargs = json_encode($args);
		$json_innerargs = json_encode($additional_vars);
	?>
	<div class="woo_offer_list <?php echo ''.$infinitescrollwrap;?>" data-filterargs='<?php echo ''.$jsonargs.'';?>' data-template="postlistpart" id="<?php echo esc_attr($containerid);?>" data-innerargs='<?php echo ''.$json_innerargs.'';?>'>
		
		<?php while ( $wp_query->have_posts() ) : $wp_query->the_post();  ?>	
			<?php include(rh_locate_template('inc/parts/postlistpart.php')); ?>
		<?php endwhile; ?>

		<?php if ($enable_pagination == '1') :?>
			<div class="clearfix"></div>
		    <div class="pagination"><?php rehub_pagination();?></div>
		<?php elseif ($enable_pagination == '2' || $enable_pagination == '3' ) :?>
			<?php wp_enqueue_script('rhajaxpagination');?> 
		    <div class="re_ajax_pagination"><span data-offset="<?php echo esc_attr($ajaxoffset);?>" data-containerid="<?php echo esc_attr($containerid);?>" class="re_ajax_pagination_btn def_btn"><?php esc_html_e('Show next', 'rehub-theme') ?></span></div>      
		<?php endif ;?>

	</div>
	<div class="clearfix"></div>
<?php endif; wp_reset_query(); ?>

<?php 
$output = ob_get_contents();
ob_end_clean();
return $output;
}
}


//////////////////////////////////////////////////////////////////
// LIST CONSTRUCTOR
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_list_constructor') ) {
function wpsm_list_constructor( $atts, $content = null ) {
$build_args = shortcode_atts(array(
	'data_source' => 'cat', //Filters start
	'cat' => '',
	'cat_name' => '',
	'tag' => '',
	'cat_exclude' => '',
	'tag_exclude' => '',	
	'ids' => '',	
	'orderby' => '',
	'order' => 'DESC',	
	'meta_key' => '',
	'show' => 10,
	'user_id' => '',
	'offset' => '',
	'show_date' => '',	
	'post_type' => '',
	'tax_name' => '',
	'tax_slug' => '',
	'tax_slug_exclude' => '',
	'post_formats' => '',
	'badge_label'=> '1',	
	'enable_pagination' => '',
	'price_range' => '',		
	'show_coupons_only' =>'', //Filters end
	'filterpanel' => '',
	'filterheading' => '',
	'taxdrop' => '',
	'taxdroplabel' => '',
	'taxdropids' => '',
	'listargs' => '',

), $atts, 'small_thumb_loop');   
extract($build_args);   
if ($enable_pagination =='2'){
	$infinitescrollwrap = ' re_aj_pag_auto_wrap';
}     
elseif ($enable_pagination =='3') {
	$infinitescrollwrap = ' re_aj_pag_clk_wrap';
} 
else {
	$infinitescrollwrap = '';
}   
$containerid = 'rh_filterid_' . mt_rand(); 
$ajaxoffset = (int)$show + (int)$offset;
ob_start(); 
?>
<?php rehub_vc_filterpanel_render($filterpanel, $containerid, $taxdrop, $taxdroplabel, $taxdropids, $filterheading);?>
<?php
	global $wp_query; 
	$argsfilter = new WPSM_Postfilters($build_args);
	$args = $argsfilter->extract_filters();

    $args = apply_filters('rh_module_args_query', $args);
    $wp_query = new WP_Query($args);
    do_action('rh_after_module_args_query', $wp_query);
?>
<?php if ( $wp_query->have_posts() ) : ?>
	<?php 
		if(!empty($args['paged'])){$pagenumber = $args['paged']; unset($args['paged']);}
		$jsonargs = json_encode($args);
		$json_innerargs = $listargs;
	?>
	<div class="rh_list_builder review_visible_circle <?php echo ''.$infinitescrollwrap;?>" data-filterargs='<?php echo ''.$jsonargs.'';?>' data-template="listbuilder" id="<?php echo esc_attr($containerid);?>" data-innerargs='<?php echo ''.$json_innerargs.'';?>'>
		
		<?php $i=0; while ( $wp_query->have_posts() ) : $wp_query->the_post(); $i++;  ?>
			<?php include(rh_locate_template('inc/parts/listbuilder.php')); ?>
		<?php endwhile; ?>

		<?php if ($enable_pagination == '1') :?>
			<div class="clearfix"></div>
		    <div class="pagination"><?php rehub_pagination();?></div>
		<?php elseif ($enable_pagination == '2' || $enable_pagination == '3' ) :?>
			<?php wp_enqueue_script('rhajaxpagination');?> 
		    <div class="re_ajax_pagination"><span data-offset="<?php echo esc_attr($ajaxoffset);?>" data-containerid="<?php echo esc_attr($containerid);?>" class="re_ajax_pagination_btn def_btn"><?php esc_html_e('Show next', 'rehub-theme') ?></span></div>      
		<?php endif ;?>

	</div>
	<div class="clearfix"></div>
<?php endif; wp_reset_query(); ?>

<?php 
$output = ob_get_contents();
ob_end_clean();
return $output;
}
}


//////////////////////////////////////////////////////////////////
// BLOG LOOP OF POSTS
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_regular_blog_loop_shortcode') ) {
function wpsm_regular_blog_loop_shortcode( $atts, $content = null ) {
$build_args =shortcode_atts(array(
	'data_source' => 'cat', //Filters start
	'cat' => '',
	'tag' => '',
	'cat_exclude' => '',
	'tag_exclude' => '',	
	'ids' => '',	
	'orderby' => '',
	'order' => 'DESC',	
	'meta_key' => '',
	'show' => 12,
	'user_id' => '',
	'offset' => '',
	'show_date' => '',	
	'post_type' => '',
	'tax_name' => '',
	'tax_slug' => '',
	'tax_slug_exclude' => '',
	'post_formats' => '',
	'badge_label'=> '1',	
	'enable_pagination' => '',
	'price_range' => '',		
	'show_coupons_only' =>'', //Filters end
), $atts, 'regular_blog_loop'); 
extract($build_args);            
if ($enable_pagination =='2'){
	$infinitescrollwrap = ' re_aj_pag_auto_wrap';
}     
elseif ($enable_pagination =='3') {
	$infinitescrollwrap = ' re_aj_pag_clk_wrap';
} 
else {
	$infinitescrollwrap = '';
}  
$containerid = 'rh_blogloop_' . mt_rand();    
$ajaxoffset = (int)$show + (int)$offset;   
ob_start(); 
?>

<?php
	global $wp_query; 
	$argsfilter = new WPSM_Postfilters($build_args);
	$args = $argsfilter->extract_filters();

    $args = apply_filters('rh_module_args_query', $args);
    $wp_query = new WP_Query($args);
    do_action('rh_after_module_args_query', $wp_query);

?>

<?php if ( $wp_query->have_posts() ) : ?>
	<?php 
		if(!empty($args['paged'])){unset($args['paged']);}
		$jsonargs = json_encode($args);
	?> 
	<div class="<?php echo ''.$infinitescrollwrap;?>" data-filterargs='<?php echo ''.$jsonargs.'';?>' data-template="query_type2" id="<?php echo esc_attr($containerid);?>">
		<?php while ( $wp_query->have_posts() ) : $wp_query->the_post();  ?>	
			<?php include(rh_locate_template('inc/parts/query_type2.php')); ?>
		<?php endwhile; ?>

		<?php if ($enable_pagination == '1') :?>
		    <div class="pagination"><?php rehub_pagination();?></div>
		<?php elseif ($enable_pagination == '2' || $enable_pagination == '3' ) :?>
			<?php wp_enqueue_script('rhajaxpagination');?> 
		    <div class="re_ajax_pagination"><span data-offset="<?php echo esc_attr($ajaxoffset);?>" data-containerid="<?php echo esc_attr($containerid);?>" class="re_ajax_pagination_btn def_btn"><?php esc_html_e('Show next', 'rehub-theme') ?></span></div>      
		<?php endif ;?>

	</div>
	<div class="clearfix"></div>
<?php endif; wp_reset_query(); ?>

<?php 
$output = ob_get_contents();
ob_end_clean();
return $output;
}
}

//////////////////////////////////////////////////////////////////
// GRID LOOP MASONRY
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_grid_loop_mod_shortcode') ) {
function wpsm_grid_loop_mod_shortcode( $atts, $content = null ) {
$build_args =shortcode_atts(array(
	'data_source' => 'cat', //Filters start
	'cat' => '',
	'tag' => '',
	'cat_exclude' => '',
	'tag_exclude' => '',	
	'ids' => '',	
	'orderby' => '',
	'order' => 'DESC',	
	'meta_key' => '',
	'show' => 12,
	'user_id' => '',
	'offset' => '',
	'show_date' => '',	
	'post_type' => '',
	'tax_name' => '',
	'tax_slug' => '',
	'tax_slug_exclude' => '',
	'post_formats' => '',
	'badge_label'=> '1',	
	'enable_pagination' => '',
	'price_range' => '',		
	'show_coupons_only' =>'', //Filters end
	'columns' => '4_col',
	'aff_link' => '',
	'filterpanel' => '',
	'filterheading' => '',
	'taxdrop' => '',
	'taxdroplabel' => '',
	'taxdropids' => '',	
), $atts, 'grid_loop_mod'); 
extract($build_args);       
if ($enable_pagination =='2'){
	$infinitescrollwrap = ' re_aj_pag_auto_wrap';
}     
elseif ($enable_pagination =='3') {
	$infinitescrollwrap = ' re_aj_pag_clk_wrap';
} 
else {
	$infinitescrollwrap = '';
}  
$containerid = 'rh_fltmasongrid_' . mt_rand();    
$ajaxoffset = (int)$show + (int)$offset;  
$additional_vars = array();
$additional_vars['columns'] = $columns; 
$additional_vars['aff_link'] = $aff_link;   
ob_start(); 
?>
<?php rehub_vc_filterpanel_render($filterpanel, $containerid, $taxdrop, $taxdroplabel, $taxdropids, $filterheading);?>
<?php
	global $wp_query; 
	$argsfilter = new WPSM_Postfilters($build_args);
	$args = $argsfilter->extract_filters();

    $args = apply_filters('rh_module_args_query', $args);
    $wp_query = new WP_Query($args);
    do_action('rh_after_module_args_query', $wp_query);

?>
<?php if ($columns =='2_col') : ?>
	<?php $columns = ' col_wrap_two';?>
<?php elseif ($columns =='3_col') : ?>
	<?php $columns = ' col_wrap_three';?> 
<?php elseif ($columns =='4_col') : ?>
	<?php $columns = ' col_wrap_fourth';?>
<?php elseif ($columns =='5_col') : ?>
	<?php $columns = ' col_wrap_fifth';?>
<?php else :?>	
	<?php $columns = ' col_wrap_two';?>
<?php endif ;?>

<?php if ( $wp_query->have_posts() ) : ?>
	<?php 
		if(!empty($args['paged'])){unset($args['paged']);}
		$jsonargs = json_encode($args);
		$json_innerargs = json_encode($additional_vars);
	?> 
	<?php echo rh_generate_incss('masonry');?>
	<div class="masonry_grid_fullwidth<?php echo ''.$columns;?> <?php echo ''.$infinitescrollwrap;?>" data-filterargs='<?php echo ''.$jsonargs.'';?>' data-template="query_type3" id="<?php echo esc_attr($containerid);?>" data-innerargs='<?php echo ''.$json_innerargs.'';?>'>
			<?php while ( $wp_query->have_posts() ) : $wp_query->the_post();  ?>	
				<?php include(rh_locate_template('inc/parts/query_type3.php')); ?>
			<?php endwhile; ?>

			<?php if ($enable_pagination == '2' || $enable_pagination == '3' ) :?> 
				<?php wp_enqueue_script('rhajaxpagination');?>
			    <div class="re_ajax_pagination"><span data-offset="<?php echo esc_attr($ajaxoffset);?>" data-containerid="<?php echo esc_attr($containerid);?>" class="re_ajax_pagination_btn def_btn"><?php esc_html_e('Show next', 'rehub-theme') ?></span></div>      
			<?php endif ;?>
		<?php if ($enable_pagination == '1') :?>
		    <div class="pagination"><?php rehub_pagination();?></div>
		<?php endif ;?>		
	</div>
	<div class="clearfix"></div>
<?php endif; wp_reset_query(); ?>

<?php 
$output = ob_get_contents();
ob_end_clean();
return $output;
}
}

//////////////////////////////////////////////////////////////////
// NEWS TICKER
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_news_ticker_shortcode') ) {
function wpsm_news_ticker_shortcode( $atts, $content = null ) {
extract(shortcode_atts(array(
	'label' => '',
	'catname' => '',
	'catslug' => 'category',
	'fetch' => '5',	
), $atts, 'wpsm_news_ticker'));                
ob_start(); 
?>
<?php if( !is_paged()) : ?>
<?php include(rh_locate_template('inc/parts/news_ticker.php')); ?>
<?php endif ; ?>

<?php 
$output = ob_get_contents();
ob_end_clean();
return $output;
}
}

//////////////////////////////////////////////////////////////////
// NEWS WITH THUMBNAILS
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_news_with_thumbs_mod_shortcode') ) {
function wpsm_news_with_thumbs_mod_shortcode( $atts, $content = null ) {
$build_args = shortcode_atts(array(
	'data_source' => 'cat', //Filters start
	'cat' => '',
	'tag' => '',
	'module_cats'=> '',
	'module_tags'=> '',
	'cat_exclude' => '',
	'tag_exclude' => '',	
	'ids' => '',	
	'orderby' => '',
	'order' => 'DESC',	
	'meta_key' => '',
	'user_id' => '',
	'show' => 1,
	'offset' => '',
	'show_date' => '',	
	'post_type' => '',
	'tax_name' => '',
	'tax_slug' => '',
	'tax_slug_exclude' => '',
	'post_formats' => '',
	'badge_label'=> '1',	
	'enable_pagination' => '',
	'price_range' => '',
	'filterpanel' => '',
	'filterheading' => '',
	'taxdrop' => '',
	'taxdroplabel' => '',
	'taxdropids' => '',	
	'show_coupons_only' =>'', //Filters end
	'secondtype'=>'1',
	'thirdtype'=> 'no'

), $atts, 'news_with_thumbs_mod'); 
extract($build_args); 
$containerid = 'rh_news_bl_' . mt_rand();  

if($secondtype == '1'){
	$show += 4;
}
elseif($secondtype == '2'){
	$show += 5;
}    
elseif($secondtype == '3'){
	$show += 2;
} 
$secstart = 2;
$secend = $show;
$thirdstart = $secend + 1;

if($thirdtype == '1' && $secondtype == '1'){
	$show += 4;
}
elseif($thirdtype == '1'){
	$show += 5;
}
elseif($thirdtype == '2' && $secondtype == '1'){
	$show += 5;
} 
elseif($thirdtype == '2'){
	$show += 6;
}    
elseif($thirdtype == '3'){
	$show += 2;
} 
$thirdend = $show;  
$build_args['show'] = $show;  
$additional_vars = array();
$additional_vars['show'] = $show; 
$additional_vars['secondtype'] = $secondtype;  
$additional_vars['thirdtype'] = $thirdtype; 
$additional_vars['secstart'] = $secstart; 
$additional_vars['secend'] = $secend; 
$additional_vars['thirdstart'] = $thirdstart; 
$additional_vars['thirdend'] = $thirdend;   
ob_start(); 
?>
<?php rehub_vc_filterpanel_render($filterpanel, $containerid, $taxdrop, $taxdroplabel, $taxdropids, $filterheading);?>
<?php
	global $wp_query;
	if($module_cats){
		$build_args['cat'] = $module_cats;
	} 
	if($module_tags){
		$build_args['tag'] = $module_tags;
	}
	if(is_array($cat)){
		$build_args['cat'] = implode(',',$cat);
	}
	if(is_array($cat_exclude)){
		$build_args['cat_exclude'] = implode(',',$cat_exclude);
	}
	if(is_array($tag_exclude)){
		$build_args['tag_exclude'] = implode(',',$tag_exclude);
	}
	if(is_array($tag)){
		$build_args['tag'] = implode(',',$tag);
	}
	$argsfilter = new WPSM_Postfilters($build_args);
	$args = $argsfilter->extract_filters();

    $args = apply_filters('rh_module_args_query', $args);
    $wp_query = new WP_Query($args);
    do_action('rh_after_module_args_query', $wp_query);
    $posts = $wp_query->posts;
    $foundposts = count($posts);
    if($thirdend > $foundposts) $thirdend = $foundposts;
    if($secend > $foundposts) {$secend = $foundposts;$thirdtype='no';}

?> 
<?php if( !is_paged()) : ?>

	<?php if ( $wp_query->have_posts() ) : ?>
		<?php 
			if(!empty($args['paged'])){unset($args['paged']);}
			$jsonargs = json_encode($args);
			$json_innerargs = json_encode($additional_vars);
		?> 
		<?php echo rh_generate_incss('newsblock');?>
		<div class="mobileblockdisplay rh_news_wrap rh-flex-columns<?php if($thirdtype=='no') echo ' rh_news_wrap_two';?>" data-filterargs='<?php echo ''.$jsonargs.'';?>' data-template="newswrap" id="<?php echo esc_attr($containerid);?>" data-innerargs='<?php echo ''.$json_innerargs.'';?>'>
			<?php $i=1; while ( $wp_query->have_posts() ) : $wp_query->the_post();   ?>	
				<?php include(rh_locate_template('inc/parts/newswrap.php')); $i++ ?>
			<?php endwhile; ?>
		</div>
		<div class="clearfix"></div>
	<?php endif; wp_reset_query(); ?>

<?php endif ; ?>

<?php 
$output = ob_get_contents();
ob_end_clean();
return $output;
}
}


//////////////////////////////////////////////////////////////////
// TITLE MODULE
//////////////////////////////////////////////////////////////////
if( !function_exists('title_mod_shortcode') ) {
function title_mod_shortcode( $atts, $content = null ) {
extract(shortcode_atts(array(
	'title_name' => '',
	'title_color' => '',
	'title_background_color' => '',
	'title_size' => 'middle',	
	'title_bold' => '',
	'title_icon' => '',
	'title_pos' => 'left',
	'title_line' => 'under-title',
	'title_line_color' => '',
	'vc_link' => '',
	'title_url_title' =>'',
	'title_url_url' =>'',
	'title_class_add' => '',
), $atts, 'title_mod'));
ob_start(); 
?>

<?php if (!empty($title_name)) :?>
	<?php $rand_id = '-'.mt_rand(); ?>
	<?php $upper_echo = ($title_bold == 1) ? 'no_bold_title' : '';?>
	<?php $back_echo = ($title_background_color != '') ? 'background_title' : '';?>
	<?php $icon_echo = ($title_icon != '') ? '<i class="'.esc_attr($title_icon).'"></i> ' : '';?>
	<?php 
		$title_url_target = '_self';
		if ($vc_link !='' && $vc_link != '||') {
			$title_url = vc_build_link( $vc_link );
			$title_url_title = ($title_url !='') ? $title_url['title'] : '';
			$title_url_url = ($title_url !='') ? $title_url['url'] : '';
			$title_url_target = ($title_url !='') ? $title_url['target'] : '';
		}

		$add_link_echo = ($title_url_url !='' && $title_url_title !='') ? '<a href="'.esc_url($title_url_url).'" target="'.esc_attr($title_url_target).'" class="add-link-title">'.esc_attr($title_url_title).'</a>' : '';

	?>

	<div id="wpsm-title<?php echo ''.$rand_id;?>" class="wpsm-title position-relative flowhidden mb25 <?php echo esc_attr($title_size);?>-size-title <?php echo esc_attr($upper_echo);?> <?php echo esc_attr($title_pos);?>-align-title <?php echo esc_attr($title_line);?>-line <?php echo esc_attr($back_echo);?> <?php echo esc_html($title_class_add);?>">
		<?php if ($title_color !='' || $title_background_color !='' || $title_line_color !='') :?>
			<style scoped>
				<?php if ($title_color !='') :?>
					#wpsm-title<?php echo ''.$rand_id;?> h5{color:<?php echo esc_attr($title_color);?>;}
				<?php endif;?>
				<?php if ($title_background_color !='') :?>
					#wpsm-title<?php echo ''.$rand_id;?> h5{background-color:<?php echo esc_attr($title_background_color)?>;}
				<?php endif;?>	
				<?php if ($title_line_color !='') :?>
					#wpsm-title<?php echo ''.$rand_id;?>:after{background-color:<?php echo esc_attr($title_line_color)?>;}
				<?php endif;?>					
			</style>
		<?php endif;?>
		<h5><?php echo ''.$icon_echo; echo esc_attr($title_name);?></h5>
		<?php echo ''.$add_link_echo;?>
	</div>

<?php endif;?>

<?php 
$output = ob_get_contents();
ob_end_clean();
return $output;
}
}


//////////////////////////////////////////////////////////////////
// DEAL and POST CAROUSEL
//////////////////////////////////////////////////////////////////
if( !function_exists('deal_carousel_shortcode') ) {
function deal_carousel_shortcode( $atts, $content = null ) {
$build_args =shortcode_atts(array(
	'data_source' => 'cat', //Filters start
	'cat' => '',
	'tag' => '',
	'cat_exclude' => '',
	'tag_exclude' => '',	
	'ids' => '',
	'user_id' => '',	
	'orderby' => '',
	'order' => 'DESC',	
	'meta_key' => '',
	'show' => 8,
	'offset' => '',
	'show_date' => '',	
	'post_type' => '',
	'tax_name' => '',
	'tax_slug' => '',
	'tax_slug_exclude' => '',
	'post_formats' => '',
	'badge_label'=> '1',	
	'enable_pagination' => '',
	'price_range' => '',		
	'show_coupons_only' =>'', //Filters end
	'style' => '1',
	'aff_link' => '',
	'autorotate'=> '',
	'showrow'=> '5',
	'nav_dis' => '',		
), $atts, 'post_carousel_mod');  
extract($build_args);
$columns = $showrow.'_col';
ob_start(); 
?>
<?php wp_enqueue_style('rhcarousel'); wp_enqueue_script('owlcarousel'); wp_enqueue_script('owlinit'); ?>
<?php $autodata = ($autorotate) ? 'data-auto="1"' : 'data-auto="0"' ;?>
<?php $disable_nav = ($nav_dis) ? 'data-navdisable="1"' : '' ;?>
<?php $disable_nav_class = ($nav_dis) ? ' no-nav-carousel' : '' ;?>
<?php
	global $wp_query; 
	$argsfilter = new WPSM_Postfilters($build_args);
	$args = $argsfilter->extract_filters();
	$args['ignore_sticky_posts'] = 1;
?>
<?php if ($style == 2):?> 
	<?php echo rh_generate_incss('offergrid');?> 
    <div class="loading carousel-style-fullpost <?php echo ''.$disable_nav_class;?>">
        <div class="re_carousel eq_grid" data-showrow="<?php echo esc_attr($showrow);?>" <?php echo ''.$autodata;?> <?php echo ''.$disable_nav;?> data-laizy="0">
	        <?php $result_cat = array(); 
	            $deal_carousel = new WP_Query($args); 
	            if( $deal_carousel->have_posts() ) :
	            while($deal_carousel->have_posts()) : $deal_carousel->the_post();
	        	global $post;
	        ?>
				<?php include(rh_locate_template('inc/parts/compact_grid.php')); ?>
            <?php endwhile; endif; wp_reset_query(); ?>
        </div>
    </div>	
<?php elseif ($style == 'simple'):?>  
	<div class="post_carousel_block loading carousel-style-2<?php echo ''.$disable_nav_class;?>">
	    <div class="re_carousel" data-showrow="<?php echo esc_attr($showrow);?>" <?php echo ''.$autodata;?> <?php echo ''.$disable_nav;?> data-laizy="1">
	        <?php $result_cat = array();
	            $home_carousel = new WP_Query($args); 
	            if( $home_carousel->have_posts() ) :
	            while($home_carousel->have_posts()) : $home_carousel->the_post();
	        ?>
			<?php 
			if ($aff_link == '1') {
			    $link = rehub_create_affiliate_link ();
			    $target = ' rel="nofollow" target="_blank"';
			}
			else {
			    $link = get_the_permalink();
			    $target = '';  
			}
			?> 
	        <?php 
	        $showimg = new WPSM_image_resizer();
	        $showimg->use_thumb = true;
	        $showimg->no_thumb = get_template_directory_uri() . '/images/default/noimage_336_220.png';
	        $showimg->width = '336';
	        $showimg->height = '220';
	        $showimg->crop = true;
	        $showimg->lazy = false;                                    
	        ?>
	        <?php 	
	        if ('post' == get_post_type($home_carousel->ID)) {
	            $category = get_the_category();
				$category_id = $category[0]->term_id; 
				$category_echo = $category_id;                  	
	        }
	        else {$category_echo = '';  }
	        ?>
	        <div class="carousel-item tabcat-<?php echo ''.$category_echo;?>">
	            <figure>
	                <a href="<?php echo ''.$link;?>"<?php echo ''.$target;?>>
	                	<img class="owl-lazy" data-src="<?php echo ''.$showimg->get_resized_url();?>" alt="<?php the_title_attribute(); ?>">
	                </a>                                           
	            </figure> 
	    		<div class="text-oncarousel">
	        		<h3><a href="<?php echo ''.$link;?>"<?php echo ''.$target;?>><?php the_title();?></a></h3>
	            	<div class="post-meta"><?php if ('post' == get_post_type($home_carousel->ID)) {meta_small( false, $category_id, false, false );} ?></div>	                        	
	            	<?php rehub_create_btn('', 'price');?>
	            	<?php do_action( 'rehub_after_uni_carousel_text' ); ?>
	        	</div>                                          
	        </div>
	        <?php endwhile; endif; wp_reset_query(); ?>
	    </div>
	</div>	    
<?php else:?>
    <div class="post_carousel_block loading carousel-style-3 <?php echo ''.$disable_nav_class;?>">
        <div class="re_carousel" data-showrow="3" data-laizy="1" data-fullrow="2" <?php echo ''.$autodata;?> <?php echo ''.$disable_nav;?>>
	        <?php $result_cat = array(); 
	            $deal_carousel = new WP_Query($args); 
	            if( $deal_carousel->have_posts() ) :
	            while($deal_carousel->have_posts()) : $deal_carousel->the_post();
	        	global $post;
	        ?>
			<?php 
			if ($aff_link == '1') {
			    $link = rehub_create_affiliate_link ();
			    $target = ' rel="nofollow" target="_blank"';
			}
			else {
			    $link = get_the_permalink();
			    $target = '';  
			}
			?> 
            <?php 
            $showimg = new WPSM_image_resizer();
            $showimg->use_thumb = true;
            $showimg->height = '120';
            $showimg->width = '180';
            $showimg->crop = true;
            $showimg->lazy = false;
        	$showimg->no_thumb = get_template_directory_uri() . '/images/default/noimage_200_140.png';                                                
            ?>
            <?php   
            if ('post' == get_post_type($post->ID)) {
                $category = get_the_category();
                $category_id = $category[0]->term_id; 
                $category_echo = $category_id;                      
            }
            else {$category_echo = '';  }
            ?>
            <div class="carouselhor-item">
                <div class="l-part-car">
                    <figure>
                        <a href="<?php echo ''.$link;?>"<?php echo ''.$target;?>>
                            <img class="owl-lazy" height="120" width="180" data-src="<?php echo ''.$showimg->get_resized_url();?>" alt="<?php the_title_attribute(); ?>">
                        </a>                                           
                    </figure> 
                </div>
                <div class="r-part-car">
                    <?php echo getHotIconfire($post->ID);?><?php echo getHotLikeTitle($post->ID);?>
                    <h2><a href="<?php echo ''.$link;?>"<?php echo ''.$target;?>><?php echo wp_trim_words( get_the_title($post->ID), 8, '...' );?></a></h2>
                    <div class="post-meta"><?php if ('post' == get_post_type($post->ID)) {meta_small( false, $category_id, false, false );} ?></div>                                
                    <?php rehub_create_price_for_list($post->ID);?>
                    <?php do_action( 'rehub_after_recash_carousel_text' ); ?>
                </div>                                           
            </div>
            <?php endwhile; endif; wp_reset_query(); ?>
        </div>
    </div>	
<?php endif;?>    

<?php 
$output = ob_get_contents();
ob_end_clean();
return $output;
}
}


//////////////////////////////////////////////////////////////////
// WOO CAROUSEL
//////////////////////////////////////////////////////////////////
if( !function_exists('woo_mod_shortcode') ) {
function woo_mod_shortcode( $atts, $content = null ) {
$build_args = shortcode_atts(array(
	'data_source' => 'cat',//Filters start
	'cat' => '',
	'tag' => '',
	'ids' => '',	
	'orderby' => '',
	'order' => 'DESC',	
	'meta_key'=>'',
	'show' => 8,	
	'show_coupons_only' => '',
	'user_id' => '',	
	'type' => 'latest',	
	'tax_name'=>'',
	'tax_slug'=>'',	
	'tax_slug_exclude'=>'',	
	'enable_pagination' => '', //end woo filters
	'autorotate'=> '',
	'showrow'=> '5',
	'aff_link' => '',
	'carouseltype' =>'columned',
	'soldout' => '',
	'price_range' => '',				
), $atts, 'woo_mod');
extract($build_args); 
ob_start(); 
?>
<?php wp_enqueue_style('rhcarousel');wp_enqueue_script('owlcarousel'); wp_enqueue_script('owlinit'); ?>
<?php $autodata = ($autorotate) ? 'data-auto="1"' : 'data-auto="0"' ;?>
<?php $full_row_data = ($carouseltype == 'compact') ? 'data-fullrow="2"' : 'data-fullrow="1"';?>
<?php if ($carouseltype == 'columned') {
	$columnclass = ' column_woo products carouselpost';
}
elseif($carouseltype == 'simple'){
	$columnclass = ' grid_woo products carouselpost';
}
elseif($carouseltype == 'compact'){
	echo rh_generate_incss('offergrid');
	$columnclass = ' eq_grid pt5 products carouselpost';
}
elseif($carouseltype == 'review'){
	$columnclass = ' woogridrev products carouselpost';
}
elseif($carouseltype == 'digital'){
	$columnclass = ' woogridrev woogriddigi products carouselpost';
}
else{
	$columnclass = '';
}
$columns = $showrow.'_col';
?>

<div class="carousel-style-fullpost woo_carousel_block loading woocommerce showrow-<?php echo ''.$showrow;?>">

    <div class="re_carousel<?php echo ''.$columnclass;?>" data-showrow="<?php echo ''.$showrow;?>" <?php echo ''.$autodata;?> <?php echo ''.$full_row_data;?> data-laizy="1" data-loopdisable="1">
		<?php		 
			$argsfilter = new WPSM_Woohelper($build_args);
			$args = $argsfilter->extract_filters();
		?>
        <?php $products = new WP_Query( $args );                    
            if ( $products->have_posts() ) : ?>                      
                <?php while ( $products->have_posts() ) : $products->the_post(); global $product; ?> 
                	<?php if($carouseltype == 'columned') :?>
                		<?php include(rh_locate_template('inc/parts/woocolumnpart.php')); ?>                	
                	<?php elseif($carouseltype == 'simple'):?>
                		<?php include(rh_locate_template('inc/parts/woogridpart.php')); ?> 
                	<?php elseif($carouseltype == 'compact'):?>
                		<?php include(rh_locate_template('inc/parts/woogridcompact.php')); ?> 
					<?php elseif($carouseltype == 'review'):?>
			  			<?php include(rh_locate_template('inc/parts/woogridrev.php')); ?>
					<?php elseif($carouseltype == 'digital'):?>
			  			<?php include(rh_locate_template('inc/parts/woogriddigi.php')); ?>
					<?php elseif($carouseltype == 'dealwhite'):?>
				  		<?php include(rh_locate_template('inc/parts/woodealgrid.php')); ?>
					<?php elseif($carouseltype == 'dealdark'):?>
				  		<?php include(rh_locate_template('inc/parts/woodealgriddark.php')); ?>
                	<?php else:?>                	                		
                	    <?php include(rh_locate_template('inc/parts/woocompactcarousel.php')); ?>
                	<?php endif;?>

        <?php endwhile; endif; wp_reset_query(); ?>
    </div>
</div>     

<?php 
$output = ob_get_contents();
ob_end_clean();
return $output;
}
}

//////////////////////////////////////////////////////////////////
// List of recent posts
//////////////////////////////////////////////////////////////////
if( !function_exists('recent_posts_function') ) {
function recent_posts_function( $atts, $content = null ) {
$build_args =shortcode_atts(array(
	'data_source' => 'cat', //Filters start
	'cat' => '',
	'tag' => '',
	'cat_exclude' => '',
	'tag_exclude' => '',	
	'ids' => '',	
	'orderby' => '',
	'order' => 'DESC',	
	'meta_key' => '',
	'user_id' => '',
	'show' => 8,
	'offset' => '',
	'show_date' => '',	
	'post_type' => '',
	'tax_name' => '',
	'tax_slug' => '',
	'tax_slug_exclude' => '',
	'post_formats' => '',
	'badge_label'=> '1',	
	'enable_pagination' => '',	
	'price_range' => '',
	'searchtitle' => '',
	'show_coupons_only' =>'', //Filters end
	'nometa' =>'',
	'image' =>'',
	'center' => '',
	'centertext' => '',
	'filterpanel' => '',
	'filterheading' => '',
	'taxdrop' => '',
	'taxdroplabel' => '',
	'taxdropids' => '',	
	'border' => '',
	'columns' => '',
	'excerpt' => '',
	'priceenable' => '',
	'compareenable' => '',
	'hotenable' => '',
	'imageheight' => '',
	'imagewidth' => '',
	'fullsizeimage' => '',
	'aff_link'=> '',
	'smartscrolllist'=> ''
), $atts, 'wpsm_recent_posts_list'); 
extract($build_args); 
$containerid = 'rh_simplepostid_' . mt_rand();
$center_class=($centertext) ? ' text-center rh-list-center': ''; 
$ajaxoffset = (int)$show + (int)$offset;
$additional_vars = array();
$additional_vars['nometa'] = $nometa;
$additional_vars['image'] = $image;
$additional_vars['border'] = $border;
$additional_vars['excerpt'] = $excerpt;
$additional_vars['priceenable'] = $priceenable;
$additional_vars['compareenable'] = $compareenable;
$additional_vars['hotenable'] = $hotenable;
$additional_vars['imageheight'] = $imageheight;
$additional_vars['imagewidth'] = $imagewidth;
$additional_vars['center'] = $center;
$additional_vars['aff_link'] = $aff_link;
$additional_vars['centertext'] = $centertext;
$additional_vars['fullsizeimage'] = $fullsizeimage;
if ($columns == '2'){
    $col_wrap = ' col_wrap_two rh-flex-eq-height';
}
elseif ($columns == '3'){
    $col_wrap = ' col_wrap_three rh-flex-eq-height';
}
elseif ($columns == '4'){
    $col_wrap = ' col_wrap_fourth rh-flex-eq-height';
}  
elseif ($columns == '5'){
    $col_wrap = ' col_wrap_fifth rh-flex-eq-height';
} 
elseif ($columns == '6'){
    $col_wrap = ' col_wrap_six rh-flex-eq-height';
} 
else{
	$col_wrap = '';
} 
if ($enable_pagination =='2'){
	$infinitescrollwrap = ' re_aj_pag_auto_wrap';
}     
elseif ($enable_pagination =='3') {
	$infinitescrollwrap = ' re_aj_pag_clk_wrap';
} 
else {
	$infinitescrollwrap = '';
}              
ob_start(); 
?>
<?php rehub_vc_filterpanel_render($filterpanel, $containerid, $taxdrop, $taxdroplabel, $taxdropids, $filterheading);?>
<?php
	global $wp_query; 
	$argsfilter = new WPSM_Postfilters($build_args);
	$args = $argsfilter->extract_filters();
	$wp_query = new WP_Query($args);
?>
	<?php 
		if(!empty($args['paged'])){unset($args['paged']);}
		$jsonargs = json_encode($args);
		$json_innerargs = json_encode($additional_vars);
	?>
	<?php if($smartscrolllist):?><div class="smart-scroll-desktop"><?php endif;?>
	<div class="wpsm_recent_posts_list mb0 <?php echo ''.$center_class.$col_wrap.$infinitescrollwrap;?>" data-filterargs='<?php echo ''.$jsonargs.'';?>' data-template="simplepostlist" id="<?php echo esc_attr($containerid);?>" data-innerargs='<?php echo ''.$json_innerargs.'';?>'>
<?php if ( $wp_query->have_posts() ) : ?>

		<?php while ( $wp_query->have_posts() ) : $wp_query->the_post();  ?>	
			<?php include(rh_locate_template('inc/parts/simplepostlist.php')); ?>
		<?php endwhile; ?>

		<?php if ($enable_pagination == '1') :?>
			<div class="clearfix"></div>
		    <div class="pagination"><?php rehub_pagination();?></div>
		<?php elseif ($enable_pagination == '2' || $enable_pagination == '3' ) :?>
			<?php wp_enqueue_script('rhajaxpagination');?> 
		    <div class="re_ajax_pagination"><span data-offset="<?php echo esc_attr($ajaxoffset);?>" data-containerid="<?php echo esc_attr($containerid);?>" class="re_ajax_pagination_btn def_btn"><?php esc_html_e('Show next', 'rehub-theme') ?></span></div>      
		<?php endif ;?>
<?php endif; wp_reset_query(); ?>
	</div>
	<?php if($smartscrolllist):?></div><?php endif;?>
	<div class="clearfix"></div>
<?php 
$output = ob_get_contents();
ob_end_clean();
return $output;
}

}

//////////////////////////////////////////////////////////////////
// 3 COLUMN FULL WIDTH ROW
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_three_col_posts_function') ) {
function wpsm_three_col_posts_function( $atts, $content = null ) {
$build_args =shortcode_atts(array(
	'data_source' => 'cat', //Filters start
	'cat' => '',
	'tag' => '',
	'cat_exclude' => '',
	'tag_exclude' => '',	
	'ids' => '',	
	'orderby' => '',
	'order' => 'DESC',	
	'meta_key' => '',
	'show' => 3,
	'user_id' => '',
	'offset' => '',
	'show_date' => '',	
	'post_type' => '',
	'tax_name' => '',
	'tax_slug' => '',
	'tax_slug_exclude' => '',
	'post_formats' => '',
	'badge_label'=> '1',	
	'enable_pagination' => '',	
	'show_coupons_only' =>'', //Filters end
), $atts, 'wpsm_three_col_posts'); 
extract($build_args); 
$rand_id = mt_rand().time(); 
$i = 0;           
ob_start(); 
?>

<?php
	global $wp_query; 
	$argsfilter = new WPSM_Postfilters($build_args);
	$args = $argsfilter->extract_filters();
	$args['ignore_sticky_posts'] = true;
	$wp_query = new WP_Query($args);
?>
<div class="rh-flex-columns wpsm_three_col_posts smart-scroll-desktop one-col-mob scroll-on-mob-nomargin" id="w_t_c_<?php echo ''.$rand_id;?>">
<?php  echo rh_generate_incss('threecol');?>
<?php if($wp_query->have_posts()): while($wp_query->have_posts()): $wp_query->the_post(); $i++ ?>	
	<div class="col-item news_in_thumb numb_<?php echo (int)$i;?>">
		<figure class="mb20 position-relative">				   			            	
		    <a href="<?php the_permalink();?>">
                <?php 
                $showimg = new WPSM_image_resizer();
                $showimg->use_thumb = true;
                $showimg->width = '400';
                $showimg->height = '224';
                $showimg->crop = true;
                $showimg->show_resized_image();                                    
                ?>		    

		    </a>
		    <div class="text_in_thumb pt0 pr20 pb10 pl20 csstransall">
		    	<h2><a href="<?php the_permalink();?>"><?php the_title();?></a></h2>
		    	<div class="post-meta"> <?php meta_small( true, false, true ); ?> </div>                            
		    </div>					
	    </figure>			    
    </div>
<?php endwhile; endif; ?>
</div>
<?php  wp_reset_query(); ?>

<?php 
$output = ob_get_contents();
ob_end_clean();
return $output;
}
}

//////////////////////////////////////////////////////////////////
// Offer Box
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_offerbox_shortcode') ) {
function wpsm_offerbox_shortcode( $atts, $content = null ) {
	
	extract(shortcode_atts(array(
		'title' => '',
		'description' => '',
		'price' => '',
		'price_old' => '',
		'offer_coupon' => '',
		'offer_coupon_date' => '',
		'offer_coupon_mask' => '',
		'offer_coupon_mask_text' => '',
		'button_text' => '',
		'button_link' => '',
		'logo_thumb' => '',
		'logo_image_id' => '',
	), $atts));

	if ($offer_coupon_mask_text =='') {
		if(rehub_option('rehub_mask_text') !=''){
			$offer_coupon_mask_text = rehub_option('rehub_mask_text');
		}
		else {
			$offer_coupon_mask_text = esc_html__('Reveal', 'rehub-theme');
		}
	}

	if ($button_text =='') {
		if(rehub_option('rehub_btn_text') !=''){
			$button_text = rehub_option('rehub_btn_text');
		}
		else {
			$button_text = esc_html__('Buy this item', 'rehub-theme');
		}
	} 

	$coupon_style = '';
	$title = (!empty($atts['title'])) ? $atts['title'] : '';
	if(!empty($offer_coupon_date)) :
		$timestamp1 = strtotime($offer_coupon_date);
		if(strpos($offer_coupon_date, ':') ===false){
			$timestamp1 += 86399;
		}
		$seconds = $timestamp1 - (int)current_time('timestamp',0);
		$days = floor($seconds / 86400);
		$seconds %= 86400;
		if ($days > 0) {
			$coupon_text = $days.' '.__('days left', 'rehub-theme');
			$coupon_style = '';
		}
		elseif ($days == 0){
			$coupon_text = esc_html__('Last day', 'rehub-theme');
			$coupon_style = '';
		}
		else {
			$coupon_text = esc_html__('Expired', 'rehub-theme');
			$coupon_style = 'expired_coupon';
		}			
	endif;
	$coupon_enabled_style = (!empty($atts['offer_coupon_mask'])) ? ' reveal_enabled '.$coupon_style.'' : ' '.$coupon_style.'';	
		
	$out = '<div class="rehub_bordered_block rh_listitem'.$coupon_enabled_style.'"><div class="rh-flex-center-align rh-flex-justify-center mobileblockdisplay">';

	if(isset($atts['image_id']) && $atts['image_id']):
		$offer_thumb = wp_get_attachment_url($atts['image_id']);
		$show_offer_thumb = new WPSM_image_resizer();
        $show_offer_thumb->src = $offer_thumb; 
        $show_offer_thumb->width = '90';
        $show_offer_thumb->height = '90';
        $show_offer_thumb->crop = false;
        $checklink = (isset($atts['button_link'])) ? esc_url($atts['button_link']) : '';
		$out .= '<div class="rh_listcolumn rh_listcolumn_image text-center"><a href="'.$checklink.'" target="_blank" rel="nofollow"><img src="'.$show_offer_thumb->get_resized_url().'" alt="'.$title.'" /></a></div>';
	elseif(isset($atts['thumb']) && $atts['thumb']):
		$offer_thumb = $atts['thumb'];
		$show_offer_thumb = new WPSM_image_resizer();
        $show_offer_thumb->src = $offer_thumb; 
        $show_offer_thumb->width = '90';
        $show_offer_thumb->height = '90';
        $show_offer_thumb->crop = false;
		$out .= '<div class="rh_listcolumn rh_listcolumn_image text-center"><img src="'.$show_offer_thumb->get_resized_url().'" alt="'.$title.'" /></div>';           		
	endif;	
	$out .= '<div class="rh_listcolumn rh-flex-grow1 rh_listcolumn_text">';
	if($title):
		$out .= '<div class="font120 fontbold rehub-main-font lineheight20">'.$title.'</div>';
	endif;

	if(isset($atts['description']) && $atts['description']):
		$out.= '<div class="mt10 greycolor font90 lineheight20">'.$atts['description'].'</div>';
	endif;
	$out .= '</div>';

	$out .= '<div class="rh_listcolumn rh_listcolumn_price text-center">';
		if(isset($atts['price']) && $atts['price']):
	    	$out .= '<span class="rh_price_wrapper"><span class="price_count rehub-main-color rehub-btn-font fontbold"><ins>'.$atts['price'].'</ins> ';
	    	if(isset($atts['price_old']) && $atts['price_old']):
	    		$out .= '<del class="lightgreycolor fontnormal">'.$atts['price_old'].'</del>';
	    	endif;
	    	$out .= '</span></span>';
		endif;
		if(isset($atts['logo_image_id']) && $atts['logo_image_id']):
			$logo_thumb = wp_get_attachment_url($atts['logo_image_id']);
			$show_logo_thumb = new WPSM_image_resizer();
        	$show_logo_thumb->src = $logo_thumb; 
        	$show_logo_thumb->width = '50';
			$out .= '<div class="brand_logo_small"><img src="'.$show_logo_thumb->get_resized_url().'" alt="image" /></div>';
		elseif(isset($atts['logo_thumb']) && $atts['logo_thumb']):
			$logo_thumb = $atts['logo_thumb'];
			$show_logo_thumb = new WPSM_image_resizer();
        	$show_logo_thumb->src = $logo_thumb; 
        	$show_logo_thumb->width = '50';
			$out .= '<div class="brand_logo_small"><img src="'.$show_logo_thumb->get_resized_url().'" alt="image" /></div>';         		
		endif;			
	$out .= '</div>';	

	$out .= '<div class="text-right-align rh_listcolumn_btn"><div class="priced_block clearfix">';
		
		if(isset($atts['button_link']) && $atts['button_link']):
		    $out .= '<div><a href="'.esc_url($atts['button_link']).'" class="re_track_btn btn_offer_block" target="_blank" rel="nofollow">'.$button_text.'</a></div>';
		endif;

		if(!empty($atts['offer_coupon'])) :
			wp_enqueue_script('zeroclipboard');
			if (empty($atts['offer_coupon_mask'])) :
                $out .= '<div class="mt15 rehub_offer_coupon not_masked_coupon ';
            		if(!empty($atts['offer_coupon_date'])) :
            			$out .= $coupon_style;
            		endif;
            	$out .= '" data-clipboard-text="'.$atts['offer_coupon'].'"><i class="rhicon rhi-scissors fa-rotate-180"></i><span class="coupon_text">'.$atts['offer_coupon'].'</span></div>';
            else :
            	wp_enqueue_script('affegg_coupons');
                $out .= '<div class="coupon_btn re_track_btn btn_offer_block rehub_offer_coupon masked_coupon ';
            		if(!empty($atts['offer_coupon_date'])) :
            			$out .= $coupon_style;
            		endif;
           		$out .= '" data-clipboard-text="'.rawurlencode(esc_html($atts['offer_coupon'])).'" data-codetext="'.rawurlencode(esc_html($atts['offer_coupon'])).'" data-dest="'.esc_url($atts['button_link']).'">'.$offer_coupon_mask_text.'</div>';
			endif;	
		endif;
        if(!empty($atts['offer_coupon_date'])) :
        	$out .='<div class="time_offer">'.$coupon_text.'</div>';
        endif;					
	$out .= '</div></div>';


	$out .= '</div></div><div class="clearfix"></div>';
    return $out;
}

}


//////////////////////////////////////////////////////////////////
// VIDEO PLAYLIST
//////////////////////////////////////////////////////////////////
if( !function_exists('video_mod_function') ) {
function video_mod_function( $atts, $content = null ) {
$build_args =shortcode_atts(array(
	'videolinks' => '',
	'playlist_auto_play' => '0',
	'playlist_host' => 'youtube',
	'playlist_width' => 'full',
	'playlist_type' => 'playlist',
	'key' => ''
), $atts, 'video_mod'); 
extract($build_args); 
$rand_id = mt_rand().time();      

ob_start(); 
?>

<?php if ($videolinks) :?>

	<?php if ($playlist_type == 'slider') :?>
		<?php $idshosts = WPSM_video_class::parse_videoid_from_urls($videolinks, 'arrayhost') ;?>	

		<?php  wp_enqueue_script('flexslider');wp_enqueue_script('flexinit');wp_enqueue_style('flexslider'); ?>
		<div class="gallery_video_wrap">
			<div class="flexslider post_slider media_slider gallery_top_slider loading"> 
			<ul class="slides">     <script src="//a.vimeocdn.com/js/froogaloop2.min.js"></script>
			<?php if (!empty ($idshosts['youtube']) && $playlist_host == 'youtube') :?>
				<?php $videoarraytube = WPSM_video_class::get_video_data($idshosts['youtube'], 'youtube', $key); ?>
				<?php foreach ($videoarraytube as $video_id=>$video_data):?>
					<li data-thumb="<?php echo esc_url($video_data['thumb']) ?>" class="play3">
					    <?php echo WPSM_video_class::embed_video_from_id($video_id, 'youtube');?>
					</li>
				<?php endforeach;?>
			<?php elseif (!empty ($idshosts['vimeo']) && $playlist_host == 'vimeo') :?>
				<?php $videoarrayvimeo = WPSM_video_class::get_video_data($idshosts['vimeo'], 'vimeo'); ?>
				<?php foreach ($videoarrayvimeo as $video_id=>$video_data):?>
					<li data-thumb="<?php echo esc_url($video_data['thumb']) ?>" class="play3">
					    <?php echo WPSM_video_class::embed_video_from_id($video_id, 'vimeo');?>
					</li>
				<?php endforeach;?>
			<?php endif;?>
			</ul>
			</div>
		</div>			

	<?php else :?>

		<?php $idshosts = WPSM_video_class::parse_videoid_from_urls($videolinks, 'arrayhost') ;?>
		<?php if (!empty ($idshosts['youtube']) && $playlist_host == 'youtube') :?>
			<?php echo WPSM_video_class::render_playlist( $atts, 'youtube', $key ); ?>
		<?php elseif (!empty ($idshosts['vimeo']) && $playlist_host == 'vimeo') :?>
			<?php echo WPSM_video_class::render_playlist( $atts, 'vimeo', $key ); ?>
		<?php endif;?>

	<?php endif; ?>

<?php endif; ?>


<?php 
$output = ob_get_contents();
ob_end_clean();
return $output;
}

}


//////////////////////////////////////////////////////////////////
// FEATURED AREA
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_featured_function') ) {
function wpsm_featured_function( $atts, $content = null ) {
$build_args =shortcode_atts(array(
	'data_source' => 'cat', //Filters start
	'cat' => '',
	'tag' => '',
	'cat_exclude' => '',
	'tag_exclude' => '',	
	'ids' => '',	
	'orderby' => '',
	'order' => 'DESC',	
	'meta_key' => '',
	'show' => 5,
	'offset' => '',
	'show_date' => '',	
	'post_type' => '',
	'tax_name' => '',
	'tax_slug' => '',
	'tax_slug_exclude' => '',
	'post_formats' => '',
	'badge_label'=> '1',	
	'enable_pagination' => '',
	'price_range' => '',	
	'show_coupons_only' =>'', //Filters end
	'feat_type'=>'3',
	'dis_excerpt' =>'',
	'bottom_style' =>'',
	'custom_height'=>'',
), $atts, 'wpsm_featured'); 
extract($build_args); 
$rand_id = 'feat_area'.mt_rand();            
ob_start(); 
?>
<?php if( !is_paged()) : ?>
<?php if ($feat_type=='1' || $feat_type == '2') {wp_enqueue_style('flexslider'); wp_enqueue_script('flexslider');wp_enqueue_script('flexinit');} ;?>
<?php
	global $wp_query; 
	$argsfilter = new WPSM_Postfilters($build_args);
	$args = $argsfilter->extract_filters();
	$args['ignore_sticky_posts'] = 1;
	$argsleft = $args;
	if ($feat_type=='1' && !empty($ids)) {
		$idscount = array_map( 'trim', explode( ",", $ids ) );
		$idscount = count($idscount);
		$argsleft['showposts'] = $idscount - 2;
	}
	if ($feat_type=='3') {
		$argsleft['showposts'] = 5;
	}
	$wp_query = new WP_Query($argsleft);
?>

<div class="wpsm_featured_wrap flowhidden mb35 wpsm_featured_<?php echo esc_attr($feat_type);?>" id="<?php echo ''.$rand_id;?>">
<?php if($feat_type =='2') : //Second type - featured full width slider?>
	<?php if($custom_height) :?>
    	<style scoped>
    		@media (min-width: 768px){
    			#<?php echo ''.$rand_id;?> .main_slider.full_width_slider.flexslider .slides .slide{height: <?php echo (int)$custom_height;?>px; line-height: <?php echo (int)$custom_height;?>px;} 
    			#<?php echo ''.$rand_id;?> .main_slider.full_width_slider.flexslider{height:<?php echo (int)$custom_height;?>px}
    		}        		
    	</style>
	<?php endif ;?>
	<div class="flexslider main_slider loading full_width_slider<?php if ($bottom_style =='1') :?> bottom_style_slider<?php endif ?>">
		<i class="rhicon rhi-spinner fa-pulse"></i>
		<ul class="slides">	
		<?php if($wp_query->have_posts()): while($wp_query->have_posts()): $wp_query->the_post(); global $post; ?>
		<?php 
	  		$image_id = get_post_thumbnail_id(get_the_ID());  
	  		$image_url = wp_get_attachment_image_src($image_id,'full');
			$image_url = $image_url[0];
			if (function_exists('_nelioefi_url')){
				$image_nelio_url = get_post_meta( $post->ID, _nelioefi_url(), true );
				if (!empty($image_nelio_url)){
					$image_url = esc_url($image_nelio_url);
				}			
			}			
		?>	
			<li class="slide" style="background-image: url('<?php echo esc_url($image_url) ;?>');"> 
				<span class="pattern"></span>
				<a href="<?php the_permalink();?>" class="feat_overlay_link"></a>
		  		<div class="flex-overlay">
		    		<div class="post-meta">
		      			<div class="inner_meta mb5">    				
				            <?php 	
				            if ('post' == get_post_type($post->ID) && rehub_option('exclude_cat_meta') != 1) {
				                $category = get_the_category();
								$category_id = $category[0]->term_id;
								$category_link = get_category_link($category_id);
								$category_name = get_cat_name($category_id);
								$category_echo = '<span class="news_cat"><a href="'.esc_url( $category_link ).'" class="rh-label-string">'.$category_name.'</a></span>';                  	
				            	echo ''.$category_echo;
				            }
				            ?>       				
		      			</div>
		    		</div>
		    		<h2><a href="<?php the_permalink();?>"><?php the_title();?></a></h2>
		    		<?php if ($dis_excerpt !='1') :?><div class="hero-description"><p><?php kama_excerpt('maxchar=150'); ?></p></div><?php endif ;?>
		    		<?php if(rehub_option('disable_btn_offer_loop')!='1')  : ?><?php rehub_create_btn('yes') ;?><?php endif; ?>	            		
		    	</div>
		    	<?php if (rehub_option('exclude_comments_meta') == 0) : ?><?php comments_popup_link( 0, 1, '%', 'comment', ''); ?><?php endif ;?> 
			</li>
		<?php endwhile; endif; ?>
		<?php  wp_reset_query(); ?>
		</ul>
	</div>
<?php else: //Third type - featured grid ?>
	<?php echo rh_generate_incss('featgrid');?>
	<div class="featured_grid flowhidden">	
		<?php $col_number = 0; if($wp_query->have_posts()): while($wp_query->have_posts()): $wp_query->the_post(); global $post; $col_number ++; ?>
		<?php if ($col_number == 2) {echo '<div class=" smart-scroll-mobile one-col-mob scroll-on-mob-nomargin disabletabletspadding col-feat-50 rh-flex-columns rh-flex-space-between pl10">';}?>
			<?php 
		  		$image_id = get_post_thumbnail_id($post->ID);  
		  		if ($col_number == 1) {
		  			$image_url = wp_get_attachment_image_src($image_id,'large');
		  		}
		  		else {
		  			$image_url = wp_get_attachment_image_src($image_id,'mediumgrid');
		  		}	
				$image_url = (!empty($image_url[0])) ? $image_url[0] : '';			
			?>
			<div class="col-feat-grid col_item flowhidden rh-hovered-wrap item-<?php echo ''.$col_number;?>">
				<style scoped>
					#<?php echo ''.$rand_id;?> .item-<?php echo ''.$col_number;?>{
						background-image: url('<?php echo esc_url($image_url) ;?>');
					}
				</style>
				<a href="<?php the_permalink();?>" class="feat_overlay_link"></a> 
		  		<div class="feat-grid-overlay text_in_thumb pt0 pr20 pb10 pl20 csstransall">
	      			<div class="inner_meta mb5">    				
			            <?php 	
			            if ('post' == get_post_type($post->ID) && rehub_option('exclude_cat_meta') != 1) {
			                $category = get_the_category();
							$category_id = $category[0]->term_id;
							$category_link = get_category_link($category_id);
							$category_name = get_cat_name($category_id);
							$category_echo = '<span class="news_cat"><a href="'.esc_url( $category_link ).'" class="rh-label-string">'.$category_name.'</a></span>';                  	
			            	echo ''.$category_echo;
			            }
			            ?>       				
	      			</div>
		    		<h2 class="mt0"><a href="<?php the_permalink();?>"><?php the_title();?></a></h2>
		    		<?php if ($col_number == 1) {echo '<div class="post-meta">'; meta_small(true, false, true); echo'</div>';}?>	            		
		    	</div> 
			</div>
		<?php endwhile; echo '</div>'; endif; ?>
		<?php  wp_reset_query(); ?>
	</div>
<?php endif;?>
</div>
<?php endif;?>


<?php 
$output = ob_get_contents();
ob_end_clean();
return $output;
}
}


//////////////////////////////////////////////////////////////////
// SEARCH BLOCK
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_searchbox_function') ) {
	function wpsm_searchbox_function( $atts, $content = null ) {
	$build_args =shortcode_atts(array(
		'by' => 'post',
		'placeholder' => 'Search',
		'color' => 'btncolor',
		'enable_ajax' => '',
		'tax' => '',
		'catid' => '',
		'label'=> '',
		'enable_compare' => '',
		'aff_link' => ''
	), $atts, 'wpsm_searchbox'); 
	extract( $build_args ); 
	ob_start(); 
	?>
	<?php wp_enqueue_style( 'rhajaxsearch' );?>
	<div class="width-100p position-relative custom_search_box<?php if ($label):?> flat_style_form<?php endif;?>">
		<?php if ( $tax && $tax !='' ) { ?>
		<form role="search" id="rh-category-search">
			<style scoped>
				/* typehead */
				#rh-category-search .hide{display:none !important}
				#rh-category-search .show{display:inherit}
				#rh-category-search .tt-clear-search{position:absolute;color:#919191;font-size:130%;cursor:pointer;top:14px;right:12px;z-index: 2}
				#rh-category-search .tt-clear-search:hover{color:#000}
				#rh-category-search .tt-dropdown-menu{width:100%;margin-top:12px;padding:0;background-color:#fff;border:1px solid rgba(0,0,0,0.2);border-radius:0;box-shadow:0 2px 2px rgba(0,0,0,0.1)}
				#rh-category-search .tt-dropdown-menu:before{content:'';position:absolute;bottom:100%;left:50%;margin-left:-8px;width:0;height:0;border-bottom:8px solid #eee;border-right:8px solid transparent;border-left:8px solid transparent}
				#rh-category-search .tt-suggestion{color:#5e5e5e;cursor:pointer;border-bottom:1px solid #eaedf0;padding:6px 12px;line-height:24px}
				#rh-category-search .tt-suggestion:before{content: "\f054"; float: right; line-height: 24px; font-size: 14px; margin: 0 }
				#rh-category-search .tt-suggestion.tt-cursor{background-color:#f3f5f6}
				#rh-category-search .tt-suggestion p{margin:0}
				#rh-category-search .tt-suggestion .autocompleted{padding-left:36px}
				#rh-category-search .empty-message{padding:4px}				
			</style>
			<span class="tt-clear-search hide js-clear-search"><i class="rhicon rhi-times"></i></span>
			<input class="typeahead search-text-input" type="text" placeholder="<?php echo ''.$placeholder?>" autocomplete="off">
			 <i class="rhicon rhi-arrow-right inside-search"></i>
		</form> 
		<?php
		$tax_arr = array_map('trim', explode(",", $tax));
		$terms = get_terms(array(
			'taxonomy'=> $tax_arr,
			'hide_empty' => true,
		) );

		if ( is_wp_error( $terms ) ) {
			return;
		}
			
		foreach ( $terms as $term ) {
			$parsed_url = parse_url( get_term_link( $term ) );
			$term_path = isset($parsed_url['path']) ? $parsed_url['path'] : ''; 
			$term_arr = array( 
				'html_name' => $term_path,
				'long_name' => $term->name,
				'key_word' => $term->description
			);
			$cat_arr[] = $term_arr;
		}

			$cat_json = json_encode( $cat_arr );
			wp_enqueue_script( 'typehead' );
			
			if ( function_exists( 'wp_add_inline_script' ) )
				wp_add_inline_script( 'rehub', 'var typeahead_categories =' . $cat_json . ';' );	
		?>

		<?php } else { ?>
			<form  role="search" class="flowhidden" method="get" id="rh-custom-search-<?php echo mt_rand();?>" action="<?php echo home_url( '/' ); ?>">
			  <input type="text" name="s" placeholder="<?php echo ''.$placeholder?>" <?php if ($enable_ajax == '1') {echo 'class="re-ajax-search" autocomplete="off"';} ?> data-posttype="<?php echo ''.$by;?>" data-enable_compare="<?php echo ''.$enable_compare;?>" data-catid="<?php echo ''.$catid;?>" data-aff="<?php echo ''.$aff_link;?>">
			  <input type="hidden" name="post_type" value="<?php echo ''.$by?>" />
			  <?php if($by == 'product'):?>
			  	<input type="hidden" name="product_cat" value="<?php echo ''.$catid?>" />
			  <?php elseif($by == 'blog'):?>
			  	<input type="hidden" name="blog_category" value="<?php echo ''.$catid?>" />
			  <?php else:?>
			  	<input type="hidden" name="cat" value="<?php echo ''.$catid?>" />
			  <?php endif;?>
			  
			  <i class="rhicon rhi-arrow-right inside-search"></i>
				<?php 
				    if($color == 'main'){
				    	$colorclass = 'rehub-main-color-bg rehub-main-color-border';
				    }
				    elseif($color == 'secondary'){
				    	$colorclass = 'rehub-sec-color-bg rehub-sec-color-border';
				    }  
				    elseif($color == 'btncolor'){
				    	$colorclass = 'rehub_btn_color';
				    }      
				    else{
				    	$colorclass = $color;
				    } 
			    ?>						  
			  <button type="submit" class="wpsm-button <?php echo esc_attr($colorclass)?>"><?php if ($label):?><?php echo esc_attr($label);?><?php else:?><i class="rhicon rhi-search"></i><?php endif;?></button>
			</form>
			<?php if ($enable_ajax == '1') { echo '<div class="re-aj-search-wrap rhscrollthin"></div>'; wp_enqueue_script( 'rehubajaxsearch' ); } ?>
		<?php } ?>
	</div>
	<?php
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
	}
	
}


//////////////////////////////////////////////////////////////////
// VERSUS BLOCK
//////////////////////////////////////////////////////////////////
if( !function_exists('wpsm_woo_versus_function') ) {
	function wpsm_woo_versus_function( $atts, $content = null ) {
	$build_args =shortcode_atts(array(
		'color' => '',
		'markcolor' => '',
		'ids' => '',
		'attr' => '',
		'min' => '',
	), $atts, 'wpsm_woo_versus'); 
	extract( $build_args ); 
	ob_start(); 
	?>
    <?php $ids = (!is_array($ids)) ? array_map( 'trim', explode( ",", $ids ) ) : $ids;?>
    <?php $attr = (!is_array($attr)) ? array_map( 'trim', explode( ",", $attr ) ) : $attr;?>
    <?php $min = (!is_array($min)) ? array_map( 'trim', explode( ",", $min ) ) : $min;?>

    <?php $attr_array = array();?>
    <?php $i = 0;?>
    <?php if(!empty($attr) && !empty($ids)):?>
        <?php foreach ($attr as $key => $attrvalue) {
        	$i ++;
        	if(stripos($attrvalue, 'pa_') === 0) {
            	$taxslug = $attrvalue;
        	}else{
             	$taxslug = 'pa_'.$attrvalue;       		
        	}

            $tax = get_taxonomy($taxslug);
            if($tax){
                $taxname = $tax->labels->singular_name;
                $attr_array[$attrvalue]['name'] = $taxname;
            }
            $maxvalue = array();
            foreach ($ids as $id) {
                
                $getattr = wc_get_product_terms( $id, $taxslug, array( 'fields' => 'names' ));
                if (!empty($getattr)){
                    $attr_array[$attrvalue]['ids'][$id]['value'] = $maxvalue[] = (int)array_shift($getattr);
                    $attr_array[$attrvalue]['ids'][$id]['title'] = esc_attr(get_the_title($id));
                    $attr_array[$attrvalue]['ids'][$id]['link'] = esc_url(get_the_permalink($id));
                }
            }
            if (!empty($min) && in_array($i, (array)$min)){
            	$min = min($maxvalue);
            	$attr_array[$attrvalue]['minmax'] = $min;
            }
            if($maxvalue){
             	$max = max($maxvalue);
            	$attr_array[$attrvalue]['max'] = $max;            	
            }


        }
        ?>
    <?php endif;?>
    <?php echo rh_generate_incss('barcompare');?>

    <?php if(!empty($attr) && !empty($ids)):?>
        <?php foreach ($attr_array as $arraybar):?>
            <div class="rehub-main-font font110 fontbold mb25"><?php echo ''.$arraybar['name'] ?></div>
            <div class="wpsm-bar-compare mb25">
               	<?php foreach ((array)$arraybar['ids'] as $key=>$id):?>
                    
                    <?php 
                    	if(empty($arraybar['max'])) continue;
                        if(($arraybar['max'] == $id['value'] && !isset($arraybar['minmax'])) || (isset($arraybar['minmax']) && $arraybar['minmax'] == $id['value']) ){
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
                        $perc_value = (int)$id['value'] / (int)$arraybar['max'] * 100;
                        if($perc_value >100) $perc_value = 100;
                        $title = $id['title'];
                        $link = $id['link'];
                        $value = $id['value'];
                        $stylebg = ($bg) ? ' style="background: '. $bg .'"' : '';
                    ?>
                        <div class="wpsm-bar wpsm-clearfix wpsm-bar-compare" data-percent="<?php echo ''.$perc_value;?>%">
                            <div class="wpsm-bar-title">
                                <span><a href="<?php echo esc_url($link);?>"><?php echo ''.$title;?></a></span>
                            </div>
                            <div class="wpsm-bar-bar"<?php echo ''.$stylebg;?>></div>
                            <div class="wpsm-bar-percent"><?php echo ''.$value;?></div>
                        </div>                        

                <?php endforeach;?>
            </div>
        <?php endforeach;?>
    <?php endif;?> 
	<div class="clearfix"></div>    

	<?php
	$output = ob_get_contents();
	ob_end_clean();
	return $output;
	}
	
}