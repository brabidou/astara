// Accessibility: axe-core (WCAG 2.2 A/AA + best practice) on every page.
const { test, expect } = require( '@playwright/test' );
const AxeBuilder = require( '@axe-core/playwright' ).default;
const pages = require( './pages' );

// CLIENT DECISION (2026-10-19, see GO-LIVE.md): keep the Figma brand orange
// (#F9703E) even though it is below WCAG AA contrast as a button fill with white
// text (2.6:1) and as text on the light backgrounds (2-2.6:1). These elements are
// excluded from the axe check so the rest of each page is still tested.
const KNOWN_ORANGE_BUTTONS = [
	'.astara-btn-solid .wp-block-button__link',
	'.astara-contact__newsletter-submit',
	'.astara-eyebrow',
	'.astara-principle__number',
	'.astara-news-archive__nav-link.is-active',
];

// Google's reCAPTCHA widget (an iframe on the Contact page) has its own contrast
// issues. It's third-party code we can't change, so it is excluded.
const THIRD_PARTY = [ 'iframe[title="reCAPTCHA"]' ];

for ( const { path, name } of pages ) {
	test( `axe: ${ name }`, async ( { page } ) => {
		await page.goto( path );
		await page.waitForLoadState( 'networkidle' );
		let axe = new AxeBuilder( { page } ).withTags( [ 'wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa', 'best-practice' ] );
		for ( const sel of [ ...KNOWN_ORANGE_BUTTONS, ...THIRD_PARTY ] ) {
			axe = axe.exclude( sel );
		}
		const { violations } = await axe.analyze();
		expect(
			violations.map( ( v ) => `${ v.id } (${ v.impact }) x${ v.nodes.length }: ${ v.nodes[ 0 ].target.join( ' ' ) }` )
		).toEqual( [] );
	} );
}
