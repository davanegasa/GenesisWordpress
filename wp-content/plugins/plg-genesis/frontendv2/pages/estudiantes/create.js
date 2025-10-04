import { api } from '../../api/client.js';
import { getCatalogs } from '../../services/catalogs.js';

export function mount(container){
	container.innerHTML = `
		<div class="card">
			<div class="card-title">Nuevo estudiante</div>
			<div class="section">
				<div class="section-title">Contacto</div>
				<div class="form-grid">
					<label>Contacto seleccionado
						<div class="u-flex u-gap">
							<input id="e-contact-display" class="input" type="text" placeholder="Ninguno" readonly style="flex:1;">
							<button id="e-contact-open" class="btn">Buscar</button>
						</div>
						<input id="e-id_contacto" type="hidden">
						<span class="hint-text">Usa "Buscar" para escoger un contacto</span>
					</label>
				</div>
				<div class="divider"></div>
			</div>
			<div class="section">
				<div class="section-title">Identificación</div>
				<div class="form-grid">
					<label>Documento<input id="e-doc_identidad" class="input" type="text" placeholder="CC/NIT"><span id="err-doc" class="error-text u-hidden">Documento ya existe</span></label>
					<label class="u-flex" style="align-items:center;gap:8px"><input id="e-usetoggle" type="checkbox"> Usar código manual</label>
					<label id="row-code" class="u-hidden">Código estudiante<input id="e-id_estudiante" class="input" type="text" placeholder="Código manual"><span class="hint-text">Opcional si no está seleccionado</span></label>
				</div>
				<div class="divider"></div>
			</div>
			<div class="section">
				<div class="section-title">Nombres</div>
				<div class="form-grid">
					<label>Nombre 1<input id="e-nombre1" class="input" type="text" placeholder="Nombre principal"></label>
					<label>Nombre 2<input id="e-nombre2" class="input" type="text" placeholder="Segundo nombre"></label>
					<label>Apellido 1<input id="e-apellido1" class="input" type="text" placeholder="Primer apellido"></label>
					<label>Apellido 2<input id="e-apellido2" class="input" type="text" placeholder="Segundo apellido"></label>
				</div>
				<div class="divider"></div>
			</div>
			<div class="section">
				<div class="section-title">Contacto</div>
				<div class="form-grid">
					<label>Celular<input id="e-celular" class="input" type="text" placeholder="Número de contacto"></label>
					<label>Email<input id="e-email" class="input" type="email" placeholder="correo@dominio.com"></label>
				</div>
				<div class="divider"></div>
			</div>
			<div class="section">
				<div class="section-title">Ubicación/Iglesia</div>
				<div class="form-grid">
					<label>Ciudad<input id="e-ciudad" class="input" type="text" placeholder="Ciudad"></label>
					<label>Iglesia<input id="e-iglesia" class="input" type="text" placeholder="Iglesia"></label>
				</div>
				<div class="divider"></div>
			</div>
			<div class="section">
				<div class="section-title">Perfil</div>
				<div class="form-grid">
					<label>Estado civil<select id="e-estado_civil" class="input"></select><span id="err-estado" class="error-text u-hidden">Requerido</span></label>
					<label>Escolaridad<select id="e-escolaridad" class="input"></select><span id="err-edu" class="error-text u-hidden">Requerido</span></label>
					<label>Ocupación<input id="e-ocupacion" class="input" type="text" placeholder="Profesión u ocupación"></label>
				</div>
			</div>
			<div class="u-flex u-gap u-mt-16">
				<button id="e-sugerir" class="btn">Sugerir código</button>
				<button id="e-crear" class="btn btn-primary">Crear</button>
				<a href="#/estudiantes" class="btn">Volver</a>
			</div>
			<pre id="e-msg" class="u-mt-16"></pre>
		</div>
	`;

	// Poblar dropdowns desde catálogos
	getCatalogs().then(cat => {
		const civils = (cat && cat.civilStatus) || [];
		const edu = (cat && cat.educationLevel) || [];
		const fill = (el, arr) => { el.innerHTML = '<option value=\"\">Seleccione…</option>'+arr.map(x=>`<option value=\"${x}\">${x}</option>`).join(''); };
		const civilEl = container.querySelector('#e-estado_civil');
		const eduEl = container.querySelector('#e-escolaridad');
		if (civilEl) fill(civilEl, civils);
		if (eduEl) fill(eduEl, edu);
	});

	const $ = (id)=> container.querySelector(id);
// Picker de contacto en modal
function openContactPicker(){
	const prev = document.querySelector('.modal-overlay'); if (prev) prev.remove();
	const overlay = document.createElement('div'); overlay.className = 'modal-overlay';
	const modal = document.createElement('div'); modal.className = 'modal'; modal.setAttribute('role','dialog'); modal.setAttribute('aria-modal','true');
	modal.innerHTML = `
		<div class="modal-header">
			<strong>Seleccionar contacto</strong>
			<button id="c-close" class="btn">Cerrar</button>
		</div>
		<div class="modal-body">
			<div class="u-flex u-gap">
				<input id="c-q" class="input" type="text" placeholder="Nombre / Iglesia / Email" style="flex:1;">
				<button id="c-buscar" class="btn">Buscar</button>
			</div>
			<div id="c-list" class="listbox u-mt-8" role="listbox" aria-label="Resultados"></div>
		</div>
		<div class="modal-footer">
			<button id="c-close2" class="btn">Cerrar</button>
		</div>
	`;
	overlay.appendChild(modal); document.body.appendChild(overlay);
	const $q = modal.querySelector('#c-q'); const $btn = modal.querySelector('#c-buscar'); const $list = modal.querySelector('#c-list');
	function select(it){
		$('#e-id_contacto').value = String(it.id);
		$('#e-contact-display').value = `${it.id} - ${it.nombre}${it.iglesia?(' — '+it.iglesia):''}`;
		overlay.remove();
	}
	async function search(){
		$list.innerHTML = '<div class="hint-text" role="status">Cargando...</div>';
		try{
			const r = await api.get('/contactos?q='+encodeURIComponent($q.value||'')+'&limit=20&offset=0');
			const items = (r && r.data && r.data.items) || [];
			$list.innerHTML = '';
			items.forEach(it=>{
				const row = document.createElement('div');
				row.className = 'listbox-item'; row.setAttribute('role','option'); row.dataset.id = String(it.id);
				row.textContent = `${it.id} - ${it.nombre}${it.iglesia?(' — '+it.iglesia):''}`;
				row.addEventListener('click', ()=> select(it));
				$list.appendChild(row);
			});
			if (items.length===0) $list.innerHTML = '<div class="hint-text">Sin resultados</div>';
		} catch(e){ $list.innerHTML = '<div class="error-text">Error al buscar</div>'; }
	}
	modal.querySelector('#c-close').addEventListener('click', ()=> overlay.remove());
	modal.querySelector('#c-close2').addEventListener('click', ()=> overlay.remove());
	$btn.addEventListener('click', search);
	$q.addEventListener('keyup', (e)=>{ if (e.key==='Enter') search(); });
	setTimeout(()=>{ $q.focus(); }, 0);
	search();
}
document.getElementById('e-contact-open').addEventListener('click', openContactPicker);

	function invalid(el, errEl, on){ if (!el || !errEl) return; el.classList.toggle('invalid', !!on); errEl.classList.toggle('u-hidden', !on); }

	// Toggle código manual
	$('#e-usetoggle').addEventListener('change', (e)=>{ $('#row-code').classList.toggle('u-hidden', !e.target.checked); });

	// Sugerir código
	$('#e-sugerir').addEventListener('click', async ()=>{
		const msg = $('#e-msg');
		if (!$('#e-id_contacto').value) { msg.textContent = 'Ingrese Contacto ID para sugerir'; return; }
		msg.textContent = 'Calculando...';
		try {
			const r = await api.get('/estudiantes/next-code?contactoId='+encodeURIComponent($('#e-id_contacto').value));
			const code = r && r.data && r.data.code; if (code) { $('#e-id_estudiante').value = code; $('#e-usetoggle').checked = true; $('#row-code').classList.remove('u-hidden'); }
			msg.textContent = 'Sugerido: '+code;
		} catch(e){ msg.textContent = e.message || 'Error sugiriendo'; }
	});

	// Verificar duplicado documento on-blur
	$('#e-doc_identidad').addEventListener('blur', async ()=>{
		const el = $('#e-doc_identidad'); const err = $('#err-doc');
		if (!el.value) { invalid(el, err, false); return; }
		try { const r = await api.get('/estudiantes/exists?doc='+encodeURIComponent(el.value)); const ex = r && r.data && r.data.exists; invalid(el, err, !!ex); } catch {}
	});

	$('#e-crear').addEventListener('click', async ()=>{
		const msg = $('#e-msg');
		const btn = $('#e-crear');
		msg.textContent = 'Creando...';
		if (!$('#e-id_contacto').value) { msg.textContent = 'Selecciona un contacto'; showToast('Selecciona un contacto', true); return; }

		const civilEl = $('#e-estado_civil');
		const eduEl = $('#e-escolaridad');
		let hasError = false;
		if (!civilEl.value) { invalid(civilEl, $('#err-estado'), true); hasError = true; } else { invalid(civilEl, $('#err-estado'), false); }
		if (!eduEl.value) { invalid(eduEl, $('#err-edu'), true); hasError = true; } else { invalid(eduEl, $('#err-edu'), false); }
		if (!$('#e-id_contacto').value) { hasError = true; showToast('Selecciona un contacto de la lista', true); }
		if (!$('#e-doc_identidad').value) { hasError = true; $('#e-doc_identidad').classList.add('invalid'); }
		if (!$('#e-nombre1').value) { hasError = true; $('#e-nombre1').classList.add('invalid'); }
		if (!$('#e-apellido1').value) { hasError = true; $('#e-apellido1').classList.add('invalid'); }
		if (hasError) { msg.textContent = 'Por favor completa los campos requeridos'; showToast('Por favor completa los campos requeridos', true); return; }

		const payload = {
			id_contacto: $('#e-id_contacto').value,
			doc_identidad: $('#e-doc_identidad').value,
			nombre1: $('#e-nombre1').value,
			nombre2: $('#e-nombre2').value,
			apellido1: $('#e-apellido1').value,
			apellido2: $('#e-apellido2').value,
			celular: $('#e-celular').value,
			email: $('#e-email').value,
			ciudad: $('#e-ciudad').value,
			iglesia: $('#e-iglesia').value,
			estado_civil: civilEl.value,
			escolaridad: eduEl.value,
			ocupacion: $('#e-ocupacion').value,
			...( $('#e-usetoggle').checked && $('#e-id_estudiante').value ? { id_estudiante: $('#e-id_estudiante').value } : {} )
		};
		try {
			btn.disabled = true; const prev = btn.textContent; btn.textContent = 'Creando…';
			const r = await api.post('/estudiantes', payload);
			const id = r && r.data && r.data.idEstudiante ? r.data.idEstudiante : '';
			msg.textContent = id ? `Creado. Código: ${id}` : 'Creado';
			showToast('Estudiante creado');
			if (id) openCreatedModal(id, `${$('#e-nombre1').value} ${$('#e-apellido1').value}`.trim());
			btn.disabled = false; btn.textContent = prev;
		} catch(e){
			const errMsg = (e && e.details && e.details.message) || e.message || 'Error al crear';
			msg.textContent = errMsg;
			showToast('Error al crear: '+errMsg, true);
			btn.disabled = false; btn.textContent = 'Crear';
		}
	});
}
export function unmount() {}

function showToast(text, isError=false){
	const t = document.createElement('div');
	t.className = 'toast';
	if (isError) t.style.borderColor = 'var(--plg-danger)';
	t.textContent = text;
	document.body.appendChild(t);
	setTimeout(()=>{ t.remove(); }, 2500);
}

function openCreatedModal(id, nombre){
	const prev = document.querySelector('.modal-overlay'); if (prev) prev.remove();
	const overlay = document.createElement('div'); overlay.className = 'modal-overlay';
	const modal = document.createElement('div'); modal.className = 'modal';
	modal.innerHTML = `
		<div class="modal-header">
			<strong>Estudiante creado</strong>
			<button id="m-close" class="btn">Cerrar</button>
		</div>
		<div class="modal-body">
			<div class="contact-card">
				<div class="contact-avatar">${(nombre||'E').slice(0,1)}</div>
				<div class="contact-body">
					<div class="contact-title">${nombre || '—'}</div>
					<div class="contact-sub">Código ${id}</div>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			<button id="m-close2" class="btn btn-primary">Entendido</button>
		</div>
	`;
	overlay.appendChild(modal); document.body.appendChild(overlay);
	const close = ()=> overlay.remove();
	modal.querySelector('#m-close').addEventListener('click', close);
	modal.querySelector('#m-close2').addEventListener('click', close);
}