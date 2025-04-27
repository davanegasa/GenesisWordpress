<?php
require_once(__DIR__ . '/../../../../../wp-load.php');  // Ajusta la ruta según tu estructura de directorios

// Verificar si el usuario no está autenticado en WordPress
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());  // Redirigir a la página de login de WordPress
    exit;
}

require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

// Definir el valor predeterminado del filtro de contacto
$filtro_id_contacto = '';

// Verificar si se envió el formulario de filtro
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    // Verificar si se proporcionó un ID de contacto válido en el formulario
    if (isset($_GET['id_contacto']) && !empty($_GET['id_contacto'])) {
        // Obtener el ID del contacto desde el formulario
        $filtro_id_contacto = $_GET['id_contacto'];
    }
}

// Consulta SQL para obtener todos los IDs de contacto
$query_contactos = "SELECT id, nombre, iglesia, code FROM contactos";
$resultado_contactos = pg_query($conexion, $query_contactos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Listar Estudiantes</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../dashboard.css">
</head>
<body>
<div class="container">
  <h1 class="mt-5">Listar Estudiantes</h1>

  <!-- Formulario de filtro por ID de contacto -->
  <form action="" method="GET" class="mt-4 mb-3">
    <div class="input-group">
      <!-- Combo box de ID de contacto -->
      <select class="form-select" name="id_contacto">
        <option value="">Seleccionar contacto</option>
        <?php
        // Verificar si se ejecutó la consulta correctamente y mostrar las opciones del combo box
        if ($resultado_contactos && pg_num_rows($resultado_contactos) > 0) {
            while ($row = pg_fetch_assoc($resultado_contactos)) {
                $selected = ($filtro_id_contacto == $row['id']) ? 'selected' : '';
                echo '<option value="' . $row['id'] . '" ' . $selected . '>' . $row['code'] . ' - ' . $row['nombre'] . ' - ' . $row['iglesia'] . '</option>';
            }
        } else {
            echo '<option value="">Error al obtener los contactos</option>';
        }
        ?>
      </select>

      <!-- Botón de filtro -->
      <button type="submit" class="btn btn-primary">Filtrar</button>
    </div>
  </form>
  
      <?php if (!empty($filtro_id_contacto)): ?>
      <!-- Botón de descarga solo si se selecciona un contacto -->
        <div class="container text-center mt-4">
            <!-- Tarjeta centrada para el botón de descarga con checkbox -->
            <div class="d-flex justify-content-center">
                <div class="card" style="width: 18rem;">
                    <div class="card-body">
                        <form action="descargar_lista_pdf.php" method="GET">
                            <input type="hidden" name="id_contacto" value="<?= $filtro_id_contacto ?>">
                            
                            <!-- Checkbox para incluir todos los cursos -->
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" name="incluir_todos" id="incluir_todos" value="true" <?= $incluir_todos ? 'checked' : '' ?>>
                                <label class="form-check-label" for="incluir_todos">Incluir todos los cursos</label>
                            </div>
        
                            <!-- Botón Descargar dentro de la tarjeta -->
                            <button type="submit" class="btn btn-success w-100">Descargar Lista en PDF</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
      <?php endif; ?>
      
<!-- Tabla de estudiantes -->
<?php
if (!empty($filtro_id_contacto)) {
    // Realizar la consulta de los estudiantes filtrados por contacto
    $query_estudiantes = "SELECT * FROM estudiantes WHERE id_contacto = '$filtro_id_contacto' ORDER By apellido1 asc";
    $resultado_estudiantes = pg_query($conexion, $query_estudiantes);
    $total_estudiantes = pg_num_rows($resultado_estudiantes);
    
    // Número de estudiantes por página
    $estudiantes_por_pagina = 10;
    // Calcular el total de páginas
    $total_paginas = ceil($total_estudiantes / $estudiantes_por_pagina);
    // Obtener la página actual
    $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    // Calcular el índice de inicio para la consulta
    $indice_inicio = ($pagina_actual - 1) * $estudiantes_por_pagina;

    // Consulta SQL para obtener los estudiantes de acuerdo a la paginación
    $query_estudiantes_paginacion = "SELECT * FROM estudiantes WHERE id_contacto = '$filtro_id_contacto' ORDER By apellido1 asc LIMIT $estudiantes_por_pagina OFFSET $indice_inicio";
    $resultado_estudiantes_paginacion = pg_query($conexion, $query_estudiantes_paginacion);
?>
    <table class="table">
      <thead>
        <tr>
          <th>Nombre</th>
          <th>Apellido</th>
          <th>Celular</th>
          <th>Correo Electrónico</th>
        </tr>
      </thead>
      <tbody>
        <?php
        // Verificar si se ejecutó la consulta de estudiantes correctamente
        if ($resultado_estudiantes_paginacion) {
            // Mostrar los datos de los estudiantes en la tabla
            while ($row = pg_fetch_assoc($resultado_estudiantes_paginacion)) {
                echo '<tr>';
                echo '<td>' . $row['nombre1'] . ' ' . $row['nombre2'] . '</td>';
                echo '<td>' . $row['apellido1'] . ' ' . $row['apellido2'] . '</td>';
                echo '<td>' . $row['celular'] . '</td>';
                echo '<td>' . $row['email'] . '</td>';
                echo '<td><a href="ver_detalle.php?id=' . $row['id'] . '" class="btn btn-primary"><i class="bi bi-info-circle"></i></a></td>';
                echo '</tr>';
            }
        } else {
            echo '<tr><td colspan="8">No se encontraron estudiantes para el contacto seleccionado.</td></tr>';
        }
        ?>
      </tbody>
    </table>

    <!-- Paginación -->
    <nav aria-label="Paginación" class="fixed-pagination">
      <ul class="pagination justify-content-center">
        <!-- Mostrar enlaces de paginación -->
        <?php for ($i = 1; $i <= $total_paginas; $i++) : ?>
          <li class="page-item <?= $pagina_actual == $i ? 'active' : '' ?>">
            <a class="page-link" href="?id_contacto=<?= $filtro_id_contacto ?>&pagina=<?= $i ?>"><?= $i ?></a>
          </li>
        <?php endfor; ?>
      </ul>
    </nav>

<?php
} else {
    echo '<p>Seleccione un contacto para ver los estudiantes.</p>';
}
?>
</div>
</body>
</html>

<?php
// Cerrar la conexión a la base de datos
pg_close($conexion);
?>