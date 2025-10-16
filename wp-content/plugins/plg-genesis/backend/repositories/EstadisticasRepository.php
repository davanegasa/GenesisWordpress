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

	/**
	 * Obtiene el informe anual de estudiantes activos, cursos corregidos y contactos
	 * @param int $year Año del informe
	 * @return array|WP_Error
	 */
	public function getInformeAnual($year) {
		$year = intval($year);
		$currentYear = intval(date('Y'));
		$currentMonth = date('m');

		// Validar que el año no sea mayor al actual
		if ($year > $currentYear) {
			return new WP_Error('invalid_year', 'No se permiten informes para años futuros.', ['status' => 400]);
		}

		// Restringir la generación de meses si es el año actual
		$endDate = $year === $currentYear ? "$year-$currentMonth-01" : "$year-12-01";

		$query = "
WITH meses AS (
    SELECT GENERATE_SERIES($1::DATE, $2::DATE, '1 month'::INTERVAL) AS mes
),
estudiantes_activos AS (
    SELECT 
        TO_CHAR(mes, 'YYYY-MM') AS mes,
        COUNT(DISTINCT ec.estudiante_id) AS activos
    FROM 
        meses
    LEFT JOIN 
        estudiantes_cursos ec ON ec.fecha BETWEEN (mes - INTERVAL '1 year') AND (mes + INTERVAL '1 month' - INTERVAL '1 day')
    GROUP BY mes
),
cursos_correjidos AS (
    SELECT 
        TO_CHAR(ec.fecha, 'YYYY-MM') AS mes,
        COUNT(ec.id) AS corregidos
    FROM 
        estudiantes_cursos ec
    WHERE 
        ec.fecha BETWEEN $1::DATE AND $2::DATE + INTERVAL '1 month' - INTERVAL '1 day'
    GROUP BY TO_CHAR(ec.fecha, 'YYYY-MM')
),
estudiantes_registrados AS (
    SELECT 
        TO_CHAR(estudiantes.fecha_registro, 'YYYY-MM') AS mes,
        COUNT(*) AS nuevos_estudiantes
    FROM 
        estudiantes
    WHERE 
        estudiantes.fecha_registro BETWEEN $1::DATE AND $2::DATE + INTERVAL '1 month' - INTERVAL '1 day'
    GROUP BY TO_CHAR(estudiantes.fecha_registro, 'YYYY-MM')
),
contactos_activos AS (
    SELECT 
        TO_CHAR(mes, 'YYYY-MM') AS mes,
        COUNT(DISTINCT c.id) AS contactos_activos
    FROM 
        meses
    LEFT JOIN 
        estudiantes e ON e.id_contacto IS NOT NULL
    LEFT JOIN 
        estudiantes_cursos ec ON ec.estudiante_id = e.id
    LEFT JOIN 
        contactos c ON c.id = e.id_contacto
    WHERE 
        ec.fecha BETWEEN (mes - INTERVAL '1 year') AND (mes + INTERVAL '1 month' - INTERVAL '1 day')
    GROUP BY mes
),
contactos_nuevos AS (
    SELECT 
        TO_CHAR(contactos.fecha_registro, 'YYYY-MM') AS mes,
        COUNT(*) AS nuevos_contactos
    FROM 
        contactos
    WHERE 
        contactos.fecha_registro BETWEEN $1::DATE AND $2::DATE + INTERVAL '1 month' - INTERVAL '1 day'
    GROUP BY TO_CHAR(contactos.fecha_registro, 'YYYY-MM')
)
SELECT 
    COALESCE(ea.mes, cc.mes, er.mes, ca.mes, cn.mes) AS mes,
    COALESCE(ea.activos, 0) AS estudiantes_activos,
    COALESCE(cc.corregidos, 0) AS cursos_correjidos,
    COALESCE(er.nuevos_estudiantes, 0) AS estudiantes_registrados,
    COALESCE(ca.contactos_activos, 0) AS contactos_activos,
    COALESCE(cn.nuevos_contactos, 0) AS contactos_registrados
FROM 
    estudiantes_activos ea
FULL OUTER JOIN 
    cursos_correjidos cc ON ea.mes = cc.mes
FULL OUTER JOIN 
    estudiantes_registrados er ON COALESCE(ea.mes, cc.mes) = er.mes
FULL OUTER JOIN 
    contactos_activos ca ON COALESCE(ea.mes, cc.mes, er.mes) = ca.mes
FULL OUTER JOIN 
    contactos_nuevos cn ON COALESCE(ea.mes, cc.mes, er.mes, ca.mes) = cn.mes
ORDER BY 
    mes;
		";

		$startDate = "$year-01-01";
		$result = pg_query_params($this->conn, $query, [$startDate, $endDate]);

		if (!$result) {
			$error = pg_last_error($this->conn);
			return new WP_Error('query_error', 'Error al ejecutar la consulta: ' . $error, ['status' => 500]);
		}

		$data = pg_fetch_all($result);
		pg_free_result($result);

		return $data ?: [];
	}
}