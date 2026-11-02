<?php
/** Home page — every section fills itself from WooCommerce (new products / categories appear automatically). */
defined( 'ABSPATH' ) || exit;
get_header();
$woo   = class_exists( 'WooCommerce' );
$count = max( 4, (int) get_theme_mod( 'jw_home_count', 10 ) );
$slides = array();
for ( $i = 1; $i <= 3; $i++ ) {
	$img   = get_theme_mod( "jw_hero{$i}_img" );
	$title = jw_mod( "jw_hero{$i}_title", 1 === $i ? __( 'Build your ride your way', 'jwstore' ) : '' );
	if ( $title || $img ) {
		$slides[] = array( 'img' => $img, 'title' => $title, 'sub' => jw_mod( "jw_hero{$i}_sub", 1 === $i ? __( 'Premium accessories for Jimny, Jetour & Defender — with installation service', 'jwstore' ) : '' ), 'link' => get_theme_mod( "jw_hero{$i}_link" ) ?: ( $woo ? wc_get_page_permalink( 'shop' ) : home_url( '/' ) ) );
	}
}
?>
<section class="jw-wrap jw-hero-wrap">
	<div class="jw-hero" data-jw-slider>
		<?php foreach ( $slides as $i => $s ) : ?>
		<div class="jw-slide<?php echo 0 === $i ? ' on' : ''; ?><?php echo $s['img'] ? '' : ' no-img'; ?>"<?php echo $s['img'] ? ' style="background-image:url(\'' . esc_url( $s['img'] ) . '\')"' : ''; ?>>
			<div class="jw-slide-txt">
				<h1><?php echo esc_html( $s['title'] ); ?></h1>
				<?php if ( $s['sub'] ) : ?><p><?php echo esc_html( $s['sub'] ); ?></p><?php endif; ?>
				<div class="jw-hero-btns"><a class="jw-btn" href="<?php echo esc_url( $s['link'] ); ?>"><?php esc_html_e( 'Shop now', 'jwstore' ); ?> <?php echo jw_ic( 'arrow', 'flip' ); ?></a>
				<?php if ( 0 === $i && $woo ) : ?><a class="jw-btn jw-btn-ghost" href="#jwCats"><?php esc_html_e( 'Browse categories', 'jwstore' ); ?></a><?php endif; ?></div>
			</div>
			<?php if ( ! $s['img'] ) : ?><img class="jw-hero-car" src="<?php echo esc_url( JW_URI . '/assets/img/hero-jimny.webp' ); ?>" alt="" width="1200" height="675" <?php echo 0 === $i ? 'fetchpriority="high"' : 'loading="lazy"'; ?>><?php endif; ?>
		</div>
		<?php endforeach; ?>
		<?php if ( count( $slides ) > 1 ) : ?><div class="jw-dots"><?php foreach ( $slides as $i => $s ) { echo '<button class="' . ( 0 === $i ? 'on' : '' ) . '" data-i="' . (int) $i . '" aria-label="' . esc_attr( $i + 1 ) . '"></button>'; } ?></div><?php endif; ?>
	</div>
</section>

<section class="jw-wrap jw-features">
	<?php foreach ( jw_features() as $f ) : ?><div class="jw-feat"><span><?php echo jw_ic( $f[0] ); ?></span><b><?php echo esc_html( $f[1] ); ?></b></div><?php endforeach; ?>
</section>

<?php if ( $woo ) : ?>
	<?php $cats = jw_top_cats(); if ( $cats ) : ?>
<section class="jw-wrap jw-sec" id="jwCats"><?php echo jw_sec_head( __( 'Shop by category', 'jwstore' ), wc_get_page_permalink( 'shop' ) ); // phpcs:ignore ?>
	<div class="jw-cats"><?php foreach ( $cats as $c ) { echo '<a class="jw-cat" href="' . esc_url( get_term_link( $c ) ) . '"><span class="jw-cat-img">' . jw_cat_image( $c ) . '</span><b>' . esc_html( $c->name ) . '</b><small>' . esc_html( sprintf( /* translators: %d: number */ _n( '%d product', '%d products', $c->count, 'jwstore' ), $c->count ) ) . '</small></a>'; } // phpcs:ignore ?></div>
</section>
	<?php endif; ?>

	<?php
	/* 1) Jimny first: every category whose name or slug contains the keyword (Customizer → Home sections). */
	$first_kw    = trim( (string) get_theme_mod( 'jw_home_first', 'jimny' ) );
	$first_terms = array();
	if ( $first_kw ) {
		$all = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true ) );
		foreach ( is_wp_error( $all ) ? array() : $all as $t ) {
			if ( false !== stripos( $t->slug, $first_kw ) || false !== stripos( $t->name, $first_kw ) ) {
				$first_terms[] = $t;
			}
		}
	}
	if ( $first_terms && ( $ids = jw_products( 'cats:' . implode( ',', wp_list_pluck( $first_terms, 'slug' ) ), $count ) ) ) :
		$tops = array_values( array_filter( $first_terms, fn( $t ) => 0 === (int) $t->parent ) );
		$link = get_term_link( $tops ? $tops[0] : $first_terms[0] );
		?>
<section class="jw-wrap jw-sec"><?php echo jw_sec_head( jw_mod( 'jw_home_first_title', __( 'Jimny products', 'jwstore' ) ), is_wp_error( $link ) ? '' : $link ) . jw_product_grid( $ids ); // phpcs:ignore ?></section>
	<?php endif; ?>

	<?php if ( get_theme_mod( 'jw_home_featured', 0 ) && ( $ids = jw_products( 'featured', $count ) ) ) : ?>
<section class="jw-wrap jw-sec"><?php echo jw_sec_head( __( 'Featured products', 'jwstore' ), wc_get_page_permalink( 'shop' ) ) . jw_product_grid( $ids ); // phpcs:ignore ?></section>
	<?php endif; ?>

	<?php if ( get_theme_mod( 'jw_home_new', 1 ) && ( $ids = jw_products( 'latest', $count ) ) ) : ?>
<section class="jw-wrap jw-sec"><?php echo jw_sec_head( __( 'New arrivals', 'jwstore' ), wc_get_page_permalink( 'shop' ) ) . jw_product_grid( $ids ); // phpcs:ignore ?></section>
	<?php endif; ?>

	<?php
	$slugs = array_filter( array_map( 'trim', explode( ',', (string) get_theme_mod( 'jw_home_cats', '' ) ) ) );
	$list  = $slugs ? array_filter( array_map( fn( $s ) => get_term_by( 'slug', $s, 'product_cat' ), $slugs ) ) : $cats;
	$skip  = wp_list_pluck( $first_terms, 'term_id' );
	foreach ( $list as $c ) :
		if ( in_array( $c->term_id, $skip, true ) ) {
			continue;
		}
		$ids = jw_products( 'cat:' . $c->slug, $count );
		if ( ! $ids ) {
			continue;
		}
		?>
<section class="jw-wrap jw-sec"><?php echo jw_sec_head( $c->name, get_term_link( $c ) ) . jw_product_grid( $ids ); // phpcs:ignore ?></section>
	<?php endforeach; ?>

	<?php if ( get_theme_mod( 'jw_home_sale', 1 ) && ( $sale = jw_products( 'sale', $count ) ) ) : ?>
<section class="jw-wrap jw-sec">
	<a class="jw-offer" href="<?php echo esc_url( get_theme_mod( 'jw_offer_link' ) ?: jw_offers_url() ); ?>"<?php echo ( $oi = get_theme_mod( 'jw_offer_img' ) ) ? ' style="--bg:url(\'' . esc_url( $oi ) . '\')"' : ''; ?>>
		<div><h2><?php echo esc_html( jw_mod( 'jw_offer_title', __( 'Special offers', 'jwstore' ) ) ); ?></h2><p><?php echo esc_html( jw_mod( 'jw_offer_sub', __( 'Discounts on top accessories for a limited time', 'jwstore' ) ) ); ?></p><span class="jw-btn jw-btn-white"><?php esc_html_e( 'Shop the sale', 'jwstore' ); ?> <?php echo jw_ic( 'arrow', 'flip' ); ?></span></div>
	</a>
	<?php echo jw_product_grid( $sale ); // phpcs:ignore ?>
</section>
	<?php endif; ?>

	<?php if ( get_theme_mod( 'jw_home_best', 0 ) && ( $ids = jw_products( 'best', $count ) ) ) : ?>
<section class="jw-wrap jw-sec"><?php echo jw_sec_head( __( 'Best sellers', 'jwstore' ) ) . jw_product_grid( $ids ); // phpcs:ignore ?></section>
	<?php endif; ?>
<?php endif; ?>

<?php if ( get_theme_mod( 'jw_home_steps', 1 ) ) : ?>
<section class="jw-wrap jw-sec jw-steps-sec"><?php echo jw_sec_head( __( 'How we work', 'jwstore' ) ); // phpcs:ignore ?>
	<ol class="jw-steps">
		<li><span>1</span><b><?php esc_html_e( 'Choose your part', 'jwstore' ); ?></b><p><?php esc_html_e( 'Browse by your car and pick the accessory you need.', 'jwstore' ); ?></p></li>
		<li><span>2</span><b><?php esc_html_e( 'Order online', 'jwstore' ); ?></b><p><?php esc_html_e( 'Pay by card, Apple Pay, installments or cash on delivery.', 'jwstore' ); ?></p></li>
		<li><span>3</span><b><?php esc_html_e( 'Book installation', 'jwstore' ); ?></b><p><?php esc_html_e( 'Optional: add a note at checkout to install it at our service center.', 'jwstore' ); ?></p></li>
		<li><span>4</span><b><?php esc_html_e( 'Receive & enjoy', 'jwstore' ); ?></b><p><?php esc_html_e( 'Fast delivery across the UAE and GCC.', 'jwstore' ); ?></p></li>
	</ol>
</section>
<?php endif; ?>
<?php
get_footer();
