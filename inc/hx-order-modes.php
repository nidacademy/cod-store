<?php
/**
 * Order form mode: "Activate form", "Activate WhatsApp" or "Activate both".
 * Store-wide: Customize > Product page > Order form & button > Order buttons (+ WhatsApp number).
 * Per product: Products > Edit product > "Order buttons" (side box).
 */
defined( 'ABSPATH' ) || exit;

/** Mode choices (value => label). '' = form (default). */
function hx_form_mode_choices() {
	return array(
		''         => 'Activate form (default)',
		'whatsapp' => 'Activate WhatsApp',
		'both'     => 'Activate both',
	);
}

/** Which buttons a product shows: array( 'order' => bool, 'wa' => bool, 'number' => digits ). */
function hx_form_modes( $product ) {
	$id     = $product ? (int) $product->get_id() : 0;
	$number = preg_replace( '/\D/', '', (string) hx_pp_val( 'pp_wa_number' ) );
	$mode   = (string) hx_pp_val( 'pp_order_mode' );
	$own    = (string) get_post_meta( $id, '_hx_form_mode', true );
	if ( in_array( $own, array( 'form', 'whatsapp', 'both' ), true ) ) {
		$mode = $own;
	}
	$has_wa = strlen( $number ) >= 8;
	$wa     = $has_wa && in_array( $mode, array( 'whatsapp', 'both' ), true );
	$order  = ! $wa || 'both' === $mode; // WhatsApp chosen but no number yet: keep the form.
	return array(
		'order'  => $order,
		'wa'     => $wa,
		'number' => $number,
	);
}

/** WhatsApp button (the message is completed by assets/hx-wa.js with the customer's choices). */
function hx_render_wa_button( $product, $number ) {
	$name = $product->get_name();
	$url  = get_permalink( $product->get_id() );
	$text = "السلام عليكم، بغيت نطلب:\n" . $name . "\n" . $url;
	$href = 'https://wa.me/' . $number . '?text=' . rawurlencode( $text );
	echo '<a class="hx-order__wa" href="' . esc_url( $href ) . '" target="_blank" rel="noopener" data-hx-wa="' . esc_attr( $number ) . '" data-name="' . esc_attr( $name ) . '" data-url="' . esc_url( $url ) . '" data-tpl-ar="' . esc_attr( hx_wa_template( 'ar' ) ) . '" data-tpl="' . esc_attr( hx_wa_template( 'other' ) ) . '">';
	echo '<svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor" aria-hidden="true"><path d="M12.04 2a9.9 9.9 0 0 0-8.5 15l-1.4 5.1 5.24-1.37A9.9 9.9 0 1 0 12.04 2zm0 18.1a8.2 8.2 0 0 1-4.18-1.15l-.3-.18-3.1.82.83-3.03-.2-.31a8.2 8.2 0 1 1 6.95 3.85zm4.5-6.15c-.25-.12-1.46-.72-1.69-.8-.23-.08-.39-.12-.55.12-.16.25-.63.8-.78.97-.14.16-.29.18-.53.06-.25-.12-1.04-.38-1.98-1.22-.73-.65-1.23-1.46-1.37-1.7-.14-.25-.02-.38.11-.5.11-.11.25-.29.37-.43.12-.15.16-.25.25-.41.08-.17.04-.31-.02-.43-.06-.12-.55-1.33-.76-1.82-.2-.48-.4-.41-.55-.42h-.47a.9.9 0 0 0-.65.31c-.23.25-.86.84-.86 2.05 0 1.2.88 2.37 1 2.53.12.16 1.73 2.64 4.2 3.7.59.26 1.05.41 1.4.52.6.19 1.13.16 1.56.1.48-.07 1.46-.6 1.67-1.18.2-.58.2-1.07.14-1.18-.06-.1-.22-.16-.47-.29z"/></svg>';
	echo '<span>' . esc_html( hx_pp_text( 'pp_wa_txt', 'واتساب' ) ) . '</span></a>';
}

add_action(
	'wp_enqueue_scripts',
	function () {
		if ( ! ( function_exists( 'is_product' ) && is_product() ) ) {
			return;
		}
		$p = wc_get_product( get_queried_object_id() );
		if ( $p ) {
			$m = hx_form_modes( $p );
			if ( $m['wa'] ) {
				wp_enqueue_script( 'hx-wa', get_theme_file_uri( 'assets/hx-wa.js' ), array(), HX_VER, true );
			}
		}
	},
	20
);

/* ---------- Admin: "Order buttons" box on the product edit page ---------- */
add_action(
	'add_meta_boxes',
	function () {
		add_meta_box( 'hx_order_modes', 'Order buttons', 'hx_order_modes_box', 'product', 'side', 'default' );
	}
);

function hx_order_modes_box( $post ) {
	wp_nonce_field( 'hx_order_modes_save', 'hx_order_modes_nonce' );
	$v       = (string) get_post_meta( $post->ID, '_hx_form_mode', true );
	$choices = array(
		''         => 'Store setting',
		'form'     => 'Activate form',
		'whatsapp' => 'Activate WhatsApp',
		'both'     => 'Activate both',
	);
	echo '<p><select name="_hx_form_mode" style="width:100%">';
	foreach ( $choices as $cv => $cl ) {
		echo '<option value="' . esc_attr( $cv ) . '"' . selected( $v, $cv, false ) . '>' . esc_html( $cl ) . '</option>';
	}
	echo '</select></p>';
	echo '<p class="description">"Store setting" follows Appearance &rsaquo; Customize &rsaquo; Product page &rsaquo; Order form &amp; button. WhatsApp needs the number set there; without it the form is shown.</p>';
}

add_action(
	'save_post_product',
	function ( $post_id ) {
		if ( ! isset( $_POST['hx_order_modes_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['hx_order_modes_nonce'] ), 'hx_order_modes_save' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$v = isset( $_POST['_hx_form_mode'] ) ? sanitize_key( $_POST['_hx_form_mode'] ) : '';
		if ( in_array( $v, array( 'form', 'whatsapp', 'both' ), true ) ) {
			update_post_meta( $post_id, '_hx_form_mode', $v );
		} else {
			delete_post_meta( $post_id, '_hx_form_mode' );
		}
	}
);

/* ---------- WhatsApp message template ---------- */
function hx_wa_template_default( $which ) {
	return 'ar' === $which
		? "السلام عليكم، بغيت نطلب:\n{product}\n{offer}\n{options}\n{link}"
		: "{greeting}\n{product}\n{offer}\n{options}\n{link}";
}

function hx_wa_template( $which ) {
	$t = trim( (string) get_theme_mod( 'ar' === $which ? 'hx_wa_tpl_ar' : 'hx_wa_tpl', '' ) );
	return '' !== $t ? $t : hx_wa_template_default( $which );
}

add_action(
	'customize_register',
	function ( $wp_customize ) {
		if ( ! $wp_customize->get_section( 'hx_pp_form' ) ) {
			return;
		}
		$row  = function ( $tag, $text ) {
			return '<li style="margin:0 0 4px"><code style="font-weight:600">' . $tag . '</code> &nbsp;' . $text . '</li>';
		};
		$list = '<strong>Fields you can use:</strong><ul style="margin:6px 0 8px;padding:0;list-style:none">'
			. $row( '{product}', 'product name' )
			. $row( '{offer}', 'chosen pack with its price, e.g. <span dir="rtl">2 قطعة (180 درهم)</span>' )
			. $row( '{options}', 'chosen colour / size (one per line)' )
			. $row( '{price}', 'price' )
			. $row( '{link}', 'product page link' );
		$tips = '<span style="display:block;margin-top:4px">• A line whose fields are all empty is left out.<br>• Leave the box empty to use the default message.</span>';
		$fields = array(
			'hx_wa_tpl_ar' => array( 'WhatsApp message - Arabic pages', $list . '</ul>' . $tips, 'ar' ),
			'hx_wa_tpl'    => array( 'WhatsApp message - other languages', $list . $row( '{greeting}', '"Hello, I would like to order:" in the visitor\'s language' ) . '</ul>' . $tips . '<span style="display:block;margin-top:4px">• Write your own text in English; product, offer and options come in the visitor\'s language.</span>', 'other' ),
		);
		$i = 900;
		foreach ( $fields as $id => $f ) {
			$wp_customize->add_setting( $id, array( 'default' => '', 'sanitize_callback' => 'sanitize_textarea_field' ) );
			$wp_customize->add_control(
				$id,
				array(
					'label'       => $f[0],
					'description' => $f[1],
					'section'     => 'hx_pp_form',
					'type'        => 'textarea',
					'priority'    => ++$i,
					'input_attrs' => array(
						'rows'        => 6,
						'placeholder' => hx_wa_template_default( $f[2] ),
						'dir'         => 'ar' === $f[2] ? 'rtl' : 'ltr',
					),
				)
			);
		}
	},
	20
);

/* Remember where visitors come from (for the order source in COD Manager). */
add_action(
	'wp_enqueue_scripts',
	function () {
		wp_enqueue_script( 'hx-src', get_theme_file_uri( 'assets/hx-src.js' ), array(), HX_VER, true );
	},
	5
);
