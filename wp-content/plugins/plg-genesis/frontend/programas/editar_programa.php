<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Programa</title>
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
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            border: 2px dashed var(--primary-color);
            border-radius: 8px;
            padding: 20px;
            min-height: 150px;
            background-color: var(--background-color);
            align-items: flex-start;
            justify-content: flex-start; /* Puedes cambiar a center si prefieres centrar */
            text-align: left;
            transition: background-color 0.3s ease;
        }
        
        .course-card {
            flex: 1 1 calc(33.333% - 10px); /* Ajusta el tamaño: ocupa un tercio del espacio */
            max-width: calc(33.333% - 10px); /* Máximo tamaño del curso */
            box-sizing: border-box;
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
    <h1 class="text-center text-primary mb-4">Editar Programa</h1>

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
    const programaId = new URLSearchParams(window.location.search).get('programa_id');
    let cursosDisponibles = [];
    let programaEstructura = {
        nombre: '',
        descripcion: '',
        niveles: [],
        cursosSinNivel: []
    };

    if (programaId) {
        cargarPrograma(programaId);
    } else {
        alert("No se especificó un programa para editar.");
        window.location.href = "listar_programas.php";
    }
    
    function actualizarConsecutivosPorNivel() {
        programaEstructura.niveles.forEach((nivel, nivelIndex) => {
            nivel.cursos.forEach((curso, cursoIndex) => {
                curso.consecutivo = cursoIndex + 1; // Consecutivo local dentro del nivel
            });
        });
    }
    
    function actualizarConsecutivosGlobales() {
        let consecutivoGlobal = 1;
    
        programaEstructura.niveles.forEach(nivel => {
            nivel.cursos.forEach(curso => {
                curso.consecutivoGlobal = consecutivoGlobal++; // Consecutivo global
            });
        });
    
        programaEstructura.cursosSinNivel.forEach(curso => {
            curso.consecutivoGlobal = consecutivoGlobal++; // Cursos sin nivel continúan la secuencia
        });
    }
    
    function actualizarConsecutivos() {
        actualizarConsecutivosPorNivel();
        actualizarConsecutivosGlobales();
    }
    
    function eliminarCursosAsignadosDeDisponibles() {
        // Asegurar que niveles y cursosSinNivel estén definidos como arrays
        const niveles = programaEstructura.niveles || [];
        const cursosSinNivel = programaEstructura.cursosSinNivel || [];
    
        // Obtener IDs de todos los cursos asignados
        const cursosAsignadosIds = [
            ...cursosSinNivel.map(curso => curso.id),
            ...niveles.flatMap(nivel => nivel.cursos.map(curso => curso.id))
        ];
    
        // Filtrar cursos disponibles para excluir los asignados
        cursosDisponibles = cursosDisponibles.filter(curso => !cursosAsignadosIds.includes(curso.id));
    
        // Actualizar la vista de cursos disponibles
        renderizarCursos();
    }

    function cargarPrograma(id) {
        $.getJSON(`../../backend/programas/obtener_programa.php?programa_id=${id}`, function (data) {
            console.log("Datos cargados del programa:", data); // Verifica la respuesta del backend
            programaEstructura = {
                nombre: data.nombre || '',
                descripcion: data.descripcion || '',
                niveles: data.niveles || [],
                cursosSinNivel: data.cursosSinNivel || [],
                prerequisitos: data.prerequisitos || []
            };
            $("#nombrePrograma").val(programaEstructura.nombre);
            $("#descripcionPrograma").val(programaEstructura.descripcion);
            cargarCursos(() => {
                eliminarCursosAsignadosDeDisponibles();
                renderizarNiveles();
                renderizarCursosSinNivel();
            });
        }).fail(function () {
            alert("Error al cargar el programa.");
        });
    }

    // Cargar cursos disponibles
    function cargarCursos(callback) {
        $.getJSON('../../backend/cursos/obtener_cursos.php', function (data) {
            cursosDisponibles = data.cursos;
            renderizarCursos();
            if (callback) callback();
        }).fail(function () {
            alert("Error al cargar los cursos.");
        });
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
                        ${nivel.cursos.map(curso => crearTarjetaCurso(curso).prop("outerHTML")).join("")}
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
            <div class="course-card" draggable="true" data-id="${curso.id}" data-consecutivo="${curso.consecutivo}">
                ${curso.nombre} <small>(#${curso.consecutivo || '-'})</small>
            </div>
        `);
    }

    // Habilitar funcionalidad Drag & Drop
    function habilitarDragAndDrop() {
        // Cursos disponibles - arrastrar
        $(".course-card").on("dragstart", function (e) {
            e.originalEvent.dataTransfer.setData("text/plain", $(this).data("id"));
            $(this).addClass("dragging");
        });

        $(".course-card").on("dragend", function () {
            $(this).removeClass("dragging");
        });

        // Zonas de arrastre - permitir soltar
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

            eliminarCursoDeEstructura(cursoId);

            if ($(this).is("#courses-container")) {
                cursosDisponibles.push(curso);
            } else {
                const nivelIndex = $(this).data("index");
                if (nivelIndex !== undefined) {
                    programaEstructura.niveles[nivelIndex].cursos.push(curso);
                } else {
                    programaEstructura.cursosSinNivel.push(curso);
                }
            }
            
            
            actualizarConsecutivos(); // Recalcula consecutivos después de mover

            renderizarCursos();
            renderizarNiveles();
            renderizarCursosSinNivel();
        });
    }

    // Buscar un curso en todas las estructuras
    function buscarCurso(cursoId) {
        return (
            cursosDisponibles.find(c => c.id == cursoId) ||
            programaEstructura.cursosSinNivel.find(c => c.id == cursoId) ||
            programaEstructura.niveles.flatMap(nivel => nivel.cursos).find(c => c.id == cursoId)
        );
    }

    // Eliminar un curso de todas las estructuras
    function eliminarCursoDeEstructura(cursoId) {
        cursosDisponibles = cursosDisponibles.filter(c => c.id != cursoId);
        programaEstructura.niveles.forEach(nivel => {
            nivel.cursos = nivel.cursos.filter(c => c.id != cursoId);
        });
        programaEstructura.cursosSinNivel = programaEstructura.cursosSinNivel.filter(c => c.id != cursoId);
    }
    
    // Manejar clic en el botón Agregar Nivel
    $("#btnAgregarNivel").on("click", function () {
        const nombreNivel = $("#nombreNivel").val().trim();
        if (!nombreNivel) {
            alert("Por favor, ingresa un nombre para el nivel.");
            return;
        }
    
        // Agregar el nuevo nivel a la estructura del programa
        programaEstructura.niveles.push({
            nombre_nivel: nombreNivel,
            cursos: [] // Inicializar con una lista vacía de cursos
        });
    
        // Limpiar el input
        $("#nombreNivel").val("");
    
        // Volver a renderizar los niveles
        renderizarNiveles();
        actualizarConsecutivos(); // Actualizar después de agregar
    });
    
    function prepararDatosParaEnvio() {
        actualizarConsecutivos(); // Asegúrate de que los consecutivos estén actualizados antes del envío
    
        return {
            programa_id: programaId,
            nombre: $("#nombrePrograma").val(),
            descripcion: $("#descripcionPrograma").val(),
            niveles: programaEstructura.niveles.map(nivel => ({
                nombre_nivel: nivel.nombre_nivel,
                cursos: nivel.cursos.map(curso => ({
                    id: curso.id,
                    consecutivo: curso.consecutivoGlobal // Enviar el consecutivo global
                }))
            })),
            cursosSinNivel: programaEstructura.cursosSinNivel.map(curso => ({
                id: curso.id,
                consecutivo: curso.consecutivoGlobal // Enviar el consecutivo global
            }))
        };
    }

    $('#btnSave').on('click', function (e) {
        e.preventDefault();
    
        // Calcular consecutivos globales antes de enviar
        actualizarConsecutivosGlobales();
    
        // Obtener datos generales del programa
        const programaData = {
            programa_id: programaId,
            nombre: $('#nombrePrograma').val(),
            descripcion: $('#descripcionPrograma').val(),
            niveles: [],
            cursosSinNivel: []
        };
    
        // Recolectar niveles y sus cursos
        $('.level').each(function () {
            const nivelIndex = $(this).find('.drop-zone').data('index');
            const nivelNombre = $(this).find('.level-title').text();
            const cursos = [];
    
            $(this).find('.course-card').each(function () {
                const cursoId = $(this).data('id');
                const consecutivoGlobal = $(this).data('consecutivoGlobal'); // Usar el consecutivo global
                cursos.push({ id: cursoId, consecutivo: consecutivoGlobal });
            });
    
            programaData.niveles.push({
                nombre_nivel: nivelNombre,
                cursos: cursos
            });
        });
    
        // Recolectar cursos sin nivel
        $('#no-level-container .course-card').each(function () {
            const cursoId = $(this).data('id');
            const consecutivoGlobal = $(this).data('consecutivoGlobal'); // Usar el consecutivo global
            programaData.cursosSinNivel.push({ id: cursoId, consecutivo: consecutivoGlobal });
        });
    
        console.log('Datos a enviar:', programaData);
    
        // Enviar datos al servicio
        $.ajax({
            url: '../../backend/programas/actualizar_programa.php',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify(programaData),
            success: function (response) {
                if (response.success) {
                    alert('Programa actualizado exitosamente.');
                    window.location.href = 'listar_programas.php';
                } else {
                    alert(response.error || 'Error al actualizar el programa.');
                }
            },
            error: function () {
                alert('Error en la conexión con el servidor.');
            }
        });
    });
    
    
    // Función para calcular consecutivos globales
    function actualizarConsecutivosGlobales() {
        let consecutivoGlobal = 1;
    
        // Cursos en niveles
        $('.level').each(function () {
            $(this).find('.course-card').each(function () {
                $(this).data('consecutivoGlobal', consecutivoGlobal++);
            });
        });
    
        // Cursos sin nivel
        $('#no-level-container .course-card').each(function () {
            $(this).data('consecutivoGlobal', consecutivoGlobal++);
        });
    }
    
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

    // Inicializar el programa
    cargarCursos();
});
</script>

</body>
</html>