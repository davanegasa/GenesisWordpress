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
    <title>Crear Congreso</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <!-- Custom styles -->
    <link href="<?php echo $plugin_url; ?>/assets/css/styles.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-calendar-plus mr-2"></i>
                            Crear Nuevo Congreso
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <form id="formCrearCongreso" class="needs-validation" novalidate>
                            <!-- Nombre del Congreso -->
                            <div class="form-group">
                                <label for="nombreCongreso" class="font-weight-bold">
                                    <i class="fas fa-bookmark text-primary mr-2"></i>
                                    Nombre del Congreso
                                </label>
                                <input type="text" 
                                       id="nombreCongreso" 
                                       name="nombre"
                                       class="form-control form-control-lg" 
                                       placeholder="Ej: Congreso de Jóvenes 2024" 
                                       required>
                                <div class="invalid-feedback">
                                    Por favor ingresa el nombre del congreso
                                </div>
                            </div>

                            <!-- Fecha del Congreso -->
                            <div class="form-group">
                                <label for="fechaCongreso" class="font-weight-bold">
                                    <i class="fas fa-calendar-alt text-primary mr-2"></i>
                                    Fecha del Congreso
                                </label>
                                <input type="date" 
                                       id="fechaCongreso" 
                                       name="fecha"
                                       class="form-control form-control-lg" 
                                       required>
                                <div class="invalid-feedback">
                                    Por favor selecciona la fecha del congreso
                                </div>
                            </div>

                            <!-- Ubicación -->
                            <div class="form-group">
                                <label for="ubicacionCongreso" class="font-weight-bold">
                                    <i class="fas fa-map-marker-alt text-primary mr-2"></i>
                                    Ubicación
                                </label>
                                <input type="text" 
                                       id="ubicacionCongreso" 
                                       name="ubicacion"
                                       class="form-control form-control-lg" 
                                       placeholder="Ej: Auditorio Principal" 
                                       required>
                                <div class="invalid-feedback">
                                    Por favor ingresa la ubicación del congreso
                                </div>
                            </div>

                            <!-- Estado del Congreso -->
                            <div class="form-group">
                                <label for="estadoCongreso" class="font-weight-bold">
                                    <i class="fas fa-info-circle text-primary mr-2"></i>
                                    Estado del Congreso
                                </label>
                                <select id="estadoCongreso" 
                                        name="estado"
                                        class="form-control form-control-lg" 
                                        required
                                        disabled>
                                    <option value="PLANEACION" selected>En Planeación</option>
                                </select>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Todos los congresos nuevos comienzan en estado de planeación.
                                </small>
                            </div>

                            <!-- Botones -->
                            <div class="form-group mb-0 d-flex justify-content-between align-items-center">
                                <button type="button" 
                                        class="btn btn-outline-secondary btn-lg"
                                        onclick="window.history.back()">
                                    <i class="fas fa-arrow-left mr-2"></i>
                                    Volver
                                </button>
                                <button type="submit" 
                                        class="btn btn-primary btn-lg">
                                    <i class="fas fa-save mr-2"></i>
                                    Crear Congreso
                                </button>
                            </div>
                        </form>

                        <!-- Mensaje de respuesta -->
                        <div id="responseMessage" class="alert d-none mt-3"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery and Bootstrap Bundle (includes Popper) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Validación del formulario
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var form = document.getElementById('formCrearCongreso');
                form.addEventListener('submit', function(event) {
                    event.preventDefault();
                    event.stopPropagation();
                    
                    if (form.checkValidity()) {
                        enviarFormulario();
                    }
                    
                    form.classList.add('was-validated');
                }, false);
            });
        })();

        function enviarFormulario() {
            const formData = {
                nombre: document.getElementById('nombreCongreso').value,
                fecha: document.getElementById('fechaCongreso').value,
                ubicacion: document.getElementById('ubicacionCongreso').value,
                estado: document.getElementById('estadoCongreso').value
            };

            // Deshabilitar el botón de envío
            const submitButton = document.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Creando...';

            fetch('../../backend/congresos/crear_congreso.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                const messageDiv = document.getElementById('responseMessage');
                messageDiv.classList.remove('d-none', 'alert-success', 'alert-danger');
                
                if (data.success) {
                    messageDiv.classList.add('alert-success');
                    messageDiv.innerHTML = '<i class="fas fa-check-circle mr-2"></i>¡Congreso creado exitosamente!';
                    
                    // Limpiar formulario
                    document.getElementById('formCrearCongreso').reset();
                    document.getElementById('formCrearCongreso').classList.remove('was-validated');
                    
                    // Redirigir después de 2 segundos
                    setTimeout(() => {
                        window.location.href = 'busqueda_congresos.php';
                    }, 2000);
                } else {
                    messageDiv.classList.add('alert-danger');
                    messageDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i>Error: ' + (data.error || 'No se pudo crear el congreso');
                }
            })
            .catch(error => {
                const messageDiv = document.getElementById('responseMessage');
                messageDiv.classList.remove('d-none');
                messageDiv.classList.add('alert-danger');
                messageDiv.innerHTML = '<i class="fas fa-exclamation-circle mr-2"></i>Error en la solicitud';
                console.error('Error:', error);
            })
            .finally(() => {
                // Restaurar el botón de envío
                submitButton.disabled = false;
                submitButton.innerHTML = originalText;
            });
        }
    </script>

    <style>
        body {
            background-color: #f8f9fa;
        }

        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .card-header {
            border-radius: 1rem 1rem 0 0 !important;
            padding: 1.5rem;
        }

        .form-control {
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            border: 1px solid #ced4da;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }

        .form-control:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .btn {
            border-radius: 0.5rem;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.15s ease-in-out;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0069d9;
            border-color: #0062cc;
        }

        .btn-outline-secondary:hover {
            background-color: #6c757d;
            border-color: #6c757d;
            color: white;
        }

        .invalid-feedback {
            font-size: 0.875rem;
            color: #dc3545;
            margin-top: 0.25rem;
        }

        .alert {
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 0;
        }

        .text-primary {
            color: #007bff !important;
        }

        .bg-primary {
            background-color: #007bff !important;
        }

        /* Animaciones */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .alert {
            animation: fadeIn 0.3s ease-in-out;
        }
    </style>
</body>
</html> 