const routes = new Map();
let current = null;

function getContainer(){
	return document.getElementById('plg-content') || document.getElementById('view') || document.body;
}

export function registerRoute(path, loader) {
	routes.set(path, loader);
}

export function startRouter() {
	window.addEventListener('hashchange', handleRoute);
	handleRoute();
}

function parseHash() {
	let h = location.hash || '#/dashboard';
	return h;
}

async function handleRoute() {
	const h = parseHash();
	if (current && current.unmount) try { current.unmount(); } catch {}
	const loader = resolveLoader(h);
	if (!loader) return;
	const container = getContainer();
	container.innerHTML = '<div class="card">Cargando…</div>';
	try {
		current = await loader();
	} catch (e) {
		container.innerHTML = '<div class="card">Error cargando la vista</div>';
		console.error('Router error:', e);
	}
}

function resolveLoader(hash) {
	const container = getContainer();
	if (hash.startsWith('#/dashboard')) return () => import('../pages/dashboard/index.js').then(m => m.mount(container));
	if (hash.startsWith('#/estudiantes/nuevo')) return () => import('../pages/estudiantes/create.js').then(m => m.mount(container));
	if (hash.startsWith('#/estudiante/')) {
        let id = hash.split('/')[2] || '';
        if (id.includes('?')) id = id.split('?')[0];
        id = decodeURIComponent(id);
		return () => import('../pages/estudiantes/detail.js').then(m => m.mount(container, { id }));
	}
    if (hash.startsWith('#/estudiantes')) return () => import('../pages/estudiantes/list.js').then(m => m.mount(container));
    if (hash.startsWith('#/contactos/nuevo')) return () => import('../pages/contactos/create.js').then(m => m.mount(container));
    if (hash.startsWith('#/contacto/')) {
        let id = hash.split('/')[2] || '';
        if (id.includes('?')) id = id.split('?')[0];
        id = decodeURIComponent(id);
        return () => import('../pages/contactos/detail.js').then(m => m.mount(container, { id }));
    }
    if (hash.startsWith('#/contactos')) return () => import('../pages/contactos/list.js').then(m => m.mount(container));
    if (hash.startsWith('#/congresos')) return () => import('../pages/congresos/list.js').then(m => m.mount(container));
    if (hash.startsWith('#/programas/nuevo')) return () => import('../pages/programas/create.js').then(m => m.mount(container));
    if (hash.startsWith('#/programa/')) { let id = hash.split('/')[2] || ''; if (id.includes('?')) id=id.split('?')[0]; id=decodeURIComponent(id); return () => import('../pages/programas/detail.js').then(m=> m.mount(container,{ id })); }
    if (hash.startsWith('#/programas')) return () => import('../pages/programas/list.js').then(m => m.mount(container));
    if (hash.startsWith('#/congreso/') && hash.includes('/asistencia')) { let id = hash.split('/')[2] || ''; if (id.includes('?')) id=id.split('?')[0]; id=decodeURIComponent(id); return () => import('../pages/congresos/attendance.js').then(m=> m.mount(container,{ id })); }
    if (hash.startsWith('#/congreso/')) { let id = hash.split('/')[2] || ''; if (id.includes('?')) id=id.split('?')[0]; id=decodeURIComponent(id); return () => import('../pages/congresos/detail.js').then(m=> m.mount(container,{ id })); }
	if (hash.startsWith('#/tema')) return () => import('../pages/settings/theme.js').then(m => m.mount(container));
	return () => import('../pages/dashboard/index.js').then(m => m.mount(container));
}