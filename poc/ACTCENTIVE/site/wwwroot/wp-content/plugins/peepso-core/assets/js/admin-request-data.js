var list_request = {
	_wpnonce: null,

	/**
	 * Register our triggers
	 *
	 * We want to capture clicks on specific links, but also value change in
	 * the pagination input field. The links contain all the information we
	 * need concerning the wanted page number or ordering, so we'll just
	 * parse the URL to extract these variables.
	 *
	 * The page number input is trickier: it has no URL so we have to find a
	 * way around. We'll use the hidden inputs added in TT_Example_List_Table::display()
	 * to recover the ordering variables, and the default paged input added
	 * automatically by WordPress.
	 */
	init: function() {
		this._wpnonce = jQuery('#request-data-nonce').val();
	},

	/** AJAX call
	 *
	 * Send the call and replace table parts with updated version!
	 *
	 * @param  data   object   data The data to pass through AJAX
	 */
	update: function(data) {
		window.location.reload();
	},
	approve_request: function(req_id) {
		var req = { req_id: req_id, _wpnonce: this._wpnonce };
		peepso.postJson('adminrequestdataajax.approve_request', req, function(json) {
			if (json.success) {
				// if (json.notices)
				// 	psmessage.show("", json.notices[0]).fade_out(pswindow.fade_time);
				list_request.update({});
			} else if (json.has_errors) {
				psmessage.show('', json.errors).fade_out(pswindow.fade_time);
			}
		});
	},
	reject_request: function(req_id) {
		var req = { req_id: req_id, _wpnonce: this._wpnonce };
		peepso.postJson('adminrequestdataajax.reject_request', req, function(json) {
			if (json.success) {
				list_request.update({});
			} else if (json.has_errors) psmessage.show('', json.errors).fade_out(pswindow.fade_time);
		});
	},

	/**
	 * Filter the URL Query to extract variables
	 *
	 * @see http://css-tricks.com/snippets/javascript/get-url-variables/
	 *
	 * @param query     string   query The URL query part containing the variables
	 * @param variable  string   variable Name of the variable we want to get
	 *
	 * @return   string|boolean The variable value if available, false else.
	 */
	__query: function(query, variable) {
		var vars = query.split('&');
		for (var i = 0; i < vars.length; i++) {
			var pair = vars[i].split('=');
			if (pair[0] === variable) {return pair[1];}
		}
		return false;
	}
};

// Show time!
list_request.init();

jQuery(document).ready(function($) {
	var _wpnonce = jQuery('#request-data-nonce').val();
});

// EOF
