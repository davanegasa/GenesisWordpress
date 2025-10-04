<?php
if (!defined('ABSPATH')) { exit; }

class PlgGenesis_ThemeValidator {
    private static $allowedKeys = [
        'bg', 'text', 'accent', 'sidebarBg', 'sidebarText', 'cardBg',
        'border', 'mutedText', 'success', 'warning', 'danger', 'info'
    ];

	public static function validate($payload) {
		if (!is_array($payload)) {
			return new WP_Error('invalid_payload', 'Formato inválido', [ 'status' => 400 ]);
		}
		$validated = [];
		foreach ($payload as $key => $value) {
			if (!in_array($key, self::$allowedKeys, true)) {
				return new WP_Error('invalid_key', 'Llave de tema no permitida: ' . $key, [ 'status' => 422 ]);
			}
			if (!is_string($value) || !preg_match('/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', $value)) {
				return new WP_Error('invalid_color', 'Color inválido para ' . $key, [ 'status' => 422 ]);
			}
			$validated[$key] = strtoupper($value);
		}
		return $validated;
	}
}