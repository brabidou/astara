<?php
/**
 * Direct links for news posts.
 *
 * Some News & Media items are just a link to a PDF (or another website) and
 * don't need a page of their own. A post can carry a "direct link"; when it
 * does, the news cards go straight to that URL and the post's own page
 * redirects there.
 *
 * @package Astara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the direct-link meta field.
 */
function astara_register_news_link_meta() {
	register_post_meta(
		'post',
		'astara_news_link',
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'esc_url_raw',
			'auth_callback'     => function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);
}
add_action( 'init', 'astara_register_news_link_meta' );

/**
 * The direct link for a post, or '' when it uses its own page.
 *
 * @param int|WP_Post $post Post or ID.
 * @return string
 */
function astara_news_direct_link( $post ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return '';
	}

	return esc_url_raw( (string) get_post_meta( $post->ID, 'astara_news_link', true ) );
}

/**
 * The URL a news card should link to: the direct link when there is one,
 * otherwise the post's own page.
 *
 * @param WP_Post $post Post.
 * @return string
 */
function astara_news_card_url( $post ) {
	$direct = astara_news_direct_link( $post );

	return $direct ? $direct : (string) get_permalink( $post );
}

/**
 * Extra anchor attributes for a news card. Direct links open in a new tab.
 * The returned string is a fixed, safe literal.
 *
 * @param WP_Post $post Post.
 * @return string
 */
function astara_news_card_link_attrs( $post ) {
	return astara_news_direct_link( $post ) ? ' target="_blank" rel="noopener noreferrer"' : '';
}

/**
 * A short file-type label for a direct link ("PDF"), or '' when it isn't a
 * recognisable document.
 *
 * @param WP_Post $post Post.
 * @return string
 */
function astara_news_card_file_label( $post ) {
	$direct = astara_news_direct_link( $post );
	if ( ! $direct ) {
		return '';
	}

	$path = wp_parse_url( $direct, PHP_URL_PATH );
	$ext  = $path ? strtolower( pathinfo( $path, PATHINFO_EXTENSION ) ) : '';

	return 'pdf' === $ext ? 'PDF' : '';
}

/**
 * Send visitors who land on a direct-link post's own page to the link instead.
 * Previews are left alone so editors can still see the page itself.
 */
function astara_redirect_news_direct_link() {
	if ( ! is_singular( 'post' ) || is_preview() ) {
		return;
	}

	$direct = astara_news_direct_link( get_queried_object() );
	if ( $direct ) {
		wp_redirect( $direct, 302, 'Astara' ); // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect -- the link is an editor-chosen external or file URL.
		exit;
	}
}
add_action( 'template_redirect', 'astara_redirect_news_direct_link' );

/**
 * Add the meta box to the post editor's sidebar.
 */
function astara_news_link_meta_box() {
	add_meta_box(
		'astara_news_link',
		__( 'Link directly to a file or website', 'astara' ),
		'astara_render_news_link_meta_box',
		'post',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'astara_news_link_meta_box' );

/**
 * Render the meta box.
 *
 * @param WP_Post $post Current post.
 */
function astara_render_news_link_meta_box( $post ) {
	wp_nonce_field( 'astara_news_link', 'astara_news_link_nonce' );
	$value = (string) get_post_meta( $post->ID, 'astara_news_link', true );
	?>
	<p>
		<label for="astara_news_link_url" class="screen-reader-text"><?php esc_html_e( 'File or website URL', 'astara' ); ?></label>
		<input type="url" id="astara_news_link_url" name="astara_news_link" class="widefat" value="<?php echo esc_attr( $value ); ?>" placeholder="https://" />
	</p>
	<p>
		<button type="button" class="button astara-choose-file" data-target="#astara_news_link_url"><?php esc_html_e( 'Choose file from Media Library', 'astara' ); ?></button>
	</p>
	<p class="description">
		<?php esc_html_e( "For items that are just a PDF or an outside link. News & Media then links straight there (in a new tab) and this post's own page isn't used. Leave blank to use the post page as normal.", 'astara' ); ?>
	</p>
	<?php
}

/**
 * Save the meta box.
 *
 * @param int $post_id Post ID being saved.
 */
function astara_save_news_link( $post_id ) {
	if ( ! isset( $_POST['astara_news_link_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['astara_news_link_nonce'] ) ), 'astara_news_link' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['astara_news_link'] ) ) {
		$url = esc_url_raw( wp_unslash( $_POST['astara_news_link'] ) );
		if ( $url ) {
			update_post_meta( $post_id, 'astara_news_link', $url );
		} else {
			delete_post_meta( $post_id, 'astara_news_link' );
		}
	}
}
add_action( 'save_post_post', 'astara_save_news_link' );
