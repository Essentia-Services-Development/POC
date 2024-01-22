<?php

namespace FSPoster\App\Pages\Accounts\Views;

use FSPoster\App\Providers\Pages;
use FSPoster\App\Providers\Helper;

defined( 'MODAL' ) or exit;
?>
<link rel="stylesheet" href="<?php echo Pages::asset( 'Accounts', 'css/fsp-accounts-webhook.css' ); ?>">

<div class="fsp-modal-header">
    <div class="fsp-modal-title">
        <div class="fsp-modal-title-icon">
            <i class="fas fa-atlas"></i>
        </div>
        <div class="fsp-modal-title-text">
            <?php echo fsp__( 'Edit the Webhook' ); ?>
        </div>
    </div>
    <div class="fsp-modal-close" data-modal-close="true">
        <i class="fas fa-times"></i>
    </div>
</div>
<div class="fsp-modal-body">
    <div id="fspWebhookTemplate">

    </div>
</div>
<div class="fsp-modal-footer">
    <button class="fsp-button fsp-is-gray" data-modal-close="true"><?php echo fsp__( 'Cancel' ); ?></button>
    <button id="fspModalWebhookTestRequestButton" class="fsp-button fsp-is-info"><?php echo fsp__( 'TEST' ); ?></button>
    <button id="fspModalEditWebhookButton" class="fsp-button"><?php echo fsp__( 'SAVE' ); ?></button>
</div>

<script>
    jQuery( document ).ready( function () {
        FSPoster.ajax( 'get_webhook_add_body', { title:'', icon:'', template: <?php echo json_encode($fsp_params, JSON_UNESCAPED_SLASHES) ?> }, function ( res ) {
            jQuery( '#fspWebhookTemplate' ).html( FSPoster.htmlspecialchars_decode( res[ 'html' ] ) );
            jQuery( '.fsp-request-content-selector, .fsp-request-method, .fsp-use-proxy' ).change();
        } );
        FSPoster.load_script( '<?php echo Pages::asset( 'Accounts', 'js/fsp-accounts-webhook.js' ); ?>', false );
    } );
</script>
