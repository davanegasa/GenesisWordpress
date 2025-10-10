<?php
if (!defined('ABSPATH')) { exit; }

require_once dirname(__FILE__, 3) . '/infrastructure/OfficeResolver.php';
require_once dirname(__FILE__, 3) . '/infrastructure/ConnectionProvider.php';
require_once dirname(__FILE__, 3) . '/repositories/CongresosRepository.php';
require_once dirname(__FILE__, 3) . '/services/CongresosService.php';

class PlgGenesis_CongresosController {
	public static function register_routes() {
        register_rest_route('plg-genesis/v1', '/congresos', [
            'methods'             => 'GET',
            'callback'            => [ __CLASS__, 'get_congresos' ],
            'permission_callback' => function() { plg_genesis_validate_user_from_cookie(); return is_user_logged_in(); }
        ]);

        register_rest_route('plg-genesis/v1', '/congresos/(?P<id>[0-9]+)', [
            'methods'             => 'GET',
            'callback'            => [ __CLASS__, 'get_congreso' ],
            'permission_callback' => function() { plg_genesis_validate_user_from_cookie(); return is_user_logged_in(); }
        ]);

        register_rest_route('plg-genesis/v1', '/congresos/(?P<id>[0-9]+)', [
            'methods'             => 'PUT',
            'callback'            => [ __CLASS__, 'put_congreso' ],
            'permission_callback' => function() { plg_genesis_validate_user_from_cookie(); return is_user_logged_in(); }
        ]);

        register_rest_route('plg-genesis/v1', '/congresos/(?P<id>[0-9]+)/stats', [
            'methods'             => 'GET',
            'callback'            => [ __CLASS__, 'get_stats' ],
            'permission_callback' => function() { plg_genesis_validate_user_from_cookie(); return is_user_logged_in(); }
        ]);

        register_rest_route('plg-genesis/v1', '/congresos/(?P<id>[0-9]+)/checkin', [
            'methods'             => 'POST',
            'callback'            => [ __CLASS__, 'post_checkin' ],
            'permission_callback' => function() { plg_genesis_validate_user_from_cookie(); return is_user_logged_in(); }
        ]);

        register_rest_route('plg-genesis/v1', '/congresos/(?P<id>[0-9]+)/void', [
            'methods'             => 'POST',
            'callback'            => [ __CLASS__, 'post_void' ],
            'permission_callback' => function() { plg_genesis_validate_user_from_cookie(); return is_user_logged_in(); }
        ]);

        register_rest_route('plg-genesis/v1', '/congresos/(?P<id>[0-9]+)/inscritos', [
            'methods'             => 'GET',
            'callback'            => [ __CLASS__, 'get_inscritos' ],
            'permission_callback' => function() { plg_genesis_validate_user_from_cookie(); return is_user_logged_in(); }
        ]);

        register_rest_route('plg-genesis/v1', '/congresos/(?P<id>[0-9]+)/no-asistentes', [
            'methods'             => 'GET',
            'callback'            => [ __CLASS__, 'get_no_asistentes' ],
            'permission_callback' => function() { plg_genesis_validate_user_from_cookie(); return is_user_logged_in(); }
        ]);

        register_rest_route('plg-genesis/v1', '/congresos/(?P<id>[0-9]+)/ultimos', [
            'methods'             => 'GET',
            'callback'            => [ __CLASS__, 'get_ultimos' ],
            'permission_callback' => function() { plg_genesis_validate_user_from_cookie(); return is_user_logged_in(); }
        ]);
	}

	public static function get_congresos($request) {
		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) {
			return self::error($office);
		}
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
		if (is_wp_error($conn)) { return self::error($conn); }

		$repo = new PlgGenesis_CongresosRepository($conn);
		$svc  = new PlgGenesis_CongresosService($repo);
		$result = $svc->listar();
		if (is_wp_error($result)) { return self::error($result); }

		return new WP_REST_Response([
			'success' => true,
			'data'    => $result,
		], 200);
	}

	public static function get_congreso($request){
		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) return self::error($office);
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
		if (is_wp_error($conn)) return self::error($conn);
		$repo = new PlgGenesis_CongresosRepository($conn); $svc = new PlgGenesis_CongresosService($repo);
		$result = $svc->obtener($request->get_param('id'));
		if (is_wp_error($result)) return self::error($result);
		return new WP_REST_Response([ 'success'=>true, 'data'=>$result ], 200);
	}

	public static function get_inscritos($request){
		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) return self::error($office);
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
		if (is_wp_error($conn)) return self::error($conn);
		$q = $request->get_param('query') ?: '';
		$tipo = $request->get_param('tipo') ?: '';
		$limit = intval($request->get_param('limit') ?: 50);
		$offset = intval($request->get_param('offset') ?: 0);
		$repo = new PlgGenesis_CongresosRepository($conn); $svc = new PlgGenesis_CongresosService($repo);
		$result = $svc->inscritos($request->get_param('id'), $q, $tipo, $limit, $offset);
		if (is_wp_error($result)) return self::error($result);
		return new WP_REST_Response([ 'success'=>true, 'data'=>$result ], 200);
	}

	public static function get_no_asistentes($request){
		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) return self::error($office);
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
		if (is_wp_error($conn)) return self::error($conn);
		$tipo = $request->get_param('tipo') ?: 'llegada';
		$limit = intval($request->get_param('limit') ?: 50);
		$offset = intval($request->get_param('offset') ?: 0);
		$repo = new PlgGenesis_CongresosRepository($conn); $svc = new PlgGenesis_CongresosService($repo);
		$result = $svc->noAsistentes($request->get_param('id'), $tipo, $limit, $offset);
		if (is_wp_error($result)) return self::error($result);
		return new WP_REST_Response([ 'success'=>true, 'data'=>$result ], 200);
	}

	public static function get_ultimos($request){
		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) return self::error($office);
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
		if (is_wp_error($conn)) return self::error($conn);
		$limit = intval($request->get_param('limit') ?: 20);
		$repo = new PlgGenesis_CongresosRepository($conn); $svc = new PlgGenesis_CongresosService($repo);
		$result = $svc->ultimos($request->get_param('id'), $limit);
		if (is_wp_error($result)) return self::error($result);
		return new WP_REST_Response([ 'success'=>true, 'data'=>$result ], 200);
	}

	public static function post_checkin($request){
		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) return self::error($office);
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
		if (is_wp_error($conn)) return self::error($conn);
		$params = $request->get_json_params() ?: [];
		$numero = $params['numeroBoleta'] ?? '';
		$codigo = $params['codigoVerificacion'] ?? '';
		$tipo   = ($params['tipo'] ?? 'llegada');
		$repo = new PlgGenesis_CongresosRepository($conn); $svc = new PlgGenesis_CongresosService($repo);
		$result = $svc->checkin($request->get_param('id'), $numero, $codigo, $tipo);
		if (is_wp_error($result)) return self::error($result);
		return new WP_REST_Response([ 'success'=>true, 'data'=>$result ], 200);
	}

	public static function post_void($request){
		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) return self::error($office);
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
		if (is_wp_error($conn)) return self::error($conn);
		$params = $request->get_json_params() ?: [];
		$numero = $params['numeroBoleta'] ?? '';
		$codigo = $params['codigoVerificacion'] ?? '';
		$repo = new PlgGenesis_CongresosRepository($conn); $svc = new PlgGenesis_CongresosService($repo);
		$result = $svc->void($request->get_param('id'), $numero, $codigo);
		if (is_wp_error($result)) return self::error($result);
		return new WP_REST_Response([ 'success'=>true, 'data'=>$result ], 200);
	}

	public static function put_congreso($request){
		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) return self::error($office);
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
		if (is_wp_error($conn)) return self::error($conn);
		$repo = new PlgGenesis_CongresosRepository($conn); $svc = new PlgGenesis_CongresosService($repo);
		$payload = $request->get_json_params();
		if (!is_array($payload)) $payload = [];
		// Normalizar y mapear campos/valores provenientes del frontend v2
		// Mapear 'lugar' -> 'ubicacion'
		if (isset($payload['lugar']) && !isset($payload['ubicacion'])) {
			$payload['ubicacion'] = $payload['lugar'];
		}
		// Mapear estados cortos a valores válidos de la BD
		if (isset($payload['estado'])) {
			$raw = strtoupper(trim((string) $payload['estado']));
			$map = [
				'PLAN'   => 'PLANEACION',
				'PLANEACION' => 'PLANEACION',
				'ABIERTO'=> 'REGISTRO',
				'REGISTRO'=> 'REGISTRO',
				'CURSO'  => 'EN_CURSO',
				'EN_CURSO'=> 'EN_CURSO',
				'FINAL'  => 'FINALIZADO',
				'FINALIZADO'=> 'FINALIZADO',
				'CANCEL' => 'CANCELADO',
				'CANCELADO'=> 'CANCELADO',
			];
			$payload['estado'] = isset($map[$raw]) ? $map[$raw] : $raw;
		}
		// Permitir actualizar solo los campos soportados
		$payload = array_intersect_key($payload, array_flip(['nombre','fecha','ubicacion','estado']));
		$result = $svc->actualizar($request->get_param('id'), is_array($payload)?$payload:[]);
		if (is_wp_error($result)) return self::error($result);
		return new WP_REST_Response([ 'success'=>true, 'data'=>[ 'updated'=>true ] ], 200);
	}

	public static function get_stats($request){
		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) return self::error($office);
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
		if (is_wp_error($conn)) return self::error($conn);
		$repo = new PlgGenesis_CongresosRepository($conn); $svc = new PlgGenesis_CongresosService($repo);
		$result = $svc->stats($request->get_param('id'));
		if (is_wp_error($result)) return self::error($result);
		return new WP_REST_Response([ 'success'=>true, 'data'=>$result ], 200);
	}

	private static function error($wp_error) {
		$status = $wp_error->get_error_data()['status'] ?? 500;
		return new WP_REST_Response([
			'success' => false,
			'error'   => [
				'code'    => $wp_error->get_error_code(),
				'message' => $wp_error->get_error_message(),
				'details' => $wp_error->get_error_data(),
			]
		], $status);
	}
}