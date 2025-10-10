<?php
if (!defined('ABSPATH')) { exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Dashboard v2</title>
	<?php wp_head(); ?>
	<link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__); ?>styles/tokens.css">
	<link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__); ?>styles/base.css">
	<link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__); ?>styles/components.css">
	<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
	<link rel="icon" href="<?php echo plugin_dir_url(__FILE__); ?>assets/favicon.ico" sizes="any">
	<link rel="icon" type="image/png" href="<?php echo plugin_dir_url(__FILE__); ?>assets/icon-32.png" sizes="32x32">
	<link rel="apple-touch-icon" href="<?php echo plugin_dir_url(__FILE__); ?>assets/apple-touch-icon.png">
	<style>
		body { margin:0; font-family: Roboto, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: var(--plg-bg, #f7f7fb); color: var(--plg-text, #111827); }
		.layout { display:flex; min-height:100vh; }
		.sidebar { position:sticky; top:0; align-self:flex-start; height:100vh; overflow:auto; width:260px; background: var(--plg-sidebarBg, #111827); color: var(--plg-sidebarText, #e5e7eb); padding:16px; }
        .sidebar a { color: var(--plg-sidebarText, #e5e7eb); text-decoration:none; display:block; padding:10px 12px; border-radius:6px; }
        .sidebar a.active, .sidebar a:hover { background: var(--plg-accent, #1f2937); color:#fff; }
		.content { flex:1; padding:24px; }
		.kpi { background: var(--plg-cardBg, #fff); border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.05); }
		.kpi-grid { display:grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap:16px; }
		.card { background: var(--plg-cardBg, #fff); border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.05); }

		/* Responsive: aplazado */

        /* Ocultar cabeceras/títulos del tema WP solo en esta página */
		.wp-block-site-title, .wp-block-post-title, .site-header, .wp-block-template-part, .entry-title, .page-title {
			display: none !important;
		}
		.wp-site-blocks > .wp-block-post-title, .wp-site-blocks > header { display:none !important; }
	</style>
</head>
<body>
	<div class="layout">
		<nav class="sidebar" id="sidebar">
			<div style="font-weight:700;font-size:20px;margin-bottom:12px;">Genesis</div>
			<a href="#/dashboard" id="nav-dashboard" class="active">Dashboard</a>
			<a href="#/estudiantes" id="nav-estudiantes">Estudiantes</a>
			<a href="#/estudiantes" class="submenu">Gestionar</a>
			<a href="#/estudiantes/nuevo" class="submenu">Crear</a>
			<a href="#/contactos" id="nav-contactos">Contactos</a>
			<a href="#/contactos" class="submenu">Buscar Contactos</a>
			<a href="#/contactos/nuevo" class="submenu">Crear Contacto</a>
			<a href="#/congresos" id="nav-congresos">Congresos</a>
			<a href="#/programas" id="nav-programas">Programas</a>
			<a href="#/programas" class="submenu">Listar Programas</a>
			<a href="#/programas/nuevo" class="submenu">Crear Programa</a>
			<a href="#/cursos" id="nav-cursos">Cursos</a>
			<a href="#/cursos" class="submenu">Listar Cursos</a>
			<a href="#/cursos/nuevo" class="submenu">Crear Curso</a>
			<a href="#/ajustes" id="nav-ajustes">Ajustes ⚙️</a>
			<a href="#/tema" class="submenu" data-group="ajustes">Tema</a>
			<a href="#/docs" class="submenu" data-group="ajustes">API Docs</a>
			<a href="<?php echo esc_url( wp_logout_url( home_url('/dashboard-v2/') ) ); ?>" class="submenu" data-group="ajustes">Cerrar sesión</a>
		</nav>
		<main class="content">
			<div id="view"></div>
		</main>
	</div>

	<script>
		window.wpApiSettings = window.wpApiSettings || {};
		window.wpApiSettings.nonce = '<?php echo wp_create_nonce('wp_rest'); ?>';
		
		// Inyectar datos del usuario actual para el frontend
		window.wpUserData = <?php 
			$current_user = wp_get_current_user();
			
			// Filtrar solo capabilities del plugin (que empiezan con plg_)
			$plg_caps = [];
			foreach ($current_user->allcaps as $cap => $val) {
				if ($val === true && strpos($cap, 'plg_') === 0) {
					$plg_caps[$cap] = true;
				}
			}
			
			$user_data = [
				'id' => $current_user->ID,
				'name' => $current_user->display_name,
				'email' => $current_user->user_email,
				'login' => $current_user->user_login,
				'roles' => $current_user->roles,
				'office' => get_user_meta($current_user->ID, 'oficina', true) ?: null,
				'capabilities' => $plg_caps,
			];
			
			echo json_encode($user_data);
		?>;
	</script>
	<script type="module" src="<?php echo plugin_dir_url(__FILE__); ?>core/bootstrap.js"></script>
	<!-- Script inline removido; ahora todo el arranque lo hace core/bootstrap.js -->

	<?php wp_footer(); ?>
</body>
</html>