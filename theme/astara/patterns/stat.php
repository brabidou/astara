<?php
/**
 * Title: Stat
 * Slug: astara/stat
 * Categories: astara
 * Description: A large number with a label, for the Stats Row.
 *
 * @package Astara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!-- wp:group {"className":"astara-stat","layout":{"type":"constrained"}} -->
<div class="wp-block-group astara-stat">
	<!-- wp:paragraph {"className":"astara-stat__number"} -->
	<p class="astara-stat__number">0</p>
	<!-- /wp:paragraph -->
	<!-- wp:paragraph {"className":"astara-stat__label"} -->
	<p class="astara-stat__label">Stat label</p>
	<!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
