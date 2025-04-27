<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del Contacto</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<div class="container mt-5">
    <h1 class="text-center text-primary mb-4">Detalle del Contacto</h1>
    <div id="detalleContacto" class="card shadow-lg p-4">
        <!-- Detalles cargados dinámicamente -->
        <h2 class="text-primary"></h2>
        <p><strong>Detalles del contacto aquí</strong></p>
    </div>
    
    <div id="programasAsignadosContainer" class="card shadow-lg p-4 mt-4">
        <h3 class="text-secondary">Programas Asignados</h3>
        <ul id="programasAsignados" class="list-group list-group-flush">
            <!-- Los programas se cargarán dinámicamente -->
        </ul>
    </div>

    <!-- Sección de Observaciones -->
    <div id="observacionesContainer" class="card shadow-lg p-4 mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="text-secondary mb-0">Observaciones</h3>
            <button class="btn btn-primary btn-sm" id="btnAgregarObservacion">
                <i class="bi bi-plus-circle"></i> Agregar Observación
            </button>
        </div>
        <div id="observacionesList" class="list-group list-group-flush">
            <!-- Las observaciones se cargarán dinámicamente -->
        </div>
    </div>
    
    <div id="mensajeError" class="alert alert-danger text-center mt-3 d-none">
        Error al cargar los detalles del contacto. Intente nuevamente más tarde.
    </div>
    <div class="text-center mt-4">
        <a href="busqueda_contactos.php" class="btn btn-secondary">Volver</a>
        <button class="btn btn-primary" id="btnEditarContacto">Editar</button>
        <button class="btn btn-success" id="btnGenerarInforme">Generar Informe en PDF</button>
        <button class="btn btn-success" id="btnListaEstudiantes">Lista de estudiantes PDF</button>
        <button class="btn btn-warning" id="btnAsignarProgramas">Asignar Programas</button>
        <button class="btn btn-success" id="btnListaEstudiantesActivos">Lista de estudiantes Activos PDF</button>
    </div>
</div>

<!-- Modal para actualizar contacto -->
<div class="modal fade" id="modalEditarContacto" tabindex="-1" aria-labelledby="modalEditarContactoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarContactoLabel">Editar Contacto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarContacto">
                    <div class="mb-3">
                        <label for="nombreContacto" class="form-label">Nombre</label>
                        <input type="text" id="nombreContacto" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="emailContacto" class="form-label">Correo Electrónico</label>
                        <input type="email" id="emailContacto" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="celularContacto" class="form-label">Celular</label>
                        <input type="text" id="celularContacto" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="direccionContacto" class="form-label">Dirección</label>
                        <input type="text" id="direccionContacto" class="form-control">
                    </div>
                    <input type="hidden" id="idContacto">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarCambios">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para mostrar el informe en PDF -->
<div class="modal fade" id="modalInformePDF" tabindex="-1" aria-labelledby="modalInformePDFLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalInformePDFLabel">Informe en PDF</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <iframe id="pdfViewer" src="" width="100%" height="80vh" style="border: none;"></iframe>
            </div>
        </div>
    </div>
</div>

<!-- Modal para asignar programas -->
<div class="modal fade" id="modalAsignarProgramas" tabindex="-1" aria-labelledby="modalAsignarProgramasLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAsignarProgramasLabel">Asignar Programas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Selecciona los programas que deseas asignar:</p>
                <form id="formAsignarProgramas">
                    <div id="programasContainer" class="list-group">
                        <!-- Programas cargados dinámicamente -->
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarAsignacion">Asignar Programas</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para agregar observación -->
<div class="modal fade" id="modalAgregarObservacion" tabindex="-1" aria-labelledby="modalAgregarObservacionLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAgregarObservacionLabel">Agregar Observación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formAgregarObservacion">
                    <div class="mb-3">
                        <label for="tipoObservacion" class="form-label">Tipo de Observación</label>
                        <select class="form-select" id="tipoObservacion" required>
                            <option value="General">General</option>
                            <option value="Académica">Académica</option>
                            <option value="Comportamiento">Comportamiento</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="observacion" class="form-label">Observación</label>
                        <textarea class="form-control" id="observacion" rows="4" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarObservacion">Guardar</button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    const contactoId = new URLSearchParams(window.location.search).get('id');
    const urlParams = new URLSearchParams(window.location.search);

    if (!contactoId) {
        $('#detalleContacto').hide();
        $('#mensajeError').removeClass('d-none').text('No se proporcionó un ID de contacto válido.');
        return;
    }
    
    // Cargar programas disponibles como checkboxes
    function cargarProgramas() {
        $.getJSON('../../backend/programas/obtener_programas.php', function (programas) {
            if (Array.isArray(programas) && programas.length > 0) {
                const container = $('#programasContainer');
                container.empty();
                programas.forEach(programa => {
                    container.append(`
                        <label class="list-group-item">
                            <input type="checkbox" class="form-check-input me-2" value="${programa.id}">
                            ${programa.nombre}
                        </label>
                    `);
                });
            } else {
                alert('No se encontraron programas disponibles.');
            }
        }).fail(function () {
            alert('Error en la conexión al servidor para obtener los programas.');
        });
    }

    // Cargar detalles del contacto
    function cargarDetallesContacto() {
        $.getJSON(`../../backend/contactos/detalle_contacto.php?id=${contactoId}`, function (data) {
            if (data.success) {
                const contacto = data.contacto;
                
                // Actualizar detalles del contacto
                $('#detalleContacto').find('h2.text-primary').text(contacto.nombre || 'Sin Nombre');
                $('#detalleContacto').find('p').remove(); // Limpia párrafos existentes
                $('#detalleContacto').append(`
                    <p><strong>Code:</strong> ${contacto.code || 'No asignado'}</p>
                    <p><strong>Teléfono:</strong> ${contacto.celular || 'No disponible'}</p>
                    <p><strong>Email:</strong> ${contacto.email || 'No disponible'}</p>
                    <p><strong>Dirección:</strong> ${contacto.direccion || 'No registrada'}</p>
                    <p><strong>Ciudad:</strong> ${contacto.ciudad || 'No especificada'}</p>
                    <p><strong>Iglesia:</strong> ${contacto.iglesia || 'No especificada'}</p>
                `);
                
                // Mostrar programas asignados
                const programasList = data.programas;
                $('#programasAsignados').empty(); // Limpia programas existentes
                if (programasList && programasList.length > 0) {
                    programasList.forEach(programa => {
                        $('#programasAsignados').append(`
                            <li class="list-group-item d-flex flex-column align-items-start">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-book text-primary me-3"></i>
                                    <span class="fw-bold">${programa.nombre}</span>
                                </div>
                                <small class="text-muted">${programa.descripcion || 'Sin descripción disponible'}</small>
                            </li>
                        `);
                    });
                } else {
                    $('#programasAsignados').append('<li class="list-group-item">No hay programas personalizados, se usará el programa inicial de la escuela</li>');
                }
                $('#programasAsignados').show();
                
                // Cargar datos en el modal
                $('#idContacto').val(contacto.id);
                $('#nombreContacto').val(contacto.nombre);
                $('#emailContacto').val(contacto.email);
                $('#celularContacto').val(contacto.celular);
                $('#direccionContacto').val(contacto.direccion || '');
            } else {
                mostrarError(data.error || 'Error al cargar los detalles.');
            }
        }).fail(function () {
            mostrarError('Error en la conexión al servidor.');
        });
    }

    // Mostrar errores
    function mostrarError(mensaje) {
        $('#detalleContacto').hide();
        $('#mensajeError').removeClass('d-none').text(mensaje);
    }

    // Abrir modal para edición
    $('#btnEditarContacto').on('click', function () {
        $('#modalEditarContacto').modal('show');
    });

    // Guardar cambios
    $('#btnGuardarCambios').on('click', function () {
        const datosActualizados = {
            id: $('#idContacto').val(),
            nombre: $('#nombreContacto').val(),
            email: $('#emailContacto').val(),
            celular: $('#celularContacto').val(),
            direccion: $('#direccionContacto').val()
        };

        $.ajax({
            url: '../../backend/contactos/actualizar_contacto.php',
            method: 'PUT',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(datosActualizados),
            success: function (data) {
                if (data.success) {
                    alert('Contacto actualizado exitosamente.');
                    $('#modalEditarContacto').modal('hide');
                    cargarDetallesContacto(); // Recargar detalles
                } else {
                    alert('Error al actualizar el contacto.');
                }
            },
            error: function () {
                alert('Error en el servidor.');
            }
        });
    });

    // Generar informe en PDF
    $('#btnGenerarInforme').on('click', function () {
        const pdfUrl = `../../frontend/estudiantes/descargar_lista_pdf.php?id_contacto=${contactoId}`;
        
        // Cargar el PDF en el iframe del modal
        $('#pdfViewer').attr('src', pdfUrl);
        
        // Mostrar el modal
        $('#modalInformePDF').modal('show');
    });
    
    $('#btnListaEstudiantes').on('click', function () {
        const pdfUrl = `../../informes/contactos/estudiantes_por_contactos.php?contacto_id=${contactoId}`;
        
        // Cargar el PDF en el iframe del modal
        $('#pdfViewer').attr('src', pdfUrl);
        
        // Mostrar el modal
        $('#modalInformePDF').modal('show');
    });
    
    $('#btnListaEstudiantesActivos').on('click', function () {
        const pdfUrl = `../../informes/contactos/estudiantes_activos_por_contacto.php?contacto_id=${contactoId}`;
        
        // Cargar el PDF en el iframe del modal
        $('#pdfViewer').attr('src', pdfUrl);
        
        // Mostrar el modal
        $('#modalInformePDF').modal('show');
    });
    
    // Abrir modal para asignar programas
    $('#btnAsignarProgramas').on('click', function () {
        cargarProgramas();
        $('#modalAsignarProgramas').modal('show');
    });

    // Manejar la asignación de programas seleccionados
    $('#btnGuardarAsignacion').on('click', function () {
        const selectedProgramas = [];
        $('#programasContainer input:checked').each(function () {
            selectedProgramas.push($(this).val());
        });
    
        if (selectedProgramas.length === 0) {
            alert('Por favor selecciona al menos un programa para asignar.');
            return;
        }
    
        const payload = {
            contacto_id: contactoId,
            programa_ids: selectedProgramas
        };
    
        $.ajax({
            url: '../../backend/programas/asignar_programa.php',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(payload),
            success: function (response) {
                if (response.success) {
                    alert('Programas asignados exitosamente.');
                    $('#modalAsignarProgramas').modal('hide');
                } else {
                    alert(response.error || 'Error al asignar programas.');
                }
            },
            error: function () {
                alert('Error en la conexión con el servidor.');
            }
        });
    });

    // Cargar observaciones
    function cargarObservaciones() {
        $.getJSON(`../../backend/contactos/obtener_observaciones.php?id=${contactoId}`, function (data) {
            if (data.success) {
                const observacionesList = $('#observacionesList');
                observacionesList.empty();
                
                if (data.observaciones && data.observaciones.length > 0) {
                    data.observaciones.forEach(obs => {
                        const fecha = new Date(obs.fecha).toLocaleString();
                        observacionesList.append(`
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">${obs.tipo}</h6>
                                    <small class="text-muted">${fecha}</small>
                                </div>
                                <p class="mb-1">${obs.observacion}</p>
                                <small class="text-muted">Por: ${obs.usuario || 'Sistema'}</small>
                            </div>
                        `);
                    });
                } else {
                    observacionesList.append('<div class="list-group-item">No hay observaciones registradas.</div>');
                }
            }
        }).fail(function () {
            mostrarError('Error al cargar las observaciones.');
        });
    }

    // Abrir modal para agregar observación
    $('#btnAgregarObservacion').on('click', function () {
        $('#modalAgregarObservacion').modal('show');
    });

    // Guardar nueva observación
    $('#btnGuardarObservacion').on('click', function () {
        const tipo = $('#tipoObservacion').val();
        const observacion = $('#observacion').val();

        if (!observacion.trim()) {
            alert('Por favor, ingrese una observación.');
            return;
        }

        $.ajax({
            url: '../../backend/contactos/agregar_observacion.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                contacto_id: contactoId,
                tipo: tipo,
                observacion: observacion
            }),
            success: function (data) {
                if (data.success) {
                    $('#modalAgregarObservacion').modal('hide');
                    $('#observacion').val('');
                    cargarObservaciones();
                } else {
                    alert('Error al guardar la observación.');
                }
            },
            error: function () {
                alert('Error en el servidor.');
            }
        });
    });

    // Inicializar
    cargarDetallesContacto();
    cargarObservaciones();
});
</script>

</body>
</html>