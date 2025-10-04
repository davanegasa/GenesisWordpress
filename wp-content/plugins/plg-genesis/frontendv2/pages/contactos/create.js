import { api } from '../../api/client.js';

export function mount(container){
    container.innerHTML = `
        <div class="card">
            <div class="card-title">Nuevo contacto</div>
            <div class="form-grid">
                <label>Nombre<input id="c-nombre" class="input" type="text" placeholder="Nombre completo"></label>
                <label>Iglesia<input id="c-iglesia" class="input" type="text" placeholder="Iglesia"></label>
                <label>Email<input id="c-email" class="input" type="email" placeholder="correo@dominio.com"></label>
                <label>Celular<input id="c-celular" class="input" type="text" placeholder="Número de contacto"></label>
                <label>Dirección<input id="c-direccion" class="input" type="text" placeholder="Dirección"></label>
                <label>Ciudad<input id="c-ciudad" class="input" type="text" placeholder="Ciudad"></label>
            </div>
            <div class="u-flex u-gap u-mt-16">
                <button id="c-crear" class="btn btn-primary">Crear</button>
                <a href="#/contactos" class="btn">Volver</a>
            </div>
            <pre id="c-msg" class="u-mt-16"></pre>
        </div>
    `;

    const $ = id => container.querySelector(id);
    function showToast(text, isError=false){ const t=document.createElement('div'); t.className='toast'; if(isError)t.style.borderColor='var(--plg-danger)'; t.textContent=text; document.body.appendChild(t); setTimeout(()=>t.remove(),2500); }

    $('#c-crear').addEventListener('click', async ()=>{
        const msg = $('#c-msg'); msg.textContent = 'Creando...';
        const nombre = $('#c-nombre').value.trim();
        if (!nombre) { showToast('El nombre es requerido', true); msg.textContent='El nombre es requerido'; return; }
        const payload = {
            nombre,
            iglesia: $('#c-iglesia').value,
            email: $('#c-email').value,
            celular: $('#c-celular').value,
            direccion: $('#c-direccion').value,
            ciudad: $('#c-ciudad').value,
        };
        try{
            const res = await api.post('/contactos', payload);
            const id = res && res.data && res.data.id;
            showToast('Contacto creado');
            msg.textContent = 'Creado: ID '+id;
            // Modal de confirmación
            const overlay = document.createElement('div'); overlay.className='modal-overlay';
            const modal = document.createElement('div'); modal.className='modal';
            modal.innerHTML = `
                <div class="modal-header"><strong>Contacto creado</strong><button id="x-close" class="btn">Cerrar</button></div>
                <div class="modal-body">
                    <div class="contact-card">
                        <div class="contact-avatar">${($('#c-nombre').value||'C').slice(0,1)}</div>
                        <div class="contact-body">
                            <div class="contact-title">${$('#c-nombre').value||'-'}</div>
                            <div class="contact-sub">ID ${id}</div>
                            <div class="contact-sub">${$('#c-email').value||''} ${$('#c-celular').value?('· '+$('#c-celular').value):''}</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button id="x-ok" class="btn btn-primary">Entendido</button></div>
            `;
            overlay.appendChild(modal); document.body.appendChild(overlay);
            const close=()=> overlay.remove(); modal.querySelector('#x-close').addEventListener('click', close); modal.querySelector('#x-ok').addEventListener('click', close);
            // Reset del formulario
            ['#c-nombre','#c-iglesia','#c-email','#c-celular','#c-direccion','#c-ciudad'].forEach(sel=>{ const el=document.querySelector(sel); if(el) el.value=''; });
        } catch(e){
            const errMsg = (e && e.details && e.details.message) || e.message || 'Error al crear';
            showToast('Error al crear: '+errMsg, true);
            msg.textContent = errMsg;
        }
    });
}

export function unmount(){}


