<?php

if(!function_exists('add_action')){
    die("access denied.");
}

function powerpress_admin_taxonomyfeeds_columns($data=array()){
	$data['name'] = __('Term Name', 'powerpress');
	$data['taxonomy'] = __('Taxonomy', 'powerpress');
	$data['feed-slug'] = __('Slug', 'powerpress');
	$data['url'] = __('Feed URL', 'powerpress');
	return $data;
}

add_filter('manage_powerpressadmin_taxonomyfeeds_columns', 'powerpress_admin_taxonomyfeeds_columns');

function powerpress_admin_taxonomyfeeds(){
	$General = powerpress_get_settings('powerpress_general');

    // If we have powerpress credentials, check if the account has been verified
    $creds = get_option('powerpress_creds');
    powerpress_check_credentials($creds);
    wp_enqueue_script('powerpress-admin', powerpress_get_root_url() . 'js/admin.js', array(), POWERPRESS_VERSION );
    ?>

    <h2 class="pp-page-header"><?php echo __('Taxonomy Podcasting', 'powerpress'); ?></h2>
    <h3 class="pp-page-h3"><?php echo __('Taxonomy Podcasting adds custom podcast settings to specific taxonomy feeds.', 'powerpress'); ?></h3>

    <div class="pp-card-body">
        <div class="pp-row pp-tools-row">
            <div class="pp-col-50">
                <div class="pp-row">
                    <h2 class="pp-page-sub-header">Add Podcasting to Existing Taxonomy Term</h2>
                </div>


                <?php
	                $current_taxonomy = (isset($_GET['taxonomy'])?$_GET['taxonomy']: (isset($_POST['taxonomy'])?$_POST['taxonomy']:''));
                    if(empty($current_taxonomy)){ ?>
                        <h3><label for="powerpress_taxonomy_select"><?php echo __('Step 1 - Select Taxonomy', 'powerpress'); ?></label></h3>
                        <select id="powerpress_taxonomy_select" name="taxonomy" style="width: 95%;">
                            <option value=""><?php echo __('Select Taxonomy', ''); ?></option>
                            <?php
                                $taxonomies = get_taxonomies('','names');
                                foreach($taxonomies as $null => $taxonomy){
                                    if($taxonomy == 'category'){
                                        continue;
                                    }
                                    $taxonomy = htmlspecialchars($taxonomy);

                                    echo "\t<option value=\"$taxonomy\"". ($current_taxonomy==$taxonomy?' selected':''). ">$taxonomy</option>\n";
                                }
                            ?>
                        </select>

                        <div class="pp-row">
                            <p class="submit"><input type="submit" class="button" name="select_taxonomy" value="<?php echo __('SELECT TAXONOMY TERM', 'powerpress'); ?>" /></p>
                        </div>
                    <?php }

                ?>

                <?php
                    if(!empty($current_taxonomy)){ ?>
                        <input type="hidden" name="action" value="powerpress-addtaxonomyfeed" />
                        <input type="hidden" name="taxonomy" value="<?php echo esc_attr($current_taxonomy); ?>" />
                        <?php wp_nonce_field('powerpress-add-taxonomy-feed'); ?>
                        <h3><label for="term"><?php echo __('Step 2 - Select Taxonomy Term', 'powerpress'); ?></label></h3>
                        <?php
                            wp_dropdown_categories(  array('class'=>'', 'show_option_none'=>__('Select Term', 'powerpress'), 'orderby'=>'name', 'hide_empty'=>0, 'hierarchical'=>1, 'name'=>'term', 'id'=>'term_id', 'taxonomy'=>$current_taxonomy ) );
                        ?>


                        <div class="pp-row" style="margin-top: 20px;">
                            <input style="margin-right: 20px;" type="submit" class="button" name="add_podcasting" value="<?php echo __('Add Podcast Settings to Term', 'powerpress'); ?>" /> &nbsp;
                            <input type="submit" class="button" name="cancel" value="<?php echo __('Cancel', 'powerpress'); ?>" />

                        </div>

                <?php } ?>
            </div>

            <?php
                // Currently, there are any reasons listed on this page, so this column is hidden
            ?>
            <div class="pp-col-50" style="display: none;">
                <div class="pp-row">
                    <h2 class="pp-page-sub-header">Why would I use Taxonomy?</h2>
                </div>

                <ul style="list-style: unset; padding-left: 20px;">
                    <li>
                        <h3 style="font-weight: 400;">You have a podcast that covers two topics that sometimes share same posts and sometimes do not. Use your main podcast feed as a combined feed of both topics 	and use category feeds to distribute topic specific episodes.</h3>
                    </li>
                     <li>
                        <h3 style="font-weight: 400;">You want to use categories to keep episodes separate from each other. Each category can be used to distribute separate podcasts with the main podcast feed combining all categories to provide a network feed</h3>
                    </li>
                </ul>
            </div>
        </div>

        <hr>

        <div class="pp-row pp-tools-row" style="margin-top: 20px;">
             <div class="pp-row">
                <h2 class="pp-page-sub-header">List of Taxonomy Terms</h2>
            </div>
        </div>

        <div class="pp-row pp-tools-row" style="margin-top: 20px;">
            <table class="widefat fixed">
                <thead>
                    <tr>
                        <?php print_column_headers('powerpressadmin_taxonomyfeeds'); ?>
                    </tr>
                </thead>

                <tfoot>
                    <tr>
                        <?php print_column_headers('powerpressadmin_taxonomyfeeds', false); ?>
                    </tr>
                </tfoot>

                <tbody>
                    <?php
                        $PowerPressTaxonomies = get_option('powerpress_taxonomy_podcasting');
                        if(empty($PowerPressTaxonomies)){
                            $PowerPressTaxonomies = array();
                        }

                        $count = 0;
                        foreach($PowerPressTaxonomies as $tt_id => $null) {
                            $taxonomy_type = '';
                            $term_ID = '';

                            global $wpdb;
                            $term_info = $wpdb->get_results("SELECT term_id, taxonomy FROM $wpdb->term_taxonomy WHERE term_taxonomy_id = $tt_id",  ARRAY_A);
                            if(!empty( $term_info[0]['term_id'])){
                                $term_ID = $term_info[0]['term_id'];
                                $taxonomy_type = $term_info[0]['taxonomy'];
                            } else {
                                continue; // we didn't find this taxonomy relationship
		                    }

                            $term_object = get_term($term_ID, $taxonomy_type, OBJECT, 'edit');
                            if(is_wp_error($term_object)){
                                continue;
                            }

                            $columns = powerpress_admin_taxonomyfeeds_columns();
                            $hidden = array();

                            if($count % 2 == 0){
                                echo '<tr class="alternate">';
                            } else {
                                echo '<tr>';
                            }

                            $edit_link = admin_url('admin.php?page=powerpress/powerpressadmin_taxonomyfeeds.php&amp;action=powerpress-edittaxonomyfeed&amp;term='. $term_ID .'&taxonomy='.$taxonomy_type.'&amp;ttid='.$tt_id) ;

                            $feed_title = $term_object->name;
                            $url = get_term_feed_link($term_ID, $taxonomy_type, 'rss2');
                            $short_url = str_replace('http://', '', $url);
                            $short_url = str_replace('www.', '', $short_url);

                            if(strlen($short_url) > 35){
                                $short_url = substr($short_url, 0, 32).'...';
                            }

                            foreach($columns as $column_name=>$column_display_name){
                                $class = "class=\"column-$column_name\"";

                                switch($column_name) {
                                    case 'feed-slug': {
                                        echo "<td $class>{$term_object->slug}";
                                        echo "</td>";
                                    } break;

                                    case 'name': {
                                        echo '<td '.$class.'><strong><a class="row-title" href="'.$edit_link.'" title="' . esc_attr(sprintf(__('Edit "%s"', 'powerpress'), $feed_title)) . '">'. esc_attr($feed_title).'</a></strong><br />';
                                        $actions = array();
                                        $actions['edit'] = '<a href="' . $edit_link . '">' . __('Edit', 'powerpress') . '</a>';
                                        $actions['remove'] = "<a class='submitdelete' href='". admin_url() . wp_nonce_url("admin.php?page=powerpress/powerpressadmin_taxonomyfeeds.php&amp;action=powerpress-delete-taxonomy-feed&amp;ttid=$tt_id", 'powerpress-delete-taxonomy-feed-' . $tt_id) . "' onclick=\"if ( confirm('" . esc_js(sprintf( __("You are about to remove podcast settings for taxonomy '%s'\n  'Cancel' to stop, 'OK' to delete.", 'powerpress'), esc_attr($feed_title) )) . "') ) { return true;}return false;\">" . __('Remove', 'powerpress') . "</a>";
                                        $action_count = count($actions);
                                        $i = 0;
                                        echo '<div class="row-actions">';
                                        foreach($actions as $action => $linkaction){
                                            ++$i;
                                            ($i == $action_count) ? $sep = '' : $sep = ' | ';
                                            echo '<span class="'.$action.'">'.$linkaction.$sep .'</span>';
					                    }
                                        echo '</div>';
                                        echo '</td>';

				                    } break;

                                    case 'url': {
                                        echo "<td $class><a href='$url' title='". esc_attr(sprintf(__('Visit %s', 'powerpress'), $feed_title))."' target=\"_blank\">$short_url</a>";
                                            echo '<div class="row-actions">';
                                            if(defined('POWERPRESS_FEEDVALIDATOR_URL')){
                                                echo '<span class="'.$action .'"><a href="'. POWERPRESS_FEEDVALIDATOR_URL . urlencode( str_replace('&amp;', '&', $url) ) .'" target="_blank">' . __('Validate Feed', 'powerpress') . '</a></span>';
                                            }
                                            echo '</div>';
                                        echo "</td>";

                                    } break;

                                    case 'episode-count': {
                                        echo "<td $class>$episode_total";
                                        echo "</td>";

                                    } break;

                                    case 'taxonomy': {
                                        echo "<td $class>$taxonomy_type";
                                        echo "</td>";
                                    } break;

                                    default: {

                                    };	break;
                                }
                            }

                            echo "\n    </tr>\n";
                            $count++;
	                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <style>
        .pp-col-50 {
            width: 50%;
        }
    </style>

<?php } ?>