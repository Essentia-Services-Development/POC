jQuery(document).ready(function($) {
	'use strict';	
	
		var widthwindow = $(window).width();
		if (widthwindow < 1024 && $('.float-panel-woo-info').length > 0){
			var floatpanellinks = $('.float-panel-woo-info').clone(true).addClass('wpsm_pretty_colored float-panel-top-links pt10 pl15 pr15 pb10 rh-float-panel');
			$('body').prepend(floatpanellinks);
			$('.float-panel-top-links').removeClass('rh-line-left ml15');
			$('.float-panel-top-links .float-panel-woo-links').removeClass('font80').addClass('font90 smart-scroll-desktop');
			if($('.re-stickyheader').length > 0){
				var stickyheight = $('.re-stickyheader').outerHeight();
				floatpanellinks.css("top", stickyheight);
			}
		}
	
		var lastId = '';
		var topMenu = $(".float-panel-woo-links");
		var topTabs = $(".float-panel-woo-tabs");
		var topMenuHeight = $("#float-panel-woo-area").outerHeight()+15;
		var sidecontents = $(".sidecontents");
		var imagedotcontoller = $("#rh-product-images-dots");
	
		if(topMenu.length > 0){
	
			// All list items
			var menuItems = topMenu.find("a");
			// Anchors corresponding to menu items
			var scrollItems = menuItems.map(function(){
				var elem = $(this).attr("href");
				  var item = $(elem);
			  if (item.length) { return item; }
			});
	
			// Bind click handler to menu items
			// so we can get a fancy scroll animation
			menuItems.click(function(e){
				var href = $(this).attr("href"),
				  offsetTop = href === "#" ? 0 : $(href).offset().top-topMenuHeight+1;
				$('html, body').stop().animate({ 
					  scrollTop: offsetTop
				}, 500);
				e.preventDefault();
			});
	
			$('#contents-section-woo-area .contents-woo-area a').click(function(e){
				var href = $(this).attr("href"),
				  offsetTop = href === "#" ? 0 : $(href).offset().top-topMenuHeight+1;
				$('html, body').stop().animate({ 
					  scrollTop: offsetTop
				}, 500);
				e.preventDefault();
			});
	
			$(window).on("scroll", $.throttle( 250, function(){
				// Get container scroll position
				var fromTop = $(this).scrollTop()+topMenuHeight;
	
				// Get id of current scroll item
				var cur = scrollItems.map(function(){
					 if ($(this).offset().top < fromTop)
					   return this;
				});
				// Get the id of the current element
				cur = cur[cur.length-1];
				var id = cur && cur.length ? cur[0].id : "";
	
				if (lastId !== id) {
					   lastId = id;
					   // Set/remove current class
					   var currentmenuItem = menuItems.filter("[href='#"+id+"']");
					   var currentmenuIteml = currentmenuItem.offset();
					   menuItems.parent().removeClass("current");
					   currentmenuItem.parent().addClass("current");
					   if (typeof currentmenuIteml !== "undefined"){
						 $('.float-panel-top-links .float-panel-woo-links').stop().animate({scrollLeft: currentmenuIteml.left - 20}, 500);
					   }
				}                   
			}));
		}
	
		if(topTabs.length > 0){
			var tabItems = topTabs.find("a");
	
			tabItems.click(function(e){
				e.preventDefault();
				var href = $(this).attr("href"), offsetTop = href === "#" ? 0 : $('.woocommerce-tabs').offset().top-topMenuHeight+1;
				$('.tabs a[href="'+href+'"]').trigger('click');
				$('html, body').stop().animate({ 
					  scrollTop: offsetTop
				}, 500);
				return false;
			});
	
		}
	
		if(sidecontents.length > 0){ 
			var sidecontentsItems = sidecontents.find('a');
			var sidelastId = '';
	
			var sidescrollItems = sidecontentsItems.map(function(){
				var elem = jQuery(this).attr('href');
				var item = jQuery(elem);
			  if (item.length) { return item; }
			});
			sidecontentsItems.click(function(e){
				var href = $(this).attr("href"),
				  offsetTop = href === "#" ? 0 : $(href).offset().top-topMenuHeight-20;
				$('html, body').stop().animate({ 
					  scrollTop: offsetTop
				}, 500);
				e.preventDefault();
			});
			jQuery(window).on("scroll", jQuery.throttle( 350, function(){
				var sidefromTop = jQuery(this).scrollTop()+55;
				var sidecur = sidescrollItems.map(function(){
					if ((jQuery(this).offset().top - 55) < sidefromTop)
					return this;
				});
				sidecur = sidecur[sidecur.length-1];
				var id = sidecur && sidecur.length ? sidecur[0].id : '';
	
				if (sidelastId !== id) {
					sidelastId = id;
					var currentmenuItem = sidecontentsItems.filter('[href=\"#'+id+'\"]');
					sidecontentsItems.addClass('greycolor').removeClass('fontbold').parent().removeClass('current');
					currentmenuItem.removeClass('greycolor').addClass('fontbold').parent().addClass('current');
				}                   
			}));
		}
	
		if(imagedotcontoller.length > 0){ 
			var heightpost = $('#photo_stack_main_img').offset().top;
			imagedotcontoller.css('top', heightpost + 20);
			var imdotItems = imagedotcontoller.find('.rhdot');
			var imdotlastId = '';
	
			var imdotscrollItems = imdotItems.map(function(){
				var elem = jQuery(this).data('scrollto');
				var item = jQuery(elem);
			  if (item.length) { return item; }
			});
			imdotItems.on('click', function(e){
				jQuery(this).addClass('current');
			});
			jQuery(window).on("scroll", jQuery.throttle( 350, function(){
				var imdotfromTop = jQuery(this).scrollTop()+55;
				var imdotcur = imdotscrollItems.map(function(){
					if ((jQuery(this).offset().top - 55) < imdotfromTop)
					return this;
				});
				imdotcur = imdotcur[imdotcur.length-1];
				var id = imdotcur && imdotcur.length ? imdotcur[0].id : '';
	
				if (imdotlastId !== id) {
					imdotlastId = id;
					var currentimdotItem = imdotItems.filter('[data-scrollto=\"#'+id+'\"]');
					
					imdotItems.removeClass('current');
					currentimdotItem.addClass('current');
				}                   
			}));
		}
	
		var lastScrollTop = 0;
		let rhVideoScrollPanel = function(){
			if($('.rh-video-scroll-copy').length && !$('.rh-video-scroll-copy').hasClass('active') && $('.rh-video-scroll-cont .rh_lazy_load_video').hasClass('video-container')){
				let videocopy = $('.rh-video-scroll-copy').offset();
				let videocopywidth = $('.rh-video-scroll-copy').width();
				let videoorigheight = $('.rh-video-scroll-cont').outerHeight();
				$('.rh-video-scroll-wrap').height(videoorigheight);
				$('.rh-video-scroll-cont').css( "top", videocopy.top - $(document).scrollTop());
				$('.rh-video-scroll-cont').css( "left", videocopy.left);
				$('.rh-video-scroll-cont').css( "position", "fixed");
				$('.rh-video-scroll-cont').css( "z-index", 9999999);
				$('.rh-video-scroll-cont').css( "width", videocopywidth);
				$('.rh-video-scroll-cont').css( "height", 200);
				$('.rh-video-scroll-copy').addClass('active').css("height", 200);
			}		
		}
		$(window).on("scroll", $.throttle( 250,function() {
			var st = $(this).scrollTop();
			if($('#contents-section-woo-area').length > 0){
				var theight = $('#contents-section-woo-area').offset();
				if (st>theight.top) {
					$('#float-panel-woo-area, .float-panel-woo-info').addClass('floating');
					$('.float_p_trigger').addClass('floatactive');
					if($('.float_trigger_clr_change').length){
						$('.float_trigger_clr_change').addClass('whitebg rh-shadow3').removeClass('darkbgl woo_white_text_layout whitecolorinner');
					}
					imagedotcontoller.addClass('rhhidden');
					rhVideoScrollPanel();
	
				}
				else {
					$('#float-panel-woo-area, .float-panel-woo-info').removeClass('floating');
					$('.float_p_trigger').removeClass('floatactive');
					if($('.float_trigger_clr_change').length){
						$('.float_trigger_clr_change').removeClass('whitebg rh-shadow3').addClass('darkbgl woo_white_text_layout whitecolorinner');
					}
					if($('.rh-video-scroll-copy').length && $('.rh-video-scroll-copy').hasClass('active') && $('.rh-video-scroll-cont .rh_lazy_load_video').hasClass('video-container')){
						//$('.rh-video-scroll-wrap').height(videoorigheight);
						$('.rh-video-scroll-cont').removeAttr('style');
						$('.rh-video-scroll-copy').removeClass('active').removeAttr('style');
						$('.rh-video-scroll-wrap').removeAttr('style');
					}
					imagedotcontoller.removeClass('rhhidden');				
				}
			   if (st > lastScrollTop){
					   $('#float-panel-woo-area, .float-panel-woo-info').addClass('scrollingDown').removeClass('scrollingUp');
			   } else {
					  $('#float-panel-woo-area, .float-panel-woo-info').addClass('scrollingUp').removeClass('scrollingDown');
			   }
			   lastScrollTop = st;	
			}	
		}));
	
		$(window).on("resize", $.throttle( 550,function() {
			if($('.rh-video-scroll-copy').length && $('.rh-video-scroll-cont .rh_lazy_load_video').hasClass('video-container')){
				if($('.rh-video-scroll-copy').hasClass('active')){
					let videocopy = $('.rh-video-scroll-copy').offset();
					let videocopywidth = $('.rh-video-scroll-copy').width();
					$('.rh-video-scroll-cont').css( "top", videocopy.top - $(document).scrollTop());
					$('.rh-video-scroll-cont').css( "left", videocopy.left);
					$('.rh-video-scroll-cont').css( "width", videocopywidth);								
				}else{
					let videoorigheight = $('.rh-video-scroll-cont').outerHeight();
					$('.rh-video-scroll-wrap').height(videoorigheight);
				}
	
			}
		}));  
			   
	});