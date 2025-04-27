<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Programas</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .program-card {
            border: 1px solid var(--gray-light);
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 15px;
            padding: 15px;
            transition: box-shadow 0.3s ease;
        }

        .program-card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .program-title {
            color: var(--primary-color);
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .program-description {
            font-size: 1rem;
            color: var(--text-color);
            margin-bottom: 10px;
        }

        .btn-actions {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .filter-container {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h1 class="text-center text-primary mb-4">Lista de Programas</h1>

    <!-- Barra de búsqueda -->
    <div class="filter-container">
        <input type="text" id="filterProgramas" class="form-control" placeholder="Buscar programas por nombre o descripción">
    </div>

    <!-- Contenedor de programas -->
    <div id="programs-container" class="row">
        <!-- Programas cargados dinámicamente -->
    </div>
</div>

<script>
$(document).ready(function () {
    let programas = [];

    // Cargar programas
    function cargarProgramas() {
        $.getJSON('../../backend/programas/obtener_programas.php', function (data) {
            programas = data;
            renderizarProgramas();
        }).fail(function () {
            alert("Error al cargar los programas.");
        });
    }

    // Renderizar programas
    function renderizarProgramas(filtro = '') {
        const container = $("#programs-container");
        container.empty();

        const programasFiltrados = programas.filter(programa =>
            programa.nombre.toLowerCase().includes(filtro.toLowerCase()) ||
            programa.descripcion.toLowerCase().includes(filtro.toLowerCase())
        );

        if (programasFiltrados.length === 0) {
            container.append(`<div class="text-center text-muted">No se encontraron programas.</div>`);
            return;
        }

        programasFiltrados.forEach(programa => {
            container.append(`
                <div class="col-md-6">
                    <div class="program-card">
                        <h3 class="program-title">${programa.nombre}</h3>
                        <p class="program-description">${programa.descripcion || 'Sin descripción'}</p>
                        <div class="btn-actions">
                            <a href="ver_programa.php?programa_id=${programa.id}" class="btn btn-primary btn-sm">Ver Detalles</a>
                            <a href="eliminar_programa.php?programa_id=${programa.id}" class="btn btn-warning btn-sm">Eliminar</a>
                        </div>
                    </div>
                </div>
            `);
        });
    }

    // Filtrar programas
    $("#filterProgramas").on("input", function () {
        const filtro = $(this).val().trim();
        renderizarProgramas(filtro);
    });

    // Inicializar
    cargarProgramas();
});
</script>

</body>
</html>