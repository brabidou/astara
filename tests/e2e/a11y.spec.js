// Accessibility: axe-core (WCAG 2.2 A/AA + best practice) on every page.
const { test, expect } = require( '@playwright/test' );
const AxeBuilder = require( '@axe-core/playwright' ).default;
const pages = require( './pages' );

// KNOWN ISSUE (see GO-LIVE.md, "Client decision"): the brand orange button
// with white text is 2.6:1. Excluded until the client picks an accessible
// colour; remove these selectors once they do.
const KNOWN_ORANGE_BUTTONS = [ '.astara-btn-solid .wp-block-button__link', '.astara-contact__newsletter-submit' ];

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
