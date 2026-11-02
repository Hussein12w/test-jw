<?php
/** Footer, floating WhatsApp, mobile menu drawer, side cart. */
defined( 'ABSPATH' ) || exit;
$woo   = class_exists( 'WooCommerce' );
$wa    = jw_wa_number();
$phone = get_theme_mod( 'jw_phone' );
$email = get_theme_mod( 'jw_email' );
$addr  = jw_mod( 'jw_address', __( 'Al Ain, United Arab Emirates', 'jwstore' ) );
$cats  = $woo ? jw_top_cats() : array();
$socials = array( 'instagram' => 'insta', 'tiktok' => 'tiktok', 'snapchat' => 'snap', 'youtube' => 'yt', 'facebook' => 'fb' );
?>
</main>
<footer class="jw-footer">
	<div class="jw-wrap jw-foot-cols">
		<div class="jw-foot-brand"><?php echo jw_logo( true ); // phpcs:ignore ?>
			<p><?php echo esc_html( get_bloginfo( 'description' ) ?: __( 'Car accessories and parts for Jimny, Jetour, Defender and more — with installation service in Al Ain.', 'jwstore' ) ); ?></p>
			<div class="jw-social"><?php foreach ( $socials as $k => $icon ) { if ( $u = get_theme_mod( 'jw_social_' . $k ) ) { echo '<a href="' . esc_url( $u ) . '" target="_blank" rel="noopener" aria-label="' . esc_attr( ucfirst( $k ) ) . '">' . jw_ic( $icon ) . '</a>'; } } // phpcs:ignore ?></div>
		</div>
		<div><h4><?php esc_html_e( 'Shop', 'jwstore' ); ?></h4>
			<?php
			if ( has_nav_menu( 'footer1' ) ) {
				wp_nav_menu( array( 'theme_location' => 'footer1', 'container' => false, 'items_wrap' => '%3$s', 'depth' => 1 ) );
			} else {
				foreach ( array_slice( $cats, 0, 6 ) as $c ) {
					echo '<a href="' . esc_url( get_term_link( $c ) ) . '">' . esc_html( $c->name ) . '</a>';
				}
				if ( $woo ) {
					echo '<a href="' . esc_url( jw_offers_url() ) . '">' . esc_html__( 'Offers', 'jwstore' ) . '</a>';
				}
			}
			?>
		</div>
		<div><h4><?php esc_html_e( 'Help', 'jwstore' ); ?></h4>
			<?php
			if ( has_nav_menu( 'footer2' ) ) {
				wp_nav_menu( array( 'theme_location' => 'footer2', 'container' => false, 'items_wrap' => '%3$s', 'depth' => 1 ) );
			} elseif ( $woo ) {
				echo '<a href="' . esc_url( wc_get_page_permalink( 'myaccount' ) ) . '">' . esc_html__( 'My account', 'jwstore' ) . '</a><a href="' . esc_url( wc_get_cart_url() ) . '">' . esc_html__( 'Cart', 'jwstore' ) . '</a>';
				if ( $p = get_privacy_policy_url() ) {
					echo '<a href="' . esc_url( $p ) . '">' . esc_html__( 'Privacy policy', 'jwstore' ) . '</a>';
				}
				if ( $t = wc_get_page_permalink( 'terms' ) ) {
					echo '<a href="' . esc_url( $t ) . '">' . esc_html__( 'Terms & conditions', 'jwstore' ) . '</a>';
				}
			}
			?>
		</div>
		<div class="jw-foot-contact"><h4><?php esc_html_e( 'Contact us', 'jwstore' ); ?></h4>
			<?php if ( $wa ) : ?><a href="<?php echo esc_url( 'https://wa.me/' . $wa ); ?>" target="_blank" rel="noopener"><?php echo jw_ic( 'wa' ); ?> <span class="ltr">+<?php echo esc_html( $wa ); ?></span></a><?php endif; ?>
			<?php if ( $phone ) : ?><a href="tel:<?php echo esc_attr( preg_replace( '/[^\d+]/', '', $phone ) ); ?>"><?php echo jw_ic( 'phone' ); ?> <span class="ltr"><?php echo esc_html( $phone ); ?></span></a><?php endif; ?>
			<?php if ( $email ) : ?><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo jw_ic( 'mail' ); ?> <span><?php echo esc_html( $email ); ?></span></a><?php endif; ?>
			<p><?php echo jw_ic( 'pin' ); ?> <span><?php echo esc_html( $addr ); ?></span></p>
		</div>
	</div>
	<div class="jw-foot-bot"><div class="jw-wrap jw-foot-bot-in">
		<span>© <?php echo esc_html( gmdate( 'Y' ) . ' ' . ( get_bloginfo( 'name' ) ?: 'JW Store' ) ); ?> — <?php esc_html_e( 'All rights reserved', 'jwstore' ); ?></span>
		<div class="jw-pay"><?php foreach ( array( 'VISA', 'Mastercard', 'Apple Pay', 'Google Pay', 'tabby', 'tamara' ) as $p ) { echo '<span class="pay-' . esc_attr( sanitize_title( $p ) ) . '">' . esc_html( $p ) . '</span>'; } ?></div>
	</div></div>
</footer>

<?php if ( $wa ) : ?><a class="jw-wa-float" href="<?php echo esc_url( 'https://wa.me/' . $wa ); ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><?php echo jw_ic( 'wa' ); ?></a><?php endif; ?>

<div class="jw-drawer jw-drawer-menu" id="jwMenu" hidden>
	<div class="jw-drawer-bg" data-jw="menu"></div>
	<aside class="jw-drawer-panel">
		<div class="jw-drawer-h"><?php echo jw_logo(); // phpcs:ignore ?><button class="jw-icon-btn" data-jw="menu" aria-label="close"><?php echo jw_ic( 'close' ); ?></button></div>
		<div class="jw-drawer-langs"><?php foreach ( jw_languages() as $l ) { echo '<a href="' . esc_url( $l[3] ) . '" class="' . ( $l[4] ? 'on' : '' ) . '"><span class="flag">' . esc_html( $l[2] ) . '</span>' . esc_html( $l[1] ) . '</a>'; } ?></div>
		<nav class="jw-drawer-nav">
			<?php
			if ( has_nav_menu( 'primary' ) ) {
				wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'depth' => 2 ) );
			} else {
				echo '<a href="' . esc_url( home_url( '/' ) ) . '">' . jw_ic( 'home' ) . esc_html__( 'Home', 'jwstore' ) . '</a>'; // phpcs:ignore
				if ( $woo ) {
					echo '<a href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">' . jw_ic( 'bag' ) . esc_html__( 'Shop', 'jwstore' ) . '</a><a href="' . esc_url( jw_offers_url() ) . '">' . jw_ic( 'tag' ) . esc_html__( 'Offers', 'jwstore' ) . '</a>'; // phpcs:ignore
				}
			}
			?>
			<?php if ( $cats ) : ?><h5><?php esc_html_e( 'Categories', 'jwstore' ); ?></h5><?php foreach ( $cats as $c ) { echo '<a href="' . esc_url( get_term_link( $c ) ) . '"><span class="jw-ac-img">' . jw_cat_image( $c, 'thumbnail' ) . '</span>' . esc_html( $c->name ) . '</a>'; } // phpcs:ignore ?><?php endif; ?>
			<?php if ( $woo ) : ?><h5><?php esc_html_e( 'My account', 'jwstore' ); ?></h5><a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"><?php echo jw_ic( 'user' ); esc_html_e( 'My account', 'jwstore' ); ?></a><a href="<?php echo esc_url( jw_wishlist_url() ); ?>"><?php echo jw_ic( 'heart' ); esc_html_e( 'Wishlist', 'jwstore' ); ?></a><?php endif; ?>
		</nav>
	</aside>
</div>

<?php if ( $woo ) : ?>
<div class="jw-drawer jw-drawer-cart" id="jwCart" hidden>
	<div class="jw-drawer-bg" data-jw="cart"></div>
	<aside class="jw-drawer-panel" aria-label="<?php esc_attr_e( 'Cart', 'jwstore' ); ?>">
		<div class="jw-drawer-h"><b><?php echo jw_ic( 'cart' ); ?> <?php esc_html_e( 'Your cart', 'jwstore' ); ?> <?php echo jw_cart_count(); // phpcs:ignore ?></b><button class="jw-icon-btn" data-jw="cart" aria-label="close"><?php echo jw_ic( 'close' ); ?></button></div>
		<div class="widget_shopping_cart_content"><?php woocommerce_mini_cart(); ?></div>
	</aside>
</div>
<?php endif; ?>
<div class="jw-toasts" id="jwToasts" aria-live="polite"></div>
<?php wp_footer(); ?>
</body>
</html>
