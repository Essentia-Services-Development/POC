<?php
if(get_current_user_id()) {
$user_stream_filters = PeepSoUser::get_stream_filters();

    /*
    * 0 == never
    * 1 == mobile
    * 2 == desktop
    * 3 == always
    */
    $compact = PeepSo::get_option_new('stream_filters_compact');
    $compact_class = [];
    if(in_array($compact,[1,3])) {
        $compact_class []= 'ps-posts__filters--compact-mobile'; // @TODO TBD
    }

    if(in_array($compact,[2,3])) {
        $compact_class []= 'ps-posts__filters--compact-desktop';// @TODO TBD
    }

    $compact_class = implode(' ', $compact_class);
?>

    <div class="ps-posts__filters <?php echo $compact_class;?>">

        <div class="ps-posts__filters-group ps-posts__filters-group--primary">
            <a href="javascript:" class="ps-posts__filters-toggle ps-js-activitystream-filters-toggle">
                <?php echo __('Filters', 'peepso-core'); ?>:
                <span></span>
            </a>
            <div class="ps-posts__filters-wrapper ps-js-activitystream-filters-wrapper" style="display:none">
                <?php
                /** HIDE MY POSTS **/
                $show_my_posts_list =  apply_filters( 'peepso_show_my_posts_list', [] );

                reset($show_my_posts_list);
                $default = key($show_my_posts_list);

                $show_my_posts = $user_stream_filters['show_my_posts'];

                if(!array_key_exists($show_my_posts, $show_my_posts_list)) {
                    $show_my_posts = $default;
                }

                $selected = $show_my_posts_list[$show_my_posts];
                ?>

                <input type="hidden" id="peepso_stream_filter_show_my_posts" value="<?php echo $show_my_posts; ?>" />
                <div class="ps-posts__filter ps-posts__filter--myposts ps-js-dropdown ps-js-activitystream-filter" data-id="peepso_stream_filter_show_my_posts">
                    <a href="javascript:" class="ps-posts__filter-toggle ps-js-dropdown-toggle" aria-haspopup="true">
                        <i class="<?php echo $selected['icon']; ?>"></i>
                        <span><?php echo $selected['label'];?> </span>
                    </a>
                    <div class="ps-posts__filter-box ps-posts__filter-box--myposts ps-js-dropdown-menu" role="menu">
                        <?php foreach ($show_my_posts_list as $key => $value) { ?>
                            <a class="ps-posts__filter-select" data-option-value="<?php echo $key; ?>" role="menuitem">
                                <div class="ps-checkbox">
                                    <input type="radio" name="peepso_stream_filter_show_my_posts" id="peepso_stream_filter_show_my_posts_opt_<?php echo $key ?>"
                                        value="<?php echo $key ?>" <?php if($key == $show_my_posts) echo "checked"; ?> />
                                    <label for="peepso_stream_filter_show_my_posts_opt_<?php echo $key ?>">
                                        <span><?php echo $value['label']; ?></span>
                                    </label>
                                    <i class="<?php echo $value['icon']; ?>"></i>
                                </div>
                            </a>
                        <?php } ?>
                        <div class="ps-posts__filter-actions">
                            <button class="ps-posts__filter-action ps-btn ps-btn--sm ps-js-cancel"><?php echo __('Cancel', 'peepso-core'); ?></button>
                            <button class="ps-posts__filter-action ps-btn ps-btn--sm ps-btn--action ps-js-apply"><?php echo __('Apply', 'peepso-core'); ?></button>
                        </div>
                    </div>
                </div>

                <?php

                /** sort by latest or recently commented **/

                $sort_posts =  apply_filters( 'peepso_stream_sort_list', [] );
                $sort_by = PeepSo::get_option_new('stream_sort_default');

                // @todo the default is being ignored here
                if(isset($user_stream_filters['sort_by']) && array_key_exists($user_stream_filters['sort_by'], $sort_posts)) {
                    $sort_by = $user_stream_filters['sort_by'];
                }

                $selected = $sort_posts[$sort_by];
                ?>

                <input type="hidden" id="peepso_stream_filter_sort_by" value="<?php echo $sort_by; ?>" />

                <div class="ps-posts__filter ps-posts__filter--myposts ps-js-dropdown ps-js-activitystream-filter" data-id="peepso_stream_filter_sort_by">
                    <a href="javascript:" class="ps-posts__filter-toggle ps-js-dropdown-toggle" aria-haspopup="true">
                        <i class="<?php echo $selected['icon']; ?>"></i>
                        <span><?php echo $selected['label']; ?></span>
                    </a>
                    <div class="ps-posts__filter-box ps-posts__filter-box--myposts ps-js-dropdown-menu" role="menu">
                        <?php foreach ($sort_posts as $key => $value) { ?>
                            <a class="ps-posts__filter-select" data-option-value="<?php echo $key; ?>" role="menuitem">
                                <div class="ps-checkbox">
                                    <input type="radio" name="peepso_stream_filter_sort_by" id="peepso_stream_filter_sort_by_opt_<?php echo $key ?>"
                                        value="<?php echo $key ?>" <?php if($key == $sort_by) echo "checked"; ?> />
                                    <label for="peepso_stream_filter_sort_by_opt_<?php echo $key ?>">
                                        <span><?php echo $value['label']; ?></span>
                                    </label>
                                    <i class="<?php echo $value['icon']; ?>"></i>
                                </div>
                            </a>
                        <?php } ?>
                        <div class="ps-posts__filter-actions">
                            <button class="ps-posts__filter-action ps-btn ps-btn--sm ps-js-cancel"><?php echo __('Cancel', 'peepso-core'); ?></button>
                            <button class="ps-posts__filter-action ps-btn ps-btn--sm ps-btn--action ps-js-apply"><?php echo __('Apply', 'peepso-core'); ?></button>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <?php

        /** SEARCH POSTS **/
        $search = FALSE;
        $PeepSoUrlSegments = PeepSoUrlSegments::get_instance();

        #4158 ?search/querystring does not work with special chars
        if('search' == $PeepSoUrlSegments->get(1)) {
            $search = $PeepSoUrlSegments->get(2);
        }

        #4158 ?search/querystring does not work with special chars
        if(isset($_GET['filter'])) {
            $PeepSoInput = new PeepSoInput();
            $search = $PeepSoInput->value('filter', '', FALSE);
        }
        ?>
        <div class="ps-posts__filters-group ps-posts__filters-group--secondary">

            <input type="hidden" id="peepso_search" value="<?php echo $show_my_posts; ?>" />
            <div class="ps-posts__filter ps-posts__filter--search ps-js-dropdown ps-js-activitystream-filter" data-id="peepso_search">
                <a class="ps-posts__filter-toggle ps-js-dropdown-toggle" aria-haspopup="true" aria-label="<?php echo __('Search', 'peepso-core'); ?>">
                    <i class="gcis gci-search"></i>
                    <span data-empty="<?php //echo __('Search', 'peepso-core'); ?>"
                          data-keyword="<?php echo __('Search: ', 'peepso-core'); ?>"></span>
                </a>
                <div class="ps-posts__filter-box ps-posts__filter-box--search ps-js-dropdown-menu" role="menu">
                    <div class="ps-posts__filter-search">
                        <i class="gcis gci-search"></i><input type="text" id="ps-activitystream-search" class="ps-input ps-input--sm"
                                                              placeholder="<?php echo __('Type to search', 'peepso-core'); ?>" value="<?php echo $search;?>" />
                    </div>

                    <a role="menuitem" class="ps-posts__filter-select" data-option-value="exact">
                        <div class="ps-checkbox">
                            <input type="radio" name="peepso_search" id="peepso_search_opt_exact" value="exact" checked />
                            <label for="peepso_search_opt_exact">
                                <span><?php echo __('Exact phrase', 'peepso-core'); ?></span>
                            </label>
                        </div>
                    </a>
                    <a role="menuitem" class="ps-posts__filter-select" data-option-value="any">
                        <div class="ps-checkbox">
                            <input type="radio" name="peepso_search" id="peepso_search_opt_any" value="any" />
                            <label for="peepso_search_opt_any">
                                <span><?php echo __('Any of the words', 'peepso-core'); ?></span>
                            </label>
                        </div>
                    </a>
                    <div class="ps-posts__filter-actions">
                        <button class="ps-posts__filter-action ps-btn ps-btn--sm ps-js-cancel"><?php echo __('Cancel', 'peepso-core'); ?></button>
                        <button class="ps-posts__filter-action ps-btn ps-btn--sm ps-btn--action ps-js-search"><?php echo __('Search', 'peepso-core'); ?></button>
                    </div>
                </div>
            </div>
            <?php do_action('peepso_action_render_stream_filters'); ?>
        </div>
    </div>

<?php }
