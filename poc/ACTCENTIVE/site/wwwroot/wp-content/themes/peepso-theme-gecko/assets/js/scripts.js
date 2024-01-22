(function ($) {
	// Header long navigation
	$(function() {
	  var $nav = $('.hm-header__menu');
	  var $btn = $('.hm-header__menu-toggle');
	  var $vlinks = $('.hm-header__menu-list');
	  var $hlinks = $('.hm-header__menu-more');

	  var numOfItems = 0;
	  var totalSpace = 0;
	  var breakWidths = [];

	  // Get initial state
	  $vlinks.children().outerWidth(function(i, w) {
	    totalSpace += w;
	    numOfItems += 1;
	    breakWidths.push(totalSpace);
	  });

	  var availableSpace, numOfVisibleItems, requiredSpace;

	  function check() {

	    // Get instant state
	    availableSpace = $vlinks.width() - 10;
	    numOfVisibleItems = $vlinks.children().length;
	    requiredSpace = breakWidths[numOfVisibleItems - 1];

	    // There is not enought space
	    if (requiredSpace > availableSpace) {
				$nav.removeClass('hm-header__menu--short');
	      $vlinks.children().last().prependTo($hlinks);
	      numOfVisibleItems -= 1;
	      check();
	      // There is more than enough space
	    } else if (availableSpace > breakWidths[numOfVisibleItems]) {
				$nav.addClass('hm-header__menu--short');
	      $hlinks.children().first().appendTo($vlinks);
	      numOfVisibleItems += 1;
	    }
	    // Update the button accordingly
	    $btn.attr("count", numOfItems - numOfVisibleItems);
	    if (numOfVisibleItems === numOfItems) {
	      $btn.hide();
	    } else $btn.show();
	  }

	  // Window listeners
	  $(window).resize(function() {
	    check();
	  });

	  $(document).mouseup(e => {
	    if (!$hlinks.is(e.target) && $hlinks.has(e.target).length === 0) {
	      $hlinks.addClass('hidden');
	      $btn.removeClass('open');
	    }
	  });

	  $btn.on('click', function(e) {
	    $(this).addClass('open');
	    $hlinks.toggleClass('hidden');
	  });

	  check();
	});

	// Initialize Gecko customizer preview if needed.
	$(function () {
		if (window.location !== window.parent.location) {
			if ('gecko-customizer-preview' === window.name) {
				initCustomizerPreview();
			}
		}
	});

	// Initialize sticky sidebar.
	$(function () {
		function initStickySidebar() {
			var $adminbar = $('#wpadminbar');
			var $header = $('.gc-js-header-wrapper').not('.gc-header__wrapper--static');
			var $footer = $('.gc-footer');
			var $bottomWidget = $('.gc-widgets--bottom');
			var offsetTop = ($adminbar.height() || 0) + ($header.height() || 0) + 29;
			var offsetBottom =
				(($footer.is(':visible') && $footer.height()) || 0) +
				($bottomWidget.outerHeight(true) || 0) +
				29;

			$('.sidebar--sticky .sidebar__inner').gc_stick_in_parent({
				offset_top: offsetTop,
				offset_bottom: offsetBottom
			});
		}

		// Initialize on the next cycle to wait for the `gc_stick_in_parent` to be available first.
		setTimeout(function () {
			if ('function' === typeof $.fn.gc_stick_in_parent) {
				initStickySidebar();
			}
		}, 1);
	});

	// Header Search Toggle
	$('.gc-js-header__search-toggle').click(function () {
		var header = $('.gc-js-header-wrapper');
		var header_class = 'gc-header__wrapper--search';
		var search = $('.gc-header__search');
		var search_box = $('.gc-header__search-box');
		var open = 'gc-header__search--open';

		$(header).toggleClass(header_class);
		$(search).toggleClass(open);
		$(search_box).fadeToggle(200, function () {
			if (search.hasClass(open)) {
				$('.gc-header__search-input').focus();
			}
		});
	});

	// Fixed header after scroll
	$(window).scroll(function () {
		var scroll = $(window).scrollTop();

		if (scroll >= 30) {
			$('.gc-js-header-wrapper').addClass('gc-header__wrapper--scroll');
		} else {
			$('.gc-js-header-wrapper').removeClass('gc-header__wrapper--scroll');
		}
	});

	// // Align footer to the bottom of the window
	// $(window).on('load', function () {
	// 	function checkScreenSize() {
	// 		var newWindowWidth = $(window).width(),
	// 			footerHeight = 0;
	//
	// 		if (newWindowWidth >= 980) {
	// 			var $footer = $('.gc-footer--sticky');
	// 			if ($footer.is(':visible')) {
	// 				footerHeight = $footer.height();
	// 			}
	// 		}
	//
	// 		$('body').css('padding-bottom', footerHeight);
	// 	}
	//
	// 	$(window).on('resize', checkScreenSize);
	// 	checkScreenSize();
	// });

	// Adapt body padding-top if Mobi Sticky Top widget is active
	// $(window).on('load', function () {
	// 	checkMobiScreenSize();
	//
	// 	function checkMobiScreenSize() {
	// 		var body = $('body');
	// 		var header = $('.gc-js-header-wrapper');
	// 		var wpmobileapp = ('is-wpmobileapp');
	// 		var wpadminBar = $('#wpadminbar').height() || 0;
	// 		var headerHeight = $(header).height() || 0;
  //     var stickyWidgetHeight = $('.gc-js-widgets-sticky').height() || 0;
  //     var headerCombinedHeight = headerHeight + stickyWidgetHeight + wpadminBar;
	//
	// 		if (body.hasClass(wpmobileapp)) {
	// 			$(body).css('--body-gap', headerCombinedHeight + 'px');
	// 		} else {
	// 			$(body).css('padding-top', headerCombinedHeight);
	// 		}
	// 	}
	//
	// 	setInterval(checkMobiScreenSize, 1000);
	// });

	// Check if element has fixed position
	function elementOrParentIsFixed(element) {
	    var $element = $(element);
	    var $checkElements = $element.add($element.parents());
	    var isFixed = false;
	    $checkElements.each(function(){
	        if ($(this).css("position") === "fixed") {
	            isFixed = true;
	            return false;
	        }
	    });
	    return isFixed;
	}

	$(window).on('load', function () {
		adaptBodyGap();

		function adaptBodyGap() {
			var body = $('body');
			var headerWrapper = $('.gc-js-header-wrapper');
			var header = $('.gc-js-header');
			var aboveHeaderBar = $('.gc-js-sticky-bar-above-header');
			var underHeaderBar = $('.gc-js-sticky-bar-under-header');
			var wpMobileAppBar = $('.gc-js-sticky-bar-mobile');

			var wpadminBarHeight = $('#wpadminbar').outerHeight() || 0;
			var headerWrapperHeight = $(headerWrapper).height() || 0;
			var aboveHeaderBarHeight = $(aboveHeaderBar).outerHeight() || 0;
			var underHeaderBarHeight = $(underHeaderBar).outerHeight() || 0;
			var wpMobileAppBarHeight = $(wpMobileAppBar).outerHeight() || 0;

			var isFixed = elementOrParentIsFixed(headerWrapper);

			if (isFixed) {
				$(body).css('padding-top', headerWrapperHeight + wpadminBarHeight + 'px');
			} else {
				$(aboveHeaderBar).css({'position': 'fixed', 'top': wpadminBarHeight, 'left': 0, 'right': 0, 'z-index': 500});
				$(underHeaderBar).css({'position': 'fixed', 'top': wpadminBarHeight + aboveHeaderBarHeight, 'left': 0, 'right': 0, 'z-index': 450});
				$(wpMobileAppBar).css({'position': 'fixed', 'top': wpadminBarHeight + aboveHeaderBarHeight + underHeaderBarHeight, 'left': 0, 'right': 0, 'z-index': 500});
				$(headerWrapper).css('position', 'static');
				$(body).css('padding-top', aboveHeaderBarHeight + underHeaderBarHeight + wpadminBarHeight + wpMobileAppBarHeight + 'px');
			}
		}

		setInterval(adaptBodyGap, 500);
	});


	// Modal
	$('.gc-js-header-menu-open').on('click', function (e) {
		e.preventDefault();
		e.stopPropagation();

		$('html').addClass('gc-is-header-sidebar-open');
		$('.gc-js-header-sidebar').addClass('gc-header__sidebar--open');
	});

	$('.gc-js-header-menu-close').on('click', function (e) {
		e.preventDefault();
		e.stopPropagation();

		$('html').removeClass('gc-is-header-sidebar-open');
		$('.gc-js-header-sidebar').removeClass('gc-header__sidebar--open');
	});

	function check_cart() {
		var woocart_toggle = $('.js-header-cart-toggle');
		var woocart_wrapper = $(woocart_toggle).parent();
		var woocart = $('.widget_shopping_cart_content p');

		if (woocart.hasClass('woocommerce-mini-cart__empty-message')) {
			$(woocart_toggle).addClass('empty');
			$(woocart_wrapper).addClass('cart-empty');
		} else {
			$(woocart_toggle).removeClass('empty');
			$(woocart_wrapper).removeClass('cart-empty');
		}
	}
	setInterval(check_cart, 1000);

	// HEADER CART TOGGLE >>>
	$('.js-header-cart-toggle').click(function (e) {
		var $toggle = $(this);
		var $document = $(document.body);
		var $widget = $toggle.parent().find('.gc-header__cart');
		var is_opened = $toggle.hasClass('open');
		var event_name = 'click.gc-header-cart';

		// Hide widget.
		if (is_opened) {
			$toggle.removeClass('open');
			$widget.stop().fadeOut();
			$document.off(event_name);
		}

		// Show widget.
		else {
			$toggle.addClass('open');
			$widget.stop().fadeIn();

			// Setup autohide trigger.
			setTimeout(function () {
				$document.off(event_name);
				$document.on(event_name, function (e) {
					// Skip if click event occurs inside the widget.
					if ($(e.target).closest($widget).length) {
						return;
					}

					// Hide widget.
					$toggle.removeClass('open');
					$widget.stop().fadeOut();
					$document.off(event_name);
				});
			}, 1);
		}
	});
	// <<< END

	// Mobile menu
	$('.gc-header__sidebar-menu .menu-item-has-children').on('click', function (e) {
		var xPosition = e.pageX;
		var width = $(this).parents('.gc-header__sidebar').width();
		var area = width - 50;
		var expandIcon = 50;

		if (xPosition > area) {
			$(this).find('> .sub-menu').slideToggle();
			$(this).toggleClass('open');

			e.stopPropagation();
		}

		// for RTL site
		if($(".rtl")[0]){
			if (xPosition < area) {
				$(this).find('> .sub-menu').slideToggle();
				$(this).toggleClass('open');

				e.stopPropagation();
			}
		}
	});

	// Scroll to top
	$(window).scroll(function () {
		var scroll = $(window).scrollTop();
		var button = '.js-scroll-top';

		if (scroll >= 100) {
			$(button).fadeIn();
		} else {
			$(button).fadeOut();
		}
	});

	// Toggle header widget
	$('.js-header-search-toggle').click(function () {
		var toggle = $(this);
		var headerWidget = '.header__search-bar';

		$(toggle).parent().find(headerWidget).toggle();
	});

	// Gecko grid arrangement using Macy.js.
	$(function () {
		var width = $("body").width();
		var macyColumnsNumber = typeof blogGridColumns !== typeof undefined ? blogGridColumns : 2;
		
		function initMasonry($container) {
			
			var instance = Macy({
				container: $container,
				trueOrder: true,
				margin: { x: 15, y: 15 },
				columns: macyColumnsNumber
			});

			function _rearrange() {
				var winWidth = $(window).width();
				if (winWidth < 980) {
					instance.remove();
				} else {
					instance.reInit();
				}
			}

			// Debounce grid rearrangement.
			var timer = null;
			function rearrange() {
				clearTimeout(timer);
				timer = setTimeout(_rearrange, 500);
			}

			$(window).on('load resize scroll', rearrange);
		}

		// TEMPORARY SOLUTION TO AVOID "JUMP" EFFECT ON MOBILE
		if (width > 980) {
			// Post grid.
			var $gridPosts = document.querySelector('#gecko-blog .content__posts');
			if ($gridPosts) {
				initMasonry($gridPosts);
			}

			// Archive grid.
			var $gridCategory = document.querySelector('.archive.date .content--grid');
			if ($gridCategory) {
				initMasonry($gridCategory);
			}

			// Category grid.
			var $gridCategory = document.querySelector('.category .content--grid');
			if ($gridCategory) {
				initMasonry($gridCategory);
			}
		}
	});

	// Gecko user theme selector.
	$(function () {
		$('select[name=peepso_gecko_user_theme]').on('change', function () {
			var $select = $(this),
				$loading = $select
					.closest('.ps-js-profile-preferences-option')
					.find('.ps-js-loading img'),
				counter = 0,
				timer;

			if (!$loading.length) {
				return;
			}

			// Reload when loading indicator is finally hidden, with max delay 30s.
			timer = setInterval(function () {
				if ($loading.is(':hidden') || ++counter >= 60) {
					clearInterval(timer);
					window.location.reload();
				}
			}, 500);
		});
	});

	/**
	 * Initialize Gecko customizer preview.
	 */
	function initCustomizerPreview() {
		// Remove adminbar on the customizer preview.
		$('#wpadminbar').remove();
		$('body').removeClass('admin-bar');

		var loadedFonts = [];

		// Function to handle received config update.
		function receiveCustomizerUpdate(e) {
			let root = document.body;
			var data = e.data || {};
			var type = data.type;
			var key = data.key;
			var value = data.value;

			if ('css' === type) {
				if ('--GC-FONT-FAMILY' === key) {
					// Load Google Fonts if necessary.
					if (loadedFonts.indexOf(value) === -1) {
						loadedFonts.push(value);

						var link = document.createElement('link');
						link.setAttribute('rel', 'stylesheet');
						link.setAttribute('type', 'text/css');
						link.setAttribute(
							'href',
							'https://fonts.googleapis.com/css2?family=' +
								value.replace(/\s/g, '+') +
								':wght@400;700&display=swap'
						);
						document.getElementsByTagName('head')[0].appendChild(link);
					}

					value = "'" + value + "'";
				}

				root.style.setProperty(key, value || false);
			}
		}

		window.addEventListener('message', receiveCustomizerUpdate, false);
	}

	// Switch Register and Login form on Landing Page mobile view
	$(function () {
		var landingRegisterForm = $('.landing .ps-page--register-main');
		var linkRegister = $('.psf-login__link--register');
		var registerLinkWrapper = $('.landing .ps-page--register-main .ps-form__row--submit');
		var landingRowLogin = $('.landing__grid .landing__row:last-child');

		if ($(landingRegisterForm).is(':visible') || $(landingRegisterForm).is(':hidden')) {
			$(linkRegister).attr('href', '#');
			$(linkRegister).addClass('psf-login__link--register-mobile');

			$(registerLinkWrapper).append(
				"<a href='#' class='psf-login__link psf-login__link--login-mobile'>Login</>"
			);
		}

		$('.psf-login__link--register-mobile').click(function () {
			$(landingRegisterForm).show();
			$(landingRowLogin).hide();
		});

		$('.psf-login__link--login-mobile').click(function () {
			$(landingRegisterForm).hide();
			$(landingRowLogin).show();
		});

		// #5392 Automatically show PeepSo registration form if necessary.
		if ($('.landing').hasClass('landing--peepso-register')) {
			// #5426 Check the availability of the verify password field to make sure.
			if (document.querySelector('input[name=password2]')) {
				$(landingRegisterForm).show();
				$(landingRowLogin).hide();
			}
		}
	});

	/**
	 * Adaptive header sub-menu
	 */
	$(function () {
		var header = $('.gc-js-header-wrapper');
		var headerWidth = header.width();

		// Added level attribute to each sub menu
		var level = 1;
		var assign_attr = function (list) {
			list.each(function () {
				$(this).attr('data-level','level-' + level);
				level++;
				assign_attr($(this).find('> li > ul'));
				level--;
			});
		};
		assign_attr($('.gc-header__menu > ul > li > ul'));

		$('.gc-header__menu > ul > li').each(function(){
			// Count level in each menu item
			var subMenu_level = {};
			$(this).find('.sub-menu').each(function() {
				var levelData = $(this).data("level");

				if (subMenu_level.hasOwnProperty(levelData) === false) {
					subMenu_level[levelData] = 1;
				} else {
					subMenu_level[levelData]++;
				}
			});

			var levelLength = Object.keys(subMenu_level).length;

			$(this).find('> ul > li').each(function() {
				// Calculate the sub menu total width of each menu item
				var subMenu_width = $(this).find('.sub-menu').width(),
						total_subMenu_width = parseInt(subMenu_width) * parseInt(levelLength),
						menu_leftPosition = $(this).parents('.menu-item').offset().left,
						subMenu_totalWidth = parseInt(total_subMenu_width) + parseInt(menu_leftPosition);

				if(!$(body).hasClass('rtl')) {
					// Compare the sub menu total width with the header menu width
					if (subMenu_totalWidth > headerWidth) {
						$(this).parents('.menu-item').addClass('gc-header__menu-item--reverse');
					}
				}
			});
		});
	});

	/**
	 * Add max-width & max-height to <iframe> elements, depending on their width & height props.
	 */
	function geckoResponsiveEmbeds() {
		var proportion, parentWidth;

		// Loop iframe elements.
		document.querySelectorAll('iframe').forEach(function (iframe) {
			// Only continue if the iframe has a width & height defined.
			if (iframe.width && iframe.height) {
				// Calculate the proportion/ratio based on the width & height.
				proportion = parseFloat(iframe.width) / parseFloat(iframe.height);
				// Get the parent element's width.
				parentWidth = parseFloat(
					window.getComputedStyle(iframe.parentElement, null).width.replace('px', '')
				);
				// Set the max-width & height.
				iframe.style.maxWidth = '100%';
				iframe.style.maxHeight = Math.round(parentWidth / proportion).toString() + 'px';
			}
		});
	}

	// Run on initial load.
	geckoResponsiveEmbeds();

	// Run on resize.
	window.onresize = geckoResponsiveEmbeds;

	// Add gecko menu item class to WOO wallet
	$('.gc-header__menu .woo-wallet-menu-contents').parent().addClass('gc-header__menu-item');

	/**
	 * Sticky PeepSo navigation bar
	 */
	$(function() {
		var $header = $('.gc-header__wrapper');
		var	$adminBar = $('#wpadminbar');
		var	$content_width, $coverProfile_height;

		// Adjust main content width and cover profile height
		function adjustSize() {
			$content_width = $('.peepso').width();

			if ($('.ps-focus')[0]) {
				$coverProfile_height = $('.ps-focus').height();
			} else {
				$coverProfile_height = 0;
			}
		}

		$(window).on('resize', function() {
			adjustSize();

			$('.gc-navbar--sticky').css({
				'--content-width': $content_width + 'px',
				'--cover-height': $coverProfile_height + 'px'
			});
		}).resize();

		$(window).on('scroll', function () {
			var scroll = $(window).scrollTop();

			if (scroll >= ($coverProfile_height + 300)) {
				$('.gc-navbar--sticky').addClass('gc-navbar--scroll');
			} else {
				$('.gc-navbar--sticky').removeClass('gc-navbar--scroll');
			}
		});

		// for WPMA: PeepSo Theme: Gecko + Native Interface
		$(document.body).on('scroll mousewheel', function(e) {
			var scroll = e.deltaY;

			if (scroll < 0) {
				$('.gc-navbar--sticky').addClass('gc-navbar--scroll');
			} else {
				$('.gc-navbar--sticky').removeClass('gc-navbar--scroll');
			}
		});

		// Check header height and existence
		var isFixed = elementOrParentIsFixed($header);
		var aboveHeaderBar = $('.gc-js-sticky-bar-above-header');
		var underHeaderBar = $('.gc-js-sticky-bar-under-header');
		var aboveHeaderBarHeight = $(aboveHeaderBar).outerHeight() || 0;
		var underHeaderBarHeight = $(underHeaderBar).outerHeight() || 0;

		if ($header[0]) {
			if (isFixed) {
				$header = $header.height();
			} else {
				$header = aboveHeaderBarHeight + underHeaderBarHeight;
			}
		} else {
			$header = aboveHeaderBarHeight + underHeaderBarHeight;
		}

		// Check admin bar height and existence
		if ($adminBar[0]) {
			if (($adminBar.css('position') == 'fixed') || ($adminBar.css('position') == 'sticky')) {
				$adminBar = 32;
			} else {
				$adminBar = 0;
			}
		} else {
			$adminBar = 0;
		}

		$('.gc-navbar--sticky').css({
			'--header-height' : $header + 'px',
			'--admin-height' : $adminBar + 'px'
		});

		// Set navbar position based on main content position
		if ($('.main').hasClass('main--left')) {
			$('.main').parents('body').find('.gc-navbar--sticky').addClass('gc-navbar--right');
		} else if ($('.main').hasClass('main--right')){
			$('.main').parents('body').find('.gc-navbar--sticky').addClass('gc-navbar--left');
		}
	});

	/**
	 * Regular buttons
	 */
	$(function() {
		// Define button elements that doesn't have a class
		var $buttons = $('button:not([class]), button[type="button"]:not([class]), input[type="submit"]:not([class]), input[type="button"]:not([class])');

		// Add gc-btn class to it
		$buttons.addClass('gc-btn');
	});

	/**
	 * WooCommerce "Hide if cart is empty" cart widget in header
	 */
	// Hide Cart dropdown by default to prevent its opens every time the page reload
	$(function() {
		$('.hide_cart_widget_if_empty').parent().hide();
	});
	// Hide if cart is empty
	function check_cart_if_empty() {
		var wocart_wrapper = $('.gc-header__cart-wrapper');
		var wocart_wrapper_empty = $('.gc-header__cart-wrapper.cart-empty');

		if($(wocart_wrapper_empty).find('.hide_cart_widget_if_empty').length !== 0) {
			$(wocart_wrapper).hide();
		} else {
			$(wocart_wrapper).show();
		}
	}
	setInterval(check_cart_if_empty, 1000);

})(jQuery);
