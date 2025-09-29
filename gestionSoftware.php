<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== "ENCARGADO") {
    header("Location: index.php");
    exit();
}

include("conexion.php");

// Obtener el establecimiento del usuario logueado
$id_establecimiento = $_SESSION['id_establecimiento'];

// --- CREAR SOFTWARE ---
if (isset($_POST['crear'])) {
    $nombre_software = $conexion->real_escape_string($_POST['nombre_software']);
    $version = $conexion->real_escape_string($_POST['version']);
    $es_critico = isset($_POST['es_critico']) ? 1 : 0;

    $sql = "INSERT INTO software (nombre_software, version, es_critico, id_establecimiento) 
            VALUES ('$nombre_software', '$version', $es_critico, $id_establecimiento)";
    $conexion->query($sql);
    header("Location: gestionSoftware.php");
    exit();
}

// --- ELIMINAR SOFTWARE ---
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    // Elimina solo si pertenece al establecimiento del encargado
    $conexion->query("DELETE FROM software WHERE id_software=$id AND id_establecimiento=$id_establecimiento");
    header("Location: gestionSoftware.php");
    exit();
}

// --- LISTAR SOFTWARE SOLO DEL ESTABLECIMIENTO ---
$software = $conexion->query("
    SELECT s.*, e.nombre_establecimiento 
    FROM software s
    INNER JOIN establecimientos e ON s.id_establecimiento = e.id_establecimiento
    WHERE s.id_establecimiento = $id_establecimiento
    ORDER BY s.id_software DESC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Software</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/styleequipos.css">
    <link rel="icon" href="/img/logo.png">
</head>
<body>
    <div class="page">
        <header style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px">
            <div>
                <h1>📦 Gestión de Software</h1>
                <p style="color:#66788b;margin-top:6px">Registra y administra el software disponible en tu establecimiento</p>
            </div>
            <div style="display:flex;gap:10px;align-items:center">
                <a class="back" href="menu.php">⬅ Volver al menú</a>
                <span class="badge">Encargado: <?php echo htmlspecialchars($_SESSION['nombre'] ?? '—'); ?></span>
            </div>
        </header>

        <!-- FORMULARIO CREAR -->
        <section class="form-card">
            <h2>➕ Registrar Software</h2>
            <form method="POST" action="">
                <div class="field">
                    <label for="nombre_software">Nombre del Software</label>
                    <input id="nombre_software" type="text" name="nombre_software" required>
                </div>
                <div class="field">
                    <label for="version">Versión</label>
                    <input id="version" type="text" name="version">
                </div>
                <div class="field">
                    <label><input type="checkbox" name="es_critico" value="1"> Marcar como crítico</label>
                </div>
                <button type="submit" name="crear">Registrar</button>
            </form>
        </section>

        <!-- LISTADO -->
        <section style="margin-top:22px">
            <h2>📋 Lista de Software Registrado</h2>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Versión</th>
                            <th>Crítico</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($software && $software->num_rows > 0): ?>
                            <?php while($row = $software->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['id_software'] ?></td>
                                    <td><?= htmlspecialchars($row['nombre_software']) ?></td>
                                    <td><?= htmlspecialchars($row['version']) ?></td>
                                    <td><?= $row['es_critico'] ? "✅ Sí" : "❌ No" ?></td>
                                    <td>
                                        <a href="editar_software.php?id=<?= $row['id_software'] ?>">✏️ Editar</a>
                                        <a href="gestionSoftware.php?eliminar=<?= $row['id_software'] ?>" onclick="return confirm('¿Eliminar este software?')">🗑 Eliminar</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="5">No hay software registrado en tu establecimiento</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</body>
</html>
