/* WhatsApp button in the order form: builds the message from the template set in
 * Customize > Product page > Order form & button (Arabic pages / other languages). */
(function () {
	'use strict';
	var HELLO = {
		ar: 'السلام عليكم، بغيت نطلب:', en: 'Hello, I would like to order:', fr: 'Bonjour, je souhaite commander :',
		es: 'Hola, me gustaría pedir:', de: 'Hallo, ich möchte bestellen:', it: 'Ciao, vorrei ordinare:', pt: 'Olá, gostaria de encomendar:',
		nl: 'Hallo, ik wil graag bestellen:', ru: 'Здравствуйте, я хочу заказать:', zh: '您好，我想订购：', tr: 'Merhaba, sipariş vermek istiyorum:'
	};
	var DEF_AR = 'السلام عليكم، بغيت نطلب:\n{product}\n{offer}\n{options}\n{link}';
	var DEF_OTHER = '{greeting}\n{product}\n{offer}\n{options}\n{link}';
	function clean(t) { return String(t || '').replace(/\s+/g, ' ').trim(); }

	function build(a) {
		var form = a.closest('form');
		var r = document.documentElement;
		var lang = /translated-(ltr|rtl)/.test(r.className) ? String(r.getAttribute('lang') || 'ar').toLowerCase().slice(0, 2) : 'ar';
		var title = document.querySelector('.hx-product__title');
		var v = {
			greeting: HELLO[lang] || HELLO.en,
			product: (lang !== 'ar' && title && clean(title.textContent)) || a.getAttribute('data-name'),
			offer: '',
			options: '',
			price: '',
			link: a.getAttribute('data-url')
		};
		var now = document.querySelector('.hx-pp__now');
		if (now) { v.price = clean(now.textContent); }
		if (form) {
			var off = form.querySelector('.hx-offer input:checked');
			if (off) {
				var card = off.closest('.hx-offer'), l = card.querySelector('.hx-offer__label'), n = card.querySelector('.hx-offer__now');
				if (n) { v.price = clean(n.textContent); }
				v.offer = (l ? clean(l.textContent) : '') + (n ? ' (' + v.price + ')' : '');
			}
			var opts = [];
			form.querySelectorAll('[data-hx-attr]').forEach(function (s) {
				if (!s.value) { return; }
				var lab = form.querySelector('label[for="' + s.id + '"]');
				opts.push((lab ? clean(lab.textContent) + ': ' : '') + clean(s.options[s.selectedIndex].text));
			});
			v.options = opts.join('\n');
		}
		var tpl = lang === 'ar' ? (a.getAttribute('data-tpl-ar') || DEF_AR) : (a.getAttribute('data-tpl') || DEF_OTHER);
		var out = [];
		tpl.replace(/\r/g, '').split('\n').forEach(function (line) {
			var had = /\{(greeting|product|offer|options|price|link)\}/.test(line);
			var res = line.replace(/\{(greeting|product|offer|options|price|link)\}/g, function (m, k) { return v[k] || ''; });
			if (had && !res.trim()) { return; } /* a line whose fields are all empty is dropped */
			out.push(res);
		});
		return out.join('\n').replace(/\n{3,}/g, '\n\n').trim();
	}

	document.addEventListener('click', function (e) {
		var a = e.target.closest && e.target.closest('[data-hx-wa]');
		if (!a) { return; }
		var num = a.getAttribute('data-hx-wa'), txt = encodeURIComponent(build(a));
		/* Phones: open the WhatsApp app directly (works with WhatsApp and WhatsApp Business).
		   If no app opens within 1.6 s, fall back to the WhatsApp web link. */
		if (/Android|iPhone|iPad|iPod/i.test(navigator.userAgent)) {
			e.preventDefault();
			var fallback = setTimeout(function () {
				if (!document.hidden) { window.location.href = 'https://api.whatsapp.com/send?phone=' + num + '&text=' + txt; }
			}, 1600);
			var seen = function () {
				if (document.hidden) { clearTimeout(fallback); document.removeEventListener('visibilitychange', seen); }
			};
			document.addEventListener('visibilitychange', seen);
			window.location.href = 'whatsapp://send?phone=' + num + '&text=' + txt;
			return;
		}
		a.href = 'https://wa.me/' + num + '?text=' + txt;
	});
})();
