<?php
if (!defined('ABSPATH')) { exit; }

/**
 * Controlador temporal para vincular usuarios contact_viewer con sus contactos
 */
class PlgGenesis_ContactViewerFixController {
	
	public static function register_routes() {
		// Listar usuarios contact_viewer sin vincular
		register_rest_route('plg-genesis/v1', '/admin/contact-viewers/unlinked', [
			'methods'             => 'GET',
			'callback'            => [ __CLASS__, 'list_unlinked' ],
			'permission_callback' => function() {
				return current_user_can('administrator');
			}
		]);
		
		// Vincular usuario con contacto
		register_rest_route('plg-genesis/v1', '/admin/contact-viewers/link', [
			'methods'             => 'POST',
			'callback'            => [ __CLASS__, 'link_user' ],
			'permission_callback' => function() {
				return current_user_can('administrator');
			}
		]);
	}
	
	/**
	 * Lista todos los usuarios contact_viewer y su estado de vinculación
	 */
	public static function list_unlinked($request) {
		$users = get_users(['role' => 'plg_contact_viewer']);
		$result = [];
		
		foreach ($users as $user) {
			$contacto_id = get_user_meta($user->ID, 'contacto_id', true);
			$oficina = get_user_meta($user->ID, 'oficina', true);
			
			$result[] = [
				'user_id' => $user->ID,
				'username' => $user->user_login,
				'display_name' => $user->display_name,
				'email' => $user->user_email,
				'contacto_id' => $contacto_id ? intval($contacto_id) : null,
				'oficina' => $oficina ?: null,
				'created' => $user->user_registered,
				'is_linked' => !empty($contacto_id),
			];
		}
		
		return new WP_REST_Response(['success' => true, 'data' => $result], 200);
	}
	
	/**
	 * Vincula un usuario con un contacto
	 */
	public static function link_user($request) {
		$payload = $request->get_json_params();
		$user_id = intval($payload['user_id'] ?? 0);
		$contacto_id = intval($payload['contacto_id'] ?? 0);
		
		if (!$user_id || !$contacto_id) {
			return new WP_REST_Response([
				'success' => false,
				'error' => ['code' => 'invalid_params', 'message' => 'Faltan parámetros']
			], 400);
		}
		
		// Verificar que el usuario existe y es contact_viewer
		$user = get_userdata($user_id);
		if (!$user || !in_array('plg_contact_viewer', $user->roles)) {
			return new WP_REST_Response([
				'success' => false,
				'error' => ['code' => 'invalid_user', 'message' => 'Usuario no válido']
			], 400);
		}
		
		// Vincular
		update_user_meta($user_id, 'contacto_id', $contacto_id);
		
		return new WP_REST_Response([
			'success' => true,
			'data' => [
				'user_id' => $user_id,
				'contacto_id' => $contacto_id,
				'message' => 'Usuario vinculado exitosamente'
			]
		], 200);
	}
}

