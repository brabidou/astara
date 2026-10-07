import { store, getContext } from '@wordpress/interactivity';

// Phones and tablets (portrait and landscape) go straight to the page instead
// of a popup, which is cramped on a small screen.
const noPopupQuery = window.matchMedia( '(max-width: 1024px)' );

store( 'astara/modal', {
	actions: {
		open( event ) {
			// The "+" is a real link to the member's own page (crawlable, works
			// with JS off, opens in a new tab on middle-click). On a small screen,
			// let the link navigate. Otherwise, with JS on and a plain click,
			// open the modal in place instead of navigating away.
			if ( noPopupQuery.matches ) {
				return;
			}
			event.preventDefault();
			getContext().isOpen = true;
		},
		close() {
			getContext().isOpen = false;
		},
		closeOnEscape( event ) {
			if ( event.key === 'Escape' ) {
				getContext().isOpen = false;
			}
		},
	},
} );
