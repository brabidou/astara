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
 * Enqueue the theme's global stylesheet — only what theme.json can't express.
 */
function astara_enqueue_assets() {
	wp_enqueue_style(
		'astara-global',
		ASTARA_THEME_URI . '/assets/css/global.css',
		array(),
		astara_asset_version( '/assets/css/global.css' )
	);

	if ( is_front_page() ) {
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
}
add_action( 'wp_enqueue_scripts', 'astara_enqueue_assets' );

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

	wp_register_script_module(
		'astara-news-filter',
		ASTARA_THEME_URI . '/assets/js/news-filter.js',
		array( '@wordpress/interactivity' ),
		astara_asset_version( '/assets/js/news-filter.js' )
	);
	wp_enqueue_script_module( 'astara-news-filter' );
}
add_action( 'wp_enqueue_scripts', 'astara_register_script_modules' );
