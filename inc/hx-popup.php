<?php
/**
 * Special offer popup (Customize > Offer popup).
 *
 * Two kinds of offer:
 * - One coupon: the popup shows the code, "Get the discount" saves it in the browser.
 * - Spin wheel: the visitor spins and wins one of several coupons (chance set per coupon).
 *   The server picks the prize and signs it, so a visitor can't pick a bigger coupon.
 * The order form then shows the discounted total and the WooCommerce coupon is applied to the order.
 *
 * @package hx-store
 */

defined( 'ABSPATH' ) || exit;

/** The 6 ready-made designs. */
function hx_pop_designs() {
	return array(
		'card'    => 'Card with photo',
		'side'    => 'Photo on the side',
		'cover'   => 'Full photo',
		'overlay' => 'Photo + big title',
		'split'   => 'Half photo',
		'bar'     => 'Bottom bar',
	);
}

/** Number of coupon rows for the wheel. */
define( 'HX_POP_WHEEL_ROWS', 6 );

/** Settings with their defaults. */
function hx_pop_opt( $id ) {
	$d = array(
		'hx_pop_on'     => 'off',
		'hx_pop_design' => 'card',
		'hx_pop_mode'   => 'single',
		'hx_pop_img'    => 0,
		'hx_pop_badge'  => '',
		'hx_pop_title'  => 'هل تحب الهدايا؟',
		'hx_pop_text'   => 'استفد من خصم خاص على طلبك الآن، العرض لمدة محدودة.',
		'hx_pop_code'   => '',
		'hx_pop_btn'    => 'احصل على الخصم',
		'hx_pop_spin'   => 'أدر العجلة واربح',
		'hx_pop_slices' => '10%, 20%, 30%, 40%, 50%',
		'hx_pop_win'    => 'مبروك! ربحت *{prize}*',
		'hx_pop_use'    => 'استعمل الخصم الآن',
		'hx_pop_done'   => 'تم تفعيل الخصم! سيتم خصمه تلقائياً عند الطلب.',
		'hx_pop_no'     => 'لا شكراً',
		'hx_pop_link'   => '',
		'hx_pop_delay'  => '5',
		'hx_pop_exit'   => 'yes',
		'hx_pop_days'   => '3',
		'hx_pop_c1'     => '',
		'hx_pop_bg'     => '',
	);
	$chances = array( 50, 30, 20, 10, 10, 10 );
	for ( $n = 1; $n <= HX_POP_WHEEL_ROWS; $n++ ) {
		$d[ 'hx_pop_w' . $n ]        = '';
		$d[ 'hx_pop_w' . $n . '_p' ] = (string) $chances[ $n - 1 ];
	}
	if ( 'hx_pop_design' === $id ) {
		$v = get_theme_mod( $id, null );
		return isset( hx_pop_designs()[ (string) $v ] ) ? $v : 'card';
	}
	$v = get_theme_mod( $id, null );
	return null === $v ? ( $d[ $id ] ?? '' ) : $v;
}

/**
 * A coupon from Marketing > Coupons, if it can be used right now.
 *
 * @param string $code Coupon code.
 * @return array|null [code, type, amount, min, label]
 */
function hx_pop_coupon_info( $code ) {
	$code = wc_format_coupon_code( (string) $code );
	if ( '' === $code || ! function_exists( 'wc_get_coupon_id_by_code' ) ) {
		return null;
	}
	$id = wc_get_coupon_id_by_code( $code );
	if ( ! $id ) {
		return null;
	}
	$c   = new WC_Coupon( $id );
	$exp = $c->get_date_expires();
	if ( 'publish' !== get_post_status( $id ) || ( $exp && $exp->getTimestamp() < time() ) || ( $c->get_usage_limit() && $c->get_usage_count() >= $c->get_usage_limit() ) ) {
		return null;
	}
	$amount = (float) $c->get_amount();
	// Products / categories the coupon is limited to (Marketing > Coupons > Usage restriction).
	$ids = function ( $list ) {
		$out = array();
		foreach ( (array) $list as $id ) {
			$out[] = (int) $id;
			$pr = wc_get_product( $id );
			if ( $pr && $pr->get_parent_id() ) {
				$out[] = (int) $pr->get_parent_id();
			}
		}
		return array_values( array_unique( $out ) );
	};
	$pids  = $ids( $c->get_product_ids() );
	$cats  = array_map( 'intval', (array) $c->get_product_categories() );
	$names = array();
	foreach ( array_slice( $c->get_product_ids(), 0, 3 ) as $id ) {
		$pr = wc_get_product( $id );
		if ( $pr ) {
			$names[] = $pr->get_parent_id() ? get_the_title( $pr->get_parent_id() ) : $pr->get_name();
		}
	}
	if ( ! $names ) {
		foreach ( array_slice( $cats, 0, 3 ) as $cid ) {
			$t = get_term( $cid, 'product_cat' );
			if ( $t && ! is_wp_error( $t ) ) {
				$names[] = $t->name;
			}
		}
	}
	return array(
		'pids'   => $pids,
		'xpids'  => $ids( $c->get_excluded_product_ids() ),
		'cats'   => $cats,
		'xcats'  => array_map( 'intval', (array) $c->get_excluded_product_categories() ),
		'for'    => implode( '، ', array_unique( $names ) ),
		'plist'  => hx_pop_coupon_products( $c ),
		'code'   => $code,
		'type'   => $c->get_discount_type(),
		'amount' => $amount,
		'min'    => (float) $c->get_minimum_amount(),
		'label'  => 'percent' === $c->get_discount_type() ? hx_num( $amount ) . '%' : hx_num( $amount ) . ' درهم',
	);
}

/**
 * Products a coupon can be used on, for the product picker after a win.
 * Limited coupon: its products (or the products of its categories). Not limited: the 40 best sellers.
 *
 * @param WC_Coupon $c Coupon.
 * @return int[] Product IDs.
 */
function hx_pop_coupon_products( $c ) {
	$ids = array();
	if ( $c->get_product_ids() ) {
		foreach ( $c->get_product_ids() as $id ) {
			$pr = wc_get_product( $id );
			if ( $pr ) {
				$ids[] = $pr->get_parent_id() ? $pr->get_parent_id() : $pr->get_id();
			}
		}
	} else {
		$args = array( 'post_type' => 'product', 'post_status' => 'publish', 'numberposts' => 40, 'fields' => 'ids' );
		if ( $c->get_product_categories() ) {
			$args['tax_query'] = array( array( 'taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => array_map( 'intval', $c->get_product_categories() ) ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		} else {
			$args['meta_key'] = 'total_sales'; // phpcs:ignore WordPress.DB.SlowDBQuery
			$args['orderby']  = 'meta_value_num';
			$args['order']    = 'DESC';
		}
		$ids = get_posts( $args );
	}
	$x   = array_map( 'intval', (array) $c->get_excluded_product_ids() );
	$xc  = array_map( 'intval', (array) $c->get_excluded_product_categories() );
	$out = array();
	foreach ( array_unique( array_map( 'intval', $ids ) ) as $id ) {
		if ( in_array( $id, $x, true ) || 'publish' !== get_post_status( $id ) ) {
			continue;
		}
		if ( $xc && array_intersect( $xc, wc_get_product_cat_ids( $id ) ) ) {
			continue;
		}
		$out[] = $id;
	}
	return array_slice( $out, 0, 60 );
}

/** The single coupon (mode "One coupon"). */
function hx_pop_coupon() {
	return hx_pop_coupon_info( hx_pop_opt( 'hx_pop_code' ) );
}

/**
 * Wheel prizes = the coupons created in Marketing > Coupons (only these can be won).
 * Chance = "Spin wheel chance" on the coupon (empty = 10).
 */
function hx_pop_wheel_list() {
	$out = array();
	foreach ( get_posts( array( 'post_type' => 'shop_coupon', 'post_status' => 'publish', 'numberposts' => 40, 'orderby' => 'date', 'order' => 'ASC' ) ) as $cpost ) {
		$info = hx_pop_coupon_info( $cpost->post_title );
		if ( ! $info || isset( $out[ $info['code'] ] ) ) {
			continue;
		}
		$w         = get_post_meta( $cpost->ID, '_hx_wheel_chance', true );
		$info['w'] = '' === (string) $w ? 10 : max( 0, (float) $w );
		$out[ $info['code'] ] = $info;
	}
	$out = array_values( $out );
	if ( $out && 0 == array_sum( array_column( $out, 'w' ) ) ) { // phpcs:ignore Universal.Operators.StrictComparisons
		foreach ( $out as &$o ) {
			$o['w'] = 1;
		}
		unset( $o );
	}
	return $out;
}

/**
 * What the wheel shows: the slices typed in Customize (default 10%-50%) plus each coupon's discount.
 * A slice gets a coupon code when a coupon has that discount; the others are only shown.
 *
 * @return array [ [label, code], ... ] sorted by number.
 */
function hx_pop_wheel_slices() {
	$list   = hx_pop_wheel_list();
	$labels = array();
	foreach ( preg_split( '/[,،\n]+/u', (string) hx_pop_opt( 'hx_pop_slices' ) ) as $l ) {
		$l = trim( $l );
		if ( '' !== $l ) {
			$labels[ $l ] = '';
		}
	}
	foreach ( $list as $c ) {
		if ( ! isset( $labels[ $c['label'] ] ) || '' === $labels[ $c['label'] ] ) {
			$labels[ $c['label'] ] = $c['code'];
		}
	}
	$out = array();
	foreach ( $labels as $l => $code ) {
		$out[] = array( 'label' => $l, 'code' => $code );
	}
	usort(
		$out,
		function ( $x, $y ) {
			return (float) preg_replace( '/[^0-9.]/', '', $x['label'] ) <=> (float) preg_replace( '/[^0-9.]/', '', $y['label'] );
		}
	);
	return array_slice( $out, 0, 12 );
}
/* Marketing > Coupons > General: chance on the spin wheel. */
add_action(
	'woocommerce_coupon_options',
	function ( $coupon_id ) {
		woocommerce_wp_text_input(
			array(
				'id'                => '_hx_wheel_chance',
				'label'             => 'Spin wheel chance',
				'placeholder'       => '10',
				'description'       => 'How often this coupon wins on the spin wheel, compared to the others (e.g. 50 vs 1). Empty = 10, 0 = shown but never won.',
				'desc_tip'          => true,
				'type'              => 'number',
				'custom_attributes' => array( 'min' => '0', 'step' => 'any' ),
				'value'             => get_post_meta( $coupon_id, '_hx_wheel_chance', true ),
			)
		);
	}
);
add_action(
	'woocommerce_coupon_options_save',
	function ( $post_id ) {
		if ( isset( $_POST['_hx_wheel_chance'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$v = sanitize_text_field( wp_unslash( $_POST['_hx_wheel_chance'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			if ( '' === $v ) {
				delete_post_meta( $post_id, '_hx_wheel_chance' );
			} else {
				update_post_meta( $post_id, '_hx_wheel_chance', (string) max( 0, (float) $v ) );
			}
		}
		do_action( 'litespeed_purge_all' );
	}
);
/** Is the wheel mode on? */
function hx_pop_is_wheel() {
	return 'wheel' === hx_pop_opt( 'hx_pop_mode' );
}

/** Signature of a won prize (so the order can trust it). */
function hx_pop_sig( $code, $t ) {
	return hash_hmac( 'sha256', $code . '|' . (int) $t, wp_salt( 'auth' ) );
}

/** Can visitors see the popup (a working coupon, or no coupon at all in single mode)? */
function hx_pop_ready() {
	if ( hx_pop_is_wheel() ) {
		return (bool) hx_pop_wheel_list();
	}
	return '' === (string) hx_pop_opt( 'hx_pop_code' ) || (bool) hx_pop_coupon();
}

/** Pages where the popup never opens. */
function hx_pop_excluded() {
	return is_admin() || ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() ) ) || is_page_template( 'template-thanks.php' ) || is_404();
}

/** Title: each line gets the white highlight, *word* is coloured. */
function hx_pop_title_html( $t ) {
	$h = preg_replace( '/\*(.+?)\*/u', '<em>$1</em>', esc_html( $t ) );
	$h = preg_replace( '/(\d+(?:[.,]\d+)?\s?%)/u', '<bdi dir="ltr">$1</bdi>', $h ); // "10%" stays "10%" in Arabic text
	return '<span>' . $h . '</span>';
}

/** Popup on for visitors, or we are in the Customizer. */
function hx_pop_active() {
	return is_customize_preview() || ( 'on' === hx_pop_opt( 'hx_pop_on' ) && hx_pop_ready() );
}

add_action(
	'wp_enqueue_scripts',
	function () {
		$ty = is_page_template( 'template-thanks.php' );
		if ( ! hx_pop_active() || ( hx_pop_excluded() && ! $ty ) ) {
			return;
		}
		wp_enqueue_style( 'hx-popup', get_theme_file_uri( 'assets/hx-popup.css' ), array(), HX_VER );
		wp_enqueue_script( 'hx-popup', get_theme_file_uri( 'assets/hx-popup.js' ), array(), HX_VER, true );
		$price = 0;
		$pid   = 0;
		$pcats = array();
		if ( function_exists( 'is_product' ) && is_product() && function_exists( 'hx_prices' ) ) {
			$p = wc_get_product( get_queried_object_id() );
			if ( $p ) {
				$pid   = $p->get_id();
				$pcats = array_map( 'intval', $p->get_category_ids() );
				$pr    = hx_prices( $p );
				$price = (float) $pr[0];
			}
		}
		$wheel   = hx_pop_is_wheel();
		$list    = $wheel ? hx_pop_wheel_list() : array();
		$coupons = array();
		if ( $wheel ) {
			foreach ( $list as $c ) {
				$coupons[ $c['code'] ] = array_intersect_key( $c, array_flip( array( 'type', 'amount', 'min', 'label', 'pids', 'xpids', 'cats', 'xcats', 'for', 'plist' ) ) );
			}
		} elseif ( $cp = hx_pop_coupon() ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition
			$coupons[ $cp['code'] ] = array_intersect_key( $cp, array_flip( array( 'type', 'amount', 'min', 'label', 'pids', 'xpids', 'cats', 'xcats', 'for' ) ) );
		}
		$cfg = array(
			'preview' => is_customize_preview(),
			'ty'      => $ty,
			'mode'    => $wheel ? 'wheel' : 'single',
			'delay'   => max( 0, (int) hx_pop_opt( 'hx_pop_delay' ) ),
			'exit'    => 'yes' === hx_pop_opt( 'hx_pop_exit' ),
			'days'    => max( 0, (int) hx_pop_opt( 'hx_pop_days' ) ),
			'link'    => esc_url_raw( (string) hx_pop_opt( 'hx_pop_link' ) ),
			'ajax'    => admin_url( 'admin-ajax.php' ),
			'coupons' => (object) $coupons,
			'wheel'   => array_map(
				function ( $c ) {
					return array( 'code' => $c['code'], 'label' => $c['label'], 'w' => $c['w'] );
				},
				$list
			),
			'slices'  => $wheel ? hx_pop_wheel_slices() : array(),
			'pmap'    => (object) hx_pop_product_map( $coupons ),
			'price'   => $price,
			'pid'     => $pid,
			'pcats'   => $pcats,
			'txt'     => array(
				'on'     => 'كود الخصم %s مفعّل',
				'save'   => 'الخصم',
				'total'  => 'المجموع بعد الخصم',
				'min'    => 'الخصم يُطبَّق على الطلبات من %s درهم فما فوق',
				'cur'    => 'درهم',
				'copied' => 'تم النسخ',
				'err'    => 'حدث خطأ، حاول مرة أخرى',
				'for'    => 'صالح على: %s',
				'pick'   => 'اختر المنتج',
			),
		);
		wp_add_inline_script( 'hx-popup', 'window.HXPOP=' . wp_json_encode( $cfg ) . ';', 'before' );
	},
	30
);

/* Colours chosen in the Customizer. */
add_action(
	'wp_head',
	function () {
		if ( ! hx_pop_active() || hx_pop_excluded() ) {
			return;
		}
		$css = '';
		$c1  = sanitize_hex_color( (string) hx_pop_opt( 'hx_pop_c1' ) );
		$bg  = sanitize_hex_color( (string) hx_pop_opt( 'hx_pop_bg' ) );
		if ( $c1 ) {
			$css .= '--pop-main:' . $c1 . ';';
		}
		if ( $bg ) {
			$css .= '--pop-bg:' . $bg . ';';
			$h    = ltrim( $bg, '#' );
			if ( 3 === strlen( $h ) ) {
				$h = $h[0] . $h[0] . $h[1] . $h[1] . $h[2] . $h[2];
			}
			$lum = ( 0.299 * hexdec( substr( $h, 0, 2 ) ) + 0.587 * hexdec( substr( $h, 2, 2 ) ) + 0.114 * hexdec( substr( $h, 4, 2 ) ) ) / 255;
			if ( $lum < 0.5 ) {
				$css .= '--pop-ink:#fff;--pop-hl:rgba(255,255,255,.14);';
			}
		}
		if ( $css ) {
			echo '<style id="hx-pop-colors">.hx-pop.hx-pop{' . $css . '}</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
		}
	},
	102
);

/* The popup markup (before the footer scripts, so the script finds it). */
add_action(
	'wp_footer',
	function () {
		if ( ! hx_pop_active() || hx_pop_excluded() ) {
			return;
		}
		$design = (string) hx_pop_opt( 'hx_pop_design' );
		$wheel  = hx_pop_is_wheel();
		if ( $wheel && 'bar' === $design ) {
			$design = 'card'; // the wheel needs room
		}
		$img   = (int) hx_pop_opt( 'hx_pop_img' );
		$badge = trim( (string) hx_pop_opt( 'hx_pop_badge' ) );
		$text  = trim( (string) hx_pop_opt( 'hx_pop_text' ) );
		$no    = trim( (string) hx_pop_opt( 'hx_pop_no' ) );
		$code  = wc_strtoupper( (string) hx_pop_opt( 'hx_pop_code' ) );
		$x     = '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/></svg>';
		$tag   = '<span class="hx-pop__tag" aria-hidden="true"><svg viewBox="0 0 24 24" width="34" height="34"><path d="M3 12.6V4.5A1.5 1.5 0 0 1 4.5 3h8.1a1.5 1.5 0 0 1 1.06.44l7.4 7.4a1.5 1.5 0 0 1 0 2.12l-8.1 8.1a1.5 1.5 0 0 1-2.12 0l-7.4-7.4A1.5 1.5 0 0 1 3 12.6z" fill="currentColor"/><circle cx="8" cy="8" r="1.8" fill="#fff"/></svg></span>';
		$photo = $img ? '<div class="hx-pop__photo">' . wp_get_attachment_image( $img, 'large', false, array( 'loading' => 'lazy', 'alt' => '' ) ) . '</div>' : '';
		$wh    = '';
		if ( $wheel ) {
			$wh = '<div class="hx-pop__wheel"><div class="hx-pop__wheelin"><svg viewBox="-100 -100 200 200" data-hx-wheel aria-hidden="true"></svg><span class="hx-pop__ptr" aria-hidden="true"></span><span class="hx-pop__hub" aria-hidden="true"><svg viewBox="0 0 24 24" width="26" height="26"><path d="M20 12v8H4v-8M2 7h20v5H2zM12 22V7M12 7H7.5a2.5 2.5 0 1 1 0-5C11 2 12 7 12 7zM12 7h4.5a2.5 2.5 0 1 0 0-5C13 2 12 7 12 7z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg></span></div></div>';
		}
		$cls = 'hx-pop hx-pop--' . $design . ' hx-pop--' . ( $wheel ? 'wheel' : 'single' ) . ( $img ? ' hx-pop--img' : ' hx-pop--noimg' );
		?>
		<div class="<?php echo esc_attr( $cls ); ?>" id="hx-pop" hidden>
			<div class="hx-pop__bg" data-hx-pop-close></div>
			<div class="hx-pop__box" role="dialog" aria-modal="true" aria-labelledby="hx-pop-t" dir="rtl">
				<button type="button" class="hx-pop__x" data-hx-pop-close aria-label="إغلاق"><?php echo $x; // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
				<?php
				// Cover: the photo is the background, the wheel goes in the panel. Others: the wheel takes the photo's place.
				if ( 'cover' === $design ) {
					echo $photo; // phpcs:ignore WordPress.Security.EscapeOutput
				} elseif ( 'bar' !== $design ) {
					echo $wheel ? $wh : $photo; // phpcs:ignore WordPress.Security.EscapeOutput
				}
				?>
				<div class="hx-pop__body">
					<?php
					if ( 'cover' === $design && $wheel ) {
						echo $wh; // phpcs:ignore WordPress.Security.EscapeOutput
					}
					if ( 'bar' === $design ) {
						echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput
					}
					?>
					<div class="hx-pop__main" data-hx-pre>
						<?php if ( '' !== $badge ) : ?>
							<span class="hx-pop__badge"><?php echo esc_html( $badge ); ?></span>
						<?php endif; ?>
						<h2 class="hx-pop__t" id="hx-pop-t"><?php echo hx_pop_title_html( (string) hx_pop_opt( 'hx_pop_title' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></h2>
						<?php if ( '' !== $text ) : ?>
							<p class="hx-pop__txt"><?php echo esc_html( $text ); ?></p>
						<?php endif; ?>
					</div>
					<?php if ( $wheel ) : ?>
						<?php if ( is_customize_preview() && ! hx_pop_wheel_list() ) : ?>
							<p class="hx-pop__warn">Only you see this: you have no working coupons yet. Create one in Marketing &gt; Coupons, otherwise visitors don't see the popup.</p>
						<?php endif; ?>
						<button type="button" class="hx-pop__btn" data-hx-pop-spin data-hx-pre><?php echo esc_html( (string) hx_pop_opt( 'hx_pop_spin' ) ); ?></button>
						<div class="hx-pop__win" data-hx-win hidden>
							<h2 class="hx-pop__t" data-hx-win-title data-tpl="<?php echo esc_attr( (string) hx_pop_opt( 'hx_pop_win' ) ); ?>"></h2>
							<p class="hx-pop__txt"><?php echo esc_html( (string) hx_pop_opt( 'hx_pop_done' ) ); ?></p>
							<button type="button" class="hx-pop__code" data-hx-copy dir="ltr"></button>
							<p class="hx-pop__for" data-hx-for hidden></p>
							<label class="hx-pop__pick" data-hx-pick hidden><span>اختر المنتج الذي تريد استعمال الكود عليه</span><select data-hx-pick-sel></select></label>
							<button type="button" class="hx-pop__btn" data-hx-pop-use><?php echo esc_html( (string) hx_pop_opt( 'hx_pop_use' ) ); ?></button>
						</div>
					<?php else : ?>
						<?php if ( '' !== $code ) : ?>
							<button type="button" class="hx-pop__code" data-hx-copy dir="ltr" title="نسخ"><?php echo esc_html( $code ); ?></button>
							<?php $hx_cp = hx_pop_coupon(); ?>
							<?php if ( $hx_cp && '' !== $hx_cp['for'] ) : ?>
								<p class="hx-pop__for"><?php echo esc_html( sprintf( 'صالح على: %s', $hx_cp['for'] ) ); ?></p>
							<?php endif; ?>
							<?php if ( ! hx_pop_coupon() && is_customize_preview() ) : ?>
								<p class="hx-pop__warn">Only you see this: the code "<?php echo esc_html( $code ); ?>" doesn't exist or has expired in Marketing &gt; Coupons, so visitors won't see the popup.</p>
							<?php endif; ?>
						<?php endif; ?>
						<button type="button" class="hx-pop__btn" data-hx-pop-claim><?php echo esc_html( (string) hx_pop_opt( 'hx_pop_btn' ) ); ?></button>
						<p class="hx-pop__done" hidden><svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path d="m5 12 5 5L20 7" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/></svg><span><?php echo esc_html( (string) hx_pop_opt( 'hx_pop_done' ) ); ?></span></p>
					<?php endif; ?>
					<?php if ( '' !== $no ) : ?>
						<button type="button" class="hx-pop__no" data-hx-pop-close data-hx-pre><?php echo esc_html( $no ); ?></button>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	},
	5
);

/* Spin: the server picks the prize (weighted) and signs it. */
function hx_pop_spin() {
	if ( 'on' !== hx_pop_opt( 'hx_pop_on' ) || ! hx_pop_is_wheel() ) {
		wp_send_json_error( null, 400 );
	}
	$list = hx_pop_wheel_list();
	if ( ! $list ) {
		wp_send_json_error( null, 400 );
	}
	$total = array_sum( array_column( $list, 'w' ) );
	$r     = wp_rand( 1, max( 1, $total ) );
	$won   = $list[0];
	foreach ( $list as $c ) {
		$r -= $c['w'];
		if ( $r <= 0 ) {
			$won = $c;
			break;
		}
	}
	$t = time();
	wp_send_json_success(
		array(
			'code'  => $won['code'],
			'label' => $won['label'],
			't'     => $t,
			'sig'   => hx_pop_sig( $won['code'], $t ),
		)
	);
}
add_action( 'wp_ajax_hx_pop_spin', 'hx_pop_spin' );
add_action( 'wp_ajax_nopriv_hx_pop_spin', 'hx_pop_spin' );

/* Apply the claimed coupon to the order (runs after the quantity offers). */
add_action(
	'hx_order_before_save',
	function ( $order ) {
		// phpcs:disable WordPress.Security.NonceVerification
		$sent = isset( $_POST['hx_coupon'] ) ? wc_format_coupon_code( sanitize_text_field( wp_unslash( $_POST['hx_coupon'] ) ) ) : '';
		if ( '' === $sent || 'on' !== hx_pop_opt( 'hx_pop_on' ) ) {
			return;
		}
		if ( hx_pop_is_wheel() ) {
			$t   = absint( $_POST['hx_coupon_t'] ?? 0 );
			$sig = sanitize_text_field( wp_unslash( $_POST['hx_coupon_sig'] ?? '' ) );
			$ok  = $t > time() - 30 * DAY_IN_SECONDS && hash_equals( hx_pop_sig( $sent, $t ), $sig ) && in_array( $sent, array_column( hx_pop_wheel_list(), 'code' ), true );
		} else {
			$cp = hx_pop_coupon();
			$ok = $cp && $cp['code'] === $sent;
		}
		// phpcs:enable
		if ( ! $ok ) {
			return;
		}
		$r = $order->apply_coupon( $sent );
		if ( is_wp_error( $r ) ) {
			$order->add_order_note( 'Popup coupon ' . wc_strtoupper( $sent ) . ' was not applied: ' . wp_strip_all_tags( $r->get_error_message() ) );
			return;
		}
		$order->add_order_note( 'Popup coupon ' . wc_strtoupper( $sent ) . ( hx_pop_is_wheel() ? ' (won on the spin wheel)' : '' ) . ' applied. New total: ' . hx_num( $order->get_total() ) . ' dh.' );
	},
	20
);

/* Clear the page cache after saving the Customizer, so visitors get the new popup. */
add_action(
	'customize_save_after',
	function () {
		do_action( 'litespeed_purge_all' );
	}
);

/* ---------- Customizer ---------- */

add_action(
	'customize_register',
	function ( $wp_customize ) {
		if ( ! class_exists( 'HX_Pop_Design_Control' ) ) {
			/** Picker with a small drawing of each design. */
			class HX_Pop_Design_Control extends WP_Customize_Control {
				public $type = 'hx_pop_design';
				public function render_content() {
					echo '<span class="customize-control-title">' . esc_html( $this->label ) . '</span><div class="hx-popd">';
					foreach ( $this->choices as $k => $label ) {
						echo '<label class="hx-popd__it hx-popd__it--' . esc_attr( $k ) . '"><input type="radio" name="' . esc_attr( '_customize-radio-' . $this->id ) . '" value="' . esc_attr( $k ) . '" ' . checked( $this->value(), $k, false ) . ' ' . $this->get_link() . '>'; // phpcs:ignore WordPress.Security.EscapeOutput
						echo '<span class="hx-popd__pic" aria-hidden="true"><i class="hx-popd__box"><i class="hx-popd__img"></i><i class="hx-popd__txt"><i class="hx-popd__l1"></i><i class="hx-popd__l2"></i><i class="hx-popd__code"></i><i class="hx-popd__btn"></i></i></i></span>';
						echo '<span class="hx-popd__nm">' . esc_html( $label ) . '</span></label>';
					}
					echo '</div>';
				}
			}
		}
		if ( ! class_exists( 'HX_Pop_Num_Control' ) ) {
			/** Small number box (kept in the main tab, not in Design). */
			class HX_Pop_Num_Control extends WP_Customize_Control {
				public $type = 'hx_pop_num';
				public $unit = '';
				public $max  = 600;
				public function render_content() {
					echo '<label><span class="customize-control-title">' . esc_html( $this->label ) . '</span>';
					if ( $this->description ) {
						echo '<span class="description customize-control-description">' . esc_html( $this->description ) . '</span>';
					}
					echo '<span style="display:flex;align-items:center;gap:8px"><input type="number" min="0" max="' . (int) $this->max . '" step="1" style="width:90px" value="' . esc_attr( $this->value() ) . '" ' . $this->get_link() . '> ' . esc_html( $this->unit ) . '</span></label>'; // phpcs:ignore WordPress.Security.EscapeOutput
				}
			}
		}

		$sec = 'hx_popup';
		$wp_customize->add_section(
			$sec,
			array(
				'title'       => 'Offer popup',
				'priority'    => 35,
				'description' => 'While this section is open, the popup is shown in the preview. Visitors only see it when "Show the popup" is on and you click Publish.',
			)
		);

		$sel = function ( $choices, $def ) {
			return function ( $v ) use ( $choices, $def ) {
				return array_key_exists( (string) $v, $choices ) ? (string) $v : $def;
			};
		};
		$add = function ( $id, $args, $setting = array() ) use ( $wp_customize, $sec ) {
			$wp_customize->add_setting( $id, array_merge( array( 'default' => hx_pop_opt( $id ), 'sanitize_callback' => 'sanitize_text_field' ), $setting ) );
			$wp_customize->add_control( $id, array_merge( array( 'section' => $sec ), $args ) );
		};
		$is_wheel  = function () use ( $wp_customize ) {
			$s = $wp_customize->get_setting( 'hx_pop_mode' );
			return $s && 'wheel' === $s->value();
		};
		$is_single = function () use ( $is_wheel ) {
			return ! $is_wheel();
		};

		$onoff = array( 'off' => 'Off', 'on' => 'On' );
		$add( 'hx_pop_on', array( 'label' => 'Show the popup', 'type' => 'select', 'choices' => $onoff, 'priority' => 1 ), array( 'default' => 'off', 'sanitize_callback' => $sel( $onoff, 'off' ) ) );

		$wp_customize->add_setting( 'hx_pop_design', array( 'default' => 'card', 'sanitize_callback' => $sel( hx_pop_designs(), 'card' ) ) );
		$wp_customize->add_control( new HX_Pop_Design_Control( $wp_customize, 'hx_pop_design', array( 'label' => 'Popup design', 'section' => $sec, 'priority' => 5, 'choices' => hx_pop_designs() ) ) );

		$modes = array( 'single' => 'One coupon', 'wheel' => 'Spin wheel (several coupons)' );
		$add( 'hx_pop_mode', array( 'label' => 'Offer', 'description' => 'Spin wheel: visitors win one of the coupons you create in Marketing > Coupons (chance: edit the coupon > Spin wheel chance). The discounts shown on the wheel are set below. A coupon limited to some products only works on those products. (With the Bottom bar design, the wheel shows as a card.)', 'type' => 'select', 'choices' => $modes, 'priority' => 7 ), array( 'default' => 'single', 'sanitize_callback' => $sel( $modes, 'single' ) ) );

		$wp_customize->add_setting( 'hx_pop_img', array( 'default' => 0, 'sanitize_callback' => 'absint' ) );
		$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'hx_pop_img', array( 'label' => 'Photo', 'description' => 'A big photo looks best (portrait or square). With the spin wheel, the wheel takes the photo\'s place (except in "Full photo").', 'section' => $sec, 'mime_type' => 'image', 'priority' => 10 ) ) );

		$add( 'hx_pop_badge', array( 'label' => 'Small label above the title (empty = none)', 'type' => 'text', 'priority' => 20 ) );
		$add( 'hx_pop_title', array( 'label' => 'Title', 'description' => 'Put a word between stars to colour it, e.g. *10%*', 'type' => 'text', 'priority' => 21 ) );
		$add( 'hx_pop_text', array( 'label' => 'Text', 'type' => 'textarea', 'priority' => 22 ), array( 'sanitize_callback' => 'sanitize_textarea_field' ) );

		/* Coupons created in Marketing > Coupons. */
		$codes = array();
		foreach ( get_posts( array( 'post_type' => 'shop_coupon', 'post_status' => 'publish', 'numberposts' => 200, 'orderby' => 'title', 'order' => 'ASC' ) ) as $cpost ) {
			$wc  = new WC_Coupon( $cpost->ID );
			$exp = $wc->get_date_expires();
			$lbl = wc_strtoupper( $wc->get_code() ) . ' — ' . ( 'percent' === $wc->get_discount_type() ? hx_num( $wc->get_amount() ) . '%' : hx_num( $wc->get_amount() ) . ' dh' );
			if ( $exp && $exp->getTimestamp() < time() ) {
				$lbl .= ' (expired)';
			} elseif ( $wc->get_usage_limit() && $wc->get_usage_count() >= $wc->get_usage_limit() ) {
				$lbl .= ' (used up)';
			}
			$codes[ wc_format_coupon_code( $wc->get_code() ) ] = $lbl;
		}
		$keep = function ( $id ) use ( &$codes ) {
			$cur = wc_format_coupon_code( (string) hx_pop_opt( $id ) );
			if ( '' !== $cur && ! isset( $codes[ $cur ] ) ) {
				$codes[ $cur ] = wc_strtoupper( $cur ) . ' (deleted)';
			}
		};
		$keep( 'hx_pop_code' );
		$none     = $codes ? '' : ' You have no coupons yet: create them in Marketing > Coupons, then reload this page.';
		$code_san = function ( $v ) use ( &$codes ) {
			$v = wc_format_coupon_code( (string) $v );
			return isset( $codes[ $v ] ) ? $v : '';
		};

		/* One coupon */
		$add( 'hx_pop_code', array( 'label' => 'Coupon', 'description' => 'Pick one of your coupons from Marketing > Coupons. No discount = the popup only shows your text and button.' . $none, 'type' => 'select', 'choices' => array( '' => '— No discount —' ) + $codes, 'priority' => 30, 'active_callback' => $is_single ), array( 'sanitize_callback' => $code_san ) );
		$add( 'hx_pop_btn', array( 'label' => 'Button text', 'type' => 'text', 'priority' => 31, 'active_callback' => $is_single ) );

		/* Spin wheel: uses all the coupons from Marketing > Coupons automatically. */
		$add( 'hx_pop_slices', array( 'label' => 'Discounts shown on the wheel', 'description' => 'Separated by commas, e.g. 10%, 20%, 30%, 40%, 50%. Visitors can only win the coupons you create in Marketing > Coupons; their discounts are added to the wheel automatically.', 'type' => 'text', 'priority' => 44, 'active_callback' => $is_wheel ) );
		$add( 'hx_pop_spin', array( 'label' => 'Spin button text', 'type' => 'text', 'priority' => 45, 'active_callback' => $is_wheel ) );
		$add( 'hx_pop_win', array( 'label' => 'Title after winning', 'description' => '{prize} = what they won, e.g. 10%', 'type' => 'text', 'priority' => 46, 'active_callback' => $is_wheel ) );
		$add( 'hx_pop_use', array( 'label' => 'Button after winning', 'type' => 'text', 'priority' => 47, 'active_callback' => $is_wheel ) );

		$add( 'hx_pop_done', array( 'label' => 'Message after getting the discount', 'type' => 'text', 'priority' => 48 ) );
		$add( 'hx_pop_link', array( 'label' => 'After that, go to (empty = stay on the page)', 'description' => 'e.g. a product or your shop page.', 'type' => 'url', 'priority' => 49 ), array( 'sanitize_callback' => 'esc_url_raw' ) );
		$add( 'hx_pop_no', array( 'label' => '"No thanks" text (empty = hide)', 'type' => 'text', 'priority' => 50 ) );

		$wp_customize->add_setting( 'hx_pop_delay', array( 'default' => '5', 'sanitize_callback' => function ( $v ) { return (string) min( 600, absint( $v ) ); } ) );
		$c       = new HX_Pop_Num_Control( $wp_customize, 'hx_pop_delay', array( 'label' => 'Wait before showing the popup', 'description' => 'How many seconds the visitor is on the page before the popup appears. 0 = right away.', 'section' => $sec, 'priority' => 55 ) );
		$c->unit = 'seconds';
		$wp_customize->add_control( $c );
		$yn = array( 'yes' => 'Yes', 'no' => 'No' );
		$add( 'hx_pop_exit', array( 'label' => 'Also show it when the mouse leaves the page (computers)', 'type' => 'select', 'choices' => $yn, 'priority' => 56 ), array( 'sanitize_callback' => $sel( $yn, 'yes' ) ) );
		$days = array( '1' => '1 day', '3' => '3 days', '7' => '7 days', '14' => '14 days', '30' => '30 days' );
		$add( 'hx_pop_days', array( 'label' => 'After it is closed, hide it for', 'type' => 'select', 'choices' => $days, 'priority' => 57 ), array( 'sanitize_callback' => $sel( $days, '3' ) ) );

		$wp_customize->add_setting( 'hx_pop_c1', array( 'default' => '', 'sanitize_callback' => 'sanitize_hex_color' ) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'hx_pop_c1', array( 'label' => 'Button & highlight colour (empty = store colour)', 'section' => $sec, 'priority' => 60 ) ) );
		$wp_customize->add_setting( 'hx_pop_bg', array( 'default' => '', 'sanitize_callback' => 'sanitize_hex_color' ) );
		$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'hx_pop_bg', array( 'label' => 'Popup background (empty = light grey)', 'section' => $sec, 'priority' => 61 ) ) );
	},
	40
);

add_filter(
	'hx_cui_design_ids',
	function ( $ids ) {
		$ids[] = 'hx_pop_design';
		return $ids;
	}
);

/* Customizer pane: picker look + open the popup in the preview while the section is open. */
add_action(
	'customize_controls_enqueue_scripts',
	function () {
		wp_enqueue_style( 'hx-pop-customizer', get_theme_file_uri( 'assets/hx-popup-customizer.css' ), array(), HX_VER );
		wp_add_inline_script(
			'customize-controls',
			"( function ( api ) {
				api.bind( 'ready', function () {
					var sec = api.section( 'hx_popup' );
					if ( ! sec ) { return; }
					function send() { api.previewer.send( 'hx-pop-open', !! sec.expanded() ); }
					sec.expanded.bind( send );
					api.previewer.bind( 'ready', function () { send(); setTimeout( send, 400 ); } );
				} );
			}( wp.customize ) );"
		);
	}
);

/** id => [name, url] for every product a prize coupon can be used on. */
function hx_pop_product_map( $coupons ) {
	$map = array();
	foreach ( (array) $coupons as $c ) {
		foreach ( (array) ( $c['plist'] ?? array() ) as $id ) {
			if ( ! isset( $map[ $id ] ) ) {
				$map[ $id ] = array( 'n' => html_entity_decode( get_the_title( $id ) ), 'u' => get_permalink( $id ) );
			}
		}
	}
	return $map;
}
