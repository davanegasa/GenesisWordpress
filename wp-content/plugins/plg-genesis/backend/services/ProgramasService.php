<?php
if (!defined('ABSPATH')) { exit; }

class PlgGenesis_ProgramasService {
    private $repo;
    public function __construct($repo) { $this->repo = $repo; }

    public function listar($q = '', $includeAll = false){ return $this->repo->list($q, $includeAll); }
    public function obtener($id){ return $this->repo->get($id); }
    public function crear($payload){ return $this->repo->create($payload); }
    public function actualizar($id, $payload){ return $this->repo->update($id, $payload); }
    public function eliminar($id, $hard = false){ return $this->repo->delete($id, $hard); }
    public function asignar($idPrograma, $idEstudiante = null, $idContacto = null, $remove = false){ return $this->repo->assign($idPrograma, $idEstudiante, $idContacto, $remove); }
}


