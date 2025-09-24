<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== "ENCARGADO") {
    header("Location: index.php");
    exit();
}

include("conexion.php");

// Obtener el id_establecimiento directo de la sesión
$id_establecimiento = $_SESSION['id_establecimiento'];

// --- CREAR EQUIPO ---
if (isset($_POST['crear'])) {
    $nombre_equipo = $conexion->real_escape_string($_POST['nombre_equipo']);
    $sistema_operativo = $conexion->real_escape_string($_POST['sistema_operativo']);

    $sql = "INSERT INTO equipos (nombre_equipo, sistema_operativo, id_establecimiento) 
            VALUES ('$nombre_equipo', '$sistema_operativo', '$id_establecimiento')";
    $conexion->query($sql);
    header("Location: gestionEquipos.php");
    exit();
}

// --- ELIMINAR EQUIPO ---
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    $conexion->query("DELETE FROM equipos WHERE id_equipo=$id AND id_establecimiento=$id_establecimiento");
    header("Location: gestionEquipos.php");
    exit();
}

// --- LISTAR EQUIPOS SOLO DEL ESTABLECIMIENTO ---
$equipos = $conexion->query("
    SELECT e.*, est.nombre_establecimiento 
    FROM equipos e
    LEFT JOIN establecimientos est ON e.id_establecimiento = est.id_establecimiento
    WHERE e.id_establecimiento = $id_establecimiento
    ORDER BY e.id_equipo DESC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Equipos</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="css/styleequipos.css">
    <link rel="icon" href="/img/logo.png">
</head>
<body>
    <div class="page">
        <header style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px">
            <div>
                <h1>💻 Gestión de Equipos</h1>
                <p style="color:#66788b;margin-top:6px">Registra y administra los equipos del establecimiento</p>
            </div>
            <div style="display:flex;gap:10px;align-items:center">
                <a class="back" href="menu.php">⬅ Volver al menú</a>
                <span class="badge">Establecimiento: <?php echo htmlspecialchars($_SESSION['establecimiento'] ?? '—'); ?></span>
            </div>
        </header>

        <!-- FORMULARIO CREAR (card) -->
        <section class="form-card" aria-labelledby="form-title">
            <div style="flex:1">
                <h2 id="form-title" style="margin:0 0 8px 0;color:var(--green-2)">➕ Registrar Equipo</h2>
                <form method="POST" action="" style="display:grid;gap:10px">
                    <div class="field">
                        <label for="nombre_equipo">Nombre del equipo</label>
                        <input id="nombre_equipo" type="text" name="nombre_equipo" placeholder="Ej: Aula-Comp-01" required>
                    </div>

                    <div class="field">
                        <label for="sistema_operativo">Sistema operativo / descripción</label>
                        <textarea id="sistema_operativo" name="sistema_operativo" placeholder="Windows 10 / Aula de informática" rows="3"></textarea>
                    </div>

                    <div style="display:flex;gap:12px;align-items:center;margin-top:6px">
                        <button type="submit" name="crear">Registrar</button>
                        <a href="gestionEquipos.php" class="btn-outline">Limpiar</a>
                    </div>
                </form>
            </div>
        </section>

        <!-- LISTADO -->
        <section style="margin-top:22px">
            <h2>📋 Lista de Equipos Registrados</h2>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre del Equipo</th>
                            <th>Sistema Operativo / Descripción</th>
                            <th>Establecimiento</th>
                            <th class="center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($equipos && $equipos->num_rows > 0): ?>
                            <?php while($row = $equipos->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['id_equipo']; ?></td>
                                <td><?php echo htmlspecialchars($row['nombre_equipo']); ?></td>
                                <td><?php echo htmlspecialchars($row['sistema_operativo']); ?></td>
                                <td><?php echo htmlspecialchars($row['nombre_establecimiento'] ?? $_SESSION['establecimiento']); ?></td>
                                <td class="center actions">
                                    <a href="editar_equipo.php?id=<?php echo $row['id_equipo']; ?>">✏️ Editar</a>
                                    <a class="delete" href="gestionEquipos.php?eliminar=<?php echo $row['id_equipo']; ?>" onclick="return confirm('¿Eliminar este equipo?')">🗑 Eliminar</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5">No hay equipos registrados en este establecimiento</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</body>
</html>
