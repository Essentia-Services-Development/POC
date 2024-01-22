'use strict';

( function ( $ ) {
	let doc = $( document );
	let fspScheduleSearchTimeout = undefined;

	doc.ready( function () {
		let currentPage = FSPObject.page;
		doc.on( 'click', '.fsp-delete-schedule', function () {
			let id = $( this ).data( 'id' );
			let row = $( `.fsp-schedule[data-id=${ id }]` );

			if ( id )
			{
				FSPoster.confirm( fsp__( 'Are you sure you want to delete?' ), function () {
					FSPoster.ajax( 'delete_schedule', { id }, function ( result ) {
						row.fadeOut( 300, function () {
							$( this ).remove();
						} );

						if ( $( '.fsp-schedules > .fsp-schedule' ).length === 0 )
						{
							$( '.fsp-emptiness' ).removeClass( 'fsp-hide' );
						}

						$( '#fspSchedulesCount' ).text( parseInt( $( '#fspSchedulesCount' ).text() ) - 1 );
						$( '#fspRemoveSelected' ).addClass( 'fsp-hide' );
					} );
				} );
			}
		} ).on( 'click', '.fsp-change-schedule', function () {
			let id = $( this ).data( 'id' );

			if ( id )
			{
				FSPoster.ajax( 'schedule_change_status', { id }, function ( result ) {
					FSPoster.loading( true );

					window.location.reload();
				} );
			}
		} ).on( 'click', '.fsp-schedule-checkbox', function () {
			let selectedCount = $( '.fsp-schedule-checkbox:checked' ).length;

			$( '#fspSelectedCount > span' ).text( selectedCount );

			if ( selectedCount )
			{
				$( '#fspRemoveSelected' ).removeClass( 'fsp-hide' );
			}
			else
			{
				$( '#fspRemoveSelected' ).addClass( 'fsp-hide' );
			}
		} ).on( 'click', '.fsp-schedule-page', function () {
			let _this = $( this );
			let page = _this.data( 'page' );

			if( page === currentPage ) {
				return;
			}
			else {
				currentPage = page;
			}

			FSPLoadSchedules( page );

		} ).on('change', "#fspSchedulesPageSelector", function () {
			let page = $( this ).val();

			FSPLoadSchedules( page );
		}).on( 'input', '#fsp-schedule-search-input', function ( e ) {
			let searchValue = e.target.value;
			let _this = $( this );
			if( searchValue ) {
				_this.siblings( 'i' ).removeClass( 'fa-search' ).addClass( 'fa-times' )
			} else {
				_this.siblings( 'i' ).removeClass( 'fa-times' ).addClass( 'fa-search' )
			}

			clearTimeout( fspScheduleSearchTimeout );
			fspScheduleSearchTimeout = setTimeout(function () {
				FSPObject.page = 1;
				currentPage = 1;

				FSPLoadSchedules( FSPObject.page );
			}, 500);
		} ).on( 'click', '.fsp-schedule-search > i.fa-times', function () {
			$( '#fsp-schedule-search-input' ).val( '' ).trigger( 'input' );
		} ).on( 'change', '#fspScheduleCountSelector', function () {
			FSPLoadSchedules( FSPObject.page );
		} );
		FSPLoadSchedules(FSPObject.page);
	} );

	$( '#fspRemoveSelected' ).on( 'click', function () {
		let selectedCount = $( '.fsp-schedule-checkbox:checked' ).length;

		if ( selectedCount )
		{
			FSPoster.confirm( fsp__( 'Are you sure you want to delete all selected schedules?' ), function () {
				let selectedIDs = [];

				$( '.fsp-schedule-checkbox:checked' ).each( function () {
					let id = $( this ).data( 'id' );

					selectedIDs.push( id );
				} );

				FSPoster.ajax( 'delete_schedules', { ids: selectedIDs }, function ( result ) {
					selectedIDs.forEach( function ( id ) {
						$( `.fsp-schedule[data-id=${ id }]` ).fadeOut( 300, function () {
							$( this ).remove();
						} );

						if ( $( '.fsp-schedules > .fsp-schedule' ).length === 0 )
						{
							$( '.fsp-emptiness' ).removeClass( 'fsp-hide' );
						}

						$( '#fspSchedulesCount' ).text( parseInt( $( '#fspSchedulesCount' ).text() ) - 1 );
						$( '#fspRemoveSelected' ).addClass( 'fsp-hide' );
					} );
				} );
			} );
		}
		else
		{
			FSPoster.toast( fsp__( 'You need to select schedules for delete!' ), 'warning' );
		}
	} );

	$( '.fsp-schedule-checkbox:checked' ).trigger( 'click' );

	function FSPLoadSchedules(page) {
		let scheduleSearchKeyword = $( '#fsp-schedule-search-input' ).val().trim().toLowerCase();
		let rows_count = $( '#fspScheduleCountSelector' ).val();

		FSPoster.ajax('fsp_fetch_schedule_list', {
			page,
			rows_count,
			search : scheduleSearchKeyword
		}, function (response) {
			let url = window.location.search;
			let urlParams = new URLSearchParams( url );
			urlParams.set( "schedule_page", page );
			urlParams.set( "search", scheduleSearchKeyword );
			window.history.pushState( '', '', window.location.pathname + "?" + urlParams.toString() );

			var fspScheduleWrapper = $( "#fspSchedules" );
			$( "#fspSchedulesCount" ).text( response.scheduleCount );

			fspScheduleWrapper.children( '.fsp-schedule' ).remove();
			if( typeof response.schedules !== "undefined" && response.schedules !== null && response.schedules.length > 0 ) {
				$( '.fsp-emptiness' ).addClass( 'fsp-hide' );
				for ( let schedule of response.schedules ) {
					fspScheduleWrapper.append( scheduleData( schedule ) );
				}
			} else {
				$( '.fsp-emptiness' ).removeClass( 'fsp-hide' );
			}

			let schedulePages = '';
			let j = 0;
			response['pages']['page_number'].forEach(function ( i ) {
				schedulePages += `<button class="fsp-button fsp-is-${ i === parseInt( response[ 'pages' ][ 'current_page' ] ) ? 'danger' : 'white' } fsp-schedule-page" data-page="${ i }">${ i }</button>`;

				if ( typeof response[ 'pages' ][ 'page_number' ][ j + 1 ] !== 'undefined' && response[ 'pages' ][ 'page_number' ][ j + 1 ] !== i + 1 )
				{
					schedulePages += '<button class="fsp-button fsp-is-white" disabled>...</button>';
				}

				j++;
			});

			schedulePages += `<select id="fspSchedulesPageSelector" class="fsp-form-select">`;

			for ( let i = 1; i <= response[ 'pages' ][ 'count' ]; i++ )
			{
				schedulePages += `<option value="${ i }" ${ i === parseInt( response[ 'pages' ][ 'current_page' ] ) ? 'selected' : '' }>${ i }</option>`;
			}

			schedulePages += `</select>`;

			$( '#fspSchedulesPages' ).html( schedulePages );
		});
	}

	function scheduleData( schedule ) {
		let statuses = {
			'paused': fsp__( 'paused' ),
			'finished': fsp__( 'finished' ),
			'active': fsp__( 'active' )
		};
		let status_btn = 'danger';
		if( schedule.status === 'finished' )
		{
			status_btn = 'success';
		}
		else if( schedule.status === 'paused' )
		{
			status_btn = 'warning';
		}

		let post_ids = schedule.save_post_ids;
		post_ids = !post_ids ? [] : post_ids.split(',');
		schedule.post_ids = post_ids;

		let nextPostDate = schedule.status === 'active' ? schedule.next_execute_time.substring(0, schedule.next_execute_time.lastIndexOf(':')) : '-';

		return `
			<div data-id="${ schedule.id }" class="fsp-schedule">
				<div class="fsp-schedule-checkbox-container">
					<input data-id="${ schedule.id }" type="checkbox" class="fsp-form-checkbox fsp-schedule-checkbox">
				</div>
				<div class="fsp-schedule-icon">
					<i class="fas fa-thumbtack"></i>
				</div>
				<div class="fsp-schedule-title">
				<div class="fsp-schedule-title-text">
					${ schedule.title.substring(0, 55) }
					${ (schedule.sleep_time_start && schedule.sleep_time_end) ? `
						<i class="fas fa-moon fsp-tooltip" data-title="Sleep times: ${ schedule.sleep_time_start } - ${ schedule.sleep_time_end } "></i>
					` : '' }
				</div>
				<div class="fsp-schedule-title-subtext">
					${ schedule.post_ids.length === 1 ? `
						${ fsp__( 'Post ID' ) }: ${ schedule.post_ids[0] }
						${ schedule.post_permalink.indexOf( 'fs_post' ) > -1 ? ', ' + fsp__( 'Scheduled on DIRECT SHARE' ) : `<a href="${ schedule.post_permalink }" target="_blank">${ schedule.the_post_title }</a>` }
					` : `
						Post type: ${ schedule.post_type_text }
					` },
					${ fsp__( 'Interval: ' )} ${ schedule.post_ids.length === 1 && schedule.post_freq === 'once' ? fsp__( 'no interval' ) : ( schedule.interval % 1440 === 0 ? ( schedule.interval  / 1440) + fsp__( ' day(s)' ) : ( schedule.interval % 60 === 0 ? schedule.interval / 60 + fsp__( ' hour(s)' ) : ( schedule.interval + fsp__( ' minute(s)' ) ) ) ) }
				</div>
				</div>
				<div class="fsp-schedule-dates">
					${ (schedule.post_ids.length === 1 && schedule.post_freq === 'once') ? `
						<div class="fsp-schedule-dates-row">
							<div class="fsp-schedule-dates-date">
								<i class="far fa-calendar-alt"></i> ${ schedule.start_date } - ${ schedule.share_time }
							</div>
						</div>
					` : `
						<div class="fsp-schedule-dates-row">
							<div class="fsp-schedule-dates-label">
								${ fsp__( 'Start date' ) }
						    </div>
						    <div class="fsp-schedule-dates-date">
								<i class="far fa-calendar-alt"></i> ${ schedule.start_date } ${schedule.share_time}
						    </div>
						</div>
						<div class="fsp-schedule-dates-row">
							<div class="fsp-schedule-dates-label">
								${ fsp__( 'Next Post' ) }
						   	</div>
						   	<div class="fsp-schedule-dates-date">
								<i class="far fa-calendar-alt"></i> ${ nextPostDate }
						   	</div>
						</div>
					` }
				</div>
				<div class="fsp-schedule-controls">
					<span class="fsp-status fsp-is-${ status_btn }">
						${ statuses[ schedule.status ] }
					</span>
					
					${ schedule.status === 'active' ? `<button type="button" class="fsp-button fsp-is-info fsp-tooltip fsp-change-schedule" data-id="${ schedule.id }" data-title="${ fsp__( "Pause shares" ) }">
					<i class="fa fa-pause"></i>
					</button>` : '' }
					${ schedule.status === 'paused' ? `<button type="button" class="fsp-button fsp-is-info fsp-tooltip fsp-change-schedule" data-id="${ schedule.id }" data-title="${ fsp__( "Resume shares" ) }">
						<i class="fa fa-play"></i>
					</button>` : '' }
						
					<div class="fsp-schedule-control fsp-tooltip" data-title="${ fsp__( 'Logs' ) }" data-load-modal="posts_list" data-parameter-schedule_id="${ schedule.id }" data-fullscreen="true">
						<i class="fas fa-bars"></i>
						<span class="fsp-schedule-control-text">${ schedule.shares_count }</span>
					</div>
					<div class="fsp-schedule-control">
						<i class="far fa-user fsp-tooltip" data-title="${ fsp__('Selected account(s)') }"></i>
						<span class="fsp-schedule-control-text">${ schedule.accounts_count }</span>
					</div>
					
					${ schedule.status !== 'finished' ? `
						<div class="fsp-schedule-control" data-load-modal="edit_schedule" data-parameter-schedule_id="${ schedule.id }">
							<i class="far fa-edit"></i>
						</div>
					` : `
						<div class="fsp-schedule-control fsp-tooltip" data-title="${ fsp__( 'Re-schedule' ) }" data-load-modal="edit_schedule" data-parameter-schedule_id="${ schedule.id }">
							<i class="fas fa-sync"></i>
						</div>
					` }
					
					<div data-id="${ schedule.id }" class="fsp-schedule-control fsp-delete-schedule">
						<i class="far fa-trash-alt"></i>
					</div>
			</div>
			</div>
		`;
	}

} )( jQuery );
