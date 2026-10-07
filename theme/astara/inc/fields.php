<?php
/**
 * Shared helpers for rich-text meta box fields, used by the Team Member and
 * Portfolio Company post types in place of the full block editor.
 *
 * @package Astara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register a rich-text (HTML) post meta field.
 *
 * @param string $post_type Post type slug.
 * @param string $meta_key  Meta key.
 */
function astara_register_richtext_meta( $post_type, $meta_key ) {
	register_post_meta(
		$post_type,
		$meta_key,
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'wp_kses_post',
			'auth_callback'     => function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);
}

/**
 * Render a rich-text meta box: a compact TinyMCE editor (bold, italic, lists,
 * links) with no media button and none of the block editor around it.
 *
 * @param WP_Post $post     Current post object.
 * @param string  $meta_key Meta key the field edits. Also used as the editor id.
 */
function astara_render_richtext_meta_box( $post, $meta_key ) {
	wp_nonce_field( $meta_key, $meta_key . '_nonce' );

	wp_editor(
		(string) get_post_meta( $post->ID, $meta_key, true ),
		$meta_key,
		array(
			'textarea_name' => $meta_key,
			'textarea_rows' => 12,
			'media_buttons' => false,
			'teeny'         => true,
			'quicktags'     => array( 'buttons' => 'strong,em,link,ul,ol,li' ),
		)
	);
}

/**
 * Save a rich-text meta box field.
 *
 * @param int    $post_id  Post ID being saved.
 * @param string $meta_key Meta key the field edits.
 */
function astara_save_richtext_meta( $post_id, $meta_key ) {
	if ( ! isset( $_POST[ $meta_key . '_nonce' ] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST[ $meta_key . '_nonce' ] ) ), $meta_key ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST[ $meta_key ] ) ) {
		update_post_meta( $post_id, $meta_key, wp_kses_post( wp_unslash( $_POST[ $meta_key ] ) ) );
	}
}

/**
 * Turn a stored rich-text field into front-end HTML: paragraphs added,
 * unsafe markup stripped.
 *
 * @param string $html Stored field value.
 * @return string
 */
function astara_richtext_html( $html ) {
	return wpautop( wp_kses_post( $html ) );
}

/**
 * One-time move of existing Team Member bios and Portfolio Company case
 * studies from post content (block editor) into their meta fields. Block
 * comments are stripped so the classic editor shows clean HTML. The post
 * content is cleared afterwards, since nothing edits or displays it anymore;
 * WordPress keeps a revision of the old content.
 *
 * Runs once per environment (the content lives in each site's database, so
 * staging and production each migrate on their first request after deploy).
 */
function astara_migrate_content_to_meta() {
	$version = '1';
	if ( get_option( 'astara_content_meta_version' ) === $version ) {
		return;
	}

	$map = array(
		'astara_team_member'  => 'astara_bio',
		'astara_portfolio_co' => 'astara_case_study',
	);

	foreach ( $map as $post_type => $meta_key ) {
		$posts = get_posts(
			array(
				'post_type'      => $post_type,
				'post_status'    => 'any',
				'posts_per_page' => -1,
			)
		);

		foreach ( $posts as $post ) {
			$content = trim( preg_replace( '/<!--\s*\/?wp:.*?-->\s*/s', '', $post->post_content ) );

			if ( '' === $content ) {
				continue;
			}

			if ( '' === (string) get_post_meta( $post->ID, $meta_key, true ) ) {
				update_post_meta( $post->ID, $meta_key, wp_kses_post( $content ) );
			}

			wp_update_post(
				array(
					'ID'           => $post->ID,
					'post_content' => '',
				)
			);
		}
	}

	update_option( 'astara_content_meta_version', $version );
}
add_action( 'init', 'astara_migrate_content_to_meta', 30 );
