<?php
/**
 * Server-rendered dynamic blocks. No block.json/JS needed — these are
 * inserted via a raw block comment in post_content, not the visual inserter.
 *
 * @package Astara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register dynamic blocks.
 */
function astara_register_blocks() {
	register_block_type(
		'astara/team-directory',
		array(
			'render_callback' => 'astara_render_team_directory',
		)
	);

	register_block_type(
		'astara/team-member-detail',
		array(
			'render_callback' => 'astara_render_team_member_detail',
		)
	);
}
add_action( 'init', 'astara_register_blocks' );

/**
 * Render a team member's bio content — photo, name, role, bio, contact links.
 * Shared between the directory's popup modal and the member's own single page,
 * so the "+" link's destination (crawlable, works with JS off, opens in a new
 * tab) shows the same content the modal would have shown.
 *
 * @param WP_Post $member   Team member post.
 * @param string  $name_tag Tag for the name heading — h3 in the modal, h1 on the single page.
 * @param string  $name_id  Optional id attribute for the name heading, so a modal's
 *                          aria-labelledby has something real to point at.
 * @return string
 */
function astara_render_team_member_bio( $member, $name_tag = 'h3', $name_id = '' ) {
	$role         = $member->post_excerpt;
	$bio          = (string) get_post_meta( $member->ID, 'astara_bio', true );
	$linkedin_url = get_post_meta( $member->ID, 'astara_linkedin_url', true );
	$email        = get_post_meta( $member->ID, 'astara_email', true );
	$id_attr      = $name_id ? ' id="' . esc_attr( $name_id ) . '"' : '';

	ob_start();
	?>
	<?php if ( has_post_thumbnail( $member ) ) : ?>
		<div class="astara-team-bio__photo"><?php echo get_the_post_thumbnail( $member, 'medium' ); ?></div>
	<?php endif; ?>
	<<?php echo tag_escape( $name_tag ); ?><?php echo $id_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from esc_attr() above. ?> class="astara-team-bio__name"><?php echo esc_html( $member->post_title ); ?></<?php echo tag_escape( $name_tag ); ?>>
	<p class="astara-team-bio__role"><?php echo esc_html( $role ); ?></p>
	<div class="astara-team-bio__bio"><?php echo astara_richtext_html( $bio ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- astara_richtext_html() runs wp_kses_post(). ?></div>
	<?php if ( $email || $linkedin_url ) : ?>
		<div class="astara-team-bio__links">
			<?php if ( $email ) : ?>
				<a href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php esc_html_e( 'Email', 'astara' ); ?></a>
			<?php endif; ?>
			<?php if ( $linkedin_url ) : ?>
				<a href="<?php echo esc_url( $linkedin_url ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'LinkedIn', 'astara' ); ?></a>
			<?php endif; ?>
		</div>
	<?php endif; ?>
	<?php
	return ob_get_clean();
}

/**
 * Render the team grid, grouped by category, queried live from the
 * Team Member post type. Each card's "+" is a real link to that member's own
 * page (crawlable, works with JS off, "open in new tab" works) that also
 * opens a bio modal in place via the Interactivity API when JS is available.
 *
 * @return string
 */
function astara_render_team_directory() {
	$categories = get_terms(
		array(
			'taxonomy'   => 'astara_team_category',
			'hide_empty' => true,
			'orderby'    => 'term_id',
			'order'      => 'ASC',
		)
	);

	if ( is_wp_error( $categories ) || empty( $categories ) ) {
		return '';
	}

	ob_start();

	$lower_band_open = false;

	foreach ( $categories as $category ) {
		if ( ! $lower_band_open && 'investors' !== $category->slug ) {
			echo '<div class="astara-team-lower">';
			$lower_band_open = true;
		}

		$members = get_posts(
			array(
				'post_type'      => 'astara_team_member',
				'posts_per_page' => -1,
				'orderby'        => array(
					'menu_order' => 'ASC',
					'ID'         => 'ASC',
				),
				'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array(
						'taxonomy' => 'astara_team_category',
						'field'    => 'term_id',
						'terms'    => $category->term_id,
					),
				),
			)
		);

		if ( empty( $members ) ) {
			continue;
		}

		$grid_class = 'investors' === $category->slug ? 'astara-team-grid--wide' : 'astara-team-grid--narrow';
		?>
		<div class="astara-team-category">
			<h2 class="astara-section-heading astara-team-category__heading"><?php echo esc_html( $category->name ); ?></h2>
			<div class="astara-team-grid <?php echo esc_attr( $grid_class ); ?>">
				<?php foreach ( $members as $member ) : ?>
					<?php $modal_id = 'team-modal-' . $member->ID . '-' . wp_unique_id(); ?>
					<div class="astara-team-member" data-wp-interactive="astara/modal" data-wp-context='{ "isOpen": false }' data-wp-on-window--keydown="actions.closeOnEscape">
						<div class="astara-team-card">
							<?php if ( has_post_thumbnail( $member ) ) : ?>
								<div class="astara-team-card__photo"><?php echo get_the_post_thumbnail( $member, 'large' ); ?></div>
							<?php endif; ?>
							<div class="astara-team-card__info">
								<p class="astara-team-card__name"><?php echo esc_html( $member->post_title ); ?></p>
								<p class="astara-team-card__role"><?php echo esc_html( $member->post_excerpt ); ?></p>
							</div>
							<a href="<?php echo esc_url( get_permalink( $member ) ); ?>" class="astara-team-card__toggle" data-wp-on--click="actions.open" aria-haspopup="dialog" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: team member name */ __( 'View bio for %s', 'astara' ), $member->post_title ) ); ?>">+</a>
						</div>

						<div class="astara-team-modal" data-wp-class--is-open="context.isOpen" data-wp-bind--hidden="!context.isOpen" role="dialog" aria-modal="true" aria-labelledby="<?php echo esc_attr( $modal_id ); ?>">
							<div class="astara-team-modal__backdrop" data-wp-on--click="actions.close"></div>
							<div class="astara-team-modal__panel">
								<button type="button" class="astara-team-modal__close" data-wp-on--click="actions.close" aria-label="<?php esc_attr_e( 'Close', 'astara' ); ?>">&times;</button>
								<?php echo str_replace( 'astara-team-bio', 'astara-team-modal', astara_render_team_member_bio( $member, 'h3', $modal_id ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- astara_render_team_member_bio() already escapes its own output. ?>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
	}

	if ( $lower_band_open ) {
		echo '</div>';
	}

	return ob_get_clean();
}

/**
 * Render a team member's bio on their own single page — used by
 * templates/single-astara_team_member.html. Same content the modal shows.
 *
 * @return string
 */
function astara_render_team_member_detail() {
	$member = get_queried_object();

	if ( ! ( $member instanceof WP_Post ) || 'astara_team_member' !== $member->post_type ) {
		return '';
	}

	ob_start();
	?>
	<div class="astara-team-detail">
		<a class="astara-team-detail__back" href="<?php echo esc_url( home_url( '/team/' ) ); ?>">&larr; <?php esc_html_e( 'Back to Team', 'astara' ); ?></a>
		<?php echo astara_render_team_member_bio( $member, 'h1' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- already escaped inside. ?>
	</div>
	<?php
	return ob_get_clean();
}
