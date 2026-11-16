/* Customer reviews: "show more", open the form, send a review. */
(function () {
	'use strict';
	var box = document.querySelector('[data-hx-rv]');
	if (!box) { return; }
	var more = box.querySelector('[data-hx-rv-showmore]');
	if (more) {
		more.addEventListener('click', function () {
			var hidden = box.querySelectorAll('[data-hx-rv-more][hidden]');
			for (var i = 0; i < hidden.length && i < 6; i++) { hidden[i].hidden = false; }
			if (hidden.length <= 6) { more.remove(); }
		});
	}
	var form = box.querySelector('[data-hx-rv-form]'), open = box.querySelector('[data-hx-rv-open]'), msg = box.querySelector('[data-hx-rv-msg]');
	if (!form || !open) { return; }
	open.addEventListener('click', function () {
		form.hidden = !form.hidden;
		if (!form.hidden) { var n = form.querySelector('[name=name]'); if (n) { n.focus(); } }
	});
	function say(t, ok) {
		msg.textContent = t;
		msg.hidden = !t;
		msg.classList.toggle('is-ok', !!ok);
	}
	form.addEventListener('submit', function (e) {
		e.preventDefault();
		var name = form.elements.name.value.trim(), text = form.elements.text.value.trim();
		if (name.length < 2 || text.length < 5) { say('المرجو كتابة الإسم ورأيك في المنتج'); return; }
		var btn = form.querySelector('[type=submit]');
		btn.disabled = true;
		say('');
		fetch((window.HXRV && HXRV.ajax) || '/wp-admin/admin-ajax.php', { method: 'POST', body: new FormData(form), credentials: 'same-origin' })
			.then(function (r) { return r.json(); })
			.then(function (res) {
				if (res && res.success) {
					form.reset();
					say(res.data.message, true);
					Array.prototype.forEach.call(form.querySelectorAll('.hx-rv__in, .hx-rv__pick, [type=submit]'), function (el) { el.hidden = true; });
				} else {
					say((res && res.data && res.data.message) || 'حدث خطأ، المرجو المحاولة مرة أخرى');
					btn.disabled = false;
				}
			})
			.catch(function () { say('حدث خطأ، المرجو المحاولة مرة أخرى'); btn.disabled = false; });
	});
})();
