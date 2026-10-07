/**
 * "Choose file from Media Library" button for a news post's direct link.
 */
( function ( $ ) {
	'use strict';

	$( function () {
		var button = $( '#astara_news_link_choose' );
		var input = $( '#astara_news_link_url' );
		var frame;

		if ( ! button.length || ! input.length || ! window.wp || ! wp.media ) {
			return;
		}

		button.on( 'click', function ( event ) {
			event.preventDefault();

			if ( ! frame ) {
				frame = wp.media( {
					title: 'Choose a file',
					button: { text: 'Use this file' },
					multiple: false,
				} );

				frame.on( 'select', function () {
					var attachment = frame.state().get( 'selection' ).first().toJSON();
					input.val( attachment.url ).trigger( 'change' );
				} );
			}

			frame.open();
		} );
	} );
} )( window.jQuery );
