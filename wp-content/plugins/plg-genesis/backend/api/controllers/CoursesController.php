<?php
if (!defined('ABSPATH')) { exit; }

require_once dirname(__FILE__, 3) . '/infrastructure/OfficeResolver.php';
require_once dirname(__FILE__, 3) . '/infrastructure/ConnectionProvider.php';

class PlgGenesis_CoursesController {
    public static function register_routes() {
        register_rest_route('plg-genesis/v1', '/cursos', [
            'methods'             => 'GET',
            'callback'            => [ __CLASS__, 'get_cursos' ],
            'permission_callback' => function() { return is_user_logged_in(); }
        ]);
    }

    private static function error($msg, $code='db_query_failed', $status=500){
        return new WP_REST_Response([ 'success'=>false, 'error'=>[ 'code'=>$code, 'message'=>$msg ] ], $status);
    }

    public static function get_cursos($request){
        $office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
        if (is_wp_error($office)) return new WP_REST_Response([ 'success'=>false, 'error'=>[ 'code'=>$office->get_error_code(), 'message'=>$office->get_error_message() ] ], $office->get_error_data()['status'] ?? 500);
        $conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
        if (is_wp_error($conn)) return new WP_REST_Response([ 'success'=>false, 'error'=>[ 'code'=>$conn->get_error_code(), 'message'=>$conn->get_error_message() ] ], $conn->get_error_data()['status'] ?? 500);
        $q = strval($request->get_param('q') ?? '');
        $params = []; $idx=1; $where=[];
        if ($q !== ''){ $where[] = "(c.nombre ILIKE $${idx} OR COALESCE(c.descripcion,'') ILIKE $${idx})"; $params[] = '%'.$q.'%'; $idx++; }
        $sql = 'SELECT c.id, c.nombre, c.descripcion FROM cursos c ' . (count($where)?('WHERE '.implode(' AND ',$where)):'') . ' ORDER BY c.nombre ASC LIMIT 500';
        $res = pg_query_params($conn, $sql, $params);
        if (!$res) return self::error('Error listando cursos');
        $items = []; while($row = pg_fetch_assoc($res)){ $items[] = [ 'id'=>intval($row['id']), 'nombre'=>$row['nombre'], 'descripcion'=>$row['descripcion'] ]; }
        pg_free_result($res);
        return new WP_REST_Response([ 'success'=>true, 'data'=>[ 'items'=>$items ] ], 200);
    }
}


