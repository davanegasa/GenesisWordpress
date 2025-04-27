<?php
require_once(__DIR__ . '/../../../../../wp-load.php');  // Ajusta la ruta según tu estructura de directorios

// Verificar si el usuario no está autenticado en WordPress
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());  // Redirigir a la página de login de WordPress
    exit;
}

require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

// Recibir el ID del estudiante desde la solicitud POST
$estudianteId = $_POST['studentId'] ?? '17366'; // Usamos un ID predeterminado para pruebas

// Escapar la entrada para evitar inyecciones SQL
$estudianteId = pg_escape_string($conexion, $estudianteId);  // Utiliza pg_escape_string y asegúrate de no incluir comillas adicionales en la consulta

// Preparar la consulta SQL para obtener la lista de cursos y porcentajes del estudiante
$query = "SELECT ec.porcentaje, c.nombre, c.consecutivo FROM estudiantes_cursos ec
          JOIN cursos c ON ec.curso_id = c.id
          WHERE ec.estudiante_id = $estudianteId ORDER BY c.consecutivo ASC";

// Ejecutar la consulta
$resultado = pg_query($conexion, $query);

// Array para almacenar los resultados y los consecutivos
$cursos = [];
$consecutivos = [];
if ($resultado && pg_num_rows($resultado) > 0) {
    while ($row = pg_fetch_assoc($resultado)) {
        $cursos[] = $row;
        $consecutivos[] = (int)$row['consecutivo'];
    }
}

// Encontrar el primer hueco en la secuencia de consecutivos
$missingConsecutivo = null;
for ($i = 1; $i <= max($consecutivos); $i++) {
    if (!in_array($i, $consecutivos)) {
        $missingConsecutivo = $i;
        break;
    }
}

// Obtener el nombre del libro sugerido para el siguiente consecutivo faltante
$suggestedBook = null;
if ($missingConsecutivo) {
    $querySuggestedBook = "SELECT descripcion FROM cursos WHERE consecutivo = $missingConsecutivo LIMIT 1";
    $resultSuggestedBook = pg_query($conexion, $querySuggestedBook);
    if ($resultSuggestedBook && pg_num_rows($resultSuggestedBook) > 0) {
        $rowSuggestedBook = pg_fetch_assoc($resultSuggestedBook);
        $suggestedBook = $rowSuggestedBook['descripcion'];
    }
}

// Preparar la respuesta
if (!empty($cursos)) {
    $respuesta = ['success' => true, 'cursos' => $cursos, 'suggestedBook' => $suggestedBook];
} else {
    $respuesta = ['success' => false, 'message' => 'No se encontraron cursos para el estudiante.'];
}

// Cerrar la conexión a la base de datos
pg_close($conexion);

// Convertir la respuesta a formato JSON y enviarla
header('Content-Type: application/json');
echo json_encode($respuesta);
?>
