<?php
/**
 * Title: Criteria Item
 * Slug: astara/criteria-item
 * Categories: astara
 * Description: A label and value pair, for the Investment Criteria row.
 *
 * @package Astara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!-- wp:group {"className":"astara-criteria__item","layout":{"type":"constrained"}} -->
<div class="wp-block-group astara-criteria__item">
	<!-- wp:paragraph {"className":"astara-criteria__label"} -->
	<p class="astara-criteria__label">Label</p>
	<!-- /wp:paragraph -->
	<!-- wp:paragraph {"className":"astara-criteria__value"} -->
	<p class="astara-criteria__value">Value</p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
