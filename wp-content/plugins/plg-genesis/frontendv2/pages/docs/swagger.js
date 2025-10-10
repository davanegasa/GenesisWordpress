import { api } from '../../api/client.js';

export async function mount(container) {
	container.innerHTML = `
		<div class="card">
			<div class="card-title">Documentación API</div>
			<div id="docs-loading">Cargando especificación...</div>
			<iframe id="swagger-frame" style="width:100%; height:80vh; border:none; border-radius:8px; display:none;"></iframe>
		</div>
	`;
	
	try {
		// Determinar la ruta base correcta
		const basePath = window.location.pathname.includes('/genesis/') ? '/genesis' : '';
		const apiUrl = `${basePath}/wp-json/plg-genesis/v1/docs/openapi?_=${Date.now()}`;
		
		const response = await fetch(apiUrl, {
			credentials: 'include',
			headers: window.wpApiSettings?.nonce ? { 'X-WP-Nonce': window.wpApiSettings.nonce } : {},
			cache: 'no-store'
		});
		
		if (!response.ok) {
			throw new Error(`HTTP ${response.status}: ${response.statusText}`);
		}
		
		const yamlContent = await response.text();
		
		const iframe = container.querySelector('#swagger-frame');
		const loading = container.querySelector('#docs-loading');
		
		iframe.srcdoc = `
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Genesis API Docs</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5/swagger-ui.css" />
  <style> body { margin: 0; } </style>
</head>
<body>
  <div id="swagger-ui"></div>
  <script src="https://unpkg.com/swagger-ui-dist@5/swagger-ui-bundle.js" crossorigin="anonymous"></script>
  <script src="https://unpkg.com/js-yaml@4/dist/js-yaml.min.js" crossorigin="anonymous"></script>
  <script>
    window.onload = () => {
      const yamlStr = ${JSON.stringify(yamlContent)};
      const spec = jsyaml.load(yamlStr);
      window.ui = SwaggerUIBundle({
        spec: spec,
        dom_id: '#swagger-ui'
      });
    };
  </script>
</body>
</html>
		`;
		
		loading.style.display = 'none';
		iframe.style.display = 'block';
	} catch (e) {
		container.querySelector('#docs-loading').textContent = 'Error cargando documentación: ' + e.message;
	}
}

export function unmount() {}

