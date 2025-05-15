<?php
require_once(__DIR__ . '/../../../../../wp-load.php');

// Verificar si el usuario está autenticado
if (!is_user_logged_in()) {
    wp_die('Acceso no autorizado');
}

// Obtener la URL base del plugin
$plugin_url = plugins_url('plg-genesis', dirname(dirname(dirname(__FILE__))));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estadísticas de Congresos</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <!-- Custom styles -->
    <link href="<?php echo $plugin_url; ?>/assets/css/styles.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Estadísticas de Congresos</h1>
        
        <!-- Filtros -->
        <div class="filtros-container">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary active" data-estado="todos">
                    <i class="fas fa-th-large mr-1"></i> Todos
                </button>
                <button type="button" class="btn btn-outline-primary" data-estado="PLANEACION">
                    <i class="fas fa-tasks mr-1"></i> En Planeación
                </button>
                <button type="button" class="btn btn-outline-primary" data-estado="REGISTRO">
                    <i class="fas fa-user-plus mr-1"></i> Registro Abierto
                </button>
                <button type="button" class="btn btn-outline-primary" data-estado="EN_CURSO">
                    <i class="fas fa-play-circle mr-1"></i> En Curso
                </button>
                <button type="button" class="btn btn-outline-primary" data-estado="FINALIZADO">
                    <i class="fas fa-check-circle mr-1"></i> Finalizados
                </button>
                <button type="button" class="btn btn-outline-primary" data-estado="CANCELADO">
                    <i class="fas fa-times-circle mr-1"></i> Cancelados
                </button>
            </div>
        </div>
    </div>
    
    <div class="row" id="congresos-container">
        <!-- Las tarjetas se cargarán aquí dinámicamente -->
    </div>
</div>

<!-- Modal para cambiar estado -->
<div class="modal fade" id="cambiarEstadoModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cambiar Estado del Congreso</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formCambiarEstado">
                    <input type="hidden" id="idCongresoEstado" name="id_congreso">
                    <div class="form-group">
                        <label for="nuevoEstado" class="font-weight-bold">Nuevo Estado</label>
                        <select id="nuevoEstado" name="estado" class="form-control form-control-lg" required>
                            <option value="PLANEACION">En Planeación</option>
                            <option value="REGISTRO">Registro Abierto</option>
                            <option value="EN_CURSO">En Curso</option>
                            <option value="FINALIZADO">Finalizado</option>
                            <option value="CANCELADO">Cancelado</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarEstado">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para detalles -->
<div class="modal fade" id="detalleContactosModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle por Contacto</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="tablaDetalleContactos" width="100%">
                        <thead>
                            <tr>
                                <th>Contacto</th>
                                <th>Estudiantes</th>
                                <th>Asistentes Externos</th>
                                <th>Total Estudiantes Inscritos</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- jQuery and Bootstrap Bundle (includes Popper) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    cargarCongresos();
    
    // Inicializar DataTable
    tablaDetalles = $('#tablaDetalleContactos').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
        },
        order: [[1, 'desc']], // Ordenar por cantidad de estudiantes
        pageLength: 10,
        responsive: true
    });

    // Manejar clics en los botones de filtro
    document.querySelectorAll('.btn-group .btn').forEach(btn => {
        btn.addEventListener('click', function() {
            // Remover clase active de todos los botones
            document.querySelectorAll('.btn-group .btn').forEach(b => b.classList.remove('active'));
            // Agregar clase active al botón clickeado
            this.classList.add('active');
            // Filtrar congresos
            filtrarCongresosPorEstado(this.dataset.estado);
        });
    });

    // Manejar cambio de estado
    document.getElementById('btnGuardarEstado').addEventListener('click', function() {
        const idCongreso = document.getElementById('idCongresoEstado').value;
        const nuevoEstado = document.getElementById('nuevoEstado').value;
        
        fetch('../../backend/congresos/actualizar_estado.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                id_congreso: idCongreso,
                estado: nuevoEstado
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Actualizar el estado en la lista de congresos
                const congresoIndex = congresosActuales.findIndex(c => c.id_congreso == idCongreso);
                if (congresoIndex !== -1) {
                    congresosActuales[congresoIndex].estado = nuevoEstado;
                }
                
                // Recargar la vista
                const estadoActual = document.querySelector('.btn-group .btn.active').dataset.estado;
                filtrarCongresosPorEstado(estadoActual);
                
                // Cerrar modal
                $('#cambiarEstadoModal').modal('hide');
                
                // Mostrar mensaje de éxito
                alert('Estado actualizado correctamente');
            } else {
                alert('Error al actualizar el estado: ' + (data.error || 'Error desconocido'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al actualizar el estado');
        });
    });
});

let congresosActuales = []; // Variable para almacenar todos los congresos

function cargarCongresos() {
    fetch('../../backend/congresos/obtener_congresos.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                congresosActuales = data.congresos; // Guardar todos los congresos
                mostrarCongresos(data.congresos);
            } else {
                console.error('Error al cargar los congresos');
                document.getElementById('congresos-container').innerHTML = 
                    '<div class="col-12"><div class="alert alert-danger">Error al cargar los congresos</div></div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('congresos-container').innerHTML = 
                '<div class="col-12"><div class="alert alert-danger">Error al cargar los congresos</div></div>';
        });
}

// Función para filtrar congresos por estado
function filtrarCongresosPorEstado(estado) {
    if (estado === 'todos') {
        mostrarCongresos(congresosActuales);
    } else {
        const congresosFiltrados = congresosActuales.filter(congreso => congreso.estado === estado);
        mostrarCongresos(congresosFiltrados);
    }
}

function mostrarCongresos(congresos) {
    const container = document.getElementById('congresos-container');
    container.innerHTML = '';

    congresos.forEach(congreso => {
        const estadoClass = getEstadoClass(congreso.estado);
        const card = `
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100">
                    <div class="card-body p-3">
                        <div class="d-flex flex-column h-100">
                            <div class="congreso-header mb-3">
                                <h5 class="text-primary mb-1 congreso-titulo">
                                    ${congreso.nombre_congreso}
                                </h5>
                                <div class="text-muted small">
                                    <i class="fas fa-calendar-alt mr-1"></i>
                                    ${formatearFecha(congreso.fecha_congreso)}
                                </div>
                                <div class="estado-badge ${estadoClass} mt-2">
                                    <i class="fas ${getEstadoIcon(congreso.estado)} mr-1"></i>
                                    ${formatearEstado(congreso.estado)}
                                </div>
                            </div>
                            <div class="stats-grid mb-3">
                                <div class="stat-item">
                                    <span class="stat-label">Total</span>
                                    <span class="stat-value">${congreso.total_asistentes}</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-label">Estudiantes</span>
                                    <span class="stat-value">${congreso.total_estudiantes}</span>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-label">Externos</span>
                                    <span class="stat-value">${congreso.total_externos}</span>
                                </div>
                            </div>
                            <div class="mt-auto">
                                <button class="btn btn-primary btn-sm btn-block" 
                                        onclick='verDetalleContactos(${JSON.stringify(congreso.detalle_contacto)}, "${congreso.nombre_congreso}")'>
                                    <i class="fas fa-list-ul mr-1"></i> Ver Detalle
                                </button>
                                <button class="btn btn-info btn-sm btn-block mt-2" 
                                        onclick='verInscritos(${congreso.id_congreso}, "${congreso.nombre_congreso}")'>
                                    <i class="fas fa-users mr-1"></i> Ver Inscritos
                                </button>
                                <button class="btn btn-warning btn-sm btn-block mt-2" 
                                        onclick='cambiarEstado(${congreso.id_congreso}, "${congreso.estado}")'>
                                    <i class="fas fa-exchange-alt mr-1"></i> Cambiar Estado
                                </button>
                                ${congreso.estado === 'REGISTRO' || congreso.estado === 'EN_CURSO' ? `
                                    <div class="row no-gutters mt-2">
                                        <div class="col-4 pr-1">
                                            <button class="btn btn-success btn-lg btn-block font-weight-bold shadow-sm" 
                                                    style="font-size:1rem;" 
                                                    onclick='registrarAsistencia(${congreso.id_congreso}, "${congreso.nombre_congreso}", "llegada")'>
                                                <i class="fas fa-sign-in-alt mr-1"></i> Llegada
                                            </button>
                                        </div>
                                        <div class="col-4 px-1">
                                            <button class="btn btn-warning btn-lg btn-block font-weight-bold shadow-sm" 
                                                    style="font-size:1rem;" 
                                                    onclick='registrarAsistencia(${congreso.id_congreso}, "${congreso.nombre_congreso}", "almuerzo")'>
                                                <i class="fas fa-utensils mr-1"></i> Almuerzo
                                            </button>
                                        </div>
                                        <div class="col-4 pl-1">
                                            <button class="btn btn-danger btn-lg btn-block font-weight-bold shadow-sm" 
                                                    style="font-size:1rem;" 
                                                    onclick='anularBoleta(${congreso.id_congreso}, "${congreso.nombre_congreso}")'>
                                                <i class="fas fa-exchange-alt mr-1"></i> Anular
                                            </button>
                                        </div>
                                    </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        container.innerHTML += card;
    });
}

function getEstadoClass(estado) {
    const clases = {
        'PLANEACION': 'estado-planeacion',
        'REGISTRO': 'estado-registro',
        'EN_CURSO': 'estado-en-curso',
        'FINALIZADO': 'estado-finalizado',
        'CANCELADO': 'estado-cancelado'
    };
    return clases[estado] || 'estado-planeacion';
}

function getEstadoIcon(estado) {
    const iconos = {
        'PLANEACION': 'fa-tasks',
        'REGISTRO': 'fa-user-plus',
        'EN_CURSO': 'fa-play-circle',
        'FINALIZADO': 'fa-check-circle',
        'CANCELADO': 'fa-times-circle'
    };
    return iconos[estado] || 'fa-tasks';
}

function formatearEstado(estado) {
    const estados = {
        'PLANEACION': 'En Planeación',
        'REGISTRO': 'Registro Abierto',
        'EN_CURSO': 'En Curso',
        'FINALIZADO': 'Finalizado',
        'CANCELADO': 'Cancelado'
    };
    return estados[estado] || estado;
}

function formatearFecha(fecha) {
    return new Date(fecha).toLocaleDateString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

let tablaDetalles;

function verDetalleContactos(detalles, nombreCongreso) {
    if (tablaDetalles) {
        tablaDetalles.clear();
        
        detalles.forEach(detalle => {
            tablaDetalles.row.add([
                detalle.nombre_contacto,
                detalle.estudiantes,
                detalle.asistentes_externos,
                detalle.estudiantes_inscritos
            ]);
        });
        
        tablaDetalles.draw();
        $('.modal-title').text(`Detalle por Contacto - ${nombreCongreso}`);
        $('#detalleContactosModal').modal('show');
    }
}

function verInscritos(idCongreso, nombreCongreso) {
    window.location.href = `detalle_inscritos.php?id=${idCongreso}`;
}

// Función para abrir el modal de cambio de estado
function cambiarEstado(idCongreso, estadoActual) {
    document.getElementById('idCongresoEstado').value = idCongreso;
    document.getElementById('nuevoEstado').value = estadoActual;
    $('#cambiarEstadoModal').modal('show');
}

// Agregar la función de registro de asistencia
function registrarAsistencia(idCongreso, nombreCongreso, tipo) {
    window.location.href = `registrar-asistencia.php?id_congreso=${idCongreso}&nombre=${encodeURIComponent(nombreCongreso)}&tipo=${tipo}`;
}

function anularBoleta(idCongreso, nombreCongreso) {
    window.location.href = `administrar_asistencia.php?id_congreso=${idCongreso}&nombre=${encodeURIComponent(nombreCongreso)}`;
}
</script>

<style>
.card {
    transition: transform .2s;
    border: none;
    height: 100%;
}

.card:hover {
    transform: translateY(-5px);
}

.border-left-primary {
    border-left: 4px solid var(--primary-color) !important;
}

.text-primary {
    color: var(--primary-color) !important;
}

.card-body {
    padding: 1rem;
    display: flex;
    flex-direction: column;
}

.congreso-header {
    border-bottom: 1px solid rgba(0,0,0,0.05);
    padding-bottom: 0.75rem;
}

.congreso-titulo {
    font-size: 1.1rem;
    font-weight: 600;
    line-height: 1.2;
    margin-bottom: 0.5rem;
    white-space: normal;
    word-wrap: break-word;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0.5rem;
}

.stat-item {
    text-align: center;
    background-color: rgba(0,0,0,0.03);
    padding: 0.5rem;
    border-radius: 4px;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.stat-label {
    display: block;
    font-size: 0.7rem;
    color: #6c757d;
    margin-bottom: 0.25rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-value {
    display: block;
    font-weight: 600;
    font-size: 1.1rem;
    color: var(--primary-color);
}

.btn-primary {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
    padding: 0.5rem 1rem;
    font-size: 0.875rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-primary:hover {
    background-color: var(--primary-color-dark);
    border-color: var(--primary-color-dark);
    transform: translateY(-1px);
}

.text-muted {
    color: #6c757d !important;
}

.small {
    font-size: 0.875rem;
}

.modal-lg {
    max-width: 90%;
}

@media (max-width: 768px) {
    .modal-lg {
        max-width: 95%;
    }
}

.table-responsive {
    padding: 1rem;
}

.dataTables_wrapper {
    padding: 1rem;
}

/* Estilos para los estados */
.estado-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.estado-planeacion {
    background-color: #e3f2fd;
    color: #1976d2;
}

.estado-registro {
    background-color: #e8f5e9;
    color: #2e7d32;
}

.estado-en-curso {
    background-color: #fff3e0;
    color: #f57c00;
}

.estado-finalizado {
    background-color: #f5f5f5;
    color: #616161;
}

.estado-cancelado {
    background-color: #ffebee;
    color: #c62828;
}

/* Estilos para los filtros */
.filtros-container {
    margin-bottom: 1rem;
}

.btn-group .btn {
    border-radius: 0.25rem;
    margin-right: 0.25rem;
    font-size: 0.875rem;
    padding: 0.5rem 1rem;
}

.btn-group .btn.active {
    background-color: var(--primary-color);
    color: white;
}

/* Estilos para el modal de cambio de estado */
.modal-content {
    border-radius: 0.5rem;
    border: none;
}

.modal-header {
    background-color: var(--primary-color);
    color: white;
    border-radius: 0.5rem 0.5rem 0 0;
}

.modal-footer {
    border-top: 1px solid rgba(0,0,0,0.05);
}
</style>

<!-- Agregar Font Awesome para los iconos -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</body>
</html>
