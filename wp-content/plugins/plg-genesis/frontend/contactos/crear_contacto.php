<?php
require_once(__DIR__ . '/../../../../../wp-load.php'); // Cargar entorno de WordPress
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Contacto</title>
    <link rel="stylesheet" href="<?php echo plugins_url('../../assets/css/styles.css', __FILE__); ?>">
</head>
<body>
<div class="centered-form-container">
    <div class="form-container">
        <h1 class="form-title">Crear Contacto</h1>
        <form id="formCrearContacto">
            <div class="mb-3">
                <label for="nombreContacto" class="form-label">Nombre Completo</label>
                <input type="text" id="nombreContacto" class="form-control" placeholder="Ingrese el nombre completo" required>
            </div>
            <div class="mb-3">
                <label for="iglesiaContacto" class="form-label">Iglesia</label>
                <input type="text" id="iglesiaContacto" class="form-control" placeholder="Ingrese el nombre de la iglesia">
            </div>
            <div class="mb-3">
                <label for="emailContacto" class="form-label">Email</label>
                <input type="email" id="emailContacto" class="form-control" placeholder="Ingrese el correo electrónico">
            </div>
            <div class="mb-3">
                <label for="celularContacto" class="form-label">Celular</label>
                <input type="text" id="celularContacto" class="form-control" placeholder="Ingrese el número de celular">
            </div>
            <div class="mb-3">
                <label for="direccionContacto" class="form-label">Dirección</label>
                <input type="text" id="direccionContacto" class="form-control" placeholder="Ingrese la dirección">
            </div>
            <div class="mb-3">
                <label for="ciudadContacto" class="form-label">Ciudad</label>
                <input type="text" id="ciudadContacto" class="form-control" placeholder="Ingrese la ciudad">
            </div>
            <div class="mb-3">
                <label for="codeContacto" class="form-label">Código</label>
                <input type="text" id="codeContacto" class="form-control" placeholder="Ingrese un código (opcional)">
            </div>
            <div class="text-center">
                <button type="submit" class="btn btn-primary w-100">Crear Contacto</button>
            </div>
        </form>
        <div id="responseMessage" class="mt-3 text-center"></div>
    </div>
</div>

<script>
document.getElementById('formCrearContacto').addEventListener('submit', function (e) {
    e.preventDefault();

    // Obtener valores de los campos
    const contactoData = {
        nombre: document.getElementById('nombreContacto').value,
        iglesia: document.getElementById('iglesiaContacto').value,
        email: document.getElementById('emailContacto').value,
        celular: document.getElementById('celularContacto').value,
        direccion: document.getElementById('direccionContacto').value,
        ciudad: document.getElementById('ciudadContacto').value,
        code: document.getElementById('codeContacto').value,
    };

    // Enviar datos al backend
    fetch('<?php echo plugin_dir_url(__FILE__); ?>../../backend/contactos/crear_contacto.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(contactoData)
    })
        .then(response => response.json())
        .then(data => {
            const messageDiv = document.getElementById('responseMessage');
            if (data.success) {
                messageDiv.textContent = 'Contacto creado exitosamente.';
                messageDiv.className = 'alert alert-success';
                document.getElementById('formCrearContacto').reset();
            } else {
                messageDiv.textContent = data.error || 'Error al crear el contacto.';
                messageDiv.className = 'alert alert-danger';
            }
        })
        .catch(error => {
            const messageDiv = document.getElementById('responseMessage');
            messageDiv.textContent = 'Error en la conexión.';
            messageDiv.className = 'alert alert-danger';
        });
});
</script>
</body>
</html>