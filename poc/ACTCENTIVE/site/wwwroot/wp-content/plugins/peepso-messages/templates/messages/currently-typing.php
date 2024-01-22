<div class="ps-chat__message">
    <a class="ps-chat__message-avatar ps-avatar ps-tip ps-tip--arrow ps-tip--left"
            href="<?php echo $user->get_profileurl(); ?>"
            aria-label="<?php echo $user->get_fullname(); ?>">
        <img src="<?php echo $user->get_avatar(); ?>" alt="" />
    </a>
    <div class="ps-chat__message-body">
        <div class="ps-chat__message-user">
            <a href="<?php echo $user->get_profileurl(); ?>"><?php
                do_action('peepso_action_render_user_name_before', $user->get_id());
                echo $user->get_fullname();
                do_action('peepso_action_render_user_name_after', $user->get_id());
            ?></a>
        </div>
        <div class="ps-chat__bubble-wrapper">
            <div class="ps-typing-indicator">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </div>
</div>
