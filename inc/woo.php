<?php
/**
 * WooCommerce integration. Checkout stays WooCommerce's standard (classic) checkout, so payment plugins
 * (Stripe, Tabby, Tamara, COD) and the store's Code Snippets (fees per gateway, COD rules, AED symbol) keep working.
 */
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

/* Our own page wrappers. */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
add_action( 'woocommerce_before_main_content', fn() => print '<div class="jw-wrap jw-woo">', 10 );
add_action( 'woocommerce_after_main_content', fn() => print '</div>', 10 );

/* Breadcrumb look. */
add_filter(
	'woocommerce_breadcrumb_defaults',
	function ( $d ) {
		$d['wrap_before'] = '<nav class="jw-crumbs" aria-label="breadcrumb">';
		$d['wrap_after']  = '</nav>';
		$d['delimiter']   = '<span class="sep">' . jw_ic( 'chev', 'flip' ) . '</span>';
		$d['home']        = __( 'Home', 'jwstore' );
		return $d;
	}
);

/* Shop grid: products per page and columns (CSS also forces 2 per row on phones). */
add_filter( 'loop_shop_per_page', fn() => (int) get_theme_mod( 'jw_per_page', 20 ), 20 );
add_filter( 'loop_shop_columns', fn() => 4 );

/* Sale badge with the percentage. */
add_filter(
	'woocommerce_sale_flash',
	function ( $html, $post, $product ) {
		$pct = 0;
		if ( $product->is_type( 'variable' ) ) {
			foreach ( $product->get_children() as $cid ) {
				$c = wc_get_product( $cid );
				if ( $c && $c->get_regular_price() && $c->get_sale_price() ) {
					$pct = max( $pct, round( 100 - ( (float) $c->get_sale_price() / (float) $c->get_regular_price() * 100 ) ) );
				}
			}
		} elseif ( $product->get_regular_price() && $product->get_sale_price() ) {
			$pct = round( 100 - ( (float) $product->get_sale_price() / (float) $product->get_regular_price() * 100 ) );
		}
		return '<span class="onsale jw-badge-sale">' . ( $pct > 0 ? '-' . $pct . '%' : esc_html__( 'Sale', 'jwstore' ) ) . '</span>';
	},
	10,
	3
);

/* Add-to-cart in cards = round icon button (keeps WooCommerce's AJAX classes). */
add_filter(
	'woocommerce_loop_add_to_cart_link',
	function ( $html, $product, $args ) {
		if ( empty( $GLOBALS['jw_in_card'] ) ) {
			return $html;
		}
		$simple = $product->is_purchasable() && $product->is_in_stock() && $product->is_type( 'simple' );
		$icon   = $simple ? jw_ic( 'cart' ) : jw_ic( 'arrow', 'flip' );
		return sprintf(
			'<a href="%s" data-quantity="1" class="%s jw-atc" %s aria-label="%s" title="%s">%s</a>',
			esc_url( $product->add_to_cart_url() ),
			esc_attr( $args['class'] ?? 'button' ),
			isset( $args['attributes'] ) ? wc_implode_html_attributes( $args['attributes'] ) : '',
			esc_attr( $product->add_to_cart_description() ),
			esc_attr( $product->add_to_cart_text() ),
			$icon
		);
	},
	10,
	3
);

/* Header cart count and side-cart content refresh after AJAX add to cart. */
add_filter(
	'woocommerce_add_to_cart_fragments',
	function ( $f ) {
		$f['span.jw-cart-count'] = jw_cart_count();
		return $f;
	}
);

/* Free-shipping progress in the side cart (Customizer → JW Store; 0 = off). */
add_action(
	'woocommerce_before_mini_cart',
	function () {
		$min = (float) get_theme_mod( 'jw_free_ship', 0 );
		if ( ! $min || ! WC()->cart ) {
			return;
		}
		$total = (float) WC()->cart->get_displayed_subtotal();
		$left  = max( 0, $min - $total );
		$pct   = min( 100, $total / $min * 100 );
		echo '<div class="jw-ship-bar"><p>' . ( $left > 0 ? sprintf( /* translators: %s: amount */ esc_html__( 'Add %s more for free shipping', 'jwstore' ), wp_kses_post( wc_price( $left ) ) ) : esc_html__( 'You get free shipping!', 'jwstore' ) ) . '</p><span><i style="width:' . esc_attr( $pct ) . '%"></i></span></div>';
	}
);

/* Product page: Buy now button + trust badges. */
add_action(
	'woocommerce_after_add_to_cart_button',
	function () {
		global $product;
		if ( $product && $product->is_purchasable() && $product->is_in_stock() && ! $product->is_type( 'external' ) && get_theme_mod( 'jw_buy_now', 1 ) ) {
			if ( $product->is_type( 'simple' ) ) {
				echo '<input type="hidden" name="add-to-cart" value="' . (int) $product->get_id() . '">';
			}
			echo '<button type="submit" name="jw_buy_now" value="1" class="button jw-buy-now">' . jw_ic( 'bolt' ) . ' ' . esc_html__( 'Buy now', 'jwstore' ) . '</button>';
		}
	}
);
add_filter(
	'woocommerce_add_to_cart_redirect',
	function ( $url ) {
		return ! empty( $_REQUEST['jw_buy_now'] ) ? wc_get_checkout_url() : $url; // phpcs:ignore
	},
	99
);
add_filter( 'wc_add_to_cart_message_html', fn( $m ) => empty( $_REQUEST['jw_buy_now'] ) ? $m : '', 99 ); // phpcs:ignore
add_action(
	'woocommerce_single_product_summary',
	function () {
		echo '<ul class="jw-trust">';
		foreach ( jw_features() as $f ) {
			echo '<li>' . jw_ic( $f[0] ) . '<span>' . esc_html( $f[1] ) . '</span></li>';
		}
		echo '</ul>';
		if ( $wa = jw_wa_number() ) {
			global $product;
			$msg = sprintf( /* translators: %s: product name */ __( 'Hello, I am interested in: %s', 'jwstore' ), $product->get_name() ) . ' ' . get_permalink( $product->get_id() );
			echo '<a class="jw-wa-ask" href="' . esc_url( 'https://wa.me/' . $wa . '?text=' . rawurlencode( $msg ) ) . '" target="_blank" rel="noopener">' . jw_ic( 'wa' ) . ' ' . esc_html__( 'Ask about this product on WhatsApp', 'jwstore' ) . '</a>';
		}
	},
	45
);
add_filter( 'woocommerce_output_related_products_args', fn( $a ) => array_merge( $a, array( 'posts_per_page' => 4, 'columns' => 4 ) ) );

/* Four store features (header strip, product page). Text editable in the Customizer. */
function jw_features() {
	$d = array(
		array( 'truck', __( 'Fast delivery across the UAE & GCC', 'jwstore' ) ),
		array( 'shield', __( 'Secure payments & cash on delivery', 'jwstore' ) ),
		array( 'card', __( 'Pay in installments with Tabby & Tamara', 'jwstore' ) ),
		array( 'tools', __( 'Installation service available', 'jwstore' ) ),
	);
	foreach ( $d as $i => $f ) {
		$v = get_theme_mod( 'jw_feat' . ( $i + 1 ) . '_' . ( is_rtl() ? 'ar' : 'en' ) );
		if ( $v ) {
			$d[ $i ][1] = $v;
		}
	}
	return $d;
}

/* My account menu icons. */
add_filter(
	'woocommerce_account_menu_items',
	function ( $items ) {
		return $items;
	}
);

/* Shop sidebar: product categories tree. */
function jw_shop_sidebar() {
	$current = is_product_category() ? get_queried_object_id() : 0;
	echo '<aside class="jw-side" id="jwFilters"><div class="jw-side-h"><b>' . esc_html__( 'Categories', 'jwstore' ) . '</b><button class="jw-icon-btn only-m" data-jw="filters" aria-label="close">' . jw_ic( 'close' ) . '</button></div><ul class="jw-cat-list">';
	echo '<li><a class="' . ( is_shop() ? 'on' : '' ) . '" href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">' . jw_ic( 'grid' ) . '<span>' . esc_html__( 'All products', 'jwstore' ) . '</span></a></li>';
	foreach ( jw_top_cats() as $c ) {
		$kids = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => $c->term_id, 'hide_empty' => true ) );
		echo '<li><a class="' . ( $current === $c->term_id ? 'on' : '' ) . '" href="' . esc_url( get_term_link( $c ) ) . '">' . jw_ic( 'tag' ) . '<span>' . esc_html( $c->name ) . '</span><em>' . (int) $c->count . '</em></a>';
		if ( $kids && ! is_wp_error( $kids ) ) {
			echo '<ul>';
			foreach ( $kids as $k ) {
				echo '<li><a class="' . ( $current === $k->term_id ? 'on' : '' ) . '" href="' . esc_url( get_term_link( $k ) ) . '"><span>' . esc_html( $k->name ) . '</span><em>' . (int) $k->count . '</em></a></li>';
			}
			echo '</ul>';
		}
		echo '</li>';
	}
	echo '</ul>';
	if ( is_active_sidebar( 'shop' ) ) {
		dynamic_sidebar( 'shop' );
	}
	if ( $b = get_theme_mod( 'jw_side_banner' ) ) {
		echo '<a class="jw-side-banner" href="' . esc_url( get_theme_mod( 'jw_side_banner_link', wc_get_page_permalink( 'shop' ) ) ) . '"><img src="' . esc_url( $b ) . '" alt="" loading="lazy"></a>';
	}
	echo '</aside>';
}
add_action(
	'widgets_init',
	function () {
		register_sidebar( array( 'name' => __( 'Shop filters (below categories)', 'jwstore' ), 'id' => 'shop', 'before_widget' => '<div class="jw-widget %2$s">', 'after_widget' => '</div>', 'before_title' => '<h4>', 'after_title' => '</h4>' ) );
	}
);
