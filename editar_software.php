<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== "ENCARGADO") {
    header("Location: index.php");
    exit();
}

include("conexion.php");

// --- Obtener ID del software a editar ---
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: gestionSoftware.php");
    exit();
}

$id = (int)$_GET['id'];

// --- Cargar datos actuales ---
$sql = "SELECT * FROM software WHERE id_software = $id LIMIT 1";
$result = $conexion->query($sql);

if (!$result || $result->num_rows == 0) {
    echo "<script>alert('❌ Software no encontrado.'); window.location='gestionSoftware.php';</script>";
    exit();
}

$software = $result->fetch_assoc();

// --- Procesar actualización ---
if (isset($_POST['actualizar'])) {
    $nombre_software = $conexion->real_escape_string($_POST['nombre_software']);
    $version = $conexion->real_escape_string($_POST['version']);
    $es_critico = isset($_POST['es_critico']) ? 1 : 0;

    $sqlUpdate = "
        UPDATE software 
        SET nombre_software = '$nombre_software',
            version = '$version',
            es_critico = $es_critico
        WHERE id_software = $id
    ";

    if ($conexion->query($sqlUpdate)) {
        echo "<script>alert('✅ Software actualizado correctamente'); window.location='gestionSoftware.php';</script>";
        exit();
    } else {
        echo "<script>alert('❌ Error al actualizar software');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Software</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/styleequipos.css">
</head>
<body>
    <div class="page">
        <header style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px">
            <h1>✏️ Editar Software</h1>
            <a class="back" href="gestionSoftware.php">⬅ Volver</a>
        </header>

        <section class="form-card">
            <form method="POST" action="">
                <div class="field">
                    <label for="nombre_software">Nombre del Software</label>
                    <input type="text" id="nombre_software" name="nombre_software" 
                           value="<?php echo htmlspecialchars($software['nombre_software']); ?>" required>
                </div>

                <div class="field">
                    <label for="version">Versión</label>
                    <input type="text" id="version" name="version" 
                           value="<?php echo htmlspecialchars($software['version']); ?>">
                </div>

                <div class="field">
                    <label>
                        <input type="checkbox" name="es_critico" value="1" 
                               <?php echo $software['es_critico'] ? "checked" : ""; ?>> Marcar como crítico
                    </label>
                </div>

                <div style="display:flex;gap:12px;align-items:center;margin-top:12px">
                    <button type="submit" name="actualizar">💾 Guardar cambios</button>
                    <a href="gestionSoftware.php" class="btn-outline">Cancelar</a>
                </div>
            </form>
        </section>
    </div>
</body>
</html>
