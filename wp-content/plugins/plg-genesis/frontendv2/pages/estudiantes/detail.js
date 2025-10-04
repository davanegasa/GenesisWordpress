import { api } from '../../api/client.js';
import { getCatalogs } from '../../services/catalogs.js';

function getHashParams(){ const h=location.hash; const q=h.includes('?')?h.split('?')[1]:''; const p=new URLSearchParams(q); const o={}; for(const [k,v] of p.entries()) o[k]=v; return o; }

export async function mount(container, { id } = {}){
	const hp = getHashParams();
	container.innerHTML = `
		<div class="card">
			<div class="u-flex u-gap" style="justify-content:space-between;align-items:center;">
				<div class="card-title">Estudiante <span class="badge">${id||''}</span></div>
				<div class="u-flex u-gap">
					<a id="u-back" class="btn btn-secondary" href="#/estudiantes${hp.contactoId?('?contactoId='+encodeURIComponent(hp.contactoId)) : ''}">Volver</a>
					<button id="u-guardar" class="btn btn-primary">Guardar cambios</button>
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
            <div id="u-view">
                <div class="contact-card">
                    <div class="contact-avatar">${(d.nombre1||d.nombreCompleto||'E').slice(0,1)}</div>
                    <div class="contact-body">
                        <div class="contact-title">${d.nombreCompleto || `${d.nombre1||''} ${d.apellido1||''}`.trim()}</div>
                        <div class="contact-sub">Código ${id} · CC ${d.docIdentidad||''}</div>
                        <div class="contact-sub">${d.email||''} · ${d.celular||''}</div>
                    </div>
                </div>
                <div class="divider"></div>
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
        `;
        // Botón Editar y alternancia de modo
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
	} catch (e) {
		$ed.textContent = 'No fue posible cargar el estudiante';
	}
}
export function unmount(){}

function showToast(text, isError=false){
	const t = document.createElement('div');
	t.className = 'toast';
	if (isError) t.style.borderColor = 'var(--plg-danger)';
	t.textContent = text;
	document.body.appendChild(t);
	setTimeout(()=>{ t.remove(); }, 2500);
}