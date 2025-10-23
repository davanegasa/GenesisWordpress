/**
 * Componente de Diplomas
 * Maneja visualización y gestión de diplomas para estudiantes/contactos
 */

const DiplomasAPI = {
	/**
	 * Obtiene diplomas elegibles para un destinatario
	 */
	getElegibles: async (estudianteId = null, contactoId = null) => {
		const params = new URLSearchParams();
		if (estudianteId) params.set('estudianteId', estudianteId);
		if (contactoId) params.set('contactoId', contactoId);
		
		const response = await fetch(`/wp-json/plg-genesis/v1/diplomas/elegibles?${params}`, {
			headers: {
				'X-WP-Nonce': window.plgGenesis?.nonce || ''
			}
		});
		return await response.json();
	},

	/**
	 * Lista diplomas emitidos
	 */
	listar: async (estudianteId = null, contactoId = null, pendientesOnly = false) => {
		const params = new URLSearchParams();
		if (estudianteId) params.set('estudianteId', estudianteId);
		if (contactoId) params.set('contactoId', contactoId);
		if (pendientesOnly) params.set('pendientes', 'true');
		
		const response = await fetch(`/wp-json/plg-genesis/v1/diplomas?${params}`, {
			headers: {
				'X-WP-Nonce': window.plgGenesis?.nonce || ''
			}
		});
		return await response.json();
	},

	/**
	 * Emite un diploma
	 */
	emitir: async (tipo, programaId, version, estudianteId = null, contactoId = null, nivelId = null, notas = null) => {
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
				estudianteId,
				contactoId,
				nivelId,
				notas
			})
		});
		return await response.json();
	},

	/**
	 * Registra la entrega física de un diploma
	 */
	registrarEntrega: async (diplomaId, fechaEntrega = null, notas = null) => {
		const response = await fetch(`/wp-json/plg-genesis/v1/diplomas/${diplomaId}/entrega`, {
			method: 'PUT',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': window.plgGenesis?.nonce || ''
			},
			body: JSON.stringify({
				fechaEntrega,
				notas
			})
		});
		return await response.json();
	},

	/**
	 * Obtiene acta de cierre de un contacto
	 */
	getActaCierre: async (contactoId) => {
		const response = await fetch(`/wp-json/plg-genesis/v1/diplomas/acta-cierre?contactoId=${contactoId}`, {
			headers: {
				'X-WP-Nonce': window.plgGenesis?.nonce || ''
			}
		});
		return await response.json();
	},

	/**
	 * Emite todos los diplomas elegibles de un destinatario
	 */
	emitirTodos: async (estudianteId = null, contactoId = null) => {
		const response = await fetch('/wp-json/plg-genesis/v1/diplomas/emitir-todos', {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': window.plgGenesis?.nonce || ''
			},
			body: JSON.stringify({
				estudianteId,
				contactoId
			})
		});
		return await response.json();
	}
};

/**
 * Renderiza la sección de diplomas completa
 */
async function renderDiplomasSection(estudianteId = null, contactoId = null, containerId = 'diplomas-section') {
	const container = document.getElementById(containerId);
	if (!container) {
		console.error('Container no encontrado:', containerId);
		return;
	}

	container.innerHTML = '<div class="loading">Cargando diplomas...</div>';

	try {
		// Cargar datos en paralelo
		const [elegiblesRes, emitidosRes] = await Promise.all([
			DiplomasAPI.getElegibles(estudianteId, contactoId),
			DiplomasAPI.listar(estudianteId, contactoId)
		]);

		if (!elegiblesRes.success || !emitidosRes.success) {
			container.innerHTML = '<div class="error">Error cargando diplomas</div>';
			return;
		}

		const elegibles = elegiblesRes.data;
		const emitidos = emitidosRes.data;
		const pendientesEntrega = emitidos.filter(d => !d.entregado);

		// Renderizar sección
		container.innerHTML = `
			<div class="diplomas-wrapper">
				<div class="diplomas-header">
					<h3>🎓 Diplomas</h3>
					${elegibles.length > 0 ? `
						<button class="btn btn-primary btn-sm" onclick="emitirTodosLosDiplomas(${estudianteId}, ${contactoId})">
							Emitir Todos los Elegibles (${elegibles.length})
						</button>
					` : ''}
				</div>

				${elegibles.length > 0 ? `
					<div class="diplomas-elegibles">
						<h4>✨ Elegibles para Emisión (${elegibles.length})</h4>
						<div class="diplomas-list">
							${elegibles.map(d => renderDiplomaElegible(d, estudianteId, contactoId)).join('')}
						</div>
					</div>
				` : ''}

				${pendientesEntrega.length > 0 ? `
					<div class="diplomas-pendientes">
						<h4>📋 Pendientes de Entrega (${pendientesEntrega.length})</h4>
						<div class="diplomas-list">
							${pendientesEntrega.map(d => renderDiplomaPendiente(d)).join('')}
						</div>
					</div>
				` : ''}

				${emitidos.filter(d => d.entregado).length > 0 ? `
					<div class="diplomas-entregados">
						<h4>✅ Entregados (${emitidos.filter(d => d.entregado).length})</h4>
						<div class="diplomas-list">
							${emitidos.filter(d => d.entregado).map(d => renderDiplomaEntregado(d)).join('')}
						</div>
					</div>
				` : ''}

				${elegibles.length === 0 && emitidos.length === 0 ? `
					<div class="empty-state">
						<p>No hay diplomas disponibles o emitidos.</p>
					</div>
				` : ''}
			</div>
		`;
	} catch (error) {
		console.error('Error renderizando diplomas:', error);
		container.innerHTML = '<div class="error">Error cargando diplomas</div>';
	}
}

function renderDiplomaElegible(diploma, estudianteId, contactoId) {
	const nombreCompleto = diploma.tipo === 'nivel' 
		? `${diploma.programa_nombre} - ${diploma.nivel_nombre}`
		: diploma.programa_nombre;

	return `
		<div class="diploma-card elegible">
			<div class="diploma-icon">🏆</div>
			<div class="diploma-info">
				<div class="diploma-estudiante">
					<strong>${escapeHtml(diploma.estudiante_codigo)}</strong> - ${escapeHtml(diploma.estudiante_nombre)}
				</div>
				<div class="diploma-nombre">${escapeHtml(nombreCompleto)}</div>
				<div class="diploma-tipo">${diploma.tipo === 'nivel' ? 'Nivel' : 'Programa Completo'}</div>
				<div class="diploma-version">Versión ${diploma.version_programa}</div>
			</div>
			<button 
				class="btn btn-success btn-sm" 
				onclick="emitirDiploma(${diploma.programa_id}, ${diploma.version_programa}, '${diploma.tipo}', ${diploma.estudiante_id}, null, ${diploma.nivel_id})">
				Emitir
			</button>
		</div>
	`;
}

function renderDiplomaPendiente(diploma) {
	return `
		<div class="diploma-card pendiente">
			<div class="diploma-icon">📜</div>
			<div class="diploma-info">
				<div class="diploma-estudiante">
					<strong>${escapeHtml(diploma.estudiante_codigo)}</strong> - ${escapeHtml(diploma.estudiante_nombre)}
				</div>
				<div class="diploma-nombre">${escapeHtml(diploma.programa_nombre)}${diploma.nivel_nombre ? ' - ' + escapeHtml(diploma.nivel_nombre) : ''}</div>
				<div class="diploma-tipo">${diploma.tipo === 'nivel' ? 'Nivel' : 'Programa Completo'}</div>
				<div class="diploma-fecha">Emitido: ${formatDate(diploma.fecha_emision)}</div>
				${diploma.notas ? `<div class="diploma-notas">${escapeHtml(diploma.notas)}</div>` : ''}
			</div>
			<button 
				class="btn btn-primary btn-sm" 
				onclick="registrarEntregaDiploma(${diploma.id})">
				Registrar Entrega
			</button>
		</div>
	`;
}

function renderDiplomaEntregado(diploma) {
	return `
		<div class="diploma-card entregado">
			<div class="diploma-icon">✅</div>
			<div class="diploma-info">
				<div class="diploma-estudiante">
					<strong>${escapeHtml(diploma.estudiante_codigo)}</strong> - ${escapeHtml(diploma.estudiante_nombre)}
				</div>
				<div class="diploma-nombre">${escapeHtml(diploma.programa_nombre)}${diploma.nivel_nombre ? ' - ' + escapeHtml(diploma.nivel_nombre) : ''}</div>
				<div class="diploma-tipo">${diploma.tipo === 'nivel' ? 'Nivel' : 'Programa Completo'}</div>
				<div class="diploma-fecha">Emitido: ${formatDate(diploma.fecha_emision)}</div>
				<div class="diploma-fecha">Entregado: ${formatDate(diploma.fecha_entrega)}</div>
				${diploma.notas ? `<div class="diploma-notas">${escapeHtml(diploma.notas)}</div>` : ''}
			</div>
		</div>
	`;
}

/**
 * Emite un diploma individual
 */
async function emitirDiploma(programaId, version, tipo, estudianteId, contactoId, nivelId) {
	if (!confirm('¿Desea emitir este diploma?')) {
		return;
	}

	try {
		const result = await DiplomasAPI.emitir(tipo, programaId, version, estudianteId, contactoId, nivelId);
		
		if (result.success) {
			showToast('Diploma emitido exitosamente', 'success');
			// Recargar la sección de diplomas
			await renderDiplomasSection(estudianteId, contactoId);
		} else {
			showToast(result.error?.message || 'Error emitiendo diploma', 'error');
		}
	} catch (error) {
		console.error('Error emitiendo diploma:', error);
		showToast('Error emitiendo diploma', 'error');
	}
}

/**
 * Emite todos los diplomas elegibles
 */
async function emitirTodosLosDiplomas(estudianteId, contactoId) {
	if (!confirm('¿Desea emitir todos los diplomas elegibles?')) {
		return;
	}

	try {
		const result = await DiplomasAPI.emitirTodos(estudianteId, contactoId);
		
		if (result.success) {
			const data = result.data;
			showToast(`${data.total_emitidos} diploma(s) emitido(s) exitosamente`, 'success');
			
			if (data.total_errores > 0) {
				console.warn('Errores al emitir diplomas:', data.errores);
			}
			
			// Recargar la sección de diplomas
			await renderDiplomasSection(estudianteId, contactoId);
		} else {
			showToast(result.error?.message || 'Error emitiendo diplomas', 'error');
		}
	} catch (error) {
		console.error('Error emitiendo diplomas:', error);
		showToast('Error emitiendo diplomas', 'error');
	}
}

/**
 * Registra la entrega física de un diploma
 */
async function registrarEntregaDiploma(diplomaId) {
	const fechaEntrega = prompt('Fecha de entrega (YYYY-MM-DD) o dejar vacío para hoy:');
	const notas = prompt('Notas sobre la entrega (opcional):');

	try {
		const result = await DiplomasAPI.registrarEntrega(
			diplomaId, 
			fechaEntrega || null, 
			notas || null
		);
		
		if (result.success) {
			showToast('Entrega registrada exitosamente', 'success');
			// Recargar la página actual (puede estar en estudiante o contacto)
			location.reload();
		} else {
			showToast(result.error?.message || 'Error registrando entrega', 'error');
		}
	} catch (error) {
		console.error('Error registrando entrega:', error);
		showToast('Error registrando entrega', 'error');
	}
}

/**
 * Helpers
 */
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

function showToast(message, type = 'info') {
	// Implementación simple de toast
	// Puedes reemplazar esto con tu sistema de notificaciones
	alert(message);
}

// Exportar para uso global
if (typeof window !== 'undefined') {
	window.DiplomasAPI = DiplomasAPI;
	window.renderDiplomasSection = renderDiplomasSection;
	window.emitirDiploma = emitirDiploma;
	window.emitirTodosLosDiplomas = emitirTodosLosDiplomas;
	window.registrarEntregaDiploma = registrarEntregaDiploma;
}

