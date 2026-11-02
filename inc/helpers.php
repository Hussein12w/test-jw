<?php
/** Helpers: icons, logo, product queries, home sections. */
defined( 'ABSPATH' ) || exit;

function jw_ic( $name, $cls = '' ) {
	static $p = null;
	$p ??= jw_icon_paths();
	return '<svg class="ic ' . esc_attr( $cls ) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ( $p[ $name ] ?? '' ) . '</svg>';
}

function jw_logo( $white = false ) {
	$id  = get_theme_mod( 'custom_logo' );
	$src = $white ? ( get_theme_mod( 'jw_logo_white' ) ?: JW_URI . '/assets/img/logo-white.png' ) : ( $id ? wp_get_attachment_image_url( $id, 'full' ) : JW_URI . '/assets/img/logo.png' );
	return '<a class="jw-logo" href="' . esc_url( home_url( '/' ) ) . '" rel="home"><img src="' . esc_url( $src ) . '" alt="' . esc_attr( get_bloginfo( 'name' ) ?: 'JW Store' ) . '" width="120" height="80"></a>';
}

/** Cart count (refreshed by WooCommerce fragments). */
function jw_cart_count() {
	$n = function_exists( 'WC' ) && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
	return '<span class="jw-cart-count' . ( $n ? '' : ' is-empty' ) . '">' . (int) $n . '</span>';
}

/** Product IDs for home sections. $type: latest | featured | sale | best | cat:<slug> | cats:<slug,slug> */
function jw_products( $type, $limit = 8 ) {
	if ( ! function_exists( 'wc_get_products' ) ) {
		return array();
	}
	$args = array( 'status' => 'publish', 'limit' => $limit, 'return' => 'ids', 'visibility' => 'catalog', 'orderby' => 'date', 'order' => 'DESC' );
	if ( get_theme_mod( 'jw_hide_oos', 1 ) ) {
		$args['stock_status'] = array( 'instock', 'onbackorder' );
	}
	if ( 'featured' === $type ) {
		$args['featured'] = true;
	} elseif ( 'sale' === $type ) {
		$ids = wc_get_product_ids_on_sale();
		if ( ! $ids ) {
			return array();
		}
		$args['include'] = $ids;
	} elseif ( 'best' === $type ) {
		$args['orderby']  = 'meta_value_num';
		$args['meta_key'] = 'total_sales'; // phpcs:ignore
	} elseif ( 0 === strpos( $type, 'cats:' ) ) {
		$args['category'] = array_filter( explode( ',', substr( $type, 5 ) ) );
	} elseif ( 0 === strpos( $type, 'cat:' ) ) {
		$args['category'] = array( substr( $type, 4 ) );
	}
	return wc_get_products( $args );
}

/** Render a list of products with the theme card. */
function jw_product_grid( $ids, $cls = '' ) {
	if ( ! $ids ) {
		return '';
	}
	ob_start();
	echo '<ul class="products jw-grid ' . esc_attr( $cls ) . '">';
	foreach ( $ids as $id ) {
		$GLOBALS['post'] = get_post( $id ); // phpcs:ignore
		setup_postdata( $GLOBALS['post'] );
		wc_get_template_part( 'content', 'product' );
	}
	echo '</ul>';
	wp_reset_postdata();
	return ob_get_clean();
}

function jw_sec_head( $title, $link = '', $sub = '' ) {
	return '<div class="jw-sec-h"><div><h2>' . esc_html( $title ) . '</h2>' . ( $sub ? '<p>' . esc_html( $sub ) . '</p>' : '' ) . '</div>' . ( $link ? '<a class="jw-more" href="' . esc_url( $link ) . '">' . esc_html__( 'View all', 'jwstore' ) . ' ' . jw_ic( 'arrow', 'flip' ) . '</a>' : '' ) . '</div>';
}

/** Top-level product categories (with products), in the order set in WooCommerce. */
function jw_top_cats( $limit = 0 ) {
	$args = array( 'taxonomy' => 'product_cat', 'parent' => 0, 'hide_empty' => true, 'orderby' => 'menu_order', 'order' => 'ASC' );
	if ( $limit ) {
		$args['number'] = $limit;
	}
	$t = get_terms( $args );
	$t = is_wp_error( $t ) ? array() : $t;
	return array_values( array_filter( $t, fn( $c ) => 'uncategorized' !== $c->slug ) );
}
function jw_cat_image( $term, $size = 'woocommerce_thumbnail' ) {
	$id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
	if ( $id ) {
		return wp_get_attachment_image( $id, $size, false, array( 'loading' => 'lazy', 'alt' => esc_attr( $term->name ) ) );
	}
	return '<span class="jw-cat-ph">' . jw_ic( 'tools' ) . '</span>';
}

function jw_wa_number() {
	return preg_replace( '/\D/', '', (string) get_theme_mod( 'jw_whatsapp', '' ) );
}
