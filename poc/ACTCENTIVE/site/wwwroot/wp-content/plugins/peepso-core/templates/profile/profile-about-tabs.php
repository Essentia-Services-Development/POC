<?php
$PeepSoUser = PeepSoUser::get_instance(PeepSoProfileShortcode::get_instance()->get_view_user_id());
if( get_current_user_id() == $PeepSoUser->get_id()) {
  $current_tab = isset($current_tab) ? $current_tab : 'about';
  ?>

  <div class="ps-tabs ps-tabs--center ps-profile__edit-tabs">
  <?php
    foreach($tabs as $key => $tab){

        foreach(['link','icon','label'] as $required_key) {
            if(!array_key_exists($required_key, $tab)) {
                new PeepSoError("Profile 'about' sub-menu: $key is missing $required_key");
            }
	    }

        ?>
      <div class="ps-tabs__item ps-tabs__item--<?php echo strtolower($tab['label']); ?> <?php if ($key == $current_tab) echo "ps-tabs__item--active"; ?>"><a
        href="<?php echo $tab['link']; ?>"><i class="<?php echo $tab['icon']; ?>"></i><span><?php echo $tab['label']; ?></span></a>
      </div>
    <?php
    }
  ?>
  </div>
<?php
}
