<?php
require_once(__DIR__ . '/../../../../../wp-load.php');
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

header('Content-Type: application/json');

// Validar método HTTP
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Método no permitido
    echo json_encode(['error' => 'Método no permitido. Use GET.']);
    exit;
}

// Obtener parámetro obligatorio
$programaId = isset($_GET['programa_id']) ? (int)$_GET['programa_id'] : null;
if (!$programaId) {
    http_response_code(400); // Solicitud incorrecta
    echo json_encode(['error' => 'El parámetro programa_id es obligatorio.']);
    exit;
}

// Construir consulta para recuperar el programa
$sqlProgramas = "
    SELECT p.id AS programa_id, p.nombre AS programa_nombre, p.descripcion AS programa_descripcion
    FROM programas p
    WHERE p.id = $1
";

$params = [$programaId];
$resultProgramas = pg_query_params($conexion, $sqlProgramas, $params);

if (!$resultProgramas) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al recuperar el programa: ' . pg_last_error($conexion)]);
    exit;
}

// Verificar si el programa existe
$programa = pg_fetch_assoc($resultProgramas);
if (!$programa) {
    http_response_code(404); // No encontrado
    echo json_encode(['error' => 'Programa no encontrado.']);
    exit;
}

// Construir estructura del programa
$programaId = $programa['programa_id'];

// Recuperar niveles asociados al programa
$sqlNiveles = "
    SELECT np.id AS nivel_id, np.nombre AS nombre_nivel
    FROM niveles_programas np
    WHERE np.programa_id = $1
    ORDER BY np.id
";
$resultNiveles = pg_query_params($conexion, $sqlNiveles, [$programaId]);

if (!$resultNiveles) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al recuperar los niveles: ' . pg_last_error($conexion)]);
    exit;
}

$niveles = [];
while ($nivel = pg_fetch_assoc($resultNiveles)) {
    $nivelId = $nivel['nivel_id'];

    // Recuperar cursos asociados al nivel
    $sqlCursosNivel = "
        SELECT c.id AS curso_id, c.nombre AS curso_nombre, pc.consecutivo
        FROM programas_cursos pc
        JOIN cursos c ON pc.curso_id = c.id
        WHERE pc.nivel_id = $1
        ORDER BY pc.consecutivo
    ";
    $resultCursosNivel = pg_query_params($conexion, $sqlCursosNivel, [$nivelId]);

    if (!$resultCursosNivel) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al recuperar los cursos de nivel: ' . pg_last_error($conexion)]);
        exit;
    }

    $cursosNivel = [];
    while ($curso = pg_fetch_assoc($resultCursosNivel)) {
        $cursosNivel[] = [
            'id' => $curso['curso_id'],
            'nombre' => $curso['curso_nombre'], // Incluir nombre del curso
            'consecutivo' => $curso['consecutivo']
        ];
    }

    $niveles[] = [
        'id' => $nivelId,
        'nombre_nivel' => $nivel['nombre_nivel'],
        'cursos' => $cursosNivel
    ];
}

// Recuperar cursos sin nivel asociados al programa
$sqlCursosSinNivel = "
    SELECT c.id AS curso_id, c.nombre AS curso_nombre, pc.consecutivo
    FROM programas_cursos pc
    JOIN cursos c ON pc.curso_id = c.id
    WHERE pc.programa_id = $1 AND pc.nivel_id IS NULL
    ORDER BY pc.consecutivo
";
$resultCursosSinNivel = pg_query_params($conexion, $sqlCursosSinNivel, [$programaId]);

if (!$resultCursosSinNivel) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al recuperar los cursos sin nivel: ' . pg_last_error($conexion)]);
    exit;
}

$cursosSinNivel = [];
while ($curso = pg_fetch_assoc($resultCursosSinNivel)) {
    $cursosSinNivel[] = [
        'id' => $curso['curso_id'],
        'nombre' => $curso['curso_nombre'], // Incluir nombre del curso
        'consecutivo' => $curso['consecutivo']
    ];
}

// Recuperar prerequisitos del programa
$sqlPrerequisitos = "
    SELECT pp.prerequisito_id AS id, p.nombre AS nombre
    FROM programas_prerequisitos pp
    JOIN programas p ON pp.prerequisito_id = p.id
    WHERE pp.programa_id = $1
    ORDER BY p.id
";
$resultPrerequisitos = pg_query_params($conexion, $sqlPrerequisitos, [$programaId]);

if (!$resultPrerequisitos) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al recuperar los prerequisitos: ' . pg_last_error($conexion)]);
    exit;
}

$prerequisitos = [];
while ($prerequisito = pg_fetch_assoc($resultPrerequisitos)) {
    $prerequisitos[] = [
        'id' => $prerequisito['id'],
        'nombre' => $prerequisito['nombre']
    ];
}

// Armar estructura final del programa
$response = [
    'id' => $programaId,
    'nombre' => $programa['programa_nombre'],
    'descripcion' => $programa['programa_descripcion'],
    'niveles' => $niveles,
    'cursos_sin_nivel' => $cursosSinNivel,
    'prerequisitos' => $prerequisitos
];

// Enviar respuesta como JSON
echo json_encode($response);

// Cerrar conexión
pg_close($conexion);
?>