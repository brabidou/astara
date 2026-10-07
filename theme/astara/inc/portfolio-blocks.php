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
	$case_study      = (string) get_post_meta( $company->ID, 'astara_case_study', true );
	$story           = trim( wp_strip_all_tags( $case_study ) ) ? $case_study : '<p>' . esc_html__( 'Case study coming soon.', 'astara' ) . '</p>';
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
	<div class="astara-portfolio-case__story"><?php echo astara_richtext_html( $story ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- astara_richtext_html() runs wp_kses_post(). ?></div>
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
 * type: one logo tile per company. A company with a case study PDF or link
 * (set in its Investment Details) also gets a "Case Study" button that opens
 * it in a new tab; without one, the tile is just the logo.
 *
 * @return string
 */
function astara_render_portfolio_directory() {
	$companies = get_posts(
		array(
			'post_type'      => 'astara_portfolio_co',
			'posts_per_page' => -1,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'ID'         => 'ASC',
			),
		)
	);

	if ( empty( $companies ) ) {
		return '';
	}

	ob_start();
	?>
	<div class="astara-portfolio-grid">
		<?php foreach ( $companies as $company ) : ?>
			<?php $case_study_url = astara_portfolio_case_study_url( $company ); ?>
			<div class="astara-portfolio-company">
				<div class="astara-portfolio-tile">
					<?php if ( has_post_thumbnail( $company ) ) : ?>
						<?php echo get_the_post_thumbnail( $company, 'medium', array( 'class' => 'astara-portfolio-tile__logo' ) ); ?>
					<?php endif; ?>
					<?php if ( $case_study_url ) : ?>
						<a class="astara-portfolio-tile__button" href="<?php echo esc_url( $case_study_url ); ?>" target="_blank" rel="noopener noreferrer" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: portfolio company name */ __( 'Case study for %s (opens in a new tab)', 'astara' ), $company->post_title ) ); ?>">
							<?php esc_html_e( 'Case Study', 'astara' ); ?>
						</a>
					<?php endif; ?>
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
	if ( ! ( $company instanceof WP_Post ) ) {
		$company = astara_editor_preview_post( 'astara_portfolio_co' );
	}

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
