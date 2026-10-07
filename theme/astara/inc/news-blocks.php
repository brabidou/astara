<?php
/**
 * Server-rendered dynamic blocks for News & Media. No block.json/JS needed —
 * inserted via a raw block comment in post_content.
 *
 * @package Astara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the News Grid and News Archive blocks.
 */
function astara_register_news_blocks() {
	register_block_type(
		'astara/news-grid',
		array(
			'render_callback' => 'astara_render_news_grid',
		)
	);

	register_block_type(
		'astara/news-archive',
		array(
			'render_callback' => 'astara_render_news_archive',
		)
	);

	register_block_type(
		'astara/news-single-post',
		array(
			'render_callback' => 'astara_render_news_single_post',
		)
	);
}
add_action( 'init', 'astara_register_news_blocks' );

/**
 * Render a single news teaser card.
 *
 * Two variants:
 * - 'grid' (default) — date/category meta row, large image with an
 *   arrow-icon affordance, title, excerpt. Matches the Figma news card
 *   layout: meta above the image, not below. Used by the News & Media
 *   page's 2x2 grid.
 * - 'list' — small thumbnail inline to the left of the title, with the
 *   date/category meta as a subtitle line underneath. Used by the News
 *   archive listing.
 *
 * The whole card links to the post's own page either way.
 *
 * @param WP_Post $post_obj Post.
 * @param string  $variant  'grid' or 'list'.
 * @return string
 */
function astara_render_news_card( $post_obj, $variant = 'grid' ) {
	$tags = get_the_terms( $post_obj, 'post_tag' );
	$tag  = ( ! is_wp_error( $tags ) && ! empty( $tags ) ) ? $tags[0] : null;

	if ( 'list' === $variant ) {
		ob_start();
		?>
		<a class="astara-news-card astara-news-card--list" href="<?php echo esc_url( get_permalink( $post_obj ) ); ?>">
			<?php if ( has_post_thumbnail( $post_obj ) ) : ?>
				<div class="astara-news-card__image">
					<?php echo get_the_post_thumbnail( $post_obj, 'thumbnail' ); ?>
				</div>
			<?php endif; ?>
			<div class="astara-news-card__body">
				<h3 class="astara-news-card__title"><?php echo esc_html( get_the_title( $post_obj ) ); ?></h3>
				<p class="astara-news-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt( $post_obj ), 20 ) ); ?></p>
				<div class="astara-news-card__meta">
					<span class="astara-news-card__date"><?php echo esc_html( get_the_date( 'F j, Y', $post_obj ) ); ?></span>
					<?php if ( $tag ) : ?>
						<span class="astara-news-card__tag"><?php echo esc_html( $tag->name ); ?></span>
					<?php endif; ?>
				</div>
			</div>
		</a>
		<?php
		return ob_get_clean();
	}

	ob_start();
	?>
	<a class="astara-news-card" href="<?php echo esc_url( get_permalink( $post_obj ) ); ?>">
		<div class="astara-news-card__meta">
			<span class="astara-news-card__date"><?php echo esc_html( get_the_date( 'F j, Y', $post_obj ) ); ?></span>
			<?php if ( $tag ) : ?>
				<span class="astara-news-card__tag"><?php echo esc_html( $tag->name ); ?></span>
			<?php endif; ?>
		</div>
		<?php if ( has_post_thumbnail( $post_obj ) ) : ?>
			<div class="astara-news-card__image">
				<?php echo get_the_post_thumbnail( $post_obj, 'medium_large' ); ?>
				<span class="astara-news-card__arrow" aria-hidden="true">
					<svg width="14" height="14" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M3 11L11 3M11 3H4.5M11 3V9.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
					</svg>
				</span>
			</div>
		<?php endif; ?>
		<div class="astara-news-card__body">
			<h3 class="astara-news-card__title"><?php echo esc_html( get_the_title( $post_obj ) ); ?></h3>
			<p class="astara-news-card__excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt( $post_obj ), 20 ) ); ?></p>
		</div>
	</a>
	<?php
	return ob_get_clean();
}

/**
 * Render the News & Media page's grid — a tag filter bar (real links, works
 * with JS off) above a single 2x2 grid of that tag's latest posts. The
 * active tag comes from the `tag` query string, so it's a normal page
 * request per filter, not a client-side toggle over a fixed pre-rendered
 * set — a tag with more than 4 posts still only shows its own latest 4,
 * not the site-wide latest 4. Ends with a "View More" link to the full
 * archive listing page.
 *
 * @return string
 */
function astara_render_news_grid() {
	$tags = get_terms(
		array(
			'taxonomy'   => 'post_tag',
			'hide_empty' => true,
		)
	);

	if ( is_wp_error( $tags ) ) {
		$tags = array();
	}

	$archive_page = get_page_by_path( 'news-archive' );
	$archive_url  = $archive_page ? get_permalink( $archive_page ) : home_url( '/news-archive/' );
	$page_url     = get_permalink();

	$requested_tag = isset( $_GET['tag'] ) ? sanitize_title( wp_unslash( $_GET['tag'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filter, no state change.
	$valid_slugs   = wp_list_pluck( $tags, 'slug' );
	$active_slug   = in_array( $requested_tag, $valid_slugs, true ) ? $requested_tag : 'all';

	$query_args = array(
		'post_type'      => 'post',
		'posts_per_page' => 4,
		'post_status'    => 'publish',
	);
	if ( 'all' !== $active_slug ) {
		$query_args['tag'] = $active_slug;
	}
	$posts = get_posts( $query_args );

	ob_start();
	?>
	<div class="astara-news-filter">
		<div class="astara-news-filter__bar">
			<a href="<?php echo esc_url( $page_url ); ?>" class="astara-news-filter__tag<?php echo ( 'all' === $active_slug ) ? ' is-active' : ''; ?>">
				<?php esc_html_e( 'All', 'astara' ); ?>
			</a>
			<?php foreach ( $tags as $tag ) : ?>
				<a href="<?php echo esc_url( add_query_arg( 'tag', $tag->slug, $page_url ) ); ?>" class="astara-news-filter__tag<?php echo ( $tag->slug === $active_slug ) ? ' is-active' : ''; ?>">
					<?php echo esc_html( $tag->name ); ?>
				</a>
			<?php endforeach; ?>
		</div>

		<?php if ( ! empty( $posts ) ) : ?>
			<div class="astara-news-grid">
				<?php
				foreach ( $posts as $post_obj ) {
					echo astara_render_news_card( $post_obj ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- astara_render_news_card() already escapes its own output.
				}
				?>
			</div>
		<?php else : ?>
			<p><?php esc_html_e( 'No posts found for this tag.', 'astara' ); ?></p>
		<?php endif; ?>
	</div>

	<div class="astara-news-view-more">
		<a class="astara-button astara-button--outline" href="<?php echo esc_url( $archive_url ); ?>"><?php esc_html_e( 'View More', 'astara' ); ?></a>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Render the full News archive listing — a left-hand sidebar with month/year
 * navigation and a tag filter (both real links, work with JS off) beside a
 * paginated grid of posts. Reads `year` / `month` / `tag` / `paged` from the
 * query string, same query-string-based pattern as the News & Media page's
 * tag filter.
 *
 * @return string
 */
function astara_render_news_archive() {
	global $wpdb;

	$archive_page = get_page_by_path( 'news-archive' );
	$archive_url  = $archive_page ? get_permalink( $archive_page ) : home_url( '/news-archive/' );

	$year  = isset( $_GET['year'] ) ? absint( $_GET['year'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filter, no state change.
	$month = isset( $_GET['month'] ) ? absint( $_GET['month'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filter, no state change.
	$paged = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filter, no state change.

	$tags = get_terms(
		array(
			'taxonomy'   => 'post_tag',
			'hide_empty' => true,
		)
	);
	if ( is_wp_error( $tags ) ) {
		$tags = array();
	}

	$requested_tag = isset( $_GET['tag'] ) ? sanitize_title( wp_unslash( $_GET['tag'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only filter, no state change.
	$valid_slugs   = wp_list_pluck( $tags, 'slug' );
	$active_tag    = in_array( $requested_tag, $valid_slugs, true ) ? $requested_tag : '';

	// Carry the tag filter over onto date links, and the date filter over onto tag links, so the two combine.
	$date_args = array();
	if ( $year ) {
		$date_args['year'] = $year;
	}
	if ( $month ) {
		$date_args['month'] = $month;
	}
	$tag_args = $active_tag ? array( 'tag' => $active_tag ) : array();

	$months = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		"SELECT YEAR(post_date) AS `year`, MONTH(post_date) AS `month`, COUNT(ID) AS `post_count`
		FROM {$wpdb->posts}
		WHERE post_type = 'post' AND post_status = 'publish'
		GROUP BY YEAR(post_date), MONTH(post_date)
		ORDER BY post_date DESC"
	);

	$query_args = array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => 9,
		'paged'          => $paged,
	);
	if ( $year ) {
		$query_args['year'] = $year;
	}
	if ( $month ) {
		$query_args['monthnum'] = $month;
	}
	if ( $active_tag ) {
		$query_args['tag'] = $active_tag;
	}
	$query = new WP_Query( $query_args );

	ob_start();
	?>
	<div class="astara-news-archive">
		<div class="astara-news-archive__sidebar">
			<nav class="astara-news-archive__nav" aria-label="<?php esc_attr_e( 'Filter by date', 'astara' ); ?>">
				<a href="<?php echo esc_url( $tag_args ? add_query_arg( $tag_args, $archive_url ) : $archive_url ); ?>" class="astara-news-archive__nav-link<?php echo ( ! $year ) ? ' is-active' : ''; ?>">
					<?php esc_html_e( 'All Posts', 'astara' ); ?>
				</a>
				<?php
				$current_year = null;
				foreach ( $months as $row ) :
					if ( (int) $row->year !== $current_year ) {
						if ( null !== $current_year ) {
							echo '</ul>';
						}
						$current_year = (int) $row->year;
						echo '<p class="astara-news-archive__year">' . esc_html( $current_year ) . '</p><ul class="astara-news-archive__months">';
					}
					$is_active = ( $year === (int) $row->year && $month === (int) $row->month );
					$month_url = add_query_arg(
						array_merge(
							$tag_args,
							array(
								'year'  => $row->year,
								'month' => $row->month,
							)
						),
						$archive_url
					);
					?>
					<li>
						<a href="<?php echo esc_url( $month_url ); ?>" class="astara-news-archive__nav-link<?php echo $is_active ? ' is-active' : ''; ?>">
							<?php echo esc_html( date_i18n( 'F', mktime( 0, 0, 0, (int) $row->month, 1 ) ) ); ?>
							<span class="astara-news-archive__count">(<?php echo esc_html( $row->post_count ); ?>)</span>
						</a>
					</li>
					<?php
				endforeach;
				if ( null !== $current_year ) {
					echo '</ul>';
				}
				?>
			</nav>

			<nav class="astara-news-archive__nav astara-news-archive__nav--tags" aria-label="<?php esc_attr_e( 'Filter by tag', 'astara' ); ?>">
				<p class="astara-news-archive__year"><?php esc_html_e( 'Topics', 'astara' ); ?></p>
				<ul class="astara-news-archive__months">
					<li>
						<a href="<?php echo esc_url( $date_args ? add_query_arg( $date_args, $archive_url ) : $archive_url ); ?>" class="astara-news-archive__nav-link<?php echo ( ! $active_tag ) ? ' is-active' : ''; ?>">
							<?php esc_html_e( 'All', 'astara' ); ?>
						</a>
					</li>
					<?php foreach ( $tags as $tag ) : ?>
						<li>
							<a href="<?php echo esc_url( add_query_arg( array_merge( $date_args, array( 'tag' => $tag->slug ) ), $archive_url ) ); ?>" class="astara-news-archive__nav-link<?php echo ( $tag->slug === $active_tag ) ? ' is-active' : ''; ?>">
								<?php echo esc_html( $tag->name ); ?>
							</a>
						</li>
					<?php endforeach; ?>
				</ul>
			</nav>
		</div>

		<div class="astara-news-archive__main">
			<?php if ( $query->have_posts() ) : ?>
				<div class="astara-news-grid">
					<?php
					while ( $query->have_posts() ) {
						$query->the_post();
						echo astara_render_news_card( get_post(), 'list' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- astara_render_news_card() already escapes its own output.
					}
					wp_reset_postdata();
					?>
				</div>
				<?php if ( $query->max_num_pages > 1 ) : ?>
					<nav class="astara-news-archive__pagination" aria-label="<?php esc_attr_e( 'Posts pagination', 'astara' ); ?>">
						<?php
						echo wp_kses_post(
							paginate_links(
								array(
									'base'      => add_query_arg( 'paged', '%#%', add_query_arg( array_merge( $date_args, $tag_args ), $archive_url ) ),
									'format'    => '',
									'current'   => $paged,
									'total'     => $query->max_num_pages,
									'prev_text' => __( '&larr; Newer', 'astara' ),
									'next_text' => __( 'Older &rarr;', 'astara' ),
								)
							)
						);
						?>
					</nav>
				<?php endif; ?>
			<?php else : ?>
				<p><?php esc_html_e( 'No posts found for this filter.', 'astara' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}

/**
 * Render a single news post — used by templates/single.html. Styled like the
 * Team/Portfolio deep-link pages: image, tag, title, date, content with an
 * orange left border accent.
 *
 * @return string
 */
function astara_render_news_single_post() {
	$post_obj = get_queried_object();
	if ( ! ( $post_obj instanceof WP_Post ) ) {
		$post_obj = astara_editor_preview_post( 'post' );
	}

	if ( ! ( $post_obj instanceof WP_Post ) || 'post' !== $post_obj->post_type ) {
		return '';
	}

	$archive_page = get_page_by_path( 'news-archive' );
	$archive_url  = $archive_page ? get_permalink( $archive_page ) : home_url( '/news-archive/' );

	$tags = get_the_terms( $post_obj, 'post_tag' );
	$tag  = ( ! is_wp_error( $tags ) && ! empty( $tags ) ) ? $tags[0] : null;

	ob_start();
	?>
	<div class="astara-news-single">
		<a class="astara-news-single__back" href="<?php echo esc_url( $archive_url ); ?>">&larr; <?php esc_html_e( 'Back to News & Media', 'astara' ); ?></a>

		<?php if ( $tag ) : ?>
			<span class="astara-news-single__tag"><?php echo esc_html( $tag->name ); ?></span>
		<?php endif; ?>

		<h1 class="astara-news-single__title"><?php echo esc_html( get_the_title( $post_obj ) ); ?></h1>
		<p class="astara-news-single__date"><?php echo esc_html( get_the_date( '', $post_obj ) ); ?></p>

		<?php if ( has_post_thumbnail( $post_obj ) ) : ?>
			<div class="astara-news-single__image"><?php echo get_the_post_thumbnail( $post_obj, 'large' ); ?></div>
		<?php endif; ?>

		<div class="astara-news-single__content">
			<?php echo apply_filters( 'the_content', $post_obj->post_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- 'the_content' is WordPress core's own filter; content is admin-authored. ?>
		</div>
	</div>
	<?php
	return ob_get_clean();
}
