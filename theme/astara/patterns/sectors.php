<?php
/**
 * Title: Sectors and Transaction Types
 * Slug: astara/sectors
 * Categories: astara
 * Description: Three photos above two heading + arrow-list rows.
 *
 * @package Astara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!-- wp:group {"className":"astara-sectors","layout":{"type":"constrained","contentSize":"1552px"}} -->
<div class="wp-block-group astara-sectors">
	<!-- wp:group {"className":"astara-sectors__photos","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
	<div class="wp-block-group astara-sectors__photos">
		<!-- wp:image {"className":"astara-sectors__photo","sizeSlug":"large"} -->
		<figure class="wp-block-image size-large astara-sectors__photo"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/home/transaction-1.jpg' ) ); ?>" alt="Wood products at a portfolio company facility"/></figure>
		<!-- /wp:image -->

		<!-- wp:image {"className":"astara-sectors__photo","sizeSlug":"large"} -->
		<figure class="wp-block-image size-large astara-sectors__photo"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/home/transaction-2.jpg' ) ); ?>" alt="Warehouse distribution operations"/></figure>
		<!-- /wp:image -->

		<!-- wp:image {"className":"astara-sectors__photo","sizeSlug":"large"} -->
		<figure class="wp-block-image size-large astara-sectors__photo"><img src="<?php echo esc_url( get_theme_file_uri( 'assets/img/home/transaction-3.jpg' ) ); ?>" alt="Packaging materials in production"/></figure>
		<!-- /wp:image -->
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"className":"astara-sectors__row","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
	<div class="wp-block-group astara-sectors__row">
		<!-- wp:group {"className":"astara-sectors__intro","layout":{"type":"constrained"}} -->
		<div class="wp-block-group astara-sectors__intro">
			<!-- wp:heading {"level":2,"className":"astara-section-heading"} -->
			<h2 class="wp-block-heading astara-section-heading">Sectors</h2>
			<!-- /wp:heading -->
			<!-- wp:paragraph {"className":"astara-body"} -->
			<p class="astara-body">Astara invests in manufacturing, distribution, and related services across these industries.</p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->

		<!-- wp:list {"className":"astara-arrow-list"} -->
		<ul class="wp-block-list astara-arrow-list">
			<!-- wp:list-item -->
			<li>Building Products &amp; Construction Services</li>
			<!-- /wp:list-item -->
			<!-- wp:list-item -->
			<li>Home and Facility Services</li>
			<!-- /wp:list-item -->
			<!-- wp:list-item -->
			<li>IT Services</li>
			<!-- /wp:list-item -->
			<!-- wp:list-item -->
			<li>Packaging</li>
			<!-- /wp:list-item -->
			<!-- wp:list-item -->
			<li>Water Infrastructure</li>
			<!-- /wp:list-item -->
			<!-- wp:list-item -->
			<li>Utility Services</li>
			<!-- /wp:list-item -->
		</ul>
		<!-- /wp:list -->
	</div>
	<!-- /wp:group -->

	<!-- wp:group {"className":"astara-sectors__row","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
	<div class="wp-block-group astara-sectors__row">
		<!-- wp:group {"className":"astara-sectors__intro","layout":{"type":"constrained"}} -->
		<div class="wp-block-group astara-sectors__intro">
			<!-- wp:heading {"level":2,"className":"astara-section-heading"} -->
			<h2 class="wp-block-heading astara-section-heading">Transaction Types</h2>
			<!-- /wp:heading -->
		</div>
		<!-- /wp:group -->

		<!-- wp:list {"className":"astara-arrow-list"} -->
		<ul class="wp-block-list astara-arrow-list">
			<!-- wp:list-item -->
			<li>Buyouts</li>
			<!-- /wp:list-item -->
			<!-- wp:list-item -->
			<li>First Institutional Capital</li>
			<!-- /wp:list-item -->
			<!-- wp:list-item -->
			<li>Corporate Carveouts &amp; Divestitures</li>
			<!-- /wp:list-item -->
			<!-- wp:list-item -->
			<li>Take-Private Transactions</li>
			<!-- /wp:list-item -->
			<!-- wp:list-item -->
			<li>Recapitalizations</li>
			<!-- /wp:list-item -->
			<!-- wp:list-item -->
			<li>Restructurings &amp; Turnarounds</li>
			<!-- /wp:list-item -->
		</ul>
		<!-- /wp:list -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
