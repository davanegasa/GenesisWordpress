<?php
if (!defined('ABSPATH')) { exit; }

class PlgGenesis_OfficeResolver {
	public static function resolve_user_office($user_id) {
		$office = get_user_meta($user_id, 'oficina', true);
		if (!$office || !is_string($office)) {
			return new WP_Error('office_not_configured', 'Oficina no configurada para el usuario actual', [ 'status' => 400 ]);
		}
		return $office;
	}
}