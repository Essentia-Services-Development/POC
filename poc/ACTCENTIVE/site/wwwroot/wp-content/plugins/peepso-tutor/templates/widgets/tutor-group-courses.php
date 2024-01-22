<?php
echo $args['before_widget'];
?>

<div class="ps-widget__wrapper<?php echo $instance['class_suffix'];?> ps-widget<?php echo $instance['class_suffix'];?>">
    <div class="ps-widget__header<?php echo $instance['class_suffix'];?>">
        <?php
        if ( ! empty( $instance['title_gc'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title_gc'] ). $args['after_title'];
        }
        ?>
    </div>
<?php

foreach($instance['courses'] as $course_id) {
    $course = get_post($course_id);

    $avatar = get_the_post_thumbnail_url($course_id);
    $url = get_the_permalink($course_id);
    $name = $course->post_title;
    ?>
    <div class="ps-tutorlms__group">
        <a class="ps-tutorlms__group-inner" href="<?php echo $url;?>" title="<?php echo $name;?>">
            <div class="ps-tutorlms__group-thumbnail" style="background-image: url('<?php echo $avatar;?>');"></div>
            <div class="ps-tutorlms__group-name">
                <?php echo $name; ?> 
            </div>
        </a>
    </div>
    <?php
}
?>

</div>

<?php
echo $args['after_widget'];
// EOF