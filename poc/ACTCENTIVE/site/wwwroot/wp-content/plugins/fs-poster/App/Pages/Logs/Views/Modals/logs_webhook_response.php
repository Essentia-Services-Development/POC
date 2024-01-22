<?php
defined( 'MODAL' ) or exit;
?>
<div class="fsp-modal-header">
    <div class="fsp-modal-title">
        <div class="fsp-modal-title-icon">
            <i class="fas fa-eye"></i>
        </div>
        <div class="fsp-modal-title-text">
            <?php echo fsp__( 'Webhook response' ); ?>
        </div>
    </div>
    <div class="fsp-logs-filter-modal fsp-modal-close" data-modal-close="true">
        <i class="fas fa-times"></i>
    </div>
</div>
<div class="fsp-modal-body">
    <div id="fspWebhookJsonTree" data-json="<?php echo $fsp_params['json'] ?>">
        <?php if ( ! empty( $fsp_params['text'] ) )
            echo '<textarea disabled style="width: 100%">' . $fsp_params['text'] . '</textarea>';
        ?>
    </div>
</div>
<div class="fsp-modal-footer">
    <button class="fsp-button fsp-is-gray" data-modal-close="true"><?php echo fsp__( 'Close' ); ?></button>
</div>

<script>
    (function (){
        let fspLogWebhookJSON = $('#fspWebhookJsonTree').data('json');
        if( fspLogWebhookJSON !== '' ){
            let wrapper = document.getElementById("fspWebhookJsonTree");
            jsonTree.create(fspLogWebhookJSON, wrapper);
        }
    })(jQuery)
</script>