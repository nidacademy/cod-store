<?php
/**
 * Blog archive: posts page, categories, tags, search, dates, authors.
 */
get_header();

$hx_blog = hx_b( 'blog' );
$hx_sub  = '';
if ( is_home() ) {
	$hx_title = $hx_blog;
	$hx_sub   = hx_b( 'blog_sub' );
} elseif ( is_search() ) {
	$hx_title = hx_b( 'results' ) . ' “' . get_search_query() . '”';
} elseif ( is_category() || is_tag() ) {
	$hx_term  = get_queried_object();
	$hx_title = $hx_term ? $hx_term->name : '';
	$hx_sub   = $hx_term ? wp_strip_all_tags( term_description( $hx_term ) ) : '';
} else {
	$hx_title = wp_strip_all_tags( get_the_archive_title() );
}

$hx_crumbs = array( array( hx_b( 'home' ), home_url( '/' ) ) );
if ( is_home() ) {
	$hx_crumbs[] = array( $hx_blog, null );
} else {
	$hx_crumbs[] = array( $hx_blog, hx_blog_url() );
	$hx_crumbs[] = array( is_search() ? hx_b( 'search' ) : $hx_title, null );
}
?>
<div class="hx-blogwrap<?php echo get_theme_mod( 'hx_blog_sidebar_m', true ) ? '' : ' hx-blogwrap--noside-m'; ?>" dir="<?php echo esc_attr( hx_blog_dir() ); ?>">
	<div class="hx-blogwrap__main">
		<header class="hx-blog__head">
			<?php echo hx_crumbs( $hx_crumbs ); ?>
			<h1 class="hx-blog__title"><?php echo esc_html( $hx_title ); ?></h1>
			<?php if ( $hx_sub ) : ?>
				<p class="hx-blog__sub"><?php echo esc_html( $hx_sub ); ?></p>
			<?php endif; ?>
		</header>

		<?php if ( have_posts() ) : ?>
			<div class="hx-blog__list">
				<?php
				while ( have_posts() ) :
					the_post();
					hx_post_card( get_post() );
				endwhile;
				?>
			</div>
			<?php
			$hx_links = paginate_links( array(
				'type'      => 'list',
				'mid_size'  => 1,
				'prev_text' => hx_b( 'prev' ),
				'next_text' => hx_b( 'next' ),
			) );
			if ( $hx_links ) {
				echo '<nav class="hx-blog__pager" aria-label="Pagination">' . $hx_links . '</nav>';
			}
			?>
		<?php else : ?>
			<p class="hx-blog__empty"><?php echo esc_html( is_search() ? hx_b( 'noresults' ) : hx_b( 'empty' ) ); ?></p>
		<?php endif; ?>
	</div>
	<?php echo hx_blog_sidebar(); ?>
</div>
<?php
get_footer();
