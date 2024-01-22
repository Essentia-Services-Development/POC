'use strict';

( function ( $ ) {
    $(document).on( 'click', '.fsp-modal-option', function () {
        let _this = $( this );
        let step = _this.data( 'step' );

        if( step == '1' ){
            $('#fspModalWebhookNextButton').removeClass('fsp-hide');
            $('.fsp-modal-step').addClass('fsp-hide');
        }
        else{
            $('.fsp-modal-step').removeClass('fsp-hide');

            if($('#fspWebhookTemplates option:selected:not(:disabled)').length>0){
                $('#fspModalWebhookNextButton').removeClass('fsp-hide');
            }
            else{
                $('#fspModalWebhookNextButton').addClass('fsp-hide');
            }
        }

        $( '.fsp-modal-option.fsp-is-selected' ).removeClass( 'fsp-is-selected' );
        _this.addClass( 'fsp-is-selected' );
    } );

    $(document).on('click', '#fspModalWebhookNextButton', function (){
        let selectedTemplate = $('#fspWebhookTemplates option:selected');
        let title, icon, template = ['', '', {}];

        if(selectedTemplate.length > 0 && $( '.fsp-modal-option.fsp-is-selected' ).data('step') != 1){
            title = selectedTemplate.data('title');
            icon = selectedTemplate.data('icon');
            template = selectedTemplate.data('template');
        }

        FSPoster.ajax( 'get_webhook_add_body', { title, icon, template }, function ( res ) {
            $( '#fspWebhookTemplate' ).html( FSPoster.htmlspecialchars_decode( res[ 'html' ] ) );
            $( '.fsp-request-content-selector, .fsp-request-method, .fsp-use-proxy' ).change();
            $( '.fsp-modal-step, .fsp-modal-options, .fsp-modal-p, #fspModalWebhookSearch, #fspModalWebhookNextButton' ).addClass('fsp-hide');
            $( '#fspModalAddWebhookButton, #fspModalWebhookTestRequestButton' ).removeClass('fsp-hide');
            $( '.fsp-modal-p' ).removeClass( 'fsp-is-jb' )
            $('.fsp-modal-title-text').text(fsp__('Add a Webhook'));
        } );
    });

    $(document).on( 'click', '.keywords_list_icon',function ()
    {
        let me = $( this );

        setTimeout( function ()
        {
            let input    = me.closest( '.with_keywords_wrapper' ).find( '.with_keywords' );

            //TODO: add more keywords
            let keywords = {
                id: fsp__("Post ID"),
                author: fsp__("Post author name"),
                content_short_40: fsp__("CONTENT_SHORT_40"),
                title: fsp__("Post title"),
                featured_image_url: fsp__("Featured image URL"),
                tags: fsp__("TAGS"),
                product_regular_price: fsp__("WooCommerce - product price"),
                terms: fsp__("Post Terms"),
                product_sale_price: fsp__("WooCommerce - product sale price"),
                product_current_price: fsp__("WooCommerce - the current price of product"),
                content_full: fsp__("Post full content"),
                short_link: fsp__("Post short link"),
                excerpt: fsp__("Post excerpt"),
                product_description: fsp__("Product short description"),
                categories: fsp__("Post Categories"),
                uniq_id: fsp__("Unique ID"),
                cf_key: fsp__("Custom fields. Replace KEY with the custom field name."),
                link: fsp__("Post link")
            };

            me.closest( '.with_keywords_wrapper' ).append( '<div class="keywords_list"><div class="keywords_search_wrapper"><input class="fsp-form-input keywords_search"></div><div class="keywords_list_inner"></div></div>' );

            let listInnerDiv = me.closest( '.with_keywords_wrapper' ).find( '.keywords_list_inner' );

            for ( let key in keywords )
            {
                let val = keywords[ key ];

                listInnerDiv.append( '<a class="keywords-list-item" href="#" data-keyword="{' + key + '}"><div>' + val + '</div><div>{' + key + '}</div></a>' );
            }

            listInnerDiv.find( '.keywords-list-item:first' ).focus();

            me.closest( '.with_keywords_wrapper' ).find( '.keywords_search' ).focus();
        }, 50 );
    } );

    $(document).on( 'keydown', '.keywords-list-item',  function ( e )
    {
        let keyCode = e.keyCode || e.which;

        switch ( keyCode )
        {
            case 38: // up
                e.preventDefault();

                $( this ).prev( '.keywords-list-item' ).focus();
                break;
            case 40: // down
                e.preventDefault();

                $( this ).next( '.keywords-list-item' ).focus();
                break;
            case 13: // enter
                e.preventDefault();

                $( this ).trigger( 'click' );
                break;
        }
    } ).on( 'click', '.keywords-list-item', function ( e )
    {
        e.preventDefault();
        let value = $( this ).data( 'keyword' );
        let input = $( this ).closest( '.with_keywords_wrapper' ).find( '.with_keywords' );
        input.val( input.val() + value );
        input.click().focus();
    } ).on( 'keyup', '.keywords_search',  function ( e )
    {
        let search = $( this ).val();
        let innerDiv = $( this ).closest( '.with_keywords_wrapper' ).find( '.keywords_list_inner' );

        if ( e.which === 40 )
        {
            innerDiv.find( '.keywords-list-item:contains("' + search + '"):first' ).focus();
            return;
        }

        if ( search == '' )
        {
            innerDiv.children( '.keywords-list-item' ).show();
            return;
        }

        innerDiv.children( '.keywords-list-item:contains("' + search + '")' ).show();
        innerDiv.children( '.keywords-list-item:not(:contains("' + search + '"))' ).hide();
    } ).on( 'click', function ( e )
    {
        if ( $( e.target ).closest( '.keywords_list' ).length === 0 ) $( '.keywords_list' ).remove();
    } );

    //key-value-pair for headers and form data;
    $(document).on('click', '.fsp-remove-key-val-btn', function (){
        $(this).closest('.fsp-key-val-group').remove();
    }).on('click', '.fsp-add-key-val-btn', function (){
        let keyPlaceHolder = '';
        let valPlaceHolder = '';
        if( $(this).parent().hasClass('fsp-request-content-form-data') ){
            keyPlaceHolder = fsp__('key');
            valPlaceHolder = fsp__('value');
        }
        else{
            keyPlaceHolder = 'Content-Type';
            valPlaceHolder = 'application/text';
        }

        let kv_group = `
            <div class="fsp-key-val-group">
                <div class="fsp-form-input-has-icon">
                    <input autocomplete="off" class="fsp-form-input" name="key" placeholder="${keyPlaceHolder}">
                </div>
                <div class="fsp-form-input-has-icon with_keywords_wrapper">
                    <i class="fa fa-tag fsp-show-tags-label keywords_list_icon"></i>
                    <input autocomplete="off" class="fsp-form-input with_keywords" name="val" placeholder="${valPlaceHolder}">
                </div>
                <button class="fsp-button fsp-remove-key-val-btn">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        `;

        $(this).siblings('.fsp-key-val-input').append(kv_group);
    });

    $(document).on('change', '.fsp-request-url-input-group select', function (){
        if($(this).val() === 'post' || $(this).val() === 'put'){
            $(this).parents('#fspWebhookTemplate').first().find('.fsp-request-content').removeClass('fsp-hide');
        }
        else{
            $(this).parents('#fspWebhookTemplate').first().find('.fsp-request-content').addClass('fsp-hide');
        }

        $('.fsp-request-content-selector').change();
    }).on('change', '.fsp-request-content-selector', function (){
        if($(this).val() === 'none'){
            $(this).parents('#fspWebhookTemplate').first().find('.fsp-request-content-json').addClass('fsp-hide');
            $(this).parents('#fspWebhookTemplate').first().find('.fsp-request-content-form-data').addClass('fsp-hide');
        }
        else if($(this).val() === 'json'){
            $(this).parents('#fspWebhookTemplate').first().find('.fsp-request-content-form-data').addClass('fsp-hide');
            $(this).parents('#fspWebhookTemplate').first().find('.fsp-request-content-json').removeClass('fsp-hide');
        }
        else{
            $(this).parents('#fspWebhookTemplate').first().find('.fsp-request-content-form-data').removeClass('fsp-hide');
            $(this).parents('#fspWebhookTemplate').first().find('.fsp-request-content-json').addClass('fsp-hide');
        }
    });

    $(document).on( 'change', '.fsp-use-proxy', function () {
        if(! ( $( this ).is( ':checked' ) )){
            $(this).parents('#fspWebhookTemplate').first().find( '.fsp-proxy' ).val( '' );
            $(this).parents('#fspWebhookTemplate').first().find( '.fsp-proxy-container' ).addClass( 'fsp-hide' );
        }
        else{
            $(this).parents('#fspWebhookTemplate').first().find( '.fsp-proxy-container' ).removeClass( 'fsp-hide' );
        }
    } );

    $(document).on( 'click', '.fsp-modal-footer > #fspModalAddWebhookButton, .fsp-modal-footer > #fspModalEditWebhookButton, .fsp-modal-footer > #fspModalWebhookTestRequestButton', function () {
        let tab = $('#fspWebhookTemplate');

        let id     = 0;
        let icon   = $('#fspModalWebhookIcon').val().trim();
        let name   = tab.find('.fsp-webhook-title').val().trim();
        let method = tab.find('.fsp-request-method').val().trim();
        let url    = tab.find('.fs-request-url').val().trim();

        let headers = {};
        tab.find('.fsp-headers').children().each(function (){
            let key = $(this).find('input[name="key"]').val().trim();
            let val = $(this).find('input[name="val"]').val().trim();

            if( key !== '' && val !== '' ){
                headers[key] = val;
            }
        });

        let content = 'none';

        if(method === 'post' || method === 'put'){
            content = tab.find('.fsp-request-content-selector').val().trim();
        }

        let json = '';
        let form = {};

        if(content === 'json'){
            json = tab.find('textarea').val().trim();
        }
        else if(content === 'form'){
            tab.find('.fsp-form-data').children().each(function (){
                let key = $(this).find('input[name="key"]').val().trim();
                let val = $(this).find('input[name="val"]').val().trim();

                if( key !== '' && val !== '' ){
                    form[key] = val;
                }
            });
        }

        let proxy = tab.find('.fsp-proxy').val().trim();

	    let isTestRequest = this.id === 'fspModalWebhookTestRequestButton';

		if( isTestRequest ){
			FSPoster.ajax( 'test_webhook_request', { method, url, headers, content, json, form, proxy }, function () {
				FSPoster.toast( fsp__('A test request is sent!'), 'success' );
			} );

			return;
		}

        let isEditAction = this.id === 'fspModalEditWebhookButton';

        id = isEditAction ? $( '#fspModalWebhookID' ).val() : id;

        FSPoster.ajax( 'save_webhook_account', { id, icon, name, method, url, headers, content, json, form, proxy }, function () {
            if(isEditAction){
                accountUpdated( fsp__('Webhook') );
            }
            else{
                accountAdded( fsp__('Webhook') );
            }
        } );
    });

    $(document).on('click', '.fsp-modal-option', function (){
        $( '#fspWebhookTemplates' ).select2( {
            containerCssClass: 'fsp-select2-container',
            dropdownCssClass: 'fsp-select2-dropdown',
            templateResult: function (state){
                if(state.disabled){
                    return '';
                }

                let icon = state.element.dataset.icon;
                let title = state.element.dataset.title;

                return $(`<div class="fsp-webhook-s2-template"><span><img src="${icon}"></span><span>${title}</span></div>`);
            }
        } );
    });

} )( jQuery );

function initKeywordsInput ( input, keywords )
{
    input.data('keywords', keywords);
    input.addClass('with_keywords').wrap('<div class="with_keywords_wrapper">').parent().append('<i class="fa fa-tag keywords_list_icon"></i>');
}