<?php
if (!defined('ABSPATH')) { exit; }

require_once dirname(__FILE__, 3) . '/infrastructure/OfficeResolver.php';
require_once dirname(__FILE__, 3) . '/setup/roles.php';

class PlgGenesis_UsersController {
	public static function register_routes() {
		// Listar usuarios
		register_rest_route('plg-genesis/v1', '/users', [
			'methods'             => 'GET',
			'callback'            => [ __CLASS__, 'get_users' ],
			'permission_callback' => plg_genesis_can('plg_view_users')
		]);

		// Obtener usuario específico
		register_rest_route('plg-genesis/v1', '/users/(?P<id>[0-9]+)', [
			'methods'             => 'GET',
			'callback'            => [ __CLASS__, 'get_user' ],
			'permission_callback' => plg_genesis_can('plg_view_users')
		]);

		// Crear usuario
		register_rest_route('plg-genesis/v1', '/users', [
			'methods'             => 'POST',
			'callback'            => [ __CLASS__, 'post_user' ],
			'permission_callback' => plg_genesis_can('plg_create_users')
		]);

		// Actualizar usuario
		register_rest_route('plg-genesis/v1', '/users/(?P<id>[0-9]+)', [
			'methods'             => 'PUT',
			'callback'            => [ __CLASS__, 'put_user' ],
			'permission_callback' => plg_genesis_can('plg_edit_users')
		]);

		// Eliminar usuario
		register_rest_route('plg-genesis/v1', '/users/(?P<id>[0-9]+)', [
			'methods'             => 'DELETE',
			'callback'            => [ __CLASS__, 'delete_user' ],
			'permission_callback' => plg_genesis_can('plg_delete_users')
		]);

		// Obtener roles asignables
		register_rest_route('plg-genesis/v1', '/users/roles/assignable', [
			'methods'             => 'GET',
			'callback'            => [ __CLASS__, 'get_assignable_roles' ],
			'permission_callback' => plg_genesis_can('plg_view_users')
		]);
	}

	public static function get_users($request) {
		$current_user_id = get_current_user_id();
		$current_office = PlgGenesis_OfficeResolver::resolve_user_office($current_user_id);
		$is_super = current_user_can('plg_switch_office');

		// Parámetros de búsqueda y paginación
		$search = $request->get_param('q') ?: '';
		$page = max(1, (int) $request->get_param('page') ?: 1);
		$limit = min(100, max(1, (int) $request->get_param('limit') ?: 20));

		// Query args
		$args = [
			'number' => $limit,
			'offset' => ($page - 1) * $limit,
		];

		if ($search) {
			$args['search'] = '*' . esc_attr($search) . '*';
			$args['search_columns'] = ['user_login', 'user_email', 'display_name'];
		}

		// Si no es Super Admin, solo ver usuarios de su oficina
		if (!$is_super && !is_wp_error($current_office)) {
			$args['meta_query'] = [
				[
					'key' => 'oficina',
					'value' => $current_office,
					'compare' => '='
				]
			];
		}

		$user_query = new WP_User_Query($args);
		$users = $user_query->get_results();
		$total = $user_query->get_total();

		$data = array_map(function($user) {
			return [
				'id' => $user->ID,
				'login' => $user->user_login,
				'email' => $user->user_email,
				'name' => $user->display_name,
				'roles' => $user->roles,
				'office' => get_user_meta($user->ID, 'oficina', true),
			];
		}, $users);

		return new WP_REST_Response([
			'success' => true,
			'data' => [
				'users' => $data,
				'pagination' => [
					'page' => $page,
					'limit' => $limit,
					'total' => $total,
					'pages' => ceil($total / $limit),
				]
			]
		], 200);
	}

	public static function get_user($request) {
		$user_id = (int) $request['id'];
		$current_user_id = get_current_user_id();

		// Verificar si puede gestionar este usuario
		if (!PlgGenesis_Roles::can_manage_user($current_user_id, $user_id)) {
			return self::error('No tienes permiso para ver este usuario', 'forbidden', 403);
		}

		$user = get_userdata($user_id);
		if (!$user) {
			return self::error('Usuario no encontrado', 'not_found', 404);
		}

		return new WP_REST_Response([
			'success' => true,
			'data' => [
				'id' => $user->ID,
				'login' => $user->user_login,
				'email' => $user->user_email,
				'name' => $user->display_name,
				'roles' => $user->roles,
				'office' => get_user_meta($user->ID, 'oficina', true),
			]
		], 200);
	}

	public static function post_user($request) {
		$payload = $request->get_json_params();
		$current_user_id = get_current_user_id();
		$current_office = PlgGenesis_OfficeResolver::resolve_user_office($current_user_id);
		$is_super = current_user_can('plg_switch_office');

		// Validar campos requeridos
		$required = ['username', 'email', 'password', 'role'];
		foreach ($required as $field) {
			if (empty($payload[$field])) {
				return self::error("Campo requerido: {$field}", 'missing_field', 400);
			}
		}

		// Validar que el rol sea asignable
		$assignable_roles = PlgGenesis_Roles::get_assignable_roles($current_user_id);
		if (!isset($assignable_roles[$payload['role']])) {
			return self::error('No puedes asignar este rol', 'invalid_role', 403);
		}

		// Determinar oficina
		$office = $payload['office'] ?? null;
		if (!$is_super) {
			// Si no es Super Admin, usar su oficina
			if (is_wp_error($current_office)) {
				return self::error($current_office);
			}
			$office = $current_office;
		}

		if (!$office) {
			return self::error('Oficina requerida', 'missing_office', 400);
		}

		// Crear usuario
		$user_id = wp_create_user(
			sanitize_user($payload['username']),
			$payload['password'],
			sanitize_email($payload['email'])
		);

		if (is_wp_error($user_id)) {
			return self::error($user_id->get_error_message(), 'user_creation_failed', 400);
		}

		// Asignar rol
		$user = new WP_User($user_id);
		$user->set_role($payload['role']);

		// Asignar oficina
		update_user_meta($user_id, 'oficina', $office);

		// Actualizar nombre si se proporcionó
		if (!empty($payload['name'])) {
			wp_update_user([
				'ID' => $user_id,
				'display_name' => sanitize_text_field($payload['name'])
			]);
		}

		return new WP_REST_Response([
			'success' => true,
			'data' => [
				'id' => $user_id,
				'message' => 'Usuario creado exitosamente'
			]
		], 201);
	}

	public static function put_user($request) {
		$user_id = (int) $request['id'];
		$payload = $request->get_json_params();
		$current_user_id = get_current_user_id();

		// Verificar si puede gestionar este usuario
		if (!PlgGenesis_Roles::can_manage_user($current_user_id, $user_id)) {
			return self::error('No tienes permiso para editar este usuario', 'forbidden', 403);
		}

		$user = get_userdata($user_id);
		if (!$user) {
			return self::error('Usuario no encontrado', 'not_found', 404);
		}

		$update_data = ['ID' => $user_id];

		// Actualizar email
		if (isset($payload['email']) && $payload['email'] !== $user->user_email) {
			$update_data['user_email'] = sanitize_email($payload['email']);
		}

		// Actualizar nombre
		if (isset($payload['name'])) {
			$update_data['display_name'] = sanitize_text_field($payload['name']);
		}

		// Actualizar password si se proporciona
		if (!empty($payload['password'])) {
			$update_data['user_pass'] = $payload['password'];
		}

		// Actualizar usuario
		if (count($update_data) > 1) {
			$result = wp_update_user($update_data);
			if (is_wp_error($result)) {
				return self::error($result->get_error_message(), 'update_failed', 400);
			}
		}

		// Actualizar rol si se proporciona
		if (isset($payload['role'])) {
			$assignable_roles = PlgGenesis_Roles::get_assignable_roles($current_user_id);
			if (!isset($assignable_roles[$payload['role']])) {
				return self::error('No puedes asignar este rol', 'invalid_role', 403);
			}
			$user->set_role($payload['role']);
		}

		// Actualizar oficina si es Super Admin
		if (isset($payload['office']) && current_user_can('plg_switch_office')) {
			update_user_meta($user_id, 'oficina', $payload['office']);
		}

		return new WP_REST_Response([
			'success' => true,
			'data' => ['message' => 'Usuario actualizado exitosamente']
		], 200);
	}

	public static function delete_user($request) {
		$user_id = (int) $request['id'];
		$current_user_id = get_current_user_id();

		// No puede eliminarse a sí mismo
		if ($user_id === $current_user_id) {
			return self::error('No puedes eliminar tu propio usuario', 'forbidden', 403);
		}

		// Verificar si puede gestionar este usuario
		if (!PlgGenesis_Roles::can_manage_user($current_user_id, $user_id)) {
			return self::error('No tienes permiso para eliminar este usuario', 'forbidden', 403);
		}

		require_once(ABSPATH . 'wp-admin/includes/user.php');
		$result = wp_delete_user($user_id);

		if (!$result) {
			return self::error('Error al eliminar usuario', 'delete_failed', 500);
		}

		return new WP_REST_Response([
			'success' => true,
			'data' => ['message' => 'Usuario eliminado exitosamente']
		], 200);
	}

	public static function get_assignable_roles($request) {
		$current_user_id = get_current_user_id();
		$roles = PlgGenesis_Roles::get_assignable_roles($current_user_id);

		return new WP_REST_Response([
			'success' => true,
			'data' => $roles
		], 200);
	}

	private static function error($msg, $code = 'error', $status = 500) {
		if ($msg instanceof WP_Error) {
			$code = $msg->get_error_code();
			$status = $msg->get_error_data()['status'] ?? 500;
			$msg = $msg->get_error_message();
		}
		return new WP_REST_Response([
			'success' => false,
			'error' => [
				'code' => $code,
				'message' => $msg
			]
		], $status);
	}
}

