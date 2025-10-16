/**
 * Calendario de Cursos - Dashboard v2
 * Consume API REST plg-genesis/v1/cursos-calendario/*
 */
import { api } from '../../api/client.js';
import AuthService from '../../services/auth.js';

let currentMonth = new Date().getMonth() + 1;
let currentYear = new Date().getFullYear();
let selectedDate = null;
let allCursos = []; // Guardar todos los cursos para filtrado
let searchTerm = '';

const monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
const dayNames = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

async function loadCalendarData() {
	const loadingEl = document.getElementById('calendar-loading');
	const gridEl = document.getElementById('calendar-grid');
	if (loadingEl) loadingEl.style.display = 'flex';
	if (gridEl) gridEl.style.display = 'none';

	try {
		const response = await api.get(`/cursos-calendario/mes?mes=${currentMonth}&anio=${currentYear}`);
		const data = response.data;

		// Actualizar título y total
		document.getElementById('calendar-title').textContent = `${monthNames[currentMonth - 1]} ${currentYear}`;
		document.getElementById('total-cursos').textContent = data.totalMes || 0;

		// Renderizar calendario
		renderCalendar(data.cursosPorDia || {});

		if (loadingEl) loadingEl.style.display = 'none';
		if (gridEl) gridEl.style.display = 'grid';
	} catch (error) {
		console.error('Error al cargar calendario:', error);
		if (loadingEl) loadingEl.innerHTML = '<div class="alert alert-error">Error al cargar el calendario</div>';
	}
}

function renderCalendar(cursosPorDia) {
	const gridEl = document.getElementById('calendar-days');
	if (!gridEl) return;

	const firstDay = new Date(currentYear, currentMonth - 1, 1);
	const lastDay = new Date(currentYear, currentMonth, 0);
	const daysInMonth = lastDay.getDate();
	const startingDayOfWeek = firstDay.getDay();

	let html = '';

	// Días vacíos al inicio
	for (let i = 0; i < startingDayOfWeek; i++) {
		html += '<div class="calendar-day empty"></div>';
	}

	// Días del mes
	for (let day = 1; day <= daysInMonth; day++) {
		const cantidad = cursosPorDia[day] || 0;
		const hasClasses = cantidad > 0 ? 'has-courses' : '';
		html += `
			<div class="calendar-day ${hasClasses}" data-day="${day}">
				<div class="day-number">${day}</div>
				${cantidad > 0 ? `<div class="course-count">${cantidad} cursos</div>` : ''}
			</div>
		`;
	}

	gridEl.innerHTML = html;
}

async function loadDayDetails(day) {
	selectedDate = { day, month: currentMonth, year: currentYear };
	const modal = document.getElementById('modal-detalles');
	const content = document.getElementById('modal-content');
	const searchInput = document.getElementById('search-cursos');
	
	modal.style.display = 'flex';
	content.innerHTML = '<div class="loading"><div class="spinner"></div><p>Cargando...</p></div>';
	if (searchInput) {
		searchInput.value = '';
		searchTerm = '';
	}

	try {
		const response = await api.get(`/cursos-calendario/dia?dia=${day}&mes=${currentMonth}&anio=${currentYear}`);
		allCursos = response.data.cursos || [];

		renderCursos(allCursos);

		// Actualizar botón de certificados del día
		const btnAllCerts = document.getElementById('btn-all-certs');
		if (btnAllCerts) {
			btnAllCerts.onclick = () => generateDayCertificates(day, currentMonth, currentYear);
		}
	} catch (error) {
		console.error('Error al cargar detalles:', error);
		content.innerHTML = '<div class="alert alert-error">Error al cargar los detalles</div>';
	}
}

function renderCursos(cursos) {
	const content = document.getElementById('modal-content');
	const canDelete = AuthService.can('plg_delete_courses');

	if (cursos.length === 0) {
		content.innerHTML = '<div class="empty-state">No hay cursos para este día</div>';
		return;
	}

	content.innerHTML = cursos.map(curso => `
		<div class="curso-card" data-id="${curso.estudianteCursoId}">
			<div class="curso-header">
				<h4>${curso.nombreCurso}</h4>
				<div class="curso-actions">
					<button class="btn-icon btn-cert" data-id="${curso.estudianteCursoId}" title="Generar Certificado">📄</button>
					${canDelete ? `<button class="btn-icon btn-delete" data-id="${curso.estudianteCursoId}" title="Eliminar">🗑️</button>` : ''}
				</div>
			</div>
			<div class="curso-info">
				<p><strong>👤 Estudiante</strong>${curso.nombreEstudiante}</p>
				${curso.estudianteId ? `<p><strong>🆔 ID</strong>${curso.estudianteId}</p>` : ''}
				${curso.celular ? `<p><strong>📱 Celular</strong>${curso.celular}</p>` : ''}
				${curso.nombreContacto ? `<p><strong>📧 Contacto</strong>${curso.nombreContacto}</p>` : ''}
				${curso.nota ? `<p><strong>📊 Nota</strong><span style="color: ${curso.nota >= 70 ? '#3fab49' : '#e11d48'}; font-weight: 600; font-size: 16px;">${curso.nota}%</span></p>` : ''}
			</div>
		</div>
	`).join('');
}

function filterCursos() {
	if (!allCursos.length) return;
	
	const filtered = allCursos.filter(curso => {
		const search = searchTerm.toLowerCase();
		return curso.nombreCurso.toLowerCase().includes(search) ||
		       curso.nombreEstudiante.toLowerCase().includes(search) ||
		       (curso.estudianteId && curso.estudianteId.toLowerCase().includes(search)) ||
		       (curso.nombreContacto && curso.nombreContacto.toLowerCase().includes(search));
	});

	renderCursos(filtered);
}

async function deleteCourse(id) {
	if (!confirm('¿Estás seguro de eliminar este registro? Esta acción no se puede deshacer.')) {
		return;
	}

	try {
		await api.delete(`/estudiantes-cursos/${id}`);
		alert('Curso eliminado correctamente');
		
		// Recargar calendario y cerrar modal
		loadCalendarData();
		closeModal();
	} catch (error) {
		console.error('Error al eliminar:', error);
		alert('Error al eliminar el curso: ' + (error.message || 'Error desconocido'));
	}
}

function generateDayCertificates(day, month, year) {
	const url = `/wp-content/plugins/plg-genesis/backend/certificados/generar_certificados_dia.php?dia=${day}&mes=${month}&anio=${year}`;
	window.open(url, '_blank');
}

function generateCertificate(id) {
	const url = `/wp-content/plugins/plg-genesis/backend/certificados/generar_certificado.php?id=${id}`;
	window.open(url, '_blank');
}

function closeModal() {
	const modal = document.getElementById('modal-detalles');
	if (modal) modal.style.display = 'none';
}

function changeMonth(delta) {
	currentMonth += delta;
	if (currentMonth > 12) {
		currentMonth = 1;
		currentYear++;
	} else if (currentMonth < 1) {
		currentMonth = 12;
		currentYear--;
	}
	loadCalendarData();
}

export function mount(container) {
	container.innerHTML = `
		<style>
			.calendar-container { max-width: 1400px; margin: 0 auto; }
			.calendar-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 16px; }
			.calendar-title { font-size: 28px; font-weight: 700; color: #111827; }
			.calendar-nav { display: flex; gap: 10px; }
			.nav-button { padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 8px; cursor: pointer; transition: all 0.2s; }
			.nav-button:hover { background: #2563eb; }
			.calendar-stats { background: #f1f5f9; border-radius: 8px; padding: 15px; margin-bottom: 20px; text-align: center; }
			.total-courses { font-size: 16px; color: #3fab49; font-weight: 500; }
			.calendar-header-row { display: grid; grid-template-columns: repeat(7, 1fr); gap: 10px; margin-bottom: 10px; }
			.day-header { text-align: center; font-weight: 600; color: #64748b; padding: 10px; background: #f8fafc; border-radius: 6px; }
			#calendar-grid { display: block; }
			#calendar-days { display: grid; grid-template-columns: repeat(7, 1fr); gap: 10px; }
			.calendar-day { min-height: 100px; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px; background: white; transition: all 0.2s; cursor: pointer; }
			.calendar-day:hover { box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1); }
			.calendar-day.empty { background: #f8fafc; border-color: #f1f5f9; cursor: default; }
			.calendar-day.has-courses { border-color: #3b82f6; background: #eff6ff; }
			.day-number { font-size: 14px; font-weight: 500; color: #64748b; margin-bottom: 5px; }
			.course-count { font-size: 13px; color: #3b82f6; font-weight: 500; padding: 4px 8px; border-radius: 4px; background: #dbeafe; display: inline-block; margin-top: 5px; }
			.loading, #calendar-loading { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 60px; }
			.spinner { width: 48px; height: 48px; border: 4px solid #e5e7eb; border-top-color: #3b82f6; border-radius: 50%; animation: spin 0.8s linear infinite; }
			@keyframes spin { to { transform: rotate(360deg); } }
			.modal-overlay { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.6); z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(2px); }
			.modal-dialog { background: white; border-radius: 16px; max-width: 900px; width: 90%; max-height: 85vh; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
			.modal-header { padding: 24px 28px; background: linear-gradient(135deg, #0c497a 0%, #3b82f6 100%); color: white; display: flex; justify-content: space-between; align-items: center; }
			.modal-header h3 { margin: 0; font-size: 22px; font-weight: 600; }
			.modal-search { padding: 16px 24px; background: white; border-bottom: 1px solid #e5e7eb; }
			.search-input { width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb; border-radius: 8px; font-size: 15px; transition: all 0.2s; }
			.search-input:focus { outline: none; border-color: #0c497a; box-shadow: 0 0 0 3px rgba(12, 73, 122, 0.1); }
			.modal-body { padding: 24px; overflow-y: auto; flex: 1; background: #f9fafb; }
			
			/* Responsive mobile */
			@media (max-width: 767px) {
				.modal-dialog { max-width: 100%; width: 100%; max-height: 100vh; border-radius: 0; }
				.modal-header { padding: 16px 20px; }
				.modal-header h3 { font-size: 18px; }
				.modal-search { padding: 12px 16px; display: block; }
				.search-input { font-size: 16px; padding: 10px 14px; }
				.modal-body { padding: 16px; }
				.modal-footer { padding: 16px; }
				.curso-card { padding: 16px; }
				.curso-header { flex-direction: column; align-items: flex-start; gap: 10px; }
				.curso-header h4 { font-size: 16px; }
				.curso-actions { width: 100%; justify-content: flex-end; }
				.curso-info { grid-template-columns: 1fr; gap: 8px; }
				.curso-info p { padding: 10px; font-size: 14px; }
				.btn-success { padding: 14px 16px; font-size: 14px; }
				.calendar-stats { padding: 12px; margin-bottom: 16px; }
				.total-courses { font-size: 15px; }
			}
			.modal-footer { padding: 20px 24px; background: white; border-top: 1px solid #e5e7eb; }
			.btn-close { background: rgba(255,255,255,0.2); border: none; font-size: 24px; cursor: pointer; color: white; width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; transition: all 0.2s; }
			.btn-close:hover { background: rgba(255,255,255,0.3); transform: rotate(90deg); }
			.curso-card { background: white; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 12px; transition: all 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
			.curso-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); transform: translateY(-2px); }
			.curso-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 2px solid #f1f5f9; }
			.curso-header h4 { margin: 0; color: #1e293b; font-size: 18px; font-weight: 600; display: flex; align-items: center; gap: 8px; }
			.curso-header h4::before { content: '📚'; font-size: 20px; }
			.curso-actions { display: flex; gap: 8px; }
			.btn-icon { background: #f1f5f9; border: none; font-size: 18px; cursor: pointer; padding: 8px 12px; border-radius: 8px; transition: all 0.2s; }
			.btn-icon:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
			.btn-cert { color: #3fab49; }
			.btn-cert:hover { background: #dcfce7; }
			.btn-delete { color: #e11d48; }
			.btn-delete:hover { background: #ffe4e6; }
			.curso-info { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; }
			.curso-info p { margin: 0; color: #64748b; font-size: 14px; padding: 8px; background: #f8fafc; border-radius: 6px; }
			.curso-info strong { color: #1e293b; display: block; margin-bottom: 4px; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; }
			.empty-state { text-align: center; padding: 40px; color: #64748b; }
			.alert { padding: 16px; border-radius: 8px; margin-bottom: 16px; }
			.alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
			.btn-success { padding: 14px 24px; background: linear-gradient(135deg, #3fab49 0%, #22c55e 100%); color: white; border: none; border-radius: 10px; font-weight: 600; cursor: pointer; width: 100%; font-size: 15px; display: flex; align-items: center; justify-content: center; gap: 10px; transition: all 0.3s; box-shadow: 0 4px 12px rgba(63, 171, 73, 0.3); }
			.btn-success:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(63, 171, 73, 0.4); }
			.btn-success:active { transform: translateY(0); }
			@media (max-width: 768px) {
				.calendar-header { flex-direction: column; text-align: center; }
				.calendar-title { font-size: 24px; }
				.calendar-day { min-height: 80px; padding: 5px; font-size: 12px; }
				.modal-dialog { width: 95%; }
			}
		</style>

		<div class="card calendar-container">
			<div class="calendar-header">
				<h1 class="calendar-title" id="calendar-title">Calendario</h1>
				<div class="calendar-nav">
					<button class="nav-button" id="btn-prev-month">‹ Mes anterior</button>
					<button class="nav-button" id="btn-next-month">Mes siguiente ›</button>
				</div>
			</div>

			<div class="calendar-stats">
				<div class="total-courses">Total de cursos este mes: <span id="total-cursos">0</span></div>
			</div>

			<div id="calendar-loading" style="display:none;">
				<div class="spinner"></div>
				<p>Cargando calendario...</p>
			</div>

			<div id="calendar-grid">
				<div class="calendar-header-row">
					${dayNames.map(day => `<div class="day-header">${day}</div>`).join('')}
				</div>
				<div id="calendar-days"></div>
			</div>
		</div>

		<div class="modal-overlay" id="modal-detalles">
			<div class="modal-dialog">
				<div class="modal-header">
					<h3>📋 Cursos del Día</h3>
					<button class="btn-close" id="btn-close-modal">&times;</button>
				</div>
				<div class="modal-search">
					<input 
						type="text" 
						id="search-cursos" 
						class="search-input" 
						placeholder="🔍 Buscar por estudiante, curso o contacto..."
					>
				</div>
				<div class="modal-body" id="modal-content"></div>
				<div class="modal-footer">
					<button class="btn-success" id="btn-all-certs">
						<span>📄</span>
						<span>Generar Todos los Certificados</span>
					</button>
				</div>
			</div>
		</div>
	`;

	// Event Listeners
	document.getElementById('btn-prev-month').addEventListener('click', () => changeMonth(-1));
	document.getElementById('btn-next-month').addEventListener('click', () => changeMonth(1));
	document.getElementById('btn-close-modal').addEventListener('click', closeModal);
	document.getElementById('modal-detalles').addEventListener('click', (e) => {
		if (e.target.id === 'modal-detalles') closeModal();
	});

	// Búsqueda en tiempo real
	document.getElementById('search-cursos').addEventListener('input', (e) => {
		searchTerm = e.target.value;
		filterCursos();
	});

	// Delegación de eventos
	document.addEventListener('click', (e) => {
		if (e.target.closest('.calendar-day.has-courses')) {
			const day = parseInt(e.target.closest('.calendar-day').dataset.day);
			loadDayDetails(day);
		}
		if (e.target.classList.contains('btn-delete')) {
			const id = parseInt(e.target.dataset.id);
			deleteCourse(id);
		}
		if (e.target.classList.contains('btn-cert')) {
			const id = parseInt(e.target.dataset.id);
			generateCertificate(id);
		}
	});

	// Cargar datos iniciales
	loadCalendarData();

	return {
		unmount() {
			// Cleanup
		}
	};
}

