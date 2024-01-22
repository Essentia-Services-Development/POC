<div class="ps-conversation-item">
    <div class="ps-conversation-avatar">
        <a class="ps-avatar" href="#">
            <img width=32 src="<?php echo $user->get_avatar();?>" alt="<?php echo $user->get_fullname();?>" class="ps-name-tips ps-messages-currently-typing">
        </a>
    </div>
	<div class="ps-conversation-body">
        <div class="ps-conversation-user">
            <a href="#"><?php echo $user->get_fullname();?></a>
        </div>
		<!-- CSS typing indicator -->
		<div class="ps-typing-indicator ps-typing-indicator-small">
			<span></span>
			<span></span>
			<span></span>
		</div>
	</div>
</div>