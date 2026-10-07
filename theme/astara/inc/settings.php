<?php
/**
 * Global site settings: social profile links and contact details that appear in
 * more than one place (footer, Contact page), editable under Site Settings in
 * the admin instead of being typed into templates.
 *
 * Output goes through two server-rendered blocks used by the footer
 * (astara/social-links, astara/footer-contact, astara/copyright) and two shortcodes used in the
 * Contact page's content ([astara_contact_address], [astara_contact_reach]).
 *
 * @package Astara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Starting values. Used until someone saves the settings page, so the site
 * looks right out of the box. Social URLs start empty, and their icons stay
 * hidden until a URL is entered.
 *
 * @return array<string, string>
 */
function astara_site_setting_defaults() {
	return array(
		'linkedin_url'   => '',
		'x_url'          => '',
		'phone'          => '+1 (609) 529-0199',
		'email'          => 'bd@astaracapital.com',
		'address'        => "550 5th Avenue\nFloor 10\nNew York, NY 10036",
		'footer_address' => "550 5th Ave. FL. 10\nNew York, NY 10036",
	);
}

/**
 * Read one setting. A saved value wins, even when it was cleared to empty on
 * purpose (so a link can be removed); otherwise the default applies.
 *
 * @param string $key Setting key.
 * @return string
 */
function astara_site_setting( $key ) {
	$defaults = astara_site_setting_defaults();
	$saved    = get_option( 'astara_site_settings', array() );

	if ( is_array( $saved ) && array_key_exists( $key, $saved ) ) {
		return (string) $saved[ $key ];
	}

	return isset( $defaults[ $key ] ) ? $defaults[ $key ] : '';
}

/**
 * The settings fields: key => [ label, input type, help text ].
 *
 * @return array<string, array{0: string, 1: string, 2: string}>
 */
function astara_site_setting_fields() {
	return array(
		'linkedin_url'   => array( __( 'LinkedIn profile URL', 'astara' ), 'url', __( 'The LinkedIn icon in the footer links here. Leave blank to hide the icon.', 'astara' ) ),
		'x_url'          => array( __( 'X (Twitter) profile URL', 'astara' ), 'url', __( 'The X icon in the footer links here. Leave blank to hide the icon.', 'astara' ) ),
		'phone'          => array( __( 'Phone', 'astara' ), 'text', __( 'Shown in the footer and on the Contact page. The call link is built from the digits.', 'astara' ) ),
		'email'          => array( __( 'Email', 'astara' ), 'email', __( 'Shown on the Contact page as a mail link.', 'astara' ) ),
		'address'        => array( __( 'Address (Contact page)', 'astara' ), 'textarea', __( 'One line per row, as it should appear on the Contact page.', 'astara' ) ),
		'footer_address' => array( __( 'Address (footer)', 'astara' ), 'textarea', __( 'The shorter version shown in the footer, one line per row.', 'astara' ) ),
	);
}

/**
 * Register the option.
 */
function astara_register_site_settings() {
	register_setting(
		'astara_site_settings_group',
		'astara_site_settings',
		array(
			'type'              => 'array',
			'sanitize_callback' => 'astara_sanitize_site_settings',
			'default'           => array(),
		)
	);
}
add_action( 'admin_init', 'astara_register_site_settings' );

/**
 * Sanitize the settings form.
 *
 * @param mixed $input Submitted values.
 * @return array<string, string>
 */
function astara_sanitize_site_settings( $input ) {
	$input = is_array( $input ) ? $input : array();
	$clean = array();

	foreach ( array_keys( astara_site_setting_fields() ) as $key ) {
		$value = isset( $input[ $key ] ) ? wp_unslash( $input[ $key ] ) : '';

		switch ( $key ) {
			case 'linkedin_url':
			case 'x_url':
				$clean[ $key ] = esc_url_raw( trim( $value ) );
				break;
			case 'email':
				$clean[ $key ] = sanitize_email( $value );
				break;
			case 'address':
			case 'footer_address':
				$clean[ $key ] = sanitize_textarea_field( $value );
				break;
			default:
				$clean[ $key ] = sanitize_text_field( $value );
		}
	}

	return $clean;
}

/**
 * Add the Site Settings page to the admin menu.
 */
function astara_site_settings_menu() {
	add_menu_page(
		__( 'Site Settings', 'astara' ),
		__( 'Site Settings', 'astara' ),
		'manage_options',
		'astara-site-settings',
		'astara_render_site_settings',
		'dashicons-admin-site-alt3',
		59
	);
}
add_action( 'admin_menu', 'astara_site_settings_menu' );

/**
 * Render the settings page.
 */
function astara_render_site_settings() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Site Settings', 'astara' ); ?></h1>
		<?php settings_errors(); // Custom top-level pages don't show the "Settings saved" notice on their own. ?>
		<p><?php esc_html_e( 'Links and contact details used across the site. Changes appear straight away in the footer and on the Contact page.', 'astara' ); ?></p>

		<form method="post" action="options.php">
			<?php settings_fields( 'astara_site_settings_group' ); ?>
			<table class="form-table" role="presentation">
				<?php foreach ( astara_site_setting_fields() as $key => $field ) : ?>
					<tr>
						<th scope="row"><label for="astara-setting-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $field[0] ); ?></label></th>
						<td>
							<?php if ( 'textarea' === $field[1] ) : ?>
								<textarea id="astara-setting-<?php echo esc_attr( $key ); ?>" name="astara_site_settings[<?php echo esc_attr( $key ); ?>]" class="large-text" rows="3"><?php echo esc_textarea( astara_site_setting( $key ) ); ?></textarea>
							<?php else : ?>
								<input type="<?php echo esc_attr( $field[1] ); ?>" id="astara-setting-<?php echo esc_attr( $key ); ?>" name="astara_site_settings[<?php echo esc_attr( $key ); ?>]" class="regular-text" value="<?php echo esc_attr( astara_site_setting( $key ) ); ?>" <?php echo in_array( $key, array( 'linkedin_url', 'x_url' ), true ) ? 'placeholder="https://"' : ''; ?> />
							<?php endif; ?>
							<p class="description"><?php echo esc_html( $field[2] ); ?></p>
						</td>
					</tr>
				<?php endforeach; ?>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * A line-broken, escaped version of a multi-line setting.
 *
 * @param string $key Setting key.
 * @return string Escaped HTML.
 */
function astara_site_setting_lines( $key ) {
	return nl2br( esc_html( astara_site_setting( $key ) ) );
}

/**
 * A tel: URL built from a phone number's digits (and leading plus).
 *
 * @param string $phone Phone number as displayed.
 * @return string
 */
function astara_phone_href( $phone ) {
	return 'tel:' . preg_replace( '/[^\d+]/', '', $phone );
}

/**
 * Footer social icons. Each icon only appears once its URL is set.
 *
 * @return string
 */
function astara_render_social_links() {
	$links = array(
		'linkedin' => array( astara_site_setting( 'linkedin_url' ), __( 'LinkedIn', 'astara' ) ),
		'x'        => array( astara_site_setting( 'x_url' ), __( 'X', 'astara' ) ),
	);

	$html = '';
	foreach ( $links as $slug => $link ) {
		if ( '' === $link[0] ) {
			continue;
		}
		$html .= sprintf(
			'<a class="astara-footer__social-icon astara-footer__social-icon--%1$s" href="%2$s" target="_blank" rel="noopener noreferrer" aria-label="%3$s"></a>',
			esc_attr( $slug ),
			esc_url( $link[0] ),
			esc_attr( $link[1] )
		);
	}

	return $html ? '<div class="astara-footer__social">' . $html . '</div>' : '';
}

/**
 * The footer's phone and address lines.
 *
 * @return string
 */
function astara_render_footer_contact() {
	$html  = '';
	$phone = astara_site_setting( 'phone' );

	if ( '' !== $phone ) {
		$html .= '<p class="astara-footer__tel">' . esc_html__( 'TEL:', 'astara' ) . ' ' . esc_html( $phone ) . '</p>';
	}
	if ( '' !== astara_site_setting( 'footer_address' ) ) {
		$html .= '<p class="astara-footer__address">' . astara_site_setting_lines( 'footer_address' ) . '</p>'; // Escaped inside.
	}

	return $html;
}

/**
 * The footer copyright line, with the current year so it never goes stale.
 *
 * @return string
 */
function astara_render_copyright() {
	return '<p class="astara-footer__copyright">&copy; ' . esc_html( wp_date( 'Y' ) ) . ' ASTARA</p>';
}

/**
 * Register the server-rendered blocks used by the footer.
 */
function astara_register_settings_blocks() {
	register_block_type( 'astara/social-links', array( 'render_callback' => 'astara_render_social_links' ) );
	register_block_type( 'astara/footer-contact', array( 'render_callback' => 'astara_render_footer_contact' ) );
	register_block_type( 'astara/copyright', array( 'render_callback' => 'astara_render_copyright' ) );
}
add_action( 'init', 'astara_register_settings_blocks' );

/**
 * [astara_contact_address] — the Contact page's address paragraph.
 *
 * @return string
 */
function astara_contact_address_shortcode() {
	return '<p class="astara-contact__address">' . astara_site_setting_lines( 'address' ) . '</p>'; // Escaped inside.
}
add_shortcode( 'astara_contact_address', 'astara_contact_address_shortcode' );

/**
 * [astara_contact_reach] — the Contact page's phone and email links.
 *
 * @return string
 */
function astara_contact_reach_shortcode() {
	$links = array();
	$phone = astara_site_setting( 'phone' );
	$email = astara_site_setting( 'email' );

	if ( '' !== $phone ) {
		$links[] = '<a href="' . esc_url( astara_phone_href( $phone ) ) . '">' . esc_html( $phone ) . '</a>';
	}
	if ( '' !== $email ) {
		$links[] = '<a href="' . esc_url( 'mailto:' . $email ) . '">' . esc_html( antispambot( $email ) ) . '</a>';
	}

	return $links ? '<p class="astara-contact__reach">' . implode( '<br />', $links ) . '</p>' : ''; // Each part escaped above.
}
add_shortcode( 'astara_contact_reach', 'astara_contact_reach_shortcode' );
