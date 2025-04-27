<?php
require_once(__DIR__ . '/../../../../../wp-load.php');  // Ajusta la ruta según tu estructura de directorios
require_once(plugin_dir_path(__FILE__) . '/../../backend/db.php'); // Conexión a PostgreSQL

global $wpdb;

// Obtener asistentes de congresos que aún no están vinculados
$asistentes = $wpdb->get_results(
    "SELECT nombre, ident as identificacion, email, telef as telefono FROM asistentes where asistencia = true and migracion= false;"
);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migración de Asistentes</title>
    <style>
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid black; padding: 10px; text-align: left; }
        th { background-color: #f2f2f2; }
        .modal { display: none; position: fixed; z-index: 1; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.4); }
        .modal-content { background-color: white; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 50%; }
        .close { color: red; float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>
    <h2>Migración de Asistentes</h2>
    <button type="button" onclick="submitMigration()">Procesar Migración</button>
    <form id="migrationForm" method="POST" action="procesar_migracion.php" onsubmit="return filterUnchecked();">
        <table>
            <tr>
                <th>Nombre</th>
                <th>Identificación</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>Posibles Coincidencias</th>
                <th>Acción</th>
            </tr>
            <?php foreach ($asistentes as $asistente): ?>
                <?php
                    $query = "SELECT id, 
                                     nombre1 || ' ' || COALESCE(nombre2, '') || ' ' || apellido1 || ' ' || COALESCE(apellido2, '') AS nombre_completo, 
                                     email, 
                                     doc_identidad, 
                                     celular, 
                                     ciudad, 
                                     iglesia, 
                                     fecha_registro,
                                     CASE 
                                         WHEN doc_identidad = $3 THEN 1  
                                         WHEN LOWER(email) = LOWER($2) THEN 2  
                                         ELSE 3  
                                     END AS prioridad
                              FROM estudiantes 
                              WHERE doc_identidad = $3
                                 OR LOWER(email) = LOWER($2)
                                 OR (nombre1 ILIKE '%' || $1 || '%' 
                                     OR apellido1 ILIKE '%' || $1 || '%' 
                                     OR nombre2 ILIKE '%' || $1 || '%' 
                                     OR apellido2 ILIKE '%' || $1 || '%')
                              ORDER BY prioridad ASC";
                    
                    $result = pg_query_params($conexion, $query, array($asistente->nombre, $asistente->email, $asistente->identificacion));
                    $coincidencias = pg_fetch_all($result);
                ?>
                <tr>
                    <td><?php echo esc_html($asistente->nombre); ?></td>
                    <td><?php echo esc_html($asistente->identificacion); ?></td>
                    <td><?php echo esc_html($asistente->email); ?></td>
                    <td><?php echo esc_html($asistente->telefono); ?></td>
                    <td>
                        <select name="coincidencia[<?php echo $asistente->identificacion; ?>]" onchange="updateSelectedStudent(this)">
                            <option value="nuevo" data-student='{}'>Agregar como nuevo asistente</option>
                            <?php foreach ($coincidencias as $coincidencia): ?>
                                <option value="<?php echo $coincidencia['id']; ?>" 
                                        data-student="<?php echo htmlspecialchars(json_encode($coincidencia, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)); ?>">
                                    <?php echo esc_html($coincidencia['nombre_completo']); ?> (<?php echo esc_html($coincidencia['email']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="button" onclick="showStudentDetails()">Ver Detalles</button>
                    </td>
                    <td>
                        <input type="checkbox" name="migrar[]" value="<?php echo $asistente->identificacion; ?>" onchange="toggleSelection(this)">
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <br>
    </form>

    <!-- Modal para mostrar detalles -->
    <div id="studentModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h3>Detalles del Estudiante</h3>
            <p id="studentDetails"></p>
        </div>
    </div>

    <script>
        function submitMigration() {
            let selectedData = [];
            document.querySelectorAll('input[name="migrar[]"]:checked').forEach(checkbox => {
                let row = checkbox.closest("tr");
                let identificacion = checkbox.value;
                let select = row.querySelector("select");
                let idSeleccionado = select.value;
    
                selectedData.push({
                    identificacion: identificacion,
                    idSeleccionado: idSeleccionado
                });
            });
    
            let requestData = {
                id_congreso: 3,  // Aquí deberías pasar el ID del congreso dinámicamente
                asistentes: selectedData
            };
    
            console.log("Enviando datos:", requestData);  // Verifica los datos antes de enviarlos
    
            fetch("procesar_migracion.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify(requestData)
            })
            .then(response => response.json())
            .then(data => {
                console.log("Respuesta del servidor:", data);
                alert("Migración procesada correctamente.");
            })
            .catch(error => {
                console.error("Error en la migración:", error);
                alert("Hubo un problema al procesar la migración.");
            });
        }
    
        let selectedStudentData = {}; 

        function updateSelectedStudent(selectElement) {
            let selectedOption = selectElement.options[selectElement.selectedIndex];
            let studentData = selectedOption.getAttribute("data-student");
    
            console.log("Opción seleccionada:", selectedOption); 
            console.log("Datos en data-student:", studentData); 
    
            selectedStudentData = studentData ? JSON.parse(studentData) : {};
        }
    
        function showStudentDetails() {
            if (!selectedStudentData || Object.keys(selectedStudentData).length === 0) {
                alert("Por favor, selecciona un estudiante válido.");
                return;
            }
    
            let details = "<strong>Nombre:</strong> " + (selectedStudentData.nombre_completo || "No disponible") + "<br>" +
                          "<strong>Email:</strong> " + (selectedStudentData.email || "No disponible") + "<br>" +
                          "<strong>Documento:</strong> " + (selectedStudentData.doc_identidad || "No disponible") + "<br>" +
                          "<strong>Celular:</strong> " + (selectedStudentData.celular || "No disponible") + "<br>" +
                          "<strong>Ciudad:</strong> " + (selectedStudentData.ciudad || "No disponible") + "<br>" +
                          "<strong>Iglesia:</strong> " + (selectedStudentData.iglesia || "No disponible") + "<br>" +
                          "<strong>Fecha Registro:</strong> " + (selectedStudentData.fecha_registro || "No disponible");
    
            document.getElementById("studentDetails").innerHTML = details;
            document.getElementById("studentModal").style.display = "block";
        }
    
        function closeModal() {
            document.getElementById("studentModal").style.display = "none";
        }
        
        function filterUnchecked() {
            document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                if (!checkbox.checked) {
                    checkbox.closest('tr').querySelectorAll('select, input').forEach(input => input.disabled = true);
                }
            });
            return true;
        }

        function toggleSelection(checkbox) {
            let row = checkbox.closest("tr");
            let select = row.querySelector("select");
            let button = row.querySelector("button");
            select.disabled = !checkbox.checked;
            button.disabled = !checkbox.checked;
        }
    </script>
</body>
</html>
