jQuery(document).ready(function($) {

	/* Variables -----------------------------------------------------------------*/
	
	var knowledgebase;
	// If Module Layout is active
	if ( $( '#epkb-modular-main-page-container' ).length ) {
		knowledgebase = $( '#epkb-modular-main-page-container' );
	} else {
		// Use Legacy Layouts as a fallback if the modular Main Page doesn't exist
		knowledgebase = $( '#epkb-main-page-container' );
	}
	var tabContainer = $('#epkb-content-container');
	var navTabsLi    = $('.epkb-nav-tabs li');
	var tabPanel     = $('.epkb-tab-panel');
	var articleContent = $('#eckb-article-content-body');
	var articleToc     = $('.eckb-article-toc');

	/********************************************************************
	 *                      Search
	 ********************************************************************/

	// handle KB search form
	$( 'body' ).on( 'submit', '#epkb_search_form', function( e ) {
		e.preventDefault();  // do not submit the form

		if ( $('#epkb_search_terms').val() === '' ) {
			return;
		}

		var postData = {
			action: 'epkb-search-kb',
			epkb_kb_id: $('#epkb_kb_id').val(),
			search_words: $('#epkb_search_terms').val(),
			is_kb_main_page: $('.eckb_search_on_main_page').length
		};

		var msg = '';

		$.ajax({
			type: 'GET',
			dataType: 'json',
			data: postData,
			url: epkb_vars.ajaxurl,
			beforeSend: function (xhr)
			{
				//Loading Spinner
				$( '.loading-spinner').css( 'display','block');
				$('#epkb-ajax-in-progress').show();
			}

		}).done(function (response)
		{
			response = ( response ? response : '' );

			//Hide Spinner
			$( '.loading-spinner').css( 'display','none');

			if ( response.error || response.status !== 'success') {
				//noinspection JSUnresolvedVariable
				msg = epkb_vars.msg_try_again;
			} else {
				msg = response.search_result;
			}

		}).fail(function (response, textStatus, error)
		{
			//noinspection JSUnresolvedVariable
			msg = epkb_vars.msg_try_again + '. [' + ( error ? error : epkb_vars.unknown_error ) + ']';

		}).always(function ()
		{
			$('#epkb-ajax-in-progress').hide();

			if ( msg ) {
				$( '#epkb_search_results' ).css( 'display','block' );
				$( '#epkb_search_results' ).html( msg );

			}

		});
	});

	$("#epkb_search_terms").on( 'keyup', function() {
		if (!this.value) {
			$('#epkb_search_results').css( 'display','none' );
		}
	});

	/********************************************************************
	 *                      Module Search
	 ********************************************************************/

	// handle KB search form
	$( 'body' ).on( 'submit', '#epkb-ml-search-form', function( e ) {
		e.preventDefault();  // do not submit the form

		if ( $( '.epkb-ml-search-box__input' ).val() === '' ) {
			return;
		}

		var postData = {
			action: 'epkb-search-kb',
			epkb_kb_id: $( '#epkb_kb_id' ).val(),
			search_words: $( '.epkb-ml-search-box__input' ).val(),
		};

		var msg = '';

		$.ajax({
			type: 'GET',
			dataType: 'json',
			data: postData,
			url: epkb_vars.ajaxurl,
			beforeSend: function (xhr)
			{
				//Loading Spinner
				$( '.epkbfa-ml-loading-icon').css( 'visibility','visible');
				$( '.epkbfa-ml-search-icon').css( 'visibility','hidden');
				$( '.epkb-ml-search-box__text').css( 'visibility','hidden');
				$( '#epkb-ajax-in-progress' ).show();
			}

		}).done(function (response)
		{
			response = ( response ? response : '' );

			//Hide Spinner
			$( '.epkbfa-ml-loading-icon').css( 'visibility','hidden');
			$( '.epkbfa-ml-search-icon').css( 'visibility','visible');
			$( '.epkb-ml-search-box__text').css( 'visibility','visible');

			if ( response.error || response.status !== 'success') {
				//noinspection JSUnresolvedVariable
				msg = epkb_vars.msg_try_again;
			} else {
				msg = response.search_result;
			}

		}).fail(function (response, textStatus, error)
		{
			//noinspection JSUnresolvedVariable
			msg = epkb_vars.msg_try_again + '. [' + ( error ? error : epkb_vars.unknown_error ) + ']';

		}).always(function ()
		{
			$('#epkb-ajax-in-progress').hide();

			if ( msg ) {
				$( '#epkb-ml-search-results' ).css( 'display','block' ).html( msg );
				if ( $( '.epkb-ml-search-results__no-results' ).length > 0 ) {
					$( '#epkb-ml-search-results' ).css( 'height','64px' );
				} else {
					$( '#epkb-ml-search-results' ).css( 'height','' );
				}
			}
		});
	});

	$( document ).on('click', function( event ) {
		let searchResults = $( '#epkb-ml-search-results' );
		let searchBox = $( '#epkb-ml-search-box' );

		let isClickInsideResults = searchResults.has( event.target ).length > 0;
		let isClickInsideSearchBox = searchBox.has( event.target ).length > 0;

		if ( !isClickInsideResults && !isClickInsideSearchBox ) {
			// Click is outside of search results and search box
			searchResults.hide(); // Hide the search results
		}
	});

	$( ".epkb-ml-search-box__input" ).on( 'keyup', function() {
		if ( !this.value ) {
			$( '#epkb-ml-search-results' ).css( 'display','none' );
		}
	});

	/********************************************************************
	 *                      Tabs / Mobile Select
	 ********************************************************************/

	//Get the highest height of Tab and make all other tabs the same height
	if ( tabContainer.length && navTabsLi.length ){
		let tallestHeight = 0;
		tabContainer.find( navTabsLi ).each( function(){
			let this_element = $(this).outerHeight( true );
			if( this_element > tallestHeight ) {
				tallestHeight = this_element;
			}
		});
		tabContainer.find( navTabsLi ).css( 'min-height', tallestHeight );
	}

	function changePanels( Index ){
		$('.epkb-panel-container .epkb-tab-panel:nth-child(' + (Index + 1) + ')').addClass('active');
	}

	function updateTabURL( tab_id, tab_name ) {
		var location = window.location.href;
		location = update_query_string_parameter(location, 'top-category', tab_name);
		window.history.pushState({"tab":tab_id}, "title", location);
		// http://stackoverflow.com/questions/32828160/appending-parameter-to-url-without-refresh
	}

	window.onpopstate = function(e){

		if ( e.state && e.state.tab.indexOf('epkb_tab_') !== -1) {
			//document.title = e.state.pageTitle;

			// hide old section
			tabContainer.find('.epkb_top_panel').removeClass('active');

			// re-set tab; true if mobile drop-down
			if ( $( "#main-category-selection" ).length > 0 )
			{
				$("#main-category-selection").val(tabContainer.find('#' + e.state.tab).val());
			} else {
				tabContainer.find('.epkb_top_categories').removeClass('active');
				tabContainer.find('#' + e.state.tab).addClass('active');
			}

			tabContainer.find('.' + e.state.tab).addClass('active');

		// if user tabs back to the initial state, select the first tab if not selected already
		} else if ( $('#epkb_tab_1').length > 0 && ! tabContainer.find('#epkb_tab_1').hasClass('active') ) {

			// hide old section
			tabContainer.find('.epkb_top_panel').removeClass('active');

			// re-set tab; true if mobile drop-down
			if ( $( "#main-category-selection" ).length > 0 )
			{
				$("#main-category-selection").val(tabContainer.find('#epkb_tab_1').val());
			} else {
				tabContainer.find('.epkb_top_categories').removeClass('active');
				tabContainer.find('#epkb_tab_1').addClass('active');
			}

			tabContainer.find('.epkb_tab_1').addClass('active');
		}
	};

	// Tabs Layout: switch to the top category user clicked on
	tabContainer.find( navTabsLi ).each(function(){

		$(this).on('click', function (){
			tabContainer.find( navTabsLi ).removeClass('active');

			$(this).addClass('active');

			tabContainer.find(tabPanel).removeClass('active');
			changePanels ( $(this).index() );
			updateTabURL( $(this).attr('id'), $(this).data('cat-name') );
		});
	});

	// Tabs Layout: MOBILE: switch to the top category user selected
	$( "#main-category-selection" ).on( 'change', function() {
			tabContainer.find(tabPanel).removeClass('active');
			// drop down
			$( "#main-category-selection option:selected" ).each(function() {
				var selected_index = $( this ).index();
				changePanels ( selected_index );
				updateTabURL( $(this).attr('id'), $(this).data('cat-name') );
			});
		});

	function update_query_string_parameter(uri, key, value) {
		var re = new RegExp("([?&])" + key + "=.*?(&|$)", "i");
		var separator = uri.indexOf('?') !== -1 ? "&" : "?";
		if (uri.match(re)) {
			return uri.replace(re, '$1' + key + "=" + value + '$2');
		}
		else {
			return uri + separator + key + "=" + value;
		}
	}


	/********************************************************************
	 *                      Sections
	 ********************************************************************/

	//Detect if a an div is inside an list item then it's a sub category
	$('.epkb-section-body .epkb-category-level-2-3').each(function(){

		$(this).on('click', function(){

			$(this).parent().children('ul').toggleClass('active');

			// Accessibility: aria-expand

			// Get current data attribute value
			let ariaExpandedVal = $( this ).attr( 'aria-expanded' );

			// Switch the value of the data Attribute on click.
			switch( ariaExpandedVal ) {
				case 'true':
					// It is being closed so Set to False
					$( this ).attr( 'aria-expanded', 'false' );
					break;
				case 'false':
					// It is being opened so Set to True
					$( this ).attr( 'aria-expanded', 'true' );
					break;
				default:
			}

		});
	});

	/**
	 * Sub Category icon toggle
	 *
	 * Toggle between open icon and close icon
	 * Accessibility: Set aria-expand values
	 */
	tabContainer.find('.epkb-section-body .epkb-category-level-2-3').each(function(){

		if( $(this).hasClass( 'epkb-category-focused' ) ) {
			return;
		}

		var $icon = $(this).find('.epkb-category-level-2-3__cat-icon');

		$(this).on('click', function (){

			var plus_icons = [ 'ep_font_icon_plus' ,'ep_font_icon_minus' ];
			var plus_icons_box = [ 'ep_font_icon_plus_box' ,'ep_font_icon_minus_box' ];
			var arrow_icons1 = [ 'ep_font_icon_right_arrow' ,'ep_font_icon_down_arrow' ];
			var arrow_icons2 = [ 'ep_font_icon_arrow_carrot_right' ,'ep_font_icon_arrow_carrot_down' ];
			var arrow_icons3 = [ 'ep_font_icon_arrow_carrot_right_circle' ,'ep_font_icon_arrow_carrot_down_circle' ];
			var folder_icon = [ 'ep_font_icon_folder_add' ,'ep_font_icon_folder_open' ];

			function toggle_category_icons( $array ){

				//If Parameter Icon exists
				if( $icon.hasClass( $array[0] ) ){

					$icon.removeClass( $array[0] );
					$icon.addClass( $array[1] );

				}else if ( $icon.hasClass( $array[1] )){

					$icon.removeClass( $array[1] );
					$icon.addClass($array[0]);
				}
			}

			toggle_category_icons( plus_icons );
			toggle_category_icons( plus_icons_box );
			toggle_category_icons( arrow_icons1 );
			toggle_category_icons( arrow_icons2 );
			toggle_category_icons( arrow_icons3 );
			toggle_category_icons( folder_icon );
		});
	});

	/**
	 * Show all articles functionality
	 *
	 * When user clicks on the "Show all articles" it will toggle the "hide" class on all hidden articles
	 */
	knowledgebase.find('.epkb-show-all-articles').on( 'click', function () {

		$( this ).toggleClass( 'active' );
		var parent = $( this ).parent( 'ul' );
		var article = parent.find( 'li');

		//If this has class "active" then change the text to Hide extra articles
		if ( $(this).hasClass( 'active')) {

			//If Active
			$(this).find('.epkb-show-text').addClass('epkb-hide-elem');
			$(this).find('.epkb-hide-text').removeClass('epkb-hide-elem');
			$(this).attr( 'aria-expanded','true' );

		} else {
			//If not Active
			$(this).find('.epkb-show-text').removeClass('epkb-hide-elem');
			$(this).find('.epkb-hide-text').addClass('epkb-hide-elem');
			$(this).attr( 'aria-expanded','false' );
		}

		$( article ).each(function() {

			//If has class "hide" remove it and replace it with class "Visible"
			if ( $(this).hasClass( 'epkb-hide-elem')) {
				$(this).removeClass('epkb-hide-elem');
				$(this).addClass('visible');
			}else if ( $(this).hasClass( 'visible')) {
				$(this).removeClass('visible');
				$(this).addClass('epkb-hide-elem');
			}
		});
	});
	
	let search_text = $( '#epkb-search-kb' ).text();
	$( '#epkb-search-kb' ).text( search_text );


	/********************************************************************
	 *                      Article Print 
	 ********************************************************************/
	$('body').on("click", ".eckb-print-button-container, .eckb-print-button-meta-container", function(event) {
		
		if ( $('body').hasClass('epkb-editor-preview') ) {
			return;
		}
		
		$('#eckb-article-content').parents().each(function(){
			$(this).siblings().addClass('eckb-print-hidden');
		});
		
		window.print();
	});


	/********************************************************************
	 *                      Article TOC v2
	 ********************************************************************/
	let TOC = {
		
		firstLevel: 1, 
		lastLevel: 6, 
		searchStr: '',
		currentId: '',
		offset: 50,
		excludeClass: false,
		
		init: function() {
			this.getOptions();
			
			let articleHeaders = this.getArticleHeaders();
			
			// show TOC only if headers are present
			if ( articleHeaders.length > 0 ) {
				
				articleToc.html( this.getToCHTML( articleHeaders ) );

				// Add h2 title for Article content section
				if( $('#eckb-article-content .eckb-article-toc').length > 0 ) {
					
					$('#eckb-article-content .eckb-article-toc').html( this.getToCHTML( articleHeaders, 'h2' ) );
				}

				if( $(' .eckb-article-toc--position-middle').length > 0 ) {
					articleToc.css('display', 'inline-block' );
				} else {
					articleToc.fadeIn();
				}
				
			} else {
				articleToc.hide();

				//FOR FE Editor ONLY
				if ($('body').hasClass('epkb-editor-preview')) {
					articleToc.show();
					let title = articleToc.find('.eckb-article-toc__title').html();
					let html = `
						<div class="eckb-article-toc__inner">
							<h4 class="eckb-article-toc__title">${title}</h4>
							<nav class="eckb-article-toc-outline" role="navigation" aria-label="Article outline">
							<ul>
								<li>${epkb_vars.toc_editor_msg}</li>
							</ul>
							</nav>
							</div>
						</div>	
						`;
					articleToc.html( html );
				}
				
			}
			
			let that = this;
			
			$('.eckb-article-toc__level a').on('click', function( e ){
				
				if ( $('.epkb-editor-preview').length ) {
					e.preventDefault();
					return;
				}
				
				let target = $(this).data('target');
				
				if ( ! target || $( '[data-id=' + target + ']' ).length == 0 ) {
					return false;
				}

				// calculate the speed of animation
				let initial_scroll_top = $('body, html').scrollTop();
				let current_scroll_top = $( '[data-id=' + target + ']').offset().top - that.offset;
				let animate_speed =  parseInt($(this).closest('.eckb-article-toc').data('speed'));

				$('body, html').animate({ scrollTop: current_scroll_top }, animate_speed);
				
				return false;
			});
			
			$(window).on( 'scroll', this.scrollSpy );
			
			this.scrollSpy();
			
			// scroll to element if it is in the hash 
			if ( ! location.hash ) {
				return;
			}
			
			let hash_link = $('[data-target=' + location.hash.slice(1) + ']' );
			if ( hash_link.length ) {
				hash_link.trigger( 'click' );
			}
		},
		
		getOptions: function() {
			
			if ( articleToc.data( 'min' ) ) {
				this.firstLevel = articleToc.data( 'min' );
			}
			
			if ( articleToc.data( 'max' ) ) {
				this.lastLevel = articleToc.data( 'max' );
			}
			
			if ( articleToc.data( 'offset' ) ) {
				this.offset = articleToc.data( 'offset' );
			} else {
				articleToc.data( 'offset', this.offset )
			}

			
			if ( typeof articleToc.data('exclude_class') !== 'undefined' ) {
				this.excludeClass = articleToc.data('exclude_class');
			}
			
			while ( this.firstLevel <= this.lastLevel ) {
				this.searchStr += 'h' + this.firstLevel + ( this.firstLevel < this.lastLevel ? ',' : '' );
				this.firstLevel++;
			}
		},
		
		// return object with headers and their ids 
		getArticleHeaders: function () {
			let headers = [];
			let that = this;
			
			articleContent.find( that.searchStr ).each( function(){
					
				if ( $(this).text().length == 0 ) {
					return;
				}
					
				if ( that.excludeClass && $(this).hasClass( that.excludeClass ) ) {
					return;
				}
					
				let tid;
				let header = {};
						
				if ( $(this).data( 'id' ) ) {
					tid = $(this).data( 'id' );
				} else {
					tid = 'articleTOC_' + headers.length;
					$(this).attr( 'data-id', tid );
				}

				header.id = tid;
				header.title = $(this).text();
						
				if ('H1' == $(this).prop("tagName")) {
					header.level = 1;
				} else if ('H2' == $(this).prop("tagName")) {
					header.level = 2;
				} else if ('H3' == $(this).prop("tagName")) {
					header.level = 3;
				} else if ('H4' == $(this).prop("tagName")) {
					header.level = 4;
				} else if ('H5' == $(this).prop("tagName")) {
					header.level = 5;
				} else if ('H6' == $(this).prop("tagName")) {
					header.level = 6;
				}
					
				headers.push(header);
				
			});
				
			if ( headers.length == 0 ) {
				return headers;
			}
				
			// find max and min header level 
			let maxH = 1;
			let minH = 6;
				
			headers.forEach(function(header){
				if (header.level > maxH) {
					maxH = header.level
				}
					
				if (header.level < minH) {
					minH = header.level
				}
			});
				
			// move down all levels to have 1 lowest 
			if ( minH > 1 ) {
				headers.forEach(function(header, i){
					headers[i].level = header.level - minH + 1;
				});
			}
				
			// now we have levels started from 1 but maybe some levels do not exist
			// check level exist and decrease if not exist 
			let i = 1;
				
			while ( i < maxH ) {
				let levelExist = false;
				headers.forEach( function( header ) {
					if ( header.level == i ) {
						levelExist = true;
					}
				});
					
				if ( levelExist ) {
					// all right, level exist, go to the next 
					i++;
				} else {
					// no such levelm move all levels that more than current down and check once more
					headers.forEach( function( header, j ) {
						if ( header.level > i ) {
							headers[j].level = header.level - 1;
						}
					});
				}
				
				i++;
			}
				
			return headers;
		},
		
		// return html from headers object 
		getToCHTML: function ( headers, titleTag='h4' ) {
			let html;
				
			if ( articleToc.find('.eckb-article-toc__title').length ) {
					
				let title = articleToc.find('.eckb-article-toc__title').html();
				html = `
					<div class="eckb-article-toc__inner">
						<${titleTag} class="eckb-article-toc__title">${title}</${titleTag}>
						<nav class="eckb-article-toc-outline" role="navigation" aria-label="Article outline">
						<ul>
					`;
					
			} else {
					
				html = `
					<div class="eckb-article-toc__inner">
						<ul>
					`;
			}

			headers.forEach( function( header ) {
				let url = new URL( location.href );
				url.hash = header.id;
				url = url.toString();
				html += `<li class="eckb-article-toc__level eckb-article-toc__level-${header.level}"><a href="${url}" aria-label="Scrolls down the page to this heading" data-target="${header.id}">${header.title}</a></li>`;
			});
				
			html += `
						</ul>
						</nav>
					</div>
				`;
				
			return html;
		},
		
		// highlight needed element
		scrollSpy: function () {

			let currentTop = $(window).scrollTop();
			let currentBottom = $(window).scrollTop() + $(window).height();
			let highlighted = false;
			let $highlightedEl = false;
			let offset = articleToc.data( 'offset' );

			// scrolled to the end, activate last el
			if ( currentBottom == $(document).height() ) {
				highlighted = true;
				$highlightedEl = $('.eckb-article-toc__level a').last();
				$('.eckb-article-toc__level a').removeClass('active');
				$highlightedEl.addClass('active');
			// at least less than 1 px from the end
			} else {

				$('.eckb-article-toc__level a').each( function ( index ) {

					$(this).removeClass('active');

					if ( highlighted ) {
						return true;
					}

					let target = $(this).data('target');

					if ( !target || $('[data-id=' + target + ']').length == 0 ) {
						return true;
					}

					let $targetEl = $('[data-id=' + target + ']');
					let elTop = $targetEl.offset().top;
					let elBottom = $targetEl.offset().top + $targetEl.height();

					// check if we have last element
					if ( ( index + 1 ) == $('.eckb-article-toc__level a').length ) {
						elBottom = $targetEl.parent().offset().top + $targetEl.parent().height();
					} else {
						let nextTarget = $('.eckb-article-toc__level a').eq( index + 1 ).data('target');

						if ( nextTarget && $('[data-id=' + nextTarget + ']').length ) {
							elBottom = $('[data-id=' + nextTarget + ']').offset().top;
						}
					}

					elTop -= offset;
					elBottom -= offset + 1;

					let elOnScreen = false;

					if ( elTop < currentBottom && elTop > currentTop ) {
						// top corner inside the screen
						elOnScreen = true;
					} else if ( elBottom < currentBottom && elBottom > currentTop ) {
						// bottom corner inside the screen
						elOnScreen = true;
					} else if ( elTop < currentTop && elBottom > currentBottom ) {
						// screen inside the block
						elOnScreen = true;
					}

					if ( elOnScreen ) {
						$(this).addClass('active');
						highlighted = true;
						$highlightedEl = $(this);
					}

				});
			}

			// check if the highlighted element is visible 
			if ( ! $highlightedEl || $highlightedEl.length == 0 || ! highlighted ){
				return;
			}
			
			let highlightPosition = $highlightedEl.position().top;
			
			if ( highlightPosition < 0 || highlightPosition > $highlightedEl.closest('.eckb-article-toc__inner').height() ) {
				$highlightedEl.closest('.eckb-article-toc__inner').scrollTop( highlightPosition - $highlightedEl.closest('.eckb-article-toc__inner').find( '.eckb-article-toc__title' ).position().top );
			}
		},
		
	};

	setTimeout ( function() {

		if ( articleToc.length ) {
			TOC.init();
		}

		// Get the Article Content Body Position
		let articleContentBodyPosition = $('#eckb-article-content-body' ).position();
		let window_width = $(window).width();
		let default_mobile_breakpoint = 768 // This is the default set on first installation.
		let mobile_breakpoint = typeof $('#eckb-article-page-container-v2').data('mobile_breakpoint') == "undefined" ? default_mobile_breakpoint : $('#eckb-article-page-container-v2').data('mobile_breakpoint');

		//TODO: Dave - Change Sidebar position if TOC is in the Middle
		// If the setting is on, Offset the Sidebar to match the article Content
		if( $('.eckb-article-page--L-sidebar-to-content').length > 0 && window_width > mobile_breakpoint ){
			$('#eckb-article-page-container-v2').find( '#eckb-article-left-sidebar ').css( "margin-top" , articleContentBodyPosition.top+'px' );
		}

		if( $('.eckb-article-page--R-sidebar-to-content').length > 0 && window_width > mobile_breakpoint ){
			$('#eckb-article-page-container-v2').find( '#eckb-article-right-sidebar ').css( "margin-top" , articleContentBodyPosition.top+'px' );
		}

		if ( articleToc.length ) {
			mobile_TOC();
		}
	}, 500 );

	function mobile_TOC() {
		let window_width = $(window).width();
		let mobile_breakpoint = typeof $('#eckb-article-page-container-v2').data('mobile_breakpoint') == "undefined" ? 111 : $('#eckb-article-page-container-v2').data('mobile_breakpoint');

		if ( window_width > mobile_breakpoint ) {
			return;
		}

		if ( $('#eckb-article-content-header-v2 .eckb-article-toc').length ) {
			return;
		}

		if ( $('#eckb-article-left-sidebar .eckb-article-toc').length ) {
			$('#eckb-article-content-header-v2').append($('#eckb-article-left-sidebar .eckb-article-toc'));
			return;
		}

		if ( $('#eckb-article-right-sidebar .eckb-article-toc').length ) {
			$('#eckb-article-content-header-v2').append($('#eckb-article-right-sidebar .eckb-article-toc'));
		}
	}


	/********************************************************************
	 *                      Logged in users
	 ********************************************************************/
	$( document ).on( 'click', '#eckb-kb-create-demo-data', function( e ) {
		e.preventDefault();

		// Do nothing on Editor preview mode
		if ( $( this ).closest( '.epkb-editor-preview' ).length ) {
			return;
		}

		let postData = {
			action: 'epkb_create_kb_demo_data',
			epkb_kb_id: $( this ).data( 'id' ),
			_wpnonce_epkb_ajax_action: epkb_vars.nonce,
		};

		let parent_container = $( this ).closest( '.eckb-kb-no-content' ),
			confirmation_box = $( '.eckb-kb-no-content' ).find( '#epkb-created-kb-content' );

		$.ajax( {
			type: 'POST',
			dataType: 'json',
			data: postData,
			url: epkb_vars.ajaxurl,
			beforeSend: function( xhr ) {
				epkb_loading_Dialog( 'show', '', parent_container );
			}

		} ).done( function( response ) {
			response = ( response ? response : '' );
			if ( typeof response.message !== 'undefined' ) {
				confirmation_box.addClass( 'epkb-dialog-box-form--active' );
			}

		} ).fail( function( response, textStatus, error ) {
						confirmation_box.addClass( 'epkb-dialog-box-form--active' ).find( '.epkb-dbf__body' ).html( error );

		} ).always( function() {
			epkb_loading_Dialog( 'remove', '', parent_container );
		} );
	});

	function epkb_loading_Dialog( displayType, message, parent_container ){

		if ( displayType === 'show' ) {

			let output =
				'<div class="epkb-admin-dialog-box-loading">' +

				//<-- Header -->
				'<div class="epkb-admin-dbl__header">' +
				'<div class="epkb-admin-dbl-icon epkbfa epkbfa-hourglass-half"></div>'+
				(message ? '<div class="epkb-admin-text">' + message + '</div>' : '' ) +
				'</div>'+

				'</div>' +
				'<div class="epkb-admin-dialog-box-overlay"></div>';

			//Add message output at the end of Body Tag
			parent_container.append( output );

		} else if( displayType === 'remove' ) {

			// Remove loading dialogs.
			parent_container.find( '.epkb-admin-dialog-box-loading' ).remove();
			parent_container.find( '.epkb-admin-dialog-box-overlay' ).remove();
		}
	}

	$( document ).on( 'click', '.eckb-kb-no-content #epkb-created-kb-content .epkb-dbf__footer__accept__btn', function() {
		location.reload();
	} );

	/********************************************************************
	 *                      Sidebar v2
	 ********************************************************************/
	if( $( '#elay-sidebar-container-v2' ).length == 0 && $( '#epkb-sidebar-container-v2' ).length > 0 ){

		function epkb_toggle_category_icons( icon, icon_name ) {

			var icons_closed = [ 'ep_font_icon_plus', 'ep_font_icon_plus_box', 'ep_font_icon_right_arrow', 'ep_font_icon_arrow_carrot_right', 'ep_font_icon_arrow_carrot_right_circle', 'ep_font_icon_folder_add' ];
			var icons_opened = [ 'ep_font_icon_minus', 'ep_font_icon_minus_box', 'ep_font_icon_down_arrow', 'ep_font_icon_arrow_carrot_down', 'ep_font_icon_arrow_carrot_down_circle', 'ep_font_icon_folder_open' ];

			var index_closed = icons_closed.indexOf( icon_name );
			var index_opened = icons_opened.indexOf( icon_name );

			if ( index_closed >= 0 ) {
				icon.removeClass( icons_closed[index_closed] );
				icon.addClass( icons_opened[index_closed] );
			} else if ( index_opened >= 0 ) {
				icon.removeClass( icons_opened[index_opened] );
				icon.addClass( icons_closed[index_opened] );
			}
		}

		function epkb_open_and_highlight_selected_article_v2() {

			let $el = $( '#eckb-article-content' );

			if ( typeof $el.data( 'article-id' ) === 'undefined' ) {
				return;
			}

			// active article id
			let id = $el.data( 'article-id' );

			// true if we have article with multiple categories (locations) in the SBL; ignore old links
			if ( typeof $el.data('kb_article_seq_no') !== 'undefined' && $el.data('kb_article_seq_no') > 0 ) {
				let new_id = id + '_' + $el.data('kb_article_seq_no');
				id = $('#sidebar_link_' + new_id).length > 0 ? new_id : id;
			}

			// after refresh highlight the Article link that is now active
			$('.epkb-sidebar__cat__top-cat li').removeClass( 'active' );
			$('.epkb-category-level-1').removeClass( 'active' );
			$('.epkb-category-level-2-3').removeClass( 'active' );
			$('.epkb-sidebar__cat__top-cat__heading-container').removeClass( 'active' );
			let $sidebar_link = $('#sidebar_link_' + id);
			$sidebar_link.addClass('active');

			// open all subcategories 
			$sidebar_link.parents('.epkb-sub-sub-category, .epkb-articles').each(function(){

				let $button = $(this).parent().children('.epkb-category-level-2-3');
				if ( ! $button.length ) {
					return true;
				}

				if ( ! $button.hasClass('epkb-category-level-2-3') ) {
					return true;
				}

				$button.next().show();
				$button.next().next().show();

				let icon = $button.find('.epkb_sidebar_expand_category_icon');
				if ( icon.length > 0 ) {
					epkb_toggle_category_icons(icon, icon.attr('class').match(/\ep_font_icon_\S+/g)[0]);
				}
			});

			// open main accordeon 
			$sidebar_link.closest('.epkb-sidebar__cat__top-cat').parent().toggleClass( 'epkb-active-top-category' );
			$sidebar_link.closest('.epkb-sidebar__cat__top-cat').find( $( '.epkb-sidebar__cat__top-cat__body-container') ).show();

			let icon = $sidebar_link.closest('.epkb-sidebar__cat__top-cat').find('.epkb-sidebar__cat__top-cat__heading-container .epkb-sidebar__heading__inner span');
			if ( icon.length > 0 ) {
				epkb_toggle_category_icons(icon, icon.attr('class').match(/\ep_font_icon_\S+/g)[0]);
			}
		}

		var sidebarV2 = $('#epkb-sidebar-container-v2');

		// TOP-CATEGORIES -----------------------------------/
		// Show or hide article in sliding motion
		sidebarV2.on('click', '.epkb-top-class-collapse-on', function (e) {

			// prevent open categories when click on editor tabs 
			if ( typeof e.originalEvent !== 'undefined' && ( $(e.originalEvent.target).hasClass('epkb-editor-zone__tab--active') || $(e.originalEvent.target).hasClass('epkb-editor-zone__tab--parent') ) ) {
				return;
			}

			$( this ).parent().toggleClass( 'epkb-active-top-category' );
			$( this).parent().find( $( '.epkb-sidebar__cat__top-cat__body-container') ).slideToggle();
		});

		// Icon toggle - toggle between open icon and close icon
		sidebarV2.on('click', '.epkb-sidebar__cat__top-cat__heading-container', function (e) {

			// prevent open categories when click on editor tabs 
			if ( typeof e.originalEvent !== 'undefined' && ( $(e.originalEvent.target).hasClass('epkb-editor-zone__tab--active') || $(e.originalEvent.target).hasClass('epkb-editor-zone__tab--parent') ) ) {
				return;
			}

			var icon = $(this).find('.epkb-sidebar__heading__inner span');
			if ( icon.length > 0 ) {
				epkb_toggle_category_icons(icon, icon.attr('class').match(/\ep_font_icon_\S+/g)[0]);
			}
		});

		// SUB-CATEGORIES -----------------------------------/
		// Show or hide article in sliding motion
		sidebarV2.on('click', '.epkb-category-level-2-3', function () {

			// show lower level of categories and show articles in this category
			$( this ).next().slideToggle();
			$( this ).next().next().slideToggle();

		});
		// Icon toggle - toggle between open icon and close icon
		sidebarV2.on('click', '.epkb-category-level-2-3', function () {
			var icon = $(this).find('span');
			if ( icon.length > 0 ) {
				epkb_toggle_category_icons(icon, icon.attr('class').match(/\ep_font_icon_\S+/g)[0]);
			}
		});

		// SHOW ALL articles functionality
		sidebarV2.on('click', '.epkb-show-all-articles', function () {

			$( this ).toggleClass( 'active' );
			var parent = $( this ).parent( 'ul' );
			var article = parent.find( 'li');

			//If this has class "active" then change the text to Hide extra articles
			if ( $(this).hasClass( 'active') ) {

				//If Active
				$(this).find('.epkb-show-text').addClass('epkb-hide-elem');
				$(this).find('.epkb-hide-text').removeClass('epkb-hide-elem');
				$(this).attr( 'aria-expanded','true' );

			} else {
				//If not Active
				$(this).find('.epkb-show-text').removeClass('epkb-hide-elem');
				$(this).find('.epkb-hide-text').addClass('epkb-hide-elem');
				$(this).attr( 'aria-expanded','false' );
			}

			$( article ).each(function() {
				//If has class "hide" remove it and replace it with class "Visible"
				if ( $(this).hasClass( 'epkb-hide-elem') ) {
					$(this).removeClass('epkb-hide-elem');
					$(this).addClass('visible');
				} else if ( $(this).hasClass( 'visible')) {
					$(this).removeClass('visible');
					$(this).addClass('epkb-hide-elem');
				}
			});
		});

		epkb_open_and_highlight_selected_article_v2();
	}


	/********************************************************************
	 *                      Module Layout
	 ********************************************************************/

	// Classic Layout --------------------------------------------------------------/

	// Show main content of Category.
	$( document ).on( 'click', '#epkb-ml-classic-layout .epkb-ml-articles-show-more', function() {
		$( this ).parent().parent().find( '.epkb-category-section__body' ).slideToggle();
		$( this ).find( '.epkb-ml-articles-show-more__show-more__icon' ).toggleClass( 'epkbfa-plus epkbfa-minus' );
		const isExpanded = $( this ).find( '.epkb-ml-articles-show-more__show-more__icon' ).hasClass( 'epkbfa-minus' );
		if ( isExpanded ) {
			$( this ).parent().find( '.epkb-ml-article-count span' ).hide();
		} else {
			$( this ).parent().find( '.epkb-ml-article-count span' ).show();
		}
	} );

	// Toggle Level 2 Category Articles and Level 3 Categories
	$( document ).on( 'click', '#epkb-ml-classic-layout .epkb-ml-2-lvl-category-container', function( e ) {
		// to hide Articles, use a click only on the "minus" icon
		if ( $( this ).hasClass( 'epkb-ml-2-lvl-category--active' ) && ! $( e.target ).hasClass( 'epkb-ml-2-lvl-category__show-more__icon' ) ) return;
		$( this ).find( '.epkb-ml-2-lvl-article-list' ).slideToggle();
		$( this ).find( '.epkb-ml-3-lvl-categories' ).slideToggle();
		$( this ).find( '.epkb-ml-2-lvl-category__show-more__icon' ).toggleClass( 'epkbfa-plus epkbfa-minus' );
		$( this ).toggleClass( 'epkb-ml-2-lvl-category--active' );
	} );

	// Toggle Level 3 Category Articles and Level 4 Categories
	$( document ).on( 'click', '#epkb-ml-classic-layout .epkb-ml-3-lvl-category-container', function( e ) {
		// to hide Articles, use a click only on the "minus" icon
		if ( $( this ).hasClass( 'epkb-ml-3-lvl-category--active' ) && ! $( e.target ).hasClass( 'epkb-ml-3-lvl-category__show-more__icon' ) ) return;
		$( this ).find( '.epkb-ml-3-lvl-article-list' ).slideToggle();
		$( this ).find( '.epkb-ml-4-lvl-categories' ).slideToggle();
		$( this ).find( '.epkb-ml-3-lvl-category__show-more__icon' ).toggleClass( 'epkbfa-plus epkbfa-minus' );
		$( this ).toggleClass( 'epkb-ml-3-lvl-category--active' );
	} );

	// Toggle Level 4 Category Articles and Level 5 Categories
	$( document ).on( 'click', '#epkb-ml-classic-layout .epkb-ml-4-lvl-category-container', function( e ) {
		// to hide Articles, use a click only on the "minus" icon
		if ( $( this ).hasClass( 'epkb-ml-4-lvl-category--active' ) && ! $( e.target ).hasClass( 'epkb-ml-4-lvl-category__show-more__icon' ) ) return;
		$( this ).find( '.epkb-ml-4-lvl-article-list' ).slideToggle();
		$( this ).find( '.epkb-ml-5-lvl-categories' ).slideToggle();
		$( this ).find( '.epkb-ml-4-lvl-category__show-more__icon' ).toggleClass( 'epkbfa-plus epkbfa-minus' );
		$( this ).toggleClass( 'epkb-ml-4-lvl-category--active' );
	} );

	// Toggle Level 5 Category Articles
	$( document ).on( 'click', '#epkb-ml-classic-layout .epkb-ml-5-lvl-category-container', function( e ) {
		// to hide Articles, use a click only on the "minus" icon
		if ( $( this ).hasClass( 'epkb-ml-5-lvl-category--active' ) && ! $( e.target ).hasClass( 'epkb-ml-5-lvl-category__show-more__icon' ) ) return;
		$( this ).find( '.epkb-ml-5-lvl-article-list' ).slideToggle();
		$( this ).find( '.epkb-ml-5-lvl-category__show-more__icon' ).toggleClass( 'epkbfa-plus epkbfa-minus' );
		$( this ).toggleClass( 'epkb-ml-5-lvl-category--active' );
	} );

	// Drill Down Layout --------------------------------------------------------------/

	// Define frequently used selectors
	const $level_1_CategoriesContent    = $( '.epkb-ml-1-lvl-categories-content-container' );
	const $level_1_CategoryButton 		= $( '.epkb-ml-1-lvl__cat-container' );
	const $level_2_CategoryButton 		= $( '.epkb-ml-2-lvl__cat-container' );
	const $level_1_CategoryContent 		= $( '.epkb-ml-1-lvl__cat-content' );
	const $level_2_CategoryContent 		= $( '.epkb-ml-2-lvl__cat-content' );
	const $level_1_ButtonContainers 	= $( '.epkb-ml-1-lvl-categories-button-container' );
	const $level_2_ButtonContainers 	= $( '.epkb-ml-2-lvl-categories-button-container' );

	const $level_1_CategoryButtonActiveClass 	= 'epkb-ml-1-lvl__cat-container--active';
	const $level_2_CategoryButtonActiveClass 	= 'epkb-ml-2-lvl__cat-container--active';
	const $level_1_CategoryContentActiveClass 	= 'epkb-ml-1-lvl__cat-content--active';
	const $level_2_CategoryContentShowClass 	= 'epkb-ml-2-lvl__cat-content--show';
	const $level_1_ButtonContainersActiveClass 	= 'epkb-ml-1-lvl-categories-button-container--active';
	const $level_2_ButtonContainersActiveClass 	= 'epkb-ml-2-lvl-categories-button-container--active';
	const $level_2_ButtonContainersShowClass 	= 'epkb-ml-2-lvl-categories-button-container--show';


	// Level 1 Category Button Trigger
	$level_1_CategoryButton.on('click', function() {

		// Do nothing if current button is already active
		if ( $( this ).hasClass( $level_1_CategoryButtonActiveClass ) ) {
			return;
		}

		//$level_1_CategoriesContent.slideUp();

		// Remove all Classes
		$level_1_CategoryButton.removeClass( $level_1_CategoryButtonActiveClass );
		$level_1_CategoryContent.removeClass( $level_1_CategoryContentActiveClass );
		$level_2_ButtonContainers.removeClass( $level_2_ButtonContainersShowClass + ' ' + $level_2_ButtonContainersActiveClass );
		$level_2_CategoryContent.removeClass( $level_2_CategoryContentShowClass );
		$level_2_CategoryButton.removeClass( $level_2_CategoryButtonActiveClass );

		// Remove any active displays from other clicked triggers of other categories.
		$level_2_CategoryContent.css( 'display', 'none' );
		$level_1_CategoriesContent.css( 'display' , 'none' );

		$level_1_CategoriesContent.slideDown();

		// Add Active Class to Button
		$( this ).addClass( $level_1_CategoryButtonActiveClass );

		// Add Active Class to Buttons container
		$level_1_ButtonContainers.addClass( $level_1_ButtonContainersActiveClass );

		// Add Class show Content
		// $level_1_CategoriesContent.addClass( 'epkb-ml-1-lvl-categories-content-container--show' );

		// Get the ID of the clicked element
		const elementId = $( this ).attr('id');

		// Show Category Description / Articles
		$( '.epkb-ml-1-lvl__cat-content[data-cat-content="'+elementId+'"]' ).addClass( $level_1_CategoryContentActiveClass );

		// Show Sub Categories
		$( '.epkb-ml-2-lvl-categories-button-container[data-cat-content="'+elementId+'"]' ).addClass( $level_2_ButtonContainersShowClass );
	});

	// Level 1 Show more
	$( '#epkb-ml-drill-down-layout .epkb-ml-1-lvl__cat-content .epkb-ml-articles-show-more' ).on( 'click',function( e ) {

		e.preventDefault();
		$( this ).hide();
		$( this ).parent().find( '.epkb-list-column li' ).removeClass( 'epkb-ml-article-hide' );

	});

	// Level 2 Category Button Trigger
	$level_2_CategoryButton.on('click', function() {

		// Check if the button already has the active class
		if ($(this).hasClass('epkb-ml-2-lvl__cat-container--active')) {
			return; // Don't run the rest of the code if the button is already active
		}

		// Get the ID of the clicked element
		const elementId = $( this ).attr('id');

		// Add Class
		$( this ).addClass( $level_2_CategoryButtonActiveClass );
		$level_2_ButtonContainers.addClass( $level_2_ButtonContainersActiveClass );

		// Remove Class
		$level_1_CategoryContent.removeClass( $level_1_CategoryContentActiveClass );

		$level_2_CategoryContent.css( 'display', 'none' );

		// Show
		$( '.epkb-ml-2-lvl__cat-content[data-cat-content="'+elementId+'"]' ).slideDown();

		$( '.epkb-ml-2-lvl__cat-content[data-cat-content="'+elementId+'"]' ).addClass( $level_2_CategoryContentShowClass );
	});

	// Level 2 Show more
	$( '#epkb-ml-drill-down-layout .epkb-ml-2-lvl__cat-content .epkb-ml-articles-show-more' ).on( 'click',function( e ) {

		e.preventDefault();
		$( this ).hide();
		$( this ).parent().find( '.epkb-ml-articles-list li' ).removeClass( 'epkb-ml-article-hide' );

	});

	// Back Button of Level 1 Category Content
	$( document ).on('click', '.epkb-back-button', function() {

		// Return to the Top Categories view if Level 1 Content is currently shown
		if ( $( '.' + $level_1_CategoryContentActiveClass ).length ) {
			$level_1_CategoriesContent.hide();
			$level_1_ButtonContainers.removeClass( $level_1_ButtonContainersActiveClass );
			$level_1_CategoryButton.removeClass( $level_1_CategoryButtonActiveClass );
			return;
		}

		// Hide Content for a better transition effect
		$level_1_CategoriesContent.hide();

		// Remove Classes
		$( this ).removeClass( $level_2_CategoryButtonActiveClass );
		$level_2_CategoryButton.removeClass( $level_2_CategoryButtonActiveClass );

		$level_2_ButtonContainers.removeClass( $level_2_ButtonContainersActiveClass );
		$level_2_CategoryContent.removeClass( $level_2_CategoryContentShowClass );

		// Hide Content
		$level_2_CategoryContent.css( 'display', 'none' );

		// Show Category Description / Articles
		const level_1_selectedCategoryID = $( '.' + $level_1_CategoryButtonActiveClass ).attr( 'id' );
		$( '.epkb-ml-1-lvl__cat-content[data-cat-content="' + level_1_selectedCategoryID + '"]' ).addClass( $level_1_CategoryContentActiveClass );

		$level_1_CategoriesContent.slideDown( 500 );
	});

	// FAQs Module -----------------------------------------------------------------/
	$('#epkb-ml__module-faqs .epkb-ml-faqs__item__question').on('click', function(){

		var container = $(this).closest('.epkb-ml-faqs__item-container').eq(0);

		if (container.hasClass('epkb-ml-faqs__item-container--active')) {
			container.find('.epkb-ml-faqs__item__answer').stop().slideUp(400);
		} else {
			container.find('.epkb-ml-faqs__item__answer').stop().slideDown(400);
		}
		container.toggleClass('epkb-ml-faqs__item-container--active');
	});


	/********************************************************************
	 *                      Articles Views Counter
	 ********************************************************************/

	// check if we on article page
	if ( $('#eckb-article-content').length > 0 ) {
		epkb_send_article_view();
	}

	function epkb_send_article_view() {
		let article_id = $('#eckb-article-content').data('article-id');

		if ( typeof article_id == undefined || article_id == '' || typeof epkb_vars
			.article_views_counter_method == undefined || epkb_vars
			.article_views_counter_method == '' ) {
			return;
		}

		// check method for article views counter
		if ( epkb_vars
			.article_views_counter_method == 'delay' ) {
			setTimeout( function() {
				epkb_send_article_view_ajax( article_id );
			}, 5000 );
		}

		if ( epkb_vars
			.article_views_counter_method == 'scroll' ) {
			$(window).one( 'scroll', function() {
				epkb_send_article_view_ajax( article_id );
			});
		}
	}

	function epkb_send_article_view_ajax( article_id ) {
		// prevent double sent ajax request
		if ( typeof epkb_vars.article_view_sent !== 'undefined' ) {
			return;
		}

		let postData = {
			action: 'epkb_count_article_view',
			article_id: article_id,
			_wpnonce_epkb_ajax_action: epkb_vars.nonce,
		};

		// don't need response
		$.ajax({
			type: 'POST',
			dataType: 'json',
			data: postData,
			url: epkb_vars.ajaxurl,
			beforeSend: function( xhr ) {
				epkb_vars.article_view_sent = true;
			}
		});
	}
});
