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
 * Enqueue the theme's global stylesheet — only what theme.json can't express.
 */
function astara_enqueue_assets() {
	wp_enqueue_style(
		'astara-global',
		ASTARA_THEME_URI . '/assets/css/global.css',
		array(),
		ASTARA_VERSION
	);

	if ( is_front_page() ) {
		wp_enqueue_style(
			'astara-home',
			ASTARA_THEME_URI . '/assets/css/blocks/home.css',
			array( 'astara-global' ),
			ASTARA_VERSION
		);
	}
}
add_action( 'wp_enqueue_scripts', 'astara_enqueue_assets' );
