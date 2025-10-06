import { api } from '../../api/client.js';
import { createTable, showToast } from '../../components/ui/index.js';

export async function mount(container, { id } = {}){
    container.innerHTML = `
        <div class="card">
            <div class="u-flex u-gap" style="justify-content:space-between;align-items:center;">
                <div class="card-title">Asistencia Congreso <span class="badge">${id}</span></div>
                <div class="u-flex u-gap">
                    <a class="btn" href="#/congreso/${id}">Detalle</a>
                </div>
            </div>
            <div id="kpis" class="kpi-grid">
                <div class="kpi-card"><div class="kpi-label">Inscritos</div><div id="k-ins" class="kpi-value">-</div></div>
                <div class="kpi-card"><div class="kpi-label">Llegadas</div><div id="k-leg" class="kpi-value">-</div></div>
                <div class="kpi-card"><div class="kpi-label">Almuerzos</div><div id="k-alm" class="kpi-value">-</div></div>
                <div class="kpi-card"><div class="kpi-label">%</div><div id="k-pct" class="kpi-value">-</div></div>
            </div>
        </div>

        <div class="card u-mt-16">
            <div class="section-title">Registrar</div>
            <div class="u-flex u-gap">
                <input id="a-num" class="input" type="text" placeholder="# Boleta" style="width:200px;">
                <input id="a-code" class="input" type="text" placeholder="Código verificación" style="width:220px;">
                <button id="a-llegada" class="btn btn-primary">Llegada</button>
                <button id="a-almuerzo" class="btn">Almuerzo</button>
                <button id="a-anular" class="btn" style="margin-left:auto;">Anular</button>
            </div>
            <pre id="a-msg" class="u-mt-16"></pre>
        </div>

        <div class="card u-mt-16">
            <div class="tabs">
                <button class="tab active" data-tab="inscritos">Inscritos</button>
                <button class="tab" data-tab="no-llegada">Sin llegada</button>
                <button class="tab" data-tab="no-almuerzo">Sin almuerzo</button>
                <button class="tab" data-tab="ultimos">Últimos</button>
            </div>
            <div id="tabc">Cargando…</div>
        </div>
    `;

    async function refreshStats(){
        try{
            const r = await api.get(`/congresos/${encodeURIComponent(id)}/stats`);
            const d = r && r.data || {};
            const ins = d.totalInscritos||0, leg=d.totalLlegadas||0, alm=d.totalAlmuerzos||0;
            container.querySelector('#k-ins').textContent = String(ins);
            container.querySelector('#k-leg').textContent = String(leg);
            container.querySelector('#k-alm').textContent = String(alm);
            container.querySelector('#k-pct').textContent = ins? Math.round((leg/ins)*100)+'%':'0%';
        }catch(e){ /* noop */ }
    }

    async function action(tipo){
        const msg = container.querySelector('#a-msg');
        const numero = container.querySelector('#a-num').value.trim();
        const code = container.querySelector('#a-code').value.trim();
        if (!numero || !code) { showToast('Boleta y código requeridos', true); return; }
        msg.textContent = 'Procesando...';
        try{
            const r = await api.post(`/congresos/${encodeURIComponent(id)}/${tipo==='void'?'void':'checkin'}`, tipo==='void' ? { numeroBoleta:numero, codigoVerificacion:code } : { numeroBoleta:numero, codigoVerificacion:code, tipo });
            msg.textContent = 'OK'; showToast('Registrado');
            refreshStats();
            loadTab(activeTab);
        } catch(e){ msg.textContent = e.message || 'Error'; showToast(msg.textContent, true); }
    }

    const active = { value: 'inscritos' }; let activeTab = 'inscritos';
    container.querySelector('#a-llegada').addEventListener('click', ()=> action('llegada'));
    container.querySelector('#a-almuerzo').addEventListener('click', ()=> action('almuerzo'));
    container.querySelector('#a-anular').addEventListener('click', ()=> action('void'));
    container.querySelectorAll('.tab').forEach(b=> b.addEventListener('click', ()=>{ container.querySelectorAll('.tab').forEach(x=>x.classList.remove('active')); b.classList.add('active'); activeTab = b.dataset.tab; loadTab(activeTab);}));

    async function loadTab(name){
        const host = container.querySelector('#tabc');
        host.textContent = 'Cargando...';
        try{
            if (name==='inscritos'){
                const r = await api.get(`/congresos/${encodeURIComponent(id)}/inscritos?limit=50&offset=0`);
                const items = (r && r.data && r.data.items) || [];
                const rows = items.map(x=> [ x.numero, x.nombre, x.tipo, x.estado, x.fechaLlegada?('Llegada '+x.fechaLlegada): (x.fechaAlmuerzo?('Almuerzo '+x.fechaAlmuerzo):'-') ]);
                host.innerHTML=''; host.appendChild(createTable({ columns:['Boleta','Nombre','Tipo','Estado','Último'], rows }));
            } else if (name==='no-llegada'){
                const r = await api.get(`/congresos/${encodeURIComponent(id)}/no-asistentes?tipo=llegada&limit=50&offset=0`);
                const items = (r && r.data && r.data.items) || [];
                const rows = items.map(x=> [ x.numero, x.nombre, x.tipo ]);
                host.innerHTML=''; host.appendChild(createTable({ columns:['Boleta','Nombre','Tipo'], rows }));
            } else if (name==='no-almuerzo'){
                const r = await api.get(`/congresos/${encodeURIComponent(id)}/no-asistentes?tipo=almuerzo&limit=50&offset=0`);
                const items = (r && r.data && r.data.items) || [];
                const rows = items.map(x=> [ x.numero, x.nombre, x.tipo ]);
                host.innerHTML=''; host.appendChild(createTable({ columns:['Boleta','Nombre','Tipo'], rows }));
            } else if (name==='ultimos'){
                const r = await api.get(`/congresos/${encodeURIComponent(id)}/ultimos?limit=20`);
                const items = (r && r.data && r.data.items) || [];
                const rows = items.map(x=> [ x.numero, x.nombre, x.tipo, x.momento ]);
                host.innerHTML=''; host.appendChild(createTable({ columns:['Boleta','Nombre','Tipo','Momento'], rows }));
            }
        } catch(e){ host.textContent = e.message || 'Error'; }
    }

    refreshStats();
    loadTab(activeTab);
}

export function unmount(){}


