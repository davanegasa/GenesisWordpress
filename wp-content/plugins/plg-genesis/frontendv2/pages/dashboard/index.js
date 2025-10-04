import { api } from '../../api/client.js';

export async function mount(container) {
	container.innerHTML = `
		<div class="hero">
			<h1>Bienvenido a Genesis</h1>
			<p>Sistema de gestión para la escuela Bíblica Emmaus</p>
		</div>
		<div class="kpi-grid">
			<div class="kpi-card"><div class="kpi-label">Estudiantes activos</div><div id="k1" class="kpi-value">-</div></div>
			<div class="kpi-card"><div class="kpi-label">Cursos este mes</div><div id="k2" class="kpi-value">-</div></div>
			<div class="kpi-card"><div class="kpi-label">Cursos completados</div><div id="k3" class="kpi-value">-</div></div>
			<div class="kpi-card"><div class="kpi-label">Contactos registrados</div><div id="k4" class="kpi-value">-</div></div>
		</div>
		<div class="card u-mt-16">
			<div class="card-title">Actividad reciente</div>
			<ul id="activity" class="activity-list"></ul>
		</div>
	`;
	try {
		const res = await api.get('/estadisticas');
		const d = res && res.data ? res.data : {};
		document.getElementById('k1').textContent = d.estudiantesActivos ?? '-';
		document.getElementById('k2').textContent = d.cursosMes ?? '-';
		document.getElementById('k3').textContent = d.cursosCompletados ?? '-';
		document.getElementById('k4').textContent = d.contactosActivos ?? '-';

		const list = document.getElementById('activity');
		list.innerHTML = '';
		(d.actividades || []).forEach(a => {
			const li = document.createElement('li');
			li.className = 'activity-item';
			li.innerHTML = `
				<span class="activity-type">${a.tipo || ''}</span>
				<span class="activity-text">${a.texto || ''}</span>
				<span class="activity-time">${a.tiempo || ''}</span>
			`;
			list.appendChild(li);
		});
	} catch (e) {
		const list = document.getElementById('activity');
		list.innerHTML = '<li class="activity-item"><span class="activity-text">Error cargando KPIs</span></li>';
	}
}
export function unmount() {}