/* Language + currency rows inside the hamburger menu (tap a row to open its list). */
(function () {
	'use strict';
	var box = document.querySelector('[data-hx-msw]');
	if (!box) { return; }
	var def = box.getAttribute('data-default') || 'ar';

	function curLang() {
		var r = document.documentElement, l = (r.getAttribute('lang') || '').toLowerCase(), hit = def;
		if (!/translated-(ltr|rtl)/.test(r.className) || !l) { return def; }
		box.querySelectorAll('[data-hx-mlang]').forEach(function (a) {
			var d = a.getAttribute('data-hx-mlang').toLowerCase();
			if (d === l || (hit === def && d.slice(0, 2) === l.slice(0, 2))) { hit = a.getAttribute('data-hx-mlang'); }
		});
		return hit;
	}
	function curCur() {
		try { return localStorage.getItem('hx_cur') || 'MAD'; } catch (e) { return 'MAD'; }
	}
	function mark(group, attr, val) {
		var g = box.querySelector('[data-hx-msw-group="' + group + '"]');
		if (!g) { return; }
		var on = null;
		g.querySelectorAll('[' + attr + ']').forEach(function (a) {
			var hit = a.getAttribute(attr) === val;
			a.classList.toggle('is-current', hit);
			if (hit) { on = a; }
		});
		if (on) {
			var f = g.querySelector('[data-hx-msw-vflag]'), t = g.querySelector('[data-hx-msw-vtxt]');
			if (f.getAttribute('src') !== on.getAttribute('data-flag')) { f.setAttribute('src', on.getAttribute('data-flag')); }
			if (t.textContent !== on.getAttribute('data-txt')) { t.textContent = on.getAttribute('data-txt'); }
		}
	}
	var LABELS = {
		ar: ['اللغة', 'العملة'], en: ['Language', 'Currency'], fr: ['Langue', 'Devise'], es: ['Idioma', 'Moneda'],
		de: ['Sprache', 'Währung'], it: ['Lingua', 'Valuta'], pt: ['Idioma', 'Moeda'], nl: ['Taal', 'Valuta'], ru: ['Язык', 'Валюта'], zh: ['语言', '货币']
	};
	function labels(l) {
		var t = LABELS[String(l).toLowerCase().slice(0, 2)] || LABELS.en;
		[['lang', 0], ['cur', 1]].forEach(function (p) {
			var el = box.querySelector('[data-hx-msw-label="' + p[0] + '"]');
			if (el && el.textContent !== t[p[1]]) { el.textContent = t[p[1]]; }
		});
	}
	function paint() {
		labels(curLang());
		mark('lang', 'data-hx-mlang', curLang());
		mark('cur', 'data-hx-mcur', curCur());
	}
	function toggle(g, on) {
		var row = g.querySelector('[data-hx-msw-row]'), panel = g.querySelector('.hx-msw__panel');
		panel.hidden = !on;
		g.classList.toggle('is-open', on);
		row.setAttribute('aria-expanded', on ? 'true' : 'false');
	}
	function closeAll() { box.querySelectorAll('[data-hx-msw-group]').forEach(function (g) { toggle(g, false); }); }

	box.addEventListener('click', function (e) {
		var row = e.target.closest('[data-hx-msw-row]');
		if (row) {
			var g = row.closest('[data-hx-msw-group]'), open = !g.classList.contains('is-open');
			closeAll();
			toggle(g, open);
			if (open) { setTimeout(function () { g.scrollIntoView({ block: 'nearest', behavior: 'smooth' }); }, 30); }
			return;
		}
		var a = e.target.closest('[data-hx-mlang]');
		if (a) {
			e.preventDefault();
			var l = a.getAttribute('data-hx-mlang');
			if (l !== curLang()) {
				if (typeof window.doGTranslate === 'function') {
					window.doGTranslate(def + '|' + l);
				} else {
					var en = document.querySelector('.hx-gt__engine a[data-gt-lang="' + l + '"]');
					if (en) { en.click(); }
				}
			}
			document.cookie = 'hx_lang_chosen=1; path=/; max-age=31536000; SameSite=Lax';
			try { localStorage.setItem('hx_lang_chosen', '1'); } catch (err) {}
			closeAll();
			setTimeout(paint, 80);
			return;
		}
		var c = e.target.closest('[data-hx-mcur]');
		if (c) {
			e.preventDefault();
			if (typeof window.hxSetCur === 'function') { window.hxSetCur(c.getAttribute('data-hx-mcur')); }
			closeAll();
			paint();
		}
	});
	/* Close the lists when the menu closes. */
	document.addEventListener('click', function (e) { if (e.target.closest && e.target.closest('[data-hx-menu-close]')) { closeAll(); } });
	new MutationObserver(paint).observe(document.documentElement, { attributes: true, attributeFilter: ['class', 'lang'] });
	window.addEventListener('hx:cur', paint);
	paint();
})();
