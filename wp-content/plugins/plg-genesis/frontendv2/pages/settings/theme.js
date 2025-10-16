import { api } from '../../api/client.js';
import { themePresets, applyPreset } from '../../core/theme.js';
import { showToast } from '../../components/ui/toast.js';

export async function mount(container){
	container.innerHTML = `
		<style>
			.theme-header {
				text-align: center;
				margin-bottom: 32px;
			}
			.theme-header h1 {
				font-size: 28px;
				font-weight: 700;
				color: var(--plg-accent);
				margin: 0 0 8px 0;
			}
			.theme-header p {
				color: var(--plg-mutedText);
				font-size: 15px;
			}
			.theme-section {
				margin-bottom: 32px;
			}
			.theme-section-title {
				font-size: 18px;
				font-weight: 700;
				color: var(--plg-text);
				margin: 0 0 16px 0;
				padding-bottom: 8px;
				border-bottom: 2px solid var(--plg-border);
			}
			.theme-grid {
				display: grid;
				grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
				gap: 16px;
			}
			.color-field {
				background: var(--plg-cardBg);
				border: 2px solid var(--plg-border);
				border-radius: 12px;
				padding: 16px;
				transition: all 0.2s ease;
				position: relative;
				overflow: hidden;
			}
			.color-field::before {
				content: '';
				position: absolute;
				top: 0;
				left: 0;
				right: 0;
				bottom: 0;
				background: white;
				opacity: 0.85;
				z-index: 0;
				pointer-events: none;
			}
			.color-field > * {
				position: relative;
				z-index: 1;
			}
			.color-field:hover {
				box-shadow: 0 6px 16px rgba(0,0,0,0.15);
				transform: translateY(-3px);
			}
			.color-field label {
				display: block;
				font-size: 13px;
				font-weight: 600;
				color: var(--plg-text);
				margin-bottom: 8px;
				text-transform: uppercase;
				letter-spacing: 0.3px;
			}
			.color-input-wrapper {
				display: flex;
				gap: 10px;
				align-items: center;
			}
			.color-preview {
				width: 48px;
				height: 48px;
				border-radius: 8px;
				border: 2px solid var(--plg-border);
				cursor: pointer;
				transition: transform 0.2s ease;
			}
			.color-preview:hover {
				transform: scale(1.1);
			}
			.color-input {
				flex: 1;
				padding: 10px 12px;
				border: 1px solid var(--plg-border);
				border-radius: 8px;
				font-size: 14px;
				font-family: monospace;
				text-transform: uppercase;
			}
			.preset-grid {
				display: grid;
				grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
				gap: 12px;
			}
			.preset-btn {
				padding: 14px 18px;
				border: 2px solid var(--plg-border);
				border-radius: 12px;
				background: var(--plg-cardBg);
				color: var(--plg-text);
				font-weight: 600;
				font-size: 15px;
				cursor: pointer;
				transition: all 0.2s ease;
				text-transform: capitalize;
			}
			.preset-btn:hover {
				border-color: var(--plg-accent);
				background: color-mix(in srgb, var(--plg-accent) 8%, var(--plg-cardBg));
				transform: translateY(-2px);
				box-shadow: 0 4px 12px rgba(0,0,0,0.1);
			}
			.action-buttons {
				display: flex;
				gap: 12px;
				margin-top: 32px;
				padding-top: 24px;
				border-top: 1px solid var(--plg-border);
			}
			@media (max-width: 768px) {
				.theme-grid {
					grid-template-columns: 1fr;
				}
				.preset-grid {
					grid-template-columns: repeat(2, 1fr);
				}
				.action-buttons {
					flex-direction: column;
				}
			}
		</style>
		<div class="card">
			<div class="theme-header">
				<h1>🎨 Personalización del Tema</h1>
				<p>Configura los colores de la interfaz para tu oficina</p>
			</div>
			<div id="t">Cargando...</div>
		</div>
	`;
	const $t = container.querySelector('#t');
	try {
		const res = await api.get('/theme');
		const data = (res && res.data) || {};
		const defaults = {
			bg:'#E2E8F0',text:'#0A0F1E',accent:'#0B3B8C',sidebarBg:'#0A1224',sidebarText:'#F1F5F9',cardBg:'#FFFFFF',
			border:'#94A3B8',mutedText:'#475569',success:'#16A34A',warning:'#D97706',danger:'#DC2626',info:'#1E40AF'
		};
		const theme = { ...defaults, ...data };
		
		const field = (id, label, val, desc) => `
			<div class="color-field">
				<label for="${id}">${label}</label>
				<div class="color-input-wrapper">
					<input type="color" id="${id}-picker" value="${val}" class="color-preview" title="Selector de color">
					<input type="text" id="${id}" value="${val}" class="color-input" placeholder="#000000">
				</div>
				${desc ? `<div style="font-size: 12px; color: var(--plg-mutedText); margin-top: 6px;">${desc}</div>` : ''}
			</div>
		`;
		
		$t.innerHTML = `
			<div class="theme-section">
				<div class="theme-section-title">🎨 Colores Base</div>
				<div class="theme-grid">
					${field('t-bg', 'Fondo', theme.bg, 'Color de fondo principal')}
					${field('t-text', 'Texto', theme.text, 'Color del texto')}
					${field('t-accent', 'Acento', theme.accent, 'Color de marca/primario')}
					${field('t-cardBg', 'Tarjetas', theme.cardBg, 'Fondo de tarjetas/cards')}
					${field('t-border', 'Bordes', theme.border, 'Color de bordes')}
					${field('t-mutedText', 'Texto Apagado', theme.mutedText, 'Texto secundario')}
				</div>
			</div>
			
			<div class="theme-section">
				<div class="theme-section-title">📐 Sidebar/Menú</div>
				<div class="theme-grid">
					${field('t-sidebarBg', 'Fondo Sidebar', theme.sidebarBg, 'Fondo del menú lateral')}
					${field('t-sidebarText', 'Texto Sidebar', theme.sidebarText, 'Texto del menú')}
				</div>
			</div>
			
			<div class="theme-section">
				<div class="theme-section-title">✨ Estados y Feedback</div>
				<div class="theme-grid">
					${field('t-success', 'Success', theme.success, 'Acciones positivas')}
					${field('t-warning', 'Warning', theme.warning, 'Advertencias')}
					${field('t-danger', 'Danger', theme.danger, 'Errores/peligro')}
					${field('t-info', 'Info', theme.info, 'Información general')}
				</div>
			</div>
			
			<div class="theme-section">
				<div class="theme-section-title">🎭 Presets Rápidos</div>
				<div class="preset-grid">
					${Object.keys(themePresets).map(name=>`<button class="preset-btn" data-preset="${name}">${name}</button>`).join('')}
				</div>
			</div>
			
			<div class="action-buttons">
				<button id="t-preview" class="btn btn-secondary" style="flex: 1;">👁️ Vista Previa</button>
				<button id="t-save" class="btn btn-primary" style="flex: 1;">💾 Guardar Cambios</button>
				<button id="t-reset" class="btn" style="flex: 0.5;">🔄 Restablecer</button>
			</div>
		`;
		// Sincronizar color pickers con inputs de texto y actualizar fondo
		const fields = ['bg', 'text', 'accent', 'sidebarBg', 'sidebarText', 'cardBg', 'border', 'mutedText', 'success', 'warning', 'danger', 'info'];
		fields.forEach(field => {
			const picker = container.querySelector(`#t-${field}-picker`);
			const input = container.querySelector(`#t-${field}`);
			const colorField = picker.closest('.color-field');
			
			// Función para actualizar el fondo de la tarjeta
			const updateBackground = (color) => {
				colorField.style.background = `linear-gradient(135deg, ${color}15 0%, ${color}25 100%)`;
				colorField.style.borderColor = color + '40';
			};
			
			// Aplicar color inicial
			updateBackground(picker.value);
			
			// Cuando cambia el picker, actualizar el input y el fondo
			picker.addEventListener('input', (e) => {
				const color = e.target.value.toUpperCase();
				input.value = color;
				updateBackground(color);
			});
			
			// Cuando cambia el input, actualizar el picker y el fondo
			input.addEventListener('input', (e) => {
				const value = e.target.value;
				if (/^#[0-9A-F]{6}$/i.test(value)) {
					picker.value = value;
					updateBackground(value);
				}
			});
		});
		
		const read = ()=>({
			bg:val('t-bg'),text:val('t-text'),accent:val('t-accent'),sidebarBg:val('t-sidebarBg'),sidebarText:val('t-sidebarText'),cardBg:val('t-cardBg'),
			border:val('t-border'),mutedText:val('t-mutedText'),success:val('t-success'),warning:val('t-warning'),danger:val('t-danger'),info:val('t-info')
		});
		function val(id){ return container.querySelector('#'+id).value; }
		function apply(vars){ Object.entries(vars).forEach(([k,v])=>document.documentElement.style.setProperty(`--plg-${k}`, v)); }
		
		// Presets
		container.querySelectorAll('[data-preset]').forEach(btn=>{
			btn.addEventListener('click', ()=>{
				const presetName = btn.dataset.preset;
				applyPreset(presetName);
				// Rellenar los inputs con el preset y actualizar fondos
				const p = themePresets[presetName];
				Object.entries(p).forEach(([k,v])=>{ 
					const input = container.querySelector('#t-'+k);
					const picker = container.querySelector('#t-'+k+'-picker');
					const colorField = picker?.closest('.color-field');
					if (input) input.value = v;
					if (picker) picker.value = v;
					// Actualizar fondo de la tarjeta
					if (colorField) {
						colorField.style.background = `linear-gradient(135deg, ${v}15 0%, ${v}25 100%)`;
						colorField.style.borderColor = v + '40';
					}
				});
				showToast(`Preset "${presetName}" aplicado`, 'success', 3000);
			});
		});
		
		// Vista previa
		container.querySelector('#t-preview').addEventListener('click', ()=>{ 
			apply(read());
			showToast('Vista previa aplicada. Los cambios no se han guardado.', 'info', 4000);
		});
		
		// Guardar
		container.querySelector('#t-save').addEventListener('click', async ()=>{
			try{ 
				await api.put('/theme', read());
				showToast('✓ Tema guardado correctamente', 'success');
			}catch(e){ 
				showToast('Error al guardar: ' + (e.message||'Error desconocido'), 'error');
			}
		});
		
		// Restablecer
		container.querySelector('#t-reset').addEventListener('click', async ()=>{
			try{ 
				const r = await api.delete('/theme');
				apply(r.data||defaults);
				// Actualizar también los inputs y fondos
				Object.entries(r.data||defaults).forEach(([k,v])=>{
					const input = container.querySelector('#t-'+k);
					const picker = container.querySelector('#t-'+k+'-picker');
					const colorField = picker?.closest('.color-field');
					if (input) input.value = v;
					if (picker) picker.value = v;
					// Actualizar fondo de la tarjeta
					if (colorField) {
						colorField.style.background = `linear-gradient(135deg, ${v}15 0%, ${v}25 100%)`;
						colorField.style.borderColor = v + '40';
					}
				});
				showToast('Tema restablecido a valores predeterminados', 'success');
			}catch(e){ 
				showToast('Error al restablecer: ' + (e.message||'Error desconocido'), 'error');
			}
		});
	} catch(e) {
		$t.textContent = 'No se pudo cargar el tema';
		showToast('Error al cargar la configuración del tema', 'error');
	}
}
export function unmount(){}