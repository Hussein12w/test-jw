<?php
/** Pages (also cart, checkout and my account — WooCommerce content is styled by the theme). */
defined( 'ABSPATH' ) || exit;
get_header();
the_post();
$wc = function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() );
?>
<div class="jw-wrap jw-page<?php echo $wc ? ' jw-wc-page' : ''; ?>">
	<?php if ( function_exists( 'woocommerce_breadcrumb' ) && ! is_front_page() ) { woocommerce_breadcrumb(); } ?>
	<h1 class="jw-page-title"><?php the_title(); ?></h1>
	<div class="jw-content"><?php the_content(); ?></div>
</div>
<?php
get_footer();
