/**
 * URL content fetching module.
 *
 * @module url
 * @example
 * let { getEmbed } = peepso.modules.url;
 *
 * getEmbed( 'https://www.google.com' )
 *     .then( data => console.log( data ) )
 *     .catch( error => console.error( error ) );
 */
export { getEmbed } from './get-embed';

/**
 * Get URL designated target.
 *
 * @module url
 */
export { getTarget } from './get-target';
