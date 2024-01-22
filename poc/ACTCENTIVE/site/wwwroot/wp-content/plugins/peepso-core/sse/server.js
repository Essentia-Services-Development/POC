const fs = require( 'fs' );
const http = require( 'http' );
const path = require( 'path' );
const rimraf = require( 'rimraf' );
const url = require( 'url' );

const args = require( 'minimist' )( process.argv.slice( 2 ) );

const WP_ROOT = path.normalize( args.path || `${ __dirname }/../../../../` );
const PORT = args.port || 8000;

/** @class */
class PeepSoSSE {
	/**
	 * Initialize PeepSo SSE endpoint.
	 *
	 * @param {http.IncomingMessage} req
	 * @param {http.ServerResponse} res
	 * @param {Object} opts
	 */
	constructor( req, res, opts = {} ) {
		let { query } = url.parse( req.url, true );

		this.userId = +query.user_id || 0;
		this.delay = +query.delay || 5000; // milliseconds
		this.timeout = +query.timeout || 30000; // milliseconds
		this.keepalive = +query.keepalive || 0; // every N loop(s)
		this.token = +query.token || null; // admin-ajax.php?action=peepso_sse_token

		this.dir = `${ WP_ROOT }wp-content/peepso/sse/`;
		this.eventsDir = `${ this.dir }events/${ this.userId }/${ this.token }/`; // directory to monitor

		this.eventId = 1; // event count is unique to the userId/token pair
		this.eventIdFile = ''; // path to file that caches the last event id in case of reconnecting
		this.tokenExpiry = 24 * 60 * 60; // delete token directory after N seconds
		this.iterations = 0; // internal iteration count

		// WordPress root directory not found?
		if ( ! fs.existsSync( this.dir ) ) {
			this.send( res, 'error_path_not_found' );
			res.end();
			return;
		}

		// Invalid user ID?
		if ( ! this.userId ) {
			this.send( res, 'error_invalid_user_id' );
			res.end();
			return;
		}

		// Invalid token?
		if ( ! this.token ) {
			this.send( res, 'error_invalid_token' );
			res.end();
			return;
		}

		// Token is a timestamp with 10000 - 99999 glued to it - expire it after {tokenExpiry} seconds.
		if ( fs.existsSync( this.eventsDir ) ) {
			let now = Math.floor( new Date().getTime() / 1000 );
			let token = +`${ this.token }`.substr( 0, 10 );
			if ( now - token >= this.tokenExpiry ) {
				rimraf.sync( this.eventsDir );
			}
		}

		// Assume that token has expired if the directory does not exist.
		if ( ! fs.existsSync( this.eventsDir ) ) {
			this.send( res, 'error_invalid_token' );
			res.end();
			return;
		}

		// Last successful event ID.
		this.eventIdFile = `${ this.eventsDir }last_event_id`;
		if ( fs.existsSync( this.eventIdFile ) ) {
			try {
				this.eventId = fs.readFileSync( this.eventIdFile, 'utf8' );
				this.eventId++;
			} catch ( err ) {}
		}

		this.send( res, 'debug_start', {
			user_id: this.userId,
			delay: this.delay,
			timeout: this.timeout,
			keepalive: this.keepalive,
			max_execution_time: opts.serverTimeout
		} );

		// Primary loop. Infinite but with built-in self-termination based on config.
		let timer = setInterval(
			() => {
				this.iterations++;

				let data = this.check();
				if ( data && data.length ) {
					data.forEach( event => {
						this.send( res, event );
					} );
				} else if ( this.keepalive > 0 && ! ( this.iterations % this.keepalive ) ) {
					this.send( res, 'keepalive' );
				}

				if ( this.timeout > 0 && this.iterations * this.delay >= this.timeout ) {
					clearInterval( timer );
					this.send( res, 'timeout' );
					res.end();
					return;
				}
			},
			// Prevent delay to be below the minimum allowed value in configuration.
			Math.max( this.delay, 1000 )
		);
	}

	/**
	 * Check for available events.
	 *
	 * @returns {string[]}
	 */
	check() {
		let events = [];

		fs.readdirSync( this.eventsDir ).forEach( file => {
			if ( file !== 'last_event_id' ) {
				events.push( file );
				fs.unlinkSync( `${ this.eventsDir }${ file }` );
			}
		} );

		return events;
	}

	/**
	 * Send event to the client.
	 *
	 * @param {http.ServerResponse} res
	 * @param {string} event
	 * @param {*} payload
	 */
	send( res, event, payload = undefined ) {
		event = {
			event,
			eventId: this.eventId,
			payload
		};

		// Cache the eventId and increment.
		try {
			fs.writeFileSync( this.eventIdFile, this.eventId, 'utf8' );
			this.eventId++;
		} catch ( err ) {}

		res.write( `data: ${ JSON.stringify( event ) }\n\n` );
	}
}

let server = http.createServer( ( req, res ) => {
	if ( req.headers.accept && req.headers.accept === 'text/event-stream' ) {
		res.writeHead( 200, {
			// CORS headers.
			'Access-Control-Allow-Origin': req.headers.origin,
			'Access-Control-Allow-Credentials': 'true',
			'Access-Control-Expose-Headers': '*',
			// Event-stream headers.
			'Content-Type': 'text/event-stream',
			'Cache-Control': 'no-cache',
			Connection: 'keep-alive'
		} );

		new PeepSoSSE( req, res, {
			serverTimeout: server.timeout
		} );
	} else {
		res.writeHead( 200, { 'Content-Type': 'text/html' } );
		res.write( 'PeepSo SSE endpoint is running.' );
		res.end();
	}
} );

server.listen( PORT );
console.log( `PeepSo SSE endpoint is running at http://localhost:${ PORT }` );
