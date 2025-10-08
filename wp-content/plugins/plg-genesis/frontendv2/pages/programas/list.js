import { api } from '../../api/client.js';
import { createTable, showToast } from '../../components/ui/index.js';

export async function mount(container){
    container.innerHTML = `
        <div class="card">
            <div class="u-flex u-gap" style="justify-content:space-between;align-items:center;">
                <div class="card-title">Programas <span id="p-count" class="badge">0</span></div>
                <div class="u-flex u-gap">
                    <a class="btn" href="#/programas/nuevo">Nuevo</a>
                </div>
            </div>
            <div class="u-flex u-gap">
                <input id="q" class="input" type="text" placeholder="Buscar programas (nombre o descripción)" style="flex:1;">
                <button id="btn" class="btn">Buscar</button>
            </div>
        </div>
        <div class="u-mt-16" id="grid"></div>
    `;
    const $q = container.querySelector('#q');
    const $btn = container.querySelector('#btn');
    const $grid = container.querySelector('#grid');

    function shorten(txt, max=120){ if(!txt) return ''; const t=String(txt); return t.length>max? (t.slice(0,max-1)+'…') : t; }

    async function load(){
        try{
            const r = await api.get('/programas?q=' + encodeURIComponent($q.value||''));
            const items = (r && r.data && r.data.items) || [];
            const $count = container.querySelector('#p-count'); if ($count) $count.textContent = String(items.length);
            $grid.innerHTML = '';
            const grid = document.createElement('div'); grid.className='program-grid';
            items.forEach(p=>{
                const card = document.createElement('div'); card.className='program-card';
                card.innerHTML = `
                    <div class="program-title">${p.nombre||''}</div>
                    <div class="program-desc">${shorten(p.descripcion, 160)}</div>
                    <div class="program-actions"><button class="btn btn-secondary" aria-label="Ver detalle">Ver detalle</button></div>
                `;
                card.title = p.descripcion || '';
                card.addEventListener('click', ()=>{ location.hash = '#/programa/' + encodeURIComponent(p.id); });
                grid.appendChild(card);
            });
            if (!grid.children.length){
                $grid.textContent = 'Sin resultados';
            } else {
                $grid.appendChild(grid);
            }
        } catch(e){ $grid.textContent = 'Error cargando programas'; showToast('Error cargando programas', true); }
    }

    $btn.addEventListener('click', load);
    $q.addEventListener('keyup', (e)=>{ if (e.key==='Enter') load(); });
    load();
}

export function unmount(){}


