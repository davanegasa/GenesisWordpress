import { api } from '../api/client.js';

export async function mount(container, { contactoId = null, titulo = 'Próximos a Graduarse' } = {}) {
    const listId = `proximos-list-${Date.now()}`;
    const selectId = `programa-select-${Date.now()}`;
    
    container.innerHTML = `
        <div class="card" style="border-left: 4px solid #ff9800;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; flex-wrap: wrap; gap: 10px;">
                <h3 style="margin: 0; color: #ff9800; display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 1.5rem;">🔥</span>
                    ${titulo}
                </h3>
                <div style="display: flex; align-items: center; gap: 10px;">
                    ${contactoId ? `
                        <select id="${selectId}" class="form-control" style="width: auto; font-size: 0.9rem;">
                            <option value="">Cargando programas...</option>
                        </select>
                    ` : ''}
                    <span style="font-size: 0.9rem; color: #666;">≥ 80% de progreso</span>
                </div>
            </div>
            <div id="${listId}">
                <div style="text-align: center; padding: 30px; color: #999;">
                    <div style="font-size: 3rem; margin-bottom: 10px;">⏳</div>
                    <p>Cargando programas...</p>
                </div>
            </div>
        </div>
    `;

    const $list = container.querySelector(`#${listId}`);
    const $select = contactoId ? container.querySelector(`#${selectId}`) : null;

    let proximosCache = {}; // Cache de estudiantes por programa
    let programasDisponibles = [];

    try {
        // PASO 1: Cargar solo la lista de programas (rápido)
        if (contactoId && $select) {
            const responseProgramas = await api.get(`/diplomas/proximos-completar/programas?contactoId=${contactoId}&umbral=80`);
            
            if (!responseProgramas || !responseProgramas.success) {
                throw new Error('Error cargando programas');
            }

            programasDisponibles = responseProgramas.data || [];

            if (programasDisponibles.length === 0) {
                $list.innerHTML = `
                    <div style="text-align: center; padding: 30px; color: #999;">
                        <div style="font-size: 3rem; margin-bottom: 10px;">📚</div>
                        <p>Este contacto no tiene estudiantes cerca de completar</p>
                    </div>
                `;
                $select.innerHTML = '<option value="">Sin programas disponibles</option>';
                $select.disabled = true;
                return;
            }

            // Poblar el selector
            const totalEstudiantes = programasDisponibles.reduce((sum, p) => sum + p.total_estudiantes, 0);
            $select.innerHTML = `
                <option value="">📚 Todos los programas (${totalEstudiantes})</option>
                ${programasDisponibles.map(p => 
                    `<option value="${p.programa_id}">${escapeHtml(p.programa_nombre)} (${p.total_estudiantes})</option>`
                ).join('')}
            `;
            
            // Evento de cambio de programa
            $select.addEventListener('change', function() {
                const programaId = this.value ? parseInt(this.value) : null;
                cargarYRenderizar(programaId);
            });

            // PASO 2: Si solo hay 1 programa, cargarlo automáticamente
            if (programasDisponibles.length === 1) {
                $select.value = programasDisponibles[0].programa_id;
                await cargarYRenderizar(programasDisponibles[0].programa_id);
            } else {
                // Si hay múltiples, mostrar mensaje de selección
                $list.innerHTML = `
                    <div style="text-align: center; padding: 30px; color: #999;">
                        <div style="font-size: 3rem; margin-bottom: 10px;">🎯</div>
                        <p>Selecciona un programa para ver los estudiantes</p>
                        <p style="font-size: 0.9rem; color: #666;">o selecciona "Todos los programas"</p>
                    </div>
                `;
            }
        } else {
            // Si no hay contacto, no mostrar nada (ya no usamos esta vista)
            $list.innerHTML = `
                <div style="text-align: center; padding: 30px; color: #999;">
                    <div style="font-size: 3rem; margin-bottom: 10px;">📚</div>
                    <p>Por favor, consulta los estudiantes desde el detalle de un contacto</p>
                </div>
            `;
        }

    } catch (error) {
        console.error('Error cargando próximos:', error);
        $list.innerHTML = '<div style="color: #dc3545; padding: 20px;">Error cargando datos</div>';
    }

    // Función para cargar estudiantes de un programa (con cache) y renderizar
    async function cargarYRenderizar(programaId) {
        const cacheKey = programaId || 'todos';

        // Si ya está en cache, renderizar directamente
        if (proximosCache[cacheKey]) {
            renderProximos(proximosCache[cacheKey]);
            return;
        }

        // Mostrar loading
        $list.innerHTML = `
            <div style="text-align: center; padding: 30px; color: #999;">
                <div style="font-size: 3rem; margin-bottom: 10px;">⏳</div>
                <p>Cargando estudiantes...</p>
            </div>
        `;

        try {
            // Cargar datos desde el servidor
            const url = programaId 
                ? `/diplomas/proximos-completar?limite=50&umbral=80&contactoId=${contactoId}&programaId=${programaId}`
                : `/diplomas/proximos-completar?limite=50&umbral=80&contactoId=${contactoId}`;
            
            const response = await api.get(url);
            
            if (!response || !response.success) {
                throw new Error('Error cargando estudiantes');
            }

            const proximos = response.data || [];
            
            // Guardar en cache
            proximosCache[cacheKey] = proximos;

            // Renderizar
            renderProximos(proximos);

        } catch (error) {
            console.error('Error cargando estudiantes:', error);
            $list.innerHTML = '<div style="color: #dc3545; padding: 20px;">Error cargando estudiantes</div>';
        }
    }

    // Función para renderizar la lista de estudiantes
    function renderProximos(proximos) {
        if (!proximos || proximos.length === 0) {
            $list.innerHTML = `
                <div style="text-align: center; padding: 30px; color: #999;">
                    <div style="font-size: 3rem; margin-bottom: 10px;">📚</div>
                    <p>No hay estudiantes cerca de completar</p>
                </div>
            `;
            return;
        }

        let html = '<div style="display: flex; flex-direction: column; gap: 12px;">';

        proximos.forEach(item => {
            const progresoColor = item.progreso >= 95 ? '#4caf50' : 
                                 item.progreso >= 90 ? '#ff9800' : '#ffc107';
            
            const nombreCompleto = item.tipo === 'nivel'
                ? `${item.programa_nombre} - ${item.nivel_nombre}`
                : `${item.programa_nombre} (Completo)`;

            html += `
                <div style="padding: 12px; background: ${item.progreso >= 95 ? '#f1f8e9' : '#fff8e1'}; border-radius: 8px; border-left: 4px solid ${progresoColor};">
                    <div style="display: flex; justify-content: space-between; align-items: start; gap: 15px;">
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                                <span style="font-size: 1.2rem;">${item.progreso >= 95 ? '🎯' : '⏳'}</span>
                                <div>
                                    <a href="#/estudiante/${encodeURIComponent(item.estudiante_codigo)}" 
                                       style="font-weight: 700; color: #007bff; text-decoration: none; font-size: 1rem;">
                                        ${escapeHtml(item.estudiante_codigo)}
                                    </a>
                                    <span style="color: #666; font-size: 0.9rem;"> - ${escapeHtml(item.estudiante_nombre)}</span>
                                </div>
                            </div>
                            <div style="font-weight: 600; color: #333; margin-bottom: 4px;">
                                ${escapeHtml(nombreCompleto)}
                            </div>
                            <div style="font-size: 0.85rem; color: #666;">
                                <span style="font-weight: 600; color: ${progresoColor};">${item.progreso}%</span> completo
                                • ${item.cursos_completados}/${item.cursos_totales} cursos
                                ${item.cursos_faltantes.length > 0 ? 
                                    `• Falta${item.cursos_faltantes.length > 1 ? 'n' : ''}: <strong>${item.cursos_faltantes.length}</strong> curso${item.cursos_faltantes.length > 1 ? 's' : ''}` 
                                    : ''}
                            </div>
                            ${item.cursos_faltantes.length > 0 && item.cursos_faltantes.length <= 3 ? `
                                <div style="margin-top: 6px; font-size: 0.8rem; color: #999;">
                                    ${item.cursos_faltantes.map(c => `<span style="padding: 2px 6px; background: #f5f5f5; border-radius: 3px; margin-right: 4px;">${escapeHtml(c.nombre)}</span>`).join('')}
                                </div>
                            ` : ''}
                        </div>
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 4px;">
                            <div style="
                                width: 60px;
                                height: 60px;
                                border-radius: 50%;
                                background: conic-gradient(${progresoColor} ${item.progreso * 3.6}deg, #e0e0e0 0deg);
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                font-weight: 700;
                                font-size: 0.9rem;
                                position: relative;
                            ">
                                <div style="
                                    width: 48px;
                                    height: 48px;
                                    border-radius: 50%;
                                    background: white;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    color: ${progresoColor};
                                ">
                                    ${item.progreso}%
                                </div>
                            </div>
                            ${item.progreso >= 95 ? `
                                <button 
                                    class="btn btn-sm btn-success" 
                                    style="font-size: 0.75rem; padding: 4px 8px;"
                                    onclick="animarEstudiante('${encodeURIComponent(item.estudiante_codigo)}', '${escapeHtml(item.estudiante_nombre)}')">
                                    💪 Animar
                                </button>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;
        });

        html += '</div>';
        $list.innerHTML = html;

        // Setup acción de animar
        window.animarEstudiante = function(codigo, nombre) {
            alert(`💪 ¡Vamos ${nombre}! Estás muy cerca de completar, no te rindas!`);
            // Aquí podrías agregar funcionalidad para enviar mensaje/email
        };
    }
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

export function unmount() {
    if (window.animarEstudiante) {
        delete window.animarEstudiante;
    }
}

