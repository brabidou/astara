<?php
/**
 * Portfolio Company post type.
 *
 * @package Astara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Portfolio Company post type.
 */
function astara_register_portfolio_company() {
	register_post_type(
		'astara_portfolio_co',
		array(
			'labels'        => array(
				'name'          => __( 'Portfolio Companies', 'astara' ),
				'singular_name' => __( 'Portfolio Company', 'astara' ),
				'add_new_item'  => __( 'Add New Portfolio Company', 'astara' ),
				'edit_item'     => __( 'Edit Portfolio Company', 'astara' ),
			),
			'public'        => true,
			'show_in_rest'  => true,
			'has_archive'   => false,
			'rewrite'       => array( 'slug' => 'portfolio' ),
			'menu_icon'     => 'dashicons-building',
			'supports'      => array( 'title', 'excerpt', 'thumbnail', 'page-attributes', 'revisions' ),
			'menu_position' => 21,
		)
	);
}
add_action( 'init', 'astara_register_portfolio_company' );

/**
 * Flush rewrite rules once after this CPT's rewrite structure changes.
 * See astara_maybe_flush_team_rewrite_rules() in inc/team.php for why.
 */
function astara_maybe_flush_portfolio_rewrite_rules() {
	$version = '1';
	if ( get_option( 'astara_portfolio_rewrite_version' ) !== $version ) {
		flush_rewrite_rules();
		update_option( 'astara_portfolio_rewrite_version', $version );
	}
}
add_action( 'init', 'astara_maybe_flush_portfolio_rewrite_rules', 20 );

/**
 * Register meta fields: investment date and company website, both optional.
 */
function astara_register_portfolio_company_meta() {
	register_post_meta(
		'astara_portfolio_co',
		'astara_investment_date',
		array(
			'type'              => 'string',
			'single'            => true,
			'show_in_rest'      => true,
			'sanitize_callback' => 'sanitize_text_field',
			'auth_callback'     => function () {
				return current_user_can( 'edit_posts' );
			},
		)
	);

	register_post_meta(
		'astara_portfolio_co',
		'astara_case_study_url',
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
		'astara_portfolio_co',
		'astara_website_url',
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
add_action( 'init', 'astara_register_portfolio_company_meta' );

/**
 * Register the rich-text case study field.
 */
function astara_register_portfolio_company_case_study_meta() {
	astara_register_richtext_meta( 'astara_portfolio_co', 'astara_case_study' );
}
add_action( 'init', 'astara_register_portfolio_company_case_study_meta' );

/**
 * The case study PDF or link for a company, or '' when it doesn't have one.
 * The Portfolio page's "Case Study" button only appears when this is set.
 *
 * @param int|WP_Post $post Company post or ID.
 * @return string
 */
function astara_portfolio_case_study_url( $post ) {
	$post = get_post( $post );

	return $post ? esc_url_raw( (string) get_post_meta( $post->ID, 'astara_case_study_url', true ) ) : '';
}

/**
 * Add a meta box for investment date, industry-tagged excerpt, and website URL.
 * (Industry itself is the post excerpt — mirrors the Team Member "role" pattern.)
 */
function astara_portfolio_company_meta_box() {
	add_meta_box(
		'astara_portfolio_company_details',
		__( 'Investment Details', 'astara' ),
		'astara_render_portfolio_company_meta_box',
		'astara_portfolio_co',
		'side'
	);
}
add_action( 'add_meta_boxes', 'astara_portfolio_company_meta_box' );

/**
 * Add the Case Study meta box in the main column, standing in for the block editor.
 */
function astara_portfolio_company_case_study_meta_box() {
	add_meta_box(
		'astara_portfolio_company_case_study',
		__( 'Case Study', 'astara' ),
		'astara_render_portfolio_company_case_study_meta_box',
		'astara_portfolio_co',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'astara_portfolio_company_case_study_meta_box' );

/**
 * Render the Case Study meta box.
 *
 * @param WP_Post $post Current post object.
 */
function astara_render_portfolio_company_case_study_meta_box( $post ) {
	astara_render_richtext_meta_box( $post, 'astara_case_study' );
	?>
	<p class="description"><?php esc_html_e( 'Shown as the "Case Study" accordion in the details popup (and on the company\'s own page). The "Case Study" button on the Portfolio page appears when this has content or when a PDF or link is set under Investment Details.', 'astara' ); ?></p>
	<?php
}

/**
 * Render the Investment Details meta box.
 *
 * @param WP_Post $post Current post object.
 */
function astara_render_portfolio_company_meta_box( $post ) {
	wp_nonce_field( 'astara_portfolio_company_meta', 'astara_portfolio_company_meta_nonce' );

	$investment_date = get_post_meta( $post->ID, 'astara_investment_date', true );
	$website_url     = get_post_meta( $post->ID, 'astara_website_url', true );
	$case_study_url  = get_post_meta( $post->ID, 'astara_case_study_url', true );
	?>
	<p>
		<label for="astara_investment_date"><?php esc_html_e( 'Investment Date', 'astara' ); ?></label>
		<input type="text" id="astara_investment_date" name="astara_investment_date" class="widefat" placeholder="<?php esc_attr_e( 'e.g. February 2024', 'astara' ); ?>" value="<?php echo esc_attr( $investment_date ); ?>" />
	</p>
	<p>
		<label for="astara_website_url"><?php esc_html_e( 'Company Website', 'astara' ); ?></label>
		<input type="url" id="astara_website_url" name="astara_website_url" class="widefat" value="<?php echo esc_attr( $website_url ); ?>" />
	</p>
	<p>
		<label for="astara_case_study_url"><?php esc_html_e( 'Case study PDF or link', 'astara' ); ?></label>
		<input type="url" id="astara_case_study_url" name="astara_case_study_url" class="widefat" placeholder="https://" value="<?php echo esc_attr( $case_study_url ); ?>" />
		<button type="button" class="button astara-choose-file" data-target="#astara_case_study_url" style="margin-top:6px;"><?php esc_html_e( 'Choose file from Media Library', 'astara' ); ?></button>
	</p>
	<p class="description"><?php esc_html_e( 'The "Case Study" button on the Portfolio page opens this PDF or link in a new tab (the button also appears when the Case Study text box has content). Use the Excerpt field for Industry.', 'astara' ); ?></p>
	<?php
}

/**
 * Save the Investment Details meta box.
 *
 * @param int $post_id Post ID being saved.
 */
function astara_save_portfolio_company_meta( $post_id ) {
	if ( ! isset( $_POST['astara_portfolio_company_meta_nonce'] ) ||
		! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['astara_portfolio_company_meta_nonce'] ) ), 'astara_portfolio_company_meta' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['astara_investment_date'] ) ) {
		update_post_meta( $post_id, 'astara_investment_date', sanitize_text_field( wp_unslash( $_POST['astara_investment_date'] ) ) );
	}

	if ( isset( $_POST['astara_case_study_url'] ) ) {
		$case_study_url = esc_url_raw( wp_unslash( $_POST['astara_case_study_url'] ) );
		if ( $case_study_url ) {
			update_post_meta( $post_id, 'astara_case_study_url', $case_study_url );
		} else {
			delete_post_meta( $post_id, 'astara_case_study_url' );
		}
	}

	if ( isset( $_POST['astara_website_url'] ) ) {
		update_post_meta( $post_id, 'astara_website_url', esc_url_raw( wp_unslash( $_POST['astara_website_url'] ) ) );
	}
}
add_action( 'save_post_astara_portfolio_co', 'astara_save_portfolio_company_meta' );

/**
 * Save the Case Study meta box.
 *
 * @param int $post_id Post ID being saved.
 */
function astara_save_portfolio_company_case_study( $post_id ) {
	astara_save_richtext_meta( $post_id, 'astara_case_study' );
}
add_action( 'save_post_astara_portfolio_co', 'astara_save_portfolio_company_case_study' );
