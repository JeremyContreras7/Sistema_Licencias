<?php
include("conexion.php"); // tu archivo de conexión a MySQL

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre_equipo = $_POST['nombre_equipo'];
    $numero_serie = $_POST['numero_serie'];
    $sistema_operativo = $_POST['sistema_operativo'];
    $establecimiento = $_POST['establecimiento'];
    $encargado = $_POST['encargado'];

    // Insertar equipo
    $sql_equipo = "INSERT INTO equipos (nombre_equipo, numero_serie, sistema_operativo, establecimiento, encargado) 
                   VALUES ('$nombre_equipo', '$numero_serie', '$sistema_operativo', '$establecimiento', '$encargado')";
    if (mysqli_query($conn, $sql_equipo)) {
        $id_equipo = mysqli_insert_id($conn);

        // Insertar software instalado
        if (!empty($_POST['software'])) {
            foreach ($_POST['software'] as $index => $software) {
                $version = $_POST['version'][$index];
                $licencia = $_POST['licencia'][$index];
                $sql_sw = "INSERT INTO software_instalado (id_equipo, nombre_software, version, licencia)
                           VALUES ('$id_equipo', '$software', '$version', '$licencia')";
                mysqli_query($conn, $sql_sw);
            }
        }

        echo "<script>alert('Equipo y software registrados con éxito'); window.location='registrar_equipo.php';</script>";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Equipos</title>
    <link rel="stylesheet" href="css/styleEquipos.css">
</head>
<body>
    <div id="page-wrapper">
        <h1>Registro de Equipos y Software</h1>
        <form method="POST">
            <input type="text" name="nombre_equipo" placeholder="Nombre del equipo" required>
            <input type="text" name="numero_serie" placeholder="Número de serie" required>
            <input type="text" name="sistema_operativo" placeholder="Sistema operativo" required>
            <input type="text" name="establecimiento" placeholder="Establecimiento" required>
            <input type="text" name="encargado" placeholder="Encargado" required>

            <h3>Software Instalado</h3>
            <div id="software-container">
                <div class="software-item">
                    <input type="text" name="software[]" placeholder="Nombre software">
                    <input type="text" name="version[]" placeholder="Versión">
                    <input type="text" name="licencia[]" placeholder="Licencia (Ej: OEM, Trial, Libre)">
                </div>
            </div>

            <button type="button" onclick="agregarSoftware()">+ Agregar Software</button>
            <br><br>
            <button type="submit" class="botonenviar">Registrar Equipo</button>
        </form>
    </div>

    <script>
        function agregarSoftware() {
            const container = document.getElementById("software-container");
            const div = document.createElement("div");
            div.classList.add("software-item");
            div.innerHTML = `
                <input type="text" name="software[]" placeholder="Nombre software">
                <input type="text" name="version[]" placeholder="Versión">
                <input type="text" name="licencia[]" placeholder="Licencia">
            `;
            container.appendChild(div);
        }
    </script>
</body>
</html>
