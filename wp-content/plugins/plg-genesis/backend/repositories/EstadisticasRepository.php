<?php
if (!defined('ABSPATH')) { exit; }

class PlgGenesis_EstadisticasRepository {
	private $conn;
	public function __construct($connection) { $this->conn = $connection; }

	public function getResumen($month, $year) {
		$month = intval($month);
		$year  = intval($year);

		// Estudiantes activos
		$q1 = pg_query_params($this->conn, "SELECT COUNT(DISTINCT e.id)
			FROM estudiantes e
			INNER JOIN estudiantes_cursos ec ON e.id = ec.estudiante_id
			WHERE ec.porcentaje > 70", []);
		$estudiantesActivos = $q1 ? intval(pg_fetch_result($q1, 0, 0)) : 0;
		if ($q1) pg_free_result($q1);

		// Cursos en el mes/año
		$q2 = pg_query_params($this->conn, "SELECT COUNT(*)
			FROM estudiantes_cursos
			WHERE EXTRACT(MONTH FROM fecha) = $1 AND EXTRACT(YEAR FROM fecha) = $2", [ $month, $year ]);
		$cursosMes = $q2 ? intval(pg_fetch_result($q2, 0, 0)) : 0;
		if ($q2) pg_free_result($q2);

		// Cursos completados (>70)
		$q3 = pg_query_params($this->conn, "SELECT COUNT(*) FROM estudiantes_cursos WHERE porcentaje > 70", []);
		$cursosCompletados = $q3 ? intval(pg_fetch_result($q3, 0, 0)) : 0;
		if ($q3) pg_free_result($q3);

		// Contactos
		$q4 = pg_query_params($this->conn, "SELECT COUNT(*) FROM contactos", []);
		$contactosActivos = $q4 ? intval(pg_fetch_result($q4, 0, 0)) : 0;
		if ($q4) pg_free_result($q4);

		// Actividad reciente (5)
		$q5 = pg_query_params($this->conn, "(SELECT 'estudiante' as tipo, CONCAT('Nuevo estudiante: ', nombre1, ' ', apellido1) as texto, fecha_registro as fecha FROM estudiantes ORDER BY fecha_registro DESC LIMIT 2)
			UNION ALL
			(SELECT 'curso' as tipo, CONCAT('Curso completado: ', c.nombre) as texto, ec.fecha as fecha FROM estudiantes_cursos ec INNER JOIN cursos c ON ec.curso_id = c.id WHERE ec.porcentaje > 70 ORDER BY ec.fecha DESC LIMIT 2)
			UNION ALL
			(SELECT 'contacto' as tipo, CONCAT('Nuevo contacto: ', nombre) as texto, fecha_registro as fecha FROM contactos ORDER BY fecha_registro DESC LIMIT 1)
			ORDER BY fecha DESC LIMIT 5", []);
		$actividad = [];
		if ($q5) {
			while ($row = pg_fetch_object($q5)) {
				$fecha = strtotime($row->fecha);
				$ahora = time();
				$diff = $ahora - $fecha;
				if ($diff < 60) { $row->tiempo = 'Hace unos segundos'; }
				elseif ($diff < 3600) { $m = floor($diff/60); $row->tiempo = 'Hace '.$m.' minuto'.($m!=1?'s':''); }
				elseif ($diff < 86400) { $h = floor($diff/3600); $row->tiempo = 'Hace '.$h.' hora'.($h!=1?'s':''); }
				else { $d = floor($diff/86400); $row->tiempo = 'Hace '.$d.' día'.($d!=1?'s':''); }
				$actividad[] = $row;
			}
			pg_free_result($q5);
		}

		return [
			'estudiantesActivos' => $estudiantesActivos,
			'cursosMes' => $cursosMes,
			'cursosCompletados' => $cursosCompletados,
			'contactosActivos' => $contactosActivos,
			'actividades' => $actividad,
		];
	}
}