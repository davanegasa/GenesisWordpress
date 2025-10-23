import { api } from '../../api/client.js';

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
        `;
        
        // Configurar tabs
        setupTabs(container);
        
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

async function loadAcademicHistory(container, contactCode, contactName) {
    const programsSection = container.querySelector('#c-programs-section');
    const studentsSection = container.querySelector('#c-students-section');
    
    if (!programsSection || !studentsSection) return;
    
    programsSection.innerHTML = '<div style="padding:20px;text-align:center;color:var(--plg-mutedText);">Cargando programas...</div>';
    studentsSection.innerHTML = '<div style="padding:20px;text-align:center;color:var(--plg-mutedText);">Cargando estudiantes...</div>';
    
    
    
    try {
        const response = await api.get(`/contactos/${encodeURIComponent(contactCode)}/academic-history`);
        const data = response?.data || {};
        
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
        
        html += `
            <div class="program-section">
                <div class="program-header collapsible collapsed" data-program-idx="${progIdx}">
                    <div class="u-flex u-gap" style="align-items:center;flex-wrap:wrap;">
                        <span class="collapse-icon">▶</span>
                        <span class="program-icon">📂</span>
                        <span class="program-name">${escapeHtml(program.programa_nombre)}</span>
                    </div>
                    <span class="level-summary">${totalCursos} curso${totalCursos !== 1 ? 's' : ''}</span>
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
        header.addEventListener('click', () => {
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

function formatDate(dateStr) {
    if (!dateStr) return '-';
    return String(dateStr).substring(0, 10);
}
