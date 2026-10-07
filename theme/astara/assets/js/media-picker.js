/**
 * "Choose file from Media Library" buttons (.astara-choose-file) that fill the
 * URL field named in their data-target. Used for a news post's direct link and
 * a portfolio company's case study PDF.
 */
( function ( $ ) {
	'use strict';

	$( function () {
		if ( ! window.wp || ! wp.media ) {
			return;
		}

		$( document ).on( 'click', '.astara-choose-file', function ( event ) {
			event.preventDefault();

			var input = $( $( this ).data( 'target' ) );
			if ( ! input.length ) {
				return;
			}

			var frame = wp.media( {
				title: 'Choose a file',
				button: { text: 'Use this file' },
				multiple: false,
			} );

			frame.on( 'select', function () {
				var attachment = frame.state().get( 'selection' ).first().toJSON();
				input.val( attachment.url ).trigger( 'change' );
			} );

			frame.open();
		} );
	} );
} )( window.jQuery );
