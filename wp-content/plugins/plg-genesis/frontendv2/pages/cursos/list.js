import { api } from '../../api/client.js';

export async function mount(container){
	container.innerHTML = `
		<div class="card">
			<div class="u-flex u-gap" style="justify-content:space-between;align-items:center;">
				<div class="card-title">Cursos</div>
				<a href="#/cursos/nuevo" class="btn btn-primary">Nuevo</a>
			</div>
			<div class="u-flex u-gap"><input id="q" class="input" type="text" placeholder="Buscar cursos"></div>
			<div id="list" class="program-grid u-mt-8"></div>
		</div>
	`;
	const $q = container.querySelector('#q');
	const $list = container.querySelector('#list');

	async function load(){
		const r = await api.get('/cursos?q='+(encodeURIComponent($q.value||'')));
		const items = (r && r.data && r.data.items) || [];
		$list.innerHTML='';
		items.forEach(c=>{
			const el = document.createElement('div'); el.className='program-card';
			el.innerHTML = `
				<div class="program-title">${c.nombre}</div>
				<div class="program-desc">${c.descripcion||''}</div>
				<div class="u-mt-8" id="kpi-${c.id}"></div>
				<div class="program-actions"><a class="btn" href="#/curso/${encodeURIComponent(c.id)}">Ver</a></div>
			`;
			$list.appendChild(el);
			// carga perezosa del KPI cuando entra a viewport
			const obs = new IntersectionObserver(async (entries,observer)=>{
				for (const e of entries){ if (!e.isIntersecting) continue; observer.unobserve(e.target); try{ const s = await loadStats(c.id); renderKpiMini(el.querySelector('#kpi-'+c.id), s); }catch(_){ /* noop */ } }
			},{ rootMargin: '100px' });
			obs.observe(el);
		});
		if (!$list.children.length){ $list.innerHTML='<div class="hint-text">Sin resultados</div>'; }
	}

	$q.addEventListener('input', ()=> load());
	await load();

	const APPROVAL_THRESHOLD = 80;
	async function loadStats(id){ const r = await api.get('/cursos/'+encodeURIComponent(id)+'/stats?thresh='+APPROVAL_THRESHOLD); return (r && r.data) || {}; }
	function renderKpiMini(node, s={}){
		const avg = Number(s.avgNota||0), apr = Number(s.aprobacionPct||0), total = Number(s.totalRealizados||0);
		node.innerHTML = `<div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
			<svg width="64" height="64" viewBox="0 0 64 64">
				<circle cx="32" cy="32" r="18" stroke="var(--plg-border)" stroke-width="6" fill="none"/>
				<circle cx="32" cy="32" r="18" stroke="var(--plg-success,#22c55e)" stroke-width="6" fill="none"
					stroke-dasharray="${(2*Math.PI*18).toFixed(1)}" stroke-dashoffset="${((2*Math.PI*18)*(1-Math.max(0,Math.min(1,(apr-80)/20)))).toFixed(1)}" stroke-linecap="round"/>
				<circle cx="32" cy="32" r="12" stroke="var(--plg-border)" stroke-width="6" fill="none"/>
				<circle cx="32" cy="32" r="12" stroke="var(--plg-info,#3b82f6)" stroke-width="6" fill="none"
					stroke-dasharray="${(2*Math.PI*12).toFixed(1)}" stroke-dashoffset="${((2*Math.PI*12)*(1-avr(avg))).toFixed(1)}" stroke-linecap="round"/>
			</svg>
			<div style="font-size:12px">
				<div><strong style="color:var(--plg-info,#3b82f6)">%Notas:</strong> ${avg.toFixed(1)}%</div>
				<div><strong style="color:var(--plg-success,#22c55e)">%Aprobación:</strong> ${apr.toFixed(1)}%</div>
				<div><strong>Total:</strong> ${total}</div>
			</div>
		</div>`;
		function avr(x){ return Math.max(0, Math.min(1, x/100)); }
	}
}

export function unmount(){}


