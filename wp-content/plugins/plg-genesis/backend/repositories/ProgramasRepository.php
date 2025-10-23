<?php
if (!defined('ABSPATH')) { exit; }

class PlgGenesis_ProgramasRepository {
    private $conn;
    public function __construct($connection){ $this->conn = $connection; }

    private function logPg($context, $sql = null){
        $pgErr = function_exists('pg_last_error') ? pg_last_error($this->conn) : 'unknown';
        $prefix = '[PlgGenesis][ProgramasRepository] ' . $context . ' -> ';
        if ($sql) { error_log($prefix . $sql . ' | err=' . $pgErr); } else { error_log($prefix . $pgErr); }
    }

    private function hasColumn($table, $column) {
        $sql = "SELECT 1 FROM information_schema.columns WHERE table_schema='public' AND table_name=$1 AND column_name=$2 LIMIT 1";
        $res = pg_query_params($this->conn, $sql, [ strval($table), strval($column) ]);
        if (!$res) { $this->logPg('hasColumn', $sql); return false; }
        $ok = pg_num_rows($res) > 0; pg_free_result($res); return $ok;
    }

    public function list($q = '', $includeAll = false){
        $clauses = [];
        $params = []; $idx = 1;
        if ($this->hasColumn('programas','deleted_at')) { $clauses[] = 'p.deleted_at IS NULL'; }
        if ($q !== '') { $clauses[] = "(p.nombre ILIKE $${idx} OR p.descripcion ILIKE $${idx})"; $params[] = '%'.$q.'%'; $idx++; }
        $where = count($clauses) ? ('WHERE '.implode(' AND ',$clauses)) : '';
        $sql = "SELECT p.id, p.nombre, p.descripcion FROM programas p $where ORDER BY p.id";
        $res = pg_query_params($this->conn, $sql, $params);
        if (!$res){ $this->logPg('list', $sql); return new WP_Error('db_query_failed','Error listando programas',[ 'status'=>500 ]); }
        $rows = pg_fetch_all($res) ?: []; pg_free_result($res);
        if (!$includeAll){
            return array_map(function($r){ return [ 'id'=>intval($r['id']), 'nombre'=>$r['nombre'], 'descripcion'=>$r['descripcion'] ]; }, $rows);
        }
        $out = [];
        foreach ($rows as $r){ $out[] = $this->get($r['id']); }
        return $out;
    }

    public function get($id, $version = null){
        $sql = "SELECT id, nombre, descripcion, COALESCE(current_version,1) AS current_version FROM programas WHERE id=$1";
        $res = pg_query_params($this->conn, $sql, [ intval($id) ]);
        if (!$res){ $this->logPg('get', $sql); return new WP_Error('db_query_failed','Error obteniendo programa',[ 'status'=>500 ]); }
        $row = pg_fetch_assoc($res); pg_free_result($res);
        if (!$row) return new WP_Error('not_found','Programa no encontrado',[ 'status'=>404 ]);
        $programaId = intval($row['id']);
        $ver = $version ? intval($version) : intval($row['current_version']);
        // niveles
        $nivSql = "SELECT id, nombre FROM niveles_programas WHERE programa_id=$1";
        $nivParams = [ $programaId ];
        if ($this->hasColumn('niveles_programas','version')){ $nivSql .= " AND version=$2"; $nivParams[] = $ver; }
        $nivSql .= " ORDER BY id";
        $qN = pg_query_params($this->conn, $nivSql, $nivParams);
        if (!$qN){ $this->logPg('get.niveles'); return new WP_Error('db_query_failed','Error obteniendo niveles',[ 'status'=>500 ]); }
        $niveles = [];
        while($n = pg_fetch_assoc($qN)){
            $nivelId = intval($n['id']);
            $sqlC = "SELECT pc.curso_id, c.nombre, c.descripcion, pc.consecutivo FROM programas_cursos pc JOIN cursos c ON pc.curso_id=c.id WHERE pc.programa_id=$1 AND pc.nivel_id=$2";
            $paramsC = [ $programaId, $nivelId ];
            if ($this->hasColumn('programas_cursos','version')){ $sqlC .= " AND pc.version=$3"; $paramsC[] = $ver; }
            $sqlC .= " ORDER BY pc.consecutivo";
            $qC = pg_query_params($this->conn, $sqlC, $paramsC);
            if (!$qC){ $this->logPg('get.cursosNivel'); return new WP_Error('db_query_failed','Error obteniendo cursos de nivel',[ 'status'=>500 ]); }
            $cursos = []; while($c = pg_fetch_assoc($qC)){ $cursos[] = [ 'id'=>intval($c['curso_id']), 'nombre'=>$c['nombre'], 'descripcion'=>$c['descripcion'], 'consecutivo'=>intval($c['consecutivo']) ]; } pg_free_result($qC);
            $niveles[] = [ 'id'=>$nivelId, 'nombre'=>$n['nombre'], 'cursos'=>$cursos ];
        }
        pg_free_result($qN);
        // cursos sin nivel
        $sqlS = "SELECT pc.curso_id, c.nombre, c.descripcion, pc.consecutivo FROM programas_cursos pc JOIN cursos c ON pc.curso_id=c.id WHERE pc.programa_id=$1 AND pc.nivel_id IS NULL";
        $paramsS = [ $programaId ];
        if ($this->hasColumn('programas_cursos','version')){ $sqlS .= " AND pc.version=$2"; $paramsS[] = $ver; }
        $sqlS .= " ORDER BY pc.consecutivo";
        $qS = pg_query_params($this->conn, $sqlS, $paramsS);
        if (!$qS){ $this->logPg('get.cursosSinNivel'); return new WP_Error('db_query_failed','Error obteniendo cursos sin nivel',[ 'status'=>500 ]); }
        $sinNivel = []; while($s = pg_fetch_assoc($qS)){ $sinNivel[] = [ 'id'=>intval($s['curso_id']), 'nombre'=>$s['nombre'], 'descripcion'=>$s['descripcion'], 'consecutivo'=>intval($s['consecutivo']) ]; } pg_free_result($qS);
        // prerequisitos
        $qP = pg_query_params($this->conn, "SELECT pp.prerequisito_id, p2.nombre FROM programas_prerequisitos pp JOIN programas p2 ON pp.prerequisito_id=p2.id WHERE pp.programa_id=$1 ORDER BY pp.prerequisito_id", [ $programaId ]);
        if (!$qP){ $this->logPg('get.prereq'); return new WP_Error('db_query_failed','Error obteniendo prerequisitos',[ 'status'=>500 ]); }
        $pre = []; while($p = pg_fetch_assoc($qP)){ $pre[] = [ 'id'=>intval($p['prerequisito_id']), 'nombre'=>$p['nombre'] ]; } pg_free_result($qP);
        return [ 'id'=>$programaId, 'nombre'=>$row['nombre'], 'descripcion'=>$row['descripcion'], 'version'=>$ver, 'niveles'=>$niveles, 'cursosSinNivel'=>$sinNivel, 'prerequisitos'=>$pre ];
    }

    public function create($payload){
        $nombre = trim(strval($payload['nombre'] ?? ''));
        $descripcion = trim(strval($payload['descripcion'] ?? ''));
        if ($nombre === '') return new WP_Error('invalid_payload','Nombre requerido',[ 'status'=>422 ]);
        // Validación: consecutivos únicos en todo el programa (incluye niveles y sin nivel)
        $allCons = [];
        foreach (($payload['niveles'] ?? []) as $nivel){
            foreach (($nivel['cursos'] ?? []) as $curso){
                $c = intval($curso['consecutivo'] ?? 0); if ($c <= 0) continue; $allCons[] = $c;
            }
        }
        foreach (($payload['cursosSinNivel'] ?? $payload['cursos_sin_nivel'] ?? []) as $csn){
            $c = intval($csn['consecutivo'] ?? 0); if ($c <= 0) continue; $allCons[] = $c;
        }
        $dups = array_unique(array_diff_assoc($allCons, array_unique($allCons)));
        if (!empty($dups)){
            return new WP_Error('invalid_payload','Consecutivos duplicados en cursos del programa',[ 'status'=>422, 'duplicates'=>array_values($dups) ]);
        }
        pg_query($this->conn, 'BEGIN');
        $q = pg_query_params($this->conn, "INSERT INTO programas (nombre, descripcion) VALUES ($1,$2) RETURNING id", [ $nombre, $descripcion ]);
        if (!$q){ pg_query($this->conn,'ROLLBACK'); $this->logPg('create.insert'); return new WP_Error('db_update_failed','Error creando programa',[ 'status'=>500 ]); }
        $programaId = intval(pg_fetch_result($q, 0, 0)); pg_free_result($q);
        // niveles
        foreach (($payload['niveles'] ?? []) as $nivel){
            $nivelNombre = trim(strval($nivel['nombre'] ?? $nivel['nombre_nivel'] ?? ''));
            if ($nivelNombre === '') continue;
            $qn = pg_query_params($this->conn, "INSERT INTO niveles_programas (programa_id, nombre) VALUES ($1,$2) RETURNING id", [ $programaId, $nivelNombre ]);
            if (!$qn){ pg_query($this->conn,'ROLLBACK'); $this->logPg('create.nivel'); return new WP_Error('db_update_failed','Error creando nivel',[ 'status'=>500 ]); }
            $nivelId = intval(pg_fetch_result($qn, 0, 0)); pg_free_result($qn);
            foreach (($nivel['cursos'] ?? []) as $curso){
                $cid = intval($curso['id'] ?? 0); $cons = intval($curso['consecutivo'] ?? 0);
                $qc = pg_query_params($this->conn, "INSERT INTO programas_cursos (programa_id, curso_id, nivel_id, consecutivo) VALUES ($1,$2,$3,$4)", [ $programaId, $cid, $nivelId, $cons ]);
                if (!$qc){ pg_query($this->conn,'ROLLBACK'); $this->logPg('create.cursoNivel'); return new WP_Error('db_update_failed','Error asociando curso',[ 'status'=>500 ]); }
                pg_free_result($qc);
            }
        }
        foreach (($payload['cursosSinNivel'] ?? $payload['cursos_sin_nivel'] ?? []) as $csn){
            $cid = intval($csn['id'] ?? 0); $cons = intval($csn['consecutivo'] ?? 0);
            $qs = pg_query_params($this->conn, "INSERT INTO programas_cursos (programa_id, curso_id, nivel_id, consecutivo) VALUES ($1,$2,NULL,$3)", [ $programaId, $cid, $cons ]);
            if (!$qs){ pg_query($this->conn,'ROLLBACK'); $this->logPg('create.cursoSinNivel'); return new WP_Error('db_update_failed','Error asociando curso sin nivel',[ 'status'=>500 ]); }
            pg_free_result($qs);
        }
        foreach (($payload['prerequisitos'] ?? []) as $pr){
            $pid = intval($pr['id'] ?? 0);
            $qp = pg_query_params($this->conn, "INSERT INTO programas_prerequisitos (programa_id, prerequisito_id) VALUES ($1,$2)", [ $programaId, $pid ]);
            if (!$qp){ pg_query($this->conn,'ROLLBACK'); $this->logPg('create.prereq'); return new WP_Error('db_update_failed','Error asociando prerequisito',[ 'status'=>500 ]); }
            pg_free_result($qp);
        }
        pg_query($this->conn, 'COMMIT');
        return $programaId;
    }

    public function update($id, $payload){
        $nombre = isset($payload['nombre']) ? trim(strval($payload['nombre'])) : null;
        $descripcion = isset($payload['descripcion']) ? trim(strval($payload['descripcion'])) : null;
        pg_query($this->conn, 'BEGIN');
        // Si solo se actualiza nombre/descripcion (sin estructura), update simple
        if (($nombre !== null || $descripcion !== null) && !isset($payload['niveles']) && !isset($payload['cursosSinNivel']) && !isset($payload['cursos_sin_nivel'])){
            $q = pg_query_params($this->conn, "UPDATE programas SET nombre=COALESCE($1,nombre), descripcion=COALESCE($2,descripcion), updated_at=NOW() WHERE id=$3", [ $nombre, $descripcion, intval($id) ]);
            if (!$q){ pg_query($this->conn,'ROLLBACK'); $this->logPg('update.programa'); return new WP_Error('db_update_failed','Error actualizando programa',[ 'status'=>500 ]); }
            pg_free_result($q);
            pg_query($this->conn,'COMMIT');
            return [ 'updated'=>true ];
        }
        // resync niveles/cursos si vienen -> crear nueva versión
        if (isset($payload['niveles']) || isset($payload['cursosSinNivel']) || isset($payload['cursos_sin_nivel'])){
            // Obtener versión actual y calcular siguiente
            $qv = pg_query_params($this->conn, "SELECT COALESCE(current_version,1) FROM programas WHERE id=$1", [ intval($id) ]);
            if (!$qv){ pg_query($this->conn,'ROLLBACK'); $this->logPg('update.getVersion'); return new WP_Error('db_query_failed','Error obteniendo versión actual',[ 'status'=>500 ]); }
            $cv = intval(pg_fetch_result($qv, 0, 0)); pg_free_result($qv);
            $next = $cv + 1;
            // Insertar niveles de la nueva versión, manteniendo el orden del payload
            $newNivelIds = [];
            $nivelesPayload = ($payload['niveles'] ?? []);
            foreach ($nivelesPayload as $idx => $nivel){
                $nivelNombre = trim(strval($nivel['nombre'] ?? $nivel['nombre_nivel'] ?? ''));
                if ($nivelNombre === '') { $newNivelIds[$idx] = null; continue; }
                $qn = pg_query_params($this->conn, "INSERT INTO niveles_programas (programa_id, nombre, version) VALUES ($1,$2,$3) RETURNING id", [ intval($id), $nivelNombre, $next ]);
                if (!$qn){ pg_query($this->conn,'ROLLBACK'); $this->logPg('update.insNivelV'); return new WP_Error('db_update_failed','Error creando nivel',[ 'status'=>500 ]); }
                $newNivelIds[$idx] = intval(pg_fetch_result($qn, 0, 0)); pg_free_result($qn);
            }
            // Insertar cursos por nivel en la versión next
            foreach ($nivelesPayload as $idx => $nivel){
                $newNivelId = $newNivelIds[$idx];
                if (!$newNivelId) continue;
                foreach (($nivel['cursos'] ?? []) as $curso){
                    $cid = intval($curso['id'] ?? 0); $cons = intval($curso['consecutivo'] ?? 0);
                    $qc = pg_query_params($this->conn, "INSERT INTO programas_cursos (programa_id, curso_id, nivel_id, consecutivo, version) VALUES ($1,$2,$3,$4,$5)", [ intval($id), $cid, $newNivelId, $cons, $next ]);
                    if (!$qc){ pg_query($this->conn,'ROLLBACK'); $this->logPg('update.cursoNivelV'); return new WP_Error('db_update_failed','Error asociando curso',[ 'status'=>500 ]); }
                    pg_free_result($qc);
                }
            }
            // cursos sin nivel
            foreach (($payload['cursosSinNivel'] ?? $payload['cursos_sin_nivel'] ?? []) as $csn){
                $cid = intval($csn['id'] ?? 0); $cons = intval($csn['consecutivo'] ?? 0);
                $qs = pg_query_params($this->conn, "INSERT INTO programas_cursos (programa_id, curso_id, nivel_id, consecutivo, version) VALUES ($1,$2,NULL,$3,$4)", [ intval($id), $cid, $cons, $next ]);
                if (!$qs){ pg_query($this->conn,'ROLLBACK'); $this->logPg('update.cursoSinNivelV'); return new WP_Error('db_update_failed','Error asociando curso sin nivel',[ 'status'=>500 ]); }
                pg_free_result($qs);
            }
            // Actualizar nombre/descripcion si vienen
            if ($nombre !== null || $descripcion !== null){
                $q = pg_query_params($this->conn, "UPDATE programas SET nombre=COALESCE($1,nombre), descripcion=COALESCE($2,descripcion), updated_at=NOW() WHERE id=$3", [ $nombre, $descripcion, intval($id) ]);
                if (!$q){ pg_query($this->conn,'ROLLBACK'); $this->logPg('update.programaNombre'); return new WP_Error('db_update_failed','Error actualizando programa',[ 'status'=>500 ]); }
                pg_free_result($q);
            }
            // Actualizar versión vigente
            $qu = pg_query_params($this->conn, "UPDATE programas SET current_version=$1, updated_at=NOW() WHERE id=$2", [ $next, intval($id) ]);
            if (!$qu){ pg_query($this->conn,'ROLLBACK'); $this->logPg('update.setCurrentVersion'); return new WP_Error('db_update_failed','Error actualizando versión',[ 'status'=>500 ]); }
            pg_free_result($qu);
            pg_query($this->conn,'COMMIT');
            return [ 'updated'=>true, 'newVersion'=>$next ];
        }
        pg_query($this->conn,'COMMIT');
        return [ 'updated'=>true ];
    }

    public function delete($id, $hard = false){
        if ($hard){
            pg_query($this->conn,'BEGIN');
            $q1 = pg_query_params($this->conn, "DELETE FROM programas_prerequisitos WHERE programa_id=$1 OR prerequisito_id=$1", [ intval($id) ]);
            $q2 = pg_query_params($this->conn, "DELETE FROM programas_cursos WHERE programa_id=$1", [ intval($id) ]);
            $q3 = pg_query_params($this->conn, "DELETE FROM niveles_programas WHERE programa_id=$1", [ intval($id) ]);
            $q4 = pg_query_params($this->conn, "DELETE FROM programas WHERE id=$1", [ intval($id) ]);
            if (!$q1 || !$q2 || !$q3 || !$q4){ pg_query($this->conn,'ROLLBACK'); $this->logPg('delete.hard'); return new WP_Error('db_update_failed','Error eliminando programa',[ 'status'=>500 ]); }
            pg_query($this->conn,'COMMIT'); return true;
        }
        if ($this->hasColumn('programas','deleted_at')){
            $q = pg_query_params($this->conn, "UPDATE programas SET deleted_at=NOW(), updated_at=NOW() WHERE id=$1", [ intval($id) ]);
            if (!$q){ $this->logPg('delete.soft'); return new WP_Error('db_update_failed','Error aplicando soft delete',[ 'status'=>500 ]); }
            pg_free_result($q); return true;
        }
        // si no hay columna deleted_at, degradar a hard delete seguro
        $qd = pg_query_params($this->conn, "DELETE FROM programas WHERE id=$1", [ intval($id) ]);
        if (!$qd){ $this->logPg('delete.noSoft'); return new WP_Error('db_update_failed','Error eliminando programa',[ 'status'=>500 ]); }
        pg_free_result($qd); return true;
    }

    public function assign($idPrograma, $idEstudiante = null, $idContacto = null, $remove = false){
        if (($idEstudiante && $idContacto) || (!$idEstudiante && !$idContacto)){
            return new WP_Error('invalid_payload','Debe enviar estudianteId o contactoId (uno)',[ 'status'=>422 ]);
        }
        $sql = $remove
            ? ( $idEstudiante ? "DELETE FROM programas_asignaciones WHERE programa_id=$1 AND estudiante_id=$2" : "DELETE FROM programas_asignaciones WHERE programa_id=$1 AND contacto_id=$2" )
            : ( $idEstudiante ? "INSERT INTO programas_asignaciones (programa_id, estudiante_id) VALUES ($1,$2)" : "INSERT INTO programas_asignaciones (programa_id, contacto_id) VALUES ($1,$2)" );
        $idRef = $idEstudiante ? intval($idEstudiante) : intval($idContacto);
        $res = pg_query_params($this->conn, $sql, [ intval($idPrograma), $idRef ]);
        if (!$res){ $this->logPg('assign'); return new WP_Error('db_update_failed','No fue posible actualizar la asignación',[ 'status'=>500 ]); }
        pg_free_result($res); return true;
    }

    public function upgradeAssignments($idPrograma, $toVersion, $scope='all'){
        $where = '';
        if ($scope === 'students') { $where = ' AND estudiante_id IS NOT NULL'; }
        else if ($scope === 'contacts') { $where = ' AND contacto_id IS NOT NULL'; }
        $sql = "UPDATE programas_asignaciones SET version=$1 WHERE programa_id=$2 AND (version IS NULL OR version<>$1)".$where;
        $res = pg_query_params($this->conn, $sql, [ intval($toVersion), intval($idPrograma) ]);
        if (!$res){ $this->logPg('upgradeAssignments'); return new WP_Error('db_update_failed','Error actualizando asignaciones',[ 'status'=>500 ]); }
        $cnt = pg_affected_rows($res); pg_free_result($res);
        return [ 'updated' => intval($cnt) ];
    }
}


