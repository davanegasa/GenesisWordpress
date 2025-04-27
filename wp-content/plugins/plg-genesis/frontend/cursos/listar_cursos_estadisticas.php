<?php
require_once(__DIR__ . '/../../../../../wp-load.php'); // Carga el entorno de WordPress
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listar Cursos</title>
    <link rel="stylesheet" href="<?php echo plugins_url('../../assets/css/styles.css', __FILE__); ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* Tarjetas */
        .curso-descripcion {
            font-size: 0.9rem; /* Reduce el tamaño */
            font-style: italic; /* Hace que la descripción se vea más elegante */
            color: #6c757d; /* Gris tenue */
            max-width: 90%; /* Evita que se desborde */
            text-align: center;
            margin-top: -5px; /* Reduce el espacio con el título */
            white-space: normal; /* Permite que el texto baje */
            overflow: hidden; /* Evita que se vea muy largo */
            text-overflow: ellipsis; /* Agrega "..." si el texto es muy largo */
            display: -webkit-box;
            -webkit-line-clamp: 2; /* Máximo 2 líneas */
            -webkit-box-orient: vertical;
        }
        
        #cursosContainer {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
            align-items: stretch; /* Esto hará que todas las cards tengan la misma altura */
        }

        .col {
            flex: 1 1 calc(25% - 15px);
            max-width: calc(25% - 15px);
        }

        @media (max-width: 1024px) {
            .col {
                flex: 1 1 calc(33.333% - 15px);
                max-width: calc(33.333% - 15px);
            }
        }

        @media (max-width: 768px) {
            .col {
                flex: 1 1 calc(50% - 15px);
                max-width: calc(50% - 15px);
            }
        }

        @media (max-width: 576px) {
            .col {
                flex: 1 1 100%;
                max-width: 100%;
            }
        }

        .card-body {
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: stretch; /* Permite que todo se acomode sin restricciones */
            flex-grow: 1; /* Hace que el contenido de la tarjeta ocupe todo el espacio */
        }
        
        .card-body p {
            margin-bottom: 10px; /* Espaciado extra para mayor claridad */
        }
        
        /* Contenedor que agrupa la gráfica y las convenciones */
        .chart-wrapper {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px; /* Espacio entre la gráfica y los datos */
            margin-bottom: 10px;
        }
        
        .card-title {
            color: #0d6efd; /* Usa el azul Bootstrap o el color principal de tu página */
            font-size: 1.1rem;
            font-weight: bold;
            text-align: center;
            margin-bottom: 5px;
            word-wrap: break-word; /* Permite que el texto se divida en varias líneas */
            white-space: normal; /* Asegura que el texto baje en vez de salir del cuadro */
            max-width: 90%; /* Evita que el título se expanda demasiado */
        }
        
        .curso-descripcion {
            font-size: 0.9rem;
            font-style: italic;
            color: #6c757d;
            max-width: 90%;
            text-align: center;
            margin-top: 0px; /* Reduce el espacio superior */
        }
        
        .chart-container {
            width: 70px;
            height: 70px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Convenciones al lado de la gráfica */
        .conventions {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            font-size: 0.8rem; /* Reducimos el tamaño */
        }
        
        .conventions p {
            margin-bottom: 3px;
        }
        
        .text-start {
            font-size: 0.85rem; /* Reducimos un poco el tamaño de los datos */
        }
        
        p strong {
            font-size: 0.9rem; /* Hacemos los títulos de los datos un poco más pequeños */
        }

        .card {
            background-color: #f8f9fa; /* Color de fondo más claro, acorde con Bootstrap */
            border: 1px solid #d1d1d1; /* Color del borde más sutil */
            border-radius: 8px; /* Bordes más redondeados */
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Sombras más suaves */
            height: auto; /* Permite que la tarjeta crezca según el contenido */
            display: flex;
            flex-direction: column;
            justify-content: space-between; /* No fuerza el espacio entre elementos */
            align-items: stretch; /* Permite que todos los elementos se acomoden bien */
            padding: 15px;
            min-height: 280px; /* Asegura que todas las tarjetas tengan un tamaño uniforme */
        }

        .chart-container {
            position: relative;
            width: 80px;
            height: 80px;
            margin-right: 10px;
        }
        
        .dot {
            width: 8px;
            height: 8px;
            display: inline-block;
            border-radius: 50%;
            margin-right: 5px;
        }
        
        .card-footer {
            margin-top: auto; /* Empuja los botones hacia abajo */
            padding-top: 10px;
        }
        
        .btn-primary {
            background-color: #0d6efd; /* Azul Bootstrap */
            border-color: #0d6efd;
        }
        .btn-primary:hover {
            background-color: #0b5ed7;
        }
        .btn-danger {
            background-color: #dc3545; /* Rojo Bootstrap */
            border-color: #dc3545;
        }
        .btn-danger:hover {
            background-color: #bb2d3b;
        }
    </style>
</head>
<body>

    <div class="container-fluid mt-4 px-4" style="max-width: 1200px;">
        <h1 class="text-primary mb-4 text-center">Listado de Cursos</h1>
        
        <div class="filters-container">
            <!-- Campo de búsqueda -->
            <input type="text" id="searchInput" class="form-control" placeholder="🔍 Buscar curso...">
        
            <!-- Selector de ordenamiento -->
            <select id="sortSelect" class="form-control">
                <option value="nombre">Ordenar por Nombre</option>
                <option value="fecha">Ordenar por Último Registro</option>
                <option value="calificacion">Ordenar por Calificación</option>
                <option value="inscritos">Ordenar por Inscritos</option>
            </select>
        </div>
    
        <div id="cursosContainer" class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 g-3"></div>
    </div>

    <script>
        document.getElementById('sortSelect').addEventListener('change', ordenarCursos);
        
        function ordenarCursos() {
            let criterio = document.getElementById('sortSelect').value;
            let cursosContainer = document.getElementById('cursosContainer');
        
            // Obtener las tarjetas de los cursos correctamente
            let cursos = Array.from(document.querySelectorAll('#cursosContainer > div'));
        
            cursos.sort((a, b) => {
                let valorA, valorB;
        
                if (criterio === "nombre") {
                    valorA = a.querySelector('.card-title').textContent.toLowerCase();
                    valorB = b.querySelector('.card-title').textContent.toLowerCase();
                    return valorA.localeCompare(valorB);
                }
        
                if (criterio === "fecha") {
                    valorA = a.querySelector('.fecha-registro').textContent.trim();
                    valorB = b.querySelector('.fecha-registro').textContent.trim();
                    
                    // Manejar valores "null" para evitar errores
                    if (valorA === "null") valorA = "1900-01-01";  
                    if (valorB === "null") valorB = "1900-01-01";  
        
                    return new Date(valorB) - new Date(valorA); // Ordena de más reciente a más antiguo
                }
        
                if (criterio === "calificacion") {
                    valorA = parseFloat(a.querySelector('.calificacion').textContent) || 0;
                    valorB = parseFloat(b.querySelector('.calificacion').textContent) || 0;
                    return valorB - valorA; // Mayor calificación primero
                }
        
                if (criterio === "inscritos") {
                    valorA = parseInt(a.querySelector('.inscritos').textContent) || 0;
                    valorB = parseInt(b.querySelector('.inscritos').textContent) || 0;
                    return valorB - valorA; // Más inscritos primero
                }
        
                return 0;
            });
        
            // Limpiar y reinsertar los cursos ordenados
            cursosContainer.innerHTML = "";
            cursos.forEach(curso => cursosContainer.appendChild(curso));
        }
    
        document.getElementById('searchInput').addEventListener('input', function () {
            let searchTerm = this.value.toLowerCase();
            let cursos = document.querySelectorAll('#cursosContainer > div'); // Seleccionar correctamente
        
            cursos.forEach(curso => {
                let nombre = curso.querySelector('.card-title').textContent.toLowerCase();
                let descripcion = curso.querySelector('.curso-descripcion') 
                    ? curso.querySelector('.curso-descripcion').textContent.toLowerCase() 
                    : '';
        
                if (nombre.includes(searchTerm) || descripcion.includes(searchTerm)) {
                    curso.style.display = "block"; // Mostrar si coincide
                } else {
                    curso.style.display = "none"; // Ocultar si no coincide
                }
            });
        });
    
        document.addEventListener('DOMContentLoaded', async function () {
            const cursosContainer = document.getElementById('cursosContainer');
    
            try {
                let response = await fetch('<?php echo plugin_dir_url(__FILE__); ?>../../backend/cursos/estadisticas_curso.php');
                let data = await response.json();
    
                if (data.success && data.cursos.length > 0) {
                    data.cursos.forEach((curso, index) => {
                        let calificacion = parseFloat(curso.promedio_calificacion);
                        let finalizacion = parseFloat(curso.porcentaje_finalizacion_exitosa);
                        
                        // Asegurar que no haya valores NaN o nulos
                        if (isNaN(calificacion) || calificacion < 0) calificacion = 0;
                        if (isNaN(finalizacion) || finalizacion < 0) finalizacion = 0;
                        
                        let cursoCard = `
                            <div class="col-lg-4 col-md-6 col-sm-12">
                                <div class="card shadow-sm p-3 h-100">
                                    <div class="card-body">
                                        <h2 class="card-title text-primary text-center">${curso.curso_nombre}</h2>
                                        <p class="card-subtitle text-muted text-center curso-descripcion">${curso.curso_descripcion}</p>
                                        
                                        <div class="chart-wrapper">
                                            <!-- Gráfica -->
                                            <div class="chart-container">
                                                <canvas id="cursoChart-${index}" width="70" height="70"></canvas>
                                            </div>
                                        
                                            <!-- Convenciones al lado derecho -->
                                            <div class="conventions">
                                                <p class="mb-1 d-flex align-items-center">
                                                    <span class="dot" style="background-color: #007bff;"></span>
                                                    <strong>% Notas:</strong> <span class="calificacion">${parseFloat(calificacion).toFixed(2)}</span>%
                                                </p>
                                                <p class="mb-0 d-flex align-items-center">
                                                    <span class="dot" style="background-color: #28a745;"></span>
                                                    <strong>% Aprobación:</strong> ${parseFloat(finalizacion).toFixed(3)}%
                                                </p>
                                            </div>
                                        </div>
    
                                        <p class="mt-3"><strong>📅 Último Registro:</strong> <span class="fecha-registro">${curso.ultima_fecha_registro}</span></p>
                                        <p><strong>👥 Total realizados:</strong> <span class="inscritos">${curso.total_inscritos}</span></p>
                                    </div>
                                    <div class="card-footer d-flex justify-content-around">
                                        <button class="btn btn-primary btn-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#modalEditarCurso"
                                            onclick="cargarDatosCurso(${curso.curso_id}, '${curso.curso_nombre}', '${curso.curso_descripcion}')">
                                            Editar
                                        </button>
                                        <button class="btn btn-danger btn-sm">Eliminar</button>
                                    </div>
                                </div>
                            </div>
                        `;
                        cursosContainer.insertAdjacentHTML('beforeend', cursoCard);
    
                        setTimeout(() => {
                            crearGraficoConcéntrico(`cursoChart-${index}`, curso.promedio_calificacion, curso.porcentaje_finalizacion_exitosa);
                        }, 100);
                    });
                } else {
                    cursosContainer.innerHTML = '<p class="text-center text-muted">No se encontraron cursos disponibles.</p>';
                }
            } catch (error) {
                cursosContainer.innerHTML = '<p class="text-center text-danger">Error al cargar los cursos.</p>';
                console.error('Error al obtener los cursos:', error);
            }
        });
    
        function crearGraficoConcéntrico(idCanvas, calificacion, finalizacion) {
            let ctx = document.getElementById(idCanvas).getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    datasets: [
                        {
                            data: [finalizacion, 100 - finalizacion],
                            backgroundColor: ['#28a745', '#e9ecef'],
                            borderWidth: 1
                        },
                        {
                            data: [calificacion, 100 - calificacion],
                            backgroundColor: ['#007bff', '#e9ecef'],
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    cutout: '60%',
                    plugins: {
                        tooltip: { enabled: false },
                        legend: { display: false }
                    }
                }
            });
        }
        
        function cargarDatosCurso(id, nombre, descripcion) {
            document.getElementById('cursoId').value = id;
            document.getElementById('cursoNombre').value = nombre;
            document.getElementById('cursoDescripcion').value = descripcion;
        }
        
        function guardarEdicion() {
            let id = document.getElementById('cursoId').value;
            let nombre = document.getElementById('cursoNombre').value;
            let descripcion = document.getElementById('cursoDescripcion').value;
            let nivel_id = 1; // Puedes obtenerlo dinámicamente si es necesario
        
            fetch('../../backend/cursos/editar_curso.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id: id,
                    nombre: nombre,
                    nivel_id: nivel_id,
                    descripcion: descripcion
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Curso actualizado exitosamente');
        
                    // Ocultar el modal
                    let modal = bootstrap.Modal.getInstance(document.getElementById('modalEditarCurso'));
                    modal.hide();
        
                    // Recargar la lista de cursos después de un breve retraso
                    setTimeout(() => {
                        location.reload();
                    }, 500);
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => console.error('Error al actualizar:', error));
        }
    </script>
    
    <div class="modal fade" id="modalEditarCurso" tabindex="-1" aria-labelledby="modalEditarCursoLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEditarCursoLabel">Editar Curso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarCurso">
                        <input type="hidden" id="cursoId">
                        <div class="mb-3">
                            <label for="cursoNombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="cursoNombre">
                        </div>
                        <div class="mb-3">
                            <label for="cursoDescripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="cursoDescripcion"></textarea>
                        </div>
                        <button type="button" class="btn btn-success" onclick="guardarEdicion()">Guardar Cambios</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>