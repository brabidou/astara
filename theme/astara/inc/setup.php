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

/**
 * Keep the inserter's Patterns tab to the theme's own patterns: turn off
 * WordPress's bundled and remote (patterns directory) patterns, then
 * unregister anything else a plugin has added.
 */
function astara_limit_patterns() {
	remove_theme_support( 'core-block-patterns' );
	add_filter( 'should_load_remote_block_patterns', '__return_false' );
}
add_action( 'after_setup_theme', 'astara_limit_patterns' );

/**
 * Unregister every pattern and pattern category that isn't Astara's.
 * Runs late on init so plugin-registered patterns are already in place.
 */
function astara_unregister_other_patterns() {
	$patterns = WP_Block_Patterns_Registry::get_instance();
	foreach ( $patterns->get_all_registered() as $pattern ) {
		if ( 0 !== strpos( $pattern['name'], 'astara/' ) ) {
			unregister_block_pattern( $pattern['name'] );
		}
	}

	$categories = WP_Block_Pattern_Categories_Registry::get_instance();
	foreach ( $categories->get_all_registered() as $category ) {
		if ( 'astara' !== $category['name'] ) {
			unregister_block_pattern_category( $category['name'] );
		}
	}
}
add_action( 'init', 'astara_unregister_other_patterns', 100 );

/**
 * The standalone Strategy page was retired; its content lives in the home page's
 * "How We Work" section. Send old links (and search results) there.
 */
function astara_redirect_strategy_page() {
	if ( ! is_404() ) {
		return;
	}
	$path = trim( (string) wp_parse_url( isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '', PHP_URL_PATH ), '/' );
	if ( 'strategy' === $path ) {
		wp_safe_redirect( home_url( '/#how-we-work' ), 301 );
		exit;
	}
}
add_action( 'template_redirect', 'astara_redirect_strategy_page' );

/**
 * Menu links to home page sections are stored as "/#section" so they work from
 * every page. On the home page itself, output them as plain "#section" hash links.
 *
 * @param string $block_content Rendered block HTML.
 * @return string
 */
function astara_home_hash_links( $block_content ) {
	if ( ! is_front_page() ) {
		return $block_content;
	}
	return str_replace( 'href="/#', 'href="#', $block_content );
}
add_filter( 'render_block_core/navigation-link', 'astara_home_hash_links' );

/**
 * Image blocks that point at a theme file (the home page photos) have no
 * attachment ID, so core can't add width/height for them. Add the attributes
 * from the file itself: without them the browser reserves no space, the page
 * reflows as each photo arrives, and a #section the browser already jumped to
 * ends up further down (most visibly on phones and in narrow windows).
 *
 * @param string $block_content Rendered block HTML.
 * @return string
 */
function astara_theme_image_dimensions( $block_content ) {
	$theme_uri = ASTARA_THEME_URI . '/';
	if ( false === strpos( $block_content, $theme_uri ) ) {
		return $block_content;
	}

	$processor = new WP_HTML_Tag_Processor( $block_content );
	while ( $processor->next_tag( 'img' ) ) {
		$src = (string) $processor->get_attribute( 'src' );
		if ( 0 !== strpos( $src, $theme_uri ) || null !== $processor->get_attribute( 'width' ) ) {
			continue;
		}
		$file = ASTARA_THEME_DIR . '/' . substr( strtok( $src, '?' ), strlen( $theme_uri ) );
		$size = file_exists( $file ) ? wp_getimagesize( $file ) : false;
		if ( $size ) {
			$processor->set_attribute( 'width', (string) $size[0] );
			$processor->set_attribute( 'height', (string) $size[1] );
		}
	}

	return $processor->get_updated_html();
}
add_filter( 'render_block_core/image', 'astara_theme_image_dimensions' );
