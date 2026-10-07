<?php
/**
 * Team Member post type.
 *
 * @package Astara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Team Member post type and its category taxonomy.
 */
function astara_register_team_member() {
	register_post_type(
		'astara_team_member',
		array(
			'labels'        => array(
				'name'          => __( 'Team Members', 'astara' ),
				'singular_name' => __( 'Team Member', 'astara' ),
				'add_new_item'  => __( 'Add New Team Member', 'astara' ),
				'edit_item'     => __( 'Edit Team Member', 'astara' ),
			),
			'public'        => true,
			'show_in_rest'  => true,
			'has_archive'   => false,
			'rewrite'       => array( 'slug' => 'team' ),
			'menu_icon'     => 'dashicons-groups',
			'supports'      => array( 'title', 'excerpt', 'thumbnail', 'page-attributes', 'revisions' ),
			'menu_position' => 20,
		)
	);

	register_taxonomy(
		'astara_team_category',
		'astara_team_member',
		array(
			'labels'       => array(
				'name'          => __( 'Team Categories', 'astara' ),
				'singular_name' => __( 'Team Category', 'astara' ),
			),
			'public'       => true,
			'show_in_rest' => true,
			'hierarchical' => true,
			'rewrite'      => array( 'slug' => 'team-category' ),
		)
	);
}
add_action( 'init', 'astara_register_team_member' );

/**
 * Flush rewrite rules once after this CPT's rewrite structure changes.
 * Registering a post type doesn't retroactively update the rewrite rules
 * WordPress already has cached — without this, single team member URLs
 * 404 until someone happens to visit Settings > Permalinks and re-save.
 * Bump ASTARA_TEAM_REWRITE_VERSION if the CPT's rewrite args ever change.
 */
function astara_maybe_flush_team_rewrite_rules() {
	$version = '1';
	if ( get_option( 'astara_team_rewrite_version' ) !== $version ) {
		flush_rewrite_rules();
		update_option( 'astara_team_rewrite_version', $version );
	}
}
add_action( 'init', 'astara_maybe_flush_team_rewrite_rules', 20 );

/**
 * Register meta fields: LinkedIn URL and email, both optional.
 */
function astara_register_team_member_meta() {
	register_post_meta(
		'astara_team_member',
		'astara_linkedin_url',
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

	register_post_meta(
		'astara_team_member',
		'astara_email',
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'sanitize_email',
			'auth_callback'     => function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);
}
add_action( 'init', 'astara_register_team_member_meta' );

/**
 * Register the rich-text bio field.
 */
function astara_register_team_member_bio_meta() {
	astara_register_richtext_meta( 'astara_team_member', 'astara_bio' );
}
add_action( 'init', 'astara_register_team_member_bio_meta' );

/**
 * Add a simple meta box for LinkedIn/email, since the block editor doesn't
 * surface register_post_meta fields in its UI without a custom sidebar plugin.
 */
function astara_team_member_meta_box() {
	add_meta_box(
		'astara_team_member_links',
		__( 'Contact Links', 'astara' ),
		'astara_render_team_member_meta_box',
		'astara_team_member',
		'side'
	);
}
add_action( 'add_meta_boxes', 'astara_team_member_meta_box' );

/**
 * Add the Bio meta box in the main column, standing in for the block editor.
 */
function astara_team_member_bio_meta_box() {
	add_meta_box(
		'astara_team_member_bio',
		__( 'Bio', 'astara' ),
		'astara_render_team_member_bio_meta_box',
		'astara_team_member',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'astara_team_member_bio_meta_box' );

/**
 * Render the Bio meta box.
 *
 * @param WP_Post $post Current post object.
 */
function astara_render_team_member_bio_meta_box( $post ) {
	astara_render_richtext_meta_box( $post, 'astara_bio' );
	?>
	<p class="description"><?php esc_html_e( 'Use the Excerpt field below for the role or title shown under the name.', 'astara' ); ?></p>
	<?php
}

/**
 * Render the Contact Links meta box.
 *
 * @param WP_Post $post Current post object.
 */
function astara_render_team_member_meta_box( $post ) {
	wp_nonce_field( 'astara_team_member_meta', 'astara_team_member_meta_nonce' );

	$linkedin_url = get_post_meta( $post->ID, 'astara_linkedin_url', true );
	$email        = get_post_meta( $post->ID, 'astara_email', true );
	?>
	<p>
		<label for="astara_linkedin_url"><?php esc_html_e( 'LinkedIn URL', 'astara' ); ?></label>
		<input type="url" id="astara_linkedin_url" name="astara_linkedin_url" class="widefat" value="<?php echo esc_attr( $linkedin_url ); ?>" />
	</p>
	<p>
		<label for="astara_email"><?php esc_html_e( 'Email', 'astara' ); ?></label>
		<input type="email" id="astara_email" name="astara_email" class="widefat" value="<?php echo esc_attr( $email ); ?>" />
	</p>
	<?php
}

/**
 * Save the Contact Links meta box.
 *
 * @param int $post_id Post ID being saved.
 */
function astara_save_team_member_meta( $post_id ) {
	if ( ! isset( $_POST['astara_team_member_meta_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['astara_team_member_meta_nonce'] ) ), 'astara_team_member_meta' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['astara_linkedin_url'] ) ) {
		update_post_meta( $post_id, 'astara_linkedin_url', esc_url_raw( wp_unslash( $_POST['astara_linkedin_url'] ) ) );
	}

	if ( isset( $_POST['astara_email'] ) ) {
		update_post_meta( $post_id, 'astara_email', sanitize_email( wp_unslash( $_POST['astara_email'] ) ) );
	}
}
add_action( 'save_post_astara_team_member', 'astara_save_team_member_meta' );

/**
 * Save the Bio meta box.
 *
 * @param int $post_id Post ID being saved.
 */
function astara_save_team_member_bio( $post_id ) {
	astara_save_richtext_meta( $post_id, 'astara_bio' );
}
add_action( 'save_post_astara_team_member', 'astara_save_team_member_bio' );
