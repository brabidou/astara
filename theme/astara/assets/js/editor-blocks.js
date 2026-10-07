/**
 * Editor support for the theme's server-rendered blocks.
 *
 * These blocks are rendered entirely in PHP, so without this the editor reports
 * them as unsupported. Registering them here, with a ServerSideRender preview,
 * shows what visitors will see. Plain JS (no build step): the list of blocks
 * arrives in window.astaraEditorBlocks from PHP.
 */
( function ( wp ) {
	'use strict';

	var el = wp.element.createElement;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var ServerSideRender = wp.serverSideRender;
	var blocks = window.astaraEditorBlocks || {};

	Object.keys( blocks ).forEach( function ( name ) {
		var info = blocks[ name ];

		wp.blocks.registerBlockType( name, {
			apiVersion: 3,
			title: info[ 0 ],
			icon: info[ 1 ],
			description: info[ 2 ],
			category: 'theme',
			supports: { html: false },
			edit: function () {
				return el( 'div', useBlockProps(), el( ServerSideRender, { block: name } ) );
			},
			save: function () {
				return null;
			},
		} );
	} );
} )( window.wp );
