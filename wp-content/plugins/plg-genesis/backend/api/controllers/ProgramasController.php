<?php
if (!defined('ABSPATH')) { exit; }

require_once dirname(__FILE__, 3) . '/infrastructure/OfficeResolver.php';
require_once dirname(__FILE__, 3) . '/infrastructure/ConnectionProvider.php';
require_once dirname(__FILE__, 3) . '/repositories/ProgramasRepository.php';
require_once dirname(__FILE__, 3) . '/services/ProgramasService.php';

class PlgGenesis_ProgramasController {
    public static function register_routes() {
        register_rest_route('plg-genesis/v1', '/programas', [
            'methods'             => 'GET',
            'callback'            => [ __CLASS__, 'get_programas' ],
            'permission_callback' => function() { return is_user_logged_in(); }
        ]);

        register_rest_route('plg-genesis/v1', '/programas/(?P<id>[0-9]+)', [
            'methods'             => 'GET',
            'callback'            => [ __CLASS__, 'get_programa' ],
            'permission_callback' => function() { return is_user_logged_in(); }
        ]);

        register_rest_route('plg-genesis/v1', '/programas', [
            'methods'             => 'POST',
            'callback'            => [ __CLASS__, 'post_programa' ],
            'permission_callback' => function() { return is_user_logged_in(); }
        ]);

        register_rest_route('plg-genesis/v1', '/programas/(?P<id>[0-9]+)', [
            'methods'             => 'PUT',
            'callback'            => [ __CLASS__, 'put_programa' ],
            'permission_callback' => function() { return is_user_logged_in(); }
        ]);

        register_rest_route('plg-genesis/v1', '/programas/(?P<id>[0-9]+)', [
            'methods'             => 'DELETE',
            'callback'            => [ __CLASS__, 'delete_programa' ],
            'permission_callback' => function() { return is_user_logged_in(); }
        ]);

        register_rest_route('plg-genesis/v1', '/programas/(?P<id>[0-9]+)/asignar', [
            'methods'             => 'POST',
            'callback'            => [ __CLASS__, 'post_asignar' ],
            'permission_callback' => function() { return is_user_logged_in(); }
        ]);

        register_rest_route('plg-genesis/v1', '/programas/(?P<id>[0-9]+)/asignar', [
            'methods'             => 'DELETE',
            'callback'            => [ __CLASS__, 'delete_asignar' ],
            'permission_callback' => function() { return is_user_logged_in(); }
        ]);
    }

    private static function error($wpError){
        if ($wpError instanceof WP_Error){
            $status = $wpError->get_error_data()['status'] ?? 500;
            return new WP_REST_Response([ 'success'=>false, 'error'=>[ 'code'=>$wpError->get_error_code(), 'message'=>$wpError->get_error_message() ] ], $status);
        }
        return new WP_REST_Response([ 'success'=>false, 'error'=>[ 'code'=>'unknown_error', 'message'=>'Error desconocido' ] ], 500);
    }

    public static function get_programas($request){
        $office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
        if (is_wp_error($office)) return self::error($office);
        $conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
        if (is_wp_error($conn)) return self::error($conn);
        $repo = new PlgGenesis_ProgramasRepository($conn); $svc = new PlgGenesis_ProgramasService($repo);
        $q = strval($request->get_param('q') ?? '');
        $include = strval($request->get_param('include') ?? '');
        $items = $svc->listar($q, $include === 'all');
        if (is_wp_error($items)) return self::error($items);
        return new WP_REST_Response([ 'success'=>true, 'data'=>[ 'items'=>$items ] ], 200);
    }

    public static function get_programa($request){
        $office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
        if (is_wp_error($office)) return self::error($office);
        $conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
        if (is_wp_error($conn)) return self::error($conn);
        $repo = new PlgGenesis_ProgramasRepository($conn); $svc = new PlgGenesis_ProgramasService($repo);
        $res = $svc->obtener($request->get_param('id'));
        if (is_wp_error($res)) return self::error($res);
        return new WP_REST_Response([ 'success'=>true, 'data'=>$res ], 200);
    }

    public static function post_programa($request){
        $office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
        if (is_wp_error($office)) return self::error($office);
        $conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
        if (is_wp_error($conn)) return self::error($conn);
        $repo = new PlgGenesis_ProgramasRepository($conn); $svc = new PlgGenesis_ProgramasService($repo);
        $payload = $request->get_json_params() ?: [];
        $res = $svc->crear(is_array($payload)?$payload:[]);
        if (is_wp_error($res)) return self::error($res);
        return new WP_REST_Response([ 'success'=>true, 'data'=>[ 'id'=>$res ] ], 201);
    }

    public static function put_programa($request){
        $office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
        if (is_wp_error($office)) return self::error($office);
        $conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
        if (is_wp_error($conn)) return self::error($conn);
        $repo = new PlgGenesis_ProgramasRepository($conn); $svc = new PlgGenesis_ProgramasService($repo);
        $payload = $request->get_json_params() ?: [];
        $ok = $svc->actualizar($request->get_param('id'), is_array($payload)?$payload:[]);
        if (is_wp_error($ok)) return self::error($ok);
        return new WP_REST_Response([ 'success'=>true, 'data'=>[ 'updated'=>true ] ], 200);
    }

    public static function delete_programa($request){
        $office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
        if (is_wp_error($office)) return self::error($office);
        $conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
        if (is_wp_error($conn)) return self::error($conn);
        $repo = new PlgGenesis_ProgramasRepository($conn); $svc = new PlgGenesis_ProgramasService($repo);
        $hard = filter_var($request->get_param('hard'), FILTER_VALIDATE_BOOLEAN);
        $ok = $svc->eliminar($request->get_param('id'), $hard);
        if (is_wp_error($ok)) return self::error($ok);
        return new WP_REST_Response([ 'success'=>true, 'data'=>[ 'deleted'=>true, 'hard'=>$hard ] ], 200);
    }

    public static function post_asignar($request){
        $office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
        if (is_wp_error($office)) return self::error($office);
        $conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
        if (is_wp_error($conn)) return self::error($conn);
        $repo = new PlgGenesis_ProgramasRepository($conn); $svc = new PlgGenesis_ProgramasService($repo);
        $payload = $request->get_json_params() ?: [];
        $est = isset($payload['estudianteId']) ? intval($payload['estudianteId']) : null;
        $con = isset($payload['contactoId']) ? intval($payload['contactoId']) : null;
        $ok = $svc->asignar($request->get_param('id'), $est, $con, false);
        if (is_wp_error($ok)) return self::error($ok);
        return new WP_REST_Response([ 'success'=>true, 'data'=>[ 'assigned'=>true ] ], 200);
    }

    public static function delete_asignar($request){
        $office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
        if (is_wp_error($office)) return self::error($office);
        $conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
        if (is_wp_error($conn)) return self::error($conn);
        $repo = new PlgGenesis_ProgramasRepository($conn); $svc = new PlgGenesis_ProgramasService($repo);
        $payload = $request->get_json_params() ?: [];
        $est = isset($payload['estudianteId']) ? intval($payload['estudianteId']) : null;
        $con = isset($payload['contactoId']) ? intval($payload['contactoId']) : null;
        $ok = $svc->asignar($request->get_param('id'), $est, $con, true);
        if (is_wp_error($ok)) return self::error($ok);
        return new WP_REST_Response([ 'success'=>true, 'data'=>[ 'unassigned'=>true ] ], 200);
    }
}


