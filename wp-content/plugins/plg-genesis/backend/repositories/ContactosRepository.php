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
				'id'       => intval($row['id']),
				'nombre'   => $row['nombre'],
				'iglesia'  => $row['iglesia'],
				'email'    => $row['email'],
				'celular'  => $row['celular'],
				'direccion'=> $row['direccion'],
				'ciudad'   => $row['ciudad'],
				'code'     => $row['code'],
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
			'nombre'   => $row['nombre'],
			'iglesia'  => $row['iglesia'],
			'email'    => $row['email'],
			'celular'  => $row['celular'],
			'direccion'=> $row['direccion'],
			'ciudad'   => $row['ciudad'],
			'code'     => $row['code'],
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
}