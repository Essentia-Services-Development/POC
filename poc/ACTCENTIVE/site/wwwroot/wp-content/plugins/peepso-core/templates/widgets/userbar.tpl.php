<?php
if(isset($args['before_widget'])) {
  echo $args['before_widget'];
}

$PeepSoProfile=PeepSoProfile::get_instance();
$PeepSoUser = $PeepSoProfile->user;
$position = $instance['content_position'];

$compact_mode_string = '';

// When in preview, disable the mobile "compact mode" trigger
if(!isset($_GET['legacy-widget-preview'])) {
    if (isset($instance['compact_mode']) && in_array($compact_mode = intval($instance['compact_mode']), [1, 2, 3])) {

        if (in_array($compact_mode, [1, 3])) {
            $compact_mode_string .= " psw-userbar--mobile ";
        }

        if (in_array($compact_mode, [2, 3])) {
            $compact_mode_string .= " psw-userbar--desktop ";
        }
    }
}
?>
    <div class="psw-userbar psw-userbar--<?php echo $position; ?> ps-js-widget-userbar <?php echo $compact_mode_string;?>">

		<div class="psw-userbar__inner">
		<?php
			if($instance['user_id'] > 0) {

			$user  = $instance['user'];
			?>

				<div class="psw-userbar__user">
					<div class="ps-notifs psw-notifs--userbar ps-js-widget-userbar-notifications">
						<?php
							do_action('peepso_action_userbar_notifications_before', $user->get_id());

							// Notifications
							echo $instance['toolbar'];

							do_action('peepso_action_userbar_notifications_after', $user->get_id());
						?>
					</div>
				</div>

        <?php
        if(isset($instance['show_name'])) {
          $show_name = intval($instance['show_name']);

          if(in_array($show_name,[1,2])) {
            $name = $user->get_firstname();

            if(2 == $show_name) {
              $name = $user->get_fullname();
            }
        ?>
            <div class="psw-userbar__name"><a href="<?php echo $user->get_profileurl();?>"><?php echo $name; ?></a></div>
        <?php
          }
        }
        ?>

        <?php if(isset($instance['show_vip']) && 1 == intval($instance['show_vip'])) { ?>
        <div class="ps-vip__icons"><?php do_action('peepso_action_userbar_user_name_before', $user->get_id()); ?></div>
        <?php } ?>

        <?php
        if(isset($instance['show_badges']) && 1 == intval($instance['show_badges'])) {
          do_action('peepso_action_userbar_user_name_after', $user->get_id());
        }
        ?>

        <div class="psw-userbar__user-profile">
          <?php if(isset($instance['show_avatar']) && 1 == intval($instance['show_avatar'])) { ?>
          <div class="ps-avatar psw-avatar--userbar">
            <a href="<?php echo $user->get_profileurl();?>">
              <img src="<?php echo $user->get_avatar();?>" alt="<?php echo $user->get_fullname();?> avatar" title="<?php echo $user->get_profileurl();?>">
            </a>
          </div>
          <?php } ?>

          <?php
            // Profile Submenu extra links
                if(apply_filters('peepso_filter_navigation_preferences', TRUE)) {
                    $instance['links']['peepso-core-preferences'] = array(
                        'href' => $user->get_profileurl() . 'about/preferences/',
                        'icon' => 'gcis gci-user-edit',
                        'label' => __('Preferences', 'peepso-core'),
                    );
                }

                if(apply_filters('peepso_filter_navigation_log_out', TRUE)) {
                    $instance['links']['peepso-core-logout'] = array(
                        'href' => PeepSo::get_page('logout'),
                        'icon' => 'gcis gci-power-off',
                        'label' => __('Log Out', 'peepso-core'),
                        'widget' => TRUE,
                    );
                }
          ?>

          <?php if(isset($instance['show_usermenu']) && 1 == intval($instance['show_usermenu'])) { ?>
          <div class="psw-userbar__menu ps-dropdown ps-dropdown--menu ps-dropdown--left ps-js-dropdown">
            <a href="javascript:" class="ps-dropdown__toggle psw-userbar__menu-toggle ps-js-dropdown-toggle">
              <i class="gcis gci-angle-down"></i>
            </a>
            
            <div class="ps-dropdown__menu ps-js-dropdown-menu">
              <?php
                foreach($instance['links'] as $id => $link)
                {
                  if(!isset($link['label']) || !isset($link['href']) || !isset($link['icon'])) {
                    var_dump($link);
                  }

                  $class = isset($link['class']) ? $link['class'] : '' ;

                  $href = $user->get_profileurl(). $link['href'];
                  if('http' == substr(strtolower($link['href']), 0,4)) {
                    $href = $link['href'];
                  }

                  echo '<a href="' . $href . '" class="' . $class . '"><i class="' . $link['icon'] . '"></i> ' . $link['label'] . '</a>';
                }
              ?>
            </div>
          </div>
          <?php } ?>
        </div>
        <?php if(isset($instance['show_logout']) && 1 == intval($instance['show_logout'])) { ?>
        <a class="psw-userbar__logout" href="<?php echo PeepSo::get_page('logout'); ?>" title="<?php echo __('Log Out', 'peepso-core'); ?>" arialabel="<?php echo __('Log Out', 'peepso-core'); ?>">
          <i class="gcis gci-power-off"></i>
        </a>
        <?php } ?>
			<?php
		} else {
			?>
			<a href="<?php echo PeepSo::get_page('activity'); ?>"><?php echo __('Log in', 'peepso-core'); ?></a>
		<?php
		}
		?>
		</div>
		<?php
			if($instance['user_id'] > 0) {

			$user  = $instance['user'];
			?>
			<div class="psw-userbar__toggle psw-userbar__toggle--avatar ps-js-widget-userbar-toggle">
				<div class="ps-avatar psw-avatar--userbar">
					<img src="<?php echo $user->get_avatar();?>" alt="<?php echo $user->get_fullname();?> avatar" title="<?php echo $user->get_profileurl();?>">
				</div>
				<span class="ps-notif__bubble ps-notif__bubble--all ps-js-notif-counter"></span>
				<i class="gcis gci-times-circle"></i>
			</div>
			<?php
		} else {
			?>
			<a href="#" class="psw-userbar__toggle ps-js-widget-userbar-toggle">
				<i class="gcis gci-user"></i>
				<span class="ps-notif__bubble ps-notif__bubble--all ps-js-notif-counter"></span>
			</a>
			<?php
		}
		?>
	</div>

<?php
if(isset($args['after_widget'])) {
  echo $args['after_widget'];
}
// EOF
