import { store, getContext } from '@wordpress/interactivity';

store( 'astara/modal', {
	actions: {
		open( event ) {
			// The "+" is a real link to the member's own page (crawlable, works
			// with JS off, opens in a new tab on middle-click). With JS on and a
			// plain click, open the modal in place instead of navigating away.
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
