(function () {
	'use strict';

	document.querySelectorAll('[data-hx-gallery]').forEach(function (g) {
		var track = g.querySelector('[data-hx-track]');
		var cur = g.querySelector('[data-hx-current]');
		if (!track) return;
		var count = track.children.length;
		function index() { return Math.round(track.scrollLeft / track.clientWidth); }
		function go(i) { i = (i + count) % count; track.scrollTo({ left: i * track.clientWidth }); }
		var prev = g.querySelector('[data-hx-prev]');
		var next = g.querySelector('[data-hx-next]');
		if (prev) prev.addEventListener('click', function () { go(index() - 1); });
		if (next) next.addEventListener('click', function () { go(index() + 1); });
		g.addEventListener('hx-go', function (e) { var i = parseInt(e.detail, 10); if (i >= 0 && i < count) track.scrollTo({ left: i * track.clientWidth, behavior: 'smooth' }); });
		var thumbs = g.querySelectorAll('[data-hx-go]');
		thumbs.forEach(function (b) { b.addEventListener('click', function () { go(parseInt(b.getAttribute('data-hx-go'), 10)); }); });
		function sync() {
			var i = index();
			if (cur) cur.textContent = i + 1;
			thumbs.forEach(function (b, k) { b.classList.toggle('is-active', k === i); });
			var a = thumbs[i];
			var p = a && a.parentNode;
			if (p && p.scrollWidth > p.clientWidth) { var ar = a.getBoundingClientRect(), pr = p.getBoundingClientRect(); p.scrollBy({ left: (ar.left + ar.width / 2) - (pr.left + pr.width / 2), behavior: 'smooth' }); }
		}
		var t;
		track.addEventListener('scroll', function () {
			clearTimeout(t);
			t = setTimeout(sync, 60);
		}, { passive: true });
	});

	/* Order form(s) — a landing page has two. */
	var pps = document.querySelectorAll('[data-hx-pp]');
	document.querySelectorAll('[data-hx-order]').forEach(function (form) {
		var variations = [];
		try { variations = JSON.parse(form.getAttribute('data-variations') || '[]'); } catch (e) {}
		var selects = form.querySelectorAll('[data-hx-attr]');
		var msg = form.querySelector('[data-hx-msg]');
		var btn = form.querySelector('[data-hx-submit]');

		function matchVariation() {
			var chosen = {}, complete = true;
			selects.forEach(function (s) { chosen[s.getAttribute('data-hx-attr')] = s.value; if (!s.value) complete = false; });
			if (!complete) return null;
			for (var i = 0; i < variations.length; i++) {
				var v = variations[i], ok = true;
				for (var k in chosen) { if (v.attributes[k] && v.attributes[k] !== chosen[k]) { ok = false; break; } }
				if (ok) return v;
			}
			return null;
		}

		selects.forEach(function (s) {
			s.addEventListener('change', function () {
				s.closest('.hx-field').classList.remove('is-invalid');
				var v = matchVariation();
				form.elements.variation_id.value = v ? v.id : '';
				if (v && v.html) { pps.forEach(function (pp) { pp.innerHTML = v.html; }); }
			});
		});

		form.querySelectorAll('.hx-field__input').forEach(function (i) {
			i.addEventListener('input', function () { i.closest('.hx-field').classList.remove('is-invalid'); });
		});

		function showMsg(text) { msg.textContent = text; msg.hidden = !text; }

		function validate() {
			var ok = true;
			var rules = {
				name: function (v) { return v.trim().length >= 2; },
				phone: function (v) { return v.replace(/\D/g, '').length >= 9; },
				city: function (v) { return v.trim().length >= 2; },
				address: function (v) { return v.trim().length >= 3; }
			};
			Object.keys(rules).forEach(function (n) {
				var el = form.elements[n];
				if (!el) return; // field hidden in COD Form
				if (!el.required && !el.value.trim()) return; // optional and left empty
				if (!rules[n](el.value)) { el.closest('.hx-field').classList.add('is-invalid'); ok = false; }
			});
			selects.forEach(function (s) { if (!s.value) { s.closest('.hx-field').classList.add('is-invalid'); ok = false; } });
			if (ok && selects.length && !form.elements.variation_id.value) ok = false;
			return ok;
		}

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			showMsg('');
			if (!validate()) {
				showMsg((window.HX && HX.msgInvalid) || 'المرجو ملء جميع الخانات بشكل صحيح');
				var bad = form.querySelector('.is-invalid .hx-field__input');
				if (bad) bad.focus();
				return;
			}
			btn.disabled = true;
			fetch(window.HX.ajax, { method: 'POST', body: new FormData(form), credentials: 'same-origin' })
				.then(function (r) { return r.json(); })
				.then(function (res) {
					if (res && res.success && res.data && res.data.redirect) {
						window.location.href = res.data.redirect;
					} else {
						showMsg((res && res.data && res.data.message) || ((window.HX && HX.msgError) || 'حدث خطأ، المرجو المحاولة مرة أخرى'));
						btn.disabled = false;
					}
				})
				.catch(function () {
					showMsg(((window.HX && HX.msgError) || 'حدث خطأ، المرجو المحاولة مرة أخرى'));
					btn.disabled = false;
				});
		});
	});
})();

/* Variation dropdowns: disable choices with no in-stock variation */
(function () {
	document.querySelectorAll('[data-hx-order]').forEach(function (form) {
		var selects = form.querySelectorAll('select[data-hx-attr]');
		if (!selects.length) return;
		var variations = [];
		try { variations = JSON.parse(form.getAttribute('data-variations') || '[]'); } catch (e) {}
		function refresh() {
			var c = {};
			selects.forEach(function (s) { c[s.getAttribute('data-hx-attr')] = s.value; });
			selects.forEach(function (s) {
				var key = s.getAttribute('data-hx-attr');
				Array.prototype.forEach.call(s.options, function (o) {
					if (!o.value) return;
					var avail = false;
					for (var i = 0; i < variations.length && !avail; i++) {
						var v = variations[i], ok = v.in_stock !== false;
						if (ok && v.attributes[key] && v.attributes[key] !== o.value) ok = false;
						for (var k in c) { if (!ok) break; if (k !== key && c[k] && v.attributes[k] && v.attributes[k] !== c[k]) ok = false; }
						if (ok) avail = true;
					}
					o.disabled = !avail;
					if (!avail && s.value === o.value) { s.value = ''; }
				});
			});
		}
		selects.forEach(function (s) {
			s.addEventListener('change', refresh);
			var real = Array.prototype.filter.call(s.options, function (o) { return o.value; });
			if (real.length === 1) { s.value = real[0].value; s.dispatchEvent(new Event('change', { bubbles: true })); }
		});
		refresh();
	});
})();
/* Variation dropdowns: empty state */
(function () {
	document.querySelectorAll('select.hx-vsel__select').forEach(function (s) {
		function upd() { s.classList.toggle('is-empty', !s.value); }
		s.addEventListener('change', upd);
		upd();
	});
})();
/* Variation floating label */
(function () {
	var sels = document.querySelectorAll('select.hx-vsel__select');
	function upd() { sels.forEach(function (s) { var f = s.closest('.hx-vsel'); if (f) f.classList.toggle('is-blank', !s.value); s.classList.toggle('is-empty', !s.value); }); }
	sels.forEach(function (s) { s.addEventListener('change', function () { setTimeout(upd, 0); }); });
	upd();
})();

/* Variation images: show the chosen variation's photo */
(function () {
	var gal = document.querySelector('[data-hx-gallery]');
	if (!gal) return;
	document.querySelectorAll('[data-hx-order]').forEach(function (form) {
		var vars = [];
		try { vars = JSON.parse(form.getAttribute('data-variations') || '[]'); } catch (e) {}
		if (!vars.some(function (v) { return v.image >= 0; })) return;
		var selects = form.querySelectorAll('select[data-hx-attr]');
		var last = -1;
		function pick() {
			var c = {};
			selects.forEach(function (s) { if (s.value) c[s.getAttribute('data-hx-attr')] = s.value; });
			if (!Object.keys(c).length) return;
			// all variations that fit what is chosen so far
			var img = null;
			for (var i = 0; i < vars.length; i++) {
				var v = vars[i], ok = true;
				for (var k in c) { if (v.attributes[k] && v.attributes[k] !== c[k]) { ok = false; break; } }
				if (!ok) continue;
				if (img === null) img = v.image;
				else if (img !== v.image) { img = null; break; }
			}
			if (img !== null && img >= 0 && img !== last) {
				last = img;
				gal.dispatchEvent(new CustomEvent('hx-go', { detail: img }));
			}
		}
		selects.forEach(function (s) { s.addEventListener('change', function () { setTimeout(pick, 0); }); });
	});
})();
