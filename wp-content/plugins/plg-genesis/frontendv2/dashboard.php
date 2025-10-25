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
	<link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__); ?>styles/responsive.css">
	<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
	<link rel="icon" href="<?php echo plugin_dir_url(__FILE__); ?>assets/favicon.ico" sizes="any">
	<link rel="icon" type="image/png" href="<?php echo plugin_dir_url(__FILE__); ?>assets/icon-32.png" sizes="32x32">
	<link rel="apple-touch-icon" href="<?php echo plugin_dir_url(__FILE__); ?>assets/apple-touch-icon.png">
	<style>
		body { margin:0; font-family: Roboto, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; background: var(--plg-bg, #f7f7fb); color: var(--plg-text, #111827); width: 100%; overflow-x: hidden; }
		.layout { display:flex; min-height:100vh; position: relative; width: 100%; }
		.sidebar { position:sticky; top:0; align-self:flex-start; height:100vh; overflow:auto; width:260px; background: var(--plg-sidebarBg, #111827); color: var(--plg-sidebarText, #e5e7eb); padding:16px; transition: transform 0.3s ease; z-index: 1000; }
        .sidebar a { color: var(--plg-sidebarText, #e5e7eb); text-decoration:none; display:block; padding:10px 12px; border-radius:6px; }
        .sidebar a.active, .sidebar a:hover { background: var(--plg-accent, #1f2937); color:#fff; }
		.main-wrapper { flex:1; display: flex; flex-direction: column; width: 100%; box-sizing: border-box; }
		.top-header { background: var(--plg-cardBg, #fff); border-bottom: 1px solid var(--plg-border, #e5e7eb); padding: 12px 24px; display: flex; align-items: center; justify-content: flex-end; gap: 24px; position: sticky; top: 0; z-index: 100; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
		.user-info { display: flex; align-items: center; gap: 12px; }
		.user-avatar { width: 36px; height: 36px; border-radius: 50%; background: var(--plg-accent, #0c497a); color: white; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px; }
		.user-details { display: flex; flex-direction: column; gap: 2px; }
		.user-name { font-weight: 600; font-size: 14px; color: var(--plg-text, #111827); }
		.user-role { font-size: 12px; color: var(--plg-textSecondary, #6b7280); text-transform: capitalize; }
		.dashboard-toggle { display: flex; align-items: center; gap: 8px; padding: 8px 12px; border-radius: 8px; background: var(--plg-bg, #f7f7fb); border: 1px solid var(--plg-border, #e5e7eb); }
		.dashboard-toggle label { font-size: 13px; font-weight: 500; color: var(--plg-text, #111827); cursor: pointer; user-select: none; }
		.toggle-switch { position: relative; display: inline-block; width: 44px; height: 24px; cursor: pointer; }
		.toggle-switch input { position: absolute; opacity: 0; width: 100%; height: 100%; top: 0; left: 0; cursor: pointer; z-index: 2; margin: 0; }
		.toggle-slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #ccc; transition: .3s; border-radius: 24px; z-index: 1; }
		.toggle-slider:before { position: absolute; content: ""; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; transition: .3s; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.2); }
		.toggle-switch input:checked + .toggle-slider { background-color: var(--plg-accent, #0c497a); }
		.toggle-switch input:checked + .toggle-slider:before { transform: translateX(20px); }
		.toggle-switch input:focus + .toggle-slider { box-shadow: 0 0 0 2px rgba(12, 73, 122, 0.2); }
		.content { flex:1; padding:24px; width: 100%; box-sizing: border-box; }
		.kpi { background: var(--plg-cardBg, #fff); border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.05); }
		.kpi-grid { display:grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap:16px; }
		.card { background: var(--plg-cardBg, #fff); border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.05); box-sizing: border-box; }

		/* Menú hamburguesa */
		.menu-toggle { display: none; position: fixed; top: 16px; left: 16px; z-index: 1001; background: var(--plg-accent); color: white; border: none; border-radius: 8px; width: 44px; height: 44px; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 4px 12px rgba(12, 73, 122, 0.3); }
		.menu-toggle:hover { background: #0a3a5f; }
		.sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 999; }
		
		/* En desktop: ocultar el botón */
		@media (min-width: 1024px) {
			.menu-toggle { display: none !important; }
		}

        /* Ocultar cabeceras/títulos del tema WP solo en esta página */
		.wp-block-site-title, .wp-block-post-title, .site-header, .wp-block-template-part, .entry-title, .page-title {
			display: none !important;
		}
		.wp-site-blocks > .wp-block-post-title, .wp-site-blocks > header { display:none !important; }
	</style>
</head>
<body>
	<button class="menu-toggle" id="menu-toggle" aria-label="Abrir menú">
		<svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
			<line x1="3" y1="12" x2="21" y2="12"></line>
			<line x1="3" y1="6" x2="21" y2="6"></line>
			<line x1="3" y1="18" x2="21" y2="18"></line>
		</svg>
	</button>
	<div class="sidebar-overlay" id="sidebar-overlay"></div>
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
		<div class="main-wrapper">
			<header class="top-header" id="top-header">
				<!-- Se llenará dinámicamente con JS -->
			</header>
			<main class="content">
				<div id="view"></div>
			</main>
		</div>
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
			'contacto_id' => get_user_meta($current_user->ID, 'contacto_id', true) ?: null,
			'capabilities' => $plg_caps,
		];
			
			echo json_encode($user_data);
		?>;
	</script>
	<script type="module" src="<?php echo plugin_dir_url(__FILE__); ?>core/bootstrap.js"></script>
	<script>
		// Menú hamburguesa toggle
		document.addEventListener('DOMContentLoaded', () => {
			const menuToggle = document.getElementById('menu-toggle');
			const sidebar = document.getElementById('sidebar');
			const overlay = document.getElementById('sidebar-overlay');

		function toggleMenu() {
			sidebar.classList.toggle('open');
			overlay.classList.toggle('active');
			document.body.style.overflow = sidebar.classList.contains('open') ? 'hidden' : '';
		}

		menuToggle?.addEventListener('click', toggleMenu);
		overlay?.addEventListener('click', toggleMenu);

		// Cerrar menú al hacer clic en un sub-item (solo en mobile)
		// No cerrar si es un item principal con acordeón
		sidebar?.addEventListener('click', (e) => {
			if (e.target.tagName === 'A' && window.innerWidth < 1024) {
				// Solo cerrar si es un submenu, no un trigger de acordeón
				if (e.target.classList.contains('submenu')) {
					toggleMenu();
				}
			}
		});
	});
	</script>

	<?php wp_footer(); ?>
</body>
</html>