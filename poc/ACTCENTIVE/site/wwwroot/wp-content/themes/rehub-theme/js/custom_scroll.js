jQuery(document).ready(function($) {
'use strict';	
	$(window).on("scroll", jQuery.throttle( 250, function() {
		if($('.sidebar').length > 0){
			var sheight = $('.sidebar').height();
			var theight = $('.sidebar').offset();
			var swidth = $('.sidebar').width();
			var tthis = $('.sidebar .stickyscroll_widget').first().height();
		}else{
			var sheight = $('.elementor-widget-sidebar').height();
			var theight = $('.elementor-widget-sidebar').offset();
			var swidth = $('.elementor-widget-sidebar').width();
			var tthis = $('.elementor-widget-sidebar .stickyscroll_widget').first().height();			
		}
		var hbot = $('.rh-content-wrap').offset();
		var hfoot = $('.rh-content-wrap').height();

		if ($(this).scrollTop()>sheight + theight.top) {
			$('.sidebar .stickyscroll_widget').first().css({'position':'fixed','top':'90px', 'width': swidth}).addClass('scrollsticky');
			if($('.sidebar .stickyscroll_widget .autocontents').length > 0){
				$('.sidebar .stickyscroll_widget .autocontents').css({'max-height':'620px','overflow-y':'auto'});
			}
		}
		else {
			$('.sidebar .stickyscroll_widget').first().css({'position':'static', 'width':'auto','top':'0'}).removeClass('scrollsticky');
			if($('.sidebar .stickyscroll_widget .autocontents').length > 0){
				$('.sidebar .stickyscroll_widget .autocontents').css({'max-height':'2000px'});
			}
		}
		if ($(this).scrollTop()>hfoot + hbot.top - tthis ) $('.sidebar .stickyscroll_widget').first().css({'position':'static', 'width':'auto','top':'0'}).removeClass('scrollsticky');
	}));
});