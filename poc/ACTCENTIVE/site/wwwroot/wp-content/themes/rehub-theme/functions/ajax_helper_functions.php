<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php

//////////////////////////////////////////////////////////////////
// AJAX SEARCH
//////////////////////////////////////////////////////////////////

if (!function_exists('rehub_ajax_search')){
function rehub_ajax_search() {
    check_ajax_referer( 'search-nonce', 'security' );
    $buffer = $buffer_msg = $buffer_store = '';

    //the search string
    if (!empty($_POST['re_string'])) {
        $re_string = sanitize_text_field($_POST['re_string']);
        $re_string = trim($re_string);
    } else {
        $re_string = '';
    }

    if (!empty($_POST['aff_link'])) {
        $aff_link = 1;
    }else{
        $aff_link = '';
    }

    //the post types for search    
    if (!empty($_POST['posttypesearch'])) {
        $posttypes = sanitize_text_field($_POST['posttypesearch']);
        $posttypes = explode(',', $posttypes);
    } else {
        $posttypes = array('post');
    }

    $count_stores = 0;

    //get the Dealstores data
    if(in_array('post', $posttypes) && rehub_option('enable_brand_taxonomy')){
        $store_args = array(
            'taxonomy' => array( 'dealstore' ),
            'hide_empty' => true,
            'number' => 2,
            'update_term_meta_cache' => false,
            'name__like' => $re_string,
        );

        $stores = get_terms($store_args);
        $count_stores = count($stores);
    
        if($count_stores > 0){
            foreach ($stores as $store) {
                $brand_url = get_term_meta( $store->term_id, 'brandimage', true );
                $brand_cashback = get_term_meta( $store->term_id, 'cashback_notice', true );
                if(empty($brand_url)){
                    $brand_url = get_template_directory_uri() . '/images/default/noimage_123_90.png';
                    $brand_url = apply_filters('rh_no_thumb_url', $brand_url, $store->term_id);
                }
                $buffer_store .= '<div class="re-search-result-div">';
                    $buffer_store .= '<div class="re-search-result-thumb"><a href="'. get_term_link($store) .'"><img src="'. $brand_url .'" alt="image"/></a></div>';
                    $buffer_store .= '<div class="re-search-result-info"><h3 class="re-search-result-title"><a href="'. get_term_link($store) .'">'. $store->name .'</a></h3>';
                    
                    $buffer_store .= '<span class="re-search-result-price greencolor">'.$brand_cashback.'</span>';
                $buffer_store .= '</div></div>';
            }
        }
    }

    //get the data
    $args = array(
        's' => $re_string,
        'post_type' => $posttypes,
        'posts_per_page' => 5,
        'post_status' => 'publish',
        'cache_results' => false,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
        'no_found_rows' => true     
    );

    if (!empty($_POST['catid'])) {
        if( in_array( 'product', $posttypes ) ){
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'slug',
                    'terms' => array( ''.esc_html($_POST['catid']).'' )
                )
            );
        }
        else {
            $args['cat'] = ''.esc_html($_POST['catid']).'';
        }
    } 
    if( in_array( 'product', $posttypes ) ){
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
    } 

    if( rehub_option('rehub_post_exclude_expired') == '1' && in_array( 'post', $posttypes ) ){
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

    $search_query = new WP_Query($args);

    //build the results
    if (!empty($search_query->posts)) {
        foreach ($search_query->posts as $post) {
            $the_price = '';
            $title = get_the_title( $post->ID );
            if ($aff_link == 1) {
                $offer_post_url = esc_url(get_post_meta( $post->ID, 'rehub_offer_product_url', true ));
                $offer_post_url = apply_filters('rehub_create_btn_url', $offer_post_url);
                $offer_url = apply_filters('rh_post_offer_url_filter', $offer_post_url );
                if(empty($offer_url)) {$offer_url = get_the_permalink($post->ID);}
                $link = $offer_url;
                $target = ' rel="nofollow sponsored" target="_blank"';  
            }
            else {
                $link = get_the_permalink($post->ID);
                $target = '';              
            }
            if($post->post_type == 'product'){
                $the_price = get_post_meta( $post->ID, '_price', true);  
                if ( '' != $the_price ) {
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
                }                
                $terms = get_the_terms($post->ID, 'product_visibility' );
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
                $offer_price = get_post_meta( $post->ID, 'rehub_offer_product_price', true );
                if($offer_price){
                   $the_price = $offer_price; 
                }
            }      
            if ( has_post_thumbnail($post->ID) ){
                $image_id = get_post_thumbnail_id($post->ID);  
                $image_url = wp_get_attachment_image_src($image_id, 'minithumb');  
                $image_url = $image_url[0];
                $image_url = apply_filters('rh_thumb_url', $image_url );
            }
            else {
                $image_url = get_template_directory_uri() . '/images/default/noimage_100_70.png' ;
                $image_url = apply_filters('rh_no_thumb_url', $image_url, $post->ID);
            } 

            $buffer .= '<div class="re-search-result-div">';
            $buffer .= '<div class="re-search-result-thumb"><a href="'.$link.'"'.$target.'><img src="'.$image_url.'" alt="image"/></a></div>';
            $buffer .= '<div class="re-search-result-info"><h3 class="re-search-result-title">'.rh_expired_or_not($post->ID, "span").'<a href="'.$link.'"'.$target.'>'.$title.'</a></h3>';
            if ( empty( $post->post_excerpt ) ) {
                $buffer .= '<div class="re-search-result-excerpt mb5 lineheight15">'.rehub_truncate("maxchar=150&text=$post->post_content&echo=false").'</div>';
            } else {
                $buffer .= '<div class="re-search-result-excerpt mb5 lineheight15">'.rehub_truncate("maxchar=150&text=$post->post_excerpt&echo=false").'</div>'; 
            }          
            if ( '' != $the_price ) {
                $buffer .= '<span class="re-search-result-price greencolor">'.$the_price.'</span>';               
            }
            else {
                $buffer .= '<span class="re-search-result-meta">'.get_the_time(get_option( 'date_format' ), $post->ID).'</span>';
            }     

            if(!empty($_POST['enable_compare'])){
                $compare_page = rehub_option('compare_page');
                $multicats_on = rehub_option('compare_multicats_textarea');
                if($compare_page || $multicats_on){
                    $buffer .= '<span class="re-search-result-compare mt5 blockstyle">'.do_shortcode('[wpsm_compare_button id='.$post->ID.']').'</span>';
                } 

            }               
                
            $buffer .= '</div></div>';
        }
    }

    if (count($search_query->posts) == 0 && !$count_stores) {
        //no results
        $buffer = '<div class="re-aj-search-result-msg no-result">' . esc_html__('No results', 'rehub-theme') . '</div>';
    } else {
        if(is_array($posttypes)){
            $posttypes = implode(',', $posttypes);
        }
        $product_cat = ($posttypes == 'product') ? '&product_cat='.esc_html($_POST['catid']) : '';
        $buffer_msg .= '<div class="re-aj-search-result-msg"><a href="' . esc_url(home_url('/?s=' . $re_string.'&post_type='.$posttypes.''.$product_cat )) .'">' . esc_html__('View all results', 'rehub-theme') . '</a></div>';
        //add wrap
        $buffer = '<div class="re-aj-search-wrap-results">' . $buffer_store. $buffer . '</div>' . $buffer_msg;
    }

    //prepare array for ajax
    $bufferArray = array(
        're_data' => $buffer,
        're_total_inlist' => count($search_query->posts),
        're_search_query'=> $re_string
    );

    //Return the String
    die(json_encode($bufferArray));
}
add_action( 'wp_ajax_nopriv_rehub_ajax_search', 'rehub_ajax_search' );
add_action( 'wp_ajax_rehub_ajax_search', 'rehub_ajax_search' );
}


//////////////////////////////////////////////////////////////////
// Filter ajax function
//////////////////////////////////////////////////////////////////
add_action( 'wp_ajax_re_filterpost', 'ajax_action_re_filterpost' );
add_action( 'wp_ajax_nopriv_re_filterpost', 'ajax_action_re_filterpost' );
if( !function_exists('ajax_action_re_filterpost') ) {
function ajax_action_re_filterpost() {  
    check_ajax_referer( 'filter-nonce', 'security' );
    $args = (!empty($_POST['filterargs'])) ? rh_sanitize_multi_arrays($_POST['filterargs']) : array();
    $innerargs = (!empty($_POST['innerargs'])) ? rh_sanitize_multi_arrays($_POST['innerargs']) : array();
    $offset = (!empty($_POST['offset'])) ? intval( $_POST['offset'] ) : 0;
    $template = (!empty($_POST['template'])) ? sanitize_text_field( $_POST['template'] ) : '';
    $sorttype = (!empty($_POST['sorttype'])) ? rh_sanitize_multi_arrays( $_POST['sorttype'] ) : '';
    $tax = (!empty($_POST['tax'])) ? rh_sanitize_multi_arrays( $_POST['tax'] ) : '';
    $containerid = (!empty($_POST['containerid'])) ? sanitize_text_field( $_POST['containerid'] ) : '';
    if ($template == '') return;
    $response = $page_sorting = '';

    if ($offset !='') {$args['offset'] = $offset;}
    $offsetnext = (!empty($args['posts_per_page'])) ? (int)$offset + $args['posts_per_page'] : (int)$offset + 12;
    $perpage = (!empty($args['posts_per_page'])) ? $args['posts_per_page'] : 12;
    $args['no_found_rows'] = true;
    $args['post_status'] = 'publish';   

    if(!empty($sorttype) && is_array($sorttype)) { //if sorting panel  
        $filtertype = $filtermetakey = $filtertaxkey = $filtertaxtermslug = $filterorder = $filterdate = $filterorderby = $filterpricerange = $filtertaxcondition = '';
        $page_sorting = ' data-sorttype=\''.json_encode($sorttype).'\'';
        extract($sorttype);
        if($filterorderby){
            $args['orderby'] = $filterorderby;
        }        
        if(!empty($filtertype) && $filtertype =='comment') {
            $args['orderby'] = 'comment_count';
        }
        if($filtertype =='meta' && !empty($filtermetakey)) { //if meta key sorting
            if(!empty($args['meta_value'])){
                $args['meta_query'] = array(array(
                    'key' => $args['meta_key'],
                    'value' => $args['meta_value'],
                    'compare' => '=',
                ));
                unset($args['meta_value']); 
            }           
            $args['orderby'] = 'meta_value_num date';
            $args['meta_key'] = esc_html($filtermetakey);
        }
        if($filtertype =='expirationdate') { //if meta key sorting
            unset($args['meta_key']);
            unset($args['orderby']);  
            $date = new DateTime();
            $date->modify("-1 day");   
            $keyexpiration = (!empty($args['post_type']) && $args['post_type']=='product') ? 'rehub_woo_coupon_date' : 'rehub_offer_coupon_date';       
            $args['meta_query'] = array(array(
                'key' => $keyexpiration,
                'value' => $date->format("Y-m-d"),
                'compare' => '>',
                'type' => 'DATE'
            ));
            $args['orderby'] = 'meta_value';
            $args['meta_key'] = $keyexpiration;            
        }        
        if($filtertype =='pricerange' && !empty($filterpricerange)) { //if meta key sorting
            $price_range_array = array_map( 'trim', explode( "-", $filterpricerange ) );
            $keymeta = (!empty($args['post_type']) && $args['post_type']=='product') ? '_price' : 'rehub_main_product_price';
            $args['meta_query'][] = array(
                'key'     => $keymeta,
                'value'   => $price_range_array,
                'type'    => 'numeric',
                'compare' => 'BETWEEN',
            );
            if ($filterorderby == 'view' || $filterorderby == 'thumb' || $filterorderby == 'discount' || $filterorderby == 'price'){
                $args['orderby'] = 'meta_value_num';
            }       
            if ($filterorderby == 'view'){
                $args['meta_key'] = 'rehub_views';
            }
            if ($filterorderby == 'thumb'){
                $args['meta_key'] = 'post_hot_count';
            }
            if ($filterorderby == 'wish'){
                $args['meta_key'] = 'post_wish_count';
            }            
            if ($filterorderby == 'discount'){
                $args['meta_key'] = '_rehub_offer_discount';
            }
            if ($filterorderby == 'price'){
                $args['meta_key'] = $keymeta;
            }            
        }        
        if($filtertype =='deals') { //if meta key sorting
            unset($args['meta_key']);
            unset($args['orderby']);
            $keymeta = (!empty($args['post_type']) && $args['post_type']=='product') ? 'rehub_woo_coupon_code' : 'rehub_offer_product_coupon'; 
            $pricemeta = (!empty($args['post_type']) && $args['post_type']=='product') ? '_product_url' : 'rehub_offer_product_url';                       
            $args['meta_query']['relation'] = 'AND';  
            $args['meta_query'][] = array(
                'relation' => 'OR',
                array(
                    'key'     => $keymeta,
                    'value'   => '',
                    'compare' => '=',
                ),
                array(
                    'key' => $keymeta,
                    'compare' => 'NOT EXISTS'
                ),
            );               
            $args['meta_query'][] = array(
                'key'     => $pricemeta,
                'value'   => '',
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
        if($filtertype =='sales') { //if meta key sorting
            $keymeta = (!empty($args['post_type']) && $args['post_type']=='product') ? '_sale_price' : 'rehub_offer_product_price_old';
            unset($args['meta_key']);
            unset($args['orderby']);  
            $args['meta_query'][] = array(
                'key' => $keymeta,
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
        if($filtertype =='expired') { //if meta key sorting
            unset($args['meta_key']);
            unset($args['orderby']);
            $args['tax_query'][] = array(
                'relation' => 'AND',
                array(
                    'taxonomy' => 'offerexpiration',
                    'field'    => 'name',
                    'terms'    => 'yes',
                    'operator' => 'IN',
                )
            );
            if(isset($args['tax_query'][1][0]['operator']) && $args['tax_query'][1][0]['operator'] == 'NOT IN'){
                unset($args['tax_query'][1]);
            }
        }                  
        if($filtertype =='coupons') { //if meta key sorting
            unset($args['meta_key']);
            unset($args['orderby']);            
            $args['meta_query']['relation'] = 'AND';  
            $keymeta = (!empty($args['post_type']) && $args['post_type']=='product') ? 'rehub_woo_coupon_code' : 'rehub_offer_product_coupon';
            $args['meta_query'][] = array(
                'key'     => $keymeta,
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
        if($filtertype =='tax' && !empty($filtertaxkey) && !empty($filtertaxtermslug)) { //if taxonomy sorting
            if (!empty($args['tax_query']) && !$filtertaxcondition) {
                unset($args['tax_query']);
            }  
            if(is_array($filtertaxtermslug)){
                $filtertaxtermslugarray = $filtertaxtermslug;
            }  
            else{
                $filtertaxtermslugarray = array_map( 'trim', explode( ",", $filtertaxtermslug) );
            } 
            if($filtertaxcondition){
                $args['tax_query'][] = array(
                    'taxonomy' => $filtertaxkey,
                    'field'    => 'slug',
                    'terms'    => $filtertaxtermslugarray,
                );                
            } 
            else{
                $args['tax_query'] = array (
                    array(
                        'taxonomy' => $filtertaxkey,
                        'field'    => 'slug',
                        'terms'    => $filtertaxtermslugarray,
                    )
                );
            }    
        }
        if($tax && $filtertype != 'tax'){
            $args['tax_query'] = array (
                array(
                    'taxonomy' => $tax['filtertaxkey'],
                    'field'    => 'slug',
                    'terms'    => $tax['filtertaxtermslug'],
                )
            );
        }
        if($filtertype =='hot') { //if meta key sorting
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
        if($filterorder) { $args['order'] = $filterorder; }
        if($filterdate) { //if date sorting
            if (!empty($args['date_query']) || $filterdate =='all') {
                if(isset($args['date_query'])){
                    unset($args['date_query']);
                }
            }
            if ($filterdate == 'day') {     
                $args['date_query'][] = array(
                    'after'  => '1 day ago',
                );
            }
            if ($filterdate == 'week') {    
                $args['date_query'][] = array(
                    'after'  => '7 days ago',
                );
            }   
            if ($filterdate == 'month') {     
                $args['date_query'][] = array(
                    'after'  => '1 month ago',
                );
            }   
            if ($filterdate == 'year') {     
                $args['date_query'][] = array(
                    'after'  => '1 year ago',
                );
            }
        }
        if($args['post_type']=='product'){
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
        if(!empty($args['show_coupons_only']) && $filtertype !='deals' && $filtertype !='sales' && $filtertype !='coupons'  && $filtertype !='expired'){
            if ($args['show_coupons_only'] == 3) {     
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
        }
    }else{ // if infinite scroll
        if(!empty($args['show_coupons_only'])){
            if ($args['show_coupons_only'] == 3) {     
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
        }
    }   

    $wp_query = new WP_Query($args);
    $i=1;

    if ( $wp_query->have_posts() ) {
        while ($wp_query->have_posts() ) {
            $wp_query->the_post();
            ob_start();
            if(!empty($innerargs)) {extract($innerargs);}
            include(rh_locate_template('inc/parts/'.$template.'.php'));
            $i++;
            $response .= ob_get_clean();
        }
        wp_reset_query();
        if ($i >= $perpage){
            $response .='<div class="re_ajax_pagination"><span data-offset="'.$offsetnext.'" data-containerid="'.$containerid.'"'.$page_sorting.' class="re_ajax_pagination_btn def_btn">' . esc_html__('Next', 'rehub-theme') . '</span></div>';
        } 
    }           
    else {
        $response .= '<div class="clearfix flexbasisclear"><span class="no_more_posts">'.__('No more!', 'rehub-theme').'<span></div>';
    }       

    echo ''.$response ;
    exit;
}
}


//////////////////////////////////////////////////////////////////
// Get full content
//////////////////////////////////////////////////////////////////
add_action( 'wp_ajax_re_getfullcontent', 'ajax_action_re_getfullcontent' );
add_action( 'wp_ajax_nopriv_re_getfullcontent', 'ajax_action_re_getfullcontent' );
if( !function_exists('ajax_action_re_getfullcontent') ) {
function ajax_action_re_getfullcontent() {  
    check_ajax_referer( 'ajaxed-nonce', 'security' );
    $postid = intval($_POST['postid']);
    if ($postid) {
        $wp_query = new WP_Query(array('p'=>$postid, 'no_found_rows'=>1, 'ignore_sticky_posts'=>1));
        if ( $wp_query->have_posts() ) {
            while ($wp_query->have_posts() ) {
                $wp_query->the_post();
                global $post;
                
                ?>
                <article class="post"><?php echo apply_filters('the_content', $post->post_content); ?></article>;
                <?php 
                
            }
        }
        wp_reset_query();           
    }
    exit;
}
}


//////////////////////////////////////////////////////////////////
// Frontend Submit to CE
//////////////////////////////////////////////////////////////////
if(!function_exists('rehub_ce_user_offer')){
    function rehub_ce_user_offer() {

        if ( !isset( $_POST['offer_nonce'] ) || !wp_verify_nonce( $_POST['offer_nonce'], 'rehub_ce_user_offer' ) ) {
            return;
        }
        
        $post_id = intval($_POST['post_id']);
        $user_id = intval($_POST['from_user']);
        $cur_offers = get_post_meta( $post_id, '_cegg_data_Offer', true );
        $cur_offers = !empty($cur_offers) ? $cur_offers : array();

        // compose an Offer
        $new_offer = array();
        $new_offer['title'] = trim(sanitize_text_field($_POST['ce_title']));
        $new_offer['orig_url'] = !empty($_POST['ce_orig_url']) ? filter_var($_POST['ce_orig_url'], FILTER_VALIDATE_URL) : '';
        $new_offer['img'] = !empty($_POST['ce_img']) ? filter_var($_POST['ce_img'], FILTER_VALIDATE_URL) : '';
        $new_offer['price'] = !empty($_POST['ce_price']) ? sanitize_text_field($_POST['ce_price']) : '';
        $new_offer['currencyCode'] = !empty($_POST['ce_currency']) ? sanitize_text_field($_POST['ce_currency']) : '';
        if(empty($new_offer['currencyCode'])){
            $new_offer['currencyCode'] = rehub_option('ce_custom_currency');
        }
        $new_offer['description'] = !empty($_POST['ce_description']) ? trim(wp_kses_post($_POST['ce_description'])) : '';
        $new_offer['priceXpath'] = $new_offer['domain'] = $new_offer['rating'] = $new_offer['merchant'] = '';
        $new_offer['extra'] = array('date' => time(), 'author' => $user_id, 'source' => 'frontend_shortcode');

        // set UID for the offer 
        $unique_id = 'OfferID_'. $user_id;
        $new_offer['unique_id'] = $unique_id;
        
        // add the current Offer to the Post Offer array
        $cur_offers[$unique_id] = apply_filters('wpsm_deal_popup_fields_save', $new_offer);
            
        $updated = update_post_meta($post_id, '_cegg_data_Offer', $cur_offers);
        \ContentEgg\application\components\ContentManager::updateItems($post_id, 'Offer');
        
        wp_die(json_encode(array( 'success' => $updated)));
    }
    add_action('wp_ajax_rehub_ce_user_offer', 'rehub_ce_user_offer');
}
