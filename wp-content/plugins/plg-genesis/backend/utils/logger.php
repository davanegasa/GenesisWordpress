<?php
// Logger centralizado para el backend de Genesis

function genesis_log($mensaje, $nivel = 'INFO') {
    $ruta_log = dirname(__DIR__) . '/genesis.log';
    $fecha = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'CLI';
    $usuario = (function_exists('wp_get_current_user')) ? wp_get_current_user()->user_login : 'desconocido';
    $archivo = basename(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1)[0]['file'] ?? 'desconocido');
    $linea = "[$fecha][$nivel][$ip][$usuario][$archivo] $mensaje\n";
    file_put_contents($ruta_log, $linea, FILE_APPEND | LOCK_EX);
} 