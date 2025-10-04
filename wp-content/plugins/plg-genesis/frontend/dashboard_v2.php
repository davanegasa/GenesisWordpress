<?php
if (!defined('ABSPATH')) { exit; }
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Dashboard v2</title>
	<?php wp_head(); ?>
	<link rel="stylesheet" href="<?php echo plugin_dir_url(__FILE__); ?>../assets/css/styles.css">
</head>
<body>
	<div style="padding:16px;max-width:1000px;margin:0 auto;">
		<h1>Dashboard v2 (API)</h1>
		<p>Prueba de consumo de API para Estudiantes.</p>

		<div style="margin:12px 0;">
			<label for="contactoId">contactoId:</label>
			<input id="contactoId" type="number" min="1" style="padding:6px;">
			<button id="btnBuscar" style="padding:6px 12px;">Buscar</button>
		</div>

    <pre id="output" style="background:#111;color:#0f0;padding:12px;border-radius:6px;overflow:auto;max-height:400px;"></pre>

    <hr style="margin:24px 0;">
    <h2>Contactos</h2>
    <div style="display:flex;gap:8px;align-items:center;margin:12px 0;">
      <input id="qContactos" type="text" placeholder="Buscar por nombre/iglesia/email" style="padding:6px;flex:1;">
      <button id="btnBuscarContactos" style="padding:6px 12px;">Buscar contactos</button>
    </div>
    <div id="contactosList" style="border:1px solid #333;border-radius:6px;padding:8px;max-height:220px;overflow:auto;"></div>

    <hr style="margin:24px 0;">
    <h2>Estadísticas</h2>
    <div style="display:flex;gap:8px;align-items:center;margin:12px 0;">
      <label for="month">Mes:</label>
      <input id="month" type="number" min="1" max="12" style="width:80px;padding:6px;" value="<?php echo date('m'); ?>">
      <label for="year">Año:</label>
      <input id="year" type="number" min="2000" max="2100" style="width:100px;padding:6px;" value="<?php echo date('Y'); ?>">
      <button id="btnStats" style="padding:6px 12px;">Consultar</button>
    </div>
    <pre id="statsOut" style="background:#111;color:#0ff;padding:12px;border-radius:6px;overflow:auto;max-height:300px;"></pre>
	</div>

	<script>
		// Exponer nonce de REST para autenticación con cookies
		window.wpApiSettings = window.wpApiSettings || {};
		window.wpApiSettings.nonce = '<?php echo wp_create_nonce('wp_rest'); ?>';
	</script>
	<script src="<?php echo plugin_dir_url(__FILE__); ?>js/api/client.js"></script>
  <script src="<?php echo plugin_dir_url(__FILE__); ?>js/services/estudiantes.js"></script>
  <script src="<?php echo plugin_dir_url(__FILE__); ?>js/services/congresos.js"></script>
  <script src="<?php echo plugin_dir_url(__FILE__); ?>js/services/contactos.js"></script>
  <script src="<?php echo plugin_dir_url(__FILE__); ?>js/services/estadisticas.js"></script>
	<script>
	(async function(){
		const $btn = document.getElementById('btnBuscar');
		const $id  = document.getElementById('contactoId');
		const $out = document.getElementById('output');

		$btn.addEventListener('click', async function(){
			$out.textContent = 'Cargando...';
			try {
				const resp = await window.PlgGenesisEstudiantesService.listarPorContacto($id.value);
				$out.textContent = JSON.stringify(resp, null, 2);
			} catch (e) {
				$out.textContent = JSON.stringify({ error: e.message, details: e.details || null }, null, 2);
			}
		});
    // Cargar congresos a modo demo
		try {
			const res = await window.PlgGenesisCongresosService.listar();
			console.log('Congresos:', res);
		} catch (e) {
			console.warn('Error cargando congresos', e);
		}

    // Estadísticas
    const $btnStats = document.getElementById('btnStats');
    const $month = document.getElementById('month');
    const $year = document.getElementById('year');
    const $statsOut = document.getElementById('statsOut');
    $btnStats.addEventListener('click', async function(){
      $statsOut.textContent = 'Cargando...';
      try {
        const r = await window.PlgGenesisEstadisticasService.resumen($month.value, $year.value);
        $statsOut.textContent = JSON.stringify(r, null, 2);
      } catch (e) {
        $statsOut.textContent = JSON.stringify({ error: e.message, details: e.details || null }, null, 2);
      }
    });

    // Contactos
    const $q = document.getElementById('qContactos');
    const $btnC = document.getElementById('btnBuscarContactos');
    const $list = document.getElementById('contactosList');
    $btnC.addEventListener('click', async function(){
      $list.textContent = 'Buscando...';
      try {
        const r = await window.PlgGenesisContactosService.buscar($q.value, 20, 0);
        if (!r || !r.data) { $list.textContent = 'Sin resultados'; return; }
        const items = r.data.items || [];
        if (items.length === 0) { $list.textContent = 'Sin resultados'; return; }
        $list.innerHTML = '';
        items.forEach(item => {
          const a = document.createElement('a');
          a.href = '#';
          a.textContent = `${item.nombre} — ${item.iglesia || ''}`;
          a.style.display = 'block';
          a.style.padding = '4px 0';
          a.addEventListener('click', async (ev) => {
            ev.preventDefault();
            // Reutilizar estudiantes por contacto
            $out.textContent = 'Cargando estudiantes del contacto...';
            try {
              const resp = await window.PlgGenesisEstudiantesService.listarPorContacto(item.id);
              $out.textContent = JSON.stringify(resp, null, 2);
            } catch (e) {
              $out.textContent = JSON.stringify({ error: e.message, details: e.details || null }, null, 2);
            }
          });
          $list.appendChild(a);
        });
      } catch (e) {
        $list.textContent = 'Error en la búsqueda';
      }
    });
	})();
	</script>

	<?php wp_footer(); ?>
</body>
</html>