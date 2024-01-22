<?php

namespace FSPoster\App\Pages\Accounts\Views;

use FSPoster\App\Providers\Pages;

defined( 'MODAL' ) or exit;
?>

<div class="fsp-modal-header">
	<div class="fsp-modal-title">
		<div class="fsp-modal-title-icon">
			<i class="fab fa-discord"></i>
		</div>
		<div class="fsp-modal-title-text">
			<?php echo fsp__( 'Add a channel' ); ?>
		</div>
	</div>
	<div class="fsp-modal-close" data-modal-close="true">
		<i class="fas fa-times"></i>
	</div>
</div>
<div class="fsp-modal-body">
	<div class="fsp-modal-step">
		<input id="fspAccountID" type="hidden" class="fsp-hide" value="<?php echo $fsp_params[ 'accountId' ]; ?>">
		<div class="fsp-form-group">
			<label><?php echo fsp__( 'Available channels' ); ?></label>
			<select class="fsp-form-select" id="fspModalChannelSelector" disabled>
				<option disabled selected><?php echo fsp__( 'No channels found' ); ?></option>
			</select>
		</div>
	</div>
</div>
<div class="fsp-modal-footer">
	<button class="fsp-button fsp-is-gray" data-modal-close="true"><?php echo fsp__( 'Cancel' ); ?></button>
	<button id="fspModalAddButton" class="fsp-button"><?php echo fsp__( 'ADD' ); ?></button>
</div>

<script>
	jQuery( document ).ready( function () {
		FSPoster.load_script( '<?php echo Pages::asset( 'Accounts', 'js/fsp-accounts-discord-channel.js' ); ?>' );
	} );
</script>
