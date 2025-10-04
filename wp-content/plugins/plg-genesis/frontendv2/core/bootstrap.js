// Frontend v2 bootstrap: carga tema, monta layout y arranca router
import { loadTheme, applyTheme } from '../core/theme.js';
import { mountLayout } from '../components/layout/Layout.js';
import { startRouter } from '../core/router.js';

export async function bootstrap() {
	try {
		const theme = await loadTheme();
		applyTheme(theme);
	} catch (e) {
		console.warn('Theme load failed', e);
	}
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
	// Hash por defecto
	if (!location.hash || location.hash === '#/' || location.hash === '#') {
		location.hash = '#/dashboard';
	}

	// Acordeón independiente por sección
	function setupAccordion(triggerId, submenuSelector){
		const trigger = document.getElementById(triggerId);
		if (!trigger) return;
		const subs = Array.from(document.querySelectorAll(submenuSelector));
		// Cierra todos los submenus primero
		const closeAll = () => document.querySelectorAll('.sidebar a.submenu').forEach(s=> s.classList.remove('open'));
		trigger.addEventListener('click', (e)=>{
			e.preventDefault();
			const isAnyClosed = subs.some(s=> !s.classList.contains('open'));
			closeAll();
			if (isAnyClosed) subs.forEach(s=> s.classList.add('open'));
		});
	}
	setupAccordion('nav-estudiantes', '.sidebar a.submenu[href^="#/estudiantes"]');
	setupAccordion('nav-contactos', '.sidebar a.submenu[href^="#/contactos"]');

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