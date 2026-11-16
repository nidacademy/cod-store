/* Product price box: countdown to the offer end date and the "you save" amount (follows the chosen pack). */
(function () {
	'use strict';
	var box = document.querySelector('[data-hx-pbox]');
	if (!box) { return; }

	var cd = box.querySelector('[data-hx-cd]');
	if (cd) {
		var end = parseInt(cd.getAttribute('data-hx-cd'), 10) * 1000, timer;
		var u = {};
		['d', 'h', 'm', 's'].forEach(function (k) { u[k] = cd.querySelector('[data-u="' + k + '"]'); });
		var two = function (n) { return (n < 10 ? '0' : '') + n; };
		var tick = function () {
			var s = Math.floor((end - Date.now()) / 1000);
			if (s <= 0) { cd.hidden = true; clearInterval(timer); return; }
			var d = Math.floor(s / 86400); s %= 86400;
			var h = Math.floor(s / 3600); s %= 3600;
			var m = Math.floor(s / 60); s %= 60;
			u.d.hidden = d === 0;
			u.d.firstChild.textContent = two(d);
			u.h.firstChild.textContent = two(h);
			u.m.firstChild.textContent = two(m);
			u.s.firstChild.textContent = two(s);
		};
		tick();
		timer = setInterval(tick, 1000);
	}

	var save = box.querySelector('[data-hx-save]'), pp = box.querySelector('[data-hx-pp]');
	if (save && pp) {
		var upd = function () {
			var n = pp.querySelector('.hx-pp__num'), o = pp.querySelector('.hx-pp__old [data-mad]');
			var diff = n && o ? Math.round((parseFloat(o.getAttribute('data-mad')) - parseFloat(n.getAttribute('data-mad'))) * 100) / 100 : 0;
			if (!(diff > 0.5)) { save.hidden = true; return; }
			save.hidden = false;
			var a = save.querySelector('[data-hx-save-amt]');
			if (a && a.getAttribute('data-mad') !== String(diff)) {
				a.setAttribute('data-mad', String(diff));
				a.textContent = String(diff);
			}
		};
		if ('MutationObserver' in window) { new MutationObserver(upd).observe(pp, { childList: true }); }
		upd();
	}
})();
