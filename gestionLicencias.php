<?php
session_start();
if (!isset($_SESSION['rol']) || !in_array($_SESSION['rol'], ['ADMIN', 'ENCARGADO'])) {
    header('Location: index.php');
    exit;
}

require 'conexion.php';

// --- CREAR LICENCIA ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear'])) {
    $id_equipo       = $_POST['id_equipo'];
    $id_software     = $_POST['id_software'];
    $fecha_inicio    = $_POST['fecha_inicio'];
    $fecha_vencimiento = $_POST['fecha_vencimiento'];

    $stmt = $conexion->prepare(
        "INSERT INTO licencias (id_equipo, id_software, fecha_inicio, fecha_vencimiento)
         VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param('iiss', $id_equipo, $id_software, $fecha_inicio, $fecha_vencimiento);
    $stmt->execute();
}

// --- ELIMINAR LICENCIA ---
if (isset($_GET['eliminar'])) {
    $id = (int) $_GET['eliminar'];
    $stmt = $conexion->prepare("DELETE FROM licencias WHERE id_licencia = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    header('Location: gestion_licencias.php');
    exit;
}

// --- DATOS PARA VISTAS ---
$licencias = $conexion->query("
    SELECT l.id_licencia, e.nombre_equipo, s.nombre_software, s.version,
           l.fecha_inicio, l.fecha_vencimiento
    FROM licencias l
    JOIN equipos e ON l.id_equipo = e.id_equipo
    JOIN software s ON l.id_software = s.id_software
    ORDER BY l.fecha_vencimiento DESC
");

$equipos  = $conexion->query("SELECT id_equipo, nombre_equipo FROM equipos ORDER BY nombre_equipo");
$software = $conexion->query("SELECT id_software, nombre_software, version FROM software ORDER BY nombre_software");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Licencias</title>
    <link rel="stylesheet" href="css/styleGlicencias.css">
    <link rel="icon" href="/img/logo.png">
</head>
<body>
  <div class="container">

    <header class="header">
      <h1>📝 Gestión de Licencias</h1>
      <a href="menu.php" class="btn btn-secondary">⬅ Volver al menú</a>
    </header>

    <main>
      <!-- FORMULARIO CREAR -->
      <section class="card card-form">
        <h2 class="card-title">➕ Registrar Licencia</h2>
        <form method="POST" action="" class="form-grid">
          <div class="form-group">
            <label for="id_equipo">Equipo:</label>
            <select id="id_equipo" name="id_equipo" required>
              <option disabled selected>Seleccionar equipo</option>
              <?php while($e = $equipos->fetch_assoc()): ?>
                <option value="<?= $e['id_equipo'] ?>">
                  <?= htmlspecialchars($e['nombre_equipo']) ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="id_software">Software:</label>
            <select id="id_software" name="id_software" required>
              <option disabled selected>Seleccionar software</option>
              <?php while($s = $software->fetch_assoc()): ?>
                <option value="<?= $s['id_software'] ?>">
                  <?= htmlspecialchars($s['nombre_software']) ?> (v<?= $s['version'] ?>)
                </option>
              <?php endwhile; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="fecha_inicio">Fecha de Inicio:</label>
            <input type="date" id="fecha_inicio" name="fecha_inicio" required>
          </div>

          <div class="form-group">
            <label for="fecha_vencimiento">Fecha de Vencimiento:</label>
            <input type="date" id="fecha_vencimiento" name="fecha_vencimiento" required>
          </div>

          <div class="form-actions">
            <button type="submit" name="crear" class="btn btn-primary">Registrar</button>
          </div>
        </form>
      </section>

      <!-- LISTADO -->
      <section class="card card-table">
        <h2 class="card-title">📋 Lista de Licencias</h2>
        <table class="table table-striped">
          <thead>
            <tr>
              <th>ID</th>
              <th>Equipo</th>
              <th>Software</th>
              <th>Versión</th>
              <th>Inicio</th>
              <th>Vencimiento</th>
              <th>Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php while($row = $licencias->fetch_assoc()): ?>
            <tr>
              <td><?= $row['id_licencia'] ?></td>
              <td><?= htmlspecialchars($row['nombre_equipo']) ?></td>
              <td><?= htmlspecialchars($row['nombre_software']) ?></td>
              <td><?= $row['version'] ?></td>
              <td><?= $row['fecha_inicio'] ?></td>
              <td><?= $row['fecha_vencimiento'] ?></td>
              <td class="actions">
                <a href="editar_licencia.php?id=<?= $row['id_licencia'] ?>" class="btn btn-sm btn-warning">✏️ Editar</a>
                <a href="?eliminar=<?= $row['id_licencia'] ?>"
                   class="btn btn-sm btn-danger"
                   onclick="return confirm('¿Eliminar esta licencia?')">
                  🗑 Eliminar
                </a>
              </td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </section>
    </main>

  </div>
</body>
</html>
