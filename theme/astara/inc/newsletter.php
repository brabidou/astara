<?php
/**
 * Newsletter signup.
 *
 * Each signup is stored as a private "Subscriber" entry (title = email), an
 * email notification is sent to a configurable address, and submissions are
 * protected by Google reCAPTCHA v2 (checkbox) plus a hidden honeypot field.
 * Embed the form with the [astara_newsletter] shortcode.
 *
 * Settings live under Subscribers → Settings. The reCAPTCHA keys and notify
 * address can also be set with constants in wp-config.php (they win over the
 * saved settings): ASTARA_RECAPTCHA_SITE_KEY, ASTARA_RECAPTCHA_SECRET_KEY,
 * ASTARA_NEWSLETTER_NOTIFY_EMAIL.
 *
 * @package Astara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Read one newsletter setting. Constants override the saved option.
 *
 * @param string $key One of 'notify_email', 'recaptcha_site_key', 'recaptcha_secret_key'.
 * @return string
 */
function astara_newsletter_setting( $key ) {
	$constants = array(
		'notify_email'         => 'ASTARA_NEWSLETTER_NOTIFY_EMAIL',
		'recaptcha_site_key'   => 'ASTARA_RECAPTCHA_SITE_KEY',
		'recaptcha_secret_key' => 'ASTARA_RECAPTCHA_SECRET_KEY',
	);

	if ( isset( $constants[ $key ] ) && defined( $constants[ $key ] ) && constant( $constants[ $key ] ) ) {
		return (string) constant( $constants[ $key ] );
	}

	$options = get_option( 'astara_newsletter', array() );
	$value   = is_array( $options ) && isset( $options[ $key ] ) ? (string) $options[ $key ] : '';

	if ( '' === $value && 'notify_email' === $key ) {
		return (string) get_option( 'admin_email' );
	}

	return $value;
}

/**
 * Whether reCAPTCHA is fully configured.
 *
 * @return bool
 */
function astara_newsletter_recaptcha_enabled() {
	return '' !== astara_newsletter_setting( 'recaptcha_site_key' ) && '' !== astara_newsletter_setting( 'recaptcha_secret_key' );
}

/**
 * Register the private Subscriber post type. Admin-only, no "Add New".
 */
function astara_register_subscriber_post_type() {
	register_post_type(
		'astara_subscriber',
		array(
			'labels'       => array(
				'name'          => __( 'Subscribers', 'astara' ),
				'singular_name' => __( 'Subscriber', 'astara' ),
				'edit_item'     => __( 'Subscriber', 'astara' ),
				'search_items'  => __( 'Search subscribers', 'astara' ),
				'not_found'     => __( 'No subscribers yet.', 'astara' ),
			),
			'public'       => false,
			'show_ui'      => true,
			'show_in_menu' => true,
			'show_in_rest' => false,
			'menu_icon'    => 'dashicons-email-alt',
			'menu_position' => 22,
			'supports'     => array( 'title' ),
			'capabilities' => array(
				'edit_post'              => 'manage_options',
				'read_post'              => 'manage_options',
				'delete_post'            => 'manage_options',
				'edit_posts'             => 'manage_options',
				'edit_others_posts'      => 'manage_options',
				'delete_posts'           => 'manage_options',
				'publish_posts'          => 'manage_options',
				'read_private_posts'     => 'manage_options',
				'delete_private_posts'   => 'manage_options',
				'delete_published_posts' => 'manage_options',
				'delete_others_posts'    => 'manage_options',
				'edit_private_posts'     => 'manage_options',
				'edit_published_posts'   => 'manage_options',
				'create_posts'           => 'do_not_allow',
			),
		)
	);
}
add_action( 'init', 'astara_register_subscriber_post_type' );

/**
 * Rename the list table columns: the title is the email address.
 *
 * @param array $columns Existing columns.
 * @return array
 */
function astara_subscriber_columns( $columns ) {
	return array(
		'cb'    => $columns['cb'],
		'title' => __( 'Email', 'astara' ),
		'date'  => __( 'Signed up', 'astara' ),
	);
}
add_filter( 'manage_astara_subscriber_posts_columns', 'astara_subscriber_columns' );

/**
 * Add an "Export CSV" button above the Subscribers list.
 *
 * @param string $which Which tablenav: 'top' or 'bottom'.
 */
function astara_subscriber_export_button( $which ) {
	if ( 'top' !== $which || 'astara_subscriber' !== get_current_screen()->post_type ) {
		return;
	}

	$url = wp_nonce_url( admin_url( 'admin-post.php?action=astara_export_subscribers' ), 'astara_export_subscribers' );
	printf(
		'<a href="%1$s" class="button">%2$s</a>',
		esc_url( $url ),
		esc_html__( 'Export CSV', 'astara' )
	);
}
add_action( 'manage_posts_extra_tablenav', 'astara_subscriber_export_button' );

/**
 * Stream all subscribers as a CSV download.
 */
function astara_export_subscribers() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to export subscribers.', 'astara' ), '', array( 'response' => 403 ) );
	}
	check_admin_referer( 'astara_export_subscribers' );

	$ids = get_posts(
		array(
			'post_type'      => 'astara_subscriber',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'ASC',
			'fields'         => 'ids',
		)
	);

	nocache_headers();
	header( 'Content-Type: text/csv; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename="astara-subscribers-' . gmdate( 'Y-m-d' ) . '.csv"' );

	$out = fopen( 'php://output', 'w' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- streaming a CSV download.
	fputcsv( $out, array( 'email', 'signed_up_utc' ) );

	foreach ( $ids as $id ) {
		$email = get_the_title( $id );
		// Stop spreadsheet apps from running a cell that starts like a formula.
		if ( preg_match( '/^[=+\-@]/', $email ) ) {
			$email = "'" . $email;
		}
		fputcsv( $out, array( $email, get_post_field( 'post_date_gmt', $id ) ) );
	}

	fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose -- see fopen above.
	exit;
}
add_action( 'admin_post_astara_export_subscribers', 'astara_export_subscribers' );

/**
 * Add the Settings page under Subscribers.
 */
function astara_newsletter_settings_menu() {
	add_submenu_page(
		'edit.php?post_type=astara_subscriber',
		__( 'Signup settings', 'astara' ),
		__( 'Settings', 'astara' ),
		'manage_options',
		'astara-newsletter-settings',
		'astara_render_newsletter_settings'
	);
}
add_action( 'admin_menu', 'astara_newsletter_settings_menu' );

/**
 * Register the settings option and its sanitizer.
 */
function astara_newsletter_register_settings() {
	register_setting(
		'astara_newsletter_group',
		'astara_newsletter',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'astara_newsletter_sanitize_settings',
			'default'           => array(),
		)
	);
}
add_action( 'admin_init', 'astara_newsletter_register_settings' );

/**
 * Sanitize the settings form. A blank secret keeps the saved one, so it never
 * has to be printed back into the page.
 *
 * @param mixed $input Submitted values.
 * @return array
 */
function astara_newsletter_sanitize_settings( $input ) {
	$input    = is_array( $input ) ? $input : array();
	$existing = get_option( 'astara_newsletter', array() );
	$existing = is_array( $existing ) ? $existing : array();

	$secret = isset( $input['recaptcha_secret_key'] ) ? sanitize_text_field( $input['recaptcha_secret_key'] ) : '';
	if ( '' === $secret && isset( $existing['recaptcha_secret_key'] ) ) {
		$secret = $existing['recaptcha_secret_key'];
	}

	return array(
		'notify_email'         => isset( $input['notify_email'] ) ? sanitize_email( $input['notify_email'] ) : '',
		'recaptcha_site_key'   => isset( $input['recaptcha_site_key'] ) ? sanitize_text_field( $input['recaptcha_site_key'] ) : '',
		'recaptcha_secret_key' => $secret,
	);
}

/**
 * Render the Settings page.
 */
function astara_render_newsletter_settings() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$options    = get_option( 'astara_newsletter', array() );
	$options    = is_array( $options ) ? $options : array();
	$notify     = isset( $options['notify_email'] ) ? $options['notify_email'] : '';
	$site_key   = isset( $options['recaptcha_site_key'] ) ? $options['recaptcha_site_key'] : '';
	$has_secret = ! empty( $options['recaptcha_secret_key'] );
	$locked     = defined( 'ASTARA_RECAPTCHA_SITE_KEY' ) || defined( 'ASTARA_RECAPTCHA_SECRET_KEY' );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Newsletter signup settings', 'astara' ); ?></h1>

		<?php if ( $locked ) : ?>
			<div class="notice notice-info inline"><p><?php esc_html_e( 'One or more reCAPTCHA keys are set in wp-config.php, and override the values below.', 'astara' ); ?></p></div>
		<?php endif; ?>

		<form method="post" action="options.php">
			<?php settings_fields( 'astara_newsletter_group' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="astara-notify-email"><?php esc_html_e( 'Notify this email', 'astara' ); ?></label></th>
					<td>
						<input type="email" id="astara-notify-email" class="regular-text" name="astara_newsletter[notify_email]" value="<?php echo esc_attr( $notify ); ?>" placeholder="<?php echo esc_attr( get_option( 'admin_email' ) ); ?>" />
						<p class="description"><?php esc_html_e( 'Gets an email each time someone signs up. Leave blank to use the site admin email.', 'astara' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="astara-recaptcha-site"><?php esc_html_e( 'reCAPTCHA site key', 'astara' ); ?></label></th>
					<td><input type="text" id="astara-recaptcha-site" class="regular-text code" name="astara_newsletter[recaptcha_site_key]" value="<?php echo esc_attr( $site_key ); ?>" autocomplete="off" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="astara-recaptcha-secret"><?php esc_html_e( 'reCAPTCHA secret key', 'astara' ); ?></label></th>
					<td>
						<input type="password" id="astara-recaptcha-secret" class="regular-text code" name="astara_newsletter[recaptcha_secret_key]" value="" autocomplete="new-password" placeholder="<?php echo $has_secret ? esc_attr__( '(saved — leave blank to keep)', 'astara' ) : ''; ?>" />
						<p class="description">
							<?php
							printf(
								/* translators: %s: link to Google reCAPTCHA admin */
								esc_html__( 'Create a reCAPTCHA v2 "I\'m not a robot" checkbox key pair at %s, and add this site\'s domain.', 'astara' ),
								'<a href="https://www.google.com/recaptcha/admin" target="_blank" rel="noopener noreferrer">google.com/recaptcha/admin</a>'
							);
							?>
						</p>
					</td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * Warn admins on the Subscribers screens when reCAPTCHA isn't set up, since the
 * form would then be protected by the honeypot alone.
 */
function astara_newsletter_admin_notice() {
	$screen = get_current_screen();
	if ( ! $screen || false === strpos( (string) $screen->id, 'astara_subscriber' ) || astara_newsletter_recaptcha_enabled() || ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$url = admin_url( 'edit.php?post_type=astara_subscriber&page=astara-newsletter-settings' );
	printf(
		'<div class="notice notice-warning"><p>%s</p></div>',
		wp_kses(
			sprintf(
				/* translators: %s: link to the settings page */
				__( 'reCAPTCHA is not configured, so the signup form is protected only by a hidden spam trap. Add your keys under <a href="%s">Subscribers → Settings</a>.', 'astara' ),
				esc_url( $url )
			),
			array( 'a' => array( 'href' => array() ) )
		)
	);
}
add_action( 'admin_notices', 'astara_newsletter_admin_notice' );

/**
 * Verify a reCAPTCHA response with Google.
 *
 * @param string $token The g-recaptcha-response value from the form.
 * @return true|WP_Error
 */
function astara_newsletter_verify_recaptcha( $token ) {
	if ( '' === $token ) {
		return new WP_Error( 'astara_captcha_missing', __( 'Please tick "I\'m not a robot".', 'astara' ), array( 'status' => 400 ) );
	}

	$response = wp_remote_post(
		'https://www.google.com/recaptcha/api/siteverify',
		array(
			'timeout' => 10,
			'body'    => array(
				'secret'   => astara_newsletter_setting( 'recaptcha_secret_key' ),
				'response' => $token,
			),
		)
	);

	if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
		return new WP_Error( 'astara_captcha_unavailable', __( 'We couldn\'t verify that you\'re human right now. Please try again in a moment.', 'astara' ), array( 'status' => 503 ) );
	}

	$data = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( empty( $data['success'] ) ) {
		return new WP_Error( 'astara_captcha_failed', __( 'The reCAPTCHA check failed. Please try again.', 'astara' ), array( 'status' => 400 ) );
	}

	return true;
}

/**
 * Handle a signup: validate, check reCAPTCHA, store, notify.
 *
 * @param WP_REST_Request $request The request.
 * @return WP_REST_Response|WP_Error
 */
function astara_newsletter_handle_signup( $request ) {
	$success = rest_ensure_response(
		array(
			'success' => true,
			'message' => __( 'Thanks! You\'re on the list.', 'astara' ),
		)
	);

	// Honeypot: real visitors never see or fill this field. Pretend it worked.
	if ( '' !== trim( (string) $request->get_param( 'website' ) ) ) {
		return $success;
	}

	$email = sanitize_email( (string) $request->get_param( 'email' ) );
	if ( ! is_email( $email ) ) {
		return new WP_Error( 'astara_invalid_email', __( 'Please enter a valid email address.', 'astara' ), array( 'status' => 400 ) );
	}

	// Cheap flood control: one attempt per address per minute, and a site-wide hourly cap.
	$email_key = 'astara_nl_' . md5( strtolower( $email ) );
	if ( get_transient( $email_key ) || (int) get_transient( 'astara_nl_hour' ) >= 100 ) {
		return new WP_Error( 'astara_rate_limited', __( 'Please wait a moment before trying again.', 'astara' ), array( 'status' => 429 ) );
	}

	if ( astara_newsletter_recaptcha_enabled() ) {
		$verified = astara_newsletter_verify_recaptcha( (string) $request->get_param( 'g-recaptcha-response' ) );
		if ( is_wp_error( $verified ) ) {
			return $verified;
		}
	}

	set_transient( $email_key, 1, MINUTE_IN_SECONDS );
	set_transient( 'astara_nl_hour', (int) get_transient( 'astara_nl_hour' ) + 1, HOUR_IN_SECONDS );

	// Already subscribed: say thanks without storing or emailing again.
	$existing = new WP_Query(
		array(
			'post_type'              => 'astara_subscriber',
			'post_status'            => 'any',
			'title'                  => $email,
			'posts_per_page'         => 1,
			'fields'                 => 'ids',
			'no_found_rows'          => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
		)
	);
	if ( $existing->have_posts() ) {
		return $success;
	}

	$id = wp_insert_post(
		array(
			'post_type'   => 'astara_subscriber',
			'post_status' => 'publish',
			'post_title'  => $email,
		),
		true
	);
	if ( is_wp_error( $id ) ) {
		return new WP_Error( 'astara_save_failed', __( 'Sorry, something went wrong. Please try again.', 'astara' ), array( 'status' => 500 ) );
	}

	$sent = wp_mail(
		astara_newsletter_setting( 'notify_email' ),
		/* translators: %s: subscriber email address */
		sprintf( __( 'New newsletter signup: %s', 'astara' ), $email ),
		sprintf(
			/* translators: 1: email address, 2: site name, 3: admin URL of the subscribers list */
			__( "%1\$s asked to be added to the %2\$s mailing list.\n\nAll subscribers: %3\$s", 'astara' ),
			$email,
			wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
			admin_url( 'edit.php?post_type=astara_subscriber' )
		)
	);
	update_post_meta( $id, 'astara_notified', $sent ? '1' : '0' );

	return $success;
}

/**
 * Register the signup REST route. Public on purpose: it's protected by
 * reCAPTCHA, the honeypot and rate limiting rather than by a nonce, because a
 * nonce on a page-cached form would expire.
 */
function astara_newsletter_register_route() {
	register_rest_route(
		'astara/v1',
		'/newsletter',
		array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => 'astara_newsletter_handle_signup',
			'permission_callback' => '__return_true',
		)
	);
}
add_action( 'rest_api_init', 'astara_newsletter_register_route' );

/**
 * [astara_newsletter] — render the signup form and load its script.
 *
 * @return string
 */
function astara_newsletter_shortcode() {
	wp_enqueue_script(
		'astara-newsletter',
		ASTARA_THEME_URI . '/assets/js/newsletter.js',
		array(),
		astara_asset_version( '/assets/js/newsletter.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	$config = array(
		'endpoint' => esc_url_raw( rest_url( 'astara/v1/newsletter' ) ),
		'siteKey'  => astara_newsletter_recaptcha_enabled() ? astara_newsletter_setting( 'recaptcha_site_key' ) : '',
		'i18n'     => array(
			'enterEmail'   => __( 'Please enter your email address.', 'astara' ),
			'confirmHuman' => __( 'Please tick "I\'m not a robot".', 'astara' ),
			'sending'      => __( 'Sending…', 'astara' ),
			'success'      => __( 'Thanks! You\'re on the list.', 'astara' ),
			'error'        => __( 'Sorry, something went wrong. Please try again.', 'astara' ),
		),
	);
	wp_add_inline_script( 'astara-newsletter', 'window.astaraNewsletter = ' . wp_json_encode( $config ) . ';', 'before' );

	ob_start();
	?>
	<div class="astara-contact__newsletter">
		<p class="astara-contact__newsletter-heading"><?php esc_html_e( 'Stay in touch', 'astara' ); ?></p>
		<form class="astara-contact__newsletter-form" data-astara-newsletter novalidate>
			<div class="astara-contact__newsletter-field">
				<label class="astara-sr-only" for="astara-newsletter-email"><?php esc_html_e( 'Email address', 'astara' ); ?></label>
				<input type="email" id="astara-newsletter-email" name="email" placeholder="<?php esc_attr_e( 'Enter Email Address', 'astara' ); ?>" required autocomplete="email" />
				<button type="submit" class="astara-contact__newsletter-submit"><?php esc_html_e( 'Sign up', 'astara' ); ?></button>
			</div>
			<div class="astara-hp" aria-hidden="true">
				<label><?php esc_html_e( 'Leave this field empty', 'astara' ); ?> <input type="text" name="website" tabindex="-1" autocomplete="off" /></label>
			</div>
			<div class="astara-contact__recaptcha" data-astara-recaptcha></div>
			<p class="astara-contact__newsletter-message" role="status" aria-live="polite"></p>
		</form>
	</div>
	<?php
	return (string) ob_get_clean();
}
add_shortcode( 'astara_newsletter', 'astara_newsletter_shortcode' );
