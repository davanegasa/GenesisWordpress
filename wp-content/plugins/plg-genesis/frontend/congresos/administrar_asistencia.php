<?php
require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

// Verificar autenticación
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());
    exit;
}

// Obtener parámetros
$id_congreso = isset($_GET['id_congreso']) ? intval($_GET['id_congreso']) : 4;
$nombre_congreso = isset($_GET['nombre']) ? sanitize_text_field($_GET['nombre']) : '';

if (!$id_congreso) {
    wp_die('ID de congreso no válido');
}

// Obtener información del congreso
$query = "SELECT * FROM congresos WHERE id = $1";
$result = pg_query_params($conexion, $query, [$id_congreso]);
$congreso = pg_fetch_assoc($result);

if (!$congreso) {
    wp_die('Congreso no encontrado');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Asistencia - <?php echo esc_html($nombre_congreso); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .scanner-container {
            position: relative;
            margin: 20px 0;
        }
        .scanner-input {
            font-size: 24px;
            padding: 15px;
            width: 100%;
            text-align: center;
            letter-spacing: 2px;
        }
        .resultado-container {
            margin-top: 20px;
            padding: 20px;
            border-radius: 8px;
            background-color: #f8f9fa;
        }
        .resultado-container.exito {
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .resultado-container.error {
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .ultimos-registros {
            margin-top: 30px;
        }
        .table-responsive {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>
                        <i class="fas fa-clipboard-check"></i>
                        Administrar Asistencia - <?php echo esc_html($nombre_congreso); ?>
                    </h1>
                    <div>
                        <a href="no_asistentes.php?id_congreso=<?php echo $id_congreso; ?>&nombre=<?php echo urlencode($nombre_congreso); ?>" 
                           class="btn btn-danger">
                            <i class="fas fa-user-times mr-2"></i>
                            Ver No Asistentes
                            <span id="contador-no-asistentes" class="badge bg-white text-danger ml-2">0</span>
                        </a>
                        <a href="busqueda_congresos.php" class="btn btn-outline-secondary ml-2">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Volver
                        </a>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-body">
                        <div class="scanner-container">
                            <input type="text" 
                                   id="scannerInput" 
                                   class="form-control scanner-input" 
                                   placeholder="Escanear código de boleta..."
                                   autofocus>
                        </div>
                        
                        <div id="resultadoContainer" class="resultado-container" style="display: none;">
                            <h4 id="resultadoTitulo"></h4>
                            <div id="resultadoDetalles"></div>
                            <div id="accionesBoleta" class="mt-3" style="display: none;">
                                <button id="btnAnularBoleta" class="btn btn-danger">
                                    <i class="fas fa-ban"></i> Anular Boleta
                                </button>
                            </div>
                            <div id="modalReemplazo" class="modal fade" tabindex="-1" role="dialog">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Anular Boleta</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label for="boletaReemplazo">Número de Boleta de Reemplazo</label>
                                                <input type="text" class="form-control" id="boletaReemplazo" placeholder="Ej: 001">
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                            <button type="button" class="btn btn-danger" id="btnConfirmarAnulacion">Anular Boleta</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="ultimos-registros">
                    <h3>Últimos Registros</h3>
                    <div class="table-responsive">
                        <table class="table table-striped" id="tablaRegistros">
                            <thead>
                                <tr>
                                    <th>Hora</th>
                                    <th>Boleta</th>
                                    <th>Nombre</th>
                                    <th>Taller</th>
                                    <th>Estado</th>
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let ultimoCodigo = '';
        let timeoutId;

        document.getElementById('scannerInput').addEventListener('input', function(e) {
            clearTimeout(timeoutId);
            
            // Si el input está vacío, no hacer nada
            if (!this.value) return;
            
            const codigo = this.value.trim();
            
            // Solo procesar si el código tiene exactamente 7 caracteres
            if (codigo.length === 7) {
                // Evitar procesar el mismo código dos veces
                if (codigo === ultimoCodigo) return;
                ultimoCodigo = codigo;
                
                // Procesar el código
                procesarCodigo(codigo);
                
                // Limpiar el input
                this.value = '';
            } else if (codigo.length > 7) {
                // Si excede los 7 caracteres, limpiar el input
                this.value = '';
                mostrarError('El código debe tener exactamente 7 caracteres');
            }
        });

        function procesarCodigo(codigo) {
            // Extraer número de boleta (3 dígitos) y código de verificación (4 dígitos)
            const numeroBoleta = codigo.substring(0, 3);
            const codigoVerificacion = codigo.substring(3);
            
            // Validar formato
            if (!/^\d{3}$/.test(numeroBoleta) || !/^\d{4}$/.test(codigoVerificacion)) {
                mostrarError('Formato de código inválido. Debe ser: 000-0000');
                return;
            }
            
            // Validar la boleta
            fetch('../../backend/congresos/validar_boleta.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    numero_boleta: numeroBoleta,
                    codigo_verificacion: codigoVerificacion,
                    id_congreso: <?php echo $id_congreso; ?>
                })
            })
            .then(response => response.json())
            .then(data => {
                mostrarResultado(data);
                if (data.success) {
                    actualizarTablaRegistros();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarError('Error al procesar la boleta');
            });
        }

        function mostrarResultado(data) {
            const container = document.getElementById('resultadoContainer');
            const titulo = document.getElementById('resultadoTitulo');
            const detalles = document.getElementById('resultadoDetalles');
            const acciones = document.getElementById('accionesBoleta');
            
            container.style.display = 'block';
            
            if (data.error) {
                container.className = 'resultado-container error';
                titulo.innerHTML = '<i class="fas fa-times-circle"></i> Error';
                detalles.innerHTML = `<p class="text-danger">${data.error}</p>`;
                acciones.style.display = 'none';
            } else {
                container.className = 'resultado-container exito';
                titulo.innerHTML = '<i class="fas fa-check-circle"></i> Boleta Válida';
                
                let html = `
                    <p><strong>Nombre:</strong> ${data.datos.nombre}</p>
                    <p><strong>Email:</strong> ${data.datos.email}</p>
                    <p><strong>Teléfono:</strong> ${data.datos.telefono || data.datos.celular}</p>
                    <p><strong>Taller:</strong> ${data.datos.taller_asignado || 'No asignado'}</p>
                `;
                
                detalles.innerHTML = html;
                
                // Mostrar botón de anulación solo si la boleta está en uso
                if (data.estado === 'usado') {
                    acciones.style.display = 'block';
                } else {
                    acciones.style.display = 'none';
                }
            }
            
            // Ocultar el resultado después de 5 segundos
            setTimeout(() => {
                container.style.display = 'none';
                acciones.style.display = 'none';
            }, 5000);
        }

        function mostrarError(mensaje) {
            const container = document.getElementById('resultadoContainer');
            const titulo = document.getElementById('resultadoTitulo');
            const detalles = document.getElementById('resultadoDetalles');
            
            container.style.display = 'block';
            container.className = 'resultado-container error';
            titulo.innerHTML = '<i class="fas fa-times-circle"></i> Error';
            detalles.innerHTML = `<p class="text-danger">${mensaje}</p>`;
            
            setTimeout(() => {
                container.style.display = 'none';
            }, 5000);
        }

        function actualizarTablaRegistros() {
            fetch(`../../backend/congresos/obtener_ultimos_registros.php?id_congreso=<?php echo $id_congreso; ?>`)
                .then(response => response.json())
                .then(data => {
                    const tbody = document.querySelector('#tablaRegistros tbody');
                    tbody.innerHTML = '';
                    
                    data.forEach(registro => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td>${new Date(registro.fecha_registro).toLocaleTimeString()}</td>
                            <td>${registro.numero_boleta}</td>
                            <td>${registro.nombre}</td>
                            <td>${registro.taller || 'No asignado'}</td>
                            <td>
                                <span class="badge bg-${registro.estado === 'usado' ? 'success' : 'warning'}">
                                    ${registro.estado === 'usado' ? 'Registrado' : 'Pendiente'}
                                </span>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });
                })
                .catch(error => console.error('Error al actualizar registros:', error));
        }

        // Actualizar la tabla cada 30 segundos
        setInterval(actualizarTablaRegistros, 30000);
        
        // Cargar registros iniciales
        actualizarTablaRegistros();

        // Manejar clic en botón de anulación
        document.getElementById('btnAnularBoleta').addEventListener('click', function() {
            $('#modalReemplazo').modal('show');
        });

        document.getElementById('btnConfirmarAnulacion').addEventListener('click', function() {
            const boletaReemplazo = document.getElementById('boletaReemplazo').value.trim();
            if (!boletaReemplazo) {
                alert('Por favor ingrese el número de la boleta de reemplazo');
                return;
            }

            const numeroBoleta = ultimoCodigo.substring(0, 3);
            const codigoVerificacion = ultimoCodigo.substring(3);
            
            fetch('../../backend/congresos/anular_boleta.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    numero_boleta: numeroBoleta,
                    codigo_verificacion: codigoVerificacion,
                    id_congreso: <?php echo $id_congreso; ?>,
                    boleta_reemplazo: boletaReemplazo
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(`Boleta anulada exitosamente.\nNueva boleta: ${data.nueva_boleta.numero}-${data.nueva_boleta.codigo}`);
                    $('#modalReemplazo').modal('hide');
                    document.getElementById('boletaReemplazo').value = '';
                    actualizarTablaRegistros();
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error al procesar la solicitud');
            });
        });

        // Función para actualizar el contador de no asistentes
        function actualizarContadorNoAsistentes() {
            $.ajax({
                url: '../../backend/congresos/obtener_no_asistentes.php',
                type: 'POST',
                data: JSON.stringify({ id_congreso: <?php echo $id_congreso; ?> }),
                contentType: 'application/json',
                success: function(response) {
                    if (response.success) {
                        $('#contador-no-asistentes').text(response.total);
                    }
                }
            });
        }

        // Actualizar el contador cada 5 minutos
        $(document).ready(function() {
            actualizarContadorNoAsistentes();
            setInterval(actualizarContadorNoAsistentes, 300000);
        });
    </script>
</body>
</html> 