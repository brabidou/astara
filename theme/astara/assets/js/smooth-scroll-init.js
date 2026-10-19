/* Smooth scrolling for #section links (see astara_enqueue_smooth_scroll() in inc/enqueue.php). */
( function () {
	if ( typeof SmoothScroll === 'undefined' ) {
		return;
	}

	// Phone menu: close the overlay before the page scrolls underneath it.
	document.addEventListener( 'click', function ( event ) {
		var link = event.target.closest && event.target.closest( '.wp-block-navigation__responsive-container.is-menu-open a[href*="#"]' );
		if ( ! link ) {
			return;
		}
		var close = link.closest( '.wp-block-navigation__responsive-container' ).querySelector( '.wp-block-navigation__responsive-container-close' );
		if ( close ) {
			close.click();
		}
	}, true );

	new SmoothScroll( 'a[href*="#"]:not([href="#"])', {
		speed: 500,
		speedAsDuration: true,
		updateURL: true,
	} );
}() );
