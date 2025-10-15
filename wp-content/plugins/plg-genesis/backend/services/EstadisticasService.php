<?php
if (!defined('ABSPATH')) { exit; }

class PlgGenesis_EstadisticasService {
	private $repo;
	public function __construct($repo) { $this->repo = $repo; }
	public function resumen($month, $year) {
		$month = $month ?: date('m');
		$year  = $year ?: date('Y');
		return $this->repo->getResumen($month, $year);
	}

	/**
	 * Obtiene el informe anual
	 * @param int|null $year Año del informe (por defecto año actual)
	 * @return array|WP_Error
	 */
	public function informeAnual($year = null) {
		$year = $year ?: date('Y');
		$year = intval($year);

		// Validar año
		$currentYear = intval(date('Y'));
		if ($year < 2000 || $year > $currentYear) {
			return new WP_Error('invalid_year', 'El año debe estar entre 2000 y el año actual.', ['status' => 400]);
		}

		return $this->repo->getInformeAnual($year);
	}
}