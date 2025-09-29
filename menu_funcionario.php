<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== "USUARIO") {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Panel Usuario</title>
  <!-- Normalize -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
  <!-- Estilos personalizados -->
  <link rel="stylesheet" href="css/stylemenu_funcionario.css">
  <link rel="icon" href="img/logo.png">
</head>
<body>
  <div class="layout">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="brand">
        <img src="img/logo.png" alt="Logo" class="brand-logo">
        <div class="brand-text">
          <h2>Panel Usuario</h2>
          <p>Departamento de Educación</p>
        </div>
      </div>
      <nav class="nav">
        <ul>
          <li><a href="mis_licencias.php">📑 Mis licencias</a></li>
          <li><a href="reportar_problema.php">⚠️ Reportar problema</a></li>
        </ul>
      </nav>
      <a href="logout.php" class="logout">🚪 Cerrar sesión</a>
    </aside>

    <!-- Contenido principal -->
    <main class="main">
      <header class="header">
        <div>
          <h1>🙋 Hola, <?= htmlspecialchars($_SESSION['nombre']) ?></h1>
          <span class="role">USUARIO</span>
        </div>
      </header>

      <section class="cards">
        <div class="card">
          <div class="card-icon">📑</div>
          <h3>Mis licencias</h3>
          <p>Revisa el estado y detalle de tus solicitudes.</p>
          <a href="mis_licencias.php" class="btn">Ir a licencias</a>
        </div>

        <div class="card">
          <div class="card-icon">⚠️</div>
          <h3>Reportar problema</h3>
          <p>Notifica cualquier inconveniente que tengas.</p>
          <a href="reportar_problema.php" class="btn">Enviar reporte</a>
        </div>
      </section>

      <footer class="footer">
        <small>Municipalidad de Ovalle – Departamento de Educación</small>
      </footer>
    </main>
  </div>
</body>
</html>
