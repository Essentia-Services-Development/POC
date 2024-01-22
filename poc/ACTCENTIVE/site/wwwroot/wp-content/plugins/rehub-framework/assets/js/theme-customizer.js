/**
 * Rehub Live Customizer
 */
( function( $ ) {

	wp.customize('rehub_body_block', function(value) {
		value.bind(function(newval) {
			if( newval == 1 ) {
				$('body').addClass('rh-boxed-container');
			}else{
				$('body').removeClass('rh-boxed-container');
			}
		});
	});
	wp.customize('rehub_content_shadow', function(value) {
		value.bind(function(newval) {
			if( newval == 1 ) {
				$('body').addClass('noinnerpadding');
			}else{
				$('body').removeClass('noinnerpadding');
			}
		});
	});
	wp.customize('rehub_logo', function(value) {
		var LogoSection = $('.logo-section').html();
		value.bind(function(newval) {
			if(newval){
				var LogoHTML = '<a href="/" class="logo_image"><img src="'+newval+'" /></a>';
				$('.logo-section .logo').html(LogoHTML);
			}else{
				$('.logo-section').html(LogoSection);
			}
		});
	});
	wp.customize('rehub_text_logo', function(value) {
		Logo = $('.logo').html();
		value.bind(function(newval) {
			if(newval){
				$('.logo-section .textlogo').text(newval);
			}else{
				$('.logo').html(Logo);
			}
		});
	});
	wp.customize('rehub_text_slogan', function(value) {
		Logo = $('.logo').html();
		value.bind(function(newval) {
			if(newval){
				$('.logo-section .sloganlogo').text(newval);
			}else{
				$('.logo').html(Logo);
			}
		});
	});
	wp.customize('rehub_sticky_nav', function(value) {
		var MainNav = $('#main_header').html();
		value.bind(function(newval) {
			if( newval == 1 ) {
				$('.main-nav').addClass('rh-stickme');
				$('.main-nav .rh-container').addClass('rh-flex-center-align logo_insticky_enabled');
				$(".rh-stickme").sticky({topSpacing:0, wrapperClassName: 'sticky-wrapper re-stickyheader', getWidthFrom: '.header_wrap', responsiveWidth : true});
			}else{
				$('#main_header').html(MainNav);
			}
		});
	});
	wp.customize('rehub_logo_sticky_url', function(value) {
		value.bind(function(newval) {
			if(newval){
				var LogoSticky = '<a href="/" class="logo_image_insticky"><img src="'+newval+'" /></a>';
				$('.main-nav .rh-container').prepend(LogoSticky);
			}else{
				$('.logo_image_insticky').replaceWith('');
			}
		});
	});
	wp.customize('header_logoline_style', function(value) {
		value.bind(function(newval) {
			if( newval == 1 ) {
				$('#main_header').removeClass('white_style');
				$('#main_header').addClass('dark_style');
			}else{
				$('#main_header').removeClass('dark_style');
				$('#main_header').addClass('white_style');
			}
		});
	});
	wp.customize('header_menuline_style', function(value) {
		value.bind(function(newval) {
			if( newval == 1 ) {
				$('div.main-nav').removeClass('white_style');
				$('div.main-nav').addClass('dark_style');
			}else{
				$('div.main-nav').removeClass('dark_style');
				$('div.main-nav').addClass('white_style');
			}
		});
	});
	wp.customize('header_topline_style', function(value) {
		value.bind(function(newval) {
			if( newval == 1 ) {
				$('.header_top_wrap').removeClass('white_style');
				$('.header_top_wrap').addClass('dark_style');
			}else{
				$('.header_top_wrap').removeClass('dark_style');
				$('.header_top_wrap').addClass('white_style');
			}
		});
	});

	//Update the site Google Fonts
	var FontConvertObj = function( newValue, selector ){
		var obj = JSON.parse(newValue);
		var GoogleFamilyArr = [obj.font,obj.weights,obj.subsets];
		var GoogleFamily = GoogleFamilyArr.join(':');
	
		if(obj.font){
			WebFont.load({google: {families: [GoogleFamily]}});
			selector.attr('style', 'font-family:'+ obj.font +' !important');
			selector.css('font-weight', obj.weights);
			selector.css('font-style', obj.styles);
		}else{
			selector.attr('style', 'font-family:inherit !important');
			selector.css('font-weight', 'inherit');
			selector.css('font-style', 'inherit');
		}
		
		return;
	}

	wp.customize( 'rehub_nav_font_group', function( value ) {
		value.bind( function( newVal ) {
			var items = $('.dl-menuwrapper li a, nav.top_menu > ul > li > a, #re_menu_near_logo li, #re_menu_near_logo li');
			FontConvertObj( newVal, items );
		});
	});
	wp.customize( 'rehub_headings_font_group', function( value ) {
		value.bind( function( newVal ) {
			var items = $('.rehub_feat_block div.offer_title, .rh_wrapper_video_playlist .rh_video_title_and_time .rh_video_title, .main_slider .flex-overlay h2, .related_articles ul li > a, h1, h2, h3, h4, h5, h6, .widget .title, .title h1, .title h5, .related_articles .related_title, #comments .title_comments, .commentlist .comment-author .fn, .commentlist .comment-author .fn a, .rate_bar_wrap .review-top .review-text span.review-header, .wpsm-numbox.wpsm-style6 span.num, .wpsm-numbox.wpsm-style5 span.num, .rehub-main-font, .logo .textlogo, .wp-block-quote.is-style-large, .comment-respond h3, .related_articles .related_title, .re_title_inmodal');
			FontConvertObj( newVal, items );
		});
	});
	wp.customize( 'rehub_btn_font_group', function( value ) {
		value.bind( function( newVal ) {
			var items = $('.priced_block .btn_offer_block, .rh-deal-compact-btn, .wpsm-button.rehub_main_btn, .woocommerce div.product p.price, .btn_more, input[type="submit"],  input[type="button"],  input[type="reset"], .vc_btn3, .re-compare-destin.wpsm-button, .rehub-btn-font, .vc_general.vc_btn3, .woocommerce a.woo_loop_btn, .woocommerce input.button.alt, .woocommerce a.add_to_cart_button, .woocommerce .single_add_to_cart_button, .woocommerce div.product form.cart .button, .woocommerce .checkout-button.button, #buddypress button.submit, .wcv-grid a.button, input.gmw-submit, #ws-plugin--s2member-profile-submit, #rtmedia_create_new_album, input[type="submit"].dokan-btn-theme, a.dokan-btn-theme:not(.dashicons), .dokan-btn-theme:not(.dashicons),  .woocommerce .single_add_to_cart_button, .woocommerce .woo-button-area .masked_coupon, .woocommerce .summary .price,  .wvm_plan, .wp-block-button .wp-block-button__link, .widget_merchant_list .buttons_col a');
			FontConvertObj( newVal, items );
		});
	});
	wp.customize( 'rehub_body_font_group', function( value ) {
		value.bind( function( newVal ) {
			var items = $('.sidebar, .rehub-body-font, body');
			FontConvertObj( newVal, items );
		});
	});
	wp.customize( 'rehub_headings_font_upper', function( value ) {
		value.bind( function( newval ) {
			var items = $('.rehub_feat_block div.offer_title, .rh_wrapper_video_playlist .rh_video_title_and_time .rh_video_title, .main_slider .flex-overlay h2, .related_articles ul li > a, h1, h2, h3, h4, h5, h6, .widget .title, .title h1, .title h5, .related_articles .related_title, #comments .title_comments, .commentlist .comment-author .fn, .commentlist .comment-author .fn a, .rate_bar_wrap .review-top .review-text span.review-header, .wpsm-numbox.wpsm-style6 span.num, .wpsm-numbox.wpsm-style5 span.num, .rehub-main-font, .logo .textlogo, .wp-block-quote.is-style-large, .comment-respond h3, .related_articles .related_title, .re_title_inmodal');
			if( newval == 1 ) {
				items.css('text-transform', 'uppercase');
			}else{
				items.css('text-transform', 'none');
			}
		});
	});
	wp.customize( 'rehub_btn_font_upper_dis', function( value ) {
		value.bind( function( newval ) {
			var items = $('.priced_block .btn_offer_block,.wpsm-button.rehub_main_btn,.priced_block .button,.woocommerce .single_add_to_cart_button,.woocommerce .woo-button-area .masked_coupon,.wc_vendors_dash_links a.button,.woocommerce a.button,.woocommerce-page a.button,.woocommerce button.button,.woocommerce-page button.button,.woocommerce input.button,.woocommerce-page input.button,.woocommerce a.woo_loop_btn,.woocommerce a.add_to_cart_button,.woocommerce-page a.add_to_cart_button,.wcv-grid a.button');
			if( newval == 1 ) {
				items.css('text-transform', 'none');
			}else{
				items.css('text-transform', 'uppercase');
			}
		});
	});
	wp.customize( 'body_font_size', function( value ) {
		value.bind( function( newval ) {
			var items = $('.post, body .post-readopt .post-inner, body .post-readopt:not(.main-side), body .post-readopt .post');
			let fontarray = newval.split(':');
			let fontlineheight = (fontarray[1]) ? fontarray[1] + 'px' : parseFloat(fontarray[0]) + 12 + 'px';
			if(newval){
				items.css('font-size', fontarray[0]+'px');
				items.css('line-height', fontlineheight);
			}else{
				var items = $('article, .post');
				var itemsbig = $('body .post-readopt .post-inner, body .post-readopt:not(.main-side), body .post-readopt .post');
				items.css('font-size', '16px');
				items.css('line-height', '28px');
				itemsbig.css('font-size', '18px');
				itemsbig.css('line-height', '1.85em');					
			}
		});
	});

	
} )( jQuery );