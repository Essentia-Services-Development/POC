<?php

namespace FSPoster\App\Pages\Accounts\Views;

use FSPoster\App\Providers\Pages;

defined( 'MODAL' ) or exit;
?>

<div class="fsp-modal-header">
	<div class="fsp-modal-title">
		<div class="fsp-modal-title-icon">
			<i class="threads-icon threads-icon-18"></i>
		</div>
		<div class="fsp-modal-title-text">
			<?php echo fsp__( 'Add a Threads account' ); ?>
		</div>
	</div>
	<div class="fsp-modal-close" data-modal-close="true">
		<i class="fas fa-times"></i>
	</div>
</div>
<div class="fsp-modal-body">
	<p class="fsp-modal-p fsp-is-jb">
		<?php echo fsp__( 'Enter your Threads (Instagram) username and password!' ); ?>
		<a href="https://www.fs-poster.com/documentation/fs-poster-schedule-auto-publish-wordpress-posts-to-threads" target="_blank" class="fsp-tooltip" data-title="<?php echo fsp__( 'How to?' ); ?>">
			<i class="far fa-question-circle"></i>
		</a>
	</p>

    <div class="fsp-form-group">
        <label><?php echo fsp__( 'Username' ); ?></label>
        <div class="fsp-form-input-has-icon">
            <i class="fas fa-user"></i>
            <input id="fspUsername" autocomplete="off" class="fsp-form-input" placeholder="<?php echo fsp__( 'Enter the username' ); ?>">
        </div>
    </div>
    <div class="fsp-form-group">
        <label><?php echo fsp__( 'Password' ); ?></label>
        <div class="fsp-form-input-has-icon">
            <i class="fas fa-key"></i>
            <input id="fspPassword" autocomplete="off" type="password" class="fsp-form-input" placeholder="<?php echo fsp__( 'Enter the password' ); ?>">
        </div>
    </div>
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
<div class="fsp-modal-footer">
	<button class="fsp-button fsp-is-gray" data-modal-close="true"><?php echo fsp__( 'Cancel' ); ?></button>
	<button id="fspModalAddButton" class="fsp-button"><?php echo fsp__( 'ADD' ); ?></button>
</div>

<script>
	jQuery( document ).ready( function () {
		FSPoster.load_script( '<?php echo Pages::asset( 'Accounts', 'js/fsp-accounts-threads.js' ); ?>' );
	} );
</script>
