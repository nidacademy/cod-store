<?php
/**
 * Quantity offers per product (e.g. 1 piece 99 dh, 2 pieces 180 dh).
 * Set in Products > Edit product > "Quantity offers". Shown as cards in the product page order form.
 * The chosen offer's price is applied on the server when the order is created.
 */
defined( 'ABSPATH' ) || exit;

/** Offers of a product: list of array( qty, price, label, badge ). Empty array if none. */
function hx_offers_get( $product_id ) {
	$raw = get_post_meta( (int) $product_id, '_hx_offers', true );
	if ( ! is_array( $raw ) ) {
		return array();
	}
	$out = array();
	foreach ( $raw as $o ) {
		$qty   = isset( $o['qty'] ) ? (int) $o['qty'] : 0;
		$price = isset( $o['price'] ) ? (float) $o['price'] : 0;
		if ( $qty < 1 || $qty > 20 || $price <= 0 ) {
			continue;
		}
		$out[] = array(
			'qty'   => $qty,
			'price' => $price,
			'label' => isset( $o['label'] ) ? (string) $o['label'] : '',
			'badge' => isset( $o['badge'] ) ? (string) $o['badge'] : '',
		);
	}
	return $out;
}

/** Price of one piece before discount (the struck-through price is this x pieces). */
function hx_offer_unit( $product ) {
	list( $p, $c ) = hx_prices( $product );
	return max( (float) $p, (float) $c );
}

/** Offer cards (radio buttons) inside the order form. */
function hx_render_offers( $offers, $unit ) {
	$items = array();
	foreach ( $offers as $i => $o ) {
		$qty   = (int) $o['qty'];
		$price = (float) $o['price'];
		$was   = (float) $unit * $qty;
		$label = '' !== trim( $o['label'] ) ? $o['label'] : $qty . ' قطعة';
		$pp    = hx_price_block( $price, $was > $price + 0.5 ? $was : 0 );
		$sum   = '<span class="hx-offer__main"><span class="hx-offer__label" dir="auto">' . esc_html( $label ) . '</span>';
		if ( $was > $price + 0.5 ) {
			$sum .= '<span class="hx-offer__save">وفّر ' . hx_m( $was - $price ) . ' ' . hx_c() . '</span>';
		}
		$sum .= '</span><span class="hx-offer__price"><span class="hx-offer__now">' . hx_m( $price ) . ' ' . hx_c( 'small' ) . '</span>';
		if ( $was > $price + 0.5 ) {
			$sum .= '<s class="hx-offer__was">' . hx_m( $was ) . ' ' . hx_c() . '</s>';
		}
		$sum .= '</span>';
		$h  = '<label class="hx-offer' . ( 0 === $i ? ' is-on' : '' ) . '">';
		$h .= '<input type="radio" name="offer" value="' . (int) $i . '" data-qty="' . $qty . '" data-pp="' . esc_attr( $pp ) . '"' . ( 0 === $i ? ' checked' : '' ) . '>';
		$h .= '<span class="hx-offer__dot" aria-hidden="true"></span>' . $sum;
		if ( '' !== trim( $o['badge'] ) ) {
			$h .= '<span class="hx-offer__badge" dir="auto">' . esc_html( $o['badge'] ) . '</span>';
		}
		$h      .= '</label>';
		$items[] = array( $h, $sum );
	}
	if ( ! $items ) {
		return;
	}
	if ( 'cards' === get_theme_mod( 'hx_cf_offers', 'dropdown' ) ) {
		echo '<div class="hx-offers" role="radiogroup" aria-label="العروض">';
		foreach ( $items as $it ) {
			echo $it[0]; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		echo '</div>';
		return;
	}
	// Dropdown: a closed box showing the chosen offer; the same radio buttons open below it.
	echo '<div class="hx-offers hx-offers--dd" data-hx-dd>';
	echo '<button type="button" class="hx-dd__btn" aria-haspopup="true" aria-expanded="false" aria-label="العروض"><span class="hx-dd__cur">' . $items[0][1] . '</span>' // phpcs:ignore WordPress.Security.EscapeOutput
		. '<svg class="hx-dd__chev" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path d="m6 9 6 6 6-6" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg></button>';
	echo '<div class="hx-dd__list" role="radiogroup" aria-label="العروض">';
	foreach ( $items as $it ) {
		echo $it[0]; // phpcs:ignore WordPress.Security.EscapeOutput
	}
	echo '</div></div>';
}

/* Customize > COD Form > Layout: offers as cards or as a dropdown. */
add_action(
	'customize_register',
	function ( $wp_customize ) {
		$wp_customize->add_setting( 'hx_cf_offers', array( 'default' => 'dropdown', 'sanitize_callback' => function ( $v ) { return 'cards' === $v ? 'cards' : 'dropdown'; } ) );
		$wp_customize->add_control(
			'hx_cf_offers',
			array(
				'label'       => 'Quantity offers',
				'description' => 'How the packs (1 piece, 2 pieces...) are shown in the form.',
				'section'     => 'hx_cf_layout',
				'type'        => 'select',
				'priority'    => 1,
				'choices'     => array(
					'dropdown' => 'Dropdown',
					'cards'    => 'Cards (one under the other)',
				),
			)
		);
	},
	40
);

/* Product page script (only when the product has offers). */
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( function_exists( 'is_product' ) && is_product() && hx_offers_get( get_queried_object_id() ) ) {
			wp_enqueue_script( 'hx-offers', get_theme_file_uri( 'assets/offers.js' ), array( 'hx-product' ), HX_VER, true );
		}
	},
	20
);

/* Apply the chosen offer to the order (quantity + pack price). */
add_action(
	'hx_order_before_save',
	function ( $order ) {
		$pid    = absint( $_POST['product_id'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification
		$offers = $pid ? hx_offers_get( $pid ) : array();
		if ( ! $offers ) {
			return;
		}
		$i = absint( $_POST['offer'] ?? 0 ); // phpcs:ignore WordPress.Security.NonceVerification
		if ( ! isset( $offers[ $i ] ) ) {
			$i = 0;
		}
		$o = $offers[ $i ];
		foreach ( $order->get_items() as $item ) {
			$item->set_quantity( $o['qty'] );
			$item->set_subtotal( $o['price'] );
			$item->set_total( $o['price'] );
			$item->save();
		}
		$order->calculate_totals( false );
		$order->update_meta_data( '_hx_offer', $o );
		$order->add_order_note( 'Quantity offer: ' . $o['qty'] . ' piece(s) for ' . hx_num( $o['price'] ) . ' dh.' );
	}
);

/* ---------- Admin: "Quantity offers" box on the product edit page ---------- */
add_action(
	'add_meta_boxes',
	function () {
		add_meta_box( 'hx_offers', 'Quantity offers', 'hx_offers_box', 'product', 'normal', 'high' );
	}
);

function hx_offers_box( $post ) {
	wp_nonce_field( 'hx_offers_save', 'hx_offers_nonce' );
	$row = function ( $o ) {
		return '<tr>'
			. '<td><input type="number" name="hx_offers[qty][]" min="1" max="20" step="1" value="' . esc_attr( $o['qty'] ) . '" style="width:80px"></td>'
			. '<td><input type="number" name="hx_offers[price][]" min="0" step="0.01" value="' . esc_attr( $o['price'] ) . '" style="width:110px"></td>'
			. '<td><input type="text" name="hx_offers[label][]" value="' . esc_attr( $o['label'] ) . '" placeholder="2 قطعة" dir="auto" style="width:100%"></td>'
			. '<td><input type="text" name="hx_offers[badge][]" value="' . esc_attr( $o['badge'] ) . '" placeholder="الأكثر طلباً" dir="auto" style="width:100%"></td>'
			. '<td style="width:30px"><button type="button" class="button-link-delete hx-offers-del" aria-label="Remove offer" style="font-size:20px;line-height:1">&times;</button></td>'
			. '</tr>';
	};
	echo '<p>Let customers pick a pack in the order form, for example <strong>1 piece 99 dh</strong>, <strong>2 pieces 180 dh</strong>. The price is the <strong>total for the whole pack</strong>. Leave empty to sell at the normal price.</p>';
	echo '<table class="widefat striped"><thead><tr><th>Pieces</th><th>Total price (dh)</th><th>Label (optional)</th><th>Badge (optional)</th><th></th></tr></thead><tbody>';
	foreach ( hx_offers_get( $post->ID ) as $o ) {
		echo $row( $o ); // phpcs:ignore WordPress.Security.EscapeOutput
	}
	echo '</tbody></table>';
	echo '<p><button type="button" class="button hx-offers-add">+ Add offer</button></p>';
	echo '<p class="description">The first offer is selected by default. Pieces: 1 to 20. Label is shown instead of "1 قطعة / 2 قطعة / 3 قطعة". The struck-through price is your regular price × pieces.</p>';
	$empty = $row( array( 'qty' => '', 'price' => '', 'label' => '', 'badge' => '' ) );
	echo '<script>(function(){var b=document.getElementById("hx_offers");if(!b){return;}var t=b.querySelector("tbody");b.querySelector(".hx-offers-add").addEventListener("click",function(){t.insertAdjacentHTML("beforeend",' . wp_json_encode( $empty ) . ');});b.addEventListener("click",function(e){if(e.target.classList.contains("hx-offers-del")){e.target.closest("tr").remove();}});})();</script>';
}

add_action(
	'save_post_product',
	function ( $post_id ) {
		if ( ! isset( $_POST['hx_offers_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['hx_offers_nonce'] ), 'hx_offers_save' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$in   = isset( $_POST['hx_offers'] ) && is_array( $_POST['hx_offers'] ) ? wp_unslash( $_POST['hx_offers'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$rows = array();
		foreach ( (array) ( $in['qty'] ?? array() ) as $i => $q ) {
			$q = (int) $q;
			$p = (float) str_replace( ',', '.', (string) ( $in['price'][ $i ] ?? '' ) );
			if ( $q < 1 || $q > 20 || $p <= 0 ) {
				continue;
			}
			$rows[] = array(
				'qty'   => $q,
				'price' => round( $p, 2 ),
				'label' => sanitize_text_field( $in['label'][ $i ] ?? '' ),
				'badge' => sanitize_text_field( $in['badge'][ $i ] ?? '' ),
			);
		}
		if ( $rows ) {
			update_post_meta( $post_id, '_hx_offers', $rows );
		} else {
			delete_post_meta( $post_id, '_hx_offers' );
		}
	}
);
