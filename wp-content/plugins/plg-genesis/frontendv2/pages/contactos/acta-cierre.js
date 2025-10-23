/**
 * Vista de Acta de Cierre para Contactos
 * Muestra todos los diplomas elegibles, emitidos y pendientes de entrega
 */

(function() {
	'use strict';

	// Obtener ID del contacto desde la URL
	const urlParams = new URLSearchParams(window.location.search);
	const contactoId = urlParams.get('id');

	if (!contactoId) {
		document.body.innerHTML = '<div class="error">ID de contacto no proporcionado</div>';
		return;
	}

	// Cargar datos del contacto y generar acta
	loadActaCierre();

	async function loadActaCierre() {
		try {
			// Cargar contacto y acta en paralelo
			const [contactoRes, actaRes] = await Promise.all([
				fetch(`/wp-json/plg-genesis/v1/contactos/${contactoId}`, {
					headers: { 'X-WP-Nonce': window.plgGenesis?.nonce || '' }
				}),
				fetch(`/wp-json/plg-genesis/v1/diplomas/acta-cierre?contactoId=${contactoId}`, {
					headers: { 'X-WP-Nonce': window.plgGenesis?.nonce || '' }
				})
			]);

			const contacto = await contactoRes.json();
			const acta = await actaRes.json();

			if (!contacto.success || !acta.success) {
				throw new Error('Error cargando datos');
			}

			renderActaCierre(contacto.data, acta.data);
		} catch (error) {
			console.error('Error cargando acta:', error);
			document.body.innerHTML = '<div class="error">Error cargando acta de cierre</div>';
		}
	}

	function renderActaCierre(contacto, acta) {
		const { elegibles, emitidos, pendientes_entrega } = acta;

		const html = `
			<div class="acta-cierre-container">
				<div class="acta-header">
					<h1>📜 Acta de Cierre</h1>
					<div class="contacto-info">
						<strong>${escapeHtml(contacto.nombre)}</strong><br>
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
						<h2>✨ Diplomas Elegibles para Emisión (${elegibles.length})</h2>
						<p>El contacto ha completado los requisitos para los siguientes diplomas:</p>
						<table class="acta-table">
							<thead>
								<tr>
									<th>Programa</th>
									<th>Tipo</th>
									<th>Nivel</th>
									<th>Versión</th>
									<th>Acciones</th>
								</tr>
							</thead>
							<tbody>
								${elegibles.map(d => `
									<tr>
										<td>${escapeHtml(d.programa_nombre)}</td>
										<td><span class="badge badge-info">${d.tipo === 'nivel' ? 'Nivel' : 'Programa Completo'}</span></td>
										<td>${d.nivel_nombre ? escapeHtml(d.nivel_nombre) : 'N/A'}</td>
										<td>${d.version_programa}</td>
										<td>
											<button 
												class="btn btn-success btn-sm" 
												onclick="emitirDesdeActa(${d.programa_id}, ${d.version_programa}, '${d.tipo}', ${d.nivel_id})">
												Emitir
											</button>
										</td>
									</tr>
								`).join('')}
							</tbody>
						</table>
					</div>
				` : ''}

				${pendientes_entrega.length > 0 ? `
					<div class="acta-section pendientes">
						<h2>📋 Diplomas Pendientes de Entrega (${pendientes_entrega.length})</h2>
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
												class="btn btn-primary btn-sm" 
												onclick="registrarEntregaDesdeActa(${d.id})">
												Registrar Entrega
											</button>
										</td>
									</tr>
								`).join('')}
							</tbody>
						</table>
					</div>
				` : ''}

				${emitidos.filter(d => d.entregado).length > 0 ? `
					<div class="acta-section entregados">
						<h2>✅ Diplomas Entregados (${emitidos.filter(d => d.entregado).length})</h2>
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
								${emitidos.filter(d => d.entregado).map(d => `
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

				<div class="acta-actions">
					${elegibles.length > 0 ? `
						<button class="btn btn-success" onclick="emitirTodosDesdeActa()">
							Emitir Todos los Elegibles (${elegibles.length})
						</button>
					` : ''}
					<button class="btn btn-primary" onclick="window.print()">
						🖨️ Imprimir Acta
					</button>
					<button class="btn btn-secondary" onclick="window.history.back()">
						← Volver
					</button>
				</div>
			</div>
		`;

		document.body.innerHTML = html;
	}

	// Funciones globales para acciones desde el HTML
	window.emitirDesdeActa = async function(programaId, version, tipo, nivelId) {
		if (!confirm('¿Desea emitir este diploma?')) return;

		try {
			const response = await fetch('/wp-json/plg-genesis/v1/diplomas/emitir', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': window.plgGenesis?.nonce || ''
				},
				body: JSON.stringify({
					tipo,
					programaId,
					version,
					contactoId: parseInt(contactoId),
					nivelId: nivelId !== 'null' && nivelId ? parseInt(nivelId) : null
				})
			});

			const result = await response.json();
			if (result.success) {
				alert('Diploma emitido exitosamente');
				location.reload();
			} else {
				alert('Error: ' + (result.error?.message || 'No se pudo emitir el diploma'));
			}
		} catch (error) {
			console.error('Error:', error);
			alert('Error emitiendo diploma');
		}
	};

	window.registrarEntregaDesdeActa = async function(diplomaId) {
		const fechaEntrega = prompt('Fecha de entrega (YYYY-MM-DD) o dejar vacío para hoy:');
		const notas = prompt('Notas sobre la entrega (opcional):');

		try {
			const response = await fetch(`/wp-json/plg-genesis/v1/diplomas/${diplomaId}/entrega`, {
				method: 'PUT',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': window.plgGenesis?.nonce || ''
				},
				body: JSON.stringify({
					fechaEntrega: fechaEntrega || null,
					notas: notas || null
				})
			});

			const result = await response.json();
			if (result.success) {
				alert('Entrega registrada exitosamente');
				location.reload();
			} else {
				alert('Error: ' + (result.error?.message || 'No se pudo registrar la entrega'));
			}
		} catch (error) {
			console.error('Error:', error);
			alert('Error registrando entrega');
		}
	};

	window.emitirTodosDesdeActa = async function() {
		if (!confirm('¿Desea emitir todos los diplomas elegibles?')) return;

		try {
			const response = await fetch('/wp-json/plg-genesis/v1/diplomas/emitir-todos', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': window.plgGenesis?.nonce || ''
				},
				body: JSON.stringify({
					contactoId: parseInt(contactoId)
				})
			});

			const result = await response.json();
			if (result.success) {
				const data = result.data;
				alert(`${data.total_emitidos} diploma(s) emitido(s) exitosamente`);
				location.reload();
			} else {
				alert('Error: ' + (result.error?.message || 'No se pudo emitir los diplomas'));
			}
		} catch (error) {
			console.error('Error:', error);
			alert('Error emitiendo diplomas');
		}
	};

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
})();

