<?php
/**
 * Accessibility helpers.
 *
 * @package astara
 */

defined( 'ABSPATH' ) || exit;

/**
 * Every page needs exactly one H1. Several designs have no visible page title,
 * so when a page's content has none, add a visually hidden one for screen readers.
 *
 * @param string $content Post content.
 * @return string
 */
function astara_ensure_page_h1( $content ) {
	if ( is_admin() || ! is_page() || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	if ( false !== stripos( $content, '<h1' ) ) {
		return $content;
	}
	return '<h1 class="screen-reader-text">' . esc_html( get_the_title() ) . '</h1>' . $content;
}
add_filter( 'the_content', 'astara_ensure_page_h1', 5 );
