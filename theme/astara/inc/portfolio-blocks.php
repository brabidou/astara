<?php
/**
 * Server-rendered dynamic blocks for Portfolio Companies. No block.json/JS
 * needed — inserted via a raw block comment in post_content.
 *
 * @package Astara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Portfolio Directory and Portfolio Company Detail blocks.
 */
function astara_register_portfolio_blocks() {
	register_block_type(
		'astara/portfolio-directory',
		array(
			'render_callback' => 'astara_render_portfolio_directory',
		)
	);

	register_block_type(
		'astara/portfolio-company-detail',
		array(
			'render_callback' => 'astara_render_portfolio_company_detail',
		)
	);
}
add_action( 'init', 'astara_register_portfolio_blocks' );

/**
 * Render a portfolio company's case study content — logo, name, investment
 * date, industry, story, website link. Shared between the directory's popup
 * modal and the company's own single page.
 *
 * @param WP_Post $company  Portfolio company post.
 * @param string  $name_tag Tag for the name heading — h3 in the modal, h1 on the single page.
 * @param string  $name_id  Optional id attribute for the name heading, so a modal's
 *                          aria-labelledby has something real to point at.
 * @return string
 */
function astara_render_portfolio_company_case_study( $company, $name_tag = 'h3', $name_id = '' ) {
	$industry        = $company->post_excerpt;
	$story           = trim( wp_strip_all_tags( $company->post_content ) ) ? $company->post_content : __( 'Case study coming soon.', 'astara' );
	$investment_date = get_post_meta( $company->ID, 'astara_investment_date', true );
	$website_url     = get_post_meta( $company->ID, 'astara_website_url', true );
	$id_attr         = $name_id ? ' id="' . esc_attr( $name_id ) . '"' : '';

	ob_start();
	?>
	<?php if ( has_post_thumbnail( $company ) ) : ?>
		<div class="astara-portfolio-case__logo"><?php echo get_the_post_thumbnail( $company, 'medium' ); ?></div>
	<?php endif; ?>
	<<?php echo tag_escape( $name_tag ); ?><?php echo $id_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from esc_attr() above. ?> class="astara-portfolio-case__name">
		<?php
		echo esc_html(
			sprintf(
				/* translators: %s: portfolio company name */
				__( '%s Case Study', 'astara' ),
				$company->post_title
			)
		);
		?>
	</<?php echo tag_escape( $name_tag ); ?>>
	<?php if ( $investment_date || $industry ) : ?>
		<div class="astara-portfolio-case__meta">
			<?php if ( $investment_date ) : ?>
				<div>
					<p class="astara-portfolio-case__meta-label"><?php esc_html_e( 'Investment Date', 'astara' ); ?></p>
					<p class="astara-portfolio-case__meta-value"><?php echo esc_html( $investment_date ); ?></p>
				</div>
			<?php endif; ?>
			<?php if ( $industry ) : ?>
				<div>
					<p class="astara-portfolio-case__meta-label"><?php esc_html_e( 'Industry', 'astara' ); ?></p>
					<p class="astara-portfolio-case__meta-value"><?php echo esc_html( $industry ); ?></p>
				</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>
	<div class="astara-portfolio-case__story"><?php echo apply_filters( 'the_content', $story ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- 'the_content' is WordPress core's own filter; content is admin-authored. ?></div>
	<?php if ( $website_url ) : ?>
		<a class="astara-portfolio-case__visit" href="<?php echo esc_url( $website_url ); ?>" target="_blank" rel="noopener noreferrer">
			<?php
			echo esc_html(
				sprintf(
					/* translators: %s: portfolio company name */
					__( 'Visit %s', 'astara' ),
					$company->post_title
				)
			);
			?>
		</a>
	<?php endif; ?>
	<?php
	return ob_get_clean();
}

/**
 * Render the portfolio grid, queried live from the Portfolio Company post
 * type. Companies with case study content get a "Case Study" badge whose
 * "+" is a real link to that company's own page (crawlable, works with JS
 * off) that also opens a popup in place via the Interactivity API when JS
 * is available. Companies with no content render as a plain logo tile.
 *
 * @return string
 */
function astara_render_portfolio_directory() {
	$companies = get_posts(
		array(
			'post_type'      => 'astara_portfolio_co',
			'posts_per_page' => -1,
			'orderby'        => 'menu_order',
			'order'          => 'ASC',
		)
	);

	if ( empty( $companies ) ) {
		return '';
	}

	ob_start();
	?>
	<div class="astara-portfolio-grid">
		<?php foreach ( $companies as $company ) : ?>
			<?php $modal_id = 'portfolio-modal-' . $company->ID . '-' . wp_unique_id(); ?>
			<div class="astara-portfolio-company" data-wp-interactive="astara/modal" data-wp-context='{ "isOpen": false }' data-wp-on-window--keydown="actions.closeOnEscape">
				<a href="<?php echo esc_url( get_permalink( $company ) ); ?>" class="astara-portfolio-tile" data-wp-on--click="actions.open" aria-haspopup="dialog" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: portfolio company name */ __( 'View case study for %s', 'astara' ), $company->post_title ) ); ?>">
					<?php if ( has_post_thumbnail( $company ) ) : ?>
						<?php echo get_the_post_thumbnail( $company, 'medium', array( 'class' => 'astara-portfolio-tile__logo' ) ); ?>
					<?php endif; ?>
					<span class="astara-portfolio-tile__badge"><?php esc_html_e( 'Case Study', 'astara' ); ?></span>
				</a>

				<div class="astara-portfolio-modal" data-wp-class--is-open="context.isOpen" data-wp-bind--hidden="!context.isOpen" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr( $modal_id ); ?>">
					<div class="astara-portfolio-modal__backdrop" data-wp-on--click="actions.close"></div>
					<div class="astara-portfolio-modal__panel">
						<button type="button" class="astara-portfolio-modal__close" data-wp-on--click="actions.close" aria-label="<?php esc_attr_e( 'Close', 'astara' ); ?>">&times;</button>
						<?php echo str_replace( 'astara-portfolio-case', 'astara-portfolio-modal', astara_render_portfolio_company_case_study( $company, 'h3', $modal_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- astara_render_portfolio_company_case_study() already escapes its own output. ?>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Render a portfolio company's case study on their own single page — used by
 * templates/single-astara_portfolio_co.html. Same content the popup shows.
 *
 * @return string
 */
function astara_render_portfolio_company_detail() {
	$company = get_queried_object();

	if ( ! ( $company instanceof WP_Post ) || 'astara_portfolio_co' !== $company->post_type ) {
		return '';
	}

	ob_start();
	?>
	<div class="astara-portfolio-detail">
		<a class="astara-portfolio-detail__back" href="<?php echo esc_url( home_url( '/portfolio/' ) ); ?>">&larr; <?php esc_html_e( 'Back to Portfolio', 'astara' ); ?></a>
		<?php echo astara_render_portfolio_company_case_study( $company, 'h1' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped inside. ?>
	</div>
	<?php
	return ob_get_clean();
}
