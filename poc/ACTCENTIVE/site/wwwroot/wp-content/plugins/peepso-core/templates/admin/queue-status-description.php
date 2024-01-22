<hr>
<div style="opacity: 0.7;font-size:10px;">
    <p>
        <?php echo __('PeepSo uses various scheduled tasks (cron jobs) in order to perform background actions such as: sending emails, generating GDPR exports, performing routine database maintenance etc.','peepso-core'); ?>
        <br/>
        <?php echo __('The Queues menu lets you check if these tasks are running as expected, and in case of issues, debug them.','peepso-core'); ?>
        <br/>
        <?php echo sprintf(__('Read more in the %s','peepso-core'),  '<a href="https://peep.so/docs_cron" target="_blank">'.__('Cron Jobs and Queues documentation','peepso-core').' <i class="fa fa-external-link"></i></a>'); ?>
    </p>

    <p>

        <strong>
            <?php echo __('Waiting', 'peepso-core');?>:
        </strong>

        <?php echo __('waiting to be processed', 'peepso-core');?>

        <br/>

        <strong>
            <?php echo __('Processing', 'peepso-core');?>:
        </strong>

        <?php echo __('currently being processed', 'peepso-core');?>

        <br/>

        <strong>
            <?php echo __('Success', 'peepso-core');?>:
        </strong>

        <?php echo __('processing complete', 'peepso-core');?>

        <br/>

        <strong>
            <?php echo __('Retry', 'peepso-core');?>:
        </strong>

        <?php echo __('process failed, but will be tried again later', 'peepso-core');?>

        <br/>

        <strong>
            <?php echo __('Failed', 'peepso-core');?>:
        </strong>

        <?php echo __('process failed despite retrying', 'peepso-core');?>

    </p>
</div>