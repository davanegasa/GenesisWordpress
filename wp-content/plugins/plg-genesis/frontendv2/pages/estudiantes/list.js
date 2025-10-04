import { api } from '../../api/client.js';
import { createTable } from '../../components/ui/index.js';

export function mount(container) {
	container.innerHTML = `
		<div class="card">
			<div class="card-title">Buscar contacto</div>
			<div class="u-flex u-gap">
				<input id="s-q" class="input" type="text" placeholder="Nombre / Iglesia / Email" style="flex:1;">
				<button id="s-btn" class="btn btn-primary">Buscar</button>
			</div>
			<div id="s-contacts" class="listbox u-mt-8" role="listbox" aria-label="Resultados de contactos"></div>
			<div class="u-flex u-gap u-mt-16">
				<button id="s-load" class="btn">Cargar estudiantes</button>
				<button id="s-new" class="btn">Nuevo estudiante</button>
			</div>
		</div>
		<div class="card u-mt-16">
			<div class="card-title">Estudiantes</div>
			<div id="s-table">Seleccione un contacto y pulse "Cargar estudiantes"</div>
			<div id="s-pag" class="u-flex u-gap u-mt-8" style="justify-content:space-between;align-items:center"></div>
		</div>
	`;
	const $q = container.querySelector('#s-q');
	const $btn = container.querySelector('#s-btn');
	const $list = container.querySelector('#s-contacts');
	const $table = container.querySelector('#s-table');
		const $pag = container.querySelector('#s-pag');
		let studentsData = [];
		let currentPage = 1;
		const pageSize = 10;
		let selectedContactId = '';

		function getHashParams(){ const h=location.hash; const q=h.includes('?')?h.split('?')[1]:''; const p=new URLSearchParams(q); const o={}; for(const [k,v] of p.entries()) o[k]=v; return o; }
		const hp = getHashParams();

		function setSelectedContact(id){
			selectedContactId = id ? String(id) : '';
			Array.from($list.querySelectorAll('.listbox-item')).forEach(el=>{
				const on = el.dataset.id === selectedContactId;
				el.classList.toggle('selected', on);
				el.setAttribute('aria-selected', String(on));
			});
		}

	async function searchContacts(){
		$list.innerHTML = '<div class="hint-text" role="status">Cargando...</div>';
		try {
			const r = await api.get('/contactos?q='+encodeURIComponent($q.value||'')+'&limit=20&offset=0');
			const items = (r && r.data && r.data.items) || [];
			$list.innerHTML = '';
			items.forEach(it=>{
				const row = document.createElement('div');
				row.className = 'listbox-item';
				row.setAttribute('role', 'option');
				row.dataset.id = String(it.id);
				row.textContent = `${it.id} - ${it.nombre}${it.iglesia?(' — '+it.iglesia):''}`;
				row.addEventListener('click', ()=> setSelectedContact(String(it.id)));
				$list.appendChild(row);
			});
			if (hp.contactoId && items.some(it=> String(it.id)===String(hp.contactoId))) {
				setSelectedContact(String(hp.contactoId));
				await loadStudents();
			} else if (items.length>0) setSelectedContact(String(items[0].id)); else setSelectedContact('');
		} catch(e){
			$list.innerHTML = '<div class="error-text">Error al buscar</div>';
		}
	}
	async function openDetailModal(id){
		const prev = document.querySelector('.modal-overlay');
		if (prev) prev.remove();
		const overlay = document.createElement('div');
		overlay.className = 'modal-overlay';
		const modal = document.createElement('div');
		modal.className = 'modal';
		modal.setAttribute('role','dialog');
		modal.setAttribute('aria-modal','true');
		modal.innerHTML = `
			<div class="modal-header">
				<strong>Detalle del estudiante</strong>
				<button id="d-close" class="btn">Cerrar</button>
			</div>
			<div class="modal-body" id="d-body"><div>Cargando...</div></div>
			<div class="modal-footer">
				<a id="d-edit" class="btn btn-primary" href="#/estudiante/${encodeURIComponent(id)}">Editar</a>
				<button id="d-close2" class="btn">Cerrar</button>
			</div>
		`;
		overlay.appendChild(modal); document.body.appendChild(overlay);
		overlay.addEventListener('click', (e)=>{ if (e.target===overlay) overlay.remove(); });
		modal.querySelector('#d-close').addEventListener('click', ()=> overlay.remove());
		modal.querySelector('#d-close2').addEventListener('click', ()=> overlay.remove());
		// Cerrar y navegar al modo edición
		modal.querySelector('#d-edit').addEventListener('click', (e)=>{
			e.preventDefault();
			overlay.remove();
			const qp = selectedContactId ? ('?contactoId='+encodeURIComponent(selectedContactId)) : '';
			location.hash = '#/estudiante/'+encodeURIComponent(id)+qp;
		});
		// Accesibilidad: foco inicial y cerrar con ESC
		const focusable = () => Array.from(modal.querySelectorAll('button, a, [href], input, select, textarea')).filter(el=>!el.disabled);
		const setInitialFocus = ()=>{ const f=focusable()[0]; if (f) f.focus(); };
		setTimeout(setInitialFocus, 0);
		function onKey(e){
			if (e.key === 'Escape') { overlay.remove(); return; }
			if (e.key === 'Tab') {
				const els = focusable(); if (els.length===0) return;
				const first = els[0], last = els[els.length-1];
				if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
				else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
			}
		}
		document.addEventListener('keydown', onKey);
		overlay.addEventListener('remove', ()=> document.removeEventListener('keydown', onKey));
		try{
			const r = await api.get('/estudiantes/'+encodeURIComponent(id));
			const st = r && r.data ? r.data : r || {};
			const nombre = st.nombreCompleto || st.nombre_completo || '';
			const doc = st.docIdentidad || st.doc_identidad || '';
			const cel = st.celular || '';
			const email = st.email || '';
			const estado = st.estadoCivil || st.estado_civil || '';
			const escolaridad = st.escolaridad || '';
			const direccion = st.direccion || '';
			const body = `
				<div class="contact-card">
					<div class="contact-avatar">${(nombre||'E').slice(0,1)}</div>
					<div class="contact-body">
						<div class="contact-title">${nombre}</div>
						<div class="contact-sub">Código ${id} · CC ${doc}</div>
						<div class="contact-sub">${email} · ${cel}</div>
					</div>
				</div>
				<div class="divider"></div>
				<div class="detail-grid">
					<div class="field-view"><div class="field-label">Estado civil</div><div class="field-value">${estado || '-'}</div></div>
					<div class="field-view"><div class="field-label">Escolaridad</div><div class="field-value">${escolaridad || '-'}</div></div>
					<div class="field-view" style="grid-column:1/-1"><div class="field-label">Dirección</div><div class="field-value">${direccion || '-'}</div></div>
				</div>
			`;
			modal.querySelector('#d-body').innerHTML = body;
		} catch(e){
			modal.querySelector('#d-body').innerHTML = '<div>Error cargando detalle</div>';
		}
	}
	function renderPagination(total, startIdx, endIdx){
			if (!$pag) return;
			if (!total) { $pag.innerHTML = ''; return; }
			$pag.innerHTML = `
				<div class="hint-text">Mostrando ${startIdx+1}-${endIdx} de ${total}</div>
				<div class="u-flex u-gap">
					<button id="p-prev" class="btn">Anterior</button>
					<button id="p-next" class="btn">Siguiente</button>
				</div>
			`;
			const $prev = $pag.querySelector('#p-prev');
			const $next = $pag.querySelector('#p-next');
			$prev.disabled = currentPage === 1;
			$next.disabled = endIdx >= total;
			$prev.addEventListener('click', ()=>{ if (currentPage>1){ currentPage--; renderRows(); } });
			$next.addEventListener('click', ()=>{ if (endIdx<total){ currentPage++; renderRows(); } });
		}

		function renderRows(){
			if (!studentsData || studentsData.length === 0){
			$table.innerHTML = '';
			$table.appendChild(createTable({ columns: ['Código','Nombre','Documento','Celular','Email'], rows: [] }));
			renderPagination(0,0,0);
				return;
			}
			const total = studentsData.length;
			const startIdx = (currentPage - 1) * pageSize;
			const endIdx = Math.min(startIdx + pageSize, total);
			const visible = studentsData.slice(startIdx, endIdx);
			const rows = visible.map(st => [
				st.idEstudiante || st.id_estudiante || '',
				st.nombreCompleto || st.nombre_completo || '',
				st.docIdentidad || st.doc_identidad || '',
				st.celular || '',
				st.email || ''
			]);
			$table.innerHTML = '';
			const tbl = createTable({ columns: ['Código','Nombre','Documento','Celular','Email'], rows });
			$table.appendChild(tbl);
			Array.from(tbl.querySelectorAll('tbody tr')).forEach((tr, idx) => {
				const id = visible[idx] && (visible[idx].idEstudiante || visible[idx].id_estudiante);
				tr.style.cursor = 'pointer';
				tr.addEventListener('click', ()=>{ if (id) openDetailModal(id); });
			});
			renderPagination(total, startIdx, endIdx);
		}

		async function loadStudents(){
		if (!selectedContactId) return;
		$table.textContent = 'Cargando...';
		try {
				const r = await api.get('/estudiantes?contactoId='+encodeURIComponent(selectedContactId));
				const payload = (r && typeof r === 'object' && 'data' in r) ? r.data : r;
				studentsData = Array.isArray(payload) ? payload : (payload && Array.isArray(payload.items) ? payload.items : []);
				currentPage = 1;
				renderRows();
			} catch(e){
				console.error('Error cargando /estudiantes', e);
				const msg = (e && (e.details && e.details.message)) || e.message || 'Error';
				$table.textContent = msg;
				showToast('Error al cargar estudiantes: '+msg, true);
		}
	}

	$btn.addEventListener('click', searchContacts);
	$q.addEventListener('keyup', (e)=>{ if (e.key==='Enter') searchContacts(); });
	container.querySelector('#s-load').addEventListener('click', loadStudents);
	container.querySelector('#s-new').addEventListener('click', ()=>{ if (selectedContactId) location.hash = '#/estudiantes/nuevo'; });

	searchContacts();
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