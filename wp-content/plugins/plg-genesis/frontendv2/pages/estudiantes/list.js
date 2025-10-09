import { api } from '../../api/client.js';
import { createTable, createModal } from '../../components/ui/index.js';

export function mount(container) {
    container.innerHTML = `
        <div class="card">
            <div class="u-flex u-gap" style="justify-content:space-between;align-items:center;">
                <div class="card-title">Estudiantes</div>
                <a class="btn" href="#/estudiantes/nuevo">Nuevo estudiante</a>
            </div>
            <div class="u-flex u-gap">
                <input id="s-q" class="input" type="text" placeholder="Buscar por código, nombre, documento, celular o email" style="flex:1;">
                <button id="s-btn" class="btn btn-primary">Buscar</button>
            </div>
            <div id="s-table" class="u-mt-8"></div>
            <div id="s-pag" class="u-flex u-gap u-mt-8" style="justify-content:space-between;align-items:center"></div>
        </div>
    `;
    const $q = container.querySelector('#s-q');
    const $btn = container.querySelector('#s-btn');
    const $table = container.querySelector('#s-table');
    const $pag = container.querySelector('#s-pag');
    let items = [];
    let page = 1; const limit = 10; let total = 0;

	async function openDetailModal(id){
		const m = createModal({ title: 'Detalle del estudiante', bodyHtml: '<div id="d-body">Cargando...</div>', primaryLabel: 'Editar', onPrimary: ()=>{ location.hash = '#/estudiante/'+encodeURIComponent(id); }, secondaryLabel: 'Cerrar' });
		document.body.appendChild(m.overlay);
        try{
            const r = await api.get('/estudiantes/'+encodeURIComponent(id)+'/quickview');
            const p = r && r.data ? r.data : {};
            const st = p.estudiante||{};
			const nombre = st.nombreCompleto || st.nombre_completo || '';
			const doc = st.docIdentidad || st.doc_identidad || '';
			const cel = st.celular || '';
			const email = st.email || '';
            const estado = st.estadoCivil || st.estado_civil || '';
            const escolaridad = st.escolaridad || '';
            const stats = p.estadisticas||{};
            const ultimo = p.ultimo_curso||null;
            const obs = p.ultima_observacion||null;
			const contacto = st.contacto||{};
			const body = `
				<div class="u-grid u-gap" style="grid-template-columns: 1fr 1fr;">
					<div class="card">
						<div class="card-title is-info">Información Personal</div>
						<div class="detail-grid">
							<div class="field-view"><div class="field-label">Código</div><div class="field-value">${id}</div></div>
							<div class="field-view" style="grid-column:1/-1"><div class="field-label">Nombre</div><div class="field-value">${nombre}</div></div>
							<div class="field-view"><div class="field-label">Documento</div><div class="field-value">${doc||'-'}</div></div>
							<div class="field-view"><div class="field-label">Email</div><div class="field-value">${email||'-'}</div></div>
							<div class="field-view"><div class="field-label">Celular</div><div class="field-value">${cel||'-'}</div></div>
							<div class="field-view"><div class="field-label">Ciudad</div><div class="field-value">${st.ciudad||'-'}</div></div>
							<div class="field-view" style="grid-column:1/-1"><div class="field-label">Iglesia</div><div class="field-value">${st.iglesia||'-'}</div></div>
						</div>
					</div>
					<div class="card">
						<div class="card-title is-success">Estadísticas</div>
						<div class="detail-grid">
							<div class="field-view"><div class="field-label">Cursos</div><div class="field-value">${stats.total_cursos||0}</div></div>
							<div class="field-view"><div class="field-label">Promedio</div><div class="field-value">${(stats.promedio_porcentaje||0)}%</div></div>
							<div class="field-view"><div class="field-label">Últ. Actividad</div><div class="field-value">${stats.ultima_actividad?String(stats.ultima_actividad).substring(0,10):'-'}</div></div>
						</div>
					</div>
					<div class="card">
						<div class="card-title is-warning">Último Curso</div>
						${ultimo?`<div class="detail-grid"><div class="field-view"><div class="field-label">Nivel</div><div class="field-value">${ultimo.nivel||'-'}</div></div><div class="field-view" style="grid-column:1/-1"><div class="field-label">${ultimo.nombre}</div><div class="field-value">${ultimo.descripcion||''}</div></div><div class="field-view"><div class="field-label">Fecha</div><div class="field-value">${ultimo.fecha?String(ultimo.fecha).substring(0,10):'-'}</div></div><div class="field-view"><div class="field-label">Nota</div><div class="field-value">${ultimo.porcentaje!=null?ultimo.porcentaje+'%':'-'}</div></div></div>`:'<div class="hint-text">Sin registros</div>'}
					</div>
					<div class="card">
						<div class="card-title is-muted">Última Observación</div>
						${obs?`<div class="detail-grid"><div class="field-view"><div class="field-label">Tipo</div><div class="field-value">${obs.tipo||'General'}</div></div><div class="field-view" style="grid-column:1/-1"><div class="field-label">Texto</div><div class="field-value">${obs.observacion}</div></div><div class="field-view"><div class="field-label">Fecha</div><div class="field-value">${obs.fecha?String(obs.fecha).substring(0,10):'-'}</div></div><div class="field-view"><div class="field-label">Usuario</div><div class="field-value">${obs.usuario_nombre||'Sistema'}</div></div></div>`:'<div class="hint-text">Sin observaciones</div>'}
					</div>
					<div class="card">
						<div class="card-title is-info">Información de Contacto</div>
						<div class="detail-grid"><div class="field-view"><div class="field-label">Código</div><div class="field-value">${contacto.codigo||'-'}</div></div><div class="field-view" style="grid-column:1/-1"><div class="field-label">Nombre</div><div class="field-value">${contacto.nombre||'-'}</div></div><div class="field-view" style="grid-column:1/-1"><div class="field-label">Iglesia</div><div class="field-value">${contacto.iglesia||'-'}</div></div></div>
					</div>
				</div>
			`;
			m.setBody(body);
		} catch(e){
			const el = document.querySelector('#d-body'); if (el) el.innerHTML = '<div>Error cargando detalle</div>';
		}
	}
    function renderPagination(){
        if (!total) { $pag.innerHTML=''; return; }
        const startIdx = (page-1)*limit; const endIdx = Math.min(startIdx+limit, total);
        $pag.innerHTML = `
            <div class="hint-text">Mostrando ${startIdx+1}-${endIdx} de ${total}</div>
            <div class="u-flex u-gap">
                <button id="p-prev" class="btn">Anterior</button>
                <button id="p-next" class="btn">Siguiente</button>
            </div>
        `;
        const $prev=$pag.querySelector('#p-prev'), $next=$pag.querySelector('#p-next');
        $prev.disabled = page===1; $next.disabled = endIdx>=total;
        $prev.onclick = ()=>{ if (page>1){ page--; load(); } };
        $next.onclick = ()=>{ if (endIdx<total){ page++; load(); } };
    }

    function renderTable(){
        const rows = (items||[]).map(st=>[
            st.idEstudiante||'',
            st.nombreCompleto||'',
            st.docIdentidad||'',
            st.celular||'',
            st.email||''
        ]);
        $table.innerHTML='';
        const tbl = createTable({ columns:['Código','Nombre','Documento','Celular','Email'], rows });
        $table.appendChild(tbl);
        Array.from(tbl.querySelectorAll('tbody tr')).forEach((tr, idx)=>{
            const id = items[idx] && items[idx].idEstudiante;
            tr.style.cursor='pointer';
            tr.addEventListener('click', ()=>{ if (id) openDetailModal(id); });
            // Edición inline para documento, celular y email
            const tdDoc = tr.children[2]; const tdCel = tr.children[3]; const tdEml = tr.children[4];
            function makeInlineEdit(td, key, placeholder){
                td.title = 'Doble clic para editar';
                td.addEventListener('dblclick', (e)=>{
                    e.stopPropagation();
                    // Evitar crear múltiples inputs si ya está editando
                    if (td.querySelector('input')) return;
                    const old = td.textContent || '';
                    const input = document.createElement('input'); input.className='input'; input.value = old; input.placeholder = placeholder || '';
                    td.innerHTML=''; td.appendChild(input); input.focus();
                    function finish(save){
                        const nv = input.value.trim();
                        if (!save){ td.textContent = old; return; }
                        if (nv === old) { td.textContent = old; return; }
                        const payload = {}; payload[key] = nv;
                        api.put('/estudiantes/'+encodeURIComponent(id), payload).then(()=>{ td.textContent = nv; showToast('Actualizado'); }).catch(()=>{ td.textContent = old; showToast('Error actualizando', true); });
                    }
                    input.addEventListener('keydown', (ev)=>{ if (ev.key==='Enter'){ finish(true); } else if (ev.key==='Escape'){ finish(false); } });
                    // En blur NO guardar para evitar borrados accidentales
                    input.addEventListener('blur', ()=> finish(false));
                });
            }
            makeInlineEdit(tdDoc, 'doc_identidad', 'Documento');
            makeInlineEdit(tdCel, 'celular', 'Celular');
            makeInlineEdit(tdEml, 'email', 'Email');
        });
    }

    async function load(){
        $table.textContent='Cargando...';
        try{
            const r = await api.get('/estudiantes?q='+encodeURIComponent($q.value||'')+'&page='+page+'&limit='+limit);
            const d = (r && r.data) || {}; items = d.items||[]; total = d.total||0; renderTable(); renderPagination();
        }catch(e){ $table.textContent='Error'; showToast('Error cargando estudiantes',true); }
    }

    $btn.addEventListener('click', ()=>{ page=1; load(); });
    let t=null; $q.addEventListener('input', ()=>{ clearTimeout(t); t=setTimeout(()=>{ page=1; load(); }, 350); });
    load();
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