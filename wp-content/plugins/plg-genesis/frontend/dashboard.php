<?php
// Verificar autenticación
if (!is_user_logged_in()) {
	wp_redirect(wp_login_url());
	exit;
}

// Redirigir solo a contact_viewer al dashboard v2
$current_user = wp_get_current_user();
if (in_array('plg_contact_viewer', $current_user->roles)) {
	$dashboard_v2_page = get_page_by_path('dashboard-v2');
	if ($dashboard_v2_page) {
		wp_redirect(get_permalink($dashboard_v2_page->ID));
		exit;
	}
	// Si no encuentra la página dashboard-v2, mostrar mensaje de error
	wp_die('Error: No se encontró la página Dashboard V2. Por favor contacta al administrador.');
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <?php wp_head(); ?>
  <link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__); ?>../assets/css/styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
  <style>
    /* Estilos base */
    :root {
      --sidebar-width: 280px;
      --header-height: 60px;
      --primary-color: #1e293b;
      --hover-color: #334155;
      --active-color: #3b82f6;
      --text-color: #f8fafc;
      --text-muted: #94a3b8;
      --border-color: rgba(148, 163, 184, 0.1);
      --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    body {
      margin: 0;
      padding: 0;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
      background: #f1f5f9;
    }

    #wrapper {
      display: flex;
      min-height: 100vh;
    }

    /* Estilos del sidebar mejorados */
    #sidebar {
      width: var(--sidebar-width);
      background: var(--primary-color);
      color: var(--text-color);
      transition: var(--transition);
      position: fixed;
      height: 100vh;
      overflow-y: auto;
      z-index: 1000;
      box-shadow: 4px 0 10px rgba(0, 0, 0, 0.1);
    }

    #sidebar::-webkit-scrollbar {
      width: 6px;
    }

    #sidebar::-webkit-scrollbar-track {
      background: var(--primary-color);
    }

    #sidebar::-webkit-scrollbar-thumb {
      background: var(--hover-color);
      border-radius: 3px;
    }

    .sidebar-logo {
      padding: 20px;
      text-align: center;
      border-bottom: 1px solid var(--border-color);
      background: rgba(0, 0, 0, 0.1);
    }

    .sidebar-logo img {
      max-width: 70%;
      height: auto;
      transition: var(--transition);
    }

    /* Enlaces del menú mejorados */
    .sidebar-link {
      display: flex;
      align-items: center;
      padding: 14px 20px;
      color: var(--text-color);
      text-decoration: none;
      transition: var(--transition);
      border-left: 3px solid transparent;
      font-size: 0.95rem;
      letter-spacing: 0.3px;
      margin: 2px 0;
    }

    .sidebar-link:hover {
      background: var(--hover-color);
      border-left-color: var(--active-color);
      color: white;
      text-decoration: none;
      padding-left: 25px;
    }

    .sidebar-link.active {
      background: var(--hover-color);
      border-left-color: var(--active-color);
      color: white;
    }

    .sidebar-link i {
      width: 24px;
      text-align: center;
      margin-right: 12px;
      font-size: 1.1rem;
      color: var(--text-muted);
      transition: var(--transition);
    }

    .sidebar-link:hover i,
    .sidebar-link.active i {
      color: var(--active-color);
    }

    .sidebar-link .fa-chevron-down {
      margin-left: auto;
      font-size: 0.85rem;
      transition: var(--transition);
      opacity: 0.7;
    }

    .sidebar-link.collapsed .fa-chevron-down {
      transform: rotate(-180deg);
    }

    /* Submenús mejorados */
    .submenu {
      list-style: none;
      padding: 5px 0;
      margin: 0;
      background: rgba(0, 0, 0, 0.15);
      border-left: 3px solid var(--border-color);
      display: none;
    }

    .submenu .sidebar-link {
      padding: 10px 20px 10px 48px;
      font-size: 0.9rem;
      color: var(--text-muted);
    }

    .submenu .sidebar-link:hover {
      padding-left: 53px;
    }

    .submenu .sidebar-link i {
      font-size: 0.9rem;
      width: 20px;
    }

    /* Botón toggle mejorado */
    #sidebarToggle {
      display: none;
      position: fixed;
      top: 15px;
      left: 15px;
      z-index: 1001;
      background: var(--primary-color);
      color: var(--text-color);
      border: none;
      padding: 12px;
      border-radius: 8px;
      cursor: pointer;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
      transition: var(--transition);
    }

    #sidebarToggle:hover {
      background: var(--hover-color);
      transform: scale(1.05);
    }

    /* Separadores de secciones */
    .sidebar-section {
      margin: 15px 0;
      padding: 0;
    }

    .sidebar-section:not(:first-child) {
      border-top: 1px solid var(--border-color);
      padding-top: 15px;
    }

    /* Botón de cerrar sesión especial */
    .logout-link {
      margin-top: 20px;
      margin-bottom: 20px;
      padding: 12px 20px;
      background: rgba(239, 68, 68, 0.1);
      border-radius: 6px;
      margin-left: 10px;
      margin-right: 10px;
    }

    .logout-link i {
      color: #ef4444 !important;
    }

    .logout-link:hover {
      background: rgba(239, 68, 68, 0.2);
    }

    /* Media queries mejorados */
    @media (max-width: 768px) {
      :root {
        --sidebar-width: 85%;
      }

      #sidebarToggle {
        display: block;
      }

      #sidebar {
        transform: translateX(-100%);
        max-width: 320px;
      }

      #sidebar.active {
        transform: translateX(0);
      }

      #content {
        margin-left: 0;
        padding-top: var(--header-height);
      }

      .sidebar-link {
        padding: 16px 20px;
      }

      .submenu .sidebar-link {
        padding-left: 48px;
      }

      .logout-link {
        margin: 20px;
      }
    }

    /* Estilos del header superior */
    .main-wrapper-v1 { 
      flex:1; 
      display: flex; 
      flex-direction: column; 
      margin-left: var(--sidebar-width);
    }
    .top-header-v1 { 
      background: white; 
      border-bottom: 1px solid #e5e7eb; 
      padding: 12px 24px; 
      display: flex; 
      align-items: center; 
      justify-content: flex-end; 
      gap: 24px; 
      position: sticky; 
      top: 0; 
      z-index: 100; 
      box-shadow: 0 1px 3px rgba(0,0,0,0.05); 
    }
    .user-info-v1 { 
      display: flex; 
      align-items: center; 
      gap: 12px; 
    }
    .user-avatar-v1 { 
      width: 36px; 
      height: 36px; 
      border-radius: 50%; 
      background: #0c497a; 
      color: white; 
      display: flex; 
      align-items: center; 
      justify-content: center; 
      font-weight: 600; 
      font-size: 14px; 
    }
    .user-details-v1 { 
      display: flex; 
      flex-direction: column; 
      gap: 2px; 
    }
    .user-name-v1 { 
      font-weight: 600; 
      font-size: 14px; 
      color: #111827; 
    }
    .user-role-v1 { 
      font-size: 12px; 
      color: #6b7280; 
      text-transform: capitalize; 
    }
    .dashboard-toggle-v1 { 
      display: flex; 
      align-items: center; 
      gap: 8px; 
      padding: 8px 12px; 
      border-radius: 8px; 
      background: #f7f7fb; 
      border: 1px solid #e5e7eb; 
    }
    .dashboard-toggle-v1 label { 
      font-size: 13px; 
      font-weight: 500; 
      color: #111827; 
      cursor: pointer; 
      user-select: none; 
    }
    .toggle-switch-v1 { 
      position: relative; 
      display: inline-block; 
      width: 44px; 
      height: 24px; 
      cursor: pointer; 
    }
    .toggle-switch-v1 input { 
      position: absolute; 
      opacity: 0; 
      width: 100%; 
      height: 100%; 
      top: 0; 
      left: 0; 
      cursor: pointer; 
      z-index: 2; 
      margin: 0; 
    }
    .toggle-slider-v1 { 
      position: absolute; 
      cursor: pointer; 
      top: 0; 
      left: 0; 
      right: 0; 
      bottom: 0; 
      background-color: #ccc; 
      transition: .3s; 
      border-radius: 24px; 
      z-index: 1; 
    }
    .toggle-slider-v1:before { 
      position: absolute; 
      content: ""; 
      height: 18px; 
      width: 18px; 
      left: 3px; 
      bottom: 3px; 
      background-color: white; 
      transition: .3s; 
      border-radius: 50%; 
      box-shadow: 0 2px 4px rgba(0,0,0,0.2); 
    }
    .toggle-switch-v1 input:checked + .toggle-slider-v1 { 
      background-color: #0c497a; 
    }
    .toggle-switch-v1 input:checked + .toggle-slider-v1:before { 
      transform: translateX(20px); 
    }

    @media (max-width: 768px) {
      .main-wrapper-v1 {
        margin-left: 0;
      }
      .top-header-v1 {
        padding: 8px 12px;
        gap: 8px;
      }
      .user-avatar-v1 {
        width: 32px;
        height: 32px;
        font-size: 12px;
      }
      .user-name-v1 {
        font-size: 13px;
      }
      .user-role-v1 {
        font-size: 11px;
      }
      .dashboard-toggle-v1 label {
        font-size: 11px;
      }
    }
  </style>
</head>
<body>

<!-- Botón toggle para móvil -->
<button id="sidebarToggle">
  <i class="fas fa-bars"></i>
</button>

<!-- Overlay para móvil -->
<div id="overlay"></div>

<div id="wrapper">
  <nav id="sidebar">
    <div class="sidebar-logo">
      <img src="<?php echo plugin_dir_url(__FILE__); ?>../images/genesis/logo.png" alt="Logo del Sitio" class="img-fluid">
    </div>

    <div class="sidebar-section">
      <!-- Gestión de Estudio con Submenú -->
      <a href="#" class="sidebar-link" onclick="toggleMenu(this)">
        <i class="fas fa-graduation-cap"></i>
        <span>Gestión de Estudio</span>
        <i class="fas fa-chevron-down ms-auto"></i>
      </a>
      <ul class="submenu">
        <li><a href="#" class="sidebar-link" onclick="loadPage('<?php echo plugin_dir_url(__FILE__); ?>estudiantes/busquedaEstudiantes.html')">
          <i class="fas fa-users"></i>
          <span>Gestionar Estudiantes</span>
        </a></li>
        <li><a href="#" class="sidebar-link" onclick="loadPage('<?php echo plugin_dir_url(__FILE__); ?>cursos/exportCSV.php')">
          <i class="fas fa-file-export"></i>
          <span>Descargar Archivo</span>
        </a></li>
        <li><a href="#" class="sidebar-link" onclick="loadPage('<?php echo plugin_dir_url(__FILE__); ?>estudiantes/informeCursosXEstudiante.php')">
          <i class="fas fa-file-alt"></i>
          <span>Descargar Informe</span>
        </a></li>
        <li><a href="#" class="sidebar-link" onclick="loadPage('<?php echo plugin_dir_url(__FILE__); ?>informes/oficina/ADC.php')">
          <i class="fas fa-chart-bar"></i>
          <span>Informes ADC</span>
        </a></li>
        <li><a href="#" class="sidebar-link" onclick="loadPage('<?php echo plugin_dir_url(__FILE__); ?>cursos/calendario_cursos.php')">
          <i class="fas fa-calendar-alt"></i>
          <span>Calendario de Cursos</span>
        </a></li>
      </ul>
    </div>

    <div class="sidebar-section">
      <!-- Cursos con Submenú -->
      <a href="#" class="sidebar-link" onclick="toggleMenu(this)">
        <i class="fas fa-book"></i>
        <span>Cursos</span>
        <i class="fas fa-chevron-down ms-auto"></i>
      </a>
      <ul class="submenu">
        <li><a href="#" class="sidebar-link" onclick="loadPage('<?php echo plugin_dir_url(__FILE__); ?>cursos/crear_nivel.php')">
          <i class="fas fa-layer-group"></i>
          <span>Crear Niveles</span>
        </a></li>
        <li><a href="#" class="sidebar-link" onclick="loadPage('<?php echo plugin_dir_url(__FILE__); ?>cursos/crear_curso.php')">
          <i class="fas fa-plus-circle"></i>
          <span>Crear Cursos</span>
        </a></li>
        <li><a href="#" class="sidebar-link" onclick="loadPage('<?php echo plugin_dir_url(__FILE__); ?>cursos/listar_cursos_estadisticas.php')">
          <i class="fas fa-list"></i>
          <span>Listar Cursos</span>
        </a></li>
      </ul>
    </div>

    <div class="sidebar-section">
      <!-- Estudiantes con Submenú -->
      <a href="#" class="sidebar-link" onclick="toggleMenu(this)">
        <i class="fas fa-user-graduate"></i>
        <span>Estudiantes</span>
        <i class="fas fa-chevron-down ms-auto"></i>
      </a>
      <ul class="submenu">
        <li><a href="#" class="sidebar-link" onclick="loadPage('<?php echo plugin_dir_url(__FILE__); ?>estudiantes/crear_estudiante_v2.php')">
          <i class="fas fa-user-plus"></i>
          <span>Crear Estudiantes</span>
        </a></li>
        <li><a href="#" class="sidebar-link" onclick="loadPage('<?php echo plugin_dir_url(__FILE__); ?>estudiantes/listar.php')">
          <i class="fas fa-list-ul"></i>
          <span>Listar por Contactos</span>
        </a></li>
      </ul>
    </div>

    <div class="sidebar-section">
      <!-- Contactos con Submenú -->
      <a href="#" class="sidebar-link" onclick="toggleMenu(this)">
        <i class="fas fa-address-book"></i>
        <span>Contactos</span>
        <i class="fas fa-chevron-down ms-auto"></i>
      </a>
      <ul class="submenu">
        <li><a href="#" class="sidebar-link" onclick="loadPage('<?php echo plugin_dir_url(__FILE__); ?>contactos/crear_contacto.php')">
          <i class="fas fa-user-plus"></i>
          <span>Crear Contactos</span>
        </a></li>
        <li><a href="#" class="sidebar-link" onclick="loadPage('<?php echo plugin_dir_url(__FILE__); ?>contactos/busqueda_contactos.php')">
          <i class="fas fa-search"></i>
          <span>Listar V2</span>
        </a></li>
      </ul>
    </div>

    <div class="sidebar-section">
      <!-- Gestión de Programas -->
      <a href="#" class="sidebar-link" onclick="toggleMenu(this)">
        <i class="fas fa-project-diagram"></i>
        <span>Gestión de Programas</span>
        <i class="fas fa-chevron-down ms-auto"></i>
      </a>
      <ul class="submenu">
        <li><a href="#" class="sidebar-link" onclick="loadPage('<?php echo plugin_dir_url(__FILE__); ?>programas/crear_programa.php')">
          <i class="fas fa-plus"></i>
          <span>Crear Programas</span>
        </a></li>
        <li><a href="#" class="sidebar-link" onclick="loadPage('<?php echo plugin_dir_url(__FILE__); ?>programas/listar_programas.php')">
          <i class="fas fa-list"></i>
          <span>Listar Programas</span>
        </a></li>
      </ul>
    </div>

    <div class="sidebar-section">
      <!-- Gestión de Congresos -->
      <a href="#" class="sidebar-link" onclick="toggleMenu(this)">
        <i class="fas fa-calendar-alt"></i>
        <span>Gestión de Congresos</span>
        <i class="fas fa-chevron-down ms-auto"></i>
      </a>
      <ul class="submenu">
        <li><a href="#" class="sidebar-link" onclick="loadPage('<?php echo plugin_dir_url(__FILE__); ?>congresos/migracion.php')">
          <i class="fas fa-exchange-alt"></i>
          <span>Migracion</span>
        </a></li>
        <li><a href="#" class="sidebar-link" onclick="loadPage('<?php echo plugin_dir_url(__FILE__); ?>congresos/crear_congreso.html')">
          <i class="fas fa-plus"></i>
          <span>Crear Congreso</span>
        </a></li>
        <li><a href="#" class="sidebar-link" onclick="loadPage('<?php echo plugin_dir_url(__FILE__); ?>congresos/busqueda_congresos.php')">
          <i class="fas fa-search"></i>
          <span>Lista de Congreso</span>
        </a></li>
      </ul>
    </div>

    <a href="<?php echo wp_logout_url(); ?>" class="sidebar-link logout-link">
      <i class="fas fa-sign-out-alt"></i>
      <span>Cerrar Sesión</span>
    </a>
  </nav>

  <!-- Main Wrapper con Header y Contenido -->
  <div class="main-wrapper-v1">
    <!-- Header con info de usuario y toggle -->
    <header class="top-header-v1">
      <?php
      $current_user = wp_get_current_user();
      $user_roles = $current_user->roles;
      $is_admin_or_super = in_array('administrator', $user_roles) || in_array('plg_super_admin', $user_roles);
      
      // Obtener nombre del rol
      $role_map = array(
        'administrator' => 'Administrador',
        'plg_super_admin' => 'Super Admin',
        'plg_office_manager' => 'Office Manager',
        'plg_staff' => 'Staff',
      );
      
      $display_role = 'Usuario';
      foreach (array('administrator', 'plg_super_admin', 'plg_office_manager', 'plg_staff') as $role) {
        if (in_array($role, $user_roles)) {
          $display_role = $role_map[$role];
          break;
        }
      }
      
      // Obtener iniciales
      $name_parts = explode(' ', trim($current_user->display_name));
      if (count($name_parts) === 1) {
        $initials = strtoupper(substr($name_parts[0], 0, 2));
      } else {
        $initials = strtoupper($name_parts[0][0] . $name_parts[count($name_parts) - 1][0]);
      }
      ?>
      
      <div class="user-info-v1">
        <div class="user-avatar-v1" title="<?php echo esc_attr($current_user->display_name); ?>">
          <?php echo $initials; ?>
        </div>
        <div class="user-details-v1">
          <div class="user-name-v1"><?php echo esc_html($current_user->display_name); ?></div>
          <div class="user-role-v1"><?php echo esc_html($display_role); ?></div>
        </div>
      </div>
      
      <?php if ($is_admin_or_super): ?>
      <div class="dashboard-toggle-v1">
        <label for="dashboard-version-toggle-v1">Dashboard V1 / V2</label>
        <label class="toggle-switch-v1">
          <input type="checkbox" id="dashboard-version-toggle-v1">
          <span class="toggle-slider-v1"></span>
        </label>
      </div>
      <?php endif; ?>
    </header>

    <!-- Contenido principal -->
    <div id="content">
      <iframe id="contentFrame" src="<?php echo plugin_dir_url(__FILE__); ?>main.html"></iframe>
    </div>
  </div>
</div>

<script>
  // Función para alternar los submenús
  function toggleMenu(element) {
    const allSubmenus = document.querySelectorAll('.submenu');
    const allCollapsed = document.querySelectorAll('.sidebar-link');
    
    allSubmenus.forEach(submenu => {
      if (submenu !== element.nextElementSibling) {
        submenu.style.display = 'none';
      }
    });
    
    allCollapsed.forEach(item => {
      if (item !== element) {
        item.classList.remove('collapsed');
      }
    });
    
    element.classList.toggle('collapsed');
    const submenu = element.nextElementSibling;
    if (submenu && submenu.classList.contains('submenu')) {
      submenu.style.display = element.classList.contains('collapsed') ? 'block' : 'none';
    }
  }

  // Función para cargar la página en el iframe
  function loadPage(url) {
    document.getElementById('contentFrame').src = url;
    document.querySelectorAll('.sidebar-link').forEach(link => {
      link.classList.remove('active');
    });
    event.currentTarget.classList.add('active');
    
    // En móvil, cerrar el menú después de seleccionar una opción
    if (window.innerWidth <= 768) {
      toggleSidebar();
    }
  }

  // Función para manejar el responsive
  function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
    document.getElementById('overlay').classList.toggle('active');
  }

  // Event listeners
  document.addEventListener('DOMContentLoaded', function() {
    // Cerrar submenús al inicio
    const allSubmenus = document.querySelectorAll('.submenu');
    allSubmenus.forEach(submenu => {
      submenu.style.display = 'none';
    });

    // Event listener para el botón toggle
    document.getElementById('sidebarToggle').addEventListener('click', toggleSidebar);

    // Event listener para el overlay
    document.getElementById('overlay').addEventListener('click', toggleSidebar);

    // Cerrar menú al cambiar el tamaño de la ventana
    window.addEventListener('resize', function() {
      if (window.innerWidth > 768) {
        document.getElementById('sidebar').classList.remove('active');
        document.getElementById('overlay').classList.remove('active');
      }
    });

    // Manejar el toggle de dashboard V1/V2
    const dashboardToggle = document.getElementById('dashboard-version-toggle-v1');
    if (dashboardToggle) {
      dashboardToggle.addEventListener('change', function(event) {
        if (event.target.checked) {
          // Cambiar a V2
          const baseUrl = window.location.origin;
          const pathPrefix = window.location.pathname.includes('/genesis/') ? '/genesis' : '';
          
          // Redirigir al dashboard v2
          window.location.href = baseUrl + pathPrefix + '/dashboard-v2';
        }
      });
    }
  });
</script>

<?php wp_footer(); ?>
</body>
</html>