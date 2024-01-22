<div class="peepso ps-page-profile ps-page--group">
    <?php PeepSoTemplate::exec_template('general','navbar'); ?>

    <?php $PeepSoGroupUser = new PeepSoGroupUser($group->id, get_current_user_id());?>
    <?php if ($PeepSoGroupUser->can('access')) { ?>

    <?php PeepSoTemplate::exec_template('groups', 'group-header', array('group' => $group, 'group_segment' => $group_segment)); ?>

    <div class="ps-files">
        <?php
            if (!get_current_user_id()) {
                PeepSoTemplate::exec_template('general', 'login-profile-tab');
            }
        ?>

        <div class="ps-files__header">
            <div class="ps-files__list-view">
                <div class="ps-btn__group">
                <a href="#" class="ps-btn ps-btn--sm ps-btn--app ps-btn--cp ps-tip ps-tip--arrow ps-js-files-viewmode ps-btn--active" data-mode="list" aria-label="<?php echo __('List', 'peepsofileuploads');?>"><i class="gcis gci-th-list"></i></a>
						<a href="#" class="ps-btn ps-btn--sm ps-btn--app ps-btn--cp ps-tip ps-tip--arrow ps-js-files-viewmode" data-mode="grid" aria-label="<?php echo __('Grid', 'peepsofileuploads');?>"><i class="gcis gci-th-large"></i></a>
                </div>
            </div>
        </div>

        <div class="mb-20"></div>
        <div class="ps-files__list ps-files__list--files ps-js-files"></div>
        <!-- file item template -->
        <script type="text/template" class="ps-js-files-templates" data-name="file-item">
            <div class="ps-file-item-wrapper ps-js-item" data-id="{{= data.id }}">
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
                    <a class="ps-tip ps-tip--arrow" aria-label="<?php echo __('Download', 'peepsofileuploads'); ?>" href="{{= data.download_link }}" download="{{= data.name }}">
                        <i class="gcis gci-download"></i>
                    </a>
                    {{ if (data.can_delete) { }}
                    <a class="ps-tip ps-tip--arrow ps-js-item-delete" aria-label="<?php echo __('Delete', 'peepsofileuploads'); ?>" href="#">
                        <i class="gcis gci-trash"></i>
                    </a>
                    {{ } }}
                </div>
            </div>
        </script>
        <div class="ps-scroll ps-js-files-triggerscroll">
            <img class="post-ajax-loader ps-js-files-loading" src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" alt="" style="display:none" />
        </div>
        <div class="mb-20"></div>

    </div>
    <?php } ?>

</div><!--end row-->

<?php PeepSoTemplate::exec_template('activity','dialogs'); ?>
