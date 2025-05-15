<?php
require_once(__DIR__ . '/../../../../../wp-load.php');

// Establecer el tipo de contenido como JSON
header('Content-Type: application/json');

// Verificar si el usuario no está autenticado en WordPress
if (!is_user_logged_in()) {
    http_response_code(401);
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

require_once(__DIR__ . '/../db.php');

// Función de validación
function validar_campo($valor, $tipo, $requerido = false) {
    if ($requerido && empty($valor)) {
        return ['valido' => false, 'mensaje' => 'Este campo es requerido'];
    }
    
    if (empty($valor) && !$requerido) {
        return ['valido' => true, 'mensaje' => ''];
    }

    switch ($tipo) {
        case 'numeros':
            if (!preg_match('/^[0-9]+$/', $valor)) {
                return ['valido' => false, 'mensaje' => 'Solo se permiten números'];
            }
            break;
            
        case 'nombre':
            // Permite letras, espacios y caracteres especiales latinos
            if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $valor)) {
                return ['valido' => false, 'mensaje' => 'Solo se permiten letras y espacios'];
            }
            break;
            
        case 'email':
            if (!filter_var($valor, FILTER_VALIDATE_EMAIL)) {
                return ['valido' => false, 'mensaje' => 'Email inválido'];
            }
            break;
            
        case 'texto_general':
            // Permite letras, números, espacios y puntuación básica
            if (!preg_match('/^[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s\.,#-]+$/', $valor)) {
                return ['valido' => false, 'mensaje' => 'Caracteres no permitidos'];
            }
            break;
            
        case 'estado_civil':
            $estados_validos = ['Soltero', 'Casado', 'Divorciado', 'Viudo', 'Unión Libre'];
            if (!in_array($valor, $estados_validos)) {
                return ['valido' => false, 'mensaje' => 'Estado civil no válido'];
            }
            break;
            
        case 'escolaridad':
            $niveles_validos = ['Primaria', 'Secundaria', 'Técnico', 'Tecnólogo', 'Universitario', 'Postgrado', 'Ninguno'];
            if (!in_array($valor, $niveles_validos)) {
                return ['valido' => false, 'mensaje' => 'Nivel de escolaridad no válido'];
            }
            break;
    }
    
    return ['valido' => true, 'mensaje' => ''];
}

// Verificar la conexión a la base de datos
if (!$conexion) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al conectar a la base de datos: ' . pg_last_error()]);
    exit;
}

// Obtener y decodificar los datos JSON del request
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos inválidos']);
    exit;
}

// Validar todos los campos
$errores = [];

// Campos requeridos
$validaciones = [
    'nombre1' => ['tipo' => 'nombre', 'requerido' => true],
    'apellido1' => ['tipo' => 'nombre', 'requerido' => true],
    'doc_identidad' => ['tipo' => 'numeros', 'requerido' => true],
    'id_contacto' => ['tipo' => 'numeros', 'requerido' => true],
    'celular' => ['tipo' => 'numeros', 'requerido' => false],
    'email' => ['tipo' => 'email', 'requerido' => false],
    'nombre2' => ['tipo' => 'nombre', 'requerido' => false],
    'apellido2' => ['tipo' => 'nombre', 'requerido' => false],
    'ciudad' => ['tipo' => 'texto_general', 'requerido' => false],
    'iglesia' => ['tipo' => 'texto_general', 'requerido' => false],
    'ocupacion' => ['tipo' => 'texto_general', 'requerido' => false],
    'estado_civil' => ['tipo' => 'estado_civil', 'requerido' => true],
    'escolaridad' => ['tipo' => 'escolaridad', 'requerido' => true]
];

foreach ($validaciones as $campo => $reglas) {
    $resultado = validar_campo(
        $data[$campo] ?? '', 
        $reglas['tipo'], 
        $reglas['requerido']
    );
    
    if (!$resultado['valido']) {
        $errores[$campo] = $resultado['mensaje'];
    }
}

if (!empty($errores)) {
    http_response_code(400);
    echo json_encode([
        'error' => 'Errores de validación',
        'errores' => $errores
    ]);
    exit;
}

try {
    // Determinar el ID del estudiante
    if (!empty($data['usar_codigo_manual']) && !empty($data['codigo_estudiante'])) {
        // Limpiar espacios del código manual
        $codigo_manual = trim($data['codigo_estudiante']);
        
        // Verificar si el código manual ya existe
        $query_verificar = "SELECT COUNT(*) FROM estudiantes WHERE id_estudiante = $1";
        $result_verificar = pg_query_params($conexion, $query_verificar, array($codigo_manual));
        
        if (!$result_verificar) {
            throw new Exception('Error al verificar el código: ' . pg_last_error($conexion));
        }
        
        $row_verificar = pg_fetch_assoc($result_verificar);
        if ($row_verificar['count'] > 0) {
            throw new Exception('El código de estudiante ya existe');
        }
        
        $id_estudiante = $codigo_manual;
    } else {
        // Generar ID automáticamente
        // Consulta SQL para obtener la cantidad de estudiantes
        $query_count = "SELECT COUNT(*) AS total_estudiantes FROM estudiantes";
        $result_count = pg_query($conexion, $query_count);
        
        if (!$result_count) {
            throw new Exception('Error al contar los estudiantes: ' . pg_last_error($conexion));
        }
        
        $row_count = pg_fetch_assoc($result_count);
        $total_estudiantes = $row_count['total_estudiantes'];

        // Obtener el código del contacto
        $query_contacto = "SELECT TRIM(code) as code FROM contactos WHERE id = $1";
        $result_contacto = pg_query_params($conexion, $query_contacto, array($data['id_contacto']));
        
        if (!$result_contacto) {
            throw new Exception('Error al obtener el código del contacto: ' . pg_last_error($conexion));
        }
        
        $contacto = pg_fetch_assoc($result_contacto);
        if (!$contacto) {
            throw new Exception('Contacto no encontrado');
        }
        
        $code_contacto = trim($contacto['code']);
        $id_estudiante = $code_contacto . $total_estudiantes;
    }

    // Consulta SQL para insertar un nuevo estudiante
    $query = "INSERT INTO estudiantes (
        id_contacto, 
        doc_identidad, 
        nombre1, 
        nombre2, 
        apellido1, 
        apellido2, 
        celular, 
        email, 
        ciudad, 
        iglesia, 
        id_estudiante,
        estado_civil,
        escolaridad,
        ocupacion
    ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14)";
    
    // Preparar y ejecutar la consulta
    $result = pg_query_params($conexion, $query, array(
        $data['id_contacto'],
        $data['doc_identidad'],
        $data['nombre1'],
        $data['nombre2'] ?? '',
        $data['apellido1'],
        $data['apellido2'] ?? '',
        $data['celular'] ?? '',
        $data['email'] ?? '',
        $data['ciudad'] ?? '',
        $data['iglesia'] ?? '',
        $id_estudiante,
        $data['estado_civil'] ?? null,
        $data['escolaridad'] ?? null,
        $data['ocupacion'] ?? null
    ));

    if (!$result) {
        throw new Exception('Error al insertar el estudiante: ' . pg_last_error($conexion));
    }

    // Devolver respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Estudiante creado exitosamente',
        'estudiante_id' => $id_estudiante
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}

pg_close($conexion);
?>