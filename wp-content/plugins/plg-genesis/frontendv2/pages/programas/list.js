import { api } from '../../api/client.js';
import { createTable, showToast } from '../../components/ui/index.js';

export async function mount(container){
    container.innerHTML = `
        <div class="card">
            <div class="u-flex u-gap" style="justify-content:space-between;align-items:center;">
                <div class="card-title">Programas</div>
                <div class="u-flex u-gap">
                    <a class="btn" href="#/programas/nuevo">Nuevo</a>
                </div>
            </div>
            <div class="u-flex u-gap">
                <input id="q" class="input" type="text" placeholder="Buscar programas" style="flex:1;">
                <button id="btn" class="btn">Buscar</button>
            </div>
        </div>
        <div class="u-mt-16" id="grid"></div>
    `;
    const $q = container.querySelector('#q');
    const $btn = container.querySelector('#btn');
    const $grid = container.querySelector('#grid');

    async function load(){
        try{
            const r = await api.get('/programas?q=' + encodeURIComponent($q.value||''));
            const items = (r && r.data && r.data.items) || [];
            const rows = items.map(p => [ p.id, p.nombre, p.descripcion ]);
            $grid.innerHTML = '';
            const tbl = createTable({ columns:['ID','Nombre','Descripción'], rows });
            $grid.appendChild(tbl);
            Array.from(tbl.querySelectorAll('tbody tr')).forEach((tr, idx) => {
                const id = items[idx].id; tr.style.cursor='pointer';
                tr.addEventListener('click', ()=>{ location.hash = '#/programa/' + encodeURIComponent(id); });
            });
        } catch(e){ $grid.textContent = 'Error cargando programas'; showToast('Error cargando programas', true); }
    }

    $btn.addEventListener('click', load);
    $q.addEventListener('keyup', (e)=>{ if (e.key==='Enter') load(); });
    load();
}

export function unmount(){}


