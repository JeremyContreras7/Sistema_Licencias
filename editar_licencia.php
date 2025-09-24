<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== "ENCARGADO") {
    header("Location: index.php");
    exit();
}

include("conexion.php");

$id_establecimiento = $_SESSION['id_establecimiento'];
$id_licencia = (int)($_GET['id'] ?? 0);

// --- Obtener licencia ---
$sql = "
    SELECT l.*, e.nombre_equipo, s.nombre_software, s.version
    FROM licencias l
    INNER JOIN equipos e ON l.id_equipo = e.id_equipo
    INNER JOIN software s ON l.id_software = s.id_software
    WHERE l.id_licencia = ? AND e.id_establecimiento = ?
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ii", $id_licencia, $id_establecimiento);
$stmt->execute();
$result = $stmt->get_result();
$licencia = $result->fetch_assoc();
$stmt->close();

if (!$licencia) {
    die("❌ No tienes permisos para editar esta licencia.");
}

// --- Actualizar licencia ---
if (isset($_POST['actualizar'])) {
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_vencimiento = $_POST['fecha_vencimiento'];

    $sql = "UPDATE licencias SET fecha_inicio=?, fecha_vencimiento=? WHERE id_licencia=? LIMIT 1";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssi", $fecha_inicio, $fecha_vencimiento, $id_licencia);

    if ($stmt->execute()) {
        echo "<script>alert('✅ Licencia actualizada correctamente'); window.location='gestionLicencias.php';</script>";
    } else {
        echo "<script>alert('❌ Error al actualizar'); window.location='gestionLicencias.php';</script>";
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Licencia</title>
</head>
<body>
    <h1>✏️ Editar Licencia</h1>
    <a href="gestionLicencias.php">⬅ Volver</a>

    <form method="POST">
        <p><strong>Equipo:</strong> <?= htmlspecialchars($licencia['nombre_equipo']) ?></p>
        <p><strong>Software:</strong> <?= htmlspecialchars($licencia['nombre_software'] . " " . $licencia['version']) ?></p>

        <label>Fecha inicio:</label>
        <input type="date" name="fecha_inicio" value="<?= $licencia['fecha_inicio'] ?>" required><br>

        <label>Fecha vencimiento:</label>
        <input type="date" name="fecha_vencimiento" value="<?= $licencia['fecha_vencimiento'] ?>" required><br>

        <button type="submit" name="actualizar">Guardar cambios</button>
    </form>
</body>
</html>
