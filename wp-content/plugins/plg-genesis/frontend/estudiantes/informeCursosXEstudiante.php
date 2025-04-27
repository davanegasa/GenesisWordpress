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
    if (isset($_GET['id_contacto']) && !empty($_GET['id_contacto'])) {
        $filtro_id_contacto = $_GET['id_contacto'];
    } else {
        echo '<p class="text-danger">El filtro de contacto es requerido.</p>';
    }
}

// Consulta SQL para obtener todos los IDs de contacto
$query_contactos = "SELECT id, code, iglesia FROM contactos";
$resultado_contactos = pg_query($conexion, $query_contactos);

// Si se ha seleccionado un filtro de contacto, generar el CSV
if (!empty($filtro_id_contacto)) {
    // Obtener la lista de nombres de cursos
    $query_nombres_cursos = "SELECT nombre FROM cursos ORDER BY id";
    $resultado_nombres_cursos = pg_query($conexion, $query_nombres_cursos);

    // Crear un array para almacenar los nombres de los cursos
    $nombres_cursos = [];
    while ($row = pg_fetch_assoc($resultado_nombres_cursos)) {
        $nombres_cursos[] = $row['nombre'];
    }

    // Construir dinámicamente el SELECT para cada curso usando el array $nombres_cursos
    $select_cursos = "";
    foreach ($nombres_cursos as $nombre_curso) {
        $alias = preg_replace('/\s+/', '', $nombre_curso); // Eliminar espacios en blanco del nombre para usar como alias
        $select_cursos .= "MAX(CASE WHEN curso.nombre = '$nombre_curso' THEN est_curso.porcentaje ELSE NULL END) AS \"$alias\", ";
    }

    // Consulta para obtener los estudiantes y sus cursos usando el SELECT dinámico
    $query_estudiantes_cursos = "
        SELECT 
            e.id AS estudiante_id,
            e.id_estudiante AS codigo_estudiante,
            CONCAT(e.nombre1, ' ', COALESCE(e.nombre2, ''), ' ', e.apellido1, ' ', e.apellido2) AS estudiante_nombre,
            $select_cursos
            c.iglesia AS iglesia
        FROM 
            estudiantes e
        JOIN 
            contactos c ON e.id_contacto = c.id
        LEFT JOIN 
            estudiantes_cursos est_curso ON e.id = est_curso.estudiante_id
        LEFT JOIN 
            cursos curso ON est_curso.curso_id = curso.id
        WHERE 
            c.id = $filtro_id_contacto
        GROUP BY 
            e.id, estudiante_nombre, e.id_estudiante, c.iglesia
        ORDER BY 
            e.id;
    ";

    $resultado_estudiantes_cursos = pg_query($conexion, $query_estudiantes_cursos);
    
    if ($resultado_estudiantes_cursos) {
        // Crear un archivo CSV y descargarlo
        $fecha_actual = date("Y-m-d_H-i-s");
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename="estudiantes_cursos_' . $fecha_actual . '.csv"');
        $output = fopen('php://output', 'w');

        // Escribir el encabezado del archivo CSV
        $header = array_merge(['Estudiante', 'Código Estudiante'], $nombres_cursos);
        fputcsv($output, $header, ';');

        // Escribir los datos de los estudiantes y sus cursos
        while ($row = pg_fetch_assoc($resultado_estudiantes_cursos)) {
            $fila = [$row['estudiante_nombre'], $row['codigo_estudiante']];
            foreach ($nombres_cursos as $nombre_curso) {
                $alias = preg_replace('/\s+/', '', $nombre_curso);
                $fila[] = $row[$alias];
            }
            fputcsv($output, $fila, ';');
        }

        fclose($output);
        exit;
    } else {
        echo '<p class="text-danger">Error al obtener los estudiantes y cursos.</p>';
    }
}
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
      <select class="form-select" name="id_contacto">
        <option value="">Seleccionar ID de contacto</option>
        <?php
        if ($resultado_contactos && pg_num_rows($resultado_contactos) > 0) {
            while ($row = pg_fetch_assoc($resultado_contactos)) {
                $selected = ($filtro_id_contacto == $row['id']) ? 'selected' : '';
                echo '<option value="' . $row['id'] . '" ' . $selected . '>' . $row['code'] . ' - ' . $row['iglesia'] . '</option>';
            }
        } else {
            echo '<option value="">Error al obtener los IDs de contacto</option>';
        }
        ?>
      </select>
      <button type="submit" class="btn btn-primary">Filtrar</button>
    </div>
  </form>
</div>
</body>
</html>

<?php
pg_close($conexion);
?>