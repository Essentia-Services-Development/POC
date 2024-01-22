<?php
if(get_current_user_id()) {
    if(isset($args['before_widget'])) {
        echo $args['before_widget'];
    }

    ?>
    <div class="ps-widget__wrapper--external ps-widget--external ps-js-widget-search">

    <div class="ps-widget__header--external">
        <?php
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        ?>
    </div>
    <div class="ps-widget__body--external">
        <div class="ps-widget--search">
            <?php PeepSoTemplate::exec_template('search', 'search', array('context' => 'widget')); ?>
        </div>
    </div>
    </div><?php
if(isset($args['after_widget'])) {
    echo $args['after_widget'];
}
// EOF

}
