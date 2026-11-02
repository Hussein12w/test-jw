<?php
/** Header: top bar (language, links), logo, search, account, wishlist, cart; category menu. */
defined( 'ABSPATH' ) || exit;
$woo   = class_exists( 'WooCommerce' );
$langs = jw_languages();
$cur   = array_values( array_filter( $langs, fn( $l ) => $l[4] ) )[0] ?? $langs[0];
$wa    = jw_wa_number();
$cats  = $woo ? jw_top_cats() : array();
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link screen-reader-text" href="#main"><?php esc_html_e( 'Skip to content', 'jwstore' ); ?></a>

<div class="jw-topbar"><div class="jw-wrap jw-topbar-in">
	<p class="jw-tb-msg"><?php echo jw_ic( 'truck' ); ?> <span><?php echo esc_html( jw_mod( 'jw_topbar', __( 'Fast delivery across the UAE & GCC', 'jwstore' ) ) ); ?></span></p>
	<div class="jw-tb-links">
		<?php
		if ( has_nav_menu( 'topbar' ) ) {
			wp_nav_menu( array( 'theme_location' => 'topbar', 'container' => false, 'items_wrap' => '%3$s', 'depth' => 1 ) );
		} else {
			if ( $t = get_theme_mod( 'jw_track_url' ) ) {
				echo '<a href="' . esc_url( $t ) . '">' . esc_html__( 'Track order', 'jwstore' ) . '</a>';
			}
			if ( $wa ) {
				echo '<a href="' . esc_url( 'https://wa.me/' . $wa ) . '" target="_blank" rel="noopener">' . esc_html__( 'Contact us', 'jwstore' ) . '</a>';
			}
		}
		if ( $sc = get_theme_mod( 'jw_topbar_sc' ) ) {
			echo '<span class="jw-tb-sc">' . do_shortcode( $sc ) . '</span>'; // phpcs:ignore
		}
		?>
		<div class="jw-lang" data-jw-dd>
			<button class="jw-lang-btn" type="button" aria-haspopup="true" aria-expanded="false"><span class="flag"><?php echo esc_html( $cur[2] ); ?></span><span><?php echo esc_html( $cur[1] ); ?></span><?php echo jw_ic( 'chevd', 'sm' ); ?></button>
			<div class="jw-lang-menu" role="menu"><?php foreach ( $langs as $l ) { echo '<a role="menuitem" href="' . esc_url( $l[3] ) . '" class="' . ( $l[4] ? 'on' : '' ) . '" lang="' . esc_attr( $l[0] ) . '" hreflang="' . esc_attr( $l[0] ) . '"><span class="flag">' . esc_html( $l[2] ) . '</span>' . esc_html( $l[1] ) . ( $l[4] ? jw_ic( 'check', 'sm' ) : '' ) . '</a>'; } ?></div>
		</div>
	</div>
</div></div>

<?php if ( $n = jw_mod( 'jw_notice' ) ) : ?>
<div class="jw-notice"><div class="jw-wrap"><?php echo jw_ic( 'info' ); ?> <span><?php echo esc_html( $n ); ?></span></div></div>
<?php endif; ?>

<header class="jw-header" id="jwHeader">
	<div class="jw-wrap jw-header-in">
		<button class="jw-icon-btn jw-menu-btn" data-jw="menu" aria-label="<?php esc_attr_e( 'Menu', 'jwstore' ); ?>"><?php echo jw_ic( 'menu' ); ?></button>
		<?php echo jw_logo(); // phpcs:ignore ?>
		<?php get_search_form(); ?>
		<div class="jw-actions">
			<button class="jw-icon-btn jw-search-toggle" data-jw="search" aria-label="<?php esc_attr_e( 'Search', 'jwstore' ); ?>"><?php echo jw_ic( 'search' ); ?></button>
			<?php if ( $woo ) : ?>
			<a class="jw-icon-btn" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" aria-label="<?php esc_attr_e( 'My account', 'jwstore' ); ?>"><?php echo jw_ic( 'user' ); ?></a>
			<a class="jw-icon-btn jw-wish-link" href="<?php echo esc_url( jw_wishlist_url() ); ?>" aria-label="<?php esc_attr_e( 'Wishlist', 'jwstore' ); ?>"><?php echo jw_ic( 'heart' ); ?><span class="jw-wish-count is-empty">0</span></a>
			<a class="jw-icon-btn jw-cart-btn" href="<?php echo esc_url( wc_get_cart_url() ); ?>" data-jw="cart" aria-label="<?php esc_attr_e( 'Cart', 'jwstore' ); ?>"><?php echo jw_ic( 'cart' ) . jw_cart_count(); // phpcs:ignore ?></a>
			<?php endif; ?>
		</div>
	</div>
	<nav class="jw-nav" aria-label="<?php esc_attr_e( 'Main menu', 'jwstore' ); ?>"><div class="jw-wrap jw-nav-in">
		<?php if ( $cats ) : ?>
		<div class="jw-allcats" data-jw-dd>
			<button class="jw-allcats-btn" type="button" aria-expanded="false"><?php echo jw_ic( 'menu' ); ?> <span><?php esc_html_e( 'All categories', 'jwstore' ); ?></span> <?php echo jw_ic( 'chevd', 'sm' ); ?></button>
			<div class="jw-allcats-menu">
				<?php
				foreach ( $cats as $c ) {
					$kids = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => $c->term_id, 'hide_empty' => true ) );
					echo '<div class="jw-ac-item"><a href="' . esc_url( get_term_link( $c ) ) . '"><span class="jw-ac-img">' . jw_cat_image( $c, 'thumbnail' ) . '</span><b>' . esc_html( $c->name ) . '</b><em>' . (int) $c->count . '</em></a>'; // phpcs:ignore
					if ( $kids && ! is_wp_error( $kids ) ) {
						echo '<div class="jw-ac-kids">' . implode( '', array_map( fn( $k ) => '<a href="' . esc_url( get_term_link( $k ) ) . '">' . esc_html( $k->name ) . '</a>', $kids ) ) . '</div>';
					}
					echo '</div>';
				}
				?>
			</div>
		</div>
		<?php endif; ?>
		<div class="jw-menu">
		<?php
		if ( has_nav_menu( 'primary' ) ) {
			wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'menu_class' => 'jw-menu-list', 'depth' => 2 ) );
		} else {
			echo '<ul class="jw-menu-list"><li class="' . ( is_front_page() ? 'current-menu-item' : '' ) . '"><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'jwstore' ) . '</a></li>';
			if ( $woo ) {
				echo '<li class="' . ( is_shop() ? 'current-menu-item' : '' ) . '"><a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">' . esc_html__( 'Shop', 'jwstore' ) . '</a></li>';
				foreach ( array_slice( $cats, 0, 5 ) as $c ) {
					echo '<li class="' . ( is_product_category( $c->slug ) ? 'current-menu-item' : '' ) . '"><a href="' . esc_url( get_term_link( $c ) ) . '">' . esc_html( $c->name ) . '</a></li>';
				}
				echo '<li><a href="' . esc_url( jw_offers_url() ) . '">' . esc_html__( 'Offers', 'jwstore' ) . '</a></li>';
			}
			echo '</ul>';
		}
		?>
		</div>
	</div></nav>
</header>
<main id="main" class="jw-main">
