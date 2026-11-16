(function () {
	'use strict';
	var bar = document.querySelector('[data-hx-hs]');
	if (!bar) return;
	var drawer = document.getElementById(bar.getAttribute('data-hx-hs'));
	var wrap = document.querySelector('.hx-products[data-hx-home]');
	if (!drawer || !wrap) return;
	var grid = wrap.querySelector('.hx-grid');
	var more = wrap.querySelector('.hx-more');
	var empty = wrap.querySelector('[data-hx-empty]');
	var overlay = drawer.nextElementSibling;
	var sform = bar.querySelector('[data-hx-hs-form]');
	var input = bar.querySelector('.hx-hs__input');
	var clear = bar.querySelector('[data-hx-hs-clear]');
	var badge = bar.querySelector('[data-hx-hs-badge]');
	var fbtn = bar.querySelector('[data-hx-hs-open]');
	var chips = document.querySelector('[data-hx-chips]');
	var fform = drawer.querySelector('[data-hx-hs-filters]');
	var sort = fform.querySelector('select[name=orderby]');
	var base = {};
	try { base = JSON.parse(wrap.getAttribute('data-hx-more') || '{}'); } catch (e) {}
	var defSort = base.orderby || 'menu_order';
	var req = 0, timer, last = '';

	function checkedCats() {
		return Array.prototype.map.call(fform.querySelectorAll('input[name="cat[]"]:checked'), function (c) { return c.value; });
	}

	function collect() {
		var fd = new FormData(fform);
		var cats = checkedCats();
		if (!cats.length && base.category) cats = [base.category];
		return {
			limit: base.limit || 12,
			orderby: sort.value || defSort,
			categories: cats,
			min_price: (fd.get('min_price') || '').trim(),
			max_price: (fd.get('max_price') || '').trim(),
			on_sale: fd.get('on_sale') ? 1 : '',
			search: input.value.trim()
		};
	}

	function syncUi(cfg) {
		var cats = checkedCats();
		var n = cats.length > 1 ? cats.length : 0;
		if (cfg.min_price || cfg.max_price) n++;
		if (cfg.on_sale) n++;
		if (cfg.orderby !== defSort) n++;
		badge.textContent = n;
		badge.hidden = !n;
		fbtn.classList.toggle('is-on', n > 0 || cats.length > 0);
		clear.hidden = !input.value;
		if (!chips) return;
		var active = cats.length === 0 ? '' : (cats.length === 1 ? cats[0] : null);
		chips.querySelectorAll('.hx-chip').forEach(function (c) {
			var on = c.getAttribute('data-cat') === active;
			c.classList.toggle('is-active', on);
			c.setAttribute('aria-pressed', on ? 'true' : 'false');
		});
	}

	function apply() {
		var cfg = collect();
		var key = JSON.stringify(cfg);
		syncUi(cfg);
		if (key === last) return;
		last = key;
		wrap.setAttribute('data-hx-more', key);
		var body = window.hxBuildBody(cfg, '');
		var id = ++req;
		wrap.classList.add('is-filtering');
		fetch(window.HXMORE.ajax, { method: 'POST', body: body, credentials: 'same-origin' })
			.then(function (r) { return r.json(); })
			.then(function (res) {
				if (id !== req || !res || !res.success) return;
				grid.innerHTML = res.data.html;
				if (more) more.hidden = !res.data.has_more;
				if (empty) empty.hidden = !!res.data.html.trim();
			})
			.finally(function () { if (id === req) wrap.classList.remove('is-filtering'); });
	}
	last = JSON.stringify(collect());

	sform.addEventListener('submit', function (e) {
		e.preventDefault();
		clearTimeout(timer);
		apply();
		input.blur();
	});
	input.addEventListener('input', function () {
		clear.hidden = !input.value;
		clearTimeout(timer);
		if (input.value.trim().length === 1) return;
		timer = setTimeout(apply, 400);
	});
	clear.addEventListener('click', function () {
		input.value = '';
		apply();
		input.focus();
	});

	if (chips) {
		chips.addEventListener('click', function (e) {
			var chip = e.target.closest('.hx-chip');
			if (!chip) return;
			var cat = chip.getAttribute('data-cat');
			fform.querySelectorAll('input[name="cat[]"]').forEach(function (c) { c.checked = !!cat && c.value === cat; });
			apply();
			if (chip.scrollIntoView) chip.scrollIntoView({ block: 'nearest', inline: 'center', behavior: 'smooth' });
		});
	}

	fform.addEventListener('change', function (e) {
		if (e.target.type === 'number') return;
		apply();
	});
	fform.addEventListener('input', function (e) {
		if (e.target.type !== 'number') return;
		clearTimeout(timer);
		timer = setTimeout(apply, 600);
	});
	drawer.querySelector('[data-hx-hs-reset]').addEventListener('click', function () {
		fform.reset();
		fform.querySelectorAll('input[type=checkbox]').forEach(function (c) { c.checked = false; });
		sort.value = defSort;
		apply();
	});

	function open() {
		drawer.classList.add('is-open');
		if (overlay) overlay.hidden = false;
		document.documentElement.classList.add('hx-no-scroll');
	}
	function close() {
		drawer.classList.remove('is-open');
		if (overlay) overlay.hidden = true;
		document.documentElement.classList.remove('hx-no-scroll');
	}
	fbtn.addEventListener('click', open);
	document.querySelectorAll('[data-hx-hs-close]').forEach(function (b) { b.addEventListener('click', close); });
	document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });
})();
