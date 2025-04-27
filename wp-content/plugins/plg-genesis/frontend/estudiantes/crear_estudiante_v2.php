<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Estudiante</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <style>
        #usar_codigo_manual {
            appearance: none;
            width: 16px;
            height: 16px;
            border: 2px solid #007bff;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            margin-right: 5px; /* Reduce la separación con el campo de texto */
        }
    
        #usar_codigo_manual:checked {
            background-color: #007bff;
            border-color: #007bff;
            position: relative;
        }
    
        #usar_codigo_manual:checked::after {
            content: '✔';
            color: white;
            font-size: 12px;
            font-weight: bold;
            display: block;
            text-align: center;
        }
    </style>
</head>
<body>

    <div class="container mt-5 centered-form-container">
        <div class="form-container" style="max-width: 900px;">
            <h1 class="form-title text-center">Crear Estudiante</h1>
            <form id="formCrearEstudiante">
                <!-- Sección: Código de Estudiante -->
                <div class="mb-3 d-flex align-items-center">
                    <input type="checkbox" id="usar_codigo_manual" name="usar_codigo_manual">
                    <label for="usar_codigo_manual" class="ms-1 me-2">Código manual</label>
                    <input type="text" id="codigo_estudiante" name="codigo_estudiante" class="form-control form-control-sm w-auto" style="width: 120px;" disabled>
                    <span id="codigo_valido" class="text-success ms-2" style="display:none;">✔</span>
                    <span id="codigo_invalido" class="text-danger ms-2" style="display:none;">✖</span>
                </div>
                
                <!-- Sección: Datos Personales -->
                <div>
                    <h3 class="mb-3">Datos Personales</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nombre1" class="form-label">Primer Nombre</label>
                                <input type="text" id="nombre1" class="form-control" placeholder="Ingrese el primer nombre" required>
                            </div>
                            <div class="mb-3">
                                <label for="apellido1" class="form-label">Primer Apellido</label>
                                <input type="text" id="apellido1" class="form-control" placeholder="Ingrese el primer apellido" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nombre2" class="form-label">Segundo Nombre</label>
                                <input type="text" id="nombre2" class="form-control" placeholder="Ingrese el segundo nombre">
                            </div>
                            <div class="mb-3">
                                <label for="apellido2" class="form-label">Segundo Apellido</label>
                                <input type="text" id="apellido2" class="form-control" placeholder="Ingrese el segundo apellido">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sección: Información de Contacto -->
                <div>
                    <h3 class="mb-3">Información de Contacto</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="docIdentidad" class="form-label">Documento de Identidad</label>
                                <input type="text" id="docIdentidad" class="form-control" placeholder="Ingrese el documento de identidad">
                            </div>
                            <div class="mb-3">
                                <label for="celular" class="form-label">Celular</label>
                                <input type="text" id="celular" class="form-control" placeholder="Ingrese el número de celular">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="email" class="form-label">Correo Electrónico</label>
                                <input type="email" id="email" class="form-control" placeholder="Ingrese el correo electrónico">
                            </div>
                            <div class="mb-3">
                                <label for="ciudad" class="form-label">Ciudad</label>
                                <input type="text" id="ciudad" class="form-control" placeholder="Ingrese la ciudad">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Sección: Otros Detalles -->
                <div>
                    <h3 class="mb-3">Otros Detalles</h3>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="iglesia" class="form-label">Iglesia</label>
                                <input type="text" id="iglesia" class="form-control" placeholder="Ingrese la iglesia">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="contacto" class="form-label">Seleccione un Contacto</label>
                                <select id="contacto" class="form-select" required>
                                    <option value="" disabled selected>Seleccione un contacto</option>
                                    <!-- Las opciones se generarán dinámicamente -->
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Botón de envío -->
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-primary w-50">Crear Estudiante</button>
                </div>
            </form>
            <div id="responseMessage" class="mt-3 text-center"></div>
        </div>
    </div>
    <script>
    $(document).ready(function () {
        $('#usar_codigo_manual').change(function () {
            if ($(this).is(':checked')) {
                $('#codigo_estudiante').prop('disabled', false);
                $('#btn_crear').prop('disabled', true);
            } else {
                $('#codigo_estudiante').prop('disabled', true).val('');
                $('#codigo_valido, #codigo_invalido').hide();
                $('#btn_crear').prop('disabled', false);
            }
        });
        
        $('#codigo_estudiante').on('input', function () {
            let codigo = $(this).val();
            if (codigo.length > 0) {
                $.post('../../backend/estudiantes/validar_codigo.php', { codigo_estudiante: codigo }, function (data) {
                    if (data.success) {
                        $('#codigo_valido').show();
                        $('#codigo_invalido').hide();
                        $('#btn_crear').prop('disabled', false);
                    } else {
                        $('#codigo_valido').hide();
                        $('#codigo_invalido').show();
                        $('#btn_crear').prop('disabled', true);
                    }
                }, 'json');
            } else {
                $('#codigo_valido, #codigo_invalido').hide();
            }
        });
        
        // Inicializar Select2 para el campo de contacto
        const selectContacto = $('#contacto');
    
        selectContacto.select2({
            placeholder: 'Seleccione un contacto',
            allowClear: true,
            width: 'resolve', // Ajustar al ancho del contenedor padre
            dropdownCssClass: 'custom-select-dropdown', // Clase CSS personalizada para el dropdown
            selectionCssClass: 'form-select', // Clase CSS personalizada para el input seleccionado
        });
        
        // Cargar contactos en el select
        $.getJSON('../../backend/contactos/obtener_contactos.php', function (data) {
            if (data.success && data.contactos) {
                const selectContacto = $('#contacto');
                selectContacto.empty();
                selectContacto.append('<option value="" disabled selected>Seleccione un contacto</option>');
    
                // Ordenar contactos por código
                const contactosOrdenados = data.contactos.sort((a, b) => {
                    const codeA = parseInt(a.code, 10) || a.code;
                    const codeB = parseInt(b.code, 10) || b.code;
                    return typeof codeA === 'number' && typeof codeB === 'number'
                        ? codeA - codeB
                        : String(codeA).localeCompare(String(codeB));
                });
    
                // Añadir opciones al select
                contactosOrdenados.forEach(contacto => {
                    selectContacto.append(`<option value="${contacto.id}">${contacto.code}- ${contacto.iglesia}</option>`);
                });
    
                // Inicializar Select2
                selectContacto.select2({
                    placeholder: 'Seleccione un contacto',
                    allowClear: true,
                    width: 'resolve',
                });
            } else {
                alert('Error al cargar los contactos.');
            }
        }).fail(function () {
            alert('Error en la conexión con el servidor.');
        });
    
        // Manejar envío del formulario
        $('#formCrearEstudiante').on('submit', function (e) {
            e.preventDefault();
        
            const estudianteData = {
                nombre1: $('#nombre1').val(),
                nombre2: $('#nombre2').val(),
                apellido1: $('#apellido1').val(),
                apellido2: $('#apellido2').val(),
                doc_identidad: $('#docIdentidad').val(),
                email: $('#email').val(),
                celular: $('#celular').val(),
                ciudad: $('#ciudad').val(),
                iglesia: $('#iglesia').val(),
                id_contacto: $('#contacto').val()
            };
        
            $.ajax({
                url: '../../backend/estudiantes/crear_estudiante.php',
                method: 'POST',
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify(estudianteData),
                success: function (response) {
                    const messageDiv = $('#responseMessage');
                    if (response.success) {
                        // Mostrar mensaje de éxito
                        messageDiv
                            .html(`Estudiante creado exitosamente. ID Generado: <strong>${response.estudiante_id}</strong>`)
                            .removeClass('alert-danger')
                            .addClass('alert alert-success')
                            .show();
        
                        // Ocultar el formulario
                        $('#formCrearEstudiante').hide();
                    } else {
                        // Mostrar mensaje de error
                        messageDiv
                            .text(response.error || 'Error al crear el estudiante.')
                            .removeClass('alert-success')
                            .addClass('alert alert-danger')
                            .show();
                    }
                },
                error: function (xhr) {
                    const messageDiv = $('#responseMessage');
        
                    if (xhr.status === 400) {
                        // Manejar errores con código 400
                        const response = JSON.parse(xhr.responseText);
                        messageDiv
                            .text(response.error || 'Error en la solicitud. Verifique los datos ingresados.')
                            .removeClass('alert-success')
                            .addClass('alert alert-danger')
                            .show();
                    } else {
                        // Manejar otros errores del servidor
                        messageDiv
                            .text('Error en el servidor.')
                            .removeClass('alert-success')
                            .addClass('alert alert-danger')
                            .show();
                    }
                }
            });
        });
    });
    </script>

</body>
</html>