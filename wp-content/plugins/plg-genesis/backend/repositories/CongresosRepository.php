<?php
if (!defined('ABSPATH')) { exit; }

class PlgGenesis_CongresosRepository {
	private $conn;

	public function __construct($connection) {
		$this->conn = $connection;
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
		if (!$res) return new WP_Error('db_query_failed','Error al obtener congreso',[ 'status'=>500 ]);
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
		if (!$res) return new WP_Error('db_update_failed','Error al actualizar congreso',[ 'status'=>500 ]);
		return true;
	}
}