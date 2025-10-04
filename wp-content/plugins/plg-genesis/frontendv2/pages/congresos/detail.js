import { api } from '../../api/client.js';
import { createTable } from '../../components/ui/index.js';

export async function mount(container, { id } = {}){
    container.innerHTML = `
        <div class="card">
            <div class="u-flex u-gap" style="justify-content:space-between;align-items:center;">
                <div class="card-title">Congreso <span class="badge">${id||''}</span></div>
                <div class="u-flex u-gap">
                    <a id="g-back" class="btn btn-secondary" href="#/congresos">Volver</a>
                    <button id="g-save" class="btn btn-primary">Guardar cambios</button>
                </div>
            </div>
            <div id="gd">Cargando...</div>
            <pre id="g-msg" class="u-mt-16"></pre>
        </div>`;

    const $gd = container.querySelector('#gd');
    try{
        const res = await api.get('/congresos/'+encodeURIComponent(id));
        const d = (res && res.data) || {};
        $gd.innerHTML = `
            <div id="g-view">
                <div class="contact-card">
                    <div class="contact-avatar">${(d.nombre||'C').slice(0,1)}</div>
                    <div class="contact-body">
                        <div class="contact-title">${d.nombre||'-'}</div>
                        <div class="contact-sub">${d.fecha||'-'} · ${d.lugar||'-'}</div>
                        <div class="contact-sub">Estado: ${d.estado||'-'}</div>
                    </div>
                </div>
                <div class="divider"></div>
                <div class="section">
                    <div class="section-title">Inscritos por contacto</div>
                    <div id="g-inscritos">Cargando inscritos…</div>
                </div>
            </div>
            <div id="g-edit" class="u-hidden">
                <div class="section">
                    <div class="section-title">Datos</div>
                    <div class="form-grid">
                        <label>Nombre<input id="g-nombre" class="input" type="text" value="${d.nombre||''}"></label>
                        <label>Fecha<input id="g-fecha" class="input" type="date" value="${(d.fecha||'').slice(0,10)}"></label>
                        <label>Lugar<input id="g-lugar" class="input" type="text" value="${d.lugar||''}"></label>
                        <label>Estado<select id="g-estado" class="input">
                            ${['PLAN','ABIERTO','CURSO','FINAL','CANCEL'].map(x=>`<option value="${x}" ${ (d.estado||'').toUpperCase().includes(x) ? 'selected' : '' }>${x}</option>`).join('')}
                        </select></label>
                    </div>
                </div>
            </div>
        `;
        const editBtn = document.createElement('button'); editBtn.id='g-edit-btn'; editBtn.className='btn'; editBtn.textContent='Editar'; container.querySelector('#g-save').parentElement.insertBefore(editBtn, container.querySelector('#g-save'));
        function setMode(edit){
            container.querySelector('#g-view').classList.toggle('u-hidden', !!edit);
            container.querySelector('#g-edit').classList.toggle('u-hidden', !edit);
            container.querySelector('#g-save').classList.toggle('u-hidden', !edit);
            editBtn.classList.toggle('u-hidden', !!edit);
        }
        setMode(false);
        editBtn.addEventListener('click', ()=> setMode(true));

        container.querySelector('#g-save').addEventListener('click', async ()=>{
            const msg = container.querySelector('#g-msg'); msg.textContent='Guardando...';
            const payload = { nombre: $('#g-nombre')?.value || '', fecha: $('#g-fecha')?.value || '', lugar: $('#g-lugar')?.value || '', estado: $('#g-estado')?.value || '' };
            function $(sel){ return container.querySelector(sel); }
            try{
                await api.put('/congresos/'+encodeURIComponent(id), payload);
                msg.textContent='Guardado';
                setMode(false);
                // Sync view
                container.querySelector('#g-view .contact-title').textContent = payload.nombre || '-';
                container.querySelectorAll('#g-view .contact-sub')[0].textContent = (payload.fecha||'-')+' · '+(payload.lugar||'-');
                container.querySelectorAll('#g-view .contact-sub')[1].textContent = 'Estado: '+(payload.estado||'-');
            } catch(e){ msg.textContent = (e && e.details && e.details.message) || e.message || 'Error'; }
        });

        // Cargar inscritos por contacto desde /congresos (stats v1)
        try {
            const stats = await api.get('/congresos');
            const list = (stats && stats.data) || [];
            const row = list.find(x => String(x.idCongreso||x.id) === String(id));
            const cont = container.querySelector('#g-inscritos');
            if (!row || !row.detalleContacto || row.detalleContacto.length===0) {
                cont.textContent = 'Sin datos';
            } else {
                const rows = row.detalleContacto.map(dc => [ dc.idContacto, dc.nombreContacto, dc.estudiantes, dc.asistentesExternos, dc.estudiantesInscritos ]);
                cont.innerHTML = '';
                const tbl = createTable({ columns: ['Contacto','Nombre','Estudiantes','Externos','Inscritos totales'], rows });
                cont.appendChild(tbl);
                Array.from(tbl.querySelectorAll('tbody tr')).forEach((tr, idx)=>{
                    const contactoId = row.detalleContacto[idx].idContacto;
                    tr.style.cursor='pointer';
                    tr.addEventListener('click', ()=>{ location.hash = '#/estudiantes?contactoId='+encodeURIComponent(contactoId); });
                });
            }
        } catch(e){
            const cont = container.querySelector('#g-inscritos');
            if (cont) cont.textContent = 'Error cargando inscritos';
        }
    } catch(e){ $gd.textContent='No fue posible cargar el congreso'; }
}

export function unmount(){}


