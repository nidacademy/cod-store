/* Quantity offers on the product page: pick a pack; the order uses its quantity and the price under the title follows it. */
(function () {
	'use strict';
	document.querySelectorAll('[data-hx-order]').forEach(function (form) {
		var cards = form.querySelectorAll('.hx-offer');
		if (!cards.length) { return; }
		var box = form.closest('.hx-product__info') || document;
		function apply() {
			cards.forEach(function (c) {
				var r = c.querySelector('input');
				c.classList.toggle('is-on', r.checked);
				if (!r.checked) { return; }
				form.elements.qty.value = r.getAttribute('data-qty');
				var html = r.getAttribute('data-pp');
				box.querySelectorAll('[data-hx-pp]').forEach(function (pp) { pp.innerHTML = html; });
			});
		}
		form.addEventListener('change', function () { setTimeout(apply, 0); });
		apply();
		window.addEventListener('load', function () { setTimeout(apply, 0); });
	});
})();

/* Offers shown as a dropdown (Customize > COD Form > Layout > Quantity offers). */
(function () {
	'use strict';
	document.querySelectorAll('[data-hx-dd]').forEach(function (dd) {
		var btn = dd.querySelector('.hx-dd__btn'), cur = dd.querySelector('.hx-dd__cur'), list = dd.querySelector('.hx-dd__list');
		if (!btn || !cur || !list) { return; }
		dd.classList.add('is-ready');
		function open(on) {
			dd.classList.toggle('is-open', on);
			btn.setAttribute('aria-expanded', on ? 'true' : 'false');
		}
		function paint() {
			var r = list.querySelector('input:checked');
			if (!r) { return; }
			var card = r.closest('.hx-offer');
			cur.innerHTML = '';
			['.hx-offer__main', '.hx-offer__price'].forEach(function (s) {
				var n = card.querySelector(s);
				if (n) { cur.appendChild(n.cloneNode(true)); }
			});
		}
		btn.addEventListener('click', function (e) {
			e.preventDefault();
			open(!dd.classList.contains('is-open'));
		});
		list.addEventListener('change', paint);
		list.addEventListener('click', function (e) {
			if (e.target.closest('.hx-offer')) { setTimeout(function () { paint(); open(false); }, 0); }
		});
		document.addEventListener('click', function (e) { if (!dd.contains(e.target)) { open(false); } });
		document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && dd.classList.contains('is-open')) { open(false); btn.focus(); } });
		paint();
	});
})();
