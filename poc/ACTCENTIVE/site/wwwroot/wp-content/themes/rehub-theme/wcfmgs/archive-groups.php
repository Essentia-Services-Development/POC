<?php
/**
 * WCFMgs plugin templates
 *
 * Main content area
 *
 * @author      WC Lovers
 * @package     wcfmgs/templates/archive-groups
 * @version   2.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $WCFM, $WCFMgs, $post;

get_header( 'shop' );

do_action( 'woocommerce_before_main_content' );

?>
<div class="rh-container"> 
    <div class="rh-content-wrap clearfix">
        <!-- Main Side -->
        <div class="main-side woocommerce page clearfix" id="content">
            <article class="post" id="page-<?php the_ID(); ?>">
                <header class="woocommerce-products-header">
                    <?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
                        <h1 class="woocommerce-products-header__title page-title"><?php esc_html_e( 'Groups', 'rehub-theme' ); ?></h1>
                    <?php endif; ?>

                    <?php
                    do_action( 'woocommerce_archive_description' );
                    ?>
                </header>
                <?php

                if ( have_posts() ) {

                    do_action( 'wcfmgs_before_groups_loop' );

                    ?>
                        <div class="products col_wrap_fourth">
                            <?php
                            while ( have_posts() ) {
                                the_post();
                    
                                do_action( 'wcfmgs_groups_loop' );
                    
                                $WCFMgs->template->get_template_part( 'content', 'groups' );
                            }
                            ?>
                        </div>
                    <?php

                    do_action( 'wcfmgs_after_groups_loop' );
                } else {
                    do_action( 'wcfmgs_no_groups_found' );
                }

                do_action( 'woocommerce_after_main_content' );

                ?>
            </article>
        </div>
        <!-- /Main Side --> 
        <?php 
            /**
             * Hook: woocommerce_sidebar.
             *
             * @hooked woocommerce_get_sidebar - 10
             */
            do_action( 'woocommerce_sidebar' );
        ?>
    </div>
</div>
<!-- /CONTENT -->    

<?php
get_footer( 'shop' );