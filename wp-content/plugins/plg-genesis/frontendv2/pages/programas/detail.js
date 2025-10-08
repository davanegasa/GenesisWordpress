import { api } from '../../api/client.js';
import { createTable, showToast, createDetailGrid, createFieldView } from '../../components/ui/index.js';

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
            <div class="section">
                <div class="section-title">Niveles</div>
                <div id="niveles"></div>
            </div>
            <div class="section">
                <div class="section-title">Cursos sin nivel</div>
                <div id="sinNivel"></div>
            </div>
            <div class="section">
                <div class="section-title">Prerequisitos</div>
                <div id="pre"></div>
            </div>
        `;
        try { renderInfo(d); } catch(e){ console.error('renderInfo error', e); const c=container.querySelector('#g-info'); if(c) c.textContent=(d.nombre||'-')+' — '+(d.descripcion||'-'); }
        renderNiveles(d);
        renderSinNivel(d);
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
            try{ const fresh = await api.get('/programas/'+encodeURIComponent(id)); renderInfo(fresh && fresh.data || d); } catch{}
            setMode(false);
        });
    } catch(e){ console.error('render programa detail error', e); $body.textContent='No fue posible cargar el programa'; }

    function renderInfo(d){
        const cont = container.querySelector('#g-info'); cont.innerHTML='';
        const grid = createDetailGrid([
            createFieldView({ label:'Nombre', value:d.nombre||'-', span:1 }),
            createFieldView({ label:'Descripción', value:d.descripcion||'-', span:1 })
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
}

export function unmount(){}


