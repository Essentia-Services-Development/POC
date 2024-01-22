<?php

namespace FSPoster\App\Pages\Accounts\Views;

use FSPoster\App\Providers\Pages;

defined( 'MODAL' ) or exit;
?>

<link rel="stylesheet" href="<?php echo Pages::asset( 'Accounts', 'css/fsp-accounts-plurk.css' ); ?>">

<div class="fsp-modal-header">
	<div class="fsp-modal-title">
		<div class="fsp-modal-title-icon">
			<i class="fas fa-parking"></i>
		</div>
		<div class="fsp-modal-title-text">
			<?php echo fsp__( 'Add a Plurk account' ); ?>
		</div>
	</div>
	<div class="fsp-modal-close" data-modal-close="true">
		<i class="fas fa-times"></i>
	</div>
</div>
<div class="fsp-modal-body">
	<div class="fsp-plurk-steps">
		<div class="fsp-form-group fsp-plurk-step" data-step="1">
			<input id="plurkRequestToken" type="hidden">
			<input id="plurkRequestTokenSecret" type="hidden">
			<label class="fsp-is-jb">
				<?php echo fsp__( 'Select an App' ); ?>
				<a href="https://www.fs-poster.com/documentation/share-wordpress-posts-to-plurk-automatically" target="_blank" class="fsp-tooltip" data-title="<?php echo fsp__( 'How to?' ); ?>">
					<i class="far fa-question-circle"></i>
				</a>
			</label>
			<select class="fsp-form-select" id="fspModalAppSelector">
				<?php foreach ( $fsp_params[ 'applications' ] as $app ) { ?>
					<option value="<?php echo $app[ 'app_key' ]; ?>"><?php echo esc_html( $app[ 'name' ] ); ?></option>
				<?php }
				if ( empty( $fsp_params[ 'applications' ] ) )
				{ ?>
					<option disabled><?php echo fsp__( 'There isn\'t a Plurk App!' ); ?></option>
				<?php } ?>
			</select>
		</div>
		<div class="fsp-form-group fsp-plurk-step" data-step="2">
			<button type="button" id="fspGetAccessToken" class="fsp-button fsp-plurk-button">
				<?php echo fsp__( 'GET ACCESS' ); ?>
			</button>
		</div>
		<div class="fsp-form-group fsp-plurk-step" data-step="3">
			<?php echo fsp__( 'When the authorization has completed, copy the verification code' ); ?>
		</div>
		<div class="fsp-form-group fsp-plurk-step" data-step="4">
			<label><?php echo fsp__( 'Verification code' ); ?></label>
			<input type="text" id="plurkVerifier" class="fsp-form-input" placeholder="<?php echo fsp__( 'Paste the copied verifier here' ); ?>">
		</div>
	</div>
	<div class="fsp-plurk-steps">
		<div class="fsp-form-checkbox-group">
			<input id="fspUseProxy" type="checkbox" class="fsp-form-checkbox">
			<label for="fspUseProxy">
				<?php echo fsp__( 'Use a proxy' ); ?>
			</label>
			<span class="fsp-tooltip" data-title="<?php echo fsp__( 'Optional field. Supported proxy formats: https://127.0.0.1:8888 or https://user:pass@127.0.0.1:8888' ); ?>"><i class="far fa-question-circle"></i></span>
		</div>
		<div id="fspProxyContainer" class="fsp-form-group fsp-hide fsp-proxy-container">
			<div class="fsp-form-input-has-icon">
				<i class="fas fa-globe"></i>
				<input id="fspProxy" autocomplete="off" class="fsp-form-input fsp-proxy" placeholder="<?php echo fsp__( 'Enter a proxy address' ); ?>">
			</div>
		</div>
	</div>
</div>
<div class="fsp-modal-footer">
	<button class="fsp-button fsp-is-gray" data-modal-close="true"><?php echo fsp__( 'Cancel' ); ?></button>
	<button id="fspModalAddButton" class="fsp-button"><?php echo fsp__( 'ADD' ); ?></button>
</div>

<script>
	jQuery( document ).ready( function () {
		FSPoster.load_script( '<?php echo Pages::asset( 'Accounts', 'js/fsp-accounts-plurk.js' ); ?>' );
	} );
</script>
