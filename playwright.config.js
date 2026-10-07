// Playwright config. Tests run against local wp-env by default; set
// E2E_BASE_URL to point them at staging (CI does this after each deploy).
const { defineConfig } = require( '@playwright/test' );
require( 'dotenv' ).config( { quiet: true } );

const baseURL = process.env.E2E_BASE_URL || 'http://localhost:8888';
const inCI = !! process.env.CI;

module.exports = defineConfig( {
	testDir: './tests/e2e',
	timeout: 60_000,
	retries: inCI ? 1 : 0,
	workers: inCI ? 2 : undefined,
	reporter: inCI ? [ [ 'github' ], [ 'html', { open: 'never' } ] ] : 'list',
	// First run with no baseline writes one instead of failing, so a new
	// platform (Linux CI vs. macOS) isn't red until baselines are committed.
	updateSnapshots: 'missing',
	expect: {
		toHaveScreenshot: { maxDiffPixelRatio: 0.03, animations: 'disabled' },
	},
	use: {
		baseURL,
		trace: 'retain-on-failure',
		screenshot: 'only-on-failure',
	},
	projects: [
		{ name: 'desktop', use: { browserName: 'chromium', viewport: { width: 1440, height: 900 } } },
		{ name: 'tablet', use: { browserName: 'chromium', viewport: { width: 768, height: 1024 } } },
		{ name: 'mobile', use: { browserName: 'chromium', viewport: { width: 375, height: 812 }, hasTouch: true } },
	],
} );
