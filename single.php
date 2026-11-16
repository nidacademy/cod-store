<?php
/**
 * Single blog article (content + sidebar).
 */
get_header();

while ( have_posts() ) :
	the_post();
	$hx_post  = get_post();
	$hx_cat   = hx_post_cat( $hx_post );
	$hx_title = get_the_title();
	$hx_url   = get_permalink();

	$hx_crumbs = array( array( hx_b( 'home' ), home_url( '/' ) ), array( hx_b( 'blog' ), hx_blog_url() ) );
	if ( $hx_cat ) {
		$hx_crumbs[] = array( $hx_cat->name, get_category_link( $hx_cat ) );
	}
	?>
	<div class="hx-blogwrap<?php echo get_theme_mod( 'hx_blog_sidebar_m', true ) ? '' : ' hx-blogwrap--noside-m'; ?>" dir="<?php echo esc_attr( hx_blog_dir() ); ?>">
		<article <?php post_class( 'hx-blogwrap__main hx-article' ); ?>>
			<header class="hx-article__head">
				<?php echo hx_crumbs( $hx_crumbs ); ?>
				<h1 class="hx-article__title"><?php echo esc_html( $hx_title ); ?></h1>
				<div class="hx-post-meta">
					<?php if ( $hx_cat ) : ?>
						<a class="hx-post-meta__cat" href="<?php echo esc_url( get_category_link( $hx_cat ) ); ?>"><?php echo esc_html( $hx_cat->name ); ?></a><span class="hx-dot" aria-hidden="true"></span>
					<?php endif; ?>
					<?php echo hx_post_meta_html( $hx_post ); ?>
				</div>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<figure class="hx-article__cover">
					<?php the_post_thumbnail( 'large', array( 'loading' => 'eager', 'fetchpriority' => 'high', 'alt' => esc_attr( $hx_title ), 'sizes' => '(max-width: 599px) calc(100vw - 32px), (max-width: 989px) calc(100vw - 80px), 800px' ) ); ?>
				</figure>
			<?php endif; ?>

			<div class="hx-prose">
				<?php the_content(); ?>
			</div>

			<?php if ( get_theme_mod( 'hx_blog_share', true ) ) : ?>
				<div class="hx-share">
					<span class="hx-share__label"><?php echo esc_html( hx_b( 'share' ) ); ?></span>
					<div class="hx-share__btns">
						<a class="is-wa" href="<?php echo esc_url( 'https://wa.me/?text=' . rawurlencode( $hx_title . ' ' . $hx_url ) ); ?>" target="_blank" rel="noopener" aria-label="WhatsApp">
							<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm0 18.2a8.2 8.2 0 0 1-4.2-1.1l-.3-.2-3 .8.8-2.9-.2-.3A8.2 8.2 0 1 1 12 20.2zm4.5-6.1c-.2-.1-1.5-.7-1.7-.8-.2-.1-.4-.1-.6.1l-.8 1c-.1.2-.3.2-.5.1a6.7 6.7 0 0 1-3.3-2.9c-.2-.4.2-.4.7-1.3.1-.2 0-.3 0-.4l-.8-1.8c-.2-.5-.4-.4-.6-.4h-.5a1 1 0 0 0-.7.3 3 3 0 0 0-.9 2.2 5.2 5.2 0 0 0 1.1 2.7 11.8 11.8 0 0 0 4.5 4c1.7.7 2.3.8 3.2.6.5-.1 1.5-.6 1.7-1.2.2-.6.2-1.1.2-1.2-.1-.1-.3-.2-.5-.3z"/></svg><span>WhatsApp</span>
						</a>
						<a class="is-fb" href="<?php echo esc_url( 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $hx_url ) ); ?>" target="_blank" rel="noopener" aria-label="Facebook">
							<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M22 12a10 10 0 1 0-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.5h-1.2c-1.2 0-1.6.8-1.6 1.6V12h2.8l-.4 2.9h-2.4v7A10 10 0 0 0 22 12z"/></svg><span>Facebook</span>
						</a>
						<button type="button" class="is-copy" data-url="<?php echo esc_url( $hx_url ); ?>" data-done="<?php echo esc_attr( hx_b( 'copied' ) ); ?>">
							<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M10 13a5 5 0 0 0 7.5.5l3-3a5 5 0 0 0-7-7l-1.7 1.7"/><path d="M14 11a5 5 0 0 0-7.5-.5l-3 3a5 5 0 0 0 7 7l1.7-1.7"/></svg><span><?php echo esc_html( hx_b( 'copy' ) ); ?></span>
						</button>
					</div>
				</div>
			<?php endif; ?>

			<?php
			if ( get_theme_mod( 'hx_blog_related', true ) ) :
				$hx_rel = get_posts( array( 'numberposts' => 2, 'post__not_in' => array( $hx_post->ID ), 'post_status' => 'publish', 'ignore_sticky_posts' => true ) );
				if ( $hx_rel ) :
					?>
					<section class="hx-article__related">
						<h2 class="hx-article__related-title"><?php echo esc_html( hx_b( 'related' ) ); ?></h2>
						<div class="hx-blog__list">
							<?php
							foreach ( $hx_rel as $hx_r ) {
								hx_post_card( $hx_r );
							}
							?>
						</div>
					</section>
					<?php
				endif;
			endif;
			?>

			<p class="hx-article__back"><a href="<?php echo esc_url( hx_blog_url() ); ?>"><?php echo hx_arrow(); ?><?php echo esc_html( hx_b( 'back' ) ); ?></a></p>
		</article>
		<?php echo hx_blog_sidebar( $hx_post->ID ); ?>
	</div>
	<script>
	document.addEventListener('click',function(e){var b=e.target.closest('.hx-share .is-copy');if(!b)return;var u=b.getAttribute('data-url'),s=b.querySelector('span'),o=s.textContent;var done=function(){s.textContent=b.getAttribute('data-done');setTimeout(function(){s.textContent=o;},1800);};if(navigator.clipboard){navigator.clipboard.writeText(u).then(done,function(){prompt('',u);});}else{prompt('',u);}});
	</script>
	<?php
endwhile;

get_footer();
