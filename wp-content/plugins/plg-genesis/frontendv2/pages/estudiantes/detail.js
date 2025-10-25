import { api } from '../../api/client.js';
import { getCatalogs } from '../../services/catalogs.js';
import { openAssignCourseModal } from '../../components/estudiantes/assignCourse.js';
import AuthService from '../../services/auth.js';

function getHashParams(){ const h=location.hash; const q=h.includes('?')?h.split('?')[1]:''; const p=new URLSearchParams(q); const o={}; for(const [k,v] of p.entries()) o[k]=v; return o; }

export async function mount(container, { id } = {}){
	const hp = getHashParams();
	const isContactViewer = AuthService.isContactViewer();
	
	// Determinar URL de retorno
	let backUrl = '#/estudiantes';
	
	if (isContactViewer) {
		// Para contact_viewer, siempre volver al dashboard
		backUrl = '#/';
	} else if (hp.contactoCode) {
		// Si viene desde el detalle de un contacto
		backUrl = '#/contacto/' + encodeURIComponent(hp.contactoCode);
	} else if (hp.contactoId) {
		// Si viene desde la lista de estudiantes filtrada por contacto
		backUrl = '#/estudiantes?contactoId=' + encodeURIComponent(hp.contactoId);
	}
	
	// Para contact_viewer: ocultar botón de guardar
	const saveButtonHtml = !isContactViewer ? '<button id="u-guardar" class="btn btn-primary">Guardar cambios</button>' : '';
	
	container.innerHTML = `
		<div class="card">
			<div class="u-flex u-gap" style="justify-content:space-between;align-items:center;">
				<div class="card-title">Estudiante <span class="badge">${id||''}</span></div>
				<div class="u-flex u-gap">
					<a id="u-back" class="btn btn-secondary" href="${backUrl}">Volver</a>
					${saveButtonHtml}
				</div>
			</div>
			<div id="u-alert" class="alert alert-warning u-hidden">Faltan datos obligatorios (Estado civil o Escolaridad). Por favor complétalos antes de guardar.</div>
			<div id="ed">Cargando...</div>
			<pre id="u-msg" class="u-mt-16"></pre>
		</div>`;
	const $ed = container.querySelector('#ed');
	try {
		const [studentRes, catalogs] = await Promise.all([
			api.get('/estudiantes/'+encodeURIComponent(id)),
			getCatalogs()
		]);
		const d = studentRes && studentRes.data ? studentRes.data : {};
		const civils = catalogs.civilStatus || [];
		const edu = catalogs.educationLevel || [];
		const options = (arr, val) => ['<option value="">Seleccione…</option>'].concat(arr.map(x=>`<option value="${x}" ${val===x?'selected':''}>${x}</option>`)).join('');
        $ed.innerHTML = `
            <div class="contact-card">
                <div class="contact-avatar">${(d.nombre1||d.nombreCompleto||'E').slice(0,1)}</div>
                <div class="contact-body">
                    <div class="contact-title">${d.nombreCompleto || `${d.nombre1||''} ${d.apellido1||''}`.trim()}</div>
                    <div class="contact-sub">Código ${id} · CC ${d.docIdentidad||''}</div>
                    <div class="contact-sub">${d.email||''} · ${d.celular||''}</div>
                </div>
            </div>
            
            <div class="tabs u-mt-16">
                <div class="tab active" data-tab="perfil">👤 Perfil</div>
                <div class="tab" data-tab="academico">📚 Historial Académico</div>
            </div>
            
            <div class="tab-content" data-content="perfil">
                <div id="u-view">
                    <div class="detail-grid">
                        <div class="field-view"><div class="field-label">Estado civil</div><div class="field-value">${d.estadoCivil||'-'}</div></div>
                        <div class="field-view"><div class="field-label">Escolaridad</div><div class="field-value">${d.escolaridad||'-'}</div></div>
                        <div class="field-view"><div class="field-label">Email</div><div class="field-value">${d.email||'-'}</div></div>
                        <div class="field-view"><div class="field-label">Celular</div><div class="field-value">${d.celular||'-'}</div></div>
                        <div class="field-view"><div class="field-label">Ciudad</div><div class="field-value">${d.ciudad||'-'}</div></div>
                        <div class="field-view"><div class="field-label">Iglesia</div><div class="field-value">${d.iglesia||'-'}</div></div>
                        <div class="field-view" style="grid-column:1/-1"><div class="field-label">Ocupación</div><div class="field-value">${d.ocupacion||'-'}</div></div>
                    </div>
                </div>

                <div id="u-edit" class="u-hidden">
                    <div class="section">
                        <div class="section-title">Identificación</div>
                        <div class="form-grid">
                            <label>Documento<input id="u-doc_identidad" class="input" type="text" value="${d.docIdentidad||''}" placeholder="CC/NIT"></label>
                        </div>
                        <div class="divider"></div>
                    </div>

                    <div class="section">
                        <div class="section-title">Nombres</div>
                        <div class="form-grid">
                            <label>Nombre 1<input id="u-nombre1" class="input" type="text" value="${d.nombre1||''}" placeholder="Nombre principal"></label>
                            <label>Nombre 2<input id="u-nombre2" class="input" type="text" value="${d.nombre2||''}" placeholder="Segundo nombre"></label>
                            <label>Apellido 1<input id="u-apellido1" class="input" type="text" value="${d.apellido1||''}" placeholder="Primer apellido"></label>
                            <label>Apellido 2<input id="u-apellido2" class="input" type="text" value="${d.apellido2||''}" placeholder="Segundo apellido"></label>
                        </div>
                        <div class="divider"></div>
                    </div>

                    <div class="section">
                        <div class="section-title">Contacto</div>
                        <div class="form-grid">
                            <label>Email<input id="u-email" class="input" type="email" value="${d.email||''}" placeholder="correo@dominio.com"></label>
                            <label>Celular<input id="u-celular" class="input" type="text" value="${d.celular||''}" placeholder="Número de contacto"></label>
                        </div>
                        <div class="divider"></div>
                    </div>

                    <div class="section">
                        <div class="section-title">Ubicación/Iglesia</div>
                        <div class="form-grid">
                            <label>Ciudad<input id="u-ciudad" class="input" type="text" value="${d.ciudad||''}" placeholder="Ciudad"></label>
                            <label>Iglesia<input id="u-iglesia" class="input" type="text" value="${d.iglesia||''}" placeholder="Iglesia"></label>
                        </div>
                        <div class="divider"></div>
                    </div>

                    <div class="section">
                        <div class="section-title">Perfil</div>
                        <div class="form-grid">
                            <label>Estado civil<select id="u-estado_civil" class="input">${options(civils, d.estadoCivil||'')}</select></label>
                            <label>Escolaridad<select id="u-escolaridad" class="input">${options(edu, d.escolaridad||'')}</select></label>
                            <label>Ocupación<input id="u-ocupacion" class="input" type="text" value="${d.ocupacion||''}" placeholder="Profesión u ocupación"></label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="tab-content u-hidden" data-content="academico">
                <div id="u-academic-section"></div>
            </div>
        `;
        
        // Configurar tabs
        setupTabs(container);
        
        // Cargar historial académico
        const studentName = d.nombreCompleto || `${d.nombre1||''} ${d.apellido1||''}`.trim();
        loadAcademicHistory(container, id, studentName);
        
        // Botón Editar y alternancia de modo (solo para no contact_viewer)
        if (!isContactViewer) {
            const headerActions = container.querySelector('#u-guardar').parentElement;
            const editBtn = document.createElement('button'); editBtn.id = 'u-editar'; editBtn.className = 'btn'; editBtn.textContent = 'Editar';
            headerActions.insertBefore(editBtn, headerActions.firstChild);
            function setMode(isEdit){
                container.querySelector('#u-view').classList.toggle('u-hidden', !!isEdit);
                container.querySelector('#u-edit').classList.toggle('u-hidden', !isEdit);
                container.querySelector('#u-guardar').classList.toggle('u-hidden', !isEdit);
                editBtn.classList.toggle('u-hidden', !!isEdit);
            }
            setMode(false);
            // Asegurar carga de catálogos para selects en modo edición
            const fillOptions = (el, arr, val)=>{ if (!el) return; el.innerHTML = ['<option value="">Seleccione…</option>'].concat(arr.map(x=>`<option value="${x}" ${val===x?'selected':''}>${x}</option>`)).join(''); };
            editBtn.addEventListener('click', ()=>{
                const civEl = container.querySelector('#u-estado_civil');
                const eduEl = container.querySelector('#u-escolaridad');
                if (civEl && civEl.options.length<=1) fillOptions(civEl, civils, d.estadoCivil||'');
                if (eduEl && eduEl.options.length<=1) fillOptions(eduEl, edu, d.escolaridad||'');
            });
            editBtn.addEventListener('click', ()=> setMode(true));
            const showAlert = ()=>{
                const alertEl = container.querySelector('#u-alert');
                const civ = container.querySelector('#u-estado_civil').value;
                const edv = container.querySelector('#u-escolaridad').value;
                const missing = (!civ || !edv);
                alertEl.classList.toggle('u-hidden', !missing);
                const civEl = container.querySelector('#u-estado_civil');
                const eduEl = container.querySelector('#u-escolaridad');
                civEl.classList.toggle('invalid', !civ);
                eduEl.classList.toggle('invalid', !edv);
                civEl.setAttribute('aria-invalid', String(!civ));
                eduEl.setAttribute('aria-invalid', String(!edv));
            };
            showAlert();
            container.querySelector('#u-estado_civil').addEventListener('change', showAlert);
            container.querySelector('#u-escolaridad').addEventListener('change', showAlert);
            ['#u-doc_identidad','#u-nombre1','#u-apellido1'].forEach(sel=>{
                const el = container.querySelector(sel);
                el.addEventListener('input', ()=>{ if (el.value) el.classList.remove('invalid'); });
            });

            container.querySelector('#u-guardar').addEventListener('click', async ()=>{
			const msg = container.querySelector('#u-msg');
			const btn = container.querySelector('#u-guardar');
			// Validación requerida
			const docEl = container.querySelector('#u-doc_identidad');
			const n1El = container.querySelector('#u-nombre1');
			const a1El = container.querySelector('#u-apellido1');
			let hasError = false;
			[docEl, n1El, a1El].forEach(el => { if (!el.value) { el.classList.add('invalid'); hasError = true; } else { el.classList.remove('invalid'); } });
			const civ = container.querySelector('#u-estado_civil');
			const edu = container.querySelector('#u-escolaridad');
			if (!civ.value) { civ.classList.add('invalid'); hasError = true; }
			if (!edu.value) { edu.classList.add('invalid'); hasError = true; }
			if (hasError) { showToast('Por favor completa los campos requeridos', true); return; }

			msg.textContent = 'Guardando...';
			btn.disabled = true; const prev = btn.textContent; btn.textContent = 'Guardando…';
			const payload = {
				doc_identidad: container.querySelector('#u-doc_identidad').value,
				nombre1: container.querySelector('#u-nombre1').value,
				nombre2: container.querySelector('#u-nombre2').value,
				apellido1: container.querySelector('#u-apellido1').value,
				apellido2: container.querySelector('#u-apellido2').value,
				email: container.querySelector('#u-email').value,
				celular: container.querySelector('#u-celular').value,
				ciudad: container.querySelector('#u-ciudad').value,
				iglesia: container.querySelector('#u-iglesia').value,
				estado_civil: container.querySelector('#u-estado_civil').value,
				escolaridad: container.querySelector('#u-escolaridad').value,
				ocupacion: container.querySelector('#u-ocupacion').value,
			};
			try {
				await api.put('/estudiantes/'+encodeURIComponent(id), payload);
				msg.textContent = 'Guardado correctamente';
				showToast('Cambios guardados');
				// Refrescar vista con nuevos valores y volver a modo vista
				d.docIdentidad = payload.doc_identidad;
				d.nombre1 = payload.nombre1; d.nombre2 = payload.nombre2;
				d.apellido1 = payload.apellido1; d.apellido2 = payload.apellido2;
				d.email = payload.email; d.celular = payload.celular;
				d.ciudad = payload.ciudad; d.iglesia = payload.iglesia;
				d.estadoCivil = payload.estado_civil; d.escolaridad = payload.escolaridad; d.ocupacion = payload.ocupacion;
				const view = container.querySelector('#u-view .detail-grid');
				if (view) {
					view.querySelectorAll('.field-view .field-value')[0].textContent = d.estadoCivil || '-';
					view.querySelectorAll('.field-view .field-value')[1].textContent = d.escolaridad || '-';
					view.querySelectorAll('.field-view .field-value')[2].textContent = d.email || '-';
					view.querySelectorAll('.field-view .field-value')[3].textContent = d.celular || '-';
					view.querySelectorAll('.field-view .field-value')[4].textContent = d.ciudad || '-';
					view.querySelectorAll('.field-view .field-value')[5].textContent = d.iglesia || '-';
					view.querySelectorAll('.field-view .field-value')[6].textContent = d.ocupacion || '-';
				}
				const card = container.querySelector('#u-view .contact-title'); if (card) card.textContent = (d.nombre1||'') + ' ' + (d.apellido1||'');
				const cc = container.querySelector('#u-view .contact-sub'); if (cc) cc.textContent = `Código ${id} · CC ${d.docIdentidad||''}`;
				const contSub = container.querySelectorAll('#u-view .contact-sub')[1]; if (contSub) contSub.textContent = `${d.email||''} · ${d.celular||''}`;
				setMode(false);
			} catch(e){
				const errMsg = (e && e.details && e.details.message) || e.message || 'Error al guardar';
				msg.textContent = errMsg;
				showToast('Error al guardar: '+errMsg, true);
			}
			btn.disabled = false; btn.textContent = prev;
		});
        } // Fin if (!isContactViewer)
	} catch (e) {
		$ed.textContent = 'No fue posible cargar el estudiante';
	}
}
export function unmount(){}

// ============ TABS ============

function setupTabs(container) {
	const tabs = container.querySelectorAll('.tab');
	const contents = container.querySelectorAll('.tab-content');
	
	tabs.forEach(tab => {
		tab.addEventListener('click', () => {
			const targetTab = tab.getAttribute('data-tab');
			
			// Remover active de todos los tabs
			tabs.forEach(t => t.classList.remove('active'));
			// Ocultar todos los contenidos
			contents.forEach(c => c.classList.add('u-hidden'));
			
			// Activar tab seleccionado
			tab.classList.add('active');
			// Mostrar contenido correspondiente
			const targetContent = container.querySelector(`[data-content="${targetTab}"]`);
			if (targetContent) {
				targetContent.classList.remove('u-hidden');
			}
		});
	});
}

// ============ HISTORIAL ACADÉMICO ============

async function loadAcademicHistory(container, studentId, studentName) {
	const section = container.querySelector('#u-academic-section');
	if (!section) return;
	
	// Botón de asignar curso solo para no contact_viewer
	const isContactViewer = AuthService.isContactViewer();
	const assignButtonHtml = !isContactViewer ? '<button id="btn-asignar-curso" class="btn btn-primary">+ Asignar Curso</button>' : '';
	
	section.innerHTML = `
		<div class="u-flex u-gap" style="justify-content:space-between;align-items:center;margin-bottom:16px;">
			${assignButtonHtml}
		</div>
		<div id="academic-content">
			<div style="padding:20px;text-align:center;color:var(--plg-mutedText);">Cargando historial...</div>
		</div>
	`;
	
	// Configurar botón de asignar curso (solo si existe)
	if (!isContactViewer) {
		const btnAsignar = section.querySelector('#btn-asignar-curso');
		if (btnAsignar) {
			btnAsignar.addEventListener('click', () => {
				openAssignCourseModal(studentId, studentName, () => {
					// Recargar historial después de asignar curso
					loadAcademicHistory(container, studentId, studentName);
				});
			});
		}
	}
	
	try {
		const response = await api.get(`/estudiantes/${encodeURIComponent(studentId)}/academic-history`);
		const data = response?.data || {};
		renderAcademicHistory(section.querySelector('#academic-content'), data);
	} catch (error) {
		section.querySelector('#academic-content').innerHTML = `
			<div style="padding:20px;text-align:center;color:var(--plg-danger);">
				Error al cargar historial académico
			</div>
		`;
	}
}

function renderAcademicHistory(container, data) {
	if (!data) {
		container.innerHTML = '<div style="padding:20px;color:var(--plg-mutedText);">No hay datos disponibles</div>';
		return;
	}
	
	let html = '';
	
	// Renderizar programas
	if (data.has_programs && data.programs && data.programs.length > 0) {
		data.programs.forEach((program, progIdx) => {
			const progCourses = data.courses_by_program[program.id] || [];
			const stats = calculateProgramStats(progCourses);
			
			html += `
				<div class="program-section">
					<div class="program-header collapsible collapsed" data-program-idx="${progIdx}">
						<div class="u-flex u-gap" style="align-items:center;flex-wrap:wrap;">
							<span class="collapse-icon">▶</span>
							<span class="program-icon">📂</span>
							<span class="program-name">${escapeHtml(program.nombre)}</span>
							<span class="program-badge badge-info" style="background:#6366f1;color:white;">
								v${program.version || 1}
							</span>
							<span class="program-badge ${program.tipo_asignacion === 'heredado' ? 'badge-secondary' : 'badge-primary'}">
								${program.tipo_asignacion === 'heredado' ? '👥 Heredado' : '🎯 Directo'}
							</span>
						</div>
						<div class="progress-container">
							<div class="progress-bar">
								<div class="progress-fill" style="width:${stats.completion}%"></div>
							</div>
							<span class="progress-text">${stats.completed} de ${stats.total} (${stats.completion}%)</span>
						</div>
					</div>
					<div class="program-body" data-program-content="${progIdx}" style="display:none;">
						${renderProgramLevels(progCourses, progIdx)}
					</div>
				</div>
			`;
		});
	}
	
	// Renderizar cursos sin programa
	if (data.standalone_courses && data.standalone_courses.length > 0) {
		const standaloneStats = calculateStandaloneStats(data.standalone_courses);
		html += `
			<div class="standalone-section">
				<div class="section-header collapsible collapsed" data-standalone-toggle="true">
					<span class="collapse-icon">▶</span>
					<span class="section-icon">📋</span>
					<span class="section-title">Cursos individuales (sin programa)</span>
					<span class="level-summary">${standaloneStats.completed} de ${standaloneStats.total} (${standaloneStats.percentage}%)</span>
				</div>
				<div class="standalone-body" data-standalone-content="true" style="display:none;">
					${renderStandaloneLevels(data.standalone_courses)}
				</div>
			</div>
		`;
	}
	
	// Si no hay programas ni cursos, mostrar mensaje
	if (!data.has_programs && (!data.standalone_courses || data.standalone_courses.length === 0)) {
		html = `
			<div class="empty-state">
				<div class="empty-icon">📚</div>
				<div class="empty-title">Sin cursos asignados</div>
				<div class="empty-text">Este estudiante aún no tiene cursos registrados</div>
			</div>
		`;
	}
	
	// Renderizar estadísticas si hay datos
	if (data.statistics && data.statistics.total_cursos > 0) {
		html = `
			<div class="stats-summary">
				<div class="stat-item">
					<div class="stat-value">${data.statistics.total_cursos}</div>
					<div class="stat-label">Cursos</div>
				</div>
				<div class="stat-item">
					<div class="stat-value">${data.statistics.promedio_porcentaje}%</div>
					<div class="stat-label">Promedio</div>
				</div>
				<div class="stat-item">
					<div class="stat-value">${data.statistics.cursos_aprobados}</div>
					<div class="stat-label">Aprobados</div>
				</div>
				<div class="stat-item">
					<div class="stat-value">${data.statistics.ultima_actividad ? formatDate(data.statistics.ultima_actividad) : '-'}</div>
					<div class="stat-label">Última actividad</div>
				</div>
			</div>
		` + html;
	}
	
	container.innerHTML = html;
	
	// Agregar event listeners para colapsar/expandir programas
	container.querySelectorAll('.program-header.collapsible').forEach(header => {
		header.addEventListener('click', () => {
			const progIdx = header.getAttribute('data-program-idx');
			const content = container.querySelector(`[data-program-content="${progIdx}"]`);
			const icon = header.querySelector('.collapse-icon');
			
			if (content.style.display === 'none') {
				content.style.display = 'block';
				icon.textContent = '▼';
				header.classList.remove('collapsed');
			} else {
				content.style.display = 'none';
				icon.textContent = '▶';
				header.classList.add('collapsed');
			}
		});
	});
	
	// Agregar event listeners para colapsar/expandir niveles
	container.querySelectorAll('.level-header.collapsible').forEach(header => {
		header.addEventListener('click', () => {
			const levelId = header.getAttribute('data-level-id');
			const content = container.querySelector(`[data-level-content="${levelId}"]`);
			const icon = header.querySelector('.collapse-icon');
			
			if (content.style.display === 'none') {
				content.style.display = 'block';
				icon.textContent = '▼';
				header.classList.remove('collapsed');
			} else {
				content.style.display = 'none';
				icon.textContent = '▶';
				header.classList.add('collapsed');
			}
		});
	});
	
	// Agregar event listener para colapsar/expandir sección standalone
	const standaloneHeader = container.querySelector('[data-standalone-toggle]');
	if (standaloneHeader) {
		standaloneHeader.addEventListener('click', () => {
			const content = container.querySelector('[data-standalone-content]');
			const icon = standaloneHeader.querySelector('.collapse-icon');
			
			if (content.style.display === 'none') {
				content.style.display = 'block';
				icon.textContent = '▼';
				standaloneHeader.classList.remove('collapsed');
			} else {
				content.style.display = 'none';
				icon.textContent = '▶';
				standaloneHeader.classList.add('collapsed');
			}
		});
	}
}

function renderProgramLevels(levels, progIdx) {
	if (!levels || levels.length === 0) {
		return '<div class="empty-text">No hay cursos en este programa</div>';
	}
	
	return levels.map((level, idx) => {
		const levelStats = calculateLevelStats(level);
		const uniqueId = `prog-${progIdx}-level-${idx}`;
		return `
			<div class="level-section">
				<div class="level-header collapsible collapsed" data-level-id="${uniqueId}">
					<span class="collapse-icon">▶</span>
					<span class="level-icon">${getLevelIcon(level)}</span>
					<span class="level-name">${escapeHtml(level.nivel_nombre)}</span>
					<span class="level-summary">${levelStats.completed} de ${levelStats.total} (${levelStats.percentage}%)</span>
					<span class="level-count">${level.cursos.length} curso${level.cursos.length !== 1 ? 's' : ''}</span>
				</div>
				<div class="courses-list" data-level-content="${uniqueId}" style="display:none;">
					${level.cursos.map(curso => renderCourse(curso)).join('')}
				</div>
			</div>
		`;
	}).join('');
}

function renderStandaloneLevels(levels) {
	if (!levels || levels.length === 0) {
		return '<div class="empty-text">No hay cursos individuales</div>';
	}
	
	return levels.map(level => `
		<div class="level-section-simple">
			<div class="level-name-simple">${escapeHtml(level.nivel_nombre)}</div>
			<div class="courses-list">
				${level.cursos.map(curso => renderCourse(curso)).join('')}
			</div>
		</div>
	`).join('');
}

function renderCourse(curso, isStandalone = false) {
	const hasProgress = curso.inscripcion_id !== null;
	const isCompleted = hasProgress && curso.porcentaje >= 70;
	const isInProgress = hasProgress && curso.porcentaje < 70;
	const isPending = !hasProgress;
	
	let statusIcon = '○';
	let statusClass = 'pending';
	
	if (isCompleted) {
		statusIcon = '✓';
		statusClass = 'completed';
	} else if (isInProgress) {
		statusIcon = '○';
		statusClass = 'in-progress';
	}
	
	// Descripción como título principal (sin truncar o con límite más amplio)
	const descripcion = curso.curso_descripcion ? curso.curso_descripcion : curso.curso_nombre;
	
	return `
		<div class="course-item ${statusClass}">
			<div class="course-status-icon">${statusIcon}</div>
			<div class="course-info">
				<div class="course-title-main">${escapeHtml(descripcion)}</div>
				<div class="course-code">${escapeHtml(curso.curso_nombre)}</div>
				${hasProgress ? `
					<div class="course-meta">
						<span class="course-percentage">${curso.porcentaje}%</span>
						<span class="course-date">${formatDate(curso.fecha_curso)}</span>
					</div>
				` : '<div class="course-meta-pending">Pendiente</div>'}
			</div>
		</div>
	`;
}

function calculateProgramStats(levels) {
	let totalCourses = 0;
	let completedCourses = 0;
	
	levels.forEach(level => {
		if (level.cursos) {
			level.cursos.forEach(curso => {
				totalCourses++;
				if (curso.porcentaje && curso.porcentaje >= 70) {
					completedCourses++;
				}
			});
		}
	});
	
	const completion = totalCourses > 0 ? Math.round((completedCourses / totalCourses) * 100) : 0;
	
	return {
		total: totalCourses,
		completed: completedCourses,
		completion: completion
	};
}

function getLevelIcon(level) {
	const total = level.cursos.length;
	const completed = level.cursos.filter(c => c.porcentaje && c.porcentaje >= 70).length;
	
	if (completed === total) return '✅';
	if (completed > 0) return '🔄';
	return '📘';
}

function formatDate(dateString) {
	if (!dateString) return '-';
	try {
		const date = new Date(dateString);
		return date.toLocaleDateString('es-ES', { year: 'numeric', month: '2-digit', day: '2-digit' });
	} catch {
		return dateString.substring(0, 10);
	}
}

function calculateLevelStats(level) {
	if (!level || !level.cursos) {
		return { total: 0, completed: 0, percentage: 0 };
	}
	
	const total = level.cursos.length;
	const completed = level.cursos.filter(c => c.porcentaje && c.porcentaje >= 70).length;
	const percentage = total > 0 ? Math.round((completed / total) * 100) : 0;
	
	return { total, completed, percentage };
}

function calculateStandaloneStats(levels) {
	let total = 0;
	let completed = 0;
	
	levels.forEach(level => {
		if (level.cursos) {
			level.cursos.forEach(curso => {
				total++;
				if (curso.porcentaje && curso.porcentaje >= 70) {
					completed++;
				}
			});
		}
	});
	
	const percentage = total > 0 ? Math.round((completed / total) * 100) : 0;
	
	return { total, completed, percentage };
}

function truncateText(text, maxLength) {
	if (!text) return '';
	if (text.length <= maxLength) return text;
	return text.substring(0, maxLength) + '...';
}

function escapeHtml(text) {
	if (!text) return '';
	const div = document.createElement('div');
	div.textContent = text;
	return div.innerHTML;
}

// ============ FIN HISTORIAL ACADÉMICO ============

function showToast(text, isError=false){
	const t = document.createElement('div');
	t.className = 'toast';
	if (isError) t.style.borderColor = 'var(--plg-danger)';
	t.textContent = text;
	document.body.appendChild(t);
	setTimeout(()=>{ t.remove(); }, 2500);
}