<?php

namespace FSPoster\App\Pages\Accounts\Views;

defined( 'MODAL' ) or exit;
?>

<div class="fsp-modal-header">
    <div class="fsp-modal-title">
        <div class="fsp-modal-title-icon">
            <i class="fab fa-xing"></i>
        </div>
        <div class="fsp-modal-title-text">
			<?php echo fsp__( empty( $fsp_params ) ? 'Add a Xing account' : 'Update cookie' ); ?>
        </div>
    </div>
    <div class="fsp-modal-close" data-modal-close="true">
        <i class="fas fa-times"></i>
    </div>
</div>
<div class="fsp-modal-body">
    <?php if ( ! empty( $fsp_params ) ) {?>
        <p class="fsp-modal-p fsp-is-jb">
		    <?php echo fsp__( 'Enter the new cookie value' ) ?>
            <a href="https://www.fs-poster.com/documentation/fs-poster-auto-publish-wordpress-posts-to-xing" target="_blank" class="fsp-tooltip" data-title="<?php echo fsp__( 'How to?' ); ?>">
                <i class="far fa-question-circle"></i>
            </a>
        </p>
        <input id="account_to_update" type="hidden" value="<?php echo $fsp_params; ?>">
    <?php } ?>
    <div class="fsp-modal-step">
        <div class="fsp-form-group">
            <label class="fsp-is-jb">
				<?php echo fsp__( 'Enter the cookie' ); ?> login
                <a href="https://www.fs-poster.com/documentation/fs-poster-auto-publish-wordpress-posts-to-xing" target="_blank" class="fsp-tooltip" data-title="<?php echo fsp__( 'How to?' ); ?>">
                    <i class="far fa-question-circle"></i>
                </a>
            </label>
            <div class="fsp-form-input-has-icon">
                <i class="fas fa-key"></i>
                <input id="fspCookie_login" autocomplete="off" class="fsp-form-input" placeholder="<?php echo fsp__( 'Enter the cookie' ); ?> login">
            </div>
        </div>
        <div class="fsp-form-group">
            <label class="fsp-is-jb">
	            <?php echo fsp__( 'Enter the cookie' ); ?> xing_csrf_token
            </label>
            <div class="fsp-form-input-has-icon">
                <i class="fas fa-key"></i>
                <input id="fspCookie_csrfToken" autocomplete="off" class="fsp-form-input" placeholder="<?php echo fsp__( 'Enter the cookie' ); ?> xing_csrf_token">
            </div>
        </div>
        <div class="fsp-form-group">
            <label class="fsp-is-jb">
				<?php echo fsp__( 'Enter the cookie' ); ?> xing_csrf_checksum
            </label>
            <div class="fsp-form-input-has-icon">
                <i class="fas fa-key"></i>
                <input id="fspCookie_csrfChecksum" autocomplete="off" class="fsp-form-input" placeholder="<?php echo fsp__( 'Enter the cookie' ); ?> xing_csrf_checksum">
            </div>
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
    <button id="<?php echo empty( $fsp_params ) ? "fspModalAddButton" : "fspModalUpdateCookiesButton" ?>Xing" class="fsp-button"><?php echo fsp__( empty( $fsp_params ) ? "ADD" : "UPDATE" ); ?></button>
</div>