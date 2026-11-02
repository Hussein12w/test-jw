<?php
/** Theme supports, menus, assets, fonts, language & direction. */
defined( 'ABSPATH' ) || exit;

add_action(
	'after_setup_theme',
	function () {
		load_theme_textdomain( 'jwstore', JW_DIR . '/languages' );
		add_theme_support( 'title-tag' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'comment-list', 'comment-form' ) );
		add_theme_support( 'custom-logo', array( 'height' => 120, 'width' => 180, 'flex-height' => true, 'flex-width' => true ) );
		add_theme_support(
			'woocommerce',
			array(
				'thumbnail_image_width' => 600,
				'single_image_width'    => 900,
				'product_grid'          => array( 'default_columns' => 4, 'default_rows' => 4, 'min_columns' => 2, 'max_columns' => 5 ),
			)
		);
		add_theme_support( 'wc-product-gallery-zoom' );
		add_theme_support( 'wc-product-gallery-lightbox' );
		add_theme_support( 'wc-product-gallery-slider' );
		register_nav_menus(
			array(
				'primary' => __( 'Main menu', 'jwstore' ),
				'topbar'  => __( 'Top bar links', 'jwstore' ),
				'footer1' => __( 'Footer — Shop', 'jwstore' ),
				'footer2' => __( 'Footer — Help & policies', 'jwstore' ),
			)
		);
	}
);

/* Assets: one stylesheet + one small script (no jQuery dependency except WooCommerce's own). */
add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_style( 'jw-fonts', 'https://fonts.googleapis.com/css2?family=Noto+Kufi+Arabic:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600;700;800;900&display=swap', array(), null );
		wp_enqueue_style( 'jw-theme', JW_URI . '/assets/css/theme.css', array(), JW_VERSION );
		wp_enqueue_script( 'jw-theme', JW_URI . '/assets/js/theme.js', array(), JW_VERSION, true );
		wp_localize_script(
			'jw-theme',
			'JW',
			array(
				'rtl'       => is_rtl(),
				'sideCart'  => (bool) get_theme_mod( 'jw_side_cart', 1 ),
				'cartUrl'   => function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '',
				'wishAdded' => __( 'Added to wishlist', 'jwstore' ),
				'wishRm'    => __( 'Removed from wishlist', 'jwstore' ),
				'copied'    => __( 'Link copied', 'jwstore' ),
			)
		);
		if ( function_exists( 'is_woocommerce' ) ) {
			wp_enqueue_script( 'wc-cart-fragments' );
		}
	}
);
add_action(
	'wp_head',
	function () {
		echo '<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><meta name="theme-color" content="#E70E1B">' . "\n";
		if ( ! has_site_icon() ) {
			echo '<link rel="icon" href="' . esc_url( JW_URI . '/assets/img/icon.png' ) . '">' . "\n";
		}
	},
	1
);

/* ---------- Language ----------
 * With TranslatePress (the live site uses it) the plugin switches the language (/ar/ URLs) and the WordPress
 * locale; WordPress then sets the direction (RTL for Arabic). Without TranslatePress the theme offers its own
 * switcher: ?lang=ar|en, remembered in a cookie, which switches the front-end locale.
 */
function jw_has_trp() {
	return function_exists( 'trp_custom_language_switcher' );
}
function jw_fallback_lang() {
	if ( isset( $_GET['lang'] ) && in_array( $_GET['lang'], array( 'ar', 'en' ), true ) ) {
		return $_GET['lang'];
	}
	if ( isset( $_COOKIE['jw_lang'] ) && in_array( $_COOKIE['jw_lang'], array( 'ar', 'en' ), true ) ) {
		return $_COOKIE['jw_lang'];
	}
	return get_theme_mod( 'jw_default_lang', 'ar' );
}
if ( ! is_admin() ) {
	add_filter(
		'locale',
		function ( $locale ) {
			if ( jw_has_trp() || wp_doing_ajax() && ! isset( $_COOKIE['jw_lang'] ) ) {
				return $locale;
			}
			return 'ar' === jw_fallback_lang() ? 'ar' : 'en_US';
		},
		5
	);
}
add_action(
	'template_redirect',
	function () {
		if ( ! jw_has_trp() && isset( $_GET['lang'] ) && in_array( $_GET['lang'], array( 'ar', 'en' ), true ) ) {
			setcookie( 'jw_lang', $_GET['lang'], time() + YEAR_IN_SECONDS, COOKIEPATH ?: '/', COOKIE_DOMAIN, is_ssl(), false );
			wp_safe_redirect( remove_query_arg( 'lang' ) );
			exit;
		}
	},
	1
);
/* Make sure is_rtl() follows the active language even when no Arabic WordPress language pack is installed. */
add_action(
	'wp_loaded',
	function () {
		global $wp_locale;
		if ( ! is_admin() && $wp_locale && 0 === strpos( determine_locale(), 'ar' ) ) {
			$wp_locale->text_direction = 'rtl';
		}
	}
);
/** Languages for the header dropdown: [code, label, flag, url, active]. */
function jw_languages() {
	$flags = array( 'ar' => '🇦🇪', 'en' => '🇬🇧' );
	$names = array( 'ar' => 'العربية', 'en' => 'English' );
	$out   = array();
	if ( jw_has_trp() ) {
		$current = function_exists( 'get_locale' ) ? get_locale() : '';
		foreach ( (array) trp_custom_language_switcher() as $code => $l ) {
			$short = strtolower( substr( $code, 0, 2 ) );
			$out[] = array( $short, $names[ $short ] ?? ( $l['language_name'] ?? $code ), $flags[ $short ] ?? '🌐', $l['current_page_url'] ?? home_url( '/' ), $code === $current );
		}
		return $out;
	}
	$cur = jw_fallback_lang();
	foreach ( array( 'ar', 'en' ) as $c ) {
		$out[] = array( $c, $names[ $c ], $flags[ $c ], add_query_arg( 'lang', $c ), $cur === $c );
	}
	return $out;
}

add_filter(
	'body_class',
	function ( $c ) {
		$c[] = is_rtl() ? 'jw-rtl' : 'jw-ltr';
		return $c;
	}
);

/* Excerpt length for blog cards. */
add_filter( 'excerpt_length', fn() => 22 );
