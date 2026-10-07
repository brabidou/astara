<?php
/**
 * Astara theme bootstrap.
 *
 * @package Astara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ASTARA_VERSION', '0.2.0' );
define( 'ASTARA_THEME_DIR', get_template_directory() );
define( 'ASTARA_THEME_URI', get_template_directory_uri() );

require ASTARA_THEME_DIR . '/inc/setup.php';
require ASTARA_THEME_DIR . '/inc/enqueue.php';
require ASTARA_THEME_DIR . '/inc/fields.php';
require ASTARA_THEME_DIR . '/inc/team.php';
require ASTARA_THEME_DIR . '/inc/portfolio.php';
require ASTARA_THEME_DIR . '/inc/blocks.php';
require ASTARA_THEME_DIR . '/inc/portfolio-blocks.php';
require ASTARA_THEME_DIR . '/inc/news-blocks.php';
require ASTARA_THEME_DIR . '/inc/news-links.php';
require ASTARA_THEME_DIR . '/inc/newsletter.php';
require ASTARA_THEME_DIR . '/inc/settings.php';
require ASTARA_THEME_DIR . '/inc/seo.php';
require ASTARA_THEME_DIR . '/inc/accessibility.php';
require ASTARA_THEME_DIR . '/inc/editor.php';
