import { api } from '../../api/client.js';
import { createTable, showToast, createDetailGrid, createFieldView, createModal } from '../../components/ui/index.js';

export async function mount(container, { id } = {}){
    container.innerHTML = `
        <div class="card">
            <div class="u-flex u-gap" style="justify-content:space-between;align-items:center;">
                <div class="card-title">Programa <span class="badge">${id||''}</span></div>
                <div class="u-flex u-gap">
                    <a class="btn btn-secondary" href="#/programas">Volver</a>
                    <button id="save" class="btn btn-primary">Guardar</button>
                </div>
            </div>
            <div id="body">Cargando…</div>
            <pre id="msg" class="u-mt-16"></pre>
        </div>
    `;
    const $body = container.querySelector('#body');
    let d = null;
    try{
        const res = await api.get('/programas/'+encodeURIComponent(id));
        d = (res && res.data) || {};
    } catch(e){ $body.textContent='No fue posible cargar el programa'; return; }

    try{
        $body.innerHTML = `
            <div id="g-info"></div>
            <div id="g-edit" class="u-hidden">
                <div class="form-grid">
                    <label>Nombre<input id="p-nombre" class="input" type="text" value="${d.nombre||''}"></label>
                    <label>Descripción<textarea id="p-desc" class="input" rows="4">${d.descripcion||''}</textarea></label>
                </div>
            </div>
            <div class="divider"></div>
            <div class="u-flex u-gap" style="justify-content:space-between;align-items:center;">
                <div class="section-title">Estructura</div>
                <div class="u-flex u-gap">
                    <button id="p-struct-edit" class="btn">Editar estructura</button>
                    <button id="p-struct-save" class="btn btn-primary u-hidden">Guardar estructura</button>
                    <button id="p-struct-cancel" class="btn u-hidden">Cancelar</button>
                </div>
            </div>
            <div id="struct-view"></div>
            <div id="struct-edit" class="u-hidden"></div>
            <!-- Secciones antiguas de "Niveles" y "Cursos sin nivel" se reemplazan por la vista de Estructura -->
            <div class="section">
                <div class="section-title">Prerequisitos</div>
                <div id="pre"></div>
            </div>
        `;
        try { renderInfo(d); } catch(e){ console.error('renderInfo error', e); const c=container.querySelector('#g-info'); if(c) c.textContent=(d.nombre||'-')+' — '+(d.descripcion||'-'); }
        renderPre(d);
        // View/Edit toggle
        const saveBtn = container.querySelector('#save');
        const editBtn = document.createElement('button'); editBtn.id='p-edit'; editBtn.className='btn'; editBtn.textContent='Editar';
        if (saveBtn && saveBtn.parentElement) {
            saveBtn.parentElement.insertBefore(editBtn, saveBtn);
        } else {
            // Fallback: añadir al header si la estructura cambia
            const headerActions = container.querySelector('.u-flex.u-gap');
            if (headerActions) headerActions.appendChild(editBtn);
        }
        function setMode(edit){
            container.querySelector('#g-edit').classList.toggle('u-hidden', !edit);
            container.querySelector('#save').classList.toggle('u-hidden', !edit);
            editBtn.classList.toggle('u-hidden', !!edit);
        }
        setMode(false);
        if (editBtn) editBtn.addEventListener('click', ()=> setMode(true));

        if (saveBtn) saveBtn.addEventListener('click', async ()=>{
            const payload = collectPayload();
            const msg = container.querySelector('#msg'); msg.textContent='Guardando…';
            try{ await api.put('/programas/'+encodeURIComponent(id), payload); msg.textContent='Guardado'; showToast('Programa actualizado'); }
            catch(e){ msg.textContent=e.details?.message||e.message||'Error'; showToast('Error guardando', true); }
            // Sync view
        try{ const fresh = await api.get('/programas/'+encodeURIComponent(id)); renderInfo(fresh && fresh.data || d); } catch(_){}
            setMode(false);
        });
        // ====== Estructura: estado y edición ======
        const state = {
            levels: (d.niveles||[]).map(n=>({ id:n.id, nombre:n.nombre, cursos:(n.cursos||[]).map(c=>({ id:c.id, nombre:c.nombre, descripcion:c.descripcion })) })),
            sinNivel: (d.cursosSinNivel||[]).map(c=>({ id:c.id, nombre:c.nombre, descripcion:c.descripcion }))
        };

        const btnStructEdit = container.querySelector('#p-struct-edit');
        const btnStructSave = container.querySelector('#p-struct-save');
        const btnStructCancel = container.querySelector('#p-struct-cancel');
        const structView = container.querySelector('#struct-view');
        const structEdit = container.querySelector('#struct-edit');

        function setStructMode(isEdit){
            structView.classList.toggle('u-hidden', !!isEdit);
            structEdit.classList.toggle('u-hidden', !isEdit);
            btnStructEdit.classList.toggle('u-hidden', !!isEdit);
            btnStructSave.classList.toggle('u-hidden', !isEdit);
            btnStructCancel.classList.toggle('u-hidden', !isEdit);
            if (isEdit) renderStructureEdit();
        }
        setStructMode(false);
        // Render inicial de la vista de estructura (después de inicializar structView)
        renderStructureView(d);
        btnStructEdit.addEventListener('click', ()=> setStructMode(true));
        btnStructCancel.addEventListener('click', ()=> { // descartar cambios releyendo estado desde d
            state.levels = (d.niveles||[]).map(n=>({ id:n.id, nombre:n.nombre, cursos:(n.cursos||[]).map(c=>({ id:c.id, nombre:c.nombre, descripcion:c.descripcion })) }));
            state.sinNivel = (d.cursosSinNivel||[]).map(c=>({ id:c.id, nombre:c.nombre, descripcion:c.descripcion }));
            setStructMode(false);
            renderStructureView({ niveles:d.niveles, cursosSinNivel:d.cursosSinNivel });
        });
        btnStructSave.addEventListener('click', async ()=>{
            const payload = buildStructurePayload();
            const msg = container.querySelector('#msg'); msg.textContent='Guardando estructura…';
            try{
                const saveResp = await api.put('/programas/'+encodeURIComponent(id), payload);
                showToast('Estructura guardada');
                // refrescar d y vista
                const fresh = await api.get('/programas/'+encodeURIComponent(id));
                d = (fresh && fresh.data) || d;
                // Solo re-render de la vista de estructura (secciones antiguas ya no existen)
                renderStructureView(d);
                setStructMode(false);
                msg.textContent='';
                // Si se creó una nueva versión, preguntar si desea forzar actualización
                if (saveResp && saveResp.data && saveResp.data.newVersion){
                    const newVer = saveResp.data.newVersion;
                    promptUpgradeAssignments(id, newVer);
                }
            }catch(e){ msg.textContent=e.details?.message||e.message||'Error guardando estructura'; showToast('Error guardando', true); }
        });

        function renderStructureView(p){
            const cont = structView; cont.innerHTML='';
            const box = document.createElement('div');
            box.className='two-col';
            // Columna niveles
            const colLv = document.createElement('div');
            colLv.innerHTML = '<div class="section-title">Vista de niveles</div>';
            (p.niveles||[]).forEach(n=>{
                const wrap = document.createElement('div'); wrap.className='card u-mb-8';
                wrap.innerHTML = `<div class="section-title">${n.nombre||'Nivel'}</div>`;
                const list = document.createElement('div'); list.className='course-grid';
                (n.cursos||[]).forEach(c=>{ const el=document.createElement('div'); el.className='course-card'; el.innerHTML=`<div class="course-name">${c.nombre}</div><span class="course-badge">#${c.consecutivo||''}</span>`; list.appendChild(el); });
                wrap.appendChild(list); colLv.appendChild(wrap);
            });
            if (!colLv.children.length){ colLv.innerHTML += '<div class="hint-text">Sin niveles</div>'; }
            // Columna sin nivel
            const colSn = document.createElement('div');
            colSn.innerHTML = '<div class="section-title">Vista cursos sin nivel</div>';
            const listSn = document.createElement('div'); listSn.className='course-grid';
            (p.cursosSinNivel||[]).forEach(c=>{ const el=document.createElement('div'); el.className='course-card'; el.innerHTML=`<div class="course-name">${c.nombre}</div><span class="course-badge">#${c.consecutivo||''}</span>`; listSn.appendChild(el); });
            if (!listSn.children.length) colSn.innerHTML += '<div class="hint-text">Sin cursos sin nivel</div>'; else colSn.appendChild(listSn);
            box.appendChild(colLv); box.appendChild(colSn); cont.appendChild(box);
        }

        function renderStructureEdit(){
            const cont = structEdit; cont.innerHTML='';
            const head = document.createElement('div'); head.className='u-flex u-gap'; head.style.marginBottom='8px';
            const addLevelBtn = document.createElement('button'); addLevelBtn.className='btn'; addLevelBtn.textContent='Añadir nivel';
            head.appendChild(addLevelBtn); cont.appendChild(head);
            addLevelBtn.addEventListener('click', ()=>{ state.levels.push({ id:null, nombre:'Nuevo nivel', cursos:[] }); renderStructureEdit(); });

            const grid = document.createElement('div'); grid.className='two-col';
            // Columna niveles editables
            const colLv = document.createElement('div');
            (state.levels||[]).forEach((n, idx)=>{
                const wrap = document.createElement('div'); wrap.className='card u-mb-8';
                const row = document.createElement('div'); row.className='u-flex u-gap'; row.style.alignItems='center';
                const inp = document.createElement('input'); inp.className='input'; inp.value=n.nombre||''; inp.style.flex='1';
                const addC = document.createElement('button'); addC.className='btn'; addC.textContent='Agregar curso';
                const del = document.createElement('button'); del.className='btn'; del.textContent='Eliminar';
                del.onclick = ()=>{ if (confirm('¿Eliminar nivel? Los cursos pasarán a "Sin nivel".')){ state.sinNivel.push(...n.cursos); state.levels.splice(idx,1); renderStructureEdit(); } };
                row.appendChild(inp); row.appendChild(addC); row.appendChild(del); wrap.appendChild(row);
                inp.oninput = ()=>{ n.nombre = inp.value; };
                addC.onclick = ()=> openCoursePicker({ to:'level', levelIndex: idx });
                const list = document.createElement('div'); list.className='drop-zone'; list.style.minHeight='40px'; list.dataset.levelIndex=String(idx);
                // cursos
                (n.cursos||[]).forEach((c, cidx)=>{ list.appendChild(renderDraggableCourse(c, { from:'level', levelIndex:idx, courseIndex:cidx })); });
                enableDrop(list, (info, toIndex)=>{
                    moveCourse(info, { to:'level', levelIndex:idx, toIndex });
                });
                wrap.appendChild(list); colLv.appendChild(wrap);
            });
            if (!colLv.children.length){ colLv.innerHTML='<div class="hint-text">No hay niveles</div>'; }

            // Columna sin nivel editable
            const colSn = document.createElement('div');
            const cardSn = document.createElement('div'); cardSn.className='card';
            cardSn.innerHTML='<div class="u-flex u-gap" style="justify-content:space-between;align-items:center;"><div class="section-title" style="margin:0;">Cursos sin nivel</div><div><button id="btn-add-sin" class="btn">Agregar curso</button></div></div>';
            const listSn = document.createElement('div'); listSn.className='drop-zone'; listSn.style.minHeight='40px';
            (state.sinNivel||[]).forEach((c, cidx)=>{ listSn.appendChild(renderDraggableCourse(c, { from:'sin', courseIndex:cidx })); });
            enableDrop(listSn, (info, toIndex)=>{ moveCourse(info, { to:'sin', toIndex }); });
            cardSn.appendChild(listSn); colSn.appendChild(cardSn);
            const btnAddSin = cardSn.querySelector('#btn-add-sin'); if (btnAddSin) btnAddSin.onclick = ()=> openCoursePicker({ to:'sin' });

            grid.appendChild(colLv); grid.appendChild(colSn); cont.appendChild(grid);
        }

        let placeholderEl = null;
        function ensurePlaceholder(){ if (placeholderEl) return placeholderEl; const ph=document.createElement('div'); ph.className='drop-placeholder'; placeholderEl = ph; return ph; }

        function renderDraggableCourse(c, origin){
            const el = document.createElement('div'); el.className='course-card'; el.draggable=true; el.style.cursor='grab';
            el.innerHTML = `
                <div class="u-flex u-gap" style="justify-content:space-between;align-items:center;">
                    <div class="course-name" style="margin:0;">${c.nombre||''}</div>
                    <button class="btn btn-quiet course-remove" title="Eliminar" aria-label="Eliminar">✕</button>
                </div>
            `;
            const btn = el.querySelector('.course-remove');
            if (btn){
                // Evitar que el drag se dispare al intentar eliminar
                btn.setAttribute('draggable','false');
                btn.addEventListener('mousedown', (e)=> e.stopPropagation());
                btn.addEventListener('dragstart', (e)=> e.preventDefault());
                btn.addEventListener('click', (e)=>{ e.stopPropagation(); removeCourse(origin); });
            }
            el.addEventListener('dragstart', (ev)=>{
                ev.dataTransfer.effectAllowed='move';
                ev.dataTransfer.setData('application/json', JSON.stringify(origin));
            });
            el.addEventListener('dragover', (ev)=>{
                ev.preventDefault();
                const zone = el.parentElement; if (!zone) return;
                const rect = el.getBoundingClientRect();
                const before = (ev.clientY - rect.top) < (rect.height/2);
                const ph = ensurePlaceholder();
                if (before){ zone.insertBefore(ph, el); }
                else { zone.insertBefore(ph, el.nextSibling); }
                zone.classList.add('drag-over');
            });
            el.addEventListener('dragleave', ()=>{ /* no-op, zone handles class */ });
            return el;
        }

        function removeCourse(origin){
            if (origin.from === 'level'){
                const arr = state.levels[origin.levelIndex]?.cursos||[];
                arr.splice(origin.courseIndex,1);
            } else {
                state.sinNivel.splice(origin.courseIndex,1);
            }
            renderStructureEdit();
        }

        async function openCoursePicker(target){
            const m = createModal({ title: 'Agregar curso', bodyHtml: `
                <div class="u-flex u-gap"><input id="find-course" class="input" placeholder="Escribe para buscar cursos..." style="flex:1"></div>
                <div id="course-results" class="listbox u-mt-8"></div>
                <div id="course-msg" class="hint-text u-mt-8"></div>
            `, primaryLabel: 'Agregar', secondaryLabel: 'Cancelar', onPrimary: (close)=>{
                const sel = selected; if (!sel){ document.querySelector('#course-msg').textContent='Selecciona un curso'; return; }
                if (existsInStructure(sel.id)){ document.querySelector('#course-msg').textContent='El curso ya existe en la estructura'; return; }
                const item = { id: sel.id, nombre: sel.nombre, descripcion: sel.descripcion };
                if (target.to==='level'){ state.levels[target.levelIndex].cursos.push(item); }
                else { state.sinNivel.push(item); }
                renderStructureEdit(); close();
            }});
            document.body.appendChild(m.overlay);
            const $q = document.querySelector('#find-course'); const $list = document.querySelector('#course-results');
            let selected = null; let items = [];
            async function searchCourses(text){
                try{ const r = await api.get('/cursos?q='+encodeURIComponent(text||'')); items = (r&&r.data&&r.data.items)||[]; renderList(); }
                catch(_){ $list.innerHTML='<div class="listbox-item">Error buscando</div>'; }
            }
            function renderList(){
                $list.innerHTML='';
                items.forEach(c=>{
                    const el = document.createElement('div'); el.className='listbox-item'; el.textContent = c.nombre; el.onclick = ()=>{ selected = c; Array.from($list.children).forEach(x=>x.classList.remove('selected')); el.classList.add('selected'); };
                    $list.appendChild(el);
                });
                if (!$list.children.length){ $list.innerHTML='<div class="listbox-item">Sin resultados</div>'; }
            }
            function existsInStructure(courseId){
                const idn = Number(courseId);
                const inLevels = (state.levels||[]).some(n=> (n.cursos||[]).some(c=> Number(c.id)===idn));
                const inSin = (state.sinNivel||[]).some(c=> Number(c.id)===idn);
                return inLevels || inSin;
            }
            $q.addEventListener('input', ()=> searchCourses($q.value||''));
            searchCourses(''); $q.focus();
        }

        function enableDrop(zone, onDrop){
            zone.addEventListener('dragover', (ev)=>{ ev.preventDefault(); zone.classList.add('drag-over'); });
            zone.addEventListener('dragleave', (ev)=>{ if (ev.target===zone){ zone.classList.remove('drag-over'); } });
            zone.addEventListener('drop', (ev)=>{
                ev.preventDefault(); zone.classList.remove('drag-over');
                let toIndex = null;
                if (placeholderEl && placeholderEl.parentElement===zone){
                    toIndex = Array.from(zone.children).indexOf(placeholderEl);
                    try{ zone.removeChild(placeholderEl); }catch(_){ }
                } else {
                    // al final
                    toIndex = Array.from(zone.children).filter(n=>n.classList.contains('course-card')).length;
                }
                try{ const info = JSON.parse(ev.dataTransfer.getData('application/json')||'{}'); onDrop && onDrop(info, toIndex); }catch(_){ /* noop */ }
            });
        }

        function moveCourse(from, to){
            // extraer
            let course = null;
            if (from.from === 'level'){
                const arr = state.levels[from.levelIndex]?.cursos||[];
                course = arr.splice(from.courseIndex,1)[0];
                // si se reordena dentro del mismo nivel y el índice destino está después, ajustar
                if (to.to==='level' && to.levelIndex===from.levelIndex && to.toIndex!=null && to.toIndex>from.courseIndex){
                    to.toIndex = to.toIndex - 1;
                }
            } else if (from.from === 'sin'){
                course = state.sinNivel.splice(from.courseIndex,1)[0];
            }
            if (!course) return;
            // insertar
            if (to.to === 'level'){
                const arr = state.levels[to.levelIndex].cursos;
                if (to.toIndex!=null && to.toIndex>=0 && to.toIndex<=arr.length){ arr.splice(to.toIndex,0,course); }
                else { arr.push(course); }
            } else {
                const arr = state.sinNivel;
                if (to.toIndex!=null && to.toIndex>=0 && to.toIndex<=arr.length){ arr.splice(to.toIndex,0,course); }
                else { arr.push(course); }
            }
            renderStructureEdit();
        }

        function buildStructurePayload(){
            // Consecutivo debe ser ÚNICO en todo el programa (constraint DB: programa_id, consecutivo)
            const seen = new Set();
            const isValidId = (x)=> Number.isInteger(Number(x)) && Number(x) > 0;
            let counter = 1;
            const niveles = state.levels.map(n=>{
                const outCursos = [];
                (n.cursos||[]).forEach((c)=>{
                    if (!isValidId(c.id)) return;
                    if (seen.has(c.id)) return;
                    seen.add(c.id);
                    outCursos.push({ id: Number(c.id), consecutivo: counter++ });
                });
                return { id: n.id || undefined, nombre: n.nombre, cursos: outCursos };
            });
            const sinNivelOut = [];
            (state.sinNivel||[]).forEach((c)=>{
                if (!isValidId(c.id)) return;
                if (seen.has(c.id)) return;
                seen.add(c.id);
                sinNivelOut.push({ id: Number(c.id), consecutivo: counter++ });
            });
            return { niveles, cursosSinNivel: sinNivelOut };
        }

    } catch(e){ console.error('render programa detail error', e); $body.textContent='No fue posible cargar el programa'; }

    function renderInfo(d){
        const cont = container.querySelector('#g-info'); cont.innerHTML='';
        const grid = createDetailGrid([
            createFieldView({ label:'Nombre', value:d.nombre||'-', span:1 }),
            createFieldView({ label:'Descripción', value:d.descripcion||'-', span:1 }),
            createFieldView({ label:'Versión actual', value:`v${d.version||1}`, span:1 })
        ]);
        cont.appendChild(grid);
    }
    function renderNiveles(d){
        const cont = container.querySelector('#niveles'); cont.innerHTML='';
        (d.niveles||[]).forEach(n=>{
            const wrap = document.createElement('div');
            wrap.className = 'card u-mb-8';
            const title = document.createElement('div'); title.className='section-title'; title.textContent = n.nombre || 'Nivel';
            const grid = document.createElement('div'); grid.className='course-grid';
            const cursos = (n.cursos||[]).slice().sort((a,b)=> (a.consecutivo||0)-(b.consecutivo||0));
            cursos.forEach(c=>{
                const item = document.createElement('div'); item.className='course-card';
                item.innerHTML = `<div class=\"course-name\">${c.nombre||''}</div><span class=\"course-badge\">#${c.consecutivo||''}</span>`;
                item.title = c.descripcion || '';
                item.addEventListener('click', ()=> openQuickView(c));
                grid.appendChild(item);
            });
            wrap.appendChild(title); wrap.appendChild(grid); cont.appendChild(wrap);
        });
        if (!cont.children.length){ cont.textContent = 'Sin niveles'; }
    }
    function renderSinNivel(d){
        const cont = container.querySelector('#sinNivel'); cont.innerHTML='';
        const grid = document.createElement('div'); grid.className='course-grid';
        (d.cursosSinNivel||[]).slice().sort((a,b)=> (a.consecutivo||0)-(b.consecutivo||0)).forEach(c=>{
            const item = document.createElement('div'); item.className='course-card';
            item.innerHTML = `<div class=\"course-name\">${c.nombre||''}</div><span class=\"course-badge\">#${c.consecutivo||''}</span>`;
            item.title = c.descripcion || '';
            item.addEventListener('click', ()=> openQuickView(c));
            grid.appendChild(item);
        });
        if (!grid.children.length){
            cont.textContent = 'Sin resultados';
        } else {
            cont.appendChild(grid);
        }
    }
    function renderPre(d){
        const cont = container.querySelector('#pre'); cont.innerHTML='';
        const rows = (d.prerequisitos||[]).map(x=>[ x.id, x.nombre||'' ]);
        cont.appendChild(createTable({ columns:['Programa','Nombre'], rows }));
    }
    function collectPayload(){
        return {
            nombre: $('#p-nombre')?.value || '',
            descripcion: $('#p-desc')?.value || ''
        };
    }
    function $(sel){ return container.querySelector(sel); }

    function openQuickView(c){
        const prev = document.querySelector('.modal-overlay'); if(prev) prev.remove();
        const overlay = document.createElement('div'); overlay.className='modal-overlay';
        const modal = document.createElement('div'); modal.className='modal';
        modal.innerHTML = `
            <div class=\"modal-header\"><strong>${c.nombre||'Curso'}</strong><button id=\"x-close\" class=\"btn\">Cerrar</button></div>
            <div class=\"modal-body\">
                <div class=\"contact-card\">
                    <div class=\"contact-body\">
                        <div class=\"contact-title\">${c.nombre||'-'}</div>
                        <div class=\"contact-sub\">Consecutivo #${c.consecutivo||'-'}</div>
                    </div>
                </div>
                <div class=\"section\"><div class=\"section-title\">Descripción</div><div>${(c.descripcion||'-')}</div></div>
            </div>
            <div class=\"modal-footer\"><button id=\"x-close2\" class=\"btn\">Cerrar</button></div>
        `;
        overlay.appendChild(modal); document.body.appendChild(overlay);
        const close = ()=> overlay.remove(); modal.querySelector('#x-close').addEventListener('click', close); modal.querySelector('#x-close2').addEventListener('click', close);
    }

    function promptUpgradeAssignments(programaId, newVersion){
        const prev = document.querySelector('.modal-overlay'); if(prev) prev.remove();
        const overlay = document.createElement('div'); overlay.className='modal-overlay';
        const modal = document.createElement('div'); modal.className='modal';
        modal.innerHTML = `
            <div class="modal-header"><strong>Nueva versión creada (v${newVersion})</strong><button id="x-close" class="btn">Cerrar</button></div>
            <div class="modal-body">
                <p>Se ha creado una nueva versión de la estructura del programa (versión ${newVersion}).</p>
                <p>Las asignaciones existentes seguirán usando su versión actual. ¿Deseas actualizar todas las asignaciones a la nueva versión?</p>
                <div class="form-grid u-mt-16">
                    <label>Alcance de la actualización:
                        <select id="upgrade-scope" class="input">
                            <option value="all">Todas (estudiantes y contactos)</option>
                            <option value="students">Solo estudiantes</option>
                            <option value="contacts">Solo contactos</option>
                        </select>
                    </label>
                </div>
                <div id="upgrade-msg" class="hint-text u-mt-8"></div>
            </div>
            <div class="modal-footer">
                <button id="x-close2" class="btn btn-secondary">Cancelar</button>
                <button id="x-upgrade" class="btn btn-primary">Actualizar asignaciones</button>
            </div>
        `;
        overlay.appendChild(modal); document.body.appendChild(overlay);
        const close = ()=> overlay.remove();
        modal.querySelector('#x-close').addEventListener('click', close);
        modal.querySelector('#x-close2').addEventListener('click', close);
        modal.querySelector('#x-upgrade').addEventListener('click', async ()=>{
            const scope = modal.querySelector('#upgrade-scope').value;
            const msgEl = modal.querySelector('#upgrade-msg');
            msgEl.textContent = 'Actualizando asignaciones...';
            try{
                const resp = await api.post(`/programas/${encodeURIComponent(programaId)}/upgrade-assignments`, { toVersion: newVersion, scope });
                const count = resp.data?.updated || 0;
                msgEl.textContent = `✓ ${count} asignaciones actualizadas a la versión ${newVersion}.`;
                showToast(`${count} asignaciones actualizadas`);
                setTimeout(close, 2000);
            }catch(e){
                msgEl.textContent = `Error: ${e.details?.message || e.message || 'Error desconocido'}`;
                showToast('Error actualizando asignaciones', true);
            }
        });
    }
}

export function unmount(){}


