import $ from 'jquery';
import { Promise } from 'peepso';
import { ajaxurl } from 'peepsodata';

/**
 * Get the url embeddable data.
 *
 * @param {string} url
 * @param {boolean} refresh
 * @returns {Promise.<Object,?string>}
 */
export function getEmbed( url, refresh = false ) {
	return new Promise( ( resolve, reject ) => {
		let endpoint = 'peepso_embed_content',
			params = { action: endpoint, url, refresh: !! refresh ? 1 : 0 },
			transport,
			json;

		transport = $.ajax( {
			type: 'POST',
			url: ajaxurl,
			data: params,
			dataType: 'json',
			success( response ) {
				json = response;
			}
		} );

		transport.always( () => {
			if ( json && json.success ) {
				resolve( json.data );
				return;
			}

			let error = ( json && json.error ) || undefined;
			reject( error );
		} );
	} );
}
