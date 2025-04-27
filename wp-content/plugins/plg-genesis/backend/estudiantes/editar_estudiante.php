<?php
// Configurar las cabeceras
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar el método de la solicitud
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Incluir el archivo de conexión a la base de datos
    include '../db.php'; // Ajusta la ruta según tu estructura

    // Leer el cuerpo de la solicitud
    $input = json_decode(file_get_contents('php://input'), true);

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

    // Preparar la consulta de actualización
    $query = "UPDATE estudiantes SET $campo = $1 WHERE id = $2";
    $resultado = pg_prepare($conexion, "update_query", $query);
    if (!$resultado) {
        error_log("Error al preparar la consulta: " . pg_last_error($conexion));
        echo json_encode(['success' => false, 'error' => 'Error al preparar la consulta.']);
        exit;
    }

    // Ejecutar la declaración con los parámetros
    $resultado = pg_execute($conexion, "update_query", [$valor, $id]);
    if ($resultado) {
        echo json_encode(['success' => true]);
    } else {
        error_log("Error al ejecutar la consulta: " . pg_last_error($conexion));
        echo json_encode(['success' => false, 'error' => 'Error al ejecutar la consulta.']);
    }

    // Cerrar la conexión
    pg_close($conexion);
} else {
    echo json_encode(['success' => false, 'error' => 'Método no permitido.']);
}
?>