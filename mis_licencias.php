<?php
session_start();
if (!isset($_SESSION['rol'])) {
    header("Location: index.php");
    exit();
}
include("conexion.php");
$id_establecimiento = $_SESSION['id_establecimiento'];

$sql = "
  SELECT l.id_licencia, e.nombre_equipo, s.nombre_software, s.version,
         l.fecha_inicio, l.fecha_vencimiento, s.es_critico
  FROM licencias l
  INNER JOIN equipos e ON l.id_equipo = e.id_equipo
  INNER JOIN software s ON l.id_software = s.id_software
  WHERE e.id_establecimiento = ?
  ORDER BY l.fecha_vencimiento ASC
";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id_establecimiento);
$stmt->execute();
$licencias = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Mis Licencias</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Normalize y Google Fonts -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <!-- Estilos personalizados -->
  <link rel="stylesheet" href="css/styleLicencia.css">
  <link rel="icon" href="img/logo.png">
</head>
<body>
  <div class="container">
    <header class="page-header">
      <h1>🔑 Mis Licencias</h1>
      <div class="user-info">
        <span><?= htmlspecialchars($_SESSION['nombre']) ?></span>
        <span class="badge"><?= htmlspecialchars($_SESSION['rol']) ?></span>
      </div>
    </header>

    <div class="top-bar">
      <a href="menu_funcionario.php" class="btn-back">⬅ Volver al menú</a>
      <input type="text" id="search" class="search" placeholder="Buscar licencia…">
    </div>

    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Equipo</th>
            <th>Software</th>
            <th>Versión</th>
            <th>Inicio</th>
            <th>Vencimiento</th>
            <th>Estado</th>
            <th>Crítico</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($licencias->num_rows): ?>
            <?php while ($row = $licencias->fetch_assoc()): ?>
              <?php
              $hoy = date("Y-m-d");
              $fven = $row['fecha_vencimiento'];
              if ($fven < $hoy) {
                $estado = 'vencida';
                $texto = '❌ Vencida';
              } elseif ($fven <= date("Y-m-d", strtotime("+30 days"))) {
                $estado = 'por-vencer';
                $texto = '⚠️ Por vencer';
              } else {
                $estado = 'vigente';
                $texto = '✅ Vigente';
              }
              ?>
              <tr>
                <td><?= $row['id_licencia'] ?></td>
                <td><?= htmlspecialchars($row['nombre_equipo']) ?></td>
                <td><?= htmlspecialchars($row['nombre_software']) ?></td>
                <td><?= htmlspecialchars($row['version']) ?></td>
                <td><?= $row['fecha_inicio'] ?></td>
                <td><?= $fven ?></td>
                <td><span class="badge status <?= $estado ?>"><?= $texto ?></span></td>
                <td>
                  <?= $row['es_critico']
                    ? '<span class="badge critico">Sí</span>'
                    : '<span class="badge normal">No</span>' ?>
                </td>
              </tr>
            <?php endwhile; ?>
          <?php else: ?>
            <tr>
              <td colspan="8" class="no-data">No hay licencias registradas</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <script>
    document.getElementById('search').addEventListener('input', function(e) {
      const term = e.target.value.toLowerCase();
      document.querySelectorAll('tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
      });
    });
  </script>
</body>
</html>
