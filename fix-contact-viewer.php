<?php
/**
 * Script para vincular usuarios contact_viewer con sus contactos
 * 
 * Uso:
 * 1. Listar usuarios contact_viewer: http://localhost:8080/fix-contact-viewer.php?action=list
 * 2. Vincular usuario: http://localhost:8080/fix-contact-viewer.php?action=link&user_id=X&contacto_id=Y
 */

// Cargar WordPress
define('WP_USE_THEMES', false);
require_once __DIR__ . '/wp-load.php';

// Verificar permisos
if (!is_user_logged_in()) {
	wp_redirect(wp_login_url($_SERVER['REQUEST_URI']));
	exit;
}

if (!current_user_can('administrator')) {
	wp_die('Solo administradores pueden usar este script');
}

$action = $_GET['action'] ?? 'list';

// HTML head
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Vincular Contact Viewers</title>
	<style>
		body { font-family: Arial, sans-serif; margin: 40px; }
		table { border-collapse: collapse; width: 100%; margin: 20px 0; }
		th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
		th { background: #4CAF50; color: white; }
		tr:nth-child(even) { background: #f2f2f2; }
		.success { color: green; font-size: 24px; }
		.error { color: red; font-weight: bold; }
		input[type="number"] { padding: 4px; width: 80px; }
		button { padding: 6px 12px; background: #4CAF50; color: white; border: none; cursor: pointer; }
		button:hover { background: #45a049; }
		a { color: #4CAF50; text-decoration: none; }
		a:hover { text-decoration: underline; }
	</style>
</head>
<body>
<?php

$action = $_GET['action'] ?? 'list';

if ($action === 'list') {
	echo "<h1>Usuarios Contact Viewer</h1>";
	echo "<table border='1' cellpadding='10'>";
	echo "<tr><th>ID</th><th>Usuario</th><th>Nombre</th><th>Email</th><th>Contacto ID</th><th>Acción</th></tr>";
	
	$users = get_users(['role' => 'plg_contact_viewer']);
	
	foreach ($users as $user) {
		$contacto_id = get_user_meta($user->ID, 'contacto_id', true);
		$oficina = get_user_meta($user->ID, 'oficina', true);
		
		echo "<tr>";
		echo "<td>{$user->ID}</td>";
		echo "<td>{$user->user_login}</td>";
		echo "<td>{$user->display_name}</td>";
		echo "<td>{$user->user_email}</td>";
		echo "<td>" . ($contacto_id ? $contacto_id : '<strong style="color:red;">NO VINCULADO</strong>') . "</td>";
		echo "<td>";
		if (!$contacto_id) {
			echo "<form method='get' style='display:inline;'>";
			echo "<input type='hidden' name='action' value='link'>";
			echo "<input type='hidden' name='user_id' value='{$user->ID}'>";
			echo "Contacto ID: <input type='number' name='contacto_id' size='5' required>";
			echo " <button type='submit'>Vincular</button>";
			echo "</form>";
		} else {
			echo "✓ Vinculado";
		}
		echo "</td>";
		echo "</tr>";
	}
	
	echo "</table>";
	
} elseif ($action === 'link') {
	$user_id = intval($_GET['user_id'] ?? 0);
	$contacto_id = intval($_GET['contacto_id'] ?? 0);
	
	if ($user_id && $contacto_id) {
		update_user_meta($user_id, 'contacto_id', $contacto_id);
		echo "<h1 class='success'>✓ Usuario vinculado exitosamente</h1>";
		echo "<p>User ID: {$user_id} → Contacto ID: {$contacto_id}</p>";
		echo "<p><a href='?action=list'>← Volver al listado</a></p>";
	} else {
		echo "<h1 class='error'>Error: Faltan parámetros</h1>";
		echo "<p><a href='?action=list'>← Volver al listado</a></p>";
	}
}
?>
</body>
</html>

