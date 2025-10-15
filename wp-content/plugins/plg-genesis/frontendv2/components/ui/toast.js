/**
 * Sistema de notificaciones toast
 */

let toastContainer = null;

/**
 * Inicializa el contenedor de toasts
 */
function initToastContainer() {
	if (toastContainer) return;
	
	toastContainer = document.createElement('div');
	toastContainer.id = 'toast-container';
	toastContainer.style.cssText = `
		position: fixed;
		top: 20px;
		right: 20px;
		z-index: 10000;
		display: flex;
		flex-direction: column;
		gap: 10px;
		max-width: 400px;
	`;
	document.body.appendChild(toastContainer);
}

/**
 * Muestra un toast
 * @param {string} message - Mensaje a mostrar
 * @param {string} type - Tipo: 'success', 'error', 'warning', 'info'
 * @param {number} duration - Duración en ms (0 = no auto-cerrar)
 */
export function showToast(message, type = 'info', duration = 5000) {
	initToastContainer();
	
	const toast = document.createElement('div');
	toast.className = `toast toast-${type}`;
	
	const colors = {
		success: '#10b981',
		error: '#ef4444',
		warning: '#f59e0b',
		info: '#3b82f6',
		forbidden: '#f97316', // Naranja para 403
	};
	
	const icons = {
		success: '✓',
		error: '✕',
		warning: '⚠',
		info: 'ℹ',
		forbidden: '🚫',
	};
	
	toast.style.cssText = `
		background: ${colors[type] || colors.info};
		color: white;
		padding: 16px 20px;
		border-radius: 8px;
		box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
		display: flex;
		align-items: center;
		gap: 12px;
		font-size: 14px;
		font-weight: 500;
		animation: slideIn 0.3s ease-out;
		cursor: pointer;
		max-width: 100%;
		word-wrap: break-word;
	`;
	
	toast.innerHTML = `
		<span style="font-size: 18px; flex-shrink: 0;">${icons[type] || icons.info}</span>
		<span style="flex: 1;">${message}</span>
		<button style="background: transparent; border: none; color: white; cursor: pointer; font-size: 18px; padding: 0; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; border-radius: 4px; transition: background 0.2s;" onmouseover="this.style.background='rgba(0,0,0,0.1)'" onmouseout="this.style.background='transparent'">×</button>
	`;
	
	// Cerrar al hacer click en la X o en el toast
	const closeBtn = toast.querySelector('button');
	const closeToast = () => {
		toast.style.animation = 'slideOut 0.3s ease-out';
		setTimeout(() => {
			if (toast.parentElement) {
				toast.remove();
			}
		}, 300);
	};
	
	closeBtn.addEventListener('click', (e) => {
		e.stopPropagation();
		closeToast();
	});
	
	toast.addEventListener('click', closeToast);
	
	toastContainer.appendChild(toast);
	
	// Auto-cerrar después de la duración especificada
	if (duration > 0) {
		setTimeout(closeToast, duration);
	}
	
	return toast;
}

/**
 * Shortcuts para tipos comunes
 */
export const toast = {
	success: (message, duration) => showToast(message, 'success', duration),
	error: (message, duration) => showToast(message, 'error', duration),
	warning: (message, duration) => showToast(message, 'warning', duration),
	info: (message, duration) => showToast(message, 'info', duration),
	forbidden: (message, duration = 7000) => showToast(message, 'forbidden', duration),
};

// Agregar animaciones CSS
if (!document.getElementById('toast-animations')) {
	const style = document.createElement('style');
	style.id = 'toast-animations';
	style.textContent = `
		@keyframes slideIn {
			from {
				transform: translateX(400px);
				opacity: 0;
			}
			to {
				transform: translateX(0);
				opacity: 1;
			}
		}
		
		@keyframes slideOut {
			from {
				transform: translateX(0);
				opacity: 1;
			}
			to {
				transform: translateX(400px);
				opacity: 0;
			}
		}
	`;
	document.head.appendChild(style);
}

