<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Estudiante</title>
    <!-- Asegurarnos de que jQuery se cargue primero -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Bootstrap CSS y JS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    
    <!-- Estilos personalizados -->
    <style>
        :root {
            --primary-color: #1a3b89;
            --primary-hover: #132c66;
            --text-primary: #2d3748;
            --text-secondary: #4a5568;
            --border-color: #e2e8f0;
            --background-light: #f7fafc;
            --background-section: #edf2f7;
            --error-color: #e53e3e;
            --success-color: #38a169;
        }

        body.bg-light {
            background-color: var(--background-light) !important;
        }

        .form-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 1.2rem;
            background-color: #fff;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        }

        .form-title {
            color: var(--primary-color);
            font-size: 1.3rem;
            margin-bottom: 1.2rem;
            font-weight: 500;
            text-align: left;
            padding-left: 0.5rem;
        }

        .section-header {
            color: var(--text-primary);
            font-size: 1rem;
            margin-bottom: 0.8rem;
            font-weight: 500;
            padding-bottom: 0.3rem;
            border-bottom: 1px solid var(--border-color);
        }

        .form-row {
            display: flex;
            flex-wrap: wrap;
            margin-right: -0.5rem;
            margin-left: -0.5rem;
        }

        .form-col {
            flex: 1;
            padding: 0 0.5rem;
            min-width: 200px;
        }

        @media (max-width: 768px) {
            .form-col {
                flex: 0 0 100%;
            }
        }

        .form-group {
            margin-bottom: 0.8rem;
        }

        .form-label {
            font-size: 0.85rem;
            font-weight: 400;
            color: var(--text-secondary);
            margin-bottom: 0.2rem;
        }

        .form-control, .form-select {
            font-size: 0.85rem;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            padding: 0.35rem 0.7rem;
            width: 100%;
            transition: all 0.2s ease;
            color: var(--text-primary);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(26, 59, 137, 0.1);
            outline: none;
        }

        .form-control::placeholder {
            color: #a0aec0;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            padding: 0.5rem 1.5rem;
            font-size: 0.9rem;
            font-weight: 400;
            transition: all 0.2s ease;
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
            transform: translateY(-1px);
        }

        .section-container {
            background-color: var(--background-section);
            border-radius: 4px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .codigo-container {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            padding: 0.6rem;
            background-color: var(--background-section);
            border-radius: 4px;
            gap: 1rem;
        }

        .form-check-input {
            width: 1rem;
            height: 1rem;
            margin-right: 0.5rem;
            cursor: pointer;
            border-color: var(--border-color);
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .form-check-label {
            font-size: 0.85rem;
            color: var(--text-secondary);
            cursor: pointer;
        }

        .validation-icon {
            margin-left: 0.5rem;
            font-size: 1rem;
        }

        .validation-icon.success {
            color: var(--success-color);
        }

        .validation-icon.error {
            color: var(--error-color);
        }

        .error-message {
            color: var(--error-color);
            font-size: 0.8rem;
            margin-top: 0.2rem;
            display: none;
        }

        .is-invalid {
            border-color: var(--error-color) !important;
        }

        .alert {
            padding: 0.8rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: 4px;
            font-size: 0.85rem;
        }

        .alert-success {
            color: var(--success-color);
            background-color: #f0fff4;
            border-color: #c6f6d5;
        }

        .alert-danger {
            color: var(--error-color);
            background-color: #fff5f5;
            border-color: #fed7d7;
        }

        .mb-4 {
            margin-bottom: 1.5rem;
        }

        .mb-3 {
            margin-bottom: 1rem;
        }

        @media (max-width: 992px) {
            .form-container {
                max-width: 100%;
                padding: 1rem;
            }
        }

        .select2-container--default .select2-selection--single {
            height: 34px;
            border: 1px solid var(--border-color);
            background-color: #fff;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 34px;
            font-size: 0.85rem;
            padding-left: 0.8rem;
            color: var(--text-primary);
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 32px;
        }

        .select2-dropdown {
            border-color: var(--border-color);
            font-size: 0.85rem;
        }

        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: var(--primary-color);
        }

        .loading-spinner {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 2rem;
            color: var(--primary-color);
        }
        .fade-out {
            opacity: 1;
            transition: opacity 1s ease-out;
        }

        .alert-custom {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
            padding: 15px;
            border-radius: 5px;
            background-color: #1a3b89;
            color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            display: none;
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
        }

        .alert-custom.show {
            display: block;
            opacity: 1;
        }
    </style>
    <link rel="stylesheet" href="/wp-content/plugins/plg-genesis/assets/common.css">
</head>
<body class="bg-light">
    <div class="container-fluid py-3">
        <div class="form-container">
            <h1 class="form-title">Crear Estudiante</h1>
            <form id="formCrearEstudiante">
                <!-- Sección: Código de Estudiante -->
                <div class="codigo-container">
                    <div class="form-check">
                        <input type="checkbox" id="usar_codigo_manual" name="usar_codigo_manual" class="form-check-input" tabindex="1">
                        <label for="usar_codigo_manual" class="form-check-label">Código manual</label>
                    </div>
                    <div class="ms-3">
                        <input type="text" id="codigo_estudiante" name="codigo_estudiante" 
                               class="form-control form-control-sm" style="width: 120px;" 
                               disabled tabindex="2">
                        <span class="validation-icon success" id="codigo_valido" style="display:none;">✓</span>
                        <span class="validation-icon error" id="codigo_invalido" style="display:none;">✗</span>
                    </div>
                </div>
                
                <!-- Sección: Datos Personales -->
                <div class="section-container">
                    <h3 class="section-header">Datos Personales</h3>
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="nombre1" class="form-label">Primer Nombre</label>
                                <input type="text" id="nombre1" class="form-control" 
                                       placeholder="Ingrese el primer nombre" required tabindex="3">
                                <div class="error-message"></div>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="nombre2" class="form-label">Segundo Nombre</label>
                                <input type="text" id="nombre2" class="form-control" 
                                       placeholder="Ingrese el segundo nombre" tabindex="4">
                                <div class="error-message"></div>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="apellido1" class="form-label">Primer Apellido</label>
                                <input type="text" id="apellido1" class="form-control" 
                                       placeholder="Ingrese el primer apellido" required tabindex="5">
                                <div class="error-message"></div>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="apellido2" class="form-label">Segundo Apellido</label>
                                <input type="text" id="apellido2" class="form-control" 
                                       placeholder="Ingrese el segundo apellido" tabindex="6">
                                <div class="error-message"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sección: Información de Contacto -->
                <div class="section-container">
                    <h3 class="section-header">Información de Contacto</h3>
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="docIdentidad" class="form-label">Documento de Identidad</label>
                                <input type="text" id="docIdentidad" class="form-control" 
                                       placeholder="Ingrese el documento" tabindex="7">
                                <div class="error-message"></div>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="celular" class="form-label">Celular</label>
                                <input type="text" id="celular" class="form-control" 
                                       placeholder="Ingrese el celular" tabindex="8">
                                <div class="error-message"></div>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="email" class="form-label">Correo Electrónico</label>
                                <input type="email" id="email" class="form-control" 
                                       placeholder="Ingrese el correo" tabindex="9">
                                <div class="error-message"></div>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="ciudad" class="form-label">Ciudad</label>
                                <input type="text" id="ciudad" class="form-control" 
                                       placeholder="Ingrese la ciudad" tabindex="10">
                                <div class="error-message"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sección: Otros Detalles -->
                <div class="section-container">
                    <h3 class="section-header">Otros Detalles</h3>
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="iglesia" class="form-label">Iglesia</label>
                                <input type="text" id="iglesia" class="form-control" 
                                       placeholder="Ingrese la iglesia" tabindex="11">
                                <div class="error-message"></div>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="estado_civil" class="form-label">Estado Civil</label>
                                <select id="estado_civil" class="form-select" required tabindex="12">
                                    <option value="" disabled selected>Seleccione el estado civil</option>
                                    <option value="Soltero">Soltero</option>
                                    <option value="Casado">Casado</option>
                                    <option value="Divorciado">Divorciado</option>
                                    <option value="Viudo">Viudo</option>
                                    <option value="Unión Libre">Unión Libre</option>
                                </select>
                                <div class="error-message"></div>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="escolaridad" class="form-label">Escolaridad</label>
                                <select id="escolaridad" class="form-select" required tabindex="13">
                                    <option value="" disabled selected>Seleccione la escolaridad</option>
                                    <option value="Primaria">Primaria</option>
                                    <option value="Secundaria">Secundaria</option>
                                    <option value="Técnico">Técnico</option>
                                    <option value="Tecnólogo">Tecnólogo</option>
                                    <option value="Universitario">Universitario</option>
                                    <option value="Postgrado">Postgrado</option>
                                    <option value="Ninguno">Ninguno</option>
                                </select>
                                <div class="error-message"></div>
                            </div>
                        </div>
                        <div class="form-col">
                            <div class="form-group">
                                <label for="ocupacion" class="form-label">Ocupación</label>
                                <input type="text" id="ocupacion" class="form-control" 
                                       placeholder="Ingrese la ocupación" required tabindex="14">
                                <div class="error-message"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sección: Contacto -->
                <div class="section-container">
                    <h3 class="section-header">Iglesia o Contacto Asignado</h3>
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <select id="contacto" class="form-select" required tabindex="15">
                                    <option value="" disabled selected>Seleccione un contacto</option>
                                </select>
                                <div class="error-message"></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Botón de envío -->
                <div class="text-end mt-3">
                    <button type="submit" class="btn btn-primary" tabindex="16">Crear Estudiante</button>
                </div>
            </form>
            <div id="responseMessage" class="mt-3"></div>
        </div>
    </div>
    <div class="loading-spinner" id="loadingSpinner">⏳</div>
    <script src="/wp-content/plugins/plg-genesis/assets/common.js"></script>
    <script>
    $(document).ready(function () {
        // Funciones de validación
        const validaciones = {
            soloLetras: function(valor) {
                return /^[a-záéíóúñüA-ZÁÉÍÓÚÑÜ\s'-]*$/.test(valor);
            },
            soloNumeros: function(valor) {
                return /^\d*$/.test(valor);
            },
            email: function(valor) {
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(valor);
            }
        };

        // Función para mostrar errores
        function mostrarError(elemento, mensaje) {
            const errorDiv = elemento.next('.error-message');
            if (errorDiv.length === 0) {
                elemento.after(`<div class="error-message text-danger small">${mensaje}</div>`);
            } else {
                errorDiv.text(mensaje);
            }
            elemento.addClass('is-invalid');
        }

        // Función para limpiar errores
        function limpiarError(elemento) {
            elemento.next('.error-message').remove();
            elemento.removeClass('is-invalid');
        }

        // Validaciones en tiempo real
        $('#nombre1, #nombre2, #apellido1, #apellido2').on('input', function() {
            const valor = $(this).val();
            if (valor && !validaciones.soloLetras(valor)) {
                mostrarError($(this), 'Solo se permiten letras, espacios y caracteres especiales latinos');
            } else {
                limpiarError($(this));
            }
        });

        $('#docIdentidad, #celular').on('input', function() {
            const valor = $(this).val();
            if (valor && !validaciones.soloNumeros(valor)) {
                mostrarError($(this), 'Solo se permiten números');
            } else {
                limpiarError($(this));
            }
        });

        $('#email').on('input', function() {
            const valor = $(this).val();
            if (valor && !validaciones.email(valor)) {
                mostrarError($(this), 'Ingrese un correo electrónico válido');
            } else {
                limpiarError($(this));
            }
        });

        // Manejo mejorado del código manual
        $('#usar_codigo_manual').change(function () {
            const codigoInput = $('#codigo_estudiante');
            if ($(this).is(':checked')) {
                codigoInput.prop('disabled', false).attr('required', true);
                $('#btn_crear').prop('disabled', true);
            } else {
                codigoInput.prop('disabled', true).val('').attr('required', false);
                $('#codigo_valido, #codigo_invalido').hide();
                limpiarError(codigoInput);
                $('#btn_crear').prop('disabled', false);
            }
        });
        
        $('#codigo_estudiante').on('input', function () {
            const codigo = $(this).val().trim();
            if (codigo.length > 0) {
                $.post('../../backend/estudiantes/validar_codigo.php', 
                    { codigo_estudiante: codigo }, 
                    function (data) {
                        if (data.success) {
                            $('#codigo_valido').show();
                            $('#codigo_invalido').hide();
                            limpiarError($('#codigo_estudiante'));
                            $('#btn_crear').prop('disabled', false);
                        } else {
                            $('#codigo_valido').hide();
                            $('#codigo_invalido').show();
                            mostrarError($('#codigo_estudiante'), 'Este código ya existe');
                            $('#btn_crear').prop('disabled', true);
                        }
                    }
                ).fail(function() {
                    mostrarError($('#codigo_estudiante'), 'Error al validar el código');
                });
            } else {
                $('#codigo_valido, #codigo_invalido').hide();
                limpiarError($('#codigo_estudiante'));
            }
        });

        // Inicializar Select2 para el campo de contacto
        const selectContacto = $('#contacto');
    
        selectContacto.select2({
            placeholder: 'Seleccione un contacto',
            allowClear: true,
            width: 'resolve',
        });
        
        // Cargar contactos en el select
        $.getJSON('../../backend/contactos/obtener_contactos.php', function (data) {
            if (data.success && data.contactos) {
                selectContacto.empty();
                selectContacto.append('<option value="" disabled selected>Seleccione un contacto</option>');

                data.contactos.forEach(contacto => {
                    selectContacto.append(`<option value="${contacto.id}">${contacto.code} - ${contacto.iglesia}</option>`);
                });

                selectContacto.trigger('change');
            } else {
                alert('Error al cargar los contactos.');
            }
        }).fail(function () {
            alert('Error en la conexión con el servidor.');
        });
    
        // Validación del formulario antes de enviar
        $('#formCrearEstudiante').on('submit', function (e) {
            e.preventDefault();
            let tieneErrores = false;

            // Mostrar el spinner de carga
            $('#loadingSpinner').show();

            // Validar campos requeridos
            $(this).find('[required]').each(function() {
                if (!$(this).val()) {
                    mostrarError($(this), 'Este campo es requerido');
                    tieneErrores = true;
                }
            });

            if (tieneErrores) {
                $('#loadingSpinner').hide();
                return false;
            }

            const estudianteData = {
                nombre1: $('#nombre1').val().trim(),
                nombre2: $('#nombre2').val().trim(),
                apellido1: $('#apellido1').val().trim(),
                apellido2: $('#apellido2').val().trim(),
                doc_identidad: $('#docIdentidad').val().trim(),
                email: $('#email').val().trim(),
                celular: $('#celular').val().trim(),
                ciudad: $('#ciudad').val().trim(),
                iglesia: $('#iglesia').val().trim(),
                id_contacto: $('#contacto').val(),
                estado_civil: $('#estado_civil').val(),
                escolaridad: $('#escolaridad').val(),
                ocupacion: $('#ocupacion').val().trim()
            };

            if ($('#usar_codigo_manual').is(':checked')) {
                estudianteData.codigo_manual = $('#codigo_estudiante').val().trim();
            }

            $.ajax({
                url: '../../backend/estudiantes/crear_estudiante.php',
                method: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify(estudianteData),
                success: function (response) {
                    $('#loadingSpinner').hide();
                    const messageDiv = $('#responseMessage');
                    if (response.success) {
                        messageDiv
                            .html(`Estudiante creado exitosamente. ID: <strong>${response.estudiante_id}</strong>`)
                            .removeClass('alert-danger')
                            .addClass('alert alert-success fade-out');
                        
                        // Limpiar formulario
                        $('#formCrearEstudiante')[0].reset();
                        $('#contacto').val(null).trigger('change');
                        
                        // Desvanecer el mensaje después de 5 segundos
                        setTimeout(() => {
                            messageDiv.fadeOut('slow', function() {
                                $(this).removeClass('fade-out').html('').show();
                            });
                        }, 5000);
                    } else {
                        let errorMsg = 'Error al crear el estudiante: ';
                        if (response.errors) {
                            errorMsg += '<ul class="mb-0">';
                            Object.entries(response.errors).forEach(([campo, mensaje]) => {
                                errorMsg += `<li>${mensaje}</li>`;
                                mostrarError($(`#${campo}`), mensaje);
                            });
                            errorMsg += '</ul>';
                        } else {
                            errorMsg += response.message || 'Error desconocido';
                        }
                        
                        messageDiv
                            .html(errorMsg)
                            .removeClass('alert-success')
                            .addClass('alert alert-danger');
                    }
                },
                error: function () {
                    $('#loadingSpinner').hide();
                    $('#responseMessage')
                        .html('Error de conexión con el servidor')
                        .removeClass('alert-success')
                        .addClass('alert alert-danger');
                }
            });
        });

        function showAlert(message, duration = 5000) {
            const alertDiv = $('<div class="alert-custom"></div>').text(message);
            $('body').append(alertDiv);
            alertDiv.addClass('show');

            setTimeout(() => {
                alertDiv.removeClass('show');
                setTimeout(() => alertDiv.remove(), 500);
            }, duration);
        }
    });
    </script>

</body>
</html>

<?php
pg_close($conexion);
?>