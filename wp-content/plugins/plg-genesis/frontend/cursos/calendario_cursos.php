<?php
require_once(__DIR__ . '/../../../../../wp-load.php');

// Verificar si el usuario no está autenticado en WordPress
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());
    exit;
}

// Obtener el mes y año actual o de la URL
$mes = isset($_GET['mes']) ? intval($_GET['mes']) : intval(date('m'));
$anio = isset($_GET['anio']) ? intval($_GET['anio']) : intval(date('Y'));

// Nombres de los meses en español
$nombres_meses = [
    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
];

// Obtener el primer día del mes
$primer_dia = mktime(0, 0, 0, $mes, 1, $anio);
$dias_en_mes = date('t', $primer_dia);
$dia_semana_inicio = date('w', $primer_dia);

// URLs para los endpoints
$backend_url = plugins_url('plg-genesis/backend/cursos/obtener_cursos_por_mes.php');
$detalles_cursos_url = plugins_url('plg-genesis/backend/cursos/obtener_detalles_cursos_dia.php');
$eliminar_curso_url = plugins_url('plg-genesis/backend/cursos/eliminar_curso.php');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario de Cursos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f8fafc;
            padding: 20px;
            margin: 0;
        }

        .calendar-container {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .calendar-title {
            font-size: 24px;
            font-weight: 600;
            color: #1e293b;
            margin: 0;
        }

        .calendar-nav {
            display: flex;
            gap: 10px;
        }

        .nav-button {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            background: #3b82f6;
            color: white;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .nav-button:hover {
            background: #2563eb;
            color: white;
        }

        .calendar-stats {
            background: #f1f5f9;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
        }

        .total-courses {
            font-size: 16px;
            color: #059669;
            font-weight: 500;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
        }

        .calendar-day-header {
            text-align: center;
            font-weight: 500;
            color: #64748b;
            padding: 10px;
            background: #f8fafc;
            border-radius: 6px;
        }

        .dia {
            min-height: 100px;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px;
            background: white;
            transition: all 0.2s;
        }

        .dia:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .dia.otro-mes {
            background: #f8fafc;
            border-color: #f1f5f9;
        }

        .numero-dia {
            font-size: 14px;
            font-weight: 500;
            color: #64748b;
            margin-bottom: 5px;
        }

        .cantidad-cursos {
            font-size: 14px;
            color: #3b82f6;
            font-weight: 500;
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 4px;
            background: #eff6ff;
            display: inline-block;
            margin-top: 5px;
            transition: all 0.2s;
        }

        .cantidad-cursos:hover {
            background: #dbeafe;
            transform: translateY(-1px);
        }

        /* Estilos para el modal */
        .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            border-radius: 12px 12px 0 0;
            padding: 1rem 1.5rem;
        }

        .modal-title {
            color: #1e293b;
            font-weight: 600;
        }

        .curso-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            position: relative;
            transition: all 0.2s;
        }

        .curso-card:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .curso-card h5 {
            color: #2563eb;
            margin: 0 0 10px 0;
            font-size: 1.1rem;
        }

        .curso-info {
            margin-bottom: 5px;
            color: #64748b;
        }

        .curso-info strong {
            color: #1e293b;
        }

        .btn-eliminar {
            position: absolute;
            top: 15px;
            right: 15px;
            color: #ef4444;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-eliminar:hover {
            color: #dc2626;
            transform: scale(1.1);
        }

        .btn-certificado {
            position: absolute;
            top: 15px;
            right: 45px;
            color: #059669;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-certificado:hover {
            color: #047857;
            transform: scale(1.1);
        }

        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .calendar-container {
                padding: 10px;
            }

            .calendar-header {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }

            .calendar-grid {
                gap: 5px;
            }

            .dia {
                min-height: 80px;
                padding: 5px;
            }

            .calendar-title {
                font-size: 20px;
            }

            .nav-button {
                padding: 6px 12px;
                font-size: 14px;
            }

            .modal-dialog {
                margin: 10px;
            }
        }

        #btnGenerarTodosCertificados:hover {
            background-color: #146c43 !important;
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            transform: translateY(-1px);
        }

        #btnGenerarTodosCertificados:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .modal-header {
            align-items: stretch;
            padding: 1.5rem;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #212529;
            margin: 0;
        }

        .modal-header .btn-close {
            padding: calc(1.5rem * .5);
            margin: calc(-.5 * 1.5rem) calc(-.5 * 1.5rem) calc(-.5 * 1.5rem) auto;
        }
    </style>
</head>
<body>
    <div class="calendar-container">
        <div class="calendar-header">
            <h1 class="calendar-title"><?php echo $nombres_meses[$mes] . ' ' . $anio; ?></h1>
            <div class="calendar-nav">
                <a href="?mes=<?php echo $mes == 1 ? 12 : $mes - 1; ?>&anio=<?php echo $mes == 1 ? $anio - 1 : $anio; ?>" 
                   class="nav-button">< Mes anterior</a>
                <a href="?mes=<?php echo $mes == 12 ? 1 : $mes + 1; ?>&anio=<?php echo $mes == 12 ? $anio + 1 : $anio; ?>" 
                   class="nav-button">Mes siguiente ></a>
            </div>
        </div>

        <div class="calendar-stats">
            <div class="total-courses">Total de cursos este mes: <span id="total-cursos">0</span></div>
        </div>

        <div class="calendar-grid">
            <div class="calendar-day-header">Dom</div>
            <div class="calendar-day-header">Lun</div>
            <div class="calendar-day-header">Mar</div>
            <div class="calendar-day-header">Mié</div>
            <div class="calendar-day-header">Jue</div>
            <div class="calendar-day-header">Vie</div>
            <div class="calendar-day-header">Sáb</div>

            <?php
            // Días del mes anterior
            for ($i = 0; $i < $dia_semana_inicio; $i++) {
                $ultimo_dia_mes_anterior = date('t', mktime(0, 0, 0, $mes - 1, 1, $anio));
                $dia = $ultimo_dia_mes_anterior - ($dia_semana_inicio - $i - 1);
                echo '<div class="dia otro-mes"><div class="numero-dia">' . $dia . '</div></div>';
            }

            // Días del mes actual
            for ($dia = 1; $dia <= $dias_en_mes; $dia++) {
                echo '<div class="dia" data-dia="' . $dia . '">';
                echo '<div class="numero-dia">' . $dia . '</div>';
                echo '<div class="cantidad-cursos" data-dia="' . $dia . '"></div>';
                echo '</div>';
            }

            // Días del mes siguiente
            $dias_siguientes = 42 - ($dia_semana_inicio + $dias_en_mes); // 42 = 6 semanas * 7 días
            for ($i = 1; $i <= $dias_siguientes; $i++) {
                echo '<div class="dia otro-mes"><div class="numero-dia">' . $i . '</div></div>';
            }
            ?>
        </div>
    </div>

    <!-- Modal para detalles de cursos -->
    <div class="modal fade" id="modalDetallesCursos" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header flex-column">
                    <div class="w-100 d-flex justify-content-between align-items-center mb-3">
                        <h5 class="modal-title" id="modalDetallesCursosLabel">Detalles de Cursos</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <button type="button" class="btn btn-success w-100 d-flex align-items-center justify-content-center" id="btnGenerarTodosCertificados" style="background-color: #198754; border: none; padding: 10px; font-size: 0.95rem; font-weight: 500; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: all 0.3s ease;">
                        <i class="bi bi-file-earmark-pdf me-2" style="font-size: 1.1rem;"></i>
                        <span>Generar Certificados</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="detalles-cursos-container"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación -->
    <div class="modal fade" id="modalConfirmarEliminacion" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas eliminar este registro de curso?</p>
                    <p class="text-danger"><strong>¡ADVERTENCIA!</strong> Esta acción no se puede deshacer.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="btnConfirmarEliminacion">Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Variables globales
        let cursoAEliminar = null;

        // Cargar datos del calendario
        function cargarDatosCalendario() {
            fetch('<?php echo $backend_url; ?>?mes=<?php echo $mes; ?>&anio=<?php echo $anio; ?>')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('total-cursos').textContent = data.total_mes;
                    document.querySelectorAll('.cantidad-cursos').forEach(el => el.textContent = '');
                    
                    Object.entries(data.cursos_por_dia).forEach(([dia, cantidad]) => {
                        const diaElement = document.querySelector(`.dia[data-dia="${dia}"] .cantidad-cursos`);
                        if (diaElement && cantidad > 0) {
                            diaElement.textContent = cantidad + ' cursos';
                        }
                    });
                })
                .catch(error => console.error('Error:', error));
        }

        // Cargar detalles de cursos
        function cargarDetallesCursos(dia, mes, anio) {
            const modal = new bootstrap.Modal(document.getElementById('modalDetallesCursos'));
            const container = document.getElementById('detalles-cursos-container');
            
            // Guardar la fecha para el botón de generar todos los certificados
            container.dataset.dia = dia;
            container.dataset.mes = mes;
            container.dataset.anio = anio;
            
            container.innerHTML = '<div class="text-center p-4"><div class="spinner-border text-primary"></div></div>';
            modal.show();
            
            fetch(`<?php echo $detalles_cursos_url; ?>?dia=${dia}&mes=${mes}&anio=${anio}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.cursos.length > 0) {
                        container.innerHTML = data.cursos.map(curso => `
                            <div class="curso-card">
                                <h5>${curso.nombre_curso}</h5>
                                <div class="curso-info"><strong>ID Estudiante:</strong> ${curso.estudiante_id || 'No disponible'}</div>
                                <div class="curso-info"><strong>Estudiante:</strong> ${curso.nombre_estudiante}</div>
                                <div class="curso-info"><strong>Celular:</strong> ${curso.celular || 'No disponible'}</div>
                                <div class="curso-info"><strong>Contacto:</strong> ${curso.nombre_contacto || 'No disponible'}</div>
                                <div class="curso-info"><strong>Nota:</strong> ${curso.nota || 'No disponible'}</div>
                                <i class="bi bi-trash btn-eliminar" data-id="${curso.estudiante_curso_id}"></i>
                                <i class="bi bi-file-earmark-pdf btn-certificado" data-id="${curso.estudiante_curso_id}" title="Generar Certificado"></i>
                            </div>
                        `).join('');
                    } else {
                        container.innerHTML = '<p class="text-center">No hay cursos programados para este día.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    container.innerHTML = '<p class="text-center text-danger">Error al cargar los detalles.</p>';
                });
        }

        // Eliminar curso
        function eliminarCurso(id) {
            cursoAEliminar = id;
            const modalConfirmacion = new bootstrap.Modal(document.getElementById('modalConfirmarEliminacion'));
            modalConfirmacion.show();
        }

        // Confirmar eliminación
        function confirmarEliminacion() {
            if (!cursoAEliminar) return;
            
            fetch('<?php echo $eliminar_curso_url; ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${cursoAEliminar}`
            })
            .then(response => response.json())
            .then(data => {
                const modalConfirmacion = bootstrap.Modal.getInstance(document.getElementById('modalConfirmarEliminacion'));
                modalConfirmacion.hide();
                
                if (data.success) {
                    cargarDatosCalendario();
                    const modalDetalles = bootstrap.Modal.getInstance(document.getElementById('modalDetallesCursos'));
                    modalDetalles.hide();
                    alert('Curso eliminado correctamente');
                } else {
                    alert(data.message || 'Error al eliminar el curso');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar la solicitud');
            });
        }

        // Event Listeners
        document.addEventListener('DOMContentLoaded', () => {
            cargarDatosCalendario();

            document.addEventListener('click', (e) => {
                if (e.target.classList.contains('cantidad-cursos') && e.target.textContent) {
                    const dia = e.target.getAttribute('data-dia');
                    cargarDetallesCursos(dia, <?php echo $mes; ?>, <?php echo $anio; ?>);
                }
                
                if (e.target.classList.contains('btn-eliminar')) {
                    eliminarCurso(e.target.getAttribute('data-id'));
                }

                if (e.target.classList.contains('btn-certificado')) {
                    const id = e.target.getAttribute('data-id');
                    window.open('../../backend/certificados/generar_certificado.php?id=' + id, '_blank');
                }
            });

            document.getElementById('btnConfirmarEliminacion').addEventListener('click', confirmarEliminacion);

            document.getElementById('btnGenerarTodosCertificados').addEventListener('click', function() {
                const container = document.getElementById('detalles-cursos-container');
                const dia = container.dataset.dia;
                const mes = container.dataset.mes;
                const anio = container.dataset.anio;
                
                if (dia && mes && anio) {
                    window.open(`../../backend/certificados/generar_certificados_dia.php?dia=${dia}&mes=${mes}&anio=${anio}`, '_blank');
                }
            });
        });
    </script>
</body>
</html> 