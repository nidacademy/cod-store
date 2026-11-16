(function () {
	'use strict';
	window.hxBuildBody = function (cfg, exclude) {
		var body = new FormData();
		body.append('action', 'hx_more');
		Object.keys(cfg).forEach(function (k) {
			var v = cfg[k];
			if (Array.isArray(v)) v = v.join(',');
			if (v !== null && v !== undefined && v !== '') body.append(k, v);
		});
		if (exclude) body.append('exclude', exclude);
		return body;
	};
	document.addEventListener('click', function (e) {
		var btn = e.target.closest('[data-hx-more-btn]');
		if (!btn || btn.disabled) return;
		var wrap = btn.closest('[data-hx-more]');
		var grid = wrap && wrap.querySelector('.hx-grid');
		if (!grid) return;
		var cfg = {};
		try { cfg = JSON.parse(wrap.getAttribute('data-hx-more') || '{}'); } catch (err) {}
		var ids = Array.prototype.map.call(grid.querySelectorAll('[data-pid]'), function (li) { return li.getAttribute('data-pid'); });
		btn.disabled = true;
		btn.classList.add('is-loading');
		fetch(window.HXMORE.ajax, { method: 'POST', body: window.hxBuildBody(cfg, ids.join(',')), credentials: 'same-origin' })
			.then(function (r) { return r.json(); })
			.then(function (res) {
				if (res && res.success) {
					grid.insertAdjacentHTML('beforeend', res.data.html);
					if (!res.data.has_more) btn.parentNode.hidden = true;
				}
			})
			.finally(function () {
				btn.disabled = false;
				btn.classList.remove('is-loading');
			});
	});
})();