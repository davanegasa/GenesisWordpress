import { api } from '../../api/client.js';
import * as ProximosCompletar from '../../components/proximos-completar.js';

// Variable global para almacenar los programas del contacto
let contactPrograms = [];
let currentContactCode = null;
let currentContactName = null;

export async function mount(container, { code } = {}){
    container.innerHTML = `
        <div class="card">
            <div class="u-flex u-gap" style="justify-content:space-between;align-items:center;">
                <div class="card-title">Contacto</div>
                <div class="u-flex u-gap">
                    <a id="c-back" class="btn btn-secondary" href="#/contactos">Volver</a>
                    <button id="c-save" class="btn btn-primary">Guardar cambios</button>
                </div>
            </div>
            <div id="ct">Cargando...</div>
            <pre id="c-msg" class="u-mt-16"></pre>
        </div>`;

    const $ct = container.querySelector('#ct');
    try{
        const res = await api.get('/contactos/'+encodeURIComponent(code.trim()));
        const d = (res && res.data) || {};
        
        $ct.innerHTML = `
            <div class="contact-card">
                <div class="contact-avatar">${(d.nombre||'C').slice(0,1)}</div>
                <div class="contact-body">
                    <div class="contact-title">${d.nombre||'-'}</div>
                    <div class="contact-sub">Código ${d.code||code} · ${d.iglesia||''}</div>
                    <div class="contact-sub">${d.email||''} · ${d.celular||''}</div>
                </div>
            </div>
            
            <div class="tabs u-mt-16">
                <div class="tab active" data-tab="perfil">👤 Perfil</div>
                <div class="tab" data-tab="programas">📂 Programas</div>
                <div class="tab" data-tab="estudiantes">👥 Estudiantes</div>
                <div class="tab" data-tab="diplomas">🎓 Diplomas</div>
                <div class="tab" data-tab="actas">📋 Actas</div>
                <div class="tab" data-tab="proximos">🔥 Por Completar</div>
            </div>
            
            <div class="tab-content" data-content="perfil">
                <div id="c-view">
                    <div class="detail-grid">
                        <div class="field-view"><div class="field-label">Dirección</div><div class="field-value">${d.direccion||'-'}</div></div>
                        <div class="field-view"><div class="field-label">Ciudad</div><div class="field-value">${d.ciudad||'-'}</div></div>
                        <div class="field-view"><div class="field-label">Email</div><div class="field-value">${d.email||'-'}</div></div>
                        <div class="field-view"><div class="field-label">Celular</div><div class="field-value">${d.celular||'-'}</div></div>
                        <div class="field-view"><div class="field-label">Iglesia</div><div class="field-value">${d.iglesia||'-'}</div></div>
                    </div>
                </div>
                <div id="c-edit" class="u-hidden">
                    <div class="section">
                        <div class="section-title">Datos</div>
                        <div class="form-grid">
                            <label>Nombre<input id="c-nombre" class="input" type="text" value="${d.nombre||''}"></label>
                            <label>Iglesia<input id="c-iglesia" class="input" type="text" value="${d.iglesia||''}"></label>
                            <label>Email<input id="c-email" class="input" type="email" value="${d.email||''}"></label>
                            <label>Celular<input id="c-celular" class="input" type="text" value="${d.celular||''}"></label>
                            <label>Dirección<input id="c-direccion" class="input" type="text" value="${d.direccion||''}"></label>
                            <label>Ciudad<input id="c-ciudad" class="input" type="text" value="${d.ciudad||''}"></label>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="tab-content u-hidden" data-content="programas">
                <div id="c-programs-section"></div>
            </div>
            
            <div class="tab-content u-hidden" data-content="estudiantes">
                <div id="c-students-section"></div>
            </div>
            
            <div class="tab-content u-hidden" data-content="diplomas">
                <div style="margin-bottom: 15px;">
                    <a href="#/contacto/${encodeURIComponent(code)}/acta-cierre" class="btn btn-primary">
                        📜 Ver Acta de Cierre Completa
                    </a>
                </div>
                <div id="c-diplomas-section">Cargando diplomas...</div>
            </div>
            
            <div class="tab-content u-hidden" data-content="actas">
                <div id="c-actas-section">Cargando actas...</div>
            </div>
            
            <div class="tab-content u-hidden" data-content="proximos">
                <div id="c-proximos-section"></div>
            </div>
        `;
        
        // Configurar tabs
        setupTabs(container, d.id, code.trim());
        
        // Cargar historial académico
        loadAcademicHistory(container, code.trim(), d.nombre || 'Contacto');
        
        const header = container.querySelector('#c-save').parentElement;
        const editBtn = document.createElement('button'); 
        editBtn.id='c-edit-btn'; 
        editBtn.className='btn'; 
        editBtn.textContent='Editar'; 
        header.insertBefore(editBtn, header.firstChild);
        
        function setMode(edit){
            container.querySelector('#c-view').classList.toggle('u-hidden', !!edit);
            container.querySelector('#c-edit').classList.toggle('u-hidden', !edit);
            container.querySelector('#c-save').classList.toggle('u-hidden', !edit);
            editBtn.classList.toggle('u-hidden', !!edit);
        }
        setMode(false);
        editBtn.addEventListener('click', ()=> setMode(true));

        container.querySelector('#c-save').addEventListener('click', async ()=>{
            const msg = container.querySelector('#c-msg');
            msg.textContent = 'Guardando...';
            const payload = {
                nombre: container.querySelector('#c-nombre').value,
                iglesia: container.querySelector('#c-iglesia').value,
                email: container.querySelector('#c-email').value,
                celular: container.querySelector('#c-celular').value,
                direccion: container.querySelector('#c-direccion').value,
                ciudad: container.querySelector('#c-ciudad').value,
            };
            try{
                await api.put('/contactos/'+encodeURIComponent(code.trim()), payload);
                msg.textContent = 'Guardado';
                // Sync view
                container.querySelector('#c-view .contact-title').textContent = payload.nombre || '-';
                container.querySelectorAll('#c-view .contact-sub')[0].textContent = 'Código '+code+' · '+(payload.iglesia||'');
                container.querySelectorAll('#c-view .contact-sub')[1].textContent = (payload.email||'')+' · '+(payload.celular||'');
                const values = container.querySelectorAll('#c-view .field-view .field-value');
                values[0].textContent = payload.direccion || '-';
                values[1].textContent = payload.ciudad || '-';
                values[2].textContent = payload.email || '-';
                values[3].textContent = payload.celular || '-';
                values[4].textContent = payload.iglesia || '-';
                setMode(false);
            } catch(e){
                msg.textContent = (e && e.details && e.details.message) || e.message || 'Error al guardar';
            }
        });
    } catch(e){
        $ct.textContent = 'No fue posible cargar el contacto';
    }
}

export function unmount(){}

// ============ TABS ============

function setupTabs(container, contactoId, contactCode) {
    const tabs = container.querySelectorAll('.tab');
    const contents = container.querySelectorAll('.tab-content');
    let diplomasLoaded = false;
    let actasLoaded = false;
    let proximosLoaded = false;
    
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
            
            // Cargar diplomas cuando se activa la tab (lazy loading)
            if (targetTab === 'diplomas' && !diplomasLoaded && contactoId) {
                diplomasLoaded = true;
                loadDiplomas(container, contactoId, contactCode);
            }
            
            // Cargar actas cuando se activa la tab (lazy loading)
            if (targetTab === 'actas' && !actasLoaded && contactoId) {
                actasLoaded = true;
                loadActas(container, contactoId, contactCode);
            }
            
            // Cargar próximos a completar cuando se activa la tab (lazy loading)
            if (targetTab === 'proximos' && !proximosLoaded && contactoId) {
                proximosLoaded = true;
                loadProximosCompletar(container, contactoId);
            }
        });
    });
}

// ============ HISTORIAL ACADÉMICO ============

async function loadAcademicHistory(container, contactCode, contactName) {
    const programsSection = container.querySelector('#c-programs-section');
    const studentsSection = container.querySelector('#c-students-section');
    
    // Guardar para recargas posteriores
    currentContactCode = contactCode;
    currentContactName = contactName;
    
    if (!programsSection || !studentsSection) return;
    
    programsSection.innerHTML = '<div style="padding:20px;text-align:center;color:var(--plg-mutedText);">Cargando programas...</div>';
    studentsSection.innerHTML = '<div style="padding:20px;text-align:center;color:var(--plg-mutedText);">Cargando estudiantes...</div>';
    
    
    
    try {
        const response = await api.get(`/contactos/${encodeURIComponent(contactCode)}/academic-history`);
        const data = response?.data || {};
        
        // Guardar programas para uso en otros componentes
        contactPrograms = data.programs || [];
        
        // Renderizar programas
        renderPrograms(programsSection, data);
        
        // Renderizar estudiantes heredados
        renderInheritedStudents(studentsSection, data, contactCode);
        
    } catch (error) {
        programsSection.innerHTML = `
            <div style="padding:20px;text-align:center;color:var(--plg-danger);">
                Error al cargar programas
            </div>
        `;
        studentsSection.innerHTML = `
            <div style="padding:20px;text-align:center;color:var(--plg-danger);">
                Error al cargar estudiantes
            </div>
        `;
    }
}

function renderPrograms(container, data) {
    const programs = data.programs || [];
    const stats = data.statistics || {};
    
    if (programs.length === 0) {
        container.innerHTML = '<div style="padding:20px;color:var(--plg-mutedText);">No hay programas asignados a este contacto</div>';
        return;
    }
    
    let html = `
        <div style="margin-bottom:16px;">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">${stats.total_programas || 0}</div>
                    <div class="stat-label">Programas</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">${stats.total_cursos_programa || 0}</div>
                    <div class="stat-label">Cursos en Programas</div>
                </div>
            </div>
        </div>
    `;
    
    programs.forEach((program, progIdx) => {
        const totalCursos = program.cursos?.reduce((sum, level) => sum + (level.cursos?.length || 0), 0) || 0;
        const isActivo = program.activo !== false; // Por defecto activo si no viene el campo
        const inactivoStyle = !isActivo ? 'opacity: 0.5; filter: grayscale(70%);' : '';
        const inactivoBadge = !isActivo ? '<span class="program-badge badge-warning" style="background:#dc3545;color:white;font-size:0.75rem;padding:2px 8px;">INACTIVO</span>' : '';
        
        html += `
            <div class="program-section" style="${inactivoStyle}">
                <div class="program-header collapsible collapsed" data-program-idx="${progIdx}" style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;">
                    <div class="u-flex u-gap" style="align-items:center;flex-wrap:wrap;flex:1;">
                        <span class="collapse-icon">▶</span>
                        <span class="program-icon">📂</span>
                        <span class="program-name">${escapeHtml(program.programa_nombre)}</span>
                        <span class="program-badge badge-info" style="background:#6366f1;color:white;font-size:0.75rem;padding:2px 8px;">
                            v${program.version || 1}
                        </span>
                        ${inactivoBadge}
                        <span class="level-summary">${totalCursos} curso${totalCursos !== 1 ? 's' : ''}</span>
                    </div>
                    <a 
                        href="javascript:void(0)"
                        onclick="mostrarModalTogglePrograma(event, ${program.asignacion_id}, ${isActivo}, '${escapeHtml(program.programa_nombre).replace(/'/g, "\\'")}', ${progIdx})"
                        title="${isActivo ? 'Desactivar programa' : 'Activar programa'}"
                        style="
                            color:${isActivo ? '#dc3545' : '#28a745'};
                            font-size:0.75rem;
                            text-decoration:none;
                            margin-left:12px;
                            opacity:0.6;
                            transition:opacity 0.2s;
                            white-space:nowrap;
                        "
                        onmouseover="this.style.opacity='1'"
                        onmouseout="this.style.opacity='0.6'">
                        ${isActivo ? '⚙️ desactivar' : '⚙️ activar'}
                    </a>
                </div>
                <div class="program-body" data-program-content="${progIdx}" style="display:none;">
                    ${renderProgramLevels(program.cursos || [], progIdx)}
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
    
    // Event listeners para colapsar/expandir programas
    container.querySelectorAll('.program-header.collapsible').forEach(header => {
        header.addEventListener('click', (e) => {
            // No expandir/colapsar si se clickeó el botón
            if (e.target.tagName === 'BUTTON' || e.target.closest('button')) {
                return;
            }
            
            const progIdx = header.getAttribute('data-program-idx');
            const body = container.querySelector(`[data-program-content="${progIdx}"]`);
            const isCollapsed = header.classList.contains('collapsed');
            
            if (isCollapsed) {
                header.classList.remove('collapsed');
                body.style.display = 'block';
            } else {
                header.classList.add('collapsed');
                body.style.display = 'none';
            }
        });
    });
    
    // Event listeners para niveles
    container.querySelectorAll('.level-header.collapsible').forEach(header => {
        header.addEventListener('click', () => {
            const levelId = header.getAttribute('data-level-id');
            const body = container.querySelector(`[data-level-content="${levelId}"]`);
            const isCollapsed = header.classList.contains('collapsed');
            
            if (isCollapsed) {
                header.classList.remove('collapsed');
                body.style.display = 'block';
            } else {
                header.classList.add('collapsed');
                body.style.display = 'none';
            }
        });
    });
}

function renderProgramLevels(levels, progIdx) {
    if (!levels || levels.length === 0) {
        return '<div class="empty-text">No hay cursos en este programa</div>';
    }
    
    return levels.map((level, idx) => {
        const uniqueId = `prog-${progIdx}-level-${idx}`;
        const totalCursos = level.cursos?.length || 0;
        
        return `
            <div class="level-section">
                <div class="level-header collapsible collapsed" data-level-id="${uniqueId}">
                    <span class="collapse-icon">▶</span>
                    <span class="level-icon">${getLevelIcon(level)}</span>
                    <span class="level-name">${escapeHtml(level.nivel_nombre)}</span>
                    <span class="level-count">${totalCursos} curso${totalCursos !== 1 ? 's' : ''}</span>
                </div>
                <div class="courses-list" data-level-content="${uniqueId}" style="display:none;">
                    ${level.cursos.map(curso => renderCourseStructure(curso)).join('')}
                </div>
            </div>
        `;
    }).join('');
}

function renderCourseStructure(curso) {
    // Descripción como título principal
    const descripcion = curso.curso_descripcion ? curso.curso_descripcion : curso.curso_nombre;
    
    return `
        <div class="course-item structure">
            <div class="course-status-icon">📖</div>
            <div class="course-info">
                <div class="course-title-main">${escapeHtml(descripcion)}</div>
                <div class="course-code">${escapeHtml(curso.curso_nombre)}</div>
            </div>
        </div>
    `;
}

function renderInheritedStudents(container, data, contactCode) {
    const students = data.inherited_students || [];
    const stats = data.statistics || {};
    
    
    
    if (students.length === 0) {
        container.innerHTML = '<div style="padding:20px;color:var(--plg-mutedText);">No hay estudiantes heredando de este contacto</div>';
        return;
    }
    
    let html = `
        <div style="margin-bottom:16px;">
            <div class="stat-card">
                <div class="stat-value">${stats.total_estudiantes || 0}</div>
                <div class="stat-label">Estudiantes Heredados</div>
            </div>
        </div>
        <div class="students-grid">
    `;
    
    students.forEach(student => {
        const studentUrl = `#/estudiante/${encodeURIComponent(student.id_estudiante)}?contactoCode=${encodeURIComponent(contactCode)}`;
        html += `
            <div class="student-card" onclick="location.hash='${studentUrl}'">
                <div class="student-header">
                    <div class="student-avatar">${(student.nombre_completo || 'E').charAt(0)}</div>
                    <div class="student-info">
                        <div class="student-name">${escapeHtml(student.nombre_completo)}</div>
                        <div class="student-code">${student.id_estudiante}</div>
                    </div>
                </div>
                <div class="student-stats">
                    <div class="stat-item">
                        <span class="stat-label">Cursos</span>
                        <span class="stat-value">${student.total_cursos}</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Promedio</span>
                        <span class="stat-value">${student.promedio_porcentaje}%</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Últ. Act.</span>
                        <span class="stat-value">${student.ultima_actividad ? formatDate(student.ultima_actividad) : '-'}</span>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

// ============ DIPLOMAS ============

async function loadDiplomas(container, contactoId, contactCode) {
    const diplomasSection = container.querySelector('#c-diplomas-section');
    if (!diplomasSection) return;

    try {
        // Cargar elegibles y emitidos en paralelo
        const [elegiblesRes, emitidosRes] = await Promise.all([
            api.get(`/diplomas/elegibles?contactoId=${contactoId}`),
            api.get(`/diplomas?contactoId=${contactoId}`)
        ]);

        const elegibles = elegiblesRes?.data || [];
        const emitidos = emitidosRes?.data || [];
        const pendientesEntrega = emitidos.filter(d => !d.entregado);
        const entregados = emitidos.filter(d => d.entregado);

        let html = '<div class="diplomas-wrapper">';

        // Elegibles - Agrupados por programa y nivel
        if (elegibles.length > 0) {
            // Agrupar por programa y nivel
            const elegiblesPorDiploma = elegibles.reduce((acc, d) => {
                const key = d.tipo === 'nivel' 
                    ? `${d.programa_id}_${d.nivel_id}_${d.tipo}_${d.version_programa}`
                    : `${d.programa_id}_${d.tipo}_${d.version_programa}`;
                if (!acc[key]) {
                    acc[key] = {
                        tipo: d.tipo,
                        programa_id: d.programa_id,
                        programa_nombre: d.programa_nombre,
                        nivel_id: d.nivel_id,
                        nivel_nombre: d.nivel_nombre,
                        version_programa: d.version_programa,
                        estudiantes: []
                    };
                }
                acc[key].estudiantes.push({
                    estudiante_id: d.estudiante_id,
                    estudiante_codigo: d.estudiante_codigo,
                    estudiante_nombre: d.estudiante_nombre
                });
                return acc;
            }, {});

            html += `
                <div class="section u-mb-16">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                        <div class="section-title" style="color: #28a745; margin: 0;">✨ Elegibles para Emisión (${elegibles.length})</div>
                        <div>
                            <button class="btn btn-sm btn-secondary" onclick="toggleSeleccionTodos()" style="margin-right: 10px;">
                                ☑️ <span id="toggle-sel-text">Seleccionar Todos</span>
                            </button>
                            <button class="btn btn-success" onclick="emitirDiplomasSeleccionados()" id="btn-emitir-sel">
                                ✅ Emitir Seleccionados (<span id="count-sel">0</span>)
                            </button>
                        </div>
                    </div>
                    <div class="u-flex u-gap" style="flex-direction: column;">
                        ${Object.values(elegiblesPorDiploma).map((grupo, idx) => {
                            const nombreCompleto = grupo.tipo === 'nivel' 
                                ? `${escapeHtml(grupo.programa_nombre)} - ${escapeHtml(grupo.nivel_nombre)}`
                                : `${escapeHtml(grupo.programa_nombre)} (Completo)`;
                            const collapseId = `elegible-${idx}`;
                            return `
                                <div class="card" style="background: #f8fff9; border: 2px solid #28a745;">
                                    <div 
                                        style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 12px; margin-bottom: 12px; border-bottom: 1px solid #d4edda; cursor: pointer;" 
                                        onclick="toggleCollapse('${collapseId}')">
                                        <div style="display: flex; align-items: center; gap: 10px; flex: 1;">
                                            <div style="font-size: 1.5rem;">🎓</div>
                                            <div style="flex: 1;">
                                                <div style="font-weight: 700; font-size: 1rem; color: #28a745;">
                                                    ${nombreCompleto}
                                                </div>
                                                <div style="font-size: 0.85rem; color: #666;">
                                                    ${grupo.tipo === 'nivel' ? 'Nivel' : 'Programa Completo'} • Versión ${grupo.version_programa} • ${grupo.estudiantes.length} estudiante${grupo.estudiantes.length > 1 ? 's' : ''}
                                                </div>
                                            </div>
                                        </div>
                                        <div id="${collapseId}-icon" style="font-size: 1.2rem; color: #28a745; transition: transform 0.2s; transform: rotate(-90deg);">▼</div>
                                    </div>
                                    <div id="${collapseId}" class="u-flex u-gap" style="flex-direction: column; gap: 8px; display: none;">
                                        ${grupo.estudiantes.map((est, estIdx) => `
                                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background: white; border-radius: 4px; border-left: 3px solid #28a745;">
                                                <div style="display: flex; align-items: center; gap: 12px;">
                                                    <input 
                                                        type="checkbox" 
                                                        class="dip-checkbox" 
                                                        data-tipo="${grupo.tipo}"
                                                        data-programa-id="${grupo.programa_id}"
                                                        data-version="${grupo.version_programa}"
                                                        data-estudiante-id="${est.estudiante_id}"
                                                        data-nivel-id="${grupo.nivel_id || ''}"
                                                        onchange="updateContadorSel()"
                                                        style="width: 18px; height: 18px; cursor: pointer;"
                                                    />
                                                    <div style="font-size: 1.2rem;">👤</div>
                                                    <div>
                                                        <div style="font-weight: 600; color: #007bff;">
                                                            ${escapeHtml(est.estudiante_codigo)}
                                                        </div>
                                                        <div style="font-size: 0.9rem; color: #666;">
                                                            ${escapeHtml(est.estudiante_nombre)}
                                                        </div>
                                                    </div>
                                                </div>
                                                <button 
                                                    class="btn btn-success btn-sm" 
                                                    onclick="emitirDiplomaContacto(${grupo.programa_id}, ${grupo.version_programa}, '${grupo.tipo}', ${est.estudiante_id}, ${grupo.nivel_id || 'null'})">
                                                    Emitir
                                                </button>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            `;
                        }).join('')}
                    </div>
                </div>
            `;
        }

        // Pendientes de entrega - Agrupados por programa y nivel
        if (pendientesEntrega.length > 0) {
            // Agrupar por programa y nivel
            const pendientesPorDiploma = pendientesEntrega.reduce((acc, d) => {
                const key = d.tipo === 'nivel' 
                    ? `${d.programa_id}_${d.nivel_id}_${d.tipo}_${d.version_programa}`
                    : `${d.programa_id}_${d.tipo}_${d.version_programa}`;
                if (!acc[key]) {
                    acc[key] = {
                        tipo: d.tipo,
                        programa_id: d.programa_id,
                        programa_nombre: d.programa_nombre,
                        nivel_id: d.nivel_id,
                        nivel_nombre: d.nivel_nombre,
                        version_programa: d.version_programa,
                        diplomas: []
                    };
                }
                acc[key].diplomas.push(d);
                return acc;
            }, {});

            html += `
                <div class="section u-mb-16">
                    <div class="section-title" style="color: #ffc107;">📋 Pendientes de Entrega (${pendientesEntrega.length})</div>
                    <div class="u-flex u-gap" style="flex-direction: column;">
                        ${Object.values(pendientesPorDiploma).map((grupo, idx) => {
                            const nombreCompleto = grupo.tipo === 'nivel' 
                                ? `${escapeHtml(grupo.programa_nombre)} - ${escapeHtml(grupo.nivel_nombre)}`
                                : `${escapeHtml(grupo.programa_nombre)} (Completo)`;
                            const collapseId = `pendiente-${idx}`;
                            return `
                                <div class="card" style="background: #fffef8; border: 2px solid #ffc107;">
                                    <div 
                                        style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 12px; margin-bottom: 12px; border-bottom: 1px solid #fff3cd; cursor: pointer;"
                                        onclick="toggleCollapse('${collapseId}')">
                                        <div style="display: flex; align-items: center; gap: 10px; flex: 1;">
                                            <div style="font-size: 1.5rem;">📜</div>
                                            <div style="flex: 1;">
                                                <div style="font-weight: 700; font-size: 1rem; color: #ffc107;">
                                                    ${nombreCompleto}
                                                </div>
                                                <div style="font-size: 0.85rem; color: #666;">
                                                    ${grupo.tipo === 'nivel' ? 'Nivel' : 'Programa Completo'} • Versión ${grupo.version_programa} • ${grupo.diplomas.length} diploma${grupo.diplomas.length > 1 ? 's' : ''}
                                                </div>
                                            </div>
                                        </div>
                                        <div id="${collapseId}-icon" style="font-size: 1.2rem; color: #ffc107; transition: transform 0.2s; transform: rotate(-90deg);">▼</div>
                                    </div>
                                    <div id="${collapseId}" class="u-flex u-gap" style="flex-direction: column; gap: 8px; display: none;">
                                        ${grupo.diplomas.map(d => `
                                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background: white; border-radius: 4px; border-left: 3px solid #ffc107;">
                                                <div style="flex: 1;">
                                                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                                        <div style="font-size: 1rem;">👤</div>
                                                        <div>
                                                            <span style="font-weight: 600; color: #007bff;">${escapeHtml(d.estudiante_codigo)}</span>
                                                            <span style="color: #666;"> - ${escapeHtml(d.estudiante_nombre)}</span>
                                                        </div>
                                                    </div>
                                                    <div style="font-size: 0.85rem; color: #666; padding-left: 28px;">
                                                        Emitido: ${formatDate(d.fecha_emision)}
                                                    </div>
                                                    ${d.notas ? `<div style="font-size: 0.85rem; color: #999; margin-top: 4px; padding-left: 28px;">${escapeHtml(d.notas)}</div>` : ''}
                                                </div>
                                                <button 
                                                    class="btn btn-primary btn-sm" 
                                                    onclick="registrarEntregaContacto(${d.id})">
                                                    Registrar Entrega
                                                </button>
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            `;
                        }).join('')}
                    </div>
                </div>
            `;
        }

        // Entregados - Agrupados por programa y nivel
        if (entregados.length > 0) {
            // Agrupar por programa y nivel
            const entregadosPorDiploma = entregados.reduce((acc, d) => {
                const key = d.tipo === 'nivel' 
                    ? `${d.programa_id}_${d.nivel_id}_${d.tipo}_${d.version_programa}`
                    : `${d.programa_id}_${d.tipo}_${d.version_programa}`;
                if (!acc[key]) {
                    acc[key] = {
                        tipo: d.tipo,
                        programa_id: d.programa_id,
                        programa_nombre: d.programa_nombre,
                        nivel_id: d.nivel_id,
                        nivel_nombre: d.nivel_nombre,
                        version_programa: d.version_programa,
                        diplomas: []
                    };
                }
                acc[key].diplomas.push(d);
                return acc;
            }, {});

            html += `
                <div class="section u-mb-16">
                    <div class="section-title" style="color: #6c757d;">✅ Entregados (${entregados.length})</div>
                    <div class="u-flex u-gap" style="flex-direction: column;">
                        ${Object.values(entregadosPorDiploma).map((grupo, idx) => {
                            const nombreCompleto = grupo.tipo === 'nivel' 
                                ? `${escapeHtml(grupo.programa_nombre)} - ${escapeHtml(grupo.nivel_nombre)}`
                                : `${escapeHtml(grupo.programa_nombre)} (Completo)`;
                            const collapseId = `entregado-${idx}`;
                            return `
                                <div class="card" style="background: #f8f9fa; border: 2px solid #6c757d; opacity: 0.9;">
                                    <div 
                                        style="display: flex; align-items: center; justify-content: space-between; padding-bottom: 12px; margin-bottom: 12px; border-bottom: 1px solid #dee2e6; cursor: pointer;"
                                        onclick="toggleCollapse('${collapseId}')">
                                        <div style="display: flex; align-items: center; gap: 10px; flex: 1;">
                                            <div style="font-size: 1.5rem;">✅</div>
                                            <div style="flex: 1;">
                                                <div style="font-weight: 700; font-size: 1rem; color: #6c757d;">
                                                    ${nombreCompleto}
                                                </div>
                                                <div style="font-size: 0.85rem; color: #666;">
                                                    ${grupo.tipo === 'nivel' ? 'Nivel' : 'Programa Completo'} • Versión ${grupo.version_programa} • ${grupo.diplomas.length} diploma${grupo.diplomas.length > 1 ? 's' : ''}
                                                </div>
                                            </div>
                                        </div>
                                        <div id="${collapseId}-icon" style="font-size: 1.2rem; color: #6c757d; transition: transform 0.2s;">▼</div>
                                    </div>
                                    <div id="${collapseId}" class="u-flex u-gap" style="flex-direction: column; gap: 8px; display: none;">
                                        ${grupo.diplomas.map(d => `
                                            <div style="padding: 10px; background: white; border-radius: 4px; border-left: 3px solid #6c757d;">
                                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                                    <div style="font-size: 1rem;">👤</div>
                                                    <div>
                                                        <span style="font-weight: 600; color: #007bff;">${escapeHtml(d.estudiante_codigo)}</span>
                                                        <span style="color: #666;"> - ${escapeHtml(d.estudiante_nombre)}</span>
                                                    </div>
                                                </div>
                                                <div style="font-size: 0.85rem; color: #666; padding-left: 28px;">
                                                    Emitido: ${formatDate(d.fecha_emision)} • Entregado: ${formatDate(d.fecha_entrega)}
                                                </div>
                                                ${d.notas ? `<div style="font-size: 0.85rem; color: #999; margin-top: 4px; padding-left: 28px;">${escapeHtml(d.notas)}</div>` : ''}
                                            </div>
                                        `).join('')}
                                    </div>
                                </div>
                            `;
                        }).join('')}
                    </div>
                </div>
            `;
        }

        // Estado vacío
        if (elegibles.length === 0 && emitidos.length === 0) {
            html += '<div class="card" style="text-align: center; padding: 40px; color: #999;">No hay diplomas disponibles para este contacto.</div>';
        }

        html += '</div>';
        diplomasSection.innerHTML = html;

        // Agregar funciones globales para las acciones
        setupDiplomasActions(contactoId, contactCode);

    } catch (error) {
        console.error('Error cargando diplomas:', error);
        diplomasSection.innerHTML = '<div class="card" style="color: #dc3545;">Error cargando diplomas</div>';
    }
}

function setupDiplomasActions(contactoId, contactCode) {
    // Actualizar contador de seleccionados
    window.updateContadorSel = function() {
        const checked = document.querySelectorAll('.dip-checkbox:checked');
        const contador = document.getElementById('count-sel');
        const btn = document.getElementById('btn-emitir-sel');
        
        if (contador) contador.textContent = checked.length;
        if (btn) btn.disabled = checked.length === 0;
    };

    // Seleccionar/deseleccionar todos
    window.toggleSeleccionTodos = function() {
        const checkboxes = document.querySelectorAll('.dip-checkbox');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        const toggleText = document.getElementById('toggle-sel-text');
        
        checkboxes.forEach(cb => cb.checked = !allChecked);
        
        if (toggleText) {
            toggleText.textContent = !allChecked ? 'Deseleccionar Todos' : 'Seleccionar Todos';
        }
        
        updateContadorSel();
    };

    // Emitir diplomas seleccionados
    window.emitirDiplomasSeleccionados = async function() {
        const checkboxes = document.querySelectorAll('.dip-checkbox:checked');
        
        if (checkboxes.length === 0) {
            alert('Por favor selecciona al menos un diploma');
            return;
        }

        if (!confirm(`¿Emitir ${checkboxes.length} diploma(s) seleccionado(s)?`)) {
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
                    let mensaje = `✅ Acta ${numero_acta} generada\n\n${total_exitosos} diploma(s) emitido(s)`;
                    if (total_errores > 0) {
                        mensaje += `\n⚠️ ${total_errores} error(es)`;
                        console.error('Errores:', errores);
                    }
                    alert(mensaje);
                    location.reload();
                } else {
                    alert('❌ No se pudo emitir ningún diploma');
                    console.error('Errores:', errores);
                }
            } else {
                alert('Error: ' + (response?.error?.message || 'No se pudo emitir diplomas'));
            }
        } catch (error) {
            console.error('Error emitiendo batch:', error);
            alert('❌ Error al emitir diplomas');
        }
    };

    // Emitir diploma individual
    window.emitirDiplomaContacto = async function(programaId, version, tipo, estudianteId, nivelId) {
        if (!confirm('¿Desea emitir este diploma?')) return;

        try {
            const response = await api.post('/diplomas/emitir', {
                tipo,
                programaId,
                version,
                estudianteId,
                nivelId: nivelId !== 'null' ? nivelId : null
            });

            if (response && response.success) {
                alert('Diploma emitido exitosamente');
                location.hash = `#/contacto/${encodeURIComponent(contactCode)}`;
                location.reload();
            } else {
                alert('Error: ' + (response?.error?.message || 'No se pudo emitir el diploma'));
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error emitiendo diploma');
        }
    };

    window.registrarEntregaContacto = async function(diplomaId) {
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
}

// ============ PRÓXIMOS A COMPLETAR ============

async function loadProximosCompletar(container, contactoId) {
    const proximosSection = container.querySelector('#c-proximos-section');
    if (!proximosSection) return;

    try {
        await ProximosCompletar.mount(proximosSection, { 
            contactoId: contactoId,
            programas: contactPrograms,
            titulo: 'Estudiantes Próximos a Graduarse'
        });
    } catch (error) {
        console.error('Error cargando próximos a completar:', error);
        proximosSection.innerHTML = '<div class="card" style="color: #dc3545;">Error cargando próximos a completar</div>';
    }
}

// ============ ACTAS ============

async function loadActas(container, contactoId, contactCode) {
    const actasSection = container.querySelector('#c-actas-section');
    if (!actasSection) return;

    try {
        // Cargar actas del contacto
        const response = await api.get(`/actas?contactoId=${contactoId}&estado=activa`);
        
        if (!response || !response.success) {
            throw new Error('Error cargando actas');
        }

        const actas = response.data || [];

        if (actas.length === 0) {
            actasSection.innerHTML = `
                <div class="card" style="text-align: center; padding: 40px; color: #999;">
                    <div style="font-size: 3rem; margin-bottom: 15px;">📋</div>
                    <p>No hay actas generadas para este contacto.</p>
                </div>
            `;
            return;
        }

        let html = '<div class="u-flex u-gap" style="flex-direction: column;">';

        actas.forEach(acta => {
            const fecha = new Date(acta.fecha_acta).toLocaleDateString('es-ES', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });

            html += `
                <div class="card" style="border-left: 4px solid #007bff;">
                    <div style="display: flex; justify-content: space-between; align-items: start;">
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                                <div style="font-size: 2rem;">📜</div>
                                <div>
                                    <div style="font-weight: 700; font-size: 1.2rem; color: #007bff;">
                                        Acta ${escapeHtml(acta.numero_acta)}
                                    </div>
                                    <div style="font-size: 0.9rem; color: #666;">
                                        ${fecha}
                                    </div>
                                </div>
                            </div>
                            <div style="display: flex; gap: 20px; margin-top: 10px; padding: 10px; background: #f8f9fa; border-radius: 4px;">
                                <div>
                                    <span style="font-weight: 600; color: #666;">Total diplomas:</span>
                                    <span style="font-weight: 700; color: #28a745; font-size: 1.1rem;">${acta.total_diplomas}</span>
                                </div>
                                <div>
                                    <span style="font-weight: 600; color: #666;">Tipo:</span>
                                    <span style="color: #333;">${acta.tipo_acta === 'cierre' ? 'Acta de Cierre' : acta.tipo_acta}</span>
                                </div>
                            </div>
                        </div>
                        <div style="display: flex; gap: 8px; flex-direction: column;">
                            <button class="btn btn-primary btn-sm" onclick="verActa(${acta.id})">
                                👁️ Ver Detalle
                            </button>
                            <button class="btn btn-secondary btn-sm" onclick="imprimirActa(${acta.id})">
                                🖨️ Reimprimir
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });

        html += '</div>';
        actasSection.innerHTML = html;

        // Setup acciones
        setupActasActions(contactCode);

    } catch (error) {
        console.error('Error cargando actas:', error);
        actasSection.innerHTML = '<div class="card" style="color: #dc3545;">Error cargando actas</div>';
    }
}

function setupActasActions(contactCode) {
    window.verActa = function(actaId) {
        // Navegar a la página de detalle del acta
        location.hash = `#/acta/${actaId}`;
    };

    window.imprimirActa = async function(actaId) {
        // Abrir el detalle del acta en modo impresión
        location.hash = `#/acta/${actaId}?print=true`;
    };
}

// ============ HELPER FUNCTIONS ============

function getLevelIcon(level) {
    const nombre = (level.nivel_nombre || '').toLowerCase();
    if (nombre.includes('básico') || nombre.includes('basico')) return '🌱';
    if (nombre.includes('intermedio')) return '🌿';
    if (nombre.includes('avanzado')) return '🌳';
    return '📚';
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function toggleCollapse(collapseId) {
    const content = document.getElementById(collapseId);
    const icon = document.getElementById(`${collapseId}-icon`);
    
    if (!content || !icon) return;
    
    if (content.style.display === 'none') {
        content.style.display = 'flex';
        icon.style.transform = 'rotate(0deg)';
    } else {
        content.style.display = 'none';
        icon.style.transform = 'rotate(-90deg)';
    }
}

// ============ TOGGLE PROGRAMA CON MODAL DE CONFIRMACIÓN ============

window.mostrarModalTogglePrograma = function(event, asignacionId, isActivo, programaNombre, progIdx) {
    event.stopPropagation();
    
    const accion = isActivo ? 'desactivar' : 'activar';
    const accionTitulo = isActivo ? 'Desactivar' : 'Activar';
    const colorAccion = isActivo ? '#dc3545' : '#28a745';
    
    // Crear overlay
    const overlay = document.createElement('div');
    overlay.id = 'toggle-programa-overlay';
    overlay.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10000;
    `;
    
    // Crear modal
    const modal = document.createElement('div');
    modal.style.cssText = `
        background: white;
        border-radius: 8px;
        padding: 24px;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    `;
    
    modal.innerHTML = `
        <div style="margin-bottom: 20px;">
            <h3 style="margin: 0 0 8px 0; color: ${colorAccion}; font-size: 1.25rem;">
                ⚠️ ${accionTitulo} Programa
            </h3>
            <p style="margin: 0; color: #666; font-size: 0.9rem;">
                Esta es una acción importante que afectará la visibilidad del programa.
            </p>
        </div>
        
        <div style="background: #f8f9fa; padding: 16px; border-radius: 6px; margin-bottom: 20px;">
            <div style="font-weight: 600; margin-bottom: 8px;">Programa:</div>
            <div style="font-size: 1.1rem; color: #333;">${escapeHtml(programaNombre)}</div>
        </div>
        
        <div style="margin-bottom: 20px;">
            <p style="margin: 0 0 12px 0; font-size: 0.9rem; color: #666;">
                ${isActivo 
                    ? '⚠️ Al desactivar, el programa se ocultará de las vistas pero <strong>NO se eliminarán</strong> los datos históricos (cursos completados, diplomas, etc.).'
                    : '✅ Al activar, el programa volverá a estar visible y accesible.'
                }
            </p>
            
            <label style="display: block; margin-bottom: 8px; font-weight: 500; color: #333;">
                Para confirmar, escribe el nombre del programa:
            </label>
            <input 
                type="text" 
                id="toggle-programa-input"
                class="input" 
                placeholder="${escapeHtml(programaNombre)}"
                style="width: 100%; padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 0.9rem;"
                autocomplete="off"
            />
            <div id="toggle-programa-error" style="color: #dc3545; font-size: 0.85rem; margin-top: 6px; display: none;">
                ❌ El nombre no coincide. Por favor verifica.
            </div>
        </div>
        
        <div style="display: flex; gap: 12px; justify-content: flex-end;">
            <button 
                id="toggle-programa-cancel"
                class="btn btn-secondary"
                style="padding: 8px 20px;">
                Cancelar
            </button>
            <button 
                id="toggle-programa-confirm"
                class="btn"
                style="background: ${colorAccion}; color: white; padding: 8px 20px; border: none;">
                ${accionTitulo} Programa
            </button>
        </div>
    `;
    
    overlay.appendChild(modal);
    document.body.appendChild(overlay);
    
    // Focus en el input
    const input = document.getElementById('toggle-programa-input');
    setTimeout(() => input.focus(), 100);
    
    // Cerrar modal
    function cerrarModal() {
        document.body.removeChild(overlay);
    }
    
    // Event listeners
    document.getElementById('toggle-programa-cancel').addEventListener('click', cerrarModal);
    
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) cerrarModal();
    });
    
    document.getElementById('toggle-programa-confirm').addEventListener('click', async () => {
        const inputValue = input.value.trim();
        const errorDiv = document.getElementById('toggle-programa-error');
        
        // Validar que el nombre coincida
        if (inputValue.toLowerCase() !== programaNombre.toLowerCase()) {
            errorDiv.style.display = 'block';
            input.style.borderColor = '#dc3545';
            input.focus();
            return;
        }
        
        // Proceder con el toggle
        const confirmBtn = document.getElementById('toggle-programa-confirm');
        confirmBtn.disabled = true;
        confirmBtn.textContent = 'Procesando...';
        
        try {
            const response = await api.put('/programas-asignaciones/' + asignacionId + '/toggle', {
                activo: !isActivo
            });
            
            if (response.success) {
                cerrarModal();
                // Recargar solo la sección de programas en lugar de toda la página
                const container = document.querySelector('.card');
                if (container && currentContactCode) {
                    await loadAcademicHistory(container, currentContactCode, currentContactName);
                } else {
                    location.reload();
                }
            } else {
                throw new Error(response.error?.message || 'Error desconocido');
            }
        } catch (error) {
            console.error('Error toggling programa:', error);
            errorDiv.textContent = '❌ Error: ' + (error.message || 'Error desconocido');
            errorDiv.style.display = 'block';
            confirmBtn.disabled = false;
            confirmBtn.textContent = accionTitulo + ' Programa';
        }
    });
    
    // Enter para confirmar
    input.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            document.getElementById('toggle-programa-confirm').click();
        }
    });
};

// Mantener la función antigua por compatibilidad pero redirigir al modal
window.togglePrograma = function(event, asignacionId, isActivo, programaNombre) {
    mostrarModalTogglePrograma(event, asignacionId, isActivo, programaNombre, 0);
};

// Hacer toggleCollapse disponible globalmente
window.toggleCollapse = toggleCollapse;

function formatDate(dateStr) {
    if (!dateStr) return '-';
    return String(dateStr).substring(0, 10);
}
