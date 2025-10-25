<?php
if (!defined('ABSPATH')) { exit; }

require_once plugin_dir_path(__FILE__) . '/../repositories/DiplomasRepository.php';

class PlgGenesis_DiplomasService {
	private $repository;

	public function __construct($connection) {
		$this->repository = new PlgGenesis_DiplomasRepository($connection);
	}

	/**
	 * Obtiene diplomas elegibles para emisión
	 */
	public function getElegibles($estudianteId = null, $contactoId = null) {
		return $this->repository->getElegibles($estudianteId, $contactoId);
	}

	/**
	 * Emite un diploma para un estudiante (validando elegibilidad)
	 */
	public function emitirDiploma($tipo, $programaId, $version, $estudianteId, $nivelId = null, $notas = null) {
		// Validar parámetros
		if (!$estudianteId) {
			return new WP_Error('invalid_params', 'Debe proporcionar estudianteId', [ 'status' => 422 ]);
		}

		if (!in_array($tipo, ['programa_completo', 'nivel'])) {
			return new WP_Error('invalid_tipo', 'Tipo debe ser programa_completo o nivel', [ 'status' => 422 ]);
		}

		if ($tipo === 'nivel' && !$nivelId) {
			return new WP_Error('missing_nivel', 'nivel_id es requerido para diplomas de tipo nivel', [ 'status' => 422 ]);
		}

		// Emitir
		$result = $this->repository->emitir($tipo, $programaId, $version, $estudianteId, $nivelId, $notas);
		if (is_wp_error($result)) {
			return $result;
		}

		return [ 'diplomaId' => $result, 'message' => 'Diploma emitido exitosamente' ];
	}

	/**
	 * Emite múltiples diplomas en batch (una sola transacción)
	 * Crea un acta automáticamente
	 */
	public function emitirBatch($diplomas, $contactoId = null, $observaciones = null) {
		if (empty($diplomas) || !is_array($diplomas)) {
			return new WP_Error('invalid_params', 'Se requiere un array de diplomas', [ 'status' => 422 ]);
		}

		return $this->repository->emitirBatch($diplomas, $contactoId, $observaciones);
	}

	/**
	 * Obtiene una acta por ID
	 */
	public function getActa($actaId) {
		return $this->repository->getActaById($actaId);
	}

	/**
	 * Lista actas con filtros
	 */
	public function listarActas($contactoId = null, $estado = 'activa', $limit = 50, $offset = 0) {
		return $this->repository->listActas($contactoId, $estado, $limit, $offset);
	}

	/**
	 * Obtiene lista de programas con estudiantes próximos a completar
	 * Retorna solo programas y contador (ligero y rápido)
	 * 
	 * @param int $contactoId ID del contacto (OBLIGATORIO)
	 * @param int $umbral Porcentaje mínimo de progreso
	 */
	public function getProgramasConProximos($contactoId, $umbral = 80) {
		return $this->repository->getProgramasConProximos($contactoId, $umbral);
	}

	/**
	 * Obtiene estudiantes próximos a completar (>= 80% de progreso)
	 * 
	 * @param int $limite Límite de resultados
	 * @param int $umbral Porcentaje mínimo de progreso
	 * @param int $contactoId ID del contacto (OBLIGATORIO)
	 * @param int|null $programaId ID del programa (opcional, para filtrar por programa específico)
	 */
	public function getProximosACompletar($limite = 50, $umbral = 80, $contactoId = null, $programaId = null) {
		return $this->repository->getProximosACompletar($limite, $umbral, $contactoId, $programaId);
	}

	/**
	 * Registra la entrega física de un diploma
	 */
	public function registrarEntrega($diplomaId, $fechaEntrega = null, $notas = null) {
		// Obtener usuario actual
		$currentUser = wp_get_current_user();
		$entregadoPor = $currentUser->ID > 0 ? $currentUser->ID : null;

		$result = $this->repository->registrarEntrega($diplomaId, $fechaEntrega, $entregadoPor, $notas);
		if (is_wp_error($result)) {
			return $result;
		}

		return [ 'message' => 'Entrega registrada exitosamente' ];
	}

	/**
	 * Lista diplomas de un destinatario
	 */
	public function listarDiplomas($estudianteId = null, $contactoId = null, $pendientesOnly = false) {
		return $this->repository->listByDestinatario($estudianteId, $contactoId, $pendientesOnly);
	}

	/**
	 * Genera acta de cierre con todos los diplomas de un contacto
	 */
	public function generarActaCierre($contactoId) {
		return $this->repository->generarActaCierre($contactoId);
	}

	/**
	 * Obtiene un diploma por ID
	 */
	public function getDiploma($id) {
		return $this->repository->getById($id);
	}

	/**
	 * Emite todos los diplomas elegibles de un destinatario (útil para cierre masivo)
	 * Si es contacto, emite diplomas individuales por cada estudiante asociado
	 */
	public function emitirTodosElegibles($estudianteId = null, $contactoId = null) {
		$elegibles = $this->repository->getElegibles($estudianteId, $contactoId);
		if (is_wp_error($elegibles)) {
			return $elegibles;
		}

		$emitidos = [];
		$errores = [];

		foreach ($elegibles as $elegible) {
			// Los diplomas ahora siempre tienen estudiante_id
			$result = $this->repository->emitir(
				$elegible['tipo'],
				$elegible['programa_id'],
				$elegible['version_programa'],
				$elegible['estudiante_id'],
				$elegible['nivel_id'],
				'Emitido automáticamente'
			);

			if (is_wp_error($result)) {
				$errores[] = [
					'elegible' => $elegible,
					'error' => $result->get_error_message()
				];
			} else {
				$emitidos[] = $result;
			}
		}

		return [
			'emitidos' => $emitidos,
			'total_emitidos' => count($emitidos),
			'errores' => $errores,
			'total_errores' => count($errores)
		];
	}
}

