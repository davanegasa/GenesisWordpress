<?php
if (!defined('ABSPATH')) { exit; }

require_once dirname(__FILE__, 3) . '/infrastructure/OfficeResolver.php';
require_once dirname(__FILE__, 3) . '/infrastructure/ConnectionProvider.php';

class PlgGenesis_CoursesController {
    public static function register_routes() {
        register_rest_route('plg-genesis/v1', '/cursos', [
            'methods'             => 'GET',
            'callback'            => [ __CLASS__, 'get_cursos' ],
            'permission_callback' => plg_genesis_can('plg_view_courses')
        ]);

        register_rest_route('plg-genesis/v1', '/cursos/(?P<id>[0-9]+)', [
            'methods'             => 'GET',
            'callback'            => [ __CLASS__, 'get_curso' ],
            'permission_callback' => plg_genesis_can('plg_view_courses')
        ]);

        register_rest_route('plg-genesis/v1', '/cursos', [
            'methods'             => 'POST',
            'callback'            => [ __CLASS__, 'post_curso' ],
            'permission_callback' => plg_genesis_can('plg_create_courses')
        ]);

        register_rest_route('plg-genesis/v1', '/cursos/(?P<id>[0-9]+)', [
            'methods'             => 'PUT',
            'callback'            => [ __CLASS__, 'put_curso' ],
            'permission_callback' => plg_genesis_can('plg_edit_courses')
        ]);

        register_rest_route('plg-genesis/v1', '/cursos/(?P<id>[0-9]+)', [
            'methods'             => 'DELETE',
            'callback'            => [ __CLASS__, 'delete_curso' ],
            'permission_callback' => plg_genesis_can('plg_delete_courses')
        ]);

        register_rest_route('plg-genesis/v1', '/cursos/(?P<id>[0-9]+)/stats', [
            'methods'             => 'GET',
            'callback'            => [ __CLASS__, 'get_curso_stats' ],
            'permission_callback' => plg_genesis_can('plg_view_stats')
        ]);

        // Endpoints para calendario de cursos
        register_rest_route('plg-genesis/v1', '/cursos-calendario/mes', [
            'methods'             => 'GET',
            'callback'            => [ __CLASS__, 'get_calendario_mes' ],
            'permission_callback' => plg_genesis_can('plg_view_courses')
        ]);

        register_rest_route('plg-genesis/v1', '/cursos-calendario/dia', [
            'methods'             => 'GET',
            'callback'            => [ __CLASS__, 'get_calendario_dia' ],
            'permission_callback' => plg_genesis_can('plg_view_courses')
        ]);

        register_rest_route('plg-genesis/v1', '/estudiantes-cursos/(?P<id>[0-9]+)', [
            'methods'             => 'DELETE',
            'callback'            => [ __CLASS__, 'delete_estudiante_curso' ],
            'permission_callback' => plg_genesis_can('plg_delete_courses')
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
        $include = strval($request->get_param('include') ?? '');
        $params = []; $idx=1; $where=[];
        if ($include !== 'all'){ $where[] = 'c.deleted_at IS NULL'; }
        if ($q !== ''){ $where[] = "(c.nombre ILIKE $".$idx." OR COALESCE(c.descripcion,'') ILIKE $".$idx.")"; $params[] = '%'.$q.'%'; $idx++; }
        $sql = 'SELECT c.id, c.nombre, c.descripcion FROM cursos c ' . (count($where)?('WHERE '.implode(' AND ',$where)):'') . ' ORDER BY c.nombre ASC LIMIT 500';
        $res = pg_query_params($conn, $sql, $params);
        if (!$res) {
            error_log('Error en get_cursos: ' . pg_last_error($conn));
            return self::error('Error listando cursos: ' . pg_last_error($conn));
        }
        $items = []; while($row = pg_fetch_assoc($res)){ $items[] = [ 'id'=>intval($row['id']), 'nombre'=>$row['nombre'], 'descripcion'=>$row['descripcion'] ]; }
        pg_free_result($res);
        return new WP_REST_Response([ 'success'=>true, 'data'=>[ 'items'=>$items ] ], 200);
    }

    public static function get_curso($request){
        $office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
        if (is_wp_error($office)) return new WP_REST_Response([ 'success'=>false, 'error'=>[ 'code'=>$office->get_error_code(), 'message'=>$office->get_error_message() ] ], $office->get_error_data()['status'] ?? 500);
        $conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
        if (is_wp_error($conn)) return new WP_REST_Response([ 'success'=>false, 'error'=>[ 'code'=>$conn->get_error_code(), 'message'=>$conn->get_error_message() ] ], $conn->get_error_data()['status'] ?? 500);
        $sql = 'SELECT id, nombre, descripcion FROM cursos WHERE id=$1';
        $res = pg_query_params($conn, $sql, [ intval($request->get_param('id')) ]);
        if (!$res) return self::error('Error obteniendo curso');
        $row = pg_fetch_assoc($res); pg_free_result($res);
        if (!$row) return self::error('Curso no encontrado','not_found',404);
        return new WP_REST_Response([ 'success'=>true, 'data'=>[ 'id'=>intval($row['id']), 'nombre'=>$row['nombre'], 'descripcion'=>$row['descripcion'] ] ], 200);
    }

    public static function post_curso($request){
        $office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
        if (is_wp_error($office)) return new WP_REST_Response([ 'success'=>false, 'error'=>[ 'code'=>$office->get_error_code(), 'message'=>$office->get_error_message() ] ], $office->get_error_data()['status'] ?? 500);
        $conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
        if (is_wp_error($conn)) return new WP_REST_Response([ 'success'=>false, 'error'=>[ 'code'=>$conn->get_error_code(), 'message'=>$conn->get_error_message() ] ], $conn->get_error_data()['status'] ?? 500);
        $body = $request->get_json_params() ?: [];
        $nombre = trim(strval($body['nombre'] ?? ''));
        $descripcion = isset($body['descripcion']) ? trim(strval($body['descripcion'])) : null;
        if ($nombre === '') return self::error('Nombre requerido','invalid_payload',422);
        $res = pg_query_params($conn, 'INSERT INTO cursos (nombre, descripcion) VALUES ($1,$2) RETURNING id', [ $nombre, $descripcion ]);
        if (!$res) return self::error('Error creando curso');
        $id = intval(pg_fetch_result($res,0,0)); pg_free_result($res);
        return new WP_REST_Response([ 'success'=>true, 'data'=>[ 'id'=>$id ] ], 201);
    }

    public static function put_curso($request){
        $office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
        if (is_wp_error($office)) return new WP_REST_Response([ 'success'=>false, 'error'=>[ 'code'=>$office->get_error_code(), 'message'=>$office->get_error_message() ] ], $office->get_error_data()['status'] ?? 500);
        $conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
        if (is_wp_error($conn)) return new WP_REST_Response([ 'success'=>false, 'error'=>[ 'code'=>$conn->get_error_code(), 'message'=>$conn->get_error_message() ] ], $conn->get_error_data()['status'] ?? 500);
        $id = intval($request->get_param('id'));
        $body = $request->get_json_params() ?: [];
        $nombre = array_key_exists('nombre',$body) ? trim(strval($body['nombre'])) : null;
        $descripcion = array_key_exists('descripcion',$body) ? trim(strval($body['descripcion'])) : null;
        $res = pg_query_params($conn, 'UPDATE cursos SET nombre=COALESCE($1,nombre), descripcion=COALESCE($2,descripcion), updated_at=NOW() WHERE id=$3', [ $nombre, $descripcion, $id ]);
        if (!$res) return self::error('Error actualizando curso'); pg_free_result($res);
        return new WP_REST_Response([ 'success'=>true, 'data'=>[ 'updated'=>true ] ], 200);
    }

    public static function delete_curso($request){
        $office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
        if (is_wp_error($office)) return new WP_REST_Response([ 'success'=>false, 'error'=>[ 'code'=>$office->get_error_code(), 'message'=>$office->get_error_message() ] ], $office->get_error_data()['status'] ?? 500);
        $conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
        if (is_wp_error($conn)) return new WP_REST_Response([ 'success'=>false, 'error'=>[ 'code'=>$conn->get_error_code(), 'message'=>$conn->get_error_message() ] ], $conn->get_error_data()['status'] ?? 500);
        $id = intval($request->get_param('id'));
        // Soft delete si la columna existe; fallback a hard delete
        $hasDeleted = pg_query_params($conn, "SELECT 1 FROM information_schema.columns WHERE table_schema='public' AND table_name='cursos' AND column_name='deleted_at' LIMIT 1", []);
        $has = $hasDeleted && pg_num_rows($hasDeleted) > 0; if ($hasDeleted) pg_free_result($hasDeleted);
        if ($has){
            $res = pg_query_params($conn, 'UPDATE cursos SET deleted_at=NOW(), updated_at=NOW() WHERE id=$1', [ $id ]);
            if (!$res) return self::error('Error aplicando soft delete'); pg_free_result($res);
        } else {
            $res = pg_query_params($conn, 'DELETE FROM cursos WHERE id=$1', [ $id ]);
            if (!$res) return self::error('Error eliminando curso'); pg_free_result($res);
        }
        return new WP_REST_Response([ 'success'=>true, 'data'=>[ 'deleted'=>true, 'soft'=>$has ] ], 200);
    }

    public static function get_curso_stats($request){
        $office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
        if (is_wp_error($office)) return new WP_REST_Response([ 'success'=>false, 'error'=>[ 'code'=>$office->get_error_code(), 'message'=>$office->get_error_message() ] ], $office->get_error_data()['status'] ?? 500);
        $conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
        if (is_wp_error($conn)) return new WP_REST_Response([ 'success'=>false, 'error'=>[ 'code'=>$conn->get_error_code(), 'message'=>$conn->get_error_message() ] ], $conn->get_error_data()['status'] ?? 500);
        $id = intval($request->get_param('id'));
        $thresh = floatval($request->get_param('thresh') ?? 70);
        $sql = 'SELECT COUNT(*) AS total, COALESCE(AVG(porcentaje),0) AS avg_nota, MAX(fecha) AS last_fecha, SUM(CASE WHEN porcentaje >= $2 THEN 1 ELSE 0 END) AS aprobados FROM estudiantes_cursos WHERE curso_id=$1';
        $res = pg_query_params($conn, $sql, [ $id, $thresh ]);
        if (!$res) return self::error('Error calculando estadísticas');
        $row = pg_fetch_assoc($res); pg_free_result($res);
        $total = intval($row['total'] ?? 0);
        $avgNota = floatval($row['avg_nota'] ?? 0);
        $aprobados = intval($row['aprobados'] ?? 0);
        $aprobacionPct = $total > 0 ? (100.0 * $aprobados / $total) : 0.0;
        $last = $row['last_fecha'] ?? null;
        return new WP_REST_Response([ 'success'=>true, 'data'=>[
            'avgNota'=> round($avgNota, 2),
            'aprobacionPct'=> round($aprobacionPct, 3),
            'ultimoRegistro'=> $last,
            'totalRealizados'=> $total,
            'umbralAprobacion'=> $thresh
        ] ], 200);
    }

    /**
     * Obtiene la cantidad de cursos por día en un mes
     */
    public static function get_calendario_mes($request) {
        $office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
        if (is_wp_error($office)) return new WP_REST_Response([ 'success'=>false, 'error'=>[ 'code'=>$office->get_error_code(), 'message'=>$office->get_error_message() ] ], $office->get_error_data()['status'] ?? 500);
        $conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
        if (is_wp_error($conn)) return new WP_REST_Response([ 'success'=>false, 'error'=>[ 'code'=>$conn->get_error_code(), 'message'=>$conn->get_error_message() ] ], $conn->get_error_data()['status'] ?? 500);

        $mes = intval($request->get_param('mes') ?: date('m'));
        $anio = intval($request->get_param('anio') ?: date('Y'));

        // Validar mes y año
        if ($mes < 1 || $mes > 12) {
            return self::error('Mes inválido', 'invalid_month', 400);
        }
        if ($anio < 2000 || $anio > 2100) {
            return self::error('Año inválido', 'invalid_year', 400);
        }

        // Obtener cantidad de cursos por día
        $sql = "SELECT DATE(fecha) as dia, COUNT(*) as cantidad
                FROM estudiantes_cursos
                WHERE EXTRACT(MONTH FROM fecha) = $1
                AND EXTRACT(YEAR FROM fecha) = $2
                GROUP BY DATE(fecha)
                ORDER BY dia";
        
        $res = pg_query_params($conn, $sql, [$mes, $anio]);
        if (!$res) return self::error('Error obteniendo cursos del mes');

        $cursos_por_dia = [];
        while ($row = pg_fetch_assoc($res)) {
            $dia = date('j', strtotime($row['dia'])); // Día del mes sin ceros a la izquierda
            $cursos_por_dia[$dia] = intval($row['cantidad']);
        }
        pg_free_result($res);

        // Obtener total del mes
        $sql_total = "SELECT COUNT(*) as total
                      FROM estudiantes_cursos
                      WHERE EXTRACT(MONTH FROM fecha) = $1
                      AND EXTRACT(YEAR FROM fecha) = $2";
        
        $res_total = pg_query_params($conn, $sql_total, [$mes, $anio]);
        $total_mes = 0;
        if ($res_total) {
            $row_total = pg_fetch_assoc($res_total);
            $total_mes = intval($row_total['total']);
            pg_free_result($res_total);
        }

        return new WP_REST_Response([
            'success' => true,
            'data' => [
                'cursosPorDia' => $cursos_por_dia,
                'totalMes' => $total_mes,
                'mes' => $mes,
                'anio' => $anio
            ]
        ], 200);
    }

    /**
     * Obtiene los detalles de cursos de un día específico
     */
    public static function get_calendario_dia($request) {
        $office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
        if (is_wp_error($office)) return new WP_REST_Response([ 'success'=>false, 'error'=>[ 'code'=>$office->get_error_code(), 'message'=>$office->get_error_message() ] ], $office->get_error_data()['status'] ?? 500);
        $conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
        if (is_wp_error($conn)) return new WP_REST_Response([ 'success'=>false, 'error'=>[ 'code'=>$conn->get_error_code(), 'message'=>$conn->get_error_message() ] ], $conn->get_error_data()['status'] ?? 500);

        $dia = intval($request->get_param('dia') ?: date('d'));
        $mes = intval($request->get_param('mes') ?: date('m'));
        $anio = intval($request->get_param('anio') ?: date('Y'));

        // Validar parámetros
        if ($dia < 1 || $dia > 31) {
            return self::error('Día inválido', 'invalid_day', 400);
        }
        if ($mes < 1 || $mes > 12) {
            return self::error('Mes inválido', 'invalid_month', 400);
        }
        if ($anio < 2000 || $anio > 2100) {
            return self::error('Año inválido', 'invalid_year', 400);
        }

        $fecha = sprintf('%04d-%02d-%02d', $anio, $mes, $dia);

        // Obtener cursos del día
        $sql = "SELECT 
                    ec.id as estudiante_curso_id,
                    COALESCE(e.id_estudiante, e.id::text) as estudiante_id,
                    c.nombre as nombre_curso,
                    c.descripcion as descripcion_curso,
                    CONCAT(e.nombre1, ' ', COALESCE(e.nombre2, ''), ' ', e.apellido1, ' ', COALESCE(e.apellido2, '')) as nombre_estudiante,
                    e.celular,
                    cont.nombre as nombre_contacto,
                    ec.porcentaje as nota,
                    ec.fecha
                FROM estudiantes_cursos ec
                JOIN cursos c ON ec.curso_id = c.id
                JOIN estudiantes e ON ec.estudiante_id = e.id
                LEFT JOIN contactos cont ON e.id_contacto = cont.id
                WHERE DATE(ec.fecha) = $1
                ORDER BY ec.fecha ASC";
        
        $res = pg_query_params($conn, $sql, [$fecha]);
        if (!$res) return self::error('Error obteniendo cursos del día');

        $cursos = [];
        while ($row = pg_fetch_assoc($res)) {
            $cursos[] = [
                'estudianteCursoId' => intval($row['estudiante_curso_id']),
                'estudianteId' => $row['estudiante_id'],
                'nombreCurso' => $row['nombre_curso'] ?: $row['descripcion_curso'],
                'descripcionCurso' => $row['descripcion_curso'],
                'nombreEstudiante' => $row['nombre_estudiante'],
                'celular' => $row['celular'],
                'nombreContacto' => $row['nombre_contacto'],
                'nota' => $row['nota'],
                'fecha' => $row['fecha']
            ];
        }
        pg_free_result($res);

        return new WP_REST_Response([
            'success' => true,
            'data' => [
                'fecha' => $fecha,
                'cursos' => $cursos
            ]
        ], 200);
    }

    /**
     * Elimina un registro de estudiante-curso
     */
    public static function delete_estudiante_curso($request) {
        $office = PlgGenesis_OfficeResolver::resolve_user_office(get_current_user_id());
        if (is_wp_error($office)) return new WP_REST_Response([ 'success'=>false, 'error'=>[ 'code'=>$office->get_error_code(), 'message'=>$office->get_error_message() ] ], $office->get_error_data()['status'] ?? 500);
        $conn = PlgGenesis_ConnectionProvider::get_connection_for_office($office);
        if (is_wp_error($conn)) return new WP_REST_Response([ 'success'=>false, 'error'=>[ 'code'=>$conn->get_error_code(), 'message'=>$conn->get_error_message() ] ], $conn->get_error_data()['status'] ?? 500);

        $id = intval($request->get_param('id'));

        if ($id <= 0) {
            return self::error('ID inválido', 'invalid_id', 400);
        }

        // Verificar que el registro existe
        $check_sql = "SELECT id FROM estudiantes_cursos WHERE id = $1";
        $check_res = pg_query_params($conn, $check_sql, [$id]);
        
        if (!$check_res) {
            return self::error('Error verificando el registro');
        }

        if (pg_num_rows($check_res) === 0) {
            pg_free_result($check_res);
            return self::error('Registro no encontrado', 'not_found', 404);
        }
        pg_free_result($check_res);

        // Eliminar el registro
        $sql = "DELETE FROM estudiantes_cursos WHERE id = $1";
        $res = pg_query_params($conn, $sql, [$id]);

        if (!$res) {
            return self::error('Error eliminando el registro');
        }

        pg_free_result($res);

        return new WP_REST_Response([
            'success' => true,
            'data' => [
                'deleted' => true,
                'id' => $id
            ]
        ], 200);
    }
}


