<?php
/*
Plugin Name: Registro de Estudiantes
Description: Plugin personalizado para registrar estudiantes desde un formulario en PostgreSQL.
Version: 1.0
Author: Daniel
*/

// Bootstrap API-first (endpoints REST)
require_once plugin_dir_path(__FILE__) . 'backend/bootstrap.php';

// Flag de característica para activar consumo de API desde el frontend
function plg_genesis_activate() {
    if (get_option('plg_genesis_use_api') === false) {
        add_option('plg_genesis_use_api', '0');
    }
}
register_activation_hook(__FILE__, 'plg_genesis_activate');

// Ocultar la barra de administración para todos los usuarios excepto administradores
add_filter('show_admin_bar', function($show) {
    if (!current_user_can('administrator')) {
        return false; // Ocultar barra de administración
    }
    return $show; // Mantener barra de administración para administradores
});


// Shortcode para mostrar el contenido del dashboard solo en la página del dashboard
function shortcode_mostrar_dashboard() {
    // Verificar si el usuario está logueado
    if (is_user_logged_in()) { 
        ob_start(); // Iniciar la captura de contenido

        // Verificar si la página actual es el dashboard
        if (is_page('dashboard')) {
            // Registrar en el log la ruta usada para incluir el archivo
            error_log('Cargando archivo dashboard.php desde: ' . plugin_dir_path(__FILE__) . 'frontend/dashboard.php');

            // Incluir el archivo dashboard.php que está en la carpeta frontend
            include plugin_dir_path(__FILE__) . 'frontend/dashboard.php';
        }

        return ob_get_clean(); // Devolver el contenido capturado
    } else {
        // Si no está logueado, redirigir a la página de inicio de sesión predeterminada de WordPress
        wp_redirect(wp_login_url()); 
        exit;
    }
}
add_shortcode('mostrar_dashboard', 'shortcode_mostrar_dashboard');


// Redirigir usuarios al dashboard después de iniciar sesión
function redirigir_despues_login($redirect_to, $request, $user) {
    // Verificar si el usuario está logueado y tiene un rol asignado
    if (isset($user->roles) && is_array($user->roles)) {
        // Redirigir todos los usuarios (incluyendo administradores) al dashboard
        return home_url('/dashboard');  // Cambia 'dashboard' por el slug correcto de tu página
    }

    // Redirigir al destino original si no se cumple la condición
    return $redirect_to;
}
add_filter('login_redirect', 'redirigir_despues_login', 10, 3);



// Proteger la página del dashboard para usuarios no logueados
function proteger_dashboard_para_registrados() {
    // Verificar si el usuario está logueado
    if (!is_user_logged_in()) {
        // Si el usuario no está logueado, redirigir a la página de login predeterminada
        wp_redirect(wp_login_url());
        exit;
    }
}
add_action('template_redirect', 'proteger_dashboard_para_registrados');


// Shortcode para Dashboard v2 (API-first)
function shortcode_mostrar_dashboard_v2() {
    if (is_user_logged_in()) {
        ob_start();
        if (is_page('dashboard')) {
            include plugin_dir_path(__FILE__) . 'frontendv2/dashboard.php';
        } else {
            include plugin_dir_path(__FILE__) . 'frontendv2/dashboard.php';
        }
        return ob_get_clean();
    } else {
        wp_redirect(wp_login_url());
        exit;
    }
}
add_shortcode('mostrar_dashboard_v2', 'shortcode_mostrar_dashboard_v2');

// Renderizar Dashboard v2 como página completa (sin header del tema)
function plg_genesis_render_dashboard_v2_fullpage() {
    if (!is_page('dashboard-v2')) {
        return;
    }
    if (!is_user_logged_in()) {
        wp_redirect(wp_login_url());
        exit;
    }
    include plugin_dir_path(__FILE__) . 'frontendv2/dashboard.php';
    exit;
}
add_action('template_redirect', 'plg_genesis_render_dashboard_v2_fullpage', 20);


// Mostrar el campo de oficina en el perfil del usuario solo para administradores
function mostrar_campo_oficina($user) {
    // Verificar si el usuario actual tiene permisos de administrador
    if (current_user_can('administrator')) {
        $oficina = get_user_meta($user->ID, 'oficina', true);
        ?>
        <h3>Información de la Oficina</h3>
        <table class="form-table">
            <tr>
                <th><label for="oficina">Oficina</label></th>
                <td>
                    <select name="oficina" id="oficina">
                        <option value="BOG" <?php selected($oficina, 'BOG'); ?>>Bogotá</option>
                        <option value="PER" <?php selected($oficina, 'PER'); ?>>Pereira</option>
                        <option value="BUC" <?php selected($oficina, 'BUC'); ?>>Bucaramanga</option>
                        <option value="BAR" <?php selected($oficina, 'BAR'); ?>>Barranquilla</option>
                        <option value="BO" <?php selected($oficina, 'BO'); ?>>Puerto Rico</option>
                        <option value="PR" <?php selected($oficina, 'PR'); ?>>Puerto Rico</option>
                        <option value="FDL" <?php selected($oficina, 'FDL'); ?>>Fuente de Luz - Colombia</option>
                    </select>
                    <p class="description">Asigna la oficina a este usuario.</p>
                </td>
            </tr>
        </table>
        <?php
    }
}
add_action('show_user_profile', 'mostrar_campo_oficina');
add_action('edit_user_profile', 'mostrar_campo_oficina');
// Guardar el valor del campo 'oficina' solo si el usuario actual es administrador
function guardar_campo_oficina($user_id) {
    // Verificar si el usuario actual tiene permisos de administrador
    if (current_user_can('administrator')) {
        // Guardar el valor del campo 'oficina' en los meta datos del usuario
        update_user_meta($user_id, 'oficina', sanitize_text_field($_POST['oficina']));
    }
}
add_action('personal_options_update', 'guardar_campo_oficina');
add_action('edit_user_profile_update', 'guardar_campo_oficina');