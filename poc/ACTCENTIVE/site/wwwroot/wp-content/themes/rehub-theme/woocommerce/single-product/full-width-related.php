<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product, $post;
$limit = (rehub_option('width_layout') =='extended') ? 6 : 5;
?>
<?php $related = wc_get_related_products($product->get_id(), $limit);
    if ( sizeof( $related ) == 0 ){
    }else{
        echo '<div class="related-woo-area clearbox flowhidden" id="related-section-woo-area"><div class="rh-container mt25 mb15">';
        $related = implode(',',$related);
        $related_array = array('ids'=>$related, 'columns'=>'5_col', 'data_source'=>'ids', 'show'=> 5); 
        if(rehub_option('width_layout') =='extended'){
            $related_array['columns'] = '6_col';
            $related_array['show'] = 6;
        }       
        echo '<div class="clearfix"></div><h3>'.__( 'Related Products', 'rehub-theme' ).'</h3>';
        $current_design = rehub_option('woo_design');
        if ($current_design == 'grid') { 
            echo wpsm_woogrid_shortcode($related_array);                  
        }
        elseif ($current_design == 'gridtwo') { 
            echo rh_generate_incss('offergrid');
            $related_array['gridtype'] = 'compact';
            echo wpsm_woogrid_shortcode($related_array);                  
        } 
        elseif ($current_design == 'gridrev') { 
            $related_array['gridtype'] = 'review';
            echo wpsm_woogrid_shortcode($related_array);                  
        }  
        elseif ($current_design == 'gridmart') { 
            $related_array['gridtype'] = 'gridmart';
            echo wpsm_woogrid_shortcode($related_array);                  
        }
        elseif ($current_design == 'griddigi') { 
            $related_array['gridtype'] = 'digital';
            echo wpsm_woogrid_shortcode($related_array);                  
        } 
        elseif ($current_design == 'dealwhite') { 
            $related_array['gridtype'] = 'dealwhite';
            echo wpsm_woogrid_shortcode($related_array);                  
        }  
        elseif ($current_design == 'dealdark') { 
            $related_array['gridtype'] = 'dealdark';
            echo wpsm_woogrid_shortcode($related_array);                  
        }              
        else{
            echo wpsm_woocolumns_shortcode($related_array);           
        }
        echo '</div></div>';           
    }          
?> 