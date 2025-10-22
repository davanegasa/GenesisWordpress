<?php
if (!defined('ABSPATH')) { exit; }

class PlgGenesis_EstudiantesService {
	private $repository;

	public function __construct($repository) {
		$this->repository = $repository;
	}

	public function listarPorContacto($contactoId) {
		if (!is_numeric($contactoId) || intval($contactoId) <= 0) {
			return new WP_Error('invalid_contacto_id', 'El ID del contacto es inválido', [ 'status' => 400 ]);
		}
		return $this->repository->findByContactoId(intval($contactoId));
	}

	public function listar($q = '', $page = 1, $limit = 20) {
		return $this->repository->list(strval($q), intval($page), intval($limit));
	}

	private function generateStudentId($contactId, $manualId = null) {
		if ($manualId) {
			$exists = $this->repository->existsStudentId($manualId);
			if ($exists instanceof WP_Error) return $exists;
			if ($exists) return new WP_Error('student_id_exists', 'El código de estudiante ya existe', [ 'status' => 409 ]);
			return $manualId;
		}
		$total = $this->repository->countEstudiantes();
		if ($total instanceof WP_Error) return $total;
		$code = $this->repository->getContactCode($contactId);
		if ($code instanceof WP_Error) return $code;
		return trim($code) . $total;
	}

	public function create(array $e) {
        // Defaults si faltan (catálogos globales)
        if (!isset($e['estado_civil']) || $e['estado_civil'] === '') {
            $e['estado_civil'] = 'Soltero';
        }
        if (!isset($e['escolaridad']) || $e['escolaridad'] === '') {
            $e['escolaridad'] = 'Ninguno';
        }

		// Soportar contacto_code (nuevo) o id_contacto (deprecated)
		if (isset($e['contacto_code']) && !empty($e['contacto_code'])) {
			$contactId = $this->repository->getContactIdByCode($e['contacto_code']);
			if ($contactId instanceof WP_Error) return $contactId;
			$e['id_contacto'] = $contactId;
		}

		$required = ['id_contacto','doc_identidad','nombre1','apellido1','estado_civil','escolaridad'];
		foreach ($required as $k) {
			if (!isset($e[$k]) || $e[$k] === '') {
				return new WP_Error('validation_error', 'Falta campo requerido: ' . $k, [ 'status' => 422 ]);
			}
		}
		$studentId = $this->generateStudentId(intval($e['id_contacto']), $e['id_estudiante'] ?? null);
		if ($studentId instanceof WP_Error) return $studentId;
		$e['id_estudiante'] = $studentId;
		return $this->repository->create($e);
	}

	public function getById($idEstudiante) {
		if (!$idEstudiante) return new WP_Error('invalid_student_id', 'ID estudiante inválido', [ 'status' => 400 ]);
		return $this->repository->getByStudentId($idEstudiante);
	}

	public function update($idEstudiante, array $e) {
		if (!$idEstudiante) return new WP_Error('invalid_student_id', 'ID estudiante inválido', [ 'status' => 400 ]);
		// Permitir actualización parcial de documento/celular/email
		if (array_key_exists('celular',$e) || array_key_exists('email',$e) || array_key_exists('doc_identidad',$e) || array_key_exists('docIdentidad',$e)) {
			$cel = isset($e['celular']) ? trim(strval($e['celular'])) : null;
			$eml = isset($e['email']) ? trim(strval($e['email'])) : null;
			$doc = isset($e['doc_identidad']) ? trim(strval($e['doc_identidad'])) : (isset($e['docIdentidad']) ? trim(strval($e['docIdentidad'])) : null);
			return $this->repository->updatePartialByStudentId($idEstudiante, $cel, $eml, $doc);
		}
		$required = ['doc_identidad','nombre1','apellido1','estado_civil','escolaridad'];
		foreach ($required as $k) {
			if (!isset($e[$k]) || $e[$k] === '') {
				return new WP_Error('validation_error', 'Falta campo requerido: ' . $k, [ 'status' => 422 ]);
			}
		}
		return $this->repository->updateByStudentId($idEstudiante, $e);
	}

    public function existsByDocumento($doc) {
        if (!$doc) return new WP_Error('invalid_doc', 'Documento inválido', [ 'status' => 400 ]);
        return $this->repository->existsByDocumento($doc);
    }

    public function nextCodeForContact($contactId) {
        if (!is_numeric($contactId) || intval($contactId) <= 0) {
            return new WP_Error('invalid_contacto_id', 'El ID del contacto es inválido', [ 'status' => 400 ]);
        }
        $total = $this->repository->countEstudiantes();
        if ($total instanceof WP_Error) return $total;
        $code = $this->repository->getContactCode($contactId);
        if ($code instanceof WP_Error) return $code;
        return trim($code) . $total;
    }

    public function nextCodeForContactByCode($contactCode) {
        if (empty($contactCode)) {
            return new WP_Error('invalid_contacto_code', 'El código del contacto es inválido', [ 'status' => 400 ]);
        }
        $total = $this->repository->countEstudiantes();
        if ($total instanceof WP_Error) return $total;
        $code = $this->repository->getContactCodeByContactCode($contactCode);
        if ($code instanceof WP_Error) return $code;
        return trim($code) . $total;
    }
}