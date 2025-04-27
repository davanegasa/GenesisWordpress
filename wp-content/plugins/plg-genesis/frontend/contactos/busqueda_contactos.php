<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Contactos</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        /* Ajustes para la tabla en pantallas pequeñas */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch; /* Mejora la experiencia táctil */
        }
        
        /* Ocultar columnas menos importantes en pantallas pequeñas */
        @media (max-width: 768px) {
            .table th:nth-child(3),
            .table td:nth-child(3), /* Iglesia */
            .table th:nth-child(4),
            .table td:nth-child(4), /* Email */
            .table th:nth-child(5),
            .table td:nth-child(5) /* Celular */ {
                display: none;
            }
        }

        /* Ajustar columnas con texto largo */
        .table td, .table th {
            white-space: nowrap; /* Evita desbordamientos */
            text-overflow: ellipsis;
            overflow: hidden;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="text-primary mb-0">Lista de Contactos</h1>
            <button id="generateInforme" class="btn btn-sm btn-secondary">Generar Informe</button>
        </div>
    
        <div class="mb-3">
            <input type="text" id="filter-global" class="form-control" placeholder="Buscar por nombre, iglesia, email, celular o código">
        </div>
    </div>


    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-primary">
                <tr>
                    <th>Code</th>
                    <th>Nombre</th>
                    <th>Iglesia</th>
                    <th>Email</th>
                    <th>Celular</th>
                    <th>Ciudad</th>
                </tr>
            </thead>
            <tbody id="tabla-cuerpo">
                <!-- Contenido dinámico generado por JavaScript -->
            </tbody>
        </table>
    </div>
    <div id="mensaje-error" class="alert alert-danger text-center d-none">
        Error al cargar los contactos. Intenta nuevamente más tarde.
    </div>
    
    <!-- Modal para mostrar el PDF -->
    <div class="modal fade" id="modalInformePDF" tabindex="-1" aria-labelledby="modalInformePDFLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalInformePDFLabel">Informe de Contactos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <iframe id="pdfViewer" src="" style="width: 100%; height: 500px; border: none;"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    let contactos = [];

    // Función para cargar contactos
    function cargarContactos() {
        $.getJSON('../../backend/contactos/obtener_contactos.php', function (data) {
            if (data.success && Array.isArray(data.contactos) && data.contactos.length > 0) {
                contactos = data.contactos;
                mostrarContactos(contactos);
            } else {
                mostrarError('No se encontraron contactos disponibles.');
            }
        }).fail(function () {
            mostrarError('Error al conectar con el servidor.');
        });
    }
    
    // Mostrar contactos en la tabla
    function mostrarContactos(contactosFiltrados) {
        const cuerpoTabla = $('#tabla-cuerpo');
        const mensajeError = $('#mensaje-error');
    
        mensajeError.addClass('d-none');
        cuerpoTabla.empty();
    
        // Ordenar contactos: primero por número, luego por texto
        contactosFiltrados.sort((a, b) => {
            const codeA = a.code?.trim() || '';
            const codeB = b.code?.trim() || '';
    
            // Intentar convertir a número
            const numA = parseFloat(codeA);
            const numB = parseFloat(codeB);
    
            // Si ambos son números, ordenarlos como números
            if (!isNaN(numA) && !isNaN(numB)) {
                return numA - numB;
            }
    
            // Si uno es número y el otro no, priorizar el numérico
            if (!isNaN(numA)) return -1;
            if (!isNaN(numB)) return 1;
    
            // Si ambos son cadenas, ordenarlos alfabéticamente
            return codeA.localeCompare(codeB);
        });
    
        contactosFiltrados.forEach(contacto => {
            cuerpoTabla.append(`
                <tr>
                    <td>
                        <a href="ver_detalle_contacto.php?id=${contacto.id}" class="text-decoration-none text-primary fw-bold">
                            ${contacto.code || '-'}
                        </a>
                    </td>
                    <td>${contacto.nombre}</td>
                    <td>${contacto.iglesia || '-'}</td>
                    <td>${contacto.email || '-'}</td>
                    <td>${contacto.celular || '-'}</td>
                    <td>${contacto.ciudad || '-'}</td>
                </tr>
            `);
        });
    }

    // Mostrar error
    function mostrarError(mensaje) {
        $('#mensaje-error').removeClass('d-none').text(mensaje);
        $('#tabla-cuerpo').empty(); // Limpiar la tabla
    }

    // Filtrar contactos en función del campo global de búsqueda
    $('#filter-global').on('keyup', function () {
        const filtroGlobal = $(this).val().toLowerCase();

        const contactosFiltrados = contactos.filter(contacto => {
            return (
                contacto.nombre.toLowerCase().includes(filtroGlobal) ||
                (contacto.iglesia || '').toLowerCase().includes(filtroGlobal) ||
                (contacto.email || '').toLowerCase().includes(filtroGlobal) ||
                (contacto.celular || '').toLowerCase().includes(filtroGlobal) ||
                (contacto.code || '').toLowerCase().includes(filtroGlobal)
            );
        });

        mostrarContactos(contactosFiltrados);
    });
    
    $('#generateInforme').on('click', function () {
        $('#pdfViewer').attr('src', '../../informes/contactos/generar_informe_contactos.php');
        $('#modalInformePDF').modal('show');
    });

    // Cargar contactos al inicio
    cargarContactos();
});
</script>

</body>
</html>