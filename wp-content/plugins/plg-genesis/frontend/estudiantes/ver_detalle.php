<?php
require_once(__DIR__ . '/../../../../../wp-load.php');  // Ajusta la ruta según tu estructura de directorios

// Verificar si el usuario no está autenticado en WordPress
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());  // Redirigir a la página de login de WordPress
    exit;
}

require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

// Verificar si se proporcionó el ID del estudiante
if (isset($_GET['id'])) {
    // Obtener el ID del estudiante desde la URL
    $id_estudiante = $_GET['id'];

    // Verificar si se ha seleccionado un nivel, si no, por defecto será el nivel 1
    $nivel_seleccionado = isset($_GET['nivel']) ? $_GET['nivel'] : 1;

    // Consulta para obtener los detalles del estudiante
    $query_detalle = "SELECT * FROM estudiantes WHERE id = '$id_estudiante'";
    $resultado_detalle = pg_query($conexion, $query_detalle);

    // Consulta para obtener todos los contactos disponibles
    $query_contactos = "SELECT id, nombre, iglesia, code FROM contactos ORDER BY iglesia, code";
    $resultado_contactos = pg_query($conexion, $query_contactos);

    // Consulta para obtener los cursos realizados por el nivel seleccionado
    $query_cursos_nivel = "
        SELECT ec.*, c.descripcion, c.consecutivo, ec.porcentaje, ec.fecha 
        FROM estudiantes_cursos ec 
        INNER JOIN cursos c ON ec.curso_id = c.id 
        WHERE ec.estudiante_id = '$id_estudiante' 
        AND c.nivel_id = '$nivel_seleccionado'";
    $resultado_cursos_nivel = pg_query($conexion, $query_cursos_nivel);

    // Consulta para obtener el curso recomendado (nivel más bajo no completado)
    $query_recomendado = "
        SELECT c.* 
        FROM cursos c
        LEFT JOIN estudiantes_cursos ec ON ec.curso_id = c.id AND ec.estudiante_id = '$id_estudiante'
        WHERE ec.curso_id IS NULL
        ORDER BY c.nivel_id ASC, c.consecutivo ASC
        LIMIT 1";
    $resultado_recomendado = pg_query($conexion, $query_recomendado);

    // Verificar si se encontraron detalles del estudiante
    if ($resultado_detalle && pg_num_rows($resultado_detalle) > 0) {
        // Obtener los detalles del estudiante
        $detalle_estudiante = pg_fetch_assoc($resultado_detalle);
        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Detalle del Estudiante</title>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
            <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" defer></script>
        </head>
        <body>
        <div class="container">
            <!-- Detalles del Estudiante en Formato de Carnet con Foto a la Izquierda -->
            <div class="card mt-4 mx-auto" style="max-width: 450px; border: 1px solid #ddd; border-radius: 10px;">
                <div class="card-body d-flex align-items-center" style="padding: 10px;">
                    <!-- Foto del Estudiante -->
                    <div class="me-3">
                       <img src="https://cdn-icons-png.flaticon.com/512/847/847969.png" alt="Foto por Defecto Animada" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 1px solid #ddd;">'
                    </div>
            
                    <!-- Información del Estudiante -->
                    <div class="text-left">
                        <!-- ID del Estudiante -->
                        <h6 class="card-title mb-2" style="font-weight: bold; font-size: 1.1rem;">ID: <?php echo $detalle_estudiante['id_estudiante']; ?></h6>
                        
                        <!-- Nombre Completo -->
                        <p class="card-text mb-1" style="font-size: 0.9rem;">
                            <?php echo $detalle_estudiante['nombre1'] . ' ' . $detalle_estudiante['nombre2'] . ' ' . $detalle_estudiante['apellido1'] . ' ' . $detalle_estudiante['apellido2']; ?>
                        </p>
                        
                        <!-- Documento de Identidad -->
                        <p class="card-text mb-1" style="font-size: 0.85rem;">
                            <strong>Documento:</strong> <?php echo $detalle_estudiante['doc_identidad']; ?>
                        </p>
                        
                        <!-- Celular -->
                        <p class="card-text mb-1" style="font-size: 0.85rem;">
                            <strong>Celular:</strong> <?php echo $detalle_estudiante['celular']; ?>
                        </p>
                        
                        <!-- Correo Electrónico -->
                        <p class="card-text mb-1" style="font-size: 0.85rem;">
                            <strong>Email:</strong> <?php echo $detalle_estudiante['email']; ?>
                        </p>
                        
                        <!-- Ciudad -->
                        <p class="card-text mb-1" style="font-size: 0.85rem;">
                            <strong>Ciudad:</strong> <?php echo $detalle_estudiante['ciudad']; ?>
                        </p>
                        
                        <!-- Iglesia -->
                        <p class="card-text mb-1" style="font-size: 0.85rem;">
                            <strong>Iglesia:</strong> <?php echo $detalle_estudiante['iglesia']; ?>
                        </p>
                        <!-- Estado Civil -->
                        <p class="card-text mb-1" style="font-size: 0.85rem;">
                            <strong>Estado Civil:</strong> <?php echo $detalle_estudiante['estado_civil'] ?: 'No especificado'; ?>
                        </p>
                        <!-- Escolaridad -->
                        <p class="card-text mb-1" style="font-size: 0.85rem;">
                            <strong>Escolaridad:</strong> <?php echo $detalle_estudiante['escolaridad'] ?: 'No especificado'; ?>
                        </p>
                        <!-- Ocupación -->
                        <p class="card-text mb-1" style="font-size: 0.85rem;">
                            <strong>Ocupación:</strong> <?php echo $detalle_estudiante['ocupacion'] ?: 'No especificado'; ?>
                        </p>
                        <button id="edit-student-btn" class="btn btn-primary mt-3">Editar Información</button>
                    </div>
                </div>
            </div>
            
            <!-- Formulario desplegable para editar información -->
            <div id="edit-student-form" class="card mt-4" style="display: none; border: none; box-shadow: none;">
                <div class="card-body">
                    <h5 class="card-title text-center mb-4" style="font-weight: bold; font-size: 1.2rem;">Editar Información Completa</h5>
                    
                    <form id="update-student-form">
                        <!-- Campo oculto con el ID del estudiante -->
                        <input type="hidden" name="id_estudiante" value="<?php echo $detalle_estudiante['id']; ?>">
            
                    <!-- Acordeón -->
                    <div class="accordion" id="accordionStudentInfo">
                    
                        <!-- Información Personal -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingPersonalInfo">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePersonalInfo" aria-expanded="true" aria-controls="collapsePersonalInfo">
                                    Información Personal
                                </button>
                            </h2>
                            <div id="collapsePersonalInfo" class="accordion-collapse collapse show" aria-labelledby="headingPersonalInfo" data-bs-parent="#accordionStudentInfo">
                                <div class="accordion-body">
                                    <div class="form-group mb-3">
                                        <label for="nombre1" class="form-label">Primer Nombre</label>
                                        <input type="text" class="form-control" id="nombre1" name="nombre1" value="<?php echo $detalle_estudiante['nombre1']; ?>">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="nombre2" class="form-label">Segundo Nombre</label>
                                        <input type="text" class="form-control" id="nombre2" name="nombre2" value="<?php echo $detalle_estudiante['nombre2']; ?>">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="apellido1" class="form-label">Primer Apellido</label>
                                        <input type="text" class="form-control" id="apellido1" name="apellido1" value="<?php echo $detalle_estudiante['apellido1']; ?>">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="apellido2" class="form-label">Segundo Apellido</label>
                                        <input type="text" class="form-control" id="apellido2" name="apellido2" value="<?php echo $detalle_estudiante['apellido2']; ?>">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="doc_identidad" class="form-label">Documento de Identidad</label>
                                        <input type="text" class="form-control" id="doc_identidad" name="doc_identidad" value="<?php echo $detalle_estudiante['doc_identidad']; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    
                        <!-- Contacto -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingContactInfo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseContactInfo" aria-expanded="false" aria-controls="collapseContactInfo">
                                    Información de Contacto
                                </button>
                            </h2>
                            <div id="collapseContactInfo" class="accordion-collapse collapse" aria-labelledby="headingContactInfo" data-bs-parent="#accordionStudentInfo">
                                <div class="accordion-body">
                                    <div class="form-group mb-3">
                                        <label for="email" class="form-label">Correo Electrónico</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo $detalle_estudiante['email']; ?>">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="celular" class="form-label">Celular</label>
                                        <input type="text" class="form-control" id="celular" name="celular" value="<?php echo $detalle_estudiante['celular']; ?>">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="direccion" class="form-label">Dirección</label>
                                        <input type="text" class="form-control" id="direccion" name="direccion" value="<?php echo $detalle_estudiante['direccion']; ?>">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="ciudad" class="form-label">Ciudad</label>
                                        <input type="text" class="form-control" id="ciudad" name="ciudad" value="<?php echo $detalle_estudiante['ciudad']; ?>">
                                    </div>
                                </div>
                            </div>
                        </div>
                    
                        <!-- Información Adicional -->
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="headingAdditionalInfo">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAdditionalInfo" aria-expanded="false" aria-controls="collapseAdditionalInfo">
                                    Información Adicional
                                </button>
                            </h2>
                            <div id="collapseAdditionalInfo" class="accordion-collapse collapse" aria-labelledby="headingAdditionalInfo" data-bs-parent="#accordionStudentInfo">
                                <div class="accordion-body">
                                    <div class="form-group mb-3">
                                        <label for="iglesia" class="form-label">Iglesia</label>
                                        <input type="text" class="form-control" id="iglesia" name="iglesia" value="<?php echo $detalle_estudiante['iglesia']; ?>">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="estado_civil" class="form-label">Estado Civil</label>
                                        <select class="form-select" id="estado_civil" name="estado_civil">
                                            <option value="">Seleccione estado civil</option>
                                            <option value="Soltero" <?php echo $detalle_estudiante['estado_civil'] == 'Soltero' ? 'selected' : ''; ?>>Soltero/a</option>
                                            <option value="Casado" <?php echo $detalle_estudiante['estado_civil'] == 'Casado' ? 'selected' : ''; ?>>Casado/a</option>
                                            <option value="Divorciado" <?php echo $detalle_estudiante['estado_civil'] == 'Divorciado' ? 'selected' : ''; ?>>Divorciado/a</option>
                                            <option value="Viudo" <?php echo $detalle_estudiante['estado_civil'] == 'Viudo' ? 'selected' : ''; ?>>Viudo/a</option>
                                            <option value="Union libre" <?php echo $detalle_estudiante['estado_civil'] == 'Union libre' ? 'selected' : ''; ?>>Unión libre</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="escolaridad" class="form-label">Escolaridad</label>
                                        <select class="form-select" id="escolaridad" name="escolaridad">
                                            <option value="">Seleccione nivel de escolaridad</option>
                                            <option value="Primaria" <?php echo $detalle_estudiante['escolaridad'] == 'Primaria' ? 'selected' : ''; ?>>Primaria</option>
                                            <option value="Secundaria" <?php echo $detalle_estudiante['escolaridad'] == 'Secundaria' ? 'selected' : ''; ?>>Secundaria</option>
                                            <option value="Técnico" <?php echo $detalle_estudiante['escolaridad'] == 'Técnico' ? 'selected' : ''; ?>>Técnico</option>
                                            <option value="Tecnólogo" <?php echo $detalle_estudiante['escolaridad'] == 'Tecnólogo' ? 'selected' : ''; ?>>Tecnólogo</option>
                                            <option value="Universitario" <?php echo $detalle_estudiante['escolaridad'] == 'Universitario' ? 'selected' : ''; ?>>Universitario</option>
                                            <option value="Postgrado" <?php echo $detalle_estudiante['escolaridad'] == 'Postgrado' ? 'selected' : ''; ?>>Postgrado</option>
                                            <option value="Ninguno" <?php echo $detalle_estudiante['escolaridad'] == 'Ninguno' ? 'selected' : ''; ?>>Ninguno</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="ocupacion" class="form-label">Ocupación</label>
                                        <input type="text" class="form-control" id="ocupacion" name="ocupacion" value="<?php echo $detalle_estudiante['ocupacion']; ?>">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                                        <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo $detalle_estudiante['fecha_nacimiento']; ?>" readonly>
                                        <small class="form-text text-muted">Este campo no puede ser modificado.</small>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="genero" class="form-label">Género</label>
                                        <select class="form-select" id="genero" name="genero" disabled>
                                            <option value="M" <?php if ($detalle_estudiante['genero'] === 'M') echo 'selected'; ?>>Masculino</option>
                                            <option value="F" <?php if ($detalle_estudiante['genero'] === 'F') echo 'selected'; ?>>Femenino</option>
                                        </select>
                                        <small class="form-text text-muted">Este campo no puede ser modificado.</small>
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="id_contacto" class="form-label">Contacto</label>
                                        <select class="form-select" id="id_contacto" name="id_contacto">
                                            <?php
                                            if ($resultado_contactos && pg_num_rows($resultado_contactos) > 0) {
                                                while ($contacto = pg_fetch_assoc($resultado_contactos)) {
                                                    $selected = ($contacto['id'] == $detalle_estudiante['id_contacto']) ? 'selected' : '';
                                                    echo '<option value="' . $contacto['id'] . '" ' . $selected . '>' . 
                                                         $contacto['code'] . ' - ' . $contacto['iglesia'] . '</option>';
                                                }
                                            }
                                            ?>
                                        </select>
                                        <small class="form-text text-muted">Seleccione el contacto al que desea asignar este estudiante.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            
                        <!-- Botones -->
                        <div class="d-flex justify-content-between mt-4">
                            <button type="submit" class="btn btn-success w-100 me-2">Guardar Cambios</button>
                            <button type="button" id="cancel-edit-btn" class="btn btn-secondary w-100">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div id="cursos_view" style="width: 100%">
                <!-- Selector para cursos por nivel -->
                <form method="GET" action="">
                    <input type="hidden" name="id" value="<?php echo $id_estudiante; ?>">
                    <label for="nivel">Seleccionar Nivel:</label>
                    <select name="nivel" id="nivel" class="form-select" onchange="this.form.submit()">
                        <option value="1" <?php if (!isset($_GET['nivel']) || $_GET['nivel'] == 1) echo 'selected'; ?>>Nivel 1</option>
                        <option value="2" <?php if (isset($_GET['nivel']) && $_GET['nivel'] == 2) echo 'selected'; ?>>Nivel 2</option>
                        <option value="3" <?php if (isset($_GET['nivel']) && $_GET['nivel'] == 3) echo 'selected'; ?>>Nivel 3</option>
                        <option value="4" <?php if (isset($_GET['nivel']) && $_GET['nivel'] == 4) echo 'selected'; ?>>Nivel 4</option>
                        <option value="5" <?php if (isset($_GET['nivel']) && $_GET['nivel'] == 5) echo 'selected'; ?>>Nivel 5</option>
                    </select>
                </form>
    
                <!-- Cursos Realizados por Nivel -->
                <div class="card mt-4">
                    <div class="card-body">
                        <h5 class="card-title">Cursos Realizados en el Nivel <?php echo isset($_GET['nivel']) ? $_GET['nivel'] : ''; ?></h5>
                        <?php
                        if (isset($resultado_cursos_nivel) && pg_num_rows($resultado_cursos_nivel) > 0) {
                            echo '<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-1">'; // Reduce el espacio entre columnas y filas
                            while ($curso_nivel = pg_fetch_assoc($resultado_cursos_nivel)) {
                                ?>
                                <div class="col">
                                    <div class="card position-relative" style="border: 1px solid #ddd; border-radius: 5px; padding: 5px; margin: 0;">
                                        <div class="card-body p-1" style="text-align: left;">
                                            <!-- Mostrar el número consecutivo en la esquina superior izquierda con ancho fijo -->
                                            <span class="position-absolute top-0 start-0 d-flex align-items-center justify-content-center bg-primary text-white rounded" 
                                                  style="font-size: 0.7rem; width: 24px; height: 24px; margin: 5px;">
                                                <?php echo str_pad($curso_nivel['consecutivo'], 3, ' ', STR_PAD_LEFT); ?>
                                            </span>
                                            
                                            <!-- Mostrar la descripción del curso -->
                                            <h6 class="card-title mb-1" style="font-size: 1rem; margin-left: 35px;">
                                                <?php echo $curso_nivel['descripcion']; ?>
                                            </h6>
                                            <p class="card-text mb-1" style="font-size: 0.8rem; line-height: 1.2;">
                                                <strong>Porcentaje:</strong> <?php echo $curso_nivel['porcentaje']; ?>%
                                            </p>
                                            <p class="card-text mb-0" style="font-size: 0.8rem; line-height: 1.2;">
                                                <strong>Fecha:</strong> <?php echo $curso_nivel['fecha']; ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                            echo '</div>'; // Cierra la fila de tarjetas
                        } else {
                            echo '<p>No se encontraron cursos en este nivel.</p>';
                        }
                        ?>
                    </div>
                </div>
    
                <!-- Curso Recomendados y ultimo curso -->
                <div class="container mt-4">
                    <div class="row">
                        <!-- Sección Curso Recomendado -->
                        <div class="col-md-6 mb-3">
                            <div class="card" style="border: 1px solid #ddd; border-radius: 10px;">
                                <div class="card-body">
                                    <h5 class="card-title text-center" style="font-weight: bold; font-size: 1.2rem; color: #007bff;">Curso Recomendado</h5>
                                    <?php
                                    if ($resultado_recomendado && pg_num_rows($resultado_recomendado) > 0) {
                                        $curso_recomendado = pg_fetch_assoc($resultado_recomendado);
                                        ?>
                                        <div class="text-center">
                                            <p class="mb-1" style="font-size: 1rem;"><strong>Descripción:</strong> <?php echo $curso_recomendado['nombre']; ?></p>
                                            <p class="mb-1" style="font-size: 0.95rem;"><strong>Nivel:</strong> <?php echo $curso_recomendado['nivel_id']; ?></p>
                                        </div>
                                        <?php
                                    } else {
                                        echo '<p class="text-center" style="font-size: 0.95rem; color: #dc3545;">No hay más cursos recomendados.</p>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                
                        <!-- Sección Último Curso Entregado -->
                        <div class="col-md-6 mb-3">
                            <div class="card" style="border: 1px solid #ddd; border-radius: 10px;">
                                <div class="card-body">
                                    <h5 class="card-title text-center" style="font-weight: bold; font-size: 1.2rem; color: #28a745;">Último Curso Entregado</h5>
                                    <?php
                                    // Consulta para obtener el último curso entregado
                                    $query_ultimo_curso = "
                                        SELECT c.nombre, ec.fecha 
                                        FROM estudiantes_cursos ec
                                        INNER JOIN cursos c ON ec.curso_id = c.id 
                                        WHERE ec.estudiante_id = '$id_estudiante'
                                        ORDER BY ec.fecha DESC 
                                        LIMIT 1";
                                    $resultado_ultimo_curso = pg_query($conexion, $query_ultimo_curso);
                                    $ultimo_curso = pg_fetch_assoc($resultado_ultimo_curso);
                                    
                                    if ($ultimo_curso) {
                                        ?>
                                        <div class="text-center">
                                            <p class="mb-1" style="font-size: 1rem;"><strong>Descripción:</strong> <?php echo $ultimo_curso['nombre']; ?></p>
                                            <p class="mb-1" style="font-size: 0.95rem;"><strong>Fecha de Entrega:</strong> <?php echo $ultimo_curso['fecha']; ?></p>
                                        </div>
                                        <?php
                                    } else {
                                        echo '<p class="text-center" style="font-size: 0.95rem; color: #dc3545;">No se ha completado ningún curso aún.</p>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Contenedor para centrar el contenido -->
                <div class="container text-center mt-4">
                
                    <!-- Botones de navegación -->
                    <div class="mb-4">
                        <a href="javascript:history.go(-1)" class="btn btn-secondary me-2">Volver</a>
                        <a href="descargar_historial_cursos.php?id=<?php echo $id_estudiante; ?>" class="btn btn-primary">Descargar Historial</a>
                    </div>
                
                    <!-- Tarjeta centrada con el formulario -->
                    <div class="d-flex justify-content-center">
                        <div class="card" style="width: 18rem;">
                            <div class="card-body">
                                <form action="descargar_lista_pdf.php" method="GET">
                                    <input type="hidden" id="id_estudiante" name="id_estudiante" value="<?php echo $id_estudiante; ?>">
                                    
                                    <!-- Botón Descargar dentro de la tarjeta -->
                                    <button type="submit" class="btn btn-primary w-100 mb-3">Ver Historial PDF</button>
                                    
                                    <!-- Checkbox para incluir todos los cursos -->
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="incluirTodos" name="incluir_todos" value="true">
                                        <label class="form-check-label" for="incluirTodos">Incluir todos los cursos</label>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
            <script>
                // Botón para mostrar el formulario de edición
                document.getElementById('edit-student-btn').addEventListener('click', function () {
                    document.getElementById('edit-student-form').style.display = 'block'; // Mostrar el formulario de edición
                    document.getElementById('cursos_view').style.display = 'none'; // Ocultar cursos_view
                });
            
                // Botón para cancelar la edición
                document.getElementById('cancel-edit-btn').addEventListener('click', function () {
                    document.getElementById('edit-student-form').style.display = 'none'; // Ocultar el formulario de edición
                    document.getElementById('cursos_view').style.display = 'block'; // Mostrar cursos_view
                });

                // Actualizar el campo iglesia cuando se cambie el contacto
                document.getElementById('id_contacto').addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const iglesiaText = selectedOption.text.split(' - ')[1]; // Obtener la iglesia del texto del option
                    document.getElementById('iglesia').value = iglesiaText;
                });
            
                // Manejar el envío del formulario
                document.getElementById('update-student-form').addEventListener('submit', function (e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    fetch('https://emmausdigital.com/genesis/wp-content/plugins/plg-genesis/backend/estudiantes/actualizar_estudiante.php', {
                        method: 'POST',
                        body: JSON.stringify(Object.fromEntries(formData)),
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Información actualizada exitosamente');
                            document.getElementById('edit-student-form').style.display = 'none'; // Ocultar el formulario de edición
                            document.getElementById('cursos_view').style.display = 'block'; // Mostrar cursos_view
                            location.reload(); // Recargar la página para reflejar los cambios
                        } else {
                            alert('Error al actualizar: ' + data.message);
                        }
                    })
                    .catch(error => console.error('Error:', error));
                });
            </script>
        </body>
        </html>
        <?php
    } else {
        // Si no se encontraron detalles del estudiante, mostrar un mensaje de error
        echo '<p>No se encontraron detalles para este estudiante.</p>';
    }
} else {
    // Si no se proporcionó el ID del estudiante, mostrar un mensaje de error
    echo '<p>No se proporcionó un ID de estudiante válido.</p>';
}

// Cerrar la conexión a la base de datos
pg_close($conexion);
?>