<?php
/**
 * Ad pixels (Customize > Ad pixels): Facebook (Meta), TikTok and Google.
 * Paste the IDs and the theme sends:
 *   PageView on every page, ViewContent on product pages, InitiateCheckout when the order form is
 *   first used, Purchase on the thank-you page (once per order, real order value) and Contact when
 *   a WhatsApp button is clicked. Amounts are sent in dirhams (MAD), like the orders.
 *
 * @package hx-store
 */

defined( 'ABSPATH' ) || exit;

/** Saved IDs, cleaned. */
function hx_px_ids() {
	$fb = array_filter( array_map( 'trim', explode( ',', preg_replace( '/[^0-9,]/', '', (string) get_theme_mod( 'hx_px_fb', '' ) ) ) ) );
	$tt = array_filter( array_map( 'trim', explode( ',', strtoupper( preg_replace( '/[^A-Za-z0-9,]/', '', (string) get_theme_mod( 'hx_px_tt', '' ) ) ) ) ) );
	$ga = array();
	foreach ( explode( ',', (string) get_theme_mod( 'hx_px_ga', '' ) ) as $g ) {
		$g = strtoupper( trim( $g ) );
		if ( preg_match( '/^(G|AW|GT|DC)-[A-Z0-9]+$/', $g ) ) {
			$ga[] = $g;
		}
	}
	$aw = trim( (string) get_theme_mod( 'hx_px_aw', '' ) );
	$aw = preg_match( '#^AW-\d+/[\w-]+$#', $aw ) ? $aw : '';
	if ( $aw && ! in_array( strtok( $aw, '/' ), $ga, true ) ) {
		$ga[] = strtok( $aw, '/' );
	}
	return array(
		'fb' => array_values( array_unique( $fb ) ),
		'tt' => array_values( array_unique( $tt ) ),
		'ga' => array_values( array_unique( $ga ) ),
		'aw' => $aw,
	);
}

/** Should pixels load on this request? */
function hx_px_active() {
	if ( is_admin() || is_customize_preview() || wp_doing_ajax() ) {
		return false;
	}
	$ids = hx_px_ids();
	return (bool) ( $ids['fb'] || $ids['tt'] || $ids['ga'] );
}

add_action(
	'customize_register',
	function ( $wp_customize ) {
		$wp_customize->add_section(
			'hx_pixels',
			array(
				'title'    => 'Ad pixels',
				'priority' => 190,
			)
		);
		$text = function ( $id, $label, $desc, $prio ) use ( $wp_customize ) {
			$wp_customize->add_setting( $id, array( 'default' => '', 'sanitize_callback' => 'sanitize_text_field' ) );
			$wp_customize->add_control( $id, array( 'label' => $label, 'description' => $desc, 'section' => 'hx_pixels', 'type' => 'text', 'priority' => $prio ) );
		};
		$text( 'hx_px_fb', 'Facebook / Meta pixel ID', 'e.g. 123456789012345', 10 );
		$text( 'hx_px_tt', 'TikTok pixel ID', 'e.g. CABCD12EFGH34IJKL5MN', 20 );
		$text( 'hx_px_ga', 'Google tag ID', 'e.g. G-ABC123XYZ or AW-123456789', 30 );
	}
);

/* Base codes + the event helper, in <head>. */
add_action(
	'wp_head',
	function () {
		if ( ! hx_px_active() ) {
			return;
		}
		$ids = hx_px_ids();
		$cfg = array(
			'fb'  => (bool) $ids['fb'],
			'tt'  => (bool) $ids['tt'],
			'ga'  => (bool) $ids['ga'],
			'aw'  => $ids['aw'],
			'cur' => 'MAD',
		);
		echo "\n<!-- Ad pixels (Customize > Ad pixels) -->\n";
		if ( $ids['fb'] ) {
			echo "<script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');";
			foreach ( $ids['fb'] as $id ) {
				echo "fbq('init'," . wp_json_encode( $id ) . ');';
			}
			echo "fbq('track','PageView');</script>\n";
		}
		if ( $ids['tt'] ) {
			echo '<script>!function(w,d,t){w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie","holdConsent","revokeConsent","grantConsent"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var r="https://analytics.tiktok.com/i18n/pixel/events.js",o=n&&n.partner;ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=r,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};n=d.createElement("script");n.type="text/javascript",n.async=!0,n.src=r+"?sdkid="+e+"&lib="+t;e=d.getElementsByTagName("script")[0];e.parentNode.insertBefore(n,e)};';
			foreach ( $ids['tt'] as $id ) {
				echo 'ttq.load(' . wp_json_encode( $id ) . ');';
			}
			echo "ttq.page();}(window,document,'ttq');</script>\n";
		}
		if ( $ids['ga'] ) {
			echo '<script async src="' . esc_url( 'https://www.googletagmanager.com/gtag/js?id=' . $ids['ga'][0] ) . '"></script>';
			echo '<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag("js",new Date());';
			foreach ( $ids['ga'] as $id ) {
				echo 'gtag("config",' . wp_json_encode( $id ) . ');';
			}
			echo "</script>\n";
		}
		?>
<script>
window.HXPX = <?php echo wp_json_encode( $cfg ); ?>;
window.hxPx = function (name, d) {
	var P = window.HXPX || {}, cur = P.cur || 'MAD';
	d = d || {};
	var items = function () { return (d.ids || []).map(function (id) { return { id: String(id), name: d.name || '', qty: d.qty || 1, price: d.price != null ? d.price : d.value }; }); };
	try {
		if (P.fb && window.fbq) {
			var fm = { view: 'ViewContent', begin: 'InitiateCheckout', purchase: 'Purchase', contact: 'Contact' }, fd = { currency: cur };
			if (d.value != null) { fd.value = d.value; }
			if (d.ids) { fd.content_ids = d.ids.map(String); fd.content_type = 'product'; }
			if (d.name) { fd.content_name = d.name; }
			if (d.qty) { fd.num_items = d.qty; }
			if (d.eid) { fbq('track', fm[name], fd, { eventID: d.eid }); } else { fbq('track', fm[name], fd); }
		}
		if (P.tt && window.ttq) {
			var tm = { view: 'ViewContent', begin: 'InitiateCheckout', purchase: 'CompletePayment', contact: 'Contact' }, td = { currency: cur, content_type: 'product' };
			if (d.value != null) { td.value = d.value; }
			if (d.ids) { td.contents = items().map(function (i) { return { content_id: i.id, content_name: i.name, quantity: i.qty, price: i.price }; }); }
			if (d.eid) { ttq.track(tm[name], td, { event_id: d.eid }); } else { ttq.track(tm[name], td); }
		}
		if (P.ga && window.gtag) {
			var gm = { view: 'view_item', begin: 'begin_checkout', purchase: 'purchase', contact: 'generate_lead' }, gd = { currency: cur };
			if (d.value != null) { gd.value = d.value; }
			if (d.ids) { gd.items = items().map(function (i) { return { item_id: i.id, item_name: i.name, quantity: i.qty, price: i.price }; }); }
			if (d.eid) { gd.transaction_id = d.eid; }
			gtag('event', gm[name], gd);
			if (name === 'purchase' && P.aw) { gtag('event', 'conversion', { send_to: P.aw, value: d.value, currency: cur, transaction_id: d.eid }); }
		}
	} catch (e) {}
};
document.addEventListener('click', function (e) {
	var a = e.target.closest && e.target.closest('a[href*="wa.me"], a[href*="whatsapp.com"]');
	if (a) { window.hxPx('contact', {}); }
}, true);
</script>
		<?php
	},
	2
);

/* Product page: ViewContent + InitiateCheckout (first time the order form is used). */
add_action(
	'wp_footer',
	function () {
		if ( ! hx_px_active() || ! ( function_exists( 'is_product' ) && is_product() ) ) {
			return;
		}
		$p = wc_get_product( get_queried_object_id() );
		if ( ! $p ) {
			return;
		}
		$price = function_exists( 'hx_prices' ) ? (float) hx_prices( $p )[0] : (float) $p->get_price();
		$data  = array(
			'ids'   => array( $p->get_id() ),
			'name'  => $p->get_name(),
			'value' => round( $price, 2 ),
			'price' => round( $price, 2 ),
			'qty'   => 1,
		);
		?>
<script>
(function () {
	var D = <?php echo wp_json_encode( $data ); ?>;
	window.hxPx('view', D);
	var sent = false;
	document.querySelectorAll('[data-hx-order]').forEach(function (form) {
		form.addEventListener('focusin', function (e) {
			if (sent || !e.target.matches('input:not([type=hidden]):not([type=radio]), select, textarea')) { return; }
			sent = true;
			var q = form.elements.qty ? parseInt(form.elements.qty.value, 10) || 1 : 1;
			var n = document.querySelector('[data-hx-pp] .hx-pp__num');
			var v = n ? parseFloat(n.getAttribute('data-mad')) || D.value : D.value;
			window.hxPx('begin', { ids: D.ids, name: D.name, value: v, price: q ? Math.round(v / q * 100) / 100 : v, qty: q });
		});
	});
})();
</script>
		<?php
	},
	30
);

/* Thank-you page: Purchase, once per order. */
add_action(
	'wp_footer',
	function () {
		if ( ! hx_px_active() || ! is_page_template( 'template-thanks.php' ) || ! isset( $_GET['order'], $_GET['key'] ) || ! function_exists( 'wc_get_order' ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}
		$o = wc_get_order( absint( $_GET['order'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		if ( ! $o || ! hash_equals( $o->get_order_key(), sanitize_text_field( wp_unslash( $_GET['key'] ) ) ) || $o->get_meta( '_hx_px_sent' ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}
		$ids  = array();
		$qty  = 0;
		$name = '';
		foreach ( $o->get_items() as $item ) {
			$ids[] = $item->get_product_id();
			$qty  += (int) $item->get_quantity();
			$name  = $name ? $name : $item->get_name();
		}
		$total = round( (float) $o->get_total(), 2 );
		$data  = array(
			'ids'   => array_values( array_unique( $ids ) ),
			'name'  => $name,
			'value' => $total,
			'price' => $qty ? round( $total / $qty, 2 ) : $total,
			'qty'   => max( 1, $qty ),
			'eid'   => 'order_' . $o->get_id(),
		);
		$o->update_meta_data( '_hx_px_sent', time() );
		$o->save();
		echo '<script>window.hxPx("purchase",' . wp_json_encode( $data ) . ');</script>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput
	},
	30
);
