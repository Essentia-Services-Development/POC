<?php
$old_post = $GLOBALS['post'];
$GLOBALS['post'] = $post;
?>
<div class="ps-job">
    <div class="ps-job__inner">
        <div class="ps-job__company-logo"><?php the_company_logo();?></div>
        <div class="ps-job__details">
            <div class="ps-job__title">
                <a href="<?php the_job_permalink(); ?>"><?php the_title();?></a>    
            </div>
            <div class="ps-job__meta">
                <div class="ps-job__company-name"><?php the_company_name();?></div>
                <?php $types = wpjm_get_the_job_types(); ?>
                <?php if ( ! empty( $types ) ) : foreach ( $types as $type ) : ?>
                    <div class="ps-job__type job-types">
                        <span class="job-type <?php echo esc_attr( sanitize_title( $type->slug ) ); ?>"><?php echo esc_html( $type->name ); ?></span>
                    </div>
                <?php endforeach; endif; ?>
            </div>
            <div class="ps-job__excerpt"><?php the_excerpt();?></div>
            <div class="ps-job__location"><i class="gcis gci-map-marker-alt"></i> <?php the_job_location();?></div>
            <div class="ps-job__application ps-js-job-application">
            <?php
                if (candidates_can_apply()) {
                    get_job_manager_template('job-application.php');
                }
            ?>
            </div>
        </div>
    </div>
</div>
<?php

$GLOBALS['post'] = $old_post;