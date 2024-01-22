'use strict';

( function ( $ ) {
	let doc = $( document );
	let schedule_data = $( '#fspKeepLogs' ).val();
	let schedule_id = $( '#fspScheduleID' ).val();

	let date_range_is_hidden = $( '#fspScheduleDateRangeRow' ).hasClass( 'fsp-hide' );
	let post_type_is_hidden = $( '#fspSchedulePostTypeFilterRow' ).hasClass( 'fsp-hide' );
	let out_of_stock_is_hidden = $( '#fspScheduleOutOfStockRow' ).hasClass( 'fsp-hide' );
	let category_filter_is_hidden = $( '#fspScheduleCategoryFilterRow' ).hasClass( 'fsp-hide' );

	doc.ready( function () {
		$( 'body' ).append( '<style>.select2.select2-container.select2-container--default{width:100%!important;}</style>' );
		$( '.select2-init' ).select2( {
			containerCssClass: 'fsp-select2-container',
			dropdownCssClass: 'fsp-select2-dropdown',
			placeholder: fsp__( 'Search categories, tags... ( min. 2 character )' ), ajax: {
				url: ajaxurl, type: 'POST', dataType: 'json', data: function ( params ) {
					return {
						action: 'get_tags_and_cats', search: params.term
					};
				}, processResults: function ( data ) {
					return {
						results: data.result
					};
				}, minimumInputLength: 2
			}
		} );

		$( '.schedule_input_post_ids' ).on( 'keyup', function () {
			let idsFieldIsNotEmpty = $( this ).val() !== '' && $( this ).val() !== undefined;

			if ( idsFieldIsNotEmpty || date_range_is_hidden )
			{
				$( '#fspScheduleDateRangeRow' ).addClass( 'fsp-hide' );
			}
			else
			{
				$( '#fspScheduleDateRangeRow' ).removeClass( 'fsp-hide' );
			}

			if ( idsFieldIsNotEmpty || post_type_is_hidden )
			{
				$( '#fspSchedulePostTypeFilterRow' ).addClass( 'fsp-hide' );
			}
			else
			{
				$( '#fspSchedulePostTypeFilterRow' ).removeClass( 'fsp-hide' );
			}

			if ( idsFieldIsNotEmpty || out_of_stock_is_hidden )
			{
				$( '#fspScheduleOutOfStockRow' ).addClass( 'fsp-hide' );
			}
			else
			{
				$( '#fspScheduleOutOfStockRow' ).removeClass( 'fsp-hide' );
			}

			if ( idsFieldIsNotEmpty || category_filter_is_hidden )
			{
				$( '#fspScheduleCategoryFilterRow' ).addClass( 'fsp-hide' );
			}
			else
			{
				$( '#fspScheduleCategoryFilterRow' ).removeClass( 'fsp-hide' );
			}

		} );

		$( '.schedule_popup' ).on( 'click', '.schedule_save_btn', function () {
			let title = $( '.schedule_popup .schedule_input_title' ).val(),
				startDate = $( '.schedule_popup .schedule_input_start_date' ).val(),
				startTime = $( '.schedule_popup .schedule_input_start_time' ).val(),
				interval = $( '.schedule_popup .interval' ).val(),
				intervalType = $( '.schedule_popup .interval_type' ).val(),
				post_type_filter = $( '.schedule_popup .schedule_input_post_type_filter' ).val(),
				dont_post_out_of_stock_products = $( '.schedule_popup .schedule_dont_post_out_of_stock_products' ).is( ':checked' ) ? 1 : 0,
				category_filter = $( '.schedule_popup .schedule_input_category_filter' ).val(),
				post_ids = $( '.schedule_popup .schedule_input_post_ids' ).val(),
				post_freq = $( '.schedule_popup .post_freq' ).val(),
				post_sort = $( '.schedule_popup .post_sort' ).val(),
				autoRescheduleEnabled = $( '#fspScheduleAutoReschedule' ).is( ':checked' ) ? 1 : 0,
				autoRescheduleCount = $( '.schedule_auto_reschedule_count' ).val(),

				filter_posts_date_range_from = $( '#fsp_filter_posts_date_range_from' ).val(),
				filter_posts_date_range_to = $( '#fsp_filter_posts_date_range_to' ).val(),

				set_sleep_time = $( '.schedule_popup .schedule_set_sleep_time' ).is( ':checked' ) ? 1 : 0,
				sleep_time_start = set_sleep_time ? $( '.schedule_popup .schedule_input_sleep_time_start' ).val() : '',
				sleep_time_end = set_sleep_time ? $( '.schedule_popup .schedule_input_sleep_time_end' ).val() : '',
				custom_messages = {}, accounts_list = [];

			if ( interval % 1 !== 0 )
			{
				FSPoster.toast( fsp__( 'Interval is not correct!' ), 'warning' );

				return false;
			}

			let matchesCount = parseInt( $( '.schedule_popup .schedule_matches_count' ).text().trim() );

			if ( matchesCount === 0 )
			{
				FSPoster.toast( fsp__( 'No post matches your filters!' ), 'warning' );

				return false;
			}
			else if ( matchesCount > 1 && post_freq === 'repeat' )
			{
				FSPoster.toast( fsp__( 'If you want to share repeatedly, you should schedule only a post!' ), 'warning' );

				return false;
			}

			$( '.schedule_popup .fsp-custom-post > textarea' ).each( function () {
				custom_messages[ $( this ).data( 'sn-id' ) ] = $( this ).val();
			} );

			let instagramPin = $( '#instagram_pin_post' ).is( ':checked' ) ? 1 : 0;

			let bypass_confirm_dialog = true;

			$( '.fspAddSchedule-step .fsp-metabox-account > input[name="share_on_nodes[]"]' ).each( function () {
				let splitVal = $( this ).val().split( ':' );

				if ( splitVal[ 3 ] !== 'no' )
				{
					bypass_confirm_dialog = false;
				}

				accounts_list.push( $( this ).val() );
			} );

			if ( schedule_data === 'off' && post_sort === 'random2' )
			{
				FSPoster.alert( 'You can not select "Random (no duplicates)" option. Because in your Publish settings "Keep shared posts log" is disabled. Please activate it firstly.' );

				return false;
			}

			if ( bypass_confirm_dialog === false )
			{
				FSPoster.confirm( 'You have accounts activated with conditions in the schedule module Accounts tab. Those conditions don\'t apply to schedules. You should add or remove your accounts according to your schedule. Do you want to continue?', function () {
					save_schedule();
				}, 'fas fa-question', 'YES, CONTINUE', function () {
					$( '.fsp-modal-tab' ).eq( 2 ).click();
				} );
			}
			else
			{
				save_schedule();
			}

			function save_schedule ()
			{
				FSPoster.ajax( 'schedule_save', {
					'id': schedule_id,
					'title': title,
					'start_date': startDate,
					'start_time': startTime,
					'interval': ( parseInt( interval ) * parseInt( intervalType ) ),
					'post_type_filter': post_type_filter,
					'filter_posts_date_range_from': filter_posts_date_range_from,
					'filter_posts_date_range_to': filter_posts_date_range_to,
					'dont_post_out_of_stock_products': dont_post_out_of_stock_products,
					'category_filter': category_filter,
					'post_ids': post_ids,
					'post_freq': post_freq,
					'post_sort': post_sort,
					'sleep_time_start': sleep_time_start,
					'sleep_time_end': sleep_time_end,
					'custom_messages': JSON.stringify( custom_messages ),
					'accounts_list': JSON.stringify( accounts_list ),
					'instagram_pin_the_post': instagramPin,
					autoRescheduleCount,
					autoRescheduleEnabled
				}, function () {
					FSPoster.loading( true );

					let page = 1, activePage = $( '.fsp-is-danger.fsp-schedule-page' );

					if ( activePage && activePage.data( 'page' ) )
					{
						page = activePage.data( 'page' );
					}

					let fspScheduleSearchInput = $( '#fsp-schedule-search-input' ).length ? $( '#fsp-schedule-search-input' ).val() : ' ';
					let searchedKeyword = fspScheduleSearchInput.trim().toLowerCase();

					window.location.href = 'admin.php?page=fs-poster-schedules&view=list&search=' + searchedKeyword + '&schedule_page=' + page;
				} );
			}
		} ).on( 'click', '.wp_native_schedule_save_btn', function () {
			let info = $( this ).data( 'info' ), custom_messages = {}, accounts_list = [];

			$( '.schedule_popup .fsp-custom-post > textarea' ).each( function () {
				custom_messages[ $( this ).data( 'sn-id' ) ] = $( this ).val();
			} );

			$( '.fsp-metabox-account > input[name="share_on_nodes[]"]' ).each( function () {
				let splitVal = $( this ).val().split( ':' );
				let realVal = splitVal.length === 3 ? `${ splitVal[ 1 ] }:${ splitVal[ 2 ] }` : $( this ).val();

				accounts_list.push( realVal );
			} );

			let instagramPin = $( '#instagram_pin_post' ).is( ':checked' ) ? 1 : 0;

			if ( Array.isArray( accounts_list ) && accounts_list.length > 0 )
			{
				FSPoster.ajax( 'wp_native_schedule_save', {
					'info': JSON.stringify( info ),
					'custom_messages': JSON.stringify( custom_messages ),
					'accounts_list': JSON.stringify( accounts_list ),
					'instagram_pin_the_post': instagramPin
				}, function ( result ) {
					FSPoster.loading( true );

					window.location.reload();
				} );
			}
			else
			{
				$( '.wp_native_schedule_delete_btn' ).trigger( 'click' );
			}

		} ).on( 'click', '.wp_native_schedule_delete_btn', function () {
			let info = $( this ).data( 'info' );

			FSPoster.confirm( fsp__( 'Are you sure to delete the schedule?' ), function () {
				FSPoster.ajax( 'wp_native_schedule_delete', {
					'info': JSON.stringify( info )
				}, function ( result ) {
					FSPoster.loading( true );
					window.location.reload();
				} );
			} );

		} ).on( 'blur', '.schedule_input_post_ids', function () {
			if ( $( this ).val() == '' && ! $( this ).data( 'old-value' ) )
			{
				return;
			}

			if ( $( this ).val() == $( this ).data( 'old-value' ) )
			{
				return;
			}

			$( this ).data( 'old-value', $( this ).val() );

			recalculate_filtered_posts_count();
		} ).on( 'change', '.schedule_input_post_type_filter', function () {
			var post_type = $( this ).val();

			if ( post_type == 'product' )
			{
				$( '.schedule_popup .fs_stock_option_area' ).slideDown( 200 );
			}
			else
			{
				$( '.schedule_popup .fs_stock_option_area' ).slideUp( 200 );
			}
		} ).on( 'change', '#fspScheduleSetSleepTime', function () {
			let checked = ! ( $( this ).is( ':checked' ) );

			$( '#fspScheduleSetSleepTimeContainer' ).toggleClass( 'fsp-hide', checked );
		} ).on( 'change', '#fspScheduleAutoReschedule', function () {
			let checked = ! ( $( this ).is( ':checked' ) );

			$( '#fspScheduleRescheduleCount' ).toggleClass( 'fsp-hide', checked );
		} ).on( 'change', '.post_freq', function () {
			let val = $( this ).val();

			if ( val === 'once' )
			{
				$( '#fspSchedulePostEveryRow' ).addClass( 'fsp-hide' );
			}
			else if ( val === 'repeat' )
			{
				$( '#fspSchedulePostEveryRow' ).removeClass( 'fsp-hide' );
			}
		} ).on( 'change', '#fsp_filter_posts_date_range', function () {
			let _this = $( this );
			let value = _this.val();

			let today = dayjs();
			let from = today;
			let to = today;

			let from_elm = $( '#fsp_filter_posts_date_range_from' );
			let to_elm = $( '#fsp_filter_posts_date_range_to' );

			switch ( value )
			{
				case 'today':
					$( '#fsp_filter_posts_custom_date_range_row' ).removeClass( 'fsp-hide' );

					break;

				case 'last_7_days':
					from = today.subtract( 7, 'day' );

					$( '#fsp_filter_posts_custom_date_range_row' ).removeClass( 'fsp-hide' );

					break;

				case 'last_15_days':
					from = today.subtract( 15, 'day' );

					$( '#fsp_filter_posts_custom_date_range_row' ).removeClass( 'fsp-hide' );

					break;

				case 'last_30_days':
					from = today.subtract( 30, 'day' );

					$( '#fsp_filter_posts_custom_date_range_row' ).removeClass( 'fsp-hide' );

					break;

				case 'last_90_days':
					from = today.subtract( 90, 'day' );

					$( '#fsp_filter_posts_custom_date_range_row' ).removeClass( 'fsp-hide' );

					break;

				case 'last_180_days':
					from = today.subtract( 180, 'day' );

					$( '#fsp_filter_posts_custom_date_range_row' ).removeClass( 'fsp-hide' );

					break;

				case 'last_365_days':
					from = today.subtract( 365, 'day' );

					$( '#fsp_filter_posts_custom_date_range_row' ).removeClass( 'fsp-hide' );

					break;

				case 'custom_date_range':
					$( '#fsp_filter_posts_custom_date_range_row' ).removeClass( 'fsp-hide' );

					break;

				case 'all_date_range':
					$( '#fsp_filter_posts_custom_date_range_row' ).addClass( 'fsp-hide' );

					from = today.subtract( 1000, 'year' );
					to = today.add( 1000, 'year' );

					break;
			}

			from = from.format( 'YYYY-MM-DD' );
			to = to.format( 'YYYY-MM-DD' );

			from_elm.val( from );
			to_elm.val( to );
		} );

		$( '.schedule_popup .schedule_input_post_ids' ).data( 'old-value', $( '.schedule_popup .schedule_input_post_ids' ).val() );
		$( '.schedule_popup' ).on( 'change', '#fsp_filter_posts_date_range, .schedule_input_post_type_filter, .schedule_input_category_filter, .schedule_dont_post_out_of_stock_products, #fsp_filter_posts_date_range_from, #fsp_filter_posts_date_range_to', function () {
			recalculate_filtered_posts_count();
		} );
		$( '.schedule_popup .schedule_set_sleep_time' ).trigger( 'change' );
		$( '.schedule_popup .sn_tabs > [data-tab-id]:eq(0)' ).trigger( 'click' );
		$( '.schedule_popup .schedule_input_post_type_filter' ).trigger( 'change' );

		$( '#fsp_filter_posts_date_range_from' ).on( 'change', function () {
			let value = $( this ).val();

			$( '#fsp_filter_posts_date_range_to' ).prop( 'min', value );
		} );
		$( '#fsp_filter_posts_date_range_to' ).on( 'change', function () {
			let value = $( this ).val();

			$( '#fsp_filter_posts_date_range_from' ).prop( 'max', value );
		} );

		$( '.fsp-custom-messages-tab' ).eq( 0 ).click();

		recalculate_filtered_posts_count();
	} );

	function recalculate_filtered_posts_count ()
	{
		let filter_posts_date_range_from = $( '#fsp_filter_posts_date_range_from' ).val(),
			filter_posts_date_range_to = $( '#fsp_filter_posts_date_range_to' ).val(),
			post_type_filter = $( '.schedule_popup .schedule_input_post_type_filter' ).val(),
			dont_post_out_of_stock_products = $( '.schedule_popup .schedule_dont_post_out_of_stock_products' ).is( ':checked' ) ? 1 : 0,
			category_filter = $( '.schedule_popup .schedule_input_category_filter' ).val(),
			post_ids = $( '.schedule_popup .schedule_input_post_ids' ).val();

		FSPoster.ajax( 'recalculate_filtered_posts_count', {
			filter_posts_date_range_from: filter_posts_date_range_from,
			filter_posts_date_range_to: filter_posts_date_range_to,
			post_type_filter: post_type_filter,
			dont_post_out_of_stock_products: dont_post_out_of_stock_products,
			category_filter: category_filter,
			post_ids: post_ids
		}, function ( result ) {
			$( '.schedule_popup .schedule_matches_count' ).text( result[ 'count' ] );

			if ( parseInt( result[ 'count' ] ) > 1 )
			{
				$( '#fspScheduleHowShareRow' ).addClass( 'fsp-hide' ).find( '.post_freq' ).val( 'once' );
				$( '#fspScheduleOrderPostsRow, #fspScheduleOutOfStockRow, #fspSchedulePostEveryRow' ).removeClass( 'fsp-hide' );
			}
			else
			{
				$( '#fspScheduleHowShareRow' ).removeClass( 'fsp-hide' );
				$( '#fspScheduleOrderPostsRow, #fspScheduleOutOfStockRow' ).addClass( 'fsp-hide' );
				$( '.schedule_popup .post_freq' ).trigger( 'change' );
			}

			if ( post_type_filter === 'product' )
			{
				$( '#fspScheduleOutOfStockRow' ).removeClass( 'fsp-hide' );
			}
			else
			{
				$( '#fspScheduleOutOfStockRow' ).addClass( 'fsp-hide' );
			}

			out_of_stock_is_hidden = $( '#fspScheduleOutOfStockRow' ).hasClass( 'fsp-hide' );
		} );
	}
} )( jQuery );