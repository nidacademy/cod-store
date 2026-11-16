/* Offer popup + spin wheel + discount line in the order form (Customize > Offer popup). */
(function () {
	'use strict';
	var C = window.HXPOP || {};
	var SEEN = 'hx_pop_seen', CLAIM = 'hx_pop_claim', USED = 'hx_pop_used';
	function get(k) { try { return JSON.parse(localStorage.getItem(k) || 'null'); } catch (e) { return null; } }
	function set(k, v) { try { localStorage.setItem(k, JSON.stringify(v)); } catch (e) {} }
	function del(k) { try { localStorage.removeItem(k); } catch (e) {} }

	/* Thank-you page: the discount was used, forget it. */
	if (C.ty) { if (get(CLAIM)) { set(USED, Date.now()); } del(CLAIM); return; }

	var COUP = C.coupons || {};
	function claimed() {
		var c = get(CLAIM);
		return c && COUP[c.code] ? c : null;
	}
	function esc(s) { return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; }); }

	/* Language popup (assets/hx-ltr.js): shown on a first visit until a language is chosen (cookie hx_lang_chosen). */
	function langChosen() {
		if (/(?:^|;\s*)hx_lang_chosen=1/.test(document.cookie)) { return true; }
		try { return '1' === localStorage.getItem('hx_lang_chosen'); } catch (e) { return false; }
	}
	function langReady(cb) {
		var hasSwitcher = document.querySelector('[data-hx-gt-btn]');
		if (!hasSwitcher || langChosen()) { cb(); return; }
		var start = Date.now(), done = false, t = setInterval(function () {
			var showing = document.querySelector('.hx-lp');
			/* go when a language was chosen (and the language popup is gone), or if the language popup never appeared */
			if ((langChosen() && !showing) || (!showing && Date.now() - start > 4000 && !langChosen() && !document.querySelector('.hx-lp')) ) {
				if (!showing && !langChosen() && Date.now() - start <= 4000) { return; }
				clearInterval(t);
				if (!done) { done = true; cb(); }
			}
		}, 250);
	}

	/* ---------- Popup ---------- */
	var pop = document.getElementById('hx-pop');
	var lastFocus = null, isOpen = false, spinning = false;
	var noLock = pop && pop.classList.contains('hx-pop--bar');

	function open() {
		if (!pop || isOpen) { return; }
		isOpen = true;
		lastFocus = document.activeElement;
		pop.hidden = false;
		requestAnimationFrame(function () { requestAnimationFrame(function () { pop.classList.add('is-open'); }); });
		if (!noLock) { document.documentElement.classList.add('hx-pop-lock'); }
	}
	function close(remember) {
		if (!pop || !isOpen || spinning) { return; }
		isOpen = false;
		pop.classList.remove('is-open');
		document.documentElement.classList.remove('hx-pop-lock');
		setTimeout(function () { if (!isOpen) { pop.hidden = true; } }, 280);
		if (remember && !C.preview) { set(SEEN, Date.now()); }
		if (lastFocus && lastFocus.focus) { try { lastFocus.focus({ preventScroll: true }); } catch (e) {} }
	}
	/* Closed without choosing: show it again after the same wait (and on the next pages). */
	var reopenT = null;
	function dismiss() {
		if (spinning) { return; }
		close(false);
		if (C.preview || claimed() || get(USED)) { return; }
		clearTimeout(reopenT);
		reopenT = setTimeout(function () {
			if (!claimed() && !get(USED) && !isOpen) { open(); }
		}, Math.max(3, C.delay || 0) * 1000);
	}
	function finish() {
		if (C.preview) { return; }
		var sel = pop.querySelector('[data-hx-pick]:not([hidden]) select');
		if (sel && sel.value && +sel.value !== +C.pid && C.pmap && C.pmap[sel.value]) {
			set(SEEN, Date.now());
			window.location.href = C.pmap[sel.value].u;
			return;
		}
		set(SEEN, Date.now());
		form();
		if (C.link) { setTimeout(function () { window.location.href = C.link; }, 900); }
		else { setTimeout(function () { close(false); }, 600); }
	}

	/* One coupon */
	function claimSingle() {
		var code = Object.keys(COUP)[0];
		if (!C.preview && code) { set(CLAIM, { code: code, t: Date.now() }); }
		var b = pop.querySelector('[data-hx-pop-claim]'), d = pop.querySelector('.hx-pop__done');
		if (b) { b.hidden = true; }
		pop.querySelectorAll('.hx-pop__no').forEach(function (n) { n.hidden = true; });
		if (d) { d.hidden = false; }
		if (!C.preview) {
			set(SEEN, Date.now());
			form();
			if (C.link) { setTimeout(function () { window.location.href = C.link; }, 1400); }
			else { setTimeout(function () { close(false); }, 2600); }
		}
	}

	/* ---------- Spin wheel ---------- */
	var wheelSvg = pop && pop.querySelector('[data-hx-wheel]');
	var slices = [], rot = 0;
	function buildWheel() {
		if (!wheelSvg) { return; }
		var base = (C.slices && C.slices.length) ? C.slices : (C.wheel && C.wheel.length) ? C.wheel : [{ code: '', label: '10%' }, { code: '', label: '20%' }, { code: '', label: '15%' }];
		/* Each discount once; only repeat when there are fewer than 4 (so slices stay wide). */
		slices = base.slice();
		while (slices.length < 4) { slices = slices.concat(base); }
		var n = slices.length, a = 360 / n, h = '', odd = n % 2 === 1;
		var fs = n <= 6 ? 26 : n <= 8 ? 22 : 18;
		function pt(deg, r) { var t = deg * Math.PI / 180; return (Math.sin(t) * r).toFixed(2) + ' ' + (-Math.cos(t) * r).toFixed(2); }
		h += '<defs><radialGradient id="hxw-shade" r="0.5"><stop offset="0.55" stop-color="#000" stop-opacity="0"/><stop offset="1" stop-color="#000" stop-opacity="0.07"/></radialGradient>' +
			'<linearGradient id="hxw-rim" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#fff" stop-opacity="0.18"/><stop offset="1" stop-color="#000" stop-opacity="0.12"/></linearGradient></defs>';
		h += '<circle r="99" class="hx-w__rim"/><circle r="99" fill="url(#hxw-rim)"/>';
		slices.forEach(function (s, i) {
			var a0 = i * a, a1 = a0 + a, mid = a0 + a / 2;
			/* alternate colours; with an odd count the last slice gets a 3rd colour so two same colours never touch */
			var cls = odd && i === n - 1 ? 'c' : (i % 2 ? 'b' : 'a');
			var m = /^\s*([\d.,]+)\s*(.*)$/.exec(String(s.label)), num = m ? m[1] : s.label, unit = m ? m[2] : '';
			h += '<path class="hx-w__s hx-w__s--' + cls + '" d="M0 0 L' + pt(a0, 88) + ' A88 88 0 0 1 ' + pt(a1, 88) + ' Z"/>';
			h += '<g transform="rotate(' + mid + ')" class="hx-w__lab hx-w__lab--' + cls + '">' +
				'<text class="hx-w__t" x="0" y="-60" text-anchor="middle" dominant-baseline="central" direction="ltr" font-size="' + fs + '">' + esc(num) +
				(unit === '%' ? '<tspan class="hx-w__u" dx="1" dy="-0.45em" font-size="0.5em">%</tspan>' : '') + '</text>' +
				(unit && unit !== '%' ? '<text class="hx-w__k" x="0" y="-39" text-anchor="middle" dominant-baseline="central">' + esc(unit) + '</text>' : '') +
				'</g>';
		});
		h += '<circle r="88" fill="url(#hxw-shade)" pointer-events="none"/><circle r="88" class="hx-w__ring"/>';
		for (var d = 0; d < 18; d++) { h += '<circle class="hx-w__dot' + (d % 2 ? ' hx-w__dot--b' : '') + '" r="2.3" transform="rotate(' + (d * 20) + ') translate(0 -93.5)"/>'; }
		wheelSvg.innerHTML = h;
	}
	
function pickLocal() {
		var list = C.wheel || [], total = 0, r;
		list.forEach(function (c) { total += c.w || 0; });
		if (!list.length) { return { code: '', label: slices[0].label }; }
		r = Math.random() * (total || list.length);
		for (var i = 0; i < list.length; i++) { r -= total ? list[i].w : 1; if (r < 0) { return list[i]; } }
		return list[list.length - 1];
	}
	function spinTo(prize, done) {
		var idx = [];
		slices.forEach(function (s, i) { if (s.code && s.code === prize.code) { idx.push(i); } });
		if (!idx.length) { slices.forEach(function (s, i) { if (s.label === prize.label) { idx.push(i); } }); }
		if (!idx.length) { idx = [0]; }
		var i = idx[Math.floor(Math.random() * idx.length)], a = 360 / slices.length;
		var target = 360 - (i * a + a / 2) + (Math.random() - 0.5) * a * 0.6;
		rot = rot - (rot % 360) + 360 * 6 + target;
		wheelSvg.style.transform = 'rotate(' + rot + 'deg)';
		var fired = false, end = function () { if (!fired) { fired = true; done(); } };
		wheelSvg.addEventListener('transitionend', end, { once: true });
		setTimeout(end, 5600);
	}
	function showWin(prize) {
		pop.classList.add('is-won');
		pop.querySelectorAll('[data-hx-pre]').forEach(function (el) { el.hidden = true; });
		var w = pop.querySelector('[data-hx-win]'), t = pop.querySelector('[data-hx-win-title]'), c = w.querySelector('[data-hx-copy]');
		var tpl = esc(t.getAttribute('data-tpl') || '').replace('{prize}', esc(prize.label)).replace(/\*(.+?)\*/g, '<em>$1</em>').replace(/(\d+(?:[.,]\d+)?\s?%)/g, '<bdi dir="ltr">$1</bdi>');
		t.innerHTML = '<span>' + tpl + '</span>';
		t.id = 'hx-pop-t';
		c.textContent = (prize.code || 'SAVE10').toUpperCase();
		var fl = w.querySelector('[data-hx-for]'), info = COUP[prize.code];
		if (fl) { fl.hidden = !(info && info.for); if (info && info.for) { fl.textContent = C.txt.for.replace('%s', info.for); } }
		/* Product picker: the products this coupon works on */
		var pick = w.querySelector('[data-hx-pick]'), sel = pick && pick.querySelector('select'), ids = (info && info.plist) || [], pm = C.pmap || {};
		if (pick && sel) {
			sel.innerHTML = '';
			ids.forEach(function (id) {
				if (!pm[id]) { return; }
				var o = document.createElement('option');
				o.value = id; o.textContent = pm[id].n;
				if (+id === +C.pid) { o.selected = true; }
				sel.appendChild(o);
			});
			pick.hidden = !sel.options.length;
			if (fl && sel.options.length) { fl.hidden = true; }
		}
		confetti();
		w.hidden = false;
	}
	function spin(btn) {
		if (spinning) { return; }
		spinning = true;
		btn.disabled = true;
		pop.classList.add('is-spinning');
		var go = function (prize) {
			spinTo(prize, function () {
				spinning = false;
				pop.classList.remove('is-spinning');
				if (!C.preview && prize.sig) { set(CLAIM, { code: prize.code, t: prize.t, sig: prize.sig }); set(SEEN, Date.now()); form(); }
				showWin(prize);
			});
		};
		if (C.preview) { go(pickLocal()); return; }
		var fd = new FormData();
		fd.append('action', 'hx_pop_spin');
		fetch(C.ajax, { method: 'POST', body: fd, credentials: 'same-origin' })
			.then(function (r) { return r.json(); })
			.then(function (res) {
				if (!res || !res.success) { throw new Error('no'); }
				go(res.data);
			})
			.catch(function () { spinning = false; btn.disabled = false; btn.textContent = C.txt.err; });
	}

	/* Little confetti burst when the visitor wins */
	function confetti() {
		var box = pop.querySelector('.hx-pop__box'), wrap = document.createElement('div');
		if (!box || (window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches)) { return; }
		wrap.className = 'hx-pop__confetti';
		var cols = ['var(--pop-main)', '#f59e0b', '#ef4444', '#3b82f6', '#22c55e', '#a855f7'];
		for (var i = 0; i < 46; i++) {
			var c = document.createElement('i');
			c.style.left = (Math.random() * 100) + '%';
			c.style.background = cols[i % cols.length];
			c.style.animationDelay = (Math.random() * 0.35) + 's';
			c.style.animationDuration = (1.4 + Math.random() * 1.1) + 's';
			c.style.setProperty('--dx', ((Math.random() - 0.5) * 140) + 'px');
			c.style.setProperty('--r', (Math.random() * 720 - 360) + 'deg');
			if (i % 3 === 0) { c.style.borderRadius = '50%'; }
			wrap.appendChild(c);
		}
		box.appendChild(wrap);
		setTimeout(function () { wrap.remove(); }, 3000);
	}

	/* Copy the code */
	function copy(el) {
		var code = el.textContent.trim(), keep = el.innerHTML;
		var ok = function () { el.innerHTML = '✓ ' + esc(C.txt.copied); setTimeout(function () { el.innerHTML = keep; }, 1400); };
		if (navigator.clipboard && navigator.clipboard.writeText) { navigator.clipboard.writeText(code).then(ok, function () {}); }
		else {
			var ta = document.createElement('textarea'); ta.value = code; document.body.appendChild(ta); ta.select();
			try { document.execCommand('copy'); ok(); } catch (e) {}
			document.body.removeChild(ta);
		}
	}

	if (pop) {
		buildWheel();
		pop.addEventListener('click', function (e) {
			var t = e.target;
			/* "No thanks" = stop showing it. Clicking outside, the x or Escape = it comes back. */
			if (t.closest('[data-hx-pop-close]')) { if (t.closest('.hx-pop__no')) { close(true); } else { dismiss(); } return; }
			if (t.closest('[data-hx-pop-claim]')) { claimSingle(); return; }
			if (t.closest('[data-hx-pop-spin]')) { spin(t.closest('[data-hx-pop-spin]')); return; }
			if (t.closest('[data-hx-pop-use]')) { finish(); if (C.preview) { close(false); } return; }
			if (t.closest('[data-hx-copy]')) { copy(t.closest('[data-hx-copy]')); }
		});
		document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && isOpen) { dismiss(); } });
	}
	function reset() {
		pop.classList.remove('is-won');
		pop.querySelectorAll('[data-hx-pre]').forEach(function (el) { el.hidden = false; });
		pop.querySelectorAll('[data-hx-win], .hx-pop__done').forEach(function (el) { el.hidden = true; });
		var b = pop.querySelector('[data-hx-pop-claim]'), s = pop.querySelector('[data-hx-pop-spin]');
		if (b) { b.hidden = false; }
		if (s) { s.disabled = false; }
		if (wheelSvg) { wheelSvg.style.transition = 'none'; rot = 0; wheelSvg.style.transform = ''; void wheelSvg.offsetWidth; wheelSvg.style.transition = ''; }
	}

	if (pop && C.preview) {
		/* Customizer: open while the "Offer popup" section is open. */
		var hook = function () {
			wp.customize.preview.bind('hx-pop-open', function (on) { if (on) { reset(); open(); } else { isOpen && close(false); } });
		};
		if (window.wp && wp.customize) {
			if (wp.customize.preview) { hook(); } else { wp.customize.bind('preview-ready', hook); }
		}
	} else if (pop) {
		var seen = get(SEEN), wait = (C.days || 0) * 864e5;
		if (!get(USED) && !claimed() && !(seen && Date.now() - seen < wait)) {
			/* Wait for the "Choose your language" popup first: the countdown starts once a language is chosen. */
			langReady(function () {
				setTimeout(open, Math.max(0, C.delay || 0) * 1000);
				if (C.exit && window.matchMedia && matchMedia('(hover: hover) and (pointer: fine)').matches) {
					document.addEventListener('mouseout', function (e) {
						if (!e.relatedTarget && e.clientY <= 0) { open(); }
					});
				}
			});
		}
	}

	/* ---------- Order form: show the discount ---------- */
	function num(v) {
		v = Math.round(v * 100) / 100;
		var s = Math.floor(v) === v ? String(v) : v.toFixed(2);
		var p = s.split('.');
		return p[0].replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + (p[1] ? '.' + p[1] : '');
	}
	function money(v) {
		return '<span class="hx-m notranslate" data-mad="' + (Math.round(v * 100) / 100) + '">' + num(v) + '</span> <span class="hx-c">' + C.txt.cur + '</span>';
	}
	function base(f) {
		var on = f.querySelector('input[name="offer"]:checked'), m;
		if (on && (m = /data-mad="([\d.]+)"/.exec(on.getAttribute('data-pp') || ''))) {
			return { total: parseFloat(m[1]), qty: parseInt(on.getAttribute('data-qty'), 10) || 1 };
		}
		var qty = parseInt((f.elements.qty && f.elements.qty.value) || '1', 10) || 1;
		var el = document.querySelector('[data-hx-pp] .hx-pp__num[data-mad]');
		var p = el ? parseFloat(el.getAttribute('data-mad')) : (C.price || 0);
		return { total: p * qty, qty: qty };
	}
	function paint(f, box, claim) {
		var cp = COUP[claim.code], b = base(f), off = 0, t = C.txt;
		if (cp.type === 'percent') { off = b.total * cp.amount / 100; }
		else if (cp.type === 'fixed_product') { off = cp.amount * b.qty; }
		else { off = cp.amount; }
		off = Math.min(off, b.total);
		var head = '<span class="hx-pop-cf__h"><svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path d="M20 12v8H4v-8M2 7h20v5H2zM12 22V7M12 7H7.5a2.5 2.5 0 1 1 0-5C11 2 12 7 12 7zM12 7h4.5a2.5 2.5 0 1 0 0-5C13 2 12 7 12 7z" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>' + t.on.replace('%s', '<b dir="ltr">' + esc(claim.code.toUpperCase()) + '</b>') + '</span>';
		if (cp.min && b.total < cp.min) {
			box.innerHTML = head + '<span class="hx-pop-cf__min">' + t.min.replace('%s', num(cp.min)) + '</span>';
		} else if (b.total > 0) {
			box.innerHTML = head + '<span class="hx-pop-cf__row"><span>' + t.save + '</span><span class="hx-pop-cf__off">−' + money(off) + '</span></span>' +
				'<span class="hx-pop-cf__row hx-pop-cf__row--total"><span>' + t.total + '</span><span><s>' + money(b.total) + '</s> <b>' + money(b.total - off) + '</b></span></span>';
		} else {
			box.innerHTML = head;
		}
		if (window.hxSetCur) { try { window.hxSetCur(localStorage.getItem('hx_cur') || 'MAD'); } catch (e) {} }
	}
	/* Is the coupon allowed for the product on this page? (Marketing > Coupons > Usage restriction) */
	function allowed(cp) {
		var pid = C.pid || 0, cats = C.pcats || [];
		var inList = function (list, v) { return (list || []).indexOf(v) > -1; };
		if (!pid) { return false; }
		if ((cp.pids || []).length && !inList(cp.pids, pid)) { return false; }
		if (inList(cp.xpids, pid)) { return false; }
		if ((cp.cats || []).length && !cats.some(function (c) { return inList(cp.cats, c); })) { return false; }
		if (cats.some(function (c) { return inList(cp.xcats, c); })) { return false; }
		return true;
	}
	function form() {
		var claim = claimed();
		if (!claim || !allowed(COUP[claim.code])) { return; }
		document.querySelectorAll('[data-hx-order]').forEach(function (f) {
			if (f.querySelector('.hx-pop-cf')) { return; }
			[['hx_coupon', claim.code], ['hx_coupon_t', claim.t || ''], ['hx_coupon_sig', claim.sig || '']].forEach(function (p) {
				var inp = document.createElement('input');
				inp.type = 'hidden'; inp.name = p[0]; inp.value = p[1];
				f.appendChild(inp);
			});
			var box = document.createElement('div');
			box.className = 'hx-pop-cf';
			box.setAttribute('aria-live', 'polite');
			var act = f.querySelector('.hx-order__actions');
			if (act) { act.parentNode.insertBefore(box, act); } else { f.appendChild(box); }
			var upd = function () { setTimeout(function () { paint(f, box, claim); }, 30); };
			f.addEventListener('change', upd);
			f.addEventListener('click', upd);
			document.querySelectorAll('[data-hx-pp]').forEach(function (pp) {
				if (window.MutationObserver) { new MutationObserver(upd).observe(pp, { childList: true, subtree: true }); }
			});
			paint(f, box, claim);
		});
	}
	if (!C.preview) { form(); }
})();
