<?php
session_start();
if (!isset($_SESSION['rol'])) {
    header("Location: index.php");
    exit();
}

include("conexion.php");

// Obtener ID del establecimiento desde la sesión
$id_establecimiento = $_SESSION['id_establecimiento'];

// Consulta: Licencias de los equipos del establecimiento del usuario
$sql = "
    SELECT l.id_licencia, e.nombre_equipo, s.nombre_software, s.version, 
           l.fecha_inicio, l.fecha_vencimiento, s.es_critico, est.nombre_establecimiento
    FROM licencias l
    INNER JOIN equipos e ON l.id_equipo = e.id_equipo
    INNER JOIN software s ON l.id_software = s.id_software
    INNER JOIN establecimientos est ON e.id_establecimiento = est.id_establecimiento
    WHERE e.id_establecimiento = $id_establecimiento
";
$licencias = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Licencias</title>
    <link rel="icon" href="/img/logo.png">
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        h1 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: center; }
        th { background: #f4f4f4; }
        .critico { color: red; font-weight: bold; }
        .alerta { background: #ffcccc; }
    </style>
</head>
<body>
    <h1>🔑 Mis Licencias (<?= $_SESSION['nombre'] ?> - <?= $_SESSION['rol'] ?>)</h1>
    <a href="menu.php">⬅ Volver al menú</a>

    <table>
        <tr>
            <th>ID</th>
            <th>Equipo</th>
            <th>Software</th>
            <th>Versión</th>
            <th>Fecha Inicio</th>
            <th>Fecha Vencimiento</th>
            <th>Estado</th>
            <th>Crítico</th>
        </tr>
        <?php if ($licencias->num_rows > 0): ?>
            <?php while($row = $licencias->fetch_assoc()): ?>
                <?php
                    $hoy = date("Y-m-d");
                    $estado = "";
                    $clase = "";

                    if ($row['fecha_vencimiento'] < $hoy) {
                        $estado = "❌ Vencida";
                        $clase = "alerta";
                    } elseif ($row['fecha_vencimiento'] <= date("Y-m-d", strtotime("+30 days"))) {
                        $estado = "⚠️ Por vencer";
                        $clase = "alerta";
                    } else {
                        $estado = "✅ Vigente";
                    }
                ?>
                <tr class="<?= $clase ?>">
                    <td><?= $row['id_licencia'] ?></td>
                    <td><?= $row['nombre_equipo'] ?></td>
                    <td><?= $row['nombre_software'] ?></td>
                    <td><?= $row['version'] ?></td>
                    <td><?= $row['fecha_inicio'] ?></td>
                    <td><?= $row['fecha_vencimiento'] ?></td>
                    <td><?= $estado ?></td>
                    <td><?= $row['es_critico'] ? '<span class="critico">Sí</span>' : 'No' ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="8">No hay licencias registradas para tu establecimiento</td></tr>
        <?php endif; ?>
    </table>
</body>
</html>
