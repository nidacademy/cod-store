(function () {
	'use strict';
	var menu = document.getElementById('hx-menu');
	if (!menu) return;
	var overlay = document.querySelector('.hx-menu-overlay');
	var burgers = document.querySelectorAll('[data-hx-menu-open]');
	var last = null;
	function expanded(on) {
		burgers.forEach(function (b) { b.setAttribute('aria-expanded', on ? 'true' : 'false'); });
	}
	function open(e) {
		last = e && e.currentTarget ? e.currentTarget : burgers[0] || null;
		menu.classList.add('is-open');
		menu.setAttribute('aria-hidden', 'false');
		overlay.hidden = false;
		expanded(true);
		document.documentElement.classList.add('hx-no-scroll');
		var first = menu.querySelector('a, button');
		if (first) first.focus();
	}
	function close() {
		menu.classList.remove('is-open');
		menu.setAttribute('aria-hidden', 'true');
		overlay.hidden = true;
		expanded(false);
		document.documentElement.classList.remove('hx-no-scroll');
	}
	burgers.forEach(function (b) { b.addEventListener('click', open); });
	document.querySelectorAll('[data-hx-menu-close]').forEach(function (b) { b.addEventListener('click', close); });
	document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && menu.classList.contains('is-open')) { close(); if (last) last.focus(); } });
})();
