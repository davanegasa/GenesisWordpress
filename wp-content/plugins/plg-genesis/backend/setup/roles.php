<?php
if (!defined('ABSPATH')) { exit; }

/**
 * Setup de roles y capabilities para plg-genesis
 * 
 * Define 4 roles principales:
 * - Super Admin: acceso total, multi-oficina
 * - Office Manager: administrador de su oficina
 * - Office Staff: operativo de su oficina
 * - Office Viewer: solo lectura de su oficina
 */
class PlgGenesis_Roles {
	
	/**
	 * Define todas las capabilities del plugin
	 */
	public static function get_all_capabilities() {
		return [
			// ESTUDIANTES
			'plg_view_students',
			'plg_create_students',
			'plg_edit_students',
			'plg_delete_students',
			
			// CURSOS
			'plg_view_courses',
			'plg_assign_courses',
			'plg_create_courses',
			'plg_edit_courses',
			'plg_delete_courses',
			
			// PROGRAMAS
			'plg_view_programs',
			'plg_create_programs',
			'plg_edit_programs',
			'plg_delete_programs',
			
			// CONTACTOS
			'plg_view_contacts',
			'plg_create_contacts',
			'plg_edit_contacts',
			'plg_delete_contacts',
			
			// CONGRESOS
			'plg_view_events',
			'plg_create_events',
			'plg_edit_events',
			'plg_delete_events',
			
			// ESTADÍSTICAS
			'plg_view_stats',
			'plg_export_stats',
			
			// TEMA
			'plg_view_theme',
			'plg_change_theme',
			
			// USUARIOS
			'plg_view_users',
			'plg_create_users',
			'plg_edit_users',
			'plg_delete_users',
			
			// ESPECIALES
			'plg_switch_office',    // Cambiar entre oficinas (solo Super Admin)
			'plg_view_swagger',     // Acceder a documentación API
		];
	}
	
	/**
	 * Define las capabilities por rol
	 */
	public static function get_role_capabilities() {
		return [
			// OFFICE VIEWER - Solo lectura
			'plg_office_viewer' => [
				'read' => true, // Capability base de WordPress
				'plg_view_students',
				'plg_view_courses',
				'plg_view_programs',
				'plg_view_contacts',
				'plg_view_events',
				'plg_view_stats',
				'plg_view_theme',
			],
			
			// OFFICE STAFF - Operativo
			'plg_office_staff' => [
				'read' => true,
				'plg_view_students',
				'plg_create_students',
				'plg_edit_students',
				'plg_view_courses',
				'plg_assign_courses',
				'plg_view_programs',
				'plg_view_contacts',
				'plg_create_contacts',
				'plg_edit_contacts',
				'plg_view_events',
				'plg_create_events',
				'plg_edit_events',
				'plg_view_stats',
				'plg_view_theme',
				'plg_view_swagger',
			],
			
			// OFFICE MANAGER - Administrador de oficina
			'plg_office_manager' => [
				'read' => true,
				// Estudiantes: full CRUD
				'plg_view_students',
				'plg_create_students',
				'plg_edit_students',
				'plg_delete_students',
				// Cursos: full CRUD
				'plg_view_courses',
				'plg_assign_courses',
				'plg_create_courses',
				'plg_edit_courses',
				'plg_delete_courses',
				// Programas: full CRUD
				'plg_view_programs',
				'plg_create_programs',
				'plg_edit_programs',
				'plg_delete_programs',
				// Contactos: full CRUD
				'plg_view_contacts',
				'plg_create_contacts',
				'plg_edit_contacts',
				'plg_delete_contacts',
				// Congresos: full CRUD
				'plg_view_events',
				'plg_create_events',
				'plg_edit_events',
				'plg_delete_events',
				// Estadísticas: ver y exportar
				'plg_view_stats',
				'plg_export_stats',
				// Tema: ver y cambiar
				'plg_view_theme',
				'plg_change_theme',
				// Usuarios: gestión completa de su oficina
				'plg_view_users',
				'plg_create_users',
				'plg_edit_users',
				'plg_delete_users',
				// Swagger
				'plg_view_swagger',
			],
			
			// SUPER ADMIN - Acceso total multi-oficina
			'plg_super_admin' => array_merge(
				['read' => true],
				array_fill_keys(self::get_all_capabilities(), true)
			),
		];
	}
	
	/**
	 * Crea o actualiza todos los roles del plugin
	 */
	public static function setup_roles() {
		$role_capabilities = self::get_role_capabilities();
		
		foreach ($role_capabilities as $role_slug => $capabilities) {
			// Eliminar el rol si existe para recrearlo limpio
			remove_role($role_slug);
			
			// Crear el rol con su nombre amigable
			$role_name = self::get_role_display_name($role_slug);
			add_role($role_slug, $role_name, $capabilities);
		}
		
		// Agregar capabilities al administrator nativo de WordPress
		// (así los admins de WordPress también pueden usar el plugin)
		$admin_role = get_role('administrator');
		if ($admin_role) {
			foreach (self::get_all_capabilities() as $cap) {
				$admin_role->add_cap($cap);
			}
		}
	}
	
	/**
	 * Elimina todos los roles del plugin
	 */
	public static function remove_roles() {
		$roles = ['plg_office_viewer', 'plg_office_staff', 'plg_office_manager', 'plg_super_admin'];
		foreach ($roles as $role) {
			remove_role($role);
		}
		
		// Remover capabilities del administrator
		$admin_role = get_role('administrator');
		if ($admin_role) {
			foreach (self::get_all_capabilities() as $cap) {
				$admin_role->remove_cap($cap);
			}
		}
	}
	
	/**
	 * Nombre amigable para mostrar en UI
	 */
	public static function get_role_display_name($role_slug) {
		$names = [
			'plg_office_viewer'  => 'Visualizador de Oficina',
			'plg_office_staff'   => 'Personal de Oficina',
			'plg_office_manager' => 'Administrador de Oficina',
			'plg_super_admin'    => 'Super Administrador',
		];
		return $names[$role_slug] ?? $role_slug;
	}
	
	/**
	 * Obtiene los roles que un usuario puede asignar
	 * (un usuario solo puede asignar roles de su nivel o inferiores)
	 */
	public static function get_assignable_roles($user_id) {
		$user = get_userdata($user_id);
		if (!$user) { return []; }
		
		$user_roles = $user->roles;
		
		// Super Admin puede asignar todos los roles
		if (in_array('plg_super_admin', $user_roles) || in_array('administrator', $user_roles)) {
			return [
				'plg_super_admin'    => 'Super Administrador',
				'plg_office_manager' => 'Administrador de Oficina',
				'plg_office_staff'   => 'Personal de Oficina',
				'plg_office_viewer'  => 'Visualizador de Oficina',
			];
		}
		
		// Office Manager solo puede asignar Staff y Viewer
		if (in_array('plg_office_manager', $user_roles)) {
			return [
				'plg_office_staff'  => 'Personal de Oficina',
				'plg_office_viewer' => 'Visualizador de Oficina',
			];
		}
		
		// Otros roles no pueden asignar
		return [];
	}
	
	/**
	 * Verifica si el usuario actual puede gestionar a otro usuario
	 * (mismo nivel o inferior, y misma oficina si no es Super Admin)
	 */
	public static function can_manage_user($manager_id, $target_user_id) {
		$manager = get_userdata($manager_id);
		$target = get_userdata($target_user_id);
		
		if (!$manager || !$target) { return false; }
		
		// Super Admin puede gestionar a todos
		if (in_array('plg_super_admin', $manager->roles) || in_array('administrator', $manager->roles)) {
			return true;
		}
		
		// Office Manager solo puede gestionar usuarios de su oficina y nivel inferior
		if (in_array('plg_office_manager', $manager->roles)) {
			$manager_office = get_user_meta($manager_id, 'oficina', true);
			$target_office = get_user_meta($target_user_id, 'oficina', true);
			
			// Misma oficina
			if ($manager_office !== $target_office) { return false; }
			
			// Solo puede gestionar Staff y Viewer
			$allowed_roles = ['plg_office_staff', 'plg_office_viewer'];
			foreach ($target->roles as $role) {
				if (!in_array($role, $allowed_roles)) {
					return false;
				}
			}
			
			return true;
		}
		
		return false;
	}
}

