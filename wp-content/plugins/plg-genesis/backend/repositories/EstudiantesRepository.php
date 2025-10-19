<?php
if (!defined('ABSPATH')) { exit; }

class PlgGenesis_EstudiantesRepository {
	private $conn;

	public function __construct($connection) {
		$this->conn = $connection;
	}

	public function findByContactoId($contactoId) {
		$sql = "SELECT 
			e.id_estudiante,
			CONCAT(e.nombre1, ' ', e.nombre2, ' ', e.apellido1, ' ', e.apellido2) AS nombre_completo,
			e.doc_identidad,
			e.celular,
			e.email
		FROM estudiantes e
		WHERE e.id_contacto = $1";
		$result = pg_query_params($this->conn, $sql, [ intval($contactoId) ]);
		if (!$result) {
			return new WP_Error('db_query_failed', 'Error al obtener los datos de los estudiantes', [ 'status' => 500 ]);
		}
		$rows = [];
		while ($row = pg_fetch_assoc($result)) {
			$rows[] = [
				'idEstudiante'   => intval($row['id_estudiante']),
				'nombreCompleto' => $row['nombre_completo'],
				'docIdentidad'   => $row['doc_identidad'],
				'celular'        => $row['celular'],
				'email'          => $row['email'],
			];
		}
		pg_free_result($result);
		return $rows;
	}

	public function list($q = '', $page = 1, $limit = 20) {
		$page = max(1, intval($page));
		$limit = max(1, min(100, intval($limit)));
		$offset = ($page - 1) * $limit;
		$params = [];
		$idx = 1;
		$where = [];
		if ($q !== '') {
			$ph = '$' . $idx;
			$where[] = "(e.id_estudiante ILIKE {$ph} OR e.doc_identidad ILIKE {$ph} OR COALESCE(e.celular,'') ILIKE {$ph} OR COALESCE(e.email,'') ILIKE {$ph} OR CONCAT(e.nombre1,' ',COALESCE(e.nombre2,''),' ',e.apellido1,' ',COALESCE(e.apellido2,'')) ILIKE {$ph})";
			$params[] = '%' . $q . '%';
			$idx++;
		}
		$whereSql = count($where) ? ('WHERE ' . implode(' AND ', $where)) : '';
		// total
		$sqlCount = "SELECT COUNT(*) FROM estudiantes e $whereSql";
		$resC = pg_query_params($this->conn, $sqlCount, $params);
		if (!$resC) { return new WP_Error('db_query_failed','Error contando estudiantes',[ 'status'=>500 ]); }
		$total = intval(pg_fetch_result($resC, 0, 0));
		pg_free_result($resC);
		// page
		$phLimit = '$' . $idx; $phOffset = '$' . ($idx+1);
		$sql = "SELECT e.id_estudiante, e.doc_identidad, e.celular, e.email,
			CONCAT(e.nombre1,' ',COALESCE(e.nombre2,''),' ',e.apellido1,' ',COALESCE(e.apellido2,'')) AS nombre_completo
			FROM estudiantes e $whereSql ORDER BY e.id_estudiante ASC LIMIT {$phLimit} OFFSET {$phOffset}";
		$paramsPage = $params; $paramsPage[] = $limit; $paramsPage[] = $offset;
		$res = pg_query_params($this->conn, $sql, $paramsPage);
		if (!$res) { return new WP_Error('db_query_failed','Error listando estudiantes',[ 'status'=>500 ]); }
		$items = [];
		while ($row = pg_fetch_assoc($res)) {
			$items[] = [
				'idEstudiante'   => $row['id_estudiante'],
				'nombreCompleto' => $row['nombre_completo'],
				'docIdentidad'   => $row['doc_identidad'],
				'celular'        => $row['celular'],
				'email'          => $row['email'],
			];
		}
		pg_free_result($res);
		return [ 'items'=>$items, 'page'=>$page, 'limit'=>$limit, 'total'=>$total ];
	}

	public function countEstudiantes() {
		$res = pg_query_params($this->conn, 'SELECT COUNT(*) AS total_estudiantes FROM estudiantes', []);
		if (!$res) { return new WP_Error('db_query_failed', 'Error contando estudiantes', [ 'status' => 500 ]); }
		$total = intval(pg_fetch_result($res, 0, 0));
		pg_free_result($res);
		return $total;
	}

	public function getContactCodeByContactCode($contactCode) {
		// Simplemente validar y retornar el code (el code ES el code)
		$res = pg_query_params($this->conn, 'SELECT TRIM(code) as code FROM contactos WHERE code = $1', [ trim($contactCode) ]);
		if (!$res) { return new WP_Error('db_query_failed', 'Error validando código del contacto', [ 'status' => 500 ]); }
		$row = pg_fetch_assoc($res);
		pg_free_result($res);
		if (!$row) { return new WP_Error('not_found', 'Contacto no encontrado', [ 'status' => 404 ]); }
		return trim($row['code'] ?? '');
	}

	// Deprecated: usar getContactCodeByContactCode
	public function getContactCode($contactId) {
		$res = pg_query_params($this->conn, 'SELECT TRIM(code) as code FROM contactos WHERE id = $1', [ intval($contactId) ]);
		if (!$res) { return new WP_Error('db_query_failed', 'Error obteniendo código del contacto', [ 'status' => 500 ]); }
		$row = pg_fetch_assoc($res);
		pg_free_result($res);
		if (!$row) { return new WP_Error('not_found', 'Contacto no encontrado', [ 'status' => 404 ]); }
		return trim($row['code'] ?? '');
	}

	public function getContactIdByCode($contactCode) {
		$res = pg_query_params($this->conn, 'SELECT id FROM contactos WHERE code = $1', [ trim($contactCode) ]);
		if (!$res) { return new WP_Error('db_query_failed', 'Error obteniendo ID del contacto', [ 'status' => 500 ]); }
		$row = pg_fetch_assoc($res);
		pg_free_result($res);
		if (!$row) { return new WP_Error('not_found', 'Contacto no encontrado', [ 'status' => 404 ]); }
		return intval($row['id']);
	}

	public function existsStudentId($idEstudiante) {
		$res = pg_query_params($this->conn, 'SELECT COUNT(*) FROM estudiantes WHERE id_estudiante = $1', [ strval($idEstudiante) ]);
		if (!$res) { return new WP_Error('db_query_failed', 'Error verificando id_estudiante', [ 'status' => 500 ]); }
		$count = intval(pg_fetch_result($res, 0, 0));
		pg_free_result($res);
		return $count > 0;
	}

    public function create(array $e) {
		$sql = "INSERT INTO estudiantes (
			id_contacto, doc_identidad, nombre1, nombre2, apellido1, apellido2,
			celular, email, ciudad, iglesia, id_estudiante, estado_civil, escolaridad, ocupacion
		) VALUES ($1,$2,$3,$4,$5,$6,$7,$8,$9,$10,$11,$12,$13,$14)";
		$params = [
			intval($e['id_contacto']),
			$e['doc_identidad'],
			$e['nombre1'],
			$e['nombre2'] ?? null,
			$e['apellido1'],
			$e['apellido2'] ?? null,
			$e['celular'] ?? null,
			$e['email'] ?? null,
			$e['ciudad'] ?? null,
			$e['iglesia'] ?? null,
			$e['id_estudiante'],
			$e['estado_civil'],
			$e['escolaridad'],
			$e['ocupacion'] ?? null,
		];
		$res = pg_query_params($this->conn, $sql, $params);
        if (!$res) {
			return new WP_Error('db_query_failed', 'Error creando estudiante', [ 'status' => 500, 'pg' => pg_last_error($this->conn) ]);
		}
		pg_free_result($res);
        return $e['id_estudiante'];
	}

	public function getByStudentId($idEstudiante) {
		$sql = "SELECT id_estudiante, id_contacto, doc_identidad, nombre1, nombre2, apellido1, apellido2,
			celular, email, ciudad, iglesia, estado_civil, escolaridad, ocupacion
			FROM estudiantes WHERE id_estudiante = $1";
		$res = pg_query_params($this->conn, $sql, [ strval($idEstudiante) ]);
		if (!$res) { return new WP_Error('db_query_failed', 'Error consultando estudiante', [ 'status' => 500 ]); }
		$row = pg_fetch_assoc($res);
		pg_free_result($res);
		if (!$row) { return new WP_Error('not_found', 'Estudiante no encontrado', [ 'status' => 404 ]); }
		return [
			'idEstudiante' => $row['id_estudiante'],
			'idContacto' => intval($row['id_contacto']),
			'docIdentidad' => $row['doc_identidad'],
			'nombre1' => $row['nombre1'],
			'nombre2' => $row['nombre2'],
			'apellido1' => $row['apellido1'],
			'apellido2' => $row['apellido2'],
			'celular' => $row['celular'],
			'email' => $row['email'],
			'ciudad' => $row['ciudad'],
			'iglesia' => $row['iglesia'],
			'estadoCivil' => $row['estado_civil'],
			'escolaridad' => $row['escolaridad'],
			'ocupacion' => $row['ocupacion'],
		];
	}

	public function updateByStudentId($idEstudiante, array $e) {
		$sql = "UPDATE estudiantes SET 
			doc_identidad=$1, nombre1=$2, nombre2=$3, apellido1=$4, apellido2=$5,
			celular=$6, email=$7, ciudad=$8, iglesia=$9, estado_civil=$10, escolaridad=$11, ocupacion=$12
			WHERE id_estudiante=$13";
		$params = [
			$e['doc_identidad'],
			$e['nombre1'],
			$e['nombre2'] ?? null,
			$e['apellido1'],
			$e['apellido2'] ?? null,
			$e['celular'] ?? null,
			$e['email'] ?? null,
			$e['ciudad'] ?? null,
			$e['iglesia'] ?? null,
			$e['estado_civil'],
			$e['escolaridad'],
			$e['ocupacion'] ?? null,
			$idEstudiante,
		];
		$res = pg_query_params($this->conn, $sql, $params);
		if (!$res) {
			return new WP_Error('db_query_failed', 'Error actualizando estudiante', [ 'status' => 500 ]);
		}
		pg_free_result($res);
		return true;
	}

	public function updatePartialByStudentId($idEstudiante, $celular = null, $email = null, $docIdentidad = null) {
		$sql = "UPDATE estudiantes SET celular=COALESCE($1,celular), email=COALESCE($2,email), doc_identidad=COALESCE($3,doc_identidad) WHERE id_estudiante=$4";
		$res = pg_query_params($this->conn, $sql, [ $celular, $email, $docIdentidad, strval($idEstudiante) ]);
		if (!$res) { return new WP_Error('db_update_failed','Error actualizando estudiante',[ 'status'=>500 ]); }
		pg_free_result($res);
		return true;
	}

	public function getLastCourseForStudent($idEstudiante, $cursoId) {
		$sql = "SELECT fecha, porcentaje FROM estudiantes_cursos WHERE estudiante_id=$1 AND curso_id=$2 ORDER BY fecha DESC LIMIT 1";
		$res = pg_query_params($this->conn, $sql, [ intval($idEstudiante), intval($cursoId) ]);
		if (!$res) { return new WP_Error('db_query_failed','Error consultando curso previo',[ 'status'=>500 ]); }
		$row = pg_fetch_assoc($res); pg_free_result($res);
		if (!$row) return null;
		return [ 'fecha' => $row['fecha'], 'porcentaje' => floatval($row['porcentaje']) ];
	}

	public function assignCourse($idEstudiante, $cursoId, $porcentaje, $forzar = false) {
		$prev = $this->getLastCourseForStudent($idEstudiante, $cursoId);
		if ($prev && !$forzar) {
			return new WP_Error('course_already_assigned','El estudiante ya tiene asignado este curso',[ 'status'=>409, 'curso_anterior'=>$prev ]);
		}
		$sql = "INSERT INTO estudiantes_cursos (estudiante_id, curso_id, fecha, porcentaje, enviado) VALUES ($1,$2,NOW(),$3,false)";
		$res = pg_query_params($this->conn, $sql, [ intval($idEstudiante), intval($cursoId), floatval($porcentaje) ]);
		if (!$res) { return new WP_Error('db_update_failed','Error asignando curso',[ 'status'=>500 ]); }
		pg_free_result($res);
		return true;
	}

	public function assignCourseByStudentCode($idEstudianteCode, $cursoId, $porcentaje, $forzar=false){
		// Resolver estudiantes.id a partir de id_estudiante (código)
		$res = pg_query_params($this->conn, 'SELECT id FROM estudiantes WHERE id_estudiante=$1', [ strval($idEstudianteCode) ]);
		if (!$res) { return new WP_Error('db_query_failed','Error resolviendo estudiante',[ 'status'=>500 ]); }
		$row = pg_fetch_assoc($res); pg_free_result($res);
		if (!$row) return new WP_Error('not_found','Estudiante no encontrado',[ 'status'=>404 ]);
		return $this->assignCourse(intval($row['id']), intval($cursoId), floatval($porcentaje), (bool)$forzar);
	}

	public function getObservaciones($idEstudiante) {
		$sql = "SELECT o.id, o.observacion, o.fecha, o.tipo, o.usuario_id
			FROM observaciones_estudiantes o
			WHERE o.estudiante_id=$1
			ORDER BY o.fecha DESC, o.id DESC";
		$res = pg_query_params($this->conn, $sql, [ intval($idEstudiante) ]);
		if (!$res) { return new WP_Error('db_query_failed','Error listando observaciones',[ 'status'=>500 ]); }
		$items = [];
		while ($row = pg_fetch_assoc($res)) {
			$usuarioNombre = null;
			if (!empty($row['usuario_id'])) {
				$wpUser = get_user_by('id', intval($row['usuario_id']));
				if ($wpUser) { $usuarioNombre = $wpUser->display_name ? $wpUser->display_name : $wpUser->user_login; }
			}
			$items[] = [
				'id' => intval($row['id']),
				'observacion' => $row['observacion'],
				'fecha' => $row['fecha'],
				'tipo' => $row['tipo'],
				'usuario_id' => !empty($row['usuario_id']) ? intval($row['usuario_id']) : null,
				'usuario_nombre' => $usuarioNombre,
			];
		}
		pg_free_result($res);
		return $items;
	}

	public function getObservacionesByStudentCode($idEstudianteCode) {
		$res = pg_query_params($this->conn, 'SELECT id FROM estudiantes WHERE id_estudiante=$1', [ strval($idEstudianteCode) ]);
		if (!$res) { return new WP_Error('db_query_failed','Error resolviendo estudiante',[ 'status'=>500 ]); }
		$row = pg_fetch_assoc($res); pg_free_result($res);
		if (!$row) return [];
		return $this->getObservaciones(intval($row['id']));
	}

	public function addObservacion($idEstudiante, $observacion, $tipo = 'General', $usuarioId = null) {
		$sql = "INSERT INTO observaciones_estudiantes (estudiante_id, observacion, fecha, usuario_id, tipo) VALUES ($1,$2,NOW(),$3,$4) RETURNING id, fecha";
		$params = [ intval($idEstudiante), strval($observacion), $usuarioId ? intval($usuarioId) : null, strval($tipo) ];
		$res = pg_query_params($this->conn, $sql, $params);
		if (!$res) { return new WP_Error('db_update_failed','Error creando observación',[ 'status'=>500 ]); }
		$row = pg_fetch_assoc($res); pg_free_result($res);
		if (!$row) { return new WP_Error('db_update_failed','Observación no creada',[ 'status'=>500 ]); }
		$usuarioNombre = null;
		if ($usuarioId) {
			$wpUser = get_user_by('id', intval($usuarioId));
			if ($wpUser) { $usuarioNombre = $wpUser->display_name ? $wpUser->display_name : $wpUser->user_login; }
		}
		return [
			'id' => intval($row['id']),
			'observacion' => strval($observacion),
			'fecha' => $row['fecha'],
			'tipo' => $tipo,
			'usuario_id' => $usuarioId ? intval($usuarioId) : null,
			'usuario_nombre' => $usuarioNombre,
		];
	}

	public function addObservacionByStudentCode($idEstudianteCode, $observacion, $tipo = 'General', $usuarioId = null) {
		$res = pg_query_params($this->conn, 'SELECT id FROM estudiantes WHERE id_estudiante=$1', [ strval($idEstudianteCode) ]);
		if (!$res) { return new WP_Error('db_query_failed','Error resolviendo estudiante',[ 'status'=>500 ]); }
		$row = pg_fetch_assoc($res); pg_free_result($res);
		if (!$row) return new WP_Error('not_found','Estudiante no encontrado',[ 'status'=>404 ]);
		return $this->addObservacion(intval($row['id']), $observacion, $tipo, $usuarioId);
	}

	public function quickView($idEstudiante) {
		// Estudiante y contacto
		$sqlE = "SELECT e.id_estudiante, e.doc_identidad, e.nombre1, e.nombre2, e.apellido1, e.apellido2,
			e.celular, e.email, e.ciudad, e.iglesia, e.id_contacto,
			c.code AS contacto_codigo, c.nombre AS contacto_nombre, c.iglesia AS contacto_iglesia
			FROM estudiantes e LEFT JOIN contactos c ON c.id = e.id_contacto WHERE e.id_estudiante=$1";
		$re = pg_query_params($this->conn, $sqlE, [ strval($idEstudiante) ]);
		if (!$re) { return new WP_Error('db_query_failed','Error obteniendo estudiante',[ 'status'=>500 ]); }
		$e = pg_fetch_assoc($re); pg_free_result($re);
		if (!$e) return new WP_Error('not_found','Estudiante no encontrado',[ 'status'=>404 ]);
		$nombreCompleto = trim($e['nombre1'].' '.($e['nombre2']??'').' '.$e['apellido1'].' '.($e['apellido2']??''));

		// Estadísticas
		$rs = pg_query_params($this->conn, "SELECT COUNT(*) AS total, COALESCE(AVG(porcentaje),0) AS avg, MAX(fecha) AS last FROM estudiantes_cursos WHERE estudiante_id=$1", [ intval($e['id_contacto']) ? intval($e['id_contacto']) : 0 ]);
		// OJO: estudiante_id debe ser clave a estudiantes.id; la migración v1_0 usa INTEGER sin FK estricta. Busquemos id por id_estudiante.
		if (!$rs) { /* fallback usando join */ }
		$qr = pg_query_params($this->conn, "SELECT s.total, s.avg, s.last FROM (
			SELECT COUNT(*) AS total, COALESCE(AVG(ec.porcentaje),0) AS avg, MAX(ec.fecha) AS last
			FROM estudiantes_cursos ec JOIN estudiantes e2 ON e2.id = ec.estudiante_id WHERE e2.id_estudiante=$1
		) s", [ strval($idEstudiante) ]);
		if (!$qr) { return new WP_Error('db_query_failed','Error obteniendo estadísticas',[ 'status'=>500 ]); }
		$st = pg_fetch_assoc($qr); pg_free_result($qr);

		// Último curso
		$qc = pg_query_params($this->conn, "SELECT ec.fecha, ec.porcentaje, c.nombre, c.descripcion, n.nombre AS nivel
			FROM estudiantes_cursos ec
			JOIN estudiantes e2 ON e2.id = ec.estudiante_id
			JOIN cursos c ON c.id = ec.curso_id
			LEFT JOIN niveles n ON n.id = c.nivel_id
			WHERE e2.id_estudiante=$1
			ORDER BY ec.fecha DESC LIMIT 1", [ strval($idEstudiante) ]);
		$ultimoCurso = null;
		if ($qc) { $row = pg_fetch_assoc($qc); pg_free_result($qc); if ($row){ $ultimoCurso = [ 'fecha'=>$row['fecha'], 'porcentaje'=>floatval($row['porcentaje']), 'nombre'=>$row['nombre'], 'descripcion'=>$row['descripcion'], 'nivel'=>$row['nivel'] ]; } }

		// Última observación
		$qo = pg_query_params($this->conn, "SELECT o.observacion, o.fecha, o.tipo, o.usuario_id
			FROM observaciones_estudiantes o
			JOIN estudiantes e2 ON e2.id = o.estudiante_id
			WHERE e2.id_estudiante=$1 ORDER BY o.fecha DESC, o.id DESC LIMIT 1", [ strval($idEstudiante) ]);
		$ultimaObs = null; if ($qo){ $r = pg_fetch_assoc($qo); pg_free_result($qo); if ($r){ $nombre = null; if (!empty($r['usuario_id'])){ $wpUser = get_user_by('id', intval($r['usuario_id'])); if ($wpUser) { $nombre = $wpUser->display_name ? $wpUser->display_name : $wpUser->user_login; } } $ultimaObs = [ 'observacion'=>$r['observacion'], 'fecha'=>$r['fecha'], 'tipo'=>$r['tipo'], 'usuario_nombre'=>$nombre ]; } }

		return [
			'estudiante' => [
				'codigo' => $e['id_estudiante'],
				'nombre_completo' => $nombreCompleto,
				'documento' => $e['doc_identidad'],
				'email' => $e['email'],
				'celular' => $e['celular'],
				'ciudad' => $e['ciudad'],
				'iglesia' => $e['iglesia'],
				'contacto' => [
					'codigo' => $e['contacto_codigo'],
					'nombre' => $e['contacto_nombre'],
					'iglesia' => $e['contacto_iglesia'],
				]
			],
			'estadisticas' => [
				'total_cursos' => intval($st['total'] ?? 0),
				'promedio_porcentaje' => round(floatval($st['avg'] ?? 0), 1),
				'ultima_actividad' => $st['last'] ?? null,
			],
			'ultimo_curso' => $ultimoCurso,
			'ultima_observacion' => $ultimaObs,
		];
	}

    public function existsByDocumento($docIdentidad) {
        $res = pg_query_params($this->conn, 'SELECT COUNT(*) FROM estudiantes WHERE doc_identidad = $1', [ strval($docIdentidad) ]);
        if (!$res) { return new WP_Error('db_query_failed', 'Error verificando documento', [ 'status' => 500 ]); }
        $count = intval(pg_fetch_result($res, 0, 0));
        pg_free_result($res);
        return $count > 0;
    }
}