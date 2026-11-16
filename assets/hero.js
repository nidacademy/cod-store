(function () {
	'use strict';
	document.querySelectorAll('[data-hx-hero]').forEach(function (hero) {
		var track = hero.querySelector('[data-hx-hero-track]');
		if (!track) return;
		var slides = track.children;
		var count = slides.length;
		if (count < 2) return;
		var dots = hero.querySelectorAll('[data-hx-hero-dot]');
		var delay = parseInt(hero.getAttribute('data-hx-hero'), 10) * 1000;
		var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
		var timer = null, paused = false, t;

		function index() { return Math.round(track.scrollLeft / track.clientWidth); }
		function go(i) {
			i = (i + count) % count;
			track.scrollTo({ left: i * track.clientWidth, behavior: reduce ? 'auto' : 'smooth' });
		}
		function sync() {
			var i = index();
			dots.forEach(function (d, k) { d.classList.toggle('is-active', k === i); });
		}
		function stop() { clearInterval(timer); timer = null; }
		function start() {
			stop();
			if (!delay || reduce || paused || document.hidden) return;
			timer = setInterval(function () { go(index() + 1); }, delay);
		}

		hero.querySelector('[data-hx-hero-prev]').addEventListener('click', function () { go(index() - 1); start(); });
		hero.querySelector('[data-hx-hero-next]').addEventListener('click', function () { go(index() + 1); start(); });
		dots.forEach(function (d) {
			d.addEventListener('click', function () { go(parseInt(d.getAttribute('data-hx-hero-dot'), 10)); start(); });
		});
		track.addEventListener('scroll', function () { clearTimeout(t); t = setTimeout(sync, 60); }, { passive: true });
		hero.addEventListener('mouseenter', function () { paused = true; stop(); });
		hero.addEventListener('mouseleave', function () { paused = false; start(); });
		track.addEventListener('touchstart', function () { paused = true; stop(); }, { passive: true });
		track.addEventListener('touchend', function () { paused = false; setTimeout(start, 1500); }, { passive: true });
		document.addEventListener('visibilitychange', start);
		window.addEventListener('resize', function () { go(index()); });
		start();
	});
})();
