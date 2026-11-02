<?php
/** Product search with a category selector. */
defined( 'ABSPATH' ) || exit;
$woo = class_exists( 'WooCommerce' );
?>
<form role="search" method="get" class="jw-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<?php if ( $woo ) : ?>
	<select name="product_cat" aria-label="<?php esc_attr_e( 'Category', 'jwstore' ); ?>"><option value=""><?php esc_html_e( 'All', 'jwstore' ); ?></option><?php foreach ( jw_top_cats() as $c ) { echo '<option value="' . esc_attr( $c->slug ) . '"' . selected( isset( $_GET['product_cat'] ) ? sanitize_title( wp_unslash( $_GET['product_cat'] ) ) : '', $c->slug, false ) . '>' . esc_html( $c->name ) . '</option>'; } ?></select>
	<input type="hidden" name="post_type" value="product">
	<?php endif; ?>
	<input type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search for products…', 'jwstore' ); ?>" aria-label="<?php esc_attr_e( 'Search', 'jwstore' ); ?>">
	<button type="submit" aria-label="<?php esc_attr_e( 'Search', 'jwstore' ); ?>"><?php echo jw_ic( 'search' ); // phpcs:ignore ?></button>
</form>
