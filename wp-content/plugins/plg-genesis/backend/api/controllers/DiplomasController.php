<?php
if (!defined('ABSPATH')) { exit; }

require_once plugin_dir_path(__FILE__) . '/../../services/DiplomasService.php';
require_once plugin_dir_path(__FILE__) . '/../../infrastructure/ConnectionProvider.php';

class PlgGenesis_DiplomasController {

	public static function register_routes() {
		register_rest_route('plg-genesis/v1', '/diplomas/elegibles', [
			'methods' => 'GET',
			'callback' => [ __CLASS__, 'get_elegibles' ],
			'permission_callback' => plg_genesis_can('plg_view_students')
		]);

		register_rest_route('plg-genesis/v1', '/diplomas/emitir', [
			'methods' => 'POST',
			'callback' => [ __CLASS__, 'emitir_diploma' ],
			'permission_callback' => plg_genesis_can('plg_create_students')
		]);

		register_rest_route('plg-genesis/v1', '/diplomas/emitir-batch', [
			'methods' => 'POST',
			'callback' => [ __CLASS__, 'emitir_batch' ],
			'permission_callback' => plg_genesis_can('plg_create_students')
		]);

		register_rest_route('plg-genesis/v1', '/diplomas/(?P<id>\d+)/entrega', [
			'methods' => 'PUT',
			'callback' => [ __CLASS__, 'registrar_entrega' ],
			'permission_callback' => plg_genesis_can('plg_edit_students')
		]);

		register_rest_route('plg-genesis/v1', '/diplomas', [
			'methods' => 'GET',
			'callback' => [ __CLASS__, 'listar_diplomas' ],
			'permission_callback' => plg_genesis_can('plg_view_students')
		]);

		register_rest_route('plg-genesis/v1', '/diplomas/(?P<id>\d+)', [
			'methods' => 'GET',
			'callback' => [ __CLASS__, 'get_diploma' ],
			'permission_callback' => plg_genesis_can('plg_view_students')
		]);

		register_rest_route('plg-genesis/v1', '/diplomas/acta-cierre', [
			'methods' => 'GET',
			'callback' => [ __CLASS__, 'acta_cierre' ],
			'permission_callback' => plg_genesis_can('plg_view_students')
		]);

		register_rest_route('plg-genesis/v1', '/diplomas/emitir-todos', [
			'methods' => 'POST',
			'callback' => [ __CLASS__, 'emitir_todos_elegibles' ],
			'permission_callback' => plg_genesis_can('plg_create_students')
		]);

		register_rest_route('plg-genesis/v1', '/actas', [
			'methods' => 'GET',
			'callback' => [ __CLASS__, 'listar_actas' ],
			'permission_callback' => plg_genesis_can('plg_view_students')
		]);

		register_rest_route('plg-genesis/v1', '/actas/(?P<id>\d+)', [
			'methods' => 'GET',
			'callback' => [ __CLASS__, 'get_acta' ],
			'permission_callback' => plg_genesis_can('plg_view_students')
		]);

		register_rest_route('plg-genesis/v1', '/diplomas/proximos-completar/programas', [
			'methods' => 'GET',
			'callback' => [ __CLASS__, 'programas_con_proximos' ],
			'permission_callback' => plg_genesis_can('plg_view_students')
		]);

		register_rest_route('plg-genesis/v1', '/diplomas/proximos-completar', [
			'methods' => 'GET',
			'callback' => [ __CLASS__, 'proximos_completar' ],
			'permission_callback' => plg_genesis_can('plg_view_students')
		]);
	}

	private static function get_service() {
		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) {
			return $office;
		}
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
		if (is_wp_error($conn)) {
			return $conn;
		}
		return new PlgGenesis_DiplomasService($conn);
	}

	/**
	 * GET /plg-genesis/v1/diplomas/elegibles?estudianteId=X o contactoId=Y
	 */
	public static function get_elegibles(WP_REST_Request $request) {
		$service = self::get_service();
		if (is_wp_error($service)) {
			return new WP_REST_Response([
				'success' => false,
				'error' => [ 'code' => 'db_connection_failed', 'message' => $service->get_error_message() ]
			], 500);
		}

		$estudianteId = $request->get_param('estudianteId');
		$contactoId = $request->get_param('contactoId');

		if (($estudianteId && $contactoId) || (!$estudianteId && !$contactoId)) {
			return new WP_REST_Response([
				'success' => false,
				'error' => [ 'code' => 'invalid_params', 'message' => 'Debe proporcionar estudianteId o contactoId' ]
			], 422);
		}

		$result = $service->getElegibles($estudianteId, $contactoId);
		if (is_wp_error($result)) {
			return new WP_REST_Response([
				'success' => false,
				'error' => [ 'code' => $result->get_error_code(), 'message' => $result->get_error_message() ]
			], $result->get_error_data()['status'] ?? 500);
		}

		return new WP_REST_Response([
			'success' => true,
			'data' => $result
		], 200);
	}

	/**
	 * POST /plg-genesis/v1/diplomas/emitir
	 * Body: { tipo, programaId, version, estudianteId, nivelId?, notas? }
	 */
	public static function emitir_diploma(WP_REST_Request $request) {
		$service = self::get_service();
		if (is_wp_error($service)) {
			return new WP_REST_Response([
				'success' => false,
				'error' => [ 'code' => 'db_connection_failed', 'message' => $service->get_error_message() ]
			], 500);
		}

		$body = $request->get_json_params();
		$tipo = $body['tipo'] ?? null;
		$programaId = $body['programaId'] ?? null;
		$version = $body['version'] ?? null;
		$estudianteId = $body['estudianteId'] ?? null;
		$nivelId = $body['nivelId'] ?? null;
		$notas = $body['notas'] ?? null;

		if (!$tipo || !$programaId || !$version || !$estudianteId) {
			return new WP_REST_Response([
				'success' => false,
				'error' => [ 'code' => 'missing_fields', 'message' => 'Campos requeridos: tipo, programaId, version, estudianteId' ]
			], 422);
		}

		$result = $service->emitirDiploma($tipo, $programaId, $version, $estudianteId, $nivelId, $notas);
		if (is_wp_error($result)) {
			return new WP_REST_Response([
				'success' => false,
				'error' => [ 'code' => $result->get_error_code(), 'message' => $result->get_error_message() ]
			], $result->get_error_data()['status'] ?? 500);
		}

		return new WP_REST_Response([
			'success' => true,
			'data' => $result
		], 201);
	}

	/**
	 * POST /plg-genesis/v1/diplomas/emitir-batch
	 * Body: { diplomas: [...], contactoId?, observaciones? }
	 */
	public static function emitir_batch(WP_REST_Request $request) {
		$service = self::get_service();
		if (is_wp_error($service)) {
			return new WP_REST_Response([
				'success' => false,
				'error' => [ 'code' => 'db_connection_failed', 'message' => $service->get_error_message() ]
			], 500);
		}

		$body = $request->get_json_params();
		$diplomas = $body['diplomas'] ?? null;
		$contactoId = $body['contactoId'] ?? null;
		$observaciones = $body['observaciones'] ?? null;

		if (!is_array($diplomas) || empty($diplomas)) {
			return new WP_REST_Response([
				'success' => false,
				'error' => [ 'code' => 'missing_fields', 'message' => 'Campo requerido: diplomas (array)' ]
			], 422);
		}

		$result = $service->emitirBatch($diplomas, $contactoId, $observaciones);
		if (is_wp_error($result)) {
			return new WP_REST_Response([
				'success' => false,
				'error' => [ 
					'code' => $result->get_error_code(), 
					'message' => $result->get_error_message(),
					'details' => $result->get_error_data()
				]
			], $result->get_error_data()['status'] ?? 500);
		}

		return new WP_REST_Response([
			'success' => true,
			'data' => $result
		], 201);
	}

	/**
	 * PUT /plg-genesis/v1/diplomas/{id}/entrega
	 * Body: { fechaEntrega?, notas? }
	 */
	public static function registrar_entrega(WP_REST_Request $request) {
		$service = self::get_service();
		if (is_wp_error($service)) {
			return new WP_REST_Response([
				'success' => false,
				'error' => [ 'code' => 'db_connection_failed', 'message' => $service->get_error_message() ]
			], 500);
		}

		$diplomaId = intval($request->get_param('id'));
		$body = $request->get_json_params();
		$fechaEntrega = $body['fechaEntrega'] ?? null;
		$notas = $body['notas'] ?? null;

		$result = $service->registrarEntrega($diplomaId, $fechaEntrega, $notas);
		if (is_wp_error($result)) {
			return new WP_REST_Response([
				'success' => false,
				'error' => [ 'code' => $result->get_error_code(), 'message' => $result->get_error_message() ]
			], $result->get_error_data()['status'] ?? 500);
		}

		return new WP_REST_Response([
			'success' => true,
			'data' => $result
		], 200);
	}

	/**
	 * GET /plg-genesis/v1/diplomas?estudianteId=X o contactoId=Y&pendientes=true
	 */
	public static function listar_diplomas(WP_REST_Request $request) {
		$service = self::get_service();
		if (is_wp_error($service)) {
			return new WP_REST_Response([
				'success' => false,
				'error' => [ 'code' => 'db_connection_failed', 'message' => $service->get_error_message() ]
			], 500);
		}

		$estudianteId = $request->get_param('estudianteId');
		$contactoId = $request->get_param('contactoId');
		$pendientes = $request->get_param('pendientes') === 'true';

		if (($estudianteId && $contactoId) || (!$estudianteId && !$contactoId)) {
			return new WP_REST_Response([
				'success' => false,
				'error' => [ 'code' => 'invalid_params', 'message' => 'Debe proporcionar estudianteId o contactoId' ]
			], 422);
		}

		$result = $service->listarDiplomas($estudianteId, $contactoId, $pendientes);
		if (is_wp_error($result)) {
			return new WP_REST_Response([
				'success' => false,
				'error' => [ 'code' => $result->get_error_code(), 'message' => $result->get_error_message() ]
			], $result->get_error_data()['status'] ?? 500);
		}

		return new WP_REST_Response([
			'success' => true,
			'data' => $result
		], 200);
	}

	/**
	 * GET /plg-genesis/v1/diplomas/{id}
	 */
	public static function get_diploma(WP_REST_Request $request) {
		$service = self::get_service();
		if (is_wp_error($service)) {
			return new WP_REST_Response([
				'success' => false,
				'error' => [ 'code' => 'db_connection_failed', 'message' => $service->get_error_message() ]
			], 500);
		}

		$diplomaId = intval($request->get_param('id'));
		$result = $service->getDiploma($diplomaId);

		if (is_wp_error($result)) {
			return new WP_REST_Response([
				'success' => false,
				'error' => [ 'code' => $result->get_error_code(), 'message' => $result->get_error_message() ]
			], $result->get_error_data()['status'] ?? 500);
		}

		return new WP_REST_Response([
			'success' => true,
			'data' => $result
		], 200);
	}

	/**
	 * GET /plg-genesis/v1/diplomas/acta-cierre?contactoId=X
	 */
	public static function acta_cierre(WP_REST_Request $request) {
		$service = self::get_service();
		if (is_wp_error($service)) {
			return new WP_REST_Response([
				'success' => false,
				'error' => [ 'code' => 'db_connection_failed', 'message' => $service->get_error_message() ]
			], 500);
		}

		$contactoId = $request->get_param('contactoId');
		if (!$contactoId) {
			return new WP_REST_Response([
				'success' => false,
				'error' => [ 'code' => 'missing_contacto', 'message' => 'contactoId es requerido' ]
			], 422);
		}

		$result = $service->generarActaCierre($contactoId);
		if (is_wp_error($result)) {
			return new WP_REST_Response([
				'success' => false,
				'error' => [ 'code' => $result->get_error_code(), 'message' => $result->get_error_message() ]
			], $result->get_error_data()['status'] ?? 500);
		}

		return new WP_REST_Response([
			'success' => true,
			'data' => $result
		], 200);
	}

	/**
	 * POST /plg-genesis/v1/diplomas/emitir-todos
	 * Body: { estudianteId? o contactoId? }
	 */
	public static function emitir_todos_elegibles(WP_REST_Request $request) {
		$service = self::get_service();
		if (is_wp_error($service)) {
			return new WP_REST_Response([
				'success' => false,
				'error' => [ 'code' => 'db_connection_failed', 'message' => $service->get_error_message() ]
			], 500);
		}

		$body = $request->get_json_params();
		$estudianteId = $body['estudianteId'] ?? null;
		$contactoId = $body['contactoId'] ?? null;

		if (($estudianteId && $contactoId) || (!$estudianteId && !$contactoId)) {
			return new WP_REST_Response([
				'success' => false,
				'error' => [ 'code' => 'invalid_params', 'message' => 'Debe proporcionar estudianteId o contactoId' ]
			], 422);
		}

		$result = $service->emitirTodosElegibles($estudianteId, $contactoId);
		if (is_wp_error($result)) {
			return new WP_REST_Response([
				'success' => false,
				'error' => [ 'code' => $result->get_error_code(), 'message' => $result->get_error_message() ]
			], $result->get_error_data()['status'] ?? 500);
		}

		return new WP_REST_Response([
			'success' => true,
			'data' => $result
		], 201);
	}

	/**
	 * GET /plg-genesis/v1/actas?contactoId=X&estado=activa
	 */
	public static function listar_actas(WP_REST_Request $request) {
		$service = self::get_service();
		if (is_wp_error($service)) {
			return new WP_REST_Response([
				'success' => false,
				'error' => [ 'code' => 'db_connection_failed', 'message' => $service->get_error_message() ]
			], 500);
		}

		$contactoId = $request->get_param('contactoId');
		$estado = $request->get_param('estado') ?? 'activa';
		$limit = intval($request->get_param('limit') ?? 50);
		$offset = intval($request->get_param('offset') ?? 0);

		$result = $service->listarActas($contactoId, $estado, $limit, $offset);
		if (is_wp_error($result)) {
			return new WP_REST_Response([
				'success' => false,
				'error' => [ 'code' => $result->get_error_code(), 'message' => $result->get_error_message() ]
			], $result->get_error_data()['status'] ?? 500);
		}

		return new WP_REST_Response([
			'success' => true,
			'data' => $result
		], 200);
	}

	/**
	 * GET /plg-genesis/v1/actas/{id}
	 */
	public static function get_acta(WP_REST_Request $request) {
		$service = self::get_service();
		if (is_wp_error($service)) {
			return new WP_REST_Response([
				'success' => false,
				'error' => [ 'code' => 'db_connection_failed', 'message' => $service->get_error_message() ]
			], 500);
		}

		$actaId = intval($request->get_param('id'));

		$result = $service->getActa($actaId);
		if (is_wp_error($result)) {
			return new WP_REST_Response([
				'success' => false,
				'error' => [ 'code' => $result->get_error_code(), 'message' => $result->get_error_message() ]
			], $result->get_error_data()['status'] ?? 500);
		}

		return new WP_REST_Response([
			'success' => true,
			'data' => $result
		], 200);
	}

	/**
	 * GET /plg-genesis/v1/diplomas/proximos-completar/programas?contactoId=123&umbral=80
	 * Retorna solo la lista de programas con contador de estudiantes (ligero y rápido)
	 */
	public static function programas_con_proximos(WP_REST_Request $request) {
		$service = self::get_service();
		if (is_wp_error($service)) {
			return new WP_REST_Response([
				'success' => false,
				'error' => [ 'code' => 'db_connection_failed', 'message' => $service->get_error_message() ]
			], 500);
		}

		$contactoId = $request->get_param('contactoId') ? intval($request->get_param('contactoId')) : null;
		$umbral = intval($request->get_param('umbral') ?? 80);

		$result = $service->getProgramasConProximos($contactoId, $umbral);
		if (is_wp_error($result)) {
			return new WP_REST_Response([
				'success' => false,
				'error' => [ 'code' => $result->get_error_code(), 'message' => $result->get_error_message() ]
			], $result->get_error_data()['status'] ?? 500);
		}

		return new WP_REST_Response([
			'success' => true,
			'data' => $result
		], 200);
	}

	/**
	 * GET /plg-genesis/v1/diplomas/proximos-completar?limite=50&umbral=80&contactoId=123&programaId=1
	 */
	public static function proximos_completar(WP_REST_Request $request) {
		$service = self::get_service();
		if (is_wp_error($service)) {
			return new WP_REST_Response([
				'success' => false,
				'error' => [ 'code' => 'db_connection_failed', 'message' => $service->get_error_message() ]
			], 500);
		}

		$limite = intval($request->get_param('limite') ?? 50);
		$umbral = intval($request->get_param('umbral') ?? 80);
		$contactoId = $request->get_param('contactoId') ? intval($request->get_param('contactoId')) : null;
		$programaId = $request->get_param('programaId') ? intval($request->get_param('programaId')) : null;

		$result = $service->getProximosACompletar($limite, $umbral, $contactoId, $programaId);
		if (is_wp_error($result)) {
			return new WP_REST_Response([
				'success' => false,
				'error' => [ 'code' => $result->get_error_code(), 'message' => $result->get_error_message() ]
			], $result->get_error_data()['status'] ?? 500);
		}

		return new WP_REST_Response([
			'success' => true,
			'data' => $result
		], 200);
	}
}



