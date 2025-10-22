<?php
if (!defined('ABSPATH')) { exit; }

require_once dirname(__FILE__, 3) . '/infrastructure/OfficeResolver.php';
require_once dirname(__FILE__, 3) . '/infrastructure/ConnectionProvider.php';
require_once dirname(__FILE__, 3) . '/repositories/EstudiantesRepository.php';
require_once dirname(__FILE__, 3) . '/services/EstudiantesService.php';

class PlgGenesis_EstudiantesController {
	public static function register_routes() {
		// Registrar rutas específicas ANTES de la ruta genérica con {id}
		register_rest_route('plg-genesis/v1', '/estudiantes/exists', [
			'methods'             => 'GET',
			'callback'            => [ __CLASS__, 'exists_estudiante' ],
			'permission_callback' => plg_genesis_can('plg_view_students')
		]);

		register_rest_route('plg-genesis/v1', '/estudiantes/next-code', [
			'methods'             => 'GET',
			'callback'            => [ __CLASS__, 'next_code' ],
			'permission_callback' => plg_genesis_can('plg_create_students')
		]);

		register_rest_route('plg-genesis/v1', '/estudiantes', [
			'methods'             => 'GET',
			'callback'            => [ __CLASS__, 'get_estudiantes' ],
			'permission_callback' => plg_genesis_can('plg_view_students')
		]);

		register_rest_route('plg-genesis/v1', '/estudiantes', [
			'methods'             => 'POST',
			'callback'            => [ __CLASS__, 'post_estudiante' ],
			'permission_callback' => plg_genesis_can('plg_create_students')
		]);

		register_rest_route('plg-genesis/v1', '/estudiantes/(?P<id>[A-Za-z0-9\-_%]+)', [
			'methods'             => 'GET',
			'callback'            => [ __CLASS__, 'get_estudiante' ],
			'permission_callback' => plg_genesis_can('plg_view_students')
		]);

		register_rest_route('plg-genesis/v1', '/estudiantes/(?P<id>[A-Za-z0-9\-_%]+)/observaciones', [
			'methods'             => 'GET',
			'callback'            => [ __CLASS__, 'get_observaciones' ],
			'permission_callback' => plg_genesis_can('plg_view_students')
		]);

		register_rest_route('plg-genesis/v1', '/estudiantes/(?P<id>[A-Za-z0-9\-_%]+)/observaciones', [
			'methods'             => 'POST',
			'callback'            => [ __CLASS__, 'post_observacion' ],
			'permission_callback' => plg_genesis_can('plg_edit_students')
		]);

		register_rest_route('plg-genesis/v1', '/estudiantes/(?P<id>[A-Za-z0-9\-_%]+)/quickview', [
			'methods'             => 'GET',
			'callback'            => [ __CLASS__, 'get_quickview' ],
			'permission_callback' => plg_genesis_can('plg_view_students')
		]);

		register_rest_route('plg-genesis/v1', '/estudiantes/(?P<id>[A-Za-z0-9\-_%]+)', [
			'methods'             => 'PUT',
			'callback'            => [ __CLASS__, 'put_estudiante' ],
			'permission_callback' => plg_genesis_can('plg_edit_students')
		]);

		register_rest_route('plg-genesis/v1', '/estudiantes/(?P<id>[A-Za-z0-9\-_%]+)/cursos', [
			'methods'             => 'POST',
			'callback'            => [ __CLASS__, 'post_asignar_curso' ],
			'permission_callback' => plg_genesis_can('plg_assign_courses')
		]);
	}

	public static function get_estudiantes($request) {
		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) return self::error($office);
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
		if (is_wp_error($conn)) return self::error($conn);
		$repo = new PlgGenesis_EstudiantesRepository($conn);
		$svc  = new PlgGenesis_EstudiantesService($repo);
		$q = strval($request->get_param('q') ?? '');
		$page = intval($request->get_param('page') ?? 1);
		$limit = intval($request->get_param('limit') ?? 20);
		$result = $svc->listar($q, $page, $limit);
		if (is_wp_error($result)) return self::error($result);
		return new WP_REST_Response([ 'success'=>true, 'data'=>$result ], 200);
	}

	public static function get_estudiante($request) {
		$id = $request->get_param('id');
		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) return self::error($office);
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
		if (is_wp_error($conn)) return self::error($conn);
		$repo = new PlgGenesis_EstudiantesRepository($conn);
		$svc  = new PlgGenesis_EstudiantesService($repo);
		$result = $svc->getById($id);
		if (is_wp_error($result)) return self::error($result);
		return new WP_REST_Response([ 'success' => true, 'data' => $result ], 200);
	}

	public static function post_estudiante($request) {
		$payload = $request->get_json_params();
		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) return self::error($office);
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
		if (is_wp_error($conn)) return self::error($conn);
		$repo = new PlgGenesis_EstudiantesRepository($conn);
		$svc  = new PlgGenesis_EstudiantesService($repo);
        $result = $svc->create(is_array($payload) ? $payload : []);
        if (is_wp_error($result)) return self::error($result);
        return new WP_REST_Response([ 'success' => true, 'data' => [ 'created' => true, 'idEstudiante' => $result ] ], 201);
	}

	public static function put_estudiante($request) {
		$id = $request->get_param('id');
		$payload = $request->get_json_params();
		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) return self::error($office);
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
		if (is_wp_error($conn)) return self::error($conn);
		$repo = new PlgGenesis_EstudiantesRepository($conn);
		$svc  = new PlgGenesis_EstudiantesService($repo);
		$result = $svc->update($id, is_array($payload) ? $payload : []);
		if (is_wp_error($result)) return self::error($result);
		return new WP_REST_Response([ 'success' => true, 'data' => [ 'updated' => true ] ], 200);
	}

	public static function post_asignar_curso($request) {
		$id = $request->get_param('id');
		$body = $request->get_json_params() ?: [];
		$cursoId = isset($body['cursoId']) ? intval($body['cursoId']) : null;
		$porcentaje = isset($body['porcentaje']) ? floatval($body['porcentaje']) : null;
		$forzar = isset($body['forzar']) ? (bool)$body['forzar'] : false;
		if (!$cursoId || !$porcentaje || $porcentaje < 1 || $porcentaje > 100){
			return new WP_REST_Response([ 'success'=>false, 'error'=>[ 'code'=>'validation_error', 'message'=>'Datos inválidos' ] ], 422);
		}
		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) return self::error($office);
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
		if (is_wp_error($conn)) return self::error($conn);
		$repo = new PlgGenesis_EstudiantesRepository($conn);
		$prev = $repo->assignCourseByStudentCode($id, $cursoId, $porcentaje, $forzar);
		if ($prev instanceof WP_Error){
			$code = $prev->get_error_code();
			$status = $prev->get_error_data()['status'] ?? 500;
			$resp = [ 'success'=>false, 'error'=>[ 'code'=>$code, 'message'=>$prev->get_error_message() ] ];
			$extra = $prev->get_error_data(); if ($extra && isset($extra['curso_anterior'])){ $resp['curso_anterior'] = $extra['curso_anterior']; }
			return new WP_REST_Response($resp, $status);
		}
		return new WP_REST_Response([ 'success'=>true, 'data'=>[ 'assigned'=>true ] ], 201);
	}

	public static function get_observaciones($request){
		$id = $request->get_param('id');
		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id()); if (is_wp_error($office)) return self::error($office);
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office); if (is_wp_error($conn)) return self::error($conn);
		$repo = new PlgGenesis_EstudiantesRepository($conn);
		$res = $repo->getObservacionesByStudentCode($id);
		if ($res instanceof WP_Error) return self::error($res);
		return new WP_REST_Response([ 'success'=>true, 'data'=>[ 'observaciones'=>$res ] ], 200);
	}

	public static function post_observacion($request){
		$id = $request->get_param('id');
		$body = $request->get_json_params() ?: [];
		$obs = trim(strval($body['observacion'] ?? ''));
		$tipo = trim(strval($body['tipo'] ?? 'General'));
		if ($obs === '') return new WP_REST_Response([ 'success'=>false, 'error'=>[ 'code'=>'validation_error', 'message'=>'Observación requerida' ] ], 422);
		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id()); if (is_wp_error($office)) return self::error($office);
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office); if (is_wp_error($conn)) return self::error($conn);
		$repo = new PlgGenesis_EstudiantesRepository($conn);
		$res = $repo->addObservacionByStudentCode($id, $obs, $tipo, get_current_user_id());
		if ($res instanceof WP_Error) return self::error($res);
		return new WP_REST_Response([ 'success'=>true, 'data'=>$res ], 201);
	}

	public static function get_quickview($request){
		$id = $request->get_param('id');
		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id()); if (is_wp_error($office)) return self::error($office);
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office); if (is_wp_error($conn)) return self::error($conn);
		$repo = new PlgGenesis_EstudiantesRepository($conn);
		$res = $repo->quickView($id);
		if ($res instanceof WP_Error) return self::error($res);
		return new WP_REST_Response([ 'success'=>true, 'data'=>$res ], 200);
	}

	public static function exists_estudiante($request) {
		$doc = $request->get_param('doc');
		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) return self::error($office);
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
		if (is_wp_error($conn)) return self::error($conn);
		$svc  = new PlgGenesis_EstudiantesService(new PlgGenesis_EstudiantesRepository($conn));
		$result = $svc->existsByDocumento($doc);
		if (is_wp_error($result)) return self::error($result);
		return new WP_REST_Response([ 'success' => true, 'data' => [ 'exists' => (bool)$result ] ], 200);
	}

	public static function next_code($request) {
		// Intentar obtener primero por code (nuevo método)
		$contactCode = $request->get_param('contactoCode') ?: $request->get_param('contactCode');
		// Fallback a contactoId para compatibilidad (deprecated)
		$contactId = $request->get_param('contactoId');
		
		$office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
		if (is_wp_error($office)) return self::error($office);
		$conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
		if (is_wp_error($conn)) return self::error($conn);
		$svc  = new PlgGenesis_EstudiantesService(new PlgGenesis_EstudiantesRepository($conn));
		
		// Usar code si está disponible, sino usar id (deprecated)
		if ($contactCode) {
			$result = $svc->nextCodeForContactByCode($contactCode);
		} else {
			$result = $svc->nextCodeForContact($contactId);
		}
		
		if (is_wp_error($result)) return self::error($result);
		return new WP_REST_Response([ 'success' => true, 'data' => [ 'code' => $result ] ], 200);
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