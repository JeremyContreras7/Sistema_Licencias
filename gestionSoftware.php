<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== "ENCARGADO") {
    header("Location: index.php");
    exit();
}

include("conexion.php");

// --- CREAR SOFTWARE ---
if (isset($_POST['crear'])) {
    $nombre_software = $conexion->real_escape_string($_POST['nombre_software']);
    $version = $conexion->real_escape_string($_POST['version']);
    $es_critico = isset($_POST['es_critico']) ? 1 : 0;

    $sql = "INSERT INTO software (nombre_software, version, es_critico) 
            VALUES ('$nombre_software', '$version', $es_critico)";
    $conexion->query($sql);
    header("Location: gestionSoftware.php");
    exit();
}

// --- ELIMINAR SOFTWARE ---
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    $conexion->query("DELETE FROM software WHERE id_software=$id");
    header("Location: gestionSoftware.php");
    exit();
}

// --- LISTAR SOFTWARE ---
$software = $conexion->query("
    SELECT * FROM software
    ORDER BY id_software DESC
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
                <p style="color:#66788b;margin-top:6px">Registra y administra el software disponible</p>
            </div>
            <div style="display:flex;gap:10px;align-items:center">
                <a class="back" href="menu.php">⬅ Volver al menú</a>
                <span class="badge">Encargado: <?php echo htmlspecialchars($_SESSION['nombre'] ?? '—'); ?></span>
            </div>
        </header>

        <!-- FORMULARIO CREAR (card) -->
        <section class="form-card" aria-labelledby="form-title">
            <div style="flex:1">
                <h2 id="form-title" style="margin:0 0 8px 0;color:var(--green-2)">➕ Registrar Software</h2>

                <form method="POST" action="" style="display:grid;gap:10px">
                    <div class="field">
                        <label for="nombre_software">Nombre del Software</label>
                        <input id="nombre_software" type="text" name="nombre_software" placeholder="Ej: Microsoft Office" required>
                    </div>

                    <div class="field">
                        <label for="version">Versión</label>
                        <input id="version" type="text" name="version" placeholder="Ej: 2021">
                    </div>

                    <div class="field">
                        <label>
                            <input type="checkbox" name="es_critico" value="1"> Marcar como crítico
                        </label>
                    </div>

                    <div style="display:flex;gap:12px;align-items:center;margin-top:6px">
                        <button type="submit" name="crear">Registrar</button>
                        <a href="gestionSoftware.php" class="btn-outline">Limpiar</a>
                    </div>
                </form>
            </div>
        </section>

        <!-- LISTADO -->
        <section style="margin-top:22px">
            <h2>📋 Lista de Software Registrado</h2>
            <div class="table-wrap">
                <table class="table" role="table" aria-label="Lista de software">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Versión</th>
                            <th>Crítico</th>
                            <th class="center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($software && $software->num_rows > 0): ?>
                            <?php while($row = $software->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id_software']; ?></td>
                                <td><?php echo htmlspecialchars($row['nombre_software']); ?></td>
                                <td><?php echo htmlspecialchars($row['version']); ?></td>
                                <td><?php echo $row['es_critico'] ? "✅ Sí" : "❌ No"; ?></td>
                                <td class="center actions">
                                    <a href="editar_software.php?id=<?php echo $row['id_software']; ?>">✏️ Editar</a>
                                    <a class="delete" href="gestionSoftware.php?eliminar=<?php echo $row['id_software']; ?>" onclick="return confirm('¿Eliminar este software?')">🗑 Eliminar</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">No hay software registrado</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</body>
</html>
