/* Header builder drawer under the Customizer preview (Customize > Header). */
(function ($, api) {
	'use strict';
	var D = window.HXHB;
	if (!D || !api) { return; }
	var ROWS = Object.keys(D.rows), ZONES = ['Left', 'Center', 'Right'];
	var layout, dev = 'desktop', $d, $body, $pop = null;

	function esc(s) {
		return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; });
	}
	function copy(o) { return JSON.parse(JSON.stringify(o)); }
	function parse(v) {
		try {
			var o = typeof v === 'string' ? JSON.parse(v) : v;
			if (o && o.desktop && o.mobile) { return o; }
		} catch (e) {}
		return copy(D.def);
	}
	function icon(n) { return '<span class="dashicons dashicons-' + n + '" aria-hidden="true"></span>'; }
	function chip(k) {
		var it = D.items[k];
		if (!it) { return ''; }
		return '<li class="hx-hbd__it" data-k="' + esc(k) + '">' + icon(it.icon) + '<span class="hx-hbd__nm">' + esc(it.label) + '</span>' +
			(it.sec ? '<button type="button" class="hx-hbd__gear" title="' + esc(it.label) + ' settings" aria-label="' + esc(it.label) + ' settings">' + icon('admin-generic') + '</button>' : '') +
			'<button type="button" class="hx-hbd__x" title="Remove" aria-label="Remove ' + esc(it.label) + '">' + icon('no-alt') + '</button></li>';
	}

	function render() {
		var L = layout[dev], used = {}, h = '';
		closePop();
		ROWS.forEach(function (r) {
			h += '<div class="hx-hbd__row" data-row="' + r + '"><div class="hx-hbd__rl"><b>' + esc(D.rows[r]) + '</b>' +
				'<button type="button" class="hx-hbd__rgear" data-sec="hx_hb_row_' + r + '" title="' + esc(D.rows[r]) + ' settings" aria-label="' + esc(D.rows[r]) + ' settings">' + icon('admin-generic') + '</button></div>';
			for (var z = 0; z < 3; z++) {
				h += '<div class="hx-hbd__zw hx-hbd__zw--' + z + '"><ul class="hx-hbd__z" data-row="' + r + '" data-z="' + z + '" data-label="' + ZONES[z] + '">';
				((L[r] && L[r][z]) || []).forEach(function (k) {
					if (D.items[k] && !used[k]) { used[k] = 1; h += chip(k); }
				});
				h += '</ul><button type="button" class="hx-hbd__add" title="Add an item here" aria-label="Add an item to ' + esc(D.rows[r]) + ', ' + ZONES[z] + '">+</button></div>';
			}
			h += '</div>';
		});
		h += '<div class="hx-hbd__pool"><b>Available items</b><ul class="hx-hbd__z hx-hbd__z--pool" data-label="All items are in use">';
		D.order.forEach(function (k) { if (!used[k]) { h += chip(k); } });
		h += '</ul></div>';
		$body.html(h);
		var $z = $body.find('.hx-hbd__z');
		$z.sortable({
			connectWith: $z,
			items: '> li',
			placeholder: 'hx-hbd__ph',
			tolerance: 'pointer',
			cursor: 'move',
			cancel: 'button',
			start: function (e, ui) { ui.placeholder.width(ui.item.outerWidth()); },
			stop: save
		});
		$d.find('.hx-hbd__tab').each(function () {
			var on = $(this).data('dev') === dev;
			$(this).toggleClass('is-on', on).attr('aria-selected', on ? 'true' : 'false');
		});
	}

	function read() {
		var L = {};
		ROWS.forEach(function (r) { L[r] = [[], [], []]; });
		$body.find('.hx-hbd__z[data-row]').each(function () {
			var r = $(this).data('row'), z = +$(this).data('z');
			$(this).children('li[data-k]').each(function () { L[r][z].push(String($(this).data('k'))); });
		});
		return L;
	}
	function save() {
		layout[dev] = read();
		api('hx_hb_layout').set(JSON.stringify(layout));
	}

	/* "+" button: list of the free items */
	function closePop() {
		if ($pop) { $pop.remove(); $pop = null; }
	}
	function openPop($btn) {
		closePop();
		var $ul = $btn.siblings('ul'), free = $body.find('.hx-hbd__z--pool > li');
		var h = '<div class="hx-hbd__pop" role="menu">';
		if (!free.length) {
			h += '<p>All items are already in this layout. Remove one first.</p>';
		}
		free.each(function () {
			var k = String($(this).data('k')), it = D.items[k];
			h += '<button type="button" role="menuitem" data-k="' + esc(k) + '">' + icon(it.icon) + esc(it.label) + '</button>';
		});
		h += '</div>';
		$pop = $(h).appendTo($d);
		var b = $btn[0].getBoundingClientRect(), r = $d[0].getBoundingClientRect(), w = $pop.outerWidth();
		$pop.css({ left: Math.max(8, Math.min(b.right - r.left - w, r.width - w - 8)), bottom: r.bottom - b.top + 6 });
		$pop.on('click', 'button[data-k]', function () {
			var k = String($(this).data('k'));
			$body.find('.hx-hbd__z--pool > li[data-k="' + k + '"]').appendTo($ul);
			closePop();
			save();
		});
	}

	function focusSection(id) {
		var s = id && api.section(id);
		if (s) { s.focus(); }
	}

	/* Show the drawer while the Header panel or one of its sections is open */
	function wanted() {
		var on = false;
		api.panel.each(function (p) { if (p.id === D.panel && p.expanded()) { on = true; } });
		api.section.each(function (s) { if (s.expanded() && D.secs.indexOf(s.id) > -1) { on = true; } });
		return on;
	}
	function offset() {
		var h = $d.hasClass('is-on') ? $d.outerHeight() : 0;
		document.body.style.setProperty('--hx-hbd-h', h + 'px');
		$(document.body).toggleClass('hx-hbd-on', h > 0);
	}
	function sync() {
		setTimeout(function () {
			var on = wanted();
			if (!on) { closePop(); }
			$d.toggleClass('is-on', on);
			offset();
		}, 30);
	}
	function setMin(min) {
		$d.toggleClass('is-min', min);
		$d.find('.hx-hbd__hide').html(min ? icon('arrow-up-alt2') + 'Show' : icon('arrow-down-alt2') + 'Hide').attr('aria-expanded', min ? 'false' : 'true');
		closePop();
		offset();
	}

	api.bind('ready', function () {
		if (!api.has('hx_hb_layout')) { return; }
		layout = parse(api('hx_hb_layout').get());
		dev = api.previewedDevice && api.previewedDevice() === 'desktop' ? 'desktop' : (api.previewedDevice ? 'mobile' : 'desktop');

		$d = $('<div id="hx-hbd" class="hx-hbd" aria-label="Header builder">' +
			'<div class="hx-hbd__bar">' +
			'<div class="hx-hbd__tabs" role="tablist">' +
			'<button type="button" class="hx-hbd__tab" role="tab" data-dev="desktop">' + icon('desktop') + 'Desktop</button>' +
			'<button type="button" class="hx-hbd__tab" role="tab" data-dev="mobile">' + icon('smartphone') + 'Tablet / Mobile</button>' +
			'</div>' +
			'<span class="hx-hbd__hint">Drag items between zones. Empty rows are hidden.</span>' +
			'<button type="button" class="button-link hx-hbd__reset">' + icon('image-rotate') + 'Reset</button>' +
			'<button type="button" class="button-link hx-hbd__hide" aria-expanded="true">' + icon('arrow-down-alt2') + 'Hide</button>' +
			'</div><div class="hx-hbd__body"></div></div>');
		$body = $d.find('.hx-hbd__body');
		$('.wp-full-overlay').first().append($d);
		render();

		$d.on('click', '.hx-hbd__tab', function () {
			dev = $(this).data('dev');
			render();
			if (api.previewedDevice) { api.previewedDevice.set(dev === 'desktop' ? 'desktop' : 'mobile'); }
		});
		$d.on('click', '.hx-hbd__x', function () {
			$(this).closest('li').appendTo($body.find('.hx-hbd__z--pool'));
			save();
		});
		$d.on('click', '.hx-hbd__gear', function () { focusSection(D.items[$(this).closest('li').data('k')].sec); });
		$d.on('click', '.hx-hbd__rgear', function () { focusSection($(this).data('sec')); });
		$d.on('click', '.hx-hbd__add', function (e) {
			e.stopPropagation();
			if ($pop && $pop.data('for') === this) { closePop(); return; }
			openPop($(this));
			$pop.data('for', this);
		});
		$d.on('click', '.hx-hbd__hide', function () { setMin(!$d.hasClass('is-min')); });
		$d.on('click', '.hx-hbd__reset', function () {
			if (!window.confirm('Bring back the original header for desktop and mobile?')) { return; }
			layout = copy(D.def);
			api('hx_hb_layout').set(JSON.stringify(layout));
			render();
		});
		$(document).on('click', function (e) {
			if ($pop && !$(e.target).closest('.hx-hbd__pop, .hx-hbd__add').length) { closePop(); }
		});
		$(document).on('click', '.hx-hb-show', function () { setMin(false); });

		if (api.previewedDevice) {
			api.previewedDevice.bind(function (v) {
				var nd = v === 'desktop' ? 'desktop' : 'mobile';
				if (nd !== dev) { dev = nd; render(); }
			});
		}
		api.panel.each(function (p) { p.expanded.bind(sync); });
		api.section.each(function (s) { s.expanded.bind(sync); });
		$(window).on('resize', offset);
		sync();
	});
}(jQuery, wp.customize));
