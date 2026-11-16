<?php
/**
 * Offer on the thank-you page: one tap adds a product to the order the customer just placed,
 * at a discount (same parcel, same cash-on-delivery payment).
 *  - Offered product: the ordered product's first Cross-sell (Products > Edit > Linked products),
 *    otherwise the default chosen in Customize > COD Form > Thank-you page offer.
 *  - Only simple products in stock, not already in the order; once per order; within 30 minutes.
 *
 * @package hx-store
 */

defined( 'ABSPATH' ) || exit;

function hx_ty_opt( $key ) {
	$def = array(
		'hx_ty_on'    => 'show',
		'hx_ty_prod'  => 'auto',
		'hx_ty_disc'  => 20,
		'hx_ty_title' => 'عرض خاص لك فقط 🎁',
		'hx_ty_text'  => 'أضف هذا المنتج إلى طلبك بخصم {discount}% — يصلك مع نفس الطلب وتدفع عند الاستلام.',
		'hx_ty_btn'   => 'أضف إلى طلبي',
	);
	$v = get_theme_mod( $key, null ); // no default here: WordPress would run it through sprintf (it contains %).
	return null === $v ? $def[ $key ] : $v;
}

/** Is a product a valid offer for this order? */
function hx_ty_valid( $p, $in_order ) {
	return $p && $p->is_type( 'simple' ) && 'publish' === $p->get_status() && $p->is_in_stock() && (float) $p->get_price() > 0 && ! in_array( $p->get_id(), $in_order, true );
}

/** The product to offer for an order (or null). */
function hx_ty_product( $order ) {
	$in = array();
	$first = 0;
	if ( $order ) {
		foreach ( $order->get_items() as $item ) {
			$in[]  = (int) $item->get_product_id();
			$first = $first ? $first : (int) $item->get_product_id();
		}
	}
	if ( $first ) {
		$fp = wc_get_product( $first );
		if ( $fp ) {
			foreach ( $fp->get_cross_sell_ids() as $cid ) {
				$c = wc_get_product( $cid );
				if ( hx_ty_valid( $c, $in ) ) {
					return $c;
				}
			}
		}
	}
	$def = (string) hx_ty_opt( 'hx_ty_prod' );
	if ( 'none' === $def ) {
		return null;
	}
	if ( 'auto' !== $def ) {
		$c = wc_get_product( (int) $def );
		return hx_ty_valid( $c, $in ) ? $c : null;
	}
	// Automatic: best seller from the same category, then any best seller.
	$cats = $first ? wc_get_product_term_ids( $first, 'product_cat' ) : array();
	foreach ( array( $cats, array() ) as $cat_ids ) {
		$args = array(
			'status'   => 'publish',
			'type'     => 'simple',
			'limit'    => 6,
			'exclude'  => $in,
			'orderby'  => 'meta_value_num',
			'meta_key' => 'total_sales', // phpcs:ignore WordPress.DB.SlowDBQuery
			'order'    => 'DESC',
		);
		if ( $cat_ids ) {
			$args['product_category_id'] = $cat_ids;
		}
		foreach ( wc_get_products( $args ) as $c ) {
			if ( hx_ty_valid( $c, $in ) ) {
				return $c;
			}
		}
	}
	return null;
}

/** Normal and offer price of a product. */
function hx_ty_prices( $p ) {
	$now  = function_exists( 'hx_prices' ) ? (float) hx_prices( $p )[0] : (float) $p->get_price();
	$disc = max( 0, min( 90, (int) hx_ty_opt( 'hx_ty_disc' ) ) );
	return array( $now, round( $now * ( 100 - $disc ) / 100 ), $disc );
}

/** Can this order still take the offer? */
function hx_ty_order_ok( $order ) {
	if ( ! $order || $order->get_meta( '_hx_ty_added' ) || ! in_array( $order->get_status(), array( 'pending', 'processing', 'on-hold' ), true ) ) {
		return false;
	}
	$created = $order->get_date_created();
	return $created && ( time() - $created->getTimestamp() ) < 30 * MINUTE_IN_SECONDS;
}

/** Print the offer card on the thank-you page. */
function hx_ty_offer( $order ) {
	if ( 'hide' === hx_ty_opt( 'hx_ty_on' ) ) {
		return;
	}
	$demo = ! $order && is_customize_preview();
	if ( ! $demo && ! hx_ty_order_ok( $order ) ) {
		return;
	}
	$p = hx_ty_product( $order );
	if ( ! $p ) {
		return;
	}
	list( $now, $offer, $disc ) = hx_ty_prices( $p );
	$text = str_replace( '{discount}', (string) $disc, (string) hx_ty_opt( 'hx_ty_text' ) );
	?>
	<div class="hx-ty" dir="rtl" data-hx-ty data-order="<?php echo $order ? (int) $order->get_id() : 0; ?>" data-key="<?php echo $order ? esc_attr( $order->get_order_key() ) : ''; ?>" data-product="<?php echo (int) $p->get_id(); ?>">
		<p class="hx-ty__title"><?php echo esc_html( (string) hx_ty_opt( 'hx_ty_title' ) ); ?></p>
		<div class="hx-ty__card">
			<div class="hx-ty__img"><?php echo $p->get_image_id() ? wp_get_attachment_image( $p->get_image_id(), 'woocommerce_thumbnail', false, array( 'alt' => esc_attr( $p->get_name() ) ) ) : wc_placeholder_img( 'woocommerce_thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
			<div class="hx-ty__info">
				<b class="hx-ty__name" dir="auto"><?php echo esc_html( $p->get_name() ); ?></b>
				<div class="hx-ty__price">
					<span class="hx-ty__now"><?php echo hx_m( $offer ) . ' ' . hx_c(); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					<?php if ( $offer < $now ) : ?>
						<s><?php echo hx_m( $now ) . ' ' . hx_c(); // phpcs:ignore WordPress.Security.EscapeOutput ?></s>
						<span class="hx-ty__badge"><?php echo esc_html( '-' . $disc . '%' ); ?></span>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<p class="hx-ty__text"><?php echo esc_html( $text ); ?></p>
		<button type="button" class="hx-ty__add" data-hx-ty-add><?php echo esc_html( (string) hx_ty_opt( 'hx_ty_btn' ) ); ?></button>
		<button type="button" class="hx-ty__no" data-hx-ty-no>لا، شكراً</button>
		<p class="hx-ty__msg" data-hx-ty-msg hidden></p>
	</div>
	<style>
	.hx-ty{margin:18px 0 6px;padding:16px;border:2px dashed var(--hx-badge,#c62828);border-radius:16px;background:color-mix(in srgb,var(--hx-badge,#c62828) 4%,#fff);font-family:"Cairo",var(--hx-font);letter-spacing:0;text-align:right}
	.hx-ty__title{margin:0 0 12px;color:var(--hx-badge,#c62828);font-size:18px;font-weight:800;text-align:center}
	.hx-ty__card{display:flex;align-items:center;gap:12px}
	.hx-ty__img{flex:0 0 84px;width:84px;height:84px;overflow:hidden;border-radius:12px;background:#f3f4f6}
	.hx-ty__img img{display:block;width:100%;height:100%;object-fit:cover}
	.hx-ty__info{flex:1 1 auto;display:grid;gap:4px;min-width:0}
	.hx-ty__name{color:var(--hx-ink,#111);font-size:15px;font-weight:800;line-height:1.4}
	.hx-ty__price{display:flex;flex-wrap:wrap;align-items:center;gap:4px 10px}
	.hx-ty__now{color:var(--hx-ink,#111);font-size:20px;font-weight:800}
	.hx-ty__price s{color:#9aa0a6;font-size:14px;font-weight:600}
	.hx-ty__badge{padding:2px 8px;border-radius:999px;background:var(--hx-badge,#c62828);color:#fff;font-size:12px;font-weight:800}
	.hx-ty__text{margin:12px 0;color:#374151;font-size:14px;font-weight:600;line-height:1.6}
	.hx-ty__add{display:block;width:100%;min-height:52px;border:0;border-radius:12px;background:var(--hx-primary);color:#fff;font:inherit;font-size:18px;font-weight:800;cursor:pointer;box-shadow:0 6px 16px rgba(var(--hx-primary-rgb),.3)}
	.hx-ty__add:disabled{opacity:.6}
	.hx-ty__no{display:block;margin:8px auto 0;padding:4px 8px;border:0;background:none;color:#6b7280;font:inherit;font-size:14px;text-decoration:underline;cursor:pointer}
	.hx-ty__msg{margin:10px 0 0;color:#b91c1c;font-size:14px;font-weight:700;text-align:center}
	.hx-ty__msg.is-ok{color:#15803d;font-size:16px}
	</style>
	<script>
	(function () {
		var box = document.querySelector('[data-hx-ty]');
		if (!box) { return; }
		var add = box.querySelector('[data-hx-ty-add]'), no = box.querySelector('[data-hx-ty-no]'), msg = box.querySelector('[data-hx-ty-msg]');
		no.addEventListener('click', function () { box.remove(); });
		add.addEventListener('click', function () {
			if (box.getAttribute('data-order') === '0') { return; }
			add.disabled = true;
			var fd = new FormData();
			fd.append('action', 'hx_ty_add');
			fd.append('order', box.getAttribute('data-order'));
			fd.append('key', box.getAttribute('data-key'));
			fd.append('product', box.getAttribute('data-product'));
			fetch(<?php echo wp_json_encode( admin_url( 'admin-ajax.php' ) ); ?>, { method: 'POST', body: fd, credentials: 'same-origin' })
				.then(function (r) { return r.json(); })
				.then(function (res) {
					msg.hidden = false;
					if (res && res.success) {
						msg.classList.add('is-ok');
						msg.textContent = res.data.message;
						add.hidden = true; no.hidden = true;
						setTimeout(function () { window.location.reload(); }, 1800);
					} else {
						msg.textContent = (res && res.data && res.data.message) || 'حدث خطأ، المرجو المحاولة مرة أخرى';
						add.disabled = false;
					}
				})
				.catch(function () { msg.hidden = false; msg.textContent = 'حدث خطأ، المرجو المحاولة مرة أخرى'; add.disabled = false; });
		});
	})();
	</script>
	<?php
}

/* Add the offered product to the order. */
function hx_ty_add() {
	// phpcs:disable WordPress.Security.NonceVerification -- the secret order key authorises the change.
	$fail  = function ( $m ) {
		wp_send_json_error( array( 'message' => $m ), 400 );
	};
	$order = isset( $_POST['order'] ) ? wc_get_order( absint( $_POST['order'] ) ) : null;
	$key   = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
	if ( ! $order || '' === $key || ! hash_equals( $order->get_order_key(), $key ) ) {
		$fail( 'حدث خطأ، المرجو المحاولة مرة أخرى' );
	}
	if ( ! hx_ty_order_ok( $order ) ) {
		$fail( 'انتهت صلاحية هذا العرض' );
	}
	$p = hx_ty_product( $order );
	if ( ! $p || ! isset( $_POST['product'] ) || absint( $_POST['product'] ) !== $p->get_id() ) {
		$fail( 'هذا العرض لم يعد متاحاً' );
	}
	list( $now, $offer, $disc ) = hx_ty_prices( $p );
	$item_id = $order->add_product( $p, 1, array( 'subtotal' => $offer, 'total' => $offer ) );
	if ( ! $item_id ) {
		$fail( 'حدث خطأ، المرجو المحاولة مرة أخرى' );
	}
	$order->update_meta_data( '_hx_ty_added', $p->get_id() );
	$order->calculate_totals( false );
	$order->add_order_note( 'Thank-you page offer accepted: ' . $p->get_name() . ' for ' . hx_num( $offer ) . ' dh (-' . $disc . '%).' );
	$order->save();
	if ( function_exists( 'wc_update_product_stock' ) && $p->managing_stock() ) {
		wc_update_product_stock( $p, 1, 'decrease' );
	}
	wp_send_json_success( array( 'message' => '✓ تمت إضافة المنتج إلى طلبك. المجموع الجديد: ' . hx_num( $order->get_total() ) . ' درهم' ) );
}
add_action( 'wp_ajax_hx_ty_add', 'hx_ty_add' );
add_action( 'wp_ajax_nopriv_hx_ty_add', 'hx_ty_add' );

add_action(
	'customize_register',
	function ( $wp_customize ) {
		$wp_customize->add_section(
			'hx_ty_offer',
			array(
				'title'       => 'Thank-you page offer',
				'panel'       => $wp_customize->get_panel( 'hx_cod_form' ) ? 'hx_cod_form' : '',
				'priority'    => 90,
				'description' => 'After ordering, the customer can add one more product to the same order with one tap. To offer a specific product after a given product, set it in Products > Edit > Linked products > Cross-sells.',
			)
		);
		$prods = array( 'auto' => 'Automatic (best seller from the same category)', 'none' => 'None (only products set as Cross-sells)' );
		foreach ( wc_get_products( array( 'status' => 'publish', 'type' => 'simple', 'limit' => 300, 'orderby' => 'title', 'order' => 'ASC' ) ) as $p ) {
			$prods[ (string) $p->get_id() ] = $p->get_name();
		}
		$wp_customize->add_setting( 'hx_ty_on', array( 'default' => 'show', 'sanitize_callback' => function ( $v ) { return 'hide' === $v ? 'hide' : 'show'; } ) );
		$wp_customize->add_control( 'hx_ty_on', array( 'label' => 'Offer on the thank-you page', 'section' => 'hx_ty_offer', 'type' => 'select', 'priority' => 10, 'choices' => array( 'show' => 'Show', 'hide' => 'Hide' ) ) );
		$wp_customize->add_setting( 'hx_ty_prod', array( 'default' => 'auto', 'sanitize_callback' => function ( $v ) use ( $prods ) { return array_key_exists( (string) $v, $prods ) ? (string) $v : 'auto'; } ) );
		$wp_customize->add_control( 'hx_ty_prod', array( 'label' => 'Default product to offer', 'section' => 'hx_ty_offer', 'type' => 'select', 'priority' => 20, 'choices' => $prods ) );
		$wp_customize->add_setting( 'hx_ty_disc', array( 'default' => 20, 'sanitize_callback' => 'absint' ) );
		$wp_customize->add_control( 'hx_ty_disc', array( 'label' => 'Discount (%)', 'section' => 'hx_ty_offer', 'type' => 'number', 'priority' => 30, 'input_attrs' => array( 'min' => 0, 'max' => 90 ) ) );
		foreach ( array( 'hx_ty_title' => array( 'Title', 40 ), 'hx_ty_text' => array( 'Text ({discount} = the discount)', 50 ), 'hx_ty_btn' => array( 'Button text', 60 ) ) as $id => $c ) {
			$wp_customize->add_setting( $id, array( 'default' => hx_ty_opt( $id ), 'sanitize_callback' => 'sanitize_text_field' ) );
			$wp_customize->add_control( $id, array( 'label' => $c[0], 'section' => 'hx_ty_offer', 'type' => 'text', 'priority' => $c[1] ) );
		}
	},
	40
);
