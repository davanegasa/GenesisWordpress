<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe de Estudiantes Activos y Cursos Corregidos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .table-container {
            max-height: 60vh;
            overflow-y: auto;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h1 class="text-center text-primary">Informe de Estudiantes Activos y Cursos Corregidos</h1>
    <div class="d-flex justify-content-between align-items-center mt-4">
        <div>
            <label for="year-selector" class="form-label">Seleccione el Año</label>
            <select id="year-selector" class="form-select">
                <!-- Años dinámicos cargados con JavaScript -->
            </select>
        </div>
        <button id="download-pdf" class="btn btn-secondary">Descargar PDF</button>
    </div>
    
    <div class="table-container mt-4">
        <table class="table table-striped table-hover">
            <thead class="table-primary">
                <tr>
                    <th>Mes</th>
                    <th>Estudiantes Activos</th>
                    <th>Cursos Correjidos</th>
                    <th>Nuevos Estudiantes</th>
                    <th>Contactos Activos</th>
                    <th>Nuevos Contactos</th>
                </tr>
            </thead>
            <tbody id="tabla-informe">
                <!-- Filas dinámicas generadas por JavaScript -->
            </tbody>
        </table>
    </div>
</div>

<script>
$(document).ready(function () {
    // Función para llenar el selector de años
    function populateYearSelector() {
        const currentYear = new Date().getFullYear();
        const yearSelector = $('#year-selector');

        for (let year = currentYear; year >= 2000; year--) {
            yearSelector.append(`<option value="${year}">${year}</option>`);
        }

        yearSelector.val(currentYear); // Seleccionar el año actual por defecto
    }

    // Función para cargar datos del informe
    function loadReport(year) {
        $.ajax({
            url: '../../../backend/informes/estudiantes_activos_cursos_corregidos.php',
            method: 'GET',
            data: { year },
            dataType: 'json',
            success: function (response) {
                const tableBody = $('#tabla-informe'); // Corregido para usar el id correcto
                tableBody.empty();

                if (response.success && response.data.length > 0) {
                    response.data.forEach(row => {
                        tableBody.append(`
                            <tr>
                                <td>${row.mes}</td>
                                <td>${row.estudiantes_activos}</td>
                                <td>${row.cursos_correjidos}</td>
                                <td>${row.estudiantes_registrados}</td>
                                <td>${row.contactos_activos}</td>
                                <td>${row.contactos_registrados}</td>
                            </tr>
                        `);
                    });
                } else {
                    tableBody.append('<tr><td colspan="6" class="text-center">No se encontraron datos para el año seleccionado.</td></tr>');
                }
            },
            error: function () {
                alert('Error al cargar el informe. Por favor, intente nuevamente.');
            }
        });
    }

    // Descargar PDF
    $('#download-pdf').on('click', function () {
        const year = $('#year-selector').val();
        window.open(`../../../backend/informes/estudiantes_activos_cursos_corregidos.php?year=${year}`, '_blank'); // Corregido ?? por ?
    });

    // Cambiar el año en el selector
    $('#year-selector').on('change', function () {
        const year = $(this).val();
        loadReport(year);
    });

    // Inicializar la página
    populateYearSelector();
    loadReport($('#year-selector').val());
});
</script>

</body>
</html>
