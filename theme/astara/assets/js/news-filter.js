import { store, getContext } from '@wordpress/interactivity';

const { state } = store( 'astara/newsFilter', {
	state: {
		activeTag: 'all',
	},
	actions: {
		setTag() {
			const { tagSlug } = getContext();
			state.activeTag = tagSlug;
		},
	},
	callbacks: {
		isTagActive() {
			const { tagSlug } = getContext();
			return tagSlug === state.activeTag;
		},
		isPanelHidden() {
			const { tagSlug } = getContext();
			return tagSlug !== state.activeTag;
		},
	},
} );
