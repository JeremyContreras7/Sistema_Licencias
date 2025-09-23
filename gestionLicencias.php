<?php
session_start();
if (!isset($_SESSION['rol']) || ($_SESSION['rol'] !== "ADMIN" && $_SESSION['rol'] !== "ENCARGADO")) {
    header("Location: index.php");
    exit();
}

include("conexion.php");

// --- CREAR LICENCIA ---
if (isset($_POST['crear'])) {
    $id_equipo = $_POST['id_equipo'];
    $id_software = $_POST['id_software'];
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_vencimiento = $_POST['fecha_vencimiento'];

    $sql = "INSERT INTO licencias (id_equipo, id_software, fecha_inicio, fecha_vencimiento) 
            VALUES ('$id_equipo','$id_software','$fecha_inicio','$fecha_vencimiento')";
    $conexion->query($sql);
}

// --- ELIMINAR LICENCIA ---
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $conexion->query("DELETE FROM licencias WHERE id_licencia=$id");
    header("Location: gestion_licencias.php");
    exit();
}

// --- LISTAR LICENCIAS ---
$sql = "SELECT l.id_licencia, e.nombre_equipo, s.nombre_software, s.version, 
               l.fecha_inicio, l.fecha_vencimiento
        FROM licencias l
        INNER JOIN equipos e ON l.id_equipo = e.id_equipo
        INNER JOIN software s ON l.id_software = s.id_software";
$licencias = $conexion->query($sql);

// --- LISTAR EQUIPOS Y SOFTWARE PARA EL SELECT ---
$equipos = $conexion->query("SELECT id_equipo, nombre_equipo FROM equipos");
$software = $conexion->query("SELECT id_software, nombre_software, version FROM software");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Licencias</title>
    <link rel="stylesheet" href="css/styleGlicencias.css">
</head>
<body>
    <h1>📝 Gestión de Licencias</h1>
    <a href="menu.php">⬅ Volver al menú</a>

    <!-- FORMULARIO CREAR -->
    <h2>➕ Registrar Licencia</h2>
    <form method="POST" action="">
        <label>Equipo:</label>
        <select name="id_equipo" required>
            <option disabled selected>Seleccionar equipo</option>
            <?php while($row = $equipos->fetch_assoc()): ?>
                <option value="<?= $row['id_equipo'] ?>"><?= $row['nombre_equipo'] ?></option>
            <?php endwhile; ?>
        </select>

        <label>Software:</label>
        <select name="id_software" required>
            <option disabled selected>Seleccionar software</option>
            <?php while($row = $software->fetch_assoc()): ?>
                <option value="<?= $row['id_software'] ?>"><?= $row['nombre_software'] ?> (<?= $row['version'] ?>)</option>
            <?php endwhile; ?>
        </select>

        <label>Fecha de Inicio:</label>
        <input type="date" name="fecha_inicio" required>
        <label>Fecha de Vencimiento:</label>
        <input type="date" name="fecha_vencimiento" required>

        <button type="submit" name="crear">Registrar</button>
    </form>

    <!-- LISTADO -->
    <h2>📋 Lista de Licencias</h2>
    <table border="1" cellpadding="5" cellspacing="0">
        <tr>
            <th>ID</th>
            <th>Equipo</th>
            <th>Software</th>
            <th>Versión</th>
            <th>Inicio</th>
            <th>Vencimiento</th>
            <th>Acciones</th>
        </tr>
        <?php while($row = $licencias->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id_licencia'] ?></td>
            <td><?= $row['nombre_equipo'] ?></td>
            <td><?= $row['nombre_software'] ?></td>
            <td><?= $row['version'] ?></td>
            <td><?= $row['fecha_inicio'] ?></td>
            <td><?= $row['fecha_vencimiento'] ?></td>
            <td>
                <a href="editar_licencia.php?id=<?= $row['id_licencia'] ?>">✏️ Editar</a> |
                <a href="gestion_licencias.php?eliminar=<?= $row['id_licencia'] ?>" onclick="return confirm('¿Eliminar esta licencia?')">🗑 Eliminar</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>
