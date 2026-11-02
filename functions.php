<?php
/**
 * JW Store theme — WooCommerce store for car accessories (Arabic RTL / English LTR).
 */
defined( 'ABSPATH' ) || exit;

define( 'JW_VERSION', '1.0.1' );
define( 'JW_DIR', get_template_directory() );
define( 'JW_URI', get_template_directory_uri() );

require JW_DIR . '/inc/icon-paths.php';
require JW_DIR . '/inc/setup.php';
require JW_DIR . '/inc/helpers.php';
require JW_DIR . '/inc/customizer.php';
require JW_DIR . '/inc/extras.php';
require JW_DIR . '/inc/woo.php';

add_action( 'after_switch_theme', 'jw_create_pages' );
