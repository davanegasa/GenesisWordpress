<?php
if (!defined('ABSPATH')) { exit; }

class PlgGenesis_AuthController {
	public static function register_routes() {
		register_rest_route('plg-genesis/v1', '/auth/login', [
			'methods'             => 'POST',
			'callback'            => [ __CLASS__, 'post_login' ],
			'permission_callback' => '__return_true',
		]);
		register_rest_route('plg-genesis/v1', '/auth/logout', [
			'methods'             => 'POST',
			'callback'            => [ __CLASS__, 'post_logout' ],
			'permission_callback' => function(){ plg_genesis_validate_user_from_cookie(); return is_user_logged_in(); },
		]);
	}

	public static function post_login($request) {
		$payload   = $request->get_json_params();
		$username  = isset($payload['username']) ? sanitize_user($payload['username']) : '';
		$password  = isset($payload['password']) ? (string)$payload['password'] : '';
		$remember  = !empty($payload['remember']);

		if ($username === '' || $password === '') {
			return new WP_REST_Response([
				'success' => false,
				'error'   => [ 'code' => 'invalid_request', 'message' => 'username y password son requeridos' ]
			], 400);
		}

		$creds = [
			'user_login'    => $username,
			'user_password' => $password,
			'remember'      => $remember,
		];
		$user = wp_signon($creds, is_ssl());
		if (is_wp_error($user)) {
			return new WP_REST_Response([
				'success' => false,
				'error'   => [ 'code' => 'invalid_credentials', 'message' => $user->get_error_message() ]
			], 401);
		}

		wp_set_current_user($user->ID);
		wp_set_auth_cookie($user->ID, $remember, is_ssl());

		$data = [
			'nonce' => wp_create_nonce('wp_rest'),
			'user'  => [
				'id'       => $user->ID,
				'username' => $user->user_login,
				'name'     => $user->display_name,
				'email'    => $user->user_email,
			],
		];
		return new WP_REST_Response([ 'success' => true, 'data' => $data ], 200);
	}

	public static function post_logout($request) {
		if (!is_user_logged_in()) {
			return new WP_REST_Response([
				'success' => false,
				'error'   => [ 'code' => 'not_logged_in', 'message' => 'Usuario no autenticado' ]
			], 401);
		}
		wp_logout();
		return new WP_REST_Response([ 'success' => true, 'data' => [ 'ok' => true ] ], 200);
	}
}


