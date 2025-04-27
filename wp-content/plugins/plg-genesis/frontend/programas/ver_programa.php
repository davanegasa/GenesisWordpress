<?php
// Asegurarte de cargar WordPress o la configuración de tu aplicación
require_once(__DIR__ . '/../../../../../wp-load.php');

// Validar y obtener el programa_id de la URL
$programaId = isset($_GET['programa_id']) ? (int)$_GET['programa_id'] : null;

if (!$programaId) {
    // Manejar el caso donde no se proporciona un ID válido
    echo "Error: ID del programa no válido.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Programa</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .program-header {
            margin-bottom: 20px;
        }

        .program-header h1 {
            font-size: 2rem;
            color: var(--primary-color);
        }

        .program-header p {
            font-size: 1.1rem;
            color: var(--gray-dark);
        }

        .levels-container {
            margin-top: 20px;
        }

        .level-card {
            border: 1px solid var(--gray-light);
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .level-title {
            font-size: 1.5rem;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .courses-list {
            margin-top: 10px;
        }

        .course-item {
            padding: 10px;
            background-color: var(--background-color);
            border: 1px solid var(--gray-light);
            border-radius: 4px;
            margin-bottom: 5px;
            display: flex;
            justify-content: space-between;
        }

        .prerequisite-card {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid var(--gray-light);
            border-radius: 5px;
            background-color: var(--background-color);
        }

        .prerequisite-title {
            font-size: 1.2rem;
            color: var(--primary-color);
        }
        
        .level-card {
            border: 1px solid var(--gray-light);
            border-radius: 8px;
            padding: 15px;
            background-color: var(--background-color);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .level-title {
            font-size: 1.25rem;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .courses-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .course-card {
            border: 1px solid var(--gray-light);
            border-radius: 6px;
            padding: 10px;
            background-color: var(--white);
            width: 150px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        
        .course-consecutive {
            font-size: 0.9rem;
            color: var(--text-color);
            margin-top: 5px;
        }
        
        .course-name {
            font-weight: bold;
            font-size: 1.2rem;
            color: var(--secondary-color);
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="program-header text-center">
        <h1 id="programaNombre">Nombre del Programa</h1>
        <p id="programaDescripcion">Descripción del Programa</p>
    </div>

    <!-- Niveles -->
    <div class="levels-container">
        <h2 class="text-primary">Niveles y Cursos</h2>
        <div id="levels-list">
            <!-- Niveles cargados dinámicamente -->
        </div>
    </div>

    <!-- Cursos sin nivel -->
    <div class="mt-4">
        <h2 class="text-primary">Cursos sin Nivel</h2>
        <div id="no-level-courses">
            <!-- Cursos sin nivel cargados dinámicamente -->
        </div>
    </div>

    <!-- Prerrequisitos -->
    <div class="mt-4">
        <h2 class="text-primary">Prerrequisitos</h2>
        <div id="prerequisites-list" class="prerequisite-card">
            <!-- Prerrequisitos cargados dinámicamente -->
        </div>
    </div>

    <div class="text-center mt-4">
        <a href="listar_programas.php" class="btn btn-secondary">Volver a la Lista</a>
        <a href="editar_programa.php?programa_id=<?php echo $programaId; ?>" class="btn btn-warning">Editar Programa</a>
    </div>
    

</div>

<script>
$(document).ready(function () {
    const urlParams = new URLSearchParams(window.location.search);
    const programaId = urlParams.get('programa_id');

    if (!programaId) {
        alert("Programa no encontrado.");
        window.location.href = "lista_programas.php";
        return;
    }

    // Cargar detalles del programa
    function cargarDetallesPrograma() {
        $.getJSON(`../../backend/programas/obtener_programa.php?programa_id=${programaId}`, function (data) {
            $("#programaNombre").text(data.nombre);
            $("#programaDescripcion").text(data.descripcion || 'Sin descripción');

            // Renderizar niveles
            const levelsList = $("#levels-list");
            levelsList.empty();
            
            if (data.niveles && data.niveles.length > 0) {
                data.niveles.forEach(nivel => {
                    const levelCard = $(`
                        <div class="level-card mb-4">
                            <h3 class="level-title">${nivel.nombre_nivel}</h3>
                            <div class="courses-container d-flex flex-wrap">
                                ${nivel.cursos.map(curso => `
                                    <div class="course-card">
                                        <div class="course-consecutive">${curso.consecutivo}</div>
                                        <div class="course-name">${curso.nombre}</div>
                                    </div>
                                `).join('')}
                            </div>
                        </div>
                    `);
                    levelsList.append(levelCard);
                });
            } else {
                levelsList.append('<p class="text-muted">Este programa no tiene niveles definidos.</p>');
            }

            // Renderizar cursos sin nivel
            const noLevelCourses = $("#no-level-courses");
            noLevelCourses.empty();
            
            if (data.cursosSinNivel && data.cursosSinNivel.length > 0) {
                const noLevelCoursesContainer = $('<div class="courses-container d-flex flex-wrap"></div>');
                data.cursosSinNivel.forEach(curso => {
                    noLevelCoursesContainer.append(`
                        <div class="course-card">
                            <div class="course-consecutive">${curso.consecutivo}</div>
                            <div class="course-name">${curso.nombre}</div>
                        </div>
                    `);
                });
                noLevelCourses.append(noLevelCoursesContainer);
            } else {
                noLevelCourses.append('<p class="text-muted">No hay cursos sin nivel asignados.</p>');
            }

            // Renderizar prerrequisitos
            const prerequisitesList = $("#prerequisites-list");
            prerequisitesList.empty();
            if (data.prerequisitos && data.prerequisitos.length > 0) {
                data.prerequisitos.forEach(prerequisito => {
                    prerequisitesList.append(`<p>${prerequisito.nombre}</p>`);
                });
            } else {
                prerequisitesList.append('<p class="text-muted">No hay prerrequisitos para este programa.</p>');
            }
        }).fail(function () {
            alert("Error al cargar los detalles del programa.");
        });
    }

    // Inicializar
    cargarDetallesPrograma();
});
</script>

</body>
</html>