<?php
get_header();
?>
<section class="hx-wrap hx-page">
	<?php
	if ( have_posts() ) :
		while ( have_posts() ) :
			the_post();
			?>
			<article <?php post_class(); ?>>
				<h1 class="hx-page__title" dir="auto"><?php the_title(); ?></h1>
				<div class="hx-page__content" dir="auto"><?php the_content(); ?></div>
			</article>
			<?php
		endwhile;
	endif;
	?>
</section>
<?php
get_footer();