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
		position: fixed !important;
		top: 20px !important;
		right: 20px !important;
		z-index: 999999 !important;
		display: flex !important;
		flex-direction: column !important;
		gap: 10px !important;
		max-width: 400px !important;
		pointer-events: none !important;
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
	
	// Usar variables CSS del tema
	const styles = {
		success: {
			bg: 'var(--plg-success, #3fab49)',
			icon: '✓',
			iconBg: 'rgba(255, 255, 255, 0.2)'
		},
		error: {
			bg: 'var(--plg-danger, #e11d48)',
			icon: '✕',
			iconBg: 'rgba(255, 255, 255, 0.2)'
		},
		warning: {
			bg: 'var(--plg-warning, #f59e0b)',
			icon: '⚠',
			iconBg: 'rgba(0, 0, 0, 0.1)'
		},
		info: {
			bg: 'var(--plg-accent, #0c497a)',
			icon: 'ℹ',
			iconBg: 'rgba(255, 255, 255, 0.2)'
		},
		forbidden: {
			bg: '#f97316',
			icon: '🚫',
			iconBg: 'rgba(255, 255, 255, 0.2)'
		},
	};
	
	const style = styles[type] || styles.info;
	
	toast.style.cssText = `
		background: ${style.bg} !important;
		color: white !important;
		padding: 16px 18px !important;
		border-radius: 12px !important;
		box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2), 0 2px 8px rgba(0, 0, 0, 0.1) !important;
		display: flex !important;
		align-items: center !important;
		gap: 14px !important;
		font-size: 15px !important;
		font-weight: 500 !important;
		line-height: 1.4 !important;
		animation: slideIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) !important;
		cursor: pointer !important;
		max-width: 100% !important;
		word-wrap: break-word !important;
		pointer-events: auto !important;
		position: relative !important;
		z-index: 1000000 !important;
		backdrop-filter: blur(10px) !important;
		border: 1px solid rgba(255, 255, 255, 0.1) !important;
	`;
	
	toast.innerHTML = `
		<div style="
			width: 36px;
			height: 36px;
			background: ${style.iconBg};
			border-radius: 50%;
			display: flex;
			align-items: center;
			justify-content: center;
			font-size: 20px;
			font-weight: 700;
			flex-shrink: 0;
		">${style.icon}</div>
		<div style="flex: 1; line-height: 1.5;">${message}</div>
		<button style="
			background: rgba(255, 255, 255, 0.15);
			border: none;
			color: white;
			cursor: pointer;
			font-size: 20px;
			padding: 0;
			width: 28px;
			height: 28px;
			display: flex;
			align-items: center;
			justify-content: center;
			border-radius: 6px;
			transition: all 0.2s ease;
			flex-shrink: 0;
		" onmouseover="this.style.background='rgba(255,255,255,0.25)'; this.style.transform='scale(1.1)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'; this.style.transform='scale(1)'">×</button>
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

// Agregar animaciones CSS mejoradas
if (!document.getElementById('toast-animations')) {
	const style = document.createElement('style');
	style.id = 'toast-animations';
	style.textContent = `
		@keyframes slideIn {
			from {
				transform: translateX(calc(100% + 40px)) scale(0.9);
				opacity: 0;
			}
			to {
				transform: translateX(0) scale(1);
				opacity: 1;
			}
		}
		
		@keyframes slideOut {
			from {
				transform: translateX(0) scale(1);
				opacity: 1;
			}
			to {
				transform: translateX(calc(100% + 40px)) scale(0.9);
				opacity: 0;
			}
		}
		
		/* Responsive: posicionar toasts mejor en mobile */
		@media (max-width: 768px) {
			#toast-container {
				top: 10px !important;
				right: 10px !important;
				left: 10px !important;
				max-width: none !important;
			}
		}
	`;
	document.head.appendChild(style);
}

