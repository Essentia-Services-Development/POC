(function( root, factory ) {
	var moduleName = 'PsNotification',
        moduleObject = factory( moduleName, jQuery, peepso.observer );

	if ( typeof module === 'object' && module.exports ) {
		module.exports = moduleObject;
	} else {
		root[ moduleName ] = moduleObject;
	}

})( this, function( moduleName, $, observer ) {

    var IS_LOGIN = +peepsodata.currentuserid,
        POLLING_INTERVAL = +peepsodata.get_latest_interval || 30000;

    return peepso.createSingleton( moduleName, /** @lends PsNotification.prototype */ {

        /**
         * Initialize class.
         * @constructs
         */
        __constructor: function() {
            this._started = false;
            this._timer = false;
            this._xhr = false;

            // Stop polling if user is logged out.
            $( window ).on( 'peepso_auth_required', function() {
                IS_LOGIN = false;
                that.stop();
            });
        },

        /**
         * Perform polling request.
         */
        doPolling: function() {
            var that = this;

            this.getLatestCount().done(function( json ) {
                var unreadCount;

                if ( json.success && ! json.session_timeout ) {

                    // Update unread counter on title bar.
                    unreadCount = 0;
                    _.each( json.data, function( item ) {
                        unreadCount += Math.max( 0, item.count ) || 0;
                    });
                    that.updateTitleBar( unreadCount );

                    // Trigger hooks.
                    observer.doAction( 'peepso_notification_update', json );
                }
            });
        },

        /**
         * Get latest state of notification counter.
         * @return {jQuery.Deferred}
         */
        getLatestCount: function() {
            var that = this,
                transport = peepso.disableAuth().disableError(),
                url = 'notificationsajax.get_latest_count?new',
                params = null;

            return $.Deferred(function( defer ) {
                if ( that._xhr ) {
                    that._xhr.abort();
                }

                that._xhr = transport.postJson( url, params, function( json ) {
                    if ( json.success && ! json.session_timeout ) {
                        that._xhr = false;
                        defer.resolve( json );
                    }
                }).ret;
            });
        },

        /**
         * Prepend browser's title bar text with unread notification count value.
         * @param {number} unreadCount
         */
        updateTitleBar: function( unreadCount ) {
            var title = ( document.title || '' ).replace( /^\(\d+\)\s*/, '' );

			if ( unreadCount > 0 ) {
				title = '(' + unreadCount + ') ' + title;
            }

			if ( document.title !== title ) {
				document.title = title;
			}
        },

        /**
         * Start notification long polling.
         */
        start: function() {
            var that = this;

            if ( this._started || ! IS_LOGIN ) {
                return;
            }

            this._started = true;
            this.doPolling();
            this._timer = setInterval(function() {
                that.doPolling();
            }, POLLING_INTERVAL );
        },

        /**
         * Stop notification long polling.
         */
        stop: function() {
            if ( this._xhr ) {
                this._xhr.abort();
                this._xhr = false;
            }

            if ( this._timer ) {
                clearTimeout( this._timer );
                this._timer = false;
            }

            this._started = false;
        },

        /**
         * Restart notification long polling.
         */
        restart: function() {
            this.stop();
            this.start();
        }

    });

});
