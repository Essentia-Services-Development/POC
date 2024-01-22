<?php
$online = '';
if (PeepSo3_Mayfly::get('peepso_cache_'.$member->get_id().'_online')) {
    $online = PeepSoTemplate::exec_template('profile', 'online', array('PeepSoUser' => $member, 'class' => 'ps-online--md ps-online--static'), TRUE);
}
?>
<div class="ps-member__header">
    <a class="ps-avatar ps-avatar--member" href="<?php echo $member->get_profileurl(); ?>">
        <img alt="<?php echo strip_tags($member->get_fullname()); ?> avatar" src="<?php echo $member->get_avatar(); ?>"><?php echo $online; ?>
    </a>
    <div class="ps-member__cover" style="background-image:url('<?php echo $member->get_cover(750); ?>')"></div>
    <div class="ps-member__options">
        <?php PeepSoMemberSearch::member_options($member->get_id()); ?>
    </div>
</div>
<div class="ps-member__body">
    <div class="ps-member__name">
        <a href="<?php echo $member->get_profileurl(); ?>" class="" data-title="<?php echo strip_tags($member->get_fullname()); ?>" alt="<?php echo strip_tags($member->get_fullname()); ?> avatar">
        <?php 
        do_action('peepso_action_render_user_name_before', $member->get_id());
        echo $member->get_fullname();
        do_action('peepso_action_render_user_name_after', $member->get_id()); 
        ?>
        </a>
    </div>
    <div class="ps-member__details">
    <?php
    do_action('peepso_after_member_thumb', $member->get_id());

    $count = PeepSoUserFollower::count_followers($member->get_id(), true);
    $href = $member->get_profileurl() . 'followers';
    $followers =  sprintf(_n('%d Follower', '%d Followers', $count, 'peepso-core'), $count);
    if(!$count) {
        $followers = __('No followers', 'peepso-core');
    }
    echo '<a class="ps-friends__mutual" href="'.$href.'"><i class="gcis gci-user-check"></i> '. $followers .'</a> &nbsp;     ';



    ?>
    </div>
    <?php 
    if (!isset($hide_member_buttons_extra)) {
        PeepSoMemberSearch::member_buttons_extra($member->get_id());
    }
    ?>
</div>