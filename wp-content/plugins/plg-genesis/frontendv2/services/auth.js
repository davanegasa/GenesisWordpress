/**
 * Servicio de autenticación y permisos
 * Maneja capabilities del usuario actual y control de acceso
 */

const AuthService = {
	/**
	 * Capabilities del usuario actual (se cargan al inicio)
	 */
	userCapabilities: null,
	userInfo: null,

	/**
	 * Inicializa el servicio obteniendo las capabilities del usuario
	 */
	async init() {
		try {
			// Obtener info del usuario actual desde WordPress
			const user = window.wpUserData || {};
			this.userInfo = {
				id: user.id || 0,
				name: user.name || 'Usuario',
				email: user.email || '',
				roles: user.roles || [],
				office: user.office || null,
			};

			// Las capabilities vienen en el objeto global de WordPress
			this.userCapabilities = user.capabilities || {};
			
			return true;
		} catch (error) {
			console.error('Error inicializando AuthService:', error);
			return false;
		}
	},

	/**
	 * Verifica si el usuario tiene una capability específica
	 * @param {string} capability - La capability a verificar (ej: 'plg_view_students')
	 * @returns {boolean}
	 */
	can(capability) {
		if (!this.userCapabilities) {
			console.warn('AuthService no inicializado');
			return false;
		}
		return this.userCapabilities[capability] === true;
	},

	/**
	 * Verifica si el usuario tiene ALGUNA de las capabilities dadas
	 * @param {string[]} capabilities - Array de capabilities
	 * @returns {boolean}
	 */
	canAny(capabilities) {
		return capabilities.some(cap => this.can(cap));
	},

	/**
	 * Verifica si el usuario tiene TODAS las capabilities dadas
	 * @param {string[]} capabilities - Array de capabilities
	 * @returns {boolean}
	 */
	canAll(capabilities) {
		return capabilities.every(cap => this.can(cap));
	},

	/**
	 * Verifica si el usuario tiene un rol específico
	 * @param {string} role - El slug del rol (ej: 'plg_super_admin')
	 * @returns {boolean}
	 */
	hasRole(role) {
		return this.userInfo?.roles?.includes(role) || false;
	},

	/**
	 * Verifica si es Super Admin
	 */
	isSuperAdmin() {
		return this.hasRole('plg_super_admin') || this.hasRole('administrator');
	},

	/**
	 * Verifica si es Office Manager
	 */
	isOfficeManager() {
		return this.hasRole('plg_office_manager');
	},

	/**
	 * Verifica si puede cambiar de oficina (solo Super Admin)
	 */
	canSwitchOffice() {
		return this.can('plg_switch_office');
	},

	/**
	 * Obtiene la oficina actual del usuario
	 */
	getOffice() {
		return this.userInfo?.office || null;
	},

	/**
	 * Obtiene información del usuario actual
	 */
	getUser() {
		return this.userInfo;
	},
};

export default AuthService;

