<?php
/**
 * Astara theme bootstrap.
 *
 * @package Astara
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ASTARA_VERSION', '0.1.0' );
define( 'ASTARA_THEME_DIR', get_template_directory() );
define( 'ASTARA_THEME_URI', get_template_directory_uri() );

require ASTARA_THEME_DIR . '/inc/setup.php';
require ASTARA_THEME_DIR . '/inc/enqueue.php';
