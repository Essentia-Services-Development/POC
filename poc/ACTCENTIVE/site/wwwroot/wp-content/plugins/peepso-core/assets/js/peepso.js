/**
 * PeepSo class.
 * @class PeepSo
 */
function PeepSo() {
	// if global 'peepsodata' exists, use settings from it
	if ('undefined' !== typeof peepsodata) {
		this.defaultUrl = peepsodata.ajaxurl_legacy;
		this.userId = parseInt(peepsodata.userid);
	}
}

PeepSo.prototype = {
	error: false, // true if error occured
	errorText: '', // error message
	errorStatus: '', // error status
	callback: null, // callback function for successful requests
	trxComplete: 0, // set to 1 when done with ajax call and transaction is complete
	errorCallback: null, // callback function for error requests
	validationErrors: new Array(), // validation error information
	url: '', // url to send request to
	ret: 0, // return value
	timeout: 0, // timeout for request
	formElementType: 'td', // DOM element that wraps form elements (the <input> element)
	async: true, // when true, do asynchronous calls
	defaultUrl: null, // default server side script to connect to
	userId: null, // user id
	action: '', // url part of the function call
	authRequired: true, // set to false if user is not required to be logged in for ajax request

	// initialize error and callback information
	init: function (ajaxCallback, sUrl) {
		this.error = false;
		this.errorText = '';
		this.errorStatus = '';
		this.callback = ajaxCallback;
		this.trxComplete = 0;
		this.errorCallback = null;
		//if ("undefined" === typeof(sUrl) || null === sUrl)
		//	this.url = peepso.defaultUrl;
		//else
		//	this.url = sUrl;
		this.url = peepsodata.ajaxurl_legacy + sUrl;
		this.action = sUrl;
		this.timeout = 0;
		this.async = true;
	},

	// default callback method for all PeepSo Ajax functions
	peepSoCallback: function (jsonData) {
		if (null === jsonData) {
			this.trxComplete = 1;
			return;
		}

		// the following sections assume certain data values are set within the
		// json data. Use the AjaxResponse class to create these.

		// check for '.session_timeout' and go to login page
		try {
			if (
				this.authRequired &&
				'undefined' !== typeof jsonData.session_timeout &&
				'auth.login' !== this.action
			) {
				jQuery(window).trigger('peepso_auth_required');
				this.trxComplete = 1;
				return;
			}
		} catch (e) {}

		// check for setting focus
		if ('undefined' !== typeof jsonData.focus && null !== jsonData.focus) {
			// look for <input id=>
			if (document.getElementById(jsonData.focus) !== null) {
				document.getElementById(jsonData.focus).focus();
			} else {
				// for for <form id=><input name=>
				var sel = '#' + jsonData.form + ' [name="' + jsonData.focus + '"]';
				jQuery(sel).focus();
			}
		}
		// response:
		// {"session_timeout":1,"errors":["Invalid credentials",""],"has_errors":1,"success":0}

		// check for messages
		try {
			// look for '.errors' and display them

			if ('undefined' !== typeof jsonData.errors) {
				var errorMsg = '';
				if (jsonData.errors.length > 0) {
					for (x = 0; x < jsonData.errors.length; x++) {
						if ('undefined' !== typeof jsonData.errors[x]['error']) {
							errorMsg += '<p>' + jsonData.errors[x]['error'] + '</p>';
						}
					}

					if ('' !== errorMsg) {
						pswindow.show(peepsodata.label_error, errorMsg);
					}
				}
			}

			// look for '.notices' and display them
			if ('undefined' !== typeof jsonData.notices) {
				var noticeMsg = '';
				if (jsonData.notices.length > 0) {
					for (x = 0; x < jsonData.notices.length; x++) {
						if (typeof jsonData.notices[x]['message'] !== 'undefined') {
							noticeMsg += jsonData.notices[x]['message'] + '\n';
						}
					}

					if ('' !== noticeMsg) {
						pswindow.show(peepsodata.label_notice, noticeMsg);
					}
				}
			}
		} catch (e) {}

		// if there is a callback function, call it
		if ('function' === typeof this.callback) {
			try {
				this.callback(jsonData);
			} catch (e) {}
		}

		this.trxComplete = 1;
	},

	// perform ajax get operation
	get: function (request, data, success_callback, datatype) {
		var inst = new PeepSo(); // create a new PeepSo instance

		// setting a custom timeout
		var timeout = this.timeout;
		inst.async = this.async;

		// reset async after every call
		this.async = true;

		target_url = peepsodata.ajaxurl_legacy + request;
		peepso.log('target=[' + target_url + ']');

		inst.init(success_callback, request); // target_url);
		if ('undefined' === typeof datatype || '' === datatype) {
			datatype = 'json';
		}
		inst.ret = jQuery.get(
			inst.url,
			data,
			function (data) {
				inst.peepSoCallback(data);
			},
			datatype,
			{ timeout: timeout },
			{ async: inst.async }
		);

		return inst;
	},

	// perform ajax get, forcing content type and data type to json
	getJson: function (target_url, data, success_callback) {
		var inst = new PeepSo();
		inst.init(success_callback, target_url);
		inst.async = this.async;
		inst.authRequired = this.authRequired;
		inst.errorDisabled = this.errorDisabled;

		// reset async after every call
		this.async = true;

		var req = {
			type: 'GET',
			url: inst.url,
			contentType: 'application/json; charset=utf-8',
			dataType: 'json',
			data: data,
			success: function (data) {
				inst.peepSoCallback(data);
			},
			error: function (jqXHR, textStatus, errorThrown) {
				inst.ajaxError(jqXHR, textStatus, errorThrown);
			},
			async: inst.async
		};

		this.authRequired = true;
		this.errorDisabled = false;
		return jQuery.ajax(req);
	},

	// perform ajax post operation with all form elements within a container
	postElems: function (target_url, req, success_callback, datatype) {
		// req has the following properties:
		//		.container	- name of jQuery selector for form container
		//		.action		- name of 'action' property to include in post data
		//		.req		- name of 'req' property to include in post data

		var inst = new PeepSo();
		inst.init(success_callback, target_url);
		inst.async = this.async;

		// reset async after every call
		this.async = true;

		if ('undefined' === typeof datatype || null === datatype) {
			datatype = 'json';
		}

		// collect data from the container
		var data = jQuery(req.container).find('input').serializeArray();
		data = jQuery.merge(data, jQuery(req.container).find('select').serializeArray());
		data = jQuery.merge(data, jQuery(req.container).find('textarea').serializeArray());
		// add the action and call attributes
		data.push({ name: 'action', value: req.action });
		data.push({ name: 'req', value: req.req });

		inst.ret = jQuery.post(
			inst.url,
			data,
			function (data) {
				inst.peepSoCallback(data);
			},
			datatype,
			{ async: inst.async }
		);
		return inst;
	},

	// perform ajax post, forcing content type and data type to json
	postJson: function (target_url, data, success_callback) {
		var inst = new PeepSo();
		inst.init(success_callback, target_url);
		inst.async = this.async;
		inst.authRequired = this.authRequired;
		inst.errorDisabled = this.errorDisabled;
		inst.errorCallback = this.errorCallback;

		// reset async after every call
		this.async = true;

		var req = {
			type: 'POST',
			url: inst.url,
			dataType: 'json',
			data: data,
			timeout: this.timeout,
			success: function (data) {
				inst.peepSoCallback(data);
			},
			error: function (jqXHR, textStatus, errorThrown) {
				inst.ajaxError(jqXHR, textStatus, errorThrown);
			},
			async: inst.async
		};

		inst.ret = jQuery.post({
			url: req.url,
			data,
			dataType: 'json',
			beforeSend(xhr) {
				xhr.setRequestHeader('X-PeepSo-Nonce', peepsodata.peepso_nonce);
			},
			success(data) {
				req.success(data);
			}
		});

		this.authRequired = true;
		this.errorDisabled = false;
		return inst;
	},

	//sets an optional timeout
	setTimeout: function (seconds) {
		this.timeout = seconds;
		return this;
	},

	// disables asynchronous calls for current instance
	disableAsync: function () {
		this.async = false;
		return this;
	},

	// disables authentication for this instance
	disableAuth: function () {
		this.authRequired = false;
		return this;
	},

	// sets the error callback function for this instance
	setErrorCallback: function (errCallback) {
		this.errorCallback = errCallback;
		return this;
	},

	// sets the form element type
	setFormElement: function (sElemName) {
		// Used to set the form element type. This is the element type that wraps
		// the individual <form> elements and is used to add validation messages
		// to the DOM.
		// If you are using tables, this should be "td". If each element is wrapped
		// in a <div> use "div". If you're using <li>s then "li".
		this.formElementType = sElemName;
		return this;
	},

	// standard handler for ajax errors
	ajaxError: function (XMLHttpReq, textStatus, errorThrown) {
		var timeout = false;

		this.error = true; // set error state to true
		this.errorStatus = textStatus || '';

		if ('undefined' === typeof XMLHttpReq) {
			this.errorText = 'Undefined error.';
		} else if (XMLHttpReq.responseText) {
			this.errorText = XMLHttpReq.responseText;
		} else {
			timeout = true;
			this.errorText = 'Connection timeout.';
		}

		if (timeout || this.errorDisabled) {
			this.log(this.errorStatus, this.errorText);
		} else {
			pswindow.show(this.errorStatus, this.errorText);
		}

		if ('function' === typeof this.errorCallback) {
			this.errorCallback();
		} // it's a function, we can safely call it
	},

	// enable error for particular instance
	enableError: function () {
		this.errorDisabled = false;
		return this;
	},

	// disable error for particular instance
	disableError: function () {
		this.errorDisabled = true;
		return this;
	},

	// perform console logging if console is available
	log: function () {
		if (window.console) {
			console.log.apply(console, arguments);
		}
	},

	// return window size
	screenSize: function () {
		var winwidth = window.innerWidth,
			size;

		if (winwidth <= 360) {
			size = 'xsmall';
		} else if (winwidth <= 480) {
			size = 'small';
		} else if (winwidth <= 991) {
			size = 'medium';
		} else {
			size = 'large';
		}

		return size;
	},

	isMobile: function () {
		var reMobile = /android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini/i,
			isMobile = reMobile.test(navigator.userAgent);
		return isMobile;
	},

	isTouch: function () {
		return 'ontouchstart' in document.documentElement;
	},

	isWebdriver: function () {
		return !!window.ps_webdriver;
	},

	/**
	 * Get default link color of current theme.
	 * @param {number} [opacity] Override opacity value from to the returned CSS color value.
	 * @returns {string} CSS color value.
	 */
	getLinkColor: function (opacity) {
		var $wrap, $dummy, color, parts;

		if (this._linkColor) {
			color = this._linkColor;
		} else {
			$wrap = jQuery('#peepso-wrap');
			$dummy = jQuery('<a/>').appendTo($wrap);
			color = $dummy.css('color');
			$dummy.remove();
			if (
				(parts = color.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+(?:\.\d+)?))?\)$/))
			) {
				color = [parts[1], parts[2], parts[3], parts[4] || 1];
			}
			this._linkColor = color;
		}

		if (typeof color !== 'string') {
			color =
				'rgba(' +
				color[0] +
				',' +
				color[1] +
				',' +
				color[2] +
				',' +
				(opacity || color[3]) +
				')';
		}

		return color;
	},

	/**
	 * Underscrore template wrapper with Mustache-style tag.
	 * @param {string} templateString Template string to be compiled.
	 * @param {object=} settings Override default settings.
	 */
	template: function (templateString, settings) {
		settings = jQuery.extend(
			{
				variable: 'data',
				evaluate: /\{\{([\s\S]+?)\}\}/g,
				interpolate: /\{\{=([\s\S]+?)\}\}/g,
				escape: /\{\{-([\s\S]+?)\}\}/g
			},
			settings || {}
		);

		var template = _.template(templateString, settings);

		// Backward-compatibility fix for Underscore prior to version 1.7.0.
		if (typeof template !== 'function') {
			template = _.template(templateString, null, settings);
		}

		return template;
	}
};

/**
 * PeepSo global instance.
 * @namespace
 * @type {PeepSo}
 */
// peepsoLegacy = new PeepSo();
peepso = peepso.npm.objectAssign(peepso, PeepSo.prototype);
// peepso = new PeepSo();

/**
 * Alias for `peepso` global object.
 * @link peepso
 * @deprecated
 */
window.$PeepSo = peepso;

jQuery(document).ready(function () {
	jQuery('.ps-tab-bar a[data-toggle=tab]').on('click.ps-tab', function (e) {
		jQuery(e.target).addClass('active').siblings('a[data-toggle=tab]').removeClass('active');
	});

	// add mobile in html class
	var className =
		'' + (peepso.isMobile() ? ' ps-mobile' : '') + (peepso.isTouch() ? ' ps-touch' : '');
	if (className !== '') {
		document.documentElement.className += className;
	}
});

jQuery(function ($) {
	$('.ps-js-navbar-toggle').on('click', function () {
		$('#ps-mobile-navbar').toggleClass('ps-navbar__submenu--open');
	});

	// Handle community filter links.
	let $links = $('.ps-js-navbar-menu').filter((index, item) =>
		item.href.match(/#(following|saved)/)
	);

	$links.on('click', function () {
		let pageHref = window.location.href.replace(/#.*$/, '');
		let linkHref = this.href.replace(/#.*$/, '');

		if (pageHref === linkHref) {
			setTimeout(function () {
				window.location.reload();
			}, 1);
		}
	});
});
