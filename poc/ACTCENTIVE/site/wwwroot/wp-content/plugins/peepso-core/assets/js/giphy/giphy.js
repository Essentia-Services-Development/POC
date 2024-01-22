(function( root, factory ) {

	var moduleName = 'PsGiphy';
	var moduleObject = factory( moduleName, root.jQuery );

	var moduleSingleton = {
		getInstance: function() {
			if ( ! this._instance ) {
				this._instance = new moduleObject();
			}
			return this._instance;
		}
	};

	// export singleton
	if ( typeof module === 'object' && module.exports ) {
		module.exports = moduleSingleton;
	} else {
		root[ moduleName ] = moduleSingleton;
	}

})( window, function( moduleName, $ ) {

	return peepso.createClass( moduleName, {

		/**
		 * Class constructor.
		 */
		__constructor: function() {
			this.apiKey = peepsogiphydata.giphy_api_key;
			this.displayLimit = peepsogiphydata.giphy_display_limit;
			this.rating = peepsogiphydata.giphy_rating;
		},

		/**
		 * Search images with result-caching to improve performance.
		 * @param {string} [keyword]
		 * @return {jQuery.Deferred}
		 */
		search: function( keyword ) {
			keyword = $.trim( keyword || '' );
			return $.Deferred( $.proxy(function( defer ) {
				this._result || (this._result = {});

				 // try to find in cache
				var result = this._result[ keyword ];
				if ( result ) {
					defer.resolveWith( this, [ result ]);
					return;
				}

				// call Giphy API if result not found in cache
				this._giphy( keyword ).done( $.proxy(function( response ) {
					this._result[ keyword ] = response.data;
					defer.resolveWith( this, [ response.data ]);
				}, this ));
			}, this ));
		},

		/**
		 * Search Giphy images by keyword.
		 * @param {string} [keyword]
		 * @return {jQuery.Deferred}
		 */
		_giphy: function( keyword ) {
			var url = 'https://api.giphy.com/v1/gifs/trending',
				params = { api_key: this.apiKey, limit: this.displayLimit };

			if ( this.rating ) {
				params.rating = this.rating;
			}

			if ( keyword ) {
				url = 'https://api.giphy.com/v1/gifs/search';
				params = _.extend( params, {
					q: keyword,
					offset: 0
				});
			}

			return $.get( url, params );
		},

	});

});
