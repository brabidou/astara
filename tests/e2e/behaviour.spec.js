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
	// Which companies have Case Study text depends on the content, so these tests
	// work from what the page shows rather than assuming specific companies.
	test( 'a company has a Case Study button exactly when it has a Case Study accordion', async ( { page } ) => {
		await page.goto( '/portfolio/' );
		const companies = page.locator( '.astara-portfolio-company' );
		const total = await companies.count();
		expect( total ).toBeGreaterThan( 0 );
		for ( let i = 0; i < total; i++ ) {
			const company = companies.nth( i );
			const hasAccordion = ( await company.locator( '.astara-portfolio-modal__accordion-toggle' ).count() ) > 0;
			const hasButton = ( await company.locator( '.astara-portfolio-tile__button' ).count() ) > 0;
			expect( hasButton, `company ${ i + 1 }: button should match accordion` ).toBe( hasAccordion );
		}
	} );

	test( 'the Case Study button opens the popup with the accordion expanded; the accordion toggles', async ( { page }, testInfo ) => {
		await page.goto( '/portfolio/' );
		const company = page.locator( '.astara-portfolio-company:has(.astara-portfolio-tile__button)' ).first();
		test.skip( ( await company.count() ) === 0, 'no company has Case Study text right now' );
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
		const company = page.locator( '.astara-portfolio-company:has(.astara-portfolio-modal__accordion-toggle)' ).first();
		test.skip( ( await company.count() ) === 0, 'no company has Case Study text right now' );
		await company.locator( '.astara-portfolio-tile' ).click();
		await expect( company.locator( '.astara-portfolio-modal' ) ).toBeVisible();
		await expect( company.locator( '.astara-portfolio-modal__accordion-panel' ) ).toBeHidden();
	} );

	test( 'the Company Description shows in the popup when a company has one', async ( { page }, testInfo ) => {
		test.skip( testInfo.project.name !== 'desktop', 'popup is desktop only' );
		await page.goto( '/portfolio/' );
		const company = page.locator( '.astara-portfolio-company:has(.astara-portfolio-modal__description)' ).first();
		test.skip( ( await company.count() ) === 0, 'no company has a description right now' );
		await company.locator( '.astara-portfolio-tile' ).click();
		const modal = company.locator( '.astara-portfolio-modal' );
		await expect( modal.locator( '.astara-portfolio-modal__description' ) ).toBeVisible();
		// Order: meta row, then description, then the Case Study accordion (if any).
		const order = await modal.evaluate( ( el ) => {
			const pos = ( sel ) => { const n = el.querySelector( sel ); return n ? n.getBoundingClientRect().top : null; };
			return { meta: pos( '.astara-portfolio-modal__meta' ), description: pos( '.astara-portfolio-modal__description' ), accordion: pos( '.astara-portfolio-modal__accordion' ) };
		} );
		if ( order.meta !== null ) {
			expect( order.description ).toBeGreaterThan( order.meta );
		}
		if ( order.accordion !== null ) {
			expect( order.accordion ).toBeGreaterThan( order.description );
		}
	} );
} );
