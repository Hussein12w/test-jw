<?php
defined( 'ABSPATH' ) || exit;
get_header();
?>
<div class="jw-wrap jw-page">
	<h1 class="jw-page-title"><?php echo esc_html( is_home() ? get_the_title( get_option( 'page_for_posts' ) ) : wp_strip_all_tags( get_the_archive_title() ) ); ?></h1>
	<div class="jw-posts">
	<?php
	if ( have_posts() ) {
		while ( have_posts() ) {
			the_post();
			echo '<article class="jw-post-card"><a href="' . esc_url( get_permalink() ) . '">' . get_the_post_thumbnail( null, 'medium' ) . '<h2>' . esc_html( get_the_title() ) . '</h2></a><p>' . esc_html( get_the_excerpt() ) . '</p></article>'; // phpcs:ignore
		}
		the_posts_pagination();
	} else {
		echo '<p>' . esc_html__( 'Nothing found.', 'jwstore' ) . '</p>';
	}
	?>
	</div>
</div>
<?php
get_footer();
