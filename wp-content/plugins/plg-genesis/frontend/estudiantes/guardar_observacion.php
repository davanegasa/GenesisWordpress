<?php
require_once(dirname(__FILE__) . '/../utils/logger.php');

$conexion = pg_connect("host=localhost dbname=genesis user=genesis password=genesis");

if (!$conexion) {
    genesis_frontend_log('Error en la conexión a la base de datos', 'ERROR');
    die('Error en la conexión a la base de datos.');
}

$resultado = pg_query($conexion, "INSERT INTO observaciones (observacion, fecha, estudiante_id) VALUES ('" . $_POST['observacion'] . "', '" . date('Y-m-d H:i:s') . "', " . $_POST['estudiante_id'] . ")");

if (!$resultado) {
    genesis_frontend_log('Error al guardar la observación: ' . pg_last_error($conexion), 'ERROR');
    echo json_encode(['success' => false, 'error' => 'Error al guardar la observación']);
    exit;
}

echo json_encode(['success' => true]); 