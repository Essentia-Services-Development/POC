<?php

namespace FSPoster\App\Pages\Accounts\Views;

use FSPoster\App\Providers\Pages;

defined( 'MODAL' ) or exit;
?>
<link rel="stylesheet" href="<?php echo Pages::asset( 'Accounts', 'css/fsp-accounts-webhook.css' ); ?>">

<div class="fsp-modal-header">
	<div class="fsp-modal-title">
		<div class="fsp-modal-title-icon">
			<i class="fas fa-atlas"></i>
		</div>
		<div class="fsp-modal-title-text">
			<?php echo fsp__( 'Add a Webhook' ); ?>
		</div>
	</div>
	<div class="fsp-modal-close" data-modal-close="true">
		<i class="fas fa-times"></i>
	</div>
</div>
<div class="fsp-modal-body">
	<p class="fsp-modal-p fsp-is-jb">
		<?php echo fsp__( 'Create a Webhook' ); ?>
		<a href="https://www.fs-poster.com/documentation/webhook" target="_blank" class="fsp-tooltip" data-title="<?php echo fsp__( 'How to?' ); ?>">
			<i class="far fa-question-circle"></i>
		</a>
	</p>
	<div class="fsp-modal-options fsp-hide">
		<div class="fsp-modal-option fsp-is-selected" data-step="1">
			<div class="fsp-modal-option-image">
				<img src="<?php echo Pages::asset( 'Accounts', 'img/android.svg' ); ?>">
			</div>
			<?php echo fsp__( 'Create from scratch' ); ?>
		</div>
		<div class="fsp-modal-option" data-step="2">
			<div class="fsp-modal-option-image">
				<img src="<?php echo Pages::asset( 'Accounts', 'img/rocket.svg' ); ?>">
			</div> <?php echo fsp__( 'Use template' ); ?>
		</div>
	</div>
	<div class="fsp-modal-step fsp-hide">
		<select id="fspWebhookTemplates" class="fsp-form-input select2-init" style="width: 100%">
			<option selected disabled><?php echo fsp__( 'Please select a template' ); ?></option>
			<?php foreach ( $fsp_params[ 'webhooks' ] as $index => $webhook ) { ?>
				<option value="<?php echo $index ?>" data-title="<?php echo $webhook[ 'title' ] ?>" data-template="<?php echo $webhook[ 'template' ] ?>" data-icon="<?php echo isset( $webhook[ 'icon' ] ) ? $webhook[ 'icon' ] : '' ?>"><?php echo $webhook[ 'title' ] ?></option>
			<?php } ?>
		</select>
	</div>

	<div id="fspWebhookTemplate">

	</div>
</div>
<div class="fsp-modal-footer">
	<button class="fsp-button fsp-is-gray" data-modal-close="true"><?php echo fsp__( 'Cancel' ); ?></button>
	<!--<button id="fspModalWebhookNextButton" class="fsp-button fsp-is-info"><?php echo fsp__( 'NEXT' ); ?></button> -->
	<button id="fspModalWebhookTestRequestButton" class="fsp-button fsp-is-info fsp-hide"><?php echo fsp__( 'TEST' ); ?></button>
	<button id="fspModalAddWebhookButton" class="fsp-button fsp-hide"><?php echo fsp__( 'ADD' ); ?></button>
</div>

<script>
	jQuery( document ).ready( function () {
		FSPoster.load_script( '<?php echo Pages::asset( 'Accounts', 'js/fsp-accounts-webhook.js' ); ?>', false );
		FSPoster.ajax( 'get_webhook_add_body', { title: '', icon: '', template: { title: '' } }, function ( res ) {
			$( '#fspWebhookTemplate' ).html( FSPoster.htmlspecialchars_decode( res[ 'html' ] ) );
			$( '.fsp-request-content-selector, .fsp-request-method, .fsp-use-proxy' ).change();
			$( '#fspModalAddWebhookButton, #fspModalWebhookTestRequestButton' ).removeClass( 'fsp-hide' );
		} );
	} );
</script>
