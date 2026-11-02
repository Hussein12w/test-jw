<?php
/**
 * Product card (JW Store design). Based on WooCommerce content-product.php.
 *
 * @package jwstore
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;
if ( ! is_a( $product, WC_Product::class ) || ! $product->is_visible() ) {
	return;
}
$id   = $product->get_id();
$cats = wc_get_product_terms( $id, 'product_cat', array( 'fields' => 'names' ) );
$GLOBALS['jw_in_card'] = true;
?>
<li <?php wc_product_class( 'jw-card', $product ); ?>>
	<div class="jw-card-media">
		<a href="<?php echo esc_url( $product->get_permalink() ); ?>" class="jw-card-img" tabindex="-1" aria-hidden="true"><?php echo woocommerce_get_product_thumbnail(); // phpcs:ignore ?></a>
		<div class="jw-card-badges">
			<?php
			if ( $product->is_on_sale() ) {
				woocommerce_show_product_loop_sale_flash();
			}
			if ( ! $product->is_in_stock() ) {
				echo '<span class="jw-badge-oos">' . esc_html__( 'Out of stock', 'jwstore' ) . '</span>';
			} elseif ( $product->is_featured() ) {
				echo '<span class="jw-badge-feat">' . jw_ic( 'star', 'sm' ) . '</span>'; // phpcs:ignore
			}
			?>
		</div>
		<button class="jw-wish" data-wish="<?php echo (int) $id; ?>" aria-label="<?php esc_attr_e( 'Add to wishlist', 'jwstore' ); ?>"><?php echo jw_ic( 'heart' ); // phpcs:ignore ?></button>
	</div>
	<div class="jw-card-body">
		<?php if ( $cats ) : ?><span class="jw-card-cat"><?php echo esc_html( $cats[0] ); ?></span><?php endif; ?>
		<h2 class="woocommerce-loop-product__title"><a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h2>
		<?php if ( $product->get_rating_count() ) : ?><div class="jw-card-rating"><?php echo jw_ic( 'star', 'sm' ); ?> <b><?php echo esc_html( number_format( (float) $product->get_average_rating(), 1 ) ); ?></b> <small>(<?php echo (int) $product->get_review_count(); ?>)</small></div><?php endif; ?>
		<div class="jw-card-foot">
			<span class="price"><?php echo $product->get_price_html(); // phpcs:ignore ?></span>
			<?php woocommerce_template_loop_add_to_cart(); ?>
		</div>
	</div>
</li>
<?php
$GLOBALS['jw_in_card'] = false;
