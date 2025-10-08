import { api } from '../../api/client.js';
import { showToast } from '../../components/ui/index.js';

export async function mount(container, { id } = {}){
    const load = async ()=>{ const r = await api.get('/cursos/'+encodeURIComponent(id)); return (r && r.data) || {}; };
    const APPROVAL_THRESHOLD = 80;
    const loadStats = async ()=>{ const r = await api.get('/cursos/'+encodeURIComponent(id)+'/stats?thresh='+APPROVAL_THRESHOLD); return (r && r.data) || {}; };
    const [data, stats] = await Promise.all([ load(), loadStats().catch(()=>({})) ]);
    container.innerHTML = `
        <div class="card">
            <div class="u-flex u-gap" style="justify-content:space-between;align-items:center;">
                <div class="card-title">Curso #${id}</div>
                <div class="u-flex u-gap">
                    <a class="btn" href="#/cursos">Volver</a>
                    <button id="del" class="btn btn-secondary">Eliminar</button>
                    <button id="save" class="btn btn-primary">Guardar</button>
                </div>
            </div>
            <div id="kpi" class="u-mt-8"></div>
            <div class="form-grid u-mt-16">
                <label>Nombre<input id="c-nombre" class="input" type="text" value="${escapeHtml(data.nombre||'')}"></label>
                <label>Descripción<input id="c-desc" class="input" type="text" value="${escapeHtml(data.descripcion||'')}"></label>
            </div>
            <pre id="msg" class="u-mt-16"></pre>
        </div>
    `;
    try{ renderStats(container.querySelector('#kpi'), stats); }catch(_){}
	container.querySelector('#save').addEventListener('click', async ()=>{
		const payload = { nombre: $('#c-nombre')?.value||'', descripcion: $('#c-desc')?.value||'' };
		try{ await api.put('/cursos/'+encodeURIComponent(id), payload); showToast('Actualizado'); }
		catch(e){ showToast(e.details?.message||e.message||'Error', true); }
	});
	container.querySelector('#del').addEventListener('click', async ()=>{
		if (!confirm('¿Eliminar curso?')) return;
		try{ await api.delete('/cursos/'+encodeURIComponent(id)); showToast('Eliminado'); location.hash = '#/cursos'; }
		catch(e){ showToast(e.details?.message||e.message||'Error', true); }
	});
	function $(sel){ return container.querySelector(sel); }
}

export function unmount(){}

function escapeHtml(s){ return String(s).replace(/[&<>"]/g, c=> ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }

function renderStats(node, s={}){
    const avg = Number(s.avgNota||0);
    const apr = Number(s.aprobacionPct||0);
    const total = Number(s.totalRealizados||0);
    const last = s.ultimoRegistro ? String(s.ultimoRegistro).substring(0,10) : '-';
    const size=140, r1=44, r2=52, c1=2*Math.PI*r1, c2=2*Math.PI*r2;
    const off1 = c1 * (1-avg/100);
    const normApr = Math.max(0, Math.min(1, (apr - 80) / 20)); // escala 80-100 → 0..1
    const off2 = c2 * (1-normApr);
    node.innerHTML = `
        <div style="display:flex;gap:24px;align-items:center;flex-wrap:wrap;">
            <svg width="${size}" height="${size}" viewBox="0 0 120 120">
                <circle cx="60" cy="60" r="${r2}" stroke="var(--plg-border)" stroke-width="10" fill="none"/>
                <circle cx="60" cy="60" r="${r2}" stroke="var(--plg-success,#22c55e)" stroke-width="10" fill="none" title="Escala 80-100%"
                        stroke-dasharray="${c2.toFixed(1)}" stroke-dashoffset="${off2.toFixed(1)}" stroke-linecap="round"/>
                <circle cx="60" cy="60" r="${r1}" stroke="var(--plg-border)" stroke-width="10" fill="none"/>
                <circle cx="60" cy="60" r="${r1}" stroke="var(--plg-info,#3b82f6)" stroke-width="10" fill="none"
                        stroke-dasharray="${c1.toFixed(1)}" stroke-dashoffset="${off1.toFixed(1)}" stroke-linecap="round"/>
            </svg>
            <div>
                <div style="margin-bottom:8px;color:var(--plg-mutedText)">La Mujer Que Agrada A Dios</div>
                <div style="margin:6px 0"><span style="color:var(--plg-info,#3b82f6);font-weight:700">% Notas:</span> ${avg.toFixed(2)}%</div>
                <div style="margin:6px 0"><span style="color:var(--plg-success,#22c55e);font-weight:700">% Aprobación:</span> ${apr.toFixed(3)}% <span style="color:var(--plg-mutedText);font-size:12px">(escala 80-100)</span></div>
                <div style="margin:6px 0">📅 <strong>Último Registro:</strong> ${last}</div>
                <div style="margin:6px 0">👥 <strong>Total realizados:</strong> ${total}</div>
            </div>
        </div>
    `;
}


