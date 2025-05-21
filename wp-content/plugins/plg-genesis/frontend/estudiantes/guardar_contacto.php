<?php
require_once(dirname(__FILE__) . '/../utils/logger.php');

$conexion = pg_connect("host=localhost dbname=genesis user=genesis password=genesis");

if (!$conexion) {
    genesis_frontend_log('Error en la conexión a la base de datos', 'ERROR');
    die('Error en la conexión a la base de datos.');
}

$resultado = pg_query($conexion, "INSERT INTO contactos (nombre, correo, mensaje) VALUES ('" . $_POST['nombre'] . "', '" . $_POST['correo'] . "', '" . $_POST['mensaje'] . "')");

if (!$resultado) {
    genesis_frontend_log('Error al guardar el contacto: ' . pg_last_error($conexion), 'ERROR');
    echo json_encode(['success' => false, 'error' => 'Error al guardar el contacto']);
    exit;
}

echo json_encode(['success' => true]); 