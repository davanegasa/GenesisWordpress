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

		$sql = "
		WITH progreso_estudiantes AS (
			-- Calcular progreso por nivel
			SELECT DISTINCT
				pa.programa_id,
				p.nombre as programa_nombre,
				e.id as estudiante_id
			FROM programas_asignaciones pa
			JOIN programas p ON pa.programa_id = p.id
			JOIN estudiantes e ON e.id_contacto = pa.contacto_id
			JOIN niveles_programas np ON np.programa_id = pa.programa_id AND np.version = pa.version
			JOIN programas_cursos pc ON pc.nivel_id = np.id AND pc.programa_id = pa.programa_id AND pc.version = pa.version
			LEFT JOIN LATERAL (
				SELECT curso_id, MAX(porcentaje) as porcentaje
				FROM estudiantes_cursos
				WHERE estudiante_id = e.id
				GROUP BY curso_id
			) ec ON ec.curso_id = pc.curso_id
			WHERE pa.contacto_id = $1
				AND EXISTS (
					SELECT 1 FROM estudiantes_cursos ec2
					JOIN programas_cursos pc2 ON pc2.curso_id = ec2.curso_id
					WHERE ec2.estudiante_id = e.id
					AND pc2.programa_id = pa.programa_id
					AND pc2.version = pa.version
					AND pc2.nivel_id = np.id
				)
			GROUP BY pa.programa_id, p.nombre, e.id, pa.version, np.id
			HAVING CASE 
				WHEN COUNT(DISTINCT pc.curso_id) > 0 
				THEN ROUND(100.0 * COUNT(DISTINCT CASE WHEN ec.porcentaje >= 70 THEN ec.curso_id END) / COUNT(DISTINCT pc.curso_id), 1)
				ELSE 0
			END >= $2
			AND NOT EXISTS (
				SELECT 1 FROM diplomas_entregados de
				WHERE de.tipo = 'nivel'
				AND de.programa_id = pa.programa_id
				AND de.nivel_id = np.id
				AND de.estudiante_id = e.id
			)
			
			UNION
			
			-- Calcular progreso por programa completo
			SELECT DISTINCT
				pa.programa_id,
				p.nombre as programa_nombre,
				e.id as estudiante_id
			FROM programas_asignaciones pa
			JOIN programas p ON pa.programa_id = p.id
			JOIN estudiantes e ON e.id_contacto = pa.contacto_id
			JOIN programas_cursos pc ON pc.programa_id = pa.programa_id AND pc.version = pa.version
			LEFT JOIN LATERAL (
				SELECT curso_id, MAX(porcentaje) as porcentaje
				FROM estudiantes_cursos
				WHERE estudiante_id = e.id
				GROUP BY curso_id
			) ec ON ec.curso_id = pc.curso_id
			WHERE pa.contacto_id = $1
				AND EXISTS (
					SELECT 1 FROM estudiantes_cursos ec2
					JOIN programas_cursos pc2 ON pc2.curso_id = ec2.curso_id
					WHERE ec2.estudiante_id = e.id
					AND pc2.programa_id = pa.programa_id
					AND pc2.version = pa.version
				)
			GROUP BY pa.programa_id, p.nombre, e.id, pa.version
			HAVING CASE 
				WHEN COUNT(DISTINCT pc.curso_id) > 0 
				THEN ROUND(100.0 * COUNT(DISTINCT CASE WHEN ec.porcentaje >= 70 THEN ec.curso_id END) / COUNT(DISTINCT pc.curso_id), 1)
				ELSE 0
			END >= $2
			AND NOT EXISTS (
				SELECT 1 FROM diplomas_entregados de
				WHERE de.tipo = 'programa_completo'
				AND de.programa_id = pa.programa_id
				AND de.estudiante_id = e.id
			)
		)
		SELECT 
			programa_id,
			programa_nombre,
			COUNT(DISTINCT estudiante_id) as total_estudiantes
		FROM progreso_estudiantes
		GROUP BY programa_id, programa_nombre
		ORDER BY programa_nombre
		";

		$res = pg_query_params($this->conn, $sql, [ $contactoIdInt, $umbralInt ]);
		if (!$res) {
			$this->logPg('getProgramasConProximos', $sql);
			return new WP_Error('db_query_failed', 'Error obteniendo programas', [ 'status' => 500 ]);
		}

		$programas = [];
		while ($row = pg_fetch_assoc($res)) {
			$programas[] = [
				'programa_id' => intval($row['programa_id']),
				'programa_nombre' => $row['programa_nombre'],
				'total_estudiantes' => intval($row['total_estudiantes'])
			];
		}
		pg_free_result($res);

		return $programas;
	}

	/**
	 * Obtiene estudiantes próximos a completar (progreso >= 80%)
	 * Retorna información de niveles y programas que están por completar
	 * OPTIMIZADO: Usa una sola query con CTEs y agregaciones en SQL
	 * IMPORTANTE: contactoId es OBLIGATORIO para evitar queries muy pesadas en producción
	 * 
	 * @param int $limite Límite de resultados
	 * @param int $umbral Porcentaje mínimo de progreso (default 80)
	 * @param int $contactoId ID del contacto (OBLIGATORIO)
	 * @param int|null $programaId ID del programa (opcional, para filtrar por programa específico)
	 */
	public function getProximosACompletar($limite = 50, $umbral = 80, $contactoId = null, $programaId = null) {
		// Validar que contactoId sea obligatorio
		if (!$contactoId) {
			return new WP_Error('invalid_params', 'El parámetro contactoId es obligatorio', [ 'status' => 422 ]);
		}
		
		$contactoFilter = "AND pa.contacto_id = " . intval($contactoId);
		$programaFilter = $programaId ? "AND pa.programa_id = " . intval($programaId) : "";
		
		// Query optimizada con CTEs que calcula todo en SQL
		// Solo considera estudiantes que tienen al menos UN curso registrado en el programa
		$sql = "
		WITH progreso_estudiantes AS (
			-- Calcular progreso por nivel
			SELECT 
				e.id as estudiante_id,
				e.id_estudiante as estudiante_codigo,
				TRIM(COALESCE(e.nombre1, '') || ' ' || COALESCE(e.apellido1, '')) as estudiante_nombre,
				pa.contacto_id,
				c.nombre as contacto_nombre,
				pa.programa_id,
				p.nombre as programa_nombre,
				pa.version,
				np.id as nivel_id,
				np.nombre as nivel_nombre,
				'nivel' as tipo,
				COUNT(DISTINCT pc.curso_id) as total_cursos,
				COUNT(DISTINCT CASE 
					WHEN ec.porcentaje >= 70 THEN ec.curso_id 
				END) as cursos_completados,
				CASE 
					WHEN COUNT(DISTINCT pc.curso_id) > 0 
					THEN ROUND(100.0 * COUNT(DISTINCT CASE WHEN ec.porcentaje >= 70 THEN ec.curso_id END) / COUNT(DISTINCT pc.curso_id), 1)
					ELSE 0
				END as progreso
			FROM programas_asignaciones pa
			JOIN programas p ON pa.programa_id = p.id
			JOIN estudiantes e ON e.id_contacto = pa.contacto_id
			JOIN contactos c ON c.id = pa.contacto_id
			JOIN niveles_programas np ON np.programa_id = pa.programa_id AND np.version = pa.version
			JOIN programas_cursos pc ON pc.nivel_id = np.id AND pc.programa_id = pa.programa_id AND pc.version = pa.version
			LEFT JOIN LATERAL (
				SELECT curso_id, MAX(porcentaje) as porcentaje
				FROM estudiantes_cursos
				WHERE estudiante_id = e.id
				GROUP BY curso_id
			) ec ON ec.curso_id = pc.curso_id
			WHERE 1=1 $contactoFilter $programaFilter
				-- Solo estudiantes que tienen al menos un curso registrado en este programa/nivel
				AND EXISTS (
					SELECT 1 FROM estudiantes_cursos ec2
					JOIN programas_cursos pc2 ON pc2.curso_id = ec2.curso_id
					WHERE ec2.estudiante_id = e.id
					AND pc2.programa_id = pa.programa_id
					AND pc2.version = pa.version
					AND pc2.nivel_id = np.id
				)
			GROUP BY e.id, e.id_estudiante, e.nombre1, e.apellido1, pa.contacto_id, c.nombre, 
					 pa.programa_id, p.nombre, pa.version, np.id, np.nombre
			
			UNION ALL
			
			-- Calcular progreso por programa completo
			SELECT 
				e.id as estudiante_id,
				e.id_estudiante as estudiante_codigo,
				TRIM(COALESCE(e.nombre1, '') || ' ' || COALESCE(e.apellido1, '')) as estudiante_nombre,
				pa.contacto_id,
				c.nombre as contacto_nombre,
				pa.programa_id,
				p.nombre as programa_nombre,
				pa.version,
				NULL as nivel_id,
				NULL as nivel_nombre,
				'programa_completo' as tipo,
				COUNT(DISTINCT pc.curso_id) as total_cursos,
				COUNT(DISTINCT CASE 
					WHEN ec.porcentaje >= 70 THEN ec.curso_id 
				END) as cursos_completados,
				CASE 
					WHEN COUNT(DISTINCT pc.curso_id) > 0 
					THEN ROUND(100.0 * COUNT(DISTINCT CASE WHEN ec.porcentaje >= 70 THEN ec.curso_id END) / COUNT(DISTINCT pc.curso_id), 1)
					ELSE 0
				END as progreso
			FROM programas_asignaciones pa
			JOIN programas p ON pa.programa_id = p.id
			JOIN estudiantes e ON e.id_contacto = pa.contacto_id
			JOIN contactos c ON c.id = pa.contacto_id
			JOIN programas_cursos pc ON pc.programa_id = pa.programa_id AND pc.version = pa.version
			LEFT JOIN LATERAL (
				SELECT curso_id, MAX(porcentaje) as porcentaje
				FROM estudiantes_cursos
				WHERE estudiante_id = e.id
				GROUP BY curso_id
			) ec ON ec.curso_id = pc.curso_id
			WHERE 1=1 $contactoFilter $programaFilter
				-- Solo estudiantes que tienen al menos un curso registrado en este programa
				AND EXISTS (
					SELECT 1 FROM estudiantes_cursos ec2
					JOIN programas_cursos pc2 ON pc2.curso_id = ec2.curso_id
					WHERE ec2.estudiante_id = e.id
					AND pc2.programa_id = pa.programa_id
					AND pc2.version = pa.version
				)
			GROUP BY e.id, e.id_estudiante, e.nombre1, e.apellido1, pa.contacto_id, c.nombre, 
					 pa.programa_id, p.nombre, pa.version
		)
		SELECT 
			pe.*,
			ARRAY_AGG(DISTINCT c.nombre) FILTER (WHERE c.id IS NOT NULL AND ec.porcentaje < 70 OR ec.porcentaje IS NULL) as cursos_faltantes
		FROM progreso_estudiantes pe
		LEFT JOIN programas_cursos pc ON pc.programa_id = pe.programa_id 
			AND pc.version = pe.version 
			AND (pe.nivel_id IS NULL OR pc.nivel_id = pe.nivel_id)
		LEFT JOIN cursos c ON c.id = pc.curso_id
		LEFT JOIN LATERAL (
			SELECT curso_id, MAX(porcentaje) as porcentaje
			FROM estudiantes_cursos
			WHERE estudiante_id = pe.estudiante_id
			GROUP BY curso_id
		) ec ON ec.curso_id = pc.curso_id
		WHERE pe.progreso >= $1 
			AND pe.progreso < 100
			-- Excluir los que ya tienen diploma emitido
			AND NOT EXISTS (
				SELECT 1 FROM diplomas_entregados de
				WHERE de.estudiante_id = pe.estudiante_id
				AND de.programa_id = pe.programa_id
				AND CASE 
					WHEN pe.tipo = 'nivel' THEN de.tipo = 'nivel' AND de.nivel_id = pe.nivel_id
					WHEN pe.tipo = 'programa_completo' THEN de.tipo = 'programa_completo'
				END
			)
		GROUP BY pe.estudiante_id, pe.estudiante_codigo, pe.estudiante_nombre, 
				 pe.contacto_id, pe.contacto_nombre, pe.programa_id, pe.programa_nombre,
				 pe.version, pe.nivel_id, pe.nivel_nombre, pe.tipo, pe.total_cursos,
				 pe.cursos_completados, pe.progreso
		ORDER BY pe.progreso DESC
		LIMIT $2
		";

		$res = pg_query_params($this->conn, $sql, [ floatval($umbral), intval($limite) ]);
		
		if (!$res) {
			$this->logPg('getProximosACompletar', $sql);
			return new WP_Error('db_error', 'Error consultando próximos a completar: ' . pg_last_error($this->conn), [ 'status' => 500 ]);
		}

		$proximos = [];
		while ($row = pg_fetch_assoc($res)) {
			// Parsear el array de PostgreSQL (formato: {curso1,curso2,curso3})
			$faltantes = [];
			if ($row['cursos_faltantes'] && $row['cursos_faltantes'] !== '{}') {
				$faltantesStr = trim($row['cursos_faltantes'], '{}');
				if ($faltantesStr) {
					$faltantes = array_map('trim', explode(',', $faltantesStr));
					$faltantes = array_filter($faltantes); // Remover vacíos
					$faltantes = array_map(function($nombre) {
						return ['nombre' => $nombre];
					}, $faltantes);
				}
			}

			$proximos[] = [
				'tipo' => $row['tipo'],
				'estudiante_id' => intval($row['estudiante_id']),
				'estudiante_codigo' => $row['estudiante_codigo'],
				'estudiante_nombre' => $row['estudiante_nombre'],
				'contacto_id' => intval($row['contacto_id']),
				'contacto_nombre' => $row['contacto_nombre'],
				'programa_id' => intval($row['programa_id']),
				'programa_nombre' => $row['programa_nombre'],
				'nivel_id' => $row['nivel_id'] ? intval($row['nivel_id']) : null,
				'nivel_nombre' => $row['nivel_nombre'],
				'version' => intval($row['version']),
				'progreso' => floatval($row['progreso']),
				'cursos_completados' => intval($row['cursos_completados']),
				'cursos_totales' => intval($row['total_cursos']),
				'cursos_faltantes' => $faltantes
			];
		}
		pg_free_result($res);

		return $proximos;
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

