<?php
    echo $args['before_widget'];
    $owner = PeepSoUser::get_instance($instance['user_id']);
?>

<div class="ps-widget__wrapper<?php echo $instance['class_suffix'];?> ps-widget<?php echo $instance['class_suffix'];?>">
    <div class="ps-widget__header<?php echo $instance['class_suffix'];?>">
        <a href="<?php echo $owner->get_profileurl();?><?php echo PeepSoVideos::profile_menu_slug();?>"><?php
            if ( ! empty( $instance['title'] ) ) {
                echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
            }
        ?></a>
    </div>
    <?php
    if(count($instance['list']))
    {
    ?>
    <div class="ps-widget__body<?php echo $instance['class_suffix'];?>">
        <div class="psw-media">
            <?php
                foreach ($instance['list'] as $video)
                {
                    $video = (array) $video;
                    echo PeepSoTemplate::exec_template('videos', 'video-item-widget', $video);
                }
            ?>
            <?php
                // @TODO add template tag for "total"
            ?>
            <div class="psw-media__more">
                <a href="<?php echo $owner->get_profileurl();?><?php echo PeepSoVideos::profile_menu_slug();?>">
                    <?php echo __('View All', 'vidso');?>
                    <span>(<?php echo $instance['total'];?>)</span>
                </a>
            </div>
        </div>
    </div>
    <?php } else { ?>
    <div class="ps-widget__body<?php echo $instance['class_suffix'];?>">
      <div class="psw-media">
        <?php
        if ($instance['media_type'] == 'all') {
            echo "<div class='psw-media__info'>".__('No media', 'vidso')."</div>";
        } else {
            if($instance['media_type'] == 'audio') {
                echo "<div class='psw-media__info'>".__('No audio', 'vidso')."</div>";
            } elseif ($instance['media_type'] == 'video') {
                echo "<div class='psw-media__info'>".__('No video', 'vidso')."</div>";
            }
        }
        ?>
      </div>
    </div>
    <?php } ?>
</div>

<?php

echo $args['after_widget'];

// EOF
