import { api } from '../../api/client.js';
import { createTable, showToast } from '../../components/ui/index.js';
import { createModal } from '../../components/ui/index.js';
import { openAssignCourseModal } from '../../components/estudiantes/assignCourse.js';

export async function mount(container){
	container.innerHTML = `
		<div class="card">
			<div class="u-flex u-gap" style="justify-content:space-between;align-items:center;">
				<div class="card-title">Gestionar Estudiantes</div>
				<a class="btn" href="#/estudiantes/nuevo">Crear Estudiante</a>
			</div>
			<div class="u-flex u-gap">
				<input id="q" class="input" type="text" placeholder="Buscar estudiante" style="flex:1;">
				<button id="b" class="btn btn-primary">Buscar</button>
			</div>
			<div id="tbl" class="u-mt-8"></div>
		</div>
	`;
	const $q = container.querySelector('#q');
	const $b = container.querySelector('#b');
	const $tbl = container.querySelector('#tbl');
	let page=1, limit=10, total=0, items=[];

    function render(){
        const rows = items.map(st=>[
            st.idEstudiante||'', st.nombreCompleto||'', st.docIdentidad||'', st.celular||'', st.email||'', { html: `<div class=\"u-flex u-gap action-icons\"><button class=\"btn icon-btn\" title=\"Detalle\" data-action=\"view\" data-id=\"${st.idEstudiante}\" data-name=\"${(st.nombreCompleto||'').replace(/\"/g,'\\\"')}\">🔍</button><button class=\"btn icon-btn\" title=\"Asignar\" data-action=\"assign\" data-id=\"${st.idEstudiante}\" data-name=\"${(st.nombreCompleto||'').replace(/\"/g,'\\\"')}\">➕</button><button class=\"btn icon-btn\" title=\"Observaciones\" data-action=\"obs\" data-id=\"${st.idEstudiante}\" data-name=\"${(st.nombreCompleto||'').replace(/\"/g,'\\\"')}\">💬</button></div>` }
        ]);
		$tbl.innerHTML='';
		const t = createTable({ columns:['Código','Nombre','Documento','Celular','Email',''], rows });
		$tbl.appendChild(t);
        Array.from(t.querySelectorAll('tbody tr')).forEach((tr, idx)=>{
            const id = items[idx] && items[idx].idEstudiante; const name = items[idx] && items[idx].nombreCompleto;
            const btnV = tr.querySelector('button[data-action="view"]'); if (btnV){ btnV.onclick = ()=> openQuickView(id, name); }
            const btnA = tr.querySelector('button[data-action="assign"]'); if (btnA){ btnA.onclick = ()=> openAssign(id, name); }
            const btnO = tr.querySelector('button[data-action="obs"]'); if (btnO){ btnO.onclick = ()=> openObs(id, name); }
			// Edición inline de documento/celular/email
			const tdDoc = tr.children[2]; const tdCel = tr.children[3]; const tdEml = tr.children[4];
			function makeInlineEdit(td, key, placeholder){
				td.title = 'Doble clic para editar';
				td.addEventListener('dblclick', (e)=>{
					e.stopPropagation();
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
					input.addEventListener('blur', ()=> finish(false));
				});
			}
			makeInlineEdit(tdDoc, 'doc_identidad', 'Documento');
			makeInlineEdit(tdCel, 'celular', 'Celular');
			makeInlineEdit(tdEml, 'email', 'Email');
        });
	}

	async function load(){
		$tbl.textContent='Cargando...';
		try{ const r = await api.get('/estudiantes?q='+encodeURIComponent($q.value||'')+'&page='+page+'&limit='+limit); const d=(r&&r.data)||{}; items=d.items||[]; total=d.total||0; render(); }catch(_){ $tbl.textContent='Error'; }
	}

	async function openAssign(id, studentName){
		openAssignCourseModal(id, studentName);
	}

	async function openObs(id, studentName){
		const m = createModal({ title: `Observaciones — ${studentName||id}`, bodyHtml: `
			<div class=\"u-flex u-gap\" style=\"align-items:flex-start\"> 
				<textarea id=\"obst\" class=\"input\" placeholder=\"Escribe una observación...\" style=\"min-height:88px;flex:1\"></textarea>
			</div>
			<div id=\"obsm\" class=\"hint-text u-mt-8\"></div>
			<div class=\"divider\"></div>
			<div id=\"obslist\" class=\"listbox\" role=\"listbox\"></div>
		`, primaryLabel: 'Guardar', onPrimary: async (close)=>{
			const txt = (document.querySelector('#obst').value||'').trim(); if (!txt){ document.querySelector('#obsm').textContent='Texto requerido'; return; }
			try { await api.post('/estudiantes/'+encodeURIComponent(id)+'/observaciones', { observacion: txt }); showToast('Observación guardada'); document.querySelector('#obst').value=''; await loadList(); }
			catch(_){ document.querySelector('#obsm').textContent='Error guardando'; }
		}, secondaryLabel: 'Cerrar' });
		document.body.appendChild(m.overlay);
		async function loadList(){
			const $list = document.querySelector('#obslist');
			$list.innerHTML = '<div class="listbox-item">Cargando...</div>';
			try{ const r = await api.get('/estudiantes/'+encodeURIComponent(id)+'/observaciones'); const arr = (r&&r.data&&r.data.observaciones)||[]; $list.innerHTML=''; if (!arr.length){ $list.innerHTML='<div class="listbox-item">Sin observaciones</div>'; } else { arr.forEach(o=>{ const li=document.createElement('div'); li.className='listbox-item'; li.innerHTML = `<div style=\"font-weight:600\">${o.tipo||'General'} · ${o.usuario_nombre||'Sistema'} · ${o.fecha?String(o.fecha).substring(0,10):''}</div><div>${o.observacion}</div>`; $list.appendChild(li); }); } }
			catch(_){ $list.innerHTML='<div class="listbox-item">Error cargando</div>'; }
		}
		loadList();
	}

	async function openQuickView(id, studentName){
		const m = createModal({ 
			title: `Detalle — ${studentName||id}`, 
			bodyHtml: '<div id="qv-body">Cargando...</div>'
		});
		document.body.appendChild(m.overlay);
		// Modal más compacto
		if (m && m.modal) { m.modal.style.width = 'min(560px, 94vw)'; }
		try{
			const r = await api.get('/estudiantes/'+encodeURIComponent(id)+'/quickview');
			const p = r && r.data ? r.data : {}; const st = p.estudiante||{}; const stats=p.estadisticas||{}; const ultimo=p.ultimo_curso||null; const obs=p.ultima_observacion||null; const contacto=st.contacto||{};
			const body = `
				<div style="margin-bottom:16px;">
					<button id="qv-btn-detail" class="btn btn-primary" style="width:100%;">📋 Ver Detalle Completo</button>
				</div>
				<div class=\"card\">
					<div class=\"card-title is-info\">${studentName||'-'} <span class=\"badge\">${st.codigo||id}</span></div>
					<div class=\"detail-grid\" style=\"grid-template-columns:1fr;\">
						<div class=\"field-view\"><div class=\"field-label\">Documento</div><div class=\"field-value\">${st.documento||st.docIdentidad||'-'}</div></div>
						<div class=\"field-view\"><div class=\"field-label\">Email</div><div class=\"field-value\">${st.email||'-'}</div></div>
						<div class=\"field-view\"><div class=\"field-label\">Celular</div><div class=\"field-value\">${st.celular||'-'}</div></div>
					</div>
				</div>
				<div class=\"card\">
					<div class=\"card-title is-success\">Estadísticas</div>
					<div class=\"detail-grid\" style=\"grid-template-columns:repeat(3,minmax(0,1fr));\">
						<div class=\"field-view\"><div class=\"field-label\">Cursos</div><div class=\"field-value\">${stats.total_cursos||0}</div></div>
						<div class=\"field-view\"><div class=\"field-label\">Promedio</div><div class=\"field-value\">${(stats.promedio_porcentaje||0)}%</div></div>
						<div class=\"field-view\"><div class=\"field-label\">Últ. Act.</div><div class=\"field-value\">${stats.ultima_actividad?String(stats.ultima_actividad).substring(0,10):'-'}</div></div>
					</div>
				</div>
				<div class=\"card\">
					<div class=\"card-title is-warning\">Último Curso</div>
					${ultimo?`<div class=\"detail-grid\" style=\"grid-template-columns:1fr 1fr;\"><div class=\"field-view\"><div class=\"field-label\">Curso</div><div class=\"field-value\">${ultimo.nombre}</div></div><div class=\"field-view\"><div class=\"field-label\">Nota</div><div class=\"field-value\">${ultimo.porcentaje!=null?ultimo.porcentaje+'%':'-'}</div></div><div class=\"field-view\"><div class=\"field-label\">Fecha</div><div class=\"field-value\">${ultimo.fecha?String(ultimo.fecha).substring(0,10):'-'}</div></div></div>`:'<div class=\"hint-text\">Sin registros</div>'}
				</div>
				${contacto && (contacto.codigo||contacto.nombre)?`<div class=\"card\"><div class=\"card-title is-info\">Contacto</div><div class=\"detail-grid\" style=\"grid-template-columns:1fr;\"><div class=\"field-view\"><div class=\"field-label\">Código</div><div class=\"field-value\">${contacto.codigo||'-'}</div></div><div class=\"field-view\"><div class=\"field-label\">Nombre</div><div class=\"field-value\">${contacto.nombre||'-'}</div></div></div></div>`:''}
				${obs?`<div class=\"card\"><div class=\"card-title is-muted\">Última Observación</div><div class=\"detail-grid\" style=\"grid-template-columns:1fr;\"><div class=\"field-view\"><div class=\"field-label\">${obs.tipo||'General'}</div><div class=\"field-value\">${obs.observacion}</div></div><div class=\"field-view\"><div class=\"field-label\">Fecha</div><div class=\"field-value\">${obs.fecha?String(obs.fecha).substring(0,10):'-'}</div></div></div></div>`:''}
			`;
			const el = document.querySelector('#qv-body'); if (el) el.innerHTML = body; else m.setBody(body);
			
			// Configurar evento del botón para cerrar modal antes de navegar
			const btnDetail = document.querySelector('#qv-btn-detail');
			if (btnDetail) {
				btnDetail.addEventListener('click', () => {
					if (m && m.overlay && m.overlay.parentNode) {
						m.overlay.parentNode.removeChild(m.overlay);
					}
					location.hash = '#/estudiante/' + encodeURIComponent(id);
				});
			}
		}catch(_){ const el=document.querySelector('#qv-body'); if (el) el.textContent='Error cargando'; }
	}

	$b.onclick = ()=>{ page=1; load(); };
	let t=null; $q.oninput = ()=>{ clearTimeout(t); t=setTimeout(()=>{ page=1; load(); }, 350); };
	load();
}

export function unmount(){}


