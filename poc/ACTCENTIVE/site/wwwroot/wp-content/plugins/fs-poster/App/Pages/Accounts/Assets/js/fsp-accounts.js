'use strict';

( function ( $ ) {
	let doc = $( document );

	doc.ready( function () {
		let currentComponent;

		doc.on( 'click', '.fsp-tab[data-component]', function () {
			let _this = $( this );
			let filter_by = FSPObject.filter_by;

			currentComponent = _this.data( 'component' );

			if ( currentComponent )
			{
				if ( $( '#fspSelectMode' ).data( 'mode' ) === 'select' )
				{
					$( '.fsp-account-selectbox:checked' ).click();
					$( '#fspSelectMode' ).click();
				}

				$( '.fsp-tab.fsp-is-active' ).removeClass( 'fsp-is-active' );
				_this.addClass( 'fsp-is-active' );

				FSPoster.ajax( 'get_accounts', { name: currentComponent }, function ( res ) {
					$( '#fspComponent' ).html( FSPoster.htmlspecialchars_decode( res[ 'html' ] ) );

					$( '.fsp-accounts-add-button > span' ).text( res[ 'extra' ][ 'button_text' ] );

					let fspAccountsCount = FSPObject.accountsCount;
					let loadModal = FSPObject.modalURL;

					_this.find( '.fsp-tab-all' ).text( fspAccountsCount );
					$( '.fsp-accounts-add-button' ).attr( 'data-load-modal', loadModal );

					if ( $( '.fsp-account-checkbox > .fsp-is-checked, .fsp-account-checkbox > .fsp-is-checked-conditionally' ).length > 0 )
					{
						$( '.fsp-tab.fsp-is-active > .fsp-tab-badges' ).addClass( 'fsp-has-active-accounts' );
					}
					else
					{
						$( '.fsp-tab.fsp-is-active > .fsp-tab-badges' ).removeClass( 'fsp-has-active-accounts' );
					}

					filterAccounts( filter_by );
				} );

				window.history.pushState( {}, '', `?page=fs-poster-accounts&tab=${ currentComponent }&filter_by=${ filter_by }` );
			}
		} ).on( 'click', '.fsp-tab[data-id]', function () {
			let _this = $( this );
			let filter_by = FSPObject.filter_by;

			currentComponent = _this.data( 'id' );

			if ( currentComponent )
			{
				if ( $( '#fspSelectMode' ).data( 'mode' ) === 'select' )
				{
					$( '.fsp-account-selectbox:checked' ).click();
					$( '#fspSelectMode' ).click();
				}

				$( '.fsp-tab.fsp-is-active' ).removeClass( 'fsp-is-active' );
				_this.addClass( 'fsp-is-active' );

				FSPoster.ajax( 'get_group_nodes', { group_id: currentComponent }, function ( res ) {
					$( '#fspComponent' ).html( FSPoster.htmlspecialchars_decode( res[ 'html' ] ) );

					let fspAccountsCount = FSPObject.accountsCount;

					_this.find( '.fsp-tab-all' ).text( fspAccountsCount );

					if ( $( '.fsp-account-checkbox > .fsp-is-checked, .fsp-account-checkbox > .fsp-is-checked-conditionally' ).length > 0 )
					{
						$( '.fsp-tab.fsp-is-active > .fsp-tab-badges' ).addClass( 'fsp-has-active-accounts' );
					}
					else
					{
						$( '.fsp-tab.fsp-is-active > .fsp-tab-badges' ).removeClass( 'fsp-has-active-accounts' );
					}

					filterAccounts( filter_by );
				} );

				window.history.pushState( {}, '', `?page=fs-poster-accounts&view=groups&group=${ currentComponent }&filter_by=${ filter_by }` );
			}
		} ).on( 'click', function ( e ) {
			if ( ! $( e.target ).is( '.fsp-account-checkbox, .fsp-account-checkbox > i' ) )
			{
				$( '#fspActivateMenu' ).hide();
			}

			if ( ! $( e.target ).is( '.fsp-account-more, .fsp-account-more > i' ) )
			{
				$( '#fspMoreMenu' ).hide();
			}

			if ( ! $( e.target ).is( '.fsp-group-more, .fsp-group-more > i' ) )
			{
				$( '#fspGroupMoreMenu' ).hide();
			}
		} ).on( 'click', '.fsp-modal-option', function () {
			let _this = $( this );
			let step = _this.data( 'step' );

			$( '.fsp-modal-option.fsp-is-selected' ).removeClass( 'fsp-is-selected' );
			_this.addClass( 'fsp-is-selected' );

			if ( step )
			{
				if ( $( `#fspModalStep_${ step }` ).length )
				{
					$( '.fsp-modal-step' ).addClass( 'fsp-hide' );
					$( `#fspModalStep_${ step }` ).removeClass( 'fsp-hide' );
				}
			}
		} ).on( 'click', '.fspjs-refetch-account', function () {
			let accountID = $( this ).data( 'id' );

			FSPoster.ajax( 'refetch_account', { 'account_id': accountID }, function () {
				$( '.fsp-tab.fsp-is-active' ).click();
			} );
		} ).on( 'change', '#fspUseProxy', function () {
			let checked = ! ( $( this ).is( ':checked' ) );

			$( '#fspProxy' ).val( '' );
			$( '#fspProxyContainer' ).toggleClass( 'fsp-hide', checked );
		} ).on( 'click', function () {
			let checkedAccounts = $( '.fsp-account-selectbox:checked' );
			let checkedAccountsLength = checkedAccounts.length;

			$( '#fspSelectedAccountsAction option:first' ).text( `Select an action (${ checkedAccountsLength })` );

			if ( checkedAccountsLength > 0 )
			{
				$( '#fspSelectedAccountsAction' ).prop( 'disabled', false );
				$( '#fspToggleSelectboxes' ).prop( 'checked', true );
			}
			else
			{
				$( '#fspSelectedAccountsAction' ).prop( 'disabled', true );
				$( '#fspToggleSelectboxes' ).prop( 'checked', false );
			}
		} ).on( 'click', '.fspjs-hide-account', function () {
			let _this = $( this );
			let menuDiv = _this.parent();
			let id = menuDiv.data( 'id' );
			let type = menuDiv.data( 'type' ) === 'account' ? 'account' : 'node';
			let hidden = menuDiv.data( 'hidden' ) ? 0 : 1;

			FSPoster.ajax( `hide_unhide_${ type }`, { id, hidden }, function () {
				$( '.fsp-tab.fsp-is-active' ).click();
			} );
		} ).on( 'keyup', '#fsp-node-search-input', function () {
			filterNodesByName( $( this ).val() );

			$( this ).siblings( 'i' ).removeClass( 'fa-search' ).addClass( 'fa-times' );
		} ).on( 'click', '.fsp-node-search > i', function () {
			$( '#fsp-node-search-input' ).val( '' ).trigger( 'keyup' );

			$( this ).removeClass( 'fa-times' ).addClass( 'fa-search' );
		} ).on( 'click', '#fspUseCustomApp', function () {
			let checked = ! ( $( this ).is( ':checked' ) );

			$( '#fspCustomAppContainer' ).toggleClass( 'fsp-hide', checked );
		} );

		$( '.fsp-tab.fsp-is-active' ).click();

		let component = $( '#fspComponent' );

		component.on( 'click', '.fsp-account-more', accountMoreClicked ).on( 'click', '.fsp-account-checkbox', function () {
			let _this = $( this );
			let accountDiv = _this.parent().parent();
			let id = accountDiv.data( 'id' );
			let type = accountDiv.data( 'type' ) ? _this.parent().parent().data( 'type' ) : 'account';

			if ( accountDiv.data( 'active' ) )
			{
				$( '#fspActivatesDiv' ).hide();
				$( '#fspDeactivatesDiv' ).show();
			}
			else
			{
				$( '#fspActivatesDiv' ).show();
				$( '#fspDeactivatesDiv' ).hide();
			}

			let top = _this.offset().top + 25 - $( window ).scrollTop();
			let left = _this.offset().left - ( $( '#fspActivateMenu' ).width() ) + 10;

			$( '#fspActivateMenu' ).data( 'id', id ).data( 'type', type ).css( { top: top, left: left } ).show();
		} ).on( 'click', '.fsp-account-caret', function () {
			let _this = $( this );
			let nodesDiv = _this.parent().parent().parent().find( '.fsp-account-nodes-container' );

			if ( nodesDiv.css( 'display' ) === 'none' )
			{
				nodesDiv.slideDown();
				_this.addClass( 'fsp-is-rotated' );
			}
			else
			{
				nodesDiv.slideUp();
				_this.removeClass( 'fsp-is-rotated' );
			}
		} );

		$( '#fspActivateMenu > #fspActivateConditionally' ).on( 'click', function () {
			let _this = $( this );
			let menuDiv = _this.parent();
			let id = menuDiv.data( 'id' );
			let type = menuDiv.data( 'type' ) === 'community' ? 'node' : 'account';

			FSPoster.loadModal( 'activate_with_condition', { id, type } );

			if ( $( '.fsp-account-checkbox > .fsp-is-checked, .fsp-account-checkbox > .fsp-is-checked-conditionally' ).length > 0 )
			{
				$( '.fsp-tab.fsp-is-active > .fsp-tab-badges' ).addClass( 'fsp-has-active-accounts' );
			}
			else
			{
				$( '.fsp-tab.fsp-is-active > .fsp-tab-badges' ).removeClass( 'fsp-has-active-accounts' );
			}
		} );

		// Handle more dropdown for account group tabs
		$( '.fsp-tab' ).on( 'click', '.fsp-group-more', groupMoreClicked );

		$( '#fspActivateMenu #fspActivate' ).on( 'click', function () {
			let _this = $( this );
			let menuDiv = _this.parent().parent();
			let id = menuDiv.data( 'id' );
			let type = menuDiv.data( 'type' );
			let ajaxType = type === 'community' ? 'settings_node_activity_change' : 'account_activity_change';
			let accountDiv = $( `.fsp-account-item[data-id=${ id }][data-type="${ type }"]` );

			FSPoster.ajax( ajaxType, { id, checked: 1 } );

			accountDiv.find( '.fsp-account-checkbox > i' ).removeClass( 'far' ).addClass( 'fas fsp-is-checked' );
			accountDiv.data( 'active', 1 );

			if ( $( '.fsp-account-checkbox > .fsp-is-checked, .fsp-account-checkbox > .fsp-is-checked-conditionally' ).length > 0 )
			{
				$( '.fsp-tab.fsp-is-active > .fsp-tab-badges' ).addClass( 'fsp-has-active-accounts' );
			}
			else
			{
				$( '.fsp-tab.fsp-is-active > .fsp-tab-badges' ).removeClass( 'fsp-has-active-accounts' );
			}
		} );

		$( '#fspActivateMenu #fspActivateForAll' ).on( 'click', function () {
			let _this = $( this );
			let menuDiv = _this.parent().parent();
			let id = menuDiv.data( 'id' );
			let type = menuDiv.data( 'type' );
			let ajaxType = type === 'community' ? 'settings_node_activity_change' : 'account_activity_change';
			let accountDiv = $( `.fsp-account-item[data-id=${ id }][data-type="${ type }"]` );

			FSPoster.ajax( ajaxType, { id, checked: 1, for_all: 1 } );

			accountDiv.find( '.fsp-account-checkbox > i' ).removeClass( 'far' ).addClass( 'fas fsp-is-checked' );
			accountDiv.data( 'active', 1 );
			accountDiv.find( '.fsp-account-is-public' ).removeClass( 'fsp-hide' );

			if ( $( '.fsp-account-checkbox > .fsp-is-checked, .fsp-account-checkbox > .fsp-is-checked-conditionally' ).length > 0 )
			{
				$( '.fsp-tab.fsp-is-active > .fsp-tab-badges' ).addClass( 'fsp-has-active-accounts' );
			}
			else
			{
				$( '.fsp-tab.fsp-is-active > .fsp-tab-badges' ).removeClass( 'fsp-has-active-accounts' );
			}
		} );

		$( '#fspActivateMenu #fspDeactivate' ).on( 'click', function () {
			let _this = $( this );
			let menuDiv = _this.parent().parent();
			let id = menuDiv.data( 'id' );
			let type = menuDiv.data( 'type' );
			let ajaxAction = type === 'community' ? 'settings_node_activity_change' : 'account_activity_change';
			let accountDiv = $( `.fsp-account-item[data-id=${ id }][data-type="${ type }"]` );

			FSPoster.ajax( ajaxAction, { id, checked: 0 } );

			accountDiv.find( '.fsp-account-checkbox > i' ).removeClass( 'fas fsp-is-checked fsp-is-checked-conditionally' ).addClass( 'far' );
			accountDiv.data( 'active', 0 );

			if ( $( '.fsp-account-checkbox > .fsp-is-checked, .fsp-account-checkbox > .fsp-is-checked-conditionally' ).length > 0 )
			{
				$( '.fsp-tab.fsp-is-active > .fsp-tab-badges' ).addClass( 'fsp-has-active-accounts' );
			}
			else
			{
				$( '.fsp-tab.fsp-is-active > .fsp-tab-badges' ).removeClass( 'fsp-has-active-accounts' );
			}
		} );

		$( '#fspActivateMenu #fspDeactivateForAll' ).on( 'click', function () {
			let _this = $( this );
			let menuDiv = _this.parent().parent();
			let id = menuDiv.data( 'id' );
			let type = menuDiv.data( 'type' );
			let ajaxAction = type === 'community' ? 'settings_node_activity_change' : 'account_activity_change';
			let accountDiv = $( `.fsp-account-item[data-id=${ id }][data-type="${ type }"]` );

			FSPoster.ajax( ajaxAction, { id, checked: 0, for_all: 1 } );

			accountDiv.find( '.fsp-account-checkbox > i' ).removeClass( 'fas fsp-is-checked fsp-is-checked-conditionally' ).addClass( 'far' );
			accountDiv.data( 'active', 0 );

			if ( $( '.fsp-account-checkbox > .fsp-is-checked, .fsp-account-checkbox > .fsp-is-checked-conditionally' ).length > 0 )
			{
				$( '.fsp-tab.fsp-is-active > .fsp-tab-badges' ).addClass( 'fsp-has-active-accounts' );
			}
			else
			{
				$( '.fsp-tab.fsp-is-active > .fsp-tab-badges' ).removeClass( 'fsp-has-active-accounts' );
			}

			ajaxAction = type === 'community' ? 'settings_node_make_public' : 'make_account_public';

			FSPoster.ajax( ajaxAction, { id, checked: 0 }, function () {
				accountDiv.find( '.fsp-account-is-public' ).addClass( 'fsp-hide' );
			} );
		} );

		$( '#fspMoreMenu > .fsp-make-public' ).on( 'click', function () {
			let _this = $( this );
			let menuDiv = _this.parent();
			let id = menuDiv.data( 'id' );
			let type = menuDiv.data( 'type' );
			let accountDiv = $( `.fsp-account-item[data-id=${ id }][data-type="${ type }"]` );
			let isChecked = ! accountDiv.find( '.fsp-account-is-public' ).hasClass( 'fsp-hide' );
			let ajaxAction = type === 'community' ? 'settings_node_make_public' : 'make_account_public';

			FSPoster.ajax( ajaxAction, { id, checked: isChecked ? 0 : 1 }, function () {
				if ( isChecked )
				{
					accountDiv.find( '.fsp-account-is-public' ).addClass( 'fsp-hide' );
				}
				else
				{
					accountDiv.find( '.fsp-account-is-public' ).removeClass( 'fsp-hide' );
				}
			} );
		} );

		$( '#fspMoreMenu > #fspDelete' ).on( 'click', function () {
			let _this = $( this );
			let menuDiv = _this.parent();
			let id = menuDiv.data( 'id' );
			let type = menuDiv.data( 'type' );
			let accountDiv = $( `.fsp-account-item[data-id=${ id }][data-type="${ type }"]` );

			FSPoster.confirm( fsp__( 'Are you sure you want to delete?' ), function () {
				let ajaxAction = type === 'community' ? 'settings_node_delete' : 'delete_account';

				FSPoster.ajax( ajaxAction, { id }, function () {
					if ( type === 'community' )
					{
						$( '.fsp-tab.fsp-is-active' ).click();
					}
					else
					{
						$( '.fsp-tab.fsp-is-active' ).click();
					}
				} );
			} );
		} );

		$( '#fspMoreMenu > #fsp-update-cookies, #fspMoreMenu > #fsp-update-webhook' ).on( 'click', function () {
			let _this = $( this );
			let menuDiv = _this.parent();
			let id = menuDiv.data( 'id' );

			if ( FSPObject.editModalURL )
			{
				FSPoster.loadModal( FSPObject.editModalURL, { 'account_id': id } );
			}
		} );

		$( '#fspMoreMenu > #fsp-export-webhook' ).on( 'click', function () {
			let _this = $( this );
			let menuDiv = _this.parent();
			let id = menuDiv.data( 'id' );

			FSPoster.confirm( fsp__('If you want to share this template with other FS Poster customers, please submit exported JSON via Google Form. After review it will be published in templates list. It is reccomended to redact informations like credentials, tokens, etc. before submit it.'), function (){
				FSPoster.ajax( 'export_webhook', { id }, function (result) {
					FSPoster.toast( result[ 'msg' ], 'success' );
					window.location.href = `${ window.location.href }&download=${ result[ 'file_id' ] }`;
					window.open( result['redirect_url'], '_blank' );
				} );
			}, 'fas fa-question', fsp__('Export') );
		} );

		$( '#fspMoreMenu > .fsp-add-to-groups' ).on( 'click', function () {
			let _this = $( this );
			let menuDiv = _this.parent();
			let id = menuDiv.data( 'id' );
			let type = menuDiv.data( 'type' );
			let node_type = type === 'account' ? 'account' : 'node';

			FSPoster.loadModal( 'edit_node_groups', { 'node_id': id, 'node_type': node_type } );
		} );

		$( '#fspMoreMenu > .fsp-custom-settings' ).on( 'click', function () {
			let _this = $( this );
			let menuDiv = _this.parent();
			let id = menuDiv.data( 'id' );
			let type = menuDiv.data( 'type' );
			let node_type = type === 'account' ? 'account' : 'node';

			FSPoster.loadModal( 'node_custom_settings', { 'node_id': id, 'node_type': node_type } );
		} );

		$( '#fspMoreMenu > .fsp-remove-from-group' ).on( 'click', function () {
			let _this = $( this );
			let menuDiv = _this.parent();
			let id = menuDiv.data( 'id' );
			let type = menuDiv.data( 'type' );
			let node_type = type === 'account' ? 'account' : 'node';

			FSPoster.ajax( 'remove_from_group', {
				'node_id': id,
				'node_type': node_type,
				'group_id': currentComponent
			}, function () {
				$( '.fsp-tab.fsp-is-active' ).click();
			} );
		} );

		$( '#fspGroupMoreMenu > .fsp-group-add' ).on( 'click', function () {
			let _this = $( this );
			let groupDiv = _this.parent();
			let id = groupDiv.data( 'id' );
			FSPoster.loadModal( 'add_node_to_group', { 'group_id': id } );
		} );

		$( '#fspGroupMoreMenu > .fsp-group-schedule' ).on( 'click', function () {
			let _this = $( this );
			let groupDiv = _this.parent();
			let id = groupDiv.data( 'id' );
			FSPoster.loadModal( 'add_schedule', { 'group_id': id } );
		} );

		$( '#fspGroupMoreMenu > .fsp-group-delete' ).on( 'click', function () {
			let _this = $( this );
			let groupDiv = _this.parent();
			let id = groupDiv.data( 'id' );

			FSPoster.confirm( fsp__( 'Are you sure to remove this group?' ), function () {
				FSPoster.ajax( 'delete_account_group', { 'group_id': id }, function () {
					$( '.fsp-tab[data-id=\'' + id + '\']' ).slideUp().remove();

					if ( currentComponent === id )
					{
						if ( $( '.fsp-tab[data-id]' ).length > 0 )
						{
							$( '.fsp-tab[data-id]:first-child' ).click();
						}
						else
						{
							$( '.fsp-layout-left.fsp-col-12.fsp-col-md-5.fsp-col-lg-4:eq(1)' ).addClass( 'fsp-hide' );

							$( '#fspComponent' ).html( `
								<div class="fsp-card fsp-emptiness">
									<div class="fsp-emptiness-image">
										<img src="${ FSPoster.asset( 'Base', 'img/empty.svg' ) }">
									</div>
									<div class="fsp-emptiness-text">
									 ${ fsp__( 'Create account groups to organize and manage your accounts easily' ) }
									<div class="fsp-emptiness-button">
										<button class="fsp-button fsp-accounts-add-button" data-load-modal='create_group'>
											<i class="fas fa-plus"></i>
											<span>${fsp__( "Create a group" )}</span>
										</button>
									</div>
								</div>
							` );
						}
					}

				} );
			} );
		} );

		$( '#fspGroupMoreMenu > .fsp-group-edit' ).on( 'click', function () {
			let _this = $( this );
			let groupDiv = _this.parent();
			let id = groupDiv.data( 'id' );

			FSPoster.loadModal( 'edit_account_group', { 'group_id': id } );
		} );

		$( '#fspSelectMode' ).on( 'click', function () {
			let _this = $( this );
			let currentMode = $( this ).data( 'mode' );
			let accountsCount = $( '.fsp-account-selectbox' ).length;

			if ( currentMode === 'ui' && accountsCount > 0 ) // select mode choosed
			{
				$( '#fspSelectedAccountsActionContainer, #fspToggleSelectboxes' ).removeClass( 'fsp-hide' );
				$( '.fsp-account-inline.fsp-is-buttons-container' ).hide();
				$( '.fsp-account-inline.fsp-is-select-container' ).show();
				_this.data( 'mode', 'select' );
				$( '.fsp-account-nodes-container' ).attr( 'style', 'overflow-y: visible !important; max-height: unset;' );
				_this.html( `<i class="fas fa-undo"></i> <span>${ fsp__( 'EXIT BULK ACTION' ) }</span>` );
			}
			else
			{
				let checkedAccounts = $( '.fsp-account-selectbox:checked' );
				let exit = function () {
					$( '#fspSelectedAccountsActionContainer, #fspToggleSelectboxes' ).addClass( 'fsp-hide' );
					$( '.fsp-account-inline.fsp-is-select-container' ).hide();
					$( '.fsp-account-inline.fsp-is-buttons-container' ).show();
					_this.data( 'mode', 'ui' );
					$( '.fsp-account-nodes-container' ).attr( 'style', 'overflow-y: auto !important; max-height: 200px;' );
					_this.html( `<i class="far fa-clone"></i> <span>${ fsp__( 'BULK ACTION' ) }</span>` );
				};

				if ( checkedAccounts.length > 0 )
				{
					FSPoster.confirm( fsp__( 'Are you sure you want to exit without applying any action for the selected accounts?' ), function () {
						exit();

						checkedAccounts.click();
					}, 'fas fa-exclamation-triangle', fsp__( 'YES, EXIT' ) );
				}
				else
				{
					exit();
				}
			}
		} );

		$( '#fspToggleSelectboxes' ).on( 'change', function () {
			let checkedAccounts = $( '.fsp-account-selectbox:checked:visible' );

			if ( checkedAccounts.length > 0 )
			{
				checkedAccounts.click();
			}
			else
			{
				$( '.fsp-account-selectbox:not(:checked):visible' ).click();
			}
		} );

		$( '#fspSelectedAccountsAction' ).on( 'change', function () {
			let _this = $( this );
			let act = _this.val();
			let actText = _this.find( 'option:selected' ).data( 'text' );
			let ids = [];

			$( '.fsp-account-selectbox:checked' ).each( function () {
				let id = $( this ).data( 'id' );
				let type = $( this ).data( 'type' );

				ids.push( { id, type } );
			} );

			if ( act !== '' )
			{
				if ( act === 'activate_condition' )
				{
					FSPoster.loadModal( 'activate_with_condition', { ids } );
				}
				else
				{
					FSPoster.confirm( actText, function () {
						FSPoster.ajax( 'bulk_account_action', { ids, act }, function () {
							$( '.fsp-tab.fsp-is-active' ).click();
						} );
					}, 'fas fa-question', `YES, CONTINUE` );
				}
			}

			_this.val( '' );
		} );

		$( '#fspAccountsFilterSelector' ).on( 'change', function () {
			let filter_by = $( this ).val();
			let url = window.location.href;

			filterAccounts( filter_by );

			if ( url.indexOf( 'filter_by' ) > -1 )
			{
				url = url.replace( /filter_by=([a-zA-Z]+)/, `filter_by=${ filter_by }` );
			}
			else
			{
				url += `${ ( url.indexOf( '?' ) > -1 ? '&' : '?' ) }filter_by=${ filter_by }`;
			}

			if ( $( '#fspSelectMode' ).data( 'mode' ) === 'ui' )
			{
				window.history.pushState( '', '', url );

				$.get( url );

				FSPObject.filter_by = filter_by;
			}
		} );

		$( '#fspCollapseAccounts' ).on( 'click', function () {
			$( '.fsp-account-caret' ).click();

			const btnText = $( this ).find( 'span' ).text() === fsp__( 'COLLAPSE ALL' ) ? fsp__( 'EXPAND ALL' ) : fsp__( 'COLLAPSE ALL' );
			$( this ).find( 'span' ).text( btnText );
		} );

		function accountMoreClicked ( e )
		{
			e.stopPropagation();

			let _this = $( this );
			let accountDiv = _this.parent().parent();
			let id = accountDiv.data( 'id' );
			let type = accountDiv.data( 'type' ) ? accountDiv.data( 'type' ) : 'account';
			let hidden = accountDiv.data( 'hidden' ) ? 1 : 0;
			let has_cookie = accountDiv.data( 'cookie' );
			let driver = accountDiv.data('driver');

			if ( hidden )
			{
				$( '#fspMoreMenu > [data-type="hide"]' ).addClass( 'fsp-hide' );
				$( '#fspMoreMenu > [data-type="unhide"]' ).removeClass( 'fsp-hide' );
			}
			else
			{
				$( '#fspMoreMenu > [data-type="hide"]' ).removeClass( 'fsp-hide' );
				$( '#fspMoreMenu > [data-type="unhide"]' ).addClass( 'fsp-hide' );
			}

			if ( accountDiv.find( '.fsp-account-is-public' ).hasClass( 'fsp-hide' ) )
			{
				$( '#fspMoreMenu > [data-type="public"]' ).removeClass( 'fsp-hide' );
				$( '#fspMoreMenu > [data-type="private"]' ).addClass( 'fsp-hide' );
			}
			else
			{
				$( '#fspMoreMenu > [data-type="public"]' ).addClass( 'fsp-hide' );
				$( '#fspMoreMenu > [data-type="private"]' ).removeClass( 'fsp-hide' );
			}

			if ( has_cookie )
			{
				$( '#fspMoreMenu > #fsp-update-cookies' ).removeClass( 'fsp-hide' );
			}
			else
			{
				$( '#fspMoreMenu > #fsp-update-cookies' ).addClass( 'fsp-hide' );
			}

			if( driver === 'webhook' )
			{
				$( '#fspMoreMenu > #fsp-update-webhook' ).removeClass( 'fsp-hide' );
			}
			else
			{
				$( '#fspMoreMenu > #fsp-update-webhook, #fspMoreMenu > #fsp-export-webhook' ).addClass( 'fsp-hide' );
			}

			let topPos = _this.offset().top + 25 - $( window ).scrollTop();

			// Change dropdown direction in case of overflow
			if ( ( topPos + $( '#fspMoreMenu' ).outerHeight() ) > $( window ).height() )
			{
				topPos -= $( '#fspMoreMenu' ).outerHeight() + 30; // 30 - margin from tab
			}

			if ( ! FSPoster.isRTL() )
			{
				$( '#fspMoreMenu' ).data( 'hidden', hidden ).data( 'id', id ).data( 'type', type ).css( {
					top: topPos,
					left: _this.offset().left - ( $( '#fspMoreMenu' ).width() ) + 10
				} ).show();
			}
			else
			{
				$( '#fspMoreMenu' ).data( 'hidden', hidden ).data( 'id', id ).data( 'type', type ).css( {
					top: topPos,
					right: $( window ).width() - _this.offset().left - ( $( '#fspMoreMenu' ).width() ) + 10
				} ).show();
			}
		}

		/** Move content to a specific target on given breakpoint */
		const moveContent = ( selector, target, breakpoint ) => {
			if ( ! document.querySelector( selector ) || ! document.querySelector( target ) )
			{
				return false;
			}

			const initWrapper = $( selector ).parent();
			let latestWidth = 0;

			$( window ).on( 'resize', () => {
				if (
					( latestWidth !== 0 && latestWidth < breakpoint && window.innerWidth < breakpoint ) ||
					( latestWidth >= breakpoint && window.innerWidth >= breakpoint )
				)
				{
					return;
				}

				latestWidth = window.innerWidth;

				if ( latestWidth < breakpoint )
				{
					$( target ).prepend( $( selector ) );
				}
				else
				{
					// Move content to its initial container
					$( initWrapper ).prepend( $( selector ) );
				}
			} );

			window.dispatchEvent( new Event( 'resize' ) );
		};

		moveContent( '.fsp-accounts-filter', '#js-filter-mobile', 750 );

	} );
} )( jQuery );

function accountAdded (nodeType = fsp__('Account'))
{
	if ( typeof jQuery !== 'undefined' )
	{
		$ = jQuery;
	}

	let modalBody = $( '.fsp-modal-body' );

	if ( modalBody.length )
	{
		$( '.fsp-modal-footer' ).remove();

		modalBody.html( `<div class="fsp-modal-succeed"><div class="fsp-modal-succeed-image"><img src="${ FSPoster.asset( 'Base', 'img/success.svg' ) }"></div><div class="fsp-modal-succeed-text">${ fsp__( '%s has been added successfully!', [nodeType] ) }</div><div class="fsp-modal-succeed-button"><button class="fsp-button" data-modal-close="true">${ fsp__( 'CLOSE' ) }</button></div></div>` );

		$( '.fsp-tab.fsp-is-active' ).click();
	}
}

function accountUpdated (nodeType = fsp__('Account'))
{
	if ( typeof jQuery !== 'undefined' )
	{
		$ = jQuery;
	}

	let modalBody = $( '.fsp-modal-body' );

	if ( modalBody.length )
	{
		$( '.fsp-modal-footer' ).remove();

		modalBody.html( `<div class="fsp-modal-succeed"><div class="fsp-modal-succeed-image"><img src="${ FSPoster.asset( 'Base', 'img/success.svg' ) }"></div><div class="fsp-modal-succeed-text">${ fsp__( '%s has been updated successfully!', [nodeType] ) }</div><div class="fsp-modal-succeed-button"><button class="fsp-button" data-modal-close="true">${ fsp__( 'CLOSE' ) }</button></div></div>` );

		$( '.fsp-tab.fsp-is-active' ).click();
	}
}

function groupCreated ( id, name )
{
	if ( typeof jQuery !== 'undefined' )
	{
		$ = jQuery;
	}

	$( '.fsp-layout-left.fsp-col-12.fsp-col-md-5.fsp-col-lg-4.fsp-hide' ).removeClass( 'fsp-hide' );

	let modalBody = $( '.fsp-modal-body' );

	if ( modalBody.length )
	{
		$( '.fsp-modal-footer' ).remove();

		modalBody.html( `<div class="fsp-modal-succeed"><div class="fsp-modal-succeed-image"><img src="${ FSPoster.asset( 'Base', 'img/success.svg' ) }"></div><div class="fsp-modal-succeed-text">${ fsp__( 'A new account group has been created successfully!' ) }</div><div class="fsp-modal-succeed-button"><button class="fsp-button" data-modal-close="true">${ fsp__( 'CLOSE' ) }</button></div></div>` );

		$( '.fsp-tab.fsp-is-active' ).removeClass( 'fsp-is-active' );
		$( '.fsp-layout-left .fsp-card' ).prepend(
			`
				<div class="fsp-tab fsp-is-active" data-id="${ id }">
					<div class="fsp-tab-title">
					  <span class="fsp-tab-title-icon fsp-account-group-badge" style="background-color: #55D56E;"></span>
					  <span class="fsp-tab-title-text">${ name }</span>
					</div>
					<div class="fsp-account-group-actions">
					  <div class="fsp-tab-badges">
						<span class="fsp-tab-all">0</span>
					  </div>
					  <div class="fsp-group-more">
						<i class="fas fa-ellipsis-h"></i>
					  </div>
					</div>
				</div>
			`
		);
		$( '.fsp-tab.fsp-is-active' ).on( 'click', '.fsp-group-more', groupMoreClicked );
		$( '.fsp-tab.fsp-is-active' ).click();
	}
}

function groupMoreClicked ( e )
{
	e.stopPropagation();

	let _this = $( this );
	let groupDiv = _this.parent().parent();
	let id = groupDiv.data( 'id' );

	let topPos = _this.offset().top + 25 - $( window ).scrollTop();

	// Change dropdown direction in case of overflow
	if ( ( topPos + $( '#fspGroupMoreMenu' ).outerHeight() ) > $( window ).height() )
	{
		topPos -= $( '#fspGroupMoreMenu' ).outerHeight() +30; // 30 - margin from tab
	}

	if ( ! FSPoster.isRTL() )
	{
		$( '#fspGroupMoreMenu' ).data( 'id', id ).css( {
			top: topPos,
			left: _this.offset().left - ( $( '#fspGroupMoreMenu' ).width() ) + 10
		} ).show();
	}
	else
	{
		$( '#fspGroupMoreMenu' ).data( 'id', id ).css( {
			top: topPos,
			right: $( window ).width() - _this.offset().left - ( $( '#fspGroupMoreMenu' ).width() ) + 10
		} ).show();
	}
}

function filterAccounts ( filter_by = 'all' )
{
	if ( typeof jQuery !== 'undefined' )
	{
		$ = jQuery;
	}

	if ( filter_by === 'all' )
	{
		$( '.fsp-account-item' ).show();
	}
	else if ( filter_by === 'active' )
	{
		$( '.fsp-account-item[data-active=1]' ).show();
		$( '.fsp-account-item[data-active=0]' ).slideUp( 100 );
	}
	else if ( filter_by === 'inactive' )
	{
		$( '.fsp-account-item[data-active=0]' ).show();
		$( '.fsp-account-item[data-active=1]' ).hide();
	}
	else if ( filter_by === 'visible' )
	{
		$( '.fsp-account-item[data-hidden=0]' ).show();
		$( '.fsp-account-item[data-hidden=1]' ).hide();
	}
	else if ( filter_by === 'hidden' )
	{
		$( '.fsp-account-item[data-hidden=1]' ).show();
		$( '.fsp-account-item[data-hidden=0]' ).hide();
	}
	else if ( filter_by === 'failed' )
	{
		$( '.fsp-account-item[data-failed=1]' ).show();
		$( '.fsp-account-item[data-failed=0]' ).hide();
	}

	setTimeout( function () {
		if ( $( '.fsp-account-item:visible' ).length === 0 )
		{
			$( '.fsp-emptiness' ).removeClass( 'fsp-hide' );
		}
		else
		{
			$( '.fsp-emptiness' ).addClass( 'fsp-hide' );
		}
	}, 200 );
}

function filterNodesByName ( query )
{
	if ( typeof jQuery !== 'undefined' )
	{
		$ = jQuery;
	}

	if ( query !== '' )
	{
		$( '.fsp-account-nodes .fsp-account-item' ).filter( function () {
			let _this = $( this );

			if ( _this.text().toLowerCase().indexOf( query ) > -1 )
			{
				_this.slideDown( 200 );
			}
			else
			{
				_this.slideUp( 200 );
			}
		} );
	}
	else
	{
		$( '.fsp-account-nodes .fsp-account-item' ).slideDown( 200 );
	}
}