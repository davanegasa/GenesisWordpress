<?php
require_once(__DIR__ . '/../../../../../wp-load.php'); // Carga el entorno de WordPress
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Nivel</title>
    <link rel="stylesheet" href="<?php echo plugins_url('../../assets/css/styles.css', __FILE__); ?>">
</head>
<body>
<div class="centered-form-container">
    <div class="form-container">
        <h1 class="form-title">Crear Nivel</h1>
        <form id="formCrearNivel">
            <div class="mb-3">
                <label for="nombreNivel" class="form-label">Nombre del Nivel</label>
                <input type="text" id="nombreNivel" class="form-control" placeholder="Ingrese el nombre del nivel" required>
            </div>
            <button type="submit" class="btn btn-primary">Crear Nivel</button>
        </form>
        <div id="responseMessage" class="mt-3"></div>
    </div>
</div>

<script>
    document.getElementById('formCrearNivel').addEventListener('submit', function (e) {
        e.preventDefault();

        const nombreNivel = document.getElementById('nombreNivel').value;

        fetch('<?php echo plugin_dir_url(__FILE__); ?>../../backend/cursos/crear_nivel.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nombre: nombreNivel })
        })
            .then(response => response.json())
            .then(data => {
                const messageDiv = document.getElementById('responseMessage');
                if (data.success) {
                    messageDiv.textContent = 'Nivel creado exitosamente.';
                    messageDiv.className = 'alert alert-success';
                    document.getElementById('formCrearNivel').reset();
                } else {
                    messageDiv.textContent = data.error || 'Error al crear el nivel.';
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