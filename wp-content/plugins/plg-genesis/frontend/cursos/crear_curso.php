<?php
require_once(__DIR__ . '/../../../../../wp-load.php'); // Carga el entorno de WordPress
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Curso</title>
    <link rel="stylesheet" href="<?php echo plugins_url('../../assets/css/styles.css', __FILE__); ?>">
</head>
<body>
<div class="centered-form-container">
    <div class="form-container">
        <h1 class="form-title">Crear Curso</h1>
        <form id="formCrearCurso">
            <!-- Nombre del Curso -->
            <div class="mb-3">
                <label for="nombreCurso" class="form-label fw-bold">Nombre del Curso</label>
                <input type="text" id="nombreCurso" class="form-control" placeholder="Ingrese el nombre del curso" required>
            </div>

            <!-- Nivel del Curso -->
            <div class="mb-3">
                <label for="nivelCurso" class="form-label fw-bold">Nivel</label>
                <select id="nivelCurso" class="form-control" required>
                    <option value="" disabled selected>Seleccione un nivel</option>
                    <!-- Opciones de nivel se llenarán dinámicamente -->
                </select>
            </div>

            <!-- Descripción -->
            <div class="mb-3">
                <label for="descripcionCurso" class="form-label fw-bold">Descripción</label>
                <textarea id="descripcionCurso" class="form-control" rows="3" placeholder="Ingrese una descripción del curso"></textarea>
            </div>

            <!-- Botón de envío -->
            <div class="text-center">
                <button type="submit" class="btn btn-primary w-100">Crear Curso</button>
            </div>
        </form>
        <div id="responseMessage" class="mt-3"></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Llenar dinámicamente el campo de niveles
        fetch('<?php echo plugin_dir_url(__FILE__); ?>../../backend/cursos/obtener_niveles.php')
            .then(response => response.json())
            .then(data => {
                const nivelCursoSelect = document.getElementById('nivelCurso');
                if (data.success) {
                    data.niveles.forEach(nivel => {
                        const option = document.createElement('option');
                        option.value = nivel.id;
                        option.textContent = nivel.nombre;
                        nivelCursoSelect.appendChild(option);
                    });
                } else {
                    alert('Error al cargar los niveles.');
                }
            })
            .catch(error => console.error('Error al cargar los niveles:', error));

        // Manejo del formulario
        document.getElementById('formCrearCurso').addEventListener('submit', function (e) {
            e.preventDefault();

            const nombreCurso = document.getElementById('nombreCurso').value;
            const nivelCurso = document.getElementById('nivelCurso').value;
            const descripcionCurso = document.getElementById('descripcionCurso').value;

            fetch('<?php echo plugin_dir_url(__FILE__); ?>../../backend/cursos/crear_curso.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    nombre: nombreCurso, 
                    nivel_id: nivelCurso, 
                    descripcion: descripcionCurso 
                })
            })
                .then(response => response.json())
                .then(data => {
                    const messageDiv = document.getElementById('responseMessage');
                    if (data.success) {
                        messageDiv.textContent = 'Curso creado exitosamente.';
                        messageDiv.className = 'alert alert-success';
                        document.getElementById('formCrearCurso').reset();
                    } else {
                        messageDiv.textContent = data.error || 'Error al crear el curso.';
                        messageDiv.className = 'alert alert-danger';
                    }
                })
                .catch(error => {
                    const messageDiv = document.getElementById('responseMessage');
                    messageDiv.textContent = 'Error en la conexión.';
                    messageDiv.className = 'alert alert-danger';
                });
        });
    });
</script>
</body>
</html>