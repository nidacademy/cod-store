/* Remembers where the visitor came from (ad / social network / search / site) for 30 days,
 * so each order can show its source in COD Manager. Last non-direct visit wins. */
(function () {
	'use strict';
	try {
		var p = new URLSearchParams(location.search), d = {};
		['utm_source', 'utm_medium', 'utm_campaign', 'fbclid', 'ttclid', 'gclid', 'gbraid', 'wbraid', 'ScCid', 'msclkid'].forEach(function (k) {
			var v = p.get(k);
			if (v) { d[k] = v.slice(0, 150); }
		});
		var ref = '';
		try { ref = document.referrer ? new URL(document.referrer).hostname : ''; } catch (e) {}
		if (ref === location.hostname || /translate\.goog$/.test(ref)) { ref = ''; }
		if (!Object.keys(d).length && !ref) { return; } /* nothing new: keep the previous source */
		d.ref = ref.slice(0, 100);
		d.land = location.pathname.slice(0, 150);
		d.t = Math.floor(Date.now() / 1000);
		document.cookie = 'hx_src=' + encodeURIComponent(JSON.stringify(d)) + '; path=/; max-age=' + (30 * 86400) + '; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');
	} catch (err) {}
})();
