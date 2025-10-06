import { api } from '../../api/client.js';
import { showToast } from '../../components/ui/index.js';

export async function mount(container){
    container.innerHTML = `
        <div class="card">
            <div class="u-flex u-gap" style="justify-content:space-between;align-items:center;">
                <div class="card-title">Nuevo Programa</div>
                <a class="btn btn-secondary" href="#/programas">Volver</a>
            </div>
            <div class="form-grid">
                <label>Nombre<input id="p-nombre" class="input" type="text" placeholder="Nombre"></label>
                <label>Descripción<input id="p-desc" class="input" type="text" placeholder="Descripción"></label>
            </div>
            <div class="u-mt-16">
                <button id="create" class="btn btn-primary">Crear</button>
            </div>
            <pre id="msg" class="u-mt-16"></pre>
        </div>
    `;
    container.querySelector('#create').addEventListener('click', async ()=>{
        const payload = { nombre: $('#p-nombre')?.value||'', descripcion: $('#p-desc')?.value||'' };
        const msg = container.querySelector('#msg'); msg.textContent='Creando…';
        try{
            const r = await api.post('/programas', payload);
            const id = r && r.data && r.data.id; msg.textContent='Creado: '+id; showToast('Programa creado');
            if (id) location.hash = '#/programa/'+encodeURIComponent(id);
        } catch(e){ msg.textContent = e.details?.message||e.message||'Error'; showToast('Error creando', true); }
    });
    function $(sel){ return container.querySelector(sel); }
}

export function unmount(){}


