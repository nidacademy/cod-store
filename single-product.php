<?php
get_header();
while ( have_posts() ) :
	the_post();
	$product = wc_get_product( get_the_ID() );
	if ( ! $product ) {
		continue;
	}
	$hx_d = hx_product_view_data( $product );
	?>
	<div class="hx-wrap hx-product">
		<div class="hx-product__media">
<?php hx_render_gallery( $product, $hx_d, array( 'thumbs' => 'hide' !== hx_pp_val( 'pp_th_show' ) ) ); ?>
		</div>

		<div class="hx-product__info">
			<h1 class="hx-product__title" dir="auto"><?php echo esc_html( $product->get_name() ); ?></h1>
			<?php list( $hx_p, $hx_c ) = hx_prices( $product ); ?>
			<?php
			$hx_o = function_exists( 'hx_offers_get' ) ? hx_offers_get( $product->get_id() ) : array();
			if ( $hx_o ) {
				$hx_was = max( (float) $hx_p, (float) $hx_c ) * $hx_o[0]['qty'];
				$hx_p   = $hx_o[0]['price'];
				$hx_c   = $hx_was > $hx_p + 0.5 ? $hx_was : 0;
			}
			?>
			<?php if ( function_exists( 'hx_render_price_box' ) ) { hx_render_price_box( $product, $hx_p, $hx_c ); } else { ?><div class="hx-pp" dir="rtl" data-hx-pp><?php echo hx_price_block( $hx_p, $hx_c ); ?></div><?php } ?>

<?php hx_render_order_form( $product, $hx_d ); ?>
<?php if ( function_exists( 'hx_render_related' ) ) { hx_render_related( $product, 'form' ); } ?>

			<div class="hx-product__desc" dir="auto">
				<?php echo apply_filters( 'the_content', $product->get_description() ); ?>
			</div>
			<?php if ( function_exists( 'hx_render_related' ) ) { hx_render_related( $product, 'desc' ); } ?>
			<?php if ( function_exists( 'hx_rv_section' ) ) { hx_rv_section( $product ); } ?>
			<?php if ( function_exists( 'hx_render_related' ) ) { hx_render_related( $product, 'reviews' ); } ?>
		</div>
	</div>
	<?php if ( function_exists( 'hx_render_related' ) ) { hx_render_related( $product, 'bottom' ); } ?>
	<?php
endwhile;
get_footer();
