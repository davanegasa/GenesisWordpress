const DEFAULT_TIMEOUT_MS = 15000;

function withTimeout(promise, ms = DEFAULT_TIMEOUT_MS) {
	return new Promise((resolve, reject) => {
		const id = setTimeout(() => reject(new Error('timeout')), ms);
		promise.then(v => { clearTimeout(id); resolve(v); }).catch(e => { clearTimeout(id); reject(e); });
	});
}

async function refreshNonceIfNeeded() {
    try {
        const r = await withTimeout(fetch('/wp-json/plg-genesis/v1/auth/nonce', { credentials: 'same-origin' }));
        const ct = r.headers.get('content-type') || '';
        const d = ct.includes('application/json') ? await r.json() : null;
        const nonce = d && d.data && d.data.nonce;
        if (nonce) {
            window.wpApiSettings = window.wpApiSettings || {};
            window.wpApiSettings.nonce = nonce;
            return true;
        }
    } catch (_) {}
    return false;
}

const API_PREFIX = (() => {
    try {
        // Si el dashboard corre bajo una subcarpeta (p.ej. /genesis/), usar ese prefijo para que el navegador envíe cookies (path)
        const path = window.location.pathname || '';
        if (path.includes('/genesis/')) return '/genesis/wp-json/plg-genesis/v1';
    } catch(_) {}
    return '/wp-json/plg-genesis/v1';
})();

async function coreRequest(method, path, { headers = {}, body } = {}) {
    const h = new Headers(headers || {});
    if (window.wpApiSettings && window.wpApiSettings.nonce) h.set('X-WP-Nonce', window.wpApiSettings.nonce);
    const hasJson = body && !(body instanceof FormData);
    if (hasJson && !h.has('Content-Type')) h.set('Content-Type', 'application/json');
    const res = await withTimeout(fetch(`${API_PREFIX}${path}` , {
        method,
        headers: h,
        credentials: 'include',
        body: hasJson ? JSON.stringify(body) : body
    }));
    const contentType = res.headers.get('content-type') || '';
    const data = contentType.includes('application/json') ? await res.json() : await res.text();
    return { res, data };
}

async function request(method, path, opts = {}) {
    let { res, data } = await coreRequest(method, path, opts);
    // Auto-refresh nonce on 403 rest_cookie_invalid_nonce and retry once
    if ((!res.ok || (data && data.success === false)) && data && (data.code === 'rest_cookie_invalid_nonce' || (data.error && data.error.code === 'rest_cookie_invalid_nonce'))) {
        const refreshed = await refreshNonceIfNeeded();
        if (refreshed) {
            ({ res, data } = await coreRequest(method, path, opts));
        }
    }
    if (!res.ok || (data && data.success === false)) {
        const errObj = (data && (data.error || data)) || { message: res.statusText, code: 'http_error' };
        const e = new Error(errObj.message || 'Request failed');
        e.details = errObj; e.status = res.status; e.payload = data;
        
        // Mostrar toast para errores de permisos (403)
        if (res.status === 403 || errObj.code === 'rest_forbidden') {
            import('../components/ui/toast.js').then(({ toast }) => {
                toast.forbidden('⛔ No tienes permisos para realizar esta acción');
            });
        }
        
        throw e;
    }
    return data;
}

export const api = {
	get: (path, opts={}) => request('GET', path, opts),
	post: (path, body, opts={}) => request('POST', path, { ...opts, body }),
	put: (path, body, opts={}) => request('PUT', path, { ...opts, body }),
	delete: (path, opts={}) => request('DELETE', path, opts)
};