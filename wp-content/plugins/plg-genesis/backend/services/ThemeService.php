<?php
if (!defined('ABSPATH')) { exit; }

require_once dirname(__FILE__, 2) . '/validation/ThemeValidator.php';

class PlgGenesis_ThemeService {
	private $repo;
	public function __construct($repo) { $this->repo = $repo; }

	public function get($office) {
		return $this->repo->getByOffice($office);
	}

	public function update($office, $payload) {
		$validated = PlgGenesis_ThemeValidator::validate($payload);
		if (is_wp_error($validated)) { return $validated; }
		$this->repo->saveForOffice($office, $validated);
		return $validated;
	}

	public function reset($office) {
		$this->repo->deleteForOffice($office);
        return [
            'bg' => '#E2E8F0',
            'text' => '#0A0F1E',
            'accent' => '#0B3B8C',
            'sidebarBg' => '#0A1224',
            'sidebarText' => '#F1F5F9',
            'cardBg' => '#FFFFFF',
            'border' => '#94A3B8',
            'mutedText' => '#475569',
            'success' => '#16A34A',
            'warning' => '#D97706',
            'danger' => '#DC2626',
            'info' => '#1E40AF',
        ];
	}
}