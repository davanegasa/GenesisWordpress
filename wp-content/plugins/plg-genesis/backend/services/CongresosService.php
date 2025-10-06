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
    public function stats($id){ return $this->repo->getStats($id); }
    public function checkin($id, $numero, $codigo, $tipo){ return $this->repo->checkin($id, $numero, $codigo, $tipo); }
    public function void($id, $numero, $codigo){ return $this->repo->voidTicket($id, $numero, $codigo); }
    public function inscritos($id, $q='', $tipo='', $limit=50, $offset=0){ return $this->repo->listInscritos($id, $q, $tipo, $limit, $offset); }
    public function noAsistentes($id, $tipo='llegada', $limit=50, $offset=0){ return $this->repo->listNoAsistentes($id, $tipo, $limit, $offset); }
    public function ultimos($id, $limit=20){ return $this->repo->listUltimos($id, $limit); }
}