/**
 * Página temporal de migración de roles
 * NOTA: Esta página debe eliminarse después de completar la migración
 */
import { api as apiClient } from '../../api/client.js';
import { createTable, createButton } from '../../components/ui/index.js';

export function mount(root) {
	return render(root);
}

async function render(root) {
	root.innerHTML = `
		<div class="card" style="border: 2px solid var(--plg-warning); background: color-mix(in srgb, var(--plg-warning) 5%, var(--plg-cardBg));">
			<div style="display:flex; align-items:center; gap:12px; margin-bottom:16px;">
				<span style="font-size:32px;">⚠️</span>
				<div>
					<h2 style="margin:0; color:var(--plg-warning);">Migración de Roles y Permisos</h2>
					<p style="margin:4px 0 0 0; color:var(--plg-mutedText);">
						Esta página es temporal. Se eliminará después de completar la migración.
					</p>
				</div>
			</div>

			<div style="background:var(--plg-cardBg); padding:16px; border-radius:8px; margin-bottom:20px;">
				<h3 style="margin:0 0 12px 0;">🚨 Acciones Rápidas</h3>
				<div style="display:flex; gap:12px; flex-wrap:wrap;">
					<button id="btn-make-me-admin" class="btn-primary">
						👑 Hacerme Super Admin
					</button>
					<button id="btn-auto-migrate" class="btn-secondary">
						🤖 Migración Automática
					</button>
					<button id="btn-refresh" class="btn-ghost">
						🔄 Recargar Lista
					</button>
				</div>
				<p style="margin:12px 0 0 0; font-size:13px; color:var(--plg-mutedText);">
					<strong>Migración Automática:</strong> Convierte administrator→Super Admin, editor→Office Manager, author/contributor→Staff, subscriber→Viewer
				</p>
			</div>

			<div id="users-table"></div>
		</div>
	`;

	// Cargar usuarios
	await loadUsers();

	// Event listeners
	document.getElementById('btn-make-me-admin')?.addEventListener('click', makeMeAdmin);
	document.getElementById('btn-auto-migrate')?.addEventListener('click', autoMigrate);
	document.getElementById('btn-refresh')?.addEventListener('click', loadUsers);
}

async function loadUsers() {
	const tableContainer = document.getElementById('users-table');
	tableContainer.innerHTML = '<p>Cargando usuarios...</p>';

	try {
		const response = await apiClient.get('/migration/users');
		
		if (!response.success) {
			throw new Error(response.error?.message || 'Error al cargar usuarios');
		}

		const users = response.data;

		// Separar usuarios que necesitan migración
		const needsMigration = users.filter(u => u.needsMigration);
		const alreadyMigrated = users.filter(u => !u.needsMigration);

		let html = '';

		// Tabla de usuarios que necesitan migración
		if (needsMigration.length > 0) {
			html += `
				<div style="margin-bottom:24px;">
					<h3 style="color:var(--plg-warning);">⚠️ Usuarios que necesitan migración (${needsMigration.length})</h3>
					${renderUsersTable(needsMigration, true)}
				</div>
			`;
		}

		// Tabla de usuarios ya migrados
		if (alreadyMigrated.length > 0) {
			html += `
				<div>
					<h3 style="color:var(--plg-success);">✅ Usuarios ya migrados (${alreadyMigrated.length})</h3>
					${renderUsersTable(alreadyMigrated, false)}
				</div>
			`;
		}

		tableContainer.innerHTML = html;

		// Agregar event listeners a botones de migración
		needsMigration.forEach(user => {
			const btn = document.getElementById(`btn-migrate-${user.id}`);
			if (btn) {
				btn.addEventListener('click', () => showMigrateModal(user));
			}
		});

	} catch (error) {
		console.error('Error cargando usuarios:', error);
		tableContainer.innerHTML = `<p style="color:red;">Error: ${error.message}</p>`;
	}
}

function renderUsersTable(users, showMigrationButton) {
	const rows = users.map(user => {
		const rolesText = user.roles.join(', ');
		const officeText = user.office || '<span style="color:var(--plg-danger);">Sin asignar</span>';
		
		return `
			<tr>
				<td>${user.login}</td>
				<td>${user.name || '<em>Sin nombre</em>'}</td>
				<td>${user.email}</td>
				<td>${rolesText}</td>
				<td>${officeText}</td>
				<td>
					${showMigrationButton ? `
						<button id="btn-migrate-${user.id}" class="btn-sm btn-primary">
							🔄 Migrar
						</button>
					` : `
						<span style="color:var(--plg-success);">✓ Migrado</span>
					`}
				</td>
			</tr>
		`;
	}).join('');

	return `
		<table class="table">
			<thead>
				<tr>
					<th>Usuario</th>
					<th>Nombre</th>
					<th>Email</th>
					<th>Roles Actuales</th>
					<th>Oficina</th>
					<th>Acción</th>
				</tr>
			</thead>
			<tbody>
				${rows}
			</tbody>
		</table>
	`;
}

function showMigrateModal(user) {
	// Sugerir rol según el rol actual
	const suggestedRole = suggestNewRole(user.roles[0]);
	
	const modal = document.createElement('div');
	modal.style.cssText = `
		position: fixed;
		top: 0;
		left: 0;
		right: 0;
		bottom: 0;
		background: rgba(0,0,0,0.5);
		display: flex;
		align-items: center;
		justify-content: center;
		z-index: 9999;
	`;

	modal.innerHTML = `
		<div style="background:var(--plg-cardBg); padding:24px; border-radius:12px; max-width:500px; width:90%;">
			<h3 style="margin:0 0 16px 0;">Migrar Usuario: ${user.login}</h3>
			
			<div style="background:color-mix(in srgb, var(--plg-info) 10%, var(--plg-cardBg)); padding:12px; border-radius:6px; margin-bottom:16px;">
				<strong>Rol actual:</strong> ${user.roles.join(', ')}<br>
				<strong>Oficina actual:</strong> ${user.office || 'Sin asignar'}
			</div>

			<div style="margin-bottom:16px;">
				<label style="display:block; margin-bottom:8px; font-weight:500;">Nuevo Rol *</label>
				<select id="modal-new-role" class="form-input">
					<option value="plg_super_admin" ${suggestedRole === 'plg_super_admin' ? 'selected' : ''}>
						👑 Super Admin (acceso total, multi-oficina)
					</option>
					<option value="plg_office_manager" ${suggestedRole === 'plg_office_manager' ? 'selected' : ''}>
						👨‍💼 Office Manager (admin de su oficina)
					</option>
					<option value="plg_office_staff" ${suggestedRole === 'plg_office_staff' ? 'selected' : ''}>
						👷 Office Staff (operativo)
					</option>
					<option value="plg_office_viewer" ${suggestedRole === 'plg_office_viewer' ? 'selected' : ''}>
						👁️ Office Viewer (solo lectura)
					</option>
				</select>
			</div>

			<div style="margin-bottom:20px;">
				<label style="display:block; margin-bottom:8px; font-weight:500;">Oficina *</label>
				<select id="modal-office" class="form-input">
					<option value="BOG" ${user.office === 'BOG' ? 'selected' : ''}>Bogotá</option>
					<option value="MED" ${user.office === 'MED' ? 'selected' : ''}>Medellín</option>
					<option value="CAL" ${user.office === 'CAL' ? 'selected' : ''}>Cali</option>
				</select>
			</div>

			<div style="display:flex; gap:12px; justify-content:flex-end;">
				<button id="btn-cancel" class="btn-secondary">Cancelar</button>
				<button id="btn-confirm" class="btn-primary">✓ Migrar Usuario</button>
			</div>
		</div>
	`;

	document.body.appendChild(modal);

	// Event listeners
	modal.querySelector('#btn-cancel').addEventListener('click', () => {
		modal.remove();
	});

	modal.querySelector('#btn-confirm').addEventListener('click', async () => {
		const newRole = modal.querySelector('#modal-new-role').value;
		const office = modal.querySelector('#modal-office').value;

		try {
			const response = await apiClient.post(`/migration/users/${user.id}`, {
				newRole,
				office
			});

			if (!response.success) {
				throw new Error(response.error?.message || 'Error al migrar usuario');
			}

			alert(`✓ Usuario ${user.login} migrado exitosamente`);
			modal.remove();
			loadUsers();
		} catch (error) {
			alert('Error: ' + error.message);
		}
	});

	// Cerrar al hacer click fuera del modal
	modal.addEventListener('click', (e) => {
		if (e.target === modal) {
			modal.remove();
		}
	});
}

function suggestNewRole(currentRole) {
	const mapping = {
		'administrator': 'plg_super_admin',
		'editor': 'plg_office_manager',
		'author': 'plg_office_staff',
		'contributor': 'plg_office_staff',
		'subscriber': 'plg_office_viewer',
	};
	return mapping[currentRole] || 'plg_office_viewer';
}

async function makeMeAdmin() {
	if (!confirm('¿Quieres convertirte en Super Admin? Tendrás acceso total a todas las oficinas.')) {
		return;
	}

	try {
		const response = await apiClient.post('/migration/make-me-admin', {});
		
		if (!response.success) {
			throw new Error(response.error?.message || 'Error al asignar rol');
		}

		alert('✓ ¡Ahora eres Super Admin! Recarga la página para ver los cambios.');
		setTimeout(() => window.location.reload(), 1500);
	} catch (error) {
		alert('Error: ' + error.message);
	}
}

async function autoMigrate() {
	if (!confirm('¿Migrar TODOS los usuarios automáticamente?\n\n' +
		'Mapeo:\n' +
		'• administrator → Super Admin\n' +
		'• editor → Office Manager\n' +
		'• author/contributor → Office Staff\n' +
		'• subscriber → Office Viewer\n\n' +
		'Los usuarios sin oficina se asignarán a BOG por defecto.')) {
		return;
	}

	try {
		const response = await apiClient.post('/migration/auto', {});
		
		if (!response.success) {
			throw new Error(response.error?.message || 'Error en migración automática');
		}

		const { migrated, skipped, total } = response.data;
		
		alert(`✓ Migración completada!\n\n` +
			`Migrados: ${total} usuarios\n` +
			`Omitidos: ${skipped.length} usuarios\n\n` +
			`Recarga la página para ver los cambios.`);
		
		loadUsers();
	} catch (error) {
		alert('Error: ' + error.message);
	}
}

