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
 * Same-page anchor links (e.g. the menu's "Investment Criteria" and "Strategy",
 * which point at sections of the home page).
 *
 * 1. Clicks: Safari on a Mac keeps trackpad momentum running after a quick scroll.
 *    If a #link is clicked while the page is still coasting, Safari jumps to the
 *    section and the leftover momentum then snaps the page back. So for links to
 *    a section on the current page we stop the momentum (freeze scrolling for one
 *    frame), scroll to the section ourselves and update the address bar.
 * 2. Page load: if the page opens on a #section (e.g. Strategy clicked from another
 *    page) and fonts/images shift the layout while loading, re-align once loaded,
 *    unless the visitor has already scrolled on their own.
 */
function astara_anchor_links_script() {
	?>
	<script>
	( function () {
		function targetFor( hash ) {
			if ( ! hash || hash.length < 2 ) {
				return null;
			}
			try {
				return document.getElementById( decodeURIComponent( hash.slice( 1 ) ) );
			} catch ( e ) {
				return null;
			}
		}

		// 1. Same-page #links.
		document.addEventListener( 'click', function ( event ) {
			if ( event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey ) {
				return;
			}
			var link = event.target.closest && event.target.closest( 'a[href*="#"]' );
			if ( ! link || ( link.target && link.target !== '_self' ) ) {
				return;
			}
			var url = new URL( link.href, location.href );
			if ( url.origin !== location.origin || url.pathname !== location.pathname || url.search !== location.search ) {
				return; // a different page: let the browser load it normally
			}
			var el = targetFor( url.hash );
			if ( ! el ) {
				return;
			}
			event.preventDefault();
			var root = document.documentElement;
			var previous = root.style.overflow;
			root.style.overflow = 'hidden'; // cancels any momentum scroll still running
			requestAnimationFrame( function () {
				root.style.overflow = previous;
				el.scrollIntoView( { block: 'start' } );
				if ( location.hash !== url.hash ) {
					history.pushState( null, '', url.hash );
				}
			} );
		} );

		// 2. Page opened on a #section: re-align after late layout shifts.
		if ( ! targetFor( location.hash ) ) {
			return;
		}
		var userMoved = false;
		[ 'wheel', 'touchstart', 'keydown', 'mousedown' ].forEach( function ( type ) {
			window.addEventListener( type, function () { userMoved = true; }, { once: true, passive: true } );
		} );
		function settle() {
			var el = targetFor( location.hash );
			if ( ! userMoved && el && Math.abs( el.getBoundingClientRect().top ) > 2 ) {
				el.scrollIntoView( { block: 'start' } );
			}
		}
		window.addEventListener( 'load', function () {
			settle();
			if ( document.fonts && document.fonts.ready ) {
				document.fonts.ready.then( settle );
			}
			setTimeout( settle, 400 );
		} );
	}() );
	</script>
	<?php
}
add_action( 'wp_footer', 'astara_anchor_links_script', 99 );
