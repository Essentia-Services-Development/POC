'use strict';

( function ( $ ) {
	let doc = $( document );

	doc.ready( function () {
		let currentPage = FSPObject.page;
		let retriedFeedId = 0;

		doc.on( 'click', '#fspModalFilterLogsBtn', function () {
			$('#fspShowLogsOf').val($('#fspModalShowLogsOf').val());
			$('#fspFilterSelector').val($('#fspModalFilterSelector').val());
			$('#fspRowsSelector').val($('#fspModalRowsSelector').val());
			$('#fspSnSelector').val($('#fspModalSnSelector').val());
			$('.fsp-logs-filter-modal.fsp-modal-close').click();
			FSPLoadLogs( 1 );
		} )
		   .on( 'change', '#fspLogsPageSelector', function () {
			   let page = $( this ).val();

			   FSPLoadLogs( page );
		   } )
		   .on( 'click', '.fsp-logs-page', function () {
			   let _this = $( this );
			   let page = _this.data( 'page' );

			   if ( page === currentPage )
			   {
				   return;
			   }
			   else
			   {
				   currentPage = page;
			   }

			   FSPLoadLogs( page );
		   } ).on( 'change', '#fspDeleteLogs', function () {

			let scheduleID = $( '#fspLogsScheduleID' ).val();
			let confirmMessage = '';

			let data = {
				'type': $( this ).val(),
				'schedule_id': scheduleID
			};

			if ( data.type === 'all' )
			{
				confirmMessage = fsp__( 'Deleting logs will also delete the related insights in the dashboard. Are you sure you want to delete all logs?' );
			}
			else if ( data.type === 'only_errors' )
			{
				confirmMessage = fsp__( 'Are you sure you want to delete the error logs?' );
			}
			else if ( data.type === 'only_successful_logs' )
			{
				confirmMessage = fsp__( 'Deleting the successful logs will also delete the related insights in the dashboard. Are you sure you want to delete all successful logs?' );
			}
			else if ( data.type === 'only_selected_logs' )
			{
				let selected_accounts = [];

				$( '.fsp-log-clear-checkbox:checked' ).each( function () {
					selected_accounts.push( $( this ).data( 'id' ) );
				} );

				data.selected_accounts = selected_accounts;

				confirmMessage = fsp__( 'Deleting the successful logs will also delete the related insights in the dashboard. Are you sure you want to delete all successful logs?' );
			}

			if ( confirmMessage !== '' )
			{
				FSPoster.confirm( confirmMessage, function () {

					FSPoster.ajax( 'fs_clear_logs', data, function () {
						let url = window.location.href;

						if ( url.indexOf( 'logs_page' ) > -1 )
						{
							url = url.replace( /logs_page=([0-9]+)/, `logs_page=1` );
						}

						window.location.href = url;
					} );
				} );

			}

			$( this ).children( '#fspDeleteLogsDefault' ).prop( 'selected', true );
		} )
		   .on( 'click', '.fsp-logs-retry', function () {
			   let _this = $( this );
			   retriedFeedId = _this.data( 'feed-id' );

			   if ( retriedFeedId )
			   {
				   FSPoster.ajax(
					   'get_feed_details',
					   {
						   'feed_id': retriedFeedId
					   },
					   function ( data ) {
						   FSPoster.loadModal( 'share_feeds', { 'post_id': data.result.post_id }, true );
					   }
				   );
			   }
		   } )
		   .on( 'click', '.fsp-share-popup-modal-close', function () {
			   FSPoster.ajax( 'report3_data', { feed_id: retriedFeedId }, function ( result ) {
				   $( '[data-feed-id=' + retriedFeedId + ']' ).parent().parent().replaceWith( logData( result[ 'data' ][ 0 ] ) );
			   } );
		   } )
		   .on( 'click', '#fspExportLogs', function () {

			   let scheduleID = $( '#fspLogsScheduleID' ).val();
			   let filter = $( '#fspFilterSelector' ).val();
			   let sn_filter = $( '#fspSnSelector' ).val();

			   FSPoster.ajax(
				   'export_logs_to_csv',
				   {
					   'schedule_id': scheduleID,
					   'filter_results': filter,
					   'sn': sn_filter
				   },
				   function ( result ) {
					   let a = $( '<a>' );

					   a.attr( 'href', result.file );

					   $( 'body' ).append( a );

					   a.attr( 'download', result.filename );
					   a[ 0 ].click();
					   a.remove();
				   }
			   );
		   } )
			.on('click', '#fspLogsFilter', function (){
				let rows_count = $( '#fspRowsSelector' ).val();
				let filter_results = $( '#fspFilterSelector' ).val();
				let sn_filter = $( '#fspSnSelector' ).val();
				let show_logs_of = $( '#fspShowLogsOf' ).val();
				FSPoster.loadModal('logs_filter', { rows_count, filter_results, sn_filter, show_logs_of });
			}).on( 'click', '.get-insights-button', function () {
				let _this = $( this );
				let fsp_feed_id = _this.data( 'feed-id' );
				FSPoster.ajax( 'get_insights', {
					feed_id : fsp_feed_id
				}, function ( result ) {
					fspShowInsightsDropdown( _this, result );
				});
		} ).on('click', '.fsp-show-webhook-data', function (){
			let id = $( this ).data( 'feed-id' );
			FSPoster.loadModal('logs_webhook_response', {id});
		}).on( 'click', function ( e ) {
			if ( ! $( e.target ).is( '.get-insights-button' ) )
			{
				$( '#fspInsightsDropdown' ).hide();
			}
		} ).on( 'change', '#fspCheckAllLogs', function () {
			let isAllChecked = $( this ).is( ':checked' );

			$( '.fsp-log input[type=checkbox]' ).prop( 'checked', isAllChecked );
		} );
		FSPLoadLogs( FSPObject.page );

		if(FSPObject.webhook_feed_id > 0){
			FSPoster.loadModal('logs_webhook_response', {id: FSPObject.webhook_feed_id});
			FSPObject.webhook_feed_id = 0;
			let url = new URL(window.location.href);

			let params = new URLSearchParams(url.search);

			params.delete('webhook_feed_id');
			window.history.pushState({}, '', '?' + params.toString());
		}
	} );
} )( jQuery );

function FSPLoadLogs ( page )
{
	if ( typeof jQuery !== 'undefined' )
	{
		$ = jQuery;
	}

	let rowsCount = $( '#fspRowsSelector' ).val();
	let filter = $( '#fspFilterSelector' ).val();
	let scheduleID = $( '#fspLogsScheduleID' ).val();
	let sn = $( '#fspSnSelector' ).val();
	let show_logs_of = $( '#fspShowLogsOf' ).val();


	FSPoster.ajax( 'report3_data', {
		page,
		'schedule_id': scheduleID,
		'rows_count': rowsCount,
		'filter_results': filter,
		'sn' : sn,
		'show_logs_of' : show_logs_of,
	}, function ( result ) {


		let url = window.location.href;

		if ( url.indexOf( 'filter_by' ) > -1 )
		{
			url = url.replace( /filter_by=([a-zA-Z]+)/, `filter_by=${ filter }` );
		}
		else
		{
			url += `${ ( url.indexOf( '?' ) > -1 ? '&' : '?' ) }filter_by=${ filter }`;
		}

		if ( url.indexOf( 'sn_filter' ) > -1 )
		{
			url = url.replace( /sn_filter=([a-zA-Z]+)/, `sn_filter=${ sn }` );
		}
		else
		{
			url += `${ ( url.indexOf( '?' ) > -1 ? '&' : '?' ) }sn_filter=${ sn }`;
		}

		if ( url.indexOf( 'logs_page' ) > -1 )
		{
			url = url.replace( /logs_page=([0-9]+)/, `logs_page=${ page }` );
		}
		else
		{
			url += `${ ( url.indexOf( '?' ) > -1 ? '&' : '?' ) }logs_page=${ page }`;
		}

		window.history.pushState( '', '', url );

		$( '#fspLogs' ).empty();

		$( '#fspLogsCount' ).text( result[ 'total' ] );

		if( parseInt( result['total'] ) > 0 ) {
			$( '.fsp-check-all-row' ).removeClass( 'fsp-hide' );
		}

		for ( let i in result[ 'data' ] )
		{
			$( '#fspLogs' ).append( logData( result[ 'data' ][ i ] ) );
		}

		let logsPages = '';
		let j = 0;

		result[ 'pages' ][ 'page_number' ].forEach( function ( i ) {
			logsPages += `<button class="fsp-button fsp-is-${ i === parseInt( result[ 'pages' ][ 'current_page' ] ) ? 'danger' : 'white' } fsp-logs-page" data-page="${ i }">${ i }</button>`;

			if ( typeof result[ 'pages' ][ 'page_number' ][ j + 1 ] !== 'undefined' && result[ 'pages' ][ 'page_number' ][ j + 1 ] !== i + 1 )
			{
				logsPages += '<button class="fsp-button fsp-is-white" disabled>...</button>';
			}

			j++;
		} );

		logsPages += `<select id="fspLogsPageSelector" class="fsp-form-select">`;

		for ( let i = 1; i <= result[ 'pages' ][ 'count' ]; i++ )
		{
			logsPages += `<option value="${ i }" ${ i === parseInt( result[ 'pages' ][ 'current_page' ] ) ? 'selected' : '' }>${ i }</option>`;
		}

		logsPages += `</select>`;

		$( '#fspLogsPages' ).html( logsPages );
	} );
}

function logData ( result )
{
	let statusBtn;

	if ( result[ 'status' ] === 'ok' )
	{
		statusBtn = `<div class="fsp-status fsp-is-success"><i class="fas fa-check"></i>${ fsp__( 'SUCCESS' ) }</div>`;
	}
	else if ( result[ 'status' ] === 'error' )
	{
		statusBtn = `<div class="fsp-status fsp-is-danger fsp-tooltip" data-title="${ result[ 'error_msg' ] }"><i class="fas fa-info-circle"></i>${ fsp__( 'ERROR' ) }</div>`;

		if ( ! result[ 'is_deleted' ] )
		{
			statusBtn += `<button class="fsp-button fsp-is-warning fsp-logs-retry" data-feed-id="${ result[ 'id' ] }"><i class="fas fa-sync"></i>${ fsp__( 'RETRY' ) }</button>`;
		}
	}
	else if ( result[ 'status' ] === 'processing' )
	{
		statusBtn = `<div class="fsp-status fsp-is-warning"><i class="fas fa-check"></i>${ fsp__( 'PROCESSING' ) }</div>`;
	}
	else
	{
		statusBtn = `<div class="fsp-status fsp-is-warning"><i class="fas fa-check"></i>${ fsp__( 'NOT SENT' ) }</div>`;
	}

	let driverIcon = result[ 'icon' ];

	let post_link = ``;

	if(result['driver'] !== 'webhook'){
		post_link = `<a target="_blank" href="${ result[ 'wp_post_link' ] }" class="fsp-tooltip" data-title="${ fsp__( 'Post permalink' ) }"><i class="fas fa-external-link-alt"></i></a>`;
	}


	let account_link = ``;

	if ( ! result[ 'is_deleted' ] && result['driver'] !== 'webhook' )
	{
		account_link = `<a target="_blank" href="${ result[ 'profile_link' ] }" class="fsp-tooltip" data-title="${ fsp__( 'Profile link' ) }"><i class="fas fa-external-link-alt"></i></a>`;
	}

	return `
				<div class="fsp-log">
					<div class="fsp-is-second">
						<input type="checkbox" class="fsp-form-checkbox fsp-log-clear-checkbox" data-id="${ result[ 'id' ] }">
					</div>
					&emsp;
					<div class="fsp-log-image">
						<img src="${ result[ 'cover' ] }" onerror="FSPoster.no_photo( this );">
					</div>
					<div class="fsp-log-title">
						<div class="fsp-log-title-text">
							${ result[ 'name' ] }
							${ account_link }
						</div>
						<div class="fsp-log-title-subtext">
							${ result[ 'date' ] }

							${ post_link }

							<span class="fsp-tooltip" data-title="${ result[ 'shared_from' ] }"><i class="fa fa-info-circle"></i></span>
						</div>
					</div>
					<div class="fsp-log-title fsp-is-second">
					${ result[ 'driver' ] !== 'webhook' ?
						`<div class="fsp-log-title-link">
							<a target="_blank" href="${ result[ 'post_link' ] }">
								<i class="fas fa-external-link-alt"></i>
								${fsp__('Shared post link')}
							</a>
						</div>` : ''
					}
						<div class="fsp-log-title-subtext fsp-log-title-sublink">
							<i class="${ driverIcon }"></i>${ result[ 'driver' ][ 0 ].toUpperCase() + result[ 'driver' ].substring( 1 ) }&nbsp;>&nbsp;${ (result[ 'driver' ] === 'webhook' ? result['username'] : result[ 'node_type' ]) + ( result[ 'feed_type' ] !== '' ? ' > ' + result[ 'feed_type' ] : '' ) }
						</div>
					</div>
					<div class="fsp-log-status-container">
						${ statusBtn }
					</div>
					<div class="fsp-log-stats">
						${ result[ 'hide_stats' ] ? '' : result['driver'] !== 'webhook' ? `
							<div class="fsp-logs-controls">
								<div class="fsp-logs-control get-insights-button" data-feed-id="${ result['id'] }">
									<i class="far fa-chart-bar"></i>
								</div>
							</div>
						`: `
						<div class="fsp-logs-controls">
								<div class="fsp-logs-control fsp-show-webhook-data" data-feed-id="${ result['id'] }">
									<i class="fas fa-eye"></i>
								</div>
							</div>
						` }
					</div>
				</div>
			`;
}

function fspShowInsightsDropdown( _this, result ) {
	let topPos = _this.offset().top + 25 - $( window ).scrollTop();
	let dropdownSelector = $( '#fspInsightsDropdown' );

	result = result.insights;

	dropdownSelector.find( '#fsp_insights_hits' ).text( result.hits );
	dropdownSelector.find( '#fsp_insights_comments' ).text( result.comments );
	dropdownSelector.find( '#fsp_insights_likes' ).text( result.like );
	dropdownSelector.find( '#fsp_insights_shares' ).text( result.shares );

	// Change dropdown direction in case of overflow
	if ( ( topPos + dropdownSelector.outerHeight() ) > $( window ).height() )
	{
		topPos -= dropdownSelector.outerHeight() + 30; // 30 - margin from tab
	}

	if ( ! FSPoster.isRTL() )
	{
		dropdownSelector.css( {
			top: topPos,
			left: _this.offset().left - ( dropdownSelector.width() ) + 10
		} ).show();
	}
	else
	{
		dropdownSelector.data( 'hidden', hidden ).data( 'id', id ).data( 'type', type ).css( {
			top: topPos,
			right: $( window ).width() - _this.offset().left - ( dropdownSelector.width() ) + 10
		} ).show();
	}
}
