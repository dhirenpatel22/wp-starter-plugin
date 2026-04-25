/* global wpspPublic */
( function () {
	'use strict';

	/**
	 * Typed REST helper — returns a Promise<Response>.
	 *
	 * @param {string} endpoint  e.g. 'items' or 'items/5'
	 * @param {string} method    HTTP verb
	 * @param {object} [body]    JSON body for POST/PUT
	 */
	window.wpspFetch = function ( endpoint, method = 'GET', body ) {
		return fetch( wpspPublic.restUrl + endpoint, {
			method,
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce':   wpspPublic.nonce,
			},
			body: body ? JSON.stringify( body ) : undefined,
		} );
	};
} )();
