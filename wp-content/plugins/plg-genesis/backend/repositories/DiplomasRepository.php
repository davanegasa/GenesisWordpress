<?php
if (!defined('ABSPATH')) { exit; }

class PlgGenesis_DiplomasRepository {
	private $conn;

	public function __construct($connection) {
		$this->conn = $connection;
	}

	private function logPg($context, $sql = null) {
		$pgErr = function_exists('pg_last_error') ? pg_last_error($this->conn) : 'unknown';
		$prefix = '[PlgGenesis][DiplomasRepository] ' . $context . ' -> ';
		if ($sql) {
			error_log($prefix . $sql . ' | err=' . $pgErr);
		} else {
			error_log($prefix . $pgErr);
		}
	}

	/**
	 * Obtiene los IDs de estudiantes asociados a un contacto
	 */
	private function getEstudiantesFromContacto($contactoId) {
		$sql = "SELECT id FROM estudiantes WHERE id_contacto = $1";
		$res = pg_query_params($this->conn, $sql, [ intval($contactoId) ]);
		if (!$res) {
			$this->logPg('getEstudiantesFromContacto', $sql);
			return [];
		}
		$ids = [];
		while ($row = pg_fetch_assoc($res)) {
			$ids[] = intval($row['id']);
		}
		pg_free_result($res);
		return $ids;
	}

	/**
	 * Calcula si un estudiante es elegible para un diploma de nivel
	 * Requiere que todos los cursos del nivel tengan porcentaje >= 70
	 */
	public function isElegibleNivel($nivelId, $estudianteId) {
		if (!$estudianteId) {
			return false;
		}

		// Obtener todos los cursos del nivel
		$sqlCursos = "SELECT pc.curso_id 
					  FROM programas_cursos pc 
					  WHERE pc.nivel_id = $1";
		$resCursos = pg_query_params($this->conn, $sqlCursos, [ intval($nivelId) ]);
		if (!$resCursos) {
			$this->logPg('isElegibleNivel.getCursos', $sqlCursos);
			return false;
		}

		$cursos = [];
		while ($row = pg_fetch_assoc($resCursos)) {
			$cursos[] = intval($row['curso_id']);
		}
		pg_free_result($resCursos);

		if (empty($cursos)) {
			return false; // No hay cursos en el nivel
		}

		// Verificar que todos los cursos estén completados con >= 70%
		$placeholders = implode(',', array_map(function($i) { return '$' . ($i + 2); }, array_keys($cursos)));

		$sqlCheck = "SELECT curso_id, MAX(porcentaje) as max_porcentaje
					 FROM estudiantes_cursos 
					 WHERE estudiante_id = $1
					 AND curso_id IN ($placeholders)
					 GROUP BY curso_id";
		$params = array_merge([ intval($estudianteId) ], $cursos);
		$resCheck = pg_query_params($this->conn, $sqlCheck, $params);
		if (!$resCheck) {
			$this->logPg('isElegibleNivel.checkCursos', $sqlCheck);
			return false;
		}

		$completados = [];
		while ($row = pg_fetch_assoc($resCheck)) {
			if (floatval($row['max_porcentaje']) >= 70) {
				$completados[] = intval($row['curso_id']);
			}
		}
		pg_free_result($resCheck);

		// Todos los cursos del nivel deben estar completados
		return count($completados) === count($cursos);
	}

	/**
	 * Calcula si un estudiante es elegible para un diploma de programa completo
	 * Requiere que todos los cursos del programa tengan porcentaje >= 70
	 */
	public function isElegiblePrograma($programaId, $version, $estudianteId) {
		if (!$estudianteId) {
			return false;
		}

		// Obtener todos los cursos del programa (versión específica)
		$sqlCursos = "SELECT curso_id 
					  FROM programas_cursos 
					  WHERE programa_id = $1 AND version = $2";
		$resCursos = pg_query_params($this->conn, $sqlCursos, [ intval($programaId), intval($version) ]);
		if (!$resCursos) {
			$this->logPg('isElegiblePrograma.getCursos', $sqlCursos);
			return false;
		}

		$cursos = [];
		while ($row = pg_fetch_assoc($resCursos)) {
			$cursos[] = intval($row['curso_id']);
		}
		pg_free_result($resCursos);

		if (empty($cursos)) {
			return false; // No hay cursos en el programa
		}

		// Verificar que todos los cursos estén completados con >= 70%
		$placeholders = implode(',', array_map(function($i) { return '$' . ($i + 2); }, array_keys($cursos)));

		$sqlCheck = "SELECT curso_id, MAX(porcentaje) as max_porcentaje
					 FROM estudiantes_cursos 
					 WHERE estudiante_id = $1
					 AND curso_id IN ($placeholders)
					 GROUP BY curso_id";
		$params = array_merge([ intval($estudianteId) ], $cursos);
		$resCheck = pg_query_params($this->conn, $sqlCheck, $params);
		if (!$resCheck) {
			$this->logPg('isElegiblePrograma.checkCursos', $sqlCheck);
			return false;
		}

		$completados = [];
		while ($row = pg_fetch_assoc($resCheck)) {
			if (floatval($row['max_porcentaje']) >= 70) {
				$completados[] = intval($row['curso_id']);
			}
		}
		pg_free_result($resCheck);

		// Todos los cursos del programa deben estar completados
		return count($completados) === count($cursos);
	}

	/**
	 * Obtiene diplomas elegibles (no emitidos aún) para un estudiante o todos los estudiantes de un contacto
	 */
	public function getElegibles($estudianteId = null, $contactoId = null) {
		if (($estudianteId && $contactoId) || (!$estudianteId && !$contactoId)) {
			return new WP_Error('invalid_params', 'Debe proporcionar estudianteId o contactoId', [ 'status' => 422 ]);
		}

		// Si es contacto, obtener sus estudiantes
		$estudiantes = [];
		if ($contactoId) {
			$sqlEst = "SELECT id, id_estudiante, nombre1, apellido1 FROM estudiantes WHERE id_contacto = $1";
			$resEst = pg_query_params($this->conn, $sqlEst, [ intval($contactoId) ]);
			if (!$resEst) {
				$this->logPg('getElegibles.estudiantes', $sqlEst);
				return new WP_Error('db_query_failed', 'Error obteniendo estudiantes', [ 'status' => 500 ]);
			}
			while ($row = pg_fetch_assoc($resEst)) {
				$estudiantes[] = [
					'id' => intval($row['id']),
					'codigo' => $row['id_estudiante'],
					'nombre' => trim(($row['nombre1'] ?? '') . ' ' . ($row['apellido1'] ?? ''))
				];
			}
			pg_free_result($resEst);
		} else {
			// Un solo estudiante
			$sqlEst = "SELECT id, id_estudiante, nombre1, apellido1 FROM estudiantes WHERE id = $1";
			$resEst = pg_query_params($this->conn, $sqlEst, [ intval($estudianteId) ]);
			if ($resEst && pg_num_rows($resEst) > 0) {
				$row = pg_fetch_assoc($resEst);
				$estudiantes[] = [
					'id' => intval($row['id']),
					'codigo' => $row['id_estudiante'],
					'nombre' => trim(($row['nombre1'] ?? '') . ' ' . ($row['apellido1'] ?? ''))
				];
			}
			if ($resEst) pg_free_result($resEst);
		}

		if (empty($estudiantes)) {
			return []; // Sin estudiantes, sin diplomas
		}

		$elegibles = [];

		// Iterar por cada estudiante
		foreach ($estudiantes as $estudiante) {
			$estId = $estudiante['id'];

			// Obtener programas asignados al contacto o estudiante
			$idField = $contactoId ? 'contacto_id' : 'estudiante_id';
			$idValue = $contactoId ? intval($contactoId) : $estId;

			$sqlAsig = "SELECT programa_id, version 
						FROM programas_asignaciones 
						WHERE $idField = $1";
			$resAsig = pg_query_params($this->conn, $sqlAsig, [ $idValue ]);
			if (!$resAsig) continue;

			while ($asig = pg_fetch_assoc($resAsig)) {
				$programaId = intval($asig['programa_id']);
				$version = intval($asig['version']);

				// Obtener nombre del programa
				$sqlPrograma = "SELECT nombre, descripcion FROM programas WHERE id = $1";
				$resPrograma = pg_query_params($this->conn, $sqlPrograma, [ $programaId ]);
				$programa = $resPrograma ? pg_fetch_assoc($resPrograma) : null;
				if ($resPrograma) pg_free_result($resPrograma);
				
				if (!$programa) continue;

				// Verificar diploma de programa completo para este estudiante
				if ($this->isElegiblePrograma($programaId, $version, $estId)) {
					// Verificar que no esté ya emitido para este estudiante
					$sqlCheck = "SELECT id FROM diplomas_entregados 
								 WHERE tipo = 'programa_completo' 
								 AND programa_id = $1 
								 AND estudiante_id = $2";
					$resCheck = pg_query_params($this->conn, $sqlCheck, [ $programaId, $estId ]);
					if ($resCheck && pg_num_rows($resCheck) === 0) {
						$elegibles[] = [
							'tipo' => 'programa_completo',
							'programa_id' => $programaId,
							'programa_nombre' => $programa['nombre'],
							'programa_descripcion' => $programa['descripcion'],
							'nivel_id' => null,
							'nivel_nombre' => null,
							'version_programa' => $version,
							'estudiante_id' => $estId,
							'estudiante_codigo' => $estudiante['codigo'],
							'estudiante_nombre' => $estudiante['nombre']
						];
					}
					if ($resCheck) pg_free_result($resCheck);
				}

				// Verificar diplomas por nivel para este estudiante
				$sqlNiveles = "SELECT np.id as nivel_id, np.nombre as nivel_nombre
							   FROM niveles_programas np
							   WHERE np.programa_id = $1 
							   AND np.version = $2";
				$resNiveles = pg_query_params($this->conn, $sqlNiveles, [ $programaId, $version ]);
				if ($resNiveles) {
					while ($nivel = pg_fetch_assoc($resNiveles)) {
						$nivelId = intval($nivel['nivel_id']);
						if ($this->isElegibleNivel($nivelId, $estId)) {
							// Verificar que no esté ya emitido para este estudiante
							$sqlCheck = "SELECT id FROM diplomas_entregados 
										 WHERE tipo = 'nivel' 
										 AND programa_id = $1 
										 AND nivel_id = $2
										 AND estudiante_id = $3";
							$resCheck = pg_query_params($this->conn, $sqlCheck, [ $programaId, $nivelId, $estId ]);
							if ($resCheck && pg_num_rows($resCheck) === 0) {
								$elegibles[] = [
									'tipo' => 'nivel',
									'programa_id' => $programaId,
									'programa_nombre' => $programa['nombre'],
									'programa_descripcion' => $programa['descripcion'],
									'nivel_id' => $nivelId,
									'nivel_nombre' => $nivel['nivel_nombre'],
									'version_programa' => $version,
									'estudiante_id' => $estId,
									'estudiante_codigo' => $estudiante['codigo'],
									'estudiante_nombre' => $estudiante['nombre']
								];
							}
							if ($resCheck) pg_free_result($resCheck);
						}
					}
					pg_free_result($resNiveles);
				}
			}
			pg_free_result($resAsig);
		}

		return $elegibles;
	}

	/**
	 * Emite un diploma (lo registra en la tabla) para un estudiante específico
	 */
	public function emitir($tipo, $programaId, $version, $estudianteId, $nivelId = null, $notas = null) {
		if (!$estudianteId) {
			return new WP_Error('invalid_params', 'Debe proporcionar estudianteId', [ 'status' => 422 ]);
		}

		if (!in_array($tipo, ['programa_completo', 'nivel'])) {
			return new WP_Error('invalid_tipo', 'Tipo debe ser programa_completo o nivel', [ 'status' => 422 ]);
		}

		if ($tipo === 'nivel' && !$nivelId) {
			return new WP_Error('invalid_params', 'nivel_id requerido para tipo=nivel', [ 'status' => 422 ]);
		}

		// Verificar elegibilidad
		$elegible = false;
		if ($tipo === 'programa_completo') {
			$elegible = $this->isElegiblePrograma($programaId, $version, $estudianteId);
		} else {
			$elegible = $this->isElegibleNivel($nivelId, $estudianteId);
		}

		if (!$elegible) {
			return new WP_Error('not_elegible', 'El estudiante no es elegible para este diploma', [ 'status' => 422 ]);
		}

		// Insertar diploma (solo estudiante_id, no necesitamos contacto_id)
		$sql = "INSERT INTO diplomas_entregados 
				(tipo, programa_id, nivel_id, version_programa, estudiante_id, notas) 
				VALUES ($1, $2, $3, $4, $5, $6) 
				RETURNING id";
		$params = [
			$tipo,
			intval($programaId),
			$nivelId ? intval($nivelId) : null,
			intval($version),
			intval($estudianteId),
			$notas
		];
		$res = pg_query_params($this->conn, $sql, $params);
		if (!$res) {
			$this->logPg('emitir.insert', $sql);
			return new WP_Error('db_insert_failed', 'Error emitiendo diploma', [ 'status' => 500 ]);
		}

		$id = intval(pg_fetch_result($res, 0, 0));
		pg_free_result($res);

		return $id;
	}

	/**
	 * Crea un acta de diplomas
	 * @return int|WP_Error ID del acta creada o error
	 */
	private function crearActa($contactoId = null, $tipoActa = 'cierre', $observaciones = null) {
		// Generar número de acta automático
		$sqlNumero = "SELECT generar_numero_acta()";
		$resNumero = pg_query($this->conn, $sqlNumero);
		if (!$resNumero) {
			$this->logPg('crearActa.generar_numero', $sqlNumero);
			return new WP_Error('db_error', 'Error generando número de acta', [ 'status' => 500 ]);
		}
		$numeroActa = pg_fetch_result($resNumero, 0, 0);
		pg_free_result($resNumero);

		// Obtener usuario actual
		$currentUser = wp_get_current_user();
		$createdBy = $currentUser->ID > 0 ? $currentUser->ID : null;

		// Insertar acta
		$sql = "INSERT INTO actas_diplomas 
				(numero_acta, contacto_id, tipo_acta, observaciones, created_by) 
				VALUES ($1, $2, $3, $4, $5) 
				RETURNING id";
		$params = [
			$numeroActa,
			$contactoId ? intval($contactoId) : null,
			$tipoActa,
			$observaciones,
			$createdBy
		];
		$res = pg_query_params($this->conn, $sql, $params);
		if (!$res) {
			$this->logPg('crearActa.insert', $sql);
			return new WP_Error('db_error', 'Error creando acta', [ 'status' => 500 ]);
		}

		$actaId = intval(pg_fetch_result($res, 0, 0));
		pg_free_result($res);

		return $actaId;
	}

	/**
	 * Emite múltiples diplomas en una sola transacción (batch)
	 * Crea un acta automáticamente y vincula todos los diplomas
	 * @param array $diplomas Array de [ ['tipo', 'programaId', 'version', 'estudianteId', 'nivelId', 'notas'], ... ]
	 * @param int $contactoId ID del contacto (opcional, para vincular el acta)
	 * @param string $observaciones Observaciones del acta (opcional)
	 * @return array [ 'actaId', 'numeroActa', 'exitosos' => [...ids], 'errores' => [...] ]
	 */
	public function emitirBatch($diplomas, $contactoId = null, $observaciones = null) {
		if (empty($diplomas) || !is_array($diplomas)) {
			return new WP_Error('invalid_params', 'Se requiere un array de diplomas', [ 'status' => 422 ]);
		}

		$exitosos = [];
		$errores = [];

		// Iniciar transacción
		pg_query($this->conn, 'BEGIN');

		// Crear acta primero
		$actaId = $this->crearActa($contactoId, 'cierre', $observaciones);
		if (is_wp_error($actaId)) {
			pg_query($this->conn, 'ROLLBACK');
			return $actaId;
		}

		// Obtener número de acta para retornar
		$sqlNumero = "SELECT numero_acta FROM actas_diplomas WHERE id = $1";
		$resNumero = pg_query_params($this->conn, $sqlNumero, [ $actaId ]);
		$numeroActa = pg_fetch_result($resNumero, 0, 0);
		pg_free_result($resNumero);

		foreach ($diplomas as $idx => $diploma) {
			$tipo = $diploma['tipo'] ?? null;
			$programaId = $diploma['programaId'] ?? null;
			$version = $diploma['version'] ?? null;
			$estudianteId = $diploma['estudianteId'] ?? null;
			$nivelId = $diploma['nivelId'] ?? null;
			$notas = $diploma['notas'] ?? null;

			// Validaciones
			if (!$tipo || !$programaId || !$version || !$estudianteId) {
				$errores[] = [
					'index' => $idx,
					'diploma' => $diploma,
					'error' => 'Campos requeridos: tipo, programaId, version, estudianteId'
				];
				continue;
			}

			if (!in_array($tipo, ['programa_completo', 'nivel'])) {
				$errores[] = [
					'index' => $idx,
					'diploma' => $diploma,
					'error' => 'Tipo debe ser programa_completo o nivel'
				];
				continue;
			}

			if ($tipo === 'nivel' && !$nivelId) {
				$errores[] = [
					'index' => $idx,
					'diploma' => $diploma,
					'error' => 'nivel_id requerido para tipo=nivel'
				];
				continue;
			}

			// Verificar elegibilidad
			$elegible = false;
			if ($tipo === 'programa_completo') {
				$elegible = $this->isElegiblePrograma($programaId, $version, $estudianteId);
			} else {
				$elegible = $this->isElegibleNivel($nivelId, $estudianteId);
			}

			if (!$elegible) {
				$errores[] = [
					'index' => $idx,
					'diploma' => $diploma,
					'error' => 'Estudiante no es elegible para este diploma'
				];
				continue;
			}

			// Insertar diploma vinculado al acta
			$sql = "INSERT INTO diplomas_entregados 
					(tipo, programa_id, nivel_id, version_programa, estudiante_id, acta_id, notas) 
					VALUES ($1, $2, $3, $4, $5, $6, $7) 
					RETURNING id";
			$params = [
				$tipo,
				intval($programaId),
				$nivelId ? intval($nivelId) : null,
				intval($version),
				intval($estudianteId),
				$actaId,
				$notas
			];
			$res = pg_query_params($this->conn, $sql, $params);
			if (!$res) {
				$errores[] = [
					'index' => $idx,
					'diploma' => $diploma,
					'error' => 'Error insertando en base de datos: ' . pg_last_error($this->conn)
				];
				continue;
			}

			$id = intval(pg_fetch_result($res, 0, 0));
			pg_free_result($res);
			$exitosos[] = [
				'index' => $idx,
				'diplomaId' => $id,
				'estudiante_id' => $estudianteId
			];
		}

		// Si hubo errores, hacer rollback; si no, commit
		if (!empty($errores) && empty($exitosos)) {
			pg_query($this->conn, 'ROLLBACK');
			return new WP_Error('batch_failed', 'Ningún diploma pudo ser emitido', [ 
				'status' => 422,
				'errores' => $errores 
			]);
		}

		// Actualizar contador de diplomas en el acta
		$totalExitosos = count($exitosos);
		$sqlUpdate = "UPDATE actas_diplomas SET total_diplomas = $1, updated_at = NOW() WHERE id = $2";
		pg_query_params($this->conn, $sqlUpdate, [ $totalExitosos, $actaId ]);

		pg_query($this->conn, 'COMMIT');

		return [
			'acta_id' => $actaId,
			'numero_acta' => $numeroActa,
			'exitosos' => $exitosos,
			'total_exitosos' => $totalExitosos,
			'errores' => $errores,
			'total_errores' => count($errores)
		];
	}

	/**
	 * Obtiene una acta por ID con sus diplomas
	 */
	public function getActaById($actaId) {
		$sql = "SELECT a.*, 
				       c.nombre as contacto_nombre,
				       c.email as contacto_email
				FROM actas_diplomas a
				LEFT JOIN contactos c ON a.contacto_id = c.id
				WHERE a.id = $1";
		$res = pg_query_params($this->conn, $sql, [ intval($actaId) ]);
		if (!$res) {
			$this->logPg('getActaById', $sql);
			return new WP_Error('db_error', 'Error obteniendo acta', [ 'status' => 500 ]);
		}

		if (pg_num_rows($res) === 0) {
			pg_free_result($res);
			return new WP_Error('not_found', 'Acta no encontrada', [ 'status' => 404 ]);
		}

		$acta = pg_fetch_assoc($res);
		pg_free_result($res);

		// Obtener diplomas del acta
		$sqlDiplomas = "SELECT d.*, 
						       p.nombre as programa_nombre,
						       n.nombre as nivel_nombre,
						       e.id_estudiante as estudiante_codigo,
						       TRIM(COALESCE(e.nombre1, '') || ' ' || COALESCE(e.apellido1, '')) as estudiante_nombre
						FROM diplomas_entregados d
						JOIN programas p ON d.programa_id = p.id
						LEFT JOIN niveles_programas n ON d.nivel_id = n.id
						LEFT JOIN estudiantes e ON d.estudiante_id = e.id
						WHERE d.acta_id = $1
						ORDER BY d.created_at";
		$resDiplomas = pg_query_params($this->conn, $sqlDiplomas, [ intval($actaId) ]);
		$diplomas = [];
		if ($resDiplomas) {
			while ($row = pg_fetch_assoc($resDiplomas)) {
				$diplomas[] = [
					'id' => intval($row['id']),
					'tipo' => $row['tipo'],
					'programa_nombre' => $row['programa_nombre'],
					'nivel_nombre' => $row['nivel_nombre'],
					'estudiante_codigo' => $row['estudiante_codigo'],
					'estudiante_nombre' => $row['estudiante_nombre'],
					'fecha_emision' => $row['fecha_emision'],
					'fecha_entrega' => $row['fecha_entrega'],
					'notas' => $row['notas']
				];
			}
			pg_free_result($resDiplomas);
		}

		return [
			'id' => intval($acta['id']),
			'numero_acta' => $acta['numero_acta'],
			'fecha_acta' => $acta['fecha_acta'],
			'contacto_id' => $acta['contacto_id'] ? intval($acta['contacto_id']) : null,
			'contacto_nombre' => $acta['contacto_nombre'],
			'contacto_email' => $acta['contacto_email'],
			'tipo_acta' => $acta['tipo_acta'],
			'total_diplomas' => intval($acta['total_diplomas']),
			'observaciones' => $acta['observaciones'],
			'estado' => $acta['estado'],
			'created_by' => $acta['created_by'] ? intval($acta['created_by']) : null,
			'created_at' => $acta['created_at'],
			'diplomas' => $diplomas
		];
	}

	/**
	 * Lista actas con filtros opcionales
	 */
	public function listActas($contactoId = null, $estado = 'activa', $limit = 50, $offset = 0) {
		$where = [];
		$params = [];
		$paramCount = 0;

		if ($contactoId) {
			$paramCount++;
			$where[] = "a.contacto_id = $" . $paramCount;
			$params[] = intval($contactoId);
		}

		if ($estado) {
			$paramCount++;
			$where[] = "a.estado = $" . $paramCount;
			$params[] = $estado;
		}

		$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

		$sql = "SELECT a.*, 
				       c.nombre as contacto_nombre
				FROM actas_diplomas a
				LEFT JOIN contactos c ON a.contacto_id = c.id
				$whereClause
				ORDER BY a.fecha_acta DESC, a.created_at DESC
				LIMIT $limit OFFSET $offset";

		$res = pg_query_params($this->conn, $sql, $params);
		if (!$res) {
			$this->logPg('listActas', $sql);
			return new WP_Error('db_error', 'Error listando actas', [ 'status' => 500 ]);
		}

		$actas = [];
		while ($row = pg_fetch_assoc($res)) {
			$actas[] = [
				'id' => intval($row['id']),
				'numero_acta' => $row['numero_acta'],
				'fecha_acta' => $row['fecha_acta'],
				'contacto_id' => $row['contacto_id'] ? intval($row['contacto_id']) : null,
				'contacto_nombre' => $row['contacto_nombre'],
				'tipo_acta' => $row['tipo_acta'],
				'total_diplomas' => intval($row['total_diplomas']),
				'estado' => $row['estado'],
				'created_at' => $row['created_at']
			];
		}
		pg_free_result($res);

		return $actas;
	}

	/**
	 * Obtiene lista de programas que tienen estudiantes próximos a completar
	 * Retorna solo los programas con contador de estudiantes (ligero y rápido)
	 * OPTIMIZADO: Usa queries simples y procesa en PHP
	 * 
	 * @param int $contactoId ID del contacto (OBLIGATORIO)
	 * @param int $umbral Porcentaje mínimo de progreso (default 80)
	 * @return array Lista de programas con contador de estudiantes próximos
	 */
	public function getProgramasConProximos($contactoId, $umbral = 80) {
		if (!$contactoId) {
			return new WP_Error('invalid_params', 'El parámetro contactoId es obligatorio', [ 'status' => 422 ]);
		}

		$contactoIdInt = intval($contactoId);
		$umbralInt = intval($umbral);

		// QUERY 1: Estudiantes del contacto con sus programas asignados
		$sqlEstudiantes = "
			SELECT 
				e.id as estudiante_id,
				pa.programa_id,
				p.nombre as programa_nombre,
				pa.version
			FROM estudiantes e
			JOIN programas_asignaciones pa ON pa.contacto_id = e.id_contacto
			JOIN programas p ON p.id = pa.programa_id
			WHERE e.id_contacto = $1
		";

		$resEstudiantes = pg_query_params($this->conn, $sqlEstudiantes, [ $contactoIdInt ]);
		if (!$resEstudiantes) {
			$this->logPg('getProgramasConProximos:estudiantes', $sqlEstudiantes);
			return new WP_Error('db_query_failed', 'Error obteniendo estudiantes', [ 'status' => 500 ]);
		}

		$estudiantesPrograms = [];
		$programasInfo = [];
		while ($row = pg_fetch_assoc($resEstudiantes)) {
			$estudianteId = intval($row['estudiante_id']);
			$programaId = intval($row['programa_id']);
			$version = intval($row['version']);

			$estudiantesPrograms[] = [
				'estudiante_id' => $estudianteId,
				'programa_id' => $programaId,
				'version' => $version
			];

			if (!isset($programasInfo[$programaId])) {
				$programasInfo[$programaId] = $row['programa_nombre'];
			}
		}
		pg_free_result($resEstudiantes);

		if (empty($estudiantesPrograms)) {
			return [];
		}

		// QUERY 2: Cursos requeridos por programa/nivel (estructura del programa)
		$programaIds = array_unique(array_column($estudiantesPrograms, 'programa_id'));
		$placeholders = implode(',', array_map(function($i) { return '$' . ($i + 1); }, array_keys($programaIds)));
		
		$sqlCursosRequeridos = "
			SELECT 
				pc.programa_id,
				pc.version,
				pc.nivel_id,
				np.nombre as nivel_nombre,
				pc.curso_id,
				c.nombre as curso_nombre
			FROM programas_cursos pc
			JOIN cursos c ON c.id = pc.curso_id
			LEFT JOIN niveles_programas np ON np.id = pc.nivel_id
			WHERE pc.programa_id IN ($placeholders)
			ORDER BY pc.programa_id, pc.nivel_id, pc.curso_id
		";

		$resCursos = pg_query_params($this->conn, $sqlCursosRequeridos, array_values($programaIds));
		if (!$resCursos) {
			$this->logPg('getProgramasConProximos:cursos', $sqlCursosRequeridos);
			return new WP_Error('db_query_failed', 'Error obteniendo cursos', [ 'status' => 500 ]);
		}

		$cursosRequeridos = [];
		while ($row = pg_fetch_assoc($resCursos)) {
			$programaId = intval($row['programa_id']);
			$version = intval($row['version']);
			$nivelId = $row['nivel_id'] ? intval($row['nivel_id']) : null;
			$cursoId = intval($row['curso_id']);

			$key = "{$programaId}_{$version}";
			if (!isset($cursosRequeridos[$key])) {
				$cursosRequeridos[$key] = [
					'niveles' => [],
					'programa_completo' => []
				];
			}

			$cursosRequeridos[$key]['programa_completo'][] = $cursoId;

			if ($nivelId) {
				if (!isset($cursosRequeridos[$key]['niveles'][$nivelId])) {
					$cursosRequeridos[$key]['niveles'][$nivelId] = [
						'nombre' => $row['nivel_nombre'],
						'cursos' => []
					];
				}
				$cursosRequeridos[$key]['niveles'][$nivelId]['cursos'][] = $cursoId;
			}
		}
		pg_free_result($resCursos);

		// QUERY 3: Progreso de estudiantes (qué cursos han completado)
		$estudianteIds = array_unique(array_column($estudiantesPrograms, 'estudiante_id'));
		$placeholders = implode(',', array_map(function($i) { return '$' . ($i + 1); }, array_keys($estudianteIds)));

		$sqlProgreso = "
			SELECT 
				ec.estudiante_id,
				ec.curso_id,
				MAX(ec.porcentaje) as porcentaje
			FROM estudiantes_cursos ec
			WHERE ec.estudiante_id IN ($placeholders)
			GROUP BY ec.estudiante_id, ec.curso_id
		";

		$resProgreso = pg_query_params($this->conn, $sqlProgreso, array_values($estudianteIds));
		if (!$resProgreso) {
			$this->logPg('getProgramasConProximos:progreso', $sqlProgreso);
			return new WP_Error('db_query_failed', 'Error obteniendo progreso', [ 'status' => 500 ]);
		}

		$progresoEstudiantes = [];
		while ($row = pg_fetch_assoc($resProgreso)) {
			$estudianteId = intval($row['estudiante_id']);
			$cursoId = intval($row['curso_id']);
			$porcentaje = floatval($row['porcentaje']);

			if (!isset($progresoEstudiantes[$estudianteId])) {
				$progresoEstudiantes[$estudianteId] = [];
			}
			$progresoEstudiantes[$estudianteId][$cursoId] = $porcentaje;
		}
		pg_free_result($resProgreso);

		// QUERY 4: Diplomas ya emitidos (para excluirlos)
		$sqlDiplomas = "
			SELECT 
				estudiante_id,
				programa_id,
				nivel_id,
				tipo
			FROM diplomas_entregados
			WHERE estudiante_id IN ($placeholders)
		";

		$resDiplomas = pg_query_params($this->conn, $sqlDiplomas, array_values($estudianteIds));
		if (!$resDiplomas) {
			$this->logPg('getProgramasConProximos:diplomas', $sqlDiplomas);
			return new WP_Error('db_query_failed', 'Error obteniendo diplomas', [ 'status' => 500 ]);
		}

		$diplomasEmitidos = [];
		while ($row = pg_fetch_assoc($resDiplomas)) {
			$estudianteId = intval($row['estudiante_id']);
			$programaId = intval($row['programa_id']);
			$nivelId = $row['nivel_id'] ? intval($row['nivel_id']) : null;
			$tipo = $row['tipo'];

			$key = $tipo === 'nivel' 
				? "{$estudianteId}_{$programaId}_nivel_{$nivelId}"
				: "{$estudianteId}_{$programaId}_programa";

			$diplomasEmitidos[$key] = true;
		}
		pg_free_result($resDiplomas);

		// PROCESAMIENTO EN PHP: Calcular porcentajes y filtrar
		$programasConProximos = [];

		foreach ($estudiantesPrograms as $ep) {
			$estudianteId = $ep['estudiante_id'];
			$programaId = $ep['programa_id'];
			$version = $ep['version'];
			$key = "{$programaId}_{$version}";

			if (!isset($cursosRequeridos[$key])) {
				continue;
			}

			$progreso = $progresoEstudiantes[$estudianteId] ?? [];
			$estructura = $cursosRequeridos[$key];

			// Calcular progreso por nivel
			foreach ($estructura['niveles'] as $nivelId => $nivelInfo) {
				$diplomaKey = "{$estudianteId}_{$programaId}_nivel_{$nivelId}";
				if (isset($diplomasEmitidos[$diplomaKey])) {
					continue; // Ya tiene diploma de este nivel
				}

				$cursosRequeridos = $nivelInfo['cursos'];
				$cursosCompletados = 0;

				foreach ($cursosRequeridos as $cursoId) {
					if (isset($progreso[$cursoId]) && $progreso[$cursoId] >= 70) {
						$cursosCompletados++;
					}
				}

				$porcentaje = count($cursosRequeridos) > 0 
					? round(100.0 * $cursosCompletados / count($cursosRequeridos), 1)
					: 0;

				if ($porcentaje >= $umbralInt) {
					if (!isset($programasConProximos[$programaId])) {
						$programasConProximos[$programaId] = [];
					}
					$programasConProximos[$programaId][$estudianteId] = true;
				}
			}

			// Calcular progreso por programa completo
			$diplomaKey = "{$estudianteId}_{$programaId}_programa";
			if (!isset($diplomasEmitidos[$diplomaKey])) {
				$cursosRequeridos = $estructura['programa_completo'];
				$cursosCompletados = 0;

				foreach ($cursosRequeridos as $cursoId) {
					if (isset($progreso[$cursoId]) && $progreso[$cursoId] >= 70) {
						$cursosCompletados++;
					}
				}

				$porcentaje = count($cursosRequeridos) > 0 
					? round(100.0 * $cursosCompletados / count($cursosRequeridos), 1)
					: 0;

				if ($porcentaje >= $umbralInt) {
					if (!isset($programasConProximos[$programaId])) {
						$programasConProximos[$programaId] = [];
					}
					$programasConProximos[$programaId][$estudianteId] = true;
				}
			}
		}

		// Formatear resultado
		$resultado = [];
		foreach ($programasConProximos as $programaId => $estudiantes) {
			$resultado[] = [
				'programa_id' => $programaId,
				'programa_nombre' => $programasInfo[$programaId],
				'total_estudiantes' => count($estudiantes)
			];
		}

		// Ordenar por nombre de programa
		usort($resultado, function($a, $b) {
			return strcmp($a['programa_nombre'], $b['programa_nombre']);
		});

		return $resultado;
	}

	/**
	 * Obtiene estudiantes próximos a completar (progreso >= 80%)
	 * Retorna información detallada de niveles y programas que están por completar
	 * OPTIMIZADO: Usa queries simples y procesa en PHP
	 * IMPORTANTE: contactoId es OBLIGATORIO para evitar queries muy pesadas en producción
	 * 
	 * @param int $limite Límite de resultados
	 * @param int $umbral Porcentaje mínimo de progreso (default 80)
	 * @param int $contactoId ID del contacto (OBLIGATORIO)
	 * @param int|null $programaId ID del programa (opcional, para filtrar por programa específico)
	 */
	public function getProximosACompletar($limite = 50, $umbral = 80, $contactoId = null, $programaId = null) {
		if (!$contactoId) {
			return new WP_Error('invalid_params', 'El parámetro contactoId es obligatorio', [ 'status' => 422 ]);
		}

		$contactoIdInt = intval($contactoId);
		$umbralInt = intval($umbral);
		$limiteInt = intval($limite);

		// QUERY 1: Estudiantes del contacto con sus programas asignados
		$sqlEstudiantes = "
			SELECT 
				e.id as estudiante_id,
				e.id_estudiante as estudiante_codigo,
				TRIM(COALESCE(e.nombre1, '') || ' ' || COALESCE(e.apellido1, '')) as estudiante_nombre,
				c.id as contacto_id,
				c.nombre as contacto_nombre,
				pa.programa_id,
				p.nombre as programa_nombre,
				pa.version
			FROM estudiantes e
			JOIN contactos c ON c.id = e.id_contacto
			JOIN programas_asignaciones pa ON pa.contacto_id = e.id_contacto
			JOIN programas p ON p.id = pa.programa_id
			WHERE e.id_contacto = $1" . ($programaId ? " AND pa.programa_id = $2" : "") . "
		";

		$params = $programaId ? [ $contactoIdInt, intval($programaId) ] : [ $contactoIdInt ];
		$resEstudiantes = pg_query_params($this->conn, $sqlEstudiantes, $params);
		
		if (!$resEstudiantes) {
			$this->logPg('getProximosACompletar:estudiantes', $sqlEstudiantes);
			return new WP_Error('db_query_failed', 'Error obteniendo estudiantes', [ 'status' => 500 ]);
		}

		$estudiantesInfo = [];
		$estudiantesPrograms = [];
		while ($row = pg_fetch_assoc($resEstudiantes)) {
			$estudianteId = intval($row['estudiante_id']);
			$programaId = intval($row['programa_id']);
			$version = intval($row['version']);

			if (!isset($estudiantesInfo[$estudianteId])) {
				$estudiantesInfo[$estudianteId] = [
					'codigo' => $row['estudiante_codigo'],
					'nombre' => $row['estudiante_nombre'],
					'contacto_id' => intval($row['contacto_id']),
					'contacto_nombre' => $row['contacto_nombre']
				];
			}

			$estudiantesPrograms[] = [
				'estudiante_id' => $estudianteId,
				'programa_id' => $programaId,
				'programa_nombre' => $row['programa_nombre'],
				'version' => $version
			];
		}
		pg_free_result($resEstudiantes);

		if (empty($estudiantesPrograms)) {
			return [];
		}

		// QUERY 2: Cursos requeridos por programa/nivel
		$programaIds = array_unique(array_column($estudiantesPrograms, 'programa_id'));
		$placeholders = implode(',', array_map(function($i) { return '$' . ($i + 1); }, array_keys($programaIds)));
		
		$sqlCursosRequeridos = "
			SELECT 
				pc.programa_id,
				pc.version,
				pc.nivel_id,
				np.nombre as nivel_nombre,
				pc.curso_id,
				c.nombre as curso_nombre
			FROM programas_cursos pc
			JOIN cursos c ON c.id = pc.curso_id
			LEFT JOIN niveles_programas np ON np.id = pc.nivel_id
			WHERE pc.programa_id IN ($placeholders)
			ORDER BY pc.programa_id, pc.nivel_id, pc.curso_id
		";

		$resCursos = pg_query_params($this->conn, $sqlCursosRequeridos, array_values($programaIds));
		if (!$resCursos) {
			$this->logPg('getProximosACompletar:cursos', $sqlCursosRequeridos);
			return new WP_Error('db_query_failed', 'Error obteniendo cursos', [ 'status' => 500 ]);
		}

		$cursosRequeridos = [];
		while ($row = pg_fetch_assoc($resCursos)) {
			$programaId = intval($row['programa_id']);
			$version = intval($row['version']);
			$nivelId = $row['nivel_id'] ? intval($row['nivel_id']) : null;
			$cursoId = intval($row['curso_id']);

			$key = "{$programaId}_{$version}";
			if (!isset($cursosRequeridos[$key])) {
				$cursosRequeridos[$key] = [
					'niveles' => [],
					'programa_completo' => []
				];
			}

			$cursosRequeridos[$key]['programa_completo'][] = [
				'id' => $cursoId,
				'nombre' => $row['curso_nombre']
			];

			if ($nivelId) {
				if (!isset($cursosRequeridos[$key]['niveles'][$nivelId])) {
					$cursosRequeridos[$key]['niveles'][$nivelId] = [
						'nombre' => $row['nivel_nombre'],
						'cursos' => []
					];
				}
				$cursosRequeridos[$key]['niveles'][$nivelId]['cursos'][] = [
					'id' => $cursoId,
					'nombre' => $row['curso_nombre']
				];
			}
		}
		pg_free_result($resCursos);

		// QUERY 3: Progreso de estudiantes
		$estudianteIds = array_unique(array_column($estudiantesPrograms, 'estudiante_id'));
		$placeholders = implode(',', array_map(function($i) { return '$' . ($i + 1); }, array_keys($estudianteIds)));

		$sqlProgreso = "
			SELECT 
				ec.estudiante_id,
				ec.curso_id,
				MAX(ec.porcentaje) as porcentaje
			FROM estudiantes_cursos ec
			WHERE ec.estudiante_id IN ($placeholders)
			GROUP BY ec.estudiante_id, ec.curso_id
		";

		$resProgreso = pg_query_params($this->conn, $sqlProgreso, array_values($estudianteIds));
		if (!$resProgreso) {
			$this->logPg('getProximosACompletar:progreso', $sqlProgreso);
			return new WP_Error('db_query_failed', 'Error obteniendo progreso', [ 'status' => 500 ]);
		}

		$progresoEstudiantes = [];
		while ($row = pg_fetch_assoc($resProgreso)) {
			$estudianteId = intval($row['estudiante_id']);
			$cursoId = intval($row['curso_id']);
			$porcentaje = floatval($row['porcentaje']);

			if (!isset($progresoEstudiantes[$estudianteId])) {
				$progresoEstudiantes[$estudianteId] = [];
			}
			$progresoEstudiantes[$estudianteId][$cursoId] = $porcentaje;
		}
		pg_free_result($resProgreso);

		// QUERY 4: Diplomas ya emitidos
		$sqlDiplomas = "
			SELECT 
				estudiante_id,
				programa_id,
				nivel_id,
				tipo
			FROM diplomas_entregados
			WHERE estudiante_id IN ($placeholders)
		";

		$resDiplomas = pg_query_params($this->conn, $sqlDiplomas, array_values($estudianteIds));
		if (!$resDiplomas) {
			$this->logPg('getProximosACompletar:diplomas', $sqlDiplomas);
			return new WP_Error('db_query_failed', 'Error obteniendo diplomas', [ 'status' => 500 ]);
		}

		$diplomasEmitidos = [];
		while ($row = pg_fetch_assoc($resDiplomas)) {
			$estudianteId = intval($row['estudiante_id']);
			$programaId = intval($row['programa_id']);
			$nivelId = $row['nivel_id'] ? intval($row['nivel_id']) : null;
			$tipo = $row['tipo'];

			$key = $tipo === 'nivel' 
				? "{$estudianteId}_{$programaId}_nivel_{$nivelId}"
				: "{$estudianteId}_{$programaId}_programa";

			$diplomasEmitidos[$key] = true;
		}
		pg_free_result($resDiplomas);

		// PROCESAMIENTO EN PHP: Calcular porcentajes y generar resultado
		$proximos = [];

		foreach ($estudiantesPrograms as $ep) {
			$estudianteId = $ep['estudiante_id'];
			$programaId = $ep['programa_id'];
			$programaNombre = $ep['programa_nombre'];
			$version = $ep['version'];
			$key = "{$programaId}_{$version}";

			if (!isset($cursosRequeridos[$key])) {
				continue;
			}

			$progreso = $progresoEstudiantes[$estudianteId] ?? [];
			$estructura = $cursosRequeridos[$key];
			$infoEstudiante = $estudiantesInfo[$estudianteId];

			// Calcular progreso por nivel
			foreach ($estructura['niveles'] as $nivelId => $nivelInfo) {
				$diplomaKey = "{$estudianteId}_{$programaId}_nivel_{$nivelId}";
				if (isset($diplomasEmitidos[$diplomaKey])) {
					continue; // Ya tiene diploma
				}

				$cursosNivel = $nivelInfo['cursos'];
				$cursosCompletados = 0;
				$faltantes = [];

				foreach ($cursosNivel as $curso) {
					$cursoId = $curso['id'];
					if (isset($progreso[$cursoId]) && $progreso[$cursoId] >= 70) {
						$cursosCompletados++;
					} else {
						$faltantes[] = ['nombre' => $curso['nombre']];
					}
				}

				$totalCursos = count($cursosNivel);
				$porcentaje = $totalCursos > 0 
					? round(100.0 * $cursosCompletados / $totalCursos, 1)
					: 0;

				if ($porcentaje >= $umbralInt && $porcentaje < 100) {
					$proximos[] = [
						'tipo' => 'nivel',
						'estudiante_id' => $estudianteId,
						'estudiante_codigo' => $infoEstudiante['codigo'],
						'estudiante_nombre' => $infoEstudiante['nombre'],
						'contacto_id' => $infoEstudiante['contacto_id'],
						'contacto_nombre' => $infoEstudiante['contacto_nombre'],
						'programa_id' => $programaId,
						'programa_nombre' => $programaNombre,
						'nivel_id' => $nivelId,
						'nivel_nombre' => $nivelInfo['nombre'],
						'version' => $version,
						'progreso' => $porcentaje,
						'cursos_completados' => $cursosCompletados,
						'cursos_totales' => $totalCursos,
						'cursos_faltantes' => $faltantes
					];
				}
			}

			// Calcular progreso por programa completo
			$diplomaKey = "{$estudianteId}_{$programaId}_programa";
			if (!isset($diplomasEmitidos[$diplomaKey])) {
				$cursosPrograma = $estructura['programa_completo'];
				$cursosCompletados = 0;
				$faltantes = [];

				foreach ($cursosPrograma as $curso) {
					$cursoId = $curso['id'];
					if (isset($progreso[$cursoId]) && $progreso[$cursoId] >= 70) {
						$cursosCompletados++;
					} else {
						$faltantes[] = ['nombre' => $curso['nombre']];
					}
				}

				$totalCursos = count($cursosPrograma);
				$porcentaje = $totalCursos > 0 
					? round(100.0 * $cursosCompletados / $totalCursos, 1)
					: 0;

				if ($porcentaje >= $umbralInt && $porcentaje < 100) {
					$proximos[] = [
						'tipo' => 'programa_completo',
						'estudiante_id' => $estudianteId,
						'estudiante_codigo' => $infoEstudiante['codigo'],
						'estudiante_nombre' => $infoEstudiante['nombre'],
						'contacto_id' => $infoEstudiante['contacto_id'],
						'contacto_nombre' => $infoEstudiante['contacto_nombre'],
						'programa_id' => $programaId,
						'programa_nombre' => $programaNombre,
						'nivel_id' => null,
						'nivel_nombre' => null,
						'version' => $version,
						'progreso' => $porcentaje,
						'cursos_completados' => $cursosCompletados,
						'cursos_totales' => $totalCursos,
						'cursos_faltantes' => $faltantes
					];
				}
			}
		}

		// Ordenar por progreso descendente
		usort($proximos, function($a, $b) {
			return $b['progreso'] <=> $a['progreso'];
		});

		// Aplicar límite
		return array_slice($proximos, 0, $limiteInt);
	}

	/**
	 * Calcula el progreso de un nivel (porcentaje de cursos completados >= 70%)
	 */
	private function calcularProgresoNivel($nivelId, $estudianteId) {
		// Obtener cursos del nivel
		$sqlCursos = "SELECT c.id, c.nombre
					  FROM programas_cursos pc
					  JOIN cursos c ON pc.curso_id = c.id
					  WHERE pc.nivel_id = $1";
		$resCursos = pg_query_params($this->conn, $sqlCursos, [ intval($nivelId) ]);
		
		$cursos = [];
		while ($row = pg_fetch_assoc($resCursos)) {
			$cursos[intval($row['id'])] = $row['nombre'];
		}
		pg_free_result($resCursos);

		if (empty($cursos)) {
			return ['porcentaje' => 0, 'completados' => 0, 'total' => 0, 'faltantes' => []];
		}

		// Verificar cuáles están completados
		$cursosIds = array_keys($cursos);
		$placeholders = implode(',', array_map(function($i) { return '$' . ($i + 2); }, array_keys($cursosIds)));
		
		$sqlCompletados = "SELECT curso_id, MAX(porcentaje) as max_porcentaje
						   FROM estudiantes_cursos
						   WHERE estudiante_id = $1
						   AND curso_id IN ($placeholders)
						   GROUP BY curso_id";
		$params = array_merge([ intval($estudianteId) ], $cursosIds);
		$resCompletados = pg_query_params($this->conn, $sqlCompletados, $params);

		$completados = [];
		$faltantes = [];
		while ($row = pg_fetch_assoc($resCompletados)) {
			$cursoId = intval($row['curso_id']);
			$porcentaje = floatval($row['max_porcentaje']);
			if ($porcentaje >= 70) {
				$completados[] = $cursoId;
				unset($cursos[$cursoId]);
			}
		}
		pg_free_result($resCompletados);

		// Los cursos restantes son los faltantes
		foreach ($cursos as $id => $nombre) {
			$faltantes[] = ['id' => $id, 'nombre' => $nombre];
		}

		$total = count($cursosIds);
		$numCompletados = count($completados);
		$porcentaje = $total > 0 ? round(($numCompletados / $total) * 100, 1) : 0;

		return [
			'porcentaje' => $porcentaje,
			'completados' => $numCompletados,
			'total' => $total,
			'faltantes' => $faltantes
		];
	}

	/**
	 * Calcula el progreso de un programa completo
	 */
	private function calcularProgresoPrograma($programaId, $version, $estudianteId) {
		// Obtener todos los cursos del programa
		$sqlCursos = "SELECT c.id, c.nombre
					  FROM programas_cursos pc
					  JOIN cursos c ON pc.curso_id = c.id
					  WHERE pc.programa_id = $1 AND pc.version = $2";
		$resCursos = pg_query_params($this->conn, $sqlCursos, [ intval($programaId), intval($version) ]);
		
		$cursos = [];
		while ($row = pg_fetch_assoc($resCursos)) {
			$cursos[intval($row['id'])] = $row['nombre'];
		}
		pg_free_result($resCursos);

		if (empty($cursos)) {
			return ['porcentaje' => 0, 'completados' => 0, 'total' => 0, 'faltantes' => []];
		}

		// Verificar cuáles están completados
		$cursosIds = array_keys($cursos);
		$placeholders = implode(',', array_map(function($i) { return '$' . ($i + 2); }, array_keys($cursosIds)));
		
		$sqlCompletados = "SELECT curso_id, MAX(porcentaje) as max_porcentaje
						   FROM estudiantes_cursos
						   WHERE estudiante_id = $1
						   AND curso_id IN ($placeholders)
						   GROUP BY curso_id";
		$params = array_merge([ intval($estudianteId) ], $cursosIds);
		$resCompletados = pg_query_params($this->conn, $sqlCompletados, $params);

		$completados = [];
		$faltantes = [];
		while ($row = pg_fetch_assoc($resCompletados)) {
			$cursoId = intval($row['curso_id']);
			$porcentaje = floatval($row['max_porcentaje']);
			if ($porcentaje >= 70) {
				$completados[] = $cursoId;
				unset($cursos[$cursoId]);
			}
		}
		pg_free_result($resCompletados);

		// Los cursos restantes son los faltantes
		foreach ($cursos as $id => $nombre) {
			$faltantes[] = ['id' => $id, 'nombre' => $nombre];
		}

		$total = count($cursosIds);
		$numCompletados = count($completados);
		$porcentaje = $total > 0 ? round(($numCompletados / $total) * 100, 1) : 0;

		return [
			'porcentaje' => $porcentaje,
			'completados' => $numCompletados,
			'total' => $total,
			'faltantes' => $faltantes
		];
	}

	/**
	 * Registra la entrega física de un diploma
	 */
	public function registrarEntrega($diplomaId, $fechaEntrega = null, $entregadoPor = null, $notas = null) {
		$fecha = $fechaEntrega ? $fechaEntrega : date('Y-m-d');

		$sql = "UPDATE diplomas_entregados 
				SET fecha_entrega = $1, 
					entregado_por = $2, 
					notas = COALESCE($3, notas),
					updated_at = NOW()
				WHERE id = $4";
		$params = [ $fecha, $entregadoPor, $notas, intval($diplomaId) ];
		$res = pg_query_params($this->conn, $sql, $params);
		if (!$res) {
			$this->logPg('registrarEntrega', $sql);
			return new WP_Error('db_update_failed', 'Error registrando entrega', [ 'status' => 500 ]);
		}
		pg_free_result($res);

		return true;
	}

	/**
	 * Lista diplomas emitidos para un estudiante/contacto
	 */
	public function listByDestinatario($estudianteId = null, $contactoId = null, $pendientesOnly = false) {
		if (($estudianteId && $contactoId) || (!$estudianteId && !$contactoId)) {
			return new WP_Error('invalid_params', 'Debe proporcionar estudianteId o contactoId', [ 'status' => 422 ]);
		}

		$where = $estudianteId ? 'd.estudiante_id = $1' : 'e.id_contacto = $1';
		$idValue = $estudianteId ? intval($estudianteId) : intval($contactoId);

		if ($pendientesOnly) {
			$where .= " AND d.fecha_entrega IS NULL";
		}

		$sql = "SELECT d.*, 
				   p.nombre as programa_nombre, 
				   p.descripcion as programa_descripcion,
				   n.nombre as nivel_nombre,
				   e.id_estudiante as estudiante_codigo,
				   TRIM(COALESCE(e.nombre1, '') || ' ' || COALESCE(e.apellido1, '')) as estudiante_nombre
			FROM diplomas_entregados d
			JOIN programas p ON d.programa_id = p.id
			LEFT JOIN niveles_programas n ON d.nivel_id = n.id
			JOIN estudiantes e ON d.estudiante_id = e.id
			WHERE $where
			ORDER BY d.fecha_emision DESC, d.created_at DESC";

		$res = pg_query_params($this->conn, $sql, [ $idValue ]);
		if (!$res) {
			$this->logPg('listByDestinatario', $sql);
			return new WP_Error('db_query_failed', 'Error listando diplomas', [ 'status' => 500 ]);
		}

		$diplomas = [];
		while ($row = pg_fetch_assoc($res)) {
			$diplomas[] = [
				'id' => intval($row['id']),
				'tipo' => $row['tipo'],
				'programa_id' => intval($row['programa_id']),
				'programa_nombre' => $row['programa_nombre'],
				'programa_descripcion' => $row['programa_descripcion'],
				'nivel_id' => $row['nivel_id'] ? intval($row['nivel_id']) : null,
				'nivel_nombre' => $row['nivel_nombre'],
				'version_programa' => intval($row['version_programa']),
				'estudiante_id' => $row['estudiante_id'] ? intval($row['estudiante_id']) : null,
				'estudiante_codigo' => $row['estudiante_codigo'],
				'estudiante_nombre' => $row['estudiante_nombre'],
				'fecha_emision' => $row['fecha_emision'],
				'fecha_entrega' => $row['fecha_entrega'],
				'entregado_por' => $row['entregado_por'] ? intval($row['entregado_por']) : null,
				'notas' => $row['notas'],
				'entregado' => !is_null($row['fecha_entrega'])
			];
		}
		pg_free_result($res);

		return $diplomas;
	}

	/**
	 * Genera un "acta de cierre" con todos los diplomas de un contacto
	 * Incluye tanto los emitidos como los elegibles pendientes
	 */
	public function generarActaCierre($contactoId) {
		// Diplomas ya emitidos
		$emitidos = $this->listByDestinatario(null, $contactoId);
		if (is_wp_error($emitidos)) {
			return $emitidos;
		}

		// Diplomas elegibles pero no emitidos
		$elegibles = $this->getElegibles(null, $contactoId);
		if (is_wp_error($elegibles)) {
			return $elegibles;
		}

		// Los elegibles ya vienen con toda la información necesaria (incluyendo estudiante)
		return [
			'emitidos' => $emitidos,
			'elegibles' => $elegibles,
			'pendientes_entrega' => array_filter($emitidos, function($d) { return !$d['entregado']; })
		];
	}

	/**
	 * Obtiene un diploma por ID
	 */
	public function getById($id) {
		$sql = "SELECT d.*, 
					   p.nombre as programa_nombre, 
					   p.descripcion as programa_descripcion,
					   n.nombre as nivel_nombre
				FROM diplomas_entregados d
				JOIN programas p ON d.programa_id = p.id
				LEFT JOIN niveles_programas n ON d.nivel_id = n.id
				WHERE d.id = $1";

		$res = pg_query_params($this->conn, $sql, [ intval($id) ]);
		if (!$res) {
			$this->logPg('getById', $sql);
			return new WP_Error('db_query_failed', 'Error obteniendo diploma', [ 'status' => 500 ]);
		}

		$row = pg_fetch_assoc($res);
		pg_free_result($res);

		if (!$row) {
			return new WP_Error('not_found', 'Diploma no encontrado', [ 'status' => 404 ]);
		}

		return [
			'id' => intval($row['id']),
			'tipo' => $row['tipo'],
			'programa_id' => intval($row['programa_id']),
			'programa_nombre' => $row['programa_nombre'],
			'programa_descripcion' => $row['programa_descripcion'],
			'nivel_id' => $row['nivel_id'] ? intval($row['nivel_id']) : null,
			'nivel_nombre' => $row['nivel_nombre'],
			'version_programa' => intval($row['version_programa']),
			'estudiante_id' => $row['estudiante_id'] ? intval($row['estudiante_id']) : null,
			'contacto_id' => $row['contacto_id'] ? intval($row['contacto_id']) : null,
			'fecha_emision' => $row['fecha_emision'],
			'fecha_entrega' => $row['fecha_entrega'],
			'entregado_por' => $row['entregado_por'] ? intval($row['entregado_por']) : null,
			'notas' => $row['notas'],
			'entregado' => !is_null($row['fecha_entrega'])
		];
	}
}

