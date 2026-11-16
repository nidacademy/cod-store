(function () {
	'use strict';
	var form = document.getElementById('hx-filter-form');
	var aside = document.getElementById('hx-filters');
	if (!form || !aside) return;
	var wrap = document.querySelector('.hx-shop [data-hx-more]');
	var grid = wrap.querySelector('.hx-grid');
	var more = wrap.querySelector('.hx-more');
	var empty = wrap.querySelector('[data-hx-empty]');
	var countEl = document.querySelector('[data-hx-count]');
	var overlay = document.querySelector('.hx-filters__overlay');
	var base = {};
	try { base = JSON.parse(wrap.getAttribute('data-hx-more') || '{}'); } catch (e) {}
	var req = 0, timer;

	function collect() {
		var fd = new FormData(form);
		var sort = document.querySelector('select[form="hx-filter-form"]');
		return {
			limit: base.limit || 12,
			orderby: sort ? sort.value : 'menu_order',
			categories: fd.getAll('cat[]'),
			min_price: (fd.get('min_price') || '').trim(),
			max_price: (fd.get('max_price') || '').trim(),
			on_sale: fd.get('on_sale') ? 1 : ''
		};
	}

	function apply() {
		var cfg = collect();
		wrap.setAttribute('data-hx-more', JSON.stringify(cfg));
		var body = window.hxBuildBody(cfg, '');
		body.append('want_total', '1');
		var id = ++req;
		wrap.classList.add('is-filtering');
		fetch(window.HXMORE.ajax, { method: 'POST', body: body, credentials: 'same-origin' })
			.then(function (r) { return r.json(); })
			.then(function (res) {
				if (id !== req || !res || !res.success) return;
				grid.innerHTML = res.data.html;
				more.hidden = !res.data.has_more;
				empty.hidden = !!res.data.html.trim();
				if (countEl && typeof res.data.total !== 'undefined') countEl.textContent = res.data.total;
			})
			.finally(function () { if (id === req) wrap.classList.remove('is-filtering'); });
	}

	form.addEventListener('change', function (e) {
		if (e.target.type === 'number') return;
		apply();
	});
	var sort = document.querySelector('select[form="hx-filter-form"]');
	if (sort) sort.addEventListener('change', apply);
	form.addEventListener('input', function (e) {
		if (e.target.type !== 'number') return;
		clearTimeout(timer);
		timer = setTimeout(apply, 600);
	});
	form.querySelector('[data-hx-filters-reset]').addEventListener('click', function () {
		form.reset();
		form.querySelectorAll('input[type=checkbox]').forEach(function (c) { c.checked = false; });
		if (sort) sort.value = 'menu_order';
		apply();
	});

	function open() {
		aside.classList.add('is-open');
		overlay.hidden = false;
		document.documentElement.classList.add('hx-no-scroll');
	}
	function close() {
		aside.classList.remove('is-open');
		overlay.hidden = true;
		document.documentElement.classList.remove('hx-no-scroll');
	}
	document.querySelectorAll('[data-hx-filters-open]').forEach(function (b) { b.addEventListener('click', open); });
	document.querySelectorAll('[data-hx-filters-close]').forEach(function (b) { b.addEventListener('click', close); });
	document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
})();