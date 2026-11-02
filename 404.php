<?php
defined( 'ABSPATH' ) || exit;
get_header();
?>
<div class="jw-wrap jw-page"><div class="jw-empty"><b class="jw-404">404</b><p><?php esc_html_e( 'Sorry, this page was not found.', 'jwstore' ); ?></p><?php get_search_form(); ?><a class="jw-btn" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'jwstore' ); ?></a></div></div>
<?php
get_footer();
