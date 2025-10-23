import { api } from '../../api/client.js';

export async function mount(container, { id } = {}) {
    container.innerHTML = `
        <div class="card">
            <div id="acta-detail-content">Cargando acta...</div>
        </div>
    `;

    const $content = container.querySelector('#acta-detail-content');

    try {
        const response = await api.get(`/actas/${id}`);
        
        if (!response || !response.success) {
            throw new Error('Error cargando acta');
        }

        const acta = response.data;
        renderActaDetail($content, acta);

        // Si viene con parámetro print, imprimir automáticamente
        const urlParams = new URLSearchParams(window.location.hash.split('?')[1]);
        if (urlParams.get('print') === 'true') {
            setTimeout(() => window.print(), 500);
        }

    } catch (error) {
        console.error('Error cargando acta:', error);
        $content.innerHTML = '<div class="alert alert-danger">Error cargando acta</div>';
    }
}

function renderActaDetail($content, acta) {
    const fecha = new Date(acta.fecha_acta).toLocaleDateString('es-ES', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });

    $content.innerHTML = `
        <style>
            @media print {
                .no-print, .btn, button { 
                    display: none !important; 
                }
                body, .card {
                    background: white !important;
                    color: black !important;
                }
            }
            .acta-header {
                text-align: center;
                padding: 30px;
                margin-bottom: 30px;
                border-bottom: 3px solid #007bff;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border-radius: 8px;
            }
            .acta-header h1 {
                margin: 0 0 10px 0;
                font-size: 2rem;
                font-weight: 700;
            }
            .acta-header .numero-acta {
                font-size: 1.5rem;
                font-weight: 700;
                background: rgba(255,255,255,0.2);
                padding: 10px 20px;
                border-radius: 8px;
                display: inline-block;
                margin-top: 10px;
            }
            .acta-info {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
                gap: 20px;
                margin-bottom: 30px;
                padding: 20px;
                background: #f8f9fa;
                border-radius: 8px;
            }
            .acta-info-item {
                display: flex;
                flex-direction: column;
            }
            .acta-info-label {
                font-size: 0.9rem;
                color: #666;
                margin-bottom: 5px;
                font-weight: 600;
            }
            .acta-info-value {
                font-size: 1.1rem;
                color: #333;
                font-weight: 700;
            }
            .diplomas-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }
            .diplomas-table th,
            .diplomas-table td {
                padding: 12px;
                text-align: left;
                border-bottom: 1px solid #e0e0e0;
            }
            .diplomas-table th {
                background: #007bff;
                color: white;
                font-weight: 600;
            }
            .diplomas-table tbody tr:hover {
                background: #f5f5f5;
            }
            .diploma-tipo-badge {
                display: inline-block;
                padding: 4px 12px;
                border-radius: 12px;
                font-size: 0.85rem;
                font-weight: 600;
            }
            .badge-nivel {
                background: #e3f2fd;
                color: #1976d2;
            }
            .badge-completo {
                background: #e8f5e9;
                color: #388e3c;
            }
        </style>

        <div class="no-print" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
            <a href="#/contacto/${escapeHtml(acta.contacto_id || '')}" class="btn btn-secondary">
                ← Volver al Contacto
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                🖨️ Imprimir Acta
            </button>
        </div>

        <div class="acta-header">
            <h1>📋 ACTA DE DIPLOMAS</h1>
            <div class="numero-acta">${escapeHtml(acta.numero_acta)}</div>
        </div>

        <div class="acta-info">
            <div class="acta-info-item">
                <div class="acta-info-label">Fecha de Emisión</div>
                <div class="acta-info-value">${fecha}</div>
            </div>
            ${acta.contacto_nombre ? `
                <div class="acta-info-item">
                    <div class="acta-info-label">Contacto</div>
                    <div class="acta-info-value">${escapeHtml(acta.contacto_nombre)}</div>
                </div>
            ` : ''}
            ${acta.contacto_email ? `
                <div class="acta-info-item">
                    <div class="acta-info-label">Email</div>
                    <div class="acta-info-value">${escapeHtml(acta.contacto_email)}</div>
                </div>
            ` : ''}
            <div class="acta-info-item">
                <div class="acta-info-label">Total Diplomas</div>
                <div class="acta-info-value" style="color: #28a745;">${acta.total_diplomas}</div>
            </div>
            <div class="acta-info-item">
                <div class="acta-info-label">Tipo de Acta</div>
                <div class="acta-info-value">${acta.tipo_acta === 'cierre' ? 'Acta de Cierre' : acta.tipo_acta}</div>
            </div>
        </div>

        ${acta.observaciones ? `
            <div style="padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107; margin-bottom: 20px; border-radius: 4px;">
                <strong>Observaciones:</strong><br>
                ${escapeHtml(acta.observaciones)}
            </div>
        ` : ''}

        <h3 style="margin-bottom: 15px; color: #333;">Diplomas Emitidos</h3>

        ${acta.diplomas && acta.diplomas.length > 0 ? `
            <table class="diplomas-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Estudiante</th>
                        <th>Programa</th>
                        <th>Tipo</th>
                        <th>Nivel</th>
                        <th>Fecha Emisión</th>
                        <th>Estado Entrega</th>
                    </tr>
                </thead>
                <tbody>
                    ${acta.diplomas.map((d, idx) => `
                        <tr>
                            <td><strong>${idx + 1}</strong></td>
                            <td>
                                <strong style="color: #007bff;">${escapeHtml(d.estudiante_codigo)}</strong><br>
                                <span style="font-size: 0.9rem; color: #666;">${escapeHtml(d.estudiante_nombre)}</span>
                            </td>
                            <td>${escapeHtml(d.programa_nombre)}</td>
                            <td>
                                <span class="diploma-tipo-badge ${d.tipo === 'nivel' ? 'badge-nivel' : 'badge-completo'}">
                                    ${d.tipo === 'nivel' ? 'Nivel' : 'Programa Completo'}
                                </span>
                            </td>
                            <td>${d.nivel_nombre ? escapeHtml(d.nivel_nombre) : 'N/A'}</td>
                            <td>${formatDate(d.fecha_emision)}</td>
                            <td>
                                ${d.fecha_entrega 
                                    ? `<span style="color: #28a745;">✅ Entregado (${formatDate(d.fecha_entrega)})</span>` 
                                    : '<span style="color: #ffc107;">⏳ Pendiente</span>'}
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        ` : '<p style="text-align: center; color: #999; padding: 40px;">No hay diplomas en esta acta.</p>'}

        <div style="margin-top: 50px; padding: 30px; border-top: 2px solid #e0e0e0;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 50px; margin-top: 30px;">
                <div style="text-align: center;">
                    <div style="border-top: 2px solid #333; padding-top: 10px; margin-top: 50px;">
                        <strong>Firma Autorizada</strong>
                    </div>
                </div>
                <div style="text-align: center;">
                    <div style="border-top: 2px solid #333; padding-top: 10px; margin-top: 50px;">
                        <strong>Recibido Por</strong>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric' 
    });
}

export function unmount() {}

