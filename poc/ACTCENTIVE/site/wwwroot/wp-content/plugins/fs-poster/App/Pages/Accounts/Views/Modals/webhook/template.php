<div class="fsp-form-group">
    <label><?php echo fsp__( 'Title' ); ?></label>
    <input type="text" class="fsp-webhook-title fsp-form-input" placeholder="<?php echo fsp__('Enter the title') ?>" value="<?php echo isset( $fsp_params[ 'title' ] ) ? $fsp_params[ 'title' ] : ''; ?>">
    <input id="fspModalWebhookID" type="hidden" value="<?php echo empty($fsp_params[ 'id' ]) ? '' : $fsp_params[ 'id' ] ?>">
    <input id="fspModalWebhookIcon" type="hidden" value="<?php echo isset( $fsp_params[ 'icon' ] ) ? $fsp_params[ 'icon' ] : ''; ?>">
</div>
<div class="fsp-form-group">
    <label><?php echo fsp__( 'URL' ); ?></label>
    <div class="fsp-request-url-input-group">
        <div>
            <select class="fsp-request-method">
                <option value="post" <?php echo isset( $fsp_params[ 'method' ] ) && $fsp_params[ 'method' ] === 'post' ? 'selected' : ''; ?>>POST</option>
                <option value="get" <?php echo isset( $fsp_params[ 'method' ] ) && $fsp_params[ 'method' ] === 'get' ? 'selected' : ''; ?>>GET</option>
                <option value="put" <?php echo isset( $fsp_params[ 'method' ] ) && $fsp_params[ 'method' ] === 'put' ? 'selected' : ''; ?>>PUT</option>
                <option value="delete" <?php echo isset( $fsp_params[ 'method' ] ) && $fsp_params[ 'method' ] === 'delete' ? 'selected' : ''; ?>>DELETE</option>
            </select>
        </div>
        <div>
            <div class="fsp-form-input-has-icon with_keywords_wrapper">
                <i class="fa fa-tag fsp-show-tags-label keywords_list_icon"></i>
                <input class="with_keywords fs-request-url" autocomplete="off" placeholder="<?php echo fsp__( 'Enter the request url' ); ?>" value="<?php echo isset( $fsp_params[ 'url' ] ) ? $fsp_params[ 'url' ] : ''; ?>">
            </div>
        </div>
    </div>
</div>
<div class="fsp-form-group">
    <label><?php echo fsp__( 'Headers' ); ?></label>
    <div class="fsp-headers fsp-key-val-input">
        <?php if( isset($fsp_params[ 'headers' ]) ){ foreach ( $fsp_params[ 'headers' ] as $k => $v ) { ?>
            <div class="fsp-key-val-group">
                <div class="fsp-form-input-has-icon">
                    <input autocomplete="off" class="fsp-form-input" name="key" placeholder="Content-Type" value="<?php echo $k; ?>">
                </div>
                <div class="fsp-form-input-has-icon with_keywords_wrapper">
                    <i class="fa fa-tag fsp-show-tags-label keywords_list_icon"></i>
                    <input autocomplete="off" class="fsp-form-input with_keywords" name="val" placeholder="application/text" value="<?php echo $v; ?>">
                </div>
                <button class="fsp-button fsp-remove-key-val-btn">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        <?php } } ?>
    </div>
    <button class="fsp-button fsp-is-gray fsp-add-key-val-btn"><?php echo fsp__('Add header') ?></button>
</div>
<div class="fsp-form-group fsp-request-content">
    <label><?php echo fsp__( 'Content type' ); ?></label>
    <div>
        <select class="fsp-form-input fsp-form-select fsp-request-content-selector">
            <option value="none" <?php echo isset( $fsp_params[ 'post_content' ] ) && $fsp_params[ 'post_content' ] === 'none' ? 'selected' : ''; ?>>none</option>
            <option value="json" <?php echo isset( $fsp_params[ 'post_content' ] ) && $fsp_params[ 'post_content' ] === 'json' ? 'selected' : ''; ?>>JSON</option>
            <option value="form" <?php echo isset( $fsp_params[ 'post_content' ] ) && $fsp_params[ 'post_content' ] === 'form' ? 'selected' : ''; ?>>Form data</option>
        </select>
    </div>
    <div class="fsp-form-input-has-icon fsp-request-content-json with_keywords_wrapper">
        <i class="fa fa-tag fsp-show-tags-label keywords_list_icon"></i>
        <textarea class="fsp-form-input with_keywords" placeholder=""><?php echo isset( $fsp_params[ 'json' ] )? $fsp_params[ 'json' ] : ''; ?></textarea>
    </div>
    <div class="fsp-request-content-form-data">
        <div class="fsp-form-data fsp-key-val-input">
            <?php if( isset($fsp_params[ 'form_data' ]) ){ foreach ( $fsp_params[ 'form_data' ] as $k => $v ) { ?>
                <div class="fsp-key-val-group">
                    <div class="fsp-form-input-has-icon">
                        <input autocomplete="off" class="fsp-form-input" name="key" placeholder="<?php echo fsp__('key'); ?>" value="<?php echo $k; ?>">
                    </div>
                    <div class="fsp-form-input-has-icon with_keywords_wrapper">
                        <i class="fa fa-tag fsp-show-tags-label keywords_list_icon"></i>
                        <input autocomplete="off" class="fsp-form-input with_keywords" name="val" placeholder="<?php echo fsp__('value'); ?>" value="<?php echo $v; ?>">
                    </div>
                    <button class="fsp-button fsp-remove-key-val-btn">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            <?php } } ?>
        </div>
        <button class="fsp-button fsp-is-gray fsp-add-key-val-btn"><?php echo fsp__('Add new field') ?></button>
    </div>
</div>
<div class="fsp-form-checkbox-group">
    <input type="checkbox" class="fsp-form-checkbox fsp-use-proxy" <?php echo ! empty( $fsp_params[ 'proxy' ] ) ? 'checked' : ''; ?>>
    <label><?php echo fsp__( 'Use a proxy' ); ?></label>
    <span class="fsp-tooltip" data-title="<?php echo fsp__( 'Optional field. Supported proxy formats: https://127.0.0.1:8888 or https://user:pass@127.0.0.1:8888' ); ?>"><i class="far fa-question-circle"></i></span>
</div>
<div class="fsp-form-group <?php echo isset( $fsp_params[ 'proxy' ] ) ? 'fsp-hide' : ''; ?> fsp-proxy-container">
    <div class="fsp-form-input-has-icon">
        <i class="fas fa-globe"></i>
        <input autocomplete="off" class="fsp-form-input fsp-proxy" placeholder="<?php echo fsp__( 'Enter a proxy address' ); ?>" value="<?php echo isset( $fsp_params[ 'proxy' ] ) ? $fsp_params[ 'proxy' ] : ''; ?>">
    </div>
</div>
