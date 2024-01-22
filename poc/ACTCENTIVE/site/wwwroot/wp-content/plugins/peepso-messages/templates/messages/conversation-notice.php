<div class="ps-chat__info ps-js-message ps-js-message-<?php echo $ID ?>" data-id="<?php echo $ID ?>">
    <div class="ps-alert ps-alert--neutral ps-alert--cp">
      <i class="gcis gci-user-alt-slash"></i>
	    <em><?php
	    	$user = PeepSoUser::get_instance($post_author);
			if( PeepSoMessagesPlugin::MESSAGE_INLINE_LEFT_CONVERSATION == $post_content) {
				printf(__('%s left the conversation', 'msgso'), $user->get_fullname());
			}

			if( PeepSoMessagesPlugin::MESSAGE_INLINE_NEW_GROUP == $post_content) {
				printf(__('%s created a new group conversation', 'msgso'), $user->get_fullname());
			}

	    ?></em>
    </div>
</div>
