<?php
echo $args['before_widget'];
?>

<div class="ps-widget__wrapper<?php echo $instance['class_suffix'];?> ps-widget<?php echo $instance['class_suffix'];?>">
    <div class="ps-widget__header<?php echo $instance['class_suffix'];?>">
        <?php
        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
        }
        ?>
    </div>
    <?php

    global $wpdb;

    if(!isset($instance['limit'])) {
        $instance['limit'] = 12;
    }

    if(!isset($instance['minsize'])) {
        $instance['minsize'] = 12;
    }

    $where = '';
    if($instance['minsize']>0) {
        $where = " WHERE ht_count >= {$instance['minsize']} ";
    }

    $hashtags = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}peepso_hashtags $where ORDER BY ht_count DESC LIMIT {$instance['limit']}");
    $max = 0;

    if ( count($hashtags) )
    {
        $result = array();

        foreach($hashtags as $hashtag) {
            $result[$hashtag->ht_name] = $hashtag->ht_count;
            $max = max($max, $hashtag->ht_count);
        }


        if(0==$instance['sortby']) {
            if(0==$instance['sortorder']) {
                ksort($result);
            }

            if(1==$instance['sortorder']) {
                krsort($result);
            }
        } elseif(1==$instance['sortby']) {
            if(0==$instance['sortorder']) {
                asort($result);
            }

            if(1==$instance['sortorder']) {
                arsort($result);
            }
        }

        $wrapper = '';

        if(1==$instance['displaystyle'] || 2==$instance['displaystyle']) {
            $wrapper='ps-widget__hashtags--list';
        }
        ?>
        <div class="ps-widget__body<?php echo $instance['class_suffix'];?>">
            <div class="ps-widget--hashtags">
                <div class="ps-widget__hashtags <?php echo $wrapper;?>">
                    <?php
                    foreach($result as $name=>$count) {

                        $percentage = 100; // default percentage if tags have no counts (the max is 0)
                        if($max > 0) {
                            $percentage = round($count / $max * 10) * 10;
                        }

                        $size = 'ps-hashtag--box ps-hashtag--size'.$percentage;

                        if(1==$instance['displaystyle']) {
                            $size='';
                        }

                        if(2==$instance['displaystyle']) {
                            $size = 'ps-hashtag--size'.$percentage;
                        }

                        ?>
                        <a data-debug="<?php echo "#$name ($count / $percentage%)";?>" class="ps-hashtag <?php echo $size;?>" href="<?php echo PeepSo::hashtag_url($name);?>">
                            #<?php echo $name;?>
                        </a>
                        <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    <?php } else { ?>
        <div class="ps-widget__body<?php echo $instance['class_suffix'];?>">
            <span class='ps-text--muted'><?php echo __('No hashtags', 'peepso-core');?></span>
        </div>
    <?php } ?>
</div>

<?php
if(isset($args['after_widget'])) {
    echo $args['after_widget'];
}

// EOF
