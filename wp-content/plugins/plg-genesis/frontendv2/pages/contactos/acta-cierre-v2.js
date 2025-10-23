import { api } from '../../api/client.js';

export async function mount(container, { code } = {}) {
	container.innerHTML = `
		<div class="card">
			<div class="u-flex u-gap" style="justify-content:space-between;align-items:center;">
				<div class="card-title">📜 Acta de Cierre</div>
				<div class="u-flex u-gap">
					<a href="#/contacto/${encodeURIComponent(code)}" class="btn btn-secondary">← Volver al Contacto</a>
					<button onclick="window.print()" class="btn btn-primary">🖨️ Imprimir</button>
				</div>
			</div>
			<div id="acta-content">Cargando acta de cierre...</div>
		</div>
	`;

	const $content = container.querySelector('#acta-content');

	try {
		// Primero cargar contacto para obtener su ID
		const contactoRes = await api.get(`/contactos/${encodeURIComponent(code)}`);
		
		if (!contactoRes || !contactoRes.data) {
			throw new Error('Contacto no encontrado');
		}

		const contacto = contactoRes.data;
		const contactoId = contacto.id;

		// Ahora cargar el acta con el ID numérico
		const actaRes = await api.get(`/diplomas/acta-cierre?contactoId=${contactoId}`);
		
		if (!actaRes) {
			throw new Error('Error cargando acta');
		}

		const acta = actaRes.data || {};

		renderActaCierre($content, contacto, acta, contactoId);

	} catch (error) {
		console.error('Error cargando acta:', error);
		$content.innerHTML = '<div class="alert alert-danger">Error cargando acta de cierre</div>';
	}
}

function renderActaCierre($content, contacto, acta, contactoId) {
	const { elegibles = [], emitidos = [], pendientes_entrega = [] } = acta;
	const entregados = emitidos.filter(d => d.entregado);

	$content.innerHTML = `
		<style>
			body, .card {
				background: #fff;
				color: #333;
			}
			.acta-header {
				text-align: center;
				padding: 20px;
				margin-bottom: 30px;
				border-bottom: 3px solid #333;
				background: #fff;
				color: #333;
			}
			.acta-header h2 {
				margin: 0 0 10px 0;
				font-size: 1.5rem;
				color: #333;
			}
			.contacto-info {
				font-size: 1.1rem;
				color: #666;
				margin-bottom: 5px;
			}
			.contacto-info strong {
				color: #333;
			}
			.fecha-generacion {
				font-size: 0.9rem;
				color: #999;
				margin-top: 10px;
			}
			.acta-section {
				margin-bottom: 30px;
				padding: 20px;
				background: #fff;
				border-radius: 8px;
				border: 1px solid #e0e0e0;
				color: #333;
			}
			.acta-section h3 {
				margin: 0 0 15px 0;
				font-size: 1.2rem;
				padding-bottom: 10px;
				border-bottom: 2px solid #e0e0e0;
				color: inherit;
			}
			.acta-section p {
				color: #666;
			}
			.acta-section.elegibles h3 { color: #28a745; }
			.acta-section.pendientes h3 { color: #ffc107; }
			.acta-section.entregados h3 { color: #6c757d; }
			.acta-table {
				width: 100%;
				border-collapse: collapse;
				margin-top: 15px;
			}
			.acta-table th,
			.acta-table td {
				padding: 12px;
				text-align: left;
				border-bottom: 1px solid #e0e0e0;
				color: #333;
			}
			.acta-table th {
				background: #f8f9fa;
				font-weight: 600;
				color: #333;
			}
			.acta-table tbody tr:hover {
				background: #f9f9f9;
			}
			.badge {
				display: inline-block;
				padding: 4px 8px;
				border-radius: 3px;
				font-size: 0.8rem;
				font-weight: 600;
			}
			.badge-success { background: #d4edda; color: #155724; }
			.badge-warning { background: #fff3cd; color: #856404; }
			.badge-info { background: #d1ecf1; color: #0c5460; }
			.empty-state {
				text-align: center;
				padding: 40px;
				color: #999;
			}
			.diploma-checkbox, #checkbox-all {
				cursor: pointer;
				width: 18px;
				height: 18px;
				accent-color: #28a745;
			}
			.acta-table {
				background: white;
			}
			.acta-table tbody tr {
				background: white;
			}
			@media print {
				.btn, .card-title, button, 
				.diploma-checkbox, #checkbox-all,
				th:first-child, td:first-child { 
					display: none !important; 
				}
			}
		</style>

		<div class="acta-header">
			<h2>Acta de Cierre de Diplomas</h2>
			<div class="contacto-info">
				<strong>${escapeHtml(contacto.nombre || 'Contacto')}</strong>
			</div>
			<div class="contacto-info">
				${contacto.email ? escapeHtml(contacto.email) : ''} 
				${contacto.celular ? '• ' + escapeHtml(contacto.celular) : ''}
			</div>
			<div class="fecha-generacion">
				Generado el ${new Date().toLocaleDateString('es-ES', { 
					year: 'numeric', 
					month: 'long', 
					day: 'numeric',
					hour: '2-digit',
					minute: '2-digit'
				})}
			</div>
		</div>

		${elegibles.length > 0 ? `
			<div class="acta-section elegibles">
				<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
					<div>
						<h3 style="margin: 0;">✨ Diplomas Elegibles para Emisión (${elegibles.length})</h3>
						<p style="margin: 5px 0 0 0; font-size: 0.9rem; color: #666;">Selecciona los diplomas que deseas emitir</p>
					</div>
					<div>
						<button class="btn btn-sm btn-secondary" onclick="toggleSeleccionarTodos()" style="margin-right: 10px;">
							<span id="toggle-text">☑️ Seleccionar Todos</span>
						</button>
						<button class="btn btn-success" onclick="emitirSeleccionados()" id="btn-emitir-seleccionados">
							✅ Emitir Seleccionados (<span id="contador-seleccionados">0</span>)
						</button>
					</div>
				</div>
				<table class="acta-table">
					<thead>
						<tr>
							<th style="width: 40px;">
								<input type="checkbox" id="checkbox-all" onchange="seleccionarTodos(this.checked)" />
							</th>
							<th>Estudiante</th>
							<th>Programa</th>
							<th>Tipo</th>
							<th>Nivel</th>
							<th>Versión</th>
						</tr>
					</thead>
					<tbody id="tabla-elegibles">
						${elegibles.map((d, idx) => `
							<tr>
								<td>
									<input 
										type="checkbox" 
										class="diploma-checkbox" 
										data-index="${idx}"
										data-tipo="${d.tipo}"
										data-programa-id="${d.programa_id}"
										data-version="${d.version_programa}"
										data-estudiante-id="${d.estudiante_id}"
										data-nivel-id="${d.nivel_id || ''}"
										onchange="actualizarContador()"
									/>
								</td>
								<td>
									<strong style="color: #007bff;">${escapeHtml(d.estudiante_codigo)}</strong>
									<br>
									<span style="font-size: 0.9rem; color: #666;">${escapeHtml(d.estudiante_nombre)}</span>
								</td>
								<td>${escapeHtml(d.programa_nombre)}</td>
								<td><span class="badge badge-info">${d.tipo === 'nivel' ? 'Nivel' : 'Programa Completo'}</span></td>
								<td>${d.nivel_nombre ? escapeHtml(d.nivel_nombre) : 'N/A'}</td>
								<td>${d.version_programa}</td>
							</tr>
						`).join('')}
					</tbody>
				</table>
			</div>
		` : ''}

		${pendientes_entrega.length > 0 ? `
			<div class="acta-section pendientes">
				<h3>📋 Diplomas Pendientes de Entrega (${pendientes_entrega.length})</h3>
				<p>Estos diplomas ya están emitidos pero pendientes de entrega física:</p>
				<table class="acta-table">
					<thead>
						<tr>
							<th>Programa</th>
							<th>Tipo</th>
							<th>Nivel</th>
							<th>Fecha Emisión</th>
							<th>Acciones</th>
						</tr>
					</thead>
					<tbody>
						${pendientes_entrega.map(d => `
							<tr>
								<td>${escapeHtml(d.programa_nombre)}</td>
								<td><span class="badge badge-warning">${d.tipo === 'nivel' ? 'Nivel' : 'Programa Completo'}</span></td>
								<td>${d.nivel_nombre ? escapeHtml(d.nivel_nombre) : 'N/A'}</td>
								<td>${formatDate(d.fecha_emision)}</td>
								<td>
									<button 
										class="btn btn-sm btn-primary" 
										onclick="registrarEntrega(${d.id})">
										Registrar Entrega
									</button>
								</td>
							</tr>
						`).join('')}
					</tbody>
				</table>
			</div>
		` : ''}

		${entregados.length > 0 ? `
			<div class="acta-section entregados">
				<h3>✅ Diplomas Entregados (${entregados.length})</h3>
				<p>Historial de diplomas ya entregados:</p>
				<table class="acta-table">
					<thead>
						<tr>
							<th>Programa</th>
							<th>Tipo</th>
							<th>Nivel</th>
							<th>Fecha Emisión</th>
							<th>Fecha Entrega</th>
							<th>Notas</th>
						</tr>
					</thead>
					<tbody>
						${entregados.map(d => `
							<tr>
								<td>${escapeHtml(d.programa_nombre)}</td>
								<td><span class="badge badge-success">${d.tipo === 'nivel' ? 'Nivel' : 'Programa Completo'}</span></td>
								<td>${d.nivel_nombre ? escapeHtml(d.nivel_nombre) : 'N/A'}</td>
								<td>${formatDate(d.fecha_emision)}</td>
								<td>${formatDate(d.fecha_entrega)}</td>
								<td>${d.notas ? escapeHtml(d.notas) : ''}</td>
							</tr>
						`).join('')}
					</tbody>
				</table>
			</div>
		` : ''}

		${elegibles.length === 0 && emitidos.length === 0 ? `
			<div class="acta-section empty-state">
				<p>No hay diplomas disponibles para este contacto.</p>
			</div>
		` : ''}
	`;

	// Agregar funciones globales para las acciones
	setupActaActions(contactoId);
}

function setupActaActions(contactoId) {
	// Contador de diplomas seleccionados
	window.actualizarContador = function() {
		const checkboxes = document.querySelectorAll('.diploma-checkbox:checked');
		const contador = document.getElementById('contador-seleccionados');
		const btn = document.getElementById('btn-emitir-seleccionados');
		
		if (contador) {
			contador.textContent = checkboxes.length;
		}
		if (btn) {
			btn.disabled = checkboxes.length === 0;
		}
		
		// Actualizar estado del checkbox "todos"
		const checkboxAll = document.getElementById('checkbox-all');
		const totalCheckboxes = document.querySelectorAll('.diploma-checkbox').length;
		if (checkboxAll) {
			checkboxAll.checked = checkboxes.length === totalCheckboxes && totalCheckboxes > 0;
		}
	};

	// Seleccionar/deseleccionar todos
	window.seleccionarTodos = function(checked) {
		const checkboxes = document.querySelectorAll('.diploma-checkbox');
		checkboxes.forEach(cb => cb.checked = checked);
		actualizarContador();
	};

	// Toggle seleccionar todos
	window.toggleSeleccionarTodos = function() {
		const checkboxAll = document.getElementById('checkbox-all');
		if (checkboxAll) {
			checkboxAll.checked = !checkboxAll.checked;
			seleccionarTodos(checkboxAll.checked);
		}
	};

	// Emitir diplomas seleccionados
	window.emitirSeleccionados = async function() {
		const checkboxes = document.querySelectorAll('.diploma-checkbox:checked');
		
		if (checkboxes.length === 0) {
			alert('Por favor selecciona al menos un diploma para emitir');
			return;
		}

		if (!confirm(`¿Desea emitir ${checkboxes.length} diploma(s) seleccionado(s)?`)) {
			return;
		}

		const diplomas = Array.from(checkboxes).map(cb => ({
			tipo: cb.dataset.tipo,
			programaId: parseInt(cb.dataset.programaId),
			version: parseInt(cb.dataset.version),
			estudianteId: parseInt(cb.dataset.estudianteId),
			nivelId: cb.dataset.nivelId ? parseInt(cb.dataset.nivelId) : null
		}));

		// Emitir todos en una sola request (batch) con acta
		try {
			const response = await api.post('/diplomas/emitir-batch', { 
				diplomas,
				contactoId: parseInt(contactoId)
			});
			
			if (response && response.success) {
				const { numero_acta, total_exitosos, total_errores, errores } = response.data;
				
				if (total_exitosos > 0) {
					let mensaje = `✅ Acta ${numero_acta} generada exitosamente\n\n${total_exitosos} diploma(s) emitido(s)`;
					if (total_errores > 0) {
						mensaje += `\n⚠️ ${total_errores} error(es)`;
						console.error('Errores al emitir:', errores);
					}
					alert(mensaje);
					location.reload();
				} else {
					alert('❌ No se pudo emitir ningún diploma. Revisa la consola para más detalles.');
					console.error('Errores:', errores);
				}
			} else {
				alert('Error: ' + (response?.error?.message || 'No se pudo emitir diplomas'));
				console.error('Response:', response);
			}
		} catch (error) {
			console.error('Error emitiendo batch:', error);
			alert('❌ Error al emitir diplomas. Revisa la consola para más detalles.');
		}
	};

	// Registrar entrega
	window.registrarEntrega = async function(diplomaId) {
		const fechaEntrega = prompt('Fecha de entrega (YYYY-MM-DD) o dejar vacío para hoy:');
		const notas = prompt('Notas sobre la entrega (opcional):');

		try {
			const response = await api.put(`/diplomas/${diplomaId}/entrega`, {
				fechaEntrega: fechaEntrega || null,
				notas: notas || null
			});

			if (response && response.success) {
				alert('Entrega registrada exitosamente');
				location.reload();
			} else {
				alert('Error: ' + (response?.error?.message || 'No se pudo registrar la entrega'));
			}
		} catch (error) {
			console.error('Error:', error);
			alert('Error registrando entrega');
		}
	};

	// Inicializar contador
	actualizarContador();
}

function escapeHtml(text) {
	if (!text) return '';
	const div = document.createElement('div');
	div.textContent = text;
	return div.innerHTML;
}

function formatDate(dateString) {
	if (!dateString) return 'N/A';
	const date = new Date(dateString);
	return date.toLocaleDateString('es-ES', { 
		year: 'numeric', 
		month: 'short', 
		day: 'numeric' 
	});
}

