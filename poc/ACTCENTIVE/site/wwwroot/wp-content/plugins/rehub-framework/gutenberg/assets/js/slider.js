document.addEventListener('DOMContentLoaded', function(){ 
	var sliders = document.getElementsByClassName('js-hook__slider');
	for (var item of sliders) {
		var slider = window.rehubSlider({
			slider: item
		});
	}
});
window.rehubSlider = function( options ) {
	options = extend({
		slider: '',
	}, options || {} );
	var container = options.slider.querySelector( '.rh-slider__inner' ),
		controlsContainer = options.slider.querySelector( '.rh-slider-controls' ),
		prevButton = controlsContainer.children[0],
        nextButton = controlsContainer.children[1],
		thumbsContainer = options.slider.querySelector( '.rh-slider-thumbs__row' ),
		slideItems = container.children,
		slideCount = slideItems ?  slideItems.length : 0,
		index = getStartIndex( getOption('startIndex') ),
		indexCached = index,
		slideBy = 1, 
		panStart = false,
		initPosition = {},
        lastPosition = {},
		swipeAngle = 15,
		moveDirectionExpected = '?';

	var controlsEvents = {
        'click': onControlsClick,
	};
	var thumbsEvents = {
        'click': onThumbClick,
	};
	var touchEvents = {
        'touchstart': onPanStart,
        'touchmove': onPanMove,
        'touchend': onPanEnd,
	};

	addEvents(controlsContainer, controlsEvents);
	addEvents(thumbsContainer, thumbsEvents);
	addEvents(container, touchEvents);
	setSlidePositions();

	function setSlidePositions () {
		for (var i = 0; i < slideItems.length; i++) {
			let item = slideItems[i];
			if (i === 0 ) { 
				item.style.left = '0%'; 
			} else {
				item.style.left = '100%';
			}
		};
		thumbsContainer.children[0].classList.add('rh-slider-thumbs-item--active');
	}

	function onPanStart (e) {
		panStart = true;
		var $ = getEvent(e);
		if ( !isTouchEvent(e) ) {
			preventDefaultBehavior(e);
		}
		lastPosition.x = initPosition.x = $.changedTouches[0].clientX;
		lastPosition.y = initPosition.y = $.changedTouches[0].clientY;
	}

	function onPanMove (e) {
		if (panStart) {
			var $ = getEvent(e);
			lastPosition.x = $.changedTouches[0].clientX;
			lastPosition.y = $.changedTouches[0].clientY;
			if (moveDirectionExpected === '?') { moveDirectionExpected = getMoveDirectionExpected(); }
		}
	}

	function onPanEnd (e) {
		if (panStart) {
			panStart = false;

			var $ = getEvent(e);
			lastPosition.x = $.changedTouches[0].clientX;
			lastPosition.y = $.changedTouches[0].clientY;
			var dist = getDist(lastPosition, initPosition);
			if (Math.abs(dist)) {
				if (!isTouchEvent(e)) {
				  // prevent "click"
				  var target = getTarget(e);
				  addEvents(target, {'click': function preventClick (e) {
					preventDefaultBehavior(e);
					removeEvents(target, {'click': preventClick});
				  }});
				}
				if (moveDirectionExpected) {
					onControlsClick(e, dist > 0 ? -1 : 1);
				}
			}
		}
	}

	function getDist(a, b) { 
		return a.x - b.x; 
	}

	function getMoveDirectionExpected () {
		return getTouchDirection(toDegree(lastPosition.y - initPosition.y, lastPosition.x - initPosition.x), swipeAngle) === 'horizontal';
	}

	function getTouchDirection(angle, range) {
		var direction = false,
			gap = Math.abs(90 - Math.abs(angle));
		if (gap >= 90 - range) {
			direction = 'horizontal';
		} else if (gap <= range) {
			direction = 'vertical';
		}
		return direction;
	}

	function toDegree (y, x) {
		return Math.atan2(y, x) * (180 / Math.PI);
	}

	function isTouchEvent (e) {
		return e.type.indexOf('touch') >= 0;
	}
	
	function onControlsClick (e, dir) {
		if (!dir) {
			e = getEvent(e);
			var target = getTarget(e);
			while (target !== controlsContainer && [prevButton, nextButton].indexOf(target) < 0) { 
				target = target.parentNode; 
			}
			var targetIn = [prevButton, nextButton].indexOf(target);
			if (targetIn >= 0) {
				dir = targetIn === 0 ? -1 : 1;
			}
		}
		if (dir) {
			index += slideBy * dir;
			render();
		}
	}

	function onThumbClick (e) {
		e = getEvent(e);
		var target = getTarget(e);
		
		target = target.parentNode;
		console.log(target);
		if( target.hasAttribute('data-slide') ){
			index = Number(target.getAttribute('data-slide'));
			if ( index !== indexCached ) {
				render();
			}
		}
	}

	function render () {
		index = Math.max( 0, Math.min(slideCount - 1, index));
		if (index !== indexCached ){
			transformCore();
		}
	}

	function transformCore () {
		animateSlide(indexCached, true);
        animateSlide(index);
        indexCached = index;
	}

	function animateSlide (number, isOut) {
		var l = number + 1;
		for (var i = number; i < l; i++) {
			var item = slideItems[i];
			item.style.left = (i - index) * 100 / 1 + '%';
		}
		if(isOut){
			thumbsContainer.children[number].classList.remove('rh-slider-thumbs-item--active');
		} else {
			thumbsContainer.children[number].classList.add('rh-slider-thumbs-item--active');
		}
	}

	function getEvent (e) {
		e = e || window.event;
		return e;
	}

	function getTarget (e) {
		return e.target || window.event.srcElement;
	}

	function getStartIndex (ind) {
		ind = ind ? Math.max( 0, Math.min( slideCount - 1, ind ) ) : 0;
		return ind;
	}

	function getOption (item) {
		var result = options[item];
		return result;
	}
	function preventDefaultBehavior (e) {
		e.preventDefault ? e.preventDefault() : e.returnValue = false;
	}
}

function extend() {
	var obj, name, copy, target = arguments[0] || {}, i = 1, length = arguments.length;
	for (; i < length; i++) {
		if ((obj = arguments[i]) !== null) {
			for (name in obj) {
				copy = obj[name];
				if (target === copy) {
					continue;
				} else if (copy !== undefined) {
					target[name] = copy;
				}
			}
		}
	}
	return target;
}

function addEvents(el, obj, preventScrolling) {
	var supportsPassive = false;
	try {
		var opts = Object.defineProperty({}, 'passive', {
			get: function() {
				supportsPassive = true;
			}
		});
		window.addEventListener("test", null, opts);
	} catch (e) {}
	var passiveOption = supportsPassive ? { passive: true } : false;
	for (var prop in obj) {
		var option = ['touchstart', 'touchmove'].indexOf(prop) >= 0 && !preventScrolling ? passiveOption : false;
		el.addEventListener(prop, obj[prop], option);
	}
}

function removeEvents(el, obj) {
	var supportsPassive = false;
	try {
		var opts = Object.defineProperty({}, 'passive', {
			get: function() {
				supportsPassive = true;
			}
		});
		window.addEventListener("test", null, opts);
	} catch (e) {}
	var passiveOption = supportsPassive ? { passive: true } : false;
	for (var prop in obj) {
	  var option = ['touchstart', 'touchmove'].indexOf(prop) >= 0 ? passiveOption : false;
	  el.removeEventListener(prop, obj[prop], option);
	}
}