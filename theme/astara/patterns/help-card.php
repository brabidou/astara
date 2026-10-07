<?php
/**
 * Title: Help Card
 * Slug: astara/help-card
 * Categories: astara
 * Description: A titled card with copy and a button, for the How We Help section.
 *
 * @package Astara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!-- wp:group {"className":"astara-help-card","layout":{"type":"constrained"}} -->
<div class="wp-block-group astara-help-card">
	<!-- wp:heading {"level":3,"className":"astara-help-card__title"} -->
	<h3 class="wp-block-heading astara-help-card__title">Card title</h3>
	<!-- /wp:heading -->
	<!-- wp:paragraph {"className":"astara-body"} -->
	<p class="astara-body">A short description of how Astara helps.</p>
	<!-- /wp:paragraph -->
	<!-- wp:buttons -->
	<div class="wp-block-buttons">
		<!-- wp:button {"className":"astara-btn-outline"} -->
		<div class="wp-block-button astara-btn-outline"><a class="wp-block-button__link wp-element-button" href="#">Button label</a></div>
		<!-- /wp:button -->
	</div>
	<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
