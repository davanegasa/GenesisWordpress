/**
 * Vista de listado y gestión de usuarios
 */
import apiClient from '../../api/client.js';
import { createTable, createButton, createModal } from '../../components/ui/index.js';
import AuthService from '../../services/auth.js';

let currentPage = 1;
const limit = 20;

export async function render(root) {
	root.innerHTML = `
		<div class="card">
			<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
				<h2 style="margin:0;">Gestión de Usuarios</h2>
				${AuthService.can('plg_create_users') ? `
					<button id="btn-create-user" class="btn-primary">+ Crear Usuario</button>
				` : ''}
			</div>
			
			<div style="display:flex; gap:12px; margin-bottom:20px;">
				<input 
					type="text" 
					id="search-input" 
					placeholder="Buscar por nombre, email o usuario..." 
					style="flex:1; padding:10px; border:1px solid #ddd; border-radius:6px;"
				/>
				<button id="btn-search" class="btn-secondary">Buscar</button>
			</div>

			<div id="users-table"></div>
			<div id="pagination" style="margin-top:20px; text-align:center;"></div>
		</div>
	`;

	// Cargar usuarios
	await loadUsers();

	// Event listeners
	document.getElementById('btn-search')?.addEventListener('click', () => {
		currentPage = 1;
		loadUsers();
	});

	document.getElementById('search-input')?.addEventListener('keypress', (e) => {
		if (e.key === 'Enter') {
			currentPage = 1;
			loadUsers();
		}
	});

	document.getElementById('btn-create-user')?.addEventListener('click', () => {
		showCreateModal();
	});
}

async function loadUsers() {
	const search = document.getElementById('search-input')?.value || '';
	const tableContainer = document.getElementById('users-table');
	
	tableContainer.innerHTML = '<p>Cargando...</p>';

	try {
		const response = await apiClient.get(`/users?q=${encodeURIComponent(search)}&page=${currentPage}&limit=${limit}`);
		
		if (!response.success) {
			throw new Error(response.error?.message || 'Error al cargar usuarios');
		}

		const { users, pagination } = response.data;

		if (users.length === 0) {
			tableContainer.innerHTML = '<p style="text-align:center; color:#666;">No se encontraron usuarios</p>';
			return;
		}

		// Crear tabla
		const columns = [
			{ key: 'login', label: 'Usuario' },
			{ key: 'name', label: 'Nombre' },
			{ key: 'email', label: 'Email' },
			{ key: 'office', label: 'Oficina' },
			{ key: 'roles', label: 'Rol', render: (roles) => formatRole(roles[0]) },
			{ key: 'actions', label: 'Acciones', render: (_, user) => renderActions(user) },
		];

		const table = createTable(columns, users);
		tableContainer.innerHTML = '';
		tableContainer.appendChild(table);

		// Paginación
		renderPagination(pagination);

	} catch (error) {
		console.error('Error cargando usuarios:', error);
		tableContainer.innerHTML = `<p style="color:red;">Error: ${error.message}</p>`;
	}
}

function formatRole(roleSlug) {
	const roleNames = {
		'plg_super_admin': 'Super Admin',
		'plg_office_manager': 'Administrador de Oficina',
		'plg_office_staff': 'Personal de Oficina',
		'plg_office_viewer': 'Visualizador',
		'administrator': 'Administrator (WP)',
	};
	return roleNames[roleSlug] || roleSlug;
}

function renderActions(user) {
	const canEdit = AuthService.can('plg_edit_users');
	const canDelete = AuthService.can('plg_delete_users');
	const isCurrentUser = user.id === AuthService.getUser()?.id;

	const actions = [];
	
	if (canEdit) {
		actions.push(`<button onclick="window.editUser(${user.id})" class="btn-sm">✏️ Editar</button>`);
	}
	
	if (canDelete && !isCurrentUser) {
		actions.push(`<button onclick="window.deleteUser(${user.id}, '${user.login}')" class="btn-sm btn-danger">🗑️ Eliminar</button>`);
	}

	return actions.join(' ');
}

function renderPagination(pagination) {
	const container = document.getElementById('pagination');
	if (!container) return;

	if (pagination.pages <= 1) {
		container.innerHTML = '';
		return;
	}

	let html = '<div style="display:flex; gap:8px; justify-content:center; align-items:center;">';
	
	// Anterior
	if (currentPage > 1) {
		html += `<button onclick="window.goToPage(${currentPage - 1})" class="btn-sm">← Anterior</button>`;
	}

	// Páginas
	html += `<span style="padding:0 16px;">Página ${currentPage} de ${pagination.pages} (${pagination.total} usuarios)</span>`;

	// Siguiente
	if (currentPage < pagination.pages) {
		html += `<button onclick="window.goToPage(${currentPage + 1})" class="btn-sm">Siguiente →</button>`;
	}

	html += '</div>';
	container.innerHTML = html;
}

// Funciones globales para acciones
window.goToPage = (page) => {
	currentPage = page;
	loadUsers();
};

window.editUser = async (userId) => {
	try {
		const response = await apiClient.get(`/users/${userId}`);
		if (!response.success) {
			throw new Error(response.error?.message || 'Error al cargar usuario');
		}
		showEditModal(response.data);
	} catch (error) {
		alert('Error: ' + error.message);
	}
};

window.deleteUser = async (userId, username) => {
	if (!confirm(`¿Estás seguro de eliminar al usuario "${username}"?`)) {
		return;
	}

	try {
		const response = await apiClient.delete(`/users/${userId}`);
		if (!response.success) {
			throw new Error(response.error?.message || 'Error al eliminar usuario');
		}
		alert('Usuario eliminado exitosamente');
		loadUsers();
	} catch (error) {
		alert('Error: ' + error.message);
	}
};

async function showCreateModal() {
	// Obtener roles asignables
	const rolesResponse = await apiClient.get('/users/roles/assignable');
	const roles = rolesResponse.success ? rolesResponse.data : {};

	const modal = createModal({
		title: 'Crear Nuevo Usuario',
		body: `
			<div style="display:flex; flex-direction:column; gap:16px;">
				<div>
					<label style="display:block; margin-bottom:4px; font-weight:500;">Usuario *</label>
					<input type="text" id="modal-username" class="form-input" placeholder="usuario123" required />
				</div>
				<div>
					<label style="display:block; margin-bottom:4px; font-weight:500;">Nombre Completo</label>
					<input type="text" id="modal-name" class="form-input" placeholder="Juan Pérez" />
				</div>
				<div>
					<label style="display:block; margin-bottom:4px; font-weight:500;">Email *</label>
					<input type="email" id="modal-email" class="form-input" placeholder="usuario@ejemplo.com" required />
				</div>
				<div>
					<label style="display:block; margin-bottom:4px; font-weight:500;">Contraseña *</label>
					<input type="password" id="modal-password" class="form-input" required />
				</div>
				<div>
					<label style="display:block; margin-bottom:4px; font-weight:500;">Rol *</label>
					<select id="modal-role" class="form-input" required>
						<option value="">Seleccionar rol...</option>
						${Object.entries(roles).map(([slug, name]) => `<option value="${slug}">${name}</option>`).join('')}
					</select>
				</div>
				${AuthService.isSuperAdmin() ? `
					<div>
						<label style="display:block; margin-bottom:4px; font-weight:500;">Oficina *</label>
						<select id="modal-office" class="form-input" required>
							<option value="BOG">Bogotá</option>
							<option value="MED">Medellín</option>
							<option value="CAL">Cali</option>
						</select>
					</div>
				` : ''}
			</div>
		`,
		footer: `
			<button id="btn-cancel-modal" class="btn-secondary">Cancelar</button>
			<button id="btn-save-user" class="btn-primary">Crear Usuario</button>
		`,
	});

	document.body.appendChild(modal);

	// Event listeners
	document.getElementById('btn-cancel-modal').addEventListener('click', () => {
		modal.remove();
	});

	document.getElementById('btn-save-user').addEventListener('click', async () => {
		const username = document.getElementById('modal-username').value.trim();
		const name = document.getElementById('modal-name').value.trim();
		const email = document.getElementById('modal-email').value.trim();
		const password = document.getElementById('modal-password').value;
		const role = document.getElementById('modal-role').value;
		const office = document.getElementById('modal-office')?.value;

		if (!username || !email || !password || !role) {
			alert('Por favor completa todos los campos requeridos');
			return;
		}

		try {
			const payload = { username, email, password, role, name };
			if (office) payload.office = office;

			const response = await apiClient.post('/users', payload);
			if (!response.success) {
				throw new Error(response.error?.message || 'Error al crear usuario');
			}

			alert('Usuario creado exitosamente');
			modal.remove();
			loadUsers();
		} catch (error) {
			alert('Error: ' + error.message);
		}
	});
}

async function showEditModal(user) {
	// Obtener roles asignables
	const rolesResponse = await apiClient.get('/users/roles/assignable');
	const roles = rolesResponse.success ? rolesResponse.data : {};

	const modal = createModal({
		title: `Editar Usuario: ${user.login}`,
		body: `
			<div style="display:flex; flex-direction:column; gap:16px;">
				<div>
					<label style="display:block; margin-bottom:4px; font-weight:500;">Nombre Completo</label>
					<input type="text" id="modal-name" class="form-input" value="${user.name || ''}" />
				</div>
				<div>
					<label style="display:block; margin-bottom:4px; font-weight:500;">Email</label>
					<input type="email" id="modal-email" class="form-input" value="${user.email || ''}" />
				</div>
				<div>
					<label style="display:block; margin-bottom:4px; font-weight:500;">Nueva Contraseña</label>
					<input type="password" id="modal-password" class="form-input" placeholder="Dejar en blanco para no cambiar" />
				</div>
				<div>
					<label style="display:block; margin-bottom:4px; font-weight:500;">Rol</label>
					<select id="modal-role" class="form-input">
						${Object.entries(roles).map(([slug, name]) => 
							`<option value="${slug}" ${user.roles.includes(slug) ? 'selected' : ''}>${name}</option>`
						).join('')}
					</select>
				</div>
				${AuthService.isSuperAdmin() ? `
					<div>
						<label style="display:block; margin-bottom:4px; font-weight:500;">Oficina</label>
						<select id="modal-office" class="form-input">
							<option value="BOG" ${user.office === 'BOG' ? 'selected' : ''}>Bogotá</option>
							<option value="MED" ${user.office === 'MED' ? 'selected' : ''}>Medellín</option>
							<option value="CAL" ${user.office === 'CAL' ? 'selected' : ''}>Cali</option>
						</select>
					</div>
				` : ''}
			</div>
		`,
		footer: `
			<button id="btn-cancel-modal" class="btn-secondary">Cancelar</button>
			<button id="btn-save-user" class="btn-primary">Guardar Cambios</button>
		`,
	});

	document.body.appendChild(modal);

	// Event listeners
	document.getElementById('btn-cancel-modal').addEventListener('click', () => {
		modal.remove();
	});

	document.getElementById('btn-save-user').addEventListener('click', async () => {
		const name = document.getElementById('modal-name').value.trim();
		const email = document.getElementById('modal-email').value.trim();
		const password = document.getElementById('modal-password').value;
		const role = document.getElementById('modal-role').value;
		const office = document.getElementById('modal-office')?.value;

		try {
			const payload = { name, email, role };
			if (password) payload.password = password;
			if (office) payload.office = office;

			const response = await apiClient.put(`/users/${user.id}`, payload);
			if (!response.success) {
				throw new Error(response.error?.message || 'Error al actualizar usuario');
			}

			alert('Usuario actualizado exitosamente');
			modal.remove();
			loadUsers();
		} catch (error) {
			alert('Error: ' + error.message);
		}
	});
}

