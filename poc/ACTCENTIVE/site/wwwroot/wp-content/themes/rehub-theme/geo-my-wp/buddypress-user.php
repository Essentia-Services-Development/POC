<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<li <?php bp_member_class( array('col_item') ); ?>>
<!-- do not remove this line -->
<?php do_action( 'gmw_search_results_loop_item_start', $gmw, $member ); ?>        
<?php 
    $author_ID = bp_get_member_user_id();
?>
<div class="member-inner-list" style="<?php rh_cover_image_url( 'members', 120, true ); ?>">
    <?php               
        $membertype = bp_get_member_type($author_ID);
        $membertype_object = bp_get_member_type_object($membertype);
        $membertype_label = (!empty($membertype_object) && is_object($membertype_object)) ? $membertype_object->labels['singular_name'] : '';
    ?>      
    <?php if($membertype_label) :?>
        <span class="rh-user-m-type rh-user-m-type-<?php echo ''.$membertype;?>"><?php echo ''.$membertype_label;?></span>                
    <?php endif;?>           
    <div class="item-avatar">
        <a href="<?php bp_member_permalink(); ?>"><?php bp_member_avatar(); ?></a>
    </div>

    <div class="item">
        <div class="item-title">
            <a href="<?php bp_member_permalink(); ?>">
                <?php the_author_meta( 'display_name',$author_ID); ?>                         
            </a>
        </div>

        <div class="item-meta">
            <?php do_action( 'gmw_search_results_before_distance', $gmw, $member); ?>                        
            <!-- distance -->
            <div class="distance-to-user-geo">
                <?php gmw_distance_to_location( $member, $gmw ); ?>
            </div>                    
            <span class="activity"><?php bp_member_last_active(); ?></span>
        </div>
        <?php echo rh_bp_show_vendor_in_loop($author_ID);?>                 
        <?php do_action( 'bp_directory_members_item' ); ?>
        <?php do_action( 'gmw_fl_search_results_member_items', $gmw, $member ); ?>
        <?php
         /***
          * If you want to show specific profile fields here you can,
          * but it'll add an extra query for each member in the loop
          * (only one regardless of the number of fields you show):
          *
          * bp_member_profile_data( 'field=the field name' );
          */
        ?>
    </div>

    <div class="action">
        <?php do_action( 'bp_directory_members_actions' ); ?>
    </div>
    <div class="clear"></div>
    <div class="adress-user-geo">
        <?php do_action( 'gmw_search_results_before_address', $gmw, $member ); ?>                   
        <!-- address -->
        <?php gmw_search_results_address( $member, $gmw ); ?>
            
        <?php gmw_search_results_directions_link( $member, $gmw ); ?>               
    </div>                 
    <?php do_action( 'gmw_search_results_loop_item_end', $gmw, $member ); ?>                

</div>
</li>