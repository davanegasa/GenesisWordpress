/**
 * Vista de Informe Anual - Dashboard v2
 * Consume API REST plg-genesis/v1/estadisticas/informe-anual
 */
import { api } from '../../api/client.js';

// Variables para las gráficas
let tendenciasChart = null;
let comparativaChart = null;
let chartJsLoaded = false;

/**
 * Carga Chart.js dinámicamente
 */
async function loadChartJs() {
	if (chartJsLoaded || typeof Chart !== 'undefined') {
		chartJsLoaded = true;
		return Promise.resolve();
	}

	return new Promise((resolve, reject) => {
		const script = document.createElement('script');
		script.src = 'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js';
		script.onload = () => {
			chartJsLoaded = true;
			resolve();
		};
		script.onerror = () => reject(new Error('No se pudo cargar Chart.js'));
		document.head.appendChild(script);
	});
}

/**
 * Formatea un mes en formato YYYY-MM a nombre legible
 */
function formatearMes(mesISO, incluirYear = true) {
	if (!mesISO || typeof mesISO !== 'string') return '-';

	const meses = [
		'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
		'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
	];

	const [year, month] = mesISO.split('-');
	const mesNum = parseInt(month, 10);
	
	if (mesNum >= 1 && mesNum <= 12) {
		return incluirYear ? `${meses[mesNum - 1]} ${year}` : meses[mesNum - 1];
	}
	return mesISO;
}

/**
 * Formatea número con separadores de miles
 */
function formatearNumero(num) {
	if (num === null || num === undefined) return '0';
	return parseInt(num, 10).toLocaleString('es-ES');
}

/**
 * Calcula totales de un informe anual
 */
function calcularTotales(datos) {
	if (!Array.isArray(datos) || datos.length === 0) {
		return {
			totalEstudiantesActivos: 0,
			totalCursosCorregidos: 0,
			totalEstudiantesRegistrados: 0,
			totalContactosActivos: 0,
			totalContactosRegistrados: 0,
			promedioMensualCursos: 0
		};
	}

	const totales = datos.reduce((acc, row) => {
		acc.totalCursosCorregidos += parseInt(row.cursos_correjidos || 0, 10);
		acc.totalEstudiantesRegistrados += parseInt(row.estudiantes_registrados || 0, 10);
		acc.totalContactosRegistrados += parseInt(row.contactos_registrados || 0, 10);
		return acc;
	}, {
		totalEstudiantesActivos: 0,
		totalCursosCorregidos: 0,
		totalEstudiantesRegistrados: 0,
		totalContactosActivos: 0,
		totalContactosRegistrados: 0
	});

	// El último mes tiene los totales acumulados para activos
	const ultimoMes = datos[datos.length - 1];
	totales.totalEstudiantesActivos = parseInt(ultimoMes?.estudiantes_activos || 0, 10);
	totales.totalContactosActivos = parseInt(ultimoMes?.contactos_activos || 0, 10);

	// Promedio mensual de cursos
	totales.promedioMensualCursos = datos.length > 0 
		? Math.round(totales.totalCursosCorregidos / datos.length)
		: 0;

	return totales;
}

/**
 * Carga los datos del informe
 */
async function loadReport(year) {
	const loadingEl = document.getElementById('informe-loading');
	const errorEl = document.getElementById('informe-error');
	const statsEl = document.getElementById('informe-stats');
	const tableEl = document.getElementById('informe-table');

	if (loadingEl) loadingEl.style.display = 'flex';
	if (errorEl) errorEl.style.display = 'none';
	if (statsEl) statsEl.style.display = 'none';
	if (tableEl) tableEl.style.display = 'none';

	try {
		const response = await api.get(`/estadisticas/informe-anual?year=${year}`);
		const datos = response.data || [];
		const totales = calcularTotales(datos);

		if (loadingEl) loadingEl.style.display = 'none';
		
		// Renderizar estadísticas
		renderStats(totales);
		
		// Renderizar tabla
		renderTable(datos);

		// Renderizar gráficas (async)
		renderCharts(datos).catch(err => {
			console.error('Error al renderizar gráficas:', err);
		});

		if (statsEl) statsEl.style.display = 'grid';
		if (tableEl) tableEl.style.display = 'block';

	} catch (error) {
		console.error('Error al cargar informe:', error);
		if (loadingEl) loadingEl.style.display = 'none';
		if (errorEl) {
			errorEl.style.display = 'flex';
			errorEl.innerHTML = `
				<div class="alert alert-error">
					<strong>⚠️ Error:</strong> ${error.message || 'No se pudo cargar el informe'}
				</div>
			`;
		}
	}
}

/**
 * Renderiza las estadísticas
 */
function renderStats(totales) {
	const container = document.getElementById('informe-stats');
	if (!container) return;

	container.innerHTML = `
		<div class="stat-card">
			<div class="stat-label">Total Cursos Corregidos</div>
			<div class="stat-value">${formatearNumero(totales.totalCursosCorregidos)}</div>
		</div>
		<div class="stat-card stat-success">
			<div class="stat-label">Estudiantes Activos</div>
			<div class="stat-value">${formatearNumero(totales.totalEstudiantesActivos)}</div>
		</div>
		<div class="stat-card stat-warning">
			<div class="stat-label">Nuevos Estudiantes</div>
			<div class="stat-value">${formatearNumero(totales.totalEstudiantesRegistrados)}</div>
		</div>
		<div class="stat-card stat-info">
			<div class="stat-label">Contactos Activos</div>
			<div class="stat-value">${formatearNumero(totales.totalContactosActivos)}</div>
		</div>
	`;
}

/**
 * Renderiza la tabla
 */
function renderTable(datos) {
	const tbody = document.getElementById('informe-tbody');
	if (!tbody) return;

	if (!datos || datos.length === 0) {
		tbody.innerHTML = `
			<tr>
				<td colspan="6" style="text-align:center; padding:40px; color:#6b7280;">
					📭 No se encontraron datos para el año seleccionado.
				</td>
			</tr>
		`;
		return;
	}

	tbody.innerHTML = datos.map(row => `
		<tr>
			<td style="font-weight:500;">${formatearMes(row.mes)}</td>
			<td style="text-align:right;">${formatearNumero(row.estudiantes_activos)}</td>
			<td style="text-align:right;">${formatearNumero(row.cursos_correjidos)}</td>
			<td style="text-align:right;">${formatearNumero(row.estudiantes_registrados)}</td>
			<td style="text-align:right;">${formatearNumero(row.contactos_activos)}</td>
			<td style="text-align:right;">${formatearNumero(row.contactos_registrados)}</td>
		</tr>
	`).join('');
}

/**
 * Renderiza las gráficas
 */
async function renderCharts(datos) {
	if (!datos || datos.length === 0) return;

	// Cargar Chart.js si no está disponible
	try {
		await loadChartJs();
	} catch (error) {
		console.error('Error al cargar Chart.js:', error);
		return;
	}

	// Preparar datos
	const labels = datos.map(row => formatearMes(row.mes, false));
	
	// Destruir gráficas anteriores
	if (tendenciasChart) {
		tendenciasChart.destroy();
		tendenciasChart = null;
	}
	if (comparativaChart) {
		comparativaChart.destroy();
		comparativaChart = null;
	}

	// Gráfica de Tendencias (Líneas)
	const tendenciasCtx = document.getElementById('tendencias-chart');
	if (tendenciasCtx) {
		tendenciasChart = new Chart(tendenciasCtx, {
			type: 'line',
			data: {
				labels: labels,
				datasets: [
					{
						label: 'Estudiantes Activos',
						data: datos.map(row => parseInt(row.estudiantes_activos || 0)),
						borderColor: '#10b981',
						backgroundColor: 'rgba(16, 185, 129, 0.1)',
						tension: 0.4,
						fill: true
					},
					{
						label: 'Cursos Corregidos',
						data: datos.map(row => parseInt(row.cursos_correjidos || 0)),
						borderColor: '#3b82f6',
						backgroundColor: 'rgba(59, 130, 246, 0.1)',
						tension: 0.4,
						fill: true
					},
					{
						label: 'Nuevos Estudiantes',
						data: datos.map(row => parseInt(row.estudiantes_registrados || 0)),
						borderColor: '#f59e0b',
						backgroundColor: 'rgba(245, 158, 11, 0.1)',
						tension: 0.4,
						fill: true
					},
					{
						label: 'Contactos Activos',
						data: datos.map(row => parseInt(row.contactos_activos || 0)),
						borderColor: '#06b6d4',
						backgroundColor: 'rgba(6, 182, 212, 0.1)',
						tension: 0.4,
						fill: true
					}
				]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: {
					legend: {
						position: 'top',
					},
					title: {
						display: true,
						text: 'Evolución Mensual de Métricas',
						font: { size: 16, weight: 'bold' }
					},
					tooltip: {
						mode: 'index',
						intersect: false,
					}
				},
				scales: {
					y: {
						beginAtZero: true,
						ticks: {
							callback: function(value) {
								return formatearNumero(value);
							}
						}
					}
				},
				interaction: {
					mode: 'nearest',
					axis: 'x',
					intersect: false
				}
			}
		});
	}

	// Gráfica Comparativa (Barras)
	const comparativaCtx = document.getElementById('comparativa-chart');
	if (comparativaCtx) {
		comparativaChart = new Chart(comparativaCtx, {
			type: 'bar',
			data: {
				labels: labels,
				datasets: [
					{
						label: 'Cursos Corregidos',
						data: datos.map(row => parseInt(row.cursos_correjidos || 0)),
						backgroundColor: '#3b82f6',
						borderRadius: 6
					},
					{
						label: 'Nuevos Estudiantes',
						data: datos.map(row => parseInt(row.estudiantes_registrados || 0)),
						backgroundColor: '#f59e0b',
						borderRadius: 6
					},
					{
						label: 'Nuevos Contactos',
						data: datos.map(row => parseInt(row.contactos_registrados || 0)),
						backgroundColor: '#8b5cf6',
						borderRadius: 6
					}
				]
			},
			options: {
				responsive: true,
				maintainAspectRatio: false,
				plugins: {
					legend: {
						position: 'top',
					},
					title: {
						display: true,
						text: 'Comparativa Mensual de Actividad',
						font: { size: 16, weight: 'bold' }
					},
					tooltip: {
						callbacks: {
							label: function(context) {
								return context.dataset.label + ': ' + formatearNumero(context.parsed.y);
							}
						}
					}
				},
				scales: {
					y: {
						beginAtZero: true,
						ticks: {
							callback: function(value) {
								return formatearNumero(value);
							}
						}
					}
				}
			}
		});
	}
}

/**
 * Cambia entre pestañas
 */
function switchTab(tabName) {
	// Ocultar todos los contenidos
	const contents = document.querySelectorAll('.tab-content');
	contents.forEach(content => content.style.display = 'none');

	// Desactivar todos los botones
	const buttons = document.querySelectorAll('.tab-button');
	buttons.forEach(btn => btn.classList.remove('active'));

	// Mostrar contenido seleccionado
	const selectedContent = document.getElementById(`tab-${tabName}`);
	if (selectedContent) {
		selectedContent.style.display = 'block';
	}

	// Activar botón seleccionado
	const selectedButton = document.querySelector(`.tab-button[data-tab="${tabName}"]`);
	if (selectedButton) {
		selectedButton.classList.add('active');
	}

	// Si cambiamos a una gráfica, forzar redibujado
	if ((tabName === 'tendencias' || tabName === 'comparativa')) {
		setTimeout(() => {
			if (tabName === 'tendencias' && tendenciasChart) {
				tendenciasChart.resize();
			} else if (tabName === 'comparativa' && comparativaChart) {
				comparativaChart.resize();
			}
		}, 100);
	}
}

/**
 * Monta la vista en el contenedor
 */
export function mount(container) {
	let currentYear = new Date().getFullYear();
	
	// Generar opciones de años
	let yearOptions = '';
	for (let year = currentYear; year >= 2000; year--) {
		yearOptions += `<option value="${year}" ${year === currentYear ? 'selected' : ''}>${year}</option>`;
	}

	container.innerHTML = `
		<style>
			.informe-header {
				display: flex;
				justify-content: space-between;
				align-items: center;
				margin-bottom: 24px;
				flex-wrap: wrap;
				gap: 16px;
			}
			.informe-title {
				font-size: 28px;
				font-weight: 700;
				color: #111827;
			}
			.informe-controls {
				display: flex;
				align-items: center;
				gap: 12px;
			}
			.informe-select {
				padding: 10px 16px;
				border: 1px solid #d1d5db;
				border-radius: 8px;
				font-size: 15px;
				background: white;
				cursor: pointer;
			}
			.informe-select:focus {
				outline: none;
				border-color: #3b82f6;
				box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
			}
			.btn-reload {
				padding: 10px 20px;
				background: #3b82f6;
				color: white;
				border: none;
				border-radius: 8px;
				font-size: 15px;
				font-weight: 500;
				cursor: pointer;
				transition: all 0.2s;
			}
			.btn-reload:hover {
				background: #2563eb;
			}
			.btn-reload:disabled {
				opacity: 0.5;
				cursor: not-allowed;
			}
			#informe-stats {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
				gap: 16px;
				margin-bottom: 24px;
			}
			.stat-card {
				background: white;
				border-radius: 12px;
				padding: 20px;
				box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
				border-left: 4px solid #3b82f6;
			}
			.stat-card.stat-success {
				border-left-color: #10b981;
			}
			.stat-card.stat-warning {
				border-left-color: #f59e0b;
			}
			.stat-card.stat-info {
				border-left-color: #06b6d4;
			}
			.stat-label {
				font-size: 14px;
				color: #6b7280;
				margin-bottom: 8px;
			}
			.stat-value {
				font-size: 32px;
				font-weight: 700;
				color: #111827;
			}
			#informe-table {
				background: white;
				border-radius: 12px;
				box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
				overflow: hidden;
			}
			.table-header {
				padding: 20px 24px;
				border-bottom: 1px solid #e5e7eb;
				font-size: 20px;
				font-weight: 600;
			}
			.data-table {
				width: 100%;
				border-collapse: collapse;
			}
			.data-table thead {
				background: #f9fafb;
			}
			.data-table th {
				padding: 12px 16px;
				text-align: left;
				font-size: 13px;
				font-weight: 600;
				color: #374151;
				text-transform: uppercase;
				letter-spacing: 0.5px;
				border-bottom: 2px solid #e5e7eb;
			}
			.data-table td {
				padding: 16px;
				font-size: 15px;
				color: #1f2937;
				border-bottom: 1px solid #f3f4f6;
			}
			.data-table tbody tr:hover {
				background: #f9fafb;
			}
			.data-table tbody tr:last-child td {
				border-bottom: none;
			}
			#informe-loading {
				display: flex;
				flex-direction: column;
				align-items: center;
				justify-content: center;
				padding: 60px 24px;
				background: white;
				border-radius: 12px;
			}
			.spinner {
				width: 48px;
				height: 48px;
				border: 4px solid #e5e7eb;
				border-top-color: #3b82f6;
				border-radius: 50%;
				animation: spin 0.8s linear infinite;
			}
			@keyframes spin {
				to { transform: rotate(360deg); }
			}
			.loading-text {
				margin-top: 16px;
				color: #6b7280;
			}
			.alert {
				padding: 16px;
				border-radius: 8px;
				margin-bottom: 24px;
			}
			.alert-error {
				background: #fef2f2;
				border: 1px solid #fecaca;
				color: #991b1b;
			}
			.tabs-container {
				background: white;
				border-radius: 12px;
				box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
				overflow: hidden;
				margin-bottom: 24px;
			}
			.tabs-header {
				display: flex;
				border-bottom: 2px solid #e5e7eb;
				background: #f9fafb;
			}
			.tab-button {
				flex: 1;
				padding: 16px 24px;
				border: none;
				background: transparent;
				font-size: 15px;
				font-weight: 500;
				color: #6b7280;
				cursor: pointer;
				transition: all 0.2s;
				border-bottom: 3px solid transparent;
				position: relative;
			}
			.tab-button:hover {
				background: white;
				color: #3b82f6;
			}
			.tab-button.active {
				color: #3b82f6;
				background: white;
				border-bottom-color: #3b82f6;
			}
			.tab-content {
				padding: 24px;
				display: none;
			}
			.tab-content.active {
				display: block;
			}
			.chart-container {
				position: relative;
				height: 400px;
				width: 100%;
			}
			
			/* Responsive mobile */
			@media (max-width: 767px) {
				.informe-header {
					flex-direction: column;
					align-items: stretch;
					gap: 12px;
				}
				.informe-title {
					font-size: 22px;
					text-align: center;
				}
				.informe-controls {
					flex-direction: column;
					gap: 8px;
				}
				.informe-select,
				.btn-reload {
					width: 100%;
					padding: 12px 16px;
					font-size: 16px;
				}
				#informe-stats {
					grid-template-columns: 1fr;
					gap: 12px;
				}
				.stat-card {
					padding: 16px;
				}
				.stat-value {
					font-size: 26px;
				}
				.tabs-header {
					flex-direction: column;
				}
				.tab-button {
					padding: 14px 16px;
					font-size: 14px;
				}
				.tab-content {
					padding: 16px;
				}
				.table-header {
					padding: 16px;
					font-size: 18px;
				}
				/* Tabla responsive con scroll horizontal */
				.data-table {
					font-size: 13px;
				}
				.data-table th,
				.data-table td {
					padding: 10px 12px;
					white-space: nowrap;
				}
				.data-table th {
					font-size: 11px;
				}
				.chart-container {
					height: 300px;
				}
			}
			
			@media (min-width: 768px) and (max-width: 1024px) {
				#informe-stats {
					grid-template-columns: repeat(2, 1fr);
				}
				.chart-container {
					height: 350px;
				}
			}
		</style>

		<div class="card">
			<div class="informe-header">
				<h1 class="informe-title">📊 Informe Anual de Oficina</h1>
				<div class="informe-controls">
					<select id="year-selector" class="informe-select">
						${yearOptions}
					</select>
					<button id="btn-reload" class="btn-reload">🔄 Actualizar</button>
				</div>
			</div>

			<div id="informe-error" style="display:none;"></div>

			<div id="informe-loading" style="display:none;">
				<div class="spinner"></div>
				<p class="loading-text">Cargando informe...</p>
			</div>

			<div id="informe-stats" style="display:none;"></div>

			<div id="informe-table" style="display:none;">
				<div class="tabs-container">
					<div class="tabs-header">
						<button class="tab-button active" data-tab="tabla" onclick="window.switchInformeTab('tabla')">
							📋 Tabla
						</button>
						<button class="tab-button" data-tab="tendencias" onclick="window.switchInformeTab('tendencias')">
							📈 Tendencias
						</button>
						<button class="tab-button" data-tab="comparativa" onclick="window.switchInformeTab('comparativa')">
							📊 Comparativa
						</button>
					</div>

					<div id="tab-tabla" class="tab-content" style="display:block;">
						<h3 style="margin:0 0 16px 0; font-size:18px; font-weight:600;">Desglose Mensual</h3>
						<div style="overflow-x:auto;">
							<table class="data-table" style="margin:0;">
								<thead>
									<tr>
										<th>Mes</th>
										<th style="text-align:right;">Estudiantes Activos</th>
										<th style="text-align:right;">Cursos Corregidos</th>
										<th style="text-align:right;">Nuevos Estudiantes</th>
										<th style="text-align:right;">Contactos Activos</th>
										<th style="text-align:right;">Nuevos Contactos</th>
									</tr>
								</thead>
								<tbody id="informe-tbody"></tbody>
							</table>
						</div>
					</div>

					<div id="tab-tendencias" class="tab-content">
						<div class="chart-container">
							<canvas id="tendencias-chart"></canvas>
						</div>
					</div>

					<div id="tab-comparativa" class="tab-content">
						<div class="chart-container">
							<canvas id="comparativa-chart"></canvas>
						</div>
					</div>
				</div>
			</div>
		</div>
	`;

	// Exponer función de cambio de pestaña
	window.switchInformeTab = switchTab;

	// Event listeners
	const yearSelector = document.getElementById('year-selector');
	const btnReload = document.getElementById('btn-reload');

	if (yearSelector) {
		yearSelector.addEventListener('change', () => {
			currentYear = parseInt(yearSelector.value, 10);
			loadReport(currentYear);
		});
	}

	if (btnReload) {
		btnReload.addEventListener('click', () => {
			loadReport(currentYear);
		});
	}

	// Cargar datos iniciales
	loadReport(currentYear);

	return {
		unmount() {
			// Limpiar gráficas
			if (tendenciasChart) {
				tendenciasChart.destroy();
				tendenciasChart = null;
			}
			if (comparativaChart) {
				comparativaChart.destroy();
				comparativaChart = null;
			}
			// Limpiar función global
			delete window.switchInformeTab;
		}
	};
}

