/* Currency switcher: converts the prices shown on the page. Display only - orders stay in dirhams.
 * Prices are marked by the theme: .hx-m[data-mad] = amount in dirhams, .hx-c = currency word. */
(function () {
	'use strict';
	var C = window.HXFX || { rates: {}, cur: {} };
	var KEY = 'hx_cur';
	var root = document.documentElement;
	function ok(c) { return c === 'MAD' || (C.cur[c] && C.rates[c] > 0); }
	var cur = 'MAD';
	try { cur = localStorage.getItem(KEY) || 'MAD'; } catch (e) {}
	if (!ok(cur)) { cur = 'MAD'; }

	/* Arabic page (not translated, or translated to Arabic): Arabic symbols (ر.س), otherwise codes (SAR). */
	function arabic() {
		if (!/translated-(ltr|rtl)/.test(root.className)) { return true; }
		return String(root.getAttribute('lang') || '').toLowerCase().slice(0, 2) === 'ar';
	}
	function sym(c) {
		var d = C.cur[c] || {};
		return (arabic() ? d.ar : d.sym) || c;
	}
	function group(s) { return s.replace(/\B(?=(\d{3})+(?!\d))/g, ' '); }
	function fmtMad(v) { /* same as the theme's hx_num() */
		if (Math.floor(v) === v) { return group(String(v)); }
		var p = v.toFixed(2).split('.');
		return group(p[0]) + '.' + p[1];
	}
	function fmt(v, dec) {
		if (dec > 0) {
			var p = (Math.max(v, 0.01)).toFixed(dec).split('.');
			return group(p[0]) + '.' + p[1];
		}
		var n = Math.round(v);
		if (n < 1 && v > 0) { n = 1; }
		return group(String(n));
	}

	function apply() {
		var rate = C.rates[cur], dec = (C.cur[cur] || {}).dec || 0, s = cur === 'MAD' ? '' : sym(cur);
		document.querySelectorAll('.hx-m[data-mad]').forEach(function (el) {
			var v = parseFloat(el.getAttribute('data-mad')) || 0;
			var t = cur === 'MAD' ? fmtMad(v) : fmt(v * rate, dec);
			if (el.textContent !== t) { el.textContent = t; }
		});
		document.querySelectorAll('.hx-c').forEach(function (el) {
			if (cur === 'MAD') {
				if (el.hasAttribute('data-hx-orig')) {
					el.textContent = el.getAttribute('data-hx-orig');
					el.removeAttribute('data-hx-orig');
					el.classList.remove('notranslate');
				}
				return;
			}
			if (!el.hasAttribute('data-hx-orig')) { el.setAttribute('data-hx-orig', el.textContent); }
			el.classList.add('notranslate');
			if (el.textContent !== s) { el.textContent = s; }
		});
	}

	var queued = false;
	function schedule() {
		if (queued) { return; }
		queued = true;
		setTimeout(function () { queued = false; apply(); paint(); }, 20);
	}

	/* Switcher (desktop) */
	var box = document.querySelector('[data-hx-cur]');
	function paint() {
		if (!box) { return; }
		var el = box.querySelector('[data-hx-cur-sym]'), t = cur, fl = box.querySelector('[data-hx-cur-flag]'), op = box.querySelector('[data-cur="' + cur + '"]');
		if (el.textContent !== t) { el.textContent = t; }
		if (fl && op && fl.getAttribute('src') !== op.getAttribute('data-flag')) { fl.setAttribute('src', op.getAttribute('data-flag')); }
		box.querySelectorAll('[data-cur]').forEach(function (a) { a.classList.toggle('is-current', a.getAttribute('data-cur') === cur); });
	}
	function setCur(c) {
		cur = ok(c) ? c : 'MAD';
		try { localStorage.setItem(KEY, cur); } catch (err) {}
		paint();
		apply();
		try { window.dispatchEvent(new Event('hx:cur')); } catch (err2) {}
	}
	window.hxSetCur = setCur;
	if (box) {
		var btn = box.querySelector('[data-hx-cur-btn]'), menu = box.querySelector('[data-hx-cur-menu]');
		var open = function (on) {
			menu.hidden = !on;
			btn.setAttribute('aria-expanded', on ? 'true' : 'false');
			if (on) { requestAnimationFrame(function () { box.classList.add('is-open'); }); } else { box.classList.remove('is-open'); }
		};
		btn.addEventListener('click', function (e) { e.stopPropagation(); open(menu.hidden); });
		box.addEventListener('click', function (e) {
			var a = e.target.closest('[data-cur]');
			if (!a) { return; }
			e.preventDefault();
			setCur(a.getAttribute('data-cur'));
			open(false);
		});
		document.addEventListener('click', function (e) { if (!box.contains(e.target)) { open(false); } });
		document.addEventListener('keydown', function (e) { if (e.key === 'Escape') { open(false); } });
	}

	/* Choosing a language (first-visit popup or language menu) also picks the currency:
	   Arabic = DH, euro-zone languages = €, other languages = $. The desktop switcher can still override it. */
	var EURO = ['fr', 'es', 'de', 'it', 'pt', 'nl', 'el', 'fi', 'sk', 'sl', 'et', 'lv', 'lt', 'ga', 'mt', 'hr'];
	function forLang(l) {
		l = String(l || '').toLowerCase().slice(0, 2);
		if (C.langs && C.langs[l]) { return C.langs[l]; }
		return (!l || l === 'ar') ? 'MAD' : (EURO.indexOf(l) > -1 ? 'EUR' : 'USD');
	}
	document.addEventListener('click', function (e) {
		var a = e.target.closest && e.target.closest('.hx-lp__opt[data-lang], .hx-gt__opt[data-lang], [data-hx-mlang]');
		if (a) { setCur(forLang(a.getAttribute('data-lang') || a.getAttribute('data-hx-mlang'))); }
	}, true);

	paint();
	apply();
	if ('MutationObserver' in window) {
		var mo = new MutationObserver(schedule);
		mo.observe(document.body, { childList: true, subtree: true, characterData: true });
		mo.observe(root, { attributes: true, attributeFilter: ['class', 'lang'] });
	}
})();
