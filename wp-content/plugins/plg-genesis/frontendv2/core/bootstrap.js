// Frontend v2 bootstrap: carga tema, monta layout y arranca router
import { loadTheme, applyTheme } from '../core/theme.js';
import { mountLayout } from '../components/layout/Layout.js';
import { startRouter } from '../core/router.js';
import AuthService from '../services/auth.js';
import { initMenu, updateActiveMenuItem } from '../components/layout/menu.js';
import { initHeader } from '../components/layout/header.js';

export async function bootstrap() {
	// 1. Inicializar servicio de autenticación
	await AuthService.init();
	
	// 2. Cargar tema
	try {
		const theme = await loadTheme();
		applyTheme(theme);
	} catch (e) {
		console.warn('Theme load failed', e);
	}
	
	// 3. Construir menú según permisos
	initMenu();
	
	// 3.1 Inicializar header con info de usuario
	initHeader();
	
	// 4. Montar layout
	const appRoot = document.getElementById('view') || document.body;
	mountLayout(appRoot);

	// Hacer que el título/logo lleve a dashboard
	const clickToDashboard = () => { location.hash = '#/dashboard'; };
	const candidates = [
		document.querySelector('#nav-dashboard'),
		document.querySelector('a[href="#/dashboard"]'),
		document.querySelector('header strong'),
		document.querySelector('.site-title a')
	];
	for (const el of candidates) {
		if (el) { el.style.cursor = 'pointer'; el.addEventListener('click', clickToDashboard); break; }
	}
	// 5. Hash por defecto
	if (!location.hash || location.hash === '#/' || location.hash === '#') {
		location.hash = '#/dashboard';
	}

	// 6. Actualizar menú activo cuando cambia el hash
	window.addEventListener('hashchange', () => {
		updateActiveMenuItem(location.hash);
	});
	updateActiveMenuItem(location.hash);

	// 7. Arrancar router
	startRouter();
}

if (typeof window !== 'undefined') {
	window.addEventListener('DOMContentLoaded', () => {
		if (!window.__plg_bootstrapped) {
			window.__plg_bootstrapped = true;
			bootstrap();
		}
	});
}