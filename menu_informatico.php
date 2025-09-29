<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== "ENCARGADO") {
    header("Location: index.php");
    exit();
}
include("conexion.php");

$id_establecimiento = $_SESSION['id_establecimiento'] ?? 0;
$nombre_usuario   = htmlspecialchars($_SESSION['nombre'] ?? '');

$stmt = $conexion->prepare("
    SELECT u.id_usuario, u.nombre, u.correo, u.rol, u.tipo_encargado, u.fecha_registro
    FROM usuarios u
    WHERE u.id_establecimiento = ?
    ORDER BY u.fecha_registro DESC
");
$stmt->bind_param("i", $id_establecimiento);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Panel Encargado Informático</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Normalize.css -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <!-- Estilos personalizados -->
  <link rel="stylesheet" href="css/styleMenu.css">
  <link rel="icon" href="img/logo.png">
</head>
<body>
  <div class="layout">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="brand">
        <img src="img/logo.png" alt="Logo" class="brand-logo">
        <div class="brand-text">
          <h2>Panel Encargado</h2>
          <p>Departamento de Educación</p>
        </div>
      </div>
      <nav class="nav">
        <ul>
          <li><a href="registrar.php">👥 Crear cuentas</a></li>
          <li><a href="gestionEquipos.php">💻 Registro de equipos</a></li>
          <li><a href="gestionLicencias.php">📋 Registro de licencias</a></li>
          <li><a href="gestionSoftware.php">💽 Registro de software</a></li>
          <li><a href="foro.php">📧 Foro consulta</a></li>
        </ul>
      </nav>
      <a href="logout.php" class="logout">🚪 Cerrar sesión</a>
    </aside>

    <!-- Contenido principal -->
    <main class="main">
      <header class="header">
        <div>
          <h1>👨‍💻 Bienvenido, <?= $nombre_usuario ?></h1>
          <span class="role">ENCARGADO</span>
        </div>
        <div class="actions">
          <a href="registrar.php" class="btn primary">Crear cuentas</a>
          <a href="gestionEquipos.php" class="btn outline">Equipos</a>
        </div>
      </header>

      <!-- Tarjetas de acceso rápido -->
      <section class="cards">
        <div class="card">
          <div class="icon">👥</div>
          <h3>Crear cuentas</h3>
          <p>Genera nuevos accesos para tu establecimiento.</p>
          <a href="registrar.php" class="btn">Ir</a>
        </div>
        <div class="card">
          <div class="icon">💻</div>
          <h3>Equipos</h3>
          <p>Añade o edita el inventario de computadoras.</p>
          <a href="gestionEquipos.php" class="btn">Ir</a>
        </div>
        <div class="card">
          <div class="icon">📋</div>
          <h3>Licencias</h3>
          <p>Administra licencias de software.</p>
          <a href="gestionLicencias.php" class="btn">Ir</a>
        </div>
        <div class="card">
          <div class="icon">💽</div>
          <h3>Software</h3>
          <p>Mantén actualizado el catálogo de programas.</p>
          <a href="gestionSoftware.php" class="btn">Ir</a>
        </div>
        <div class="card">
          <div class="icon">📧</div>
          <h3>Foro consulta</h3>
          <p>Revisa dudas y soporte de la comunidad.</p>
          <a href="foro.php" class="btn">Ir</a>
        </div>
      </section>

      <!-- Tabla de usuarios -->
      <section class="table-section">
        <h2>Usuarios de tu Establecimiento</h2>
        <input type="text" id="search" class="search" placeholder="Buscar usuario…">
        <div class="table-container">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Rol</th>
                <th>Tipo Encargado</th>
                <th>Fecha Registro</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($result->num_rows): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                  <tr>
                    <td><?= $row['id_usuario'] ?></td>
                    <td><?= htmlspecialchars($row['nombre']) ?></td>
                    <td><?= htmlspecialchars($row['correo']) ?></td>
                    <td><?= htmlspecialchars($row['rol']) ?></td>
                    <td><?= htmlspecialchars($row['tipo_encargado'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($row['fecha_registro']) ?></td>
                  </tr>
                <?php endwhile; ?>
              <?php else: ?>
                <tr>
                  <td colspan="6" class="no-data">No hay usuarios registrados</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>

      <footer class="footer">
        <small>Departamento de Educación — Municipalidad de Ovalle</small>
      </footer>
    </main>
  </div>

  <script>
    // Búsqueda en tiempo real
    document.getElementById('search').addEventListener('input', function(e) {
      const term = e.target.value.toLowerCase();
      document.querySelectorAll('tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
      });
    });
  </script>
</body>
</html>
