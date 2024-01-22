<?php

$PeepSoPostbox = PeepSoPostbox::get_instance();
$PeepSoMessages = PeepSoMessages::get_instance();
$PeepSoGeneral = PeepSoGeneral::get_instance();
$PeepSoUser = PeepSoUser::get_instance(get_current_user_id());

$pref_url = PeepSoUser::get_instance()->get_profileurl().'about/preferences/';

// TODO:
$notif = TRUE;
$muted = TRUE;

?><div class="peepso">
    <div class="ps-page ps-page--messages">
        <?php PeepSoTemplate::exec_template('general','navbar'); ?>
        <?php wp_nonce_field('load-messages', '_messages_nonce'); ?>


        <div class="ps-messages-page__nav-wrapper">
            <div class="ps-btn__group">
                <a href="#" class="ps-btn ps-btn--sm ps-btn--app ps-btn--cp ps-tip ps-tip--arrow ps-js-messages-show-all active" aria-label="<?php echo __('All', 'peepso-core'); ?>">
                    <i class="gcis gci-list-check"></i>
                </a>
                <a href="#" class="ps-btn ps-btn--sm ps-btn--app ps-btn--cp  ps-tip ps-tip--arrow  ps-js-messages-show-unread" aria-label="<?php echo __('Unread', 'peepso-core'); ?>">
                    <i class="gcis gci-exclamation-circle"></i>
                </a>
            </div>
            <div class="ps-messages__nav-actions">
                <?php if (class_exists('PeepSoMessagesPlugin') && FALSE !== apply_filters('peepso_permissions_messages_create', TRUE)) { ?>
                    <a href="javascript:" class="ps-btn ps-btn--app ps-btn--sm  ps-tip ps-tip--arrow " onclick="ps_messages.new_message(undefined, 'is_friend')" aria-label="<?php echo __('New message', 'msgso'); ?>">
                        <i class="gcis gci-pen-to-square"></i>
                    </a>
                <?php } ?>
            </div>
        </div>

        <div class="ps-messages ps-js-messages">
            <div class="ps-messages__inner">
                <div class="ps-messages__side ps-js-messages-list">
                    <form action="" class="ps-form ps-messages__search ps-js-messages-search-form" role="form" onsubmit="return false;">
                        <div class="ps-messages__search-inner ps-input__wrapper ps-input__wrapper--icon">
                            <i class="ps-input__icon gcis gci-search"></i>
                            <input type="text" class="ps-input ps-input--icon ps-input--sm search-query" name="query" aria-describedby="queryStatus"
                                   value="<?php echo esc_attr($search); ?>" placeholder="<?php echo esc_attr(__('Search...', 'msgso')); ?>" />
                            <button type="reset" class="ps-messages__search-clear ps-js-btn-clear" title="<?php echo __('Clear search', 'msgso') ?>"
                                style="display:none">
                                <i class="gcis gci-circle-xmark"></i>
                            </button>
                        </div>
                        <div class="ps-messages__search-results ps-js-loading" style="display: none;">
                            <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" />
                        </div>
                    </form>

                    <span style="display:none"><?php echo __('No messages found.' ,'msgso'); ?></span>

                    <form class="ps-form ps-messages__inbox" action="<?php PeepSo::get_page('messages');?>" method="post">
                        <div class="ps-messages__list-wrapper ps-js-messages-list-scrollable">
                            <div class="ps-messages__list ps-js-messages-list-table"></div>
                            <div class="ps-js-messages-list-loading" style="text-align: center">
                                <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" />
                            </div>
                        </div>
                    </form>

                </div>

                <div class="ps-messages__view ps-js-conversation-wrapper">
                    <div class="ps-messages__info" style="display:none">
                        <div class="ps-messages__info-inner">
                            <?php if (class_exists('PeepSoMessagesPlugin')) : ?>
                                <p><?php echo __('No messages found.' ,'msgso'); ?></p>
                                <?php do_action('peepso_messages_list_header'); ?>
                            <?php else : ?>
                                <?php echo __('No messages found.' ,'msgso'); ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="ps-js-conversation-loading" style="text-align:center; padding-top:20px; display:none">
                        <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>">
                    </div>
                    <div class="ps-js-conversation" style="display:none">
                        <div class="ps-conversation">
                            <div class="ps-conversation__header">
                                <div class="ps-conversation__back">
                                    <a href="#" class="ps-btn ps-btn--app ps-js-conversation-back">
                                        <i class="gcis gci-left-long"></i><?php echo __('All messages', 'msgso'); ?>
                                    </a>
                                </div>
                                <div class="ps-conversation__header-inner">
                                    <div class="ps-conversation__participants ps-js-participant-summary">
                                        <span class="ps-conversation__participants-label"><?php echo __('Conversation with', 'msgso'); ?>:</span>
                                        <span class="ps-conversation__status"><i class="gcir gci-clock"></i></span>
                                        <span class="ps-js-conversation-participant-summary"></span>
                                    </div>
                                    <div class="ps-conversation__options">
                                        <div class="ps-conversation__options-menu ps-tip ps-tip--arrow ps-js-conversation-options"
                                             data-id="{id}" aria-label="<?php echo __('Options', 'msgso');?>">
                                            <i class="gcis gci-cog"></i>
                                        </div>
                                    </div>
                                    <div class="ps-conversation__dropdown-menu ps-dropdown__menu ps-js-conversation-dropdown"
                                         style="display:none">
                                        <?php if (isset($show_blockuser)) { ?>
                                        <a href="#" class="ps-js-btn-blockuser" data-user-id="<?php echo $show_blockuser_id; ?>">
                                            <i class="gcis gci-ban"></i>
                                            <span><?php echo __('Block this user', 'msgso'); ?></span>
                                        </a>
                                        <?php } ?>
                                        <a href="#" id="add-recipients-toggle">
                                            <i class="gcis gci-user-plus"></i>
                                            <span><?php echo __('Add People to the conversation', 'msgso'); ?></span>
                                        </a>
                                        <?php if (isset($read_notification)) { ?>
                                        <a href="#" class="ps-js-btn-toggle-checkmark <?php echo $notif ? '' : ' disabled' ?>"
                                            onclick="return ps_messages.toggle_checkmark(<?php echo $parent->ID;?>, <?php echo $notif ? 0 : 1 ?>);"
                                        >
                                            <i class="gcir gci-check-circle"></i>
                                            <span><?php echo $notif ? __("Don't send read receipt", 'msgso') : __('Send read receipt', 'msgso'); ?></span>
                                        </a>
                                        <?php 
                                        } 
                                        if (isset($parent)) {
                                        ?>
                                        <a href="#" class="ps-js-btn-mute-conversation"
                                            onclick="return ps_messages.<?php echo $muted ? 'unmute' : 'mute'; ?>_conversation(<?php echo $parent->ID;?>, <?php echo $muted ? 0 : 1; ?>);"
                                        >
                                            <i class="<?php echo $muted ? 'gcis gci-bell-slash' : 'gcir gci-bell'; ?>"></i>
                                            <span><?php echo $muted ? __('Unmute conversation', 'msgso') : __('Mute conversation', 'msgso'); ?></span>
                                        </a>
                                        <?php } ?>
                                        <a href="<?php echo $PeepSoMessages->get_leave_conversation_url();?>"
                                        onclick="return ps_messages.leave_conversation('<?php echo __('Are you sure you want to leave this conversation?', 'msgso'); ?>', this)"
                                        >
                                            <i class="gcis gci-times"></i>
                                            <span><?php echo __('Leave this conversation', 'msgso'); ?></span>
                                        </a>
                                    </div>
                                </div>
                                <div class="ps-conversation__add ps-js-recipients">
                                    <select name="recipients"
                                        data-placeholder="<?php echo __('Add People to the conversation', 'msgso');?>"
                                        data-loading="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>"
                                        multiple></select>
                                    <?php wp_nonce_field('add-participant', 'add-participant-nonce'); ?>
                                    <button class="ps-btn ps-btn--sm ps-btn--action ps-js-add-recipients">
                                        <?php echo __('Done', 'msgso'); ?>
                                        <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" style="display:none;">
                                    </button>
                                </div>
                            </div>

                            <div class="ps-conversation__chat ps-js-conversation-scrollable">
                                <div class="ps-chat__messages">
                                    <div class="ps-js-conversation-messages">
                                        <div class="ps-js-conversation-messages-loading" style="text-align: center; visibility: hidden">
                                            <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" />
                                        </div>
                                        <div class="ps-js-conversation-messages-list"></div>
                                        <div class="ps-js-conversation-messages-temporary"></div>
                                        <div class="ps-js-conversation-messages-typing"></div>
                                    </div>
                                </div>
                            </div>

                            <div id="postbox-message" class="ps-postbox ps-conversation__postbox"></div>
                            <div data-template="postbox" style="display:none !important">
                                <?php $PeepSoPostbox->before_postbox(); ?>
                                <div class="ps-postbox__inner">
                                    <div id="ps-postbox-status" class="ps-postbox__content ps-postbox-content">
                                        <div class="ps-postbox__views ps-postbox-tabs"><?php $PeepSoPostbox->postbox_tabs('messages'); ?></div>
                                        <?php
                                        add_filter('peepso_permissions_post_create', '__return_true', 99);
                                        PeepSoTemplate::exec_template('general', 'postbox-status',['placeholder' => __('Write a message...','msgso')]);
                                        ?>
                                    </div>

                                    <div class="ps-postbox__footer ps-js-postbox-footer ps-postbox-tab ps-postbox-tab-root" style="display: none;">
                                        <div class="ps-postbox__menu ps-postbox__menu--tabs">
                                            <?php $PeepSoGeneral->post_types(array('postbox_message' => TRUE)); ?>
                                        </div>
                                    </div>

                                    <div class="ps-postbox__footer ps-conversation__postbox-footer ps-js-postbox-footer ps-postbox-tab selected interactions">
                                        <div class="ps-postbox__menu ps-postbox__menu--interactions">
                                            <?php $PeepSoPostbox->post_interactions(array('postbox_message' => TRUE)); ?>
                                        </div>
                                        <div class="ps-postbox__actions ps-postbox-action">
                                            <div class="ps-checkbox ps-checkbox--enter">
                                                <input type="checkbox" id="enter-to-send" class="ps-checkbox__input ps-js-checkbox-entertosend">
                                                <label class="ps-checkbox__label" for="enter-to-send">
                                                    <?php printf(__('%s to send', 'msgso'),apply_filters('peepso_chat_enter_to_send','&#9166;')); ?>
                                                </label>
                                            </div>

                                            <?php if(PeepSo::is_admin() && PeepSo::is_dev_mode('embeds')) { ?>
                                                <button type="button" class="ps-btn ps-btn--sm ps-postbox__action ps-postbox__action--cancel ps-js-btn-preview">Fetch URL</button>
                                            <?php } ?>
                                            <button type="button" class="ps-btn ps-btn--sm ps-postbox__action ps-tip ps-tip--arrow ps-postbox__action--cancel ps-button-cancel"
                                                    aria-label="<?php echo __('Cancel', 'peepso-core'); ?>"
                                                    style="display:none"><i class="gcis gci-times"></i></button>
                                            <button type="button" class="ps-btn ps-btn--sm ps-btn--action ps-postbox__action ps-postbox__action--post ps-button-action postbox-submit"
                                                    style="display:none"><?php echo __('Post', 'peepso-core'); ?></button>
                                        </div>
                                        <div class="ps-loading ps-postbox-loading" style="display: none">
                                            <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>">
                                            <div> </div>
                                        </div>
                                    </div>
                                </div>
                                <?php $PeepSoPostbox->after_postbox(); ?>
                            </div>
                        </div>

                        <?php ob_start(); ?>
                        <div class="ps-chat__message ps-chat__message--me ps-js-temporary-message" style="opacity:.5">
                            <a class="ps-chat__message-avatar ps-avatar ps-tip ps-tip--arrow ps-tip--left"
                                    href="<?php echo $PeepSoUser->get_profileurl(); ?>"
                                    aria-label="<?php echo $PeepSoUser->get_fullname(); ?>">
                                <img src="<?php echo $PeepSoUser->get_avatar(); ?>" alt="" />
                            </a>
                            <div class="ps-chat__message-body">
                                <div class="ps-chat__message-user">
                                    <a href="<?php echo $PeepSoUser->get_profileurl(); ?>"><?php
                                        do_action('peepso_action_render_user_name_before', $PeepSoUser->get_id());
                                        echo $PeepSoUser->get_fullname();
                                        do_action('peepso_action_render_user_name_after', $PeepSoUser->get_id());
                                    ?></a>
                                </div>
                                <div class="ps-chat__message-content-wrapper">
                                    <div class="ps-chat__message-content">{{= data.content }}</div>
                                </div>
                                {{ if ('object' === typeof data.attachment) { }}
                                <div class="ps-chat__message-attachments">
                                    <div class="ps-media__attachment ps-media__attachment--{{= 'photo' === data.attachment.type ? 'photos' : data.attachment.type }}">
                                        {{ for ( var i = 0; i < data.attachment.count; i++ ) { }}
                                        <a class="ps-media ps-media--{{= data.attachment.type }}"
                                                style="width:62px; height:62px; line-height:62px; text-align:center; vertical-align:middle; background:lightgrey; border-radius: var(--BORDER-RADIUS--MD);">
	                                        <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>">
                                        </a>
                                        {{ } }}
                                    </div>
                                </div>
                                {{ } }}
                                <div class="ps-chat__message-time">
                                    <i class="gcir gci-check-circle"></i>
                                    <span><?php echo __('just now', 'msgso'); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php

                            $message_item_template = ob_get_clean();
                            echo '<script type="text/template" data-template="message-item">';
                            echo $message_item_template;
                            echo '</script>';

                        ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php PeepSoTemplate::exec_template('activity', 'dialogs'); ?>
