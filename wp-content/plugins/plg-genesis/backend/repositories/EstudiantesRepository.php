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

	public function countEstudiantes() {
		$res = pg_query_params($this->conn, 'SELECT COUNT(*) AS total_estudiantes FROM estudiantes', []);
		if (!$res) { return new WP_Error('db_query_failed', 'Error contando estudiantes', [ 'status' => 500 ]); }
		$total = intval(pg_fetch_result($res, 0, 0));
		pg_free_result($res);
		return $total;
	}

	public function getContactCode($contactId) {
		$res = pg_query_params($this->conn, 'SELECT TRIM(code) as code FROM contactos WHERE id = $1', [ intval($contactId) ]);
		if (!$res) { return new WP_Error('db_query_failed', 'Error obteniendo código del contacto', [ 'status' => 500 ]); }
		$row = pg_fetch_assoc($res);
		pg_free_result($res);
		if (!$row) { return new WP_Error('not_found', 'Contacto no encontrado', [ 'status' => 404 ]); }
		return trim($row['code'] ?? '');
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

    public function existsByDocumento($docIdentidad) {
        $res = pg_query_params($this->conn, 'SELECT COUNT(*) FROM estudiantes WHERE doc_identidad = $1', [ strval($docIdentidad) ]);
        if (!$res) { return new WP_Error('db_query_failed', 'Error verificando documento', [ 'status' => 500 ]); }
        $count = intval(pg_fetch_result($res, 0, 0));
        pg_free_result($res);
        return $count > 0;
    }
}