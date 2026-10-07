<?php
/**
 * Search and social metadata: the home page title, a description for every page,
 * a favicon, and basic structured data.
 *
 * Works with or without an SEO plugin. When Yoast SEO (or another SEO plugin) is
 * active, the plugin prints the tags and this file only supplies defaults for
 * what the editor hasn't set (via Yoast's filters). Without a plugin, the theme
 * prints a minimal set itself. A description or title entered in the SEO plugin
 * always wins over the defaults below.
 *
 * @package Astara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Whether a known SEO plugin is handling the page's meta tags.
 *
 * @return bool
 */
function astara_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' );
}

/**
 * Default meta descriptions, keyed by page slug ('home' for the front page).
 * Search engines show roughly the first 155 characters. The home and strategy
 * wording comes from the current astaracapital.com.
 *
 * @return array<string, string>
 */
function astara_seo_descriptions() {
	return array(
		'home'        => 'Astara is an integrated team of investors and operators that has worked together for years. With over 100 years of cumulative experience in the middle market, the members of the Astara team have participated in control equity investments and held C-level positions through every phase of the business cycle.',
		'team'        => 'Meet the Astara Capital Partners team: investors and operators who work side by side with management to drive long-term success.',
		'strategy'    => 'Astara invests in middle-market companies where our financial, operational, and strategic resources combine to build sustainable value and deliver exceptional returns to our investors.',
		'portfolio'   => 'Explore the companies Astara Capital Partners has invested in, across building products, residential services, packaging, food, water infrastructure and more.',
		'news-media'  => 'Updates, insights, and press from Astara Capital Partners: company announcements, press releases and news coverage.',
		'news-archive' => 'Browse every Astara Capital Partners news item and press release by topic, year and month.',
		'contact'     => 'Get in touch with Astara Capital Partners in New York for new deals and general inquiries.',
	);
}

/**
 * The default description for the page being viewed, or '' when there isn't one.
 *
 * @return string
 */
function astara_seo_default_description() {
	$descriptions = astara_seo_descriptions();

	if ( is_front_page() ) {
		return $descriptions['home'];
	}

	if ( is_page() ) {
		$slug = get_post_field( 'post_name', get_queried_object_id() );
		return isset( $descriptions[ $slug ] ) ? $descriptions[ $slug ] : '';
	}

	return '';
}

/**
 * Home page title: just the site name ("Astara Capital Partners"), not
 * "Home - Astara Capital Partners". Other pages keep "Page - Site name".
 *
 * @param string $title Title computed by WordPress or the SEO plugin.
 * @return string
 */
function astara_seo_home_title( $title ) {
	if ( is_front_page() && ! get_post_meta( get_queried_object_id(), '_yoast_wpseo_title', true ) ) {
		return wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
	}

	return $title;
}
add_filter( 'pre_get_document_title', 'astara_seo_home_title_core', 20 );

/**
 * Core title tag, used when no SEO plugin is handling it.
 *
 * @param string $title Pre-filtered title ('' to let core build it).
 * @return string
 */
function astara_seo_home_title_core( $title ) {
	if ( astara_seo_plugin_active() ) {
		return $title;
	}

	return astara_seo_home_title( $title ) ?: $title;
}

/**
 * Yoast: fall back to our description when none was set for the page.
 *
 * @param string $description Description from Yoast.
 * @return string
 */
function astara_seo_fallback_description( $description ) {
	return '' !== trim( (string) $description ) ? $description : astara_seo_default_description();
}
add_filter( 'wpseo_metadesc', 'astara_seo_fallback_description' );
add_filter( 'wpseo_opengraph_desc', 'astara_seo_fallback_description' );
add_filter( 'wpseo_twitter_description', 'astara_seo_fallback_description' );
add_filter( 'wpseo_title', 'astara_seo_home_title' );
add_filter( 'wpseo_opengraph_title', 'astara_seo_home_title' );

/**
 * The favicon files shipped with the theme, as [ size, file ].
 *
 * @return array<int, array{0: string, 1: string}>
 */
function astara_seo_icons() {
	return array(
		array( '32x32', 'favicon-32x32.jpg' ),
		array( '192x192', 'favicon-192x192.jpg' ),
	);
}

/**
 * Favicon fallback. WordPress's own Site Icon (Settings → General) wins; this
 * only prints when none is set, so the site always has an icon. Replace the
 * files in assets/img/icons/ or upload a Site Icon to change it.
 */
function astara_seo_favicon() {
	if ( has_site_icon() ) {
		return;
	}

	$base = ASTARA_THEME_URI . '/assets/img/icons/';
	foreach ( astara_seo_icons() as $icon ) {
		printf( '<link rel="icon" href="%1$s" sizes="%2$s" type="image/jpeg" />' . "\n", esc_url( $base . $icon[1] ), esc_attr( $icon[0] ) );
	}
	printf( '<link rel="apple-touch-icon" href="%s" />' . "\n", esc_url( $base . 'favicon-180x180.jpg' ) );
}
add_action( 'wp_head', 'astara_seo_favicon', 5 );

/**
 * Minimal tags for when no SEO plugin is active: description, Open Graph, and
 * Organization/WebSite structured data on the home page.
 */
function astara_seo_head() {
	if ( astara_seo_plugin_active() ) {
		return;
	}

	$description = astara_seo_default_description();
	$title       = is_front_page() ? wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) : wp_get_document_title();
	$url         = is_singular() ? get_permalink() : home_url( '/' );

	if ( $description ) {
		printf( '<meta name="description" content="%s" />' . "\n", esc_attr( $description ) );
	}

	printf( '<meta property="og:site_name" content="%s" />' . "\n", esc_attr( get_bloginfo( 'name' ) ) );
	printf( '<meta property="og:type" content="%s" />' . "\n", is_front_page() ? 'website' : 'article' );
	printf( '<meta property="og:title" content="%s" />' . "\n", esc_attr( $title ) );
	printf( '<meta property="og:url" content="%s" />' . "\n", esc_url( $url ) );
	if ( $description ) {
		printf( '<meta property="og:description" content="%s" />' . "\n", esc_attr( $description ) );
	}
	echo '<meta name="twitter:card" content="summary" />' . "\n";

	if ( is_front_page() ) {
		$same_as = array_values( array_filter( array( astara_site_setting( 'linkedin_url' ), astara_site_setting( 'x_url' ) ) ) );
		$graph   = array(
			'@context' => 'https://schema.org',
			'@graph'   => array(
				array(
					'@type'  => 'Organization',
					'@id'    => home_url( '/#organization' ),
					'name'   => get_bloginfo( 'name' ),
					'url'    => home_url( '/' ),
					'logo'   => ASTARA_THEME_URI . '/assets/img/icons/favicon-192x192.jpg',
					'sameAs' => $same_as,
				),
				array(
					'@type'     => 'WebSite',
					'@id'       => home_url( '/#website' ),
					'url'       => home_url( '/' ),
					'name'      => get_bloginfo( 'name' ),
					'publisher' => array( '@id' => home_url( '/#organization' ) ),
				),
			),
		);
		echo '<script type="application/ld+json">' . wp_json_encode( $graph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON from wp_json_encode().
	}
}
add_action( 'wp_head', 'astara_seo_head', 6 );
