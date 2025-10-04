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
}