<?php
/**
 * Order button that stays on screen on phones (product pages).
 * Shown at the bottom while the order form is out of view; tapping it scrolls to the form.
 * Customize > COD Form > Button: "Order button that stays on screen (phones)" and its text.
 *
 * @package hx-store
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'customize_register',
	function ( $wp_customize ) {
		$sec = $wp_customize->get_section( 'hx_cf_button' ) ? 'hx_cf_button' : 'hx_cf_layout';
		$wp_customize->add_setting( 'hx_sbuy', array( 'default' => 'show', 'sanitize_callback' => function ( $v ) { return 'hide' === $v ? 'hide' : 'show'; } ) );
		$wp_customize->add_control( 'hx_sbuy', array( 'label' => 'Order button that stays on screen (phones)', 'section' => $sec, 'type' => 'select', 'priority' => 1, 'choices' => array( 'show' => 'Show', 'hide' => 'Hide' ) ) );
		$wp_customize->add_setting( 'hx_sbuy_txt', array( 'default' => 'أطلب الآن', 'sanitize_callback' => 'sanitize_text_field' ) );
		$wp_customize->add_control( 'hx_sbuy_txt', array( 'label' => 'Its text', 'section' => $sec, 'type' => 'text', 'priority' => 2 ) );
	},
	40
);

add_action(
	'wp_footer',
	function () {
		if ( ! ( function_exists( 'is_product' ) && is_product() ) || 'hide' === get_theme_mod( 'hx_sbuy', 'show' ) ) {
			return;
		}
		$txt = trim( (string) get_theme_mod( 'hx_sbuy_txt', 'أطلب الآن' ) );
		$txt = '' !== $txt ? $txt : 'أطلب الآن';
		?>
<div class="hx-sbuy" data-hx-sbuy aria-hidden="true">
	<div class="hx-sbuy__in" dir="rtl">
		<span class="hx-sbuy__price" data-hx-sbuy-price></span>
		<button type="button" class="hx-sbuy__btn" tabindex="-1"><?php echo esc_html( $txt ); ?><svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="20" r="1.4"/><circle cx="18" cy="20" r="1.4"/><path d="M2.5 3.5h2.6l2.4 11.2a1.8 1.8 0 0 0 1.8 1.4h8.6a1.8 1.8 0 0 0 1.8-1.4l1.6-7.2H6.2"/></svg></button>
	</div>
</div>
<style>
.hx-sbuy{position:fixed;left:0;right:0;bottom:0;z-index:90;padding:10px 12px calc(10px + env(safe-area-inset-bottom));background:#fff;box-shadow:0 -6px 22px rgba(15,23,42,.14);font-family:"Cairo",var(--hx-font);letter-spacing:0;transform:translateY(115%);transition:transform .25s ease}
.hx-sbuy.is-on{transform:none}
.hx-sbuy__in{display:flex;align-items:center;gap:12px;max-width:560px;margin:0 auto}
.hx-sbuy__price{flex:0 0 auto;white-space:nowrap}
.hx-sbuy__price:empty{display:none}
.hx-sbuy__price .hx-pp__now{display:inline-flex;align-items:baseline;gap:4px;color:var(--hx-ink,#111);line-height:1}
.hx-sbuy__price .hx-pp__num{font-size:24px;font-weight:800}
.hx-sbuy__price .hx-pp__cur{font-size:13px;font-weight:700}
.hx-sbuy__btn{flex:1 1 auto;display:flex;align-items:center;justify-content:center;gap:10px;min-height:50px;margin:0;padding:0 16px;border:0;border-radius:12px;background:var(--hx-primary);color:#fff;font:inherit;font-size:18px;font-weight:800;cursor:pointer;box-shadow:0 6px 16px rgba(var(--hx-primary-rgb),.35)}
.hx-sbuy__btn:active{transform:scale(.98)}
body.hx-sbuy-pad{padding-bottom:76px}
@media (min-width:750px){.hx-sbuy{display:none}body.hx-sbuy-pad{padding-bottom:0}}
@media (prefers-reduced-motion:reduce){.hx-sbuy{transition:none}}
</style>
<script>
(function () {
	var bar = document.querySelector('[data-hx-sbuy]'), form = document.querySelector('[data-hx-order]');
	if (!bar || !form) { return; }
	var btn = bar.querySelector('button'), price = bar.querySelector('[data-hx-sbuy-price]'), pp = document.querySelector('[data-hx-pp]');
	var mq = window.matchMedia('(max-width: 749px)');
	function paint() {
		var n = pp && pp.querySelector('.hx-pp__now');
		price.innerHTML = n ? n.outerHTML : '';
	}
	function show(on) {
		on = on && mq.matches;
		bar.classList.toggle('is-on', on);
		bar.setAttribute('aria-hidden', on ? 'false' : 'true');
		btn.tabIndex = on ? 0 : -1;
		document.body.classList.toggle('hx-sbuy-pad', mq.matches);
	}
	/* Hidden while the form fills the middle of the screen or a field is being typed in. */
	var ticking = false;
	function check() {
		ticking = false;
		var r = form.getBoundingClientRect(), vh = window.innerHeight;
		var inView = r.top < vh * 0.7 && r.bottom > vh * 0.3;
		var typing = form.contains(document.activeElement) && document.activeElement !== document.body;
		show(!inView && !typing);
	}
	function queue() { if (!ticking) { ticking = true; requestAnimationFrame(check); } }
	window.addEventListener('scroll', queue, { passive: true });
	window.addEventListener('resize', queue);
	form.addEventListener('focusin', queue);
	form.addEventListener('focusout', function () { setTimeout(queue, 50); });
	check();
	btn.addEventListener('click', function () {
		var top = form.getBoundingClientRect().top + window.pageYOffset - 12;
		window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
		var first = form.querySelector('input:not([type=hidden]):not([type=radio]):not(.hx-hp), select');
		if (first) { setTimeout(function () { try { first.focus({ preventScroll: true }); } catch (err) { first.focus(); } }, 650); }
	});
	if (pp && 'MutationObserver' in window) { new MutationObserver(paint).observe(pp, { childList: true }); }
	paint();
})();
</script>
		<?php
	},
	40
);
