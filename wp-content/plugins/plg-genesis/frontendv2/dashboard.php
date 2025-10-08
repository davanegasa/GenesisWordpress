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
	<style>
		body { margin:0; font-family: Roboto, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: var(--plg-bg, #f7f7fb); color: var(--plg-text, #111827); }
		.layout { display:flex; min-height:100vh; }
        .sidebar { width:260px; background: var(--plg-sidebarBg, #111827); color: var(--plg-sidebarText, #e5e7eb); padding:16px; }
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
			<a href="#/estudiantes/nuevo" class="submenu">Crear Estudiantes</a>
			<a href="#/estudiantes" class="submenu">Listar por Contactos</a>
			<a href="#/contactos" id="nav-contactos">Contactos</a>
			<a href="#/contactos" class="submenu">Buscar Contactos</a>
			<a href="#/contactos/nuevo" class="submenu">Crear Contacto</a>
			<a href="#/congresos" id="nav-congresos">Congresos</a>
			<a href="#/programas" id="nav-programas">Programas</a>
			<a href="#/programas" class="submenu">Listar Programas</a>
			<a href="#/programas/nuevo" class="submenu">Crear Programa</a>
			<a href="#/tema" id="nav-tema">Tema</a>
		</nav>
		<main class="content">
			<div id="view"></div>
		</main>
	</div>

	<script>
		window.wpApiSettings = window.wpApiSettings || {};
		window.wpApiSettings.nonce = '<?php echo wp_create_nonce('wp_rest'); ?>';
	</script>
	<script type="module" src="<?php echo plugin_dir_url(__FILE__); ?>core/bootstrap.js"></script>
	<!-- Script inline removido; ahora todo el arranque lo hace core/bootstrap.js -->

	<?php wp_footer(); ?>
</body>
</html>