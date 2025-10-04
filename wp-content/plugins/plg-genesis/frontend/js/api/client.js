(function(){
	const DEFAULT_TIMEOUT_MS = 15000;

	async function apiFetch(path, options = {}) {
		const controller = new AbortController();
		const timeout = setTimeout(() => controller.abort(), options.timeout || DEFAULT_TIMEOUT_MS);

		const headers = new Headers(options.headers || {});
		// Nonce WP si está disponible en la página (wp_localize_script en el futuro)
		if (window.wpApiSettings && window.wpApiSettings.nonce) {
			headers.set('X-WP-Nonce', window.wpApiSettings.nonce);
		}

		// Si enviamos JSON, asegurar header correcto
		const hasJsonBody = options.body && !(options.body instanceof FormData);
		if (hasJsonBody && !headers.has('Content-Type')) {
			headers.set('Content-Type', 'application/json');
		}

		try {
			const res = await fetch(`/wp-json/plg-genesis/v1${path}`, {
				method: options.method || 'GET',
				headers,
				body: hasJsonBody ? JSON.stringify(options.body) : undefined,
				signal: controller.signal,
				credentials: 'same-origin'
			});
			clearTimeout(timeout);

			const contentType = res.headers.get('content-type') || '';
			const data = contentType.includes('application/json') ? await res.json() : await res.text();

			if (!res.ok || (data && data.success === false)) {
				const error = data && data.error ? data.error : { code: 'http_error', message: res.statusText };
				throw Object.assign(new Error(error.message || 'Request failed'), { status: res.status, details: error });
			}
			return data;
		} catch (err) {
			if (err.name === 'AbortError') {
				throw Object.assign(new Error('Request timeout'), { code: 'timeout' });
			}
			throw err;
		}
	}

	window.PlgGenesisApiClient = {
		get: (path, opts={}) => apiFetch(path, { ...opts, method: 'GET' }),
		post: (path, body, opts={}) => apiFetch(path, { ...opts, method: 'POST', body }),
		put: (path, body, opts={}) => apiFetch(path, { ...opts, method: 'PUT', body }),
		delete: (path, opts={}) => apiFetch(path, { ...opts, method: 'DELETE' })
	};
})();