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
</head>
<body>
<div class="container mt-5">
    <h1 class="text-primary mb-4 text-center">Listado de Cursos</h1>
    <div class="accordion" id="accordionCursos">
        <!-- Niveles y cursos se generan din¨¢micamente -->
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const accordionCursos = document.getElementById('accordionCursos');

        // Llamar al servicio para obtener los cursos
        fetch('<?php echo plugin_dir_url(__FILE__); ?>../../backend/cursos/listar_cursos.php')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.niveles) {
                    const niveles = data.niveles;

                    Object.keys(niveles).forEach(nivelId => {
                        const nivel = niveles[nivelId];
                        const cursos = nivel.cursos;

                        // Crear acorde¨®n para cada nivel
                        const nivelPanel = `
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading-${nivelId}">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapse-${nivelId}" aria-expanded="false"
                                            aria-controls="collapse-${nivelId}">
                                        ${nivel.nombre}
                                    </button>
                                </h2>
                                <div id="collapse-${nivelId}" class="accordion-collapse collapse"
                                     aria-labelledby="heading-${nivelId}" data-bs-parent="#accordionCursos">
                                    <div class="accordion-body">
                                        <div class="row g-4">
                                            ${cursos.map(curso => `
                                                <div class="col-md-4 col-sm-6">
                                                    <div class="card shadow-sm">
                                                        <div class="card-body">
                                                            <h5 class="card-title text-primary">
                                                                ${curso.nombre}
                                                            </h5>
                                                            <p class="card-text">
                                                                ${curso.descripcion}
                                                            </p>
                                                        </div>
                                                        <div class="card-footer d-flex justify-content-around">
                                                            <button class="btn btn-primary btn-sm">Editar</button>
                                                            <button class="btn btn-danger btn-sm">Eliminar</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            `).join('')}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        accordionCursos.insertAdjacentHTML('beforeend', nivelPanel);
                    });
                } else {
                    accordionCursos.innerHTML = '<p class="text-center text-muted">No se encontraron cursos disponibles.</p>';
                }
            })
            .catch(error => {
                accordionCursos.innerHTML = '<p class="text-center text-danger">Error al cargar los cursos.</p>';
                console.error('Error al obtener los cursos:', error);
            });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>