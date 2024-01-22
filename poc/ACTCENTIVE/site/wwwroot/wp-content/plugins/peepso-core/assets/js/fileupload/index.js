/**
 * The jQuery plugin namespace.
 * @external "jQuery.fn"
 * @see {@link https://learn.jquery.com/plugins/|jQuery Plugins}
 */

var $ = require( 'jquery' ),
	fileupload = $.fn.fileupload;

require( 'blueimp-file-upload/js/jquery.iframe-transport' );
require( 'blueimp-file-upload' );

/**
 * Wrapped jQuery File Upload Plugin, to prevent conflict
 * when other plugins loaded the same library.
 *
 * @function external:"jQuery.fn".psFileupload
 * @see {@link https://www.npmjs.com/package/blueimp-file-upload}
 */
$.fn.psFileupload = $.fn.fileupload;

// Restore the original library if it exists.
if ( fileupload ) {
	$.fn.fileupload = fileupload;
} else {
	delete $.fn.fileupload;
}
