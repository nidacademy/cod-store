/* When GTranslate shows the store in a left-to-right language, flip the text (not the layout). */
(function () {
	'use strict';
	var root = document.documentElement, on = false, timer = null, mo = null;
	var SKIP = '#wpadminbar, .hx-gt, .hx-cur, .hx-msw, .hx-lp, script, style, svg, img, font, br';

	function isLtr() {
		return root.classList.contains('translated-ltr');
	}

	function fix(scope) {
		var els = (scope || document.body).querySelectorAll('*');
		for (var i = 0; i < els.length; i++) {
			var e = els[i];
			if (e.hasAttribute('data-hx-ltr') || e.matches(SKIP) || e.closest('#wpadminbar, .hx-gt, .hx-cur, .hx-msw, .hx-lp')) {
				continue;
			}
			var cs = getComputedStyle(e), mark = '';
			if ('rtl' === e.getAttribute('dir')) {
				e.setAttribute('dir', 'ltr');
				mark += 'd';
			} else if ('rtl' === cs.direction && (!e.parentElement || 'ltr' === getComputedStyle(e.parentElement).direction)) {
				e.style.direction = 'ltr';
				mark += 's';
			}
			if ('right' === cs.textAlign || ('start' === cs.textAlign && 'rtl' === cs.direction)) {
				e.style.textAlign = 'left';
				mark += 'a';
			}
			if (mark) {
				e.setAttribute('data-hx-ltr', mark);
			}
		}
	}

	function undo() {
		var els = document.querySelectorAll('[data-hx-ltr]');
		for (var i = 0; i < els.length; i++) {
			var e = els[i], m = e.getAttribute('data-hx-ltr');
			if (m.indexOf('d') > -1) { e.setAttribute('dir', 'rtl'); }
			if (m.indexOf('s') > -1) { e.style.direction = ''; }
			if (m.indexOf('a') > -1) { e.style.textAlign = ''; }
			e.removeAttribute('data-hx-ltr');
		}
	}

	function watchNew() {
		mo = new MutationObserver(function (list) {
			var added = list.some(function (r) {
				return Array.prototype.some.call(r.addedNodes, function (n) { return 1 === n.nodeType && 'FONT' !== n.tagName; });
			});
			if (added) {
				clearTimeout(timer);
				timer = setTimeout(function () { fix(); }, 150);
			}
		});
		mo.observe(document.body, { childList: true, subtree: true });
	}

	function sync() {
		var want = isLtr();
		if (want === on) { return; }
		on = want;
		if (on) {
			fix();
			watchNew();
		} else {
			if (mo) { mo.disconnect(); mo = null; }
			undo();
		}
	}

	new MutationObserver(sync).observe(root, { attributes: true, attributeFilter: ['class'] });
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', sync);
	} else {
		sync();
	}
})();

/* Language dropdown in the top bar (drives GTranslate). */
(function () {
	'use strict';
	var box = document.querySelector('[data-hx-gt]');
	if (!box) { return; }
	var btn = box.querySelector('[data-hx-gt-btn]'), menu = box.querySelector('[data-hx-gt-menu]'),
		flag = box.querySelector('[data-hx-gt-flag]'), code = box.querySelector('[data-hx-gt-code]'),
		def = box.getAttribute('data-default') || 'ar';

	function current() {
		var r = document.documentElement, l = (r.getAttribute('lang') || '').toLowerCase(), hit = def;
		if (!/translated-(ltr|rtl)/.test(r.className) || !l) { return def; }
		Array.prototype.forEach.call(box.querySelectorAll('.hx-gt__opt'), function (a) {
			var d = a.getAttribute('data-lang').toLowerCase();
			if (d === l || (hit === def && d.slice(0, 2) === l.slice(0, 2))) { hit = a.getAttribute('data-lang'); }
		});
		return hit;
	}
	function paint() {
		var cur = current(), opt = box.querySelector('.hx-gt__opt[data-lang="' + cur + '"]');
		if (!opt) { cur = def; opt = box.querySelector('.hx-gt__opt[data-lang="' + def + '"]'); }
		Array.prototype.forEach.call(box.querySelectorAll('.hx-gt__opt'), function (a) {
			a.classList.toggle('is-current', a === opt);
		});
		if (opt) { flag.src = opt.querySelector('img').src; }
		code.textContent = cur.slice(0, 2).toUpperCase();
	}
	function open() {
		menu.hidden = false;
		btn.setAttribute('aria-expanded', 'true');
		requestAnimationFrame(function () { box.classList.add('is-open'); });
	}
	function close() {
		box.classList.remove('is-open');
		btn.setAttribute('aria-expanded', 'false');
		setTimeout(function () { if (!box.classList.contains('is-open')) { menu.hidden = true; } }, 180);
	}
	function go(lang) {
		var a = box.querySelector('.hx-gt__engine a[data-gt-lang="' + lang + '"]');
		if (typeof window.doGTranslate === 'function') {
			window.doGTranslate(def + '|' + lang);
		} else if (a) {
			a.click();
		}
		setTimeout(paint, 60);
	}

	btn.addEventListener('click', function (e) {
		e.stopPropagation();
		if (menu.hidden) { open(); } else { close(); }
	});
	menu.addEventListener('click', function (e) {
		var a = e.target.closest('.hx-gt__opt');
		if (!a) { return; }
		e.preventDefault();
		close();
		if (!a.classList.contains('is-current')) { go(a.getAttribute('data-lang')); }
	});
	document.addEventListener('click', function (e) { if (!box.contains(e.target) && !menu.hidden) { close(); } });
	document.addEventListener('keydown', function (e) { if ('Escape' === e.key && !menu.hidden) { close(); btn.focus(); } });
	new MutationObserver(paint).observe(document.documentElement, { attributes: true, attributeFilter: ['class', 'lang'] });
	paint();

	/* ---------- First visit: "Choose your language" popup ---------- */
	var KEY = 'hx_lang_chosen';
	function chosen() {
		if (/(?:^|;\s*)hx_lang_chosen=1/.test(document.cookie)) { return true; }
		try { return '1' === localStorage.getItem(KEY); } catch (e) { return false; }
	}
	function remember() {
		document.cookie = KEY + '=1; path=/; max-age=31536000; SameSite=Lax';
		try { localStorage.setItem(KEY, '1'); } catch (e) {}
	}
	function popup() {
		var cur = current(), opts = Array.prototype.slice.call(box.querySelectorAll('.hx-gt__opt'));
		var wrap = document.createElement('div');
		wrap.className = 'hx-lp notranslate';
		wrap.innerHTML = '<div class="hx-lp__card" role="dialog" aria-modal="true" aria-labelledby="hx-lp-t">' +
			'<button type="button" class="hx-lp__x" aria-label="Close / إغلاق"><svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"/></svg></button>' +
			'<h2 class="hx-lp__t" id="hx-lp-t">Language - اللغة</h2>' +
			'<div class="hx-lp__grid"></div></div>';
		var grid = wrap.querySelector('.hx-lp__grid');
		opts.forEach(function (a) {
			var l = a.getAttribute('data-lang'), b = document.createElement('button');
			b.type = 'button';
			b.className = 'hx-lp__opt' + (l === cur ? ' is-cur' : '');
			b.setAttribute('data-lang', l);
			b.innerHTML = '<i class="hx-lp__flag"><img src="' + a.querySelector('img').src + '" width="56" height="56" alt=""></i>' +
				'<span dir="auto">' + a.querySelector('span').textContent + '</span>';
			grid.appendChild(b);
		});
		function shut() {
			remember();
			wrap.classList.remove('is-open');
			document.documentElement.classList.remove('hx-lp-lock');
			setTimeout(function () { wrap.remove(); }, 250);
		}
		wrap.addEventListener('click', function (e) {
			var b = e.target.closest('.hx-lp__opt');
			if (b) {
				shut();
				if (b.getAttribute('data-lang') !== current()) { go(b.getAttribute('data-lang')); }
			} else if (e.target === wrap || e.target.closest('.hx-lp__x')) {
				shut();
			}
		});
		document.addEventListener('keydown', function esc(e) {
			if ('Escape' === e.key && document.body.contains(wrap)) { shut(); document.removeEventListener('keydown', esc); }
		});
		document.body.appendChild(wrap);
		document.documentElement.classList.add('hx-lp-lock');
		requestAnimationFrame(function () {
			wrap.classList.add('is-open');
			var first = grid.querySelector('.hx-lp__opt');
			if (first) { first.focus({ preventScroll: true }); }
		});
	}
	// Remember a choice made from the header dropdown too.
	menu.addEventListener('click', function (e) { if (e.target.closest('.hx-gt__opt')) { remember(); } });
	if (!chosen()) {
		setTimeout(function () {
			if (/translated-(ltr|rtl)/.test(document.documentElement.className)) { remember(); return; }
			popup();
		}, 700);
	}
})();
