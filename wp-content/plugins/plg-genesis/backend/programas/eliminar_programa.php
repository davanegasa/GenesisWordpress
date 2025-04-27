<?php
require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

header('Content-Type: application/json');

// Validar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    http_response_code(405); // Método no permitido
    echo json_encode(['error' => 'Método no permitido. Use DELETE.']);
    exit;
}

// Obtener el cuerpo de la solicitud
$input = json_decode(file_get_contents('php://input'), true);

// Validar que se reciba el ID del programa
if (!isset($input['programa_id']) || empty($input['programa_id'])) {
    http_response_code(400); // Solicitud incorrecta
    echo json_encode(['error' => 'El ID del programa es obligatorio.']);
    exit;
}

$programaId = (int) $input['programa_id'];

try {
    // Iniciar una transacción
    pg_query($conexion, 'BEGIN');

    // Eliminar los prerequisitos relacionados con el programa
    $sqlEliminarPrerequisitos = "
        DELETE FROM programas_prerequisitos
        WHERE programa_id = $1 OR prerequisito_id = $1
    ";
    $resultPrerequisitos = pg_query_params($conexion, $sqlEliminarPrerequisitos, [$programaId]);
    if (!$resultPrerequisitos) {
        throw new Exception('Error al eliminar los prerequisitos: ' . pg_last_error($conexion));
    }

    // Eliminar los cursos asociados al programa
    $sqlEliminarCursos = "
        DELETE FROM programas_cursos
        WHERE programa_id = $1
    ";
    $resultCursos = pg_query_params($conexion, $sqlEliminarCursos, [$programaId]);
    if (!$resultCursos) {
        throw new Exception('Error al eliminar los cursos del programa: ' . pg_last_error($conexion));
    }

    // Eliminar los niveles asociados al programa
    $sqlEliminarNiveles = "
        DELETE FROM niveles_programas
        WHERE programa_id = $1
    ";
    $resultNiveles = pg_query_params($conexion, $sqlEliminarNiveles, [$programaId]);
    if (!$resultNiveles) {
        throw new Exception('Error al eliminar los niveles del programa: ' . pg_last_error($conexion));
    }

    // Eliminar el programa
    $sqlEliminarPrograma = "
        DELETE FROM programas
        WHERE id = $1
    ";
    $resultPrograma = pg_query_params($conexion, $sqlEliminarPrograma, [$programaId]);
    if (!$resultPrograma) {
        throw new Exception('Error al eliminar el programa: ' . pg_last_error($conexion));
    }

    // Confirmar la transacción
    pg_query($conexion, 'COMMIT');

    // Respuesta exitosa
    echo json_encode(['success' => true, 'message' => 'Programa eliminado exitosamente.']);
} catch (Exception $e) {
    // Revertir la transacción en caso de error
    pg_query($conexion, 'ROLLBACK');
    http_response_code(500); // Error interno del servidor
    echo json_encode(['error' => $e->getMessage()]);
} finally {
    // Cerrar la conexión
    pg_close($conexion);
}
?>