<?php
$config = array();
$config['hideempty'] = (isset($instance['hideempty']) && true == $instance['hideempty']) ? 1 : 0;
$config['totalmember'] = (isset($instance['totalmember']) && true == $instance['totalmember']) ? 1 : 0;
$config['totalonline'] = (isset($instance['totalonline']) && true == $instance['totalonline']) ? 1 : 0;
$config['limit'] = (isset($instance['limit']) && is_int($instance['limit'])) ? $instance['limit'] : 5;

$config['id'] = 'peepso-online-members-'.md5(implode($config));

$PeepSoMemberSearch = PeepSoMemberSearch::get_instance();
if(isset($args['before_widget'])) {
    echo $args['before_widget'];
}

?><div class="ps-widget__wrapper<?php echo $instance['class_suffix']; ?> ps-widget<?php echo $instance['class_suffix']; ?> ps-js-widget-online-members"
        data-hideempty="<?php echo $config['hideempty']; ?>"
       data-totalmember="<?php echo $config['totalmember']; ?>"
       data-totalonline="<?php echo $config['totalonline']; ?>"
        data-limit="<?php echo $config['limit']; ?>">

    <div class="ps-widget__header<?php echo $instance['class_suffix']; ?>">
        <?php
			if (!empty($instance['title'])) {
				echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
			}
		?>
    </div>
    <div class="ps-widget__body<?php echo $instance['class_suffix']; ?>">
        <div class="psw-members ps-js-widget-content" id="<?php echo $config['id'];?>">
        	<img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>">
        </div>
    </div>
</div><?php
if(isset($args['after_widget'])) {
    echo $args['after_widget'];
}
// EOF
