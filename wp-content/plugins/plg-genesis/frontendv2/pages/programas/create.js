import { api } from '../../api/client.js';
import { showToast } from '../../components/ui/index.js';

export async function mount(container){
    container.innerHTML = `
        <div class="two-col">
            <div class="main-col">
                <div class="card">
                    <div class="u-flex u-gap" style="justify-content:space-between;align-items:center;">
                        <div class="card-title">Nuevo Programa</div>
                        <a class="btn btn-secondary" href="#/programas">Volver</a>
                    </div>
                    <div class="form-grid">
                        <label>Nombre<input id="p-nombre" class="input" type="text" placeholder="Nombre"></label>
                        <label>Descripción<input id="p-desc" class="input" type="text" placeholder="Descripción"></label>
                    </div>
                    <div class="section">
                        <div class="section-title">Niveles</div>
                        <div id="niveles"></div>
                        <div class="u-mt-8"><button id="add-nivel" class="btn">Agregar nivel</button></div>
                    </div>
                    <div class="section">
                        <div class="section-title">Cursos sin nivel</div>
                        <div id="sinNivel" class="drop-zone" aria-label="Zona para cursos sin nivel"></div>
                        <div class="u-mt-8"><button id="add-sin" class="btn">Agregar curso</button></div>
                    </div>
                    <div class="section">
                        <div class="section-title">Prerequisitos</div>
                        <div id="pre"></div>
                    </div>
                    <div class="u-mt-16">
                        <button id="create" class="btn btn-primary">Crear</button>
                    </div>
                    <pre id="msg" class="u-mt-16"></pre>
                </div>
            </div>
            <aside class="card aside">
                <div class="section-title">Cursos disponibles</div>
                <div class="u-flex u-gap">
                    <input id="q-cursos" class="input" type="text" placeholder="Buscar cursos (v1 estilo)">
                </div>
                <div id="cursosDisp" class="course-grid u-mt-8"></div>
            </aside>
        </div>
    `;
    let cursosCache = [];
    async function loadCursos(){
        if (cursosCache.length) return cursosCache;
        const r = await api.get('/cursos'); cursosCache = (r && r.data && r.data.items) || []; return cursosCache;
    }

	let programasCache = [];
	async function loadProgramas(){
		if (programasCache.length) return programasCache;
		const r = await api.get('/programas?q='); programasCache = (r && r.data && r.data.items) || []; return programasCache;
	}

    const niveles = [];
    const sinNivel = [];
    const prereq = [];

    const $niv = container.querySelector('#niveles');
    const $sin = container.querySelector('#sinNivel');
    const $disp = container.querySelector('#cursosDisp');
    const $q = container.querySelector('#q-cursos');

    function setupDropZone(el, onDropCb){
        const onOver = (e)=>{ e.preventDefault(); try{ e.dataTransfer.dropEffect = 'copy'; }catch(_){} el.classList.add('drag-over'); };
        const onEnter = (e)=>{ e.preventDefault(); el.classList.add('drag-over'); };
        const onLeave = ()=> el.classList.remove('drag-over');
        const handleDrop = async (e)=>{
            e.preventDefault(); el.classList.remove('drag-over');
            const dt = e.dataTransfer; if (!dt) return;
            const id = dt.getData('text/plain') || dt.getData('text');
            const course = (await loadCursos()).find(x=> String(x.id)===String(id));
            if (!course) return;
            try { onDropCb(course); } catch(_){}
        };
        // Listeners en burbuja y captura para maximizar compatibilidad
        el.addEventListener('dragover', onOver);
        el.addEventListener('dragover', onOver, true);
        el.addEventListener('dragenter', onEnter);
        el.addEventListener('dragenter', onEnter, true);
        el.addEventListener('dragleave', onLeave);
        el.addEventListener('dragleave', onLeave, true);
        el.addEventListener('drop', handleDrop);
        el.addEventListener('drop', handleDrop, true);
    }

    function nextGlobalConsecutivo(){
        const all = [];
        niveles.forEach(n=> (n.cursos||[]).forEach(c=> all.push(c.consecutivo||0)) );
        sinNivel.forEach(c=> all.push(c.consecutivo||0));
        const max = all.length ? Math.max(...all) : 0; return max + 1;
    }

    function isConsecutivoUsed(cons){
        cons = parseInt(cons,10);
        if (Number.isNaN(cons) || cons<=0) return false;
        if (sinNivel.some(c=> c.consecutivo===cons)) return true;
        for (const n of niveles){ if ((n.cursos||[]).some(c=> c.consecutivo===cons)) return true; }
        return false;
    }

    function recalcConsecutivos(){
        let counter = 1;
        for (const n of niveles){
            if (!n.cursos) continue;
            for (const c of n.cursos){ c.consecutivo = counter++; }
        }
        for (const c of sinNivel){ c.consecutivo = counter++; }
    }

    function renderNiveles(){
        $niv.innerHTML='';
        if (niveles.length===0){ $niv.textContent='Sin niveles'; return; }
        niveles.forEach((n, idx)=>{
            const wrap = document.createElement('div'); wrap.className='card u-mb-8';
            wrap.innerHTML = `<div class="u-flex u-gap" style="justify-content:space-between;align-items:center;">
                <div class="section-title">${n.nombre||('Nivel '+(idx+1))}</div>
                <div class="u-flex u-gap"><button data-act="add" class="btn">Agregar curso</button><button data-act="rm" class="btn btn-secondary">Quitar</button></div>
            </div><div class="drop-zone" id="c-${idx}"></div>`;
            const cont = wrap.querySelector('#c-'+idx);
			const items = (n.cursos||[]).map((c,i)=>{
                const el = document.createElement('div'); el.className='course-card'; el.draggable = true; el.dataset.id = c.id;
                el.innerHTML = `<span class="course-name">${c.nombre||c.id}</span><span class="course-badge">#${c.consecutivo}</span>`;
                el.addEventListener('dragstart', (e)=>{ e.dataTransfer?.setData('text/plain', String(c.id)); });
				el.addEventListener('dblclick', ()=>{ n.cursos.splice(i,1); renderNiveles(); renderCursosDisponibles($q.value||''); });
                return el;
            });
            items.forEach(el=> cont.appendChild(el));
            setupDropZone(cont, async (course)=>{
                // mover desde donde esté
                for (const lvl of niveles){ const i = (lvl.cursos||[]).findIndex(c=> String(c.id)===String(course.id)); if (i>=0){ lvl.cursos.splice(i,1); break; } }
                const i2 = sinNivel.findIndex(c=> String(c.id)===String(course.id)); if (i2>=0){ sinNivel.splice(i2,1); }
                const existsHere = (n.cursos||[]).some(c=> String(c.id)===String(course.id));
                if (existsHere){ showToast('Ese curso ya está en este nivel', true); return; }
                n.cursos = n.cursos||[]; n.cursos.push({ id: course.id, nombre: course.nombre, consecutivo: 0 });
                recalcConsecutivos();
                renderNiveles();
                renderCursosDisponibles($q.value||'');
            });
            wrap.querySelector('[data-act="add"]').addEventListener('click', ()=> addCursoNivel(idx));
            wrap.querySelector('[data-act="rm"]').addEventListener('click', ()=>{ niveles.splice(idx,1); renderNiveles(); });
            $niv.appendChild(wrap);
        });
    }
    function renderSinNivel(){
        $sin.innerHTML='';
		sinNivel.forEach((c,i)=>{
            const el = document.createElement('div'); el.className='course-card'; el.draggable = true; el.dataset.id = c.id;
            el.innerHTML = `<span class="course-name">${c.nombre||c.id}</span><span class="course-badge">#${c.consecutivo}</span>`;
            el.addEventListener('dragstart', (e)=>{ e.dataTransfer?.setData('text/plain', String(c.id)); });
            el.addEventListener('dblclick', ()=>{ sinNivel.splice(i,1); recalcConsecutivos(); renderSinNivel(); renderCursosDisponibles($q.value||''); });
            $sin.appendChild(el);
        });
        setupDropZone($sin, async (course)=>{
            for (const lvl of niveles){ const i = (lvl.cursos||[]).findIndex(c=> String(c.id)===String(course.id)); if (i>=0){ lvl.cursos.splice(i,1); break; } }
            const existsHere = sinNivel.some(c=> String(c.id)===String(course.id));
            if (existsHere){ showToast('Ese curso ya está sin nivel', true); return; }
            sinNivel.push({ id: course.id, nombre: course.nombre, consecutivo: 0 });
            recalcConsecutivos();
            renderSinNivel();
            renderCursosDisponibles($q.value||'');
        });
    }

	function isCourseAlreadyAdded(courseId){
        for (const lvl of niveles){ if ((lvl.cursos||[]).some(c=> String(c.id)===String(courseId))) return true; }
        if (sinNivel.some(c=> String(c.id)===String(courseId))) return true;
        return false;
    }

	// Prerequisitos UI
	const $pre = container.querySelector('#pre');
	$pre.innerHTML = `
		<div class="u-flex u-gap"><input id="pre-q" class="input" type="text" placeholder="Buscar programas"></div>
		<div class="u-mt-8" id="pre-chips" style="display:flex;gap:8px;flex-wrap:wrap;"></div>
		<div class="u-mt-8 listbox" id="pre-list" role="listbox" aria-label="Programas disponibles"></div>
	`;
	const $preQ = $pre.querySelector('#pre-q');
	const $preList = $pre.querySelector('#pre-list');
	const $preChips = $pre.querySelector('#pre-chips');

	function renderPrereqSelected(){
		$preChips.innerHTML = '';
		prereq.forEach(p=>{
			const chip = document.createElement('span'); chip.className='tag'; chip.textContent = p.nombre || ('Programa '+p.id);
			chip.style.cursor='pointer'; chip.title='Quitar';
			chip.addEventListener('click', ()=>{ const i = prereq.findIndex(x=> String(x.id)===String(p.id)); if (i>=0){ prereq.splice(i,1); renderPrereqSelected(); renderPrereqList($preQ.value||''); } });
			$preChips.appendChild(chip);
		});
	}

	async function renderPrereqList(filter=''){
		const list = await loadProgramas();
		$preList.innerHTML='';
		list.filter(x=> (x.nombre||'').toLowerCase().includes(filter.toLowerCase()))
			.forEach(item=>{
				const el = document.createElement('div'); el.className='listbox-item'; el.setAttribute('role','option'); el.textContent = item.nombre;
				const already = prereq.some(p=> String(p.id)===String(item.id));
				if (already){ el.classList.add('selected'); }
				el.addEventListener('click', ()=>{
					if (already) return;
					prereq.push({ id:item.id, nombre:item.nombre });
					renderPrereqSelected(); renderPrereqList($preQ.value||'');
				});
				$preList.appendChild(el);
			});
		if (!$preList.children.length){ const em=document.createElement('div'); em.className='listbox-item'; em.textContent='Sin resultados'; $preList.appendChild(em); }
	}

    async function renderCursosDisponibles(filter=''){
        const list = await loadCursos();
        $disp.innerHTML='';
        list
            .filter(x => !isCourseAlreadyAdded(x.id))
            .filter(x => (x.nombre||'').toLowerCase().includes(filter.toLowerCase()))
            .forEach(item=>{
                const el = document.createElement('div'); el.className='course-card'; el.draggable = true; el.dataset.id = item.id;
                el.innerHTML = `<span class="course-name">${item.nombre}</span>`;
                el.addEventListener('dragstart', (e)=>{ e.dataTransfer?.setData('text/plain', String(item.id)); });
                $disp.appendChild(el);
            });
        if (!$disp.children.length){ $disp.innerHTML = '<div class="hint-text">Sin resultados</div>'; }
    }

    async function pickCurso(){
        const list = await loadCursos();
        return new Promise(resolve=>{
            const prev = document.querySelector('.modal-overlay'); if (prev) prev.remove();
            const overlay = document.createElement('div'); overlay.className='modal-overlay';
            const modal = document.createElement('div'); modal.className='modal';
            modal.innerHTML = `
                <div class="modal-header"><strong>Seleccionar curso</strong><button id="x-close" class="btn">Cerrar</button></div>
                <div class="modal-body">
                    <div class="u-flex u-gap"><input id="cp-q" class="input" type="text" placeholder="Buscar curso" style="flex:1;"> <input id="cp-cons" class="input" type="number" min="1" value="1" style="width:120px" aria-label="Consecutivo"></div>
                    <div id="cp-list" class="listbox u-mt-8" role="listbox" aria-label="Cursos disponibles"></div>
                </div>
                <div class="modal-footer"><button id="cp-ok" class="btn btn-primary">Seleccionar</button></div>
            `;
            overlay.appendChild(modal); document.body.appendChild(overlay);
            const $q = modal.querySelector('#cp-q'); const $list = modal.querySelector('#cp-list'); const $cons = modal.querySelector('#cp-cons');
            let selected = null;
            function render(filter=''){
                $list.innerHTML='';
                const filtered = list.filter(x => (x.nombre||'').toLowerCase().includes(filter.toLowerCase()));
                filtered.forEach(item=>{
                    const el = document.createElement('div'); el.className='listbox-item'; el.textContent = item.nombre; el.setAttribute('role','option'); el.tabIndex=0;
                    el.addEventListener('click', ()=>{ Array.from($list.children).forEach(c=>c.classList.remove('selected')); el.classList.add('selected'); selected=item; });
                    el.addEventListener('dblclick', ()=>{ selected=item; confirmSel(); });
                    $list.appendChild(el);
                });
                if (!$list.children.length){ const em=document.createElement('div'); em.className='listbox-item'; em.textContent='Sin resultados'; $list.appendChild(em); }
            }
            function confirmSel(){ const cons = parseInt($cons.value||'1',10); if(!selected || !(cons>=1)){ showToast('Selecciona un curso y consecutivo válido', true); return; } close(); resolve({ id:selected.id, nombre:selected.nombre, consecutivo:cons }); }
            function close(){ overlay.remove(); }
            modal.querySelector('#cp-ok').addEventListener('click', confirmSel);
            modal.querySelector('#x-close').addEventListener('click', ()=>{ close(); resolve(null); });
            $q.addEventListener('input', ()=> render($q.value||''));
            render(''); $q.focus();
        });
    }
    async function addCursoNivel(idx){ const item = await pickCurso(); if (!item) return; if (isCourseAlreadyAdded(item.id)) { showToast('Ese curso ya está agregado', true); return; } const cons = nextGlobalConsecutivo(); if (isConsecutivoUsed(cons)) { showToast('Ese consecutivo ya está en uso', true); return; } niveles[idx].cursos = niveles[idx].cursos||[]; niveles[idx].cursos.push({ id:item.id, nombre:item.nombre, consecutivo:cons }); renderNiveles(); renderCursosDisponibles($q.value||''); }
    async function addCursoSin(){ const item = await pickCurso(); if (!item) return; if (isCourseAlreadyAdded(item.id)) { showToast('Ese curso ya está agregado', true); return; } const cons = nextGlobalConsecutivo(); if (isConsecutivoUsed(cons)) { showToast('Ese consecutivo ya está en uso', true); return; } sinNivel.push({ id:item.id, nombre:item.nombre, consecutivo:cons }); renderSinNivel(); renderCursosDisponibles($q.value||''); }

    container.querySelector('#add-nivel').addEventListener('click', ()=>{
        const prev = document.querySelector('.modal-overlay'); if (prev) prev.remove();
        const overlay = document.createElement('div'); overlay.className='modal-overlay';
        const modal = document.createElement('div'); modal.className='modal';
        modal.innerHTML = `
            <div class="modal-header"><strong>Nuevo nivel</strong><button id="x-close" class="btn">Cerrar</button></div>
            <div class="modal-body">
                <label>Nombre del nivel<input id="nivel-nombre" class="input" type="text" placeholder="Ej: Nivel 1"></label>
            </div>
            <div class="modal-footer"><button id="ok" class="btn btn-primary">Agregar</button></div>
        `;
        overlay.appendChild(modal); document.body.appendChild(overlay);
        function close(){ overlay.remove(); }
        modal.querySelector('#x-close').addEventListener('click', close);
        modal.querySelector('#ok').addEventListener('click', ()=>{
            const nombre = (modal.querySelector('#nivel-nombre')?.value||'').trim();
            if (!nombre){ showToast('Nombre del nivel requerido', true); return; }
            niveles.push({ nombre, cursos:[] }); renderNiveles(); close();
        });
        modal.querySelector('#nivel-nombre').focus();
    });
    container.querySelector('#add-sin').addEventListener('click', addCursoSin);
    $q.addEventListener('input', ()=>{ renderCursosDisponibles($q.value||''); });
    $preQ.addEventListener('input', ()=>{ renderPrereqList($preQ.value||''); });

    // Primera renderización
    renderCursosDisponibles('');
    renderPrereqSelected();
    renderPrereqList('');

    container.querySelector('#create').addEventListener('click', async ()=>{
        const payload = { nombre: $('#p-nombre')?.value||'', descripcion: $('#p-desc')?.value||'' };
        const msg = container.querySelector('#msg'); msg.textContent='Creando…';
        // Validaciones mínimas
        if (!payload.nombre.trim()){ msg.textContent='Nombre requerido'; showToast('Nombre requerido', true); return; }
        payload.niveles = niveles.map(n=> ({ nombre: n.nombre||'', cursos: (n.cursos||[]).map(c=> ({ id: c.id, consecutivo: c.consecutivo })) }));
        payload.cursosSinNivel = sinNivel.map(c=> ({ id: c.id, consecutivo: c.consecutivo }));
        payload.prerequisitos = prereq.map(p=> ({ id: p.id }));
        try{
            const r = await api.post('/programas', payload);
            const id = r && r.data && r.data.id; msg.textContent='Creado: '+id; showToast('Programa creado');
            if (id) location.hash = '#/programa/'+encodeURIComponent(id);
        } catch(e){ msg.textContent = e.details?.message||e.message||'Error'; showToast('Error creando', true); }
    });
    function $(sel){ return container.querySelector(sel); }
}

export function unmount(){}


