<?php
require_once(__DIR__ . '/../../../../../wp-load.php');  // Ajusta la ruta seg¨²n tu estructura de directorios

// Verificar si el usuario no est¨¢ autenticado en WordPress
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());  // Redirigir a la p¨¢gina de login de WordPress
    exit;
}

require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

// Consulta para obtener los cursos de la base de datos
$sql = "SELECT * FROM cursos";
$result = pg_query($conexion, $sql);

// Verificar si se encontraron cursos
if ($result) {
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Listar Cursos</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../dashboard.css">
</head>
<body>

<div class="container">
  <h1 class="mt-5">Listar Cursos</h1>
  <div class="row mt-4">
    <?php
    // Iterar sobre los resultados y mostrar cada curso en una tarjeta
    while ($row = pg_fetch_assoc($result)) {
    ?>
      <div class="col-md-4 mb-4">
        <div class="card">
          <div class="card-body">
            <h5 class="card-title"><?php echo $row["nombre"]; ?></h5>
            <p class="card-text"><?php echo $row["descripcion"]; ?></p>
          </div>
        </div>
      </div>
    <?php
    }
    ?>
  </div>
</div>

</body>
</html>

<?php
} else {
  echo "No se encontraron cursos.";
}

// Liberar el resultado y cerrar la conexiÃ³n
pg_free_result($result);
pg_close($conexion);
?>
