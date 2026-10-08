// Visual regression: full-page screenshots at each viewport. Baselines live in
// tests/e2e/visual.spec.js-snapshots/ (one set per platform). After an
// intentional design change run:  npx playwright test visual --update-snapshots
const { test, expect } = require( '@playwright/test' );
const pages = require( './pages' );

for ( const { path, name } of pages ) {
	test( `visual: ${ name }`, async ( { page }, testInfo ) => {
		await page.goto( path );
		// Google's reCAPTCHA widget is third-party and only present when keys are
		// saved, which changes the page height. Hide it so the layout is comparable.
		await page.addStyleTag( { content: '[data-astara-recaptcha] { display: none !important; }' } );
		await page.waitForLoadState( 'networkidle' );
		// Let lazy images and web fonts settle, then start from the top.
		await page.evaluate( async () => {
			await document.fonts.ready;
			for ( let y = 0; y < document.body.scrollHeight; y += 600 ) {
				window.scrollTo( 0, y );
				await new Promise( ( r ) => setTimeout( r, 80 ) );
			}
			window.scrollTo( 0, 0 );
		} );
		// Team headshots are content, and their resampling shifts slightly between
		// runs, so mask them: the layout, text and spacing around them are still checked.
		const mask = [ page.locator( '.astara-team-card__photo img' ) ];
		await expect( page ).toHaveScreenshot( `${ name }-${ testInfo.project.name }.png`, { fullPage: true, mask } );
	} );
}
