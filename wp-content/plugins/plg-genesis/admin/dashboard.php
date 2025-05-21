<?php
// Archivo: admin/dashboard.php
// Estructura inicial del dashboard administrativo para Genesis

if (!defined('ABSPATH')) exit;

function genesis_admin_dashboard_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('No tienes permisos suficientes para acceder a esta página.'));
    }
    ?>
    <div class="wrap">
        <h1>Dashboard Administrativo Genesis</h1>
        <p>Visualización básica de logs del sistema.</p>
        <div id="genesis-logs-container">
            <button class="button" onclick="genesisLoadLogs('backend')">Ver Logs Backend</button>
            <button class="button" onclick="genesisLoadLogs('frontend')">Ver Logs Frontend</button>
            <pre id="genesis-logs-output" style="background:#222;color:#eee;padding:1em;max-height:400px;overflow:auto;"></pre>
        </div>
        <script>
        function genesisLoadLogs(tipo) {
            fetch(ajaxurl + '?action=genesis_get_logs&type=' + tipo)
                .then(r => r.text())
                .then(txt => {
                    document.getElementById('genesis-logs-output').textContent = txt;
                });
        }
        </script>
    </div>
    <?php
}

// Registrar la página en el menú de administración
function genesis_admin_menu_dashboard() {
    add_menu_page(
        'Genesis Dashboard',
        'Genesis Dashboard',
        'manage_options',
        'genesis-dashboard',
        'genesis_admin_dashboard_page',
        'dashicons-admin-tools',
        3
    );
}
add_action('admin_menu', 'genesis_admin_menu_dashboard');

// Endpoint AJAX para leer logs
add_action('wp_ajax_genesis_get_logs', function() {
    if (!current_user_can('manage_options')) {
        wp_die('No autorizado');
    }
    $type = $_GET['type'] ?? 'backend';
    $log_file = $type === 'frontend'
        ? plugin_dir_path(__FILE__) . '/../genesis_frontend.log'
        : plugin_dir_path(__FILE__) . '/../genesis.log';
    if (!file_exists($log_file)) {
        echo 'No hay logs.';
        wp_die();
    }
    // Leer solo las últimas 200 líneas para no saturar
    $lines = file($log_file);
    $last_lines = array_slice($lines, -200);
    echo esc_html(implode('', $last_lines));
    wp_die();
}); 