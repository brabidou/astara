<?php
/**
 * Title: Accordion Item
 * Slug: astara/accordion-item
 * Categories: astara
 * Description: A single expandable item (matches the "+" toggles on the Team page), built with the Interactivity API — no custom JS beyond the shared store.
 *
 * @package Astara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="astara-accordion" data-wp-interactive="astara/accordion" data-wp-context='{ "isOpen": false }'>
	<h3 class="astara-accordion__heading">
		<button type="button" class="astara-accordion__trigger" data-wp-on--click="actions.toggle" data-wp-bind--aria-expanded="context.isOpen">
			<span class="astara-accordion__label"><?php esc_html_e( 'Accordion item title', 'astara' ); ?></span>
			<span class="astara-accordion__icon" data-wp-class--is-open="context.isOpen" aria-hidden="true"></span>
		</button>
	</h3>
	<div class="astara-accordion__panel" data-wp-bind--hidden="!context.isOpen">
		<p><?php esc_html_e( 'Expandable content goes here.', 'astara' ); ?></p>
	</div>
</div>
