// Page structure: landmarks, headings, titles, meta, images, no sideways scroll.
const { test, expect } = require( '@playwright/test' );
const pages = require( './pages' );

for ( const { path, name } of pages ) {
	test.describe( name, () => {
		test.beforeEach( async ( { page } ) => {
			await page.goto( path );
		} );

		test( 'has one banner, one main and one contentinfo landmark', async ( { page } ) => {
			await expect( page.getByRole( 'banner' ) ).toHaveCount( 1 );
			await expect( page.getByRole( 'main' ) ).toHaveCount( 1 );
			await expect( page.getByRole( 'contentinfo' ) ).toHaveCount( 1 );
		} );

		test( 'has exactly one h1', async ( { page } ) => {
			await expect( page.locator( 'h1' ) ).toHaveCount( 1 );
		} );

		test( 'has a title and meta description', async ( { page } ) => {
			await expect( page ).toHaveTitle( /\S/ );
			const desc = await page.locator( 'meta[name="description"]' ).first().getAttribute( 'content' );
			expect( desc?.length ).toBeGreaterThan( 30 );
		} );

		test( 'every image has alt text', async ( { page } ) => {
			const missing = await page.locator( 'img:not([alt])' ).count();
			expect( missing ).toBe( 0 );
		} );

		test( 'does not scroll sideways', async ( { page } ) => {
			const overflow = await page.evaluate( () => document.documentElement.scrollWidth - document.documentElement.clientWidth );
			expect( overflow ).toBeLessThanOrEqual( 0 );
		} );
	} );
}

test( 'home page title is the site name', async ( { page } ) => {
	await page.goto( '/' );
	await expect( page ).toHaveTitle( 'Astara Capital Partners' );
} );

test( 'unknown URL returns the 404 page', async ( { page } ) => {
	const response = await page.goto( '/this-page-does-not-exist/' );
	expect( response.status() ).toBe( 404 );
	await expect( page.locator( 'h1' ) ).toHaveCount( 1 );
} );
