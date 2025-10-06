<?php
if (!defined('ABSPATH')) { exit; }

class PlgGenesis_CongresosRepository {
	private $conn;

	public function __construct($connection) {
		$this->conn = $connection;
	}

	private function logPg($context, $sql = null) {
		$pgErr = function_exists('pg_last_error') ? pg_last_error($this->conn) : 'unknown';
		$prefix = '[PlgGenesis][CongresosRepository] ' . $context . ' -> ';
		if ($sql) {
			error_log($prefix . $sql . ' | err=' . $pgErr);
		} else {
			error_log($prefix . $pgErr);
		}
	}

    private function hasColumn($table, $column) {
        $sql = "SELECT 1 FROM information_schema.columns WHERE table_schema='public' AND table_name=$1 AND column_name=$2 LIMIT 1";
        $res = pg_query_params($this->conn, $sql, [ strval($table), strval($column) ]);
        if (!$res) { $this->logPg('hasColumn', $sql); return false; }
        $ok = pg_num_rows($res) > 0; pg_free_result($res); return $ok;
    }

	public function findAllWithStats() {
		$sqlCongresos = "
			SELECT 
				c.id AS id_congreso,
				c.nombre AS nombre_congreso,
				c.fecha AS fecha_congreso,
				c.estado,
				COALESCE(COUNT(a.id), 0) AS total_asistentes,
				COALESCE(SUM(CASE WHEN a.id_estudiante IS NOT NULL THEN 1 ELSE 0 END), 0) AS total_estudiantes,
				COALESCE(SUM(CASE WHEN a.id_asistente IS NOT NULL THEN 1 ELSE 0 END), 0) AS total_externos
			FROM congresos c
			LEFT JOIN asistencias_congresos a ON c.id = a.id_congreso
			GROUP BY c.id, c.nombre, c.fecha, c.estado
			ORDER BY c.fecha DESC;
		";
        $resCongresos = pg_query_params($this->conn, $sqlCongresos, []);
        if (!$resCongresos) {
            $this->logPg('findAllWithStats.congresos', $sqlCongresos);
            return new WP_Error('db_query_failed', 'Error en la consulta de congresos', [ 'status' => 500 ]);
        }
		$congresosRows = pg_fetch_all($resCongresos) ?: [];
		pg_free_result($resCongresos);

		$sqlDetalle = "
			SELECT 
				ac.id_congreso,
				co.id AS id_contacto,
				co.iglesia AS nombre_contacto,
				COUNT(CASE WHEN ac.id_estudiante IS NOT NULL THEN 1 END) AS estudiantes,
				COUNT(CASE WHEN ac.id_asistente IS NOT NULL THEN 1 END) AS asistentes_externos,
				(SELECT COUNT(*) FROM estudiantes WHERE id_contacto = co.id) AS estudiantes_inscritos
			FROM asistencias_congresos ac
			LEFT JOIN estudiantes e ON ac.id_estudiante = e.id
			LEFT JOIN asistentes_externos ae ON ac.id_asistente = ae.id
			LEFT JOIN contactos co ON (e.id_contacto = co.id OR ae.id_contacto = co.id)
			GROUP BY ac.id_congreso, co.id, co.iglesia
			ORDER BY ac.id_congreso, co.iglesia;
		";
        $resDetalle = pg_query_params($this->conn, $sqlDetalle, []);
        if (!$resDetalle) {
            $this->logPg('findAllWithStats.detalle', $sqlDetalle);
            return new WP_Error('db_query_failed', 'Error en la consulta de asistentes por contacto', [ 'status' => 500 ]);
        }
		$detalleRows = pg_fetch_all($resDetalle) ?: [];
		pg_free_result($resDetalle);

		$map = [];
		foreach ($congresosRows as $c) {
			$map[$c['id_congreso']] = [
				'idCongreso'      => intval($c['id_congreso']),
				'nombre'          => $c['nombre_congreso'],
				'fecha'           => $c['fecha_congreso'],
				'estado'          => $c['estado'],
				'totalAsistentes' => intval($c['total_asistentes']),
				'totalEstudiantes'=> intval($c['total_estudiantes']),
				'totalExternos'   => intval($c['total_externos']),
				'detalleContacto' => [],
			];
		}

		foreach ($detalleRows as $d) {
			$id = $d['id_congreso'];
			if (!isset($map[$id])) { continue; }
			$map[$id]['detalleContacto'][] = [
				'idContacto'          => intval($d['id_contacto']),
				'nombreContacto'       => $d['nombre_contacto'],
				'estudiantes'          => intval($d['estudiantes']),
				'asistentesExternos'   => intval($d['asistentes_externos']),
				'estudiantesInscritos' => intval($d['estudiantes_inscritos']),
			];
		}

		return array_values($map);
	}

    public function getById($id){
        $sql = "SELECT id, nombre, fecha, estado FROM congresos WHERE id=$1";
        $res = pg_query_params($this->conn, $sql, [ intval($id) ]);
        if (!$res) { $this->logPg('getById', $sql); return new WP_Error('db_query_failed','Error al obtener congreso',[ 'status'=>500 ]); }
		$row = pg_fetch_assoc($res); pg_free_result($res);
		if (!$row) return new WP_Error('not_found','Congreso no encontrado',[ 'status'=>404 ]);
        return [ 'id'=>intval($row['id']), 'nombre'=>$row['nombre'], 'fecha'=>$row['fecha'], 'estado'=>$row['estado'] ];
	}

	public function update($id, $data){
		$nombre = trim($data['nombre'] ?? '');
		$fecha  = trim($data['fecha'] ?? '');
		$estado = trim($data['estado'] ?? '');
        $sql = "UPDATE congresos SET nombre=$1, fecha=$2, estado=$3 WHERE id=$4";
        $res = pg_query_params($this->conn, $sql, [ $nombre, $fecha, $estado, intval($id) ]);
        if (!$res) { $this->logPg('update', $sql); return new WP_Error('db_update_failed','Error al actualizar congreso',[ 'status'=>500 ]); }
		return true;
	}

	public function getStats($idCongreso){
		// Totales basados en boletas_congresos (estado 'usado')
        $sql1 = 'SELECT COUNT(*) FROM boletas_congresos WHERE id_congreso = $1 AND estado = $2';
        $q1 = pg_query_params($this->conn, $sql1, [ intval($idCongreso), 'usado' ]);
        if (!$q1) { $this->logPg('getStats.q1', $sql1); return new WP_Error('db_query_failed','Error contando inscritos',[ 'status'=>500 ]); }
		$totalInscritos = intval(pg_fetch_result($q1, 0, 0));
		pg_free_result($q1);
        $useFechaCols = $this->hasColumn('boletas_congresos', 'fecha_llegada');
        $sql2 = $useFechaCols
            ? 'SELECT COUNT(*) FROM boletas_congresos WHERE id_congreso = $1 AND estado = $2 AND fecha_llegada IS NOT NULL'
            : "SELECT COUNT(*) FROM boletas_congresos WHERE id_congreso = $1 AND estado = $2 AND COALESCE((datos_registro->>'fecha_llegada')::text, '') <> ''";
        $q2 = pg_query_params($this->conn, $sql2, [ intval($idCongreso), 'usado' ]);
        if (!$q2) { $this->logPg('getStats.q2', $sql2); return new WP_Error('db_query_failed','Error contando llegadas',[ 'status'=>500 ]); }
		$totalLlegadas = intval(pg_fetch_result($q2, 0, 0));
		pg_free_result($q2);
        $sql3 = $useFechaCols
            ? 'SELECT COUNT(*) FROM boletas_congresos WHERE id_congreso = $1 AND estado = $2 AND fecha_almuerzo IS NOT NULL'
            : "SELECT COUNT(*) FROM boletas_congresos WHERE id_congreso = $1 AND estado = $2 AND COALESCE((datos_registro->>'fecha_almuerzo')::text, '') <> ''";
        $q3 = pg_query_params($this->conn, $sql3, [ intval($idCongreso), 'usado' ]);
        if (!$q3) { $this->logPg('getStats.q3', $sql3); return new WP_Error('db_query_failed','Error contando almuerzos',[ 'status'=>500 ]); }
		$totalAlmuerzos = intval(pg_fetch_result($q3, 0, 0));
		pg_free_result($q3);
		return [
			'totalInscritos' => $totalInscritos,
			'totalLlegadas' => $totalLlegadas,
			'totalAlmuerzos' => $totalAlmuerzos,
		];
	}

	private function fetchTicket($idCongreso, $numeroBoleta, $codigoVerificacion){
		$sql = "SELECT bc.id, bc.estado, bc.fecha_llegada, bc.fecha_almuerzo, bc.id_asistencia,
				ac.id_estudiante, ac.id_asistente
			FROM boletas_congresos bc
			JOIN asistencias_congresos ac ON bc.id_asistencia = ac.id
			WHERE bc.id_congreso = $1 AND bc.numero_boleta = $2 AND bc.codigo_verificacion = $3";
        $res = pg_query_params($this->conn, $sql, [ intval($idCongreso), strval($numeroBoleta), strval($codigoVerificacion) ]);
        if (!$res) { $this->logPg('fetchTicket', $sql); return new WP_Error('db_query_failed','Error consultando boleta',[ 'status'=>500 ]); }
		$row = pg_fetch_assoc($res); pg_free_result($res);
		if (!$row) return new WP_Error('not_found','Boleta no encontrada',[ 'status'=>404 ]);
		return $row;
	}

	public function checkin($idCongreso, $numeroBoleta, $codigoVerificacion, $tipo){
		$ticket = $this->fetchTicket($idCongreso, $numeroBoleta, $codigoVerificacion);
		if (is_wp_error($ticket)) return $ticket;
		if ($ticket['estado'] !== 'usado') return new WP_Error('invalid_state','Boleta no está en estado válido para asistencia',[ 'status'=>422 ]);
		$nowField = $tipo === 'almuerzo' ? 'fecha_almuerzo' : 'fecha_llegada';
		if ($tipo === 'almuerzo' && $ticket['fecha_llegada'] === null && $ticket['fecha_almuerzo'] === null){
			return new WP_Error('precondition_failed','Debe registrar la llegada antes del almuerzo',[ 'status'=>409 ]);
		}
		if ($ticket[$nowField] !== null){
			return new WP_Error('already_registered','Asistencia ya registrada previamente',[ 'status'=>409 ]);
		}
        $upd = pg_query_params($this->conn, "UPDATE boletas_congresos SET {$nowField}=NOW() WHERE id=$1", [ intval($ticket['id']) ]);
        if (!$upd) { $this->logPg('checkin.update'); return new WP_Error('db_update_failed','No fue posible registrar la asistencia',[ 'status'=>500 ]); }
		// Recuperar datos del participante básico
		$payload = [
			'boletaId' => intval($ticket['id']),
			'tipo'     => ($ticket['id_estudiante'] !== null) ? 'estudiante' : 'externo',
			'idEstudiante' => $ticket['id_estudiante'] !== null ? intval($ticket['id_estudiante']) : null,
			'idAsistente'  => $ticket['id_asistente'] !== null ? intval($ticket['id_asistente']) : null,
			'fecha'   => current_time('mysql'),
		];
		return $payload;
	}

	public function voidTicket($idCongreso, $numeroBoleta, $codigoVerificacion){
		$ticket = $this->fetchTicket($idCongreso, $numeroBoleta, $codigoVerificacion);
		if (is_wp_error($ticket)) return $ticket;
        $upd = pg_query_params($this->conn, "UPDATE boletas_congresos SET estado='anulado', fecha_llegada=NULL, fecha_almuerzo=NULL WHERE id=$1", [ intval($ticket['id']) ]);
        if (!$upd) { $this->logPg('voidTicket.update'); return new WP_Error('db_update_failed','No fue posible anular la boleta',[ 'status'=>500 ]); }
		return [ 'boletaId' => intval($ticket['id']), 'anulado' => true ];
	}

	public function listInscritos($idCongreso, $query = '', $tipo = '', $limit = 50, $offset = 0){
        $useFechaCols = $this->hasColumn('boletas_congresos', 'fecha_llegada');
        $fechaLlegSel = $useFechaCols ? 'bc.fecha_llegada' : "(bc.datos_registro->>'fecha_llegada')";
        $fechaAlmSel  = $useFechaCols ? 'bc.fecha_almuerzo' : "(bc.datos_registro->>'fecha_almuerzo')";
        $sql = "SELECT bc.id, bc.numero_boleta, bc.estado, {$fechaLlegSel} AS fecha_llegada, {$fechaAlmSel} AS fecha_almuerzo,
			ac.id_estudiante, ac.id_asistente,
            COALESCE(e.nombre1 || ' ' || e.apellido1, ae.nombre) as nombre,
			CASE WHEN ac.id_estudiante IS NOT NULL THEN 'estudiante' ELSE 'externo' END as tipo
			FROM boletas_congresos bc
			JOIN asistencias_congresos ac ON bc.id_asistencia = ac.id
			LEFT JOIN estudiantes e ON ac.id_estudiante = e.id
			LEFT JOIN asistentes_externos ae ON ac.id_asistente = ae.id
			WHERE bc.id_congreso = $1 AND bc.estado = 'usado'";
		$params = [ intval($idCongreso) ];
		if ($query !== '') { $sql .= " AND (bc.numero_boleta::text ILIKE $2 OR COALESCE(e.nombre1 || ' ' || e.apellido1, ae.nombre) ILIKE $2)"; $params[] = '%'.strval($query).'%'; }
		if ($tipo === 'estudiante') { $sql .= " AND ac.id_estudiante IS NOT NULL"; }
		if ($tipo === 'externo') { $sql .= " AND ac.id_asistente IS NOT NULL"; }
		$sql .= " ORDER BY bc.id DESC LIMIT $".(count($params)+1)." OFFSET $".(count($params)+2);
		$params[] = intval($limit); $params[] = intval($offset);
        $res = pg_query_params($this->conn, $sql, $params);
        if (!$res) { $this->logPg('listInscritos', $sql); return new WP_Error('db_query_failed','Error listando inscritos',[ 'status'=>500 ]); }
		$rows = [];
		while($row = pg_fetch_assoc($res)) { $rows[] = [
			'id' => intval($row['id']), 'numero' => $row['numero_boleta'], 'estado' => $row['estado'],
			'fechaLlegada' => $row['fecha_llegada'], 'fechaAlmuerzo' => $row['fecha_almuerzo'],
			'tipo' => $row['tipo'], 'nombre' => $row['nombre'],
		]; }
		pg_free_result($res);
		return [ 'items' => $rows ];
	}

	public function listNoAsistentes($idCongreso, $tipo = 'llegada', $limit = 50, $offset = 0){
        $useFechaCols = $this->hasColumn('boletas_congresos', 'fecha_llegada');
        $field = $tipo === 'almuerzo' ? ($useFechaCols ? 'fecha_almuerzo' : "(bc.datos_registro->>'fecha_almuerzo')") : ($useFechaCols ? 'fecha_llegada' : "(bc.datos_registro->>'fecha_llegada')");
        $sql = "SELECT bc.id, bc.numero_boleta, bc.estado,
            COALESCE(e.nombre1 || ' ' || e.apellido1, ae.nombre) as nombre,
			CASE WHEN ac.id_estudiante IS NOT NULL THEN 'estudiante' ELSE 'externo' END as tipo
			FROM boletas_congresos bc
			JOIN asistencias_congresos ac ON bc.id_asistencia = ac.id
			LEFT JOIN estudiantes e ON ac.id_estudiante = e.id
			LEFT JOIN asistentes_externos ae ON ac.id_asistente = ae.id
			WHERE bc.id_congreso = $1 AND bc.estado='usado' AND {$field} IS NULL
			ORDER BY bc.id DESC LIMIT $2 OFFSET $3";
        $res = pg_query_params($this->conn, $sql, [ intval($idCongreso), intval($limit), intval($offset) ]);
        if (!$res) { $this->logPg('listNoAsistentes', $sql); return new WP_Error('db_query_failed','Error listando no asistentes',[ 'status'=>500 ]); }
		$rows = [];
        while($row = pg_fetch_assoc($res)) { $rows[] = [ 'id' => intval($row['id']), 'numero'=>$row['numero_boleta'], 'estado'=>$row['estado'], 'nombre'=>$row['nombre'], 'tipo'=>$row['tipo'] ]; }
		pg_free_result($res);
		return [ 'items' => $rows ];
	}

	public function listUltimos($idCongreso, $limit = 20){
        $useFechaCols = $this->hasColumn('boletas_congresos', 'fecha_llegada');
        $momentoExpr = $useFechaCols
            ? "GREATEST(COALESCE(bc.fecha_llegada, 'epoch'), COALESCE(bc.fecha_almuerzo, 'epoch'))"
            : "GREATEST( COALESCE( (bc.datos_registro->>'fecha_llegada')::timestamp, 'epoch'::timestamp), COALESCE( (bc.datos_registro->>'fecha_almuerzo')::timestamp, 'epoch'::timestamp) )";
        $tipoExpr = $useFechaCols
            ? "CASE WHEN bc.fecha_almuerzo IS NOT NULL THEN 'almuerzo' ELSE 'llegada' END"
            : "CASE WHEN (bc.datos_registro->>'fecha_almuerzo') IS NOT NULL THEN 'almuerzo' ELSE 'llegada' END";
        $sql = "SELECT bc.id, bc.numero_boleta,
            {$momentoExpr} as momento,
			COALESCE(e.nombre1 || ' ' || e.apellido1, ae.nombre) as nombre,
            {$tipoExpr} as tipo
			FROM boletas_congresos bc
			JOIN asistencias_congresos ac ON bc.id_asistencia = ac.id
			LEFT JOIN estudiantes e ON ac.id_estudiante = e.id
			LEFT JOIN asistentes_externos ae ON ac.id_asistente = ae.id
            WHERE bc.id_congreso=$1 AND ( {$momentoExpr} IS NOT NULL )
			ORDER BY momento DESC LIMIT $2";
        $res = pg_query_params($this->conn, $sql, [ intval($idCongreso), intval($limit) ]);
        if (!$res) { $this->logPg('listUltimos', $sql); return new WP_Error('db_query_failed','Error listando últimos',[ 'status'=>500 ]); }
		$rows = [];
        while($row = pg_fetch_assoc($res)) { $rows[] = [ 'id'=>intval($row['id']), 'numero'=>$row['numero_boleta'], 'momento'=>$row['momento'], 'nombre'=>$row['nombre'], 'tipo'=>$row['tipo'] ]; }
		pg_free_result($res);
		return [ 'items' => $rows ];
	}
}