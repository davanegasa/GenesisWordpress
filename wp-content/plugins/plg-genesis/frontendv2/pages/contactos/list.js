import { buscar } from '../../services/contactos.js';
import { createTable } from '../../components/ui/index.js';

export async function mount(container){
    container.innerHTML = `
        <div class="card">
            <div class="card-title">Buscar contactos</div>
            <div class="u-flex u-gap">
                <input id="c-q" class="input" type="text" placeholder="Nombre / Iglesia / Email" style="flex:1;">
                <button id="c-btn" class="btn btn-primary">Buscar</button>
            </div>
        </div>
        <div class="card u-mt-16">
            <div class="card-title">Resultados</div>
            <div id="c-table">Escribe y pulsa “Buscar”.</div>
            <div id="c-pag" class="u-flex u-gap u-mt-8" style="justify-content:space-between;align-items:center"></div>
        </div>
    `;

    const $q = container.querySelector('#c-q');
    const $btn = container.querySelector('#c-btn');
    const $table = container.querySelector('#c-table');
    const $pag = container.querySelector('#c-pag');

    let contactsData = [];
    let currentPage = 1;
    const pageSize = 10;

    function openQuickView(ct){
        const prev = document.querySelector('.modal-overlay'); if (prev) prev.remove();
        const overlay = document.createElement('div'); overlay.className = 'modal-overlay';
        const modal = document.createElement('div'); modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-header">
                <strong>Detalle — ${ct.nombre || ct.code}</strong>
                <button id="q-close" class="btn">×</button>
            </div>
            <div class="modal-body">
                <div style="margin-bottom:16px;">
                    <button id="q-detail" class="btn btn-primary" style="width:100%;">📋 Ver Detalle Completo</button>
                </div>
                <div class="contact-card">
                    <div class="contact-avatar">${(ct.nombre||'C').slice(0,1)}</div>
                    <div class="contact-body">
                        <div class="contact-title">${ct.nombre || '-'}</div>
                        <div class="contact-sub">Código ${ct.code||''} · ${ct.iglesia||''}</div>
                        <div class="contact-sub">${ct.email||''}${ct.celular?(' · '+ct.celular):''}</div>
                    </div>
                </div>
                <div class="divider" style="margin:16px 0"></div>
                <div class="detail-grid" style="grid-template-columns:1fr;">
                    <div class="field-view"><div class="field-label">Dirección</div><div class="field-value">${ct.direccion||'-'}</div></div>
                    <div class="field-view"><div class="field-label">Ciudad</div><div class="field-value">${ct.ciudad||'-'}</div></div>
                </div>
            </div>
        `;
        overlay.appendChild(modal); document.body.appendChild(overlay);
        const close = ()=> overlay.remove();
        modal.querySelector('#q-close').addEventListener('click', close);
        
        // Botón Ver Detalle Completo
        modal.querySelector('#q-detail').addEventListener('click', ()=> {
            close();
            location.hash = '#/contacto/'+encodeURIComponent((ct.code||'').trim());
        });
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
        if (!contactsData || contactsData.length === 0){
            $table.textContent = 'Sin resultados';
            renderPagination(0,0,0);
            return;
        }
        const total = contactsData.length;
        const startIdx = (currentPage - 1) * pageSize;
        const endIdx = Math.min(startIdx + pageSize, total);
        const visible = contactsData.slice(startIdx, endIdx);
        const rows = visible.map(it => [it.code || '', it.nombre || '', it.iglesia || '', it.email || '']);
        const columnLabels = ['Código','Nombre','Iglesia','Email'];
        const tbl = createTable({ columns: columnLabels, rows });
        
        // Agregar clase específica para contactos
        tbl.classList.add('contactos-table');
        
        // Agregar data-labels a cada td para mobile (patrón de estudiantes)
        const tbody = tbl.querySelector('tbody');
        if (tbody) {
            Array.from(tbody.querySelectorAll('tr')).forEach(tr => {
                Array.from(tr.querySelectorAll('td')).forEach((td, idx) => {
                    td.setAttribute('data-label', columnLabels[idx]);
                });
            });
        }
        
        $table.innerHTML = '';
        $table.appendChild(tbl);
        Array.from(tbl.querySelectorAll('tbody tr')).forEach((tr, idx) => {
            tr.style.cursor = 'pointer';
            tr.addEventListener('click', ()=> openQuickView(visible[idx]));
        });
        renderPagination(total, startIdx, endIdx);
    }

    async function doSearch(){
        $table.textContent = 'Cargando...';
        try{
            contactsData = await buscar($q.value||'', 100, 0);
            currentPage = 1;
            renderRows();
        } catch(e){
            $table.textContent = 'Error buscando contactos';
        }
    }

    $btn.addEventListener('click', doSearch);
    $q.addEventListener('keyup', (e)=>{ if (e.key==='Enter') doSearch(); });
}

export function unmount(){}


