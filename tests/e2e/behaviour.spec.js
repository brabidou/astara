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

test.describe( 'portfolio case study', () => {
	test( 'every company with Case Study text has a Case Study button', async ( { page } ) => {
		await page.goto( '/portfolio/' );
		const tiles = await page.locator( '.astara-portfolio-company' ).count();
		expect( tiles ).toBeGreaterThan( 0 );
		await expect( page.locator( '.astara-portfolio-tile__button' ) ).toHaveCount( tiles );
	} );

	test( 'the Case Study button opens the popup with the accordion expanded; the accordion toggles', async ( { page }, testInfo ) => {
		await page.goto( '/portfolio/' );
		const company = page.locator( '.astara-portfolio-company' ).first();
		await company.locator( '.astara-portfolio-tile__button' ).click();
		if ( testInfo.project.name !== 'desktop' ) {
			// Small screens go to the company page, where the accordion starts open.
			await expect( page ).toHaveURL( /\/portfolio\/[^/]+\/$/ );
			await expect( page.locator( 'details.astara-portfolio-case__accordion' ) ).toHaveAttribute( 'open', '' );
			return;
		}
		const toggle = company.locator( '.astara-portfolio-modal__accordion-toggle' );
		const panel = company.locator( '.astara-portfolio-modal__accordion-panel' );
		await expect( company.locator( '.astara-portfolio-modal' ) ).toBeVisible();
		await expect( toggle ).toHaveAttribute( 'aria-expanded', 'true' );
		await expect( panel ).toBeVisible();
		await toggle.click();
		await expect( panel ).toBeHidden();
		await toggle.click();
		await expect( panel ).toBeVisible();
	} );

	test( 'opening from the logo leaves the accordion closed', async ( { page }, testInfo ) => {
		test.skip( testInfo.project.name !== 'desktop', 'popup is desktop only' );
		await page.goto( '/portfolio/' );
		const company = page.locator( '.astara-portfolio-company' ).first();
		await company.locator( '.astara-portfolio-tile' ).click();
		await expect( company.locator( '.astara-portfolio-modal__accordion-panel' ) ).toBeHidden();
	} );
} );
