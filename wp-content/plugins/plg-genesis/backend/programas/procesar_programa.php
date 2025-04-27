<?php
require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

header('Content-Type: application/json');

// Validar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Método no permitido
    echo json_encode(['error' => 'Método no permitido. Use POST.']);
    exit;
}

// Obtener datos del cuerpo de la solicitud
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['nombre']) || !isset($data['descripcion'])) {
    http_response_code(400); // Solicitud incorrecta
    echo json_encode(['error' => 'Datos insuficientes para procesar el programa.']);
    exit;
}

// Variables principales
$nombre = pg_escape_string($conexion, $data['nombre']);
$descripcion = pg_escape_string($conexion, $data['descripcion']);

// Iniciar transacción
pg_query($conexion, 'BEGIN');

try {
    // Insertar el programa
    $sqlPrograma = "INSERT INTO programas (nombre, descripcion) VALUES ('$nombre', '$descripcion') RETURNING id";
    $resultPrograma = pg_query($conexion, $sqlPrograma);

    if (!$resultPrograma) {
        throw new Exception('Error al insertar el programa.');
    }

    // Capturar el ID del programa
    $programaId = pg_fetch_result($resultPrograma, 0, 0);

    // Insertar niveles y cursos
    foreach ($data['niveles'] as $nivel) {
        $nombreNivel = pg_escape_string($conexion, $nivel['nombre_nivel']);

        // Insertar nivel en niveles_programas y capturar el nivel_id generado
        $sqlNivel = "INSERT INTO niveles_programas (programa_id, nombre) VALUES ($programaId, '$nombreNivel') RETURNING id";
        $resultNivel = pg_query($conexion, $sqlNivel);

        if (!$resultNivel) {
            throw new Exception('Error al insertar el nivel.');
        }

        // Capturar el ID del nivel generado automáticamente
        $nivelId = pg_fetch_result($resultNivel, 0, 0);

        // Validar que nivelId no sea null
        if ($nivelId === null) {
            throw new Exception("nivel_id es NULL para el nivel: $nombreNivel.");
        }

        // Insertar cursos del nivel
        foreach ($nivel['cursos'] as $curso) {
            $cursoId = (int)$curso['id'];
            $consecutivo = (int)$curso['consecutivo'];

            // Insertar cursos vinculados al nivel generado
            $sqlCurso = "INSERT INTO programas_cursos (programa_id, curso_id, nivel_id, consecutivo) 
                         VALUES ($programaId, $cursoId, $nivelId, $consecutivo)";
            if (!pg_query($conexion, $sqlCurso)) {
                throw new Exception('Error al insertar cursos del nivel.');
            }
        }
    }

    // Insertar cursos sin nivel
    foreach ($data['cursos_sin_nivel'] as $curso) {
        $cursoId = (int)$curso['id'];
        $consecutivo = (int)$curso['consecutivo'];

        // Insertar curso sin nivel asociado
        $sqlCursoSinNivel = "INSERT INTO programas_cursos (programa_id, curso_id, nivel_id, consecutivo) 
                             VALUES ($programaId, $cursoId, NULL, $consecutivo)";
        if (!pg_query($conexion, $sqlCursoSinNivel)) {
            throw new Exception('Error al insertar cursos sin nivel.');
        }
    }

    // Insertar prerequisitos
    foreach ($data['prerequisitos'] as $prerequisito) {
        $prerequisitoId = (int)$prerequisito['id'];

        $sqlPrerequisito = "INSERT INTO programas_prerequisitos (programa_id, prerequisito_id) 
                            VALUES ($programaId, $prerequisitoId)";
        if (!pg_query($conexion, $sqlPrerequisito)) {
            throw new Exception('Error al insertar los prerequisitos.');
        }
    }

    // Confirmar transacción
    pg_query($conexion, 'COMMIT');

    // Respuesta exitosa
    echo json_encode(['success' => true, 'message' => 'Programa procesado correctamente.', 'programa_id' => $programaId]);
} catch (Exception $e) {
    // Rollback en caso de error
    pg_query($conexion, 'ROLLBACK');
    http_response_code(500); // Error interno del servidor
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

// Cerrar conexión
pg_close($conexion);
?>