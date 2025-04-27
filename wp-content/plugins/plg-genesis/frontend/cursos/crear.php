<?php
require_once(__DIR__ . '/../../../../../wp-load.php');  // Ajusta la ruta según tu estructura de directorios

// Verificar si el usuario no está autenticado en WordPress
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());  // Redirigir a la página de login de WordPress
    exit;
}

require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

// Obtener los niveles disponibles
$query_niveles = "SELECT id, nombre FROM niveles";
$result_niveles = pg_query($conexion, $query_niveles);

// Verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los datos del formulario
    $nombre = $_POST["nombreCurso"];
    $descripcion = $_POST["descripcion"];
    $nivel_id = $_POST["nivel"];

    // Preparar la consulta SQL para insertar el curso
    $query = "INSERT INTO cursos (nombre, descripcion, nivel_id) VALUES ($1, $2, $3)";

    // Ejecutar la consulta con par芍metros preparados para evitar inyecci車n SQL
    $result = pg_query_params($conexion, $query, array($nombre, $descripcion, $nivel_id));

    // Verificar si la consulta fue exitosa
    if ($result) {
        echo "<div class='alert alert-success' role='alert'>Curso guardado exitosamente.</div>";
    } else {
        echo "<div class='alert alert-danger' role='alert'>Error al guardar el curso. Por favor, int谷ntalo de nuevo.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ingresar Cursos</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../dashboard.css">
</head>
<body>

<div class="container">
  <h1 class="mt-5">Ingresar Cursos</h1>
  <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <div class="mb-3">
      <label for="nombreCurso" class="form-label">Nombre del Curso</label>
      <input type="text" class="form-control" id="nombreCurso" name="nombreCurso" placeholder="Ingrese el nombre del curso">
    </div>
    <div class="mb-3">
      <label for="descripcion" class="form-label">Descripci車n</label>
      <textarea class="form-control" id="descripcion" name="descripcion" rows="3" placeholder="Ingrese una descripci車n del curso"></textarea>
    </div>
    <div class="mb-3">
      <label for="nivel" class="form-label">Nivel</label>
      <select class="form-select" id="nivel" name="nivel">
        <?php
        while ($row = pg_fetch_assoc($result_niveles)) {
            echo "<option value='" . $row["id"] . "'>" . $row["nombre"] . "</option>";
        }
        ?>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">Guardar Curso</button>
  </form>
</div>

</body>
</html>
