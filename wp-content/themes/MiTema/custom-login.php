<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>styles.css">
</head>
<body>
<?php
if(isset($_GET['error'])) {
    echo '<div id="error-message" class="error-message show">' . htmlspecialchars($_GET['error']) . '</div>';
}
?>
<script>
    // Función para ocultar el mensaje de error después de 4 segundos
    setTimeout(function() {
        var errorMessage = document.getElementById('error-message');
        if (errorMessage) {
            errorMessage.classList.remove('show');
        }
    }, 4000);
</script>
  <div class="card">
    <div class="card-header">
      <div class="responsive-banner">
        <img src="<?php echo get_template_directory_uri(); ?>/images/emmaus/header.png" alt="Logo Emmaus">
      </div>      
    </div>
    <div class="card-body">
        <form action="<?php echo esc_url(site_url('wp-login.php')); ?>" method="post">
          <div class="responsive-banner">
            <img src="<?php echo get_template_directory_uri(); ?>/images/genesis/logo.png" alt="Logo Genesis">
          </div>
          <div class="form-group">
            <label for="username" class="form-label">Usuario</label>
            <input type="text" id="username" name="log" class="form-control" placeholder="Usuario" required>
          </div>
          <div class="form-group">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" id="password" name="pwd" class="form-control" placeholder="Contraseña" required>
          </div>
          <button type="submit" class="btn-primary">Iniciar sesión</button>
        </form>
        <div class="text-center mt-3">
            <a href="<?php echo esc_url(wp_lostpassword_url()); ?>" class="text-muted">¿Olvidaste tu contraseña?</a>
        </div>
    </div>
  </div>
</body>
</html>