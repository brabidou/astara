// Interactions: team popups, mobile menu.
const { test, expect } = require( '@playwright/test' );

test.describe( 'team page', () => {
	const triggers = {
		photo: '.astara-team-card__photo a',
		name: '.astara-team-card__name a',
		'+': '.astara-team-card__toggle',
	};

	for ( const [ label, selector ] of Object.entries( triggers ) ) {
		test( `${ label } opens the bio (popup on desktop, page on smaller screens)`, async ( { page }, testInfo ) => {
			await page.goto( '/team/' );
			const member = page.locator( '.astara-team-member' ).first();
			await member.locator( selector ).click();
			if ( testInfo.project.name === 'desktop' ) {
				await expect( member.locator( '.astara-team-modal' ) ).toBeVisible();
				await page.keyboard.press( 'Escape' );
				await expect( member.locator( '.astara-team-modal' ) ).toBeHidden();
			} else {
				await expect( page ).toHaveURL( /\/team\/[^/]+\/$/ );
			}
		} );
	}

	test( 'the name link is keyboard reachable', async ( { page } ) => {
		await page.goto( '/team/' );
		const link = page.locator( '.astara-team-card__name a' ).first();
		await link.focus();
		await expect( link ).toBeFocused();
	} );
} );

test.describe( 'mobile menu', () => {
	test.beforeEach( ( { page }, testInfo ) => { // eslint-disable-line no-unused-vars
		test.skip( testInfo.project.name !== 'mobile', 'mobile only' );
	} );

	test( 'opens, shows links and closes', async ( { page } ) => {
		await page.goto( '/' );
		await page.locator( '.wp-block-navigation__responsive-container-open' ).first().click();
		const overlay = page.locator( '.wp-block-navigation__responsive-container.is-menu-open' );
		await expect( overlay ).toBeVisible();
		await expect( overlay.getByRole( 'link', { name: /team/i } ) ).toBeVisible();
		await overlay.locator( '.wp-block-navigation__responsive-container-close' ).click();
		await expect( overlay ).toBeHidden();
	} );
} );
