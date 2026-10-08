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
			const context = getContext();
			context.isOpen = false;
			context.caseOpen = false;
		},
		// The popup's "Case Study" accordion.
		toggleCase() {
			const context = getContext();
			context.caseOpen = ! context.caseOpen;
		},
		// The grid's "Case Study" button for a text-only case study: open the
		// popup with the accordion expanded (small screens just follow the link).
		openCase( event ) {
			if ( noPopupQuery.matches ) {
				return;
			}
			event.preventDefault();
			const context = getContext();
			context.isOpen = true;
			context.caseOpen = true;
		},
		closeOnEscape( event ) {
			if ( event.key === 'Escape' ) {
				const context = getContext();
				context.isOpen = false;
				context.caseOpen = false;
			}
		},
	},
} );
