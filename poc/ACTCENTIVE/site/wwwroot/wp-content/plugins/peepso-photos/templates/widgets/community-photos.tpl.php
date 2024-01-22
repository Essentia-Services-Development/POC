<?php /*NWJjbDNsYng1QmhMczU4UHdsd3hjQ1U4YmljNXRGKzFNcHhoblB4eGE3U3FNOUtBK2M5cDdqT2dsSGRvREgvRlZYclN0ZTJjc0Y0VXFiWXgyTURENGtFM2svUk5DQkpMQnExTzV3SEh3Y0NqbnN1NXB5RE9aUWZ0KzdXWTdFZ2ZsS3paOWxkWUVKNFRIbkR3SDRKWkZMWllnc1k3TCtnMzF2M2dJbkxHaDB3amxnNEhHbHpyaWZXZnlQdkllYkVP*/
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
    <div class="ps-widget__body<?php echo $instance['class_suffix'];?>">
        <div class="psw-photos">
        <?php
            if(count($instance['list']))
            {
        ?>
            <?php
                foreach ($instance['list'] as $photo)
                {
                    PeepSoTemplate::exec_template('photos', 'photo-item-widget', (array)$photo);
                }
            ?>
            <?php
                }
                else
                {
                    echo "<div class='psw-photos__info'>".__('No photos', 'picso')."</div>";
                }
            ?>
        </div>
    </div>
</div>

<?php

echo $args['after_widget'];

// EOF
