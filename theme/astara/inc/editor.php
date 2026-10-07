<?php
/**
 * Site Editor polish: readable template names and descriptions, and editor
 * support for the theme's server-rendered blocks so they show a live preview
 * instead of a "block not supported" error.
 *
 * @package Astara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Titles and descriptions for the theme's own templates. WordPress only fills
 * these in for its built-in template names; ours would otherwise show up as the
 * raw file name with no description.
 *
 * @return array<string, array{title: string, description: string}>
 */
function astara_template_details() {
	return array(
		'single-astara_team_member'  => array(
			'title'       => __( 'Single Team Member', 'astara' ),
			'description' => __( "Displays one team member's own page: photo, name, role, bio and contact links. The text comes from that member's fields under Team Members in the admin, so only the layout around it is edited here.", 'astara' ),
		),
		'single-astara_portfolio_co' => array(
			'title'       => __( 'Single Portfolio Company', 'astara' ),
			'description' => __( "Displays one portfolio company's own page: logo, name, investment date, industry, case study and website link. The text comes from that company's fields under Portfolio Companies in the admin, so only the layout around it is edited here.", 'astara' ),
		),
	);
}

/**
 * Apply the title and description to a template, unless someone has already
 * given it a real title of their own.
 *
 * @param WP_Block_Template $template Template object.
 * @return WP_Block_Template
 */
function astara_apply_template_details( $template ) {
	$details = astara_template_details();

	if ( $template instanceof WP_Block_Template && 'wp_template' === $template->type && isset( $details[ $template->slug ] ) ) {
		if ( '' === (string) $template->title || $template->title === $template->slug ) {
			$template->title = $details[ $template->slug ]['title'];
		}
		if ( '' === (string) $template->description ) {
			$template->description = $details[ $template->slug ]['description'];
		}
	}

	return $template;
}

/**
 * Template lists (Site Editor, REST).
 *
 * @param WP_Block_Template[] $templates Templates.
 * @return WP_Block_Template[]
 */
function astara_filter_block_templates( $templates ) {
	return array_map( 'astara_apply_template_details', (array) $templates );
}
add_filter( 'get_block_templates', 'astara_filter_block_templates' );

/**
 * A single theme-file template, looked up by id.
 *
 * @param WP_Block_Template|null $template Template or null.
 * @return WP_Block_Template|null
 */
function astara_filter_block_file_template( $template ) {
	return $template ? astara_apply_template_details( $template ) : $template;
}
add_filter( 'get_block_file_template', 'astara_filter_block_file_template' );

/**
 * Whether this request is the editor asking for a block preview.
 *
 * @return bool
 */
function astara_is_editor_preview() {
	return defined( 'REST_REQUEST' ) && REST_REQUEST;
}

/**
 * A sample post to show in the editor preview of a "detail" block. On the real
 * page the block shows the page's own post; in the editor there isn't one, so
 * show the first entry to give a representative layout.
 *
 * @param string $post_type Post type slug.
 * @return WP_Post|null Null outside the editor, or when there's nothing to show.
 */
function astara_editor_preview_post( $post_type ) {
	if ( ! astara_is_editor_preview() ) {
		return null;
	}

	$posts = get_posts(
		array(
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'ID'         => 'ASC',
			),
		)
	);

	return $posts ? $posts[0] : null;
}

/**
 * The theme's server-rendered blocks, as name => [ title, dashicon, description ].
 *
 * @return array<string, array{0: string, 1: string, 2: string}>
 */
function astara_editor_blocks() {
	return array(
		'astara/team-directory'           => array( __( 'Team Directory', 'astara' ), 'groups', __( 'Every team member, grouped by category. Edit people under Team Members.', 'astara' ) ),
		'astara/team-member-detail'       => array( __( 'Team Member Detail', 'astara' ), 'id', __( "A team member's photo, name, role, bio and links. Used on each member's own page.", 'astara' ) ),
		'astara/portfolio-directory'      => array( __( 'Portfolio Directory', 'astara' ), 'building', __( 'The portfolio logo grid. Edit companies under Portfolio Companies.', 'astara' ) ),
		'astara/portfolio-company-detail' => array( __( 'Portfolio Company Detail', 'astara' ), 'portfolio', __( "A company's logo, details and case study. Used on each company's own page.", 'astara' ) ),
		'astara/news-grid'                => array( __( 'News Grid', 'astara' ), 'megaphone', __( 'The latest news cards.', 'astara' ) ),
		'astara/news-archive'             => array( __( 'News Archive', 'astara' ), 'archive', __( 'The full filterable news archive.', 'astara' ) ),
		'astara/news-single-post'         => array( __( 'News Post Detail', 'astara' ), 'media-text', __( "A single news post's content. Used on each post's own page.", 'astara' ) ),
		'astara/social-links'             => array( __( 'Social Links', 'astara' ), 'share', __( 'LinkedIn and X icons, from Site Settings. Icons without a URL are hidden.', 'astara' ) ),
		'astara/copyright'                => array( __( 'Copyright', 'astara' ), 'info', __( 'The footer copyright line. The year updates itself.', 'astara' ) ),
		'astara/footer-contact'           => array( __( 'Footer Contact Details', 'astara' ), 'phone', __( 'The footer phone number and address, from Site Settings.', 'astara' ) ),
	);
}

/**
 * Load the script that tells the editor about those blocks, with the data it
 * needs, so it can render each through the server (live preview).
 */
function astara_enqueue_editor_blocks() {
	wp_enqueue_script(
		'astara-editor-blocks',
		ASTARA_THEME_URI . '/assets/js/editor-blocks.js',
		array( 'wp-blocks', 'wp-element', 'wp-block-editor', 'wp-server-side-render' ),
		astara_asset_version( '/assets/js/editor-blocks.js' ),
		true
	);
	wp_add_inline_script( 'astara-editor-blocks', 'window.astaraEditorBlocks = ' . wp_json_encode( astara_editor_blocks() ) . ';', 'before' );
}
add_action( 'enqueue_block_editor_assets', 'astara_enqueue_editor_blocks' );

/**
 * Load the Media Library picker (for "Choose file" buttons next to a URL field)
 * on the screens that have one: News posts and Portfolio Companies.
 *
 * @param string $hook_suffix Current admin page.
 */
function astara_media_picker_assets( $hook_suffix ) {
	if ( ! in_array( $hook_suffix, array( 'post.php', 'post-new.php' ), true ) || ! in_array( get_post_type(), array( 'post', 'astara_portfolio_co' ), true ) ) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_script(
		'astara-media-picker',
		ASTARA_THEME_URI . '/assets/js/media-picker.js',
		array( 'jquery' ),
		astara_asset_version( '/assets/js/media-picker.js' ),
		true
	);
}
add_action( 'admin_enqueue_scripts', 'astara_media_picker_assets' );
