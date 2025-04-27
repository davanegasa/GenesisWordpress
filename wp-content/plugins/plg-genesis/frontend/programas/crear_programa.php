<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Nuevo Programa</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        .main-container {
            display: flex;
            flex-direction: row;
            gap: 20px;
        }

        .left-panel {
            flex: 3;
        }

        .right-panel {
            flex: 1;
            max-height: 600px;
            overflow-y: auto;
            border: 1px solid var(--gray-light);
            border-radius: 8px;
            padding: 10px;
            background-color: var(--background-color);
            position: sticky;
            top: 20px; /* Ajusta según el margen superior deseado */
        }

        .filter-container {
            margin-bottom: 10px;
        }

        .drag-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .drop-zone {
            border: 2px dashed var(--primary-color);
            border-radius: 8px;
            padding: 10px;
            min-height: 150px;
            background-color: var(--background-color);
            display: grid; /* Usamos grid en lugar de flex */
            grid-template-columns: repeat(auto-fit, minmax(100px, 1fr)); /* Crea columnas dinámicas */
            gap: 10px; /* Espaciado entre las tarjetas */
            align-items: center;
            justify-items: center;
            text-align: center;
            transition: background-color 0.3s ease;
        }

        .course-card {
            padding: 10px;
            background-color: var(--white);
            border: 1px solid var(--gray-light);
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            cursor: grab;
        }

        .course-card.dragging {
            opacity: 0.5;
        }

        .level-container {
            margin-top: 20px;
        }

        .level-title {
            color: var(--primary-color);
            font-size: 1.25rem;
            margin-bottom: 10px;
        }

        .btn-save {
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h1 class="text-center text-primary mb-4">Crear Nuevo Programa</h1>

    <div class="main-container">
        <!-- Panel izquierdo -->
        <div class="left-panel">
            <form id="formPrograma">
                <div class="mb-3">
                    <label for="nombrePrograma" class="form-label">Nombre del Programa</label>
                    <input type="text" id="nombrePrograma" class="form-control" placeholder="Ingrese el nombre del programa" required>
                </div>
                <div class="mb-3">
                    <label for="descripcionPrograma" class="form-label">Descripción del Programa</label>
                    <textarea id="descripcionPrograma" class="form-control" rows="3" placeholder="Ingrese una descripción para el programa"></textarea>
                </div>
            </form>

            <!-- Zona de niveles y cursos sin nivel -->
            <div id="levels-container" class="level-container">
                <!-- Niveles agregados dinámicamente -->
            </div>
            <div id="no-level-container" class="drop-zone mt-3">
                <strong>Cursos sin Nivel</strong>
            </div>

            <div class="mt-4">
                <div class="input-group">
                    <input type="text" id="nombreNivel" class="form-control" placeholder="Nombre del nuevo nivel">
                    <button class="btn btn-primary" id="btnAgregarNivel">Agregar Nivel</button>
                </div>
            </div>

            <!-- Botón para guardar programa -->
            <button class="btn btn-success btn-save mt-3" id="btnSave">Guardar Programa</button>
        </div>

        <!-- Panel derecho -->
        <div class="right-panel">
            <div class="filter-container">
                <input type="text" id="filterCursos" class="form-control" placeholder="Filtrar cursos...">
            </div>
            <div id="courses-container" class="drag-container">
                <!-- Cursos cargados dinámicamente -->
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    let cursosDisponibles = [];
    let programaEstructura = {
        nombre: '',
        descripcion: '',
        niveles: [],
        cursosSinNivel: [],
        prerequisitos: []
    };

    // Cargar cursos disponibles
    function cargarCursos() {
        $.getJSON('../../backend/cursos/obtener_cursos.php', function (data) {
            cursosDisponibles = data.cursos;
            renderizarCursos();
        }).fail(function () {
            alert("Error al cargar los cursos.");
        });
    }

    // Recalcular y verificar
    function recalcularYVerificar() {
        // Recalcular consecutivos en niveles
        programaEstructura.niveles.forEach(nivel => {
            recalcularConsecutivos(nivel.cursos);
        });

        // Recalcular consecutivos en cursos sin nivel
        recalcularConsecutivos(programaEstructura.cursosSinNivel);

        // Verificar que no haya duplicados en los cursos disponibles
        cursosDisponibles = cursosDisponibles.filter(cursoDisponible => {
            const existeEnNiveles = programaEstructura.niveles.some(nivel => 
                nivel.cursos.some(curso => curso.id == cursoDisponible.id)
            );
            const existeEnSinNivel = programaEstructura.cursosSinNivel.some(curso => curso.id == cursoDisponible.id);
            return !existeEnNiveles && !existeEnSinNivel;
        });

        // Renderizar nuevamente las cajas y cursos disponibles
        renderizarNiveles();
        renderizarCursosSinNivel();
        renderizarCursos();
    }

    // Función para recalcular consecutivos
    function recalcularConsecutivos(cursos) {
        cursos.forEach((curso, index) => {
            curso.consecutivo = index + 1;
        });
    }

    // Agregar nivel
    $("#btnAgregarNivel").on("click", function () {
        const nombreNivel = $("#nombreNivel").val().trim();
        if (!nombreNivel) {
            alert("Ingrese un nombre para el nivel.");
            return;
        }
        programaEstructura.niveles.push({
            nombre_nivel: nombreNivel,
            cursos: []
        });
        renderizarNiveles();
        recalcularYVerificar(); // Recalcular después de agregar nivel
        $("#nombreNivel").val(''); // Limpiar campo
    });

    // Habilitar funcionalidad Drag & Drop
    function habilitarDragAndDrop() {
        $(".course-card").on("dragstart", function (e) {
            $(this).addClass("dragging");
            e.originalEvent.dataTransfer.setData("text/plain", $(this).data("id"));
        });

        $(".course-card").on("dragend", function () {
            $(this).removeClass("dragging");
        });

        $(".drop-zone, #courses-container").on("dragover", function (e) {
            e.preventDefault();
            $(this).addClass("drag-over");
        });

        $(".drop-zone, #courses-container").on("dragleave", function () {
            $(this).removeClass("drag-over");
        });

        $(".drop-zone, #courses-container").on("drop", function (e) {
            e.preventDefault();
            $(this).removeClass("drag-over");

            const cursoId = e.originalEvent.dataTransfer.getData("text/plain");
            const curso = buscarCurso(cursoId);

            if (!curso) return;

            // Eliminar curso de su ubicación actual
            eliminarCursoDeEstructura(cursoId);

            if ($(this).is("#courses-container")) {
                // Retornar a cursos disponibles si se suelta allí
                if (!cursosDisponibles.some(c => c.id == cursoId)) {
                    cursosDisponibles.push(curso);
                }
            } else {
                // Agregar curso a nivel o sin nivel
                const nivelIndex = $(this).data("index");
                if (nivelIndex !== undefined) {
                    programaEstructura.niveles[nivelIndex].cursos.push(curso);
                } else {
                    programaEstructura.cursosSinNivel.push(curso);
                }
            }

            // Ejecutar recalculación y verificación
            recalcularYVerificar();
        });
    }

    // Función para buscar un curso en todas las estructuras
    function buscarCurso(cursoId) {
        return (
            cursosDisponibles.find(c => c.id == cursoId) ||
            programaEstructura.cursosSinNivel.find(c => c.id == cursoId) ||
            programaEstructura.niveles.flatMap(nivel => nivel.cursos).find(c => c.id == cursoId)
        );
    }

    // Función para eliminar un curso de todas las estructuras
    function eliminarCursoDeEstructura(cursoId) {
        cursosDisponibles = cursosDisponibles.filter(c => c.id != cursoId);
        programaEstructura.niveles.forEach(nivel => {
            nivel.cursos = nivel.cursos.filter(c => c.id != cursoId);
        });
        programaEstructura.cursosSinNivel = programaEstructura.cursosSinNivel.filter(c => c.id != cursoId);
    }

    // Renderizar cursos disponibles
    function renderizarCursos() {
        const container = $("#courses-container");
        container.empty();
        cursosDisponibles.forEach(curso => {
            container.append(crearTarjetaCurso(curso));
        });
        habilitarDragAndDrop();
    }

    function renderizarNiveles() {
        const container = $("#levels-container");
        container.empty();
        programaEstructura.niveles.forEach((nivel, index) => {
            container.append(`
                <div class="level">
                    <h3 class="level-title">${nivel.nombre_nivel}</h3>
                    <div class="drop-zone" data-index="${index}">
                        ${nivel.cursos.map(curso => crearTarjetaCurso(curso).prop('outerHTML')).join('')}
                    </div>
                </div>
            `);
        });
        habilitarDragAndDrop();
    }
    
    // Renderizar cursos sin nivel
    function renderizarCursosSinNivel() {
        const container = $("#no-level-container");
        container.empty();
        container.append(`<strong>Cursos sin Nivel</strong>`);
        programaEstructura.cursosSinNivel.forEach(curso => {
            container.append(crearTarjetaCurso(curso));
        });
        habilitarDragAndDrop();
    }

    // Crear tarjeta de curso
    function crearTarjetaCurso(curso) {
        return $(`
            <div class="course-card" draggable="true" data-id="${curso.id}">
                ${curso.nombre} <small>(#${curso.consecutivo || '-'})</small>
            </div>
        `);
    }
    
        // Guardar programa con consecutivos globales
    $("#btnSave").on("click", function () {
        programaEstructura.nombre = $("#nombrePrograma").val().trim();
        programaEstructura.descripcion = $("#descripcionPrograma").val().trim();
    
        if (!programaEstructura.nombre) {
            alert("El nombre del programa es obligatorio.");
            return;
        }
    
        // Calcular consecutivos globales
        let globalConsecutivo = 1;
    
        programaEstructura.niveles.forEach(nivel => {
            nivel.cursos.forEach(curso => {
                curso.consecutivo = globalConsecutivo++;
            });
        });
    
        programaEstructura.cursosSinNivel.forEach(curso => {
            curso.consecutivo = globalConsecutivo++;
        });
    
        // Enviar datos al backend
        $.ajax({
            url: '../../backend/programas/procesar_programa.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(programaEstructura),
            success: function (response) {
                alert(response.message);
                if (response.success) location.reload();
            },
            error: function () {
                alert("Error al guardar el programa.");
            }
        });
    });
    
    $('#filterCursos').on('keyup', function () {
        const filtro = $(this).val().toLowerCase();
    
        // Filtrar cursos por nombre o consecutivo
        const cursosFiltrados = cursosDisponibles.filter(curso =>
            curso.nombre.toLowerCase().includes(filtro) ||
            String(curso.consecutivo).includes(filtro)
        );
    
        // Renderizar los cursos filtrados
        const container = $("#courses-container");
        container.empty();
    
        cursosFiltrados.forEach(curso => {
            container.append(crearTarjetaCurso(curso));
        });
    
        habilitarDragAndDrop();
    });

    cargarCursos();
});
</script>

</body>
</html>