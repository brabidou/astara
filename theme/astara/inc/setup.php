<?php
/**
 * Theme support and block-theme setup.
 *
 * @package Astara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register theme support.
 */
function astara_setup() {
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );

	// Without this, 'editor-styles' support does nothing — the editor iframe
	// only picks up theme.json styles, not our own stylesheets.
	add_editor_style( array( 'assets/css/global.css', 'assets/css/blocks/home.css', 'assets/css/blocks/team.css', 'assets/css/blocks/contact.css', 'assets/css/blocks/portfolio.css', 'assets/css/blocks/news.css' ) );

	load_theme_textdomain( 'astara', ASTARA_THEME_DIR . '/languages' );
}
add_action( 'after_setup_theme', 'astara_setup' );

/**
 * Register the pattern category the theme's patterns are filed under, so
 * editors find them together in the inserter's Patterns tab.
 */
function astara_register_pattern_category() {
	register_block_pattern_category(
		'astara',
		array( 'label' => __( 'Astara', 'astara' ) )
	);
}
add_action( 'init', 'astara_register_pattern_category' );

/**
 * Hide the Template dropdown in Post Attributes for Team Members and
 * Portfolio Companies. It only listed the theme's own single-* templates,
 * which WordPress already applies automatically — Order is the only
 * attribute editors need.
 *
 * @return array
 */
function astara_no_page_templates() {
	return array();
}
add_filter( 'theme_astara_team_member_templates', 'astara_no_page_templates' );
add_filter( 'theme_astara_portfolio_co_templates', 'astara_no_page_templates' );
