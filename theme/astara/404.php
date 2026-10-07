<?php
/**
 * 404 template — a plain PHP template, so it isn't listed or editable in the
 * Site Editor. WordPress prefers this file over the block theme's index.html for
 * missing pages. Styled by assets/css/blocks/404.css (loaded on 404s only).
 *
 * It renders the same block markup the other templates use, so the header,
 * footer and layout match the rest of the site.
 *
 * @package Astara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$astara_404_content = sprintf(
	'<!-- wp:group {"tagName":"main","layout":{"type":"constrained"}} -->
<main class="wp-block-group">
	<!-- wp:group {"align":"full","className":"astara-404","layout":{"type":"constrained","contentSize":"1552px"}} -->
	<div class="wp-block-group alignfull astara-404">
		<!-- wp:paragraph {"className":"astara-eyebrow"} -->
		<p class="astara-eyebrow">%1$s</p>
		<!-- /wp:paragraph -->

		<!-- wp:heading {"level":1,"className":"astara-404__title"} -->
		<h1 class="wp-block-heading astara-404__title">%2$s</h1>
		<!-- /wp:heading -->

		<!-- wp:paragraph {"className":"astara-body astara-404__body"} -->
		<p class="astara-body astara-404__body">%3$s</p>
		<!-- /wp:paragraph -->

		<!-- wp:buttons -->
		<div class="wp-block-buttons">
			<!-- wp:button {"className":"astara-btn-solid"} -->
			<div class="wp-block-button astara-btn-solid"><a class="wp-block-button__link wp-element-button" href="%4$s">%5$s</a></div>
			<!-- /wp:button -->

			<!-- wp:button {"className":"astara-btn-outline"} -->
			<div class="wp-block-button astara-btn-outline"><a class="wp-block-button__link wp-element-button" href="%6$s">%7$s</a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->
	</div>
	<!-- /wp:group -->
</main>
<!-- /wp:group -->',
	esc_html__( '404', 'astara' ),
	esc_html__( "We can't find the page you're looking for.", 'astara' ),
	esc_html__( "It may have moved or no longer exists. Head back to the home page or get in touch and we'll point you in the right direction.", 'astara' ),
	esc_url( home_url( '/' ) ),
	esc_html__( 'Back to Home', 'astara' ),
	esc_url( home_url( '/contact/' ) ),
	esc_html__( 'Contact', 'astara' )
);

// Render the blocks first, before wp_head() prints, the way WordPress does for
// block templates: their layout styles are collected while rendering and have
// to be registered before the head is output.
$astara_404_header = do_blocks( '<!-- wp:template-part {"slug":"header","tagName":"header"} /-->' );
$astara_404_main   = do_blocks( $astara_404_content );
$astara_404_footer = do_blocks( '<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />
	<title><?php echo esc_html( wp_get_document_title() ); ?></title>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="wp-site-blocks">
	<?php
	echo $astara_404_header; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core block markup, no user input.
	echo $astara_404_main; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- every dynamic value is escaped above.
	echo $astara_404_footer; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- core block markup, no user input.
	?>
</div>
<?php wp_footer(); ?>
</body>
</html>
