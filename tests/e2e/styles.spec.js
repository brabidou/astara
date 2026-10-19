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
		expect( s.size ).toBeCloseTo( 72, 0 ); // Figma H1 is 90px; scaled down one step at the client's request
		expect( s.weight ).toBe( '200' ); // one step lighter than Figma, at the client's request
		expect( s.color ).toBe( ORANGE );

		const label = await page.locator( '.astara-stat__label' ).first().evaluate( ( el ) => getComputedStyle( el ).fontSize );
		expect( label ).toBe( '18px' );

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
		expect( s ).toEqual( { bg: ORANGE, color: OFF_WHITE, size: '16px', weight: '600', height: 44, radius: '4px' } );

		const outline = page.locator( '.astara-btn-outline .wp-block-button__link' ).first();
		expect( await outline.evaluate( ( el ) => getComputedStyle( el ).backgroundColor ) ).toBe( 'rgba(0, 0, 0, 0)' );
	} );

	test( 'eyebrow text uses the Figma brand orange', async ( { page } ) => {
		await page.goto( '/' );
		const color = await page.locator( '.astara-how-we-work .astara-eyebrow' ).first().evaluate( ( el ) => getComputedStyle( el ).color );
		expect( color ).toBe( ORANGE );
	} );

	test( 'header navigation is visible', async ( { page } ) => {
		await page.goto( '/' );
		await expect( page.locator( '.astara-header nav.astara-nav' ) ).toBeVisible();
	} );
} );

test.describe( 'layout', () => {
	for ( const width of [ 1280, 1512, 1920, 2560 ] ) {
		test( `header, sections and footer share one left edge at ${ width }px`, async ( { page }, testInfo ) => {
			test.skip( testInfo.project.name !== 'desktop', 'desktop only' );
			await page.setViewportSize( { width, height: 900 } );
			for ( const path of [ '/', '/team/', '/contact/' ] ) {
				await page.goto( path );
				const edges = await page.evaluate( () => {
					const left = ( el ) => Math.round( el.getBoundingClientRect().left );
					const sections = [ ...document.querySelectorAll( '.wp-block-post-content > *' ) ].map( ( sec ) => {
						const style = getComputedStyle( sec );
						return Math.round( sec.getBoundingClientRect().left + parseFloat( style.paddingLeft ) + parseFloat( style.marginLeft ) );
					} );
					return { logo: left( document.querySelector( '.astara-logo' ) ), footer: left( document.querySelector( '.astara-footer__wordmark' ) ), sections };
				} );
				expect( Math.abs( edges.footer - edges.logo ), `${ path } footer vs logo` ).toBeLessThanOrEqual( 1 );
				expect( edges.sections.some( ( x ) => Math.abs( x - edges.logo ) <= 1 ), `${ path } a section aligns with the logo` ).toBe( true );
			}
		} );
	}

	test( 'the home hero fits on a 14-inch laptop screen (1512x860)', async ( { page }, testInfo ) => {
		test.skip( testInfo.project.name !== 'desktop', 'desktop only' );
		await page.setViewportSize( { width: 1512, height: 860 } );
		await page.goto( '/' );
		const bottom = await page.locator( '.astara-hero' ).evaluate( ( el ) => el.getBoundingClientRect().bottom );
		expect( bottom ).toBeLessThanOrEqual( 860 );
	} );
} );

test.describe( 'client round 2', () => {
	test( 'Team photos match the Figma grid at 1728px', async ( { page }, testInfo ) => {
		test.skip( testInfo.project.name !== 'desktop', 'desktop only' );
		await page.setViewportSize( { width: 1728, height: 1000 } );
		await page.goto( '/team/' );
		const sizes = await page.evaluate( () => [ ...document.querySelectorAll( '.astara-team-category' ) ].map( ( c ) => {
			const r = c.querySelector( '.astara-team-card__photo img' ).getBoundingClientRect();
			return { wide: !! c.querySelector( '.astara-team-grid--wide' ), w: r.width, h: r.height };
		} ) );
		for ( const s of sizes ) {
			expect( s.w ).toBeCloseTo( s.wide ? 400 : 270, -1 );
			expect( s.h ).toBeCloseTo( 349, -1 );
		}
		const banner = await page.locator( '.astara-team-hero' ).evaluate( ( el ) => el.getBoundingClientRect().height );
		expect( banner ).toBeCloseTo( 738, -1 );
	} );

	test( 'Contact newsletter: heading, field and Submit button on one line', async ( { page }, testInfo ) => {
		test.skip( testInfo.project.name !== 'desktop', 'desktop only' );
		await page.goto( '/contact/' );
		const button = page.locator( '.astara-contact__newsletter-submit' );
		await expect( button ).toHaveText( 'Submit' );
		const tops = await page.evaluate( () => [ '.astara-contact__newsletter-heading', '#astara-newsletter-email', '.astara-contact__newsletter-submit' ].map( ( sel ) => {
			const r = document.querySelector( sel ).getBoundingClientRect();
			return r.top + r.height / 2;
		} ) );
		expect( Math.max( ...tops ) - Math.min( ...tops ) ).toBeLessThanOrEqual( 4 );
	} );

	test( 'Investment Criteria eyebrow is white', async ( { page } ) => {
		await page.goto( '/' );
		const color = await page.locator( '.astara-criteria__eyebrow' ).evaluate( ( el ) => getComputedStyle( el ).color );
		expect( color ).toBe( 'rgb(247, 247, 243)' );
	} );

	test( 'home page buttons link to real pages', async ( { page } ) => {
		await page.goto( '/' );
		for ( const [ label, path ] of [ [ 'Contact', '/contact/' ], [ 'Meet our Team', '/team/' ] ] ) {
			const href = await page.locator( '.wp-block-button__link', { hasText: label } ).first().getAttribute( 'href' );
			expect( href ).toContain( path );
		}
	} );
} );
