<?php
/**
 * Product page building blocks (gallery + cash-on-delivery order form).
 * Used by single-product.php.
 *
 * @package hx-store
 */

defined( 'ABSPATH' ) || exit;

/** Images, variations and attributes for a product, in the shape the gallery and order form need. */
function hx_product_view_data( $product ) {
	$img_ids    = array_values( array_filter( array_merge( array( $product->get_image_id() ), $product->get_gallery_image_ids() ) ) );
	$total      = count( $img_ids );
	$variations = array();
	$attributes = array();
	if ( $product->is_type( 'variable' ) ) {
		$attributes = $product->get_variation_attributes();
		foreach ( $product->get_available_variations( 'objects' ) as $v ) {
			list( $vp, $vc ) = hx_prices( $v );
			$hx_vimg = (int) $v->get_image_id( 'edit' );
			$hx_vidx = -1;
			if ( $hx_vimg ) {
				$hx_vidx = array_search( $hx_vimg, array_map( 'intval', $img_ids ), true );
				if ( false === $hx_vidx ) {
					$img_ids[] = $hx_vimg;
					$hx_vidx   = count( $img_ids ) - 1;
				}
			}
			$variations[] = array(
				'id'         => $v->get_id(),
				'attributes' => $v->get_variation_attributes(),
				'price'      => hx_money( $vp ),
				'compare'    => $vc ? hx_money( $vc, false ) : '',
				'in_stock'   => $v->is_in_stock(),
				'html'       => function_exists( 'hx_translate_html' ) ? hx_translate_html( hx_price_block( $vp, $vc ) ) : hx_price_block( $vp, $vc ),
				'image'      => (int) $hx_vidx,
			);
		}
		$total = count( $img_ids );
	}
	return array(
		'img_ids'    => $img_ids,
		'total'      => $total,
		'variations' => $variations,
		'attributes' => $attributes,
	);
}

/** Swipeable product gallery. */
function hx_render_gallery( $product, $d, $opt = array() ) {
	$arrow = '<svg viewBox="0 0 10 6" width="10"><path fill="currentColor" fill-rule="evenodd" d="M9.354.646a.5.5 0 00-.708 0L5 4.293 1.354.646a.5.5 0 00-.708.708l4 4a.5.5 0 00.708 0l4-4a.5.5 0 000-.708z" transform="rotate(%d 5 3)"/></svg>';
	?>
			<div class="hx-gallery<?php echo ( ! empty( $opt['thumbs'] ) && $d['total'] > 1 ) ? ' hx-gallery--thumbs' : ''; ?>" data-hx-gallery>
				<div class="hx-gallery__track" data-hx-track>
					<?php foreach ( $d['img_ids'] as $i => $id ) : ?>
						<div class="hx-gallery__slide">
							<?php echo wp_get_attachment_image( $id, 'large', false, array( 'loading' => $i ? 'lazy' : 'eager', 'alt' => esc_attr( $product->get_name() ) ) ); ?>
						</div>
					<?php endforeach; ?>
				</div>
				<?php if ( ! empty( $opt['thumbs'] ) && $d['total'] > 1 ) : ?>
					<div class="hx-gallery__thumbs">
						<?php foreach ( $d['img_ids'] as $i => $id ) : ?>
							<button type="button" class="hx-gallery__thumb<?php echo 0 === $i ? ' is-active' : ''; ?>" data-hx-go="<?php echo (int) $i; ?>" aria-label="<?php echo (int) ( $i + 1 ); ?>"><?php echo wp_get_attachment_image( $id, 'thumbnail', false, array( 'loading' => 'lazy', 'alt' => '' ) ); ?></button>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
				<?php if ( $d['total'] > 1 ) : ?>
					<div class="hx-gallery__nav">
						<button type="button" class="hx-gallery__btn" data-hx-prev aria-label="Précédent"><?php printf( $arrow, 90 ); ?></button>
						<span class="hx-gallery__count"><span data-hx-current>1</span>/<?php echo (int) $d['total']; ?></span>
						<button type="button" class="hx-gallery__btn" data-hx-next aria-label="Suivant"><?php printf( $arrow, -90 ); ?></button>
					</div>
				<?php endif; ?>
			</div>
	<?php
}

/**
 * Cash-on-delivery order form.
 * $opt: 'sfx' (suffix for element ids), 'id' (form id).
 */
function hx_render_order_form( $product, $d, $opt = array() ) {
	$sfx        = isset( $opt['sfx'] ) ? (string) $opt['sfx'] : '';
	$form_id    = isset( $opt['id'] ) ? (string) $opt['id'] : '';
	$attributes = $d['attributes'];
	$hx_offers  = function_exists( 'hx_offers_get' ) ? hx_offers_get( $product->get_id() ) : array();
	$hx_m       = function_exists( 'hx_form_modes' ) ? hx_form_modes( $product ) : array( 'order' => true, 'wa' => false, 'number' => '' );
	if ( ! $hx_m['order'] && ! $hx_m['wa'] ) {
		return;
	}
	$pin        = '<svg viewBox="0 0 24 24" width="20"><path fill="currentColor" d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"/></svg>';
	?>
			<form class="hx-order <?php echo esc_attr( function_exists( 'hx_cf_classes' ) ? hx_cf_classes() : '' ); ?><?php echo $hx_m['order'] ? '' : ' hx-order--wa-only'; ?>" dir="rtl" data-hx-order novalidate<?php echo $form_id ? ' id="' . esc_attr( $form_id ) . '"' : ''; ?> data-variations="<?php echo esc_attr( wp_json_encode( $d['variations'] ) ); ?>">
				<div class="hx-order__head">
					<h2 class="hx-order__title"><?php echo esc_html( hx_pp_text( 'pp_f_title_txt', 'المرجو إدخال معلوماتك لإتمام عملية الشراء' ) ); ?></h2>
					<p class="hx-order__sub"><?php echo esc_html( hx_pp_text( 'pp_f_sub_txt', 'الدفع عند الاستلام • غادي نتصلو بيك باش نأكدو الطلب' ) ); ?></p>
				</div>
				<input type="hidden" name="action" value="hx_order">
				<input type="hidden" name="product_id" value="<?php echo (int) $product->get_id(); ?>">
				<input type="hidden" name="variation_id" value="">
				<input type="hidden" name="qty" value="<?php echo $hx_offers ? (int) $hx_offers[0]['qty'] : 1; ?>">
				<input type="text" name="website" class="hx-hp" tabindex="-1" autocomplete="off" aria-hidden="true">
				<?php if ( $hx_offers ) { hx_render_offers( $hx_offers, hx_offer_unit( $product ) ); } ?>

				<?php if ( $attributes ) : ?><div class="hx-vgrid"><?php endif; ?>
				<?php foreach ( $attributes as $attr_name => $options ) : ?>
					<?php
					$hx_key   = 'attribute_' . sanitize_title( $attr_name );
					$hx_label = hx_tr_attr( wc_attribute_label( $attr_name, $product ) );
					?>
					<div class="hx-field hx-vsel">
						<label class="hx-vsel__label" for="<?php echo esc_attr( 'hx-' . $hx_key . $sfx ); ?>"><?php echo esc_html( $hx_label ); ?></label>
						<div class="hx-vsel__wrap">
						<select class="hx-field__input hx-vsel__select" id="<?php echo esc_attr( 'hx-' . $hx_key . $sfx ); ?>" data-hx-attr="<?php echo esc_attr( $hx_key ); ?>" aria-label="<?php echo esc_attr( $hx_label ); ?>" required>
							<option value=""></option>
							<?php
							foreach ( $options as $opt_v ) :
								$hx_name = $opt_v;
								if ( taxonomy_exists( $attr_name ) ) {
									$hx_term = get_term_by( 'slug', $opt_v, $attr_name );
									if ( $hx_term ) {
										$hx_name = $hx_term->name;
									}
								}
								?>
								<option value="<?php echo esc_attr( $opt_v ); ?>"><?php echo esc_html( hx_tr_attr( $hx_name ) ); ?></option>
							<?php endforeach; ?>
						</select>
						<span class="hx-vsel__arrow" aria-hidden="true"><svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg></span>
						</div>
					</div>
				<?php endforeach; ?>
				<?php if ( $attributes ) : ?></div><?php endif; ?>

				<?php hx_cf_render_fields( $sfx ); ?>

				<p class="hx-order__msg" data-hx-msg role="alert" hidden></p>

				<div class="hx-order__actions">
				<?php if ( $hx_m['order'] ) : ?>
				<button type="submit" class="hx-order__btn" data-hx-submit>
					<span><?php echo esc_html( hx_pp_text( 'pp_f_btn_txt', 'أطلب الآن' ) ); ?></span>
					<svg viewBox="0 0 24 24" width="22" aria-hidden="true"><path fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" d="M3 4h2l2.4 10.2a1.5 1.5 0 0 0 1.46 1.15h8.1a1.5 1.5 0 0 0 1.45-1.1L20.5 8H6.2M10 20a1 1 0 1 0 0-.01M17 20a1 1 0 1 0 0-.01M11 11.5l1.5 1.5 3-3"/></svg>
				</button>
				<?php endif; ?>
				<?php if ( $hx_m['wa'] ) { hx_render_wa_button( $product, $hx_m['number'] ); } ?>
				</div>
				<?php if ( $hx_m['order'] ) : ?>
				<?php hx_cf_render_trust(); ?>
				<?php endif; ?>
			</form>
	<?php
}
