import { api } from '../../api/client.js';
import { themePresets, applyPreset } from '../../core/theme.js';

export async function mount(container){
	container.innerHTML = `<div class="card"><div class="card-title">Tema por oficina</div><div id="t">Cargando...</div></div>`;
	const $t = container.querySelector('#t');
	try {
		const res = await api.get('/theme');
		const data = (res && res.data) || {};
		const defaults = {
			bg:'#E2E8F0',text:'#0A0F1E',accent:'#0B3B8C',sidebarBg:'#0A1224',sidebarText:'#F1F5F9',cardBg:'#FFFFFF',
			border:'#94A3B8',mutedText:'#475569',success:'#16A34A',warning:'#D97706',danger:'#DC2626',info:'#1E40AF'
		};
		const theme = { ...defaults, ...data };
		const field = (id,label,val)=>`<label>${label}<input id="${id}" class="input" type="color" value="${val}"></label>`;
		$t.innerHTML = `
			<div class="u-grid" style="grid-template-columns:repeat(2,minmax(0,1fr));gap:var(--plg-gap);">
				${field('t-bg','Fondo (bg)',theme.bg)}
				${field('t-text','Texto (text)',theme.text)}
				${field('t-accent','Acento (accent)',theme.accent)}
				${field('t-sidebarBg','Sidebar fondo',theme.sidebarBg)}
				${field('t-sidebarText','Sidebar texto',theme.sidebarText)}
				${field('t-cardBg','Tarjeta fondo',theme.cardBg)}
				${field('t-border','Borde',theme.border)}
				${field('t-mutedText','Muted text',theme.mutedText)}
				${field('t-success','Success',theme.success)}
				${field('t-warning','Warning',theme.warning)}
				${field('t-danger','Danger',theme.danger)}
				${field('t-info','Info',theme.info)}
			</div>
			<div class="section u-mt-16">
				<div class="section-title">Presets rápidos</div>
				<div class="u-grid" style="grid-template-columns:repeat(5,minmax(0,1fr));gap:var(--plg-gap);">
					${Object.keys(themePresets).map(name=>`<button class="btn" data-preset="${name}">${name}</button>`).join('')}
				</div>
			</div>
			<div class="u-flex u-gap u-mt-16">
				<button id="t-preview" class="btn">Vista previa</button>
				<button id="t-save" class="btn btn-primary">Guardar</button>
				<button id="t-reset" class="btn" style="margin-left:auto;">Restablecer</button>
			</div>
			<pre id="t-msg" class="u-mt-16"></pre>
		`;
		const read = ()=>({
			bg:val('t-bg'),text:val('t-text'),accent:val('t-accent'),sidebarBg:val('t-sidebarBg'),sidebarText:val('t-sidebarText'),cardBg:val('t-cardBg'),
			border:val('t-border'),mutedText:val('t-mutedText'),success:val('t-success'),warning:val('t-warning'),danger:val('t-danger'),info:val('t-info')
		});
		function val(id){ return container.querySelector('#'+id).value; }
		function apply(vars){ Object.entries(vars).forEach(([k,v])=>document.documentElement.style.setProperty(`--plg-${k}`, v)); }
		container.querySelectorAll('[data-preset]').forEach(btn=>{
			btn.addEventListener('click', ()=>{
				applyPreset(btn.dataset.preset);
				// Rellenar los inputs con el preset para que al guardar quede persistido
				const p = themePresets[btn.dataset.preset];
				Object.entries(p).forEach(([k,v])=>{ const el = container.querySelector('#t-'+k); if (el) el.value = v; });
			});
		});
		container.querySelector('#t-preview').addEventListener('click', ()=>{ apply(read()); });
		container.querySelector('#t-save').addEventListener('click', async ()=>{
			const msg = container.querySelector('#t-msg'); msg.textContent = 'Guardando...';
			try{ await api.put('/theme', read()); msg.textContent = 'Guardado'; }catch(e){ msg.textContent = e.message||'Error'; }
		});
		container.querySelector('#t-reset').addEventListener('click', async ()=>{
			const msg = container.querySelector('#t-msg'); msg.textContent = 'Restableciendo...';
			try{ const r = await api.delete('/theme'); apply(r.data||defaults); msg.textContent = 'Restablecido'; }catch(e){ msg.textContent = e.message||'Error'; }
		});
	} catch(e) {
		$t.textContent = 'No se pudo cargar el tema';
	}
}
export function unmount(){}