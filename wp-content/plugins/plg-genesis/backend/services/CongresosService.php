<?php
if (!defined('ABSPATH')) { exit; }

class PlgGenesis_CongresosService {
	private $repo;
	public function __construct($repo) { $this->repo = $repo; }
	public function listar() {
		return $this->repo->findAllWithStats();
	}

    public function obtener($id){ return $this->repo->getById($id); }
    public function actualizar($id, $payload){ return $this->repo->update($id, $payload); }
}