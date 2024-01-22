<?php

$config = array();
$config['limit'] = (isset($instance['limit']) && is_int($instance['limit'])) ? $instance['limit'] : 6;

echo $args['before_widget'];
if (isset($instance['user_id'])) {
    $owner = PeepSoUser::get_instance($instance['user_id']);
    $url = $owner->get_profileurl() . 'files';
} else {
    $url = '#';
}
?>

<div class="ps-widget__wrapper<?php echo $instance['class_suffix']; ?> ps-widget<?php echo $instance['class_suffix']; ?> ps-js-widget-my-files"
        data-limit="<?php echo $config['limit']; ?>">
    <div class="ps-widget__header<?php echo $instance['class_suffix']; ?>">
        <a href="<?php echo $url; ?>">
        <?php
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        ?>
        </a>
    </div>
    <div class="ps-widget__body<?php echo $instance['class_suffix']; ?>">
        <div class="ps-js-widget-content"></div>
        <script type="text/template" data-name="item-template">
            <div class="ps-file-item-wrapper" data-id="{{= data.id }}">
                <div class="ps-file-item-content">
                    <div class="ps-file-item-content__icon ps-file-item-content__icon--{{= data.extension }}">
                        <div class="ps-file-item-content__icon-image">
                            {{= data.extension }}
                        </div>
                    </div>
                    <div class="ps-file-item-content__details">
                        <div class="ps-file-item-content__name" title="{{= data.name }}">{{= data.name }}</div>
                        <div class="ps-file-item-content__size">{{= data.size }}</div>
                    </div>
                </div>
                <div class="ps-file-item-action">
                    <a class="ps-tip ps-tip--arrow" aria-label="<?php echo __('Download', 'peepsofileuploads') ?>" href="{{= data.download_link }}" download="{{= data.name }}">
                        <i class="gcis gci-download"></i>
                    </a>
                </div>
            </div>
        </script>
    </div>
</div>

<?php
echo $args['after_widget'];

// EOF
