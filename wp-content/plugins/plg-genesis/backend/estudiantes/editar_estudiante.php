<?php
// Configurar las cabeceras
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST, PUT');
header('Access-Control-Allow-Headers: Content-Type');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar el método de la solicitud
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Incluir el archivo de conexión a PostgreSQL para Docker
    include '../db_docker.php';

    // Leer el cuerpo de la solicitud
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Si no hay datos JSON, intentar leer de POST
    if (empty($input)) {
        $input = $_POST;
    }

    // Log para debugging
    error_log("Datos recibidos: " . print_r($input, true));

    // Validar los datos recibidos
    $id = $input['id'] ?? null;
    $campo = $input['campo'] ?? null;
    $valor = $input['valor'] ?? null;

    // Validar los campos
    if (!$id || !$campo || !$valor) {
        error_log("Datos inválidos: ID: $id, Campo: $campo, Valor: $valor");
        echo json_encode(['success' => false, 'error' => 'Datos inválidos.']);
        exit;
    }

    // Validar conexión
    if (!$conexion) {
        error_log("Conexión a la base de datos fallida.");
        echo json_encode(['success' => false, 'error' => 'Error al conectar con la base de datos.']);
        exit;
    }

    // Lista de campos permitidos
    $camposPermitidos = ['doc_identidad', 'celular', 'email'];
    if (!in_array($campo, $camposPermitidos)) {
        echo json_encode(['success' => false, 'error' => 'Campo no permitido.']);
        exit;
    }

    try {
        // Preparar la consulta de actualización
        $query = "UPDATE estudiantes SET $campo = $1 WHERE id = $2";
        $resultado = pg_prepare($conexion, "update_query", $query);
        if (!$resultado) {
            throw new Exception(pg_last_error($conexion));
        }

        // Ejecutar la declaración con los parámetros
        $resultado = pg_execute($conexion, "update_query", [$valor, $id]);
        if ($resultado) {
            echo json_encode(['success' => true, 'message' => 'Actualización exitosa']);
        } else {
            throw new Exception(pg_last_error($conexion));
        }
    } catch (Exception $e) {
        error_log("Error en la base de datos: " . $e->getMessage());
        echo json_encode(['success' => false, 'error' => 'Error en la base de datos: ' . $e->getMessage()]);
    }

    // Cerrar la conexión
    pg_close($conexion);
} else {
    echo json_encode(['success' => false, 'error' => 'Método no permitido. Use POST o PUT.']);
}
?>