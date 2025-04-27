<?php
require_once(__DIR__ . '/../../../../../wp-load.php');  // Ajusta la ruta según tu estructura de directorios

// Verificar si el usuario no está autenticado en WordPress
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url());  // Redirigir a la página de login de WordPress
    exit;
}

require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');

// Verificar la conexión a la base de datos
if (!$conexion) {
    echo '<div class="alert alert-danger" role="alert">Error al conectar a la base de datos: ' . pg_last_error() . '</div>';
    exit;
}

// Verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener los datos del formulario
    $doc_identidad = $_POST['doc_identidad'];
    $contacto_seleccionado = $_POST['contacto_seleccionado'];
    $nombre1 = $_POST['nombre1'];
    $nombre2 = $_POST['nombre2'];
    $apellido1 = $_POST['apellido1'];
    $apellido2 = $_POST['apellido2'];
    $celular = $_POST['celular'];
    $correo_electronico = $_POST['correo_electronico'];
    $ciudad = $_POST['ciudad'];
    $iglesia = $_POST['iglesia'];


    if (empty($contacto_seleccionado)) {
        echo '<div class="alert alert-danger" role="alert">Error: Debes seleccionar un contacto válido.</div>';
        exit;
    }
    
    // Consulta SQL para obtener la cantidad de estudiantes
    $query_count = "SELECT COUNT(*) AS total_estudiantes FROM estudiantes";
    $result_count = pg_query($conexion, $query_count);
    
    if (!$result_count) {
        echo '<div class="alert alert-danger" role="alert">Error al contar los estudiantes: ' . pg_last_error($conexion) . '</div>';
        exit;
    }
    
    $row_count = pg_fetch_assoc($result_count);
    $total_estudiantes = $row_count['total_estudiantes'];

    echo "<script>console.log('Total Estudiantes: ". $total_estudiantes. "');</script>";

    // Generar ID de estudiante
    $id_estudiante = $code_contacto . $total_estudiantes;

    // Consulta SQL para insertar un nuevo estudiante en la base de datos
    $query = "INSERT INTO estudiantes (id_contacto, doc_identidad, nombre1, nombre2, apellido1, apellido2, celular, email, ciudad, iglesia, id_estudiante) 
              VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11)";
    
    // Preparar y ejecutar la consulta
    $result = pg_query_params($conexion, $query, array($id_contacto, $doc_identidad, $nombre1, $nombre2, $apellido1, $apellido2, $celular, $correo_electronico, $ciudad, $iglesia, $id_estudiante));

    // Verificar si la consulta fue exitosa
    if ($result) {
        echo '<div class="alert alert-success" role="alert">Estudiante ingresado correctamente. Codigo Asignado: '. $id_estudiante .'</div>';
    } else {
        echo '<div class="alert alert-danger" role="alert">Error al ingresar el estudiante: ' . pg_last_error($conexion) . '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ingresar Estudiantes</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="../dashboard.css">
</head>
<body>

<div class="container">
  <h1 class="mt-5">Ingresar Estudiantes</h1>
  <form method="post">
    <div class="mb-3">
      <label for="doc_identidad" class="form-label">Documento de Identidad</label>
      <input type="text" class="form-control" id="doc_identidad" name="doc_identidad" placeholder="Ingrese el documento de identidad">
    </div>
    <div class="mb-3">
      <label for="contacto_seleccionado" class="form-label">Contacto</label>
      <select class="form-select" id="contacto_seleccionado" name="contacto_seleccionado">
        <option value="">Seleccionar contacto</option>
        <?php
            // Incluir el archivo de conexión a la base de datos
            require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php');
        
            try {
                // Consulta SQL para obtener todos los contactos
                $query = "SELECT id, nombre, iglesia, code FROM contactos";
                $result = pg_query($conexion, $query);
            
                // Verificar si hay resultados
                if ($result && pg_num_rows($result) > 0) {
                    // Iterar sobre cada fila de resultados
                    while ($row = pg_fetch_assoc($result)) {
                        echo '<option value="' . $row['id'] . '">' . $row['iglesia'] . ' - ' . $row['nombre']. '</option>';
                    }
                }
            } catch (Exception $e) {
                echo 'Error al obtener los contactos: ' . $e->getMessage();
            }
        ?>
      </select>
    </div>
    <div class="mb-3">
      <label for="nombre1" class="form-label">Primer Nombre</label>
      <input type="text" class="form-control" id="nombre1" name="nombre1" placeholder="Ingrese el primer nombre">
    </div>
    <div class="mb-3">
      <label for="nombre2" class="form-label">Segundo Nombre</label>
      <input type="text" class="form-control" id="nombre2" name="nombre2" placeholder="Ingrese el segundo nombre">
    </div>
    <div class="mb-3">
      <label for="apellido1" class="form-label">Primer Apellido</label>
      <input type="text" class="form-control" id="apellido1" name="apellido1" placeholder="Ingrese el primer apellido">
    </div>
    <div class="mb-3">
      <label for="apellido2" class="form-label">Segundo Apellido</label>
      <input type="text" class="form-control" id="apellido2" name="apellido2" placeholder="Ingrese el segundo apellido">
    </div>
    <div class="mb-3">
      <label for="celular" class="form-label">Celular</label>
      <input type="text" class="form-control" id="celular" name="celular" placeholder="Ingrese el número de celular">
    </div>
    <div class="mb-3">
      <label for="correo_electronico" class="form-label">Correo Electrónico</label>
      <input type="email" class="form-control" id="correo_electronico" name="correo_electronico" placeholder="Ingrese el correo electrónico">
    </div>
    <div class="mb-3">
      <label for="ciudad" class="form-label">Ciudad</label>
      <input type="text" class="form-control" id="ciudad" name="ciudad" placeholder="Ingrese la ciudad">
    </div>
    <div class="mb-3">
      <label for="iglesia" class="form-label">Iglesia</label>
      <input type="text" class="form-control" id="iglesia" name="iglesia" placeholder="Ingrese el nombre de la iglesia">
    </div>
    <button type="submit" class="btn btn-primary">Guardar Estudiante</button>
  </form>
</div>

</body>
</html>

<?php
  // Cerrar la conexión a la base de datos
  pg_close($conexion);
?>
