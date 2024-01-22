<div class="ps-chat__message ps-chat__message--me ps-conversation-item my-message my-message-photos-{id}">
  <div class="ps-chat__message-body ps-conversation-body">
    <div class="ps-chat__message-content ps-conversation-content"></div>
    <div class="ps-chat__message-attachments ps-conversation-attachment">
      <div class="ps-media__attachment ps-media__attachment--photos">
        {item}<a class="ps-media ps-media--photo ps-conversation-photo-item ps-conversation-photo-placeholder"><img class="ps-loading__image" src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" /></a>{/item}
      </div>
    </div>
    <div class="ps-chat__message-time ps-conversation-time">
      <i class="gcir gci-check-circle"></i>
      <span><?php echo __('just now', 'msgso'); ?></span>
    </div>
  </div>
</div>
