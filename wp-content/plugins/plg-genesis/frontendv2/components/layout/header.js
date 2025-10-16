/**
 * Header component - Muestra información del usuario y toggle de dashboard
 */
import AuthService from '../../services/auth.js';
import { showToast } from '../ui/toast.js';

/**
 * Obtiene el nombre del rol formateado para mostrar
 * @param {string[]} roles - Array de roles del usuario
 * @returns {string}
 */
function getRoleDisplayName(roles) {
	if (!roles || roles.length === 0) return 'Usuario';

	// Mapeo de roles a nombres legibles
	const roleMap = {
		'administrator': 'Administrador',
		'plg_super_admin': 'Super Admin',
		'plg_office_manager': 'Office Manager',
		'plg_staff': 'Staff',
	};

	// Buscar el rol más alto en prioridad
	const priority = ['administrator', 'plg_super_admin', 'plg_office_manager', 'plg_staff'];
	
	for (const role of priority) {
		if (roles.includes(role)) {
			return roleMap[role] || role;
		}
	}

	// Si no hay ninguno conocido, retornar el primero formateado
	const firstRole = roles[0];
	return roleMap[firstRole] || firstRole.replace('plg_', '').replace(/_/g, ' ');
}

/**
 * Verifica si el usuario puede ver el toggle de dashboard
 * @returns {boolean}
 */
function canSeeDashboardToggle() {
	return AuthService.hasRole('administrator') || AuthService.hasRole('plg_super_admin');
}

/**
 * Obtiene las iniciales del nombre del usuario
 * @param {string} name - Nombre completo del usuario
 * @returns {string}
 */
function getUserInitials(name) {
	if (!name) return 'U';
	
	const parts = name.trim().split(' ');
	if (parts.length === 1) {
		return parts[0].substring(0, 2).toUpperCase();
	}
	
	return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
}

/**
 * Construye el HTML del header
 * @returns {string}
 */
export function buildHeader() {
	const user = AuthService.getUser();
	
	if (!user) {
		return '<div class="top-header"></div>';
	}

	const initials = getUserInitials(user.name);
	const displayRole = getRoleDisplayName(user.roles);
	const showToggle = canSeeDashboardToggle();
	
	// Verificar si estamos en dashboard v2 (siempre true en este contexto)
	const isV2 = true;

	let html = `
		<div class="user-info">
			<div class="user-avatar" title="${user.name}">${initials}</div>
			<div class="user-details">
				<div class="user-name">${user.name}</div>
				<div class="user-role">${displayRole}</div>
			</div>
		</div>
	`;

	// Agregar toggle solo para admin/super admin
	if (showToggle) {
		html += `
			<div class="dashboard-toggle">
				<label for="dashboard-version-toggle">Dashboard V1 / V2</label>
				<label class="toggle-switch">
					<input type="checkbox" id="dashboard-version-toggle" ${isV2 ? 'checked' : ''}>
					<span class="toggle-slider"></span>
				</label>
			</div>
		`;
	}

	return html;
}

/**
 * Maneja el cambio de dashboard
 */
function handleDashboardToggle(event) {
	const isChecked = event.target.checked;
	
	if (isChecked) {
		// Ya estamos en V2, no hacer nada
		showToast('Ya estás en Dashboard V2', 'info');
	} else {
		// Cambiar a V1
		const baseUrl = window.location.origin;
		const pathPrefix = window.location.pathname.includes('/genesis/') ? '/genesis' : '';
		
		// Redirigir al dashboard v1 (página con shortcode)
		showToast('Cambiando a Dashboard V1...', 'info');
		setTimeout(() => {
			window.location.href = `${baseUrl}${pathPrefix}/dashboard`;
		}, 500);
	}
}

/**
 * Inicializa el header
 */
export function initHeader() {
	const headerElement = document.getElementById('top-header');
	if (!headerElement) return;

	// Construir y renderizar el header
	headerElement.innerHTML = buildHeader();

	// Agregar listener para el toggle si existe
	const toggleInput = document.getElementById('dashboard-version-toggle');
	if (toggleInput) {
		toggleInput.addEventListener('change', handleDashboardToggle);
	}
}

