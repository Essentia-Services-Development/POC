<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<?php wp_enqueue_style('rhcomments');?>
<div id="comments" class="clearfix">
<?php if(rehub_option('rehub_disable_comments') != '1') :?>
    <?php $postid = get_the_ID();?>
    <div class="post-comments">
        <?php
            if ( comments_open() ) :
            echo "<div class='title_comments'>";
            $title_nocomments = (rehub_option('rehub_commenttitle_text') != '') ? rehub_option('rehub_commenttitle_text') : esc_html__('We will be happy to hear your thoughts','rehub-theme');
            comments_number($title_nocomments, esc_html__('1 Comment','rehub-theme'), '% ' . esc_html__('Comments','rehub-theme') );
            echo "</div>";
            endif;
        ?>
    <?php if ((rehub_option('type_user_review') == 'full_review' || rehub_option('type_user_review') == 'user') && get_comments_number() > 1) :?>
        <?php wp_enqueue_script('rhcommentsort');?>
        <div id="rehub-comments-tabs" class="mb30 rh_grey_tabs_span" data-postid = "<?php echo ''.$postid;?>">
            <span data-tabID="1" data-posttype="post" class="active lineheight20"><?php esc_html_e('Show all', 'rehub-theme'); ?></span>
            <span data-tabID="2" data-posttype="post" class="lineheight20"><?php esc_html_e('Most Helpful', 'rehub-theme'); ?></span>
            <span data-tabID="3" data-posttype="post" class="lineheight20"><?php esc_html_e('Highest Rating', 'rehub-theme'); ?></span>
            <span data-tabID="4" data-posttype="post" class="lineheight20"><?php esc_html_e('Lowest Rating', 'rehub-theme'); ?></span>
            <a href="#respond" class="rehub_scroll add_user_review_link def_btn"><?php esc_html_e("Add your review", "rehub-theme"); ?></a>
        </div>
    <?php endif ;?>
    <div id="tab-1">
        <ol class="commentlist">
            <?php

                $commenter = wp_get_current_commenter();
                $comment_author_email = $commenter['comment_author_email'];
                $user_ID = get_current_user_id();

                $comment_args = array(
                  'post_id' => $postid,
                  'orderby' => 'comment_date',
                  'order'   => 'DESC',
                  'update_comment_meta_cache' => false,
                  'status'  => 'approve',                    
                );

                if ( $user_ID ) {
                    $comment_args['include_unapproved'] = array( $user_ID );
                } elseif ( ! empty( $comment_author_email ) ) {
                    $comment_args['include_unapproved'] = array( $comment_author_email );
                }                

                $comments_v = get_comments($comment_args);                

                wp_list_comments(array(
                  'avatar_size'   => 50,
                  'max_depth'     => 4,
                  'style'         => 'ul',
                  'callback'      => 'rehub_framework_comments',
                  'reverse_top_level' => (get_option('comment_order')==='asc' ? 1 : 0),
                ), $comments_v);
                unset($comments_v);
            ?>
        </ol>
        <div id='comments_pagination'>
                <?php paginate_comments_links(array('prev_text' => '&laquo;', 'next_text' => '&raquo;')); ?>
        </div>      
    </div>

    <ol id="loadcomment-list" class="commentlist">
    </ol>
        <?php
            $custom_comment_field = '<textarea id="comment" name="comment" cols="30" rows="10" aria-required="true" aria-label="comment"></textarea>';
            $commenter = wp_get_current_commenter();
            comment_form(array(
                'comment_field'         => $custom_comment_field,
                'comment_notes_after'   => '',
                'logged_in_as'          => '',
                'comment_notes_before'  => '',
                'title_reply'           => esc_html__('Leave a reply', 'rehub-theme'),
                'cancel_reply_link'     => esc_html__('Cancel reply', 'rehub-theme'),
                'label_submit'          => esc_html__('Submit', 'rehub-theme'),
                'fields' => apply_filters( 'comment_form_default_fields', array(

                    'author' =>
                        '<div class="usr_re"><input id="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) .'" name="author" placeholder="'.__('Name', 'rehub-theme').'"></div>',

                    'email' =>
                        '<div class="email_re"><input id="email" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) .'" name="email" placeholder="'.__('E-mail', 'rehub-theme').'"></div>',

                    'url' =>
                        '<div class="site_re end"><input id="url" type="text" value="' . esc_attr( $commenter['comment_author_url'] ) .'" name="url" placeholder="'.__('Website', 'rehub-theme').'"></div><div class="clearfix"></div>',
                )
              ),
            ));
         ?>
    </div> <!-- end comments div -->
<?php endif;?>
</div>
<?php if(rehub_option('rehub_single_after_comment')) : ?><div class="mediad mediad_after_comment mb15"><?php echo do_shortcode(rehub_option('rehub_single_after_comment')); ?></div><div class="clearfix"></div><?php endif; ?>