<?php
if (!defined('ABSPATH')) { exit; }

/**
 * ContactResolver - Vincula usuarios WordPress con contactos PostgreSQL
 * 
 * Maneja la lógica de asociación entre usuarios de WordPress y contactos
 * de la base de datos PostgreSQL, y valida permisos para contact_viewer.
 */
class PlgGenesis_ContactResolver {
	
	/**
	 * Obtiene el ID del contacto asociado a un usuario WordPress
	 * 
	 * @param int $user_id ID del usuario de WordPress
	 * @return int|WP_Error ID del contacto o error si no está asociado
	 */
	public static function resolve_user_contact($user_id) {
		$contacto_id = get_user_meta($user_id, 'contacto_id', true);
		
		if (empty($contacto_id)) {
			return new WP_Error(
				'no_contact', 
				'Usuario no está asociado a ningún contacto', 
				['status' => 403]
			);
		}
		
		return intval($contacto_id);
	}
	
	/**
	 * Verifica si un usuario tiene el rol de contact_viewer
	 * 
	 * @param int $user_id ID del usuario de WordPress
	 * @return bool True si es contact_viewer, false si no
	 */
	public static function is_contact_viewer($user_id) {
		$user = get_userdata($user_id);
		if (!$user) {
			return false;
		}
		
		return in_array('plg_contact_viewer', $user->roles);
	}
	
	/**
	 * Verifica si un usuario puede acceder a un contacto específico
	 * 
	 * - Contact viewers solo pueden acceder a su propio contacto
	 * - Otros roles tienen acceso completo
	 * 
	 * @param int $user_id ID del usuario de WordPress
	 * @param int $contacto_id ID del contacto en PostgreSQL
	 * @return bool|WP_Error True si tiene acceso, WP_Error si no
	 */
	public static function can_access_contact($user_id, $contacto_id) {
		// Si no es contact_viewer, tiene acceso completo
		if (!self::is_contact_viewer($user_id)) {
			return true;
		}
		
		// Obtener el contacto propio del usuario
		$own_contacto_id = self::resolve_user_contact($user_id);
		
		if (is_wp_error($own_contacto_id)) {
			return $own_contacto_id;
		}
		
		// Verificar que sea el mismo contacto
		if ($own_contacto_id !== intval($contacto_id)) {
			return new WP_Error(
				'forbidden', 
				'No tienes permiso para acceder a este contacto', 
				['status' => 403]
			);
		}
		
		return true;
	}
	
	/**
	 * Verifica si un usuario puede acceder a un estudiante específico
	 * 
	 * - Contact viewers solo pueden acceder a estudiantes de su contacto
	 * - Otros roles tienen acceso completo
	 * 
	 * @param int $user_id ID del usuario de WordPress
	 * @param int $estudiante_contacto_id ID del contacto del estudiante en PostgreSQL
	 * @return bool|WP_Error True si tiene acceso, WP_Error si no
	 */
	public static function can_access_student($user_id, $estudiante_contacto_id) {
		// Si no es contact_viewer, tiene acceso completo
		if (!self::is_contact_viewer($user_id)) {
			return true;
		}
		
		// Obtener el contacto propio del usuario
		$own_contacto_id = self::resolve_user_contact($user_id);
		
		if (is_wp_error($own_contacto_id)) {
			return $own_contacto_id;
		}
		
		// Verificar que el estudiante pertenezca a su contacto
		if ($own_contacto_id !== intval($estudiante_contacto_id)) {
			return new WP_Error(
				'forbidden', 
				'No tienes permiso para acceder a este estudiante', 
				['status' => 403]
			);
		}
		
		return true;
	}
}

