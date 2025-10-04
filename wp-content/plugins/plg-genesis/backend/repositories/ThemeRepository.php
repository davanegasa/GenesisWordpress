<?php
if (!defined('ABSPATH')) { exit; }

class PlgGenesis_ThemeRepository {
	private static function option_key($office) { return 'plg_genesis_theme_' . $office; }

	public function getByOffice($office) {
		$key = self::option_key($office);
		$raw = get_option($key, '{}');
		$data = json_decode(is_string($raw) ? $raw : '{}', true);
		return is_array($data) ? $data : [];
	}

	public function saveForOffice($office, $theme) {
		$key = self::option_key($office);
		return update_option($key, wp_json_encode($theme), false);
	}

	public function deleteForOffice($office) {
		$key = self::option_key($office);
		return delete_option($key);
	}
}