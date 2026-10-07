// Design tokens: computed styles checked against the Figma values.
// Run at the 1728px design width (desktop project only).
const { test, expect } = require( '@playwright/test' );

const ORANGE = 'rgb(249, 112, 62)';
const OFF_WHITE = 'rgb(247, 247, 243)';

test.describe( 'design values @ 1728', () => {
	test.beforeEach( ( { page }, testInfo ) => { // eslint-disable-line no-unused-vars
		test.skip( testInfo.project.name !== 'desktop', 'desktop only' );
	} );
	test.use( { viewport: { width: 1728, height: 1000 } } );

	test( 'stats section', async ( { page } ) => {
		await page.goto( '/' );
		const number = page.locator( '.astara-stat__number' ).first();
		const s = await number.evaluate( ( el ) => {
			const c = getComputedStyle( el );
			return { size: parseFloat( c.fontSize ), weight: c.fontWeight, color: c.color };
		} );
		expect( s.size ).toBeCloseTo( 90, 0 ); // Figma H1: 90px
		expect( s.weight ).toBe( '300' );
		expect( s.color ).toBe( ORANGE );

		const label = await page.locator( '.astara-stat__label' ).first().evaluate( ( el ) => getComputedStyle( el ).fontSize );
		expect( label ).toBe( '20px' );

		// All four stats sit on one row.
		const tops = await page.locator( '.astara-stat' ).evaluateAll( ( els ) => els.map( ( e ) => Math.round( e.getBoundingClientRect().top ) ) );
		expect( new Set( tops ).size ).toBe( 1 );
	} );

	test( 'solid and outline buttons', async ( { page } ) => {
		await page.goto( '/' );
		const solid = page.locator( '.astara-btn-solid .wp-block-button__link' ).first();
		const s = await solid.evaluate( ( el ) => {
			const c = getComputedStyle( el );
			return { bg: c.backgroundColor, color: c.color, size: c.fontSize, weight: c.fontWeight, height: el.getBoundingClientRect().height, radius: c.borderRadius };
		} );
		expect( s ).toEqual( { bg: ORANGE, color: OFF_WHITE, size: '18px', weight: '700', height: 50, radius: '4px' } );

		const outline = page.locator( '.astara-btn-outline .wp-block-button__link' ).first();
		expect( await outline.evaluate( ( el ) => getComputedStyle( el ).backgroundColor ) ).toBe( 'rgba(0, 0, 0, 0)' );
	} );

	test( 'eyebrow text uses the accessible darker orange on light sections', async ( { page } ) => {
		await page.goto( '/' );
		const color = await page.locator( '.astara-how-we-work .astara-eyebrow' ).first().evaluate( ( el ) => getComputedStyle( el ).color );
		expect( color ).toBe( 'rgb(168, 56, 11)' );
	} );

	test( 'header navigation is visible', async ( { page } ) => {
		await page.goto( '/' );
		await expect( page.locator( '.astara-header nav.astara-nav' ) ).toBeVisible();
	} );
} );
