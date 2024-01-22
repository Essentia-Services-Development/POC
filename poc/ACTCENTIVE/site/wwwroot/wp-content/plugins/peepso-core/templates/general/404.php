<div class="peepso">
    <?php PeepSoTemplate::exec_template('general','navbar'); ?>
    <?php PeepSoTemplate::exec_template('general', 'register-panel'); ?>
    <section id="mainbody" class="ps-page-unstyled">
        <section id="component" role="article" class="ps-clearfix">

            <h4 class="ps-text--title ps-text--danger"><?php echo __('This content is not available at this time.', 'peepso-core');?></h4>
            <p>
                <?php echo __('Possible causes for the content of the page not to show:', 'peepso-core');?>
            </p>
            <ul class="ps-list-classic">
                <li><?php echo __('It has been removed.', 'peepso-core');?></li>
                <li><?php echo __('You may not have the necessary permissions to view it.', 'peepso-core');?></li>
                <li><?php echo __('If you are logged out. Log in to continue.', 'peepso-core');?></li>
            </ul>
        </section>
    </section>
</div>
