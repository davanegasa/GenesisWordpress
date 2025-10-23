<?php
if (!defined('ABSPATH')) { exit; }

class PlgGenesis_ContactosRepository {
	private $conn;
	public function __construct($connection) { $this->conn = $connection; }

	public function search($queryText, $limit = 20, $offset = 0) {
		$q = trim((string)$queryText);
		$limit = max(1, min(100, intval($limit)));
		$offset = max(0, intval($offset));

		$sql = "SELECT id, nombre, iglesia, email, celular, direccion, ciudad, code
			FROM contactos
			WHERE ($1 = '' OR nombre ILIKE '%' || $1 || '%' OR iglesia ILIKE '%' || $1 || '%' OR email ILIKE '%' || $1 || '%')
			ORDER BY nombre ASC
			LIMIT $2 OFFSET $3";
		$res = pg_query_params($this->conn, $sql, [ $q, $limit, $offset ]);
		if (!$res) {
			return new WP_Error('db_query_failed', 'Error al obtener contactos', [ 'status' => 500 ]);
		}
		$items = [];
		while ($row = pg_fetch_assoc($res)) {
			$items[] = [
				'code'     => trim($row['code']),
				'nombre'   => $row['nombre'],
				'iglesia'  => $row['iglesia'],
				'email'    => $row['email'],
				'celular'  => $row['celular'],
				'direccion'=> $row['direccion'],
				'ciudad'   => $row['ciudad'],
			];
		}
		pg_free_result($res);

		$sqlCount = "SELECT COUNT(*) AS total FROM contactos
			WHERE ($1 = '' OR nombre ILIKE '%' || $1 || '%' OR iglesia ILIKE '%' || $1 || '%' OR email ILIKE '%' || $1 || '%')";
		$resCount = pg_query_params($this->conn, $sqlCount, [ $q ]);
		$total = $resCount ? intval(pg_fetch_result($resCount, 0, 0)) : count($items);
		if ($resCount) pg_free_result($resCount);

		return [ 'items' => $items, 'limit' => $limit, 'offset' => $offset, 'total' => $total ];
	}

	public function create($data) {
		$nombre    = isset($data['nombre']) ? trim($data['nombre']) : '';
		$iglesia   = isset($data['iglesia']) ? trim($data['iglesia']) : '';
		$email     = isset($data['email']) ? trim($data['email']) : '';
		$celular   = isset($data['celular']) ? trim($data['celular']) : '';
		$direccion = isset($data['direccion']) ? trim($data['direccion']) : '';
		$ciudad    = isset($data['ciudad']) ? trim($data['ciudad']) : '';

		$sql = "INSERT INTO contactos (nombre, iglesia, email, celular, direccion, ciudad)
				VALUES ($1,$2,$3,$4,$5,$6) RETURNING id";
		$res = pg_query_params($this->conn, $sql, [ $nombre, $iglesia, $email, $celular, $direccion, $ciudad ]);
		if (!$res) {
			return new WP_Error('db_insert_failed', 'Error al crear contacto', [ 'status' => 500 ]);
		}
		$id = intval(pg_fetch_result($res, 0, 0));
		pg_free_result($res);
		return $id;
	}

	public function getById($id) {
		$sql = "SELECT id, nombre, iglesia, email, celular, direccion, ciudad, code FROM contactos WHERE id = $1";
		$res = pg_query_params($this->conn, $sql, [ intval($id) ]);
		if (!$res) return new WP_Error('db_query_failed', 'Error al obtener contacto', [ 'status' => 500 ]);
		$row = pg_fetch_assoc($res);
		pg_free_result($res);
		if (!$row) return new WP_Error('not_found', 'Contacto no encontrado', [ 'status' => 404 ]);
		return [
			'id'       => intval($row['id']),
			'code'     => trim($row['code']),
			'nombre'   => $row['nombre'],
			'iglesia'  => $row['iglesia'],
			'email'    => $row['email'],
			'celular'  => $row['celular'],
			'direccion'=> $row['direccion'],
			'ciudad'   => $row['ciudad'],
		];
	}

	public function getByCode($code) {
		$sql = "SELECT id, nombre, iglesia, email, celular, direccion, ciudad, code FROM contactos WHERE code = $1";
		$res = pg_query_params($this->conn, $sql, [ trim($code) ]);
		if (!$res) return new WP_Error('db_query_failed', 'Error al obtener contacto', [ 'status' => 500 ]);
		$row = pg_fetch_assoc($res);
		pg_free_result($res);
		if (!$row) return new WP_Error('not_found', 'Contacto no encontrado', [ 'status' => 404 ]);
		return [
			'id'       => intval($row['id']),
			'code'     => trim($row['code']),
			'nombre'   => $row['nombre'],
			'iglesia'  => $row['iglesia'],
			'email'    => $row['email'],
			'celular'  => $row['celular'],
			'direccion'=> $row['direccion'],
			'ciudad'   => $row['ciudad'],
		];
	}

	public function update($id, $data) {
		$nombre    = isset($data['nombre']) ? trim($data['nombre']) : '';
		$iglesia   = isset($data['iglesia']) ? trim($data['iglesia']) : '';
		$email     = isset($data['email']) ? trim($data['email']) : '';
		$celular   = isset($data['celular']) ? trim($data['celular']) : '';
		$direccion = isset($data['direccion']) ? trim($data['direccion']) : '';
		$ciudad    = isset($data['ciudad']) ? trim($data['ciudad']) : '';

		$sql = "UPDATE contactos SET nombre=$1, iglesia=$2, email=$3, celular=$4, direccion=$5, ciudad=$6 WHERE id=$7";
		$res = pg_query_params($this->conn, $sql, [ $nombre, $iglesia, $email, $celular, $direccion, $ciudad, intval($id) ]);
		if (!$res) return new WP_Error('db_update_failed', 'Error al actualizar contacto', [ 'status' => 500 ]);
		return true;
	}

	public function updateByCode($code, $data) {
		$nombre    = isset($data['nombre']) ? trim($data['nombre']) : '';
		$iglesia   = isset($data['iglesia']) ? trim($data['iglesia']) : '';
		$email     = isset($data['email']) ? trim($data['email']) : '';
		$celular   = isset($data['celular']) ? trim($data['celular']) : '';
		$direccion = isset($data['direccion']) ? trim($data['direccion']) : '';
		$ciudad    = isset($data['ciudad']) ? trim($data['ciudad']) : '';

		$sql = "UPDATE contactos SET nombre=$1, iglesia=$2, email=$3, celular=$4, direccion=$5, ciudad=$6 WHERE code=$7";
		$res = pg_query_params($this->conn, $sql, [ $nombre, $iglesia, $email, $celular, $direccion, $ciudad, trim($code) ]);
		if (!$res) return new WP_Error('db_update_failed', 'Error al actualizar contacto', [ 'status' => 500 ]);
		return true;
	}

	/**
	 * Obtiene el historial académico completo de un contacto
	 */
	public function getAcademicHistory($code) {
		// Primero obtener el contacto para tener el ID
		$contacto = $this->getByCode($code);
		if (is_wp_error($contacto)) {
			return $contacto;
		}
		
		$contactoId = $contacto['id'];
		
		// Obtener programas asignados directamente al contacto
		$programs = $this->getContactPrograms($contactoId);
		if (is_wp_error($programs)) {
			return $programs;
		}
		
		// Para cada programa, obtener los cursos
		$programsWithCourses = [];
		foreach ($programs as $program) {
			$courses = $this->getCoursesByProgram($contactoId, $program['programa_id']);
			if (!is_wp_error($courses)) {
				$program['cursos'] = $courses;
			} else {
				$program['cursos'] = [];
			}
			$programsWithCourses[] = $program;
		}
		
		// Obtener estudiantes que heredan de este contacto
		$inheritedStudents = $this->getInheritedStudents($contactoId);
		if (is_wp_error($inheritedStudents)) {
			$inheritedStudents = [];
		}
		
		// Obtener estadísticas generales
		$stats = $this->getOverallStats($contactoId);
		if (is_wp_error($stats)) {
			$stats = [
				'total_programas' => count($programsWithCourses),
				'total_estudiantes' => count($inheritedStudents),
				'total_cursos_programa' => 0
			];
		}
		
		return [
			'programs' => $programsWithCourses,
			'inherited_students' => $inheritedStudents,
			'statistics' => $stats
		];
	}

	/**
	 * Obtiene los programas asignados directamente al contacto
	 */
	private function getContactPrograms($contactoId) {
		$sql = "
			SELECT 
				pa.id as asignacion_id,
				pa.programa_id,
				pa.fecha_asignacion,
				p.nombre as programa_nombre,
				p.descripcion as programa_descripcion
			FROM programas_asignaciones pa
			INNER JOIN programas p ON pa.programa_id = p.id
			WHERE pa.contacto_id = $1
			ORDER BY pa.fecha_asignacion DESC
		";
		
		$result = pg_query_params($this->conn, $sql, [$contactoId]);
		if (!$result) {
			return new WP_Error('db_query_failed', 'Error al obtener programas del contacto', ['status' => 500]);
		}
		
		$programs = [];
		while ($row = pg_fetch_assoc($result)) {
			$programs[] = [
				'asignacion_id' => intval($row['asignacion_id']),
				'programa_id' => intval($row['programa_id']),
				'programa_nombre' => $row['programa_nombre'],
				'programa_descripcion' => $row['programa_descripcion'],
				'fecha_asignacion' => $row['fecha_asignacion']
			];
		}
		pg_free_result($result);
		
		return $programs;
	}

	/**
	 * Obtiene los cursos de un programa (estructura del programa, sin progreso individual)
	 */
	private function getCoursesByProgram($contactoId, $programaId) {
		$sql = "
			SELECT 
				c.id as curso_id,
				c.nombre as curso_nombre,
				c.descripcion as curso_descripcion,
				pc.consecutivo,
				np.id as nivel_programa_id,
				np.nombre as nivel_nombre,
				COALESCE(pc.nivel_id, 0) as nivel_id
			FROM programas_cursos pc
			INNER JOIN cursos c ON pc.curso_id = c.id
			LEFT JOIN niveles_programas np ON pc.nivel_id = np.id
			WHERE pc.programa_id = $1
			ORDER BY 
				COALESCE(pc.nivel_id, 999),
				pc.consecutivo
		";
		
		$result = pg_query_params($this->conn, $sql, [intval($programaId)]);
		if (!$result) {
			return [];
		}
		
		// Agrupar por nivel
		$byLevel = [];
		while ($row = pg_fetch_assoc($result)) {
			$nivelKey = $row['nivel_programa_id'] ? intval($row['nivel_programa_id']) : 'sin_nivel';
			
			if (!isset($byLevel[$nivelKey])) {
				$byLevel[$nivelKey] = [
					'nivel_id' => $row['nivel_programa_id'] ? intval($row['nivel_programa_id']) : null,
					'nivel_nombre' => $row['nivel_nombre'] ?: 'Sin nivel',
					'cursos' => []
				];
			}
			
			$byLevel[$nivelKey]['cursos'][] = [
				'curso_id' => intval($row['curso_id']),
				'curso_nombre' => $row['curso_nombre'],
				'curso_descripcion' => $row['curso_descripcion'],
				'consecutivo' => intval($row['consecutivo'])
			];
		}
		pg_free_result($result);
		
		return array_values($byLevel);
	}

	/**
	 * Obtiene los estudiantes que heredan los programas de este contacto
	 */
	private function getInheritedStudents($contactoId) {
		$sql = "
			SELECT 
				e.id_estudiante,
				e.nombre1,
				e.nombre2,
				e.apellido1,
				e.apellido2,
				e.doc_identidad,
				e.email,
				e.celular,
				(
					SELECT COUNT(*)
					FROM estudiantes_cursos ec
					WHERE ec.estudiante_id = e.id
				) as total_cursos,
				(
					SELECT COALESCE(AVG(ec.porcentaje), 0)
					FROM estudiantes_cursos ec
					WHERE ec.estudiante_id = e.id
				) as promedio_porcentaje,
				(
					SELECT MAX(ec.fecha)
					FROM estudiantes_cursos ec
					WHERE ec.estudiante_id = e.id
				) as ultima_actividad
			FROM estudiantes e
			WHERE e.id_contacto = $1
			ORDER BY e.nombre1, e.apellido1
		";
		
		$result = pg_query_params($this->conn, $sql, [$contactoId]);
		if (!$result) {
			return new WP_Error('db_query_failed', 'Error al obtener estudiantes heredados', ['status' => 500]);
		}
		
		$students = [];
		while ($row = pg_fetch_assoc($result)) {
			$nombreCompleto = trim(
				($row['nombre1'] ?: '') . ' ' .
				($row['nombre2'] ?: '') . ' ' .
				($row['apellido1'] ?: '') . ' ' .
				($row['apellido2'] ?: '')
			);
			
			$students[] = [
				'id_estudiante' => $row['id_estudiante'],
				'nombre_completo' => $nombreCompleto,
				'doc_identidad' => $row['doc_identidad'],
				'email' => $row['email'],
				'celular' => $row['celular'],
				'total_cursos' => intval($row['total_cursos']),
				'promedio_porcentaje' => round(floatval($row['promedio_porcentaje']), 1),
				'ultima_actividad' => $row['ultima_actividad']
			];
		}
		pg_free_result($result);
		
		return $students;
	}

	/**
	 * Obtiene estadísticas generales del contacto
	 */
	private function getOverallStats($contactoId) {
		// Contar programas
		$sqlProgramas = "SELECT COUNT(*) as total FROM programas_asignaciones WHERE contacto_id = $1";
		$resProg = pg_query_params($this->conn, $sqlProgramas, [$contactoId]);
		$totalProgramas = $resProg ? intval(pg_fetch_result($resProg, 0, 0)) : 0;
		if ($resProg) pg_free_result($resProg);
		
		// Contar estudiantes heredados
		$sqlEst = "SELECT COUNT(*) as total FROM estudiantes WHERE id_contacto = $1";
		$resEst = pg_query_params($this->conn, $sqlEst, [$contactoId]);
		$totalEstudiantes = $resEst ? intval(pg_fetch_result($resEst, 0, 0)) : 0;
		if ($resEst) pg_free_result($resEst);
		
		// Contar cursos en los programas
		$sqlCursos = "
			SELECT COUNT(DISTINCT pc.curso_id) as total
			FROM programas_asignaciones pa
			INNER JOIN programas_cursos pc ON pa.programa_id = pc.programa_id
			WHERE pa.contacto_id = $1
		";
		$resCursos = pg_query_params($this->conn, $sqlCursos, [$contactoId]);
		$totalCursosPrograma = $resCursos ? intval(pg_fetch_result($resCursos, 0, 0)) : 0;
		if ($resCursos) pg_free_result($resCursos);
		
		return [
			'total_programas' => $totalProgramas,
			'total_estudiantes' => $totalEstudiantes,
			'total_cursos_programa' => $totalCursosPrograma
		];
	}
}