<?php
require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

header('Content-Type: application/json');

// Verificar el método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Leer el cuerpo de la solicitud
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validar entrada
if (!isset($data['programa_id']) || empty($data['programa_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'El ID del programa es requerido.']);
    exit;
}

$programaId = intval($data['programa_id']);
$nombre = $data['nombre'] ?? null;
$descripcion = $data['descripcion'] ?? null;
$niveles = $data['niveles'] ?? [];
$cursosSinNivel = $data['cursosSinNivel'] ?? [];

// Iniciar transacción
pg_query($conexion, 'BEGIN');

// Actualizar los datos del programa
$queryPrograma = "
    UPDATE programas
    SET nombre = $1, descripcion = $2
    WHERE id = $3
";
$resultPrograma = pg_query_params($conexion, $queryPrograma, [$nombre, $descripcion, $programaId]);

if (!$resultPrograma) {
    pg_query($conexion, 'ROLLBACK');
    echo json_encode(['error' => 'Error al actualizar el programa.']);
    exit;
}

// Eliminar asignaciones actuales de cursos y niveles
$queryDeleteCursos = "DELETE FROM programas_cursos WHERE programa_id = $1";
$resultDeleteCursos = pg_query_params($conexion, $queryDeleteCursos, [$programaId]);

if (!$resultDeleteCursos) {
    pg_query($conexion, 'ROLLBACK');
    echo json_encode(['error' => 'Error al eliminar asignaciones anteriores de cursos.']);
    exit;
}

// Eliminar niveles no presentes en la estructura enviada
$nivelesIdsEnviados = array_map(fn($nivel) => intval($nivel['id'] ?? 0), $niveles);
$queryDeleteNiveles = "
    DELETE FROM niveles_programas
    WHERE programa_id = $1 AND id NOT IN (" . implode(',', $nivelesIdsEnviados ?: [0]) . ")
";
$resultDeleteNiveles = pg_query_params($conexion, $queryDeleteNiveles, [$programaId]);

if (!$resultDeleteNiveles) {
    pg_query($conexion, 'ROLLBACK');
    echo json_encode(['error' => 'Error al eliminar niveles no usados.']);
    exit;
}

// Insertar niveles y cursos
foreach ($niveles as $nivel) {
    $nivelId = $nivel['id'] ?? null;
    $nivelNombre = trim($nivel['nombre_nivel'] ?? '');

    // Validar si el nivel tiene un nombre
    if (empty($nivelNombre)) {
        continue; // Ignorar niveles sin nombre
    }

    if ($nivelId) {
        // Actualizar nivel existente
        $queryNivel = "
            UPDATE niveles_programas
            SET nombre = $1
            WHERE id = $2 AND programa_id = $3
        ";
        $resultNivel = pg_query_params($conexion, $queryNivel, [$nivelNombre, $nivelId, $programaId]);

        if (!$resultNivel) {
            pg_query($conexion, 'ROLLBACK');
            echo json_encode(['error' => 'Error al actualizar nivel.']);
            exit;
        }
    } else {
        // Insertar nuevo nivel
        $queryNivel = "
            INSERT INTO niveles_programas (programa_id, nombre)
            VALUES ($1, $2)
            RETURNING id
        ";
        $resultNivel = pg_query_params($conexion, $queryNivel, [$programaId, $nivelNombre]);

        if (!$resultNivel) {
            pg_query($conexion, 'ROLLBACK');
            echo json_encode(['error' => 'Error al insertar nivel.']);
            exit;
        }

        $nivelId = pg_fetch_result($resultNivel, 0, 'id');
    }

    // Insertar cursos del nivel
    foreach ($nivel['cursos'] as $curso) {
        $cursoId = $curso['id'];
        $consecutivo = $curso['consecutivo'];

        $queryCurso = "
            INSERT INTO programas_cursos (programa_id, curso_id, nivel_id, consecutivo)
            VALUES ($1, $2, $3, $4)
        ";
        $resultCurso = pg_query_params($conexion, $queryCurso, [$programaId, $cursoId, $nivelId, $consecutivo]);

        if (!$resultCurso) {
            pg_query($conexion, 'ROLLBACK');
            echo json_encode(['error' => 'Error al insertar curso en nivel.']);
            exit;
        }
    }
}

// Insertar cursos sin nivel
foreach ($cursosSinNivel as $curso) {
    $cursoId = $curso['id'];
    $consecutivo = $curso['consecutivo'];

    $queryCursoSinNivel = "
        INSERT INTO programas_cursos (programa_id, curso_id, nivel_id, consecutivo)
        VALUES ($1, $2, NULL, $3)
    ";
    $resultCursoSinNivel = pg_query_params($conexion, $queryCursoSinNivel, [$programaId, $cursoId, $consecutivo]);

    if (!$resultCursoSinNivel) {
        pg_query($conexion, 'ROLLBACK');
        echo json_encode(['error' => 'Error al insertar curso sin nivel.']);
        exit;
    }
}

// Finalizar transacción
pg_query($conexion, 'COMMIT');
pg_close($conexion);

echo json_encode(['success' => true, 'message' => 'Programa actualizado exitosamente.']);