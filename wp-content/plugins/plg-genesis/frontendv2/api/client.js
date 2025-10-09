const DEFAULT_TIMEOUT_MS = 15000;

function withTimeout(promise, ms = DEFAULT_TIMEOUT_MS) {
	return new Promise((resolve, reject) => {
		const id = setTimeout(() => reject(new Error('timeout')), ms);
		promise.then(v => { clearTimeout(id); resolve(v); }).catch(e => { clearTimeout(id); reject(e); });
	});
}

async function request(method, path, { headers = {}, body } = {}) {
	const h = new Headers(headers);
	if (window.wpApiSettings && window.wpApiSettings.nonce) h.set('X-WP-Nonce', window.wpApiSettings.nonce);
	const hasJson = body && !(body instanceof FormData);
	if (hasJson && !h.has('Content-Type')) h.set('Content-Type', 'application/json');
	const res = await withTimeout(fetch(`/wp-json/plg-genesis/v1${path}` , {
		method,
		headers: h,
		credentials: 'same-origin',
		body: hasJson ? JSON.stringify(body) : body
	}));
	const contentType = res.headers.get('content-type') || '';
	const data = contentType.includes('application/json') ? await res.json() : await res.text();
    if (!res.ok || (data && data.success === false)) {
        const err = (data && data.error) || { message: res.statusText, code: 'http_error' };
        const e = new Error(err.message || 'Request failed');
        e.details = err; e.status = res.status; e.payload = data; throw e;
    }
	return data;
}

export const api = {
	get: (path, opts={}) => request('GET', path, opts),
	post: (path, body, opts={}) => request('POST', path, { ...opts, body }),
	put: (path, body, opts={}) => request('PUT', path, { ...opts, body }),
	delete: (path, opts={}) => request('DELETE', path, opts)
};