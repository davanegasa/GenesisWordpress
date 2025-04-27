<?php
require_once(__DIR__ . '/../../../../../wp-load.php');

// Verificar si el usuario está autenticado
if (!is_user_logged_in()) {
    wp_die('Acceso no autorizado');
}

// Obtener parámetros de la URL
$id_congreso = isset($_GET['id_congreso']) ? intval($_GET['id_congreso']) : 0;
$nombre_congreso = isset($_GET['nombre']) ? sanitize_text_field($_GET['nombre']) : '';
$tipo_registro = isset($_GET['tipo']) && in_array($_GET['tipo'], ['llegada', 'almuerzo']) ? $_GET['tipo'] : 'llegada';

if ($id_congreso <= 0) {
    wp_die('ID de congreso no válido');
}

$plugin_url = plugins_url('plg-genesis', dirname(dirname(dirname(__FILE__))));

function getTipoRegistroLabel($tipo) {
    return $tipo === 'almuerzo' ? 'Almuerzo' : 'Llegada';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar <?php echo getTipoRegistroLabel($tipo_registro); ?> - <?php echo esc_html($nombre_congreso); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="<?php echo $plugin_url; ?>/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-light">
    <input type="hidden" id="id_congreso" value="<?php echo esc_attr($id_congreso); ?>">
    <input type="hidden" id="tipo_registro" value="<?php echo esc_attr($tipo_registro); ?>">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-7 col-md-7 col-12">
                <!-- Formulario de registro -->
                <div class="card mb-4 h-100">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-clipboard-check mr-2"></i>
                            Registrar <?php echo getTipoRegistroLabel($tipo_registro); ?> - <?php echo esc_html($nombre_congreso); ?>
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <div class="card mb-4 mx-auto" style="max-width: 400px;">
                            <div class="card-body p-3">
                                <ul class="nav nav-tabs mb-3" id="modoBusquedaTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link active" id="tab-barcode" data-toggle="tab" href="#barcode" role="tab" aria-controls="barcode" aria-selected="true">
                                            <i class="fas fa-barcode mr-1"></i> Código de Barras
                                        </a>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <a class="nav-link" id="tab-manual" data-toggle="tab" href="#manual" role="tab" aria-controls="manual" aria-selected="false">
                                            <i class="fas fa-keyboard mr-1"></i> Manual
                                        </a>
                                    </li>
                                </ul>
                                <div class="tab-content" id="modoBusquedaTabsContent">
                                    <div class="tab-pane fade show active" id="barcode" role="tabpanel" aria-labelledby="tab-barcode">
                                        <form id="buscar-barcode-form" autocomplete="off">
                                            <div class="form-group mb-2">
                                                <label for="barcode_input" class="font-weight-bold">
                                                    <i class="fas fa-barcode text-primary mr-2"></i>
                                                    Código de Barras (7 dígitos)
                                                </label>
                                                <input type="text" id="barcode_input" maxlength="7" class="form-control form-control-lg text-center" placeholder="Ej: 0054731" autofocus required>
                                                <div class="invalid-feedback">Ingrese el código de barras (7 dígitos)</div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="tab-pane fade" id="manual" role="tabpanel" aria-labelledby="tab-manual">
                                        <form id="buscar-boleta-form" class="needs-validation" novalidate autocomplete="off">
                                            <div class="form-group mb-2">
                                                <label for="numero_boleta" class="font-weight-bold mb-1">
                                                    <i class="fas fa-ticket-alt text-primary mr-2"></i>
                                                    Número de Boleta
                                                </label>
                                                <input type="text" id="numero_boleta" name="numero_boleta" maxlength="3" class="form-control form-control-lg text-center" placeholder="Ej: 005" required>
                                                <div class="invalid-feedback">Ingrese el número de boleta (3 dígitos)</div>
                                            </div>
                                            <div class="form-group mb-2">
                                                <label for="codigo_verificacion" class="font-weight-bold mb-1">
                                                    <i class="fas fa-key text-primary mr-2"></i>
                                                    Código de Verificación
                                                </label>
                                                <input type="text" id="codigo_verificacion" name="codigo_verificacion" maxlength="4" class="form-control form-control-lg text-center" placeholder="Ej: 4731" required>
                                                <div class="invalid-feedback">Ingrese el código de verificación (4 dígitos)</div>
                                            </div>
                                            <div class="form-group mt-3 mb-0">
                                                <button type="submit" class="btn btn-info btn-block font-weight-bold">
                                                    <i class="fas fa-search mr-1"></i> Buscar Participante
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="datos-participante" style="display:none;">
                            <hr>
                            <h5 class="mb-3"><i class="fas fa-user mr-2"></i>Datos del Participante</h5>
                            <ul class="list-group mb-3">
                                <li class="list-group-item"><strong>Nombre:</strong> <span id="nombre-participante"></span></li>
                                <li class="list-group-item"><strong>Email:</strong> <span id="email-participante"></span></li>
                                <li class="list-group-item"><strong>Celular:</strong> <span id="celular-participante"></span></li>
                                <li class="list-group-item"><strong>Tipo de Asistente:</strong> <span id="tipo-participante" class="font-weight-bold"></span></li>
                            </ul>
                        </div>
                        <div id="resultado-registro" class="alert mt-3" style="display: none;"></div>
                        <div class="mt-4 text-center">
                            <a href="busqueda_congresos.php" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left mr-2"></i>
                                Volver a la lista de congresos
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-5 col-md-5 col-12 d-flex align-items-stretch">
                <!-- Estadísticas -->
                <div class="card w-100 mb-4 h-100">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-chart-bar mr-2"></i>Estadísticas del Congreso</h5>
                    </div>
                    <div class="card-body" id="estadisticas-congreso">
                        <div class="text-center text-muted">Cargando estadísticas...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    let participanteData = null;
    jQuery(document).ready(function($) {
        // Tabs de modo de búsqueda
        $('#modoBusquedaTabs a').on('click', function (e) {
            e.preventDefault();
            $(this).tab('show');
            $('#resultado-registro').hide();
            $('#datos-participante').hide();
            participanteData = null;
            $('#barcode_input').val('');
            $('#numero_boleta').val('');
            $('#codigo_verificacion').val('');
        });

        // Buscar por código de barras
        $('#barcode_input').on('input', function() {
            const val = $(this).val();
            if (val.length === 7) {
                const numero_boleta = val.substring(0,3);
                const codigo_verificacion = val.substring(3,7);
                buscarYRegistrar(numero_boleta, codigo_verificacion);
            }
        });

        // Buscar por formulario manual
        $('#buscar-boleta-form').on('submit', function(e) {
            e.preventDefault();
            const numero_boleta = $('#numero_boleta').val();
            const codigo_verificacion = $('#codigo_verificacion').val();
            buscarYRegistrar(numero_boleta, codigo_verificacion);
        });

        function mostrarResultadoRegistro(response) {
            const resultado = $('#resultado-registro');
            resultado.show();
            if (response.success) {
                resultado.removeClass('alert-danger').addClass('alert-success');
                let msg = '<i class="fas fa-check-circle mr-2"></i>Asistencia registrada correctamente.';
                if (response.fecha_registro) {
                    msg += ' <strong>Fecha:</strong> ' + response.fecha_registro;
                }
                resultado.html(msg);
            } else {
                resultado.removeClass('alert-success').addClass('alert-danger');
                let msg = '<i class="fas fa-exclamation-circle mr-2"></i>Error: ' + response.error;
                if (response.fecha_registro) {
                    msg += '<br><strong>Fecha registrada:</strong> ' + response.fecha_registro;
                }
                resultado.html(msg);
            }
            // Mostrar datos del participante si existen
            if (response.participante) {
                $('#nombre-participante').text(response.participante.nombre);
                $('#email-participante').text(response.participante.email);
                $('#celular-participante').text(response.participante.celular);
                $('#tipo-participante').text(response.participante.tipo);
                $('#datos-participante').show();
            } else {
                $('#datos-participante').hide();
            }
            $('#buscar-barcode-form')[0].reset();
            $('#buscar-boleta-form')[0].reset();
            participanteData = null;
        }

        function buscarYRegistrar(numero_boleta, codigo_verificacion) {
            const id_congreso = $('#id_congreso').val();
            const tipo_registro = $('#tipo_registro').val();
            if (!numero_boleta || !codigo_verificacion || !id_congreso) return;
            $.ajax({
                url: '../../backend/congresos/registrar_asistencia.php',
                type: 'POST',
                data: JSON.stringify({ numero_boleta, codigo_verificacion, tipo_registro, id_congreso }),
                contentType: 'application/json',
                success: function(response) {
                    mostrarResultadoRegistro(response);
                },
                error: function(xhr, status, error) {
                    const resultado = $('#resultado-registro');
                    resultado.removeClass('alert-success').addClass('alert-danger');
                    resultado.html('<i class="fas fa-exclamation-circle mr-2"></i>Error al procesar la solicitud: ' + error);
                    resultado.show();
                }
            });
        }

        // Obtener estadísticas al cargar la página
        function cargarEstadisticasCongreso() {
            const id_congreso = $('#id_congreso').val();
            $.ajax({
                url: '../../backend/congresos/estadisticas_congreso.php',
                type: 'POST',
                data: JSON.stringify({ id_congreso }),
                contentType: 'application/json',
                success: function(response) {
                    if (response.success) {
                        const inscritos = response.total_inscritos;
                        const llegadas = response.total_llegadas;
                        const almuerzos = response.total_almuerzos;
                        const pct_llegadas = inscritos > 0 ? ((llegadas / inscritos) * 100).toFixed(1) : '0.0';
                        const pct_almuerzos = inscritos > 0 ? ((almuerzos / inscritos) * 100).toFixed(1) : '0.0';
                        $('#estadisticas-congreso').html(`
                            <div class='d-flex flex-column align-items-center justify-content-center h-100'>
                                <div class='mb-4 w-100 text-center'>
                                    <div class='h2 text-primary font-weight-bold'>${inscritos}</div>
                                    <div>Total Inscritos</div>
                                </div>
                                <div class='mb-4 w-100 text-center'>
                                    <div class='h2 text-success font-weight-bold'>${llegadas}</div>
                                    <div>Llegadas</div>
                                    <div class='small text-muted'>${pct_llegadas}%</div>
                                </div>
                                <div class='w-100 text-center'>
                                    <div class='h2 text-warning font-weight-bold'>${almuerzos}</div>
                                    <div>Almuerzos</div>
                                    <div class='small text-muted'>${pct_almuerzos}%</div>
                                </div>
                            </div>
                        `);
                    } else {
                        $('#estadisticas-congreso').html('<div class="text-danger">No se pudieron cargar las estadísticas.</div>');
                    }
                },
                error: function() {
                    $('#estadisticas-congreso').html('<div class="text-danger">Error al cargar estadísticas.</div>');
                }
            });
        }
        $(document).ready(function() {
            cargarEstadisticasCongreso();
        });
    });
    </script>
    <style>
    @media (min-width: 992px) {
        .container .row > .col-lg-7 { flex: 0 0 60%; max-width: 60%; }
        .container .row > .col-lg-5 { flex: 0 0 40%; max-width: 40%; }
    }
    </style>
</body>
</html> 