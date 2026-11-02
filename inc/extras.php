<?php
/** Wishlist (cookie, works for visitors), offers page filter, pages created on activation. */
defined( 'ABSPATH' ) || exit;

function jw_wishlist_url() {
	$p = get_page_by_path( 'wishlist' );
	return $p ? get_permalink( $p ) : home_url( '/wishlist/' );
}
function jw_wishlist_ids() {
	$raw = isset( $_COOKIE['jw_wish'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['jw_wish'] ) ) : '';
	return array_values( array_filter( array_map( 'absint', explode( ',', $raw ) ) ) );
}

/* Offers: /shop/?offers=1 lists products on sale. */
function jw_offers_url() {
	return function_exists( 'wc_get_page_permalink' ) ? add_query_arg( 'offers', 1, wc_get_page_permalink( 'shop' ) ) : home_url( '/' );
}
add_action(
	'woocommerce_product_query',
	function ( $q ) {
		if ( ! empty( $_GET['offers'] ) ) { // phpcs:ignore
			$ids = wc_get_product_ids_on_sale();
			$q->set( 'post__in', $ids ? $ids : array( 0 ) );
		}
	}
);
add_filter(
	'woocommerce_page_title',
	function ( $t ) {
		return ! empty( $_GET['offers'] ) ? __( 'Offers', 'jwstore' ) : $t; // phpcs:ignore
	}
);

function jw_create_pages() {
	if ( ! get_page_by_path( 'wishlist' ) ) {
		$id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => 'wishlist', 'post_title' => 'Wishlist' ) );
		update_post_meta( $id, '_wp_page_template', 'page-templates/wishlist.php' );
	}
}
