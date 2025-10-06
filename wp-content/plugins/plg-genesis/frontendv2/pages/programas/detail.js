import { api } from '../../api/client.js';
import { createTable, showToast } from '../../components/ui/index.js';

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
    try{
        const res = await api.get('/programas/'+encodeURIComponent(id));
        const d = res && res.data || {};
        $body.innerHTML = `
            <div class="form-grid">
                <label>Nombre<input id="p-nombre" class="input" type="text" value="${d.nombre||''}"></label>
                <label>Descripción<input id="p-desc" class="input" type="text" value="${d.descripcion||''}"></label>
            </div>
            <div class="section">
                <div class="section-title">Niveles</div>
                <div id="niveles"></div>
            </div>
            <div class="section">
                <div class="section-title">Cursos sin nivel</div>
                <div id="sinNivel"></div>
            </div>
        `;
        renderNiveles(d);
        renderSinNivel(d);
        container.querySelector('#save').addEventListener('click', async ()=>{
            const payload = collectPayload();
            const msg = container.querySelector('#msg'); msg.textContent='Guardando…';
            try{ await api.put('/programas/'+encodeURIComponent(id), payload); msg.textContent='Guardado'; showToast('Programa actualizado'); }
            catch(e){ msg.textContent=e.details?.message||e.message||'Error'; showToast('Error guardando', true); }
        });
    } catch(e){ $body.textContent='No fue posible cargar el programa'; }

    function renderNiveles(d){
        const cont = container.querySelector('#niveles'); cont.innerHTML='';
        const rows = (d.niveles||[]).map(n=>[ n.id, n.nombre, (n.cursos||[]).map(c=>`${c.id}#${c.consecutivo}`).join(', ') ]);
        cont.appendChild(createTable({ columns:['ID','Nombre','Cursos (id#cons)'], rows }));
    }
    function renderSinNivel(d){
        const cont = container.querySelector('#sinNivel'); cont.innerHTML='';
        const rows = (d.cursosSinNivel||[]).map(c=>[ c.id, c.consecutivo ]);
        cont.appendChild(createTable({ columns:['Curso','Consecutivo'], rows }));
    }
    function collectPayload(){
        return {
            nombre: $('#p-nombre')?.value || '',
            descripcion: $('#p-desc')?.value || ''
        };
    }
    function $(sel){ return container.querySelector(sel); }
}

export function unmount(){}


