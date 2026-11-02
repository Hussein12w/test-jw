<?php
/**
 * Template Name: JW Store — Wishlist
 */
defined( 'ABSPATH' ) || exit;
get_header();
$ids = class_exists( 'WooCommerce' ) ? jw_wishlist_ids() : array();
$ids = $ids ? wc_get_products( array( 'include' => $ids, 'limit' => 60, 'return' => 'ids', 'status' => 'publish' ) ) : array();
?>
<div class="jw-wrap jw-page">
	<h1 class="jw-page-title"><?php echo jw_ic( 'heart' ); ?> <?php esc_html_e( 'Wishlist', 'jwstore' ); ?></h1>
	<?php echo $ids ? jw_product_grid( $ids ) : '<div class="jw-empty">' . jw_ic( 'heart' ) . '<p>' . esc_html__( 'Your wishlist is empty.', 'jwstore' ) . '</p><a class="jw-btn" href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">' . esc_html__( 'Continue shopping', 'jwstore' ) . '</a></div>'; // phpcs:ignore ?>
</div>
<?php
get_footer();
