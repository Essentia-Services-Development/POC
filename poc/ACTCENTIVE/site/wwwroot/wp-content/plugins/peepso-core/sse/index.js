const fs = require( 'fs' );
const forever = require( 'forever-monitor' );

const args = require( 'minimist' )( process.argv.slice( 2 ) );

let config = require( './config.sample' );
let configFile = args.config;

if ( configFile && fs.existsSync( configFile ) ) {
	let configOverride = require( configFile );
	Object.assign( config, configOverride );
}

/**
 * The script below will run the SSE endpoint.
 */
( function() {
	let server = new forever.Monitor( './server.js', {
		max: 3,
		args: [
			config.WP_ROOT ? `--path=${ config.WP_ROOT }` : '',
			config.PORT ? `--port=${ config.PORT }` : ''
		]
	} );

	server.start();
} )();
