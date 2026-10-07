<?php
/**
 * Title: Home Hero
 * Slug: astara/hero
 * Categories: astara
 * Description: Large headline with a photo, intro copy and two buttons.
 *
 * @package Astara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!-- wp:group {"className":"astara-hero","layout":{"type":"constrained","contentSize":"1552px"}} -->
<div class="wp-block-group astara-hero">
	<!-- wp:heading {"level":1,"className":"astara-hero__title"} -->
	<h1 class="wp-block-heading astara-hero__title">Investors and Operators Partnering to Build Great Businesses</h1>
	<!-- /wp:heading -->

	<!-- wp:group {"className":"astara-hero__row","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
	<div class="wp-block-group astara-hero__row">
		<!-- wp:image {"className":"astara-hero__image","sizeSlug":"large"} -->
		<figure class="wp-block-image size-large astara-hero__image"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/home/hero-team.jpg' ) ); ?>" alt="Astara team member on site at a manufacturing partner"/></figure>
		<!-- /wp:image -->

		<!-- wp:group {"className":"astara-hero__copy","layout":{"type":"constrained"}} -->
		<div class="wp-block-group astara-hero__copy">
			<!-- wp:heading {"level":2,"className":"astara-hero__heading"} -->
			<h2 class="wp-block-heading astara-hero__heading">Astara is a trusted capital partner dedicated to transforming high-quality companies into industry leaders.</h2>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"className":"astara-body"} -->
			<p class="astara-body">We work with companies and owners who understand that selecting the right partner is one of the most important decisions they will ever make.</p>
			<!-- /wp:paragraph -->

			<!-- wp:buttons -->
			<div class="wp-block-buttons">
				<!-- wp:button {"className":"astara-btn-solid"} -->
				<div class="wp-block-button astara-btn-solid"><a class="wp-block-button__link wp-element-button" href="#">View Case Studies</a></div>
				<!-- /wp:button -->

				<!-- wp:button {"className":"astara-btn-outline"} -->
				<div class="wp-block-button astara-btn-outline"><a class="wp-block-button__link wp-element-button" href="#">Contact</a></div>
				<!-- /wp:button -->
			</div>
			<!-- /wp:buttons -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->

