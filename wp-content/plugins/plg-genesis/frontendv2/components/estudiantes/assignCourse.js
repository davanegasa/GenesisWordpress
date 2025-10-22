import { api } from '../../api/client.js';
import { createModal, showToast } from '../ui/index.js';

/**
 * Abre el modal de asignación de curso para un estudiante
 * @param {string} id - ID del estudiante
 * @param {string} studentName - Nombre del estudiante
 * @param {Function} onSuccess - Callback opcional cuando se asigna exitosamente
 */
export async function openAssignCourseModal(id, studentName, onSuccess = null) {
	const m = createModal({ 
		title: `Asignar Curso — ${studentName || id}`, 
		bodyHtml: `
			<div class="u-flex u-gap">
				<input id="cq" class="input" placeholder="Escribe para buscar un curso..." style="flex:1">
				<input id="cp" class="input" type="number" min="1" max="100" placeholder="Ingrese el porcentaje obtenido (1-100)" style="width:220px">
			</div>
			<div id="cl" class="listbox u-mt-8"></div>
			<div id="cm" class="hint-text u-mt-8"></div>
		`, 
		primaryLabel: 'Asignar Curso', 
		onPrimary: async (close) => {
			const pct = parseInt(document.querySelector('#cp').value || '', 10);
			if (!selected) { 
				document.querySelector('#cm').textContent = 'Selecciona un curso'; 
				return; 
			}
			if (!(pct >= 1 && pct <= 100)) { 
				document.querySelector('#cm').textContent = 'Porcentaje 1-100'; 
				return; 
			}
			
			try { 
				await api.post('/estudiantes/' + encodeURIComponent(id) + '/cursos', { 
					cursoId: selected, 
					porcentaje: pct 
				}); 
				showToast('Curso asignado'); 
				close();
				if (onSuccess) onSuccess();
			} catch(e) { 
				const err = e && (e.details || e.error) || {};
				const extra = (e && e.payload && e.payload.curso_anterior) ? e.payload.curso_anterior : (err.curso_anterior || {});
				
				if (e && (e.status === 409 || err.code === 'course_already_assigned')) {
					// Mostrar modal rico de confirmación con detalles
					const anterior = extra || {};
					const m2 = createModal({ 
						title: 'Curso ya asignado', 
						bodyHtml: `
							<div class="alert alert-warning"><strong>¡Atención!</strong> Este estudiante ya tiene asignado el curso seleccionado</div>
							<div style="font-weight:600;margin:12px 0 4px;">Nuevo intento</div>
							<div class="detail-grid">
								<div class="field-view"><div class="field-label">Estudiante</div><div class="field-value">${studentName || id}</div></div>
								<div class="field-view"><div class="field-label">Curso</div><div class="field-value">${(cursos.find(c => c.id === selected)?.nombre) || selected}</div></div>
								<div class="field-view"><div class="field-label">Nueva nota</div><div class="field-value">${pct}%</div></div>
							</div>
							<div class="divider"></div>
							<div style="font-weight:600;margin:12px 0 4px;">Registro anterior</div>
							<div class="detail-grid">
								<div class="field-view"><div class="field-label">Nota anterior</div><div class="field-value">${(anterior.porcentaje != null ? anterior.porcentaje + '%' : 'N/A')}</div></div>
								<div class="field-view"><div class="field-label">Fecha anterior</div><div class="field-value">${anterior.fecha ? String(anterior.fecha).substring(0, 10) : 'N/A'}</div></div>
							</div>
							<div class="hint-text u-mt-8">Al confirmar, se creará un nuevo registro para este intento del curso.</div>
						`, 
						primaryLabel: 'Repetir Curso', 
						onPrimary: async (close2) => { 
							try { 
								await api.post('/estudiantes/' + encodeURIComponent(id) + '/cursos', { 
									cursoId: selected, 
									porcentaje: pct, 
									forzar: true 
								}); 
								showToast('Repetido'); 
								close2(); 
								close();
								if (onSuccess) onSuccess();
							} catch(_) { 
								document.querySelector('#cm').textContent = 'Error'; 
							}
						}, 
						secondaryLabel: 'Cancelar' 
					});
					document.body.appendChild(m2.overlay);
				} else { 
					document.querySelector('#cm').textContent = 'Error'; 
				}
			}
		}, 
		secondaryLabel: 'Cancelar' 
	});
	
	document.body.appendChild(m.overlay);
	
	const $cq = document.querySelector('#cq');
	const $cl = document.querySelector('#cl');
	let cursos = [];
	let selected = null;
	
	async function l(f = '') { 
		const r = await api.get('/cursos?q=' + encodeURIComponent(f)); 
		cursos = (r && r.data && r.data.items) || [];
		$cl.innerHTML = '';
		cursos.forEach(c => { 
			const el = document.createElement('div');
			el.className = 'listbox-item';
			el.textContent = c.nombre;
			el.onclick = () => { 
				selected = c.id;
				Array.from($cl.children).forEach(x => x.classList.remove('selected'));
				el.classList.add('selected');
			};
			$cl.appendChild(el);
		});
		if (!$cl.children.length) { 
			$cl.innerHTML = '<div class="listbox-item">Sin resultados</div>';
		}
	}
	
	$cq.oninput = () => l($cq.value || '');
	l('');
	$cq.focus();
}

