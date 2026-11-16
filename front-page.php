<?php
if ( 'page' === get_option( 'show_on_front' ) ) {
	get_header();
	while ( have_posts() ) {
		the_post();
		the_content();
	}
	get_footer();
	return;
}
get_header();
$hx_products = function_exists( 'wc_get_products' ) ? wc_get_products( array(
	'status'     => 'publish',
	'limit'      => 48,
	'orderby'    => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
	'visibility' => 'catalog',
) ) : array();
?>
<section class="hx-wrap hx-home">
	<?php hx_grid( $hx_products ); ?>
</section>
<?php
get_footer();