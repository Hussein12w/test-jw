<?php
/**
 * Shop / category page with a categories sidebar (JW Store design). Based on WooCommerce archive-product.php.
 *
 * @package jwstore
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );
do_action( 'woocommerce_before_main_content' );
$term = is_product_category() ? get_queried_object() : null;
$img  = $term ? (int) get_term_meta( $term->term_id, 'thumbnail_id', true ) : 0;
?>
<header class="jw-shop-head<?php echo $img ? ' has-img' : ''; ?>">
	<div><h1><?php woocommerce_page_title(); ?></h1>
	<?php if ( $term && $term->description ) : ?><p><?php echo esc_html( wp_strip_all_tags( $term->description ) ); ?></p><?php endif; ?></div>
	<?php if ( $img ) { echo wp_get_attachment_image( $img, 'medium', false, array( 'class' => 'jw-shop-head-img' ) ); } ?>
</header>
<div class="jw-shop">
	<?php jw_shop_sidebar(); ?>
	<div class="jw-shop-main">
	<?php
	if ( woocommerce_product_loop() ) {
		echo '<div class="jw-shop-bar"><button class="jw-btn jw-btn-line only-m" data-jw="filters">' . jw_ic( 'sliders' ) . ' ' . esc_html__( 'Categories', 'jwstore' ) . '</button>'; // phpcs:ignore
		do_action( 'woocommerce_before_shop_loop' );
		echo '</div>';
		woocommerce_product_loop_start();
		if ( wc_get_loop_prop( 'total' ) ) {
			while ( have_posts() ) {
				the_post();
				do_action( 'woocommerce_shop_loop' );
				wc_get_template_part( 'content', 'product' );
			}
		}
		woocommerce_product_loop_end();
		do_action( 'woocommerce_after_shop_loop' );
	} else {
		do_action( 'woocommerce_no_products_found' );
	}
	?>
	</div>
</div>
<?php
do_action( 'woocommerce_after_main_content' );
get_footer( 'shop' );
