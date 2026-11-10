import { store, getContext } from '@wordpress/interactivity';

// Phones and tablets (portrait and landscape) go straight to the page instead
// of a popup, which is cramped on a small screen.
const noPopupQuery = window.matchMedia( '(max-width: 1024px)' );

// The dialog panel's own focusable elements, for the open-focus and Tab trap below.
function focusableElements( dialog ) {
	return Array.from( dialog.querySelectorAll( 'a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])' ) );
}

function dialogFor( triggerEl ) {
	const root = triggerEl && triggerEl.closest( '[data-wp-context]' );
	return root && root.querySelector( '[role="dialog"]' );
}

function openModal( context, triggerEl ) {
	context.triggerEl = triggerEl;
	context.isOpen = true;
	// Wait a frame so the now-unhidden dialog has something focusable.
	requestAnimationFrame( () => {
		const [ first ] = focusableElements( dialogFor( triggerEl ) || document.createElement( 'div' ) );
		if ( first ) {
			first.focus();
		}
	} );
}

function closeModal( context ) {
	context.isOpen = false;
	context.caseOpen = false;
	if ( context.triggerEl ) {
		context.triggerEl.focus();
	}
}

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
			openModal( getContext(), event.currentTarget );
		},
		close() {
			closeModal( getContext() );
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
			context.caseOpen = true;
			openModal( context, event.currentTarget );
		},
		closeOnEscape( event ) {
			const context = getContext();
			if ( ! context.isOpen ) {
				return;
			}
			if ( event.key === 'Escape' ) {
				closeModal( context );
				return;
			}
			// Trap Tab inside the open dialog, since it has no native inert/focus-trap of its own.
			if ( event.key !== 'Tab' ) {
				return;
			}
			const dialog = dialogFor( context.triggerEl );
			const focusable = dialog ? focusableElements( dialog ) : [];
			if ( ! focusable.length ) {
				return;
			}
			const first = focusable[ 0 ];
			const last = focusable[ focusable.length - 1 ];
			if ( event.shiftKey && document.activeElement === first ) {
				event.preventDefault();
				last.focus();
			} else if ( ! event.shiftKey && document.activeElement === last ) {
				event.preventDefault();
				first.focus();
			}
		},
	},
} );
