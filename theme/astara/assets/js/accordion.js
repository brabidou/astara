import { store, getContext } from '@wordpress/interactivity';

store( 'astara/accordion', {
	actions: {
		toggle() {
			const context = getContext();
			context.isOpen = ! context.isOpen;
		},
	},
} );
