import { api } from '../../api/client.js';
import { createTable } from '../../components/ui/index.js';

export async function mount(container){
    container.innerHTML = `
        <div class="card">
            <div class="card-title">Estadísticas de Congresos</div>
            <div class="tabs">
                <button class="tab active" data-status="">Todos</button>
                <button class="tab" data-status="PLAN">En Planeación</button>
                <button class="tab" data-status="ABIERTO">Registro Abierto</button>
                <button class="tab" data-status="CURSO">En Curso</button>
                <button class="tab" data-status="FINAL">Finalizados</button>
                <button class="tab" data-status="CANCEL">Cancelados</button>
            </div>
            <div class="u-flex u-gap">
                <input id="g-q" class="input" type="text" placeholder="Filtrar por nombre" style="flex:1;">
                <button id="g-btn" class="btn">Buscar</button>
            </div>
        </div>
        <div class="u-mt-16" id="g-grid"></div>
        <div id="g-pag" class="u-flex u-gap u-mt-8" style="justify-content:space-between;align-items:center"></div>
    `;

    const $q = container.querySelector('#g-q');
    const $btn = container.querySelector('#g-btn');
    const $grid = container.querySelector('#g-grid');
    const $pag = container.querySelector('#g-pag');

    let data = [];
    let currentPage = 1; const pageSize = 10;

    function openQuickView(cg){
        const prev = document.querySelector('.modal-overlay'); if (prev) prev.remove();
        const overlay = document.createElement('div'); overlay.className='modal-overlay';
        const modal = document.createElement('div'); modal.className='modal';
        modal.innerHTML = `
            <div class="modal-header"><strong>Detalle de congreso</strong><button id="x-close" class="btn">Cerrar</button></div>
            <div class="modal-body">
                <div class="contact-card">
                    <div class="contact-avatar">${(cg.nombre||'C').slice(0,1)}</div>
                    <div class="contact-body">
                        <div class="contact-title">${cg.nombre||'-'}</div>
                        <div class="contact-sub">Fecha ${cg.fecha||'-'} · Lugar ${cg.lugar||'-'}</div>
                        <div class="contact-sub">Inscritos ${cg.estudiantes_inscritos ?? '-'}</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer"><button id="x-close2" class="btn">Cerrar</button></div>
        `;
        overlay.appendChild(modal); document.body.appendChild(overlay);
        const close=()=> overlay.remove(); modal.querySelector('#x-close').addEventListener('click', close); modal.querySelector('#x-close2').addEventListener('click', close);
    }

    function showToast(text, isError=false){ const t=document.createElement('div'); t.className='toast'; if(isError) t.style.borderColor='var(--plg-danger)'; t.textContent=text; document.body.appendChild(t); setTimeout(()=>t.remove(),2500); }

    async function changeState(cg){
        const order = ['PLAN','ABIERTO','CURSO','FINAL','CANCEL'];
        const cur = (cg.estado||'').toUpperCase();
        const idx = Math.max(0, order.indexOf(cur));
        const next = order[(idx+1) % order.length];
        try{
            await api.put('/congresos/'+encodeURIComponent(cg.idCongreso || cg.id || ''), { estado: next, nombre: cg.nombre, fecha: cg.fecha });
            cg.estado = next; showToast('Estado actualizado a '+next);
            renderRows();
        } catch(e){ showToast('Error actualizando estado', true); }
    }

    function renderPagination(total, startIdx, endIdx){
        if (!total) { $pag.innerHTML=''; return; }
        $pag.innerHTML = `
            <div class="hint-text">Mostrando ${startIdx+1}-${endIdx} de ${total}</div>
            <div class="u-flex u-gap"><button id="p-prev" class="btn">Anterior</button><button id="p-next" class="btn">Siguiente</button></div>`;
        const $prev = $pag.querySelector('#p-prev'); const $next = $pag.querySelector('#p-next');
        $prev.disabled = currentPage===1; $next.disabled = endIdx>=total;
        $prev.addEventListener('click', ()=>{ if (currentPage>1){ currentPage--; renderRows(); } });
        $next.addEventListener('click', ()=>{ if (endIdx<total){ currentPage++; renderRows(); } });
    }

    function renderRows(){
        const q = ($q.value||'').toLowerCase();
        const status = container.querySelector('.tab.active')?.dataset.status || '';
        const filtered = data.filter(x=> (!q || (x.nombre||'').toLowerCase().includes(q)) && (!status || (x.estado||'').toUpperCase().includes(status)));
        if (filtered.length===0){ $grid.textContent='Sin resultados'; renderPagination(0,0,0); return; }
        const total = filtered.length; const startIdx=(currentPage-1)*pageSize; const endIdx=Math.min(startIdx+pageSize,total);
        const visible = filtered.slice(startIdx,endIdx);
        $grid.innerHTML='';
        visible.forEach(cg=>{
            const card = document.createElement('div'); card.className='congreso-card';
            const statusClass = (cg.estado||'').toUpperCase().includes('ABIERTO')?'status-abierto':(cg.estado||'').toUpperCase().includes('CURSO')?'status-curso':(cg.estado||'').toUpperCase().includes('FINAL')?'status-final':(cg.estado||'').toUpperCase().includes('CANCEL')?'status-cancel':'status-plan';
            card.innerHTML = `
                <div class="congreso-head">
                    <div>
                        <div class="congreso-title">${cg.nombre||'-'}</div>
                        <div class="congreso-date">${cg.fecha||'-'}</div>
                    </div>
                    <span class="status-badge ${statusClass}">${cg.estado||''}</span>
                </div>
                <div class="congreso-metrics">
                    <div class="metric"><div class="metric-label">Total</div><div class="metric-value">${cg.totalAsistentes ?? '-'}</div></div>
                    <div class="metric"><div class="metric-label">Estudiantes</div><div class="metric-value">${cg.totalEstudiantes ?? '-'}</div></div>
                    <div class="metric"><div class="metric-label">Externos</div><div class="metric-value">${cg.totalExternos ?? '-'}</div></div>
                </div>
                <div class="congreso-actions">
                    <button class="btn" data-act="detalle">Ver detalle</button>
                    <button class="btn" data-act="inscritos">Ver inscritos</button>
                    <button class="btn" data-act="asistencia">Asistencia</button>
                    <button class="btn" data-act="estado">Cambiar estado</button>
                </div>
            `;
            card.querySelector('[data-act="detalle"]').addEventListener('click', ()=> { location.hash = '#/congreso/'+encodeURIComponent(cg.idCongreso || cg.id || ''); });
            card.querySelector('[data-act="inscritos"]').addEventListener('click', ()=> { location.hash = '#/congreso/'+encodeURIComponent(cg.idCongreso || cg.id || '') ; });
            card.querySelector('[data-act="asistencia"]').addEventListener('click', ()=> { location.hash = '#/congreso/'+encodeURIComponent(cg.idCongreso || cg.id || '')+'/asistencia' ; });
            card.querySelector('[data-act="estado"]').addEventListener('click', ()=> changeState(cg));
            $grid.appendChild(card);
        });
        renderPagination(total, startIdx, endIdx);
    }

    async function load(){
        try{
            const r = await api.get('/congresos');
            data = (r && r.data) || [];
            currentPage = 1; renderRows();
        } catch(e){ $grid.textContent = 'Error cargando congresos'; }
    }

    $btn.addEventListener('click', ()=>{ currentPage=1; renderRows(); });
    $q.addEventListener('keyup', (e)=>{ if(e.key==='Enter'){ currentPage=1; renderRows(); } });
    container.querySelectorAll('.tab').forEach(t=> t.addEventListener('click', (e)=>{ container.querySelectorAll('.tab').forEach(x=>x.classList.remove('active')); e.currentTarget.classList.add('active'); currentPage=1; renderRows(); }));

    load();
}

export function unmount(){}


