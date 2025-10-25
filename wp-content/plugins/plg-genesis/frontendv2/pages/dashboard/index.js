import { api } from '../../api/client.js';
import AuthService from '../../services/auth.js';

export async function mount(container) {
	// Detectar si es contact_viewer
	if (AuthService.isContactViewer()) {
		await mountContactDashboard(container);
	} else {
		await mountAdminDashboard(container);
	}
}

/**
 * Dashboard para administradores (código original)
 */
async function mountAdminDashboard(container) {
	container.innerHTML = `
		<div class="hero">
			<h1>Bienvenido a Genesis</h1>
			<p>Sistema de gestión para la escuela Bíblica Emmaus</p>
		</div>
		<div class="kpi-grid">
			<div class="kpi-card"><div class="kpi-label">Estudiantes activos</div><div id="k1" class="kpi-value">-</div></div>
			<div class="kpi-card"><div class="kpi-label">Cursos este mes</div><div id="k2" class="kpi-value">-</div></div>
			<div class="kpi-card"><div class="kpi-label">Cursos completados</div><div id="k3" class="kpi-value">-</div></div>
			<div class="kpi-card"><div class="kpi-label">Contactos registrados</div><div id="k4" class="kpi-value">-</div></div>
		</div>
		<div class="card u-mt-16">
			<div class="card-title">Actividad reciente</div>
			<ul id="activity" class="activity-list"></ul>
		</div>
		<div class="card u-mt-16" style="border-left: 4px solid #ff9800;">
			<div style="display: flex; align-items: center; gap: 12px; margin-bottom: 12px;">
				<span style="font-size: 1.5rem;">🔥</span>
				<h3 style="margin: 0; color: #ff9800;">Próximos a Graduarse</h3>
			</div>
			<p style="color: #666; margin-bottom: 10px;">
				Para ver los estudiantes próximos a completar programas o niveles, 
				ve al detalle de cada contacto y busca la pestaña <strong>"🔥 Por Completar"</strong>.
			</p>
			<a href="#/contactos" class="btn btn-primary" style="display: inline-block;">
				📋 Ver Contactos
			</a>
		</div>
	`;
	try {
		const res = await api.get('/estadisticas');
		const d = res && res.data ? res.data : {};
		document.getElementById('k1').textContent = d.estudiantesActivos ?? '-';
		document.getElementById('k2').textContent = d.cursosMes ?? '-';
		document.getElementById('k3').textContent = d.cursosCompletados ?? '-';
		document.getElementById('k4').textContent = d.contactosActivos ?? '-';

		const list = document.getElementById('activity');
		list.innerHTML = '';
		(d.actividades || []).forEach(a => {
			const li = document.createElement('li');
			li.className = 'activity-item';
			li.innerHTML = `
				<span class="activity-type">${a.tipo || ''}</span>
				<span class="activity-text">${a.texto || ''}</span>
				<span class="activity-time">${a.tiempo || ''}</span>
			`;
			list.appendChild(li);
		});
	} catch (e) {
		const list = document.getElementById('activity');
		list.innerHTML = '<li class="activity-item"><span class="activity-text">Error cargando KPIs</span></li>';
	}
}

/**
 * Dashboard para contactos (NUEVO)
 */
async function mountContactDashboard(container) {
	const contactId = AuthService.getContactId();
	const userName = AuthService.getUser().name;
	
	if (!contactId) {
		container.innerHTML = `
			<div class="card" style="max-width:600px;margin:40px auto;text-align:center;">
				<div style="font-size:48px;margin-bottom:16px;">⚠️</div>
				<h2>Configuración Incompleta</h2>
				<p style="margin:16px 0;color:var(--plg-mutedText);">
					Tu usuario no está vinculado a ningún contacto. 
				</p>
				<p style="margin:16px 0;color:var(--plg-mutedText);">
					Por favor, contacta al administrador para completar la configuración de tu cuenta.
				</p>
				<div style="margin-top:24px;padding:16px;background:var(--plg-surface);border-radius:8px;text-align:left;">
					<p><strong>Información técnica:</strong></p>
					<ul style="margin:8px 0;padding-left:24px;color:var(--plg-mutedText);font-size:14px;">
						<li>Usuario: ${escapeHtml(userName || '')}</li>
						<li>Estado: Sin contacto asociado</li>
					</ul>
				</div>
				<div style="margin-top:20px;">
					<button id="btn-refresh-user" class="btn btn-primary">
						🔄 Refrescar Datos
					</button>
					<p style="margin-top:8px;font-size:14px;color:var(--plg-mutedText);">
						Si el administrador ya configuró tu cuenta, haz clic aquí para actualizar.
					</p>
				</div>
			</div>
		`;
		
		// Agregar evento al botón de refrescar
		const btnRefresh = document.getElementById('btn-refresh-user');
		if (btnRefresh) {
			btnRefresh.addEventListener('click', async () => {
				btnRefresh.disabled = true;
				btnRefresh.textContent = '⏳ Actualizando...';
				
				try {
					// Llamar al endpoint /me para obtener datos frescos
					const response = await api.get('/me');
					
					if (response.success && response.data) {
						// Actualizar AuthService con los nuevos datos
						AuthService.userInfo = response.data;
						
						// Recargar la página para aplicar cambios
						window.location.reload();
					} else {
						alert('No se pudo actualizar. Por favor, cierra sesión e inicia nuevamente.');
						btnRefresh.disabled = false;
						btnRefresh.textContent = '🔄 Refrescar Datos';
					}
				} catch (error) {
					console.error('Error refrescando usuario:', error);
					alert('Error al actualizar. Por favor, cierra sesión e inicia nuevamente.');
					btnRefresh.disabled = false;
					btnRefresh.textContent = '🔄 Refrescar Datos';
				}
			});
		}
		
		return;
	}
	
	container.innerHTML = `
		<div class="hero">
			<h1>Bienvenido ${escapeHtml(userName || '')}</h1>
			<p>Portal de estudiantes - Emmaus Digital</p>
		</div>
		<div class="kpi-grid">
			<div class="kpi-card">
				<div class="kpi-label">Mis estudiantes</div>
				<div id="k1" class="kpi-value">-</div>
			</div>
			<div class="kpi-card">
				<div class="kpi-label">Cursos completados</div>
				<div id="k2" class="kpi-value">-</div>
			</div>
			<div class="kpi-card">
				<div class="kpi-label">Diplomas obtenidos</div>
				<div id="k3" class="kpi-value">-</div>
			</div>
			<div class="kpi-card">
				<div class="kpi-label">Progreso promedio</div>
				<div id="k4" class="kpi-value">-</div>
			</div>
		</div>
		<div id="students-list" class="u-mt-16"></div>
	`;
	
	try {
		// Obtener el código del contacto usando el ID
		const contactRes = await api.get(`/contactos/id/${contactId}`);
		if (!contactRes.success) {
			throw new Error('Error obteniendo datos del contacto');
		}
		
		const contactCode = contactRes.data.code || contactRes.data.codigo;
		
		if (!contactCode) {
			throw new Error('No se pudo obtener el código del contacto');
		}
		
		// Llamar a academic-history con el código (reutilizar endpoint existente)
		const res = await api.get(`/contactos/${contactCode}/academic-history`);
		const data = res.data;
		
		// Calcular KPIs
		// Los estudiantes pueden venir en "estudiantes" o "inherited_students"
		const estudiantes = data.estudiantes || data.inherited_students || [];
		
		// Calcular cursos completados (cada estudiante tiene un array de cursos en academic-history)
		const totalCursosCompletados = estudiantes.reduce((sum, est) => {
			// Contar cursos con promedio >= 70%
			const total_cursos = est.total_cursos || 0;
			const promedio = est.promedio_porcentaje || 0;
			return sum + (promedio >= 70 ? total_cursos : 0);
		}, 0);
		
		const diplomasCount = data.diplomas?.entregados?.length || 0;
		
		// Progreso promedio de todos los estudiantes
		const promedioProgreso = estudiantes.length > 0
			? Math.round(estudiantes.reduce((sum, est) => sum + (est.promedio_porcentaje || 0), 0) / estudiantes.length)
			: 0;
		
		document.getElementById('k1').textContent = estudiantes.length;
		document.getElementById('k2').textContent = totalCursosCompletados;
		document.getElementById('k3').textContent = diplomasCount;
		document.getElementById('k4').textContent = promedioProgreso + '%';
		
		// Renderizar lista de estudiantes
		renderStudentsList(estudiantes, document.getElementById('students-list'));
	} catch (e) {
		console.error('Error cargando datos:', e);
		document.getElementById('students-list').innerHTML = '<div class="card"><p>Error cargando datos</p></div>';
	}
}

/**
 * Renderiza la lista de estudiantes para el dashboard de contactos
 */
function renderStudentsList(estudiantes, container) {
	if (!estudiantes || estudiantes.length === 0) {
		container.innerHTML = '<div class="card"><p>No hay estudiantes asociados</p></div>';
		return;
	}
	
	let html = `
		<div class="card">
			<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
				<h3 style="margin:0;">Mis Estudiantes</h3>
				<div style="position:relative;width:300px;">
					<input 
						type="text" 
						id="search-students" 
						placeholder="🔍 Buscar por nombre o código..." 
						style="width:100%;padding:10px 12px;border:2px solid #e0e0e0;border-radius:8px;font-size:14px;transition:border-color 0.2s;"
						onfocus="this.style.borderColor='var(--plg-primary)'"
						onblur="this.style.borderColor='#e0e0e0'"
					/>
				</div>
			</div>
			<div style="overflow-x:auto;">
				<table style="width:100%;border-collapse:collapse;">
					<thead>
						<tr style="background:var(--plg-primary);color:white;">
							<th style="padding:12px;text-align:left;font-weight:600;">Código</th>
							<th style="padding:12px;text-align:left;font-weight:600;">Documento</th>
							<th style="padding:12px;text-align:left;font-weight:600;">Nombre</th>
							<th style="padding:12px;text-align:left;font-weight:600;min-width:200px;">Progreso</th>
							<th style="padding:12px;text-align:center;font-weight:600;width:120px;">Acción</th>
						</tr>
					</thead>
					<tbody id="students-table-body">
	`;
	
	estudiantes.forEach((est, index) => {
		// Los datos vienen del academic-history endpoint
		const codigoEstudiante = est.id_estudiante || '-';
		const documento = est.doc_identidad || '-';
		const nombre = est.nombre_completo || est.nombre || '-';
		const progreso = est.promedio_porcentaje || 0;
		const idParaNavegar = est.id_estudiante || est.doc_identidad || est.codigo || '';
		
		// Color de la barra según progreso
		let barColor = '#4caf50'; // Verde
		if (progreso < 50) barColor = '#f44336'; // Rojo
		else if (progreso < 70) barColor = '#ff9800'; // Naranja
		
		const rowBg = index % 2 === 0 ? '#f9f9f9' : 'white';
		
		html += `
			<tr class="student-row" data-search="${escapeHtml(nombre.toLowerCase())} ${escapeHtml(codigoEstudiante)} ${escapeHtml(documento)}" style="background:${rowBg};transition:background 0.2s;" onmouseover="this.style.background='#e3f2fd'" onmouseout="this.style.background='${rowBg}'">
				<td style="padding:12px;border-bottom:1px solid #e0e0e0;font-weight:600;color:var(--plg-primary);">${escapeHtml(codigoEstudiante)}</td>
				<td style="padding:12px;border-bottom:1px solid #e0e0e0;font-weight:500;">${escapeHtml(documento)}</td>
				<td style="padding:12px;border-bottom:1px solid #e0e0e0;">${escapeHtml(nombre)}</td>
				<td style="padding:12px;border-bottom:1px solid #e0e0e0;">
					<div style="display:flex;align-items:center;gap:12px;">
						<div style="flex:1;background:#e0e0e0;height:10px;border-radius:5px;overflow:hidden;">
							<div style="width:${progreso}%;background:${barColor};height:100%;transition:width 0.3s;"></div>
						</div>
						<span style="font-weight:600;color:${barColor};min-width:45px;">${progreso}%</span>
					</div>
				</td>
				<td style="padding:12px;border-bottom:1px solid #e0e0e0;text-align:center;">
					<a href="#/estudiante/${encodeURIComponent(idParaNavegar)}" 
					   class="btn btn-primary" 
					   style="padding:8px 16px;border-radius:4px;text-decoration:none;display:inline-block;font-size:14px;">
						Ver detalle
					</a>
				</td>
			</tr>
		`;
	});
	
	html += `
					</tbody>
				</table>
			</div>
			<div id="no-results" style="display:none;padding:40px;text-align:center;color:var(--plg-mutedText);">
				<p style="font-size:18px;margin:0;">No se encontraron estudiantes</p>
			</div>
		</div>
	`;
	container.innerHTML = html;
	
	// Agregar funcionalidad de búsqueda
	const searchInput = document.getElementById('search-students');
	const tableBody = document.getElementById('students-table-body');
	const noResults = document.getElementById('no-results');
	
	if (searchInput) {
		searchInput.addEventListener('input', function(e) {
			const searchTerm = e.target.value.toLowerCase().trim();
			const rows = tableBody.querySelectorAll('.student-row');
			let visibleCount = 0;
			
			rows.forEach(row => {
				const searchData = row.getAttribute('data-search');
				if (searchData.includes(searchTerm)) {
					row.style.display = '';
					visibleCount++;
				} else {
					row.style.display = 'none';
				}
			});
			
			// Mostrar/ocultar mensaje de "no hay resultados"
			if (visibleCount === 0) {
				tableBody.parentElement.style.display = 'none';
				noResults.style.display = 'block';
			} else {
				tableBody.parentElement.style.display = '';
				noResults.style.display = 'none';
			}
		});
	}
}

/**
 * Escapa HTML para evitar XSS
 */
function escapeHtml(text) {
	const div = document.createElement('div');
	div.textContent = text;
	return div.innerHTML;
}

export function unmount() {
	// Nothing to unmount
}
