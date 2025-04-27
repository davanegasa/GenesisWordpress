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
    <title>Detalle de Inscritos - Congresos</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <!-- Custom styles -->
    <link href="<?php echo $plugin_url; ?>/assets/css/styles.css" rel="stylesheet">
</head>
<body>
<div class="container-fluid mt-4">
    <h1 class="h3 mb-2 text-gray-800">Detalle de Inscritos</h1>
    
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">Lista de Inscritos</h6>
            <button id="btnDescargarExcel" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Descargar Excel
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="tablaInscritos" width="100%">
                    <thead>
                        <tr>
                            <th>Número Boleta</th>
                            <th>Nombre</th>
                            <th>Cédula</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Congregación</th>
                            <th>Taller</th>
                            <th>Fecha Inscripción</th>
                            <th>Tipo</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
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
    // Obtener el ID del congreso de la URL
    const urlParams = new URLSearchParams(window.location.search);
    const idCongreso = urlParams.get('id');

    if (!idCongreso) {
        alert('No se especificó un congreso');
        window.location.href = 'busqueda_congresos.php';
        return;
    }

    // Inicializar DataTable
    const tabla = $('#tablaInscritos').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
        },
        processing: true,
        serverSide: true,
        ajax: {
            url: '../../backend/congresos/obtener_inscritos.php',
            type: 'POST',
            data: function(d) {
                d.id_congreso = idCongreso;
            }
        },
        columns: [
            { data: 'numero_boleta' },
            { data: 'nombre' },
            { data: 'identificacion' },
            { data: 'email' },
            { data: 'telefono' },
            { data: 'congregacion' },
            { data: 'taller' },
            { 
                data: 'fecha_inscripcion',
                render: function(data) {
                    return data ? new Date(data).toLocaleString('es-ES') : '';
                }
            },
            { 
                data: 'tipo',
                render: function(data) {
                    return data === 'estudiante' ? 'Estudiante' : 'Asistente Externo';
                }
            }
        ],
        order: [[7, 'desc'], [1, 'asc']] // Ordenar por fecha de inscripción descendente y luego por nombre
    });

    // Manejar la descarga de Excel
    document.getElementById('btnDescargarExcel').addEventListener('click', function() {
        window.location.href = `<?php echo $plugin_url; ?>/backend/congresos/descargar_inscritos_excel.php?id_congreso=${idCongreso}`;
    });
});
</script>

<style>
.card {
    margin-bottom: 2rem;
}

.table th {
    background-color: #f8f9fc;
    font-weight: 600;
}

.dataTables_wrapper .dataTables_processing {
    background: rgba(255,255,255,0.9);
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 1rem;
}
</style>

<!-- Agregar Font Awesome para los iconos -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</body>
</html> 