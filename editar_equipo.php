<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== "ENCARGADO") {
    header("Location: index.php");
    exit();
}

include("conexion.php");

$id_establecimiento = $_SESSION['id_establecimiento'];

// Validar ID recibido
if (!isset($_GET['id'])) {
    header("Location: gestionEquipos.php");
    exit();
}
$id_equipo = (int)$_GET['id'];

// Obtener datos del equipo
$stmt = $conexion->prepare("SELECT * FROM equipos WHERE id_equipo = ? AND id_establecimiento = ?");
$stmt->bind_param("ii", $id_equipo, $id_establecimiento);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>alert('❌ Equipo no encontrado.'); window.location='gestionEquipos.php';</script>";
    exit();
}

$equipo = $result->fetch_assoc();

// Guardar cambios
if (isset($_POST['guardar'])) {
    $nombre_equipo = $conexion->real_escape_string($_POST['nombre_equipo']);
    $sistema_operativo = $conexion->real_escape_string($_POST['sistema_operativo']);

    $update = $conexion->prepare("UPDATE equipos SET nombre_equipo = ?, sistema_operativo = ? WHERE id_equipo = ? AND id_establecimiento = ?");
    $update->bind_param("ssii", $nombre_equipo, $sistema_operativo, $id_equipo, $id_establecimiento);

    if ($update->execute()) {
        echo "<script>alert('✅ Equipo actualizado correctamente.'); window.location='gestionEquipos.php';</script>";
        exit();
    } else {
        echo "<script>alert('❌ Error al actualizar el equipo.');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Equipo</title>
    <link rel="stylesheet" href="css/styleequipos.css">
</head>
<body>
<div class="page">
    <h1>✏️ Editar Equipo</h1>
    <form method="POST" action="">
        <label>Nombre del equipo</label><br>
        <input type="text" name="nombre_equipo" value="<?= htmlspecialchars($equipo['nombre_equipo']) ?>" required><br><br>

        <label>Sistema operativo / Descripción</label><br>
        <textarea name="sistema_operativo" rows="3"><?= htmlspecialchars($equipo['sistema_operativo']) ?></textarea><br><br>

        <button type="submit" name="guardar">💾 Guardar cambios</button>
        <a href="gestionEquipos.php">⬅ Cancelar</a>
    </form>
</div>
</body>
</html>
