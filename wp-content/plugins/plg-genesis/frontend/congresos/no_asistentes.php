<?php
require_once(__DIR__ . '/../../../../../wp-load.php');

// Verificar si el usuario está autenticado
if (!is_user_logged_in()) {
    wp_die('Acceso no autorizado');
}

// Obtener parámetros de la URL
$id_congreso = isset($_GET['id_congreso']) ? intval($_GET['id_congreso']) : 4;
$nombre_congreso = isset($_GET['nombre']) ? sanitize_text_field($_GET['nombre']) : 'Congreso';

$plugin_url = plugins_url('plg-genesis', dirname(dirname(dirname(__FILE__))));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>No Asistentes - <?php echo esc_html($nombre_congreso); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col">
                <h2 class="mb-3">
                    <i class="fas fa-user-times text-danger mr-2"></i>
                    Participantes Sin Registrar Llegada
                </h2>
                <h5 class="text-muted"><?php echo esc_html($nombre_congreso); ?></h5>
            </div>
            <div class="col-auto">
                <a href="busqueda_congresos.php" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver a la lista de congresos
                </a>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table id="tabla-no-asistentes" class="table table-striped table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Teléfono</th>
                                <th>Congregación</th>
                                <th>Tipo</th>
                                <th>N° Boleta</th>
                                <th>Taller</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Los datos se cargarán dinámicamente -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>
    <script>
        jQuery(document).ready(function($) {
            // Inicializar DataTable
            const table = $('#tabla-no-asistentes').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json'
                },
                order: [[0, 'asc']],
                pageLength: 25,
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                     '<"row"<"col-sm-12"tr>>' +
                     '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                initComplete: function() {
                    cargarNoAsistentes();
                }
            });

            function cargarNoAsistentes() {
                $.ajax({
                    url: '../../backend/congresos/obtener_no_asistentes.php',
                    type: 'POST',
                    data: JSON.stringify({ id_congreso: <?php echo $id_congreso; ?> }),
                    contentType: 'application/json',
                    success: function(response) {
                        if (response.success) {
                            table.clear();
                            response.data.forEach(function(participante) {
                                table.row.add([
                                    participante.nombre,
                                    participante.email,
                                    participante.telefono,
                                    participante.congregacion,
                                    `<span class="badge badge-${participante.tipo === 'Estudiante' ? 'primary' : 'info'}">${participante.tipo}</span>`,
                                    participante.numero_boleta,
                                    participante.taller || 'No asignado'
                                ]);
                            });
                            table.draw();

                            // Actualizar contador en el título
                            $('h2').append(` <span class="badge badge-danger">${response.total}</span>`);
                        } else {
                            console.error('Error al cargar datos:', response.error);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error en la solicitud:', error);
                    }
                });
            }
        });
    </script>
</body>
</html> 