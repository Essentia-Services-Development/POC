<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product, $post;
?>
<?php
    $upsells = $product->get_upsell_ids();

    if ( sizeof( $upsells ) === 0 ) {                                
    }
    else{
        echo '<div class="upsells-woo-area flowhidden" id="upsell-section-woo-area"><div class="rh-container mt30 mb30">';
        $upsells = implode(',',$upsells);
        echo '<h3>'.__( 'You may also like&hellip;', 'rehub-theme' ).'</h3>';
        $upsells_array = array('ids'=>$upsells, 'columns'=>'5_col', 'data_source'=>'ids', 'show'=> 5);
        if(rehub_option('width_layout') =='extended'){
            $upsells_array['columns'] = '6_col';
            $upsells_array['show'] = 6;
        }  
        $current_design = rehub_option('woo_design');       
        if ($current_design == 'grid') { 
            echo wpsm_woogrid_shortcode($upsells_array);                  
        }
        elseif ($current_design == 'gridtwo') {
            echo rh_generate_incss('offergrid'); 
            $upsells_array['gridtype'] = 'compact';
            echo wpsm_woogrid_shortcode($upsells_array);                  
        } 
        elseif ($current_design == 'gridrev') { 
            $upsells_array['gridtype'] = 'review';
            echo wpsm_woogrid_shortcode($upsells_array);                  
        }  
        elseif ($current_design == 'gridmart') { 
            $upsells_array['gridtype'] = 'gridmart';
            echo wpsm_woogrid_shortcode($upsells_array);                  
        }
        elseif ($current_design == 'griddigi') { 
            $upsells_array['gridtype'] = 'digital';
            echo wpsm_woogrid_shortcode($upsells_array);                  
        } 
        elseif ($current_design == 'dealwhite') { 
            $upsells_array['gridtype'] = 'dealwhite';
            echo wpsm_woogrid_shortcode($upsells_array);                  
        }  
        elseif ($current_design == 'dealdark') { 
            $upsells_array['gridtype'] = 'dealdark';
            echo wpsm_woogrid_shortcode($upsells_array);                  
        }               
        else{
            echo wpsm_woocolumns_shortcode($upsells_array);           
        } 
        echo '</div></div>';                               
    }
?>