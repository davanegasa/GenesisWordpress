import { api } from '../../api/client.js';

export async function mount(container, { code } = {}){
    container.innerHTML = `
        <div class="card">
            <div class="u-flex u-gap" style="justify-content:space-between;align-items:center;">
                <div class="card-title">Contacto <span class="badge">${code||''}</span></div>
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
        const res = await api.get('/contactos/'+encodeURIComponent(code));
        const d = (res && res.data) || {};
        $ct.innerHTML = `
            <div id="c-view">
                <div class="contact-card">
                    <div class="contact-avatar">${(d.nombre||'C').slice(0,1)}</div>
                    <div class="contact-body">
                        <div class="contact-title">${d.nombre||'-'}</div>
                        <div class="contact-sub">Código ${d.code||code} · ${d.iglesia||''}</div>
                        <div class="contact-sub">${d.email||''} · ${d.celular||''}</div>
                    </div>
                </div>
                <div class="divider"></div>
                <div class="detail-grid">
                    <div class="field-view"><div class="field-label">Dirección</div><div class="field-value">${d.direccion||'-'}</div></div>
                    <div class="field-view"><div class="field-label">Ciudad</div><div class="field-value">${d.ciudad||'-'}</div></div>
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
        `;
        const header = container.querySelector('#c-save').parentElement;
        const editBtn = document.createElement('button'); editBtn.id='c-edit-btn'; editBtn.className='btn'; editBtn.textContent='Editar'; header.insertBefore(editBtn, header.firstChild);
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
                await api.put('/contactos/'+encodeURIComponent(code), payload);
                msg.textContent = 'Guardado';
                // Sync view
                container.querySelector('#c-view .contact-title').textContent = payload.nombre || '-';
                container.querySelectorAll('#c-view .contact-sub')[0].textContent = 'Código '+code+' · '+(payload.iglesia||'');
                container.querySelectorAll('#c-view .contact-sub')[1].textContent = (payload.email||'')+' · '+(payload.celular||'');
                const values = container.querySelectorAll('#c-view .field-view .field-value');
                values[0].textContent = payload.direccion || '-';
                values[1].textContent = payload.ciudad || '-';
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


