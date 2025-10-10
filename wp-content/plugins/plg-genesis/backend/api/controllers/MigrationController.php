<?php
if (!defined('ABSPATH')) { exit; }

require_once dirname(__FILE__, 3) . '/setup/roles.php';

/**
 * Controlador temporal para migración de usuarios a nuevos roles
 * NOTA: Este controlador debe eliminarse después de la migración
 */
class PlgGenesis_MigrationController {
	public static function register_routes() {
		// Listar todos los usuarios con sus roles actuales
		register_rest_route('plg-genesis/v1', '/migration/users', [
			'methods'             => 'GET',
			'callback'            => [ __CLASS__, 'get_all_users' ],
			'permission_callback' => function() {
				// Solo Super Admin o Administrator de WordPress
				return current_user_can('administrator') || current_user_can('plg_switch_office');
			}
		]);

		// Migrar un usuario específico
		register_rest_route('plg-genesis/v1', '/migration/users/(?P<id>[0-9]+)', [
			'methods'             => 'POST',
			'callback'            => [ __CLASS__, 'migrate_user' ],
			'permission_callback' => function() {
				return current_user_can('administrator') || current_user_can('plg_switch_office');
			}
		]);

		// Migrar todos los usuarios automáticamente
		register_rest_route('plg-genesis/v1', '/migration/auto', [
			'methods'             => 'POST',
			'callback'            => [ __CLASS__, 'auto_migrate' ],
			'permission_callback' => function() {
				return current_user_can('administrator') || current_user_can('plg_switch_office');
			}
		]);

		// Hacer al usuario actual Super Admin (emergencia)
		register_rest_route('plg-genesis/v1', '/migration/make-me-admin', [
			'methods'             => 'POST',
			'callback'            => [ __CLASS__, 'make_me_admin' ],
			'permission_callback' => function() {
				return current_user_can('administrator');
			}
		]);
	}

	public static function get_all_users($request) {
		$users = get_users(['fields' => 'all']);
		
		$data = array_map(function($user) {
			$roles = $user->roles;
			$hasNewRole = false;
			foreach ($roles as $role) {
				if (strpos($role, 'plg_') === 0) {
					$hasNewRole = true;
					break;
				}
			}

			return [
				'id' => $user->ID,
				'login' => $user->user_login,
				'email' => $user->user_email,
				'name' => $user->display_name,
				'roles' => $roles,
				'office' => get_user_meta($user->ID, 'oficina', true) ?: null,
				'hasNewRole' => $hasNewRole,
				'needsMigration' => !$hasNewRole,
			];
		}, $users);

		return new WP_REST_Response([
			'success' => true,
			'data' => $data
		], 200);
	}

	public static function migrate_user($request) {
		$user_id = (int) $request['id'];
		$payload = $request->get_json_params();

		if (!isset($payload['newRole']) || !isset($payload['office'])) {
			return self::error('Se requiere newRole y office', 'missing_params', 400);
		}

		$user = get_userdata($user_id);
		if (!$user) {
			return self::error('Usuario no encontrado', 'not_found', 404);
		}

		// Validar que el rol sea válido
		$valid_roles = ['plg_super_admin', 'plg_office_manager', 'plg_office_staff', 'plg_office_viewer'];
		if (!in_array($payload['newRole'], $valid_roles)) {
			return self::error('Rol inválido', 'invalid_role', 400);
		}

		// Validar oficina
		$valid_offices = ['BOG', 'MED', 'CAL'];
		if (!in_array($payload['office'], $valid_offices)) {
			return self::error('Oficina inválida', 'invalid_office', 400);
		}

		// Asignar nuevo rol (reemplaza el anterior)
		$user->set_role($payload['newRole']);

		// Asignar oficina
		update_user_meta($user_id, 'oficina', $payload['office']);

		return new WP_REST_Response([
			'success' => true,
			'data' => [
				'message' => "Usuario {$user->user_login} migrado exitosamente",
				'newRole' => $payload['newRole'],
				'office' => $payload['office']
			]
		], 200);
	}

	public static function auto_migrate($request) {
		$users = get_users(['fields' => 'all']);
		$migrated = [];
		$skipped = [];

		// Mapeo automático de roles antiguos a nuevos
		$role_mapping = [
			'administrator' => 'plg_super_admin',
			'editor' => 'plg_office_manager',
			'author' => 'plg_office_staff',
			'contributor' => 'plg_office_staff',
			'subscriber' => 'plg_office_viewer',
		];

		foreach ($users as $user) {
			// Si ya tiene un rol del plugin, saltar
			$hasNewRole = false;
			foreach ($user->roles as $role) {
				if (strpos($role, 'plg_') === 0) {
					$hasNewRole = true;
					break;
				}
			}

			if ($hasNewRole) {
				$skipped[] = $user->user_login . ' (ya tiene rol del plugin)';
				continue;
			}

			// Obtener primer rol del usuario
			$current_role = $user->roles[0] ?? 'subscriber';
			$new_role = $role_mapping[$current_role] ?? 'plg_office_viewer';

			// Asignar nuevo rol
			$user->set_role($new_role);

			// Si no tiene oficina, asignar BOG por defecto
			$office = get_user_meta($user->ID, 'oficina', true);
			if (!$office) {
				update_user_meta($user->ID, 'oficina', 'BOG');
				$office = 'BOG';
			}

			$migrated[] = [
				'login' => $user->user_login,
				'oldRole' => $current_role,
				'newRole' => $new_role,
				'office' => $office,
			];
		}

		return new WP_REST_Response([
			'success' => true,
			'data' => [
				'message' => 'Migración automática completada',
				'migrated' => $migrated,
				'skipped' => $skipped,
				'total' => count($migrated),
			]
		], 200);
	}

	public static function make_me_admin($request) {
		$current_user_id = get_current_user_id();
		$user = new WP_User($current_user_id);

		// Asignar rol de Super Admin
		$user->set_role('plg_super_admin');

		// Si no tiene oficina, asignar BOG
		$office = get_user_meta($current_user_id, 'oficina', true);
		if (!$office) {
			update_user_meta($current_user_id, 'oficina', 'BOG');
			$office = 'BOG';
		}

		return new WP_REST_Response([
			'success' => true,
			'data' => [
				'message' => '¡Ahora eres Super Admin!',
				'role' => 'plg_super_admin',
				'office' => $office,
			]
		], 200);
	}

	private static function error($msg, $code = 'error', $status = 500) {
		return new WP_REST_Response([
			'success' => false,
			'error' => [
				'code' => $code,
				'message' => $msg
			]
		], $status);
	}
}

