<?php
if (!defined('ABSPATH')) { exit; }

class PlgGenesis_ContactosService {
	private $repo;
	public function __construct($repo) { $this->repo = $repo; }
	public function buscar($q, $limit = 20, $offset = 0) {
		return $this->repo->search($q, $limit, $offset);
	}

    public function crear($payload) {
        $nombre = trim((string)($payload['nombre'] ?? ''));
        if ($nombre === '') return new WP_Error('validation_error', 'El nombre es requerido', [ 'status' => 422 ]);
        return $this->repo->create($payload);
    }

    public function obtener($id) { return $this->repo->getById($id); }
    public function actualizar($id, $payload) { return $this->repo->update($id, $payload); }
}