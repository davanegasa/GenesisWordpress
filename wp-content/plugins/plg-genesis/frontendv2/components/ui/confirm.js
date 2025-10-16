/**
 * Modal de confirmación reutilizable
 * Reemplaza confirm() nativo con un diseño bonito
 */

export function showConfirm(options = {}) {
	const {
		title = '¿Estás seguro?',
		message = 'Esta acción no se puede deshacer.',
		confirmText = 'Confirmar',
		cancelText = 'Cancelar',
		icon = '⚠️',
		confirmClass = 'danger' // danger, success, primary
	} = options;

	return new Promise((resolve) => {
		const confirmModal = document.createElement('div');
		confirmModal.className = 'confirm-modal-overlay';
		
		// Usar colores del tema
		const bgColors = {
			danger: 'var(--plg-danger)',
			success: 'var(--plg-success)',
			primary: 'var(--plg-accent)',
			warning: 'var(--plg-warning)'
		};

		confirmModal.innerHTML = `
			<div class="confirm-modal">
				<div class="confirm-icon">${icon}</div>
				<h3 class="confirm-title">${title}</h3>
				<p class="confirm-message">${message}</p>
				<div class="confirm-actions">
					<button class="btn-cancel" id="confirm-cancel">${cancelText}</button>
					<button class="btn-confirm-action" id="confirm-action" data-class="${confirmClass}">${confirmText}</button>
				</div>
			</div>
		`;
		
		document.body.appendChild(confirmModal);
		
		// Aplicar estilos del botón de confirmación según el tipo
		const confirmBtn = confirmModal.querySelector('#confirm-action');
		const bgColor = bgColors[confirmClass] || bgColors.danger;
		confirmBtn.style.background = `linear-gradient(135deg, ${bgColor} 0%, color-mix(in srgb, ${bgColor} 90%, #000) 100%)`;
		confirmBtn.style.boxShadow = `0 4px 12px color-mix(in srgb, ${bgColor} 30%, transparent)`;

		// Agregar estilos si no existen
		if (!document.getElementById('confirm-modal-styles')) {
			const styles = document.createElement('style');
			styles.id = 'confirm-modal-styles';
			styles.textContent = `
				.confirm-modal-overlay {
					position: fixed;
					top: 0;
					left: 0;
					right: 0;
					bottom: 0;
					background: color-mix(in srgb, var(--plg-text) 60%, transparent);
					z-index: 10000;
					display: flex;
					align-items: center;
					justify-content: center;
					backdrop-filter: blur(4px);
					animation: fadeIn 0.2s ease;
				}
				.confirm-modal {
					background: var(--plg-cardBg);
					border-radius: 16px;
					padding: 32px;
					max-width: 420px;
					width: 90%;
					box-shadow: var(--plg-shadow);
					animation: slideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
					text-align: center;
				}
				.confirm-icon {
					font-size: 56px;
					margin-bottom: 16px;
					animation: bounce 0.6s ease;
				}
				.confirm-title {
					font-size: 24px;
					font-weight: 700;
					color: var(--plg-text);
					margin: 0 0 12px 0;
				}
				.confirm-message {
					font-size: 15px;
					color: var(--plg-mutedText);
					line-height: 1.6;
					margin: 0 0 28px 0;
					white-space: pre-line;
				}
				.confirm-actions {
					display: flex;
					gap: 12px;
				}
				.btn-cancel, .btn-confirm-action {
					flex: 1;
					padding: 14px 20px;
					border: none;
					border-radius: 10px;
					font-weight: 600;
					font-size: 15px;
					cursor: pointer;
					transition: all 0.2s;
				}
				.btn-cancel {
					background: color-mix(in srgb, var(--plg-border) 40%, var(--plg-cardBg));
					color: var(--plg-mutedText);
				}
				.btn-cancel:hover {
					background: color-mix(in srgb, var(--plg-border) 60%, var(--plg-cardBg));
					transform: translateY(-1px);
				}
				.btn-confirm-action {
					color: white;
				}
				.btn-confirm-action:hover {
					transform: translateY(-2px);
					filter: brightness(1.1);
				}
				@keyframes fadeIn {
					from { opacity: 0; }
					to { opacity: 1; }
				}
				@keyframes fadeOut {
					from { opacity: 1; }
					to { opacity: 0; }
				}
				@keyframes slideUp {
					from { transform: translateY(20px); opacity: 0; }
					to { transform: translateY(0); opacity: 1; }
				}
				@keyframes bounce {
					0%, 100% { transform: scale(1); }
					50% { transform: scale(1.1); }
				}
			`;
			document.head.appendChild(styles);
		}

		const handleCancel = () => {
			confirmModal.style.animation = 'fadeOut 0.2s ease';
			setTimeout(() => {
				document.body.removeChild(confirmModal);
				resolve(false);
			}, 200);
		};

		const handleConfirm = () => {
			confirmModal.style.animation = 'fadeOut 0.2s ease';
			setTimeout(() => {
				document.body.removeChild(confirmModal);
				resolve(true);
			}, 200);
		};

		confirmModal.querySelector('#confirm-cancel').addEventListener('click', handleCancel);
		confirmModal.querySelector('#confirm-action').addEventListener('click', handleConfirm);
		confirmModal.addEventListener('click', (e) => {
			if (e.target === confirmModal) handleCancel();
		});

		// Cerrar con ESC
		const escHandler = (e) => {
			if (e.key === 'Escape') {
				handleCancel();
				document.removeEventListener('keydown', escHandler);
			}
		};
		document.addEventListener('keydown', escHandler);
	});
}

