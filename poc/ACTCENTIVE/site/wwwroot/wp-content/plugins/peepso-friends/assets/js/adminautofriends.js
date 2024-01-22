var list = {
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
		this._wpnonce = jQuery('#autofriends-nonce').val();

		// This will have its utility when dealing with the page number input
		var timer;
		var delay = 500;

		// Pagination links, sortable link
		jQuery('.tablenav-pages a, .manage-column.sortable a, .manage-column.sorted a').on(
			'click',
			function(e) {
				// We don't want to actually follow these links
				e.preventDefault();
				// Simple way: use the URL to extract our needed variables
				var query = this.search.substring(1);

				var data = {
					paged: list.__query(query, 'paged') || '1',
					order: list.__query(query, 'order') || 'asc',
					orderby: list.__query(query, 'orderby') || 'rep_id'
				};
				list.update(data);
			}
		);

		// Page number input
		jQuery('input[name=paged]').on('keyup', function(e) {
			// If user hit enter, we don't want to submit the form
			// We don't preventDefault() for all keys because it would
			// also prevent to get the page number!
			if (13 === e.which) {
				e.preventDefault();
			}

			// This time we fetch the variables in inputs
			var data = {
				paged: parseInt(jQuery('input[name=paged]').val()) || '1',
				order: jQuery('input[name=order]').val() || 'asc',
				orderby: jQuery('input[name=orderby]').val() || 'rep_id'
			};

			// Now the timer comes to use: we wait half a second after
			// the user stopped typing to actually send the call. If
			// we don't, the keyup event will trigger instantly and
			// thus may cause duplicate calls before sending the intended
			// value
			window.clearTimeout(timer);
			timer = window.setTimeout(function() {
				list.update(data);
			}, delay);
		});
	},

	/** AJAX call
	 *
	 * Send the call and replace table parts with updated version!
	 *
	 * @param  data   object   data The data to pass through AJAX
	 */
	update: function(data) {
		peepso.postJson('userautofriends.sort', data, function(json) {
			if (json.success) {
				response = json.data;
				// Add the requested rows
				if (response.rows.length) {
					jQuery('#the-list').html(response.rows);
				}
				// Update column headers for sorting
				if (response.column_headers.length) {
					jQuery('thead tr, tfoot tr').html(response.column_headers);
				}
				// Update pagination for navigation
				if (response.pagination.bottom.length) {
					jQuery('.tablenav.top .tablenav-pages').html(
						jQuery(response.pagination.top).html()
					);
				}
				if (response.pagination.top.length) {
					jQuery('.tablenav.bottom .tablenav-pages').html(
						jQuery(response.pagination.bottom).html()
					);
				}

				// Init back our event handlers
				list.init();
			}
		});
	},

	befriend: function(user_id) {
		var req = { user_id: user_id, _wpnonce: this._wpnonce };
		peepso.postJson('userautofriends.befriend', req, function(json) {
			if (json.success) {
				list.update({});
			} else if (json.errors) {
				alert(json.errors);
			}
		});
	},

	remove: function(user_id) {
		var req = { user_id: user_id, _wpnonce: this._wpnonce };
		peepso.postJson('userautofriends.remove', req, function(json) {
			if (json.success) {
				list.update({});
			} else if (json.errors) {
				alert(json.errors);
			}
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
			if (pair[0] === variable) {
				return pair[1];
			}
		}
		return false;
	}
};

// Show time!
list.init();

// autocomplete

jQuery(document).ready(function($) {
	var _wpnonce = jQuery('#autofriends-nonce').val();

	// autocomplete
	$('#search_user').autocomplete({
		source: function(request, response) {
			var req = { user_name: request.term, _wpnonce: _wpnonce };
			peepso.postJson('userautofriends.search_user', req, function(json) {
				if (json.success) {
					resp = json.data;
					response(resp.users);
				} else if (json.errors) {
					alert(json.errors);
				}
			});
		},
		response: function(event, ui) {
			// ui.content is the array that's about to be sent to the response callback.
			if (ui.content.length === 0) {
				$('#empty-message').text('No results found');
			} else {
				$('#empty-message').empty();
			}
		},
		select: function(event, ui) {
			//console.log( "Selected: " + ui.item.value + " aka " + ui.item.id );
			var req = { user_id: ui.item.id, _wpnonce: _wpnonce };
			peepso.postJson('userautofriends.add_user', req, function(json) {
				if (json.success) {
					list.update({});
				} else if (json.errors) {
					alert(json.errors);
				}
			});
			$(this).val('');
			return false;
		}
	});
});

// EOF
