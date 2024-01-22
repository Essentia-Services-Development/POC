<?php
/**
 * Members locator "Buddypress 3 col" search results template file. 
 * 
 * The information on this file will be displayed as the search results.
 * 
 * The function pass 2 args for you to use:
 * $gmw    - the form being used ( array )
 * $member - each member in the loop
 * 
 * You could but It is not recomemnded to edit this file directly as your changes will be overridden on the next update of the plugin.
 * Instead you can copy-paste this template ( the "Buddypress 3 col" folder contains this file and the "css" folder ) 
 * into the theme's or child theme's folder of your site and apply your changes from there. 
 * 
 * The template folder will need to be placed under:
 * your-theme's-or-child-theme's-folder/geo-my-wp/members-locator/search-results/
 * 
 * Once the template folder is in the theme's folder you will be able to choose it when editing the Members locator form.
 * It will show in the "Search results" dropdown menu as "Custom: Buddypress 3 col".
 */
?>  
<!--  Main results wrapper - wraps the paginations, map and results -->
<div class="gmw-results-wrapper gmw-fl-bpcustom-results-wrapper <?php echo esc_attr( $gmw['prefix'] ); ?>" data-id="<?php absint( $gmw['ID'] ); ?>" data-prefix="<?php echo esc_attr( $gmw['prefix'] ); ?>">
    <?php if ( $gmw_form->has_locations() ) : ?>
        <div id="buddypress">
        <?php do_action( 'gmw_search_results_start' , $gmw ); ?>
        
        <div id="pag-top" class="geo-pagination">

            <!-- results message -->
            <div class="pag-count floatleft tabletblockdisplay" id="member-dir-count-top">
                <?php gmw_results_message( $gmw, false ); ?>
            </div>

            <!-- per page -->
            <?php gmw_per_page( $gmw ); ?>
            <!-- pagination -->
            <div class="pagination-links tabletblockdisplay" id="member-dir-pag-top">
                <?php gmw_pagination( $gmw ); ?>
            </div>


        </div>

        <div class="clear"></div>

        <!-- GEO my WP Map -->
        <?php gmw_results_map( $gmw ); ?>
        
        <?php do_action( 'bp_before_directory_members_list' ); ?>

        <div class="rhbp-grid-loop">
        <ul id="members-list" class="item-list geowp-item-list col_wrap_fourth rh-flex-eq-height">
        <?php while ( bp_members() ) : bp_the_member(); ?>
            
            <!-- do not remove this line -->
            <?php $member = $members_template->member; ?>
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
        <?php endwhile; ?>
        </ul>
        </div>

        <?php do_action( 'bp_after_directory_members_list' ); ?>

        <?php bp_member_hidden_fields(); ?>
        
        <?php do_action( 'gmw_search_results_end', $gmw ); ?>   
        </div>
    <?php else : ?>

        <div class="gmw-no-results">
            
            <?php do_action( 'gmw_no_results_start', $gmw ); ?>

            <?php gmw_no_results_message( $gmw ); ?>
            
            <?php do_action( 'gmw_no_results_end', $gmw ); ?> 

        </div>

    <?php endif; ?>        
</div>