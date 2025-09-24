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
    <link rel="stylesheet" href="css/stylemenu.css">
    <link rel="icon" href="/img/logo.png">
</head>
<body>
  <div class="container">
    <aside class="sidebar">
      <div class="brand">
        <img src="img/logo.png" alt="Logo" class="brand-logo">
        <div class="brand-text">
          <strong>Panel Usuario</strong>
          <small>Departamento de Educación</small>
        </div>
      </div>

      <nav class="main-nav">
        <ul class="nav-list">
          <li><a href="mis_licencias.php">📑 Mis licencias</a></li>
          <li><a href="foro.php">⚠️ Reportar problema</a></li>
        </ul>
      </nav>

      <div class="sidebar-footer">
        <a class="logout" href="logout.php">🚪 Cerrar sesión</a>
      </div>
    </aside>

    <main class="main">
      <header class="main-header">
        <div>
          <h1>🙋 Hola, <?php echo htmlspecialchars($_SESSION['nombre']); ?></h1>
          <p class="subtitle">Rol: <strong>USUARIO</strong></p>
        </div>

        <div class="quick-actions">
          <a class="btn btn-primary" href="mis_licencias.php">Ver mis licencias</a>
          <a class="btn btn-outline" href="reportar_problema.php">Reportar</a>
        </div>
      </header>

      <section class="cards">
        <article class="card">
          <h3>Licencias vigentes</h3>
          <div class="value" id="count-licencias">--</div>
        </article>

        <article class="card">
          <h3>Licencias por vencer</h3>
          <div class="value" id="count-vence">--</div>
        </article>

        <article class="card">
          <h3>Solicitudes abiertas</h3>
          <div class="value" id="count-requests">--</div>
        </article>
      </section>

      <section class="content-panel">
        <h2>Resumen rápido</h2>
        <p class="muted">Accede a tus licencias y envía solicitudes al encargado desde los botones superiores.</p>

        <div class="table-wrap">
          <table class="table" aria-label="Mis licencias">
            <thead>
              <tr>
                <th>ID</th>
                <th>Software</th>
                <th>Equipo</th>
                <th>Vencimiento</th>
                <th>Estado</th>
              </tr>
            </thead>
            <tbody id="mis-licencias-body">
              <!-- filas dinámicas desde PHP o AJAX -->
            </tbody>
          </table>
        </div>
      </section>

      <footer class="footer">
        <small>Departamento de Educación — Ilustre Municipalidad de Ovalle</small>
      </footer>
    </main>
  </div>

  <script>
    fetch('api/resumen_usuario.php').then(r=>r.json()).then(data=>{
    document.getElementById('count-licencias').textContent = data.vigentes;
    document.getElementById('count-vence').textContent = data.vencer;
    document.getElementById('count-requests').textContent = data.solicitudes;
    });
  </script>
</body>
</html>
