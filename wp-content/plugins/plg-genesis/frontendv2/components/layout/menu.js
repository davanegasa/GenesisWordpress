/**
 * Constructor del menú del dashboard con filtrado por permisos
 */
import AuthService from '../../services/auth.js';

/**
 * Define la estructura completa del menú con sus permisos requeridos
 */
const menuStructure = [
	{
		id: 'dashboard',
		label: 'Dashboard',
		icon: '📊',
		href: '#/dashboard',
		requires: null, // Siempre visible
	},
	{
		id: 'estudiantes',
		label: 'Estudiantes',
		icon: '👨‍🎓',
		href: '#/estudiantes',
		requires: 'plg_view_students',
		submenu: [
			{
				id: 'estudiantes-gestionar',
				label: 'Gestionar',
				href: '#/estudiantes',
				requires: 'plg_view_students',
			},
			{
				id: 'estudiantes-crear',
				label: 'Crear',
				href: '#/estudiantes/nuevo',
				requires: 'plg_create_students',
			},
		],
	},
	{
		id: 'contactos',
		label: 'Contactos',
		icon: '📇',
		href: '#/contactos',
		requires: 'plg_view_contacts',
		submenu: [
			{
				id: 'contactos-buscar',
				label: 'Buscar Contactos',
				href: '#/contactos',
				requires: 'plg_view_contacts',
			},
			{
				id: 'contactos-crear',
				label: 'Crear Contacto',
				href: '#/contactos/nuevo',
				requires: 'plg_create_contacts',
			},
		],
	},
	{
		id: 'congresos',
		label: 'Congresos',
		icon: '🎪',
		href: '#/congresos',
		requires: 'plg_view_events',
	},
	{
		id: 'programas',
		label: 'Programas',
		icon: '📚',
		href: '#/programas',
		requires: 'plg_view_programs',
		submenu: [
			{
				id: 'programas-listar',
				label: 'Listar Programas',
				href: '#/programas',
				requires: 'plg_view_programs',
			},
			{
				id: 'programas-crear',
				label: 'Crear Programa',
				href: '#/programas/nuevo',
				requires: 'plg_create_programs',
			},
		],
	},
	{
		id: 'cursos',
		label: 'Cursos',
		icon: '📖',
		href: '#/cursos',
		requires: 'plg_view_courses',
		submenu: [
			{
				id: 'cursos-listar',
				label: 'Listar Cursos',
				href: '#/cursos',
				requires: 'plg_view_courses',
			},
			{
				id: 'cursos-crear',
				label: 'Crear Curso',
				href: '#/cursos/nuevo',
				requires: 'plg_create_courses',
			},
		],
	},
	{
		id: 'ajustes',
		label: 'Ajustes',
		icon: '⚙️',
		href: '#/ajustes',
		requires: null, // Siempre visible
		submenu: [
			{
				id: 'ajustes-tema',
				label: 'Tema',
				href: '#/tema',
				requires: 'plg_view_theme',
			},
			{
				id: 'ajustes-usuarios',
				label: 'Usuarios',
				href: '#/usuarios',
				requires: 'plg_view_users',
			},
		{
			id: 'ajustes-docs',
			label: 'API Docs',
			href: '#/docs',
			requires: 'plg_view_swagger',
		},
		{
			id: 'ajustes-logout',
			label: '🚪 Cerrar sesión',
			href: '/wp-login.php?action=logout&redirect_to=' + encodeURIComponent(window.location.origin),
			requires: null, // Siempre visible
		},
	],
},
];

/**
 * Filtra un item del menú según permisos del usuario
 * @param {object} item - Item del menú
 * @returns {object|null} - Item filtrado o null si no tiene permiso
 */
function filterMenuItem(item) {
	// Si no requiere permiso, siempre mostrar
	if (!item.requires) {
		// Filtrar submenú si existe
		if (item.submenu) {
			const filteredSubmenu = item.submenu
				.map(filterMenuItem)
				.filter(Boolean);
			
			// Si no quedan items en el submenú y el padre requiere permiso, ocultar
			if (filteredSubmenu.length === 0 && item.requires) {
				return null;
			}
			
			return { ...item, submenu: filteredSubmenu };
		}
		return item;
	}

	// Verificar permiso
	if (!AuthService.can(item.requires)) {
		return null;
	}

	// Filtrar submenú si existe
	if (item.submenu) {
		const filteredSubmenu = item.submenu
			.map(filterMenuItem)
			.filter(Boolean);
		
		return { ...item, submenu: filteredSubmenu };
	}

	return item;
}

/**
 * Construye el HTML del menú filtrado según permisos
 * @returns {string} - HTML del menú
 */
export function buildMenu() {
	const filteredMenu = menuStructure
		.map(filterMenuItem)
		.filter(Boolean);

	let html = '<div style="font-weight:700;font-size:20px;margin-bottom:12px;">Genesis</div>';
	
	// Agregar selector de oficina para Super Admin
	if (AuthService.canSwitchOffice()) {
		const currentOffice = AuthService.getOffice() || 'BOG';
		html += `
			<div style="margin-bottom:16px; padding:8px; background:rgba(255,255,255,0.1); border-radius:6px;">
			<label style="font-size:12px; opacity:0.8;">Oficina:</label>
			<select id="office-selector" style="width:100%; padding:6px; border-radius:4px; border:none; margin-top:4px;">
				<option value="BOG" ${currentOffice === 'BOG' ? 'selected' : ''}>Bogotá (BOG)</option>
				<option value="BAR" ${currentOffice === 'BAR' ? 'selected' : ''}>Barranquilla (BAR)</option>
				<option value="BUC" ${currentOffice === 'BUC' ? 'selected' : ''}>Bucaramanga (BUC)</option>
				<option value="PER" ${currentOffice === 'PER' ? 'selected' : ''}>Pereira (PER)</option>
				<option value="FDL" ${currentOffice === 'FDL' ? 'selected' : ''}>Floridablanca (FDL)</option>
				<option value="PR" ${currentOffice === 'PR' ? 'selected' : ''}>Puerto Rico (PR)</option>
				<option value="BO" ${currentOffice === 'BO' ? 'selected' : ''}>Bolivia (BO)</option>
			</select>
			</div>
		`;
	}

	filteredMenu.forEach(item => {
		html += `<a href="${item.href}" id="nav-${item.id}" class="${item.id === 'dashboard' ? 'active' : ''}">
			${item.icon ? item.icon + ' ' : ''}${item.label}
		</a>`;

		if (item.submenu && item.submenu.length > 0) {
			item.submenu.forEach(sub => {
				html += `<a href="${sub.href}" class="submenu" data-group="${item.id}">${sub.label}</a>`;
			});
		}
	});

	return html;
}

/**
 * Inicializa el menú en el sidebar
 */
export function initMenu() {
	const sidebar = document.getElementById('sidebar');
	if (!sidebar) return;

	sidebar.innerHTML = buildMenu();

	// Agregar listener para selector de oficina si existe
	const officeSelector = document.getElementById('office-selector');
	if (officeSelector) {
		officeSelector.addEventListener('change', async (e) => {
			const newOffice = e.target.value;
			
			try {
				// Llamar al backend para cambiar la oficina
				const { api: apiClient } = await import('../../api/client.js');
				const response = await apiClient.post('/user-management/switch-office', { office: newOffice });
				
				if (response.success) {
					// Recargar la página para que todos los datos se actualicen
					// Usamos true para forzar recarga desde el servidor (bypass cache)
					window.location.reload(true);
				} else {
					console.error('Error al cambiar de oficina:', response.error);
					alert('Error al cambiar de oficina: ' + (response.error?.message || 'Error desconocido'));
					// Revertir el selector a la oficina anterior
					officeSelector.value = AuthService.getOffice() || 'BOG';
				}
			} catch (error) {
				console.error('Error al cambiar de oficina:', error);
				alert('Error al cambiar de oficina');
				// Revertir el selector a la oficina anterior
				officeSelector.value = AuthService.getOffice() || 'BOG';
			}
		});
	}

	// Configurar acordeones para submenús
	setupAccordions();
}

/**
 * Configura los acordeones para los menús con submenús
 */
function setupAccordions() {
	const menuItems = [
		{ triggerId: 'nav-estudiantes', submenuSelector: '.sidebar a.submenu[data-group="estudiantes"]' },
		{ triggerId: 'nav-contactos', submenuSelector: '.sidebar a.submenu[data-group="contactos"]' },
		{ triggerId: 'nav-congresos', submenuSelector: '.sidebar a.submenu[data-group="congresos"]' },
		{ triggerId: 'nav-programas', submenuSelector: '.sidebar a.submenu[data-group="programas"]' },
		{ triggerId: 'nav-cursos', submenuSelector: '.sidebar a.submenu[data-group="cursos"]' },
		{ triggerId: 'nav-ajustes', submenuSelector: '.sidebar a.submenu[data-group="ajustes"]' },
	];

	menuItems.forEach(({ triggerId, submenuSelector }) => {
		const trigger = document.getElementById(triggerId);
		if (!trigger) return;

		const subs = Array.from(document.querySelectorAll(submenuSelector));
		if (subs.length === 0) return;

		// Cierra todos los submenus excepto el actual
		const closeOthers = () => {
			document.querySelectorAll('.sidebar a.submenu').forEach(s => {
				if (!subs.includes(s)) {
					s.style.display = 'none';
				}
			});
		};

		trigger.addEventListener('click', (e) => {
			e.preventDefault();
			const isAnyClosed = subs.some(s => s.style.display === 'none' || !s.style.display);
			closeOthers();
			
			if (isAnyClosed) {
				subs.forEach(s => s.style.display = 'block');
			} else {
				subs.forEach(s => s.style.display = 'none');
			}
		});
	});
}

/**
 * Actualiza el item activo del menú
 * @param {string} hash - El hash actual (ej: '#/estudiantes')
 */
export function updateActiveMenuItem(hash) {
	// Remover active de todos
	document.querySelectorAll('.sidebar a').forEach(a => {
		a.classList.remove('active');
	});

	// Agregar active al correspondiente
	const cleanHash = hash.split('?')[0]; // Remover query params
	const link = document.querySelector(`.sidebar a[href="${cleanHash}"], .sidebar a[href^="${cleanHash}"]`);
	if (link) {
		link.classList.add('active');
	}
}

