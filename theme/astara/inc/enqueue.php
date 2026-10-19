<?php
/**
 * Global asset loading. Per-block styles are enqueued via wp_enqueue_block_style()
 * next to the block/pattern that uses them, not here.
 *
 * @package Astara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * File-modified-time based version string, so edits bust the browser cache
 * automatically instead of waiting on a manual ASTARA_VERSION bump.
 *
 * @param string $relative_path Path relative to the theme root, e.g. '/assets/css/global.css'.
 * @return string
 */
function astara_asset_version( $relative_path ) {
	$file = ASTARA_THEME_DIR . $relative_path;
	return file_exists( $file ) ? (string) filemtime( $file ) : ASTARA_VERSION;
}

/**
 * Whether the current singular post uses one of the home-section patterns
 * (hero, how we work, etc.) — those are styled by home.css, so it has to load
 * wherever an editor drops them, not just on the front page.
 *
 * @return bool
 */
function astara_has_home_sections() {
	if ( ! is_singular() ) {
		return false;
	}

	$content = (string) get_post_field( 'post_content', get_queried_object_id() );
	foreach ( array( 'astara-hero', 'astara-how-we-work', 'astara-how-we-help', 'astara-stats', 'astara-criteria', 'astara-sectors' ) as $marker ) {
		if ( false !== strpos( $content, $marker ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Enqueue the theme's global stylesheet — only what theme.json can't express.
 */
function astara_enqueue_assets() {
	wp_enqueue_style(
		'astara-global',
		ASTARA_THEME_URI . '/assets/css/global.css',
		array(),
		astara_asset_version( '/assets/css/global.css' )
	);

	if ( is_front_page() || astara_has_home_sections() ) {
		wp_enqueue_style(
			'astara-home',
			ASTARA_THEME_URI . '/assets/css/blocks/home.css',
			array( 'astara-global' ),
			astara_asset_version( '/assets/css/blocks/home.css' )
		);
	}

	if ( is_page( 'team' ) || is_singular( 'astara_team_member' ) ) {
		wp_enqueue_style(
			'astara-team',
			ASTARA_THEME_URI . '/assets/css/blocks/team.css',
			array( 'astara-global' ),
			astara_asset_version( '/assets/css/blocks/team.css' )
		);
	}

	if ( is_page( 'contact' ) ) {
		wp_enqueue_style(
			'astara-contact',
			ASTARA_THEME_URI . '/assets/css/blocks/contact.css',
			array( 'astara-global' ),
			astara_asset_version( '/assets/css/blocks/contact.css' )
		);
	}

	if ( is_page( 'portfolio' ) || is_singular( 'astara_portfolio_co' ) ) {
		wp_enqueue_style(
			'astara-portfolio',
			ASTARA_THEME_URI . '/assets/css/blocks/portfolio.css',
			array( 'astara-global' ),
			astara_asset_version( '/assets/css/blocks/portfolio.css' )
		);
	}

	if ( is_page( 'news-media' ) || is_page( 'news-archive' ) || is_singular( 'post' ) ) {
		wp_enqueue_style(
			'astara-news',
			ASTARA_THEME_URI . '/assets/css/blocks/news.css',
			array( 'astara-global' ),
			astara_asset_version( '/assets/css/blocks/news.css' )
		);
	}

	if ( is_404() ) {
		wp_enqueue_style(
			'astara-404',
			ASTARA_THEME_URI . '/assets/css/blocks/404.css',
			array( 'astara-global' ),
			astara_asset_version( '/assets/css/blocks/404.css' )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'astara_enqueue_assets' );

/**
 * Smooth scrolling for #section links (e.g. the menu's "Investment Criteria" and
 * "Strategy" on the home page), using smooth-scroll v16.1.3 by Chris Ferdinandi
 * (MIT, assets/js/lib/smooth-scroll.min.js). It only handles links to a section
 * of the current page; links to other pages load normally. Animating the scroll
 * also stops Safari's trackpad momentum from snapping the page back after a jump.
 */
function astara_enqueue_smooth_scroll() {
	wp_enqueue_script(
		'astara-smooth-scroll',
		ASTARA_THEME_URI . '/assets/js/lib/smooth-scroll.min.js',
		array(),
		'16.1.3',
		array( 'in_footer' => true )
	);
	wp_enqueue_script(
		'astara-smooth-scroll-init',
		ASTARA_THEME_URI . '/assets/js/smooth-scroll-init.js',
		array( 'astara-smooth-scroll' ),
		astara_asset_version( '/assets/js/smooth-scroll-init.js' ),
		array( 'in_footer' => true )
	);
}
add_action( 'wp_enqueue_scripts', 'astara_enqueue_smooth_scroll' );


/**
 * Register Interactivity API script modules.
 */
function astara_register_script_modules() {
	wp_register_script_module(
		'astara-accordion',
		ASTARA_THEME_URI . '/assets/js/accordion.js',
		array( '@wordpress/interactivity' ),
		astara_asset_version( '/assets/js/accordion.js' )
	);
	wp_enqueue_script_module( 'astara-accordion' );

	wp_register_script_module(
		'astara-team-modal',
		ASTARA_THEME_URI . '/assets/js/team-modal.js',
		array( '@wordpress/interactivity' ),
		astara_asset_version( '/assets/js/team-modal.js' )
	);
	wp_enqueue_script_module( 'astara-team-modal' );
}
add_action( 'wp_enqueue_scripts', 'astara_register_script_modules' );
